<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\StockMovement;
use Illuminate\Http\Request;
use RuntimeException;

class StockController extends Controller
{
    public function index(Request $request)
    {
        $movements = StockMovement::with(['product', 'user'])
            ->when($request->product, fn ($q, $p) => $q->where('product_id', $p))
            ->when($request->type, fn ($q, $t) => $q->where('type', $t))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('stock.index', [
            'movements' => $movements,
            'products' => Product::orderBy('name')->get(['id', 'name', 'sku', 'stock']),
            'selected' => $request->product,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'product_id' => ['required', \Illuminate\Validation\Rule::exists('products', 'id')->where('business_id', auth()->user()->business_id)],
            'type' => ['required', 'in:in,out,adjustment'],
            'qty' => ['required', 'integer', 'min:0', 'max:100000'],
            'note' => ['nullable', 'string', 'max:255'],
        ]);

        $product = Product::findOrFail($data['product_id']);

        // in/out = jumlah perubahan; adjustment (opname) = jumlah stok sebenarnya di rak
        $delta = match ($data['type']) {
            'in' => $data['qty'],
            'out' => -$data['qty'],
            'adjustment' => $data['qty'] - $product->stock,
        };

        if ($delta === 0) {
            return back()->withErrors(['qty' => 'Tidak ada perubahan stok.'])->withInput();
        }

        try {
            $product->adjustStock($delta, $data['type'], $data['note'], $request->user()->id);
        } catch (RuntimeException $e) {
            return back()->withErrors(['qty' => $e->getMessage()])->withInput();
        }

        return redirect()->route('stock.index', ['product' => $product->id])
            ->with('success', "Stok {$product->name} sekarang {$product->stock}.");
    }
}
