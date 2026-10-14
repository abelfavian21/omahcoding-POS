<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use \App\Models\Concerns\BelongsToBusiness;

    protected $fillable = ['name', 'emoji', 'sort_order'];

    public function products()
    {
        return $this->hasMany(Product::class);
    }
}
