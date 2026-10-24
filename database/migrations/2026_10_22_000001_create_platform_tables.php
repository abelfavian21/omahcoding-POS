<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('platform_admins', function (Blueprint $t) {
            $t->id();
            $t->string('name');
            $t->string('email')->unique();
            $t->string('password');
            $t->boolean('is_active')->default(true);
            $t->boolean('must_change_password')->default(false);
            $t->timestamp('last_login_at')->nullable();
            $t->string('last_login_ip', 45)->nullable();
            $t->rememberToken();
            $t->timestamps();
        });

        Schema::create('platform_audit_logs', function (Blueprint $t) {
            $t->id();
            $t->foreignId('platform_admin_id')->nullable()->constrained('platform_admins')->nullOnDelete();
            $t->unsignedBigInteger('business_id')->nullable()->index();   // tanpa FK: catatan tetap ada walau toko dihapus
            $t->string('action', 60)->index();
            $t->string('target')->nullable();
            $t->json('meta')->nullable();
            $t->string('ip', 45)->nullable();
            $t->string('user_agent', 255)->nullable();
            $t->timestamp('created_at')->nullable()->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('platform_audit_logs');
        Schema::dropIfExists('platform_admins');
    }
};
