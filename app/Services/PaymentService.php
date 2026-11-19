<?php

namespace App\Services;

use App\Models\Order;
use App\Models\PaymentTransaction;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

/**
 * Service untuk menangani alur pembayaran Greenova,
 * termasuk integrasi Midtrans Snap Sandbox, verifikasi webhook,
 * dan sinkronisasi status pembayaran dengan pemenuhan pesanan.
 */
class PaymentService
{
    public const STATUS_UNPAID = 'unpaid';
    public const STATUS_PENDING = 'pending';
    public const STATUS_PAID = 'paid';
    public const STATUS_FAILED = 'failed';
    public const STATUS_EXPIRED = 'expired';
    public const STATUS_CANCELLED = 'cancelled';
    public const STATUS_CHALLENGE = 'challenge';

    /**
     * Konfigurasi Midtrans SDK dengan kredensial sandbox / production.
     */
    public function configureMidtrans(): void
    {
        \Midtrans\Config::$serverKey = (string) config('services.midtrans.server_key');
        \Midtrans\Config::$isProduction = (bool) config('services.midtrans.is_production', false);
        \Midtrans\Config::$isSanitized = (bool) config('services.midtrans.is_sanitized', true);
        \Midtrans\Config::$is3ds = (bool) config('services.midtrans.is_3ds', true);
    }

    /**
     * Periksa apakah Server Key Midtrans sudah diatur di environment.
     */
    public function isConfigured(): bool
    {
        $key = config('services.midtrans.server_key');
        return !empty($key) && is_string($key);
    }

    /**
     * Ambil Client Key Midtrans untuk frontend Snap JS.
     */
    public function getClientKey(): ?string
    {
        return config('services.midtrans.client_key');
    }

    /**
     * URL script Midtrans Snap (Sandbox vs Production).
     */
    public function getSnapUrl(): string
    {
        return config('services.midtrans.is_production')
            ? 'https://app.midtrans.com/snap/snap.js'
            : 'https://app.sandbox.midtrans.com/snap/snap.js';
    }

    /**
     * Buat kode referensi transaksi unik untuk satu kali checkout.
     */
    public function generatePaymentReference(): string
    {
        return 'GNV-' . now()->format('Ymd') . '-' . strtoupper(Str::random(8));
    }

    /**
     * Tentukan data pembayaran awal saat checkout.
     */
    public function resolveInitialPaymentData(string $paymentMethod, ?string $paymentReference = null): array
    {
        $reference = $paymentReference ?: $this->generatePaymentReference();

        if ($paymentMethod === 'midtrans') {
            return [
                'payment_gateway' => 'midtrans',
                'payment_status' => self::STATUS_PENDING,
                'payment_reference' => $reference,
                'paid_at' => null,
            ];
        }

        return [
            'payment_gateway' => 'manual',
            'payment_status' => self::STATUS_UNPAID,
            'payment_reference' => $reference,
            'paid_at' => null,
        ];
    }

    /**
     * Filter atribut pembayaran agar hanya menyertakan kolom yang sudah ada di tabel orders.
     */
    public function filterPersistableAttributes(array $paymentData): array
    {
        $available = [];
        $columns = ['payment_gateway', 'payment_status', 'payment_reference', 'paid_at', 'order_group'];

        foreach ($columns as $column) {
            if (array_key_exists($column, $paymentData) && Schema::hasColumn('orders', $column)) {
                $available[$column] = $paymentData[$column];
            }
        }

        return $available;
    }

