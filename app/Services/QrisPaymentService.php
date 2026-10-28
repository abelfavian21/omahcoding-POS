<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\Order;
use App\Models\QrisPayment;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

/**
 * Siklus pembayaran QRIS dinamis.
 *
 *  start()  -> hitung total di server, minta QR ke Midtrans, simpan keranjang (belum ada Order)
 *  apply()  -> dipanggil oleh webhook ATAU polling; saat LUNAS memanggil CheckoutService yang sama
 *              dengan kasir tunai, sehingga stok / poin / diskon / audit tidak punya jalur ganda.
 *
 * Aturan emas: uang sudah masuk tidak boleh "hilang". Bila order gagal dibuat saat lunas
 * (stok habis, kasir dinonaktifkan, dll), status menjadi REVIEW + audit log untuk ditangani admin.
 */
class QrisPaymentService
{
    public function __construct(private CheckoutService $checkout)
    {
    }

    /** Klien Midtrans milik bisnis pemilik pembayaran (Server Key per bisnis). */
    private function client(QrisPayment $payment): MidtransClient
    {
        return MidtransClient::forBusiness($payment->business);
    }

    public function start(User $user, array $data): QrisPayment
    {
        $key = $data['idempotency_key'];

        $mt = MidtransClient::forBusiness($user->business);
        if (! $mt->enabled()) {
            throw new RuntimeException('QRIS dinamis belum aktif untuk bisnis ini. Admin dapat mengisi Midtrans Server Key di menu Pengaturan.');
        }

        if (QrisPayment::where('idempotency_key', $key)->where('status', QrisPayment::REVIEW)->exists()) {
            throw new RuntimeException('Pembayaran QRIS untuk pesanan ini perlu dicek admin. Jangan scan ulang; lihat Audit Log.');
        }

        // QR yang masih aktif untuk keranjang yang sama dipakai ulang (klik ganda / halaman dimuat ulang).
        $active = QrisPayment::where('idempotency_key', $key)->where('status', QrisPayment::PENDING)
            ->where('expires_at', '>', now())->latest('id')->first();
        if ($active) {
            return $active;
        }

        // QR lama yang sudah lewat waktu: batalkan di gateway supaya tidak bisa dibayar dobel.
        foreach (QrisPayment::where('idempotency_key', $key)->where('status', QrisPayment::PENDING)->get() as $old) {
            $this->cancel($old);
        }
        if ($settled = QrisPayment::where('idempotency_key', $key)->where('status', QrisPayment::SETTLED)->first()) {
            return $settled;   // ternyata sudah dibayar
        }
        if (Order::where('idempotency_key', $key)->exists()) {
            throw new RuntimeException('Pesanan ini sudah dibayar. Muat ulang halaman kasir.');
        }

        $quote = $this->checkout->quote($user, $data);
        $amount = (int) round($quote['total']);
        if ($amount < 1) {
            throw new RuntimeException('Total pembayaran tidak valid untuk QRIS.');
        }

        $payment = QrisPayment::create([
            'user_id' => $user->id,
            'idempotency_key' => $key,
            'midtrans_order_id' => 'Q' . $user->business_id . '-' . now()->format('ymdHis') . '-' . Str::upper(Str::random(6)),
            'status' => QrisPayment::PENDING,
            'gross_amount' => $amount,
            'payload' => collect($data)->only(['idempotency_key', 'items', 'customer_id', 'order_type', 'table_no', 'discount', 'note'])->all(),
            'expires_at' => now()->addMinutes(15),
        ]);

        try {
            $res = $mt->chargeQris($payment->midtrans_order_id, $amount);
        } catch (RuntimeException $e) {
            $payment->update(['status' => QrisPayment::FAILED, 'failure_reason' => mb_substr($e->getMessage(), 0, 255)]);
            throw $e;
        }

        $payment->update([
            'transaction_id' => $res['transaction_id'] ?? null,
            'qr_url' => MidtransClient::qrImageUrl($res),
            'qr_string' => $res['qr_string'] ?? null,
            'expires_at' => isset($res['expiry_time']) ? \Illuminate\Support\Carbon::parse($res['expiry_time'], 'Asia/Jakarta')->setTimezone(config('app.timezone')) : $payment->expires_at,
        ]);
        AuditLog::record('qris.created', $payment->midtrans_order_id, ['amount' => $amount]);

        return $payment->refresh();
    }

    /** Tanya status ke Midtrans lalu terapkan. Melempar RuntimeException bila gateway tidak terjangkau. */
    public function sync(QrisPayment $payment): QrisPayment
    {
        $mt = $this->client($payment)->status($payment->midtrans_order_id);
        $payment->forceFill(['checked_at' => now()])->saveQuietly();

        return $this->apply($payment, $mt);
    }

    /** Seperti sync(), tetapi gangguan gateway tidak mematahkan alur (status tetap seperti sebelumnya). */
    public function syncQuietly(QrisPayment $payment): QrisPayment
    {
        try {
            return $this->sync($payment);
        } catch (Throwable $e) {
            report($e);

            return $payment->refresh();
        }
    }

