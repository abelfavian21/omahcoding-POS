<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BusinessCategory extends Model
{
    protected $fillable = ['slug', 'name', 'icon', 'color', 'tagline', 'features', 'badge', 'preset', 'default_mode', 'is_active', 'sort_order'];

    protected function casts(): array
    {
        return ['preset' => 'array', 'is_active' => 'boolean'];
    }

    public function businesses()
    {
        return $this->hasMany(Business::class);
    }
}
