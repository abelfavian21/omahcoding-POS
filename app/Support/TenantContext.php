<?php

namespace App\Support;

use Closure;

/**
 * Konteks bisnis yang EKSPLISIT. Satu-satunya sumber kebenaran untuk global scope "business".
 * Didaftarkan sebagai scoped singleton (dibuang per request / per job queue / per tes).
 *
 *  - HTTP     : diisi otomatis dari user toko yang login (lihat TenantServiceProvider)
 *  - webhook, job, console, seeder : WAJIB run($businessId, fn) atau withoutTenant(fn)
 *  - tanpa keduanya : query tenant mengembalikan 0 baris, create() melempar LogicException
 */
final class TenantContext
{
    private ?int $businessId = null;
    private bool $unscoped = false;

    public function id(): ?int
    {
        return $this->businessId;
    }

    public function isUnscoped(): bool
    {
        return $this->unscoped;
    }

    public function set(?int $businessId): void
    {
        $this->businessId = $businessId;
    }

    /** Jalankan sebagai bisnis tertentu. Konteks sebelumnya dipulihkan, juga saat exception. */
    public function run(int $businessId, Closure $fn): mixed
    {
        [$prevId, $prevUnscoped] = [$this->businessId, $this->unscoped];
        [$this->businessId, $this->unscoped] = [$businessId, false];

        try {
            return $fn();
        } finally {
            [$this->businessId, $this->unscoped] = [$prevId, $prevUnscoped];
        }
    }

    /** Lewati isolasi secara SADAR (login, dasbor platform, pencarian awal webhook). Jarang dan terdaftar di tes. */
    public function withoutTenant(Closure $fn): mixed
    {
        [$prevId, $prevUnscoped] = [$this->businessId, $this->unscoped];
        $this->unscoped = true;

        try {
            return $fn();
        } finally {
            [$this->businessId, $this->unscoped] = [$prevId, $prevUnscoped];
        }
    }
}
