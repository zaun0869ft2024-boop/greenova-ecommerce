<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;
    protected $table = 'orders';

    protected $primaryKey = 'orders_id';

    public $timestamps = false;
    protected $fillable = [
        'order_group',
        'user_id',
        'product_id',
        'qty',
        'status',
        'date',
        'recipient_name',
        'recipient_phone',
        'shipping_address',
        'payment_method',
        'unit_price',
        'discount_amount',
        'voucher_code',
        'voucher_discount',
        'subtotal',
        'payment_status',
        'payment_gateway',
        'payment_reference',
        'paid_at',
    ];

    public function paymentTransaction()
    {
        return $this->belongsTo(PaymentTransaction::class, 'payment_reference', 'order_group');
    }

    public function product()
    {
        return $this->belongsTo(
            Product::class,
            'product_id',
            'product_id'
        );
    }
    public function user()
    {
        return $this->belongsTo(
            User::class,
            'user_id',
            'id'
        );
    }

    public function review()
    {
        return $this->hasOne(
            ProductReview::class,
            'order_id',
            'orders_id'
        );
    }

    public function canBeReviewed(): bool
    {
        return strcasecmp(trim($this->status ?? ''), 'Selesai') === 0 && !$this->review()->exists();
    }

    /**
     * Verifikasi apakah order ini layak di-review oleh user tertentu untuk produk tertentu.
     * Memeriksa: kepemilikan, status Selesai (case-insensitive), produk sesuai, belum punya review.
     */
    public function canBeReviewedByUser(int $userId, int $productId): bool
    {
        return (int) $this->user_id === $userId
            && (int) $this->product_id === $productId
            && strcasecmp(trim($this->status ?? ''), 'selesai') === 0
            && ! $this->review()->exists();
    }
}