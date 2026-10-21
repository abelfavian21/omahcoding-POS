<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\CashShift;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * SATU-SATUNYA jalur pembayaran. Semua angka dihitung ulang di server:
 * harga, pajak (per bisnis), diskon (dibatasi untuk kasir), poin.
 */
class CheckoutService
{
    public function checkout(User $user, array $data): Order
    {
        $key = $data['idempotency_key'] ?? null;

        // Permintaan yang sama dikirim ulang (jaringan putus / klik ganda) -> kembalikan order lama.
        if ($key && ($dup = Order::where('idempotency_key', $key)->first())) {
            return $dup->load(['items', 'customer']);
        }

        for ($attempt = 1; ; $attempt++) {
            try {
                return DB::transaction(fn () => $this->process($user, $data));
            } catch (UniqueConstraintViolationException $e) {
                // tabrakan idempotency_key (dua request bersamaan) atau nomor invoice
                if ($key && ($dup = Order::where('idempotency_key', $key)->first())) {
                    return $dup->load(['items', 'customer']);
                }
                if ($attempt >= 3) {
                    throw new RuntimeException('Gagal membuat nomor invoice, silakan coba lagi.');
                }
            }
        }
    }

    private function process(User $user, array $data): Order
    {
        $shift = CashShift::currentFor($user);
        if (! $shift && $user->business?->requiresShift()) {
            throw new RuntimeException('Buka shift kasir terlebih dahulu sebelum menerima pembayaran.');
        }

        $held = isset($data['held_id']) ? Order::held()->lockForUpdate()->find($data['held_id']) : null;
        if (isset($data['held_id']) && ! $held) {
            throw new RuntimeException('Pesanan tertahan tidak ditemukan.');
        }

        $lines = collect($data['items'])->groupBy('product_id')->map(fn ($g) => (int) $g->sum('qty'));
        $products = Product::whereIn('id', $lines->keys())->lockForUpdate()->get()->keyBy('id');

        $subtotal = 0;
        $rows = [];
        foreach ($lines as $pid => $qty) {
            $p = $products[$pid] ?? null;
            if (! $p) {
                throw new RuntimeException('Produk tidak ditemukan.');
            }
            if (! $p->is_active) {
                throw new RuntimeException("{$p->name} sedang tidak dijual.");
            }
            $rows[] = [
                'product' => $p,
                'qty' => $qty,
                'data' => [
                    'product_id' => $p->id,
                    'product_name' => $p->name,
                    'price' => $p->price,
                    'cost_price' => $p->cost_price,
                    'qty' => $qty,
                    'subtotal' => $p->price * $qty,
                ],
            ];
            $subtotal += $p->price * $qty;
        }

        /* ---- diskon manual: dibatasi untuk kasir, admin bebas; selalu dicatat ---- */
        $manualDiscount = round((float) ($data['discount'] ?? 0));
        if ($manualDiscount > $subtotal) {
            throw new RuntimeException('Diskon tidak boleh melebihi subtotal.');
        }
        $capPct = (float) config('pos.cashier_max_discount_pct', 10);
        if (! $user->isAdmin() && $manualDiscount > $subtotal * $capPct / 100) {
            throw new RuntimeException("Diskon kasir maksimal {$capPct}% dari subtotal. Minta admin untuk diskon lebih besar.");
        }

        $customer = ! empty($data['customer_id']) ? Customer::lockForUpdate()->find($data['customer_id']) : null;

        $wantPoints = (int) ($data['points_redeemed'] ?? 0);
        if ($wantPoints > 0 && ! $customer) {
            throw new RuntimeException('Pilih pelanggan untuk menukar poin.');
        }

        $pointValue = Customer::pointValue();
        $maxByMoney = (int) floor(($subtotal - $manualDiscount) / $pointValue);
        $redeem = min(max($wantPoints, 0), $customer?->points ?? 0, $maxByMoney);
        $pointsDiscount = $redeem * $pointValue;
        $discount = $manualDiscount + $pointsDiscount;

        $taxRate = $user->business?->taxRate() ?? (float) config('pos.tax_rate');
        $tax = round(($subtotal - $discount) * $taxRate);
        $total = $subtotal - $discount + $tax;

        $method = $data['payment_method'];
        $paid = $method === 'cash' ? (float) ($data['paid_amount'] ?? 0) : $total;
        if ($paid < $total) {
            throw new RuntimeException('Uang diterima kurang dari total tagihan.');
        }

        $attrs = [
            'user_id' => $user->id,
            'cash_shift_id' => $shift?->id,
            'customer_id' => $customer?->id,
            'order_type' => $data['order_type'],
            'table_no' => $data['order_type'] === 'dine_in' ? ($data['table_no'] ?? null) : null,
            'subtotal' => $subtotal,
            'discount' => $discount,
            'points_redeemed' => $redeem,
            'tax' => $tax,
            'total' => $total,
            'payment_method' => $method,
            'payment_ref' => $data['payment_ref'] ?? null,
            'paid_amount' => $paid,
            'change_amount' => $paid - $total,
            'status' => 'paid',
            'kitchen_status' => 'queued',
            'note' => $data['note'] ?? $held?->note,
            'paid_at' => now(),
            'idempotency_key' => $data['idempotency_key'] ?? null,
        ];

        if ($held) {
            $held->items()->delete();
            $held->update($attrs);
            $order = $held;
        } else {
            $order = Order::create($attrs + ['invoice_no' => Order::nextInvoiceNo()]);
        }

        foreach ($rows as $r) {
            $order->items()->create($r['data']);
            // melempar RuntimeException bila stok kurang -> seluruh transaksi di-rollback
            $r['product']->adjustStock(-$r['qty'], 'sale', $order->invoice_no, $user->id, $order->id);
        }

        if ($customer && $redeem > 0) {
            $customer->points = max(0, $customer->points - $redeem);
            $customer->save();
        }

        $earned = $customer ? $customer->applyPurchase($total, $order->paid_at) : 0;
        $order->update(['points_earned' => $earned]);

        if ($manualDiscount > 0) {
            AuditLog::record('order.discount', $order->invoice_no, [
                'discount' => $manualDiscount, 'subtotal' => $subtotal, 'by_role' => $user->role,
            ]);
        }

        return $order->fresh(['items', 'customer']);
    }

