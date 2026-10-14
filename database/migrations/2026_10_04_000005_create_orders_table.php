<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $t) {
            $t->id();
            $t->string('invoice_no', 30)->unique();
            $t->foreignId('user_id')->nullable()->constrained()->nullOnDelete();      // kasir
            $t->foreignId('customer_id')->nullable()->constrained()->nullOnDelete();
            $t->string('order_type', 12)->default('dine_in');   // dine_in | takeaway
            $t->string('table_no', 10)->nullable();
            $t->decimal('subtotal', 14, 2)->default(0);
            $t->decimal('discount', 14, 2)->default(0);
            $t->decimal('tax', 14, 2)->default(0);
            $t->decimal('total', 14, 2)->default(0);
            $t->string('payment_method', 12)->default('cash'); // cash | qris | card
            $t->decimal('paid_amount', 14, 2)->default(0);
            $t->decimal('change_amount', 14, 2)->default(0);
            $t->string('status', 12)->default('paid');          // paid | void
            $t->text('note')->nullable();
            $t->timestamp('paid_at')->nullable()->index();
            $t->timestamps();
        });

        Schema::create('order_items', function (Blueprint $t) {
            $t->id();
            $t->foreignId('order_id')->constrained()->cascadeOnDelete();
            $t->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
            $t->string('product_name');                         // snapshot, aman bila produk diubah/dihapus
            $t->decimal('price', 12, 2);
            $t->unsignedInteger('qty');
            $t->decimal('subtotal', 14, 2);
            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
    }
};
