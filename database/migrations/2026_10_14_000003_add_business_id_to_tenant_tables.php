<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Multi-bisnis: setiap data milik satu bisnis (business_id).
 * Kolom dibuat nullable agar aman dijalankan di DB yang sudah berisi data.
 * Unique global diganti unique per-bisnis (nama kategori, SKU, nomor invoice).
 */
return new class extends Migration {
    private array $tables = ['users', 'categories', 'products', 'customers', 'orders', 'stock_movements'];

    public function up(): void
    {
        foreach ($this->tables as $name) {
            Schema::table($name, function (Blueprint $t) {
                $t->foreignId('business_id')->nullable()->constrained()->cascadeOnDelete();
            });
        }

        Schema::table('categories', function (Blueprint $t) {
            $t->dropUnique(['name']);
            $t->unique(['business_id', 'name']);
        });
        Schema::table('products', function (Blueprint $t) {
            $t->dropUnique(['sku']);
            $t->unique(['business_id', 'sku']);
        });
        Schema::table('orders', function (Blueprint $t) {
            $t->dropUnique(['invoice_no']);
            $t->unique(['business_id', 'invoice_no']);
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $t) {
            $t->dropUnique(['business_id', 'invoice_no']);
            $t->unique('invoice_no');
        });
        Schema::table('products', function (Blueprint $t) {
            $t->dropUnique(['business_id', 'sku']);
            $t->unique('sku');
        });
        Schema::table('categories', function (Blueprint $t) {
            $t->dropUnique(['business_id', 'name']);
            $t->unique('name');
        });

        foreach (array_reverse($this->tables) as $name) {
            Schema::table($name, function (Blueprint $t) {
                $t->dropConstrainedForeignId('business_id');
            });
        }
    }
};
