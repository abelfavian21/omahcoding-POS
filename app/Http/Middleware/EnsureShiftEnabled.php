<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/** Halaman Shift Kasir disembunyikan (diarahkan ke Order Line) saat admin menonaktifkan fitur shift. */
class EnsureShiftEnabled
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! ($request->user()?->business?->requiresShift() ?? true)) {
            return redirect()->route('pos');
        }

        return $next($request);
    }
}
