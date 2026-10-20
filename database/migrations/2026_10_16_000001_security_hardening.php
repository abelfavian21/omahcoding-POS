<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Keamanan & integritas:
 *  - audit_logs (append-only)
 *  - orders: idempotency_key (anti dobel transaksi), payment_ref, data void terstruktur
 *  - users: jejak login terakhir
 * Jalankan SETELAH 2026_10_15_000001_add_competition_ops_features.
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::create('audit_logs', function (Blueprint $t) {
            $t->id();
            $t->foreignId('business_id')->nullable()->constrained()->cascadeOnDelete();
            $t->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $t->string('action', 40)->index();
            $t->string('target', 120)->nullable();
            $t->json('meta')->nullable();
            $t->string('ip', 45)->nullable();
            $t->string('user_agent', 255)->nullable();
            $t->timestamp('created_at')->useCurrent();
            $t->index(['business_id', 'id']);
        });

        Schema::table('orders', function (Blueprint $t) {
            $t->string('idempotency_key', 64)->nullable();
            $t->string('payment_ref', 60)->nullable();
            $t->foreignId('voided_by')->nullable()->constrained('users')->nullOnDelete();
            $t->timestamp('voided_at')->nullable();
            $t->string('void_reason', 150)->nullable();
            $t->unique(['business_id', 'idempotency_key']);
        });

        Schema::table('users', function (Blueprint $t) {
            $t->timestamp('last_login_at')->nullable();
            $t->string('last_login_ip', 45)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $t) {
            $t->dropColumn(['last_login_at', 'last_login_ip']);
        });

        Schema::table('orders', function (Blueprint $t) {
            $t->dropUnique(['business_id', 'idempotency_key']);
            $t->dropConstrainedForeignId('voided_by');
            $t->dropColumn(['idempotency_key', 'payment_ref', 'voided_at', 'void_reason']);
        });

        Schema::dropIfExists('audit_logs');
    }
};
