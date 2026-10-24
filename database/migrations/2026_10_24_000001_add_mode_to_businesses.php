<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Bisnis kini punya 'mode' yang menentukan perilaku POS:
 *   - fnb   (default): dine-in / takeaway, nomor meja, kitchen display
 *   - retail: tanpa dine-in/meja, order selalu "takeaway", cocok untuk minimarket/kelontong
 *
 * Kolom 'mode' juga ditambahkan ke business_categories sebagai default
 * ketika bisnis baru didaftarkan lewat kategori tersebut.
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::table('businesses', function (Blueprint $t) {
            $t->string('mode', 20)->default('fnb')->after('status');
        });

        Schema::table('business_categories', function (Blueprint $t) {
            $t->string('default_mode', 20)->default('fnb')->after('preset');
        });
    }

    public function down(): void
    {
        Schema::table('businesses', function (Blueprint $t) {
            $t->dropColumn('mode');
        });
        Schema::table('business_categories', function (Blueprint $t) {
            $t->dropColumn('default_mode');
        });
    }
};
