<?php

namespace Tests\Feature;

use App\Models\BusinessCategory;
use App\Models\ChatConversation;
use App\Models\ChatMessage;
use App\Models\PlatformAdmin;
use App\Models\PlatformAuditLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * Chat customer service di landing page + inbox admin platform.
 * Belum pernah dijalankan oleh penulis (tidak ada PHP di lingkungan penyusunan) — jalankan:
 *   php artisan test --filter=ChatWidgetTest
 * Semua panggilan AI dipalsukan lewat Http::fake(); tidak ada request keluar. Login admin memakai alur asli
 * (POST /platform/login), sama seperti PlatformAdminTest.
 */
class ChatWidgetTest extends TestCase
{
    use RefreshDatabase;

    private const PASS = 'Str0ng-Passw0rd!';

    protected function setUp(): void
    {
        parent::setUp();

        // Default: provider utama terisi, cadangan kosong, batas longgar. Tiap tes menimpa yang perlu.
        config([
            'services.ai.base_url' => 'https://primary.test/v1',
            'services.ai.api_key' => 'key-utama',
            'services.ai.model' => 'model-utama',
            'services.ai.fallback.base_url' => null,
            'services.ai.fallback.api_key' => null,
            'services.ai.fallback.model' => null,
            'services.ai.daily_limit' => 100,
            'chat.limits.ip_per_minute' => 100,
            'chat.limits.ip_per_hour' => 1000,
            'chat.limits.new_conversations_per_hour' => 100,
            'chat.limits.conversation_per_minute' => 100,
            'chat.limits.poll_per_minute' => 100,
        ]);
    }

    private function aiOk(string $text = 'Halo, ada yang bisa dibantu?'): void
    {
        Http::fake(['primary.test/*' => Http::response(['choices' => [['message' => ['content' => $text]]]], 200)]);
    }

    private function kirim(string $pesan, ?string $token = null)
    {
        return $this->postJson(route('chat.send'), ['message' => $pesan], $token ? ['X-Chat-Token' => $token] : []);
    }

    private function admin(string $email = 'pemilik@t.test'): PlatformAdmin
    {
        return PlatformAdmin::create(['name' => 'Pemilik', 'email' => $email, 'password' => self::PASS, 'is_active' => true]);
    }

    private function login(PlatformAdmin $a)
    {
        return $this->post(route('platform.login.store'), ['email' => $a->email, 'password' => self::PASS]);
    }

    public function test_pengunjung_kirim_pesan_dan_dapat_balasan_ai(): void
    {
        $this->aiOk('Gratis 14 hari.');

        $res = $this->kirim('Apakah gratis?')->assertOk()->assertJsonPath('status', 'ai');
        $token = $res->json('token');

        $this->assertMatchesRegularExpression('/^[A-Za-z0-9]{40}$/', $token);
        $this->assertSame(['visitor', 'ai'], collect($res->json('messages'))->pluck('role')->all());
        $this->assertSame('Gratis 14 hari.', $res->json('messages.1.body'));

        $c = ChatConversation::firstOrFail();
        $this->assertSame($token, $c->token);
        $this->assertSame('127.0.0.1', $c->ip);
        $this->assertNotNull($c->last_active_at);
        $this->assertFalse($c->needs_admin);

        Http::assertSentCount(1);
        Http::assertSent(function ($r) {
            $msgs = $r['messages'];

            return $r->url() === 'https://primary.test/v1/chat/completions'
                && $r->hasHeader('Authorization', 'Bearer key-utama')
                && $r['model'] === 'model-utama'
                && $msgs[0]['role'] === 'system'
                && $msgs[count($msgs) - 1]['content'] === 'Apakah gratis?';
        });
    }

    public function test_pesan_divalidasi_dan_dibersihkan(): void
    {
        $this->aiOk();

        $this->kirim('   ')->assertStatus(422);
        $this->kirim(str_repeat('a', 501))->assertStatus(422);
        $this->postJson(route('chat.send'), ['message' => ['bukan', 'teks']])->assertStatus(422);
        $this->assertSame(0, ChatConversation::count());   // pesan tidak valid tidak membuat percakapan

        $this->kirim("Halo\x00\x07 dunia")->assertOk();
        $this->assertSame('Halo dunia', ChatMessage::where('role', 'visitor')->firstOrFail()->body);
    }

    public function test_pesan_di_escape_saat_tampil_di_panel_admin(): void
    {
        $this->aiOk();
        $this->kirim('<script>alert(1)</script>')->assertOk();
        $c = ChatConversation::firstOrFail();

        $this->login($this->admin());
        $this->get(route('platform.chat.show', $c))->assertOk()
            ->assertSee('&lt;script&gt;alert(1)&lt;/script&gt;', false)
            ->assertDontSee('<script>alert(1)</script>', false);
    }

