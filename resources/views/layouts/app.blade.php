<!DOCTYPE html>
<html lang="id">
<head>
  <title>@yield('title', 'Dashboard') · CashierStore</title>
  @include('partials.head')
</head>
<body class="min-h-screen">
@php
  $u = auth()->user();
  // 'group' hanya dipakai untuk tampilan sidebar; key lain tetap sama seperti sebelumnya (dipakai juga oleh partials.voice)
  $nav = [
    ['route' => 'dashboard', 'match' => 'dashboard', 'icon' => 'grid_view', 'label' => 'Dashboard', 'admin' => true, 'group' => 'Overview'],
    ['route' => 'pos', 'match' => 'pos', 'icon' => 'receipt_long', 'label' => 'Order Line', 'admin' => false, 'group' => 'Overview'],
    ['route' => 'reports.index', 'match' => 'reports.*', 'icon' => 'query_stats', 'label' => 'Laporan Penjualan', 'admin' => true, 'group' => 'Overview'],
    ['route' => 'shifts.index', 'match' => 'shifts.*', 'icon' => 'payments', 'label' => 'Shift Kasir', 'admin' => false, 'group' => 'Overview'],
    ['route' => 'products.index', 'match' => 'products.*', 'icon' => 'lunch_dining', 'label' => 'Produk', 'admin' => false, 'group' => 'Management'],
    ['route' => 'stock.index', 'match' => 'stock.*', 'icon' => 'warehouse', 'label' => 'Stok Barang', 'admin' => true, 'group' => 'Management'],
    ['route' => 'customers.index', 'match' => 'customers.*', 'icon' => 'group', 'label' => 'Pelanggan', 'admin' => false, 'group' => 'Management'],
    ['route' => 'staff.index', 'match' => 'staff.*', 'icon' => 'badge', 'label' => 'Staff', 'admin' => true, 'group' => 'Management'],
    ['route' => 'settings.edit', 'match' => 'settings.*', 'icon' => 'settings', 'label' => 'Pengaturan', 'admin' => true, 'group' => 'Management'],
    ['route' => 'audit.index', 'match' => 'audit.*', 'icon' => 'policy', 'label' => 'Audit Log', 'admin' => true, 'group' => 'Management'],
    ['route' => 'office', 'match' => 'office', 'icon' => 'apartment', 'label' => 'Kantor Virtual', 'admin' => false, 'group' => 'Management'],
  ];
  $shiftOn = (bool) ($u->business?->requiresShift() ?? true);
  $visible = collect($nav)->reject(fn ($n) => ($n['admin'] && ! $u->isAdmin()) || ($n['route'] === 'shifts.index' && ! $shiftOn));
  $groups = $visible->groupBy('group');

  // Bottom nav mobile: 4 menu utama + tombol "Menu". Disembunyikan di kasir karena halaman itu punya bar keranjang sendiri.
  $bottomShort = ['dashboard' => 'Dashboard', 'pos' => 'Order', 'products.index' => 'Produk', 'customers.index' => 'Pelanggan', 'shifts.index' => 'Shift'];
  $bottom = $visible->filter(fn ($n) => isset($bottomShort[$n['route']]))
      ->sortBy(fn ($n) => array_search($n['route'], array_keys($bottomShort)))->take(4)->values();
  $showBottomNav = ! request()->routeIs('pos');
@endphp

