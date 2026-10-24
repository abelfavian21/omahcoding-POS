<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\BusinessCategory;
use App\Models\PlatformAdmin;
use App\Models\PlatformAuditLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * Panel platform (pemilik CashierStore). Belum pernah dijalankan oleh penulis (PHP di lingkungan penyusunan
 * lebih lama dari yang dibutuhkan framework) — jalankan:  php artisan test --filter=PlatformAdminTest
 * Login memakai alur asli (POST /platform/login), BUKAN actingAs(..., 'platform'): actingAs memanggil
 * Auth::shouldUse() sehingga guard default berubah dan tidak lagi meniru produksi.
 */
class PlatformAdminTest extends TestCase
{
    use RefreshDatabase;

    private const PASS = 'Str0ng-Passw0rd!';

    private function admin(string $email = 'pemilik@t.test', array $extra = []): PlatformAdmin
    {
        return PlatformAdmin::create($extra + ['name' => 'Pemilik', 'email' => $email, 'password' => self::PASS, 'is_active' => true]);
    }

    private function login(PlatformAdmin $a, string $password = self::PASS)
    {
        return $this->post(route('platform.login.store'), ['email' => $a->email, 'password' => $password]);
    }

    private function business(string $code, string $status = 'trial', ?string $trialEnds = '+10 days'): Business
    {
        $cat = BusinessCategory::first() ?? BusinessCategory::create([
            'slug' => 'cafe', 'name' => 'Cafe', 'icon' => 'x', 'color' => 'blue', 'tagline' => 't',
            'features' => 'x', 'badge' => null, 'preset' => [], 'is_active' => true, 'sort_order' => 1,
        ]);

        return Business::create([
            'business_category_id' => $cat->id, 'code' => $code, 'name' => 'Toko ' . $code, 'owner_name' => 'Owner',
            'email' => Str::lower($code) . '@t.test', 'status' => $status, 'trial_ends_at' => $trialEnds ? now()->modify($trialEnds) : null,
        ]);
    }

    private function tenantUser(Business $b, string $role, string $name): User
    {
        return User::create([
            'business_id' => $b->id, 'name' => $name, 'email' => Str::lower(Str::random(8)) . '@t.test',
            'password' => self::PASS, 'role' => $role, 'is_active' => true,
        ]);
    }

    public function test_tamu_diarahkan_ke_login_platform(): void
    {
        $this->get(route('platform.dashboard'))->assertRedirect(route('platform.login'));
        $this->get(route('platform.businesses.index'))->assertRedirect(route('platform.login'));
    }

    public function test_admin_toko_tidak_bisa_masuk_panel_platform(): void
    {
        $b = $this->business('CS-AAA-1111', 'active');
        $owner = $this->tenantUser($b, 'admin', 'Owner Toko');

        $this->actingAs($owner)->get(route('platform.dashboard'))->assertRedirect(route('platform.login'));

        // akun toko juga tidak bisa dipakai di form login platform
        $this->post(route('platform.login.store'), ['email' => $owner->email, 'password' => self::PASS])->assertSessionHasErrors('email');
        $this->assertGuest('platform');
    }

    public function test_admin_platform_tidak_bisa_membuka_halaman_toko(): void
    {
        $a = $this->admin();
        $this->login($a)->assertRedirect(route('platform.dashboard'));

        $this->get(route('dashboard'))->assertRedirect();   // guard toko tetap tamu
        $this->assertGuest('web');
    }

    public function test_login_berhasil_mencatat_audit_dan_jejak_login(): void
    {
        $a = $this->admin();

        $this->login($a)->assertRedirect(route('platform.dashboard'));
        $this->assertAuthenticatedAs($a, 'platform');
        $this->assertNotNull($a->fresh()->last_login_at);
        $this->assertTrue(PlatformAuditLog::where('action', 'login.success')->exists());
    }

    public function test_password_salah_dan_akun_nonaktif_ditolak(): void
    {
        $a = $this->admin();
        $this->login($a, 'salah-salah-salah')->assertSessionHasErrors('email');
        $this->assertGuest('platform');
        $this->assertTrue(PlatformAuditLog::where('action', 'login.failed')->exists());

        $a->forceFill(['is_active' => false])->save();
        $this->login($a)->assertSessionHasErrors('email');
        $this->assertGuest('platform');
    }

