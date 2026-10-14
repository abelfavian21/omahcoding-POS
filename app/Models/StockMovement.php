<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockMovement extends Model
{
    use \App\Models\Concerns\BelongsToBusiness;

    protected $fillable = ['product_id', 'user_id', 'type', 'qty', 'stock_after', 'note', 'order_id'];

    public const TYPES = [
        'in' => 'Barang masuk',
        'out' => 'Barang keluar',
        'adjustment' => 'Stok opname',
        'sale' => 'Penjualan',
        'void' => 'Pembatalan',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
