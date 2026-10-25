<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Balasan AI untuk chat landing. Format OpenAI-compatible (POST {base_url}/chat/completions) lewat Http::,
 * jadi Gemini / Groq / OpenRouter cukup ganti konfigurasi (config/services.php -> 'ai').
 *
 * Satu method publik. Return null = AI tidak bisa menjawab (tanpa key, kuota harian habis, atau semua provider
 * gagal/429). Pemanggil lalu memakai config('chat.fallback_message') dan menandai percakapan butuh admin.
 */
class ChatAiService
{
    /**
     * @param  array<int, array{role: string, body: string}>  $history  urut lama -> baru; role: visitor|ai|admin
     */
    public function reply(array $history): ?string
    {
        $providers = $this->providers();
        if (! $providers) {
            return null;   // tanpa key: langsung fallback, tanpa panggilan HTTP
        }

        $messages = $this->buildMessages($history);

        foreach ($providers as $provider) {
            // Kuota global per hari dihitung per panggilan HTTP, supaya kuota gratis tidak habis oleh satu penyerang.
            if (! $this->reserveDailyCall()) {
                Log::warning('chat.ai.daily_limit_reached');

                return null;
            }

            $text = $this->callProvider($provider, $messages);
            if ($text !== null) {
                return $text;
            }
        }

        return null;
    }

    /** @return array<int, array{base_url: string, api_key: string, model: string}> */
    private function providers(): array
    {
        $cfg = (array) config('services.ai', []);
        $list = [];

        foreach ([$cfg, (array) ($cfg['fallback'] ?? [])] as $p) {
            $base = rtrim(trim((string) ($p['base_url'] ?? '')), '/');
            $key = trim((string) ($p['api_key'] ?? ''));
            $model = trim((string) ($p['model'] ?? ''));

            // https wajib: API key tidak boleh terkirim lewat http polos.
            if ($base !== '' && $key !== '' && $model !== '' && str_starts_with($base, 'https://')) {
                $list[] = ['base_url' => $base, 'api_key' => $key, 'model' => $model];
            }
        }

        return $list;
    }

    private function reserveDailyCall(): bool
    {
        $limit = (int) config('services.ai.daily_limit', 200);
        if ($limit <= 0) {
            return false;
        }

        $key = 'chat:ai:calls:'.now()->format('Y-m-d');
        Cache::add($key, 0, now()->endOfDay()->addHour());

        return (int) Cache::increment($key) <= $limit;
    }

    private function buildMessages(array $history): array
    {
        $n = max(1, (int) config('services.ai.context_messages', 10));   // hanya N pesan terakhir
        $recent = array_slice(array_values($history), -$n);

        while ($recent && ($recent[0]['role'] ?? '') !== 'visitor') {
            array_shift($recent);   // konteks mulai dari pesan pengunjung
        }

        $messages = [['role' => 'system', 'content' => $this->systemPrompt()]];
        foreach ($recent as $m) {
            $messages[] = [
                'role' => ($m['role'] ?? '') === 'visitor' ? 'user' : 'assistant',
                'content' => mb_substr((string) ($m['body'] ?? ''), 0, 1000),
            ];
        }

        return $messages;
    }

    private function systemPrompt(): string
    {
        $path = (string) config('chat.system_prompt_path');
        $text = is_file($path) ? (string) file_get_contents($path) : '';
        $text = trim((string) preg_replace('/<!--.*?-->/s', '', $text));   // buang catatan TODO

        return $text !== '' ? $text
            : 'Kamu asisten customer service CashierStore. Jawab singkat. Jangan mengarang; kalau tidak yakin, arahkan ke admin.';
    }

    private function callProvider(array $provider, array $messages): ?string
    {
        $timeout = min(10, max(2, (int) config('services.ai.timeout', 8)));
        $host = parse_url($provider['base_url'], PHP_URL_HOST);

        $payload = [
            'model' => $provider['model'],
            'messages' => $messages,
            'max_tokens' => (int) config('services.ai.max_tokens', 400),
            'temperature' => 0.3,
        ];
        if ($effort = config('services.ai.reasoning_effort')) {
            $payload['reasoning_effort'] = $effort;
        }

        try {
            $res = Http::withToken($provider['api_key'])
                ->acceptJson()->asJson()
                ->connectTimeout(min(5, $timeout))->timeout($timeout)
                ->post($provider['base_url'].'/chat/completions', $payload);
        } catch (Throwable $e) {
            Log::warning('chat.ai.exception', ['host' => $host, 'error' => class_basename($e)]);

            return null;
        }

        if (! $res->successful()) {
            Log::warning('chat.ai.http_error', ['host' => $host, 'status' => $res->status()]);   // termasuk 429

            return null;
        }

        $text = trim((string) data_get($res->json(), 'choices.0.message.content', ''));
        if ($text === '') {
            Log::warning('chat.ai.empty_reply', ['host' => $host]);

            return null;
        }

        return mb_substr($text, 0, 1500);
    }
}
