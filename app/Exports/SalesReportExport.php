<?php

namespace App\Exports;

use App\Models\Order;
use App\Services\SalesReport;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

/** Excel 3 sheet: Ringkasan, Transaksi, Per Produk */
class SalesReportExport implements WithMultipleSheets
{
    public function __construct(private SalesReport $report) {}

    public function sheets(): array
    {
        $r = $this->report;
        $s = $r->summary();
        $store = auth()->user()?->business?->name ?? config('pos.store_name');
        $period = 'Periode: ' . $r->periodLabel();
        $rp = '"Rp "#,##0';

        // --- Ringkasan ---
        $summary = new TableSheet('Ringkasan', "Laporan Penjualan · {$store}", $period,
            ['Keterangan', 'Nilai'],
            [
                ['Jumlah transaksi', $s['orders']],
                ['Item terjual', $s['items']],
                ['Subtotal penjualan (Rp)', $s['subtotal']],
                ['Diskon (Rp)', $s['discount']],
                ['Pajak (Rp)', $s['tax']],
                ['Total pendapatan (Rp)', $s['revenue']],
                ['Rata-rata per transaksi (Rp)', round($s['avg'])],
                ['Laba kotor (Rp)', $s['profit']],
            ],
            ['B' => '#,##0'],
        );

        // --- Transaksi ---
        $orders = $r->orders();
        $rows = $orders->map(fn (Order $o) => [
            $o->invoice_no,
            $o->paid_at->format('d/m/Y H:i'),
            $o->cashier->name ?? '-',
            $o->customer->name ?? 'Walk-in',
            $o->order_type === 'dine_in' ? 'Dine-in' : 'Takeaway',
            $o->table_no ?: '-',
            Order::PAYMENTS[$o->payment_method] ?? $o->payment_method,
            (float) $o->subtotal, (float) $o->discount, (float) $o->tax, (float) $o->total,
        ])->all();
        $n = count($rows);
        $tx = new TableSheet('Transaksi', "Daftar Transaksi · {$store}", $period,
            ['No. Invoice', 'Tanggal', 'Kasir', 'Pelanggan', 'Tipe', 'Meja', 'Pembayaran', 'Subtotal', 'Diskon', 'Pajak', 'Total'],
            $rows,
            ['H' => $rp, 'I' => $rp, 'J' => $rp, 'K' => $rp],
            $n ? ['TOTAL', '', '', '', '', '', '', "=SUM(H5:H" . (4 + $n) . ")", "=SUM(I5:I" . (4 + $n) . ")", "=SUM(J5:J" . (4 + $n) . ")", "=SUM(K5:K" . (4 + $n) . ")"] : null,
        );

        // --- Per produk ---
        $products = $r->byProduct();
        $prow = $products->map(fn ($p) => [$p->product_name, (int) $p->qty, (float) $p->revenue, (float) $p->cogs, (float) $p->revenue - (float) $p->cogs])->values()->all();
        $pn = count($prow);
        $prod = new TableSheet('Per Produk', "Penjualan per Produk · {$store}", $period,
            ['Produk', 'Qty terjual', 'Penjualan', 'HPP', 'Laba kotor'],
            $prow,
            ['B' => '#,##0', 'C' => $rp, 'D' => $rp, 'E' => $rp],
            $pn ? ['TOTAL', "=SUM(B5:B" . (4 + $pn) . ")", "=SUM(C5:C" . (4 + $pn) . ")", "=SUM(D5:D" . (4 + $pn) . ")", "=SUM(E5:E" . (4 + $pn) . ")"] : null,
        );

        return [$summary, $tx, $prod];
    }
}
