<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Cetak struk ke printer thermal (Pengaturan). Default nonaktif = perilaku lama (struk PDF).
 *  - receipt_print_enabled : fitur cetak thermal aktif / nonaktif
 *  - receipt_auto_print    : langsung cetak setelah pembayaran berhasil
 *  - receipt_paper         : lebar kertas thermal dalam mm (58 atau 80)
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::table('businesses', function (Blueprint $t) {
            $t->boolean('receipt_print_enabled')->default(false);
            $t->boolean('receipt_auto_print')->default(true);
            $t->unsignedTinyInteger('receipt_paper')->default(80);
        });
    }

    public function down(): void
    {
        Schema::table('businesses', function (Blueprint $t) {
            $t->dropColumn(['receipt_print_enabled', 'receipt_auto_print', 'receipt_paper']);
        });
    }
};
