<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\IpUtils;

/**
 * /platform pura-pura tidak ada (404) untuk IP di luar PLATFORM_ALLOWED_IPS.
 * Allowlist kosong: terbuka, KECUALI platform.require_allowlist aktif (default di produksi) -> 404 untuk semua.
 */
class PlatformIpAllowlist
{
    public function handle(Request $request, Closure $next)
    {
        $allowed = config('platform.allowed_ips', []);

        if (! $allowed) {
            abort_if(config('platform.require_allowlist'), 404);

            return $next($request);
        }

        if (! IpUtils::checkIp((string) $request->ip(), $allowed)) {
            abort(404);
        }

        return $next($request);
    }
}
