<?php

namespace App\Models;

use App\Models\Concerns\BelongsToBusiness;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class Ingredient extends Model
{
    use BelongsToBusiness;

    protected $fillable = [
        'name', 'unit', 'stock', 'min_stock', 'cost_per_unit', 'is_active',
    ];

    protected $casts = [
        'stock' => 'decimal:2',
        'min_stock' => 'decimal:2',
        'cost_per_unit' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function movements()
    {
        return $this->hasMany(IngredientMovement::class)->latest();
    }

    public function recipes()
    {
        return $this->hasMany(ProductRecipe::class);
    }

    public function scopeActive($q)
    {
        return $q->where('is_active', true);
    }

    public function scopeLowStock($q)
    {
        return $q->whereColumn('stock', '<=', 'min_stock');
    }

    public function getStockStatusAttribute(): string
    {
        if ($this->stock <= 0) return 'habis';
        if ($this->stock <= $this->min_stock) return 'menipis';
        return 'aman';
    }

    /**
     * Ubah stok bahan baku secara atomik + catat riwayat.
     * $delta bertanda: positif = masuk (restock), negatif = keluar (pemakaian transaksi/waste).
     */
    public function adjustStock(float $delta, string $type, ?string $note = null, ?int $userId = null, ?int $orderId = null): IngredientMovement
    {
        return DB::transaction(function () use ($delta, $type, $note, $userId, $orderId) {
            $locked = static::whereKey($this->getKey())->lockForUpdate()->firstOrFail();
            $new = (float) $locked->stock + $delta;

            if ($new < -0.0001) {
                throw new RuntimeException("Stok bahan baku {$locked->name} tidak cukup (tersisa {$locked->stock} {$locked->unit}).");
            }

            // Batasi presisi decimal
            $new = round($new, 2);
            $locked->update(['stock' => $new]);
            $this->stock = $new;

            return IngredientMovement::create([
                'business_id' => $locked->business_id,
                'ingredient_id' => $locked->id,
                'user_id' => $userId,
                'type' => $type,
                'qty' => $delta,
                'stock_after' => $new,
                'note' => $note,
                'order_id' => $orderId,
            ]);
        });
    }
}
