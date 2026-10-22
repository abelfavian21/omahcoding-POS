<?php

namespace App\Models;

use App\Models\Concerns\BelongsToBusiness;
use Illuminate\Database\Eloquent\Model;

class IngredientMovement extends Model
{
    use BelongsToBusiness;

    protected $fillable = [
        'ingredient_id', 'user_id', 'type', 'qty', 'stock_after', 'note', 'order_id',
    ];

    protected $casts = [
        'qty' => 'decimal:2',
        'stock_after' => 'decimal:2',
    ];

    public function ingredient()
    {
        return $this->belongsTo(Ingredient::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
