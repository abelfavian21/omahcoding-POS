<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\Business;
use App\Models\BusinessCategory;
use App\Models\CashShift;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * Tes properti keamanan utama. Belum pernah dijalankan oleh penulis (tidak ada PHP di lingkungan
 * penyusunan) — jalankan:  php artisan test --filter=SecurityTest  lalu perbaiki bila ada selisih kecil.
 */
class SecurityTest extends TestCase
{
    use RefreshDatabase;

    private function business(string $code): Business
    {
        $cat = BusinessCategory::first() ?? BusinessCategory::create([
            'slug' => 'cafe', 'name' => 'Cafe', 'icon' => 'x', 'color' => 'blue', 'tagline' => 't',
            'features' => 'x', 'badge' => null, 'preset' => [], 'is_active' => true, 'sort_order' => 1,
        ]);

        return Business::create([
            'business_category_id' => $cat->id, 'code' => $code, 'name' => $code, 'owner_name' => 'Owner',
            'email' => Str::lower($code) . '@t.test', 'status' => 'active',
        ]);
    }

    private function user(Business $b, string $role, array $extra = []): User
    {
        return User::create($extra + [
            'business_id' => $b->id, 'name' => ucfirst($role) . ' ' . Str::random(4),
            'email' => Str::lower(Str::random(8)) . '@t.test', 'password' => 'Str0ng-Passw0rd!',
            'role' => $role, 'is_active' => true,
        ]);
    }

    private function product(User $as, int $stock = 10): Product
    {
        Auth::setUser($as);
        $p = Product::create(['sku' => Str::random(6), 'name' => 'Kopi', 'price' => 10000, 'cost_price' => 4000, 'stock' => 0, 'min_stock' => 1, 'is_active' => true]);
        $p->adjustStock($stock, 'in', 'awal', $as->id);
        Auth::forgetGuards();

        return $p;
    }

    private function openShift(User $u): void
    {
        Auth::setUser($u);
        CashShift::create(['user_id' => $u->id, 'opened_at' => now(), 'opening_cash' => 0]);
        Auth::forgetGuards();
    }

    private function payload(Product $p, array $over = []): array
    {
        return $over + [
            'idempotency_key' => (string) Str::uuid(), 'items' => [['product_id' => $p->id, 'qty' => 2]],
            'order_type' => 'takeaway', 'payment_method' => 'cash', 'paid_amount' => 100000,
        ];
    }

    public function test_tenant_tidak_bisa_melihat_data_bisnis_lain(): void
    {
        $a = $this->business('CS-AAA-1111');
        $b = $this->business('CS-BBB-2222');
        $adminA = $this->user($a, 'admin');
        $adminB = $this->user($b, 'admin');

        Auth::setUser($adminB);
        $cust = Customer::create(['name' => 'Rahasia B']);
        Auth::forgetGuards();

        $this->actingAs($adminA)->get(route('customers.edit', $cust))->assertNotFound();
    }

    public function test_user_tanpa_bisnis_tidak_melihat_data_apa_pun(): void
    {
        $b = $this->business('CS-BBB-2222');
        $adminB = $this->user($b, 'admin');
        Auth::setUser($adminB);
        Customer::create(['name' => 'Data B']);
        Auth::forgetGuards();

        $orphan = User::forceCreate([
            'business_id' => null, 'name' => 'Yatim', 'email' => 'y@t.test', 'password' => bcrypt('x'),
            'role' => 'admin', 'is_active' => true,
        ]);
        Auth::setUser($orphan);

        $this->assertSame(0, Customer::count());
    }

    private function ipDiAuditLogin(?string $trusted): ?string
    {
        config(['trustedproxy.proxies' => $trusted]);
        $this->withServerVariables(['REMOTE_ADDR' => '10.0.0.1'])->withHeader('X-Forwarded-For', '203.0.113.9')
            ->post(route('login.password'), ['email' => 'x@t.test', 'password' => 'salah-banget']);

        return \App\Models\AuditLog::withoutGlobalScopes()->where('action', 'login.failed')->value('ip');
    }

