<?php

namespace App\Models;

use App\Models\Concerns\BelongsToBusiness;
use Illuminate\Database\Eloquent\Model;

class ProductVariant extends Model
{
    use BelongsToBusiness;

    protected $fillable = [
        'product_id', 'group_name', 'name', 'price_extra', 'cost_extra', 'is_active',
    ];

    protected $casts = [
        'price_extra' => 'decimal:2',
        'cost_extra' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function recipes()
    {
        return $this->hasMany(ProductRecipe::class);
    }
}
