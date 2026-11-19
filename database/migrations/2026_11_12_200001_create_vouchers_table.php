<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vouchers', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique();
            $table->enum('discount_type', ['percent', 'nominal']);
            $table->decimal('discount_value', 12, 2);
            $table->decimal('min_purchase', 14, 2)->default(0);
            $table->decimal('max_discount', 14, 2)->nullable(); // batas atas potongan untuk tipe percent
            $table->integer('usage_limit')->nullable();         // NULL = tidak terbatas
            $table->integer('used_count')->default(0);
            $table->dateTime('starts_at')->nullable();
            $table->dateTime('ends_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vouchers');
    }
};
