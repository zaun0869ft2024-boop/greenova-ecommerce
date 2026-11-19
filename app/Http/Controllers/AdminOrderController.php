<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use App\Services\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminOrderController extends Controller
{
    public function index(Request $request)
    {
        $paymentFilter = strtolower(trim((string) $request->input('payment_status', 'semua')));
        $orderFilter = trim((string) $request->input('status', 'semua'));
        $search = trim((string) $request->input('search', ''));

        $query = Order::with(['product', 'user']);

        // Filter status pembayaran
        if ($paymentFilter !== 'semua' && in_array($paymentFilter, ['paid', 'pending', 'unpaid', 'failed', 'expired', 'cancelled'], true)) {
            $query->whereRaw('LOWER(payment_status) = ?', [$paymentFilter]);
        }

        // Filter status pemenuhan pesanan
        if ($orderFilter !== 'semua' && in_array($orderFilter, ['Pending', 'Diproses', 'Dikirim', 'Selesai', 'Dibatalkan'], true)) {
            $query->where('status', $orderFilter);
        }

        // Pencarian ID, referensi, nama penerima, user
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('orders_id', 'like', "%{$search}%")
                  ->orWhere('payment_reference', 'like', "%{$search}%")
                  ->orWhere('recipient_name', 'like', "%{$search}%")
                  ->orWhere('recipient_phone', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($uq) use ($search) {
                      $uq->where('name', 'like', "%{$search}%")
                         ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }

        $orders = $query
            ->orderBy('date', 'desc')
            ->orderBy('orders_id', 'desc')
            ->paginate(15)
            ->withQueryString();

        $statistics = [
            'total' => Order::count(),
            'paid' => Order::whereRaw('LOWER(payment_status) = ?', ['paid'])->count(),
            'pending_payment' => Order::whereRaw('LOWER(payment_status) = ?', ['pending'])->count(),
            'order_pending' => Order::where('status', 'Pending')->count(),
            'order_diproses' => Order::where('status', 'Diproses')->count(),
            'order_dikirim' => Order::where('status', 'Dikirim')->count(),
            'order_selesai' => Order::where('status', 'Selesai')->count(),
        ];

        return view('admin.orders.index', compact(
            'orders',
            'statistics',
            'paymentFilter',
            'orderFilter',
            'search'
        ));
    }

    public function syncMidtrans(Request $request, $id, PaymentService $paymentService)
    {
        $order = Order::findOrFail($id);

        if (empty($order->payment_reference)) {
            return back()->with('error', 'Pesanan ini tidak memiliki nomor referensi pembayaran.');
        }

        try {
            $res = $paymentService->checkAndSyncStatus($order->payment_reference);
            $label = $res['payment_label'] ?? $res['payment_status'];

            return back()->with(
                'success',
                "Status pembayaran untuk referensi {$order->payment_reference} berhasil diverifikasi dengan Midtrans: {$label}."
            );
        } catch (\Throwable $e) {
            return back()->with('error', 'Gagal memverifikasi status dengan Midtrans: ' . $e->getMessage());
        }
    }

    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:Pending,Diproses,Dikirim,Selesai,Dibatalkan'
        ]);

        return DB::transaction(function () use ($request, $id) {
            $order = Order::where('orders_id', $id)->lockForUpdate()->firstOrFail();
            $oldStatus = trim($order->status ?? '');
            $newStatus = trim($request->status);

            // Jika status tidak berubah, tidak ada yang perlu diubah
            if (strcasecmp($oldStatus, $newStatus) === 0) {
                return redirect()
                    ->route('admin.orders.index')
                    ->with('success', 'Status pesanan tidak berubah.');
            }

            $product = Product::where('product_id', $order->product_id)->lockForUpdate()->first();
            $inventoryService = app(\App\Services\InventoryService::class);

            // 1. Dari status aktif menjadi Dibatalkan: kembalikan stok
            if (strcasecmp($newStatus, 'Dibatalkan') === 0 && strcasecmp($oldStatus, 'Dibatalkan') !== 0) {
                if ($product && (int) $order->qty > 0) {
                    $inventoryService->recordCancellation($product, (int) $order->qty, $order->payment_reference ?: ('#' . $order->orders_id), auth()->id());
                    $product->increment('stock', (int) $order->qty);
                }
            }
            // 2. Dari Dibatalkan menjadi status aktif: potong stok jika tersedia
            elseif (strcasecmp($oldStatus, 'Dibatalkan') === 0 && strcasecmp($newStatus, 'Dibatalkan') !== 0) {
                if ($product) {
                    if ((int) $product->stock < (int) $order->qty) {
                        return back()->with(
                            'error',
                            "Gagal mengaktifkan pesanan: Stok {$product->product_title} tidak mencukupi (tersedia: {$product->stock}, dibutuhkan: {$order->qty})."
                        );
                    }
                    $inventoryService->recordPurchase($product, (int) $order->qty, $order->payment_reference ?: ('#' . $order->orders_id), auth()->id());
                    $product->decrement('stock', (int) $order->qty);
                }
            }

            // 3. Update status pesanan (Status pembayaran tetap terpisah)
            $order->status = $newStatus;
            $order->save();

            return redirect()
                ->route('admin.orders.index')
                ->with('success', "Status pesanan #{$order->orders_id} berhasil diubah menjadi {$newStatus}.");
        });
    }
}