    /**
     * Buat atau ambil Snap Token untuk seluruh item dalam satu checkout (order_group).
     * Nominal selalu dihitung ulang dari database agar aman dari manipulasi frontend.
     */
    public function createOrGetSnapToken(string $orderGroup, ?User $user = null): array
    {
        // Cari seluruh item pesanan yang tergabung dalam grup checkout ini
        $query = Order::with('product')
            ->where(function ($q) use ($orderGroup) {
                $q->where('payment_reference', $orderGroup);
                if (Schema::hasColumn('orders', 'order_group')) {
                    $q->orWhere('order_group', $orderGroup);
                }
            });

        if ($user) {
            $query->where('user_id', $user->id);
        }

        $orders = $query->get();

        if ($orders->isEmpty()) {
            throw new \RuntimeException("Pesanan dengan referensi '{$orderGroup}' tidak ditemukan.");
        }

        $orderUser = $user ?: User::find($orders->first()->user_id);

        // Hitung total nilai transaksi dari database
        $grossAmount = 0.0;
        foreach ($orders as $order) {
            $grossAmount += (float) ($order->subtotal ?? ($order->unit_price ? $order->unit_price * $order->qty : 0));
        }
        $grossAmount = round($grossAmount, 2);

        if ($grossAmount <= 0) {
            throw new \RuntimeException("Total transaksi tidak valid.");
        }

        // Periksa apakah tabel payment_transactions tersedia
        $hasTransactionTable = Schema::hasTable('payment_transactions');
        $existingTx = null;

        if ($hasTransactionTable) {
            $existingTx = PaymentTransaction::where('order_group', $orderGroup)->first();

            // Jika token aktif sudah ada dan belum kedaluwarsa, gunakan kembali untuk mencegah duplikasi token
            if ($existingTx && !empty($existingTx->snap_token) && $existingTx->canPay()) {
                return [
                    'snap_token' => $existingTx->snap_token,
                    'redirect_url' => $existingTx->snap_redirect_url,
                    'gross_amount' => (float) $existingTx->gross_amount,
                    'order_group' => $orderGroup,
                    'client_key' => $this->getClientKey(),
                    'snap_url' => $this->getSnapUrl(),
                ];
            }
        } else {
            // Fallback cache aplikasi jika tabel payment_transactions belum ada
            $cacheKey = "snap_token_{$orderGroup}";
            $cachedToken = \Illuminate\Support\Facades\Cache::get($cacheKey);
            if (!empty($cachedToken)) {
                return [
                    'snap_token' => $cachedToken,
                    'redirect_url' => "https://app.sandbox.midtrans.com/snap/v2/vtweb/{$cachedToken}",
                    'gross_amount' => $grossAmount,
                    'order_group' => $orderGroup,
                    'client_key' => $this->getClientKey(),
                    'snap_url' => $this->getSnapUrl(),
                ];
            }
        }

        // Validasi kredensial Midtrans
        if (!$this->isConfigured()) {
            throw new \RuntimeException('Midtrans Server Key belum dikonfigurasi di file .env.');
        }

        $this->configureMidtrans();

        // Rancang rincian item untuk Midtrans (1 item per pesanan dengan subtotal final)
        $itemDetails = [];
        foreach ($orders as $order) {
            $lineTotal = (int) round((float) ($order->subtotal ?? 0));
            $itemDetails[] = [
                'id' => (string) $order->orders_id,
                'price' => $lineTotal,
                'quantity' => 1,
                'name' => Str::limit($order->product->product_title ?? ('Pesanan #' . $order->orders_id), 45),
            ];
        }

        $firstOrder = $orders->first();
        $customerDetails = [
            'first_name' => $firstOrder->recipient_name ?: ($orderUser?->name ?? 'Pelanggan Greenova'),
            'email' => $orderUser?->email ?? 'customer@greenova.id',
            'phone' => $firstOrder->recipient_phone ?: ($orderUser?->phone ?? '081234567890'),
            'shipping_address' => [
                'first_name' => $firstOrder->recipient_name ?: ($orderUser?->name ?? 'Pelanggan Greenova'),
                'phone' => $firstOrder->recipient_phone ?: ($orderUser?->phone ?? '081234567890'),
                'address' => Str::limit($firstOrder->shipping_address ?: 'Indonesia', 200),
            ],
        ];

        $params = [
            'transaction_details' => [
                'order_id' => $orderGroup,
                'gross_amount' => (int) round($grossAmount),
            ],
            'customer_details' => $customerDetails,
            'item_details' => $itemDetails,
        ];

        try {
            $snapToken = \Midtrans\Snap::getSnapToken($params);
            $redirectUrl = "https://app.sandbox.midtrans.com/snap/v2/vtweb/{$snapToken}";
        } catch (\Throwable $e) {
            Log::error("Gagal mendapatkan Midtrans Snap Token untuk {$orderGroup}: " . $e->getMessage());
            throw new \RuntimeException('Gagal menghubungkan ke Midtrans Snap: ' . $e->getMessage(), 0, $e);
        }

        // Simpan catatan transaksi pembayaran jika tabel tersedia
        if ($hasTransactionTable) {
            PaymentTransaction::updateOrCreate(
                ['order_group' => $orderGroup],
                [
                    'user_id' => $firstOrder->user_id,
                    'gross_amount' => $grossAmount,
                    'snap_token' => $snapToken,
                    'snap_redirect_url' => $redirectUrl,
                    'payment_status' => self::STATUS_PENDING,
                    'expired_at' => now()->addHours(24),
                ]
            );
        } else {
            \Illuminate\Support\Facades\Cache::put("snap_token_{$orderGroup}", $snapToken, now()->addHours(24));
        }

        return [
            'snap_token' => $snapToken,
            'redirect_url' => $redirectUrl,
            'gross_amount' => $grossAmount,
            'order_group' => $orderGroup,
            'client_key' => $this->getClientKey(),
            'snap_url' => $this->getSnapUrl(),
        ];
    }

