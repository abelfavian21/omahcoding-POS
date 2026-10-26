<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\BusinessCategory;
use App\Models\CashShift;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * Tes QRIS manual (gambar QRIS statis per bisnis + opsi nomor referensi).
 * Belum pernah dijalankan (tidak ada PHP saat ditulis):  php artisan test --filter=ManualQrisTest
 * Upload gambar memakai UploadedFile::fake()->image() sehingga butuh ekstensi GD (aktif di Laragon).
 */
class ManualQrisTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
    }

    /** Ganti user di tengah tes dengan sesi bersih (AuthenticateSession menolak sesi yang hash passwordnya milik user lain). */
    private function as(User $u): static
    {
        $this->flushSession();
        $this->app['auth']->forgetGuards();

        return $this->actingAs($u);
    }

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

    private function openShift(User $u): void
    {
        Auth::setUser($u);
        CashShift::create(['user_id' => $u->id, 'opened_at' => now(), 'opening_cash' => 0]);
        Auth::forgetGuards();
    }

    private function qrisPayload(Product $p, array $over = []): array
    {
        return $over + [
            'idempotency_key' => (string) Str::uuid(), 'items' => [['product_id' => $p->id, 'qty' => 2]],
            'order_type' => 'takeaway', 'payment_method' => 'qris', 'paid_amount' => 20000,
        ];
    }

    /** Pasang gambar QRIS langsung ke bisnis (tanpa lewat form). */
    private function giveQris(Business $b, bool $requireRef = false): string
    {
        $path = 'qris/' . $b->id . '-seed.png';
        Storage::disk('local')->put($path, UploadedFile::fake()->image('q.png', 400, 400)->getContent());
        $b->forceFill(['qris_image' => $path, 'qris_require_ref' => $requireRef])->save();

        return $path;
    }

    public function test_qris_manual_tanpa_gambar_mengikuti_pengaturan_referensi(): void
    {
        $b = $this->business('CS-AAA-1111');
        $b->forceFill(['qris_require_ref' => true])->save();
        $admin = $this->user($b, 'admin');
        $kasir = $this->user($b, 'kasir');
        $p = $this->product($admin);
        $this->openShift($kasir);

        $this->actingAs($kasir)->postJson(route('pos.checkout'), $this->qrisPayload($p))
            ->assertStatus(422)->assertJsonValidationErrors('payment_ref');
        $this->assertSame(0, Order::withoutGlobalScopes()->count());

        $b->forceFill(['qris_require_ref' => false])->save();
        $this->actingAs($kasir->fresh())->postJson(route('pos.checkout'), $this->qrisPayload($p))->assertCreated();
        $this->assertSame(1, Order::withoutGlobalScopes()->count());
    }

    public function test_qris_manual_dengan_gambar_tidak_wajib_referensi(): void
    {
        $b = $this->business('CS-AAA-1111');
        $this->giveQris($b);
        $admin = $this->user($b, 'admin');
        $kasir = $this->user($b, 'kasir');
        $p = $this->product($admin);
        $this->openShift($kasir);

        $this->actingAs($kasir)->postJson(route('pos.checkout'), $this->qrisPayload($p))->assertCreated();
        $this->assertSame(1, Order::withoutGlobalScopes()->count());
    }

    public function test_qris_manual_dengan_gambar_tetap_wajib_referensi_bila_admin_mewajibkan(): void
    {
        $b = $this->business('CS-AAA-1111');
        $this->giveQris($b, requireRef: true);
        $admin = $this->user($b, 'admin');
        $kasir = $this->user($b, 'kasir');
        $p = $this->product($admin);
        $this->openShift($kasir);

        $this->actingAs($kasir)->postJson(route('pos.checkout'), $this->qrisPayload($p))
            ->assertStatus(422)->assertJsonValidationErrors('payment_ref');
        $this->actingAs($kasir)->postJson(route('pos.checkout'), $this->qrisPayload($p, ['payment_ref' => 'REF-1']))
            ->assertCreated();
    }

    public function test_kartu_tetap_selalu_wajib_referensi_walau_ada_gambar_qris(): void
    {
        $b = $this->business('CS-AAA-1111');
        $this->giveQris($b);
        $admin = $this->user($b, 'admin');
        $kasir = $this->user($b, 'kasir');
        $p = $this->product($admin);
        $this->openShift($kasir);

        $this->actingAs($kasir)->postJson(route('pos.checkout'), $this->qrisPayload($p, ['payment_method' => 'card']))
            ->assertStatus(422)->assertJsonValidationErrors('payment_ref');
    }

    public function test_admin_bisa_unggah_gambar_qris_dan_opsi_tersimpan(): void
    {
        $b = $this->business('CS-AAA-1111');
        $admin = $this->user($b, 'admin');

        $this->actingAs($admin)->put(route('settings.qris'), [
            'qris_image' => UploadedFile::fake()->image('qris.png', 400, 400),
            'qris_require_ref' => '1',
        ])->assertRedirect(route('settings.edit'));

        $b->refresh();
        $this->assertNotNull($b->qris_image);
        $this->assertTrue($b->qris_require_ref);
        Storage::disk('local')->assertExists($b->qris_image);
        $this->assertTrue($b->hasManualQris());
    }

    public function test_mengganti_gambar_menghapus_file_lama(): void
    {
        $b = $this->business('CS-AAA-1111');
        $admin = $this->user($b, 'admin');
        $old = $this->giveQris($b);

        $this->actingAs($admin)->put(route('settings.qris'), ['qris_image' => UploadedFile::fake()->image('baru.png', 400, 400)])
            ->assertRedirect(route('settings.edit'));

        $b->refresh();
        $this->assertNotSame($old, $b->qris_image);
        Storage::disk('local')->assertMissing($old);
        Storage::disk('local')->assertExists($b->qris_image);
    }

    public function test_hapus_gambar_mengosongkan_dan_menghapus_file(): void
    {
        $b = $this->business('CS-AAA-1111');
        $admin = $this->user($b, 'admin');
        $path = $this->giveQris($b);

        $this->actingAs($admin)->put(route('settings.qris'), ['remove_image' => '1'])->assertRedirect(route('settings.edit'));

        $this->assertNull($b->refresh()->qris_image);
        Storage::disk('local')->assertMissing($path);
        $this->assertFalse($b->qrisManualNeedsRef());   // default qris_require_ref false
    }

    public function test_file_bukan_gambar_dan_svg_ditolak(): void
    {
        $b = $this->business('CS-AAA-1111');
        $admin = $this->user($b, 'admin');

        $this->actingAs($admin)->put(route('settings.qris'), ['qris_image' => UploadedFile::fake()->create('shell.php', 10, 'application/x-php')])
            ->assertSessionHasErrors('qris_image');
        $this->actingAs($admin)->put(route('settings.qris'), ['qris_image' => UploadedFile::fake()->create('x.svg', 10, 'image/svg+xml')])
            ->assertSessionHasErrors('qris_image');
        $this->assertNull($b->refresh()->qris_image);
    }

    public function test_kasir_tidak_bisa_mengubah_pengaturan_qris(): void
    {
        $b = $this->business('CS-AAA-1111');
        $kasir = $this->user($b, 'kasir');

        $this->actingAs($kasir)->put(route('settings.qris'), ['qris_image' => UploadedFile::fake()->image('q.png', 400, 400)])
            ->assertForbidden();
        $this->assertNull($b->refresh()->qris_image);
    }

    public function test_gambar_qris_hanya_untuk_bisnis_sendiri(): void
    {
        $a = $this->business('CS-AAA-1111');
        $this->giveQris($a);
        $kasirA = $this->user($a, 'kasir');
        $bb = $this->business('CS-BBB-2222');
        $kasirB = $this->user($bb, 'kasir');

        $this->as($kasirA)->get(route('pos.qris.manual'))->assertOk();
        $this->as($kasirB)->get(route('pos.qris.manual'))->assertNotFound();   // B tidak punya gambar & tidak bisa melihat milik A
        $this->flushSession();
        $this->app['auth']->forgetGuards();
        $this->get(route('pos.qris.manual'))->assertRedirect();                // tamu: harus login
    }
}
