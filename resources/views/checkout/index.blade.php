@extends('layouts.app')

@section('title', 'Checkout Pembayaran — Greenova')

@section('content')
@php
    $total = $subtotalBeforeVoucher ?? 0;
    $productDiscountTotal = 0;
    $originalTotal = 0;
    foreach ($cart as $item) {
        $qty = (int) $item['quantity'];
        $base = (float) ($item['base_price'] ?? $item['price']);
        $eff = (float) $item['price'];
        $originalTotal += $base * $qty;
        $productDiscountTotal += max(0, ($base - $eff) * $qty);
        if (!isset($subtotalBeforeVoucher)) {
            $total += $eff * $qty;
        }
    }
    $voucherDiscountAmount = $voucherDiscount ?? 0;
    $finalGrandTotal = $grandTotal ?? max(0, $total - $voucherDiscountAmount);
@endphp

<div class="py-4 py-lg-5" style="background: var(--gn-surface, #FAF9F6); min-height: 85vh;">
    <div class="container">

        <!-- Checkout Header & Step Indicator -->
        <div class="mb-4 pb-2">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-3">
                <div>
                    <span class="gn-badge gn-badge-green mb-2 d-inline-flex align-items-center gap-1">
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                        Checkout Terenkripsi
                    </span>
                    <h1 class="fw-bold mb-1" style="font-size: clamp(1.6rem, 2.5vw, 2.2rem); color: var(--gn-primary, #244B35); letter-spacing: -0.02em;">
                        Konfirmasi &amp; Pembayaran
                    </h1>
                    <p class="mb-0 text-muted small">Periksa rincian belanja, alamat pengiriman, dan selesaikan pembayaran dengan aman.</p>
                </div>

                <!-- Breadcrumb Steps -->
                <div class="d-none d-md-flex align-items-center gap-2 small text-muted">
                    <a href="{{ route('cart') }}" class="text-decoration-none text-muted d-flex align-items-center gap-1">
                        <span class="badge rounded-circle bg-light text-dark border">1</span> Keranjang
                    </a>
                    <span class="text-muted">&rsaquo;</span>
                    <span class="fw-bold text-dark d-flex align-items-center gap-1">
                        <span class="badge rounded-circle text-white" style="background: var(--gn-primary, #244B35);">2</span> Pembayaran
                    </span>
                    <span class="text-muted">&rsaquo;</span>
                    <span class="text-muted d-flex align-items-center gap-1">
                        <span class="badge rounded-circle bg-light text-muted border">3</span> Selesai
                    </span>
                </div>
            </div>
        </div>

        @if(session('error'))
            <div class="alert alert-danger border-0 rounded-4 shadow-sm mb-4 d-flex align-items-center gap-3 p-3" role="alert">
                <svg width="24" height="24" fill="currentColor" class="text-danger flex-shrink-0" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                <div class="small fw-medium">{{ session('error') }}</div>
            </div>
        @endif

        @if(isset($errors) && $errors->any())
            <div class="alert alert-danger border-0 rounded-4 shadow-sm mb-4 p-4" role="alert">
                <div class="fw-bold small mb-2 d-flex align-items-center gap-2">
                    <svg width="18" height="18" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                    Mohon lengkapi formulir pengiriman:
                </div>
                <ul class="mb-0 small ps-3">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form id="checkout-form" action="{{ route('checkout.process') }}" method="POST">
            @csrf

            <div class="row g-4 align-items-start">
                <!-- Kolom Kiri: Form & Produk (Desktop: 7 col, Mobile: 12 col) -->
                <div class="col-lg-7 d-flex flex-column gap-4">

                    <!-- Kartu 1: Produk Dipesan -->
                    <section class="bg-white rounded-4 border p-4 shadow-sm" style="border-color: var(--gn-border-warm, #E7E4DC) !important;">
                        <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom" style="border-color: #F1EFEA !important;">
                            <div class="d-flex align-items-center gap-2">
                                <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 32px; height: 32px; background: var(--gn-accent-soft, #EAF2EC); color: var(--gn-primary, #244B35);">
                                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                                </div>
                                <h2 class="h6 fw-bold mb-0" style="color: var(--gn-primary, #244B35);">Item Pesanan</h2>
                            </div>
                            <span class="gn-badge gn-badge-neutral">{{ count($cart) }} Produk</span>
                        </div>

                        <div class="d-flex flex-column gap-3">
                            @forelse($cart as $id => $item)
                                @php
                                    $itemSubtotal = (float) $item['price'] * (int) $item['quantity'];
                                    $hasDisc = ($item['base_price'] ?? $item['price']) > $item['price'];
                                @endphp
                                <div class="d-flex align-items-center gap-3 p-3 rounded-3" style="background: var(--gn-surface, #FAF9F6); border: 1px solid var(--gn-border-warm, #E7E4DC);">
                                    <div class="rounded-3 overflow-hidden border flex-shrink-0" style="width: 68px; height: 68px; background: #FFF;">
                                        <img
                                            src="{{ asset('product_images/' . $item['image']) }}"
                                            alt="{{ $item['name'] }}"
                                            class="w-100 h-100"
                                            style="object-fit: cover;"
                                            onerror="this.onerror=null; this.src='https://placehold.co/150x150/eaf2ec/244b35?text=Greenova';">
                                    </div>

                                    <div class="flex-grow-1 min-w-0">
                                        <div class="fw-bold text-dark text-truncate" style="font-size: 14.5px;">{{ $item['name'] }}</div>
                                        <div class="d-flex align-items-center gap-2 small text-muted mt-1">
                                            <span>{{ $item['quantity'] }} unit</span>
                                            <span>&bull;</span>
                                            @if($hasDisc)
                                                <span class="text-decoration-line-through text-muted small">Rp {{ number_format($item['base_price'], 0, ',', '.') }}</span>
                                            @endif
                                            <span class="fw-medium text-dark">Rp {{ number_format($item['price'], 0, ',', '.') }}</span>
                                        </div>
                                    </div>

                                    <div class="fw-bold text-end ps-2" style="color: var(--gn-primary, #244B35); font-size: 15px; white-space: nowrap;">
                                        Rp {{ number_format($itemSubtotal, 0, ',', '.') }}
                                    </div>
                                </div>
                            @empty
                                <div class="text-center py-4 text-muted small">
                                    Keranjang belanja Anda kosong. <a href="{{ route('products') }}" class="text-decoration-underline" style="color: var(--gn-primary);">Mulai belanja</a>
                                </div>
                            @endforelse
                        </div>
                    </section>

                    <!-- Kartu 2: Data Pengiriman -->
                    <section class="bg-white rounded-4 border p-4 shadow-sm" style="border-color: var(--gn-border-warm, #E7E4DC) !important;">
                        <div class="d-flex align-items-center gap-2 mb-3 pb-2 border-bottom" style="border-color: #F1EFEA !important;">
                            <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 32px; height: 32px; background: var(--gn-accent-soft, #EAF2EC); color: var(--gn-primary, #244B35);">
                                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            </div>
                            <h2 class="h6 fw-bold mb-0" style="color: var(--gn-primary, #244B35);">Alamat &amp; Data Penerima</h2>
                        </div>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="recipient_name" class="form-label small fw-bold text-dark mb-1">Nama Lengkap Penerima <span class="text-danger">*</span></label>
                                <input
                                    type="text"
                                    id="recipient_name"
                                    name="recipient_name"
                                    class="form-control rounded-3 py-2 px-3"
                                    style="border-color: #CBD5E1; font-size: 14px;"
                                    value="{{ old('recipient_name', auth()->user()?->name) }}"
                                    placeholder="Contoh: Budi Santoso"
                                    maxlength="100"
                                    required>
                            </div>

                            <div class="col-md-6">
                                <label for="recipient_phone" class="form-label small fw-bold text-dark mb-1">Nomor WhatsApp / HP <span class="text-danger">*</span></label>
                                <input
                                    type="tel"
                                    id="recipient_phone"
                                    name="recipient_phone"
                                    class="form-control rounded-3 py-2 px-3"
                                    style="border-color: #CBD5E1; font-size: 14px;"
                                    value="{{ old('recipient_phone', auth()->user()?->phone) }}"
                                    placeholder="081234567890"
                                    maxlength="20"
                                    required>
                            </div>

                            <div class="col-12">
                                <label for="shipping_address" class="form-label small fw-bold text-dark mb-1">Alamat Lengkap Pengiriman <span class="text-danger">*</span></label>
                                <textarea
                                    id="shipping_address"
                                    name="shipping_address"
                                    rows="3"
                                    class="form-control rounded-3 py-2 px-3"
                                    style="border-color: #CBD5E1; font-size: 14px; line-height: 1.5;"
                                    placeholder="Tulis nama jalan, nomor rumah/kantor, RT/RW, kelurahan, kecamatan, kota, serta kode pos..."
                                    maxlength="1000"
                                    required>{{ old('shipping_address', auth()->user()?->address) }}</textarea>
                            </div>
                        </div>
                    </section>

                    <!-- Kartu 3: Pilihan Metode Pembayaran -->
                    <section class="bg-white rounded-4 border p-4 shadow-sm" style="border-color: var(--gn-border-warm, #E7E4DC) !important;">
                        <div class="d-flex align-items-center gap-2 mb-3 pb-2 border-bottom" style="border-color: #F1EFEA !important;">
                            <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 32px; height: 32px; background: var(--gn-accent-soft, #EAF2EC); color: var(--gn-primary, #244B35);">
                                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                            </div>
                            <h2 class="h6 fw-bold mb-0" style="color: var(--gn-primary, #244B35);">Pilih Cara Pembayaran</h2>
                        </div>

                        <div class="d-flex flex-column gap-3">
                            <!-- Pembayaran Online via Midtrans Snap -->
                            <label class="d-flex align-items-start gap-3 p-3 rounded-3 border position-relative"
                                   id="label-payment-midtrans"
                                   style="cursor: pointer; border-color: var(--gn-primary, #244B35) !important; background: #F6FBF7; transition: all 0.2s ease;">
                                <input type="radio"
                                       name="payment_method"
                                       value="midtrans"
                                       id="radio-midtrans"
                                       class="form-check-input mt-1"
                                       style="accent-color: var(--gn-primary, #244B35);"
                                       checked
                                       required>
                                <div class="flex-grow-1">
                                    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-1">
                                        <span class="fw-bold text-dark" style="font-size: 15px;">Pembayaran Online Otomatis</span>
                                        <span class="gn-badge gn-badge-green" style="font-size: 11px;">Verifikasi Cepat</span>
                                    </div>
                                    <p class="small text-muted mb-2" style="font-size: 13px; line-height: 1.5;">
                                        Mendukung <strong>QRIS</strong> (GoPay, OVO, ShopeePay, DANA), <strong>Virtual Account</strong> (BCA, Mandiri, BNI, BRI, Permata), dan Kartu Debit/Kredit via Midtrans Snap.
                                    </p>
                                    <div class="d-flex flex-wrap gap-1 align-items-center">
                                        <span class="badge bg-white text-dark border px-2 py-1" style="font-size: 11px;">QRIS</span>
                                        <span class="badge bg-white text-dark border px-2 py-1" style="font-size: 11px;">BCA VA</span>
                                        <span class="badge bg-white text-dark border px-2 py-1" style="font-size: 11px;">Mandiri VA</span>
                                        <span class="badge bg-white text-dark border px-2 py-1" style="font-size: 11px;">BNI VA</span>
                                        <span class="badge bg-white text-dark border px-2 py-1" style="font-size: 11px;">BRI VA</span>
                                        <span class="badge bg-white text-dark border px-2 py-1" style="font-size: 11px;">GoPay</span>
                                    </div>
                                </div>
                            </label>
                        </div>
                    </section>

                </div>

                <!-- Kolom Kanan: Ringkasan Biaya & CTA (Desktop: 5 col, Sticky) -->
                <div class="col-lg-5">
                    <aside class="bg-white rounded-4 border p-4 shadow-sm position-sticky" style="top: 96px; border-color: var(--gn-border-warm, #E7E4DC) !important;">
                        <div class="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom" style="border-color: #F1EFEA !important;">
                            <h2 class="h6 fw-bold mb-0" style="color: var(--gn-primary, #244B35);">Ringkasan Pesanan</h2>
                            <span class="small text-muted">{{ collect($cart)->sum('quantity') }} Total Item</span>
                        </div>

                        <!-- Rincian Biaya -->
                        <div class="d-flex flex-column gap-2 mb-3 small">
                            <div class="d-flex justify-content-between">
                                <span class="text-muted">Total Harga Produk</span>
                                <span class="text-dark fw-medium">Rp {{ number_format($originalTotal, 0, ',', '.') }}</span>
                            </div>

                            @if($productDiscountTotal > 0)
                                <div class="d-flex justify-content-between text-success">
                                    <span>Hemat Diskon Produk</span>
                                    <span class="fw-semibold">- Rp {{ number_format($productDiscountTotal, 0, ',', '.') }}</span>
                                </div>
                            @endif

                            <div class="d-flex justify-content-between">
                                <span class="text-muted">Subtotal Belanja</span>
                                <span class="text-dark fw-semibold">Rp {{ number_format($total, 0, ',', '.') }}</span>
                            </div>

                            @if($voucherDiscountAmount > 0 && isset($appliedVoucher))
                                <div class="d-flex justify-content-between text-success">
                                    <span>Voucher Hemat ({{ $appliedVoucher->code }})</span>
                                    <span class="fw-semibold">- Rp {{ number_format($voucherDiscountAmount, 0, ',', '.') }}</span>
                                </div>
                            @endif

                            <div class="d-flex justify-content-between align-items-center pt-2 border-top" style="border-color: #F3F4F6 !important;">
                                <span class="text-muted">Ongkos Kirim</span>
                                <span class="gn-badge gn-badge-green" style="font-size: 11px;">✓ Gratis Ongkir</span>
                            </div>
                        </div>

                        <!-- Kupon / Voucher Input -->
                        <div class="p-3 rounded-3 mb-3" style="background: var(--gn-surface, #FAF9F6); border: 1px dashed #CBD5E1;">
                            <label for="voucher_code_input" class="form-label small fw-bold text-dark mb-1 d-flex align-items-center gap-1">
                                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
                                Punya Kode Voucher?
                            </label>

                            @if(session('voucher_success'))
                                <div class="small text-success mb-2 fw-medium">{{ session('voucher_success') }}</div>
                            @endif
                            @if(session('voucher_error'))
                                <div class="small text-danger mb-2 fw-medium">{{ session('voucher_error') }}</div>
                            @endif

                            @if(isset($appliedVoucher) && $appliedVoucher)
                                <div class="d-flex align-items-center justify-content-between gap-2 p-2 rounded-2 bg-white border">
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="gn-badge gn-badge-green">{{ $appliedVoucher->code }}</span>
                                        <span class="small text-muted">Voucher aktif</span>
                                    </div>
                                    <button type="submit" form="voucher-remove-form" class="btn btn-sm btn-link text-danger text-decoration-none p-0 fw-semibold" style="font-size: 12px;">
                                        Hapus
                                    </button>
                                </div>
                            @else
                                <div class="d-flex gap-2">
                                    <input
                                        type="text"
                                        id="voucher_code_input"
                                        name="voucher_code"
                                        form="voucher-apply-form"
                                        class="form-control rounded-3 py-1 px-2 text-uppercase font-monospace"
                                        style="font-size: 13px;"
                                        placeholder="KODE VOUCHER"
                                        maxlength="50">
                                    <button type="submit" form="voucher-apply-form" class="gn-btn gn-btn-outline gn-btn-sm flex-shrink-0" style="padding: 6px 14px; font-size: 13px;">
                                        Terapkan
                                    </button>
                                </div>
                            @endif
                        </div>

                        <!-- Total Tagihan Final -->
                        <div class="pt-3 mb-4 border-top" style="border-color: #E7E4DC !important;">
                            <div class="d-flex justify-content-between align-items-baseline mb-1">
                                <span class="text-muted small">Total Tagihan:</span>
                                <strong class="fw-bold" style="font-size: 24px; color: var(--gn-primary, #244B35); letter-spacing: -0.02em;">
                                    Rp {{ number_format($finalGrandTotal, 0, ',', '.') }}
                                </strong>
                            </div>
                            <div class="text-end text-muted" style="font-size: 11px;">Sudah termasuk seluruh diskon &amp; PPN</div>
                        </div>

                        <!-- Feedback Alert Box -->
                        <div id="checkout-alert" class="alert alert-danger d-none small py-2 px-3 mb-3 rounded-3" role="alert"></div>

                        <!-- Tombol Submit Utama -->
                        <button type="submit"
                                id="btn-submit-checkout"
                                class="gn-btn gn-btn-primary w-100 py-3 fw-bold d-flex align-items-center justify-content-center gap-2 rounded-3 shadow-sm"
                                style="font-size: 15.5px; background: var(--gn-primary, #244B35);">
                            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                            <span id="btn-text">Bayar Sekarang</span>
                            <span id="btn-spinner" class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                        </button>

                        <a href="{{ route('cart') }}" class="gn-btn gn-btn-outline w-100 py-2 mt-2 rounded-3 text-center" style="font-size: 13.5px;">
                            &larr; Kembali ke Keranjang
                        </a>

                        <!-- Jaminan Keamanan -->
                        <div class="mt-4 pt-3 border-top text-center" style="border-color: #F1EFEA !important;">
                            <div class="d-flex align-items-center justify-content-center gap-2 text-muted" style="font-size: 11.5px;">
                                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" class="text-success" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                                <span>256-Bit SSL Encryption • Transaksi Aman</span>
                            </div>
                        </div>
                    </aside>
                </div>
            </div>
        </form>

        <!-- Hidden Forms untuk Voucher -->
        <form id="voucher-apply-form" action="{{ route('checkout.voucher.apply') }}" method="POST" style="display: none;">
            @csrf
        </form>
        <form id="voucher-remove-form" action="{{ route('checkout.voucher.remove') }}" method="POST" style="display: none;">
            @csrf
        </form>

    </div>
</div>

{{-- Midtrans Snap JS Integration --}}
@php
    $paymentService = app(\App\Services\PaymentService::class);
    $snapUrl = $paymentService->getSnapUrl();
    $clientKey = $paymentService->getClientKey();
@endphp
@if(!empty($clientKey))
    <script src="{{ $snapUrl }}" data-client-key="{{ $clientKey }}"></script>
@endif

<script>
document.addEventListener('DOMContentLoaded', function () {
    const checkoutForm = document.getElementById('checkout-form');
    const submitBtn = document.getElementById('btn-submit-checkout');
    const btnText = document.getElementById('btn-text');
    const btnSpinner = document.getElementById('btn-spinner');
    const alertBox = document.getElementById('checkout-alert');

    btnText.textContent = 'Bayar Sekarang (Midtrans Snap)';

    function setLoading(isLoading, text = 'Memproses...') {
        submitBtn.disabled = isLoading;
        if (isLoading) {
            btnText.textContent = text;
            btnSpinner.classList.remove('d-none');
        } else {
            btnText.textContent = 'Bayar Sekarang (Midtrans Snap)';
            btnSpinner.classList.add('d-none');
        }
    }

    function showAlert(msg) {
        alertBox.textContent = msg;
        alertBox.classList.remove('d-none');
        alertBox.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }

    checkoutForm.addEventListener('submit', function (e) {
        e.preventDefault();
        alertBox.classList.add('d-none');

        // Validasi data penerima di browser
        const name = document.getElementById('recipient_name').value.trim();
        const phone = document.getElementById('recipient_phone').value.trim();
        const address = document.getElementById('shipping_address').value.trim();

        if (!name || !phone || !address) {
            showAlert('Silakan lengkapi nama penerima, nomor telepon, dan alamat pengiriman.');
            return;
        }

        setLoading(true, 'Menyiapkan Pembayaran Midtrans...');

        const formData = new FormData(checkoutForm);

        fetch('{{ route('checkout.process') }}', {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value
            },
            body: formData
        })
        .then(async response => {
            const data = await response.json();
            if (!response.ok) {
                throw new Error(data.message || 'Gagal memproses pesanan.');
            }
            return data;
        })
        .then(data => {
            const token = data.snap_token || data.data?.snap_token;
            if (token && typeof window.snap !== 'undefined') {
                setLoading(true, 'Menampilkan Popup Pembayaran...');
                window.snap.pay(token, {
                    onSuccess: function (result) {
                        window.location.href = data.order_success_url;
                    },
                    onPending: function (result) {
                        window.location.href = data.order_success_url;
                    },
                    onError: function (result) {
                        window.location.href = data.order_success_url;
                    },
                    onClose: function () {
                        // Pelanggan menutup popup tanpa bayar: tetap arahkan ke order success agar bisa bayar nanti
                        window.location.href = data.order_success_url;
                    }
                });
            } else {
                // Fallback jika Snap JS belum siap atau kredensial offline
                window.location.href = data.order_success_url;
            }
        })
        .catch(err => {
            setLoading(false);
            showAlert(err.message || 'Terjadi kendala saat memproses pesanan. Silakan coba kembali.');
        });
    });
});
</script>
@endsection
