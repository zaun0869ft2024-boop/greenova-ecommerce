<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Discount;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductReview;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ProductController extends Controller
{
    /**
     * Halaman Utama: Menampilkan Hero Banner, Kategori, Produk Unggulan,
     * Best Seller, Produk Terbaru, Diskon Aktif, dan Rekomendasi.
     */
    public function index()
    {
        // 1. Kategori dengan jumlah produk & sampel produk untuk cover kategori
        $categories = Category::withCount('products')
            ->with(['products' => function ($q) {
                $q->whereNotNull('product_image')->orderBy('product_id', 'asc');
            }])
            ->orderBy('cat_id')
            ->get();

        // 2. Produk Unggulan (Featured) — prioritaskan produk dengan stok tersedia
        $featuredProducts = Product::with(['category', 'reviews', 'discounts'])
            ->orderByRaw('CASE WHEN stock > 0 THEN 0 ELSE 1 END')
            ->orderBy('product_id', 'asc')
            ->take(8)
            ->get();

        // 3. Produk Terbaru (New Arrivals)
        $newArrivals = Product::with(['category', 'reviews', 'discounts'])
            ->orderBy('product_id', 'desc')
            ->take(4)
            ->get();

        // 4. Produk Terlaris (Best Seller) dari data penjualan riil pesanan Selesai
        $bestSellerIds = Order::where('status', 'Selesai')
            ->select('product_id', DB::raw('SUM(qty) as total_sold'))
            ->groupBy('product_id')
            ->orderByDesc('total_sold')
            ->take(4)
            ->pluck('product_id');

        if ($bestSellerIds->isNotEmpty()) {
            $bestSellers = Product::with(['category', 'reviews', 'discounts'])
                ->whereIn('product_id', $bestSellerIds)
                ->get()
                ->sortBy(function ($p) use ($bestSellerIds) {
                    return array_search($p->product_id, $bestSellerIds->toArray());
                })
                ->values();
        } else {
            // Fallback produk jika belum ada transaksi selesai
            $bestSellers = Product::with(['category', 'reviews', 'discounts'])
                ->take(4)
                ->get();
        }

        // 5. Promo Diskon Aktif
        $activePromos = collect();
        if (Schema::hasTable('discounts')) {
            $activePromos = Discount::with('product.category')
                ->where('is_active', true)
                ->where(function ($q) {
                    $q->whereNull('starts_at')->orWhere('starts_at', '<=', now());
                })
                ->where(function ($q) {
                    $q->whereNull('ends_at')->orWhere('ends_at', '>=', now());
                })
                ->whereHas('product')
                ->take(4)
                ->get();
        }

        // 6. Rekomendasi Produk Berdasarkan Rating Tertinggi
        $recommendedProducts = Product::with(['category', 'reviews', 'discounts'])
            ->where('stock', '>', 0)
            ->take(4)
            ->get();

        // Backward compatibility for view variables
        $products = $featuredProducts->take(4);

        return view('home', compact(
            'categories',
            'featuredProducts',
            'newArrivals',
            'bestSellers',
            'activePromos',
            'recommendedProducts',
            'products'
        ));
    }

    /**
     * Halaman Katalog Produk: Pencarian, Filter Kategori, Filter Harga Min/Maks,
     * Ketersediaan Stok, Pengurutan (Harga, Terbaru, Terlaris, Rating), dan Pagination.
     */
    public function products(Request $request)
    {
        $allowedSorts = ['latest', 'best_seller', 'rating', 'price_asc', 'price_desc'];

        $search   = mb_substr(trim((string) $request->input('search', '')), 0, 100);
        $category = ctype_digit((string) $request->input('category', '')) ? (int) $request->input('category') : null;
        $minPrice = is_numeric($request->input('min_price')) && (float) $request->input('min_price') > 0
            ? (int) $request->input('min_price') : null;
        $maxPrice = is_numeric($request->input('max_price')) && (float) $request->input('max_price') > 0
            ? (int) $request->input('max_price') : null;
        $inStock  = $request->boolean('in_stock');
        $sort     = in_array($request->input('sort'), $allowedSorts, true) ? $request->input('sort') : 'latest';

        // Jika pengguna tertukar memasukkan harga min > maks, tukar agar filter tetap masuk akal
        if ($minPrice !== null && $maxPrice !== null && $minPrice > $maxPrice) {
            [$minPrice, $maxPrice] = [$maxPrice, $minPrice];
        }

        $categories = Category::withCount('products')
            ->orderBy('cat_title')
            ->get();

        $activeCategory = $category !== null
            ? $categories->firstWhere('cat_id', $category)
            : null;

        // Harga efektif (setelah diskon aktif) agar filter & urutan harga sesuai harga yang dilihat pelanggan
        [$priceSql, $priceBindings] = $this->effectivePriceSql();

        $query = Product::with(['category', 'reviews', 'discounts'])
            ->select('products.*')
            ->when($search !== '', function ($q) use ($search) {
                // Dikelompokkan agar OR tidak mengabaikan filter lain
                $q->where(function ($sq) use ($search) {
                    $sq->where('products.product_title', 'like', '%' . $search . '%')
                       ->orWhere('products.product_desc', 'like', '%' . $search . '%');
                });
            })
            ->when($category !== null, fn($q) => $q->where('products.product_cat', $category))
            ->when($minPrice !== null, fn($q) => $q->whereRaw("($priceSql) >= ?", [...$priceBindings, $minPrice]))
            ->when($maxPrice !== null, fn($q) => $q->whereRaw("($priceSql) <= ?", [...$priceBindings, $maxPrice]))
            ->when($inStock, fn($q) => $q->where('products.stock', '>', 0));

        // Pengurutan (Sorting) — memakai subquery berkorelasi (tanpa GROUP BY) agar aman di MySQL strict mode
        if ($sort === 'price_asc') {
            $query->orderByRaw("($priceSql) asc", $priceBindings)->orderByDesc('products.product_id');
        } elseif ($sort === 'price_desc') {
            $query->orderByRaw("($priceSql) desc", $priceBindings)->orderByDesc('products.product_id');
        } elseif ($sort === 'best_seller') {
            $query->orderByDesc(
                Order::selectRaw('COALESCE(SUM(orders.qty), 0)')
                    ->whereColumn('orders.product_id', 'products.product_id')
                    ->where('orders.status', 'Selesai')
            )->orderByDesc('products.product_id');
        } elseif ($sort === 'rating' && Schema::hasTable('product_reviews')) {
            $query->orderByDesc(
                ProductReview::selectRaw('COALESCE(AVG(product_reviews.rating), 0)')
                    ->whereColumn('product_reviews.product_id', 'products.product_id')
            )->orderByDesc('products.product_id');
        } else {
            $query->orderByDesc('products.product_id');
        }

        $products = $query->paginate(12)->withQueryString();

        return view('product.index', compact(
            'products',
            'search',
            'category',
            'minPrice',
            'maxPrice',
            'inStock',
            'sort',
            'categories',
            'activeCategory'
        ));
    }

    /**
     * Ekspresi SQL harga efektif produk (harga dasar dikurangi diskon aktif pertama).
     * Logika sama dengan Product::effectivePrice() dan portabel untuk MySQL/MariaDB maupun SQLite.
     *
     * @return array{0: string, 1: array}
     */
    private function effectivePriceSql(): array
    {
        if (!Schema::hasTable('discounts')) {
            return ['products.product_price', []];
        }

        $now = now()->toDateTimeString();

        $sql = "COALESCE((
            SELECT CASE
                WHEN d.discount_type = 'percent'
                    THEN products.product_price * (1 - d.discount_value / 100.0)
                WHEN products.product_price - d.discount_value < 0 THEN 0
                ELSE products.product_price - d.discount_value
            END
            FROM discounts d
            WHERE d.product_id = products.product_id
              AND d.is_active = 1
              AND (d.starts_at IS NULL OR d.starts_at <= ?)
              AND (d.ends_at IS NULL OR d.ends_at >= ?)
            ORDER BY d.id
            LIMIT 1
        ), products.product_price)";

        return [$sql, [$now, $now]];
    }

    /**
     * Halaman Detail Produk: Informasi Produk, Gambar, Diskon, Ulasan Pembeli, dan Produk Terkait.
     */
    public function detail($id)
    {
        $product = Product::with(['category', 'reviews.user', 'discounts'])
            ->findOrFail($id);

        $siblingIds = $product->siblingProductIds();

        $reviews = collect();
        if (Schema::hasTable('product_reviews')) {
            $reviews = ProductReview::whereIn('product_id', $siblingIds)
                ->with('user')
                ->orderBy('id', 'desc')
                ->get();
        }

        $relatedProducts = Product::with(['category', 'reviews', 'discounts'])
            ->where('product_cat', $product->product_cat)
            ->whereNotIn('product_id', $siblingIds)
            ->orderBy('product_id', 'desc')
            ->take(4)
            ->get();

        $userCompletedOrder = null;
        $userExistingReview = null;
        if (auth()->check()) {
            $userCompletedOrder = Order::where('user_id', auth()->id())
                ->whereIn('product_id', $siblingIds)
                ->whereRaw('LOWER(status) = ?', ['selesai'])
                ->orderBy('orders_id', 'desc')
                ->first();

            if ($userCompletedOrder && Schema::hasTable('product_reviews')) {
                $userExistingReview = ProductReview::where('order_id', $userCompletedOrder->orders_id)->first();
            }
        }

        return view('product.detail', compact(
            'product',
            'reviews',
            'relatedProducts',
            'userCompletedOrder',
            'userExistingReview'
        ));
    }
}
