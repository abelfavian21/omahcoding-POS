<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Bisnis baru: shift kasir nonaktif secara default. Bisnis yang sudah ada tidak diubah. */
return new class extends Migration {
    public function up(): void
    {
        Schema::table('businesses', function (Blueprint $t) {
            $t->boolean('shift_enabled')->default(false)->change();
        });
    }

    public function down(): void
    {
        Schema::table('businesses', function (Blueprint $t) {
            $t->boolean('shift_enabled')->default(true)->change();
        });
    }
};
