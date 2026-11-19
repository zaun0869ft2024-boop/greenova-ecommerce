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
        if (!Schema::hasTable('payment_transactions')) {
            Schema::create('payment_transactions', function (Blueprint $table) {
                $table->id();
                $table->string('order_group', 120)->unique();
                $table->unsignedBigInteger('user_id');
                $table->decimal('gross_amount', 14, 2);
                $table->string('snap_token', 255)->nullable();
                $table->text('snap_redirect_url')->nullable();
                $table->string('payment_status', 30)->default('pending');
                $table->string('payment_type', 50)->nullable();
                $table->string('transaction_id', 100)->nullable();
                $table->string('fraud_status', 30)->nullable();
                $table->json('payment_payload')->nullable();
                $table->timestamp('paid_at')->nullable();
                $table->timestamp('expired_at')->nullable();
                $table->timestamps();

                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
                $table->index('order_group');
                $table->index('payment_status');
            });
        }

        if (Schema::hasTable('orders') && !Schema::hasColumn('orders', 'order_group')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->string('order_group', 120)->nullable()->after('orders_id');
                $table->index('order_group');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('orders') && Schema::hasColumn('orders', 'order_group')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->dropIndex(['order_group']);
                $table->dropColumn('order_group');
            });
        }

        Schema::dropIfExists('payment_transactions');
    }
};
