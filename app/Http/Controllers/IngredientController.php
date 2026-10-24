<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Ingredient;
use Illuminate\Http\Request;

class IngredientController extends Controller
{
    public function index(Request $request)
    {
        $query = Ingredient::query();

        if ($q = trim($request->input('q', ''))) {
            $query->where('name', 'like', "%{$q}%");
        }

        if ($request->input('filter') === 'low') {
            $query->lowStock();
        }

        $ingredients = $query->orderBy('name')->paginate(20)->withQueryString();

        return view('ingredients.index', [
            'ingredients' => $ingredients,
            'q' => $q,
            'filter' => $request->input('filter'),
            'lowCount' => Ingredient::lowStock()->count(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'unit' => ['required', 'string', 'max:20'],
            'stock' => ['required', 'numeric', 'min:0'],
            'min_stock' => ['required', 'numeric', 'min:0'],
            'cost_per_unit' => ['required', 'numeric', 'min:0'],
        ]);

        $initialStock = (float) $data['stock'];
        unset($data['stock']);

        $ingredient = Ingredient::create($data + ['stock' => 0]);

        if ($initialStock > 0) {
            $ingredient->adjustStock($initialStock, 'in', 'Saldo awal', $request->user()->id);
        }

        AuditLog::record('ingredient.created', $ingredient->name, ['initial_stock' => $initialStock]);

        return redirect()->route('ingredients.index')->with('success', "Bahan baku {$ingredient->name} berhasil ditambahkan.");
    }

    public function update(Request $request, Ingredient $ingredient)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'unit' => ['required', 'string', 'max:20'],
            'min_stock' => ['required', 'numeric', 'min:0'],
            'cost_per_unit' => ['required', 'numeric', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $ingredient->update([
            'name' => $data['name'],
            'unit' => $data['unit'],
            'min_stock' => $data['min_stock'],
            'cost_per_unit' => $data['cost_per_unit'],
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()->route('ingredients.index')->with('success', "Bahan baku {$ingredient->name} diperbarui.");
    }

    public function adjust(Request $request, Ingredient $ingredient)
    {
        $data = $request->validate([
            'type' => ['required', 'in:in,out,adjustment'],
            'amount' => ['required', 'numeric', 'gt:0'],
            'note' => ['nullable', 'string', 'max:150'],
        ]);

        $amount = (float) $data['amount'];
        $delta = match ($data['type']) {
            'in' => $amount,
            'out' => -$amount,
            'adjustment' => $amount - (float) $ingredient->stock,
        };

        try {
            $ingredient->adjustStock($delta, $data['type'], $data['note'] ?? null, $request->user()->id);
        } catch (\RuntimeException $e) {
            return back()->withErrors(['amount' => $e->getMessage()]);
        }

        AuditLog::record('ingredient.adjust', $ingredient->name, [
            'type' => $data['type'], 'delta' => $delta, 'stock_after' => $ingredient->stock
        ]);

        return back()->with('success', "Stok bahan baku {$ingredient->name} berhasil diperbarui.");
    }

    public function history(Ingredient $ingredient)
    {
        $movements = $ingredient->movements()->with(['user', 'order'])->paginate(25);

        return view('ingredients.history', [
            'ingredient' => $ingredient,
            'movements' => $movements,
        ]);
    }
}
