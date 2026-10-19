<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Pembayaran QRIS dinamis (Midtrans). Order baru dibuat SETELAH gateway mengonfirmasi lunas;
 * keranjang yang sudah divalidasi server disimpan di kolom payload selama menunggu.
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::create('qris_payments', function (Blueprint $t) {
            $t->id();
            $t->foreignId('business_id')->constrained()->cascadeOnDelete();
            $t->foreignId('user_id')->nullable()->constrained()->nullOnDelete();   // kasir
            $t->foreignId('order_id')->nullable()->constrained()->nullOnDelete();  // terisi setelah lunas
            $t->uuid('idempotency_key')->index();
            $t->string('midtrans_order_id', 50)->unique();                         // unik se-akun Midtrans
            $t->string('transaction_id', 64)->nullable()->index();
            $t->string('status', 12)->default('pending')->index();                 // pending|settled|expired|cancelled|failed|review
            $t->decimal('gross_amount', 14, 2);
            $t->json('payload');
            $t->text('qr_url')->nullable();
            $t->text('qr_string')->nullable();
            $t->timestamp('expires_at')->nullable();
            $t->timestamp('settled_at')->nullable();
            $t->timestamp('checked_at')->nullable();
            $t->string('failure_reason', 255)->nullable();
            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('qris_payments');
    }
};
