@extends('layouts.app')
@section('title', 'Dashboard')
@section('content')
@php
  $hour = now()->hour;
  $greet = $hour < 11 ? 'Selamat pagi' : ($hour < 15 ? 'Selamat siang' : ($hour < 19 ? 'Selamat sore' : 'Selamat malam'));
  $first = explode(' ', auth()->user()->name)[0];

  // jam tersibuk (hanya kalau memang ada pesanan)
  $peak = $chart->sortByDesc(fn ($c) => $c['dine'] + $c['take'])->first();
  $peak = ($peak && $peak['count'] > 0) ? $peak : null;
  $topMax = max(1, (int) $topItems->max('sold'));

  // komposisi dine-in vs takeaway (dihitung dari angka yang sudah ada)
  $mixTotal = (float) $dineTotal + (float) $takeTotal;
  $dinePct = $mixTotal > 0 ? round($dineTotal / $mixTotal * 100) : 0;

  $trendPill = fn ($v) => $v >= 0 ? 'bg-emerald-50 text-emerald-600' : 'bg-red-50 text-red-600';
  $arrow = fn ($v) => $v >= 0 ? 'arrow_upward' : 'arrow_downward';
  $focus = 'focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#B1D501]';
  $label = 'text-xs font-bold text-slate-400 tracking-wide uppercase';
@endphp

{{-- Header --}}
<div class="flex flex-col lg:flex-row lg:items-end justify-between gap-4 mb-6">
  <div class="min-w-0">
    <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-slate-900">{{ $greet }}, {{ $first }} <x-ico v="waving_hand" class="text-lime-600 !align-middle" style="font-size:28px" /></h1>
    <p class="text-sm font-medium text-slate-500 mt-1">Ringkasan penjualan hari ini, dibandingkan dengan kemarin.</p>
  </div>
  <div class="flex flex-wrap items-center gap-2.5">
    <span class="h-11 px-4 inline-flex items-center gap-2 rounded-full bg-white border border-card-border text-xs font-bold text-slate-700 shadow-sm">
      <span class="relative flex w-2 h-2"><span class="absolute inline-flex h-full w-full rounded-full bg-lime-500 opacity-75 animate-ping motion-reduce:animate-none"></span><span class="relative inline-flex w-2 h-2 rounded-full bg-lime-500"></span></span>
      {{ now()->translatedFormat('l, d F Y') }}
    </span>
    <div class="inline-flex h-11 items-center gap-1 rounded-full bg-white border border-card-border p-1 shadow-sm" role="group" aria-label="Tema tampilan">
      @foreach (['light' => ['Mode terang', 'light_mode'], 'dark' => ['Mode gelap', 'dark_mode']] as $mode => [$lbl, $icon])
        <button type="button" data-theme-set="{{ $mode }}" aria-pressed="false" aria-label="{{ $lbl }}" title="{{ $lbl }}"
                class="grid place-items-center w-9 h-9 rounded-full transition text-slate-400 hover:text-slate-700 aria-pressed:bg-[#181829] aria-pressed:text-lime-accent {{ $focus }}">
          <span class="material-symbols-outlined">{{ $icon }}</span>
        </button>
      @endforeach
    </div>
    @if (auth()->user()->isAdmin())
      <a href="{{ route('reports.index') }}" class="btn bg-white border border-card-border text-slate-700 hover:bg-slate-50 shadow-sm {{ $focus }}">
        <span class="material-symbols-outlined">download</span> <span class="hidden xs:inline">Export laporan</span><span class="xs:hidden">Export</span>
      </a>
    @endif
    <a href="{{ route('pos') }}" class="btn-dark text-lime-accent shadow-sm {{ $focus }}">
      <span class="material-symbols-outlined">add_circle</span> Pesanan baru
    </a>
  </div>
</div>

