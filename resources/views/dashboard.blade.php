@extends('layouts.app')

@section('title', 'Dashboard Akun Saya — Greenova')

@section('content')
<div class="py-4 py-lg-5" style="background: var(--gn-surface, #FAF9F6); min-height: 85vh;">
    <div class="container">

        @if(session('success'))
            <div class="alert alert-success border-0 rounded-4 shadow-sm mb-4 d-flex align-items-center gap-2 p-3" role="status">
                <svg width="20" height="20" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                <div class="small fw-medium">{{ session('success') }}</div>
            </div>
        @endif

        <!-- Customer Welcome Card -->
        <div class="card border-0 rounded-4 shadow-sm mb-4 overflow-hidden" style="background: linear-gradient(135deg, #1B3B2A 0%, #244B35 100%); color: #fff;">
            <div class="card-body p-4 p-md-5">
                <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                    <div class="d-flex align-items-center gap-3">
                        <div class="rounded-circle d-flex align-items-center justify-content-center fw-bold fs-3 text-success flex-shrink-0"
                             style="width: 64px; height: 64px; background: #ECFDF5; border: 3px solid rgba(255,255,255,0.2);">
                            {{ strtoupper(mb_substr($user->name, 0, 1)) }}
                        </div>
                        <div>
                            <span class="badge px-3 py-1 mb-1 fw-semibold" style="background: rgba(255,255,255,0.18); color: #ECFDF5; font-size: 11px;">
                                AKUN PELANGGAN GREENOVA
                            </span>
                            <h2 class="fw-bold mb-1 text-white" style="font-size: clamp(1.4rem, 2.4vw, 1.85rem);">
                                Halo, {{ $user->name }}!
                            </h2>
                            <p class="mb-0 small text-white-50">
                                {{ $user->email }} &bull; Bergabung sejak {{ $user->created_at ? $user->created_at->format('d M Y') : '2026' }}
                            </p>
                        </div>
                    </div>

                    <div class="d-flex gap-2">
                        <a href="{{ route('profile.edit') }}" class="btn btn-outline-light btn-sm rounded-3 px-3 py-2 fw-semibold">
                            Ubah Profil
                        </a>
                        <a href="{{ route('orders') }}" class="btn btn-light btn-sm rounded-3 px-3 py-2 fw-semibold text-success">
                            Riwayat Pesanan &rarr;
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- 4 Stat Metric Cards -->
        <div class="row g-3 mb-4">
            <div class="col-6 col-lg-3">
                <a href="{{ route('orders', ['status' => 'pending']) }}" class="text-decoration-none">
                    <div class="card border-0 rounded-4 shadow-sm p-4 bg-white h-100 hover-card">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="small fw-bold text-muted text-uppercase" style="letter-spacing: 0.05em; font-size: 11px;">Menunggu Bayar</span>
                            <span class="badge bg-warning-subtle text-warning-emphasis rounded-pill">Belum Bayar</span>
                        </div>
                        <div class="fw-bold fs-3 text-warning-emphasis mb-1">
                            {{ $statistics['pending_payment'] }}
                        </div>
                        <span class="small text-muted">Pesanan belum selesai bayar</span>
                    </div>
                </a>
            </div>

            <div class="col-6 col-lg-3">
                <a href="{{ route('orders', ['status' => 'diproses']) }}" class="text-decoration-none">
                    <div class="card border-0 rounded-4 shadow-sm p-4 bg-white h-100 hover-card">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="small fw-bold text-muted text-uppercase" style="letter-spacing: 0.05em; font-size: 11px;">Sedang Diproses</span>
                            <span class="badge bg-primary-subtle text-primary rounded-pill">Diproses</span>
                        </div>
                        <div class="fw-bold fs-3 text-primary mb-1">
                            {{ $statistics['processing'] }}
                        </div>
                        <span class="small text-muted">Dikemas atau dalam kurir</span>
                    </div>
                </a>
            </div>

            <div class="col-6 col-lg-3">
                <a href="{{ route('orders', ['status' => 'selesai']) }}" class="text-decoration-none">
                    <div class="card border-0 rounded-4 shadow-sm p-4 bg-white h-100 hover-card">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="small fw-bold text-muted text-uppercase" style="letter-spacing: 0.05em; font-size: 11px;">Pesanan Selesai</span>
                            <span class="badge bg-success-subtle text-success rounded-pill">Selesai</span>
                        </div>
                        <div class="fw-bold fs-3 text-success mb-1">
                            {{ $statistics['completed'] }}
                        </div>
                        <span class="small text-muted">Diterima &amp; dapat diulas</span>
                    </div>
                </a>
            </div>

            <div class="col-6 col-lg-3">
                <div class="card border-0 rounded-4 shadow-sm p-4 bg-white h-100">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="small fw-bold text-muted text-uppercase" style="letter-spacing: 0.05em; font-size: 11px;">Total Belanja</span>
                        <span class="badge bg-light text-dark border rounded-pill">Akumulasi</span>
                    </div>
                    <div class="fw-bold fs-4 text-dark mb-1">
                        Rp {{ number_format($statistics['total_spent'], 0, ',', '.') }}
                    </div>
                    <span class="small text-muted">{{ $statistics['total_orders'] }} total pesanan</span>
                </div>
            </div>
        </div>

        <div class="row g-4 align-items-start">
            <!-- Sisi Kiri: Pesanan Terkini -->
            <div class="col-lg-8">
                <div class="card border-0 rounded-4 shadow-sm p-4 bg-white mb-4">
                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3 pb-2 border-bottom">
                        <div>
                            <h5 class="fw-bold mb-1 text-dark">Pesanan Terkini</h5>
                            <p class="text-muted small mb-0">Daftar transaksi terbaru Anda di Greenova.</p>
                        </div>
                        <a href="{{ route('orders') }}" class="small fw-semibold text-success text-decoration-none">
                            Lihat Semua Pesanan &rarr;
                        </a>
                    </div>

                    @forelse($recentOrders as $order)
                        @php
                            $sub = (float) ($order->subtotal ?? ($order->unit_price ? $order->unit_price * $order->qty : 0));
                            $st = strtolower(trim($order->status ?? 'pending'));
                            $paySt = strtolower(trim($order->payment_status ?? 'pending'));
                            $isMidtrans = in_array(strtolower((string)$order->payment_method), ['midtrans']);
                            $canPay = $isMidtrans && ($paySt === 'pending') && ($st !== 'dibatalkan');

                            $badgeClass = match ($st) {
                                'selesai' => 'gn-badge-green',
                                'diproses', 'dikirim' => 'gn-badge-blue',
                                'dibatalkan' => 'gn-badge-red',
                                default => 'gn-badge-amber',
                            };
                            $statusLabel = match($st) {
                                'selesai' => 'Selesai',
                                'dikirim' => 'Dikirim',
                                'diproses' => 'Diproses',
                                'dibatalkan' => 'Dibatalkan',
                                default => 'Menunggu Pembayaran',
                            };
                        @endphp
                        <div class="p-3 mb-3 rounded-4 border bg-light d-flex flex-wrap align-items-center justify-content-between gap-3">
                            <div class="d-flex align-items-center gap-3">
                                <div class="rounded-3 overflow-hidden bg-white border flex-shrink-0" style="width: 58px; height: 58px;">
                                    <img src="{{ $order->product && $order->product->product_image ? asset('product_images/' . $order->product->product_image) : 'https://placehold.co/100x100?text=Produk' }}"
                                         alt="{{ $order->product->product_title ?? 'Produk' }}"
                                         class="w-100 h-100 object-fit-cover">
                                </div>
                                <div>
                                    <div class="d-flex align-items-center gap-2 mb-1">
                                        <span class="small font-monospace text-muted">#{{ $order->orders_id }}</span>
                                        <span class="small text-muted">&bull; {{ \Carbon\Carbon::parse($order->date)->format('d M Y') }}</span>
                                        <span class="gn-badge {{ $badgeClass }}" style="font-size: 11px; padding: 2px 8px;">
                                            {{ $statusLabel }}
                                        </span>
                                    </div>
                                    <div class="fw-bold text-dark text-truncate" style="max-width: 280px;">
                                        {{ $order->product->product_title ?? 'Produk #' . $order->product_id }}
                                    </div>
                                    <div class="small text-muted">
                                        {{ $order->qty }} unit &bull; Total: <strong class="text-dark">Rp {{ number_format($sub, 0, ',', '.') }}</strong>
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex align-items-center gap-2">
                                @if($canPay)
                                    <a href="{{ route('orders.show', $order->orders_id) }}" class="btn btn-warning btn-sm rounded-3 fw-bold text-dark px-3">
                                        Bayar Sekarang
                                    </a>
                                @endif
                                <a href="{{ route('orders.show', $order->orders_id) }}" class="btn btn-outline-success btn-sm rounded-3 px-3">
                                    Detail
                                </a>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-5 text-muted">
                            <div class="mx-auto mb-3 d-inline-flex align-items-center justify-content-center rounded-circle"
                                 style="width: 56px; height: 56px; background: #ECFDF5; color: #166534;">
                                <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4Z"/><path d="M3 6h18"/><path d="M16 10a4 4 0 0 1-8 0"/>
                                </svg>
                            </div>
                            <h6 class="fw-bold text-dark">Belum ada pesanan</h6>
                            <p class="small text-muted mb-3">Mulai belanja produk ramah lingkungan pilihan Greenova hari ini.</p>
                            <a href="{{ route('products') }}" class="btn btn-success btn-sm rounded-3 px-4">
                                Buka Katalog Produk
                            </a>
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- Sisi Kanan: Informasi Akun & Shortcut -->
            <div class="col-lg-4">
                <div class="card border-0 rounded-4 shadow-sm p-4 bg-white mb-4">
                    <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
                        <h6 class="fw-bold mb-0 text-dark">Informasi Akun</h6>
                        <a href="{{ route('profile.edit') }}" class="small fw-semibold text-success text-decoration-none">Edit</a>
                    </div>

                    <div class="mb-3">
                        <span class="small text-muted d-block mb-1">Nama Penerima / Akun:</span>
                        <strong class="text-dark">{{ $user->name }}</strong>
                    </div>

                    <div class="mb-3">
                        <span class="small text-muted d-block mb-1">Alamat Email:</span>
                        <span class="text-dark">{{ $user->email }}</span>
                    </div>

                    <div class="mb-3">
                        <span class="small text-muted d-block mb-1">Nomor Telepon:</span>
                        <span class="text-dark">{{ $user->phone ?: 'Belum ditambahkan' }}</span>
                    </div>

                    <div class="mb-3">
                        <span class="small text-muted d-block mb-1">Alamat Pengiriman Utama:</span>
                        <p class="small text-secondary mb-0" style="line-height: 1.6;">
                            {{ $user->address ?: 'Belum ada alamat tersimpan. Silakan lengkapi pada halaman profil Anda.' }}
                        </p>
                    </div>
                </div>

                <!-- Card Shortcut Layanan -->
                <div class="card border-0 rounded-4 shadow-sm p-4 bg-white">
                    <h6 class="fw-bold mb-3 pb-2 border-bottom text-dark">Menu Layanan</h6>
                    <div class="d-flex flex-column gap-2">
                        <a href="{{ route('orders') }}" class="btn btn-light btn-sm text-start rounded-3 d-flex justify-content-between align-items-center py-2 px-3">
                            <span class="d-flex align-items-center gap-2">
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-success"><path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4Z"/><path d="M3 6h18"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>
                                Pesanan Saya
                            </span>
                            <span class="small text-muted">&rarr;</span>
                        </a>
                        <a href="{{ route('cart') }}" class="btn btn-light btn-sm text-start rounded-3 d-flex justify-content-between align-items-center py-2 px-3">
                            <span class="d-flex align-items-center gap-2">
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-success"><circle cx="8" cy="21" r="1"/><circle cx="19" cy="21" r="1"/><path d="M2.05 2.05h2l2.66 12.42a2 2 0 0 0 2 1.58h9.78a2 2 0 0 0 1.95-1.57l1.65-7.43H5.12"/></svg>
                                Keranjang Belanja
                            </span>
                            <span class="small text-muted">&rarr;</span>
                        </a>
                        <a href="{{ route('products') }}" class="btn btn-light btn-sm text-start rounded-3 d-flex justify-content-between align-items-center py-2 px-3">
                            <span class="d-flex align-items-center gap-2">
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-success"><path d="M11 20A7 7 0 0 1 9.8 6.1C15.5 5 17 4.48 19 2c1 2 2 4.18 2 8 0 5.5-4.78 10-10 10Z"/><path d="M2 21c0-3 1.85-5.36 5.08-6C9.5 14.52 12 13 13 12"/></svg>
                                Katalog Produk
                            </span>
                            <span class="small text-muted">&rarr;</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection
