<?php

namespace App\Http\Controllers;

use App\Models\CartItem;
use App\Models\Order;
use App\Models\Product;
use App\Models\Voucher;
use App\Models\VoucherUsage;
use App\Services\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class CartController extends Controller
{
    private function getCart(): array
    {
        if (auth()->check()) {
            return CartItem::with(['product.discounts'])
                ->where('user_id', auth()->id())
                ->get()
                ->filter(fn ($item) => $item->product !== null)
                ->mapWithKeys(function ($item) {
                    $product = $item->product;

                    return [
                        (string) $item->product_id => [
                            'name' => $product->product_title,
                            'price' => $product->effectivePrice(),
                            'base_price' => (int) $product->product_price,
                            'image' => $product->product_image,
                            'quantity' => (int) $item->quantity,
                            'stock' => (int) $product->stock,
                        ],
                    ];
                })
                ->all();
        }

        $cart = session()->get('cart', []);

        foreach ($cart as $id => &$item) {
            $product = Product::with('discounts')->find($id);

            if (!$product) {
                unset($cart[$id]);
                continue;
            }

            $item['name'] = $product->product_title;
            $item['price'] = $product->effectivePrice();
            $item['base_price'] = (int) $product->product_price;
            $item['image'] = $product->product_image;
            $item['stock'] = (int) $product->stock;
        }
        unset($item);

        session()->put('cart', $cart);

        return $cart;
    }

    public function addToCart(Request $request, $id)
    {
        $product = Product::findOrFail($id);

        if ((int) $product->stock < 1) {
            return back()->with('error', 'Stok produk habis.');
        }

        $addQuantity = max(1, (int) $request->input('quantity', 1));

        if (auth()->check()) {
            $item = CartItem::firstOrNew([
                'user_id' => auth()->id(),
                'product_id' => $product->product_id,
            ]);

            $currentQuantity = $item->exists
                ? (int) $item->quantity
                : 0;

            if ($currentQuantity + $addQuantity > (int) $product->stock) {
                return back()->with(
                    'error',
                    "Stok {$product->product_title} hanya tersedia {$product->stock}."
                );
            }

            $item->quantity = $currentQuantity + $addQuantity;
            $item->save();
        } else {
            $cart = session()->get('cart', []);
            $currentQuantity = isset($cart[$id])
                ? (int) $cart[$id]['quantity']
                : 0;

            if ($currentQuantity + $addQuantity > (int) $product->stock) {
                return back()->with(
                    'error',
                    "Stok {$product->product_title} hanya tersedia {$product->stock}."
                );
            }

            $cart[$id] = [
                'name' => $product->product_title,
                'price' => $product->effectivePrice(),
                'base_price' => (int) $product->product_price,
                'image' => $product->product_image,
                'quantity' => $currentQuantity + $addQuantity,
                'stock' => (int) $product->stock,
            ];

            session()->put('cart', $cart);
        }

        return back()->with(
            'success',
            'Produk berhasil ditambahkan ke keranjang!'
        );
    }

    public function buyNow(Request $request, $id)
    {
        $product = Product::findOrFail($id);

        if ((int) $product->stock < 1) {
            return back()->with('error', 'Stok produk habis.');
        }

        $buyQuantity = max(1, (int) $request->input('quantity', 1));
        if ($buyQuantity > (int) $product->stock) {
            return back()->with(
                'error',
                "Stok {$product->product_title} hanya tersedia {$product->stock}."
            );
        }

        session()->put('checkout_items', [$id]);
        session()->put('checkout_mode', 'direct');

        session()->put('direct_checkout_product', [
            'name' => $product->product_title,
            'price' => $product->effectivePrice(),
            'base_price' => (int) $product->product_price,
            'image' => $product->product_image,
            'quantity' => $buyQuantity,
            'stock' => (int) $product->stock,
        ]);

        return redirect()->route('checkout');
    }

    public function cart()
    {
        $cart = $this->getCart();

        return view('cart.index', compact('cart'));
    }

    public function prepareCheckout(Request $request)
    {
        $request->validate([
            'selected_products' => 'required|array|min:1',
            'selected_products.*' => 'required|integer',
        ], [
            'selected_products.required' =>
                'Pilih minimal satu produk untuk checkout.',
            'selected_products.min' =>
                'Pilih minimal satu produk untuk checkout.',
        ]);

        if (!auth()->check()) {
            return redirect()->route('login')
                ->with('error', 'Login terlebih dahulu untuk checkout.');
        }

        $cart = $this->getCart();

        $selected = array_unique(array_map(
            'strval',
            $request->input('selected_products', [])
        ));

        foreach ($selected as $id) {
            if (!isset($cart[$id])) {
                return redirect()->route('cart')
                    ->with('error', 'Pilihan produk tidak valid. Silakan pilih ulang.');
            }

            $item = $cart[$id];

            if (
                (int) $item['quantity'] < 1 ||
                (int) $item['quantity'] > (int) $item['stock']
            ) {
                return redirect()->route('cart')
                    ->with(
                        'error',
                        "Jumlah {$item['name']} melebihi stok yang tersedia ({$item['stock']})."
                    );
            }
        }

        session()->put('checkout_items', $selected);
        session()->put('checkout_mode', 'cart');
        session()->forget('direct_checkout_product');

        return redirect()->route('checkout');
    }

    private function resolveCheckoutCart(): array
    {
        $mode = session()->get('checkout_mode');
        $selected = session()->get('checkout_items', []);
        $cart = $this->getCart();
        $checkoutCart = [];

        if ($mode === 'direct') {
            $productId = $selected[0] ?? null;
            $product = $productId ? Product::with('discounts')->find($productId) : null;

            if (!$product) {
                return ['error_route' => 'products', 'error' => 'Produk tidak ditemukan.', 'cart' => []];
            }

            if ((int) $product->stock < 1) {
                return ['error_route' => 'products', 'error' => 'Stok produk habis.', 'cart' => []];
            }

            $directInfo = session()->get('direct_checkout_product', []);
            $directQty = max(1, (int) ($directInfo['quantity'] ?? 1));
            if ($directQty > (int) $product->stock) {
                $directQty = (int) $product->stock;
            }

            $checkoutCart[(string) $productId] = [
                'name' => $product->product_title,
                'price' => $product->effectivePrice(),
                'base_price' => (int) $product->product_price,
                'image' => $product->product_image,
                'quantity' => $directQty,
                'stock' => (int) $product->stock,
            ];
        } elseif ($mode === 'cart') {
            foreach ($selected as $id) {
                if (isset($cart[$id])) {
                    $checkoutCart[$id] = $cart[$id];
                }
            }

            if (empty($checkoutCart)) {
                return ['error_route' => 'cart', 'error' => 'Pilih produk yang ingin dibeli terlebih dahulu.', 'cart' => []];
            }

            foreach ($checkoutCart as $item) {
                if ((int) $item['quantity'] < 1 || (int) $item['quantity'] > (int) $item['stock']) {
                    return [
                        'error_route' => 'cart',
                        'error' => "Stok {$item['name']} tidak mencukupi. Stok tersedia: {$item['stock']}.",
                        'cart' => [],
                    ];
                }
            }
        } else {
            return ['error_route' => 'cart', 'error' => 'Silakan pilih produk untuk checkout.', 'cart' => []];
        }

        return ['error_route' => null, 'error' => null, 'cart' => $checkoutCart];
    }

    public function checkout()
    {
        $resolved = $this->resolveCheckoutCart();
        if ($resolved['error_route'] !== null) {
            return redirect()->route($resolved['error_route'])
                ->with('error', $resolved['error']);
        }

        $checkoutCart = $resolved['cart'];
        $subtotalBeforeVoucher = 0.0;
        foreach ($checkoutCart as $item) {
            $subtotalBeforeVoucher += (float) $item['price'] * (int) $item['quantity'];
        }

        $appliedVoucher = null;
        $voucherDiscount = 0.0;
        $voucherCode = session()->get('checkout_voucher');

        if ($voucherCode) {
            $voucher = Voucher::where('code', $voucherCode)->first();
            if ($voucher) {
                $check = $voucher->isValid($subtotalBeforeVoucher);
                if ($check['valid']) {
                    $appliedVoucher = $voucher;
                    $voucherDiscount = round($voucher->calculateDiscount($subtotalBeforeVoucher), 2);
                } else {
                    session()->forget('checkout_voucher');
                    session()->flash('voucher_error', $check['message']);
                }
            } else {
                session()->forget('checkout_voucher');
            }
        }

        $grandTotal = max(0.0, $subtotalBeforeVoucher - $voucherDiscount);

        return view('checkout.index', [
            'cart' => $checkoutCart,
            'subtotalBeforeVoucher' => $subtotalBeforeVoucher,
            'appliedVoucher' => $appliedVoucher,
            'voucherDiscount' => $voucherDiscount,
            'grandTotal' => $grandTotal,
        ]);
    }

    public function applyVoucher(Request $request)
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        $request->validate([
            'voucher_code' => 'required|string|max:50',
        ], [
            'voucher_code.required' => 'Masukkan kode voucher terlebih dahulu.',
        ]);

        $resolved = $this->resolveCheckoutCart();
        if ($resolved['error_route'] !== null) {
            return redirect()->route($resolved['error_route'])
                ->with('error', $resolved['error']);
        }

        $subtotalBeforeVoucher = 0.0;
        foreach ($resolved['cart'] as $item) {
            $subtotalBeforeVoucher += (float) $item['price'] * (int) $item['quantity'];
        }

        $code = strtoupper(trim((string) $request->input('voucher_code')));
        $voucher = Voucher::whereRaw('UPPER(code) = ?', [$code])->first();

        if (!$voucher) {
            return redirect()->route('checkout')
                ->with('voucher_error', "Kode voucher '{$code}' tidak ditemukan.");
        }

        $check = $voucher->isValid($subtotalBeforeVoucher);
        if (!$check['valid']) {
            return redirect()->route('checkout')
                ->with('voucher_error', $check['message']);
        }

        session()->put('checkout_voucher', $voucher->code);

        return redirect()->route('checkout')
            ->with('voucher_success', "Voucher {$voucher->code} berhasil diterapkan.");
    }

    public function removeVoucher()
    {
        session()->forget('checkout_voucher');

        return redirect()->route('checkout')
            ->with('voucher_success', 'Voucher dihapus dari checkout.');
    }

 