{{-- KPI --}}
<div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4 sm:gap-5 mb-5">
  {{-- Hero gelap --}}
  <div class="card-dark p-5 sm:p-6 flex flex-col justify-between">
    <div class="flex items-start justify-between">
      <div>
        <span class="text-xs font-bold text-slate-400 tracking-wide uppercase">Total pendapatan</span>
        <div class="mt-2 text-[28px] leading-[34px] font-extrabold tracking-[-0.03em] break-all"><span class="text-sm font-bold text-slate-400 align-top mr-1">Rp</span>{{ number_format($revenue, 0, ',', '.') }}</div>
      </div>
      <div class="w-9 h-9 rounded-xl bg-lime-accent text-slate-950 grid place-items-center shrink-0"><span class="material-symbols-outlined fill-1">payments</span></div>
    </div>
    <div class="mt-5 flex flex-wrap items-center gap-2">
      <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-lime-accent/15 text-lime-accent font-bold text-xs"><span class="material-symbols-outlined" style="font-size:14px">{{ $arrow($revenueTrend) }}</span>{{ $revenueTrend >= 0 ? '+' : '' }}{{ $revenueTrend }}%</span>
      <span class="text-xs font-medium text-slate-400">vs kemarin (Rp {{ number_format($revenueY, 0, ',', '.') }})</span>
    </div>
    <span aria-hidden="true" class="material-symbols-outlined absolute -right-3 -bottom-4 text-white/5 pointer-events-none" style="font-size:96px">receipt</span>
  </div>

  <div class="card p-5 sm:p-6 flex flex-col justify-between">
    <div class="flex items-start justify-between">
      <div>
        <span class="{{ $label }}">Total pesanan</span>
        <div class="mt-2 flex items-baseline gap-1.5"><span class="text-[28px] leading-[34px] font-extrabold tracking-[-0.03em] text-slate-900">{{ $orders }}</span><span class="text-[11px] font-bold text-slate-400">PESANAN</span></div>
      </div>
      <div class="w-9 h-9 rounded-xl bg-slate-100 text-slate-600 grid place-items-center shrink-0"><span class="material-symbols-outlined">receipt_long</span></div>
    </div>
    <div class="mt-5 flex flex-wrap items-center gap-2">
      <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full font-bold text-xs {{ $trendPill($ordersTrend) }}"><span class="material-symbols-outlined" style="font-size:14px">{{ $arrow($ordersTrend) }}</span>{{ $ordersTrend >= 0 ? '+' : '' }}{{ $ordersTrend }}%</span>
      <span class="text-xs font-medium text-slate-400">selesai hari ini</span>
    </div>
  </div>

  <div class="card p-5 sm:p-6 flex flex-col justify-between">
    <div class="flex items-start justify-between">
      <div>
        <span class="{{ $label }}">Rata-rata transaksi</span>
        <div class="mt-2 text-[28px] leading-[34px] font-extrabold tracking-[-0.03em] text-slate-900 break-all"><span class="text-sm font-bold text-slate-400 align-top mr-1">Rp</span>{{ number_format($avg, 0, ',', '.') }}</div>
      </div>
      <div class="w-9 h-9 rounded-xl bg-slate-100 text-slate-600 grid place-items-center shrink-0"><span class="material-symbols-outlined">sell</span></div>
    </div>
    <div class="mt-5 flex flex-wrap items-center gap-2">
      <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full font-bold text-xs {{ $trendPill($avgTrend) }}"><span class="material-symbols-outlined" style="font-size:14px">{{ $arrow($avgTrend) }}</span>{{ $avgTrend >= 0 ? '+' : '' }}{{ $avgTrend }}%</span>
      <span class="text-xs font-medium text-slate-400">per pesanan</span>
    </div>
  </div>

  <div class="card p-5 sm:p-6 flex flex-col justify-between">
    <div class="flex items-start justify-between">
      <div>
        <span class="{{ $label }}">Dine-in vs takeaway</span>
        <div class="mt-2 flex items-baseline gap-1.5"><span class="text-[28px] leading-[34px] font-extrabold tracking-[-0.03em] text-slate-900">{{ $dinePct }}<span class="text-xl text-slate-400 font-semibold">%</span></span><span class="text-[11px] font-bold text-slate-400">DINE-IN</span></div>
      </div>
      <div class="w-9 h-9 rounded-xl bg-slate-100 text-slate-600 grid place-items-center shrink-0"><span class="material-symbols-outlined">restaurant</span></div>
    </div>
    <div class="mt-5">
      <div class="h-2 rounded-full overflow-hidden flex bg-slate-100"><div class="h-full bg-lime-accent" style="width: {{ $dinePct }}%"></div><div class="h-full hatch" style="width: {{ $mixTotal > 0 ? 100 - $dinePct : 0 }}%"></div></div>
      <div class="mt-2 text-xs font-medium text-slate-400">Takeaway {{ $mixTotal > 0 ? 100 - $dinePct : 0 }}%</div>
    </div>
  </div>
