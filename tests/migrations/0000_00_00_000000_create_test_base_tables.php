<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Skema tabel dasar untuk database testing terisolasi (SQLite in-memory).
     * Berisi tabel legacy yang di MySQL diimpor via dump SQL.
     */
    public function up(): void
    {
        if (! Schema::hasTable('categories')) {
            Schema::create('categories', function (Blueprint $table) {
                $table->increments('cat_id');
                $table->string('cat_title');
                $table->text('cat_description')->nullable();
            });
        }

        if (! Schema::hasTable('products')) {
            Schema::create('products', function (Blueprint $table) {
                $table->increments('product_id');
                $table->string('product_title');
                $table->integer('product_price');
                $table->string('product_image')->nullable();
                $table->text('product_desc')->nullable();
                $table->integer('product_cat')->nullable();
            });
        }

        if (! Schema::hasTable('orders')) {
            Schema::create('orders', function (Blueprint $table) {
                $table->increments('orders_id');
                $table->unsignedBigInteger('user_id');
                $table->integer('product_id');
                $table->integer('qty');
                $table->string('status', 50)->default('Pending');
                $table->date('date')->nullable();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
        Schema::dropIfExists('products');
        Schema::dropIfExists('categories');
    }
};
