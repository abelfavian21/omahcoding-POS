<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('businesses', function (Blueprint $t) {
            $t->decimal('tax_rate', 5, 4)->default(0.1000)->after('trial_ends_at');
            $t->string('tax_label', 40)->default('Pajak (PB1)')->after('tax_rate');
            $t->string('receipt_footer', 255)->nullable()->after('tax_label');
        });

        Schema::create('cash_shifts', function (Blueprint $t) {
            $t->id();
            $t->foreignId('business_id')->constrained()->cascadeOnDelete();
            $t->foreignId('user_id')->constrained()->cascadeOnDelete();
            $t->timestamp('opened_at');
            $t->timestamp('closed_at')->nullable();
            $t->decimal('opening_cash', 14, 2)->default(0);
            $t->decimal('closing_cash', 14, 2)->nullable();
            $t->decimal('expected_cash', 14, 2)->nullable();
            $t->decimal('difference', 14, 2)->nullable();
            $t->string('note')->nullable();
            $t->timestamps();
            $t->index(['business_id', 'user_id', 'closed_at']);
        });

        Schema::table('orders', function (Blueprint $t) {
            $t->foreignId('cash_shift_id')->nullable()->after('user_id')->constrained('cash_shifts')->nullOnDelete();
            $t->unsignedInteger('points_redeemed')->default(0)->after('discount');
            $t->unsignedInteger('points_earned')->default(0)->after('points_redeemed');
            $t->string('kitchen_status', 12)->nullable()->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $t) {
            $t->dropConstrainedForeignId('cash_shift_id');
            $t->dropColumn(['points_redeemed', 'points_earned', 'kitchen_status']);
        });
        Schema::dropIfExists('cash_shifts');
        Schema::table('businesses', function (Blueprint $t) {
            $t->dropColumn(['tax_rate', 'tax_label', 'receipt_footer']);
        });
    }
};
