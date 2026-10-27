<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

/**
 * Admin platform CashierStore (pemilik / partner). BUKAN user toko: tidak punya business_id dan
 * tidak memakai trait BelongsToBusiness. Login lewat guard "platform".
 */
class PlatformAdmin extends Authenticatable
{
    public const RECOVERY_CODE_COUNT = 8;

    protected $fillable = ['name', 'email', 'password', 'is_active', 'must_change_password'];

    // last_login_* dan kolom 2FA sengaja TIDAK fillable (diisi sistem lewat forceFill)

    protected $hidden = ['password', 'remember_token', 'totp_secret', 'recovery_codes'];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'is_active' => 'boolean',
            'must_change_password' => 'boolean',
            'last_login_at' => 'datetime',
            'totp_secret' => 'encrypted',
            'totp_confirmed_at' => 'datetime',
            'recovery_codes' => 'encrypted:array',
        ];
    }

    public function getInitialsAttribute(): string
    {
        return collect(explode(' ', trim($this->name)))
            ->take(2)->map(fn ($w) => mb_strtoupper(mb_substr($w, 0, 1)))->implode('');
    }

    public function hasTwoFactor(): bool
    {
        return filled($this->totp_secret) && $this->totp_confirmed_at !== null;
    }

    public function recoveryCodesLeft(): int
    {
        return count($this->recovery_codes ?? []);
    }

    /** Buat set kode pemulihan baru. Simpan hash-nya, kembalikan kode asli (tampil sekali saja). */
    public function issueRecoveryCodes(): array
    {
        $alphabet = 'abcdefghjkmnpqrstuvwxyz23456789';
        $plain = [];
        for ($i = 0; $i < self::RECOVERY_CODE_COUNT; $i++) {
            $c = '';
            for ($j = 0; $j < 10; $j++) {
                $c .= $alphabet[random_int(0, strlen($alphabet) - 1)];
            }
            $plain[] = substr($c, 0, 5) . '-' . substr($c, 5);
        }
        $this->forceFill(['recovery_codes' => array_map([self::class, 'hashRecoveryCode'], $plain)])->save();

        return $plain;
    }

    /** Pakai satu kode pemulihan (sekali pakai). Panggil di dalam transaksi + lockForUpdate. */
    public function consumeRecoveryCode(string $input): bool
    {
        $hash = self::hashRecoveryCode($input);
        $codes = $this->recovery_codes ?? [];
        $found = null;
        foreach ($codes as $i => $h) {
            if (hash_equals($h, $hash)) {
                $found = $i;
            }
        }
        if ($found === null) {
            return false;
        }
        unset($codes[$found]);
        $this->forceFill(['recovery_codes' => array_values($codes)])->save();

        return true;
    }

    private static function hashRecoveryCode(string $code): string
    {
        $norm = strtolower(preg_replace('/[^a-z0-9]/i', '', $code));

        return hash_hmac('sha256', $norm, (string) config('app.key'));
    }
}
