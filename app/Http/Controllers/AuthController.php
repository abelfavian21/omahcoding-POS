<?php

namespace App\Http\Controllers;

use App\Models\Business;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    /** Form login sekarang ada di landing page (tab "Masuk"). */
    public function showLogin()
    {
        return redirect(route('landing') . '#masuk');
    }

    /** Login pemilik / admin: email + password */
    public function login(Request $request)
    {
        $cred = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (! Auth::attempt($cred + ['is_active' => true], $request->boolean('remember'))) {
            return $this->backToLogin('email', 'Email atau password salah, atau akun nonaktif.', 'email')
                ->withInput($request->only('email'));
        }

        $request->session()->regenerate();

        return redirect()->intended(Auth::user()->isAdmin() ? route('dashboard') : route('pos'));
    }

    /** Login cepat kasir: Kode Toko + PIN 4 digit (PIN hanya unik di dalam satu bisnis) */
    public function loginPin(Request $request)
    {
        $data = $request->validate([
            'store_code' => ['required', 'string', 'max:30'],
            'pin' => ['required', 'digits:4'],
        ]);

        $business = Business::where('code', strtoupper(trim($data['store_code'])))->first();

        $user = $business
            ? User::where('business_id', $business->id)->where('is_active', true)->whereNotNull('pin')->get()
                ->first(fn (User $u) => Hash::check($data['pin'], $u->pin))
            : null;

        if (! $user) {
            return $this->backToLogin('pin', 'Kode Toko atau PIN tidak dikenali.', 'pin')
                ->withInput($request->only('store_code'));
        }

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->intended($user->isAdmin() ? route('dashboard') : route('pos'));
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('landing');
    }

    private function backToLogin(string $field, string $message, string $mode)
    {
        return redirect(route('landing') . '#masuk')
            ->withErrors([$field => $message], 'login')
            ->with('login_mode', $mode === 'pin' ? 'pin' : 'email');
    }
}
