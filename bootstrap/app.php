<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // user yang sudah login & membuka halaman tamu -> admin ke dashboard, kasir ke POS
        $middleware->redirectUsersTo(fn () => auth()->user()?->isAdmin() ? route('dashboard') : route('pos'));

        // ganti password / nonaktifkan akun -> sesi di perangkat lain ikut tidak berlaku
        $middleware->authenticateSessions();

        // Webhook Midtrans dipanggil server-ke-server (tanpa cookie/CSRF); keamanannya = verifikasi signature_key.
        $middleware->validateCsrfTokens(except: ['payments/midtrans/notify']);

        // header keamanan di semua respons web
        $middleware->web(append: [\App\Http\Middleware\SecurityHeaders::class]);

        $middleware->alias([
            'role' => \App\Http\Middleware\RoleMiddleware::class,
            'account' => \App\Http\Middleware\EnsureAccountActive::class,
        ]);

        // Di belakang Nginx/Cloudflare/load balancer: percayai proxy supaya IP asli (rate limit)
        // dan skema https (cookie secure, HSTS) terbaca benar. Lihat catatan di SECURITY_REVIEW.md.
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
