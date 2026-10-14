<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use RuntimeException;

class OrderController extends Controller
{
    /** Struk pembelian PDF (kertas thermal 80mm). ?download=1 untuk unduh langsung. */
    public function receipt(Request $request, Order $order)
    {
        $order->load(['items', 'customer', 'cashier']);

        // tinggi kertas menyesuaikan jumlah item (satuan point: 1mm = 2.835pt)
        $height = 380 + $order->items->count() * 38;
        $pdf = Pdf::loadView('pdf.receipt', ['order' => $order])->setPaper([0, 0, 226.77, $height], 'portrait');
        $file = 'struk-' . $order->invoice_no . '.pdf';

        return $request->boolean('download') ? $pdf->download($file) : $pdf->stream($file);
    }

    public function void(Request $request, Order $order)
    {
        $request->validate(['reason' => ['nullable', 'string', 'max:150']]);

        try {
            $order->void($request->user()->id, $request->reason);
        } catch (RuntimeException $e) {
            return back()->withErrors(['void' => $e->getMessage()]);
        }

        return back()->with('success', "Transaksi {$order->invoice_no} dibatalkan, stok dikembalikan.");
    }
}
