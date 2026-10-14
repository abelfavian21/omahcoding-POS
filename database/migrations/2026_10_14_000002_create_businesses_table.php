<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('businesses', function (Blueprint $t) {
            $t->id();
            $t->foreignId('business_category_id')->constrained()->restrictOnDelete();
            $t->string('code', 30)->unique();          // ID Toko, contoh CS-ORELY-8804 (dipakai login PIN kasir)
            $t->string('name');
            $t->string('owner_name');
            $t->string('email');
            $t->string('phone', 30)->nullable();
            $t->string('location')->nullable();
            $t->string('outlet_count', 40)->default('1 Outlet');
            $t->string('status', 20)->default('trial');   // trial | active | suspended
            $t->timestamp('trial_ends_at')->nullable();
            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('businesses');
    }
};