<div class="min-h-screen md:flex">
  {{-- ========== SIDEBAR: drawer (mobile) · icon rail (tablet) · penuh (desktop) ========== --}}
  <aside id="sidebar" class="fixed inset-y-0 left-0 z-50 w-[280px] -translate-x-full md:translate-x-0 md:sticky md:top-0 md:h-screen md:w-20 xl:w-[260px] flex flex-col shrink-0 bg-sidebar-bg text-slate-300 border-r border-slate-800/60 shadow-2xl md:shadow-none transition-transform duration-300 overflow-y-auto no-scrollbar">
    <div class="flex items-center gap-3 px-5 md:px-0 md:justify-center xl:justify-start xl:px-5 h-20 shrink-0">
      <div class="w-10 h-10 rounded-2xl bg-lime-accent text-slate-950 grid place-items-center shadow-lime shrink-0"><span class="material-symbols-outlined fill-1" style="font-size:24px">storefront</span></div>
      <div class="leading-tight min-w-0 md:hidden xl:block">
        <div class="font-extrabold text-lg text-white tracking-tight">Cashier<span class="text-lime-accent">.</span>store</div>
        <div class="text-[10px] font-bold text-slate-400 tracking-wider uppercase truncate">{{ auth()->user()?->business?->name ?? config('pos.store_name') }} POS</div>
      </div>
      <button type="button" id="closeMenu" class="ml-auto p-2 -mr-2 text-slate-400 hover:text-white md:hidden" aria-label="Tutup menu"><span class="material-symbols-outlined">close</span></button>
    </div>

    <nav class="flex-1 px-3 xl:px-4 pb-4 space-y-5">
      @foreach ($groups as $gname => $items)
        @php $hasActive = collect($items)->contains(fn ($n) => request()->routeIs($n['match'])); @endphp
        <div class="nav-group" data-group="{{ \Illuminate\Support\Str::slug($gname) }}" data-has-active="{{ $hasActive ? 1 : 0 }}">
          <button type="button" class="nav-group-btn w-full px-3 mb-2 items-center justify-between text-[11px] font-bold text-slate-500 hover:text-slate-300 tracking-wider uppercase hidden xl:flex max-md:flex" aria-expanded="true">
            <span>{{ $gname }}</span>
            <span class="material-symbols-outlined nav-chev text-base transition-transform">expand_more</span>
          </button>
          @if (! $loop->first)<div class="hidden md:block xl:hidden h-px bg-white/10 mx-3 mb-3"></div>@endif
          <div class="nav-group-body space-y-1.5">
            @foreach ($items as $n)
              @php $on = request()->routeIs($n['match']); @endphp
              <a href="{{ route($n['route']) }}" title="{{ $n['label'] }}" @if ($on) aria-current="page" @endif
                 class="flex items-center gap-3.5 h-11 px-4 md:px-0 md:justify-center xl:justify-start xl:px-4 rounded-2xl text-sm transition-all
                        {{ $on ? 'bg-lime-accent text-slate-950 font-bold shadow-md' : 'text-slate-400 font-medium hover:text-white hover:bg-white/5' }}">
                <span class="material-symbols-outlined {{ $on ? 'fill-1' : '' }}">{{ $n['icon'] }}</span>
                <span class="truncate md:hidden xl:inline">{{ $n['label'] }}</span>
              </a>
            @endforeach
          </div>
        </div>
      @endforeach
    </nav>

    <div class="px-3 xl:px-4 pb-2 shrink-0">
      <button type="button" onclick="toggleTheme()" title="Ganti tema"
              class="w-full flex items-center gap-3.5 h-11 px-4 md:px-0 md:justify-center xl:justify-start xl:px-4 rounded-2xl text-sm font-medium text-slate-400 hover:text-white hover:bg-white/5 transition">
        <span class="material-symbols-outlined dark:hidden">dark_mode</span>
        <span class="material-symbols-outlined hidden dark:inline">light_mode</span>
        <span class="md:hidden xl:inline dark:hidden">Mode Gelap</span>
        <span class="hidden md:hidden dark:inline dark:md:hidden dark:xl:inline">Mode Terang</span>
      </button>
    </div>
    <div class="p-3 xl:p-4 border-t border-slate-800/80 shrink-0">
      <form method="POST" action="{{ route('logout') }}" class="flex items-center gap-3 px-1 md:flex-col md:px-0 xl:flex-row xl:px-1">
        @csrf
        <div class="w-10 h-10 rounded-full bg-slate-800 border border-slate-700 text-lime-accent grid place-items-center text-sm font-bold shrink-0" title="{{ $u->name }}">{{ $u->initials }}</div>
        <div class="flex-1 min-w-0 leading-tight md:hidden xl:block">
          <div class="text-sm font-bold truncate text-white">{{ $u->name }}</div>
          <div class="text-xs text-slate-400 capitalize">{{ $u->role }}</div>
        </div>
        <button class="p-2 rounded-xl text-slate-400 hover:bg-white/10 hover:text-white" title="Keluar" aria-label="Keluar"><span class="material-symbols-outlined">logout</span></button>
      </form>
    </div>
  </aside>
  <div id="backdrop" class="hidden fixed inset-0 z-40 bg-slate-950/60 backdrop-blur-sm md:hidden"></div>

  {{-- ========== KONTEN ========== --}}
  <main class="flex-1 min-w-0 {{ $showBottomNav ? 'pb-24' : 'pb-6' }} md:pb-0 {{ request()->routeIs('pos') ? 'overflow-x-clip' : '' }}">
    <div class="md:hidden sticky top-0 z-30 flex items-center justify-between px-4 h-14 bg-white/90 backdrop-blur border-b border-card-border">
      <button id="menuBtn" class="p-2 -ml-2 rounded-xl" aria-label="Buka menu"><span class="material-symbols-outlined">menu</span></button>
      <span class="font-extrabold tracking-tight flex items-center gap-1.5">
        <span class="w-6 h-6 rounded-lg bg-lime-accent text-slate-950 grid place-items-center"><span class="material-symbols-outlined fill-1" style="font-size:16px">storefront</span></span>
        Cashier<span class="text-brand-600">.</span>store
      </span>
      <button type="button" onclick="toggleTheme()" class="p-2 -mr-2 rounded-xl" title="Ganti tema" aria-label="Ganti tema">
        <span class="material-symbols-outlined dark:hidden">dark_mode</span>
        <span class="material-symbols-outlined hidden dark:inline">light_mode</span>
      </button>
    </div>

    <div class="p-4 sm:p-6 xl:p-8 max-w-[1500px] mx-auto">
      @if (session('success'))
        <div class="mb-5 flex items-center gap-2.5 rounded-2xl bg-lime-subtle border border-lime-accent/60 text-lime-900 dark:bg-lime-accent/10 dark:border-lime-accent/30 dark:text-lime-accent px-4 py-3 text-sm font-semibold">
          <span class="material-symbols-outlined fill-1">check_circle</span> {{ session('success') }}
        </div>
      @endif
      @yield('content')
    </div>
  </main>
