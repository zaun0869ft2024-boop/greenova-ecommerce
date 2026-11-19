<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tambah kolom snapshot harga ke tabel orders.
     * Semua kolom nullable / default 0 agar data pesanan LAMA tidak terpengaruh.
     */
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->decimal('unit_price', 14, 2)->nullable()->after('qty');
            $table->decimal('discount_amount', 14, 2)->default(0)->after('unit_price');
            $table->string('voucher_code', 50)->nullable()->after('discount_amount');
            $table->decimal('voucher_discount', 14, 2)->default(0)->after('voucher_code');
            $table->decimal('subtotal', 14, 2)->nullable()->after('voucher_discount');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'unit_price',
                'discount_amount',
                'voucher_code',
                'voucher_discount',
                'subtotal',
            ]);
        });
    }
};
