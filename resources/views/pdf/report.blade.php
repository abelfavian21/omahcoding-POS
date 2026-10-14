<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<style>
  @page { margin: 28px 30px; }
  body { font-family: 'DejaVu Sans', sans-serif; font-size: 9px; color: #0f172a; }
  h1 { font-size: 17px; margin: 0; } h2 { font-size: 11px; margin: 16px 0 5px; color: #1d4ed8; }
  .muted { color: #64748b; }
  .kpi { width: 100%; border-collapse: separate; border-spacing: 6px 0; margin: 10px -6px 0; }
  .kpi td { background: #eff6ff; border-radius: 6px; padding: 8px 10px; width: 25%; }
  .kpi .v { font-size: 13px; font-weight: bold; margin-top: 2px; }
  table.t { width: 100%; border-collapse: collapse; }
  table.t th { background: #2563eb; color: #fff; padding: 5px 6px; text-align: left; font-size: 8.5px; }
  table.t td { padding: 4px 6px; border-bottom: 1px solid #e2e8f0; }
  table.t tr:nth-child(even) td { background: #f8fafc; }
  thead { display: table-header-group; }
  tr { page-break-inside: avoid; }
  .r { text-align: right; } .tot td { font-weight: bold; background: #eff6ff !important; border-top: 1px solid #2563eb; }
</style>
</head>
<body>
  <h1>Laporan Penjualan</h1>
  <div class="muted">{{ (auth()->user()?->business?->name ?? config('pos.store_name')) }} · {{ $report->periodLabel() }}</div>
  <div class="muted">Dicetak {{ now()->translatedFormat('d M Y H:i') }}</div>

  <table class="kpi"><tr>
    <td><div class="muted">Total pendapatan</div><div class="v">Rp {{ number_format($summary['revenue'], 0, ',', '.') }}</div></td>
    <td><div class="muted">Jumlah transaksi</div><div class="v">{{ $summary['orders'] }}</div></td>
    <td><div class="muted">Item terjual</div><div class="v">{{ $summary['items'] }}</div></td>
    <td><div class="muted">Laba kotor</div><div class="v">Rp {{ number_format($summary['profit'], 0, ',', '.') }}</div></td>
  </tr></table>

  <h2>Ringkasan</h2>
  <table class="t">
    <tr><td>Subtotal penjualan</td><td class="r">Rp {{ number_format($summary['subtotal'], 0, ',', '.') }}</td>
        <td>Rata-rata / transaksi</td><td class="r">Rp {{ number_format($summary['avg'], 0, ',', '.') }}</td></tr>
    <tr><td>Diskon</td><td class="r">Rp {{ number_format($summary['discount'], 0, ',', '.') }}</td>
        <td>Pajak</td><td class="r">Rp {{ number_format($summary['tax'], 0, ',', '.') }}</td></tr>
  </table>

  <h2>Per Metode Pembayaran</h2>
  <table class="t">
    <thead><tr><th>Metode</th><th class="r">Transaksi</th><th class="r">Total</th></tr></thead>
    @foreach ($byPayment as $p)
      <tr><td>{{ \App\Models\Order::PAYMENTS[$p->payment_method] ?? $p->payment_method }}</td><td class="r">{{ $p->c }}</td><td class="r">Rp {{ number_format($p->total, 0, ',', '.') }}</td></tr>
    @endforeach
  </table>

  <h2>Per Produk</h2>
  <table class="t">
    <thead><tr><th>Produk</th><th class="r">Qty</th><th class="r">Penjualan</th><th class="r">Laba kotor</th></tr></thead>
    @foreach ($products as $p)
      <tr><td>{{ $p->product_name }}</td><td class="r">{{ $p->qty }}</td><td class="r">Rp {{ number_format($p->revenue, 0, ',', '.') }}</td><td class="r">Rp {{ number_format($p->revenue - $p->cogs, 0, ',', '.') }}</td></tr>
    @endforeach
  </table>

  <h2>Daftar Transaksi</h2>
  <table class="t">
    <thead><tr><th>Invoice</th><th>Waktu</th><th>Kasir</th><th>Pelanggan</th><th>Bayar</th><th class="r">Diskon</th><th class="r">Pajak</th><th class="r">Total</th></tr></thead>
    @foreach ($orders as $o)
      <tr>
        <td>{{ $o->invoice_no }}</td><td>{{ $o->paid_at->format('d/m H:i') }}</td><td>{{ $o->cashier->name ?? '-' }}</td>
        <td>{{ $o->customer->name ?? 'Walk-in' }}</td><td>{{ \App\Models\Order::PAYMENTS[$o->payment_method] ?? $o->payment_method }}</td>
        <td class="r">{{ number_format($o->discount, 0, ',', '.') }}</td><td class="r">{{ number_format($o->tax, 0, ',', '.') }}</td><td class="r">{{ number_format($o->total, 0, ',', '.') }}</td>
      </tr>
    @endforeach
    <tr class="tot"><td colspan="5">TOTAL</td><td class="r">{{ number_format($orders->sum('discount'), 0, ',', '.') }}</td><td class="r">{{ number_format($orders->sum('tax'), 0, ',', '.') }}</td><td class="r">{{ number_format($orders->sum('total'), 0, ',', '.') }}</td></tr>
  </table>
</body>
</html>
