<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Voucher extends Model
{
    use HasFactory;

    protected $table = 'vouchers';

    public $timestamps = true;

    protected $fillable = [
        'code',
        'discount_type',
        'discount_value',
        'min_purchase',
        'max_discount',
        'usage_limit',
        'used_count',
        'starts_at',
        'ends_at',
        'is_active',
    ];

    protected $casts = [
        'is_active'      => 'boolean',
        'starts_at'      => 'datetime',
        'ends_at'        => 'datetime',
        'discount_value' => 'float',
        'min_purchase'   => 'float',
        'max_discount'   => 'float',
        'usage_limit'    => 'integer',
        'used_count'     => 'integer',
    ];

    // ─── Scopes ───────────────────────────────────────────────────────────────

    /**
     * Voucher yang sedang aktif sekarang.
     */
    public function scopeActive($query)
    {
        $now = now();

        return $query->where('is_active', true)
            ->where(function ($q) use ($now) {
                $q->whereNull('starts_at')->orWhere('starts_at', '<=', $now);
            })
            ->where(function ($q) use ($now) {
                $q->whereNull('ends_at')->orWhere('ends_at', '>=', $now);
            });
    }

    // ─── Methods ──────────────────────────────────────────────────────────────

    /**
     * Validasi apakah voucher bisa dipakai untuk subtotal tertentu.
     *
     * @return array{valid: bool, message: string}
     */
    public function isValid(float $subtotal): array
    {
        if (!$this->is_active) {
            return ['valid' => false, 'message' => 'Voucher tidak aktif.'];
        }

        $now = now();

        if ($this->starts_at !== null && $this->starts_at->gt($now)) {
            return ['valid' => false, 'message' => 'Voucher belum berlaku.'];
        }

        if ($this->ends_at !== null && $this->ends_at->lt($now)) {
            return ['valid' => false, 'message' => 'Voucher sudah kadaluarsa.'];
        }

        if ($this->min_purchase !== null && $subtotal < $this->min_purchase) {
            return [
                'valid'   => false,
                'message' => 'Minimum pembelian Rp ' . number_format($this->min_purchase, 0, ',', '.') . ' untuk menggunakan voucher ini.',
            ];
        }

        if ($this->usage_limit !== null && $this->used_count >= $this->usage_limit) {
            return ['valid' => false, 'message' => 'Kuota voucher sudah habis.'];
        }

        return ['valid' => true, 'message' => 'Voucher berhasil diterapkan.'];
    }

    /**
     * Hitung besar diskon dari voucher.
     */
    public function calculateDiscount(float $subtotal): float
    {
        if ($this->discount_type === 'percent') {
            $discount = $subtotal * $this->discount_value / 100;

            if ($this->max_discount !== null) {
                $discount = min($discount, $this->max_discount);
            }

            return max(0.0, $discount);
        }

        // nominal
        return max(0.0, min((float) $this->discount_value, $subtotal));
    }
}
