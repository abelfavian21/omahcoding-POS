<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\BusinessCategory;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * Tes cetak struk thermal (aktif/nonaktif di Pengaturan).
 * Belum pernah dijalankan (tidak ada PHP saat ditulis):  php artisan test --filter=ReceiptPrintTest
 */
class ReceiptPrintTest extends TestCase
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

    private function checkout(User $kasir, Product $p)
    {
        return $this->actingAs($kasir)->postJson(route('pos.checkout'), [
            'idempotency_key' => (string) Str::uuid(), 'items' => [['product_id' => $p->id, 'qty' => 1]],
            'order_type' => 'takeaway', 'payment_method' => 'cash', 'paid_amount' => 100000,
        ])->assertCreated();
    }

    public function test_default_nonaktif_dan_halaman_thermal_404(): void
    {
        $b = $this->business('CS-AAA-1111');
        $this->assertFalse($b->refresh()->receiptPrintEnabled());
        $this->assertFalse($b->receiptAutoPrint());
        $this->assertSame(80, $b->receiptPaperMm());

        $kasir = $this->user($b, 'kasir');
        $p = $this->product($this->user($b, 'admin'));
        $order = $this->checkout($kasir, $p)->json();

        $this->actingAs($kasir)->get(route('orders.receipt.thermal', $order['order_id']))->assertNotFound();
    }

    public function test_admin_mengaktifkan_lalu_struk_thermal_tampil_dan_boleh_di_frame(): void
    {
        $b = $this->business('CS-AAA-1111');
        $admin = $this->user($b, 'admin');
        $kasir = $this->user($b, 'kasir');
        $p = $this->product($admin);

        $this->actingAs($admin)->put(route('settings.print'), [
            'receipt_print_enabled' => '1', 'receipt_auto_print' => '1', 'receipt_paper' => '58',
        ])->assertRedirect(route('settings.edit'));
        $b->refresh();
        $this->assertTrue($b->receiptPrintEnabled());
        $this->assertTrue($b->receiptAutoPrint());
        $this->assertSame(58, $b->receiptPaperMm());

        $co = $this->as($kasir)->checkout($kasir, $p)->assertJsonStructure(['thermal_url'])->json();
        $res = $this->as($kasir)->get($co['thermal_url'])->assertOk()->assertSee('TOTAL')->assertSee('window.print()', false);
        $this->assertSame('SAMEORIGIN', $res->headers->get('X-Frame-Options'));

        // tanpa ?print=1 tidak ada skrip cetak (dipakai untuk pratinjau)
        $this->as($kasir)->get(route('orders.receipt.thermal', $co['order_id']))->assertOk()->assertDontSee('window.print()', false);
    }

    public function test_menonaktifkan_menghapus_akses_thermal(): void
    {
        $b = $this->business('CS-AAA-1111');
        $admin = $this->user($b, 'admin');
        $kasir = $this->user($b, 'kasir');
        $p = $this->product($admin);
        $b->forceFill(['receipt_print_enabled' => true])->save();
        $order = $this->checkout($kasir, $p)->json();

        $this->as($admin)->put(route('settings.print'), ['receipt_paper' => '80'])->assertRedirect(route('settings.edit'));
        $this->assertFalse($b->refresh()->receiptPrintEnabled());
        $this->as($kasir)->get(route('orders.receipt.thermal', $order['order_id']))->assertNotFound();
    }

    public function test_halaman_lain_tetap_tidak_boleh_di_frame(): void
    {
        $b = $this->business('CS-AAA-1111');
        $kasir = $this->user($b, 'kasir');

        $res = $this->actingAs($kasir)->get(route('pos'))->assertOk();
        $this->assertSame('DENY', $res->headers->get('X-Frame-Options'));
    }

    public function test_kasir_tidak_bisa_mengubah_pengaturan_cetak(): void
    {
        $b = $this->business('CS-AAA-1111');
        $kasir = $this->user($b, 'kasir');

        $this->actingAs($kasir)->put(route('settings.print'), ['receipt_print_enabled' => '1', 'receipt_paper' => '80'])->assertForbidden();
        $this->assertFalse($b->refresh()->receiptPrintEnabled());
    }

    public function test_lebar_kertas_tidak_valid_ditolak(): void
    {
        $b = $this->business('CS-AAA-1111');
        $admin = $this->user($b, 'admin');

        $this->actingAs($admin)->put(route('settings.print'), ['receipt_print_enabled' => '1', 'receipt_paper' => '100'])->assertSessionHasErrors('receipt_paper');
        $this->assertFalse($b->refresh()->receiptPrintEnabled());
    }

    public function test_kasir_tidak_bisa_membuka_struk_thermal_milik_kasir_lain(): void
    {
        $b = $this->business('CS-AAA-1111');
        $admin = $this->user($b, 'admin');
        $k1 = $this->user($b, 'kasir');
        $k2 = $this->user($b, 'kasir');
        $p = $this->product($admin);
        $b->forceFill(['receipt_print_enabled' => true])->save();
        $order = $this->checkout($k1, $p)->json();

        $this->as($k2)->get(route('orders.receipt.thermal', $order['order_id']))->assertForbidden();
    }

    public function test_panduan_memuat_bagian_thermal_untuk_admin_dan_kasir(): void
    {
        $b = $this->business('CS-AAA-1111');
        $admin = $this->user($b, 'admin');
        $kasir = $this->user($b, 'kasir');

        $this->actingAs($admin)->get(route('help'))->assertOk()->assertSee('id="thermal"', false)->assertSee('Menyiapkan (admin, sekali saja)');
        $this->as($kasir)->get(route('help'))->assertOk()->assertSee('id="thermal"', false)->assertDontSee('Menyiapkan (admin, sekali saja)');
    }

    /** Ganti user di tengah tes dengan sesi bersih. */
    private function as(User $u): static
    {
        $this->flushSession();
        $this->app['auth']->forgetGuards();

        return $this->actingAs($u);
    }
}
