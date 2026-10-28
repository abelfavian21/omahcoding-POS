<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Http\Middleware\EnsurePlatformAdmin;
use App\Models\PlatformAdmin;
use App\Models\PlatformAuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('platform.login');
    }

    /** Email + password. Tanpa "ingat saya". Rate limit: limiter "platform-login". Bila 2FA aktif: lanjut ke tantangan. */
    public function login(Request $request)
    {
        $request->merge(['email' => Str::lower(trim((string) $request->input('email')))]);

        $cred = $request->validate([
            'email' => ['required', 'email', 'max:255'],
            'password' => ['required', 'string', 'max:200'],
        ]);

        // biaya waktu disamakan: email terdaftar / tidak terdaftar tidak bisa dibedakan dari lamanya respons
        if (! PlatformAdmin::where('email', $cred['email'])->exists()) {
            Hash::make($cred['password']);
        }

        $guard = Auth::guard('platform');

        if (! $guard->attempt($cred + ['is_active' => true])) {
            PlatformAuditLog::record('login.failed', $cred['email']);

            return back()->withErrors(['email' => 'Email atau password salah, atau akun nonaktif.'])->onlyInput('email');
        }

        $request->session()->regenerate();
        // bukti 2FA dari sesi sebelumnya tidak boleh ikut terbawa ke login baru
        $request->session()->forget(EnsurePlatformAdmin::TWO_FACTOR_OK);
        $request->session()->put(EnsurePlatformAdmin::LAST_ACTIVITY, now()->timestamp);

        $admin = $guard->user();
        $admin->forceFill(['last_login_at' => now(), 'last_login_ip' => $request->ip()])->saveQuietly();
        PlatformAuditLog::record('login.success', $admin->email, ['2fa_pending' => $admin->hasTwoFactor()], null, $admin->id);

        if ($admin->hasTwoFactor()) {
            return redirect()->route('platform.2fa.challenge');
        }

        // sengaja BUKAN redirect()->intended(): kunci "intended" dipakai bersama login toko
        return redirect()->route('platform.dashboard');
    }

    public function logout(Request $request)
    {
        $admin = Auth::guard('platform')->user();
        PlatformAuditLog::record('logout', $admin?->email, [], null, $admin?->id);

        Auth::guard('platform')->logout();
        $request->session()->forget([EnsurePlatformAdmin::TWO_FACTOR_OK, EnsurePlatformAdmin::LAST_ACTIVITY, 'platform_2fa_pending_secret']);

        // sesi yang sama bisa dipakai login toko di browser ini: hanya dihapus total bila tidak ada user toko
        Auth::guard('web')->check() ? $request->session()->regenerate() : $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('platform.login');
    }
}
