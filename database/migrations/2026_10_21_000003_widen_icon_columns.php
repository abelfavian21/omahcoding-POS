<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Kolom ikon sebelumnya 8 karakter (cukup untuk emoji). Nama ikon vektor lebih panjang, jadi diperlebar. */
return new class extends Migration {
    public function up(): void
    {
        Schema::table('products', fn (Blueprint $t) => $t->string('emoji', 40)->nullable()->change());
        Schema::table('categories', fn (Blueprint $t) => $t->string('emoji', 40)->nullable()->change());
        Schema::table('business_categories', fn (Blueprint $t) => $t->string('icon', 40)->nullable()->change());
    }

    public function down(): void
    {
        // tidak dikembalikan ke 8 karakter: nama ikon yang sudah tersimpan akan terpotong
    }
};
