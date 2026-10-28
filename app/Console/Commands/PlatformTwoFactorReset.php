<?php

namespace App\Console\Commands;

use App\Models\PlatformAdmin;
use App\Models\PlatformAuditLog;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

/**
 * Admin kehilangan ponsel DAN kode pemulihan. Hanya bisa dijalankan dari server (SSH), bukan dari web.
 *   php artisan platform:2fa-reset pemilik@contoh.id
 * Saat login berikutnya admin dipaksa mendaftarkan 2FA baru (bila PLATFORM_REQUIRE_2FA aktif).
 */
class PlatformTwoFactorReset extends Command
{
    protected $signature = 'platform:2fa-reset {email : Email admin} {--force : Lewati konfirmasi}';

    protected $description = 'Hapus 2FA (TOTP + kode pemulihan) milik satu admin platform';

    public function handle(): int
    {
        $email = Str::lower(trim($this->argument('email')));
        $admin = PlatformAdmin::where('email', $email)->first();

        if (! $admin) {
            $this->error("Admin {$email} tidak ditemukan.");

            return self::FAILURE;
        }
        if (! $admin->hasTwoFactor()) {
            $this->info("{$email} memang belum memakai 2FA.");

            return self::SUCCESS;
        }
        if (! $this->option('force') && ! $this->confirm("Hapus 2FA milik {$email}? Pastikan permintaan ini benar dari pemiliknya.")) {
            return self::FAILURE;
        }

        $admin->forceFill([
            'totp_secret' => null, 'totp_confirmed_at' => null, 'totp_last_step' => null, 'recovery_codes' => null,
        ])->save();
        PlatformAuditLog::record('admin.2fa_reset_cli', $email, [], null, $admin->id);

        $this->info("2FA {$email} dihapus.");
        $this->warn('Bila akun dicurigai dibobol, reset juga passwordnya: php artisan platform:create-admin ' . $email . ' --reset');

        return self::SUCCESS;
    }
}
