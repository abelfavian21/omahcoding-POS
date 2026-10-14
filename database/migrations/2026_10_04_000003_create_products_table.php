<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('products', function (Blueprint $t) {
            $t->id();
            $t->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
            $t->string('sku', 40)->unique();
            $t->string('name');
            $t->text('description')->nullable();
            $t->decimal('price', 12, 2);
            $t->decimal('cost_price', 12, 2)->default(0);   // HPP, untuk hitung margin
            $t->integer('stock')->default(0);
            $t->integer('min_stock')->default(5);           // batas peringatan stok menipis
            $t->string('emoji', 8)->nullable();             // dipakai bila belum ada foto
            $t->string('image')->nullable();                // path di storage/public
            $t->boolean('is_active')->default(true);
            $t->timestamps();
        });

        Schema::create('stock_movements', function (Blueprint $t) {
            $t->id();
            $t->foreignId('product_id')->constrained()->cascadeOnDelete();
            $t->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $t->string('type', 20);                         // in | out | adjustment | sale | void
            $t->integer('qty');                             // bertanda: + masuk, - keluar
            $t->integer('stock_after');
            $t->string('note')->nullable();
            $t->unsignedBigInteger('order_id')->nullable(); // diisi saat type = sale/void
            $t->timestamps();
            $t->index(['product_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
        Schema::dropIfExists('products');
    }
};
