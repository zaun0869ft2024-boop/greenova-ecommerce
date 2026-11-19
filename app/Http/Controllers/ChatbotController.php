<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use App\Models\ProductReview;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ChatbotController extends Controller
{
    /**
     * Endpoint Pencarian & Rekomendasi Produk untuk Greenova Assistant.
     * Menggunakan data riil dari database (katalog, harga, stok, rating, dan riwayat pesanan).
     */
    public function products(Request $request)
    {
        $validated = $request->validate([
            'search' => 'nullable|string|max:100',
            'min_price' => 'nullable|integer|min:0',
            'max_price' => 'nullable|integer|min:0',
            'category_id' => 'nullable|integer',
            'available_only' => 'nullable|boolean',
            'sort' => 'nullable|string|in:cheapest,highest_price,best_seller,top_rated,newest',
            'compare_ids' => 'nullable|array',
            'compare_ids.*' => 'integer',
        ]);

        // Skenario 1: Mode Perbandingan Produk (Product Comparison)
        if (!empty($validated['compare_ids']) && count($validated['compare_ids']) >= 2) {
            $compareProducts = Product::with(['category', 'reviews', 'discounts'])
                ->whereIn('product_id', array_slice($validated['compare_ids'], 0, 3))
                ->get();

            return response()->json([
                'success' => true,
                'mode' => 'comparison',
                'products' => $compareProducts->map(fn ($p) => $this->formatProduct($p))->values(),
            ]);
        }

        $search = trim($validated['search'] ?? '');
        $minPrice = $validated['min_price'] ?? null;
        $maxPrice = $validated['max_price'] ?? null;
        $categoryId = $validated['category_id'] ?? null;
        $availableOnly = $request->boolean('available_only');
        $sort = $validated['sort'] ?? null;

        $query = Product::with(['category', 'reviews', 'discounts']);

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('product_title', 'like', '%' . $search . '%')
                  ->orWhere('product_desc', 'like', '%' . $search . '%');
            });
        }

        if ($categoryId !== null) {
            $query->where('product_cat', $categoryId);
        }

        if ($minPrice !== null) {
            $query->where('product_price', '>=', $minPrice);
        }

        if ($maxPrice !== null) {
            $query->where('product_price', '<=', $maxPrice);
        }

        if ($availableOnly) {
            $query->where('stock', '>', 0);
        }

        // Pengurutan pintar berdasarkan instruksi asisten
        if ($sort === 'cheapest') {
            $query->orderBy('product_price', 'asc');
        } elseif ($sort === 'highest_price') {
            $query->orderBy('product_price', 'desc');
        } elseif ($sort === 'best_seller') {
            $query->leftJoin('orders', function ($join) {
                $join->on('products.product_id', '=', 'orders.product_id')
                     ->where('orders.status', '=', 'Selesai');
            })
            ->select('products.*', DB::raw('COALESCE(SUM(orders.qty), 0) as sales_vol'))
            ->groupBy('products.product_id')
            ->orderByDesc('sales_vol')
            ->orderByDesc('products.product_id');
        } elseif ($sort === 'top_rated' && Schema::hasTable('product_reviews')) {
            $query->leftJoin('product_reviews', 'products.product_id', '=', 'product_reviews.product_id')
                ->select('products.*', DB::raw('COALESCE(AVG(product_reviews.rating), 0) as avg_rat'))
                ->groupBy('products.product_id')
                ->orderByDesc('avg_rat')
                ->orderByDesc('products.product_id');
        } elseif ($maxPrice !== null) {
            $query->orderBy('product_price', 'asc');
        } else {
            $query->orderBy('product_id', 'desc');
        }

        $products = $query->limit(6)->get();

        return response()->json([
            'success' => true,
            'mode' => 'search',
            'count' => $products->count(),
            'products' => $products->map(fn ($product) => $this->formatProduct($product))->values(),
        ]);
    }

    /**
     * Format data produk yang kaya informasi untuk komponen kartu chatbot.
     */
    private function formatProduct(Product $product): array
    {
        $avgRating = $product->averageRating();
        $reviewsCount = $product->reviewsCount();
        $effectivePrice = $product->effectivePrice();
        $basePrice = (float) $product->product_price;
        $hasDiscount = $effectivePrice < $basePrice;

        return [
            'id' => $product->product_id,
            'name' => $product->product_title,
            'price' => (float) $effectivePrice,
            'base_price' => $basePrice,
            'has_discount' => $hasDiscount,
            'formatted_price' => 'Rp ' . number_format($effectivePrice, 0, ',', '.'),
            'formatted_base_price' => 'Rp ' . number_format($basePrice, 0, ',', '.'),
            'stock' => (int) ($product->stock ?? 0),
            'in_stock' => (int) ($product->stock ?? 0) > 0,
            'category' => $product->category->cat_title ?? 'Umum',
            'category_id' => $product->product_cat,
            'rating' => round($avgRating, 1),
            'reviews_count' => $reviewsCount,
            'sold_count' => $product->soldCount(),
            'image' => $product->product_image
                ? asset('product_images/' . $product->product_image)
                : null,
            'url' => route('product.detail', $product->product_id),
        ];
    }
}
