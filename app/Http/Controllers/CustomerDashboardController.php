<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class CustomerDashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        // Jika akun berstatus Administrator dan bukan mengakses paksa customer dashboard
        if ($user->role === 'admin' && !request()->has('customer_view')) {
            return redirect()->route('admin.dashboard');
        }

        $userId = $user->id;

        // 1. Statistik Pesanan Customer
        $statistics = [
            'total_orders' => Order::where('user_id', $userId)->count(),
            'pending_payment' => Order::where('user_id', $userId)
                ->where(function ($q) {
                    $q->whereRaw('LOWER(payment_status) = ?', ['pending'])
                      ->orWhere(function ($sq) {
                          $sq->where('status', 'Pending')
                             ->whereRaw('LOWER(COALESCE(payment_status, "pending")) = ?', ['pending']);
                      });
                })
                ->where('status', '!=', 'Dibatalkan')
                ->count(),
            'processing' => Order::where('user_id', $userId)
                ->whereIn('status', ['Diproses', 'Dikirim'])
                ->count(),
            'completed' => Order::where('user_id', $userId)
                ->where('status', 'Selesai')
                ->count(),
            'cancelled' => Order::where('user_id', $userId)
                ->where('status', 'Dibatalkan')
                ->count(),
            'total_spent' => (float) (Order::where('user_id', $userId)
                ->where(function ($q) {
                    $q->where('status', 'Selesai')
                      ->orWhereRaw('LOWER(payment_status) = ?', ['paid']);
                })
                ->where('status', '!=', 'Dibatalkan')
                ->selectRaw('SUM(COALESCE(subtotal, unit_price * qty)) as spent')
                ->value('spent') ?? 0),
        ];

        // 2. Pesanan Terbaru
        $recentOrders = Order::with(['product', 'review'])
            ->where('user_id', $userId)
            ->orderBy('date', 'desc')
            ->orderBy('orders_id', 'desc')
            ->take(5)
            ->get();

        return view('dashboard', compact('user', 'statistics', 'recentOrders'));
    }
}
