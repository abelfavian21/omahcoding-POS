<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Dicek di SETIAP request (bukan cuma saat login), sehingga:
 *  - kasir yang dinonaktifkan langsung terlempar, walau sesi / "ingat saya" masih hidup
 *  - bisnis yang di-suspend (atau trial habis, bila diaktifkan) langsung terkunci
 *  - akun yatim tanpa bisnis tidak bisa masuk
 */
class EnsureAccountActive
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        if ($user) {
            $business = $user->business;

            $reason = match (true) {
                ! $user->is_active => 'Akun Anda sudah dinonaktifkan. Hubungi pemilik toko.',
                ! $user->business_id || ! $business => 'Akun tidak terhubung ke bisnis mana pun.',
                ! $business->isUsable() => $business->unusableReason(),
                default => null,
            };

            if ($reason) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                if ($request->expectsJson()) {
                    return response()->json(['message' => $reason], 401);
                }

                return redirect(route('landing') . '#masuk')
                    ->withErrors(['email' => $reason], 'login')
                    ->with('login_mode', 'email');
            }
        }

        return $next($request);
    }
}
