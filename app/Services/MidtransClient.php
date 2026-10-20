<?php

namespace App\Services;

use App\Models\Business;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * Klien tipis untuk Midtrans Core API (QRIS), satu instance per bisnis.
 * Server Key milik bisnis (terenkripsi di DB) dan tidak pernah dikirim ke browser.
 * Mode sandbox/produksi dibaca dari awalan key: "SB-Mid-server-…" = sandbox, "Mid-server-…" = produksi.
 */
class MidtransClient
{
    public function __construct(private ?string $serverKey = null)
    {
    }

    public static function forBusiness(?Business $business): self
    {
        return new self($business?->midtransKey());
    }

    public static function forKey(?string $key): self
    {
        return new self($key !== null ? trim($key) : null);
    }

    public static function validKeyFormat(string $key): bool
    {
        return (bool) preg_match('/^(SB-)?Mid-server-[A-Za-z0-9_\-]{8,}$/', $key);
    }

    public function enabled(): bool
    {
        return filled($this->serverKey);
    }

    public function isProduction(): bool
    {
        return $this->enabled() && ! str_starts_with($this->serverKey, 'SB-');
    }

    private function baseUrl(): string
    {
        return $this->isProduction() ? 'https://api.midtrans.com' : 'https://api.sandbox.midtrans.com';
    }

    private function http(): PendingRequest
    {
        return Http::withBasicAuth((string) $this->serverKey, '')->acceptJson()->asJson()->timeout(15)->connectTimeout(8);
    }

    /** Buat transaksi QRIS. Mengembalikan body respons Midtrans (transaction_id, qr_string, actions, expiry_time, ...). */
    public function chargeQris(string $orderId, int $amount): array
    {
        $headers = [];
        if (filled($url = config('services.midtrans.notify_url'))) {
            $headers['X-Override-Notification'] = $url;   // opsional: tanpa ini, pakai URL di dashboard Midtrans bisnis
        }

        $body = $this->send(fn () => $this->http()->withHeaders($headers)->post($this->baseUrl() . '/v2/charge', [
            'payment_type' => 'qris',
            'transaction_details' => ['order_id' => $orderId, 'gross_amount' => $amount],
            'qris' => ['acquirer' => config('services.midtrans.qris_acquirer', 'gopay')],
        ]));

        if (! in_array((string) ($body['status_code'] ?? ''), ['200', '201'], true)) {
            throw new RuntimeException('Gagal membuat QRIS: ' . ($body['status_message'] ?? 'respons gateway tidak dikenali') . '.');
        }

        return $body;
    }

    /** Status transaksi (sumber kebenaran; isi webhook tidak dipercaya mentah-mentah). */
    public function status(string $orderId): array
    {
        return $this->send(fn () => $this->http()->get($this->baseUrl() . '/v2/' . rawurlencode($orderId) . '/status'));
    }

    public function cancel(string $orderId): array
    {
        return $this->send(fn () => $this->http()->post($this->baseUrl() . '/v2/' . rawurlencode($orderId) . '/cancel'));
    }

    /**
     * Uji key tanpa membuat transaksi: tanya status order fiktif. Key benar -> 404 "tidak ada";
     * key salah / beda mode -> 401. Melempar RuntimeException bila ditolak atau gateway tak terjangkau.
     */
    public function verifyKey(): void
    {
        $body = $this->status('keycheck-' . Str::lower(Str::random(12)));

        if ((string) ($body['status_code'] ?? '') === '401') {
            throw new RuntimeException('Server Key ditolak Midtrans. Pastikan key benar dan berasal dari akun Midtrans bisnis ini.');
        }
    }

    /** signature_key = SHA512(order_id + status_code + gross_amount + server_key), dibandingkan constant-time. */
    public function validSignature(array $n): bool
    {
        foreach (['order_id', 'status_code', 'gross_amount', 'signature_key'] as $k) {
            if (! isset($n[$k]) || ! is_scalar($n[$k])) {
                return false;
            }
        }
        if (! $this->enabled()) {
            return false;
        }

        $expected = hash('sha512', $n['order_id'] . $n['status_code'] . $n['gross_amount'] . $this->serverKey);

        return hash_equals($expected, strtolower((string) $n['signature_key']));
    }

    /** URL gambar QR (PNG) dari array actions pada respons charge. */
    public static function qrImageUrl(array $charge): ?string
    {
        foreach ($charge['actions'] ?? [] as $a) {
            if (($a['name'] ?? '') === 'generate-qr-code' && filled($a['url'] ?? null)) {
                return $a['url'];
            }
        }

        return null;
    }

    /** Cegah SSRF: proxy gambar hanya boleh mengambil dari domain Midtrans lewat HTTPS. */
    public static function trustedImageUrl(?string $url): bool
    {
        $p = parse_url((string) $url);
        $host = strtolower($p['host'] ?? '');

        return ($p['scheme'] ?? '') === 'https'
            && (str_ends_with($host, '.midtrans.com') || str_ends_with($host, '.veritrans.co.id'));
    }

    private function send(callable $call): array
    {
        if (! $this->enabled()) {
            throw new RuntimeException('Midtrans Server Key belum diisi untuk bisnis ini.');
        }

        try {
            $res = $call();
        } catch (ConnectionException $e) {
            Log::warning('Midtrans tidak dapat dihubungi: ' . $e->getMessage());
            throw new RuntimeException('Tidak dapat menghubungi Midtrans. Periksa koneksi internet lalu coba lagi.');
        }

        if ($res->serverError()) {
            Log::warning('Midtrans error ' . $res->status());
            throw new RuntimeException('Midtrans sedang bermasalah (' . $res->status() . '). Coba lagi sebentar.');
        }

        return $res->json() ?? [];
    }
}
