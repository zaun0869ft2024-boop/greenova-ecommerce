@extends('layouts.app')

@section('title', 'Pesanan Saya — Greenova')

@section('content')
@php
    $paymentService = app(\App\Services\PaymentService::class);
@endphp

<div class="py-4 py-lg-5">
    <div class="container">

        @if(session('success'))
            <div class="alert alert-success border-0 rounded-3 shadow-sm mb-4" role="status">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger border-0 rounded-3 shadow-sm mb-4" role="alert">
                {{ session('error') }}
            </div>
        @endif

        <div class="mb-4">
            <span class="gn-badge gn-badge-green mb-2">Greenova Orders</span>
            <h1 class="fw-bold mb-1" style="font-size: clamp(1.6rem, 2.8vw, 2.1rem); color: var(--gn-primary);">
                Pesanan Saya
            </h1>
            <p class="mb-0 text-muted small">Pantau status transaksi, pembayaran, dan berikan ulasan untuk pesanan yang telah selesai.</p>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-6 col-md-3">
                <div class="bg-white rounded-4 border p-3 p-md-4 h-100" style="border-color: var(--gn-border-warm) !important;">
                    <div class="small text-muted fw-semibold mb-1">Total Pesanan</div>
                    <div class="fw-bold" style="font-size: 24px; color: var(--gn-primary);">{{ $statistics['total'] }}</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="bg-white rounded-4 border p-3 p-md-4 h-100" style="border-color: var(--gn-border-warm) !important;">
                    <div class="small text-muted fw-semibold mb-1">Pending</div>
                    <div class="fw-bold" style="font-size: 24px; color: var(--gn-charcoal);">{{ $statistics['pending'] }}</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="bg-white rounded-4 border p-3 p-md-4 h-100" style="border-color: var(--gn-border-warm) !important;">
                    <div class="small text-muted fw-semibold mb-1">Diproses</div>
                    <div class="fw-bold" style="font-size: 24px; color: var(--gn-warning);">{{ $statistics['diproses'] }}</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="bg-white rounded-4 border p-3 p-md-4 h-100" style="border-color: var(--gn-border-warm) !important;">
                    <div class="small text-muted fw-semibold mb-1">Selesai</div>
                    <div class="fw-bold" style="font-size: 24px; color: var(--gn-accent);">{{ $statistics['selesai'] }}</div>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-4 border p-3 p-md-4 mb-4" style="border-color: var(--gn-border-warm) !important;">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
                <h2 class="h6 fw-bold mb-0" style="color: var(--gn-primary);">Riwayat Transaksi</h2>
                <span class="gn-badge gn-badge-neutral">{{ $orders->count() }} Pesanan</span>
            </div>

            <div class="d-flex flex-wrap gap-2">
                @foreach(['semua' => 'Semua', 'pending' => 'Pending', 'diproses' => 'Diproses', 'dikirim' => 'Dikirim', 'selesai' => 'Selesai', 'dibatalkan' => 'Dibatalkan'] as $key => $label)
                    <a href="{{ $key === 'semua' ? route('orders') : route('orders', ['status' => $key]) }}"
                       class="gn-btn gn-btn-sm {{ $status === $key ? 'gn-btn-primary' : 'gn-btn-outline' }}">
                        {{ $label }}
                    </a>
                @endforeach
            </div>
        </div>

        @if($orders->count() > 0)
            <div class="d-flex flex-column gap-3">
                @foreach($orders as $order)
                    @php
                        $orderStatus = strtolower(trim($order->status ?? 'pending'));
                        $badgeClass = match ($orderStatus) {
                            'selesai' => 'gn-badge-green',
                            'dikirim' => 'gn-badge-blue',
                            'diproses' => 'gn-badge-amber',
                            'dibatalkan' => 'gn-badge-red',
                            default => 'gn-badge-neutral',
                        };
                        $isSelesai = ($orderStatus === 'selesai');
                        $hasReview = (bool) $order->review;
                        $orderTotal = $order->subtotal ?? ($order->unit_price !== null
                            ? max(0, ((float) $order->unit_price * (int) $order->qty) - (float) ($order->discount_amount ?? 0))
                            : ($order->product ? (float) $order->product->product_price * (int) $order->qty : 0));
                    @endphp

                    <div class="bg-white rounded-4 border p-3 p-md-4" style="border-color: var(--gn-border-warm) !important;">
                        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 pb-3 mb-3 border-bottom" style="border-color: var(--gn-border-warm) !important;">
                            <div class="d-flex flex-wrap align-items-center gap-2 small">
                                <span class="fw-bold" style="color: var(--gn-primary);">Pesanan #{{ $order->orders_id }}</span>
                                @if(!empty($order->payment_reference))
                                    <span class="text-muted">•</span>
                                    <span class="font-monospace text-muted" style="font-size: 11px;">Ref: {{ $order->payment_reference }}</span>
                                @endif
                                <span class="text-muted">•</span>
                                <span class="text-muted">{{ \Carbon\Carbon::parse($order->date)->format('d M Y') }}</span>
                            </div>

                            @php
                                $itemPayStatus = strtolower(trim((string)($order->payment_status ?? 'pending')));
                                $itemPayBadge = match($itemPayStatus) {
                                    'paid' => 'gn-badge-green',
                                    'pending' => 'gn-badge-amber',
                                    'failed', 'cancelled', 'deny' => 'gn-badge-red',
                                    'expired' => 'gn-badge-neutral',
                                    default => 'gn-badge-neutral',
                                };
                            @endphp
                            <div class="d-flex flex-wrap align-items-center gap-2">
                                <span class="gn-badge {{ $itemPayBadge }}">{{ $paymentService->labelForOrder($order) }}</span>
                                <span class="gn-badge {{ $badgeClass }}">{{ ucfirst($order->status ?? 'Pending') }}</span>
                            </div>
                        </div>

                        <div class="d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between gap-3">
                            <div class="d-flex align-items-center gap-3">
                                <div class="rounded-3 overflow-hidden border flex-shrink-0" style="width: 72px; height: 72px; background: var(--gn-surface); border-color: var(--gn-border-warm) !important;">
                                    @if($order->product && $order->product->product_image)
                                        <img
                                            src="{{ asset('product_images/' . $order->product->product_image) }}"
                                            alt="{{ $order->product->product_title }}"
                                            class="w-100 h-100"
                                            style="object-fit: cover;"
                                            onerror="this.onerror=null; this.src='https://placehold.co/150x150/eaf2ec/244b35?text=Greenova';">
                                    @endif
                                </div>

                                <div>
                                    <h3 class="h6 fw-bold mb-1 text-dark">
                                        {{ $order->product->product_title ?? 'Produk tidak tersedia' }}
                                    </h3>
                                    <div class="small text-muted">
                                        Jumlah: <strong>{{ $order->qty }}</strong> produk
                                        <span class="mx-1">•</span>
                                        Total: <strong style="color: var(--gn-primary);">Rp {{ number_format((float) $orderTotal, 0, ',', '.') }}</strong>
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex flex-wrap gap-2 w-100 w-md-auto justify-content-md-end">
                                @if($isSelesai)
                                    @if($hasReview)
                                        <button type="button"
                                                class="gn-btn gn-btn-outline gn-btn-sm btn-review-trigger"
                                                data-order-id="{{ $order->orders_id }}"
                                                data-product-id="{{ $order->product_id }}"
                                                data-product-title="{{ $order->product->product_title ?? 'Produk Greenova' }}"
                                                data-product-image="{{ $order->product && $order->product->product_image ? asset('product_images/' . $order->product->product_image) : '' }}"
                                                data-is-edit="1"
                                                data-review-id="{{ $order->review->id }}"
                                                data-rating="{{ $order->review->rating }}"
                                                data-comment="{{ $order->review->comment ?? '' }}"
                                                data-store-url="{{ route('product.review.store', $order->product_id) }}"
                                                data-update-url="{{ route('review.update', $order->review->id) }}">
                                            <span style="color: var(--gn-gold);">★</span> {{ $order->review->rating }}/5 • Edit Ulasan
                                        </button>
                                    @else
                                        <button type="button"
                                                class="gn-btn gn-btn-secondary gn-btn-sm btn-review-trigger"
                                                data-order-id="{{ $order->orders_id }}"
                                                data-product-id="{{ $order->product_id }}"
                                                data-product-title="{{ $order->product->product_title ?? 'Produk Greenova' }}"
                                                data-product-image="{{ $order->product && $order->product->product_image ? asset('product_images/' . $order->product->product_image) : '' }}"
                                                data-is-edit="0"
                                                data-review-id=""
                                                data-rating="5"
                                                data-comment=""
                                                data-store-url="{{ route('product.review.store', $order->product_id) }}"
                                                data-update-url="">
                                            Beri Penilaian
                                        </button>
                                    @endif
                                @endif

                                @php
                                    $canPayThisOrder = in_array(strtolower((string)$order->payment_method), ['midtrans'])
                                        && strtolower(trim((string)($order->payment_status ?? 'pending'))) === 'pending'
                                        && $orderStatus !== 'dibatalkan';
                                @endphp

                                @if($canPayThisOrder)
                                    <a href="{{ route('orders.show', $order->orders_id) }}" class="gn-btn gn-btn-primary gn-btn-sm" style="background: var(--gn-primary);">
                                        Bayar Sekarang
                                    </a>
                                @endif

                                <a href="{{ route('orders.show', $order->orders_id) }}" class="gn-btn {{ $canPayThisOrder ? 'gn-btn-outline' : 'gn-btn-primary' }} gn-btn-sm">
                                    Lihat Detail
                                </a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="bg-white rounded-4 border p-5 text-center" style="border-color: var(--gn-border-warm) !important;">
                <h3 class="h5 fw-bold mb-2" style="color: var(--gn-primary);">
                    {{ $status === 'semua' ? 'Belum ada pesanan' : 'Tidak ada pesanan ' . ucfirst($status) }}
                </h3>
                <p class="small text-muted mb-4">
                    {{ $status === 'semua' ? 'Anda belum memiliki riwayat pesanan di Greenova.' : 'Belum ada pesanan dengan status ' . ucfirst($status) . '.' }}
                </p>
                <a href="{{ $status === 'semua' ? route('products') : route('orders') }}" class="gn-btn gn-btn-primary">
                    {{ $status === 'semua' ? 'Mulai Belanja' : 'Tampilkan Semua Pesanan' }}
                </a>
            </div>
        @endif

    </div>
</div>

@include('order.review-modal')
@endsection
