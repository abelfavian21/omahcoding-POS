@extends('layouts.app')
@section('title', 'Laporan Penjualan')
@section('content')
@php $pay = \App\Models\Order::PAYMENTS; @endphp
<div class="flex flex-wrap items-end justify-between gap-4 mb-6">
  <div>
    <div class="text-xs text-slate-500 mb-1">Dashboard › <span class="text-brand-600 font-semibold">Laporan Penjualan</span></div>
    <h1 class="text-3xl font-extrabold tracking-tight">Laporan Penjualan</h1>
    <p class="text-sm text-slate-500">{{ $report->periodLabel() }}</p>
  </div>
  <div class="flex gap-2">
    <a href="{{ route('reports.pdf', $query) }}" class="btn bg-white border border-slate-200 text-slate-700 hover:bg-slate-50"><span class="material-symbols-outlined">picture_as_pdf</span> Export PDF</a>
    <a href="{{ route('reports.excel', $query) }}" class="btn-primary"><span class="material-symbols-outlined">table_view</span> Export Excel</a>
  </div>
</div>

<form method="GET" class="card p-4 mb-5 flex flex-wrap items-end gap-3">
  <div><label class="label">Dari</label><input type="date" name="from" value="{{ $query['from'] }}" class="input"></div>
  <div><label class="label">Sampai</label><input type="date" name="to" value="{{ $query['to'] }}" class="input"></div>
  <div><label class="label">Pembayaran</label>
    <select name="method" class="input"><option value="">Semua</option>@foreach ($pay as $k => $v)<option value="{{ $k }}" @selected($report->method === $k)>{{ $v }}</option>@endforeach</select></div>
  <button class="btn-primary">Terapkan</button>
  <div class="flex gap-2 ml-auto text-xs font-semibold">
    <a class="px-3 h-9 inline-flex items-center rounded-full bg-brand-50 text-brand-700" href="{{ route('reports.index', ['from' => today()->toDateString(), 'to' => today()->toDateString()]) }}">Hari ini</a>
    <a class="px-3 h-9 inline-flex items-center rounded-full bg-brand-50 text-brand-700" href="{{ route('reports.index', ['from' => today()->subDays(6)->toDateString(), 'to' => today()->toDateString()]) }}">7 hari</a>
    <a class="px-3 h-9 inline-flex items-center rounded-full bg-brand-50 text-brand-700" href="{{ route('reports.index') }}">Bulan ini</a>
  </div>
</form>

<div class="grid sm:grid-cols-2 xl:grid-cols-4 gap-5 mb-5">
  @foreach ([
    ['Total Pendapatan', 'Rp ' . number_format($summary['revenue'], 0, ',', '.'), 'Sudah termasuk pajak'],
    ['Jumlah Transaksi', $summary['orders'], 'Rata-rata Rp ' . number_format($summary['avg'], 0, ',', '.')],
    ['Item Terjual', $summary['items'], 'Diskon Rp ' . number_format($summary['discount'], 0, ',', '.')],
    ['Laba Kotor', 'Rp ' . number_format($summary['profit'], 0, ',', '.'), 'Penjualan − HPP − diskon'],
  ] as [$l, $v, $f])
    <div class="card p-5"><div class="text-[11px] font-bold uppercase tracking-wider text-slate-500">{{ $l }}</div>
      <div class="text-2xl font-extrabold mt-2">{{ $v }}</div><div class="text-xs text-slate-500 mt-2">{{ $f }}</div></div>
  @endforeach
</div>

