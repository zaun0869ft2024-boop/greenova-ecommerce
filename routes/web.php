<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\ProductController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\ChatbotController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AdminProductController;
use App\Http\Controllers\AdminOrderController;
use App\Http\Controllers\AdminCategoryController;
use App\Http\Controllers\AdminUserController;
use App\Http\Controllers\Auth\GoogleController;
use App\Http\Controllers\ProductReviewController;
use App\Http\Controllers\AdminReviewController;
use App\Http\Controllers\AdminDiscountController;
use App\Http\Controllers\AdminVoucherController;
use App\Http\Controllers\MidtransWebhookController;
use App\Http\Controllers\CustomerDashboardController;
use App\Http\Controllers\AdminInventoryController;
use App\Http\Controllers\AdminReportController;


// ========================================
// 1. ROUTE PUBLIK (GUEST & CUSTOMER)
// ========================================

// Midtrans Payment Webhook (Notification)
Route::post('/midtrans/notification', [MidtransWebhookController::class, 'handle'])
    ->name('midtrans.notification');

// Halaman Utama / Beranda
Route::get('/', [ProductController::class, 'index'])
    ->name('home');

// Katalog & Detail Produk (Hanya lihat informasi, rating, & komentar)
Route::get('/products', [ProductController::class, 'products'])
    ->name('products');

Route::get('/product/{id}', [ProductController::class, 'detail'])
    ->name('product.detail');

// Greenova Chatbot Catalog Endpoint
Route::get('/chatbot/products', [ChatbotController::class, 'products'])
    ->name('chatbot.products');

// Google Authentication
Route::get('/auth/google', [GoogleController::class, 'redirect'])
    ->name('auth.google');

Route::get('/auth/google/callback', [GoogleController::class, 'callback'])
    ->name('auth.google.callback');


// ========================================
// 2. ROUTE TERPROTEKSI AUTENTIKASI (CUSTOMER)
// ========================================

