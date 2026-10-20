<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Midtrans Server Key per bisnis. Disimpan TERENKRIPSI (cast "encrypted" di model Business). */
return new class extends Migration {
    public function up(): void
    {
        Schema::table('businesses', function (Blueprint $t) {
            $t->text('midtrans_server_key')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('businesses', function (Blueprint $t) {
            $t->dropColumn('midtrans_server_key');
        });
    }
};
