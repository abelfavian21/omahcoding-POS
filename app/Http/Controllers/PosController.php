<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class PosController extends Controller
{
    public function index(Request $request)
    {
        $products = Product::active()->orderBy('name')->get()->map(fn (Product $p) => [
            'id' => $p->id,
            'name' => $p->name,
            'price' => (float) $p->price,
            'stock' => $p->stock,
            'emoji' => $p->emoji ?: '🍽️',
            'image' => $p->image_url,
            'category_id' => $p->category_id,
        ])->values();

        $customer = $request->customer ? Customer::find($request->customer) : null;

        return view('pos.index', [
            'products' => $products,
            'categories' => Category::orderBy('sort_order')->get(['id', 'name', 'emoji']),
            'customer' => $customer ? $this->customerJson($customer) : null,
            'taxRate' => (float) config('pos.tax_rate'),
            'taxLabel' => config('pos.tax_label'),
            'nextInvoice' => Order::nextInvoiceNo(),
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

    public function checkout(Request $request)
    {
        $data = $request->validate([
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'integer', \Illuminate\Validation\Rule::exists('products', 'id')->where('business_id', auth()->user()->business_id)],
            'items.*.qty' => ['required', 'integer', 'min:1', 'max:999'],
            'customer_id' => ['nullable', \Illuminate\Validation\Rule::exists('customers', 'id')->where('business_id', auth()->user()->business_id)],
            'order_type' => ['required', 'in:dine_in,takeaway'],
            'table_no' => ['nullable', 'string', 'max:10'],
            'discount' => ['nullable', 'numeric', 'min:0'],
            'payment_method' => ['required', 'in:cash,qris,card'],
            'paid_amount' => ['nullable', 'numeric', 'min:0'],
            'note' => ['nullable', 'string', 'max:255'],
        ]);

        try {
            $order = DB::transaction(function () use ($data, $request) {
                // gabungkan baris produk yang sama; HARGA SELALU diambil dari database, bukan dari browser
                $lines = collect($data['items'])->groupBy('product_id')->map(fn ($g) => (int) $g->sum('qty'));
                $products = Product::whereIn('id', $lines->keys())->get()->keyBy('id');

                $subtotal = 0;
                $rows = [];
                foreach ($lines as $pid => $qty) {
                    $p = $products[$pid];
                    if (! $p->is_active) throw new RuntimeException("{$p->name} sedang tidak dijual.");
                    $rows[] = [
                        'product' => $p, 'qty' => $qty,
                        'data' => ['product_id' => $p->id, 'product_name' => $p->name, 'price' => $p->price,
                                   'cost_price' => $p->cost_price, 'qty' => $qty, 'subtotal' => $p->price * $qty],
                    ];
                    $subtotal += $p->price * $qty;
                }

                $discount = (float) ($data['discount'] ?? 0);
                if ($discount > $subtotal) throw new RuntimeException('Diskon tidak boleh melebihi subtotal.');

                $tax = round(($subtotal - $discount) * config('pos.tax_rate'));
                $total = $subtotal - $discount + $tax;

                $method = $data['payment_method'];
                $paid = $method === 'cash' ? (float) ($data['paid_amount'] ?? 0) : $total;
                if ($paid < $total) throw new RuntimeException('Uang diterima kurang dari total tagihan.');

                $order = Order::create([
                    'invoice_no' => Order::nextInvoiceNo(),
                    'user_id' => $request->user()->id,
                    'customer_id' => $data['customer_id'] ?? null,
                    'order_type' => $data['order_type'],
                    'table_no' => $data['order_type'] === 'dine_in' ? ($data['table_no'] ?? null) : null,
                    'subtotal' => $subtotal, 'discount' => $discount, 'tax' => $tax, 'total' => $total,
                    'payment_method' => $method, 'paid_amount' => $paid, 'change_amount' => $paid - $total,
                    'status' => 'paid', 'note' => $data['note'] ?? null, 'paid_at' => now(),
                ]);

                foreach ($rows as $r) {
                    $order->items()->create($r['data']);
                    // melempar RuntimeException bila stok kurang -> seluruh transaksi di-rollback
                    $r['product']->adjustStock(-$r['qty'], 'sale', $order->invoice_no, $request->user()->id, $order->id);
                }

                $order->customer?->applyPurchase($total, $order->paid_at);

                return $order;
            });
        } catch (RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
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

    private function customerJson(Customer $c): array
    {
        return ['id' => $c->id, 'name' => $c->name, 'phone' => $c->phone, 'code' => $c->code, 'tier' => $c->tier, 'points' => $c->points, 'allergy' => $c->allergy];
    }
}
