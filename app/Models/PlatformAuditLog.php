<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

/** Jejak aksi admin platform: append-only (tidak bisa diubah/dihapus lewat aplikasi). */
class PlatformAuditLog extends Model
{
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

    public function admin()
    {
        return $this->belongsTo(PlatformAdmin::class, 'platform_admin_id');
    }

    /** Tidak pernah melempar error: kegagalan audit tidak boleh menggagalkan aksinya. */
    public static function record(string $action, ?string $target = null, array $meta = [], ?int $businessId = null, ?int $adminId = null): void
    {
        try {
            $log = new static(['action' => $action, 'target' => $target, 'meta' => $meta ?: null]);
            $log->business_id = $businessId;
            $log->platform_admin_id = $adminId ?? Auth::guard('platform')->id();
            $log->ip = request()?->ip();
            $log->user_agent = mb_substr((string) request()?->userAgent(), 0, 255);
            $log->created_at = now();
            $log->save();
        } catch (\Throwable $e) {
            report($e);
        }
    }
}
