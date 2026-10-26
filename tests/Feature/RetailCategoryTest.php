<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\BusinessCategory;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Database\Seeders\BusinessCategorySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Tests\TestCase;

class RetailCategoryTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(BusinessCategorySeeder::class);
    }

    public function test_kategori_retail_aktif_dan_bisa_didaftarkan(): void
    {
        $cat = BusinessCategory::where('slug', 'retail')->first();
        $this->assertNotNull($cat);
        $this->assertTrue($cat->is_active);
        $this->assertEquals('retail', $cat->default_mode);

        $res = $this->post(route('register'), [
            'owner_name' => 'Toko Barokah',
            'business_name' => 'Minimarket Barokah',
            'email' => 'barokah@toko.test',
            'phone' => '081234567890',
            'password' => 'B4rokah-M4rket!',
            'category' => 'retail',
            'outlet_count' => '1 Outlet',
            'location' => 'Surakarta',
            'agree' => '1',
        ]);

        $res->assertRedirect();
        $business = Business::where('email', 'barokah@toko.test')->first();
        $this->assertNotNull($business);
        $this->assertEquals('retail', $business->mode);
        $this->assertTrue($business->isRetail());

        // Preset kategori produk minimarket otomatis dibuat
        $categories = Category::withoutGlobalScopes()->where('business_id', $business->id)->pluck('name');
        $this->assertTrue($categories->contains('Sembako'));
        $this->assertTrue($categories->contains('Makanan & Snack'));
    }

    public function test_pos_retail_otomatis_takeaway_dan_tanpa_meja(): void
    {
        $cat = BusinessCategory::where('slug', 'retail')->first();
        $business = Business::create([
            'business_category_id' => $cat->id,
            'code' => 'CS-BRK-1234',
            'name' => 'Minimarket Barokah',
            'owner_name' => 'Budi',
            'email' => 'budi@barokah.test',
            'status' => 'active',
            'mode' => 'retail',
            'tax_rate' => 0,
            'shift_enabled' => false,
        ]);

        $user = User::create([
            'business_id' => $business->id,
            'name' => 'Kasir Ritel',
            'email' => 'kasir@barokah.test',
            'password' => 'K4s1r-R1tel123!',
            'role' => 'kasir',
            'is_active' => true,
        ]);

        Auth::setUser($user);
        $prod = Product::create([
            'sku' => 'BRK-001',
            'name' => 'Beras Ramos 5kg',
            'price' => 75000,
            'cost_price' => 68000,
            'stock' => 50,
            'min_stock' => 5,
            'is_active' => true,
        ]);

        // Request checkout dari POS kasir ritel (tanpa kirim order_type dan table_no)
        $res = $this->actingAs($user)->postJson(route('pos.checkout'), [
            'idempotency_key' => (string) Str::uuid(),
            'items' => [
                ['product_id' => $prod->id, 'qty' => 2],
            ],
            'payment_method' => 'cash',
            'paid_amount' => 150000,
        ]);

        $res->assertCreated();
        $order = Order::where('invoice_no', $res->json('invoice_no'))->first();
        $this->assertNotNull($order);
        $this->assertEquals('takeaway', $order->order_type);
        $this->assertNull($order->table_no);
        $this->assertEquals(150000, (float) $order->subtotal);
    }
}
