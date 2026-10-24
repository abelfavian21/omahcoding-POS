<?php

namespace App\Models;

use App\Models\Concerns\BelongsToBusiness;
use Illuminate\Database\Eloquent\Model;

class ProductRecipe extends Model
{
    use BelongsToBusiness;

    protected $fillable = [
        'product_id', 'product_variant_id', 'ingredient_id', 'amount',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function variant()
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }

    public function ingredient()
    {
        return $this->belongsTo(Ingredient::class);
    }
}
