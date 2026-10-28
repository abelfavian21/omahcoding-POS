<?php

namespace App\Console\Commands;

use App\Models\PlatformAdmin;
use App\Models\PlatformAuditLog;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;

/**
 * Membuat admin platform PERTAMA (tidak ada pendaftaran publik) atau mereset password admin yang ada.
 *   php artisan platform:create-admin pemilik@contoh.id --name="Nama Anda"
 *   php artisan platform:create-admin pemilik@contoh.id --reset      (lupa password)
 */
class PlatformCreateAdmin extends Command
{
    protected $signature = 'platform:create-admin {email : Email admin} {--name= : Nama (wajib untuk admin baru)} {--reset : Reset password admin yang sudah ada}';

    protected $description = 'Buat admin platform CashierStore, atau reset password-nya';

    public function handle(): int
    {
        $email = Str::lower(trim($this->argument('email')));
        $existing = PlatformAdmin::where('email', $email)->first();

        if (Validator::make(['email' => $email], ['email' => 'required|email|max:255'])->fails()) {
            $this->error('Email tidak valid.');

            return self::FAILURE;
        }

        if ($existing && ! $this->option('reset')) {
            $this->error("Admin {$email} sudah ada. Pakai --reset untuk mengganti password-nya.");

            return self::FAILURE;
        }

        if (! $existing && $this->option('reset')) {
            $this->error("Admin {$email} tidak ditemukan.");

            return self::FAILURE;
        }

        $name = $existing?->name ?? trim((string) $this->option('name'));
        if ($name === '') {
            $this->error('Isi --name="Nama" untuk admin baru.');

            return self::FAILURE;
        }

        $password = (string) $this->secret('Password (minimal 12 karakter, huruf besar+kecil, angka)');
        $v = Validator::make(['password' => $password], ['password' => ['required', 'max:200', Password::min(12)->letters()->mixedCase()->numbers()]]);
        if ($v->fails()) {
            $this->error(implode(' ', $v->errors()->all()));

            return self::FAILURE;
        }
        if ($password !== (string) $this->secret('Ulangi password')) {
            $this->error('Konfirmasi password tidak sama.');

            return self::FAILURE;
        }

        if ($existing) {
            $existing->forceFill(['password' => $password, 'is_active' => true, 'must_change_password' => false])->save();
            PlatformAuditLog::record('admin.password_reset_cli', $email, [], null, $existing->id);
            $this->info("Password {$email} direset. Akun diaktifkan.");
        } else {
            $admin = PlatformAdmin::create(['name' => $name, 'email' => $email, 'password' => $password, 'is_active' => true, 'must_change_password' => false]);
            PlatformAuditLog::record('admin.created_cli', $email, ['name' => $name], null, $admin->id);
            $this->info("Admin {$email} dibuat. Masuk di /platform/login.");
        }

        return self::SUCCESS;
    }
}