    public function test_ip_asli_hanya_dibaca_dari_proxy_yang_dipercaya(): void
    {
        $this->assertSame('10.0.0.1', $this->ipDiAuditLogin(null));   // header palsu diabaikan
    }

    public function test_ip_asli_terbaca_lewat_proxy_yang_dipercaya(): void
    {
        $this->assertSame('203.0.113.9', $this->ipDiAuditLogin('10.0.0.0/8'));
    }

    public function test_admin_tidak_bisa_login_dengan_pin_tapi_kasir_bisa(): void
    {
        $b = $this->business('CS-AAA-1111');
        $this->user($b, 'admin', ['pin' => '482913']);
        $this->user($b, 'kasir', ['pin' => '715264']);

        $this->post(route('login.pin'), ['store_code' => 'CS-AAA-1111', 'pin' => '482913'])->assertRedirect();
        $this->assertGuest();

        $this->post(route('login.pin'), ['store_code' => 'CS-AAA-1111', 'pin' => '715264']);
        $this->assertAuthenticated();
    }

    public function test_login_pin_dibatasi_setelah_5_percobaan(): void
    {
        $this->business('CS-AAA-1111');
        for ($i = 0; $i < 5; $i++) {
            $this->post(route('login.pin'), ['store_code' => 'CS-AAA-1111', 'pin' => '000001']);
        }
        $this->post(route('login.pin'), ['store_code' => 'CS-AAA-1111', 'pin' => '000002'])->assertStatus(429);
    }

    public function test_kasir_tidak_bisa_mengakses_halaman_admin(): void
    {
        $b = $this->business('CS-AAA-1111');
        $kasir = $this->user($b, 'kasir');

        foreach (['dashboard', 'reports.index', 'staff.index', 'audit.index', 'settings.edit', 'stock.index'] as $r) {
            $this->actingAs($kasir)->get(route($r))->assertForbidden();
        }
    }

    public function test_akun_dinonaktifkan_langsung_terlempar_walau_sesi_masih_hidup(): void
    {
        $b = $this->business('CS-AAA-1111');
        $kasir = $this->user($b, 'kasir');

        $this->actingAs($kasir)->get(route('pos'))->assertOk();

        $kasir->forceFill(['is_active' => false])->save();

        $this->actingAs($kasir->fresh())->get(route('pos'))->assertRedirect();
        $this->assertGuest();
    }

    public function test_diskon_kasir_dibatasi_server(): void
    {
        $b = $this->business('CS-AAA-1111');
        $admin = $this->user($b, 'admin');
        $kasir = $this->user($b, 'kasir');
        $p = $this->product($admin);
        $this->openShift($kasir);

        // subtotal 20.000, diskon 50% > batas 10%
        $this->actingAs($kasir)->postJson(route('pos.checkout'), $this->payload($p, ['discount' => 10000]))
            ->assertStatus(422);

        $this->actingAs($kasir)->postJson(route('pos.checkout'), $this->payload($p, ['discount' => 1000]))
            ->assertCreated();
    }

    public function test_checkout_ditolak_tanpa_shift_jika_fitur_shift_aktif(): void
    {
        $b = $this->business('CS-AAA-1111');
        $b->forceFill(['shift_enabled' => true])->save();
        $admin = $this->user($b, 'admin');
        $kasir = $this->user($b, 'kasir');
        $p = $this->product($admin);

        $this->actingAs($kasir)->postJson(route('pos.checkout'), $this->payload($p))->assertStatus(422);
    }

    public function test_pembayaran_manual_non_tunai_wajib_nomor_referensi(): void
    {
        $b = $this->business('CS-AAA-1111');
        $admin = $this->user($b, 'admin');
        $kasir = $this->user($b, 'kasir');
        $p = $this->product($admin);
        $this->openShift($kasir);

        $card = $this->payload($p, ['payment_method' => 'card']);
        $this->actingAs($kasir)->postJson(route('pos.checkout'), $card)
            ->assertStatus(422)->assertJsonValidationErrors('payment_ref');

        $this->actingAs($kasir)->postJson(route('pos.checkout'), $card + ['payment_ref' => 'APPR-123456'])
            ->assertCreated();
        $this->assertTrue(AuditLog::withoutGlobalScopes()->where('action', 'order.manual_payment')->exists());
    }

