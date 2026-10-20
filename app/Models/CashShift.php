<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CashShift extends Model
{
    use Concerns\BelongsToBusiness;

    protected $fillable = [
        'user_id', 'opened_at', 'closed_at', 'opening_cash',
        'closing_cash', 'expected_cash', 'difference', 'note',
    ];

    protected function casts(): array
    {
        return [
            'opened_at' => 'datetime',
            'closed_at' => 'datetime',
            'opening_cash' => 'decimal:2',
            'closing_cash' => 'decimal:2',
            'expected_cash' => 'decimal:2',
            'difference' => 'decimal:2',
        ];
    }

    public function cashier()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function scopeOpen($q)
    {
        return $q->whereNull('closed_at');
    }

    public function isOpen(): bool
    {
        return $this->closed_at === null;
    }

    public static function currentFor(User $user): ?self
    {
        return static::open()->where('user_id', $user->id)->latest('opened_at')->first();
    }

    public function cashSalesTotal(): float
    {
        return (float) $this->orders()->paid()->where('payment_method', 'cash')->sum('total');
    }

    public function expectedAtClose(): float
    {
        return (float) $this->opening_cash + $this->cashSalesTotal();
    }
}
