<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $products = Product::with('category')
            ->when($request->q, fn ($q, $s) => $q->where(fn ($w) => $w->where('name', 'like', "%$s%")->orWhere('sku', 'like', "%$s%")))
            ->when($request->category, fn ($q, $c) => $q->where('category_id', $c))
            ->when($request->stock === 'low', fn ($q) => $q->lowStock())
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        return view('products.index', [
            'products' => $products,
            'categories' => Category::orderBy('sort_order')->get(),
            'lowCount' => Product::active()->lowStock()->count(),
        ]);
    }

    public function create()
    {
        return view('products.form', ['product' => new Product(['is_active' => true, 'min_stock' => 5]), 'categories' => Category::orderBy('sort_order')->get()]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $stock = (int) ($data['stock'] ?? 0);
        unset($data['stock']);

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('products', 'public');
        }

        $product = Product::create($data + ['stock' => 0]);
        if ($stock > 0) {
            $product->adjustStock($stock, 'in', 'Stok awal', $request->user()->id);
        }

        return redirect()->route('products.index')->with('success', "Produk {$product->name} ditambahkan.");
    }

    public function edit(Product $product)
    {
        return view('products.form', ['product' => $product, 'categories' => Category::orderBy('sort_order')->get()]);
    }

    public function update(Request $request, Product $product)
    {
        $data = $this->validated($request, $product);
        unset($data['stock']); // stok hanya diubah lewat menu Stok (tercatat riwayatnya)

        if ($request->hasFile('image')) {
            if ($product->image) Storage::disk('public')->delete($product->image);
            $data['image'] = $request->file('image')->store('products', 'public');
        }

        $product->update($data);

        return redirect()->route('products.index')->with('success', 'Produk diperbarui.');
    }

    public function destroy(Product $product)
    {
        // order_items.product_id = null on delete; nama produk tetap aman di snapshot order_items
        if ($product->image) Storage::disk('public')->delete($product->image);
        $product->delete();

        return redirect()->route('products.index')->with('success', 'Produk dihapus.');
    }

    private function validated(Request $request, ?Product $product = null): array
    {
        $data = $request->validate([
            'category_id' => ['nullable', Rule::exists('categories', 'id')->where('business_id', auth()->user()->business_id)],
            'sku' => ['required', 'string', 'max:40', Rule::unique('products', 'sku')->where('business_id', auth()->user()->business_id)->ignore($product?->id)],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'price' => ['required', 'numeric', 'min:0'],
            'cost_price' => ['nullable', 'numeric', 'min:0'],
            'stock' => ['nullable', 'integer', 'min:0'],
            'min_stock' => ['required', 'integer', 'min:0'],
            'emoji' => ['nullable', 'string', 'max:8'],
            'image' => ['nullable', 'image', 'max:2048'],
        ]);
        $data['is_active'] = $request->boolean('is_active');
        $data['cost_price'] = $data['cost_price'] ?? 0;
        unset($data['image']);

        return $data;
    }
}
