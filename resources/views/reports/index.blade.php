@extends('layouts.app')
@section('title', 'Laporan Penjualan')
@section('content')
@php
  $pay = \App\Models\Order::PAYMENTS;
  $lbl = 'text-xs font-bold text-slate-400 tracking-wide uppercase';
  $num = 'text-2xl sm:text-[28px] leading-[34px] font-extrabold tracking-[-0.03em] break-all';
@endphp
<div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4 mb-6">
  <div>
    <div class="text-xs text-slate-400 font-semibold mb-1">Dashboard › <span class="text-slate-700">Laporan Penjualan</span></div>
    <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight">Laporan Penjualan</h1>
    <p class="text-sm font-medium text-slate-500 mt-1">{{ $report->periodLabel() }}</p>
  </div>
  <div class="flex flex-wrap gap-2">
    <a href="{{ route('reports.pdf', $query) }}" class="btn-secondary"><span class="material-symbols-outlined">picture_as_pdf</span> Export PDF</a>
    <a href="{{ route('reports.excel', $query) }}" class="btn-primary"><span class="material-symbols-outlined">table_view</span> Export Excel</a>
  </div>
</div>

<form method="GET" class="card p-4 mb-5 space-y-4">
  <div class="grid grid-cols-2 lg:flex lg:flex-wrap lg:items-end gap-3">
    <div><label class="label">Dari</label><input type="date" name="from" value="{{ $query['from'] }}" class="input"></div>
    <div><label class="label">Sampai</label><input type="date" name="to" value="{{ $query['to'] }}" class="input"></div>
    <div class="col-span-2 lg:col-auto"><label class="label">Pembayaran</label>
      <select name="method" class="input"><option value="">Semua</option>@foreach ($pay as $k => $v)<option value="{{ $k }}" @selected($report->method === $k)>{{ $v }}</option>@endforeach</select></div>
    <button class="btn-dark text-lime-accent col-span-2 lg:col-auto">Terapkan</button>
    <div class="col-span-2 lg:ml-auto flex gap-2 overflow-x-auto no-scrollbar">
      <a class="chip" href="{{ route('reports.index', ['from' => today()->toDateString(), 'to' => today()->toDateString()]) }}">Hari ini</a>
      <a class="chip" href="{{ route('reports.index', ['from' => today()->subDays(6)->toDateString(), 'to' => today()->toDateString()]) }}">7 hari</a>
      <a class="chip" href="{{ route('reports.index') }}">Bulan ini</a>
    </div>
  </div>
</form>

<div class="grid grid-cols-2 xl:grid-cols-4 gap-3 sm:gap-5 mb-5">
  @foreach ([
    ['Total Pendapatan', 'Rp ' . number_format($summary['revenue'], 0, ',', '.'), 'Sudah termasuk pajak', 'payments'],
    ['Jumlah Transaksi', $summary['orders'], 'Rata-rata Rp ' . number_format($summary['avg'], 0, ',', '.'), 'receipt_long'],
    ['Item Terjual', $summary['items'], 'Diskon Rp ' . number_format($summary['discount'], 0, ',', '.'), 'shopping_bag'],
    ['Laba Kotor', 'Rp ' . number_format($summary['profit'], 0, ',', '.'), 'Penjualan − HPP − diskon', 'trending_up'],
  ] as $i => [$l, $v, $f, $ic])
    @if ($i === 0)
      <div class="card-dark p-4 sm:p-6">
        <div class="flex items-start justify-between gap-2"><span class="text-xs font-bold text-slate-400 tracking-wide uppercase">{{ $l }}</span>
          <span class="w-8 h-8 rounded-xl bg-lime-accent text-slate-950 grid place-items-center shrink-0"><span class="material-symbols-outlined fill-1" style="font-size:18px">{{ $ic }}</span></span></div>
        <div class="{{ $num }} mt-2">{{ $v }}</div><div class="text-xs font-medium text-slate-400 mt-3">{{ $f }}</div>
      </div>
    @else
      <div class="card p-4 sm:p-6">
        <div class="flex items-start justify-between gap-2"><span class="{{ $lbl }}">{{ $l }}</span>
          <span class="w-8 h-8 rounded-xl bg-slate-100 text-slate-600 grid place-items-center shrink-0"><span class="material-symbols-outlined" style="font-size:18px">{{ $ic }}</span></span></div>
        <div class="{{ $num }} mt-2">{{ $v }}</div><div class="text-xs font-medium text-slate-400 mt-3">{{ $f }}</div>
      </div>
    @endif
  @endforeach
