<?php

namespace App\Models\Concerns;

use App\Models\Business;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

/**
 * Data otomatis terisolasi per bisnis:
 *  - query difilter business_id milik user yang sedang login
 *  - record baru otomatis diberi business_id milik user tersebut
 *
 * Auth::hasUser() (bukan Auth::check()) dipakai supaya saat Laravel sendiri
 * mengambil user dari session/login tidak terjadi rekursi.
 */
trait BelongsToBusiness
{
    protected static function bootBelongsToBusiness(): void
    {
        static::addGlobalScope('business', function (Builder $q) {
            if (Auth::hasUser() && Auth::user()->business_id) {
                $q->where($q->getModel()->getTable() . '.business_id', Auth::user()->business_id);
            }
        });

        static::creating(function ($model) {
            if (! $model->business_id && Auth::hasUser()) {
                $model->business_id = Auth::user()->business_id;
            }
        });
    }

    public function business()
    {
        return $this->belongsTo(Business::class);
    }
}
