<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Category;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\Request;
use App\Models\CashShift;
use App\Services\CheckoutService;
use App\Services\MidtransClient;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use RuntimeException;

class PosController extends Controller
{
    public function index(Request $request)
    {
        $products = Product::active()->with(['variants', 'recipes'])->orderBy('name')->get()->map(fn (Product $p) => [
            'id' => $p->id,
            'name' => $p->name,
            'price' => (float) $p->price,
            'stock' => $p->stock,
            'has_recipe' => $p->recipes->isNotEmpty(),
            'emoji' => $p->emoji,   // emoji lama atau nama ikon; diterjemahkan di browser
            'image' => $p->image_url,
            'category_id' => $p->category_id,
            'variants' => $p->variants->map(fn ($v) => [
                'id' => $v->id,
                'group' => $v->group_name,
                'name' => $v->name,
                'price' => (float) $v->price_extra,
            ])->values(),
        ])->values();

        $customer = $request->customer ? Customer::find($request->customer) : null;

        return view('pos.index', [
            'products' => $products,
            'categories' => Category::orderBy('sort_order')->get(['id', 'name', 'emoji']),
            'customer' => $customer ? $this->customerJson($customer) : null,
            'taxRate' => (float) ($request->user()->business?->taxRate() ?? config('pos.tax_rate')),
            'taxLabel' => $request->user()->business?->tax_label ?? config('pos.tax_label'),
            'shiftOpen' => (bool) CashShift::currentFor($request->user()),
            'shiftRequired' => (bool) $request->user()->business?->requiresShift(),
            'isRetail' => (bool) $request->user()->business?->isRetail(),
            'nextInvoice' => Order::nextInvoiceNo(),
            'qrisDynamic' => MidtransClient::forBusiness($request->user()->business)->enabled(),
            'qrisManualUrl' => $request->user()->business?->hasManualQris()
                ? route('pos.qris.manual', ['v' => substr(md5($request->user()->business->qris_image), 0, 8)]) : null,
            'qrisNeedsRef' => (bool) $request->user()->business?->qrisManualNeedsRef(),
        ]);
    }

    public function customers(Request $request)
    {
        $q = trim((string) $request->q);

        return Customer::query()
            ->when($q !== '', fn ($w) => $w->where(fn ($x) => $x->where('name', 'like', "%$q%")->orWhere('phone', 'like', "%$q%")->orWhere('code', 'like', "%$q%")))
            ->orderBy('name')->limit(8)->get()
            ->map(fn ($c) => $this->customerJson($c));
    }

    public function quickCustomer(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
        ]);

        return response()->json($this->customerJson(Customer::create($data)), 201);
    }

    public function checkout(Request $request, CheckoutService $checkout)
    {
        // Key Midtrans milik bisnis ini (BUKAN injeksi container: itu membuat klien tanpa key -> enabled() selalu false)
        $mt = MidtransClient::forBusiness($request->user()->business);
        $bid = $request->user()->business_id;
        // Nomor referensi: kartu selalu wajib; QRIS manual wajib bila bisnis belum mengunggah gambar QRIS atau admin mewajibkannya
        $needsRef = $request->input('payment_method') === 'card'
            || ($request->input('payment_method') === 'qris' && $request->user()->business->qrisManualNeedsRef());

        if ($request->user()->business?->isRetail()) {
            $request->merge(['order_type' => 'takeaway', 'table_no' => null]);
        }

        $data = $request->validate([
            'idempotency_key' => ['required', 'uuid'],
            'items' => ['required', 'array', 'min:1', 'max:100'],
            'items.*.product_id' => ['required', 'integer', Rule::exists('products', 'id')->where('business_id', $bid)],
            'items.*.qty' => ['required', 'integer', 'min:1', 'max:999'],
            'items.*.variant_ids' => ['nullable', 'array'],
            'items.*.variant_ids.*' => ['integer'],
            'customer_id' => ['nullable', Rule::exists('customers', 'id')->where('business_id', $bid)],
            'order_type' => ['required', 'in:dine_in,takeaway'],
            'table_no' => ['nullable', 'string', 'max:10'],
            'discount' => ['nullable', 'numeric', 'min:0', 'max:999999999'],
            'payment_method' => ['required', 'in:cash,qris,card'],
            // pembayaran manual (QRIS statis / kartu) tidak bisa diverifikasi server; wajib referensi sesuai aturan di atas
            'payment_ref' => ['nullable', 'string', 'max:60', Rule::requiredIf($needsRef)],
            'paid_amount' => ['nullable', 'numeric', 'min:0', 'max:9999999999'],
            'note' => ['nullable', 'string', 'max:255'],
        ]);

        // QRIS dinamis aktif: QRIS hanya sah lewat /pos/qris (diverifikasi Midtrans), bukan input manual kasir
        if ($data['payment_method'] === 'qris' && $mt->enabled()) {
            return response()->json(['message' => 'QRIS dinamis aktif. Gunakan tombol QRIS untuk membuat kode bayar.'], 422);
        }

        try {
            $order = $checkout->checkout($request->user(), $data);
        } catch (RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        if ($data['payment_method'] !== 'cash') {
            AuditLog::record('order.manual_payment', $order->invoice_no, [
                'method' => $data['payment_method'], 'ref' => $data['payment_ref'] ?? null, 'total' => (float) $order->total,
            ]);
        }

        return response()->json([
            'order_id' => $order->id,
            'invoice_no' => $order->invoice_no,
            'total' => (float) $order->total,
            'change' => (float) $order->change_amount,
            'receipt_url' => route('orders.receipt', $order),
            'download_url' => route('orders.receipt', [$order, 'download' => 1]),
        ], 201);
    }

    /** Gambar QRIS manual milik bisnis kasir yang login (disk privat, tidak pernah publik). */
    public function manualQris(Request $request)
    {
        $business = $request->user()->business;
        abort_unless($business && $business->hasManualQris(), 404);

        return Storage::disk('local')->response($business->qris_image, null, ['Cache-Control' => 'private, max-age=3600']);
    }

    private function customerJson(Customer $c): array
    {
        return ['id' => $c->id, 'name' => $c->name, 'phone' => $c->phone, 'code' => $c->code, 'tier' => $c->tier, 'points' => $c->points, 'allergy' => $c->allergy];
    }
}