    /**
     * Verifikasi keaslian webhook signature key resmi dari Midtrans.
     * Formula: SHA512(order_id + status_code + gross_amount + ServerKey)
     */
    public function verifySignature(string $orderId, string $statusCode, string $grossAmount, string $signatureKey): bool
    {
        $serverKey = (string) config('services.midtrans.server_key');
        if (empty($serverKey)) {
            return false;
        }

        $expected = hash('sha512', $orderId . $statusCode . $grossAmount . $serverKey);
        return hash_equals($expected, $signatureKey);
    }

    /**
     * Tangani notifikasi / webhook resmi dari Midtrans secara aman dan idempotent.
     */
    public function handleNotification(array $payload): array
    {
        $orderId = (string) ($payload['order_id'] ?? '');
        $statusCode = (string) ($payload['status_code'] ?? '');
        $grossAmount = (string) ($payload['gross_amount'] ?? '');
        $signatureKey = (string) ($payload['signature_key'] ?? '');
        $transactionStatus = strtolower(trim((string) ($payload['transaction_status'] ?? '')));
        $fraudStatus = strtolower(trim((string) ($payload['fraud_status'] ?? '')));
        $paymentType = (string) ($payload['payment_type'] ?? 'unknown');
        $transactionId = (string) ($payload['transaction_id'] ?? null);

        if (empty($orderId) || empty($statusCode) || empty($grossAmount) || empty($signatureKey)) {
            return [
                'success' => false,
                'code' => 400,
                'message' => 'Parameter notifikasi Midtrans tidak lengkap.',
            ];
        }

        // Verifikasi signature key
        if (!$this->verifySignature($orderId, $statusCode, $grossAmount, $signatureKey)) {
            Log::warning("Midtrans Webhook: Signature key tidak valid untuk order {$orderId}");
            return [
                'success' => false,
                'code' => 403,
                'message' => 'Signature key tidak valid.',
            ];
        }

        // Cari seluruh baris pesanan terkait
        $orders = Order::where(function ($q) use ($orderId) {
            $q->where('payment_reference', $orderId);
            if (Schema::hasColumn('orders', 'order_group')) {
                $q->orWhere('order_group', $orderId);
            }
        })->get();

        if ($orders->isEmpty()) {
            Log::warning("Midtrans Webhook: Pesanan dengan referensi {$orderId} tidak ditemukan.");
            return [
                'success' => false,
                'code' => 404,
                'message' => "Pesanan dengan referensi {$orderId} tidak ditemukan.",
            ];
        }

        // Verifikasi nominal transaksi tidak melenceng
        $expectedAmount = round((float) $orders->sum('subtotal'), 2);
        if (abs($expectedAmount - (float) $grossAmount) > 1.0) {
            Log::warning("Midtrans Webhook: Nominal tidak cocok untuk {$orderId}. DB: {$expectedAmount}, Midtrans: {$grossAmount}");
            return [
                'success' => false,
                'code' => 400,
                'message' => 'Nominal transaksi tidak cocok dengan catatan sistem Greenova.',
            ];
        }

        // Pemetaan status resmi Midtrans ke status pembayaran Greenova
        $newPaymentStatus = match ($transactionStatus) {
            'capture' => ($fraudStatus === 'challenge') ? self::STATUS_CHALLENGE : self::STATUS_PAID,
            'settlement' => self::STATUS_PAID,
            'pending' => self::STATUS_PENDING,
            'deny' => self::STATUS_FAILED,
            'expire' => self::STATUS_EXPIRED,
            'cancel' => self::STATUS_CANCELLED,
            default => self::STATUS_PENDING,
        };

        // Pembaruan atomik di database
        $this->applyStatusTransition(
            $orders,
            $orderId,
            $newPaymentStatus,
            $paymentType,
            $transactionId,
            $fraudStatus,
            $payload,
            ($newPaymentStatus === self::STATUS_PAID) ? now() : null
        );

        Log::info("Midtrans Webhook: Berhasil memperbarui order {$orderId} ke status {$newPaymentStatus}");

        return [
            'success' => true,
            'code' => 200,
            'message' => "Status pembayaran berhasil diperbarui menjadi {$newPaymentStatus}.",
            'payment_status' => $newPaymentStatus,
            'order_group' => $orderId,
        ];
    }