    public function test_login_platform_dibatasi_setelah_5_percobaan(): void
    {
        $a = $this->admin();
        for ($i = 0; $i < 5; $i++) {
            $this->login($a, 'salah-salah-salah');
        }
        $this->login($a)->assertStatus(429);
    }

    public function test_admin_yang_dinonaktifkan_saat_sesi_hidup_langsung_terlempar(): void
    {
        $a = $this->admin();
        $this->login($a);
        $this->get(route('platform.dashboard'))->assertOk();

        $a->forceFill(['is_active' => false])->save();
        Auth::forgetGuards();   // guard di tes yang sama menyimpan instance user lama

        $this->get(route('platform.dashboard'))->assertRedirect(route('platform.login'));
        $this->assertGuest('platform');
    }

    public function test_dashboard_dan_daftar_toko_tampil(): void
    {
        $this->business('CS-AAA-1111', 'active');
        $this->business('CS-BBB-2222', 'trial', '+3 days');
        $this->login($this->admin());

        $this->get(route('platform.dashboard'))->assertOk()->assertSee('Ringkasan platform');
        $this->get(route('platform.businesses.index'))->assertOk()->assertSee('CS-AAA-1111')->assertSee('CS-BBB-2222');
        $this->get(route('platform.businesses.index', ['q' => 'BBB']))->assertOk()->assertSee('CS-BBB-2222')->assertDontSee('CS-AAA-1111');
        $this->get(route('platform.businesses.index', ['status' => 'active']))->assertOk()->assertSee('CS-AAA-1111')->assertDontSee('CS-BBB-2222');
    }

    public function test_detail_toko_menampilkan_staff_toko_itu_walau_admin_juga_login_sebagai_user_toko(): void
    {
        $a = $this->business('CS-AAA-1111', 'active');
        $b = $this->business('CS-BBB-2222', 'active');
        $userA = $this->tenantUser($a, 'admin', 'Staff Toko A');
        $this->tenantUser($b, 'kasir', 'Staff Toko B');

        $this->login($this->admin());
        // browser yang sama juga sedang login sebagai user toko A: scope tenant tidak boleh membocor ke panel platform
        $this->actingAs($userA)->get(route('platform.businesses.show', $b))
            ->assertOk()->assertSee('Staff Toko B')->assertDontSee('Staff Toko A');
    }

    public function test_tangguhkan_wajib_alasan_dan_langsung_mengunci_pengguna_toko(): void
    {
        $b = $this->business('CS-AAA-1111', 'active');
        $kasir = $this->tenantUser($b, 'kasir', 'Kasir');
        $this->login($this->admin());

        $this->post(route('platform.businesses.status', $b), ['action' => 'suspend'])->assertSessionHasErrors('reason');
        $this->assertSame('active', $b->fresh()->status);

        $this->post(route('platform.businesses.status', $b), ['action' => 'suspend', 'reason' => 'pembayaran belum masuk'])->assertSessionHasNoErrors();
        $this->assertSame('suspended', $b->fresh()->status);
        $this->assertTrue(PlatformAuditLog::where('action', 'business.suspend')->where('business_id', $b->id)->exists());

        $this->actingAs($kasir->fresh())->get(route('pos'))->assertRedirect();
        $this->assertGuest('web');

        $this->post(route('platform.businesses.status', $b), ['action' => 'activate']);
        $this->assertSame('active', $b->fresh()->status);
    }

    public function test_perpanjang_trial_menambah_hari_dari_tanggal_berakhir(): void
    {
        $b = $this->business('CS-AAA-1111', 'trial', '+5 days');
        $before = $b->trial_ends_at->copy();
        $this->login($this->admin());

        $this->post(route('platform.businesses.trial', $b), ['days' => 14])->assertSessionHasNoErrors();
        $this->assertSame($before->copy()->addDays(14)->toDateString(), $b->fresh()->trial_ends_at->toDateString());

        $this->post(route('platform.businesses.trial', $b), ['days' => 3])->assertSessionHasErrors('days');   // hanya 7/14/30

        $aktif = $this->business('CS-CCC-3333', 'active', null);
        $this->post(route('platform.businesses.trial', $aktif), ['days' => 7])->assertSessionHasErrors('days');
    }

