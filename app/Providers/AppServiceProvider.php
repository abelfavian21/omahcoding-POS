<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        // Pemakaian di Blade: @rupiah($angka)  =>  Rp 25.000
        Blade::directive('rupiah', fn ($e) => "<?php echo 'Rp ' . number_format((float) ($e), 0, ',', '.'); ?>");

        if ($this->app->isProduction()) {
            URL::forceHttps();
        }

        /* ---- Rate limiting login: per akun+IP, per IP, dan per akun (anti credential-stuffing) ---- */
        RateLimiter::for('login', function (Request $r) {
            $email = Str::lower(trim((string) $r->input('email')));

            return [
                Limit::perMinute(5)->by('login|' . $email . '|' . $r->ip()),
                Limit::perMinute(30)->by('login-ip|' . $r->ip()),
                Limit::perHour(20)->by('login-acct|' . $email),
            ];
        });

        /* ---- PIN: ruang tebakan kecil, jadi dikunci juga per KODE TOKO (berapa pun IP penyerangnya) ---- */
        RateLimiter::for('pin', function (Request $r) {
            $code = Str::upper(trim((string) $r->input('store_code')));

            return [
                Limit::perMinute(5)->by('pin|' . $code . '|' . $r->ip()),
                Limit::perMinute(30)->by('pin-ip|' . $r->ip()),
                Limit::perHour(30)->by('pin-store|' . $code),
            ];
        });

        RateLimiter::for('register', fn (Request $r) => [
            Limit::perMinute(3)->by('reg|' . $r->ip()),
            Limit::perDay(10)->by('reg-day|' . $r->ip()),
        ]);
    }
}
