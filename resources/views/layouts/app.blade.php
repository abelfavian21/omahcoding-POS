<!DOCTYPE html>
<html lang="id">
<head>
  <title>@yield('title', 'Dashboard') · CashierStore</title>
  @include('partials.head')
</head>
<body>
@php
  $u = auth()->user();
  $nav = [
    ['route' => 'dashboard', 'match' => 'dashboard', 'icon' => 'dashboard', 'label' => 'Dashboard', 'admin' => false],
    ['route' => 'pos', 'match' => 'pos', 'icon' => 'point_of_sale', 'label' => 'Order Line', 'admin' => false],
    ['route' => 'reports.index', 'match' => 'reports.*', 'icon' => 'bar_chart', 'label' => 'Laporan Penjualan', 'admin' => true],
    ['route' => 'products.index', 'match' => 'products.*', 'icon' => 'inventory_2', 'label' => 'Produk', 'admin' => false],
    ['route' => 'stock.index', 'match' => 'stock.*', 'icon' => 'warehouse', 'label' => 'Stok Barang', 'admin' => true],
    ['route' => 'customers.index', 'match' => 'customers.*', 'icon' => 'groups', 'label' => 'Pelanggan', 'admin' => false],
    ['route' => 'staff.index', 'match' => 'staff.*', 'icon' => 'badge', 'label' => 'Staff', 'admin' => true],
    ['route' => 'office', 'match' => 'office', 'icon' => 'apartment', 'label' => 'Kantor Virtual', 'admin' => false],
  ];
@endphp

<div class="min-h-screen lg:flex">
  <aside id="sidebar" class="fixed inset-y-0 left-0 z-40 w-64 -translate-x-full lg:translate-x-0 lg:sticky lg:top-0 lg:h-screen lg:flex flex-col shrink-0 bg-slate-900 dark:bg-slate-950 border-r border-slate-800 text-slate-300 transition-transform">
    <div class="flex items-center gap-3 px-5 h-20">
      <div class="w-10 h-10 rounded-xl bg-brand-600 text-white grid place-items-center shadow-lg shadow-brand-600/30"><span class="material-symbols-outlined">shopping_cart</span></div>
      <div class="leading-tight">
        <div class="font-extrabold text-lg text-white">Cashier<span class="text-blue-400">store</span></div>
        <div class="text-[11px] text-slate-400">{{ (auth()->user()?->business?->name ?? config('pos.store_name')) }} POS</div>
      </div>
    </div>
    <nav class="flex-1 px-3 space-y-1 overflow-y-auto">
      @foreach ($nav as $n)
        @continue($n['admin'] && ! $u->isAdmin())
        <a href="{{ route($n['route']) }}"
           class="flex items-center gap-3 h-11 px-4 rounded-xl text-sm font-semibold transition
                  {{ request()->routeIs($n['match']) ? 'bg-brand-600 text-white shadow-md shadow-brand-600/30' : 'text-slate-300 hover:bg-white/10 hover:text-white' }}">
          <span class="material-symbols-outlined">{{ $n['icon'] }}</span> {{ $n['label'] }}
        </a>
      @endforeach
    </nav>
    <div class="px-3 pb-2">
      <button type="button" onclick="toggleTheme()" class="w-full flex items-center gap-3 h-11 px-4 rounded-xl text-sm font-semibold text-slate-300 hover:bg-white/10 hover:text-white transition">
        <span class="material-symbols-outlined dark:hidden">dark_mode</span>
        <span class="material-symbols-outlined hidden dark:inline">light_mode</span>
        <span class="dark:hidden">Mode Gelap</span>
        <span class="hidden dark:inline">Mode Terang</span>
      </button>
    </div>
    <div class="p-3 border-t border-slate-800">
      <form method="POST" action="{{ route('logout') }}" class="flex items-center gap-3 px-2">
        @csrf
        <div class="w-10 h-10 rounded-full bg-amber-400 text-slate-900 grid place-items-center text-sm font-bold">{{ $u->initials }}</div>
        <div class="flex-1 min-w-0 leading-tight">
          <div class="text-sm font-bold truncate text-white">{{ $u->name }}</div>
          <div class="text-xs text-slate-400 capitalize">{{ $u->role }}</div>
        </div>
        <button class="p-2 rounded-lg text-slate-400 hover:bg-white/10 hover:text-white" title="Keluar"><span class="material-symbols-outlined">logout</span></button>
      </form>
    </div>
  </aside>
  <div id="backdrop" class="hidden fixed inset-0 z-30 bg-slate-950/50 lg:hidden"></div>

  <main class="flex-1 min-w-0">
    <div class="lg:hidden flex items-center justify-between px-4 h-14 bg-white border-b border-slate-100">
      <button id="menuBtn" class="p-2 -ml-2"><span class="material-symbols-outlined">menu</span></button>
      <span class="font-extrabold">Cashier<span class="text-brand-600">store</span></span>
      <button type="button" onclick="toggleTheme()" class="p-2 -mr-2" title="Ganti tema">
        <span class="material-symbols-outlined dark:hidden">dark_mode</span>
        <span class="material-symbols-outlined hidden dark:inline">light_mode</span>
      </button>
    </div>

    <div class="p-4 md:p-7 max-w-[1500px] mx-auto">
      @if (session('success'))
        <div class="mb-5 flex items-center gap-2 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 text-sm font-semibold">
          <span class="material-symbols-outlined">check_circle</span> {{ session('success') }}
        </div>
      @endif
      @yield('content')
    </div>
  </main>
</div>

<script>
  const sb = document.getElementById('sidebar'), bd = document.getElementById('backdrop');
  const toggle = () => { sb.classList.toggle('-translate-x-full'); bd.classList.toggle('hidden'); };
  document.getElementById('menuBtn')?.addEventListener('click', toggle);
  bd.addEventListener('click', toggle);
</script>
@stack('scripts')
</body>
</html>
