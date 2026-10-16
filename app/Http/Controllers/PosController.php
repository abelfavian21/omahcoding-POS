<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\Request;
use App\Models\CashShift;
use App\Services\CheckoutService;
use Illuminate\Validation\Rule;
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
            'taxRate' => (float) ($request->user()->business?->taxRate() ?? config('pos.tax_rate')),
            'taxLabel' => $request->user()->business?->tax_label ?? config('pos.tax_label'),
            'shiftOpen' => (bool) CashShift::currentFor($request->user()),
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

    public function checkout(Request $request, CheckoutService $checkout)
    {
        $bid = $request->user()->business_id;

        $data = $request->validate([
            'idempotency_key' => ['required', 'uuid'],
            'items' => ['required', 'array', 'min:1', 'max:100'],
            'items.*.product_id' => ['required', 'integer', Rule::exists('products', 'id')->where('business_id', $bid)],
            'items.*.qty' => ['required', 'integer', 'min:1', 'max:999'],
            'customer_id' => ['nullable', Rule::exists('customers', 'id')->where('business_id', $bid)],
            'order_type' => ['required', 'in:dine_in,takeaway'],
            'table_no' => ['nullable', 'string', 'max:10'],
            'discount' => ['nullable', 'numeric', 'min:0', 'max:999999999'],
            'payment_method' => ['required', 'in:cash,qris,card'],
            'payment_ref' => ['nullable', 'string', 'max:60'],
            'paid_amount' => ['nullable', 'numeric', 'min:0', 'max:9999999999'],
            'note' => ['nullable', 'string', 'max:255'],
        ]);

        try {
            $order = $checkout->checkout($request->user(), $data);
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
