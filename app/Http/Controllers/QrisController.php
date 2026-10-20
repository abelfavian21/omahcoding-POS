<?php

namespace App\Http\Controllers;

use App\Models\CashShift;
use App\Models\Order;
use App\Models\QrisPayment;
use App\Services\MidtransClient;
use App\Services\QrisPaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\Rule;
use RuntimeException;

/** Sisi kasir untuk QRIS dinamis: buat QR, pantau status, batalkan, tampilkan gambar QR. */
class QrisController extends Controller
{
    public function store(Request $request, QrisPaymentService $svc)
    {
        $mt = MidtransClient::forBusiness($request->user()->business);
        if (! $mt->enabled()) {
            return response()->json(['message' => 'QRIS dinamis belum aktif untuk bisnis ini. Admin dapat mengisi Midtrans Server Key di menu Pengaturan.'], 503);
        }

        $bid = $request->user()->business_id;
        $data = $request->validate([
            'idempotency_key' => ['required', 'uuid'],
            'items' => ['required', 'array', 'min:1', 'max:100'],
            'items.*.product_id' => ['required', 'integer', Rule::exists('products', 'id')->where('business_id', $bid)],
            'items.*.qty' => ['required', 'integer', 'min:1', 'max:999'],
            'customer_id' => ['nullable', Rule::exists('customers', 'id')->where('business_id', $bid)],
            'order_type' => ['required', 'in:dine_in,takeaway'],
            'table_no' => ['nullable', 'string', 'max:10'],
            'discount' => ['nullable', 'numeric', 'min:0', 'max:999999999'],
            'note' => ['nullable', 'string', 'max:255'],
        ]);

        if (config('pos.require_shift') && ! CashShift::currentFor($request->user())) {
            return response()->json(['message' => 'Buka shift kasir terlebih dahulu sebelum menerima pembayaran.'], 422);
        }

        try {
            $payment = $svc->start($request->user(), $data);
        } catch (RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json($this->present($payment, $mt), 201);
    }

    public function show(Request $request, QrisPayment $payment, QrisPaymentService $svc)
    {
        $this->authorizeAccess($request, $payment);
        $mt = MidtransClient::forBusiness($request->user()->business);

        // Cadangan bila webhook belum/ tidak sampai (mis. server lokal tanpa URL publik): cek ke Midtrans tiap ≥ 6 detik.
        if ($payment->status === QrisPayment::PENDING && (! $payment->checked_at || $payment->checked_at->diffInSeconds(now()) >= 6)) {
            $payment = $svc->syncQuietly($payment);
        }

        return response()->json($this->present($payment, $mt));
    }

    public function cancel(Request $request, QrisPayment $payment, QrisPaymentService $svc)
    {
        $this->authorizeAccess($request, $payment);
        $mt = MidtransClient::forBusiness($request->user()->business);

        return response()->json($this->present($svc->cancel($payment), $mt));
    }

    /** Gambar QR diteruskan lewat server sendiri: CSP hanya mengizinkan img-src 'self', dan URL gateway tidak perlu dibuka ke browser. */
    public function qr(Request $request, QrisPayment $payment)
    {
        $this->authorizeAccess($request, $payment);
        abort_unless(MidtransClient::trustedImageUrl($payment->qr_url), 404);

        $res = Http::timeout(10)->get($payment->qr_url);
        abort_unless($res->successful() && str_starts_with((string) $res->header('Content-Type'), 'image/'), 502);

        return response($res->body(), 200, [
            'Content-Type' => $res->header('Content-Type'),
            'Cache-Control' => 'private, no-store',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    /** Kasir hanya boleh melihat pembayarannya sendiri; admin boleh semua (data sudah terisolasi per bisnis). */
    private function authorizeAccess(Request $request, QrisPayment $payment): void
    {
        abort_unless($payment->user_id === $request->user()->id || $request->user()->isAdmin(), 403);
    }

    private function present(QrisPayment $p, MidtransClient $mt): array
    {
        $order = $p->status === QrisPayment::SETTLED && $p->order_id ? Order::find($p->order_id) : null;
        $sandbox = ! $mt->isProduction();

        return [
            'id' => $p->id,
            'status' => $p->status,
            'total' => (float) $p->gross_amount,
            'seconds_left' => $p->expires_at ? max(0, (int) now()->diffInSeconds($p->expires_at, false)) : 0,
            'qr' => $p->qr_url ? route('pos.qris.qr', $p) : null,
            'sandbox' => $sandbox,
            'gateway_qr_url' => $sandbox ? $p->qr_url : null,   // dipakai simulator Midtrans; tidak ditampilkan di produksi
            'message' => $p->failure_reason,
            'urls' => ['show' => route('pos.qris.show', $p), 'cancel' => route('pos.qris.cancel', $p)],
            'order' => $order ? [
                'invoice_no' => $order->invoice_no,
                'total' => (float) $order->total,
                'receipt_url' => route('orders.receipt', $order),
                'download_url' => route('orders.receipt', [$order, 'download' => 1]),
            ] : null,
        ];
    }
}
