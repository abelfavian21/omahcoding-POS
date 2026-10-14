<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use \App\Models\Concerns\BelongsToBusiness;

    protected $fillable = [
        'code', 'name', 'phone', 'email', 'tier', 'points', 'total_spent',
        'visits', 'last_visit_at', 'birthday', 'notes', 'allergy',
    ];

    protected $casts = [
        'total_spent' => 'decimal:2',
        'last_visit_at' => 'datetime',
        'birthday' => 'date',
    ];

    public const TIERS = ['bronze' => 'Bronze', 'silver' => 'Silver', 'gold' => 'Gold', 'vip' => 'VIP'];

    /** Batas total belanja untuk naik tier (hanya naik, tidak turun otomatis) */
    public const TIER_THRESHOLDS = ['vip' => 5000000, 'gold' => 2000000, 'silver' => 500000];
    private const TIER_RANK = ['bronze' => 0, 'silver' => 1, 'gold' => 2, 'vip' => 3];
    public const POINT_PER = 10000; // 1 poin tiap belanja Rp 10.000

    public function applyPurchase(float $total, $at = null): void
    {
        $this->visits = $this->visits + 1;
        $this->total_spent = (float) $this->total_spent + $total;
        $this->points = $this->points + (int) floor($total / self::POINT_PER);
        $this->last_visit_at = $at ?? now();

        foreach (self::TIER_THRESHOLDS as $tier => $min) {
            if ($this->total_spent >= $min) {
                if (self::TIER_RANK[$tier] > self::TIER_RANK[$this->tier]) $this->tier = $tier;
                break;
            }
        }
        $this->save();
    }

    public function revertPurchase(float $total): void
    {
        $this->visits = max(0, $this->visits - 1);
        $this->total_spent = max(0, (float) $this->total_spent - $total);
        $this->points = max(0, $this->points - (int) floor($total / self::POINT_PER));
        $this->last_visit_at = $this->orders()->paid()->max('paid_at');
        $this->save();
    }

    protected static function booted(): void
    {
        static::created(function (Customer $c) {
            if (! $c->code) {
                $c->updateQuietly(['code' => 'CR-' . str_pad($c->id, 4, '0', STR_PAD_LEFT)]);
            }
        });
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function getInitialsAttribute(): string
    {
        return collect(explode(' ', trim($this->name)))
            ->take(2)->map(fn ($w) => mb_strtoupper(mb_substr($w, 0, 1)))->implode('');
    }

    public function getAvgSpendAttribute(): float
    {
        return $this->visits > 0 ? (float) $this->total_spent / $this->visits : 0;
    }
}