</div>

<div class="grid xl:grid-cols-3 gap-5 mb-5">
  <div class="card p-5 sm:p-6 xl:col-span-2 min-w-0">
    <h2 class="font-extrabold text-base mb-1">Pendapatan Harian</h2>
    <p class="text-xs text-slate-400 font-medium mb-4">Ketuk atau arahkan kursor ke bar untuk melihat detail.</p>
    <div class="overflow-x-auto no-scrollbar"><div class="flex items-end gap-1 h-48 border-b border-slate-100" style="min-width: {{ count($daily) * 22 }}px">
      @foreach ($daily as $d)
        <div class="flex-1 h-full flex items-end group relative outline-none" tabindex="0">
          <div class="w-full rounded-full {{ $d['total'] > 0 ? 'bg-lime-accent' : 'bg-slate-200' }}" style="height: {{ max(2, $d['total'] / $dailyMax * 100) }}%"></div>
          <div class="hidden group-hover:block group-focus:block absolute bottom-full mb-1 left-1/2 -translate-x-1/2 z-10 bg-[#181829] text-white text-[11px] rounded-xl px-2.5 py-1.5 whitespace-nowrap shadow-lg">{{ $d['date']->format('d M') }} · @rupiah($d['total']) · {{ $d['orders'] }} trx</div>
        </div>
      @endforeach
    </div>
    <div class="flex gap-1 mt-1.5" style="min-width: {{ count($daily) * 22 }}px">@foreach ($daily as $d)<div class="flex-1 text-center text-[9px] font-bold text-slate-400">{{ $d['date']->format('j') }}</div>@endforeach</div></div>
  </div>

  <div class="space-y-5 min-w-0">
    <div class="card p-5 sm:p-6"><h2 class="font-extrabold text-base mb-4">Metode Pembayaran</h2>
      @foreach ($pay as $k => $label)
        @php $row = $byPayment[$k] ?? null; $pct = $summary['revenue'] > 0 && $row ? round($row->total / $summary['revenue'] * 100) : 0; @endphp
        <div class="mb-3.5 last:mb-0"><div class="flex justify-between gap-2 text-sm"><span class="font-semibold">{{ $label }} <span class="text-slate-400 font-normal">({{ $row->c ?? 0 }})</span></span><b>@rupiah($row->total ?? 0)</b></div>
          <div class="h-2 bg-slate-100 rounded-full mt-1.5 overflow-hidden"><div class="h-full bg-lime-accent rounded-full" style="width: {{ $pct }}%"></div></div></div>
      @endforeach
    </div>
    <div class="card p-5 sm:p-6"><h2 class="font-extrabold text-base mb-3">Produk Terlaris</h2>
      @forelse ($topProducts as $p)
        <div class="flex justify-between gap-2 text-sm py-2 border-b border-slate-50 last:border-0"><span class="truncate">{{ $p->product_name }}</span><span class="text-slate-400 shrink-0">{{ $p->qty }}× · <b class="text-slate-900">@rupiah($p->revenue)</b></span></div>
      @empty <p class="text-sm text-slate-500">Belum ada data.</p> @endforelse
    </div>
  </div>
</div>

@if ($errors->has('void'))<div class="mb-4 rounded-2xl bg-red-50 border border-red-200 text-red-700 px-4 py-3 text-sm font-semibold">{{ $errors->first('void') }}</div>@endif

