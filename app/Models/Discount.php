<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Discount extends Model
{
    use HasFactory;

    protected $table = 'discounts';

    public $timestamps = true;

    protected $fillable = [
        'product_id',
        'discount_type',
        'discount_value',
        'starts_at',
        'ends_at',
        'is_active',
    ];

    protected $casts = [
        'is_active'  => 'boolean',
        'starts_at'  => 'datetime',
        'ends_at'    => 'datetime',
        'discount_value' => 'float',
    ];

    // ─── Relations ────────────────────────────────────────────────────────────

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id', 'product_id');
    }

    // ─── Methods ──────────────────────────────────────────────────────────────

    /**
     * Apakah diskon ini sedang aktif sekarang?
     */
    public function isActive(): bool
    {
        if (!$this->is_active) {
            return false;
        }

        $now = now();

        if ($this->starts_at !== null && $this->starts_at->gt($now)) {
            return false;
        }

        if ($this->ends_at !== null && $this->ends_at->lt($now)) {
            return false;
        }

        return true;
    }

    /**
     * Harga efektif setelah diskon diterapkan.
     */
    public function effectivePrice(int $basePrice): int
    {
        if ($this->discount_type === 'percent') {
            return (int) max(0, floor($basePrice * (1 - $this->discount_value / 100)));
        }

        // nominal
        return (int) max(0, $basePrice - $this->discount_value);
    }

    /**
     * Persentase diskon yang ditampilkan di badge.
     */
    public function discountPercent(int $basePrice): int
    {
        if ($basePrice <= 0) {
            return 0;
        }

        if ($this->discount_type === 'percent') {
            return (int) $this->discount_value;
        }

        // nominal → konversi ke persen
        return (int) floor(($this->discount_value / $basePrice) * 100);
    }
}
