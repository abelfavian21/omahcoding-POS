<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Business extends Model
{
    protected $fillable = [
        'business_category_id', 'code', 'name', 'owner_name', 'email', 'phone',
        'location', 'outlet_count', 'status', 'trial_ends_at',
        'tax_rate', 'tax_label', 'receipt_footer',
    ];

    protected function casts(): array
    {
        return ['trial_ends_at' => 'datetime', 'tax_rate' => 'float'];
    }

    public function category()
    {
        return $this->belongsTo(BusinessCategory::class, 'business_category_id');
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }

    /** Tarif pajak bisnis ini; fallback ke config bila belum diatur. */
    public function taxRate(): float
    {
        return (float) ($this->tax_rate ?? config('pos.tax_rate'));
    }

    public function isUsable(): bool
    {
        if ($this->status === 'suspended') {
            return false;
        }

        return ! ($this->isTrialExpired() && config('pos.enforce_trial'));
    }

    public function isTrialExpired(): bool
    {
        return $this->status === 'trial' && $this->trial_ends_at && $this->trial_ends_at->isPast();
    }

    public function unusableReason(): string
    {
        return $this->status === 'suspended'
            ? 'Akun bisnis ini ditangguhkan. Hubungi dukungan CashierStore.'
            : 'Masa percobaan sudah berakhir. Hubungi dukungan CashierStore untuk mengaktifkan.';
    }

    /** ID Toko unik, contoh: CS-ORELY-8804 */
    public static function generateCode(string $name): string
    {
        $base = strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', Str::ascii($name)), 0, 5)) ?: 'TOKO';
        do {
            $code = 'CS-' . $base . '-' . random_int(1000, 9999);
        } while (static::where('code', $code)->exists());

        return $code;
    }
}
