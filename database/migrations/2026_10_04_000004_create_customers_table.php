<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('customers', function (Blueprint $t) {
            $t->id();
            $t->string('code', 20)->nullable()->unique();   // CR-0001, dibuat otomatis
            $t->string('name');
            $t->string('phone', 30)->nullable()->index();
            $t->string('email')->nullable();
            $t->string('tier', 10)->default('bronze');      // bronze | silver | gold | vip
            $t->unsignedInteger('points')->default(0);
            $t->decimal('total_spent', 14, 2)->default(0);
            $t->unsignedInteger('visits')->default(0);
            $t->timestamp('last_visit_at')->nullable();
            $t->date('birthday')->nullable();
            $t->text('notes')->nullable();                  // catatan barista / preferensi
            $t->string('allergy', 255)->nullable();         // alert alergi
            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
