<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // 1. Bahan Baku (Raw Ingredients)
        Schema::create('ingredients', function (Blueprint $t) {
            $t->id();
            $t->foreignId('business_id')->constrained()->cascadeOnDelete();
            $t->string('name');
            $t->string('unit', 20)->default('gram'); // gram, ml, pcs, shot, slice, dll
            $t->decimal('stock', 14, 2)->default(0);
            $t->decimal('min_stock', 14, 2)->default(0);
            $t->decimal('cost_per_unit', 14, 2)->default(0); // HPP per satuan (misal Rp 150 per gram)
            $t->boolean('is_active')->default(true);
            $t->timestamps();

            $t->index(['business_id', 'is_active']);
        });

        // 2. Riwayat Pergerakan Stok Bahan Baku
        Schema::create('ingredient_movements', function (Blueprint $t) {
            $t->id();
            $t->foreignId('business_id')->constrained()->cascadeOnDelete();
            $t->foreignId('ingredient_id')->constrained()->cascadeOnDelete();
            $t->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $t->string('type', 20); // in (restock), out (waste/rusak), adjustment (opname), sale (terpakai transaksi), void (pengembalian)
            $t->decimal('qty', 14, 2); // bertanda: + masuk, - keluar
            $t->decimal('stock_after', 14, 2);
            $t->string('note')->nullable();
            $t->unsignedBigInteger('order_id')->nullable();
            $t->timestamps();

            $t->index(['business_id', 'ingredient_id']);
            $t->index(['ingredient_id', 'created_at']);
        });

        // 3. Varian Produk & Modifiers (Ukuran, Topping, Level Gula, dll)
        Schema::create('product_variants', function (Blueprint $t) {
            $t->id();
            $t->foreignId('business_id')->constrained()->cascadeOnDelete();
            $t->foreignId('product_id')->constrained()->cascadeOnDelete();
            $t->string('group_name', 50)->default('Varian'); // contoh: 'Ukuran', 'Topping', 'Tingkat Manis'
            $t->string('name', 100);                         // contoh: 'Large', 'Extra Boba', 'Less Sugar'
            $t->decimal('price_extra', 12, 2)->default(0);  // tambahan harga jual
            $t->decimal('cost_extra', 12, 2)->default(0);   // tambahan modal/HPP
            $t->boolean('is_active')->default(true);
            $t->timestamps();

            $t->index(['business_id', 'product_id']);
        });

        // 4. Resep / Bill of Materials (BOM)
        Schema::create('product_recipes', function (Blueprint $t) {
            $t->id();
            $t->foreignId('business_id')->constrained()->cascadeOnDelete();
            $t->foreignId('product_id')->constrained()->cascadeOnDelete();
            $t->foreignId('product_variant_id')->nullable()->constrained('product_variants')->nullOnDelete();
            $t->foreignId('ingredient_id')->constrained()->cascadeOnDelete();
            $t->decimal('amount', 14, 2); // takaran bahan per porsi/menu
            $t->timestamps();

            $t->index(['business_id', 'product_id']);
        });

        // 5. Tambah kolom variants_json di order_items untuk merekam opsi varian & catatan pilihan kasir
        Schema::table('order_items', function (Blueprint $t) {
            $t->json('variants_json')->nullable()->after('subtotal');
        });
    }

    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $t) {
            $t->dropColumn('variants_json');
        });

        Schema::dropIfExists('product_recipes');
        Schema::dropIfExists('product_variants');
        Schema::dropIfExists('ingredient_movements');
        Schema::dropIfExists('ingredients');
    }
};