Route::middleware('auth')->group(function () {

    // DASHBOARD CUSTOMER
    Route::get('/dashboard', [CustomerDashboardController::class, 'index'])
        ->name('dashboard');

    // KERANJANG BELANJA
    Route::get('/cart', [CartController::class, 'cart'])
        ->name('cart');

    Route::post('/add-cart/{id}', [CartController::class, 'addToCart'])
        ->name('add.cart');

    // Kompatibilitas route GET add-cart (terlindungi auth)
    Route::get('/add-cart/{id}', [CartController::class, 'addToCart']);

    // HTTP method DELETE untuk hapus cart item (dengan fallback kompatibilitas GET & DELETE)
    Route::match(['delete', 'get'], '/remove-cart/{id}', [CartController::class, 'remove'])
        ->name('remove.cart');

    Route::delete('/cart/{id}', [CartController::class, 'remove'])
        ->name('cart.remove');

    Route::post('/update-cart/{id}', [CartController::class, 'update'])
        ->name('update.cart');

    // TRANSAKSI & CHECKOUT
    Route::post('/buy-now/{id}', [CartController::class, 'buyNow'])
        ->name('buy.now');

    Route::post('/checkout/select', [CartController::class, 'prepareCheckout'])
        ->name('checkout.select');

    Route::get('/checkout', [CartController::class, 'checkout'])
        ->name('checkout');

    Route::post('/checkout', [CartController::class, 'processCheckout'])
        ->name('checkout.process');

    Route::post('/checkout/voucher/apply', [CartController::class, 'applyVoucher'])
        ->name('checkout.voucher.apply');

    Route::post('/checkout/voucher/remove', [CartController::class, 'removeVoucher'])
        ->name('checkout.voucher.remove');

    Route::get('/order-success', function (\Illuminate\Http\Request $request) {
        $ref = $request->query('ref', session('order_group'));
        $orders = collect();
        $snapToken = session('snap_token');
        $paymentService = app(\App\Services\PaymentService::class);
        $grossTotal = 0.0;
        $paymentMethod = null;

        if (!empty($ref)) {
            $orders = \App\Models\Order::with('product')
                ->where('payment_reference', $ref)
                ->where('user_id', auth()->id())
                ->get();

            if ($orders->isNotEmpty()) {
                $grossTotal = (float) $orders->sum('subtotal');
                $paymentMethod = $orders->first()->payment_method;

                // Jika metode pembayaran Midtrans dan status masih pending, lakukan verifikasi via Midtrans GET Status API
                if ($paymentMethod === 'midtrans' && strtolower(trim((string)$orders->first()->payment_status)) === 'pending') {
                    try {
                        $paymentService->checkAndSyncStatus($ref, auth()->user());
                        // Muat ulang data orders setelah rekonsiliasi
                        $orders = \App\Models\Order::with('product')
                            ->where('payment_reference', $ref)
                            ->where('user_id', auth()->id())
                            ->get();
                    } catch (\Throwable $e) {
                        \Illuminate\Support\Facades\Log::warning("Order success status sync notice: " . $e->getMessage());
                    }
                }

                $firstOrder = $orders->first();
                $currentPaymentStatus = strtolower(trim((string)($firstOrder->payment_status ?? 'pending')));

                if (empty($snapToken) && $paymentMethod === 'midtrans' && $currentPaymentStatus === 'pending') {
                    try {
                        $tokenData = $paymentService->createOrGetSnapToken($ref, auth()->user());
                        $snapToken = $tokenData['snap_token'] ?? null;
                    } catch (\Throwable $e) {
                        // Server Key belum diisi atau sandbox offline
                    }
                }
            }
        }

        return view('order.success', compact('ref', 'orders', 'snapToken', 'grossTotal', 'paymentMethod', 'paymentService'));
    })->name('order.success');

    // PEMBAYARAN MIDTRANS (CUSTOMER)
    Route::get('/payment/snap-token/{orderGroup}', [MidtransWebhookController::class, 'getSnapToken'])
        ->name('payment.snap_token');

    Route::get('/payment/status/{orderGroup}', [MidtransWebhookController::class, 'checkStatus'])
        ->name('payment.status');

    // RIWAYAT & DETAIL PESANAN
    Route::get('/orders', [CartController::class, 'orders'])
        ->name('orders');

    Route::get('/orders/{id}', [CartController::class, 'showOrder'])
        ->name('orders.show');

    Route::patch('/orders/{id}/cancel', [CartController::class, 'cancelOrder'])
        ->name('orders.cancel');

    // PROFIL PENGGUNA
    Route::get('/profile', [ProfileController::class, 'edit'])
        ->name('profile.edit');

    Route::patch('/profile', [ProfileController::class, 'update'])
        ->name('profile.update');

    Route::delete('/profile', [ProfileController::class, 'destroy'])
        ->name('profile.destroy');

    // RATING & REVIEW PRODUK (Hanya dari riwayat pesanan Selesai)
    Route::post('/product/{productId}/review', [ProductReviewController::class, 'store'])
        ->name('product.review.store');

    Route::patch('/review/{id}', [ProductReviewController::class, 'update'])
        ->name('review.update');

});


// ========================================
// 3. ROUTE KHUSUS ADMIN
// ========================================

