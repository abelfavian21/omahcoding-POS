<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $t) {
            $t->string('role', 20)->default('kasir')->after('email');   // admin | kasir
            $t->string('phone', 30)->nullable()->after('role');
            $t->string('pin')->nullable()->after('password');            // PIN 4 digit (hashed)
            $t->string('shift', 40)->nullable()->after('pin');           // contoh: "07:00 - 15:00"
            $t->boolean('is_active')->default(true)->after('shift');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $t) {
            $t->dropColumn(['role', 'phone', 'pin', 'shift', 'is_active']);
        });
    }
};
