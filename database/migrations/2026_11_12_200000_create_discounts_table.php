<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('discounts')) {
            Schema::create('discounts', function (Blueprint $table) {
                $table->id();
                $table->integer('product_id');
                $table->enum('discount_type', ['percent', 'nominal']);
                $table->decimal('discount_value', 12, 2);
                $table->dateTime('starts_at')->nullable();
                $table->dateTime('ends_at')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();

                $table->foreign('product_id')
                      ->references('product_id')
                      ->on('products')
                      ->onDelete('cascade');
            });

            return;
        }

        // Tangani tabel parsial akibat kegagalan foreign key sebelumnya tanpa DROP TABLE.
        Schema::table('discounts', function (Blueprint $table) {
            $table->integer('product_id')->change();
        });

        $foreignKeys = collect(Schema::getForeignKeys('discounts'))
            ->pluck('columns')
            ->flatten()
            ->all();

        if (!in_array('product_id', $foreignKeys, true)) {
            Schema::table('discounts', function (Blueprint $table) {
                $table->foreign('product_id')
                      ->references('product_id')
                      ->on('products')
                      ->onDelete('cascade');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('discounts');
    }
};
