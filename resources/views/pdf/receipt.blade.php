<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<style>
  @page { margin: 8px 10px; }
  body { font-family: 'DejaVu Sans Mono', monospace; font-size: 8.5px; color: #000; line-height: 1.35; }
  .c { text-align: center; } .r { text-align: right; } .b { font-weight: bold; }
  .store { font-size: 12px; font-weight: bold; }
  hr { border: 0; border-top: 1px dashed #000; margin: 5px 0; }
  table { width: 100%; border-collapse: collapse; }
  td { vertical-align: top; padding: 1px 0; }
  .total td { font-size: 10.5px; font-weight: bold; padding-top: 3px; }
  .void { border: 1px solid #000; text-align: center; font-weight: bold; padding: 3px; margin: 4px 0; }
</style>
</head>
<body>
  <div class="c">
    <div class="store">{{ (auth()->user()?->business?->name ?? config('pos.store_name')) }}</div>
    <div>{{ auth()->user()?->business?->location ?? config('pos.store_address') }}</div>
    <div>Telp {{ auth()->user()?->business?->phone ?? config('pos.store_phone') }}</div>
  </div>
  <hr>
  @if ($order->status === 'void')<div class="void">*** TRANSAKSI DIBATALKAN (VOID) ***</div>@endif
  <table>
    <tr><td>No</td><td class="r">{{ $order->invoice_no }}</td></tr>
    <tr><td>Tanggal</td><td class="r">{{ $order->paid_at?->format('d/m/Y H:i') }}</td></tr>
    <tr><td>Kasir</td><td class="r">{{ $order->cashier->name ?? '-' }}</td></tr>
    <tr><td>Tipe</td><td class="r">{{ $order->order_type === 'dine_in' ? 'Dine-in' . ($order->table_no ? ' · Meja ' . $order->table_no : '') : 'Takeaway' }}</td></tr>
    @if ($order->customer)<tr><td>Pelanggan</td><td class="r">{{ $order->customer->name }}</td></tr>@endif
  </table>
  <hr>
  <table>
    @foreach ($order->items as $i)
      <tr>
        <td colspan="2" class="b">
          {{ $i->product_name }}
          @if (!empty($i->variants_json))
            <div style="font-size:7.5px;font-weight:normal;color:#444">
              + {{ implode(', ', array_column($i->variants_json, 'name')) }}
            </div>
          @endif
        </td>
      </tr>
      <tr><td>{{ $i->qty }} x {{ number_format($i->price, 0, ',', '.') }}</td><td class="r">{{ number_format($i->subtotal, 0, ',', '.') }}</td></tr>
    @endforeach
  </table>
  <hr>
  <table>
    <tr><td>Subtotal</td><td class="r">{{ number_format($order->subtotal, 0, ',', '.') }}</td></tr>
    @if ($order->discount > 0)<tr><td>Diskon</td><td class="r">-{{ number_format($order->discount, 0, ',', '.') }}</td></tr>@endif
    <tr><td>{{ $order->business?->tax_label ?? config('pos.tax_label') }}</td><td class="r">{{ number_format($order->tax, 0, ',', '.') }}</td></tr>
    <tr class="total"><td>TOTAL</td><td class="r">Rp {{ number_format($order->total, 0, ',', '.') }}</td></tr>
    <tr><td>Bayar ({{ \App\Models\Order::PAYMENTS[$order->payment_method] ?? $order->payment_method }})</td><td class="r">{{ number_format($order->paid_amount, 0, ',', '.') }}</td></tr>
    <tr><td>Kembali</td><td class="r">{{ number_format($order->change_amount, 0, ',', '.') }}</td></tr>
  </table>
  <hr>
  @if ($order->customer && $order->status === 'paid')
    <div class="c">Poin Anda: {{ number_format($order->customer->points) }}</div>
  @endif
  <div class="c" style="margin-top:4px">{{ $order->business?->receipt_footer ?? config('pos.receipt_footer') }}</div>
</body>
</html>
