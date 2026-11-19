@extends('layouts.app')

@section('title', 'Greenova — Toko Produk Ramah Lingkungan & Berkelanjutan')

@section('content')
@php
    $heroProduct = $featuredProducts->first() ?? $products->first();
    $heroImage = file_exists(public_path('product_images/produk1.jpg'))
        ? asset('product_images/produk1.jpg')
        : ($heroProduct && $heroProduct->product_image
            ? asset('product_images/' . $heroProduct->product_image)
            : 'https://placehold.co/800x600/eaf2ec/244b35?text=Greenova');

    $catCount = $categories->count();
    $catColClass = match (true) {
        $catCount === 1 => 'col-12',
        $catCount === 2 => 'col-12 col-md-6',
        $catCount === 3 => 'col-12 col-md-4',
        default         => 'col-6 col-md-4 col-lg-3',
    };
@endphp

<!-- Hero Section (2 Kolom Simetris Kiri & Kanan) -->
<section class="py-4 py-lg-5" style="background: linear-gradient(180deg, #EFECE4 0%, #F7F6F2 100%); border-bottom: 1px solid var(--gn-border-warm);">
    <div class="container">
        <div class="row align-items-center g-4 g-xl-5">
            <!-- Kolom Kiri: Teks & Tombol -->
            <div class="col-lg-6 d-flex flex-column justify-content-center">
                <div>
                    <span class="gn-badge gn-badge-green mb-3">Sustainable Lifestyle Store</span>
                    <h1 class="fw-bold mb-3" style="font-size: clamp(2rem, 3.1vw, 2.85rem); line-height: 1.18; letter-spacing: -0.02em; color: var(--gn-primary);">
                        Hidup Lebih Hijau Dimulai dari Pilihan Sehari-Hari.
                    </h1>
                    <p class="mb-4" style="font-size: 15.5px; line-height: 1.75; color: var(--gn-text-secondary); max-width: 620px;">
                        Temukan perlengkapan rumah tangga, kebutuhan harian, dan fashion yang dirancang untuk mengurangi limbah sekali pakai tanpa mengorbankan kenyamanan dan estetika.
                    </p>
                    <div class="d-flex flex-wrap align-items-center gap-3 mb-4">
                        <a href="{{ route('products') }}" class="gn-btn gn-btn-primary gn-btn-lg">
                            Belanja Sekarang
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
                        </a>
                        <a href="#kategori-greenova" class="gn-btn gn-btn-outline gn-btn-lg">
                            Lihat Kategori
                        </a>
                    </div>

                    <!-- 3 Poin Keunggulan Sejajar Simetris di Bawah Tombol -->
                    <div class="row g-2 pt-3 border-top" style="border-color: rgba(36, 75, 53, 0.12) !important;">
                        <div class="col-4">
                            <div class="fw-bold small" style="color: var(--gn-primary);">Ramah Lingkungan</div>
                            <div class="text-muted" style="font-size: 12px;">Material pilihan berkelanjutan</div>
                        </div>
                        <div class="col-4">
                            <div class="fw-bold small" style="color: var(--gn-primary);">Stok &amp; Harga Real-Time</div>
                            <div class="text-muted" style="font-size: 12px;">Diskon dihitung otomatis</div>
                        </div>
                        <div class="col-4">
                            <div class="fw-bold small" style="color: var(--gn-primary);">Ulasan Terverifikasi</div>
                            <div class="text-muted" style="font-size: 12px;">Dari transaksi selesai</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Kolom Kanan: Kartu Foto Hero Proporsional & Simetris -->
            <div class="col-lg-6">
                <div class="bg-white p-3 rounded-4 border" style="border-color: var(--gn-border-warm) !important; box-shadow: var(--gn-shadow-md);">
                    <div class="rounded-3 overflow-hidden position-relative" style="height: clamp(240px, 26vw, 350px); background: var(--gn-surface);">
                        <img
                            src="{{ $heroImage }}"
                            alt="{{ $heroProduct->product_title ?? 'Koleksi Greenova' }}"
                            class="w-100 h-100"
                            style="object-fit: cover; object-position: center;"
                            onerror="this.onerror=null; this.src='https://placehold.co/800x500/eaf2ec/244b35?text=Greenova';">

                        <span class="position-absolute bottom-0 start-0 m-3 px-3 py-1 rounded-pill small fw-semibold bg-white text-dark shadow-sm d-inline-flex align-items-center gap-2">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" class="text-success"><path d="M11 20A7 7 0 0 1 9.8 6.1C15.5 5 17 4.48 19 2c1 2 2 4.18 2 8 0 5.5-4.78 10-10 10Z"/><path d="M2 21c0-3 1.85-5.36 5.08-6C9.5 14.52 12 13 13 12"/></svg>
                            Produk Pilihan
                        </span>
                    </div>

                    @if($heroProduct)
                        <div class="d-flex align-items-center justify-content-between gap-3 pt-3 px-1">
                            <div class="min-w-0">
                                <div class="small fw-semibold" style="color: var(--gn-accent); font-size: 11.5px;">
                                    {{ $heroProduct->category->cat_title ?? 'Katalog Greenova' }}
                                </div>
                                <div class="fw-bold text-dark text-truncate">{{ $heroProduct->product_title }}</div>
                                <div class="fw-bold" style="color: var(--gn-primary);">
                                    Rp {{ number_format($heroProduct->effectivePrice(), 0, ',', '.') }}
                                </div>
                            </div>
                            <a href="{{ route('product.detail', $heroProduct->product_id) }}" class="gn-btn gn-btn-secondary gn-btn-sm flex-shrink-0">
                                Lihat Produk
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Product Categories Section (Sejajar Penuh Kiri ke Kanan) -->
@if($categories->isNotEmpty())
<section id="kategori-greenova" class="py-5">
    <div class="container">
        <div class="d-flex flex-wrap align-items-end justify-content-between gap-3 mb-4">
            <div>
                <span class="gn-badge gn-badge-green mb-2">Kategori Produk</span>
                <h2 class="fw-bold mb-1" style="font-size: clamp(1.35rem, 2.2vw, 1.7rem); color: var(--gn-primary);">Jelajahi Berdasarkan Kebutuhan</h2>
                <p class="mb-0 text-muted small">Telusuri produk berdasarkan kategori kebutuhan Anda.</p>
            </div>
            <a href="{{ route('products') }}" class="gn-btn gn-btn-outline gn-btn-sm">Lihat Semua</a>
        </div>

        <div class="row g-3 g-md-4">
            @foreach($categories as $cat)
                @php
                    $catSample = $cat->relationLoaded('products')
                        ? $cat->products->first()
                        : \App\Models\Product::where('product_cat', $cat->cat_id)->whereNotNull('product_image')->first();
                @endphp
                <div class="{{ $catColClass }}">
                    <a href="{{ route('products', ['category' => $cat->cat_id]) }}"
                       class="d-flex flex-column bg-white rounded-4 border overflow-hidden text-decoration-none h-100"
                       style="border-color: var(--gn-border-warm) !important; box-shadow: var(--gn-shadow-xs);">
                        <div style="height: 220px; background: var(--gn-surface); overflow: hidden;">
                            <img
                                src="{{ $catSample && $catSample->product_image ? asset('product_images/' . $catSample->product_image) : 'https://placehold.co/600x360/eaf2ec/244b35?text=' . urlencode($cat->cat_title) }}"
                                alt="{{ $cat->cat_title }}"
                                loading="lazy"
                                class="w-100 h-100"
                                style="object-fit: cover; object-position: center;"
                                onerror="this.onerror=null; this.src='https://placehold.co/600x360/eaf2ec/244b35?text=Greenova';">
                        </div>
                        <div class="p-3 d-flex align-items-center justify-content-between gap-2 mt-auto">
                            <div class="min-w-0">
                                <h3 class="h6 fw-bold mb-0 text-truncate text-dark">{{ $cat->cat_title }}</h3>
                                <span class="small text-muted">{{ $cat->products_count }} produk</span>
                            </div>
                            <span class="small fw-semibold flex-shrink-0" style="color: var(--gn-accent); font-size: 12px;">
                                Lihat Koleksi &rarr;
                            </span>
                        </div>
                    </a>
                </div>
            @endforeach
        </div>
    </div>