Route::middleware(['auth', 'admin'])->group(function () {

    // DASHBOARD ADMIN
    Route::get('/admin', [AdminController::class, 'index'])
        ->name('admin.dashboard');

    // KELOLA PRODUK
    Route::get('/admin/products', [AdminProductController::class, 'index'])
        ->name('admin.products.index');

    Route::get('/admin/products/create', [AdminProductController::class, 'create'])
        ->name('admin.products.create');

    Route::post('/admin/products', [AdminProductController::class, 'store'])
        ->name('admin.products.store');

    Route::post('/admin/products/import', [AdminProductController::class, 'importCsv'])
        ->name('admin.products.import');

    Route::get('/admin/products/{id}/edit', [AdminProductController::class, 'edit'])
        ->name('admin.products.edit');

    Route::put('/admin/products/{id}', [AdminProductController::class, 'update'])
        ->name('admin.products.update');

    Route::delete('/admin/products/{id}', [AdminProductController::class, 'destroy'])
        ->name('admin.products.destroy');

    // KELOLA PESANAN
    Route::get('/admin/orders', [AdminOrderController::class, 'index'])
        ->name('admin.orders.index');

    Route::put('/admin/orders/{id}/status', [AdminOrderController::class, 'updateStatus'])
        ->name('admin.orders.status');

    Route::post('/admin/orders/{id}/sync-midtrans', [AdminOrderController::class, 'syncMidtrans'])
        ->name('admin.orders.sync_midtrans');

    // MANAJEMEN INVENTORI & STOK
    Route::get('/admin/inventory', [AdminInventoryController::class, 'index'])
        ->name('admin.inventory.index');

    Route::get('/admin/inventory/history', [AdminInventoryController::class, 'history'])
        ->name('admin.inventory.history');

    Route::post('/admin/inventory/adjust', [AdminInventoryController::class, 'adjust'])
        ->name('admin.inventory.adjust');

    // LAPORAN TRANSAKSI & PENJUALAN
    Route::get('/admin/reports/transactions', [AdminReportController::class, 'index'])
        ->name('admin.reports.transactions');

    Route::get('/admin/reports/transactions/export-csv', [AdminReportController::class, 'exportCsv'])
        ->name('admin.reports.export_csv');

    Route::get('/admin/reports/transactions/print', [AdminReportController::class, 'exportPrint'])
        ->name('admin.reports.print');

    // KELOLA PENGGUNA & PERAN (ROLE)
    Route::get('/admin/users', [AdminUserController::class, 'index'])
        ->name('admin.users.index');

    Route::patch('/admin/users/{id}/role', [AdminUserController::class, 'updateRole'])
        ->name('admin.users.update_role');

    // KELOLA KATEGORI
    Route::get('/admin/categories', [AdminCategoryController::class, 'index'])
        ->name('admin.categories.index');

    Route::get('/admin/categories/create', [AdminCategoryController::class, 'create'])
        ->name('admin.categories.create');

    Route::post('/admin/categories', [AdminCategoryController::class, 'store'])
        ->name('admin.categories.store');

    Route::get('/admin/categories/{id}/edit', [AdminCategoryController::class, 'edit'])
        ->name('admin.categories.edit');

    Route::put('/admin/categories/{id}', [AdminCategoryController::class, 'update'])
        ->name('admin.categories.update');

    Route::delete('/admin/categories/{id}', [AdminCategoryController::class, 'destroy'])
        ->name('admin.categories.destroy');

    // MODERASI ULASAN
    Route::get('/admin/reviews', [AdminReviewController::class, 'index'])
        ->name('admin.reviews.index');

    Route::delete('/admin/reviews/{id}', [AdminReviewController::class, 'destroy'])
        ->name('admin.reviews.destroy');

    // KELOLA DISKON PRODUK
    Route::get('/admin/discounts', [AdminDiscountController::class, 'index'])
        ->name('admin.discounts.index');

    Route::get('/admin/discounts/create', [AdminDiscountController::class, 'create'])
        ->name('admin.discounts.create');

    Route::post('/admin/discounts', [AdminDiscountController::class, 'store'])
        ->name('admin.discounts.store');

    Route::get('/admin/discounts/{id}/edit', [AdminDiscountController::class, 'edit'])
        ->name('admin.discounts.edit');

    Route::put('/admin/discounts/{id}', [AdminDiscountController::class, 'update'])
        ->name('admin.discounts.update');

    Route::delete('/admin/discounts/{id}', [AdminDiscountController::class, 'destroy'])
        ->name('admin.discounts.destroy');

    // KELOLA VOUCHER
    Route::get('/admin/vouchers', [AdminVoucherController::class, 'index'])
        ->name('admin.vouchers.index');

    Route::get('/admin/vouchers/create', [AdminVoucherController::class, 'create'])
        ->name('admin.vouchers.create');

    Route::post('/admin/vouchers', [AdminVoucherController::class, 'store'])
        ->name('admin.vouchers.store');

    Route::get('/admin/vouchers/{id}/edit', [AdminVoucherController::class, 'edit'])
        ->name('admin.vouchers.edit');

    Route::put('/admin/vouchers/{id}', [AdminVoucherController::class, 'update'])
        ->name('admin.vouchers.update');

    Route::delete('/admin/vouchers/{id}', [AdminVoucherController::class, 'destroy'])
        ->name('admin.vouchers.destroy');

});


// ========================================
// 4. ROUTE AUTENTIKASI LARAVEL BREEZE
// ========================================

require __DIR__ . '/auth.php';
