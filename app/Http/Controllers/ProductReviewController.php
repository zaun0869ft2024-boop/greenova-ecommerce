<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use App\Models\ProductReview;
use Illuminate\Http\Request;

class ProductReviewController extends Controller
{
    /**
     * Simpan ulasan baru.
     * Hanya pembeli dengan pesanan berstatus Selesai yang dapat memberikan ulasan.
     * Satu order hanya boleh memiliki satu review (dicegah di DB dan di sini).
     */
    public function store(Request $request, $productId)
    {
        $validated = $request->validate([
            'order_id' => 'required|integer',
            'rating'   => 'required|integer|min:1|max:5',
            'comment'  => 'nullable|string|max:1000',
        ], [
            'order_id.required' => 'ID pesanan wajib diisi.',
            'rating.required'   => 'Pilih rating bintang 1 sampai 5.',
            'rating.min'        => 'Rating minimal 1 bintang.',
            'rating.max'        => 'Rating maksimal 5 bintang.',
            'comment.max'       => 'Ulasan maksimal 1000 karakter.',
        ]);

        $product = Product::findOrFail($productId);
        $siblingIds = $product->siblingProductIds();

        // Backend verification: cari order milik user untuk produk ini
        $order = Order::where('orders_id', (int) $validated['order_id'])
            ->where('user_id', auth()->id())
            ->whereIn('product_id', $siblingIds)
            ->first();

        if (! $order) {
            return back()->with('error', 'Pesanan tidak ditemukan atau bukan milik Anda.');
        }

        // Verifikasi: status Selesai (case-insensitive) dan belum ada review
        if (strcasecmp(trim($order->status ?? ''), 'selesai') !== 0) {
            return back()->with('error', 'Hanya pesanan berstatus Selesai yang dapat diulas.');
        }

        if ($order->review()->exists()) {
            return back()->with('error', 'Pesanan ini sudah pernah diulas sebelumnya.');
        }

        ProductReview::create([
            'user_id'    => auth()->id(),
            'product_id' => $product->product_id,
            'order_id'   => $order->orders_id,
            'rating'     => (int) $validated['rating'],
            'comment'    => isset($validated['comment']) ? trim($validated['comment']) : null,
        ]);

        return back()->with('success', 'Terima kasih! Ulasan kamu berhasil dikirim.');
    }

    /**
     * Perbarui ulasan milik pengguna sendiri.
     * Pengguna tidak dapat mengubah ulasan milik orang lain (abort 403).
     */
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'rating'  => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:1000',
        ], [
            'rating.required' => 'Pilih rating bintang 1 sampai 5.',
            'rating.min'      => 'Rating minimal 1 bintang.',
            'rating.max'      => 'Rating maksimal 5 bintang.',
            'comment.max'     => 'Ulasan maksimal 1000 karakter.',
        ]);

        $review = ProductReview::findOrFail($id);

        // Pastikan hanya pemilik ulasan yang dapat mengedit
        if ((int) $review->user_id !== (int) auth()->id()) {
            abort(403, 'Kamu tidak memiliki izin untuk mengubah ulasan ini.');
        }

        $review->update([
            'rating'  => (int) $validated['rating'],
            'comment' => isset($validated['comment']) ? trim($validated['comment']) : null,
        ]);

        return back()->with('success', 'Ulasan kamu berhasil diperbarui.');
    }
}
