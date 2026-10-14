<?php

namespace App\Http\Controllers;

use App\Exports\SalesReportExport;
use App\Models\Order;
use App\Services\SalesReport;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $report = SalesReport::fromRequest($request);
        $daily = $report->daily();

        $orders = $report->query()->with(['customer', 'cashier'])->latest('paid_at')->paginate(15)->withQueryString();

        // transaksi void pada periode (ditampilkan terpisah agar jelas)
        $voids = Order::where('status', 'void')->whereBetween('updated_at', [$report->from, $report->to])->latest('updated_at')->limit(5)->get();

        return view('reports.index', [
            'report' => $report,
            'summary' => $report->summary(),
            'daily' => $daily,
            'dailyMax' => max(1, collect($daily)->max('total')),
            'orders' => $orders,
            'byPayment' => $report->byPayment()->keyBy('payment_method'),
            'topProducts' => $report->byProduct()->take(8),
            'voids' => $voids,
            'query' => $request->only(['from', 'to', 'method']) + ['from' => $report->from->toDateString(), 'to' => $report->to->toDateString()],
        ]);
    }

    public function pdf(Request $request)
    {
        $report = SalesReport::fromRequest($request);

        $pdf = Pdf::loadView('pdf.report', [
            'report' => $report,
            'summary' => $report->summary(),
            'orders' => $report->orders(),
            'byPayment' => $report->byPayment(),
            'products' => $report->byProduct(),
        ])->setPaper('a4', 'portrait');

        return $pdf->download('laporan-penjualan-' . $report->from->format('Ymd') . '-' . $report->to->format('Ymd') . '.pdf');
    }

    public function excel(Request $request)
    {
        $report = SalesReport::fromRequest($request);

        return Excel::download(
            new SalesReportExport($report),
            'laporan-penjualan-' . $report->from->format('Ymd') . '-' . $report->to->format('Ymd') . '.xlsx'
        );
    }
}