    public function test_tidak_bisa_menonaktifkan_diri_sendiri_atau_admin_aktif_terakhir(): void
    {
        $a = $this->admin();
        $this->login($a);

        $this->post(route('platform.admins.toggle', $a))->assertSessionHasErrors('admin');
        $this->assertTrue($a->fresh()->is_active);

        $b = $this->admin('partner@t.test', ['name' => 'Partner']);
        $this->post(route('platform.admins.toggle', $b))->assertSessionHasNoErrors();
        $this->assertFalse($b->fresh()->is_active);

        $this->post(route('platform.logout'));
        $this->login($b)->assertSessionHasErrors('email');   // nonaktif tidak bisa masuk
    }

    public function test_admin_baru_wajib_ganti_password_pertama_kali(): void
    {
        $a = $this->admin();
        $this->login($a);

        $this->post(route('platform.admins.store'), ['name' => 'Partner', 'email' => 'Partner@T.test', 'password' => 'short'])->assertSessionHasErrors('password');
        $this->post(route('platform.admins.store'), ['name' => 'Partner', 'email' => 'Partner@T.test', 'password' => 'Temp-Passw0rd-Aman'])->assertSessionHasNoErrors();
        $p = PlatformAdmin::where('email', 'partner@t.test')->firstOrFail();
        $this->assertTrue($p->must_change_password);

        // sesi baru sebagai partner
        $this->post(route('platform.logout'));
        $this->post(route('platform.login.store'), ['email' => 'partner@t.test', 'password' => 'Temp-Passw0rd-Aman'])->assertRedirect(route('platform.dashboard'));
        $this->get(route('platform.dashboard'))->assertRedirect(route('platform.account'));
        $this->get(route('platform.businesses.index'))->assertRedirect(route('platform.account'));
        $this->get(route('platform.account'))->assertOk();

        $this->put(route('platform.account.update'), ['current_password' => 'Temp-Passw0rd-Aman', 'password' => 'Temp-Passw0rd-Aman', 'password_confirmation' => 'Temp-Passw0rd-Aman'])->assertSessionHasErrors('password');
        $this->put(route('platform.account.update'), ['current_password' => 'salah', 'password' => 'An0ther-Str0ng-Pass', 'password_confirmation' => 'An0ther-Str0ng-Pass'])->assertSessionHasErrors('current_password');
        $this->put(route('platform.account.update'), ['current_password' => 'Temp-Passw0rd-Aman', 'password' => 'An0ther-Str0ng-Pass', 'password_confirmation' => 'An0ther-Str0ng-Pass'])->assertRedirect(route('platform.dashboard'));

        $this->assertFalse($p->fresh()->must_change_password);
        $this->get(route('platform.dashboard'))->assertOk();
    }

    public function test_logout_mengakhiri_sesi_platform(): void
    {
        $this->login($this->admin());
        $this->post(route('platform.logout'))->assertRedirect(route('platform.login'));
        $this->assertGuest('platform');
        $this->get(route('platform.dashboard'))->assertRedirect(route('platform.login'));
    }

    public function test_daftar_ip_mengunci_panel_dengan_404(): void
    {
        config(['platform.allowed_ips' => ['203.0.113.5', '198.51.100.0/24']]);

        $this->get(route('platform.login'))->assertNotFound();
        $this->withServerVariables(['REMOTE_ADDR' => '203.0.113.5'])->get(route('platform.login'))->assertOk();
        $this->withServerVariables(['REMOTE_ADDR' => '198.51.100.77'])->get(route('platform.login'))->assertOk();
    }

    public function test_audit_platform_tidak_bisa_diubah_atau_dihapus(): void
    {
        PlatformAuditLog::record('login.success', 'x@t.test');
        $log = PlatformAuditLog::firstOrFail();

        $log->action = 'diubah';
        $log->save();
        $this->assertSame('login.success', $log->fresh()->action);

        $log->delete();
        $this->assertSame(1, PlatformAuditLog::count());
    }
}
