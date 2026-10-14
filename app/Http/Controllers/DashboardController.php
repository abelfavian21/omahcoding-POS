<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;

class DashboardController extends Controller
{
    public function index()
    {
        $today = Order::paid()->whereDate('paid_at', today())->get();
        $yesterday = Order::paid()->whereDate('paid_at', today()->subDay())->get();

        $revenue = (float) $today->sum('total');
        $revenueY = (float) $yesterday->sum('total');
        $orders = $today->count();
        $ordersY = $yesterday->count();
        $avg = $orders ? $revenue / $orders : 0;
        $avgY = $ordersY ? $revenueY / $ordersY : 0;

        // Grafik per jam (08:00 - 20:00), dine-in vs takeaway
        $chart = collect(range(8, 20))->map(function ($h) use ($today) {
            $slice = $today->filter(fn ($o) => $o->paid_at->hour === $h);
            return [
                'label' => sprintf('%02d:00', $h),
                'dine' => (float) $slice->where('order_type', 'dine_in')->sum('total'),
                'take' => (float) $slice->where('order_type', 'takeaway')->sum('total'),
                'count' => $slice->count(),
            ];
        });
        $chartMax = max(1, $chart->max(fn ($r) => max($r['dine'], $r['take'])));

        $topItems = OrderItem::query()
            ->selectRaw('product_id, product_name, SUM(qty) as sold, SUM(subtotal) as revenue')
            ->whereHas('order', fn ($q) => $q->paid()->where('paid_at', '>=', now()->subDays(7)))
            ->groupBy('product_id', 'product_name')
            ->orderByDesc('sold')
            ->limit(5)
            ->get()
            ->each(fn ($i) => $i->setRelation('product', Product::find($i->product_id)));

        return view('dashboard', [
            'revenue' => $revenue,
            'orders' => $orders,
            'avg' => $avg,
            'revenueTrend' => $this->trend($revenue, $revenueY),
            'ordersTrend' => $this->trend($orders, $ordersY),
            'avgTrend' => $this->trend($avg, $avgY),
            'revenueY' => $revenueY,
            'chart' => $chart,
            'chartMax' => $chartMax,
            'dineTotal' => (float) $today->where('order_type', 'dine_in')->sum('total'),
            'takeTotal' => (float) $today->where('order_type', 'takeaway')->sum('total'),
            'topItems' => $topItems,
            'recent' => Order::with(['customer', 'items'])->paid()->latest('paid_at')->limit(5)->get(),
            'lowStock' => Product::active()->lowStock()->orderBy('stock')->limit(5)->get(),
            'staff' => User::where('is_active', true)->orderBy('role')->get(),
        ]);
    }

    private function trend(float|int $now, float|int $prev): float
    {
        if ($prev == 0) return $now > 0 ? 100.0 : 0.0;
        return round((($now - $prev) / $prev) * 100, 1);
    }
}
