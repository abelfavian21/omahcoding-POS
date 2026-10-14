<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderItem;
use Carbon\Carbon;
use Illuminate\Http\Request;

class SalesReport
{
    public function __construct(
        public Carbon $from,
        public Carbon $to,
        public ?string $method = null,
    ) {}

    public static function fromRequest(Request $r): static
    {
        $from = $r->date('from') ?? now()->startOfMonth();
        $to = $r->date('to') ?? now();
        if ($from->gt($to)) [$from, $to] = [$to, $from];

        $method = in_array($r->input('method'), array_keys(Order::PAYMENTS), true) ? $r->input('method') : null;

        return new static($from->copy()->startOfDay(), $to->copy()->endOfDay(), $method);
    }

    public function periodLabel(): string
    {
        return $this->from->translatedFormat('d M Y') . ' – ' . $this->to->translatedFormat('d M Y')
            . ($this->method ? ' · ' . Order::PAYMENTS[$this->method] : '');
    }

    /** Query transaksi yang SUDAH dibayar (void tidak ikut dihitung). */
    public function query()
    {
        return Order::paid()
            ->whereBetween('paid_at', [$this->from, $this->to])
            ->when($this->method, fn ($q, $m) => $q->where('payment_method', $m));
    }

    public function orders()
    {
        return $this->query()->with(['customer', 'cashier'])->orderBy('paid_at')->get();
    }

    public function summary(): array
    {
        $o = $this->query()
            ->selectRaw('COUNT(*) c, COALESCE(SUM(total),0) total, COALESCE(SUM(subtotal),0) subtotal, COALESCE(SUM(discount),0) discount, COALESCE(SUM(tax),0) tax')
            ->first();

        $i = OrderItem::query()
            ->whereIn('order_id', $this->query()->select('id'))
            ->selectRaw('COALESCE(SUM(qty),0) qty, COALESCE(SUM(subtotal),0) rev, COALESCE(SUM(cost_price * qty),0) cogs')
            ->first();

        $orders = (int) $o->c;

        return [
            'orders' => $orders,
            'revenue' => (float) $o->total,
            'subtotal' => (float) $o->subtotal,
            'discount' => (float) $o->discount,
            'tax' => (float) $o->tax,
            'avg' => $orders ? (float) $o->total / $orders : 0,
            'items' => (int) $i->qty,
            // laba kotor = penjualan item - HPP - diskon (pajak bukan pendapatan toko)
            'profit' => (float) $i->rev - (float) $i->cogs - (float) $o->discount,
            'voided' => Order::where('status', 'void')->whereBetween('updated_at', [$this->from, $this->to])->count(),
        ];
    }

    public function daily(): array
    {
        $rows = $this->query()->get(['paid_at', 'total'])->groupBy(fn ($o) => $o->paid_at->format('Y-m-d'));
        $out = [];
        for ($d = $this->from->copy(); $d->lte($this->to); $d->addDay()) {
            $k = $d->format('Y-m-d');
            $out[] = ['date' => $d->copy(), 'orders' => ($rows[$k] ?? collect())->count(), 'total' => (float) ($rows[$k] ?? collect())->sum('total')];
            if (count($out) >= 370) break;
        }
        return $out;
    }

    public function byPayment()
    {
        return $this->query()->selectRaw('payment_method, COUNT(*) c, SUM(total) total')->groupBy('payment_method')->get();
    }

    public function byProduct()
    {
        return OrderItem::query()
            ->whereIn('order_id', $this->query()->select('id'))
            ->selectRaw('product_name, SUM(qty) qty, SUM(subtotal) revenue, SUM(cost_price * qty) cogs')
            ->groupBy('product_name')
            ->orderByDesc('qty')
            ->get();
    }
}
