<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;
    protected $table = 'products';

    protected $primaryKey = 'product_id';

    public $timestamps = false;

    protected $fillable = [
        'product_title',
        'product_price',
        'product_image',
        'product_desc',
        'product_cat',
        'stock',
    ];

    // ─── Relations ────────────────────────────────────────────────────────────

    public function category()
    {
        return $this->belongsTo(
            Category::class,
            'product_cat',
            'cat_id'
        );
    }

    public function reviews()
    {
        return $this->hasMany(
            ProductReview::class,
            'product_id',
            'product_id'
        );
    }

    public function orders()
    {
        return $this->hasMany(Order::class, 'product_id', 'product_id');
    }

    public function discounts()
    {
        return $this->hasMany(Discount::class, 'product_id', 'product_id');
    }

    // ─── Discount helpers ─────────────────────────────────────────────────────

    /**
     * Eager-loadable single active discount (approximate; use activeDiscount() for precision).
     */
    public function discount()
    {
        return $this->hasOne(Discount::class, 'product_id', 'product_id')
                    ->where('is_active', true)
                    ->where(function ($q) {
                        $q->whereNull('starts_at')->orWhereDate('starts_at', '<=', now()->toDateString());
                    })
                    ->where(function ($q) {
                        $q->whereNull('ends_at')->orWhereDate('ends_at', '>=', now()->toDateString());
                    });
    }

    /**
     * Kembalikan Discount yang benar-benar aktif sekarang, atau null.
     */
    public function activeDiscount(): ?Discount
    {
        // Jika relasi discounts sudah di-load, cari dari koleksi
        if ($this->relationLoaded('discounts')) {
            return $this->discounts->first(fn($d) => $d->isActive());
        }

        // Jika relasi discount (hasOne) sudah di-load
        if ($this->relationLoaded('discount')) {
            $d = $this->getRelation('discount');
            return ($d && $d->isActive()) ? $d : null;
        }

        // Query fresh
        return $this->discounts()
            ->where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('starts_at')->orWhere('starts_at', '<=', now());
            })
            ->where(function ($q) {
                $q->whereNull('ends_at')->orWhere('ends_at', '>=', now());
            })
            ->first();
    }

    /**
     * Harga efektif setelah diskon produk.
     */
    public function effectivePrice(): int
    {
        $discount = $this->activeDiscount();
        if (!$discount) {
            return (int) $this->product_price;
        }
        return $discount->effectivePrice((int) $this->product_price);
    }

    /**
     * Jumlah total qty yang sudah terjual (status Selesai).
     */
    public function soldCount(): int
    {
        if ($this->relationLoaded('orders')) {
            return (int) $this->orders
                ->where('status', 'Selesai')
                ->sum('qty');
        }
        return (int) $this->orders()->where('status', 'Selesai')->sum('qty');
    }

    // ─── Rating ───────────────────────────────────────────────────────────────

    public function siblingProductIds(): array
    {
        if (empty($this->product_title)) {
            return [$this->product_id];
        }

        static $cache = [];
        if (!isset($cache[$this->product_title])) {
            $cache[$this->product_title] = static::where('product_title', $this->product_title)
                ->pluck('product_id')
                ->toArray();
        }

        return $cache[$this->product_title] ?: [$this->product_id];
    }

    public function averageRating(): float
    {
        if ($this->relationLoaded('reviews') && $this->reviews->isNotEmpty()) {
            $avg = $this->reviews->avg('rating');
            return $avg ? round((float) $avg, 1) : 0.0;
        }

        $ids = $this->siblingProductIds();
        if (count($ids) > 1) {
            $avg = ProductReview::whereIn('product_id', $ids)->avg('rating');
            return $avg ? round((float) $avg, 1) : 0.0;
        }

        $avg = $this->reviews()->avg('rating');
        return $avg ? round((float) $avg, 1) : 0.0;
    }

    public function reviewsCount(): int
    {
        if ($this->relationLoaded('reviews') && $this->reviews->isNotEmpty()) {
            return (int) $this->reviews->count();
        }

        $ids = $this->siblingProductIds();
        if (count($ids) > 1) {
            return (int) ProductReview::whereIn('product_id', $ids)->count();
        }

        return (int) $this->reviews()->count();
    }

}
