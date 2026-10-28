<?php

namespace App\Providers;

use App\Auth\TenantUserProvider;
use App\Models\User;
use App\Support\TenantContext;
use Illuminate\Auth\Events\Authenticated;
use Illuminate\Auth\Events\Logout;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class TenantServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // scoped: dibuang otomatis per request, per job queue, dan per worker Octane
        $this->app->scoped(TenantContext::class);
    }

    public function boot(): void
    {
        Auth::provider('tenant', fn ($app, array $config) => new TenantUserProvider($app['hash'], $config['model']));

        // Konteks HTTP = bisnis milik user toko yang sedang terautentikasi (guard platform diabaikan).
        // Authenticated juga terpicu oleh Auth::setUser() dan actingAs() di tes.
        Event::listen(Authenticated::class, function (Authenticated $e) {
            if ($e->user instanceof User) {
                app(TenantContext::class)->set($e->user->business_id ? (int) $e->user->business_id : null);
            }
        });

        Event::listen(Logout::class, function (Logout $e) {
            if ($e->user instanceof User) {
                app(TenantContext::class)->set(null);
            }
        });
    }
}
