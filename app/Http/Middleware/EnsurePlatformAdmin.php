<?php

namespace App\Http\Middleware;

use App\Models\PlatformAuditLog;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Dicek di SETIAP request ke /platform:
 *  - harus login di guard "platform" (bukan guard toko)
 *  - admin yang dinonaktifkan langsung terlempar, walau sesinya masih hidup
 *  - sesi tidak aktif lebih dari platform.idle_timeout_minutes -> keluar
 *  - admin dengan 2FA hanya boleh membuka halaman tantangan sampai kodenya benar
 *  - admin yang wajib ganti password hanya boleh membuka halaman akun
 *  - bila platform.require_2fa aktif, admin tanpa 2FA hanya boleh membuka halaman pendaftaran 2FA
 */
class EnsurePlatformAdmin
{
    /** Kunci sesi: berisi id admin yang sudah lolos 2FA di sesi ini. */
    public const TWO_FACTOR_OK = 'platform_2fa_ok';
    public const LAST_ACTIVITY = 'platform_last_activity';

    public function handle(Request $request, Closure $next)
    {
        $guard = Auth::guard('platform');
        $admin = $guard->user();

        if (! $admin) {
            return $request->expectsJson()
                ? response()->json(['message' => 'Unauthenticated.'], 401)
                : redirect()->route('platform.login');
        }

        if (! $admin->is_active) {
            PlatformAuditLog::record('session.revoked', $admin->email, ['reason' => 'inactive'], null, $admin->id);

            return $this->kick($request, 'Akun admin ini sudah dinonaktifkan.');
        }

        $idle = (int) config('platform.idle_timeout_minutes', 30);
        $last = (int) $request->session()->get(self::LAST_ACTIVITY, 0);
        if ($idle > 0 && $last > 0 && (now()->timestamp - $last) > $idle * 60) {
            PlatformAuditLog::record('session.expired', $admin->email, ['reason' => 'idle'], null, $admin->id);

            return $this->kick($request, 'Sesi berakhir karena tidak aktif. Silakan masuk lagi.');
        }
        $request->session()->put(self::LAST_ACTIVITY, now()->timestamp);

        // 2FA: bukti kode harus ada SEBELUM halaman apa pun (termasuk ganti password) terbuka.
        $twoFactorOk = $request->session()->get(self::TWO_FACTOR_OK) === $admin->id;
        if ($admin->hasTwoFactor() && ! $twoFactorOk
            && ! $request->routeIs('platform.2fa.challenge', 'platform.2fa.verify', 'platform.logout')) {
            return redirect()->route('platform.2fa.challenge');
        }

        if ($admin->must_change_password && ! $request->routeIs('platform.account', 'platform.account.update', 'platform.logout')) {
            return redirect()->route('platform.account')->with('warning', 'Ganti password sementara Anda dulu sebelum melanjutkan.');
        }

        if (! $admin->hasTwoFactor() && config('platform.require_2fa')
            && ! $request->routeIs('platform.2fa.setup', 'platform.2fa.confirm', 'platform.account', 'platform.account.update', 'platform.logout')) {
            return redirect()->route('platform.2fa.setup')->with('warning', 'Aktifkan 2FA (aplikasi authenticator) untuk melanjutkan.');
        }

        return $next($request);
    }

    private function kick(Request $request, string $message)
    {
        Auth::guard('platform')->logout();
        $request->session()->forget([self::TWO_FACTOR_OK, self::LAST_ACTIVITY]);
        $request->session()->regenerate();
        $request->session()->regenerateToken();

        return redirect()->route('platform.login')->withErrors(['email' => $message]);
    }
}
