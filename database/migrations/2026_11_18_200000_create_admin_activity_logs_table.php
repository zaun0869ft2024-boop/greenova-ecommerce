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
        if (!Schema::hasTable('admin_activity_logs')) {
            Schema::create('admin_activity_logs', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id')->nullable();
                $table->string('action', 50); // product_create, product_update, stock_adjust, order_status, user_update, etc.
                $table->string('subject_type', 60)->nullable();
                $table->string('subject_id', 50)->nullable();
                $table->text('description');
                $table->text('properties')->nullable(); // JSON data
                $table->string('ip_address', 45)->nullable();
                $table->timestamps();

                $table->index('user_id');
                $table->index('action');
                $table->index('created_at');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('admin_activity_logs');
    }
};
