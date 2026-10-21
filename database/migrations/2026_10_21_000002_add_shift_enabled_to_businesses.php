<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Shift kasir bisa diaktifkan / dinonaktifkan per bisnis (Pengaturan). Default aktif = perilaku lama. */
return new class extends Migration {
    public function up(): void
    {
        Schema::table('businesses', function (Blueprint $t) {
            $t->boolean('shift_enabled')->default(true);
        });
    }

    public function down(): void
    {
        Schema::table('businesses', function (Blueprint $t) {
            $t->dropColumn('shift_enabled');
        });
    }
};
