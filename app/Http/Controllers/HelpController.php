<?php

namespace App\Http\Controllers;

/** Halaman panduan penggunaan (How to Use). Dapat dibuka admin dan kasir; bagian konfigurasi hanya tampil untuk admin. */
class HelpController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $business = $user->business;

        return view('help.index', [
            'isAdmin' => $user->isAdmin(),
            'shiftOn' => (bool) ($business?->requiresShift() ?? true),
            'storeCode' => $business?->code,
            'taxPct' => round(($business?->taxRate() ?? config('pos.tax_rate')) * 100, 2),
            'cashierMaxDiscount' => (float) config('pos.cashier_max_discount_pct', 10),
            'pointPer' => \App\Models\Customer::POINT_PER,
            'tiers' => \App\Models\Customer::TIER_THRESHOLDS,
        ]);
    }
}