    /**
     * Terapkan transisi status pembayaran secara aman, atomik, dan idempotent.
     */
    public function applyStatusTransition(
        $orders,
        string $orderId,
        string $newPaymentStatus,
        ?string $paymentType,
        ?string $transactionId,
        ?string $fraudStatus,
        array $payload = [],
        ?\DateTimeInterface $paidAt = null
    ): void {
        DB::transaction(function () use ($orderId, $newPaymentStatus, $paymentType, $transactionId, $fraudStatus, $payload, $orders, $paidAt) {
            $hasTransactionTable = Schema::hasTable('payment_transactions');

            if ($hasTransactionTable && $orders->isNotEmpty()) {
                PaymentTransaction::updateOrCreate(
                    ['order_group' => $orderId],
                    [
                        'user_id' => $orders->first()->user_id,
                        'gross_amount' => (float) $orders->sum('subtotal'),
                        'payment_status' => $newPaymentStatus,
                        'payment_type' => $paymentType,
                        'transaction_id' => $transactionId,
                        'fraud_status' => $fraudStatus ?: null,
                        'payment_payload' => $payload,
                        'paid_at' => ($newPaymentStatus === self::STATUS_PAID) ? ($paidAt ?: now()) : null,
                    ]
                );
            }

            foreach ($orders as $order) {
                $currentPaymentStatus = strtolower(trim((string) ($order->payment_status ?? '')));
                $currentOrderStatus = strtolower(trim((string) ($order->status ?? '')));

                // Jangan menurunkan status yang sudah LUNAS (paid) menjadi pending / unpaid / failed
                if ($currentPaymentStatus === self::STATUS_PAID && $newPaymentStatus !== self::STATUS_PAID) {
                    continue;
                }

                // Skenario 1: Pembayaran BERHASIL (settlement / capture accept)
                if ($newPaymentStatus === self::STATUS_PAID) {
                    $order->payment_status = self::STATUS_PAID;
                    $order->paid_at = $paidAt ?: now();
                    // Status pemenuhan pesanan tetap di alur Admin (TIDAK otomatis 'Selesai')
                    $order->save();
                }
                // Skenario 2: Pembayaran GAGAL / KEDALUWARSA / DIBATALKAN
                elseif (in_array($newPaymentStatus, [self::STATUS_FAILED, self::STATUS_EXPIRED, self::STATUS_CANCELLED], true)) {
                    // Hanya batalkan pesanan dan kembalikan stok JIKA status sebelumnya masih Pending (idempotent)
                    if ($currentOrderStatus === 'pending') {
                        $order->payment_status = $newPaymentStatus;
                        $order->status = 'Dibatalkan';
                        $order->save();

                        // Kembalikan stok produk tepat satu kali
                        $product = Product::where('product_id', $order->product_id)->lockForUpdate()->first();
                        if ($product && (int) $order->qty > 0) {
                            app(InventoryService::class)->recordCancellation($product, (int) $order->qty, $orderId, $order->user_id);
                            $product->increment('stock', (int) $order->qty);
                        }
                    } else {
                        $order->payment_status = $newPaymentStatus;
                        $order->save();
                    }
                }
                // Skenario 3: Masih Pending / Challenge
                else {
                    $order->payment_status = $newPaymentStatus;
                    $order->save();
                }
            }
        });
    }