    public function test_batas_per_ip(): void
    {
        $this->aiOk();
        config(['chat.limits.ip_per_minute' => 3]);

        for ($i = 0; $i < 3; $i++) {
            $this->kirim('Halo')->assertOk();
        }
        $this->kirim('Halo')->assertStatus(429)->assertJsonPath('error', 'rate_limited');
    }

    public function test_batas_percakapan_baru_per_ip(): void
    {
        $this->aiOk();
        config(['chat.limits.new_conversations_per_hour' => 2]);

        $this->kirim('Satu')->assertOk();
        $this->kirim('Dua')->assertOk();
        $this->kirim('Tiga')->assertStatus(429);
        $this->assertSame(2, ChatConversation::count());
    }

    public function test_batas_per_percakapan_per_menit_dan_total_pesan(): void
    {
        $this->aiOk();
        config(['chat.limits.conversation_per_minute' => 2]);

        $token = $this->kirim('Satu')->json('token');
        $this->kirim('Dua', $token)->assertOk();
        $this->kirim('Tiga', $token)->assertStatus(429)->assertJsonPath('error', 'rate_limited');

        // batas total pesan pengunjung per percakapan
        config(['chat.limits.conversation_per_minute' => 100, 'chat.max_visitor_messages' => 2]);
        $this->kirim('Empat', $token)->assertStatus(429)->assertJsonPath('error', 'limit');
    }

    public function test_fallback_tanpa_api_key_tanpa_panggilan_http(): void
    {
        Http::fake();
        config(['services.ai.api_key' => null]);

        $res = $this->kirim('Halo')->assertOk();

        $this->assertSame(config('chat.fallback_message'), $res->json('messages.1.body'));
        $this->assertTrue(ChatConversation::firstOrFail()->needs_admin);
        Http::assertNothingSent();
    }

    public function test_fallback_saat_provider_mengembalikan_429(): void
    {
        Http::fake(['primary.test/*' => Http::response(['error' => 'quota'], 429)]);

        $res = $this->kirim('Halo')->assertOk();

        $this->assertSame(config('chat.fallback_message'), $res->json('messages.1.body'));
        $this->assertTrue(ChatConversation::firstOrFail()->needs_admin);
        Http::assertSentCount(1);
    }

    public function test_provider_cadangan_dipakai_saat_utama_429(): void
    {
        config([
            'services.ai.fallback.base_url' => 'https://backup.test/v1',
            'services.ai.fallback.api_key' => 'key-cadangan',
            'services.ai.fallback.model' => 'model-cadangan',
        ]);
        Http::fake([
            'primary.test/*' => Http::response(['error' => 'quota'], 429),
            'backup.test/*' => Http::response(['choices' => [['message' => ['content' => 'Dari cadangan']]]], 200),
        ]);

        $res = $this->kirim('Halo')->assertOk();

        $this->assertSame('Dari cadangan', $res->json('messages.1.body'));
        $this->assertFalse(ChatConversation::firstOrFail()->needs_admin);
        Http::assertSentCount(2);
    }

    public function test_fallback_bila_utama_dan_cadangan_sama_sama_gagal(): void
    {
        config([
            'services.ai.fallback.base_url' => 'https://backup.test/v1',
            'services.ai.fallback.api_key' => 'key-cadangan',
            'services.ai.fallback.model' => 'model-cadangan',
        ]);
        Http::fake([
            'primary.test/*' => Http::response('boom', 500),
            'backup.test/*' => Http::response(['error' => 'quota'], 429),
        ]);

        $res = $this->kirim('Halo')->assertOk();

        $this->assertSame(config('chat.fallback_message'), $res->json('messages.1.body'));
        $this->assertTrue(ChatConversation::firstOrFail()->needs_admin);
        Http::assertSentCount(2);
    }

    public function test_batas_harian_global_memakai_fallback(): void
    {
        $this->aiOk('Balasan AI');
        config(['services.ai.daily_limit' => 1]);

        $token = $this->kirim('Satu')->json('token');
        $this->assertSame('Balasan AI', ChatMessage::where('role', 'ai')->firstOrFail()->body);

        $res = $this->kirim('Dua', $token)->assertOk();
        $this->assertSame(config('chat.fallback_message'), collect($res->json('messages'))->last()['body']);
        Http::assertSentCount(1);   // panggilan kedua tidak pernah dikirim
    }

