<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class Product extends Model
{
    use \App\Models\Concerns\BelongsToBusiness;

    protected $fillable = [
        'category_id', 'sku', 'name', 'description', 'price', 'cost_price',
        'stock', 'min_stock', 'emoji', 'image', 'is_active',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'cost_price' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function movements()
    {
        return $this->hasMany(StockMovement::class)->latest();
    }

    public function scopeActive($q)
    {
        return $q->where('is_active', true);
    }

    public function scopeLowStock($q)
    {
        return $q->whereColumn('stock', '<=', 'min_stock');
    }

    public function getImageUrlAttribute(): ?string
    {
        return $this->image ? Storage::disk('public')->url($this->image) : null;
    }

    public function getStockStatusAttribute(): string
    {
        if ($this->stock <= 0) return 'habis';
        if ($this->stock <= $this->min_stock) return 'menipis';
        return 'aman';
    }

    /**
     * Ubah stok secara atomik + catat riwayatnya.
     * $delta bertanda: positif = masuk, negatif = keluar.
     * Dipakai juga oleh POS (tahap 2) dengan type "sale".
     */
    public function adjustStock(int $delta, string $type, ?string $note = null, ?int $userId = null, ?int $orderId = null): StockMovement
    {
        return DB::transaction(function () use ($delta, $type, $note, $userId, $orderId) {
            $locked = static::whereKey($this->getKey())->lockForUpdate()->firstOrFail();
            $new = $locked->stock + $delta;

            if ($new < 0) {
                throw new RuntimeException("Stok {$locked->name} tidak cukup (tersisa {$locked->stock}).");
            }

            $locked->update(['stock' => $new]);
            $this->stock = $new;

            return StockMovement::create([
                'product_id' => $locked->id,
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
