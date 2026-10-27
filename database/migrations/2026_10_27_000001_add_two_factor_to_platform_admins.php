<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('platform_admins', function (Blueprint $t) {
            $t->text('totp_secret')->nullable();                    // terenkripsi (cast "encrypted")
            $t->timestamp('totp_confirmed_at')->nullable();
            $t->unsignedBigInteger('totp_last_step')->nullable();   // anti-replay: step TOTP terakhir yang dipakai
            $t->text('recovery_codes')->nullable();                 // terenkripsi; hanya hash HMAC kode
        });
    }

    public function down(): void
    {
        Schema::table('platform_admins', function (Blueprint $t) {
            $t->dropColumn(['totp_secret', 'totp_confirmed_at', 'totp_last_step', 'recovery_codes']);
        });
    }
};
