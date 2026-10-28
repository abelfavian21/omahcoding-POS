<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
        Blade::directive('rupiah', fn($e) => "<?php echo 'Rp ' . number_format((float) ($e), 0, ',', '.'); ?>");

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

        /* ---- Login admin platform: lebih ketat dari login toko ---- */
        RateLimiter::for('platform-login', function (Request $r) {
            $email = Str::lower(trim((string) $r->input('email')));

            return [
                Limit::perMinute(5)->by('plogin|' . $email . '|' . $r->ip()),
                Limit::perMinute(15)->by('plogin-ip|' . $r->ip()),
                Limit::perHour(10)->by('plogin-acct|' . $email),
            ];
        });

        /* ---- 2FA admin platform: per admin+IP dan per admin (tebak kode 6 digit) ---- */
        RateLimiter::for('platform-2fa', function (Request $r) {
            $id = Auth::guard('platform')->id() ?? 'x';

            return [
                Limit::perMinute(5)->by('p2fa|' . $id . '|' . $r->ip()),
                Limit::perHour(15)->by('p2fa-acct|' . $id),
            ];
        });

        RateLimiter::for('register', function (Request $request) {
            return [
                // cegah spam cepat
                Limit::perMinute(10)->by('reg-min:' . $request->ip()),
                // batas harian yang longgar, per IP + email
                Limit::perHour(30)->by('reg-hour:' . $request->ip() . '|' . strtolower((string) $request->input('email'))),
            ];
        });
    }
}
