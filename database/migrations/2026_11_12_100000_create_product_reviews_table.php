<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('product_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->integer('product_id');
            $table->integer('order_id');
            $table->unsignedTinyInteger('rating'); // 1 sampai 5
            $table->text('comment')->nullable();
            $table->timestamps();

            // Foreign key order_id mengacu ke orders.orders_id dengan tipe data int(11) yang sesuai
            $table->foreign('order_id')->references('orders_id')->on('orders')->cascadeOnDelete();

            // Mencegah ulasan ganda untuk item pesanan yang sama oleh pengguna yang sama
            $table->unique(['user_id', 'order_id', 'product_id'], 'user_order_product_review_unique');

            $table->index('product_id');
            $table->index('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_reviews');
    }
};
