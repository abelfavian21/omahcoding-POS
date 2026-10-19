<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QrisPayment extends Model
{
    use \App\Models\Concerns\BelongsToBusiness;

    public const PENDING = 'pending';
    public const SETTLED = 'settled';
    public const EXPIRED = 'expired';
    public const CANCELLED = 'cancelled';
    public const FAILED = 'failed';
    public const REVIEW = 'review';   // uang diterima tetapi order gagal dibuat -> perlu ditangani admin

    protected $fillable = [
        'user_id', 'order_id', 'idempotency_key', 'midtrans_order_id', 'transaction_id', 'status',
        'gross_amount', 'payload', 'qr_url', 'qr_string', 'expires_at', 'settled_at', 'checked_at', 'failure_reason',
    ];

    protected function casts(): array
    {
        return [
            'payload' => 'array', 'gross_amount' => 'decimal:2',
            'expires_at' => 'datetime', 'settled_at' => 'datetime', 'checked_at' => 'datetime',
        ];
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function cashier()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /** QR yang masih aktif milik satu kasir. */
    public function scopePendingFor($q, int $userId)
    {
        return $q->where('user_id', $userId)->where('status', self::PENDING)->where('expires_at', '>', now());
    }
}
