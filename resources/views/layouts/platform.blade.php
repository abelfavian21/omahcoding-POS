<!DOCTYPE html>
<html lang="id">
<head>
  <title>@yield('title', 'Platform') · CashierStore Platform</title>
  <meta name="robots" content="noindex,nofollow">
  @include('partials.head')
  <style>
    /* panel platform: teks sekunder dibuat lebih gelap (kontras >= 4.5:1) dan label kolom tidak huruf kapital kecil */
    .label { color:#565B75; }
    .th { text-transform:none; letter-spacing:0; font-size:13px; color:#565B75; }
    .pill-green { color:#047857; } .pill-amber { color:#8A4B00; } .pill-red { color:#B91C1C; }
    main .text-slate-500 { color:#4B5069; } main .text-slate-400 { color:#5A5F78; }
    .dark main .text-slate-500, .dark main .text-slate-400 { color:#B7BBCB; }
    .dark .label, .dark .th { color:#B7BBCB; }
    .dark .pill-green { color:#34D399; } .dark .pill-amber { color:#FBBF24; } .dark .pill-red { color:#F87171; }
  </style>
</head>
<body class="min-h-screen">
@php
  $me = auth('platform')->user();
  $nav = [
    ['platform.dashboard', 'platform.dashboard', 'space_dashboard', 'Ringkasan'],
    ['platform.businesses.index', 'platform.businesses.*', 'storefront', 'Toko'],
    ['platform.admins.index', 'platform.admins.*', 'admin_panel_settings', 'Admin platform'],
    ['platform.audit', 'platform.audit', 'policy', 'Audit log'],
    ['platform.chat.index', 'platform.chat.*', 'forum', 'Chat'],
    ['platform.account', 'platform.account*', 'key', 'Akun saya'],
  ];
@endphp
<div class="min-h-screen md:flex">
  <aside class="hidden md:flex md:sticky md:top-0 md:h-screen w-20 xl:w-[260px] shrink-0 flex-col bg-sidebar-bg text-slate-300 border-r border-slate-800/60">
    <div class="flex items-center gap-3 px-0 xl:px-5 justify-center xl:justify-start h-20 shrink-0">
      <div class="w-10 h-10 rounded-2xl bg-lime-accent text-slate-950 grid place-items-center shrink-0"><span class="material-symbols-outlined fill-1" style="font-size:24px">shield_person</span></div>
      <div class="leading-tight hidden xl:block">
        <div class="font-extrabold text-lg text-white tracking-tight">Cashier<span class="text-lime-accent">.</span>store</div>
        <div class="text-xs font-bold text-slate-400">Platform</div>
      </div>
    </div>
    <nav class="flex-1 px-3 xl:px-4 pb-4 space-y-1.5" aria-label="Navigasi platform">
      @foreach ($nav as [$route, $match, $icon, $label])
        @php $on = request()->routeIs($match); @endphp
        <a href="{{ route($route) }}" title="{{ $label }}" @if ($on) aria-current="page" @endif
           class="flex items-center gap-3.5 h-11 px-0 xl:px-4 justify-center xl:justify-start rounded-2xl text-sm transition-all {{ $on ? 'bg-lime-accent text-slate-950 font-bold' : 'text-slate-400 font-medium hover:text-white hover:bg-white/5' }}">
          <span class="material-symbols-outlined {{ $on ? 'fill-1' : '' }}">{{ $icon }}</span>
          <span class="hidden xl:inline">{{ $label }}</span>
        </a>
      @endforeach
    </nav>
    <div class="px-3 xl:px-4 pb-2"><button type="button" onclick="toggleTheme()" title="Ganti tema" class="w-full flex items-center gap-3.5 h-11 px-0 xl:px-4 justify-center xl:justify-start rounded-2xl text-sm font-medium text-slate-400 hover:text-white hover:bg-white/5"><span class="material-symbols-outlined">contrast</span><span class="hidden xl:inline">Ganti tema</span></button></div>
    <div class="p-3 xl:p-4 border-t border-slate-800/80">
      <form method="POST" action="{{ route('platform.logout') }}" class="flex items-center gap-3 xl:px-1 justify-center xl:justify-start">
        @csrf
        <div class="w-10 h-10 rounded-full bg-slate-800 border border-slate-700 text-lime-accent grid place-items-center text-sm font-bold shrink-0" title="{{ $me->name }}">{{ $me->initials }}</div>
        <div class="flex-1 min-w-0 leading-tight hidden xl:block"><div class="text-sm font-bold truncate text-white">{{ $me->name }}</div><div class="text-xs text-slate-400 truncate">{{ $me->email }}</div></div>
        <button class="p-2 rounded-xl text-slate-400 hover:bg-white/10 hover:text-white" title="Keluar" aria-label="Keluar"><span class="material-symbols-outlined">logout</span></button>
      </form>
    </div>
  </aside>

  <main class="flex-1 min-w-0">
    {{-- mobile: bilah atas dengan hamburger; menu ada di drawer (di bawah) --}}
    <div class="md:hidden sticky top-0 z-30 bg-white/90 backdrop-blur border-b border-card-border">
      <div class="flex items-center justify-between px-2 h-14">
        <div class="flex items-center gap-1">
          <button type="button" id="pMenuBtn" class="p-2.5 rounded-xl" aria-label="Buka menu" aria-controls="pDrawer" aria-expanded="false"><span class="material-symbols-outlined">menu</span></button>
          <span class="font-extrabold tracking-tight flex items-center gap-1.5"><span class="w-6 h-6 rounded-lg bg-lime-accent text-slate-950 grid place-items-center"><span class="material-symbols-outlined fill-1" style="font-size:16px">shield_person</span></span>Platform</span>
        </div>
        <form method="POST" action="{{ route('platform.logout') }}">@csrf<button class="p-2.5 rounded-xl" aria-label="Keluar"><span class="material-symbols-outlined">logout</span></button></form>
      </div>
    </div>

    <div class="p-4 sm:p-6 xl:p-8 max-w-[1400px] mx-auto">
      @if (session('success'))
        <div class="mb-5 flex items-center gap-2.5 rounded-2xl bg-lime-subtle border border-lime-accent/60 text-lime-900 dark:bg-lime-accent/10 dark:border-lime-accent/30 dark:text-lime-accent px-4 py-3 text-sm font-semibold"><span class="material-symbols-outlined fill-1">check_circle</span> {{ session('success') }}</div>
      @endif
      @if (session('warning'))
        <div class="mb-5 flex items-center gap-2.5 rounded-2xl bg-amber-50 border border-amber-200 text-amber-800 px-4 py-3 text-sm font-semibold"><span class="material-symbols-outlined fill-1">warning</span> {{ session('warning') }}</div>
      @endif
      @if ($errors->any())
        <div class="mb-5 rounded-2xl bg-red-50 border border-red-100 text-red-700 px-4 py-3 text-sm font-semibold" role="alert"><ul class="list-disc pl-5">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
      @endif
      @yield('content')
    </div>
  </main>
</div>

{{-- mobile: drawer navigasi (hamburger) --}}
<div id="pBackdrop" class="md:hidden hidden fixed inset-0 z-40 bg-black/50" aria-hidden="true"></div>
<aside id="pDrawer" inert class="md:hidden fixed inset-y-0 left-0 z-50 w-72 max-w-[85vw] -translate-x-full transition-transform duration-200 flex flex-col bg-sidebar-bg text-slate-300 border-r border-slate-800/60" aria-label="Menu platform">
  <div class="flex items-center justify-between gap-3 px-5 h-16 shrink-0">
    <div class="flex items-center gap-3">
      <div class="w-9 h-9 rounded-2xl bg-lime-accent text-slate-950 grid place-items-center shrink-0"><span class="material-symbols-outlined fill-1" style="font-size:22px">shield_person</span></div>
      <div class="leading-tight"><div class="font-extrabold text-white tracking-tight">Cashier<span class="text-lime-accent">.</span>store</div><div class="text-xs font-bold text-slate-400">Platform</div></div>
    </div>
    <button type="button" id="pCloseBtn" class="p-2 rounded-xl text-slate-400 hover:bg-white/10 hover:text-white" aria-label="Tutup menu"><span class="material-symbols-outlined">close</span></button>
  </div>
  <nav class="flex-1 overflow-y-auto px-4 pb-4 space-y-1.5" aria-label="Navigasi platform">
    @foreach ($nav as [$route, $match, $icon, $label])
      @php $on = request()->routeIs($match); @endphp
      <a href="{{ route($route) }}" @if ($on) aria-current="page" @endif
         class="flex items-center gap-3.5 h-11 px-4 rounded-2xl text-sm transition-all {{ $on ? 'bg-lime-accent text-slate-950 font-bold' : 'text-slate-400 font-medium hover:text-white hover:bg-white/5' }}">
        <span class="material-symbols-outlined {{ $on ? 'fill-1' : '' }}">{{ $icon }}</span>
        <span>{{ $label }}</span>
      </a>
    @endforeach
  </nav>
  <div class="px-4 pb-2"><button type="button" onclick="toggleTheme()" class="w-full flex items-center gap-3.5 h-11 px-4 rounded-2xl text-sm font-medium text-slate-400 hover:text-white hover:bg-white/5"><span class="material-symbols-outlined">contrast</span><span>Ganti tema</span></button></div>
  <div class="p-4 border-t border-slate-800/80 safe-b">
    <div class="flex items-center gap-3">
      <div class="w-10 h-10 rounded-full bg-slate-800 border border-slate-700 text-lime-accent grid place-items-center text-sm font-bold shrink-0">{{ $me->initials }}</div>
      <div class="flex-1 min-w-0 leading-tight"><div class="text-sm font-bold truncate text-white">{{ $me->name }}</div><div class="text-xs text-slate-400 truncate">{{ $me->email }}</div></div>
    </div>
  </div>
</aside>
<script>
  (() => {
    const drawer = document.getElementById('pDrawer'), backdrop = document.getElementById('pBackdrop'), btn = document.getElementById('pMenuBtn');
    const set = (open) => {
      drawer.classList.toggle('-translate-x-full', !open);
      drawer.inert = !open;
      backdrop.classList.toggle('hidden', !open);
      btn.setAttribute('aria-expanded', String(open));
      document.body.style.overflow = open ? 'hidden' : '';
      if (open) document.getElementById('pCloseBtn').focus(); else if (document.activeElement && drawer.contains(document.activeElement)) btn.focus();
    };
    btn.addEventListener('click', () => set(true));
    document.getElementById('pCloseBtn').addEventListener('click', () => set(false));
    backdrop.addEventListener('click', () => set(false));
    document.addEventListener('keydown', (e) => { if (e.key === 'Escape' && !drawer.inert) set(false); });
    window.matchMedia('(min-width: 768px)').addEventListener('change', (e) => { if (e.matches && !drawer.inert) set(false); });
  })();
</script>
</body>
</html>