</div>

{{-- ========== BOTTOM NAV (mobile) ========== --}}
@if ($showBottomNav)
  <nav class="md:hidden fixed bottom-0 inset-x-0 z-40 bg-white/90 backdrop-blur border-t border-card-border safe-b" aria-label="Navigasi utama">
    <div class="grid grid-cols-5 gap-1 px-2 pt-2 pb-2">
      @foreach ($bottom as $n)
        @php $on = request()->routeIs($n['match']); @endphp
        <a href="{{ route($n['route']) }}" @if ($on) aria-current="page" @endif class="flex flex-col items-center gap-1 text-[10px] font-bold {{ $on ? 'text-slate-900' : 'text-slate-400' }}">
          <span class="h-8 w-14 rounded-full grid place-items-center transition {{ $on ? 'bg-lime-accent text-slate-950' : '' }}"><span class="material-symbols-outlined {{ $on ? 'fill-1' : '' }}">{{ $n['icon'] }}</span></span>
          {{ $bottomShort[$n['route']] }}
        </a>
      @endforeach
      <button type="button" id="menuBtn2" class="flex flex-col items-center gap-1 text-[10px] font-bold text-slate-400">
        <span class="h-8 w-14 rounded-full grid place-items-center"><span class="material-symbols-outlined">apps</span></span>
        Menu
      </button>
    </div>
  </nav>
@endif

<style>
  .nav-group.is-collapsed .nav-group-body { display: none; }
  .nav-group.is-collapsed .nav-chev { transform: rotate(-90deg); }
  /* icon rail (tablet): semua menu selalu terlihat */
  @media (min-width: 768px) and (max-width: 1279px) { .nav-group.is-collapsed .nav-group-body { display: block; } }
</style>
<script>
  (() => {
    const KEY = 'sidebar-collapsed';
    let saved = [];
    try { saved = JSON.parse(localStorage.getItem(KEY) || '[]'); } catch (e) {}
    const save = () => { try { localStorage.setItem(KEY, JSON.stringify(saved)); } catch (e) {} };
    document.querySelectorAll('.nav-group').forEach((g) => {
      const id = g.dataset.group, btn = g.querySelector('.nav-group-btn');
      const set = (collapsed) => { g.classList.toggle('is-collapsed', collapsed); btn.setAttribute('aria-expanded', String(!collapsed)); };
      // grup yang berisi halaman aktif selalu terbuka
      set(saved.includes(id) && g.dataset.hasActive !== '1');
      btn.addEventListener('click', () => {
        const collapsed = !g.classList.contains('is-collapsed');
        set(collapsed);
        saved = saved.filter((x) => x !== id);
        if (collapsed) saved.push(id);
        save();
      });
    });
  })();
</script>
<script>
  const sb = document.getElementById('sidebar'), bd = document.getElementById('backdrop');
  const isOpen = () => !sb.classList.contains('-translate-x-full');
  const setMenu = (open) => {
    sb.classList.toggle('-translate-x-full', !open);
    bd.classList.toggle('hidden', !open);
    document.body.style.overflow = open ? 'hidden' : '';
  };
  ['menuBtn', 'menuBtn2'].forEach((id) => document.getElementById(id)?.addEventListener('click', () => setMenu(!isOpen())));
  document.getElementById('closeMenu')?.addEventListener('click', () => setMenu(false));
  bd.addEventListener('click', () => setMenu(false));
  document.addEventListener('keydown', (e) => { if (e.key === 'Escape' && isOpen()) setMenu(false); });
</script>
@include('partials.voice')
@stack('scripts')
</body>
</html>
