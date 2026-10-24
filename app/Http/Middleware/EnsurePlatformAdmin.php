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
 *  - admin yang wajib ganti password hanya boleh membuka halaman akun
 */
class EnsurePlatformAdmin
{
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
            $guard->logout();
            $request->session()->regenerate();
            $request->session()->regenerateToken();

            return redirect()->route('platform.login')
                ->withErrors(['email' => 'Akun admin ini sudah dinonaktifkan.']);
        }

        if ($admin->must_change_password && ! $request->routeIs('platform.account', 'platform.account.update', 'platform.logout')) {
            return redirect()->route('platform.account')->with('warning', 'Ganti password sementara Anda dulu sebelum melanjutkan.');
        }

        return $next($request);
    }
}