public function processCheckout(Request $request)
{
    if (!auth()->check()) {
        return redirect()->route('login');
    }

    // Validasi data penerima dan metode pembayaran (Midtrans Snap).
    $validated = $request->validate([
        'recipient_name' => 'required|string|max:100',
        'recipient_phone' => 'required|string|max:20',
        'shipping_address' => 'required|string|max:1000',
        'payment_method' => 'required|in:midtrans,bank_transfer,ewallet',
    ], [
        'recipient_name.required' => 'Nama penerima wajib diisi.',
        'recipient_phone.required' => 'Nomor telepon wajib diisi.',
        'shipping_address.required' => 'Alamat pengiriman wajib diisi.',
        'payment_method.required' => 'Pilih metode pembayaran.',
        'payment_method.in' => 'Metode pembayaran tidak valid.',
    ]);

    $mode = session()->get('checkout_mode');
    $selected = session()->get('checkout_items', []);
    $voucherCode = session()->get('checkout_voucher');

    if (empty($selected) || !in_array($mode, ['direct', 'cart'], true)) {
        if ($request->expectsJson()) {
            return response()->json(['success' => false, 'message' => 'Pilih produk yang ingin dibeli terlebih dahulu.'], 400);
        }
        return redirect()->route('cart')
            ->with('error', 'Pilih produk yang ingin dibeli terlebih dahulu.');
    }

    $checkoutReference = null;
    $firstCreatedOrderId = null;
    $finalTotalAmount = 0.0;

    try {
        DB::transaction(function () use ($mode, $selected, $validated, $voucherCode, &$checkoutReference, &$firstCreatedOrderId, &$finalTotalAmount) {
            $itemsToBuy = [];

            if ($mode === 'direct') {
                $directInfo = session()->get('direct_checkout_product', []);
                $itemsToBuy[(int) $selected[0]] = max(1, (int) ($directInfo['quantity'] ?? 1));
            } else {
                foreach ($selected as $id) {
                    $cartItem = CartItem::where('user_id', auth()->id())
                        ->where('product_id', (int) $id)
                        ->first();

                    if (!$cartItem) {
                        throw ValidationException::withMessages([
                            'checkout' => 'Produk tidak ditemukan di keranjang.',
                        ]);
                    }

                    $itemsToBuy[(int) $id] = (int) $cartItem->quantity;
                }
            }

            $preparedLines = [];
            $checkoutSubtotal = 0.0;

            foreach ($itemsToBuy as $productId => $quantity) {
                $product = Product::with('discounts')
                    ->where('product_id', $productId)
                    ->lockForUpdate()
                    ->first();

                if (!$product) {
                    throw ValidationException::withMessages([
                        'checkout' => 'Produk sudah tidak tersedia.',
                    ]);
                }

                if ($quantity < 1 || $quantity > (int) $product->stock) {
                    throw ValidationException::withMessages([
                        'checkout' =>
                            "Stok {$product->product_title} tidak mencukupi. Stok tersedia: {$product->stock}.",
                    ]);
                }

                $baseUnitPrice = (float) $product->product_price;
                $effectiveUnitPrice = (float) $product->effectivePrice();
                $discountAmount = max(0.0, ($baseUnitPrice - $effectiveUnitPrice) * $quantity);
                $lineNetBeforeVoucher = $effectiveUnitPrice * $quantity;
                $checkoutSubtotal += $lineNetBeforeVoucher;

                $preparedLines[] = [
                    'product' => $product,
                    'quantity' => $quantity,
                    'unit_price' => $baseUnitPrice,
                    'discount_amount' => $discountAmount,
                    'net_before_voucher' => $lineNetBeforeVoucher,
                ];
            }

            // Validasi voucher di dalam transaksi (lockForUpdate) agar tidak melebihi kuota.
            $appliedVoucherCode = null;
            $remainingVoucherDiscount = 0.0;

            if ($voucherCode) {
                $voucher = Voucher::where('code', $voucherCode)
                    ->lockForUpdate()
                    ->first();

                if (!$voucher) {
                    session()->forget('checkout_voucher');
                    throw ValidationException::withMessages([
                        'checkout' => 'Voucher tidak lagi tersedia.',
                    ]);
                }

                $check = $voucher->isValid($checkoutSubtotal);
                if (!$check['valid']) {
                    session()->forget('checkout_voucher');
                    throw ValidationException::withMessages([
                        'checkout' => $check['message'],
                    ]);
                }

                $appliedVoucherCode = $voucher->code;
                $remainingVoucherDiscount = round($voucher->calculateDiscount($checkoutSubtotal), 2);

                VoucherUsage::create([
                    'voucher_id' => $voucher->id,
                    'user_id' => auth()->id(),
                    'order_group' => (string) Str::uuid(),
                    'used_at' => now(),
                ]);

                $voucher->increment('used_count');
            }

            $paymentService = app(PaymentService::class);
            $checkoutReference = $paymentService->generatePaymentReference();
            $initialPayment = $paymentService->filterPersistableAttributes(
                $paymentService->resolveInitialPaymentData($validated['payment_method'], $checkoutReference)
            );

            // Tambahkan order_group jika kolom tersedia
            if (Schema::hasColumn('orders', 'order_group')) {
                $initialPayment['order_group'] = $checkoutReference;
            }

            foreach ($preparedLines as $line) {
                $lineVoucherDiscount = min($line['net_before_voucher'], $remainingVoucherDiscount);
                $remainingVoucherDiscount = round(max(0.0, $remainingVoucherDiscount - $lineVoucherDiscount), 2);
                $finalSubtotal = round(max(0.0, $line['net_before_voucher'] - $lineVoucherDiscount), 2);
                $finalTotalAmount += $finalSubtotal;

                $createdOrder = Order::create(array_merge([
                    'user_id' => auth()->id(),
                    'product_id' => $line['product']->product_id,
                    'qty' => $line['quantity'],
                    'unit_price' => $line['unit_price'],
                    'discount_amount' => $line['discount_amount'],
                    'voucher_code' => $appliedVoucherCode,
                    'voucher_discount' => $lineVoucherDiscount,
                    'subtotal' => $finalSubtotal,
                    'status' => 'Pending',
                    'date' => now()->toDateString(),
                    'recipient_name' => $validated['recipient_name'],
                    'recipient_phone' => $validated['recipient_phone'],
                    'shipping_address' => $validated['shipping_address'],
                    'payment_method' => $validated['payment_method'],
                ], $initialPayment));

                if (!$firstCreatedOrderId) {
                    $firstCreatedOrderId = $createdOrder->orders_id;
                }

                $inventoryService = app(\App\Services\InventoryService::class);
                $inventoryService->recordPurchase($line['product'], $line['quantity'], $checkoutReference, auth()->id());
                $line['product']->decrement('stock', $line['quantity']);
            }

            // Buat catatan di payment_transactions jika tabel sudah tersedia
            if (Schema::hasTable('payment_transactions')) {
                \App\Models\PaymentTransaction::create([
                    'order_group' => $checkoutReference,
                    'user_id' => auth()->id(),
                    'gross_amount' => $finalTotalAmount,
                    'payment_status' => ($validated['payment_method'] === 'midtrans')
                        ? PaymentService::STATUS_PENDING
                        : PaymentService::STATUS_UNPAID,
                    'payment_type' => $validated['payment_method'],
                    'expired_at' => now()->addHours(24),
                ]);
            }

            // Hapus hanya produk keranjang yang berhasil dibeli.
            if ($mode === 'cart') {
                CartItem::where('user_id', auth()->id())
                    ->whereIn('product_id', array_keys($itemsToBuy))
                    ->delete();
            }
        });
    } catch (ValidationException $e) {
        if ($request->expectsJson()) {
            return response()->json([
                'success' => false,
                'message' => $e->errors()['checkout'][0] ?? 'Stok produk tidak mencukupi.',
            ], 422);
        }
        return redirect()->route('cart')
            ->with(
                'error',
                $e->errors()['checkout'][0] ?? 'Stok produk tidak mencukupi.'
            );
    } catch (\Throwable $e) {
        report($e);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan pesanan: ' . $e->getMessage(),
            ], 500);
        }

        return redirect()->route('checkout')
            ->withInput()
            ->with('error', 'Gagal menyimpan pesanan: ' . $e->getMessage());
    }

    session()->forget([
        'checkout_items',
        'checkout_mode',
        'direct_checkout_product',
        'checkout_voucher',
    ]);

    // Jika metode pembayaran Midtrans, mintakan Snap Token secara terpadu
    $snapToken = null;
    $redirectUrl = null;
    $paymentService = app(PaymentService::class);

    if ($validated['payment_method'] === 'midtrans' && $checkoutReference) {
        try {
            $snapData = $paymentService->createOrGetSnapToken($checkoutReference, auth()->user());
            $snapToken = $snapData['snap_token'] ?? null;
            $redirectUrl = $snapData['redirect_url'] ?? null;
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning("Checkout Snap Token notice: " . $e->getMessage());
        }
    }

    $orderSuccessUrl = route('order.success') . '?ref=' . $checkoutReference;
    $orderShowUrl = $firstCreatedOrderId
        ? route('orders.show', $firstCreatedOrderId) . '?ref=' . $checkoutReference
        : route('orders');

    // Jika request melalui AJAX / JSON dari frontend Snap
    if ($request->expectsJson()) {
        return response()->json([
            'success' => true,
            'payment_method' => $validated['payment_method'],
            'order_group' => $checkoutReference,
            'snap_token' => $snapToken,
            'snap_redirect_url' => $redirectUrl,
            'order_show_url' => $orderShowUrl,
            'order_success_url' => $orderSuccessUrl,
            'message' => 'Pesanan berhasil dibuat.',
        ]);
    }

    if ($snapToken) {
        session()->flash('snap_token', $snapToken);
        session()->flash('order_group', $checkoutReference);
    }

    return redirect($orderSuccessUrl);
}



