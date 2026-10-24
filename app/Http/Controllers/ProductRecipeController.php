<?php

namespace App\Http\Controllers;

use App\Models\Ingredient;
use App\Models\Product;
use App\Models\ProductRecipe;
use App\Models\ProductVariant;
use Illuminate\Http\Request;

class ProductRecipeController extends Controller
{
    public function show(Product $product)
    {
        $product->load(['allVariants', 'recipes.ingredient', 'recipes.variant']);
        $ingredients = Ingredient::active()->orderBy('name')->get();

        return view('products.recipe', [
            'product' => $product,
            'ingredients' => $ingredients,
        ]);
    }

    public function storeVariant(Request $request, Product $product)
    {
        $data = $request->validate([
            'group_name' => ['required', 'string', 'max:50'],
            'name' => ['required', 'string', 'max:100'],
            'price_extra' => ['required', 'numeric', 'min:0'],
            'cost_extra' => ['nullable', 'numeric', 'min:0'],
        ]);

        $product->allVariants()->create([
            'business_id' => $product->business_id,
            'group_name' => $data['group_name'],
            'name' => $data['name'],
            'price_extra' => $data['price_extra'],
            'cost_extra' => $data['cost_extra'] ?? 0,
            'is_active' => true,
        ]);

        return back()->with('success', "Varian {$data['name']} berhasil ditambahkan.");
    }

    public function destroyVariant(ProductVariant $variant)
    {
        $variant->delete();
        return back()->with('success', 'Varian berhasil dihapus.');
    }

    public function storeRecipe(Request $request, Product $product)
    {
        $data = $request->validate([
            'ingredient_id' => ['required', 'exists:ingredients,id'],
            'amount' => ['required', 'numeric', 'gt:0'],
            'product_variant_id' => ['nullable', 'exists:product_variants,id'],
        ]);

        $product->recipes()->create([
            'business_id' => $product->business_id,
            'ingredient_id' => $data['ingredient_id'],
            'amount' => $data['amount'],
            'product_variant_id' => $data['product_variant_id'] ?: null,
        ]);

        // Hitung estimasi HPP dari resep jika diinginkan
        $this->updateProductCostFromRecipe($product);

        return back()->with('success', 'Bahan resep berhasil ditambahkan.');
    }

    public function destroyRecipe(ProductRecipe $recipe)
    {
        $product = $recipe->product;
        $recipe->delete();

        if ($product) {
            $this->updateProductCostFromRecipe($product);
        }

        return back()->with('success', 'Bahan resep dihapus.');
    }

    private function updateProductCostFromRecipe(Product $product): void
    {
        $baseRecipes = $product->recipes()->whereNull('product_variant_id')->with('ingredient')->get();
        if ($baseRecipes->isNotEmpty()) {
            $calcCost = 0;
            foreach ($baseRecipes as $r) {
                if ($r->ingredient) {
                    $calcCost += (float) $r->ingredient->cost_per_unit * (float) $r->amount;
                }
            }
            if ($calcCost > 0) {
                $product->update(['cost_price' => round($calcCost, 2)]);
            }
        }
    }
}
