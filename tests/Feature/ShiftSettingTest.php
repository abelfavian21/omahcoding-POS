<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\BusinessCategory;
use App\Models\CashShift;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * Tes pengaturan shift kasir (aktif/nonaktif per bisnis).
 * Belum pernah dijalankan (tidak ada PHP saat ditulis):  php artisan test --filter=ShiftSettingTest
 */
class ShiftSettingTest extends TestCase
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

    private function user(Business $b, string $role): User
    {
        return User::create([
            'business_id' => $b->id, 'name' => ucfirst($role) . ' ' . Str::random(4),
            'email' => Str::lower(Str::random(8)) . '@t.test', 'password' => 'Str0ng-Passw0rd!',
            'role' => $role, 'is_active' => true,
        ]);
    }

    private function product(User $as): Product
    {
        Auth::setUser($as);
        $p = Product::create(['sku' => Str::random(6), 'name' => 'Kopi', 'price' => 10000, 'cost_price' => 4000, 'stock' => 0, 'min_stock' => 1, 'is_active' => true]);
        $p->adjustStock(10, 'in', 'awal', $as->id);
        Auth::forgetGuards();

        return $p;
    }

    /** Ganti user di tengah tes dengan sesi bersih (AuthenticateSession menolak sesi yang hash passwordnya milik user lain). */
    private function as(User $u): static
    {
        $this->flushSession();
        $this->app['auth']->forgetGuards();

        return $this->actingAs($u);
    }

    private function cash(Product $p): array
    {
        return [
            'idempotency_key' => (string) Str::uuid(), 'items' => [['product_id' => $p->id, 'qty' => 1]],
            'order_type' => 'takeaway', 'payment_method' => 'cash', 'paid_amount' => 100000,   // cukup walau ada pajak
        ];
    }

    public function test_default_shift_aktif_dan_checkout_tanpa_shift_ditolak(): void
    {
        $b = $this->business('CS-AAA-1111');
        $this->assertTrue($b->refresh()->requiresShift());
        $admin = $this->user($b, 'admin');
        $kasir = $this->user($b, 'kasir');
        $p = $this->product($admin);

        $res = $this->actingAs($kasir)->postJson(route('pos.checkout'), $this->cash($p))->assertStatus(422);
        $this->assertStringContainsString('shift', strtolower((string) $res->json('message')));   // ditolak KARENA shift, bukan karena hal lain
        $this->assertSame(0, Order::withoutGlobalScopes()->count());
    }

    public function test_admin_menonaktifkan_shift_kasir_bisa_bayar_tanpa_shift(): void
    {
        $b = $this->business('CS-AAA-1111');
        $admin = $this->user($b, 'admin');
        $kasir = $this->user($b, 'kasir');
        $p = $this->product($admin);

        $this->actingAs($admin)->put(route('settings.shift'), [])->assertRedirect(route('settings.edit'));   // checkbox tidak dicentang = nonaktif
        $this->assertFalse($b->refresh()->requiresShift());

        $this->as($kasir)->postJson(route('pos.checkout'), $this->cash($p))->assertCreated();
        $this->assertSame(1, Order::withoutGlobalScopes()->count());
        $this->assertNull(Order::withoutGlobalScopes()->first()->cash_shift_id);
    }

    public function test_menonaktifkan_shift_ditolak_selama_ada_shift_terbuka(): void
    {
        $b = $this->business('CS-AAA-1111');
        $admin = $this->user($b, 'admin');
        $kasir = $this->user($b, 'kasir');
        Auth::setUser($kasir);
        CashShift::create(['user_id' => $kasir->id, 'opened_at' => now(), 'opening_cash' => 0]);
        Auth::forgetGuards();

        $this->actingAs($admin)->put(route('settings.shift'), [])->assertSessionHasErrors('shift_enabled');
        $this->assertTrue($b->refresh()->requiresShift());
    }

    public function test_halaman_shift_disembunyikan_saat_fitur_dinonaktifkan(): void
    {
        $b = $this->business('CS-AAA-1111');
        $kasir = $this->user($b, 'kasir');

        $this->actingAs($kasir)->get(route('shifts.index'))->assertOk();                 // aktif: halaman terbuka

        $b->forceFill(['shift_enabled' => false])->save();
        $this->as($kasir)->get(route('shifts.index'))->assertRedirect(route('pos'));      // nonaktif: diarahkan ke Order Line
        $this->as($kasir)->post(route('shifts.store'), ['opening_cash' => 0])->assertRedirect(route('pos'));
        $this->assertSame(0, CashShift::withoutGlobalScopes()->count());
        $this->as($kasir)->get(route('pos'))->assertOk()->assertDontSee(route('shifts.index'));   // tautan menu juga hilang
    }

    public function test_kasir_tidak_bisa_mengubah_pengaturan_shift(): void
    {
        $b = $this->business('CS-AAA-1111');
        $kasir = $this->user($b, 'kasir');

        $this->actingAs($kasir)->put(route('settings.shift'), [])->assertForbidden();
        $this->assertTrue($b->refresh()->requiresShift());
    }

    public function test_pengaturan_di_satu_bisnis_tidak_mempengaruhi_bisnis_lain(): void
    {
        $a = $this->business('CS-AAA-1111');
        $bb = $this->business('CS-BBB-2222');
        $adminA = $this->user($a, 'admin');

        $this->actingAs($adminA)->put(route('settings.shift'), []);
        $this->assertFalse($a->refresh()->requiresShift());
        $this->assertTrue($bb->refresh()->requiresShift());
    }
}
