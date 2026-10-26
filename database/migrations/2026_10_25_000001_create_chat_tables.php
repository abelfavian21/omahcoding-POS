<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Chat customer service di landing page. Data level PLATFORM, bukan per bisnis:
 * tabel ini sengaja TANPA business_id dan modelnya tidak memakai BelongsToBusiness.
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::create('chat_conversations', function (Blueprint $t) {
            $t->id();
            $t->string('token', 40)->unique();                  // token publik acak; disimpan di localStorage pengunjung
            $t->string('status', 10)->default('ai')->index();   // ai | human | closed
            $t->boolean('needs_admin')->default(false)->index();
            $t->foreignId('handled_by')->nullable()->constrained('platform_admins')->nullOnDelete();
            $t->string('ip', 45)->nullable();
            $t->string('user_agent', 255)->nullable();
            $t->timestamp('last_active_at')->nullable()->index();
            $t->timestamps();
        });

        Schema::create('chat_messages', function (Blueprint $t) {
            $t->id();
            $t->foreignId('chat_conversation_id')->constrained('chat_conversations')->cascadeOnDelete();
            $t->string('role', 10);                             // visitor | ai | admin
            $t->foreignId('platform_admin_id')->nullable()->constrained('platform_admins')->nullOnDelete();
            $t->text('body');
            $t->timestamp('created_at')->nullable();
            $t->index(['chat_conversation_id', 'id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chat_messages');
        Schema::dropIfExists('chat_conversations');
    }
};
