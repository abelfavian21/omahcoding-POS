@extends('layouts.app')
@section('title', 'Dashboard')
@section('content')
@php
  $hour = now()->hour;
  $greet = $hour < 11 ? 'Selamat pagi' : ($hour < 15 ? 'Selamat siang' : ($hour < 19 ? 'Selamat sore' : 'Selamat malam'));
  $trendPill = fn ($v) => $v >= 0 ? 'pill-green' : 'pill-red';
  $kpis = [
    ['label' => 'Total Pendapatan', 'icon' => 'payments', 'value' => 'Rp ' . number_format($revenue, 0, ',', '.'), 'trend' => $revenueTrend, 'foot' => 'vs kemarin (Rp ' . number_format($revenueY, 0, ',', '.') . ')'],
    ['label' => 'Total Pesanan', 'icon' => 'receipt_long', 'value' => $orders, 'trend' => $ordersTrend, 'foot' => 'pesanan selesai hari ini'],
    ['label' => 'Rata-rata Transaksi', 'icon' => 'sell', 'value' => 'Rp ' . number_format($avg, 0, ',', '.'), 'trend' => $avgTrend, 'foot' => 'per pesanan'],
  ];
@endphp

<div class="flex flex-wrap items-end justify-between gap-4 mb-6">
  <div>
    <div class="text-xs text-slate-500 mb-1">Dashboard › <span class="text-brand-600 font-semibold">Ringkasan</span></div>
    <h1 class="text-3xl font-extrabold tracking-tight">{{ $greet }}, {{ explode(' ', auth()->user()->name)[0] }} ☕</h1>
    <p class="text-sm text-slate-500">Ringkasan aktivitas kafe hari ini, {{ now()->translatedFormat('l, d F Y') }}.</p>
  </div>
  <div class="flex gap-2">
    @if (auth()->user()->isAdmin())
      <a href="{{ route('reports.index') }}" class="btn bg-white border border-slate-200 text-slate-700 hover:bg-slate-50"><span class="material-symbols-outlined">download</span> Export Laporan</a>
    @endif
    <a href="{{ route('pos') }}" class="btn-primary"><span class="material-symbols-outlined">add_circle</span> Pesanan Baru</a>
  </div>
</div>

