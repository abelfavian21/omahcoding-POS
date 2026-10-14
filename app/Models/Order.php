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
    ];

    protected $casts = [
        'subtotal' => 'decimal:2', 'discount' => 'decimal:2', 'tax' => 'decimal:2',
        'total' => 'decimal:2', 'paid_amount' => 'decimal:2', 'change_amount' => 'decimal:2',
        'paid_at' => 'datetime',
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

    public static function nextInvoiceNo(): string
    {
        $prefix = 'INV-' . now()->format('Ymd') . '-';
        $last = static::where('invoice_no', 'like', $prefix . '%')->orderByDesc('invoice_no')->value('invoice_no');
        $seq = $last ? ((int) substr($last, -4)) + 1 : 1;
        return $prefix . str_pad($seq, 4, '0', STR_PAD_LEFT);
    }

    /** Batalkan transaksi: stok kembali, statistik pelanggan dikoreksi. */
    public function void(?int $userId = null, ?string $reason = null): void
    {
        if ($this->status === 'void') {
            throw new \RuntimeException('Transaksi sudah dibatalkan.');
        }

        \Illuminate\Support\Facades\DB::transaction(function () use ($userId, $reason) {
            $this->load(['items', 'customer']);

            foreach ($this->items as $item) {
                if ($item->product) {
                    $item->product->adjustStock($item->qty, 'void', "Void {$this->invoice_no}", $userId, $this->id);
                }
            }

            $this->update([
                'status' => 'void',
                'note' => trim(($this->note ? $this->note . ' | ' : '') . 'VOID' . ($reason ? ': ' . $reason : '')),
            ]);

            $this->customer?->revertPurchase((float) $this->total);
        });
    }
}
