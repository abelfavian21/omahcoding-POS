<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('business_categories', function (Blueprint $t) {
            $t->id();
            $t->string('slug', 40)->unique();          // cafe, resto, bakery, quick-service, retail, salon
            $t->string('name');                        // "Cafe & Coffee Shop"
            $t->string('icon', 8)->nullable();         // emoji
            $t->string('color', 20)->default('blue');  // warna kartu di landing (nama warna Tailwind)
            $t->string('tagline')->nullable();         // deskripsi singkat di kartu
            $t->string('features')->nullable();        // fitur unggulan (ringkas)
            $t->string('badge', 60)->nullable();       // label kecil, mis. "Table Floor Plan"
            $t->json('preset')->nullable();            // kategori produk bawaan: [["Kopi","☕"], ...]
            $t->boolean('is_active')->default(false);  // false = tampil "Segera hadir"
            $t->unsignedSmallInteger('sort_order')->default(0);
            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('business_categories');
    }
};
