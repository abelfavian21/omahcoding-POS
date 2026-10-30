<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SecurityHeaders
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);
        $h = $response->headers;

        // Struk thermal dibuka di iframe milik POS (satu origin) untuk dicetak; semua halaman lain tetap tidak boleh di-frame.
        $framable = $request->routeIs('orders.receipt.thermal');
        $h->set('X-Frame-Options', $framable ? 'SAMEORIGIN' : 'DENY');
        $h->set('X-Content-Type-Options', 'nosniff');
        $h->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $h->set('Cross-Origin-Opener-Policy', 'same-origin');
        // mikrofon dipakai fitur perintah suara kasir; sisanya dimatikan
        $h->set('Permissions-Policy', 'camera=(), geolocation=(), payment=(), microphone=(self)');

        if ($request->isSecure()) {
            $h->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        }

        $mode = config('pos.csp_mode', 'report');
        if ($mode !== 'off') {
            $csp = implode('; ', [
                "default-src 'self'",
                // 'unsafe-inline' diperlukan karena view memakai <script> inline & Tailwind CDN.
                // Target jangka panjang: build Tailwind lewat Vite, lalu hapus CDN + 'unsafe-inline'.
                // 'inline-speculation-rules': izinkan <script type="speculationrules"> (prefetch menu sidebar).
                "script-src 'self' 'unsafe-inline' 'inline-speculation-rules' https://cdn.tailwindcss.com",
                "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com",
                "font-src 'self' https://fonts.gstatic.com",
                "img-src 'self' data: blob:",
                "connect-src 'self'",
                "object-src 'none'",
                "base-uri 'self'",
                "form-action 'self'",
                $framable ? "frame-ancestors 'self'" : "frame-ancestors 'none'",
            ]);
            $h->set($mode === 'enforce' ? 'Content-Security-Policy' : 'Content-Security-Policy-Report-Only', $csp);
        }

        return $response;
    }
}
