<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\BusinessCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * Tes bahasa tampilan (Pengaturan): default Indonesia, bisa diganti ke English, nilai lain ditolak.
 * Belum pernah dijalankan (tidak ada vendor/ saat ditulis):  php artisan test --filter=LanguageSettingTest
 */
class LanguageSettingTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        $cat = BusinessCategory::first() ?? BusinessCategory::create([
            'slug' => 'cafe', 'name' => 'Cafe', 'icon' => 'x', 'color' => 'blue', 'tagline' => 't',
            'features' => 'x', 'badge' => null, 'preset' => [], 'is_active' => true, 'sort_order' => 1,
        ]);
        $b = Business::create([
            'business_category_id' => $cat->id, 'code' => 'LANG1', 'name' => 'Lang', 'owner_name' => 'Owner',
            'email' => 'lang@t.test', 'status' => 'active',
        ]);

        return User::create([
            'business_id' => $b->id, 'name' => 'Admin', 'email' => Str::lower(Str::random(8)) . '@t.test',
            'password' => 'Str0ng-Passw0rd!', 'role' => 'admin', 'is_active' => true,
        ]);
    }

    public function test_default_is_indonesian(): void
    {
        $u = $this->admin();
        $this->assertSame('id', $u->business->fresh()->language);
        $this->actingAs($u)->get(route('pos'))->assertOk()->assertSee('Keranjang')->assertDontSee('Walk-in Customer');
    }

    public function test_switch_to_english_and_back(): void
    {
        $u = $this->admin();
        $this->actingAs($u)->put(route('settings.language'), ['language' => 'en'])->assertRedirect(route('settings.edit'));
        $this->assertSame('en', $u->business->fresh()->language);
        $this->actingAs($u)->get(route('pos'))->assertOk()->assertSee('Cart')->assertDontSee('Keranjang kosong');

        $this->actingAs($u)->put(route('settings.language'), ['language' => 'id']);
        $this->assertSame('id', $u->business->fresh()->language);
    }

    public function test_rejects_unknown_language(): void
    {
        $u = $this->admin();
        $this->actingAs($u)->put(route('settings.language'), ['language' => 'fr'])->assertSessionHasErrors('language');
        $this->assertSame('id', $u->business->fresh()->language);
    }
}