    public function test_qris_manual_ditolak_saat_qris_dinamis_aktif(): void
    {
        $b = $this->business('CS-AAA-1111');
        $b->forceFill(['midtrans_server_key' => 'SB-Mid-server-test1234'])->save();   // key kini per bisnis, bukan config
        $admin = $this->user($b, 'admin');
        $kasir = $this->user($b, 'kasir');
        $p = $this->product($admin);
        $this->openShift($kasir);

        $this->actingAs($kasir)->postJson(route('pos.checkout'), $this->payload($p, ['payment_method' => 'qris', 'payment_ref' => 'X1']))
            ->assertStatus(422);
        $this->assertSame(0, Order::withoutGlobalScopes()->count());
    }

    public function test_checkout_idempotent_tidak_menggandakan_transaksi_dan_stok(): void
    {
        $b = $this->business('CS-AAA-1111');
        $admin = $this->user($b, 'admin');
        $kasir = $this->user($b, 'kasir');
        $p = $this->product($admin, 10);
        $this->openShift($kasir);
        $payload = $this->payload($p);

        $r1 = $this->actingAs($kasir)->postJson(route('pos.checkout'), $payload)->assertCreated();
        $r2 = $this->actingAs($kasir)->postJson(route('pos.checkout'), $payload)->assertCreated();

        $this->assertSame($r1->json('invoice_no'), $r2->json('invoice_no'));
        Auth::setUser($admin);
        $this->assertSame(1, Order::count());
        $this->assertSame(8, $p->fresh()->stock);   // 10 - 2, bukan 10 - 4
    }

    public function test_void_dua_kali_tidak_menggandakan_stok(): void
    {
        $b = $this->business('CS-AAA-1111');
        $admin = $this->user($b, 'admin');
        $kasir = $this->user($b, 'kasir');
        $p = $this->product($admin, 10);
        $this->openShift($kasir);
        $id = $this->actingAs($kasir)->postJson(route('pos.checkout'), $this->payload($p))->json('order_id');

        Auth::forgetGuards();
        $this->actingAs($admin)->post(route('orders.void', $id), ['reason' => 'salah input']);
        $this->actingAs($admin)->post(route('orders.void', $id), ['reason' => 'salah input lagi']);

        $this->assertSame(10, $p->fresh()->stock);   // kembali ke 10, bukan 12
    }

    public function test_kasir_tidak_bisa_void_dan_void_wajib_alasan(): void
    {
        $b = $this->business('CS-AAA-1111');
        $admin = $this->user($b, 'admin');
        $kasir = $this->user($b, 'kasir');
        $p = $this->product($admin);
        $this->openShift($kasir);
        $id = $this->actingAs($kasir)->postJson(route('pos.checkout'), $this->payload($p))->json('order_id');

        $this->actingAs($kasir)->postJson(route('orders.void', $id), ['reason' => 'x'])->assertForbidden();

        // ganti pengguna di tes yang sama: bersihkan sesi supaya tidak terbawa dari request kasir
        $this->flushSession();
        Auth::forgetGuards();

        $this->actingAs($admin)->postJson(route('orders.void', $id), [])
            ->assertStatus(422)->assertJsonValidationErrors('reason');
    }

    public function test_pin_lemah_ditolak_saat_membuat_staff(): void
    {
        $b = $this->business('CS-AAA-1111');
        $admin = $this->user($b, 'admin');

        $this->actingAs($admin)->post(route('staff.store'), [
            'name' => 'Kasir Baru', 'email' => 'baru@t.test', 'role' => 'kasir',
            'password' => 'Str0ng-Passw0rd!', 'pin' => '123456', 'is_active' => 1,
        ])->assertSessionHasErrors('pin');
    }
}
