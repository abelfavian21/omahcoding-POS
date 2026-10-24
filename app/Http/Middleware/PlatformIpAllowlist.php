<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\IpUtils;

/** Bila PLATFORM_ALLOWED_IPS diisi, /platform pura-pura tidak ada (404) untuk IP lain. */
class PlatformIpAllowlist
{
    public function handle(Request $request, Closure $next)
    {
        $allowed = config('platform.allowed_ips', []);

        if ($allowed && ! IpUtils::checkIp((string) $request->ip(), $allowed)) {
            abort(404);
        }

        return $next($request);
    }
}