<div class="grid xl:grid-cols-3 gap-5 mb-5">
  <div class="card p-5 xl:col-span-2">
    <h2 class="font-bold text-lg mb-4">Pendapatan Harian</h2>
    <div class="overflow-x-auto"><div class="flex items-end gap-1 h-48 border-b border-slate-100" style="min-width: {{ count($daily) * 22 }}px">
      @foreach ($daily as $d)
        <div class="flex-1 h-full flex items-end group relative">
          <div class="w-full rounded-t-md {{ $d['total'] > 0 ? 'bg-brand-600' : 'bg-slate-200' }}" style="height: {{ max(2, $d['total'] / $dailyMax * 100) }}%"></div>
          <div class="hidden group-hover:block absolute bottom-full mb-1 left-1/2 -translate-x-1/2 z-10 bg-slate-800 text-white text-[11px] rounded-lg px-2 py-1 whitespace-nowrap">{{ $d['date']->format('d M') }} · @rupiah($d['total']) · {{ $d['orders'] }} trx</div>
        </div>
      @endforeach
    </div>
    <div class="flex gap-1 mt-1" style="min-width: {{ count($daily) * 22 }}px">@foreach ($daily as $d)<div class="flex-1 text-center text-[9px] text-slate-500">{{ $d['date']->format('j') }}</div>@endforeach</div></div>
  </div>

  <div class="space-y-5">
    <div class="card p-5"><h2 class="font-bold text-lg mb-3">Metode Pembayaran</h2>
      @foreach ($pay as $k => $label)
        @php $row = $byPayment[$k] ?? null; $pct = $summary['revenue'] > 0 && $row ? round($row->total / $summary['revenue'] * 100) : 0; @endphp
        <div class="mb-3"><div class="flex justify-between text-sm"><span class="font-semibold">{{ $label }} <span class="text-slate-400 font-normal">({{ $row->c ?? 0 }})</span></span><b>@rupiah($row->total ?? 0)</b></div>
          <div class="h-1.5 bg-slate-100 rounded-full mt-1"><div class="h-full bg-brand-600 rounded-full" style="width: {{ $pct }}%"></div></div></div>
      @endforeach
    </div>
    <div class="card p-5"><h2 class="font-bold text-lg mb-3">Produk Terlaris</h2>
      @forelse ($topProducts as $p)
        <div class="flex justify-between text-sm py-1.5 border-b border-slate-50 last:border-0"><span class="truncate pr-2">{{ $p->product_name }}</span><span class="text-slate-500 shrink-0">{{ $p->qty }}× · <b class="text-slate-900">@rupiah($p->revenue)</b></span></div>
      @empty <p class="text-sm text-slate-500">Belum ada data.</p> @endforelse
    </div>
  </div>
</div>

@if ($errors->has('void'))<div class="mb-4 rounded-xl bg-red-50 border border-red-200 text-red-700 px-4 py-3 text-sm">{{ $errors->first('void') }}</div>@endif

<div class="card overflow-hidden">
  <div class="px-5 py-4 flex items-center justify-between"><h2 class="font-bold text-lg">Transaksi</h2><span class="pill pill-slate">{{ $orders->total() }} transaksi</span></div>
  <div class="overflow-x-auto">
    <table class="w-full">
      <thead class="bg-brand-50/50"><tr><th class="th">Invoice</th><th class="th">Pelanggan</th><th class="th">Kasir</th><th class="th">Bayar</th><th class="th text-right">Total</th><th class="th text-right">Aksi</th></tr></thead>
      <tbody class="divide-y divide-slate-50">
        @forelse ($orders as $o)
          <tr>
            <td class="td"><div class="font-bold text-brand-600">{{ $o->invoice_no }}</div><div class="text-xs text-slate-500">{{ $o->paid_at->format('d M Y, H:i') }}</div></td>
            <td class="td">{{ $o->customer->name ?? 'Walk-in' }}<div class="text-xs text-slate-500">{{ $o->order_type === 'dine_in' ? 'Dine-in' . ($o->table_no ? ' · T' . $o->table_no : '') : 'Takeaway' }}</div></td>
            <td class="td text-slate-600">{{ $o->cashier->name ?? '-' }}</td>
            <td class="td"><span class="pill pill-slate">{{ $pay[$o->payment_method] ?? $o->payment_method }}</span></td>
            <td class="td text-right font-bold">@rupiah($o->total)</td>
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
  <div class="px-4 py-3 border-t border-slate-100">{{ $orders->links() }}</div>
</div>

@if ($voids->count())
  <div class="card p-5 mt-5"><h2 class="font-bold mb-3">Transaksi dibatalkan (void)</h2>
    @foreach ($voids as $o)<div class="flex justify-between text-sm py-1.5 border-b border-slate-50 last:border-0"><span>{{ $o->invoice_no }} <span class="text-slate-400">· {{ $o->note }}</span></span><b class="line-through text-slate-400">@rupiah($o->total)</b></div>@endforeach
  </div>
@endif
@endsection
