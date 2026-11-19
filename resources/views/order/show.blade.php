@extends('layouts.app')

@section('title', 'Detail Pesanan #' . $order->orders_id . ' — Greenova')

@section('content')
@php
    $orderStatus = strtolower(trim($order->status ?? 'pending'));
    $orderBadgeClass = match ($orderStatus) {
        'selesai' => 'gn-badge-green',
        'dikirim' => 'gn-badge-blue',
        'diproses' => 'gn-badge-blue',
        'dibatalkan' => 'gn-badge-red',
        default => 'gn-badge-amber',
    };

    $paymentLabels = [
        'midtrans'      => 'Pembayaran Online (Midtrans Snap)',
        'bank_transfer' => 'Transfer Bank',
        'ewallet'       => 'E-Wallet',
    ];

    $paymentService = app(\App\Services\PaymentService::class);
    $hasSnapshot = $order->subtotal !== null || $order->unit_price !== null;
    $snapshotTotal = $order->subtotal ?? max(0, ((float) ($order->unit_price ?? 0) * (int) $order->qty) - (float) ($order->discount_amount ?? 0));
    $displayTotal = $hasSnapshot ? (float) $snapshotTotal : ($order->product ? (float) $order->product->product_price * (int) $order->qty : 0);

    $isMidtrans = in_array(strtolower((string)$order->payment_method), ['midtrans']) || in_array(strtolower((string)$order->payment_gateway), ['midtrans']);
    $payStatus = strtolower(trim((string)($order->payment_status ?? 'pending')));
    $isPaid = ($payStatus === 'paid');
    $canPayMidtrans = $isMidtrans && ($payStatus === 'pending') && ($orderStatus !== 'dibatalkan');
    $orderGroupRef = $order->payment_reference ?: ($order->order_group ?? null);
    $snapToken = $snapToken ?? ($paymentTransaction->snap_token ?? null);

    $payBadgeClass = match ($payStatus) {
        'paid' => 'gn-badge-green',
        'pending' => 'gn-badge-amber',
        'failed', 'cancelled', 'deny' => 'gn-badge-red',
        'expired' => 'gn-badge-neutral',
        default => 'gn-badge-neutral',
    };
@endphp

