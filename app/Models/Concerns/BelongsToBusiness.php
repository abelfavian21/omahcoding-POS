<?php

namespace App\Models\Concerns;

use App\Models\Business;
use App\Support\TenantContext;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Log;
use LogicException;

/**
 * Isolasi per bisnis, FAIL-CLOSED sungguhan (konteks diambil dari TenantContext, bukan Auth::hasUser()):
 *  - ada konteks bisnis        -> query difilter ke bisnis itu
 *  - withoutTenant()           -> tanpa filter (sadar, terdaftar di tes)
 *  - TIDAK ada konteks         -> 0 baris (tenant.strict=false: hanya log peringatan, untuk masa migrasi)
 *  - create tanpa konteks dan tanpa business_id -> LogicException (dulu: tersimpan dengan business_id NULL)
 *  - business_id eksplisit yang beda dari konteks -> LogicException
 *  - business_id tidak boleh dipindah setelah record dibuat
 *  - Model boleh melonggarkan create dengan method allowsNullBusiness(): true (dipakai AuditLog: catatan sistem seperti
 *    "login gagal / kode toko tidak dikenal" sah tanpa bisnis, dan platform/webhook menulis untuk bisnis lain secara eksplisit)
 */
trait BelongsToBusiness
{
    protected static function bootBelongsToBusiness(): void
    {
        static::addGlobalScope('business', function (Builder $q) {
            $ctx = app(TenantContext::class);

            if ($ctx->isUnscoped()) {
                return;
            }

            if ($ctx->id() !== null) {
                $q->where($q->getModel()->getTable() . '.business_id', $ctx->id());

                return;
            }

            if (config('tenant.strict', true)) {
                $q->whereRaw('1 = 0');
            } else {
                Log::warning('tenant.unscoped_query', ['model' => $q->getModel()::class]);
            }
        });

        static::creating(function ($model) {
            $ctx = app(TenantContext::class);
            $lenient = method_exists($model, 'allowsNullBusiness') && $model->allowsNullBusiness();

            if ($model->business_id) {
                if (! $lenient && ! $ctx->isUnscoped() && $ctx->id() !== null && (int) $model->business_id !== $ctx->id()) {
                    throw new LogicException('Tidak boleh membuat data untuk bisnis lain.');
                }

                return;
            }

            if ($ctx->id() === null) {
                if ($lenient) {
                    return;
                }

                throw new LogicException('Tidak ada konteks bisnis. Pakai TenantContext::run($businessId, ...) atau isi business_id.');
            }

            $model->business_id = $ctx->id();
        });

        static::updating(function ($model) {
            if ($model->isDirty('business_id') && $model->getOriginal('business_id') !== null) {
                throw new LogicException('business_id tidak boleh diubah.');
            }
        });
    }

    public function business()
    {
        return $this->belongsTo(Business::class);
    }
}
