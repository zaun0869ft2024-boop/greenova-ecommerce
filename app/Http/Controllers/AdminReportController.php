<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminReportController extends Controller
{
    /**
     * Membangun query transaksi yang terfilter untuk laporan dan ekspor.
     */
    private function buildReportQuery(Request $request)
    {
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $orderStatus = trim((string) $request->input('order_status', 'semua'));
        $paymentStatus = trim((string) $request->input('payment_status', 'semua'));
        $paymentMethod = trim((string) $request->input('payment_method', 'semua'));
        $search = trim((string) $request->input('search', ''));

        $query = Order::with(['product.category', 'user']);

        if (!empty($startDate)) {
            $query->whereDate('date', '>=', $startDate);
        }

        if (!empty($endDate)) {
            $query->whereDate('date', '<=', $endDate);
        }

        if ($orderStatus !== 'semua' && in_array($orderStatus, ['Pending', 'Diproses', 'Dikirim', 'Selesai', 'Dibatalkan'], true)) {
            $query->where('status', $orderStatus);
        }

        if ($paymentStatus !== 'semua' && in_array($paymentStatus, ['paid', 'pending', 'unpaid', 'failed', 'expired', 'cancelled'], true)) {
            $query->whereRaw('LOWER(payment_status) = ?', [strtolower($paymentStatus)]);
        }

        if ($paymentMethod !== 'semua') {
            $query->where('payment_method', $paymentMethod);
        }

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('orders_id', 'like', "%{$search}%")
                  ->orWhere('payment_reference', 'like', "%{$search}%")
                  ->orWhere('recipient_name', 'like', "%{$search}%")
                  ->orWhere('recipient_phone', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($uq) use ($search) {
                      $uq->where('name', 'like', "%{$search}%")
                         ->orWhere('email', 'like', "%{$search}%");
                  })
                  ->orWhereHas('product', function ($pq) use ($search) {
                      $pq->where('product_title', 'like', "%{$search}%");
                  });
            });
        }

        return $query;
    }

    /**
     * Halaman Utama Laporan Transaksi.
     */
    public function index(Request $request)
    {
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $orderStatus = $request->input('order_status', 'semua');
        $paymentStatus = $request->input('payment_status', 'semua');
        $paymentMethod = $request->input('payment_method', 'semua');
        $search = $request->input('search', '');

        $query = $this->buildReportQuery($request);

        // Ringkasan berdasarkan filter aktif
        $totalOrders = (clone $query)->count();
        $totalUnits = (int) ((clone $query)->sum('qty') ?? 0);

        // Omzet transaksi yang lunas / selesai dalam filter aktif
        $totalRevenue = (float) ((clone $query)
            ->where(function ($q) {
                $q->where('status', 'Selesai')
                  ->orWhereRaw('LOWER(payment_status) = ?', ['paid']);
            })
            ->where('status', '!=', 'Dibatalkan')
            ->selectRaw('SUM(COALESCE(subtotal, unit_price * qty)) as rev')
            ->value('rev') ?? 0);

        $paidOrdersCount = (clone $query)->whereRaw('LOWER(payment_status) = ?', ['paid'])->count();
        $pendingOrdersCount = (clone $query)->whereRaw('LOWER(payment_status) = ?', ['pending'])->count();
        $cancelledOrdersCount = (clone $query)->where(function ($q) {
            $q->where('status', 'Dibatalkan')
              ->orWhereIn(DB::raw('LOWER(payment_status)'), ['failed', 'expired', 'cancelled']);
        })->count();

        $averageOrderValue = $totalOrders > 0 ? ($totalRevenue / max(1, $paidOrdersCount)) : 0;

        $orders = $query->orderBy('date', 'desc')
            ->orderBy('orders_id', 'desc')
            ->paginate(20)
            ->withQueryString();

        return view('admin.reports.transactions', compact(
            'orders',
            'startDate',
            'endDate',
            'orderStatus',
            'paymentStatus',
            'paymentMethod',
            'search',
            'totalOrders',
            'totalUnits',
            'totalRevenue',
            'paidOrdersCount',
            'pendingOrdersCount',
            'cancelledOrdersCount',
            'averageOrderValue'
        ));
    }

    /**
     * Ekspor Laporan Transaksi ke Format CSV.
     */
    public function exportCsv(Request $request): StreamedResponse
    {
        $query = $this->buildReportQuery($request)->orderBy('date', 'desc')->orderBy('orders_id', 'desc');
        $filename = 'laporan-transaksi-greenova-' . now()->format('Ymd-His') . '.csv';

        return response()->streamDownload(function () use ($query) {
            $handle = fopen('php://output', 'w');
            // Menulis UTF-8 BOM untuk kompatibilitas Excel di Windows
            fputs($handle, "\xEF\xBB\xBF");

            // Header Kolom
            fputcsv($handle, [
                'ID Pesanan',
                'Tanggal',
                'Nomor Referensi',
                'Nama Pelanggan',
                'Email Pelanggan',
                'Nama Penerima',
                'Telepon Penerima',
                'Alamat Pengiriman',
                'Produk',
                'Kategori',
                'Kuantitas',
                'Harga Satuan (Rp)',
                'Diskon (Rp)',
                'Subtotal (Rp)',
                'Metode Pembayaran',
                'Status Pembayaran',
                'Status Pesanan',
                'Waktu Lunas'
            ]);

            $query->chunk(200, function ($orders) use ($handle) {
                foreach ($orders as $order) {
                    $unitPrice = (float) ($order->unit_price ?? ($order->product->product_price ?? 0));
                    $discount = (float) ($order->discount_amount ?? 0) + (float) ($order->voucher_discount ?? 0);
                    $subtotal = (float) ($order->subtotal ?? ($unitPrice * $order->qty - $discount));

                    fputcsv($handle, [
                        $order->orders_id,
                        $order->date ? Carbon::parse($order->date)->format('Y-m-d') : '',
                        $order->payment_reference ?? '',
                        $order->user->name ?? '',
                        $order->user->email ?? '',
                        $order->recipient_name ?? '',
                        $order->recipient_phone ?? '',
                        $order->shipping_address ?? '',
                        $order->product->product_title ?? ('Produk #' . $order->product_id),
                        $order->product->category->cat_title ?? '',
                        $order->qty,
                        $unitPrice,
                        $discount,
                        $subtotal,
                        strtoupper($order->payment_method ?? 'MANUAL'),
                        strtoupper($order->payment_status ?? 'PENDING'),
                        $order->status ?? 'Pending',
                        $order->paid_at ? Carbon::parse($order->paid_at)->format('Y-m-d H:i:s') : '',
                    ]);
                }
            });

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Cache-Control' => 'no-store, no-cache',
        ]);
    }

    /**
     * Tampilan Cetak Laporan (Print / Save as PDF).
     */
    public function exportPrint(Request $request)
    {
        $query = $this->buildReportQuery($request)->orderBy('date', 'desc')->orderBy('orders_id', 'desc');
        $orders = $query->take(500)->get();

        $totalRevenue = (float) $orders->where('payment_status', 'paid')->sum('subtotal');
        if ($totalRevenue <= 0) {
            $totalRevenue = (float) $orders->where('status', 'Selesai')->sum('subtotal');
        }
        $totalOrders = $orders->count();
        $totalUnits = $orders->sum('qty');

        return view('admin.reports.print', compact(
            'orders',
            'totalRevenue',
            'totalOrders',
            'totalUnits'
        ));
    }
}
