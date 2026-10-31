<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Bahasa tampilan per bisnis (Pengaturan): 'id' (default) atau 'en'. */
return new class extends Migration {
    public function up(): void
    {
        Schema::table('businesses', function (Blueprint $t) {
            $t->string('language', 5)->default('id');
        });
    }

    public function down(): void
    {
        Schema::table('businesses', function (Blueprint $t) {
            $t->dropColumn('language');
        });
    }
};