<div class="py-4 py-lg-5" style="background: var(--gn-surface, #FAF9F6); min-height: 85vh;">
    <div class="container">

        @if(session('success'))
            <div class="alert alert-success border-0 rounded-4 shadow-sm mb-4 d-flex align-items-center gap-2 p-3" role="status">
                <svg width="20" height="20" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                <div class="small fw-medium">{{ session('success') }}</div>
            </div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger border-0 rounded-4 shadow-sm mb-4 d-flex align-items-center gap-2 p-3" role="alert">
                <svg width="20" height="20" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                <div class="small fw-medium">{{ session('error') }}</div>
            </div>
        @endif

        <!-- Header Navigasi -->
        <div class="d-flex flex-wrap align-items-end justify-content-between gap-3 mb-4 pb-2 border-bottom" style="border-color: #E7E4DC !important;">
            <div>
                <div class="d-flex align-items-center gap-2 mb-2">
                    <span class="gn-badge gn-badge-green">Detail Transaksi</span>
                    @if(!empty($orderGroupRef))
                        <span class="font-monospace text-muted small" style="font-size: 12px;">Ref: {{ $orderGroupRef }}</span>
                    @endif
                </div>
                <h1 class="fw-bold mb-1" style="font-size: clamp(1.6rem, 2.5vw, 2.2rem); color: var(--gn-primary, #244B35); letter-spacing: -0.02em;">
                    Pesanan #{{ $order->orders_id }}
                </h1>
                <div class="text-muted small">
                    Dibuat pada {{ \Carbon\Carbon::parse($order->date)->format('d M Y, H:i') }} WIB
                </div>
            </div>
            <a href="{{ route('orders') }}" class="gn-btn gn-btn-outline gn-btn-sm rounded-3">
                &larr; Riwayat Pesanan
            </a>
        </div>

        <div class="row g-4 align-items-start">
            <div class="col-lg-8 d-flex flex-column gap-4">

                <!-- KARTU 1: STATUS PEMBAYARAN & PESANAN (TERPISAH & TEGAS) -->
                <section class="bg-white rounded-4 border p-4 shadow-sm" style="border-color: var(--gn-border-warm, #E7E4DC) !important;">
                    <div class="row g-3 align-items-center">
                        <!-- Sisi Kiri: Status Pembayaran -->
                        <div class="col-sm-6 border-end-sm">
                            <div class="small text-uppercase fw-bold text-muted mb-1" style="letter-spacing: 0.06em; font-size: 11px;">
                                Status Pembayaran
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <span class="gn-badge {{ $payBadgeClass }} px-3 py-1 fw-bold" style="font-size: 13.5px;">
                                    {{ $paymentService->labelForOrder($order) }}
                                </span>
                            </div>
                            <div class="small text-muted mt-2" style="font-size: 12px;">
                                @if($isPaid && $order->paid_at)
                                    <span class="text-success fw-medium">Lunas pada {{ \Carbon\Carbon::parse($order->paid_at)->format('d M Y, H:i') }} WIB</span>
                                @elseif($isPaid)
                                    <span class="text-success fw-medium">Pembayaran telah terkonfirmasi lunas</span>
                                @elseif($payStatus === 'pending')
                                    <span class="text-amber fw-medium">Menunggu penyelesaian transfer / QRIS</span>
                                @else
                                    <span>Transaksi {{ $paymentService->labelForOrder($order) }}</span>
                                @endif
                            </div>
                        </div>

                        <!-- Sisi Kanan: Status Pemenuhan Pesanan -->
                        <div class="col-sm-6 ps-sm-4">
                            <div class="small text-uppercase fw-bold text-muted mb-1" style="letter-spacing: 0.06em; font-size: 11px;">
                                Status Pemenuhan Pesanan
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <span class="gn-badge {{ $orderBadgeClass }} px-3 py-1 fw-bold" style="font-size: 13.5px;">
                                    {{ ucfirst($order->status ?? 'Pending') }}
                                </span>
                            </div>
                            <div class="small text-muted mt-2" style="font-size: 12px;">
                                @if($orderStatus === 'selesai')
                                    <span class="text-success">Paket telah diterima dan transaksi selesai</span>
                                @elseif($orderStatus === 'dikirim')
                                    <span class="text-primary">Pesanan sedang dalam proses pengiriman kurir menuju alamat Anda</span>
                                @elseif($orderStatus === 'diproses')
                                    <span class="text-primary">Pesanan sedang disiapkan &amp; dikemas oleh tim gudang</span>
                                @elseif($orderStatus === 'dibatalkan')
                                    <span class="text-danger">Pesanan dibatalkan (stok produk telah dikembalikan)</span>
                                @else
                                    <span>Pesanan baru (menunggu konfirmasi operasional penjual)</span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- 5-Stage Order Progress Tracker -->
                    <div class="mt-4 pt-4 border-top" style="border-color: #F1EFEA !important;">
                        @if($orderStatus === 'dibatalkan')
                            <div class="p-3 bg-danger-subtle rounded-3 text-danger text-center small fw-semibold">
                                ✕ Pesanan ini telah dibatalkan. Dana atau stok produk telah disesuaikan kembali.
                            </div>
                        @else
                            <div class="d-flex justify-content-between position-relative text-center small">
                                <!-- Step 1: Menunggu Pembayaran -->
                                <div class="d-flex flex-column align-items-center flex-fill">
                                    <div class="rounded-circle d-flex align-items-center justify-content-center mb-1 text-white fw-bold"
                                         style="width: 28px; height: 28px; background: var(--gn-primary, #244B35); font-size: 12px;">✓</div>
                                    <span class="fw-semibold text-dark" style="font-size: 11px;">1. Menunggu Pembayaran</span>
                                </div>
                                <!-- Step 2: Pembayaran Dikonfirmasi -->
                                <div class="d-flex flex-column align-items-center flex-fill">
                                    <div class="rounded-circle d-flex align-items-center justify-content-center mb-1 fw-bold {{ $isPaid ? 'text-white' : 'text-muted' }}"
                                         style="width: 28px; height: 28px; background: {{ $isPaid ? 'var(--gn-primary, #244B35)' : '#E5E7EB' }}; font-size: 12px;">
                                        {{ $isPaid ? '✓' : '2' }}
                                    </div>
                                    <span class="{{ $isPaid ? 'fw-semibold text-dark' : 'text-muted' }}" style="font-size: 11px;">
                                        2. Pembayaran Terkonfirmasi
                                    </span>
                                </div>
                                <!-- Step 3: Sedang Diproses -->
                                @php
                                    $step3Active = in_array($orderStatus, ['diproses', 'dikirim', 'selesai']);
                                @endphp
                                <div class="d-flex flex-column align-items-center flex-fill">
                                    <div class="rounded-circle d-flex align-items-center justify-content-center mb-1 fw-bold {{ $step3Active ? 'text-white' : 'text-muted' }}"
                                         style="width: 28px; height: 28px; background: {{ $step3Active ? 'var(--gn-primary, #244B35)' : '#E5E7EB' }}; font-size: 12px;">
                                        {{ $step3Active ? '✓' : '3' }}
                                    </div>
                                    <span class="{{ $step3Active ? 'fw-semibold text-dark' : 'text-muted' }}" style="font-size: 11px;">
                                        3. Sedang Diproses
                                    </span>
                                </div>
                                <!-- Step 4: Dikirim -->
                                @php
                                    $step4Active = in_array($orderStatus, ['dikirim', 'selesai']);
                                @endphp
                                <div class="d-flex flex-column align-items-center flex-fill">
                                    <div class="rounded-circle d-flex align-items-center justify-content-center mb-1 fw-bold {{ $step4Active ? 'text-white' : 'text-muted' }}"
                                         style="width: 28px; height: 28px; background: {{ $step4Active ? 'var(--gn-primary, #244B35)' : '#E5E7EB' }}; font-size: 12px;">
                                        {{ $step4Active ? '✓' : '4' }}
                                    </div>
                                    <span class="{{ $step4Active ? 'fw-semibold text-dark' : 'text-muted' }}" style="font-size: 11px;">
                                        4. Dalam Pengiriman
                                    </span>
                                </div>
                                <!-- Step 5: Selesai -->
                                <div class="d-flex flex-column align-items-center flex-fill">
                                    <div class="rounded-circle d-flex align-items-center justify-content-center mb-1 fw-bold {{ $orderStatus === 'selesai' ? 'text-white' : 'text-muted' }}"
                                         style="width: 28px; height: 28px; background: {{ $orderStatus === 'selesai' ? 'var(--gn-primary, #244B35)' : '#E5E7EB' }}; font-size: 12px;">
                                        {{ $orderStatus === 'selesai' ? '✓' : '5' }}
                                    </div>
                                    <span class="{{ $orderStatus === 'selesai' ? 'fw-semibold text-dark' : 'text-muted' }}" style="font-size: 11px;">
                                        5. Pesanan Selesai
                                    </span>
                                </div>
                            </div>
                        @endif
                    </div>
                </section>

                <!-- CALLOUT KHUSUS: MENUNGGU PEMBAYARAN MIDTRANS DENGAN RECONCILIATION -->
                @if($canPayMidtrans)
                    <section class="rounded-4 p-4 border shadow-sm" style="background: #FFFDF7; border-color: #FDE68A !important;">
                        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-3">
                            <div>
                                <span class="gn-badge gn-badge-amber mb-2 d-inline-block">Pembayaran Tertunda</span>
                                <h2 class="h5 fw-bold mb-1 text-dark">Selesaikan Pembayaran via Midtrans Snap</h2>
                                <p class="small text-muted mb-0">
                                    Total tagihan <strong>Rp {{ number_format($groupedOrders->isNotEmpty() ? $groupedOrders->sum('subtotal') : $displayTotal, 0, ',', '.') }}</strong> belum terbayar.
                                </p>
                            </div>
                        </div>

                        <!-- Tombol Aksi Pembayaran & Sync -->
                        <div class="d-flex flex-wrap gap-2 pt-2 border-top" style="border-color: rgba(0,0,0,0.06) !important;">
                            <button type="button"
                                    id="btnPayShowSnap"
                                    class="gn-btn gn-btn-primary py-2 px-4 fw-bold d-inline-flex align-items-center gap-2 rounded-3 shadow-sm"
                                    style="background: var(--gn-primary, #244B35);">
                                <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                                <span>Bayar Sekarang (Midtrans Snap)</span>
                            </button>

                            <button type="button"
                                    id="btnCheckShowStatus"
                                    class="gn-btn gn-btn-outline py-2 px-3 fw-semibold d-inline-flex align-items-center gap-2 rounded-3">
                                <span id="sync-spinner" class="spinner-border spinner-border-sm d-none" role="status"></span>
                                <span id="sync-btn-text">Periksa Status Pembayaran</span>
                            </button>
                        </div>

                        <div id="sync-feedback" class="small mt-2 d-none"></div>
                    </section>
                @endif

                <!-- KARTU 2: DAFTAR PRODUK DALAM PESANAN -->
                <section class="bg-white rounded-4 border p-4 shadow-sm" style="border-color: var(--gn-border-warm, #E7E4DC) !important;">
                    <h2 class="h6 fw-bold mb-3 pb-2 border-bottom" style="color: var(--gn-primary, #244B35); border-color: #F1EFEA !important;">
                        Produk yang Dipesan
                    </h2>

                    <div class="d-flex flex-column flex-sm-row align-items-start align-items-sm-center gap-3">
                        <div class="rounded-3 overflow-hidden border flex-shrink-0" style="width: 88px; height: 88px; background: var(--gn-surface, #FAF9F6);">
                            @if($order->product && $order->product->product_image)
                                <img
                                    src="{{ asset('product_images/' . $order->product->product_image) }}"
                                    alt="{{ $order->product->product_title }}"
                                    class="w-100 h-100"
                                    style="object-fit: cover;"
                                    onerror="this.onerror=null; this.src='https://placehold.co/200x200/eaf2ec/244b35?text=Greenova';">
                            @endif
                        </div>

                        <div class="flex-grow-1 min-w-0">
                            <h3 class="h6 fw-bold mb-1">
                                @if($order->product)
                                    <a href="{{ route('product.detail', $order->product->product_id) }}" class="text-decoration-none text-dark">
                                        {{ $order->product->product_title }}
                                    </a>
                                @else
                                    Produk tidak tersedia
                                @endif
                            </h3>
                            <div class="small text-muted mb-2">Jumlah: <strong>{{ $order->qty }} unit</strong></div>

                            @if($hasSnapshot)
                                <div class="fw-bold" style="font-size: 18px; color: var(--gn-primary, #244B35);">
                                    Rp {{ number_format($displayTotal, 0, ',', '.') }}
                                </div>
                                <div class="small text-muted" style="font-size: 12px;">
                                    Harga satuan transaksi: Rp {{ number_format((float) $order->unit_price, 0, ',', '.') }}
                                    @if((float) ($order->discount_amount ?? 0) > 0)
                                        • Hemat diskon -Rp {{ number_format((float) $order->discount_amount, 0, ',', '.') }}
                                    @endif
                                    @if((float) ($order->voucher_discount ?? 0) > 0)
                                        • Voucher {{ $order->voucher_code }} -Rp {{ number_format((float) $order->voucher_discount, 0, ',', '.') }}
                                    @endif
                                </div>
                            @elseif($order->product)
                                <div class="fw-bold" style="font-size: 18px; color: var(--gn-primary, #244B35);">
                                    Rp {{ number_format($displayTotal, 0, ',', '.') }}
                                </div>
                            @endif
                        </div>
                    </div>

                    @if($groupedOrders->count() > 1)
                        <div class="mt-4 pt-3 border-top" style="border-color: #F1EFEA !important;">
                            <div class="small fw-bold text-muted mb-3 text-uppercase" style="letter-spacing: 0.05em; font-size: 11px;">
                                Produk Lain Dalam Transaksi Ini ({{ $groupedOrders->count() - 1 }})
                            </div>
                            <div class="d-flex flex-column gap-2">
                                @foreach($groupedOrders as $groupedItem)
                                    @if($groupedItem->orders_id !== $order->orders_id)
                                        <div class="d-flex align-items-center justify-content-between p-2 rounded-3 bg-light small">
                                            <div class="d-flex align-items-center gap-2 min-w-0">
                                                <span class="badge rounded-pill bg-white text-dark border">{{ $groupedItem->qty }}x</span>
                                                <a href="{{ route('orders.show', $groupedItem->orders_id) }}" class="text-decoration-none text-dark fw-medium text-truncate">
                                                    {{ $groupedItem->product->product_title ?? 'Produk #' . $groupedItem->product_id }}
                                                </a>
                                            </div>
                                            <div class="d-flex align-items-center gap-2 ps-2">
                                                <span class="fw-semibold text-dark" style="white-space: nowrap;">Rp {{ number_format((float) $groupedItem->subtotal, 0, ',', '.') }}</span>
                                                <a href="{{ route('orders.show', $groupedItem->orders_id) }}" class="gn-btn gn-btn-outline gn-btn-sm py-1 px-2" style="font-size: 11px;">
                                                    Buka
                                                </a>
                                            </div>
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                        </div>
                    @endif
                </section>

                <!-- KARTU 3: ULASAN & PENILAIAN PRODUK (JIKA STATUS SELESAI) -->
                @if($orderStatus === 'selesai')
                    <section class="bg-white rounded-4 border p-4 shadow-sm" style="border-color: var(--gn-border-warm, #E7E4DC) !important;">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h2 class="h6 fw-bold mb-0" style="color: var(--gn-primary, #244B35);">Penilaian &amp; Ulasan Produk</h2>
                            @if($order->review)
                                <span class="gn-badge gn-badge-green">Sudah Dinilai</span>
                            @endif
                        </div>

                        @if($order->review)
                            <div class="p-3 rounded-3 mb-3" style="background: var(--gn-surface, #FAF9F6); border: 1px solid var(--gn-border-warm, #E7E4DC);">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <div>
                                        @for($i = 1; $i <= 5; $i++)
                                            <span style="color: {{ $i <= $order->review->rating ? 'var(--gn-gold, #F59E0B)' : '#D1D5DB' }};">★</span>
                                        @endfor
                                        <strong class="small ms-1">{{ $order->review->rating }}/5</strong>
                                    </div>
                                    <span class="small text-muted">{{ $order->review->created_at ? $order->review->created_at->format('d M Y, H:i') : '' }}</span>
                                </div>
                                <p class="small mb-0 text-dark">
                                    {{ $order->review->comment ?: 'Tidak ada ulasan tertulis.' }}
                                </p>
                            </div>

                            <button type="button"
                                    class="gn-btn gn-btn-outline gn-btn-sm btn-review-trigger rounded-3"
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
                                Edit Ulasan Saya
                            </button>
                        @else
                            <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 p-3 rounded-3" style="background: var(--gn-ivory, #F7F6F2);">
                                <div class="small">
                                    <div class="fw-bold text-dark">Bagikan ulasan Anda</div>
                                    <div class="text-muted">Pesanan telah selesai. Ulasan Anda membantu pelanggan lain memilih produk berkelanjutan.</div>
                                </div>
                                <button type="button"
                                        class="gn-btn gn-btn-primary gn-btn-sm btn-review-trigger rounded-3"
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
                                    ★ Beri Penilaian
                                </button>
                            </div>
                        @endif
                    </section>
                @endif

                <!-- KARTU 4: INFORMASI PENGIRIMAN -->
                <section class="bg-white rounded-4 border p-4 shadow-sm" style="border-color: var(--gn-border-warm, #E7E4DC) !important;">
                    <h2 class="h6 fw-bold mb-3 pb-2 border-bottom" style="color: var(--gn-primary, #244B35); border-color: #F1EFEA !important;">
                        Alamat Pengiriman
                    </h2>
                    <div class="row g-3 small">
                        <div class="col-md-6">
                            <div class="text-muted mb-1">Nama Penerima</div>
                            <div class="fw-semibold text-dark">{{ $order->recipient_name ?: '-' }}</div>
                        </div>
                        <div class="col-md-6">
                            <div class="text-muted mb-1">Nomor Telepon</div>
                            <div class="fw-semibold text-dark">{{ $order->recipient_phone ?: '-' }}</div>
                        </div>
                        <div class="col-12">
                            <div class="text-muted mb-1">Alamat Tujuan</div>
                            <div class="fw-semibold text-dark" style="line-height: 1.5;">{{ $order->shipping_address ?: '-' }}</div>
                        </div>
                    </div>
                </section>

                <!-- Tombol Pembatalan Pesanan (Hanya jika belum bayar) -->
                @if($orderStatus === 'pending' && !$isPaid)
                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 p-3 rounded-3" style="background: var(--gn-danger-soft, #FEF2F2); border: 1px solid #FECACA;">
                        <div class="small">
                            <div class="fw-bold text-danger">Ingin membatalkan pesanan?</div>
                            <div class="text-muted">Pesanan yang belum dibayar dapat dibatalkan dan stok produk akan langsung dikembalikan ke etalase toko.</div>
                        </div>
                        <form action="{{ route('orders.cancel', $order->orders_id) }}"
                              method="POST"
                              onsubmit="return confirm('Apakah Anda yakin ingin membatalkan pesanan ini?');">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="gn-btn gn-btn-sm text-white rounded-3" style="background: var(--gn-danger, #DC2626);">
                                Batalkan Pesanan
                            </button>
                        </form>
                    </div>
                @endif

            </div>

            <!-- Kolom Kanan: Ringkasan Rincian Biaya -->
            <div class="col-lg-4">
                <aside class="bg-white rounded-4 border p-4 shadow-sm position-sticky" style="top: 96px; border-color: var(--gn-border-warm, #E7E4DC) !important;">
                    <h2 class="h6 fw-bold mb-3 pb-2 border-bottom" style="color: var(--gn-primary, #244B35); border-color: #F1EFEA !important;">
                        Rincian Transaksi
                    </h2>

                    @if(!empty($orderGroupRef))
                        <div class="d-flex justify-content-between small mb-2">
                            <span class="text-muted">Nomor Transaksi</span>
                            <strong class="font-monospace text-dark" style="font-size: 11.5px;">{{ $orderGroupRef }}</strong>
                        </div>
                    @endif

                    <div class="d-flex justify-content-between small mb-2">
                        <span class="text-muted">Jumlah Item</span>
                        <strong class="text-dark">{{ $order->qty }} unit</strong>
                    </div>

                    <div class="d-flex justify-content-between small mb-2">
                        <span class="text-muted">Metode Pembayaran</span>
                        <strong class="text-dark">{{ $paymentLabels[$order->payment_method] ?? ($order->payment_method ?: '-') }}</strong>
                    </div>

                    <div class="d-flex justify-content-between small mb-2">
                        <span class="text-muted">Status Pembayaran</span>
                        <span class="gn-badge {{ $payBadgeClass }} py-0 px-2" style="font-size: 11px;">{{ $paymentService->labelForOrder($order) }}</span>
                    </div>

                    <div class="d-flex justify-content-between small mb-3">
                        <span class="text-muted">Status Pesanan</span>
                        <span class="gn-badge {{ $orderBadgeClass }} py-0 px-2" style="font-size: 11px;">{{ ucfirst($order->status ?? 'Pending') }}</span>
                    </div>

                    <hr style="border-color: #E7E4DC;">

                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <span class="fw-bold">Total Pembayaran</span>
                        <strong style="font-size: 20px; color: var(--gn-primary, #244B35);">
                            Rp {{ number_format($displayTotal, 0, ',', '.') }}
                        </strong>
                    </div>

                    @if($canPayMidtrans)
                        <button type="button"
                                id="btnPaySidebarSnap"
                                class="gn-btn gn-btn-primary w-100 mb-2 py-3 fw-bold rounded-3 shadow-sm"
                                style="background: var(--gn-primary, #244B35);">
                            Bayar Sekarang (Midtrans Snap)
                        </button>
                    @endif

                    <a href="{{ route('orders') }}" class="gn-btn gn-btn-outline w-100 py-2 rounded-3 text-center" style="font-size: 13.5px;">
                        Kembali ke Pesanan Saya
                    </a>
                </aside>
            </div>
        </div>

    </div>
</div>

@if($canPayMidtrans)
    <script src="{{ $paymentService->getSnapUrl() }}" data-client-key="{{ $paymentService->getClientKey() }}"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        const btnMain = document.getElementById('btnPayShowSnap');
        const btnSidebar = document.getElementById('btnPaySidebarSnap');
        const btnSync = document.getElementById('btnCheckShowStatus');
        const syncSpinner = document.getElementById('sync-spinner');
        const syncText = document.getElementById('sync-btn-text');
        const syncFeedback = document.getElementById('sync-feedback');

        let currentSnapToken = @json($snapToken);
        const orderRef = @json($orderGroupRef);

        async function openSnap() {
            const btns = [btnMain, btnSidebar].filter(Boolean);
            if (!currentSnapToken) {
                btns.forEach(b => {
                    b.disabled = true;
                    b.innerText = 'Menyiapkan Snap Token...';
                });
                try {
                    const res = await fetch(`{{ url('/payment/snap-token') }}/${encodeURIComponent(orderRef)}`, {
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });
                    const data = await res.json();
                    const token = data.snap_token || (data.data && data.data.snap_token);
                    if (token) {
                        currentSnapToken = token;
                    } else {
                        alert(data.message || 'Gagal memuat Snap Token Midtrans.');
                        btns.forEach(b => {
                            b.disabled = false;
                            b.innerText = 'Bayar Sekarang';
                        });
                        return;
                    }
                } catch (err) {
                    console.error('Snap token error:', err);
                    alert('Gagal menghubungi server pembayaran.');
                    btns.forEach(b => {
                        b.disabled = false;
                        b.innerText = 'Bayar Sekarang';
                    });
                    return;
                }
            }

            btns.forEach(b => {
                b.disabled = false;
                b.innerText = 'Bayar Sekarang';
            });

            if (typeof window.snap !== 'undefined' && currentSnapToken) {
                window.snap.pay(currentSnapToken, {
                    onSuccess: function (result) {
                        window.location.reload();
                    },
                    onPending: function (result) {
                        window.location.reload();
                    },
                    onError: function (result) {
                        alert('Pembayaran gagal atau dibatalkan.');
                        window.location.reload();
                    },
                    onClose: function () {
                        // Cek status seketika
                        checkStatusSync();
                    }
                });
            } else {
                alert('Midtrans Snap belum siap. Silakan muat ulang halaman.');
            }
        }

        async function checkStatusSync() {
            if (!orderRef) return;
            if (btnSync) {
                btnSync.disabled = true;
                syncSpinner.classList.remove('d-none');
                syncText.textContent = 'Memeriksa Midtrans...';
                syncFeedback.className = 'small mt-2 text-muted';
                syncFeedback.textContent = 'Menghubungkan ke API Midtrans...';
                syncFeedback.classList.remove('d-none');
            }

            try {
                const res = await fetch(`{{ url('/payment/status') }}/${encodeURIComponent(orderRef)}`, {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                const data = await res.json();

                if (data.is_paid || (data.payment_status && data.payment_status.toLowerCase() === 'paid')) {
                    if (syncFeedback) {
                        syncFeedback.className = 'small mt-2 text-success fw-bold';
                        syncFeedback.textContent = '✓ Pembayaran berhasil terkonfirmasi lunas! Memuat ulang...';
                    }
                    setTimeout(() => window.location.reload(), 1200);
                    return;
                }

                if (syncFeedback) {
                    syncFeedback.className = 'small mt-2 text-muted';
                    syncFeedback.textContent = 'Status saat ini: ' + (data.payment_label || 'Menunggu Pembayaran') + '. Jika baru bayar di simulator, tunggu beberapa detik dan coba lagi.';
                }
            } catch (err) {
                if (syncFeedback) {
                    syncFeedback.className = 'small mt-2 text-danger';
                    syncFeedback.textContent = 'Gagal menghubungi server pemeriksaan.';
                }
            } finally {
                if (btnSync) {
                    btnSync.disabled = false;
                    syncSpinner.classList.add('d-none');
                    syncText.textContent = 'Periksa Status Pembayaran';
                }
            }
        }

        if (btnMain) btnMain.addEventListener('click', openSnap);
        if (btnSidebar) btnSidebar.addEventListener('click', openSnap);
        if (btnSync) btnSync.addEventListener('click', checkStatusSync);
    });
    </script>
@endif

@include('order.review-modal')
@endsection
