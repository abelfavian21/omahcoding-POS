<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Models\Business;
use App\Models\Order;
use App\Models\User;

class DashboardController extends Controller
{
    public function index()
    {
        $now = now();

        $byStatus = Business::selectRaw('status, count(*) as c')->groupBy('status')->pluck('c', 'status');

        $monthOrders = fn () => Order::withoutGlobalScope('business')
            ->where('status', 'paid')->where('paid_at', '>=', $now->copy()->startOfMonth());

        $top = $monthOrders()->selectRaw('business_id, count(*) as orders, sum(total) as revenue')
            ->groupBy('business_id')->orderByDesc('revenue')->limit(5)->get();
        $names = Business::whereIn('id', $top->pluck('business_id'))->pluck('name', 'id');

        return view('platform.dashboard', [
            'total' => $byStatus->sum(),
            'byStatus' => $byStatus,
            'signups7' => Business::where('created_at', '>=', $now->copy()->subDays(7))->count(),
            'signups30' => Business::where('created_at', '>=', $now->copy()->subDays(30))->count(),
            'activeUsers' => User::withoutGlobalScope('business')->where('is_active', true)->count(),
            'monthOrders' => $monthOrders()->count(),
            'monthRevenue' => (float) $monthOrders()->sum('total'),
            'top' => $top->map(fn ($r) => (object) ['name' => $names[$r->business_id] ?? '(toko dihapus)', 'id' => $r->business_id, 'orders' => $r->orders, 'revenue' => (float) $r->revenue]),
            'recent' => Business::with('category')->latest()->limit(8)->get(),
        ]);
    }
}
