<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * QRIS manual per bisnis: gambar QRIS statis milik bisnis (disk privat) dan opsi apakah kasir
 * tetap wajib mengisi nomor referensi. Tidak menyentuh QRIS dinamis Midtrans.
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::table('businesses', function (Blueprint $t) {
            $t->string('qris_image', 120)->nullable();
            $t->boolean('qris_require_ref')->default(false);
        });
    }

    public function down(): void
    {
        Schema::table('businesses', function (Blueprint $t) {
            $t->dropColumn(['qris_image', 'qris_require_ref']);
        });
    }
};
