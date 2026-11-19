<?php

namespace App\Http\Controllers;

use App\Models\ProductReview;
use Illuminate\Http\Request;

class AdminReviewController extends Controller
{
    /**
     * Tampilkan seluruh ulasan pelanggan untuk keperluan moderasi admin.
     * Rating rata-rata hanya dihitung dari ulasan yang masih ada (tidak terhapus).
     */
    public function index(Request $request)
    {
        $query = ProductReview::with(['user', 'product', 'order']);

        // Filter pencarian: nama user, email, nama produk, atau komentar
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->whereHas('user', fn($uq) =>
                    $uq->where('name', 'like', "%{$search}%")
                       ->orWhere('email', 'like', "%{$search}%")
                )->orWhereHas('product', fn($pq) =>
                    $pq->where('product_title', 'like', "%{$search}%")
                )->orWhere('comment', 'like', "%{$search}%");
            });
        }

        // Filter berdasarkan rating
        if ($request->filled('rating')) {
            $query->where('rating', (int) $request->rating);
        }

        $reviews = $query->orderBy('created_at', 'desc')
                         ->paginate(15)
                         ->withQueryString();

        // Statistik hanya berdasarkan ulasan yang masih tersisa (tidak terhapus)
        $totalReviews  = ProductReview::count();
        $avgRaw        = ProductReview::avg('rating');
        $averageRating = $avgRaw ? round((float) $avgRaw, 1) : 0.0;

        return view('admin.reviews.index', compact('reviews', 'totalReviews', 'averageRating'));
    }

    /**
     * Hapus ulasan yang melanggar aturan toko.
     * Rata-rata rating produk akan otomatis terupdate karena dihitung dinamis dari DB.
     */
    public function destroy($id)
    {
        $review = ProductReview::findOrFail($id);
        $review->delete();

        return redirect()
            ->route('admin.reviews.index')
            ->with('success', 'Ulasan berhasil dihapus.');
    }
}
