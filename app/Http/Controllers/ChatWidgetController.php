<?php

namespace App\Http\Controllers;

use App\Models\ChatConversation;
use App\Models\ChatMessage;
use App\Services\ChatAiService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;

/**
 * Endpoint publik widget chat di landing page (pengunjung anonim).
 * Identitas pengunjung = token acak 40 karakter di header X-Chat-Token (disimpan di localStorage browser),
 * bukan di URL, supaya tidak masuk access log. Limiter dipakai manual (bukan middleware throttle)
 * agar bisa per IP DAN per percakapan dengan pesan JSON.
 */
class ChatWidgetController extends Controller
{
    public function send(Request $request, ChatAiService $ai): JsonResponse
    {
        abort_unless(config('chat.enabled'), 404);

        $ip = (string) $request->ip();
        $limits = config('chat.limits');

        if ($blocked = $this->hit("chat:ip:min:{$ip}", (int) $limits['ip_per_minute'], 60)
            ?? $this->hit("chat:ip:hour:{$ip}", (int) $limits['ip_per_hour'], 3600)) {
            return $blocked;
        }

        // validasi + sanitasi (HTML tidak dibuang: aman karena di-escape saat tampil)
        $raw = $request->input('message');
        $message = is_string($raw) ? ChatMessage::sanitize($raw) : '';
        $max = (int) config('chat.message_max_length');
        if ($message === '') {
            return response()->json(['error' => 'invalid', 'message' => 'Pesan tidak boleh kosong.'], 422);
        }
        if (mb_strlen($message) > $max) {
            return response()->json(['error' => 'invalid', 'message' => "Pesan maksimal {$max} karakter."], 422);
        }

        $isNew = false;
        $conv = ChatConversation::findByToken($this->token($request));
        if (! $conv) {
            if ($blocked = $this->hit("chat:new:{$ip}", (int) $limits['new_conversations_per_hour'], 3600)) {
                return $blocked;
            }
            $conv = ChatConversation::start($ip, $request->userAgent());
            $isNew = true;
        }

        if ($conv->status === ChatConversation::CLOSED) {
            return response()->json(['error' => 'closed', 'status' => 'closed', 'message' => 'Percakapan ini sudah ditutup.'], 409);
        }

        if ($blocked = $this->hit("chat:conv:{$conv->id}", (int) $limits['conversation_per_minute'], 60)) {
            return $blocked;
        }
        if ($conv->messages()->where('role', 'visitor')->count() >= (int) config('chat.max_visitor_messages')) {
            return response()->json([
                'error' => 'limit',
                'message' => 'Batas pesan di percakapan ini sudah tercapai. Mulai percakapan baru atau tunggu balasan admin.',
            ], 429);
        }

        // Satu balasan AI dalam satu waktu per percakapan (cegah balasan ganda dan pemborosan kuota).
        $lock = null;
        if ($conv->status === ChatConversation::AI) {
            $lock = Cache::lock("chat:reply:{$conv->id}", 30);
            if (! $lock->get()) {
                return response()->json(['error' => 'busy', 'message' => 'Tunggu balasan sebelumnya selesai.'], 429);
            }
        }

        try {
            $conv->addMessage('visitor', $message);
            $conv->refresh();   // admin bisa mengambil alih / menutup sesaat sebelumnya

            if ($conv->status === ChatConversation::AI) {
                $this->aiReply($conv, $ai);
            }
        } finally {
            $lock?->release();
        }

        // Token hanya dikirim sekali, saat percakapan baru dibuat (widget menyimpannya). Respons lain tidak membawanya.
        return response()->json($this->state($conv->fresh(), max(0, (int) $request->input('after', 0)), $isNew));
    }

    public function poll(Request $request): JsonResponse
    {
        abort_unless(config('chat.enabled'), 404);

        if ($blocked = $this->hit('chat:poll:'.$request->ip(), (int) config('chat.limits.poll_per_minute'), 60)) {
            return $blocked;
        }

        $conv = ChatConversation::findByToken($this->token($request));
        if (! $conv) {
            return response()->json(['error' => 'not_found'], 404);
        }

        return response()->json($this->state($conv, max(0, (int) $request->query('after', 0))));
    }

    /** Minta balasan AI. Status HUMAN/CLOSED dicek ulang saat menulis: admin boleh mengambil alih selagi AI berpikir. */
    private function aiReply(ChatConversation $conv, ChatAiService $ai): void
    {
        $history = $conv->messages()->latest('id')
            ->limit((int) config('services.ai.context_messages', 10))->get()
            ->reverse()->map(fn ($m) => ['role' => $m->role, 'body' => $m->body])->values()->all();

        $text = $ai->reply($history);   // null = fallback

        DB::transaction(function () use ($conv, $text) {
            $locked = ChatConversation::whereKey($conv->id)->lockForUpdate()->first();
            if (! $locked || $locked->status !== ChatConversation::AI) {
                return;   // sudah diambil alih / ditutup: AI tidak boleh membalas
            }

            if ($text === null) {
                $locked->addMessage('ai', (string) config('chat.fallback_message'));
                $locked->forceFill(['needs_admin' => true])->save();

                return;
            }

            $locked->addMessage('ai', $text);
        });
    }

    private function state(ChatConversation $conv, int $after, bool $withToken = false): array
    {
        $state = [
            'status' => $conv->status,
            'messages' => $conv->messages()->where('id', '>', $after)->orderBy('id')->limit(100)->get()
                ->map(fn ($m) => $m->toWidget())->all(),
        ];

        return $withToken ? ['token' => $conv->token] + $state : $state;
    }

    private function token(Request $request): ?string
    {
        $t = (string) $request->header('X-Chat-Token', '');

        return preg_match('/^[A-Za-z0-9]{40}$/', $t) ? $t : null;
    }

    /** Catat satu percobaan. Return respons 429 bila batas terlewati, null bila masih boleh. */
    private function hit(string $key, int $max, int $decaySeconds): ?JsonResponse
    {
        if (RateLimiter::tooManyAttempts($key, $max)) {
            $wait = RateLimiter::availableIn($key);

            return response()->json([
                'error' => 'rate_limited',
                'message' => 'Terlalu banyak permintaan. Coba lagi sebentar.',
                'retry_after' => $wait,
            ], 429)->header('Retry-After', (string) $wait);
        }

        RateLimiter::hit($key, $decaySeconds);

        return null;
    }
}
