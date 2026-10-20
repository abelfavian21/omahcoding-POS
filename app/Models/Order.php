<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use \App\Models\Concerns\BelongsToBusiness;

    protected $fillable = [
        'invoice_no', 'user_id', 'customer_id', 'order_type', 'table_no',
        'subtotal', 'discount', 'tax', 'total', 'payment_method',
        'paid_amount', 'change_amount', 'status', 'note', 'paid_at',
        'cash_shift_id', 'points_redeemed', 'points_earned', 'kitchen_status',
        'idempotency_key', 'payment_ref',
    ];   // voided_by / voided_at / void_reason sengaja tidak fillable: hanya diisi oleh void()

    protected $casts = [
        'subtotal' => 'decimal:2', 'discount' => 'decimal:2', 'tax' => 'decimal:2',
        'total' => 'decimal:2', 'paid_amount' => 'decimal:2', 'change_amount' => 'decimal:2',
        'paid_at' => 'datetime', 'voided_at' => 'datetime',
    ];

    public const PAYMENTS = ['cash' => 'Tunai', 'qris' => 'QRIS', 'card' => 'Kartu'];

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function cashier()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function scopePaid($q)
    {
        return $q->where('status', 'paid');
    }

    public function scopeHeld($q)
    {
        return $q->where('status', 'held');
    }

    public function scopeKitchenOpen($q)
    {
        return $q->where('status', 'paid')->whereIn('kitchen_status', ['queued', 'cooking', 'ready']);
    }

    /** Transaksi yang dibatalkan pada periode (data lama tanpa voided_at memakai updated_at). */
    public function scopeVoidedBetween($q, $from, $to)
    {
        return $q->where('status', 'void')->where(function ($w) use ($from, $to) {
            $w->whereBetween('voided_at', [$from, $to])
              ->orWhere(fn ($x) => $x->whereNull('voided_at')->whereBetween('updated_at', [$from, $to]));
        });
    }

    public function voider()
    {
        return $this->belongsTo(User::class, 'voided_by');
    }

    /**
     * Nomor berikutnya per bisnis per hari. Dihitung NUMERIK (dulu urut string:
     * setelah 9999 hasilnya salah) dan dilindungi unique(business_id, invoice_no);
     * CheckoutService mengulang bila terjadi tabrakan.
     */
    public static function nextInvoiceNo(): string
    {
        $prefix = 'INV-' . now()->format('Ymd') . '-';
        $last = static::where('invoice_no', 'like', $prefix . '%')
            ->orderByRaw('LENGTH(invoice_no) DESC')->orderByDesc('invoice_no')
            ->value('invoice_no');
        $seq = $last ? ((int) substr($last, strlen($prefix))) + 1 : 1;

        return $prefix . str_pad((string) $seq, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Batalkan transaksi. Aman terhadap klik ganda / dua admin bersamaan:
     * baris dikunci, status dicek ulang di dalam transaksi, hanya order LUNAS yang bisa di-void.
     */
    public function void(int $userId, string $reason): void
    {
        \Illuminate\Support\Facades\DB::transaction(function () use ($userId, $reason) {
            $order = static::whereKey($this->getKey())->lockForUpdate()->firstOrFail();

            if ($order->status === 'void') {
                throw new \RuntimeException('Transaksi sudah dibatalkan.');
            }
            if ($order->status !== 'paid') {
                throw new \RuntimeException('Hanya transaksi yang sudah lunas yang bisa dibatalkan.');
            }

            $order->load(['items', 'customer']);

            foreach ($order->items as $item) {
                if ($item->product) {
                    $item->product->adjustStock($item->qty, 'void', "Void {$order->invoice_no}", $userId, $order->id);
                }
            }

            $order->forceFill([
                'status' => 'void',
                'voided_by' => $userId,
                'voided_at' => now(),
                'void_reason' => mb_substr($reason, 0, 150),
                'note' => trim(($order->note ? $order->note . ' | ' : '') . 'VOID: ' . $reason),
            ])->save();

            $order->customer?->revertPurchase((float) $order->total, (int) $order->points_earned, (int) $order->points_redeemed);

            $this->setRawAttributes($order->getAttributes(), true);
        });
    }
}
