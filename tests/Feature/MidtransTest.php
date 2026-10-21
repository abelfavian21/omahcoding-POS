<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\BusinessCategory;
use App\Models\QrisPayment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * Tes webhook Midtrans (Server Key per bisnis).
 * Belum pernah dijalankan (tidak ada PHP saat ditulis): jalankan  php artisan test --filter=MidtransTest
 */
class MidtransTest extends TestCase
{
    use RefreshDatabase;

    private const KEY = 'SB-Mid-server-test1234';

    private function business(string $code, ?string $key = self::KEY): Business
    {
        $cat = BusinessCategory::first() ?? BusinessCategory::create([
            'slug' => 'cafe', 'name' => 'Cafe', 'icon' => 'x', 'color' => 'blue', 'tagline' => 't',
            'features' => 'x', 'badge' => null, 'preset' => [], 'is_active' => true, 'sort_order' => 1,
        ]);
        $b = Business::create([
            'business_category_id' => $cat->id, 'code' => $code, 'name' => $code, 'owner_name' => 'Owner',
            'email' => Str::lower($code) . '@t.test', 'status' => 'active',
        ]);
        $b->forceFill(['midtrans_server_key' => $key])->save();

        return $b;
    }

    private function payment(Business $b, string $orderId): QrisPayment
    {
        $p = new QrisPayment([
            'idempotency_key' => (string) Str::uuid(), 'midtrans_order_id' => $orderId, 'status' => QrisPayment::PENDING,
            'gross_amount' => 20000, 'payload' => [], 'expires_at' => now()->addMinutes(15),
        ]);
        $p->business_id = $b->id;
        $p->save();

        return $p;
    }

    private function notif(string $orderId, string $key, array $over = []): array
    {
        $n = $over + ['order_id' => $orderId, 'status_code' => '200', 'gross_amount' => '20000.00', 'transaction_status' => 'settlement'];
        $n['signature_key'] = hash('sha512', $n['order_id'] . $n['status_code'] . $n['gross_amount'] . $key);

        return $n;
    }

    public function test_notifikasi_order_tak_dikenal_diabaikan(): void
    {
        $this->postJson(route('midtrans.notify'), $this->notif('TIDAK-ADA', self::KEY))
            ->assertOk()->assertJson(['message' => 'ignored']);
    }

    public function test_signature_dengan_key_bisnis_lain_ditolak(): void
    {
        $a = $this->business('CS-AAA-1111');
        $this->business('CS-BBB-2222', 'SB-Mid-server-lain5678');
        $this->payment($a, 'Q1-261021000000-ABCDEF');

        // ditandatangani dengan key bisnis B, tetapi order milik bisnis A
        $this->postJson(route('midtrans.notify'), $this->notif('Q1-261021000000-ABCDEF', 'SB-Mid-server-lain5678'))
            ->assertStatus(403);
        $this->assertSame(QrisPayment::PENDING, QrisPayment::withoutGlobalScopes()->first()->status);
    }

    public function test_bisnis_tanpa_key_tidak_bisa_memvalidasi_signature(): void
    {
        $a = $this->business('CS-AAA-1111', null);
        $this->payment($a, 'Q1-261021000000-ABCDEF');

        $this->postJson(route('midtrans.notify'), $this->notif('Q1-261021000000-ABCDEF', ''))
            ->assertStatus(403);
    }

    public function test_server_key_tidak_ikut_ke_json_bisnis(): void
    {
        $this->assertArrayNotHasKey('midtrans_server_key', $this->business('CS-AAA-1111')->toArray());
    }
}
