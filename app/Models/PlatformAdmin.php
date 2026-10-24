<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

/**
 * Admin platform CashierStore (pemilik / partner). BUKAN user toko: tidak punya business_id dan
 * tidak memakai trait BelongsToBusiness. Login lewat guard "platform".
 */
class PlatformAdmin extends Authenticatable
{
    protected $fillable = ['name', 'email', 'password', 'is_active', 'must_change_password'];

    // last_login_* sengaja TIDAK fillable (diisi sistem)

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'is_active' => 'boolean',
            'must_change_password' => 'boolean',
            'last_login_at' => 'datetime',
        ];
    }

    public function getInitialsAttribute(): string
    {
        return collect(explode(' ', trim($this->name)))
            ->take(2)->map(fn ($w) => mb_strtoupper(mb_substr($w, 0, 1)))->implode('');
    }
}
