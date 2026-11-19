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
        if (!Schema::hasTable('stock_movements')) {
            Schema::create('stock_movements', function (Blueprint $table) {
                $table->id();
                $table->unsignedInteger('product_id');
                $table->unsignedBigInteger('user_id')->nullable();
                $table->string('type', 30); // in, out, adjustment, purchase, cancellation
                $table->integer('quantity'); // Positif untuk penambahan, negatif untuk pengurangan
                $table->integer('stock_before');
                $table->integer('stock_after');
                $table->text('description')->nullable();
                $table->timestamps();

                $table->index('product_id');
                $table->index('user_id');
                $table->index('type');
                $table->index('created_at');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
    }
};
