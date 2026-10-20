<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;

class KitchenController extends Controller
{
    public function index()
    {
        return view('kitchen.index');
    }

    public function feed()
    {
        $orders = Order::kitchenOpen()
            ->with('items')
            ->orderBy('paid_at')
            ->get()
            ->map(fn (Order $o) => $this->payload($o));

        $held = Order::held()->with('items')->latest()->limit(8)->get()
            ->map(fn (Order $o) => $this->payload($o));

        return response()->json(['orders' => $orders, 'held' => $held]);
    }

    public function update(Request $request, Order $order)
    {
        $data = $request->validate([
            'kitchen_status' => ['required', 'in:queued,cooking,ready,served'],
        ]);

        if ($order->status !== 'paid') {
            return response()->json(['message' => 'Hanya pesanan lunas yang masuk dapur.'], 422);
        }

        $order->update(['kitchen_status' => $data['kitchen_status']]);

        return response()->json($this->payload($order->fresh('items')));
    }

    private function payload(Order $o): array
    {
        return [
            'id' => $o->id,
            'invoice_no' => $o->invoice_no,
            'status' => $o->status,
            'kitchen_status' => $o->kitchen_status,
            'order_type' => $o->order_type,
            'table_no' => $o->table_no,
            'paid_at' => $o->paid_at?->format('H:i'),
            'created_at' => $o->created_at?->format('H:i'),
            'items' => $o->items->map(fn ($i) => [
                'name' => $i->product_name,
                'qty' => $i->qty,
            ]),
        ];
    }
}
