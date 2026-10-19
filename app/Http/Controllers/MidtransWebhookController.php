<?php

namespace App\Http\Controllers;

use App\Models\QrisPayment;
use App\Services\MidtransClient;
use App\Services\QrisPaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Penerima notifikasi HTTP Midtrans. Rute publik tanpa CSRF (lihat bootstrap/app.php),
 * dilindungi oleh: (1) verifikasi signature_key, (2) konfirmasi ulang status langsung ke API Midtrans,
 * (3) pengecekan nominal, (4) pemrosesan idempoten.
 */
class MidtransWebhookController extends Controller
{
    public function notify(Request $request, MidtransClient $mt, QrisPaymentService $svc)
    {
        $n = $request->json()->all() ?: $request->all();

        if (! $mt->validSignature($n)) {
            Log::warning('Midtrans notify: signature tidak valid', ['ip' => $request->ip()]);

            return response()->json(['message' => 'invalid signature'], 403);
        }

        // Tanpa login -> tidak ada filter bisnis; order_id Midtrans unik se-akun sehingga aman.
        $payment = QrisPayment::where('midtrans_order_id', $n['order_id'])->first();
        if (! $payment) {
            return response()->json(['message' => 'ignored']);   // mis. tombol "Test notification" di dashboard
        }

        try {
            $svc->sync($payment);   // status diambil dari API Midtrans, bukan dari isi notifikasi
        } catch (Throwable $e) {
            report($e);

            return response()->json(['message' => 'retry later'], 503);   // Midtrans akan mengirim ulang
        }

        return response()->json(['message' => 'ok']);
    }
}
