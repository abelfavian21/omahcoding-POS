<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Business;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    /** Form login ada di landing page (tab "Masuk"). */
    public function showLogin()
    {
        return redirect(route('landing') . '#masuk');
    }

    /** Login pemilik / admin / kasir: email + password. Rate limit: lihat limiter "login". */
    public function login(Request $request)
    {
        $request->merge(['email' => Str::lower(trim((string) $request->input('email')))]);

        $cred = $request->validate([
            'email' => ['required', 'email', 'max:255'],
            'password' => ['required', 'string', 'max:200'],
        ]);

        if (! Auth::attempt($cred + ['is_active' => true], $request->boolean('remember'))) {
            AuditLog::record('login.failed', $cred['email'], ['method' => 'password']);

            return $this->backToLogin('email', 'Email atau password salah, atau akun nonaktif.', 'email')
                ->withInput($request->only('email'));
        }

        $user = Auth::user();
        if ($user->business && ! $user->business->isUsable()) {
            $reason = $user->business->unusableReason();
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return $this->backToLogin('email', $reason, 'email');
        }

        $request->session()->regenerate();
        $this->stamp($user, 'password', $request);

        return redirect()->intended($user->isAdmin() ? route('dashboard') : route('pos'));
    }

    /**
     * Login cepat KASIR: Kode Toko + PIN.
     *  - hanya role kasir (admin TIDAK bisa masuk dengan PIN)
     *  - dibatasi limiter "pin": per (kode toko + IP), per IP, dan per kode toko
     *  - waktu respons disamakan agar kode toko valid/tidak valid tidak bisa dibedakan
     */
    public function loginPin(Request $request)
    {
        $data = $request->validate([
            'store_code' => ['required', 'string', 'max:30'],
            'pin' => ['required', 'digits_between:4,6'],
        ]);

        $code = Str::upper(trim($data['store_code']));
        $business = Business::where('code', $code)->first();

        $user = null;
        if ($business && $business->isUsable()) {
            $user = User::where('business_id', $business->id)
                ->where('role', 'kasir')->where('is_active', true)->whereNotNull('pin')
                ->get()->first(fn (User $u) => Hash::check($data['pin'], $u->pin));
        } else {
            Hash::make($data['pin']);   // biaya waktu setara satu pengecekan
        }

        if (! $user) {
            AuditLog::record('login.failed', $code, ['method' => 'pin'], $business?->id);

            return $this->backToLogin('pin', 'Kode Toko atau PIN tidak dikenali.', 'pin')
                ->withInput($request->only('store_code'));
        }

        Auth::login($user);   // tanpa "ingat saya": perangkat kasir dipakai bergantian
        $request->session()->regenerate();
        $this->stamp($user, 'pin', $request);

        return redirect()->intended(route('pos'));
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('landing');
    }

    private function stamp(User $user, string $method, Request $request): void
    {
        $user->forceFill(['last_login_at' => now(), 'last_login_ip' => $request->ip()])->saveQuietly();
        AuditLog::record('login.success', $user->email, ['method' => $method]);
    }

    private function backToLogin(string $field, string $message, string $mode)
    {
        return redirect(route('landing') . '#masuk')
            ->withErrors([$field => $message], 'login')
            ->with('login_mode', $mode === 'pin' ? 'pin' : 'email');
    }
}
