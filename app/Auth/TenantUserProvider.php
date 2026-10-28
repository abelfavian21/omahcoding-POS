<?php

namespace App\Auth;

use App\Support\TenantContext;
use Illuminate\Auth\EloquentUserProvider;

/**
 * Mengambil user dari sesi / kredensial terjadi SEBELUM ada konteks bisnis (user-lah yang menentukan bisnisnya),
 * jadi tiga pencarian ini sengaja berjalan tanpa filter tenant. Hanya ini yang boleh.
 */
class TenantUserProvider extends EloquentUserProvider
{
    public function retrieveById($identifier)
    {
        return app(TenantContext::class)->withoutTenant(fn () => parent::retrieveById($identifier));
    }

    public function retrieveByToken($identifier, $token)
    {
        return app(TenantContext::class)->withoutTenant(fn () => parent::retrieveByToken($identifier, $token));
    }

    public function retrieveByCredentials(array $credentials)
    {
        return app(TenantContext::class)->withoutTenant(fn () => parent::retrieveByCredentials($credentials));
    }
}
