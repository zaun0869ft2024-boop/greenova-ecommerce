<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\PaymentTransaction;
use App\Services\PaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class MidtransWebhookController extends Controller
{
    /**
     * Endpoint Webhook / HTTP Notification dari Midtrans.
     * Tidak memerlukan login customer, diverifikasi menggunakan signature key resmi.
     */
    public function handle(Request $request, PaymentService $paymentService): JsonResponse
    {
        Log::info('Midtrans Webhook: Menerima notifikasi pembayaran', [
            'order_id' => $request->input('order_id'),
            'transaction_status' => $request->input('transaction_status'),
            'gross_amount' => $request->input('gross_amount'),
        ]);

        $result = $paymentService->handleNotification($request->all());

        return response()->json($result, $result['code'] ?? 200);
    }

    /**
     * Endpoint untuk mendapatkan Snap Token dari frontend (AJAX / tombol "Bayar Sekarang").
     * Hanya dapat diakses oleh customer pemilik pesanan (dilindungi auth).
     */
    public function getSnapToken(Request $request, string $orderGroup, PaymentService $paymentService): JsonResponse
    {
        if (!auth()->check()) {
            return response()->json([
                'success' => false,
                'message' => 'Silakan masuk ke akun Anda terlebih dahulu.',
            ], 401);
        }

        $orderExistsAny = Order::where(function ($q) use ($orderGroup) {
            $q->where('payment_reference', $orderGroup);
            if (Schema::hasColumn('orders', 'order_group')) {
                $q->orWhere('order_group', $orderGroup);
            }
        })->exists();

        if ($orderExistsAny) {
            $isOwner = Order::where(function ($q) use ($orderGroup) {
                $q->where('payment_reference', $orderGroup);
                if (Schema::hasColumn('orders', 'order_group')) {
                    $q->orWhere('order_group', $orderGroup);
                }
            })->where('user_id', auth()->id())->exists();

            if (!$isOwner) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda tidak memiliki hak akses untuk transaksi ini.',
                ], 403);
            }
        }

        try {
            $tokenData = $paymentService->createOrGetSnapToken($orderGroup, auth()->user());

            return response()->json(array_merge([
                'success' => true,
                'data' => $tokenData,
            ], $tokenData));
        } catch (\Throwable $e) {
            Log::error("Error getSnapToken untuk {$orderGroup}: " . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Endpoint untuk memeriksa status pembayaran pesanan secara berkala setelah popup ditutup
     * atau saat tombol "Periksa Status Pembayaran" ditekan oleh customer.
     * Menggunakan Midtrans GET Status API untuk sinkronisasi seketika jika webhook belum sampai.
     */
    public function checkStatus(Request $request, string $orderGroup, PaymentService $paymentService): JsonResponse
    {
        if (!auth()->check()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $result = $paymentService->checkAndSyncStatus($orderGroup, auth()->user());

        $code = ($result['success'] ?? false) ? 200 : 404;
        return response()->json($result, $code);
    }
}
