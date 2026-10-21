@props(['v' => null, 'fallback' => 'restaurant'])
<span {{ $attributes->class(['material-symbols-outlined']) }} aria-hidden="true">{{ \App\Support\Icons::name($v, $fallback) }}</span>
