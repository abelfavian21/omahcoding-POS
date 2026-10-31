<?php

namespace App\Http\Middleware;

use App\Models\Business;
use Closure;
use Illuminate\Http\Request;

/** Pakai bahasa yang dipilih bisnis di Pengaturan; tamu / platform admin memakai default aplikasi (id). */
class SetLocale
{
    public function handle(Request $request, Closure $next)
    {
        $lang = $request->user()?->business?->language;
        app()->setLocale(in_array($lang, Business::LANGUAGES, true) ? $lang : config('app.locale'));

        return $next($request);
    }
}
