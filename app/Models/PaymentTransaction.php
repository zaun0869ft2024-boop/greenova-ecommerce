<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PaymentTransaction extends Model
{
    use HasFactory;

    protected $table = 'payment_transactions';

    protected $fillable = [
        'order_group',
        'user_id',
        'gross_amount',
        'snap_token',
        'snap_redirect_url',
        'payment_status',
        'payment_type',
        'transaction_id',
        'fraud_status',
        'payment_payload',
        'paid_at',
        'expired_at',
    ];

    protected $casts = [
        'gross_amount' => 'decimal:2',
        'payment_payload' => 'array',
        'paid_at' => 'datetime',
        'expired_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function orders(): HasMany
    {
        // Hubungkan ke seluruh baris order yang memiliki order_group atau payment_reference yang sama
        return $this->hasMany(Order::class, 'payment_reference', 'order_group');
    }

    public function isPaid(): bool
    {
        return strtolower(trim((string) $this->payment_status)) === 'paid';
    }

    public function isPending(): bool
    {
        return strtolower(trim((string) $this->payment_status)) === 'pending';
    }

    public function isFailed(): bool
    {
        return in_array(strtolower(trim((string) $this->payment_status)), ['failed', 'expired', 'cancelled'], true);
    }

    public function canPay(): bool
    {
        if (!$this->isPending()) {
            return false;
        }

        if ($this->expired_at !== null && $this->expired_at->isPast()) {
            return false;
        }

        return true;
    }

    public function label(): string
    {
        return match (strtolower(trim((string) $this->payment_status))) {
            'paid' => 'Lunas',
            'pending' => 'Menunggu Pembayaran',
            'failed' => 'Gagal',
            'expired' => 'Kedaluwarsa',
            'cancelled' => 'Dibatalkan',
            default => 'Belum Dibayar',
        };
    }
}
