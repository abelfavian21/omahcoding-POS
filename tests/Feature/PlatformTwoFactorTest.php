<?php

namespace Tests\Feature;

use App\Models\PlatformAdmin;
use App\Support\Totp;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Crypt;
use Tests\TestCase;

class PlatformTwoFactorTest extends TestCase
{
    use RefreshDatabase;

    private const PASSWORD = 'Sangat-Panjang-9!Aman';
    private const SECRET = 'GEZDGNBVGY3TQOJQGEZDGNBVGY3TQOJQ';

    protected function setUp(): void
    {
        parent::setUp();
        config(['platform.require_2fa' => true, 'platform.allowed_ips' => [], 'platform.require_allowlist' => false]);
    }

    private function admin(bool $withTotp = true): PlatformAdmin
    {
        $a = PlatformAdmin::create(['name' => 'Owner', 'email' => 'owner@example.test', 'password' => self::PASSWORD, 'is_active' => true, 'must_change_password' => false]);
        if ($withTotp) {
            $a->forceFill(['totp_secret' => self::SECRET, 'totp_confirmed_at' => now()])->save();
        }

        return $a;
    }

    private function login()
    {
        return $this->post(route('platform.login.store'), ['email' => 'owner@example.test', 'password' => self::PASSWORD]);
    }

    private function nowCode(): string
    {
        return Totp::code(self::SECRET, Totp::step());
    }

    public function test_login_dengan_2fa_diarahkan_ke_tantangan_dan_panel_terkunci(): void
    {
        $this->admin();

        $this->login()->assertRedirect(route('platform.2fa.challenge'));
        $this->get(route('platform.dashboard'))->assertRedirect(route('platform.2fa.challenge'));
        $this->get(route('platform.2fa.manage'))->assertRedirect(route('platform.2fa.challenge'));
        $this->get(route('platform.account'))->assertRedirect(route('platform.2fa.challenge'));
    }

    public function test_kode_benar_membuka_panel(): void
    {
        $this->admin();
        $this->login();

        $this->post(route('platform.2fa.verify'), ['code' => $this->nowCode()])->assertRedirect(route('platform.dashboard'));
        $this->get(route('platform.dashboard'))->assertOk();
    }

    public function test_kode_salah_ditolak_dan_panel_tetap_terkunci(): void
    {
        $this->admin();
        $this->login();

        $this->post(route('platform.2fa.verify'), ['code' => '000000'])->assertSessionHasErrors('code');
        $this->get(route('platform.dashboard'))->assertRedirect(route('platform.2fa.challenge'));
    }

    public function test_kode_totp_tidak_bisa_dipakai_dua_kali(): void
    {
        $this->admin();
        $code = $this->nowCode();

        $this->login();
        $this->post(route('platform.2fa.verify'), ['code' => $code])->assertRedirect(route('platform.dashboard'));
        $this->post(route('platform.logout'));

        $this->login();
        $this->post(route('platform.2fa.verify'), ['code' => $code])->assertSessionHasErrors('code');
    }

    public function test_kode_pemulihan_sekali_pakai(): void
    {
        $admin = $this->admin();
        $codes = $admin->issueRecoveryCodes();

        $this->login();
        $this->post(route('platform.2fa.verify'), ['code' => $codes[0]])->assertRedirect(route('platform.2fa.manage'));
        $this->assertSame(PlatformAdmin::RECOVERY_CODE_COUNT - 1, $admin->refresh()->recoveryCodesLeft());
        $this->post(route('platform.logout'));

        $this->login();
        $this->post(route('platform.2fa.verify'), ['code' => $codes[0]])->assertSessionHasErrors('code');
    }

    public function test_admin_tanpa_2fa_dipaksa_mendaftar_saat_wajib(): void
    {
        $this->admin(false);

        $this->login()->assertRedirect(route('platform.dashboard'));
        $this->get(route('platform.dashboard'))->assertRedirect(route('platform.2fa.setup'));
        $this->get(route('platform.2fa.setup'))->assertOk();
    }

    public function test_pendaftaran_butuh_password_dan_kode_lalu_menyimpan_secret_terenkripsi(): void
    {
        $admin = $this->admin(false);
        $this->login();
        $this->get(route('platform.2fa.setup'))->assertOk();

        $secret = Crypt::decryptString(session('platform_2fa_pending_secret'));
        $code = Totp::code($secret, Totp::step());

        $this->post(route('platform.2fa.confirm'), ['password' => 'salah', 'code' => $code])->assertSessionHasErrors('password');
        $this->assertFalse($admin->refresh()->hasTwoFactor());

        $this->post(route('platform.2fa.confirm'), ['password' => self::PASSWORD, 'code' => $code])
            ->assertRedirect(route('platform.2fa.manage'))
            ->assertSessionHas('recovery_codes');

        $admin->refresh();
        $this->assertTrue($admin->hasTwoFactor());
        $this->assertSame(PlatformAdmin::RECOVERY_CODE_COUNT, $admin->recoveryCodesLeft());
        $this->assertNotSame($secret, \Illuminate\Support\Facades\DB::table('platform_admins')->value('totp_secret'), 'secret harus tersimpan terenkripsi');
    }

    public function test_allowlist_kosong_di_produksi_membuat_panel_404(): void
    {
        config(['platform.require_allowlist' => true, 'platform.allowed_ips' => []]);
        $this->get('/platform/login')->assertNotFound();
    }

    public function test_allowlist_membatasi_ip(): void
    {
        config(['platform.allowed_ips' => ['203.0.113.7']]);

        $this->withServerVariables(['REMOTE_ADDR' => '198.51.100.9'])->get('/platform/login')->assertNotFound();
        $this->withServerVariables(['REMOTE_ADDR' => '203.0.113.7'])->get('/platform/login')->assertOk();
    }
}
