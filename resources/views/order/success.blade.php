@extends('layouts.app')

@section('title', 'Status Pembayaran & Pesanan — Greenova')

@section('content')
@php
    $paymentService = $paymentService ?? app(\App\Services\PaymentService::class);
    $firstOrder = $orders->first();
    $paymentMethod = $paymentMethod ?? ($firstOrder?->payment_method ?? 'midtrans');
    $isMidtrans = ($paymentMethod === 'midtrans' || ($firstOrder?->payment_gateway ?? '') === 'midtrans');
    $paymentStatus = strtolower(trim((string)($firstOrder?->payment_status ?? 'pending')));
    $isPaid = ($paymentStatus === 'paid');
    $isFailed = in_array($paymentStatus, ['failed', 'deny', 'expire', 'cancelled', 'expired'], true);
    $firstCreatedOrderId = $firstOrder?->orders_id;
@endphp

<div class="py-5" style="background: var(--gn-surface, #FAF9F6); min-height: 80vh;">
    <div class="container">

        <!-- Card Utama -->
        <div class="bg-white rounded-4 border p-4 p-md-5 shadow-sm text-center mb-4" style="border-color: var(--gn-border-warm, #E7E4DC) !important;">

            @if($isPaid)
                <!-- ================= STATE 1: PEMBAYARAN BERHASIL (LUNAS) ================= -->
                <div class="d-inline-flex align-items-center justify-content-center rounded-circle mb-3"
                     style="width: 80px; height: 80px; background: #DCFCE7; color: #166534; box-shadow: 0 0 0 8px rgba(220, 252, 231, 0.4);">
                    <svg width="40" height="40" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                </div>

                <div class="mb-2">
                    <span class="gn-badge gn-badge-green px-3 py-1 fw-bold" style="font-size: 13px;">✓ Terverifikasi Lunas</span>
                </div>

                <h1 class="fw-bold mb-2" style="font-size: clamp(1.6rem, 2.6vw, 2.2rem); color: var(--gn-primary, #244B35); letter-spacing: -0.02em;">
                    Pembayaran Berhasil!
                </h1>

                <p class="text-muted mb-4" style="max-width: 540px; margin-inline: auto; font-size: 14.5px; line-height: 1.6;">
                    Terima kasih atas pesanan Anda. Transaksi telah diverifikasi oleh sistem pembayaran Greenova dan pesanan Anda sedang disiapkan oleh tim kami.
                </p>

                <!-- Informasi Transaksi Lunas Grid -->
                <div class="rounded-3 p-3 p-md-4 mb-4 text-start" style="background: var(--gn-surface, #FAF9F6); border: 1px solid var(--gn-border-warm, #E7E4DC);">
                    <div class="row g-3">
                        <div class="col-sm-6">
                            <div class="small text-muted mb-1">Nomor Referensi Transaksi</div>
                            <strong class="font-monospace text-dark" style="font-size: 14px;">{{ $ref ?: ($firstOrder?->payment_reference ?? '-') }}</strong>
                        </div>
                        <div class="col-sm-6">
                            <div class="small text-muted mb-1">Waktu Konfirmasi Pembayaran</div>
                            <strong class="text-dark" style="font-size: 14px;">
                                {{ $firstOrder?->paid_at ? \Carbon\Carbon::parse($firstOrder->paid_at)->format('d M Y, H:i') . ' WIB' : now()->format('d M Y, H:i') . ' WIB' }}
                            </strong>
                        </div>
                        <div class="col-sm-6">
                            <div class="small text-muted mb-1">Metode Pembayaran</div>
                            <strong class="text-dark" style="font-size: 14px;">
                                Midtrans Online ({{ strtoupper($firstOrder?->payment_type ?: 'Snap Sandbox') }})
                            </strong>
                        </div>
                        <div class="col-sm-6">
                            <div class="small text-muted mb-1">Total Dibayar</div>
                            <strong class="fw-bold" style="font-size: 18px; color: var(--gn-primary, #244B35);">
                                Rp {{ number_format($grossTotal, 0, ',', '.') }}
                            </strong>
                        </div>
                    </div>
                </div>

            @elseif($isFailed)
                <!-- ================= STATE 2: PEMBAYARAN GAGAL / KEDALUWARSA ================= -->
                <div class="d-inline-flex align-items-center justify-content-center rounded-circle mb-3"
                     style="width: 80px; height: 80px; background: #FEE2E2; color: #DC2626;">
                    <svg width="40" height="40" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </div>

                <div class="mb-2">
                    <span class="gn-badge gn-badge-red px-3 py-1 fw-bold" style="font-size: 13px;">Pembayaran {{ ucfirst($paymentStatus) }}</span>
                </div>

                <h1 class="fw-bold mb-2" style="font-size: clamp(1.6rem, 2.6vw, 2.2rem); color: var(--gn-charcoal, #1F2937);">
                    Pembayaran Tidak Selesai
                </h1>

                <p class="text-muted mb-4" style="max-width: 520px; margin-inline: auto; font-size: 14.5px;">
                    Transaksi ini telah dibatalkan atau masa pembayaran telah kedaluwarsa. Stok barang telah otomatis dikembalikan ke etalase.
                </p>

            @else
                <!-- ================= STATE 3: MENUNGGU PEMBAYARAN (MIDTRANS SANDBOX) ================= -->
                <div class="d-inline-flex align-items-center justify-content-center rounded-circle mb-3"
                     style="width: 80px; height: 80px; background: #FEF3C7; color: #D97706; box-shadow: 0 0 0 8px rgba(254, 243, 199, 0.5);">
                    <svg width="40" height="40" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>

                <div class="mb-2">
                    <span class="gn-badge gn-badge-amber px-3 py-1 fw-bold" id="badge-payment-status" style="font-size: 13px;">Menunggu Pembayaran</span>
                </div>

                <h1 class="fw-bold mb-2" style="font-size: clamp(1.6rem, 2.6vw, 2.2rem); color: var(--gn-primary, #244B35); letter-spacing: -0.02em;">
                    Selesaikan Pembayaran Anda
                </h1>

                <p class="text-muted mb-4" style="max-width: 540px; margin-inline: auto; font-size: 14.5px; line-height: 1.6;">
                    Pesanan Anda telah diamankan. Silakan selesaikan pembayaran melalui Midtrans Snap. Setelah menyelesaikan simulasi pembayaran, tekan tombol <strong>Periksa Status</strong> di bawah.
                </p>

                <!-- Box Detail Tagihan -->
                <div class="rounded-4 p-4 text-start mb-4" style="background: #FFFDF7; border: 1px solid #FDE68A;">
                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-3">
                        <div>
                            <div class="small text-muted text-uppercase fw-bold" style="letter-spacing: 0.05em;">Nomor Transaksi</div>
                            <strong class="font-monospace text-dark" style="font-size: 15px;">{{ $ref }}</strong>
                        </div>
                        <div class="text-sm-end">
                            <div class="small text-muted text-uppercase fw-bold" style="letter-spacing: 0.05em;">Total Tagihan</div>
                            <strong class="fw-bold" style="font-size: 22px; color: var(--gn-primary, #244B35);">
                                Rp {{ number_format($grossTotal, 0, ',', '.') }}
                            </strong>
                        </div>
                    </div>

                    <!-- Simulator Helper Alert -->
                    <div class="p-3 rounded-3 mb-3 d-flex align-items-start gap-2 small" style="background: #FEF3C7; color: #92400E;">
                        <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" class="flex-shrink-0 mt-0.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <div>
                            <strong>Simulasi Sandbox:</strong> Jika Anda membayar menggunakan <a href="https://simulator.sandbox.midtrans.com/" target="_blank" class="fw-bold text-decoration-underline" style="color: #92400E;">Midtrans Payment Simulator</a>, status dapat langsung disinkronkan dengan menekan tombol <em>Periksa Status Pembayaran</em> di bawah.
                        </div>
                    </div>

                    <!-- Tombol Aksi Pembayaran -->
                    <div class="d-flex flex-column flex-sm-row gap-2 pt-2">
                        <button type="button"
                                id="btnPaySnap"
                                class="gn-btn gn-btn-primary flex-grow-1 py-3 fw-bold d-flex align-items-center justify-content-center gap-2 rounded-3 shadow-sm"
                                style="font-size: 15px; background: var(--gn-primary, #244B35);">
                            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                            <span>Bayar Sekarang (Midtrans Snap)</span>
                        </button>

                        <button type="button"
                                id="btnCheckStatus"
                                class="gn-btn gn-btn-outline py-3 px-3 fw-semibold d-flex align-items-center justify-content-center gap-2 rounded-3"
                                style="font-size: 14px;">
                            <span id="check-status-spinner" class="spinner-border spinner-border-sm d-none" role="status"></span>
                            <span id="check-status-text">Periksa Status Pembayaran</span>
                        </button>
                    </div>

                    <div id="check-status-feedback" class="mt-2 small text-center d-none"></div>
                </div>
            @endif

            <!-- Daftar Ringkas Barang Dibeli -->
            @if($orders->isNotEmpty())
                <div class="text-start border rounded-3 p-3 p-md-4 mb-4" style="border-color: var(--gn-border-warm, #E7E4DC) !important; background: var(--gn-surface, #FAF9F6);">
                    <div class="small fw-bold text-muted mb-3 text-uppercase" style="letter-spacing: 0.06em;">Ringkasan Barang ({{ $orders->count() }} Produk)</div>
                    <div class="d-flex flex-column gap-2">
                        @foreach($orders as $item)
                            <div class="d-flex align-items-center justify-content-between gap-3 small py-1 border-bottom border-light">
                                <div class="d-flex align-items-center gap-2 min-w-0">
                                    <span class="badge rounded-pill bg-light text-dark border px-2">{{ $item->qty }}x</span>
                                    <span class="text-dark fw-medium text-truncate">{{ $item->product->product_title ?? 'Produk Greenova' }}</span>
                                </div>
                                <span class="fw-bold text-dark ps-2" style="white-space: nowrap;">Rp {{ number_format((float) ($item->subtotal ?? 0), 0, ',', '.') }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- Tombol Navigasi Utama -->
            <div class="d-flex flex-column flex-sm-row align-items-center justify-content-center gap-3 pt-2">
                @if($firstCreatedOrderId)
                    <a href="{{ route('orders.show', $firstCreatedOrderId) }}" class="gn-btn gn-btn-primary w-100 w-sm-auto px-4 py-2 rounded-3" style="font-size: 14px;">
                        Lihat Detail Pesanan
                    </a>
                @endif
                <a href="{{ route('orders') }}" class="gn-btn gn-btn-outline w-100 w-sm-auto px-4 py-2 rounded-3" style="font-size: 14px;">
                    Daftar Pesanan Saya
                </a>
                <a href="{{ route('products') }}" class="gn-btn gn-btn-outline w-100 w-sm-auto px-4 py-2 rounded-3" style="font-size: 14px;">
                    Lanjut Belanja
                </a>
            </div>

        </div>

    </div>
</div>

@if($isMidtrans && !$isPaid)
    <script src="{{ $paymentService->getSnapUrl() }}" data-client-key="{{ $paymentService->getClientKey() }}"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        const btnPay = document.getElementById('btnPaySnap');
        const btnCheck = document.getElementById('btnCheckStatus');
        const checkSpinner = document.getElementById('check-status-spinner');
        const checkText = document.getElementById('check-status-text');
        const feedback = document.getElementById('check-status-feedback');
        let currentSnapToken = @json($snapToken);
        const orderRef = @json($ref);

        async function triggerPayment() {
            if (!currentSnapToken) {
                if (btnPay) {
                    btnPay.disabled = true;
                    btnPay.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Menyiapkan Snap Token...';
                }
                try {
                    const res = await fetch(`{{ url('/payment/snap-token') }}/${encodeURIComponent(orderRef)}`, {
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });
                    const data = await res.json();
                    const token = data.snap_token || data.data?.snap_token;
                    if (token) {
                        currentSnapToken = token;
                    } else {
                        alert(data.message || 'Gagal memuat Snap Token Midtrans.');
                        if (btnPay) {
                            btnPay.disabled = false;
                            btnPay.innerHTML = 'Bayar Sekarang (Midtrans Snap)';
                        }
                        return;
                    }
                } catch (err) {
                    console.error('Snap token error:', err);
                    alert('Koneksi pembayaran gagal. Silakan coba lagi.');
                    if (btnPay) {
                        btnPay.disabled = false;
                        btnPay.innerHTML = 'Bayar Sekarang (Midtrans Snap)';
                    }
                    return;
                }
            }

            if (btnPay) {
                btnPay.disabled = false;
                btnPay.innerHTML = '<svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg> <span>Bayar Sekarang (Midtrans Snap)</span>';
            }

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
                        // Periksa status saat ditutup jika user bayar via tab terpisah
                        checkPaymentStatus(false);
                    }
                });
            } else {
                alert('Midtrans Snap SDK belum siap. Silakan muat ulang halaman.');
            }
        }

        async function checkPaymentStatus(isUserInitiated = true) {
            if (!orderRef) return;

            if (isUserInitiated && btnCheck) {
                btnCheck.disabled = true;
                checkSpinner.classList.remove('d-none');
                checkText.textContent = 'Memeriksa Midtrans...';
                feedback.className = 'mt-2 small text-center text-muted';
                feedback.textContent = 'Menghubungkan ke API Midtrans...';
                feedback.classList.remove('d-none');
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
                    if (feedback) {
                        feedback.className = 'mt-2 small text-center text-success fw-bold';
                        feedback.textContent = '✓ Pembayaran berhasil terkonfirmasi lunas! Memuat ulang halaman...';
                    }
                    setTimeout(() => window.location.reload(), 1200);
                    return;
                }

                if (isUserInitiated) {
                    feedback.className = 'mt-2 small text-center text-muted';
                    feedback.textContent = 'Status saat ini: ' + (data.payment_label || 'Menunggu Pembayaran') + '. Jika baru saja bayar di simulator, tunggu beberapa detik dan periksa kembali.';
                }
            } catch (err) {
                if (isUserInitiated) {
                    feedback.className = 'mt-2 small text-center text-danger';
                    feedback.textContent = 'Gagal menghubungi server pemeriksaan. Silakan coba lagi.';
                }
            } finally {
                if (isUserInitiated && btnCheck) {
                    btnCheck.disabled = false;
                    checkSpinner.classList.add('d-none');
                    checkText.textContent = 'Periksa Status Pembayaran';
                }
            }
        }

        if (btnPay) btnPay.addEventListener('click', triggerPayment);
        if (btnCheck) btnCheck.addEventListener('click', () => checkPaymentStatus(true));
    });
    </script>
@endif

@endsection