</div>

<div class="grid grid-cols-1 xl:grid-cols-12 gap-5">
  {{-- Grafik per jam --}}
  <section class="card p-5 sm:p-6 xl:col-span-8 min-w-0">
    <div class="flex flex-wrap items-start justify-between gap-4 mb-5">
      <div>
        <h2 class="text-base font-extrabold text-slate-900">Pendapatan per jam</h2>
        <p class="text-xs text-slate-400 font-medium">Hari ini, dine-in dan takeaway.@if ($peak) <span class="text-slate-700 font-bold">Tersibuk {{ $peak['label'] }} · {{ $peak['count'] }} pesanan.</span>@endif</p>
      </div>
      <dl class="flex gap-5">
        <div><dt class="flex items-center gap-2 text-xs font-semibold text-slate-500"><span class="w-3 h-3 rounded-full bg-lime-accent"></span>Dine-in</dt><dd class="mt-0.5 text-sm font-extrabold text-slate-900">@rupiah($dineTotal)</dd></div>
        <div><dt class="flex items-center gap-2 text-xs font-semibold text-slate-500"><span class="w-3 h-3 rounded-full hatch"></span>Takeaway</dt><dd class="mt-0.5 text-sm font-extrabold text-slate-900">@rupiah($takeTotal)</dd></div>
      </dl>
    </div>

    <div class="relative h-56">
      <div aria-hidden="true" class="absolute inset-0 flex flex-col justify-between">
        <div class="border-t border-slate-100"></div><div class="border-t border-slate-100"></div><div class="border-t border-slate-100"></div><div class="border-t border-slate-200"></div>
      </div>
      <div class="relative flex items-end gap-1 sm:gap-2 h-full">
        @foreach ($chart as $c)
          @php $tipPos = $loop->first ? 'left-0' : ($loop->last ? 'right-0' : 'left-1/2 -translate-x-1/2'); @endphp
          <div class="group relative flex-1 min-w-0 h-full flex items-end justify-center gap-0.5 rounded-md outline-none focus-visible:ring-2 focus-visible:ring-[#B1D501]" tabindex="0" role="img"
               aria-label="{{ $c['label'] }}: {{ $c['count'] }} pesanan, dine-in @rupiah($c['dine']), takeaway @rupiah($c['take'])">
            <div class="w-full max-w-[14px] rounded-full bg-lime-accent transition group-hover:brightness-95" style="height: {{ max(2, $c['dine'] / $chartMax * 100) }}%"></div>
            <div class="w-full max-w-[14px] rounded-full hatch transition group-hover:brightness-95" style="height: {{ max(2, $c['take'] / $chartMax * 100) }}%"></div>
            <div class="hidden group-hover:block group-focus:block absolute -top-11 {{ $tipPos }} z-10 rounded-xl bg-[#181829] text-white text-[11px] font-semibold px-2.5 py-1.5 whitespace-nowrap shadow-lg">
              {{ $c['label'] }} &middot; {{ $c['count'] }} pesanan
            </div>
          </div>
        @endforeach
      </div>
    </div>
    <div class="flex gap-1 sm:gap-2 mt-2 pt-2 border-t border-slate-100">
      @foreach ($chart as $c)
        <div class="flex-1 min-w-0 text-center text-[10px] font-bold {{ $peak && $c['label'] === $peak['label'] ? 'text-slate-900' : 'text-slate-400' }}">{{ substr($c['label'], 0, 2) }}</div>
      @endforeach
    </div>
  </section>

  {{-- Menu terlaris --}}
  <section class="card p-5 sm:p-6 xl:col-span-4 min-w-0">
    <div class="flex justify-between items-baseline mb-5">
      <h2 class="text-base font-extrabold text-slate-900">Menu terlaris</h2>
      <span class="text-xs font-semibold text-slate-400">7 hari terakhir</span>
    </div>
    <ul class="space-y-4">
      @forelse ($topItems as $i)
        <li class="flex items-center gap-3">
          <div class="w-12 h-12 rounded-2xl bg-slate-100 grid place-items-center text-2xl overflow-hidden shrink-0">
            @if ($i->product?->image_url)<img src="{{ $i->product->image_url }}" class="w-full h-full object-cover" alt="">@else<x-ico :v="$i->product?->emoji" style="font-size:22px" />@endif
          </div>
          <div class="flex-1 min-w-0">
            <div class="flex items-baseline justify-between gap-2">
              <span class="font-bold text-sm truncate">{{ $i->product_name }}</span>
              <span class="text-sm font-extrabold shrink-0">@rupiah($i->revenue)</span>
            </div>
            <div class="mt-1.5 h-1.5 rounded-full bg-slate-100 overflow-hidden"><div class="h-full rounded-full bg-lime-accent" style="width: {{ round($i->sold / $topMax * 100) }}%"></div></div>
            <div class="mt-1 text-xs text-slate-400 font-medium">{{ $i->sold }} terjual</div>
          </div>
        </li>
      @empty
        <li class="text-sm text-slate-500">Belum ada data penjualan.</li>
      @endforelse
    </ul>
  </section>

  {{-- Pesanan terbaru --}}
  <section class="card xl:col-span-8 min-w-0 overflow-hidden">
    <div class="flex justify-between items-center px-5 sm:px-6 pt-5 sm:pt-6 pb-3">
      <div class="flex items-center gap-2"><h2 class="text-base font-extrabold text-slate-900">Pesanan terbaru</h2><span class="w-2 h-2 rounded-full bg-lime-500 animate-pulse motion-reduce:animate-none"></span></div>
      @if (auth()->user()->isAdmin())
        <a href="{{ route('reports.index') }}" class="text-sm font-bold text-brand-600 hover:underline rounded {{ $focus }}">Lihat semua</a>
      @endif
    </div>

    {{-- tabel (tablet+) --}}
    <div class="hidden md:block overflow-x-auto px-2 pb-3">
      <table class="w-full">
        <thead><tr class="border-b border-slate-100"><th class="th">Order</th><th class="th">Pelanggan</th><th class="th">Item</th><th class="th">Bayar</th><th class="th text-right">Total</th></tr></thead>
        <tbody class="divide-y divide-slate-50">
          @forelse ($recent as $o)
            <tr class="hover:bg-slate-50/70 transition-colors">
              <td class="td">
                <div class="flex items-center gap-2.5">
                  <span class="w-9 h-9 rounded-xl bg-[#181829] text-lime-accent grid place-items-center shrink-0"><span class="material-symbols-outlined" style="font-size:18px">receipt</span></span>
                  <div><div class="font-bold text-slate-900">{{ $o->invoice_no }}</div><div class="text-[11px] text-slate-400">{{ $o->paid_at?->format('H:i') }}</div></div>
                </div>
              </td>
              <td class="td"><div class="font-bold">{{ $o->customer->name ?? 'Walk-in' }}</div>
                <div class="text-[11px] text-slate-400">{{ $o->order_type === 'dine_in' ? 'Dine-in' . ($o->table_no ? ' · Meja ' . $o->table_no : '') : 'Takeaway' }}</div></td>
              <td class="td text-slate-500 max-w-[200px] truncate">{{ $o->items->map(fn ($i) => $i->qty . '× ' . $i->product_name)->implode(', ') }}</td>
              <td class="td"><span class="pill pill-slate">{{ \App\Models\Order::PAYMENTS[$o->payment_method] ?? $o->payment_method }}</span></td>
              <td class="td text-right font-extrabold text-slate-900">@rupiah($o->total)</td>
            </tr>
          @empty
            <tr><td colspan="5" class="px-6 py-12 text-center">
              <span class="mx-auto mb-3 w-12 h-12 rounded-2xl bg-slate-100 text-slate-500 grid place-items-center"><span class="material-symbols-outlined">receipt_long</span></span>
              <p class="text-sm font-semibold">Belum ada pesanan.</p>
              <a href="{{ route('pos') }}" class="inline-block mt-1 text-sm font-bold text-brand-600 hover:underline">Buat pesanan pertama</a>
            </td></tr>
          @endforelse
        </tbody>
      </table>
    </div>

    {{-- daftar kartu (mobile) --}}
    <ul class="md:hidden divide-y divide-slate-50 px-5 pb-3">
      @forelse ($recent as $o)
        <li class="py-3.5 flex items-start gap-3">
          <span class="w-10 h-10 rounded-xl bg-[#181829] text-lime-accent grid place-items-center shrink-0"><span class="material-symbols-outlined" style="font-size:18px">receipt</span></span>
          <div class="min-w-0 flex-1">
            <div class="flex justify-between gap-2"><span class="font-bold text-sm truncate">{{ $o->customer->name ?? 'Walk-in' }}</span><span class="font-extrabold text-sm shrink-0">@rupiah($o->total)</span></div>
            <div class="text-[11px] text-slate-400">{{ $o->invoice_no }} · {{ $o->paid_at?->format('H:i') }} · {{ $o->order_type === 'dine_in' ? 'Dine-in' . ($o->table_no ? ' T' . $o->table_no : '') : 'Takeaway' }}</div>
            <div class="text-xs text-slate-500 truncate mt-0.5">{{ $o->items->map(fn ($i) => $i->qty . '× ' . $i->product_name)->implode(', ') }}</div>
            <span class="pill pill-slate mt-1.5">{{ \App\Models\Order::PAYMENTS[$o->payment_method] ?? $o->payment_method }}</span>
          </div>
        </li>
      @empty
        <li class="py-10 text-center text-sm text-slate-500">Belum ada pesanan. <a href="{{ route('pos') }}" class="font-bold text-brand-600">Buat pesanan pertama</a></li>
      @endforelse
    </ul>
  </section>

  {{-- Kolom kanan bawah --}}
  <div class="xl:col-span-4 space-y-5 min-w-0">
    <section class="card p-5 sm:p-6">
      <div class="flex justify-between items-baseline mb-4">
        <h2 class="text-base font-extrabold text-slate-900">Stok menipis</h2>
        <a href="{{ route('products.index', ['stock' => 'low']) }}" class="text-sm font-bold text-brand-600 hover:underline rounded {{ $focus }}">Lihat semua</a>
      </div>
      <ul class="space-y-1">
        @forelse ($lowStock as $p)
          <li class="flex items-center justify-between gap-3 p-3 -mx-3 rounded-2xl hover:bg-slate-50 transition-colors">
            <div class="min-w-0">
              <div class="font-semibold text-sm truncate"><x-ico :v="$p->emoji" class="text-slate-400 mr-1" style="font-size:18px" />{{ $p->name }}</div>
              <div class="text-xs text-slate-400">Minimum {{ $p->min_stock }}</div>
            </div>
            <span class="pill {{ $p->stock <= 0 ? 'pill-red' : 'pill-amber' }}">{{ $p->stock <= 0 ? 'Habis' : 'Sisa ' . $p->stock }}</span>
          </li>
        @empty
          <li class="flex items-center gap-2 text-sm text-slate-500"><span class="material-symbols-outlined text-emerald-600">check_circle</span> Semua stok aman.</li>
        @endforelse
      </ul>
    </section>

    <section class="card p-5 sm:p-6">
      <div class="flex justify-between items-center mb-4">
        <h2 class="text-base font-extrabold text-slate-900">Staff bertugas</h2>
        <span class="pill pill-blue">● Aktif</span>
      </div>
      <ul class="space-y-3.5">
        @foreach ($staff as $s)
          <li class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-full bg-[#181829] text-lime-accent grid place-items-center text-sm font-bold shrink-0 ring-2 ring-lime-accent/40">{{ $s->initials }}</div>
            <div class="flex-1 min-w-0"><div class="font-bold text-sm truncate">{{ $s->name }}</div><div class="text-xs text-slate-400 capitalize">{{ $s->role }}</div></div>
            <span class="text-xs font-semibold text-slate-400">{{ $s->shift }}</span>
          </li>
        @endforeach
      </ul>
    </section>
  </div>
</div>
@endsection

@push('scripts')
<script>
  (function () {
    if (!window.setTheme) {
      window.setTheme = function (mode) {
        document.documentElement.classList.toggle('dark', mode === 'dark');
        try { localStorage.setItem('theme', mode); } catch (e) {}
      };
    }
    var btns = document.querySelectorAll('[data-theme-set]');
    function sync() {
      var dark = document.documentElement.classList.contains('dark');
      btns.forEach(function (b) { b.setAttribute('aria-pressed', String((b.dataset.themeSet === 'dark') === dark)); });
    }
    btns.forEach(function (b) { b.addEventListener('click', function () { window.setTheme(b.dataset.themeSet); sync(); }); });
    new MutationObserver(sync).observe(document.documentElement, { attributes: true, attributeFilter: ['class'] });
    sync();
  })();
</script>
@endpush