</section>
@endif

<!-- Featured Products Section -->
@if(isset($featuredProducts) && $featuredProducts->isNotEmpty())
<section class="py-5 bg-white border-top border-bottom" style="border-color: var(--gn-border-warm) !important;">
    <div class="container">
        <div class="d-flex flex-wrap align-items-end justify-content-between gap-3 mb-4">
            <div>
                <span class="gn-badge gn-badge-green mb-2">Koleksi Unggulan</span>
                <h2 class="fw-bold mb-1" style="font-size: clamp(1.35rem, 2.2vw, 1.7rem); color: var(--gn-primary);">Produk Pilihan Greenova</h2>
                <p class="mb-0 text-muted small">Koleksi produk ramah lingkungan terkurasi yang siap dipesan.</p>
            </div>
            <a href="{{ route('products') }}" class="gn-btn gn-btn-outline gn-btn-sm">
                Buka Katalog Lengkap
            </a>
        </div>

        <div class="gn-product-grid">
            @foreach($featuredProducts as $product)
                @include('partials.product-card', ['product' => $product])
            @endforeach
        </div>
    </div>
</section>
@endif

<!-- Promotional Discounts Section -->
@if(isset($activePromos) && $activePromos->isNotEmpty())
<section class="py-5" style="background: #FAF8F3; border-bottom: 1px solid var(--gn-border-warm);">
    <div class="container">
        <div class="d-flex flex-wrap align-items-end justify-content-between gap-3 mb-4">
            <div>
                <span class="gn-badge gn-badge-red mb-2">Penawaran Spesial</span>
                <h2 class="fw-bold mb-1" style="font-size: clamp(1.35rem, 2.2vw, 1.7rem); color: var(--gn-primary);">Promo &amp; Diskon Hari Ini</h2>
                <p class="mb-0 text-muted small">Potongan harga langsung untuk produk ramah lingkungan pilihan.</p>
            </div>
            <a href="{{ route('products', ['sort' => 'price_asc']) }}" class="gn-btn gn-btn-outline gn-btn-sm">Lihat Semua Promo</a>
        </div>

        <div class="gn-product-grid">
            @foreach($activePromos as $promo)
                @if($promo->product)
                    @include('partials.product-card', ['product' => $promo->product])
                @endif
            @endforeach
        </div>
    </div>
