<?php

namespace App\Models\Concerns;

use App\Models\Business;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use LogicException;

/**
 * Data otomatis terisolasi per bisnis (FAIL-CLOSED):
 *  - user login dengan business_id  -> query difilter ke bisnisnya
 *  - user login TANPA business_id   -> TIDAK melihat data apa pun (dulu: melihat semua tenant!)
 *  - record baru otomatis diberi business_id; user tanpa bisnis tidak boleh membuat data
 *  - business_id tidak boleh dipindah setelah record dibuat
 *
 * Auth::hasUser() (bukan Auth::check()) dipakai supaya saat Laravel sendiri
 * mengambil user dari session/login tidak terjadi rekursi.
 */
trait BelongsToBusiness
{
    protected static function bootBelongsToBusiness(): void
    {
        static::addGlobalScope('business', function (Builder $q) {
            if (! Auth::hasUser()) {
                return; // konteks tanpa login: halaman login/registrasi, console, seeder
            }

            $column = $q->getModel()->getTable() . '.business_id';
            $bid = Auth::user()->business_id;

            $bid ? $q->where($column, $bid) : $q->whereRaw('1 = 0');
        });

        static::creating(function ($model) {
            if ($model->business_id || ! Auth::hasUser()) {
                return;
            }
            if (! Auth::user()->business_id) {
                throw new LogicException('Akun tanpa bisnis tidak boleh membuat data.');
            }
            $model->business_id = Auth::user()->business_id;
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
