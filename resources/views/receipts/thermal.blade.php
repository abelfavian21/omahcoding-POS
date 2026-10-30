<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Struk {{ $order->invoice_no }}</title>
@php
  // lebar area cetak: kertas 58 mm -> 48 mm, kertas 80 mm -> 72 mm (sisanya margin fisik printer)
  $w = $paper === 58 ? 48 : 72;
  $rp = fn ($n) => number_format((float) $n, 0, ',', '.');
@endphp
<style>
  @page { margin: 0; }
  * { box-sizing: border-box; }
  html, body { margin: 0; padding: 0; background: #fff; }
  body { width: {{ $w }}mm; padding: 2mm 1mm 12mm; font-family: 'Courier New', Courier, monospace; font-size: {{ $paper === 58 ? 11 : 12 }}px; line-height: 1.3; color: #000; }
  .c { text-align: center; } .b { font-weight: bold; }
  .store { font-size: {{ $paper === 58 ? 14 : 16 }}px; font-weight: bold; }
  hr { border: 0; border-top: 1px dashed #000; margin: 5px 0; }
  .row { display: flex; justify-content: space-between; gap: 6px; }
  .row > span:last-child { text-align: right; white-space: nowrap; }
  .sub { padding-left: 8px; font-size: 90%; }
  .total { font-size: 115%; font-weight: bold; }
  .void { border: 1px solid #000; text-align: center; font-weight: bold; padding: 3px; margin: 4px 0; }
  @media screen { body { margin: 8px auto; box-shadow: 0 0 0 1px #ddd; } }
</style>
</head>
<body>
  <div class="c">
    <div class="store">{{ $business->name ?? config('pos.store_name') }}</div>
    @if ($business->location ?? config('pos.store_address'))<div>{{ $business->location ?? config('pos.store_address') }}</div>@endif
    @if ($business->phone ?? config('pos.store_phone'))<div>Telp {{ $business->phone ?? config('pos.store_phone') }}</div>@endif
  </div>
  <hr>
  @if ($order->status === 'void')<div class="void">*** TRANSAKSI DIBATALKAN (VOID) ***</div>@endif
  <div class="row"><span>No</span><span>{{ $order->invoice_no }}</span></div>
  <div class="row"><span>Tanggal</span><span>{{ $order->paid_at?->format('d/m/Y H:i') }}</span></div>
  <div class="row"><span>Kasir</span><span>{{ $order->cashier->name ?? '-' }}</span></div>
  <div class="row"><span>Tipe</span><span>{{ $order->order_type === 'dine_in' ? 'Dine-in' . ($order->table_no ? ' - Meja ' . $order->table_no : '') : 'Takeaway' }}</span></div>
  @if ($order->customer)<div class="row"><span>Pelanggan</span><span>{{ $order->customer->name }}</span></div>@endif
  <hr>
  @foreach ($order->items as $i)
    <div class="b">{{ $i->product_name }}</div>
    @if (! empty($i->variants_json))<div class="sub">+ {{ implode(', ', array_column($i->variants_json, 'name')) }}</div>@endif
    <div class="row"><span>{{ $i->qty }} x {{ $rp($i->price) }}</span><span>{{ $rp($i->subtotal) }}</span></div>
  @endforeach
  <hr>
  <div class="row"><span>Subtotal</span><span>{{ $rp($order->subtotal) }}</span></div>
  @if ($order->discount > 0)<div class="row"><span>Diskon</span><span>-{{ $rp($order->discount) }}</span></div>@endif
  <div class="row"><span>{{ $business->tax_label ?? config('pos.tax_label') }}</span><span>{{ $rp($order->tax) }}</span></div>
  <div class="row total"><span>TOTAL</span><span>Rp {{ $rp($order->total) }}</span></div>
  <div class="row"><span>Bayar ({{ \App\Models\Order::PAYMENTS[$order->payment_method] ?? $order->payment_method }})</span><span>{{ $rp($order->paid_amount) }}</span></div>
  <div class="row"><span>Kembali</span><span>{{ $rp($order->change_amount) }}</span></div>
  <hr>
  @if ($order->customer && $order->status === 'paid')<div class="c">Poin Anda: {{ number_format($order->customer->points) }}</div>@endif
  <div class="c" style="margin-top:4px">{{ $business->receipt_footer ?? config('pos.receipt_footer') }}</div>

  @if ($autoPrint)
  <script>
    // Dibuka di iframe tersembunyi oleh POS: tunggu render, lalu buka dialog cetak.
    window.addEventListener('load', function () { setTimeout(function () { window.focus(); window.print(); }, 150); });
  </script>
  @endif
</body>
</html>
