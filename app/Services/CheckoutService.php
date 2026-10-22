<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\CashShift;
use App\Models\Customer;
use App\Models\Ingredient;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * SATU-SATUNYA jalur pembayaran. Semua angka dihitung ulang di server:
 * harga, varian, BOM (resep/bahan baku), pajak (per bisnis), diskon, poin.
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

        $calc = $this->calculateItems($data['items'] ?? []);
        $rows = $calc['rows'];
        $subtotal = $calc['subtotal'];
        $ingredientDeductions = $calc['ingredient_deductions'];
        $productDeductions = $calc['product_deductions'];

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
        }

        // 1. Potong stok produk jadi (yang tidak punya resep)
        foreach ($productDeductions as $pId => $qty) {
            $prod = Product::lockForUpdate()->findOrFail($pId);
            $prod->adjustStock(-$qty, 'sale', $order->invoice_no, $user->id, $order->id);
        }

        // 2. Potong stok bahan baku (BOM)
        foreach ($ingredientDeductions as $ingId => $amount) {
            $ingredient = Ingredient::lockForUpdate()->findOrFail($ingId);
            $ingredient->adjustStock(-$amount, 'sale', "Pesanan {$order->invoice_no}", $user->id, $order->id);
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
     * Hitung total tagihan TANPA menyimpan apa pun (untuk validasi & penawaran QRIS dinamis).
     */
    public function quote(User $user, array $data): array
    {
        $calc = $this->calculateItems($data['items'] ?? [], false);
        $subtotal = $calc['subtotal'];

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
            $calc = $this->calculateItems($data['items'] ?? [], false);
            $subtotal = $calc['subtotal'];
            $rows = $calc['rows'];

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

            foreach ($rows as $r) {
                $order->items()->create($r['data']);
            }

            return $order->load('items');
        });
    }

    /**
     * Helper kalkulasi item keranjang, varian, dan kebutuhan bahan baku (BOM).
     */
    private function calculateItems(array $rawItems, bool $lock = true): array
    {
        if (empty($rawItems)) {
            throw new RuntimeException('Keranjang belanja kosong.');
        }

        $pIds = collect($rawItems)->pluck('product_id')->filter()->unique()->values()->all();
        $productQuery = Product::with(['recipes', 'variants'])->whereIn('id', $pIds);
        if ($lock) {
            $productQuery->lockForUpdate();
        }
        $products = $productQuery->get()->keyBy('id');

        // Kumpulkan semua varian id yang dipilih
        $vIds = [];
        foreach ($rawItems as $item) {
            if (! empty($item['variant_ids']) && is_array($item['variant_ids'])) {
                foreach ($item['variant_ids'] as $vid) {
                    $vIds[] = (int) $vid;
                }
            }
        }
        $variants = ProductVariant::whereIn('id', array_unique($vIds))->get()->keyBy('id');

        $rows = [];
        $subtotal = 0;
        $ingredientDeductions = []; // [ingredient_id => total_amount]
        $productDeductions = [];    // [product_id => total_qty] (khusus produk tanpa resep)

        foreach ($rawItems as $item) {
            $pid = $item['product_id'] ?? null;
            $qty = (int) ($item['qty'] ?? 1);
            if ($qty <= 0) continue;

            $p = $products[$pid] ?? null;
            if (! $p) {
                throw new RuntimeException('Produk tidak ditemukan.');
            }
            if (! $p->is_active) {
                throw new RuntimeException("{$p->name} sedang tidak dijual.");
            }

            $selectedVariants = [];
            $extraPrice = 0;
            $extraCost = 0;

            if (! empty($item['variant_ids']) && is_array($item['variant_ids'])) {
                foreach ($item['variant_ids'] as $vid) {
                    $v = $variants[$vid] ?? null;
                    if ($v && $v->product_id == $p->id && $v->is_active) {
                        $selectedVariants[] = [
                            'id' => $v->id,
                            'group' => $v->group_name,
                            'name' => $v->name,
                            'price' => (float) $v->price_extra,
                        ];
                        $extraPrice += (float) $v->price_extra;
                        $extraCost += (float) $v->cost_extra;
                    }
                }
            }

            $unitPrice = (float) $p->price + $extraPrice;
            $unitCost = (float) $p->cost_price + $extraCost;
            $lineTotal = $unitPrice * $qty;
            $subtotal += $lineTotal;

            $rows[] = [
                'product' => $p,
                'qty' => $qty,
                'data' => [
                    'product_id' => $p->id,
                    'product_name' => $p->name,
                    'price' => $unitPrice,
                    'cost_price' => $unitCost,
                    'qty' => $qty,
                    'subtotal' => $lineTotal,
                    'variants_json' => ! empty($selectedVariants) ? $selectedVariants : null,
                ],
            ];

            // Cek apakah produk ini memiliki resep (BOM)
            $hasRecipe = $p->recipes->isNotEmpty();
            if ($hasRecipe) {
                // Potong bahan baku sesuai resep dasar & resep per varian
                $selectedVids = array_column($selectedVariants, 'id');
                foreach ($p->recipes as $recipe) {
                    // Resep berlaku jika umum (product_variant_id null) ATAU jika varian tersebut dipilih
                    if ($recipe->product_variant_id === null || in_array($recipe->product_variant_id, $selectedVids)) {
                        $needed = (float) $recipe->amount * $qty;
                        $ingredientDeductions[$recipe->ingredient_id] = ($ingredientDeductions[$recipe->ingredient_id] ?? 0) + $needed;
                    }
                }
            } else {
                // Produk jadi tanpa resep -> potong stok produk fisik
                $productDeductions[$p->id] = ($productDeductions[$p->id] ?? 0) + $qty;
            }
        }

        // Cek kecukupan stok produk jadi jika quote / verifikasi
        foreach ($productDeductions as $pId => $needQty) {
            $prod = $products[$pId] ?? null;
            if ($prod && $prod->stock < $needQty) {
                throw new RuntimeException("Stok {$prod->name} tidak cukup (tersisa {$prod->stock}).");
            }
        }

        return [
            'rows' => $rows,
            'subtotal' => $subtotal,
            'ingredient_deductions' => $ingredientDeductions,
            'product_deductions' => $productDeductions,
        ];
    }
}