    public function test_ambil_alih_admin_ai_berhenti_membalas_lalu_dikembalikan(): void
    {
        $this->aiOk();
        $token = $this->kirim('Halo')->json('token');
        $c = ChatConversation::firstOrFail();

        $this->login($this->admin());
        $this->post(route('platform.chat.takeover', $c))->assertSessionHasNoErrors();
        $this->assertSame('human', $c->fresh()->status);
        $this->assertNotNull($c->fresh()->handled_by);
        $this->assertTrue(PlatformAuditLog::where('action', 'chat.takeover')->where('target', 'chat#'.$c->id)->exists());

        // mode human: pesan pengunjung tersimpan, AI TIDAK membalas dan tidak ada panggilan HTTP baru
        $res = $this->kirim('Ada orang?', $token)->assertOk()->assertJsonPath('status', 'human');
        $this->assertSame('visitor', collect($res->json('messages'))->last()['role']);
        $this->assertSame(1, ChatMessage::where('role', 'ai')->count());
        Http::assertSentCount(1);

        // admin membalas; pengunjung menerimanya lewat polling
        $this->post(route('platform.chat.reply', $c), ['message' => 'Halo, saya admin.'])->assertSessionHasNoErrors();
        $poll = $this->getJson(route('chat.poll'), ['X-Chat-Token' => $token])->assertOk()->assertJsonPath('status', 'human');
        $last = collect($poll->json('messages'))->last();
        $this->assertSame('admin', $last['role']);
        $this->assertSame('Halo, saya admin.', $last['body']);
        $this->assertTrue(PlatformAuditLog::where('action', 'chat.reply')->exists());

        // dikembalikan ke AI: AI membalas lagi
        $this->post(route('platform.chat.release', $c))->assertSessionHasNoErrors();
        $this->assertSame('ai', $c->fresh()->status);
        $this->assertTrue(PlatformAuditLog::where('action', 'chat.release')->exists());

        $this->kirim('Masih ada?', $token)->assertOk();
        Http::assertSentCount(2);
        $this->assertSame(2, ChatMessage::where('role', 'ai')->count());
    }

    public function test_admin_tidak_bisa_membalas_sebelum_ambil_alih(): void
    {
        $this->aiOk();
        $this->kirim('Halo')->assertOk();
        $c = ChatConversation::firstOrFail();

        $this->login($this->admin());
        $this->post(route('platform.chat.reply', $c), ['message' => 'Halo'])->assertSessionHasErrors('message');
        $this->assertSame(0, ChatMessage::where('role', 'admin')->count());
    }

    public function test_tutup_percakapan_menolak_pesan_baru(): void
    {
        $this->aiOk();
        $token = $this->kirim('Halo')->json('token');
        $c = ChatConversation::firstOrFail();

        $this->login($this->admin());
        $this->post(route('platform.chat.close', $c))->assertSessionHasNoErrors();
        $this->assertSame('closed', $c->fresh()->status);
        $this->assertTrue(PlatformAuditLog::where('action', 'chat.close')->exists());

        $this->kirim('Masih di sana?', $token)->assertStatus(409)->assertJsonPath('error', 'closed');
    }

    public function test_inbox_dan_polling_admin(): void
    {
        config(['services.ai.api_key' => null]);
        $this->kirim('Tolong bantu soal QRIS')->assertOk();
        $c = ChatConversation::firstOrFail();

        $this->get(route('platform.chat.index'))->assertRedirect(route('platform.login'));

        $this->login($this->admin());
        $this->get(route('platform.chat.index'))->assertOk()->assertSee('Pengunjung #'.$c->id)->assertSee('Perlu admin');
        $this->get(route('platform.chat.index', ['status' => 'closed']))->assertOk()->assertDontSee('Pengunjung #'.$c->id);
        $this->getJson(route('platform.chat.poll'))->assertOk()->assertJsonStructure(['html']);
        $this->getJson(route('platform.chat.messages', $c))->assertOk()->assertJsonPath('status', 'ai')->assertJsonCount(2, 'messages');
    }

    public function test_token_tidak_dikenal_ditolak_saat_polling(): void
    {
        $this->getJson(route('chat.poll'))->assertStatus(404);
        $this->getJson(route('chat.poll'), ['X-Chat-Token' => str_repeat('a', 40)])->assertStatus(404);
        $this->getJson(route('chat.poll'), ['X-Chat-Token' => '../../etc/passwd'])->assertStatus(404);
    }

    public function test_saklar_chat_nonaktif_dan_widget_tampil_di_landing(): void
    {
        BusinessCategory::create([
            'slug' => 'cafe', 'name' => 'Cafe', 'icon' => 'x', 'color' => 'blue', 'tagline' => 't',
            'features' => 'x', 'badge' => null, 'preset' => [], 'is_active' => true, 'sort_order' => 1,
        ]);

        $this->get(route('landing'))->assertOk()
            ->assertSee('id="cs-chat"', false)
            ->assertSee('Jangan kirim data sensitif (password, PIN, data kartu)', false);

        config(['chat.enabled' => false]);
        $this->get(route('landing'))->assertOk()->assertDontSee('id="cs-chat"', false);
        $this->kirim('Halo')->assertNotFound();
    }
}