    public function cancel(QrisPayment $payment): QrisPayment
    {
        $payment = $this->syncQuietly($payment);
        if ($payment->status !== QrisPayment::PENDING) {
            return $payment;   // sudah lunas / kedaluwarsa duluan
        }

        try {
            $this->client($payment)->cancel($payment->midtrans_order_id);
        } catch (RuntimeException $e) {
            report($e);
        }
        $payment = $this->syncQuietly($payment);

        return DB::transaction(function () use ($payment) {
            $p = QrisPayment::withoutGlobalScopes()->whereKey($payment->getKey())->lockForUpdate()->firstOrFail();
            if ($p->status === QrisPayment::PENDING) {
                $p->update(['status' => QrisPayment::CANCELLED]);
                AuditLog::record('qris.cancelled', $p->midtrans_order_id, ['amount' => (float) $p->gross_amount], $p->business_id, $p->user_id);
            }

            return $p;
        });
    }

    /**
     * Terapkan status dari Midtrans. IDEMPOTEN: webhook bisa datang berkali-kali dan bersamaan
     * dengan polling; baris dikunci dan status akhir tidak diproses ulang.
     */
    public function apply(QrisPayment $payment, array $mt): QrisPayment
    {
        return DB::transaction(function () use ($payment, $mt) {
            $p = QrisPayment::withoutGlobalScopes()->whereKey($payment->getKey())->lockForUpdate()->firstOrFail();

            if (in_array($p->status, [QrisPayment::SETTLED, QrisPayment::REVIEW], true)) {
                return $p;
            }

            $ts = (string) ($mt['transaction_status'] ?? '');
            $paid = in_array($ts, ['settlement', 'capture'], true) && ($mt['fraud_status'] ?? 'accept') === 'accept';

            if ($paid) {
                if (isset($mt['gross_amount']) && (int) round((float) $mt['gross_amount']) !== (int) round((float) $p->gross_amount)) {
                    return $this->flag($p, 'Nominal dari Midtrans (' . $mt['gross_amount'] . ') berbeda dengan tagihan.');
                }
                if ($p->status !== QrisPayment::PENDING) {
                    return $this->flag($p, 'Dibayar setelah QR dibatalkan/kedaluwarsa. Lakukan refund lewat dashboard Midtrans.');
                }

                return $this->finalize($p, $mt);
            }

            if ($p->status !== QrisPayment::PENDING) {
                return $p;
            }

            if ($ts === 'expire') {
                $p->update(['status' => QrisPayment::EXPIRED]);
            } elseif ($ts === 'cancel') {
                $p->update(['status' => QrisPayment::CANCELLED]);
            } elseif (in_array($ts, ['deny', 'failure'], true)) {
                $p->update(['status' => QrisPayment::FAILED, 'failure_reason' => 'Ditolak gateway (' . $ts . ').']);
            }

            return $p;
        });
    }

    private function finalize(QrisPayment $p, array $mt): QrisPayment
    {
        $user = User::withoutGlobalScopes()->find($p->user_id);
        if (! $user || ! $user->is_active) {
            return $this->flag($p, 'Akun kasir pembuat transaksi sudah tidak aktif.');
        }

        $data = ($p->payload ?? []) + [
            'payment_method' => 'qris',
            'payment_ref' => $mt['transaction_id'] ?? $p->transaction_id,
        ];

        // Webhook tidak punya sesi login: jalankan sebagai kasir pembuat transaksi supaya
        // isolasi per bisnis (BelongsToBusiness) dan shift kas berlaku persis seperti checkout biasa.
        // Konteks bisnis dipasang eksplisit lewat TenantContext::run (otomatis pulih, juga saat exception).
        try {
            $order = app(\App\Support\TenantContext::class)->run((int) $p->business_id, function () use ($user, $data) {
                $previous = Auth::hasUser() ? Auth::user() : null;
                Auth::setUser($user);

                try {
                    return $this->checkout->checkout($user, $data);
                } finally {
                    $previous ? Auth::setUser($previous) : Auth::forgetUser();
                }
            });
        } catch (RuntimeException $e) {
            return $this->flag($p, mb_substr($e->getMessage(), 0, 200));
        }

        $p->update(['status' => QrisPayment::SETTLED, 'order_id' => $order->id, 'settled_at' => now(), 'transaction_id' => $mt['transaction_id'] ?? $p->transaction_id]);
        AuditLog::record('qris.settled', $order->invoice_no, [
            'transaction_id' => $p->transaction_id, 'amount' => (float) $p->gross_amount,
        ], $p->business_id, $p->user_id);

        if ((int) round((float) $order->total) !== (int) round((float) $p->gross_amount)) {
            AuditLog::record('qris.amount_mismatch', $order->invoice_no, [
                'order_total' => (float) $order->total, 'charged' => (float) $p->gross_amount,
            ], $p->business_id, $p->user_id);   // harga berubah selama menunggu pembayaran
        }

        return $p;
    }

    private function flag(QrisPayment $p, string $reason): QrisPayment
    {
        $p->update(['status' => QrisPayment::REVIEW, 'failure_reason' => mb_substr($reason, 0, 255)]);
        AuditLog::record('qris.review', $p->midtrans_order_id, [
            'reason' => $reason, 'amount' => (float) $p->gross_amount, 'transaction_id' => $p->transaction_id,
        ], $p->business_id, $p->user_id);

        return $p;
    }
}