<div class="grid grid-cols-1 xl:grid-cols-12 gap-5">
  <div class="xl:col-span-8 space-y-5">
    {{-- KPI --}}
    <div class="grid sm:grid-cols-3 gap-5">
      @foreach ($kpis as $k)
        <div class="card p-5">
          <div class="flex justify-between items-start">
            <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500">{{ $k['label'] }}</span>
            <span class="w-9 h-9 rounded-xl bg-brand-50 text-brand-600 grid place-items-center"><span class="material-symbols-outlined">{{ $k['icon'] }}</span></span>
          </div>
          <div class="text-2xl xl:text-[26px] font-extrabold tracking-tight mt-2">{{ $k['value'] }}</div>
          <div class="flex items-center gap-2 mt-3">
            <span class="pill {{ $trendPill($k['trend']) }}">{{ $k['trend'] >= 0 ? '↗ +' : '↘ ' }}{{ $k['trend'] }}%</span>
            <span class="text-xs text-slate-500">{{ $k['foot'] }}</span>
          </div>
        </div>
      @endforeach
    </div>

    {{-- Grafik per jam --}}
    <div class="card p-6">
      <div class="flex flex-wrap justify-between gap-3 mb-4">
        <div>
          <h2 class="text-lg font-bold">Analitik Pendapatan <span class="pill pill-blue ml-1">Hari ini</span></h2>
          <p class="text-xs text-slate-500">Pendapatan per jam, dine-in vs takeaway</p>
        </div>
        <div class="flex gap-5 text-sm">
          <div><span class="inline-block w-2.5 h-2.5 rounded-full bg-brand-600 mr-1"></span><b>Dine-in</b> @rupiah($dineTotal)</div>
          <div><span class="inline-block w-2.5 h-2.5 rounded-full bg-amber-400 mr-1"></span><b>Takeaway</b> @rupiah($takeTotal)</div>
        </div>
      </div>
      <div class="flex items-end gap-2 h-56 border-b border-slate-100">
        @foreach ($chart as $c)
          <div class="flex-1 flex items-end justify-center gap-0.5 h-full group relative">
            <div class="w-full max-w-[18px] rounded-t-md bg-brand-600" style="height: {{ max(2, $c['dine'] / $chartMax * 100) }}%" title="Dine-in @rupiah($c['dine'])"></div>
            <div class="w-full max-w-[18px] rounded-t-md bg-amber-400" style="height: {{ max(2, $c['take'] / $chartMax * 100) }}%" title="Takeaway @rupiah($c['take'])"></div>
            <div class="hidden group-hover:block absolute -top-9 z-10 bg-slate-800 text-white text-[11px] rounded-lg px-2 py-1 whitespace-nowrap">{{ $c['label'] }} · {{ $c['count'] }} pesanan</div>
          </div>
        @endforeach
      </div>
      <div class="flex gap-2 mt-2">
        @foreach ($chart as $c)<div class="flex-1 text-center text-[10px] font-semibold text-slate-500">{{ substr($c['label'], 0, 2) }}</div>@endforeach
      </div>
    </div>

    {{-- Pesanan terbaru --}}
    <div class="card overflow-hidden">
      <div class="flex justify-between items-center px-6 pt-5 pb-3">
        <h2 class="text-lg font-bold">Pesanan Terbaru</h2>
        @if (auth()->user()->isAdmin())<a href="{{ route('reports.index') }}" class="text-sm font-semibold text-brand-600">Lihat semua →</a>@endif
      </div>
      <div class="overflow-x-auto">
        <table class="w-full">
          <thead class="bg-brand-50/50"><tr><th class="th">Order</th><th class="th">Pelanggan</th><th class="th">Item</th><th class="th text-right">Total</th><th class="th">Bayar</th></tr></thead>
          <tbody class="divide-y divide-slate-50">
            @forelse ($recent as $o)
              <tr>
                <td class="td"><div class="font-bold text-brand-600">{{ $o->invoice_no }}</div><div class="text-xs text-slate-500">{{ $o->paid_at?->format('H:i') }}</div></td>
                <td class="td"><div class="font-semibold">{{ $o->customer->name ?? 'Walk-in' }}</div>
                  <span class="pill pill-slate !text-[10px]">{{ $o->order_type === 'dine_in' ? 'Dine-in' . ($o->table_no ? ' · T' . $o->table_no : '') : 'Takeaway' }}</span></td>
                <td class="td text-slate-600 max-w-[220px] truncate">{{ $o->items->map(fn ($i) => $i->qty . '× ' . $i->product_name)->implode(', ') }}</td>
                <td class="td text-right font-bold">@rupiah($o->total)</td>
                <td class="td text-slate-600">{{ \App\Models\Order::PAYMENTS[$o->payment_method] ?? $o->payment_method }}</td>
              </tr>
            @empty
              <tr><td colspan="5" class="td text-center text-slate-500 py-8">Belum ada pesanan.</td></tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
  </div>

  {{-- Kolom kanan --}}
  <div class="xl:col-span-4 space-y-5">
    <div class="card p-5">
      <div class="flex justify-between items-center mb-4"><h2 class="text-lg font-bold">Menu Terlaris</h2><span class="text-xs font-semibold text-slate-500">7 hari terakhir</span></div>
      <div class="space-y-3">
        @forelse ($topItems as $i)
          <div class="flex items-center gap-3">
            <div class="w-12 h-12 rounded-xl bg-amber-50 grid place-items-center text-2xl overflow-hidden shrink-0">
              @if ($i->product?->image_url)<img src="{{ $i->product->image_url }}" class="w-full h-full object-cover" alt="">@else{{ $i->product?->emoji ?? '🍽️' }}@endif
            </div>
            <div class="flex-1 min-w-0"><div class="font-bold truncate">{{ $i->product_name }}</div><div class="text-xs text-slate-500">{{ $i->sold }} terjual</div></div>
            <div class="font-bold text-sm">@rupiah($i->revenue)</div>
          </div>
        @empty
          <p class="text-sm text-slate-500">Belum ada data penjualan.</p>
        @endforelse
      </div>
    </div>

    <div class="card p-5">
      <div class="flex justify-between items-center mb-4"><h2 class="text-lg font-bold">Stok Menipis</h2>
        <a href="{{ route('products.index', ['stock' => 'low']) }}" class="text-sm font-semibold text-brand-600">Lihat →</a></div>
      <div class="space-y-3">
        @forelse ($lowStock as $p)
          <div class="flex items-center justify-between">
            <div class="min-w-0"><div class="font-semibold truncate">{{ $p->emoji }} {{ $p->name }}</div><div class="text-xs text-slate-500">Minimum {{ $p->min_stock }}</div></div>
            <span class="pill {{ $p->stock <= 0 ? 'pill-red' : 'pill-amber' }}">{{ $p->stock <= 0 ? 'Habis' : 'Sisa ' . $p->stock }}</span>
          </div>
        @empty
          <p class="text-sm text-slate-500">Semua stok aman 👍</p>
        @endforelse
      </div>
    </div>

    <div class="card p-5">
      <div class="flex justify-between items-center mb-4"><h2 class="text-lg font-bold">Staff Bertugas</h2><span class="pill pill-green">● Aktif</span></div>
      <div class="space-y-3">
        @foreach ($staff as $s)
          <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-full bg-brand-100 text-brand-700 grid place-items-center text-sm font-bold">{{ $s->initials }}</div>
            <div class="flex-1 min-w-0"><div class="font-bold truncate">{{ $s->name }}</div><div class="text-xs text-slate-500 capitalize">{{ $s->role }}</div></div>
            <span class="text-xs font-semibold text-slate-500">{{ $s->shift }}</span>
          </div>
        @endforeach
      </div>
    </div>
  </div>
</div>
@endsection
