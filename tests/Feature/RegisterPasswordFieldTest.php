<?php

namespace Tests\Feature;

use App\Livewire\RegisterPasswordField;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class RegisterPasswordFieldTest extends TestCase
{
    use RefreshDatabase;

    public function test_halaman_landing_memuat_komponen_kata_sandi(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSeeLivewire(RegisterPasswordField::class);
    }

    public function test_password_lemah_ditolak_dan_diberitahukan_ke_halaman(): void
    {
        Livewire::test(RegisterPasswordField::class)
            ->set('password', '123')
            ->assertHasErrors(['password'])
            ->assertDispatched('password-validation-updated', valid: false);
    }

    public function test_password_kuat_diterima(): void
    {
        Livewire::test(RegisterPasswordField::class)
            ->set('password', 'TokoKopi2026x')
            ->assertHasNoErrors()
            ->assertDispatched('password-validation-updated', valid: true);
    }

    public function test_password_tidak_boleh_memuat_nama_pemilik(): void
    {
        Livewire::test(RegisterPasswordField::class)
            ->dispatch('registration-context-updated', name: 'Budi Santoso', email: 'budi@toko.test')
            ->set('password', 'Budi Santoso12')
            ->assertHasErrors(['password'])
            ->assertDispatched('password-validation-updated', valid: false);
    }
}
