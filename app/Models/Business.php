<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Business extends Model
{
    protected $fillable = [
        'business_category_id', 'code', 'name', 'owner_name', 'email', 'phone',
        'location', 'outlet_count', 'status', 'trial_ends_at',
    ];

    protected function casts(): array
    {
        return ['trial_ends_at' => 'datetime'];
    }

    public function category()
    {
        return $this->belongsTo(BusinessCategory::class, 'business_category_id');
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }

    /** ID Toko unik, contoh: CS-ORELY-8804 */
    public static function generateCode(string $name): string
    {
        $base = strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', Str::ascii($name)), 0, 5)) ?: 'TOKO';
        do {
            $code = 'CS-' . $base . '-' . random_int(1000, 9999);
        } while (static::where('code', $code)->exists());

        return $code;
    }
}