<div class="card overflow-hidden">
  <div class="px-5 sm:px-6 pt-5 pb-3 flex items-center justify-between"><h2 class="font-extrabold text-base">Transaksi</h2><span class="pill pill-slate">{{ $orders->total() }} transaksi</span></div>

  {{-- tabel (tablet+) --}}
  <div class="hidden md:block overflow-x-auto px-2">
    <table class="w-full">
      <thead><tr class="border-b border-slate-100"><th class="th">Invoice</th><th class="th">Pelanggan</th><th class="th">Kasir</th><th class="th">Bayar</th><th class="th text-right">Total</th><th class="th text-right">Aksi</th></tr></thead>
      <tbody class="divide-y divide-slate-50">
        @forelse ($orders as $o)
          <tr class="hover:bg-slate-50/70 transition-colors">
            <td class="td"><div class="font-bold text-slate-900">{{ $o->invoice_no }}</div><div class="text-xs text-slate-400">{{ $o->paid_at->format('d M Y, H:i') }}</div></td>
            <td class="td">{{ $o->customer->name ?? 'Walk-in' }}<div class="text-xs text-slate-400">{{ $o->order_type === 'dine_in' ? 'Dine-in' . ($o->table_no ? ' · T' . $o->table_no : '') : 'Takeaway' }}</div></td>
            <td class="td text-slate-600">{{ $o->cashier->name ?? '-' }}</td>
            <td class="td"><span class="pill pill-slate">{{ $pay[$o->payment_method] ?? $o->payment_method }}</span></td>
            <td class="td text-right font-extrabold">@rupiah($o->total)</td>
            <td class="td text-right whitespace-nowrap">
              <a href="{{ route('orders.receipt', $o) }}" target="_blank" class="btn-ghost !h-9 !px-3" title="Struk PDF"><span class="material-symbols-outlined">receipt_long</span></a>
              <form method="POST" action="{{ route('orders.void', $o) }}" class="inline" onsubmit="const r = prompt('Alasan membatalkan {{ $o->invoice_no }}? (stok akan dikembalikan)'); if (r === null) return false; this.reason.value = r; return true;">
                @csrf <input type="hidden" name="reason"><button class="btn-ghost !h-9 !px-3 text-red-600" title="Void"><span class="material-symbols-outlined">block</span></button>
              </form>
            </td>
          </tr>
        @empty
          <tr><td colspan="6" class="td text-center text-slate-500 py-10">Tidak ada transaksi pada periode ini.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>

  {{-- daftar kartu (mobile) --}}
  <ul class="md:hidden divide-y divide-slate-50 px-4">
    @forelse ($orders as $o)
      <li class="py-3.5">
        <div class="flex items-start gap-3">
          <span class="w-10 h-10 rounded-xl bg-[#181829] text-lime-accent grid place-items-center shrink-0"><span class="material-symbols-outlined" style="font-size:18px">receipt</span></span>
          <div class="min-w-0 flex-1">
            <div class="flex justify-between gap-2"><span class="font-bold text-sm truncate">{{ $o->customer->name ?? 'Walk-in' }}</span><span class="font-extrabold text-sm shrink-0">@rupiah($o->total)</span></div>
            <div class="text-[11px] text-slate-400">{{ $o->invoice_no }} · {{ $o->paid_at->format('d M, H:i') }}</div>
            <div class="text-[11px] text-slate-400">{{ $o->order_type === 'dine_in' ? 'Dine-in' . ($o->table_no ? ' · T' . $o->table_no : '') : 'Takeaway' }} · Kasir {{ $o->cashier->name ?? '-' }}</div>
            <span class="pill pill-slate mt-1.5">{{ $pay[$o->payment_method] ?? $o->payment_method }}</span>
          </div>
        </div>
        <div class="flex gap-2 mt-3">
          <a href="{{ route('orders.receipt', $o) }}" target="_blank" class="btn-secondary !h-10 flex-1 !px-3"><span class="material-symbols-outlined">receipt_long</span> Struk</a>
          <form method="POST" action="{{ route('orders.void', $o) }}" class="flex-1" onsubmit="const r = prompt('Alasan membatalkan {{ $o->invoice_no }}? (stok akan dikembalikan)'); if (r === null) return false; this.reason.value = r; return true;">
            @csrf <input type="hidden" name="reason"><button class="btn-danger !h-10 w-full !px-3"><span class="material-symbols-outlined">block</span> Void</button>
          </form>
        </div>
      </li>
    @empty
      <li class="py-10 text-center text-sm text-slate-500">Tidak ada transaksi pada periode ini.</li>
    @endforelse
  </ul>
  <div class="px-4 py-3 border-t border-slate-100 overflow-x-auto">{{ $orders->links() }}</div>
</div>

@if ($voids->count())
  <div class="card p-5 sm:p-6 mt-5"><h2 class="font-extrabold text-base mb-3">Transaksi dibatalkan (void)</h2>
    @foreach ($voids as $o)<div class="flex justify-between gap-3 text-sm py-2 border-b border-slate-50 last:border-0"><span class="min-w-0">{{ $o->invoice_no }} <span class="text-slate-400">· {{ $o->note }}</span></span><b class="line-through text-slate-400 shrink-0">@rupiah($o->total)</b></div>@endforeach
  </div>
@endif
@endsection
