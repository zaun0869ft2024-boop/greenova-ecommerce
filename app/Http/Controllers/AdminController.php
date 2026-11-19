<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Discount;
use App\Models\Order;
use App\Models\PaymentTransaction;
use App\Models\Product;
use App\Models\User;
use App\Models\Voucher;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AdminController extends Controller
{
    public function index(Request $request)
    {
        $period = $request->input('period', '30_days');
        $customStart = $request->input('start_date');
        $customEnd = $request->input('end_date');

        // Tentukan rentang tanggal berdasarkan filter
        $now = Carbon::now();
        $startDate = match ($period) {
            'today' => $now->copy()->startOfDay(),
            '7_days' => $now->copy()->subDays(6)->startOfDay(),
            '30_days' => $now->copy()->subDays(29)->startOfDay(),
            'this_month' => $now->copy()->startOfMonth(),
            'this_year' => $now->copy()->startOfYear(),
            'custom' => !empty($customStart) ? Carbon::parse($customStart)->startOfDay() : $now->copy()->subDays(29)->startOfDay(),
            default => $now->copy()->subDays(29)->startOfDay(),
        };

        $endDate = match ($period) {
            'custom' => !empty($customEnd) ? Carbon::parse($customEnd)->endOfDay() : $now->copy()->endOfDay(),
            default => $now->copy()->endOfDay(),
        };

        $startStr = $startDate->toDateString();
        $endStr = $endDate->toDateString();

        // Query dasar order dalam periode
        $ordersInPeriod = Order::whereBetween('date', [$startStr, $endStr]);

        // 1. Total Pendapatan Bersih (Hanya dari pesanan Selesai / Lunas, tidak termasuk pending, failed, expired, cancelled)
        $completedOrdersQuery = Order::where(function ($q) {
            $q->where('status', 'Selesai')
              ->orWhereRaw('LOWER(payment_status) = ?', ['paid']);
        })->where(function ($q) {
            $q->where('status', '!=', 'Dibatalkan')
              ->whereNotIn(DB::raw('LOWER(COALESCE(payment_status, "pending"))'), ['failed', 'expired', 'cancelled']);
        });

        $hasSnapshotColumns = Schema::hasColumns('orders', ['subtotal', 'unit_price', 'discount_amount']);

        if ($hasSnapshotColumns) {
            $totalRevenue = (float) ((clone $completedOrdersQuery)
                ->whereBetween('date', [$startStr, $endStr])
                ->leftJoin('products', 'orders.product_id', '=', 'products.product_id')
                ->selectRaw('SUM(COALESCE(orders.subtotal, CASE WHEN orders.unit_price IS NOT NULL THEN CASE WHEN (orders.unit_price * orders.qty) - COALESCE(orders.discount_amount, 0) < 0 THEN 0 ELSE (orders.unit_price * orders.qty) - COALESCE(orders.discount_amount, 0) END ELSE orders.qty * COALESCE(products.product_price, 0) END)) as revenue')
                ->value('revenue') ?? 0);
        } else {
            $totalRevenue = (float) ((clone $completedOrdersQuery)
                ->whereBetween('date', [$startStr, $endStr])
                ->leftJoin('products', 'orders.product_id', '=', 'products.product_id')
                ->selectRaw('SUM(orders.qty * COALESCE(products.product_price, 0)) as revenue')
                ->value('revenue') ?? 0);
        }

        // 2. Statistik Pesanan & Pelanggan
        $totalOrders = (clone $ordersInPeriod)->count();
        $totalSoldUnits = (int) ((clone $completedOrdersQuery)->whereBetween('date', [$startStr, $endStr])->sum('qty') ?? 0);
        $totalProducts = Product::count();
        $totalCategories = Category::count();
        $totalCustomers = User::where('role', 'user')->count();

        // 3. Status Transaksi (Sukses, Pending, Batal) dalam periode
        $successfulCount = (clone $completedOrdersQuery)->whereBetween('date', [$startStr, $endStr])->count();
        $pendingCount = (clone $ordersInPeriod)->where(function ($q) {
            $q->where('status', 'Pending')
              ->orWhereRaw('LOWER(payment_status) = ?', ['pending']);
        })->where('status', '!=', 'Dibatalkan')->count();

        $cancelledCount = (clone $ordersInPeriod)->where(function ($q) {
            $q->where('status', 'Dibatalkan')
              ->orWhereIn(DB::raw('LOWER(payment_status)'), ['failed', 'expired', 'cancelled']);
        })->count();

        // 4. Produk Stok Menipis (&le; 10)
        $lowStockProducts = Product::where('stock', '<=', 10)
            ->orderBy('stock', 'asc')
            ->take(6)
            ->get();
        $lowStockCount = Product::where('stock', '<=', 10)->count();

        // 5. Produk Terlaris (Berdasarkan kuantitas terjual riil di pesanan selesai)
        $topProducts = (clone $completedOrdersQuery)
            ->whereBetween('date', [$startStr, $endStr])
            ->select('product_id', DB::raw('SUM(qty) as total_sold'), DB::raw('SUM(COALESCE(subtotal, 0)) as product_revenue'))
            ->groupBy('product_id')
            ->orderByDesc('total_sold')
            ->take(6)
            ->with(['product.category'])
            ->get();

        // 6. Data Trend Penjualan Harian untuk Diagram Interaktif
        $dailyData = (clone $completedOrdersQuery)
            ->whereBetween('date', [$startStr, $endStr])
            ->select(
                'date',
                DB::raw('COUNT(orders_id) as order_count'),
                DB::raw('SUM(COALESCE(subtotal, 0)) as day_revenue'),
                DB::raw('SUM(qty) as day_units')
            )
            ->groupBy('date')
            ->orderBy('date', 'asc')
            ->get()
            ->keyBy(fn ($item) => Carbon::parse($item->date)->toDateString());

        // Buat deret tanggal berurutan agar grafik tidak melompati hari kosong
        $chartLabels = [];
        $chartRevenues = [];
        $chartOrders = [];

        $cursor = $startDate->copy();
        $maxDays = 60; // Batasi jika custom rentang sangat panjang
        $dayCount = 0;
        while ($cursor->lte($endDate) && $dayCount < $maxDays) {
            $dateKey = $cursor->toDateString();
            $chartLabels[] = $cursor->format('d M');
            $record = $dailyData->get($dateKey);
            $chartRevenues[] = $record ? (float) $record->day_revenue : 0;
            $chartOrders[] = $record ? (int) $record->order_count : 0;
            $cursor->addDay();
            $dayCount++;
        }

        // 7. Pesanan Terbaru
        $recentOrders = Order::with(['product', 'user'])
            ->orderBy('orders_id', 'desc')
            ->take(7)
            ->get();

        // 8. Promo & Voucher Aktif
        $activeDiscounts = Schema::hasTable('discounts')
            ? Discount::where('is_active', true)
                ->where(function ($q) {
                    $q->whereNull('ends_at')->orWhere('ends_at', '>=', now());
                })
                ->count()
            : 0;

        $activeVouchers = Schema::hasTable('vouchers')
            ? Voucher::where('is_active', true)
                ->where(function ($q) {
                    $q->whereNull('ends_at')->orWhere('ends_at', '>=', now());
                })
                ->count()
            : 0;

        return view('admin.dashboard', compact(
            'period',
            'customStart',
            'customEnd',
            'startDate',
            'endDate',
            'totalRevenue',
            'totalOrders',
            'totalSoldUnits',
            'totalProducts',
            'totalCategories',
            'totalCustomers',
            'successfulCount',
            'pendingCount',
            'cancelledCount',
            'lowStockProducts',
            'lowStockCount',
            'topProducts',
            'chartLabels',
            'chartRevenues',
            'chartOrders',
            'recentOrders',
            'activeDiscounts',
            'activeVouchers'
        ));
    }
}