    /**
     * Hitung total tagihan TANPA menyimpan apa pun (dipakai QRIS dinamis untuk menentukan nominal QR).
     * Aturan sama dengan process(): harga dari DB, batas diskon kasir, pajak per bisnis.
     * Jika aturan di process() diubah, ubah juga di sini.
     */
    public function quote(User $user, array $data): array
    {
        $lines = collect($data['items'])->groupBy('product_id')->map(fn ($g) => (int) $g->sum('qty'));
        $products = Product::whereIn('id', $lines->keys())->get()->keyBy('id');

        $subtotal = 0;
        foreach ($lines as $pid => $qty) {
            $p = $products[$pid] ?? null;
            if (! $p) {
                throw new RuntimeException('Produk tidak ditemukan.');
            }
            if (! $p->is_active) {
                throw new RuntimeException("{$p->name} sedang tidak dijual.");
            }
            if ($p->stock < $qty) {
                throw new RuntimeException("Stok {$p->name} tidak cukup.");
            }
            $subtotal += $p->price * $qty;
        }

        $discount = round((float) ($data['discount'] ?? 0));
        if ($discount > $subtotal) {
            throw new RuntimeException('Diskon tidak boleh melebihi subtotal.');
        }
        $capPct = (float) config('pos.cashier_max_discount_pct', 10);
        if (! $user->isAdmin() && $discount > $subtotal * $capPct / 100) {
            throw new RuntimeException("Diskon kasir maksimal {$capPct}% dari subtotal. Minta admin untuk diskon lebih besar.");
        }

        $taxRate = $user->business?->taxRate() ?? (float) config('pos.tax_rate');
        $tax = round(($subtotal - $discount) * $taxRate);

        return ['subtotal' => $subtotal, 'discount' => $discount, 'tax' => $tax, 'total' => $subtotal - $discount + $tax];
    }

    public function hold(User $user, array $data): Order
    {
        return DB::transaction(function () use ($user, $data) {
            $lines = collect($data['items'])->groupBy('product_id')->map(fn ($g) => (int) $g->sum('qty'));
            $products = Product::whereIn('id', $lines->keys())->get()->keyBy('id');

            $subtotal = 0;
            $rows = [];
            foreach ($lines as $pid => $qty) {
                $p = $products[$pid] ?? null;
                if (! $p || ! $p->is_active) {
                    throw new RuntimeException('Produk tidak tersedia untuk ditahan.');
                }
                $rows[] = [
                    'product_id' => $p->id,
                    'product_name' => $p->name,
                    'price' => $p->price,
                    'cost_price' => $p->cost_price,
                    'qty' => $qty,
                    'subtotal' => $p->price * $qty,
                ];
                $subtotal += $p->price * $qty;
            }

            $order = Order::create([
                'invoice_no' => Order::nextInvoiceNo(),
                'user_id' => $user->id,
                'customer_id' => $data['customer_id'] ?? null,
                'order_type' => $data['order_type'] ?? 'dine_in',
                'table_no' => $data['table_no'] ?? null,
                'subtotal' => $subtotal,
                'discount' => 0,
                'tax' => 0,
                'total' => $subtotal,
                'payment_method' => 'cash',
                'paid_amount' => 0,
                'change_amount' => 0,
                'status' => 'held',
                'note' => $data['note'] ?? null,
            ]);
            $order->items()->createMany($rows);

            return $order->load('items');
        });
    }
}