    /**
     * Rekonsiliasi status pembayaran langsung ke Midtrans GET Status API.
     * Digunakan jika webhook terlambat/terhambat, saat customer membuka detail pesanan,
     * atau saat customer menekan tombol "Periksa Status Pembayaran".
     */
    public function checkAndSyncStatus(string $orderGroup, ?User $user = null): array
    {
        $query = Order::where(function ($q) use ($orderGroup) {
            $q->where('payment_reference', $orderGroup);
            if (Schema::hasColumn('orders', 'order_group')) {
                $q->orWhere('order_group', $orderGroup);
            }
        });

        if ($user) {
            $query->where('user_id', $user->id);
        }

        $orders = $query->get();

        if ($orders->isEmpty()) {
            return [
                'success' => false,
                'message' => "Pesanan dengan referensi '{$orderGroup}' tidak ditemukan.",
                'is_paid' => false,
                'payment_status' => self::STATUS_PENDING,
            ];
        }

        $firstOrder = $orders->first();
        $currentPaymentStatus = strtolower(trim((string) ($firstOrder->payment_status ?? 'pending')));

        // Jika sudah tercatat paid di database, kembalikan data verifikasi
        if ($currentPaymentStatus === self::STATUS_PAID) {
            return [
                'success' => true,
                'order_group' => $orderGroup,
                'payment_status' => self::STATUS_PAID,
                'payment_label' => $this->labelForOrder($firstOrder),
                'is_paid' => true,
                'paid_at' => $firstOrder->paid_at,
                'status_changed' => false,
                'source' => 'database',
            ];
        }

        if (!$this->isConfigured()) {
            return [
                'success' => true,
                'order_group' => $orderGroup,
                'payment_status' => $currentPaymentStatus,
                'payment_label' => $this->labelForOrder($firstOrder),
                'is_paid' => false,
                'status_changed' => false,
                'source' => 'database',
            ];
        }

        $this->configureMidtrans();

        try {
            $statusObj = \Midtrans\Transaction::status($orderGroup);
        } catch (\Throwable $e) {
            // Transaksi belum terbuat di Midtrans atau koneksi offline
            return [
                'success' => true,
                'order_group' => $orderGroup,
                'payment_status' => $currentPaymentStatus,
                'payment_label' => $this->labelForOrder($firstOrder),
                'is_paid' => false,
                'status_changed' => false,
                'source' => 'midtrans_pending_or_unreachable',
            ];
        }

        $rawStatus = (array) $statusObj;
        $transactionStatus = strtolower(trim((string) ($rawStatus['transaction_status'] ?? '')));
        $fraudStatus = strtolower(trim((string) ($rawStatus['fraud_status'] ?? '')));
        $paymentType = (string) ($rawStatus['payment_type'] ?? 'midtrans');
        $transactionId = (string) ($rawStatus['transaction_id'] ?? null);

        $newPaymentStatus = match ($transactionStatus) {
            'capture' => ($fraudStatus === 'challenge') ? self::STATUS_CHALLENGE : self::STATUS_PAID,
            'settlement' => self::STATUS_PAID,
            'pending' => self::STATUS_PENDING,
            'deny' => self::STATUS_FAILED,
            'expire' => self::STATUS_EXPIRED,
            'cancel' => self::STATUS_CANCELLED,
            default => $currentPaymentStatus,
        };

        $paidAt = null;
        if ($newPaymentStatus === self::STATUS_PAID) {
            $settleTime = $rawStatus['settlement_time'] ?? ($rawStatus['transaction_time'] ?? null);
            $paidAt = $settleTime ? \Carbon\Carbon::parse($settleTime) : now();
        }

        $statusChanged = ($newPaymentStatus !== $currentPaymentStatus);

        if ($statusChanged || $newPaymentStatus === self::STATUS_PAID) {
            $this->applyStatusTransition(
                $orders,
                $orderGroup,
                $newPaymentStatus,
                $paymentType,
                $transactionId,
                $fraudStatus,
                $rawStatus,
                $paidAt
            );
            $firstOrder->refresh();
        }

        return [
            'success' => true,
            'order_group' => $orderGroup,
            'payment_status' => $newPaymentStatus,
            'payment_label' => $this->labelForOrder($firstOrder),
            'is_paid' => ($newPaymentStatus === self::STATUS_PAID),
            'paid_at' => $firstOrder->paid_at,
            'payment_type' => $paymentType,
            'transaction_id' => $transactionId,
            'status_changed' => $statusChanged,
            'source' => 'midtrans_api',
        ];
    }

    /**
     * Label status pembayaran yang ramah pengguna.
     */
    public function labelForOrder(Order $order): string
    {
        $orderStatus = strtolower(trim((string) ($order->status ?? 'pending')));

        if (Schema::hasColumn('orders', 'payment_status') && !empty($order->payment_status)) {
            return match (strtolower(trim($order->payment_status))) {
                self::STATUS_PAID => 'Lunas',
                self::STATUS_PENDING => 'Menunggu Pembayaran',
                self::STATUS_FAILED => 'Pembayaran Gagal',
                self::STATUS_EXPIRED => 'Kedaluwarsa',
                self::STATUS_CANCELLED => 'Dibatalkan',
                self::STATUS_CHALLENGE => 'Menunggu Verifikasi (Challenge)',
                default => 'Belum Dibayar',
            };
        }

        if ($orderStatus === 'dibatalkan') {
            return 'Dibatalkan';
        }

        if ($orderStatus === 'selesai') {
            return 'Lunas';
        }

        return 'Menunggu Pembayaran';
    }
}