public function orders(Request $request)
{
    // Status yang diperbolehkan
    $allowedStatuses = [
        'pending',
        'diproses',
        'dikirim',
        'selesai',
        'dibatalkan'
    ];

    // Ambil filter dari URL
    $status = strtolower(
        trim($request->query('status', 'semua'))
    );

    if (!in_array($status, array_merge(['semua'], $allowedStatuses))) {
        $status = 'semua';
    }

    // Statistik seluruh pesanan pengguna
    $statistics = [
        'total' => Order::where('user_id', auth()->id())->count(),

        'pending' => Order::where('user_id', auth()->id())
            ->where('status', 'Pending')
            ->count(),

        'diproses' => Order::where('user_id', auth()->id())
            ->where('status', 'Diproses')
            ->count(),

        'dikirim' => Order::where('user_id', auth()->id())
            ->where('status', 'Dikirim')
            ->count(),

        'selesai' => Order::where('user_id', auth()->id())
            ->where('status', 'Selesai')
            ->count(),

        'dibatalkan' => Order::where('user_id', auth()->id())
            ->where('status', 'Dibatalkan')
            ->count(),
    ];

    // Daftar pesanan yang dapat difilter
    $query = Order::with(['product', 'review'])
        ->where('user_id', auth()->id());

    if ($status !== 'semua') {
        $query->whereRaw('LOWER(status) = ?', [$status]);
    }

    $orders = $query
        ->orderBy('date', 'desc')
        ->orderBy('orders_id', 'desc')
        ->get();

    return view('order.index', compact(
        'orders',
        'statistics',
        'status'
    ));
}

    public function showOrder($id)
    {
        $order = Order::with(['product', 'review'])
            ->where('user_id', auth()->id())
            ->where('orders_id', $id)
            ->firstOrFail();

        $groupedOrders = collect();
        if (!empty($order->payment_reference)) {
            $groupedOrders = Order::with(['product', 'review'])
                ->where('user_id', auth()->id())
                ->where('payment_reference', $order->payment_reference)
                ->get();
        }

        // Jika metode pembayaran Midtrans dan status masih pending, lakukan verifikasi via Midtrans GET Status API
        if (in_array(strtolower((string)$order->payment_method), ['midtrans']) && strtolower(trim((string)$order->payment_status)) === 'pending' && !empty($order->payment_reference)) {
            try {
                $paymentService = app(PaymentService::class);
                $paymentService->checkAndSyncStatus($order->payment_reference, auth()->user());
                $order->refresh();
                if (!empty($order->payment_reference)) {
                    $groupedOrders = Order::with(['product', 'review'])
                        ->where('user_id', auth()->id())
                        ->where('payment_reference', $order->payment_reference)
                        ->get();
                }
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning("Show order status sync notice: " . $e->getMessage());
            }
        }

        $paymentTransaction = null;
        if (Schema::hasTable('payment_transactions') && !empty($order->payment_reference)) {
            $paymentTransaction = \App\Models\PaymentTransaction::where('order_group', $order->payment_reference)->first();
        }

        $snapToken = $paymentTransaction?->snap_token;
        if (empty($snapToken) && !empty($order->payment_reference) && in_array(strtolower((string)$order->payment_method), ['midtrans']) && strtolower(trim((string)$order->payment_status)) === 'pending') {
            try {
                $paymentService = app(PaymentService::class);
                $snapData = $paymentService->createOrGetSnapToken($order->payment_reference, auth()->user());
                $snapToken = $snapData['snap_token'] ?? null;
            } catch (\Throwable $e) {
                // Token dapat diambil via AJAX saat tombol ditekan
            }
        }

        return view('order.show', compact('order', 'groupedOrders', 'paymentTransaction', 'snapToken'));
    }

    public function cancelOrder($id)
    {
        return DB::transaction(function () use ($id) {

            // Cari pesanan milik pengguna yang login
            $order = Order::where('user_id', auth()->id())
                ->where('orders_id', $id)
                ->lockForUpdate()
                ->firstOrFail();

            // Hanya pesanan Pending yang boleh dibatalkan
            if (strtolower(trim($order->status ?? '')) !== 'pending') {
                return redirect()
                    ->route('orders.show', $order->orders_id)
                    ->with('error', 'Pesanan tidak dapat dibatalkan karena sudah diproses atau selesai.');
            }

            // Pesanan yang sudah lunas tidak boleh dibatalkan mandiri tanpa koordinasi admin
            if (strtolower(trim($order->payment_status ?? '')) === 'paid') {
                return redirect()
                    ->route('orders.show', $order->orders_id)
                    ->with('error', 'Pesanan yang sudah dibayar tidak dapat dibatalkan mandiri. Silakan hubungi admin Greenova.');
            }

            // Ambil produk untuk mengembalikan stok
            $product = Product::where('product_id', $order->product_id)
                ->lockForUpdate()
                ->first();

            if (!$product) {
                return redirect()
                    ->route('orders.show', $order->orders_id)
                    ->with('error', 'Produk tidak ditemukan. Pembatalan gagal.');
            }

            if ((int) $order->qty < 1) {
                return redirect()
                    ->route('orders.show', $order->orders_id)
                    ->with('error', 'Jumlah produk pada pesanan tidak valid.');
            }

            // Kembalikan stok produk
            $inventoryService = app(\App\Services\InventoryService::class);
            $inventoryService->recordCancellation($product, (int) $order->qty, $order->payment_reference ?: ('#' . $order->orders_id), auth()->id());
            $product->increment('stock', (int) $order->qty);

            // Ubah status pesanan dan status pembayaran
            $order->status = 'Dibatalkan';
            $order->payment_status = 'cancelled';
            $order->save();

            if (Schema::hasTable('payment_transactions') && !empty($order->payment_reference)) {
                \App\Models\PaymentTransaction::where('order_group', $order->payment_reference)
                    ->update(['payment_status' => 'cancelled']);
            }

            return redirect()
                ->route('orders.show', $order->orders_id)
                ->with('success', 'Pesanan berhasil dibatalkan.');
        });
    }

    public function remove($id)
    {
        if (auth()->check()) {
            CartItem::where('user_id', auth()->id())
                ->where('product_id', $id)
                ->delete();
        } else {
            $cart = session()->get('cart', []);
            unset($cart[$id]);
            session()->put('cart', $cart);
        }

        return redirect()->route('cart')
            ->with('success', 'Produk dihapus dari keranjang.');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'quantity' => 'required|integer|min:1',
        ]);

        $quantity = (int) $request->input('quantity');
        $product = Product::find($id);

        if (!$product) {
            return redirect()->route('cart')
                ->with('error', 'Produk tidak ditemukan.');
        }

        if ($quantity > (int) $product->stock) {
            return redirect()->route('cart')
                ->with(
                    'error',
                    "Stok {$product->product_title} hanya tersedia {$product->stock}."
                );
        }

        if (auth()->check()) {
            $item = CartItem::where('user_id', auth()->id())
                ->where('product_id', $id)
                ->first();

            if (!$item) {
                return redirect()->route('cart')
                    ->with('error', 'Produk tidak ditemukan di keranjang.');
            }

            $item->update(['quantity' => $quantity]);
        } else {
            $cart = session()->get('cart', []);

            if (!isset($cart[$id])) {
                return redirect()->route('cart')
                    ->with('error', 'Produk tidak ditemukan di keranjang.');
            }

            $cart[$id]['quantity'] = $quantity;
            $cart[$id]['stock'] = (int) $product->stock;

            session()->put('cart', $cart);
        }

        return redirect()->route('cart')
            ->with('success', 'Jumlah produk berhasil diperbarui.');
    }
}
