@extends('layouts.app')
@section('title', 'Dashboard')
@section('content')
@php
  $hour = now()->hour;
  $greet = $hour < 11 ? 'Selamat pagi' : ($hour < 15 ? 'Selamat siang' : ($hour < 19 ? 'Selamat sore' : 'Selamat malam'));
  $first = explode(' ', auth()->user()->name)[0];
  $trendPill = fn ($v) => $v >= 0 ? 'pill-green' : 'pill-red';

  // jam tersibuk (hanya kalau memang ada pesanan)
  $peak = $chart->sortByDesc(fn ($c) => $c['dine'] + $c['take'])->first();
  $peak = ($peak && $peak['count'] > 0) ? $peak : null;
  $topMax = max(1, (int) $topItems->max('sold'));

  // satu bahasa permukaan, sama dengan kartu di landing: rounded-3xl + border tipis
  $surface = 'bg-white rounded-3xl border border-slate-200/80 dark:border-slate-700/60 shadow-sm';
  $focus = 'focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600';

  $kpis = [
    ['label' => 'Total pendapatan', 'icon' => 'payments', 'tile' => 'bg-blue-50 text-blue-600 dark:bg-blue-500/15 dark:text-blue-300',
     'value' => 'Rp ' . number_format($revenue, 0, ',', '.'), 'trend' => $revenueTrend, 'foot' => 'vs kemarin (Rp ' . number_format($revenueY, 0, ',', '.') . ')'],
    ['label' => 'Total pesanan', 'icon' => 'receipt_long', 'tile' => 'bg-indigo-50 text-indigo-600 dark:bg-indigo-500/15 dark:text-indigo-300',
     'value' => $orders, 'trend' => $ordersTrend, 'foot' => 'pesanan selesai hari ini'],
    ['label' => 'Rata-rata transaksi', 'icon' => 'sell', 'tile' => 'bg-cyan-50 text-cyan-600 dark:bg-cyan-500/15 dark:text-cyan-300',
     'value' => 'Rp ' . number_format($avg, 0, ',', '.'), 'trend' => $avgTrend, 'foot' => 'per pesanan'],
  ];
@endphp

{{-- Latar ambient: orb + grid biru tipis, sama seperti hero landing --}}
<div aria-hidden="true" class="pointer-events-none fixed inset-0 -z-10 overflow-hidden">
  <div class="absolute -top-32 right-0 w-[520px] h-[520px] rounded-full bg-blue-300/30 blur-[80px] dark:bg-blue-500/10"></div>
  <div class="absolute top-1/2 left-1/3 w-[460px] h-[460px] rounded-full bg-indigo-200/40 blur-[80px] dark:bg-indigo-500/10"></div>
  <div class="absolute inset-0" style="background-size:40px 40px;background-image:linear-gradient(to right,rgba(37,99,235,.05) 1px,transparent 1px),linear-gradient(to bottom,rgba(37,99,235,.05) 1px,transparent 1px)"></div>
</div>

