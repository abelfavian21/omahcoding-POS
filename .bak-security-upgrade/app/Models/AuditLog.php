<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

/**
 * Jejak audit: append-only (tidak bisa diubah/dihapus lewat aplikasi).
 * Mencatat siapa, kapan, dari IP mana, melakukan apa.
 */
class AuditLog extends Model
{
    use Concerns\BelongsToBusiness;

    public $timestamps = false;

    protected $fillable = ['action', 'target', 'meta'];

    protected function casts(): array
    {
        return ['meta' => 'array', 'created_at' => 'datetime'];
    }

    protected static function booted(): void
    {
        static::updating(fn () => false);
        static::deleting(fn () => false);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /** Tidak pernah melempar error: kegagalan audit tidak boleh menggagalkan transaksi. */
    public static function record(string $action, ?string $target = null, array $meta = [], ?int $businessId = null, ?int $userId = null): void
    {
        try {
            $actor = Auth::hasUser() ? Auth::user() : null;

            $log = new static(['action' => $action, 'target' => $target, 'meta' => $meta ?: null]);
            $log->business_id = $businessId ?? $actor?->business_id;
            $log->user_id = $userId ?? $actor?->id;
            $log->ip = request()?->ip();
            $log->user_agent = mb_substr((string) request()?->userAgent(), 0, 255);
            $log->created_at = now();
            $log->save();
        } catch (\Throwable $e) {
            report($e);
        }
    }
}