</section>
@endif

<!-- New Arrivals Section -->
@if(isset($newArrivals) && $newArrivals->isNotEmpty())
<section class="py-5 bg-white border-bottom" style="border-color: var(--gn-border-warm) !important;">
    <div class="container">
        <div class="d-flex flex-wrap align-items-end justify-content-between gap-3 mb-4">
            <div>
                <span class="gn-badge gn-badge-green mb-2">Koleksi Terbaru</span>
                <h2 class="fw-bold mb-1" style="font-size: clamp(1.35rem, 2.2vw, 1.7rem); color: var(--gn-primary);">Produk Baru Masuk</h2>
                <p class="mb-0 text-muted small">Inovasi produk berkelanjutan terbaru di katalog Greenova.</p>
            </div>
            <a href="{{ route('products', ['sort' => 'latest']) }}" class="gn-btn gn-btn-primary gn-btn-sm">
                Lihat Katalog Lengkap
            </a>
        </div>

        <div class="gn-product-grid">
            @foreach($newArrivals as $product)
                @include('partials.product-card', ['product' => $product])
            @endforeach
        </div>
    </div>
</section>
@endif

<!-- Brand Information Section (2 Kolom Simetris dalam Kartu) -->
<section class="py-5">
    <div class="container">
        <div class="bg-white rounded-4 border p-4 p-lg-5" style="border-color: var(--gn-border-warm) !important;">
            <div class="row align-items-center g-4">
                <div class="col-lg-6">
                    <span class="gn-badge gn-badge-green mb-3">Tentang Greenova</span>
                    <h2 class="fw-bold mb-3" style="font-size: clamp(1.35rem, 2.2vw, 1.7rem); color: var(--gn-primary);">
                        Belanja Lebih Sadar, Mudah, dan Transparan.
                    </h2>
                    <p class="mb-0" style="color: var(--gn-text-secondary); line-height: 1.75; font-size: 14.5px;">
                        Greenova menghadirkan produk kebutuhan rumah tangga, gaya hidup, dan fashion yang lebih ramah lingkungan dalam satu platform. Setiap produk dilengkapi informasi stok real-time, harga transparan, serta ulasan asli dari pembeli yang telah menyelesaikan transaksi.
                    </p>
                </div>
                <div class="col-lg-6">
                    <div class="row g-3">
                        <div class="col-sm-4">
                            <div class="p-3 rounded-3 h-100" style="background: var(--gn-ivory, #F9F8F5);">
                                <div class="fw-bold mb-1" style="color: var(--gn-primary); font-size: 14px;">Harga Transparan</div>
                                <div class="small text-muted">Diskon produk &amp; voucher dihitung otomatis saat checkout.</div>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="p-3 rounded-3 h-100" style="background: var(--gn-ivory, #F9F8F5);">
                                <div class="fw-bold mb-1" style="color: var(--gn-primary); font-size: 14px;">Pembayaran Aman</div>
                                <div class="small text-muted">Mendukung pembayaran online via Midtrans Snap.</div>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="p-3 rounded-3 h-100" style="background: var(--gn-ivory, #F9F8F5);">
                                <div class="fw-bold mb-1" style="color: var(--gn-primary); font-size: 14px;">Ulasan Terverifikasi</div>
                                <div class="small text-muted">Hanya pembeli pesanan selesai yang dapat memberi ulasan.</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