{{-- Header --}}
<div class="flex flex-wrap items-end justify-between gap-5 mb-8">
  <div class="space-y-3">
    <div class="inline-flex items-center gap-2.5 px-3.5 py-1.5 rounded-full bg-blue-50 border border-blue-200/80 text-blue-700 text-xs font-bold shadow-sm dark:bg-blue-500/15 dark:border-blue-400/30 dark:text-blue-300">
      <span class="relative flex w-2 h-2">
        <span class="absolute inline-flex h-full w-full rounded-full bg-blue-500 opacity-75 animate-ping motion-reduce:animate-none"></span>
        <span class="relative inline-flex w-2 h-2 rounded-full bg-blue-600"></span>
      </span>
      Live &middot; {{ now()->translatedFormat('l, d F Y') }}
    </div>
    <h1 class="text-3xl sm:text-4xl font-black tracking-tight leading-[1.12] text-slate-900">{{ $greet }}, {{ $first }} ☕</h1>
    <p class="text-sm text-slate-500 max-w-xl">Ringkasan penjualan hari ini. Angka dibandingkan dengan kemarin.</p>
  </div>
  <div class="flex flex-wrap gap-3">
    <div class="inline-flex h-11 items-center gap-1 rounded-xl bg-slate-100 p-1 border border-slate-200/80 dark:border-slate-700/60" role="group" aria-label="Tema tampilan">
      @foreach (['light' => ['Mode terang', 'light_mode'], 'dark' => ['Mode gelap', 'dark_mode']] as $mode => [$label, $icon])
        <button type="button" data-theme-set="{{ $mode }}" aria-pressed="false" aria-label="{{ $label }}" title="{{ $label }}"
                class="grid place-items-center w-9 h-9 rounded-lg transition text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 aria-pressed:bg-blue-600 aria-pressed:text-white aria-pressed:shadow-sm aria-pressed:hover:text-white {{ $focus }}">
          <span class="material-symbols-outlined">{{ $icon }}</span>
        </button>
      @endforeach
    </div>
    @if (auth()->user()->isAdmin())
      <a href="{{ route('reports.index') }}" class="inline-flex items-center gap-2 h-11 px-5 rounded-xl bg-white border border-slate-200 text-slate-700 text-sm font-bold hover:bg-slate-50 transition {{ $focus }}">
        <span class="material-symbols-outlined">download</span> Export laporan
      </a>
    @endif
    <a href="{{ route('pos') }}" class="inline-flex items-center gap-2 h-11 px-5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-sm font-bold shadow-lg shadow-blue-600/30 hover:-translate-y-0.5 transition-all motion-reduce:transition-none motion-reduce:hover:translate-y-0 {{ $focus }}">
      <span class="material-symbols-outlined">add_circle</span> Pesanan baru
    </a>
  </div>
</div>

<div class="grid grid-cols-1 xl:grid-cols-12 gap-6">
  <div class="xl:col-span-8 space-y-6 min-w-0">

    {{-- KPI --}}
    <div class="grid sm:grid-cols-3 gap-6">
      @foreach ($kpis as $k)
        <div class="{{ $surface }} p-6">
          <div class="flex items-start justify-between">
            <span class="w-12 h-12 rounded-2xl grid place-items-center {{ $k['tile'] }}"><span class="material-symbols-outlined">{{ $k['icon'] }}</span></span>
            <span class="pill {{ $trendPill($k['trend']) }}">{{ $k['trend'] >= 0 ? '↗ +' : '↘ ' }}{{ $k['trend'] }}%</span>
          </div>
          <div class="mt-5 text-sm font-semibold text-slate-500">{{ $k['label'] }}</div>
          <div class="mt-1 text-2xl xl:text-[28px] font-black tracking-tight text-slate-900">{{ $k['value'] }}</div>
          <div class="mt-2 text-xs text-slate-500">{{ $k['foot'] }}</div>
        </div>
      @endforeach
    </div>

    {{-- Grafik per jam: satu-satunya blok gelap, sama dengan band navy di landing --}}
    <section class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-slate-900 via-blue-950 to-slate-900 text-white p-6 sm:p-8 shadow-xl shadow-slate-900/10 dark:ring-1 dark:ring-white/10">
      <div aria-hidden="true" class="absolute inset-0" style="background-size:40px 40px;background-image:linear-gradient(to right,rgba(255,255,255,.04) 1px,transparent 1px),linear-gradient(to bottom,rgba(255,255,255,.04) 1px,transparent 1px)"></div>
      <div class="relative">
        <div class="flex flex-wrap items-start justify-between gap-6">
          <div>
            <h2 class="text-2xl font-black tracking-tight">Pendapatan per jam</h2>
            <p class="text-sm text-slate-300 mt-1">Hari ini, dine-in dan takeaway.</p>
            @if ($peak)
              <p class="text-sm font-semibold text-blue-300 mt-3">Tersibuk pukul {{ $peak['label'] }}, {{ $peak['count'] }} pesanan.</p>
            @endif
          </div>
          <dl class="flex gap-8">
            <div>
              <dt class="flex items-center gap-2 text-xs font-semibold text-slate-300"><span class="w-2.5 h-2.5 rounded-full bg-blue-400"></span>Dine-in</dt>
              <dd class="mt-1 text-xl font-black">@rupiah($dineTotal)</dd>
            </div>
            <div>
              <dt class="flex items-center gap-2 text-xs font-semibold text-slate-300"><span class="w-2.5 h-2.5 rounded-full bg-amber-400"></span>Takeaway</dt>
              <dd class="mt-1 text-xl font-black">@rupiah($takeTotal)</dd>
            </div>
          </dl>
        </div>

        <div class="relative mt-10 h-56">
          <div aria-hidden="true" class="absolute inset-0 flex flex-col justify-between">
            <div class="border-t border-white/10"></div><div class="border-t border-white/10"></div><div class="border-t border-white/10"></div><div class="border-t border-white/20"></div>
          </div>
          <div class="relative flex items-end gap-2 h-full">
            @foreach ($chart as $c)
              @php $tipPos = $loop->first ? 'left-0' : ($loop->last ? 'right-0' : 'left-1/2 -translate-x-1/2'); @endphp
              <div class="group relative flex-1 h-full flex items-end justify-center gap-0.5 rounded-md outline-none focus-visible:ring-2 focus-visible:ring-blue-400" tabindex="0" role="img"
                   aria-label="{{ $c['label'] }}: {{ $c['count'] }} pesanan, dine-in @rupiah($c['dine']), takeaway @rupiah($c['take'])">
                <div class="w-full max-w-[18px] rounded-t-md bg-gradient-to-t from-blue-600 to-blue-400 transition group-hover:brightness-125 group-focus-visible:brightness-125" style="height: {{ max(2, $c['dine'] / $chartMax * 100) }}%"></div>
                <div class="w-full max-w-[18px] rounded-t-md bg-amber-400 transition group-hover:brightness-110 group-focus-visible:brightness-110" style="height: {{ max(2, $c['take'] / $chartMax * 100) }}%"></div>
                <div class="hidden group-hover:block group-focus-visible:block absolute -top-12 {{ $tipPos }} z-10 rounded-lg bg-white text-slate-900 text-[11px] font-semibold px-2.5 py-1.5 whitespace-nowrap shadow-lg">
                  {{ $c['label'] }} &middot; {{ $c['count'] }} pesanan
                </div>
              </div>
            @endforeach
          </div>
        </div>
        <div class="flex gap-2 mt-3">
          @foreach ($chart as $c)
            <div class="flex-1 text-center text-[10px] font-semibold {{ $peak && $c['label'] === $peak['label'] ? 'text-blue-300' : 'text-slate-400' }}">{{ substr($c['label'], 0, 2) }}</div>
          @endforeach
        </div>
      </div>
    </section>

    {{-- Pesanan terbaru --}}
    <section class="{{ $surface }} overflow-hidden">
      <div class="flex justify-between items-center px-6 sm:px-8 pt-6 pb-4">
        <h2 class="text-xl font-black tracking-tight">Pesanan terbaru</h2>
        @if (auth()->user()->isAdmin())
          <a href="{{ route('reports.index') }}" class="text-sm font-bold text-brand-600 hover:underline rounded {{ $focus }}">Lihat semua</a>
        @endif
      </div>
      <div class="overflow-x-auto">
        <table class="w-full">
          <thead class="bg-brand-50/50"><tr><th class="th sm:pl-8">Order</th><th class="th">Pelanggan</th><th class="th">Item</th><th class="th text-right">Total</th><th class="th sm:pr-8">Bayar</th></tr></thead>
          <tbody class="divide-y divide-slate-50">
            @forelse ($recent as $o)
              <tr class="hover:bg-slate-50/60 transition-colors">
                <td class="td sm:pl-8"><div class="font-bold text-brand-600">{{ $o->invoice_no }}</div><div class="text-xs text-slate-500">{{ $o->paid_at?->format('H:i') }}</div></td>
                <td class="td"><div class="font-semibold">{{ $o->customer->name ?? 'Walk-in' }}</div>
                  <span class="pill pill-slate !text-[10px]">{{ $o->order_type === 'dine_in' ? 'Dine-in' . ($o->table_no ? ' · T' . $o->table_no : '') : 'Takeaway' }}</span></td>
                <td class="td text-slate-600 max-w-[220px] truncate">{{ $o->items->map(fn ($i) => $i->qty . '× ' . $i->product_name)->implode(', ') }}</td>
                <td class="td text-right font-black">@rupiah($o->total)</td>
                <td class="td text-slate-600 sm:pr-8">{{ \App\Models\Order::PAYMENTS[$o->payment_method] ?? $o->payment_method }}</td>
              </tr>
            @empty
              <tr><td colspan="5" class="px-6 py-12 text-center">
                <span class="mx-auto mb-3 w-12 h-12 rounded-2xl bg-blue-50 text-blue-600 grid place-items-center dark:bg-blue-500/15 dark:text-blue-300"><span class="material-symbols-outlined">receipt_long</span></span>
                <p class="text-sm font-semibold">Belum ada pesanan.</p>
                <a href="{{ route('pos') }}" class="inline-block mt-1 text-sm font-bold text-brand-600 hover:underline">Buat pesanan pertama</a>
              </td></tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </section>
  </div>

  {{-- Kolom kanan --}}
  <div class="xl:col-span-4 space-y-6 min-w-0">

    <section class="{{ $surface }} p-6">
      <div class="flex justify-between items-baseline mb-5">
        <h2 class="text-xl font-black tracking-tight">Menu terlaris</h2>
        <span class="text-xs font-semibold text-slate-500">7 hari terakhir</span>
      </div>
      <ul class="space-y-4">
        @forelse ($topItems as $i)
          <li class="flex items-center gap-3">
            <div class="w-12 h-12 rounded-2xl bg-amber-50 grid place-items-center text-2xl overflow-hidden shrink-0">
              @if ($i->product?->image_url)<img src="{{ $i->product->image_url }}" class="w-full h-full object-cover" alt="">@else{{ $i->product?->emoji ?? '🍽️' }}@endif
            </div>
            <div class="flex-1 min-w-0">
              <div class="flex items-baseline justify-between gap-2">
                <span class="font-bold truncate">{{ $i->product_name }}</span>
                <span class="text-sm font-black shrink-0">@rupiah($i->revenue)</span>
              </div>
              <div class="mt-1.5 h-1.5 rounded-full bg-slate-100 overflow-hidden">
                <div class="h-full rounded-full bg-gradient-to-r from-blue-600 to-indigo-500" style="width: {{ round($i->sold / $topMax * 100) }}%"></div>
              </div>
              <div class="mt-1 text-xs text-slate-500">{{ $i->sold }} terjual</div>
            </div>
          </li>
        @empty
          <li class="text-sm text-slate-500">Belum ada data penjualan.</li>
        @endforelse
      </ul>
    </section>

    <section class="{{ $surface }} p-6">
      <div class="flex justify-between items-baseline mb-5">
        <h2 class="text-xl font-black tracking-tight">Stok menipis</h2>
        <a href="{{ route('products.index', ['stock' => 'low']) }}" class="text-sm font-bold text-brand-600 hover:underline rounded {{ $focus }}">Lihat semua</a>
      </div>
      <ul class="space-y-3">
        @forelse ($lowStock as $p)
          <li class="flex items-center justify-between gap-3 p-3 -mx-3 rounded-2xl hover:bg-slate-50 transition-colors">
            <div class="min-w-0">
              <div class="font-semibold truncate">{{ $p->emoji }} {{ $p->name }}</div>
              <div class="text-xs text-slate-500">Minimum {{ $p->min_stock }}</div>
            </div>
            <span class="pill {{ $p->stock <= 0 ? 'pill-red' : 'pill-amber' }}">{{ $p->stock <= 0 ? 'Habis' : 'Sisa ' . $p->stock }}</span>
          </li>
        @empty
          <li class="flex items-center gap-2 text-sm text-slate-500"><span class="material-symbols-outlined text-emerald-600">check_circle</span> Semua stok aman.</li>
        @endforelse
      </ul>
    </section>

    <section class="{{ $surface }} p-6">
      <div class="flex justify-between items-baseline mb-5">
        <h2 class="text-xl font-black tracking-tight">Staff bertugas</h2>
        <span class="pill pill-green">● Aktif</span>
      </div>
      <ul class="space-y-4">
        @foreach ($staff as $s)
          <li class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-full bg-gradient-to-tr from-blue-700 to-blue-500 text-white grid place-items-center text-sm font-bold shadow-md shadow-blue-500/25">{{ $s->initials }}</div>
            <div class="flex-1 min-w-0"><div class="font-bold truncate">{{ $s->name }}</div><div class="text-xs text-slate-500 capitalize">{{ $s->role }}</div></div>
            <span class="text-xs font-semibold text-slate-500">{{ $s->shift }}</span>
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
    // Cadangan kalau partials/theme-init belum terpasang; kunci 'theme' sama dengan head
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
    // ikut berubah kalau tema diganti dari tombol sidebar / tab lain
    new MutationObserver(sync).observe(document.documentElement, { attributes: true, attributeFilter: ['class'] });
    sync();
  })();
</script>
@endpush
