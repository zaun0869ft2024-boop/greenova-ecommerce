@extends('layouts.app')

@section('title', $product->product_title . ' — Greenova')

@section('content')
@php
    $avg   = $product->averageRating();
    $count = $product->reviewsCount();
    $detailDiscount = $product->activeDiscount();
    $detailEffective = $product->effectivePrice();
    $detailBase = (int) $product->product_price;
    $detailHasDiscount = $detailDiscount && $detailEffective < $detailBase;
    $detailPct = $detailHasDiscount ? $detailDiscount->discountPercent($detailBase) : 0;
    $detailSold = $product->soldCount();
    $inStock = (int) ($product->stock ?? 0) > 0;
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

        <nav aria-label="Breadcrumb" class="mb-4">
            <div class="d-flex flex-wrap align-items-center gap-2 small text-muted">
                <a href="{{ route('home') }}" class="text-decoration-none text-muted">Beranda</a>
                <span>/</span>
                <a href="{{ route('products') }}" class="text-decoration-none text-muted">Produk</a>
                @if($product->category)
                    <span>/</span>
                    <a href="{{ route('products', ['category' => $product->category->cat_id]) }}" class="text-decoration-none fw-semibold" style="color: var(--gn-accent);">
                        {{ $product->category->cat_title }}
                    </a>
                @endif
            </div>
        </nav>

        <div class="bg-white rounded-4 border p-3 p-md-4 p-lg-5 mb-5" style="border-color: var(--gn-border-warm) !important; box-shadow: var(--gn-shadow-sm);">
            <div class="row g-4 g-lg-5 align-items-start">

                <div class="col-lg-6">
                    <div class="rounded-4 overflow-hidden border position-relative" style="aspect-ratio: 4 / 3; background: var(--gn-surface); border-color: var(--gn-border-warm) !important;">
                        <img
                            src="{{ asset('product_images/' . $product->product_image) }}"
                            alt="{{ $product->product_title }}"
                            class="w-100 h-100"
                            style="object-fit: cover;"
                            onerror="this.onerror=null; this.src='https://placehold.co/800x600/eaf2ec/244b35?text=Greenova';">

                        @if($detailHasDiscount && $inStock)
                            <span class="gn-product-discount-tag" style="top: 14px; left: 14px; font-size: 12px; padding: 6px 12px;">
                                Hemat {{ $detailPct }}%
                            </span>
                        @endif
                    </div>
                </div>

                <div class="col-lg-6">
                    @if($product->category)
                        <a href="{{ route('products', ['category' => $product->category->cat_id]) }}" class="gn-badge gn-badge-green text-decoration-none mb-2">
                            {{ $product->category->cat_title }}
                        </a>
                    @endif

                    <h1 class="fw-bold mb-3" style="font-size: clamp(1.5rem, 2.5vw, 2rem); color: var(--gn-charcoal); line-height: 1.25;">
                        {{ $product->product_title }}
                    </h1>

                    <div class="d-flex flex-wrap align-items-center gap-3 mb-3 pb-3 border-bottom" style="border-color: var(--gn-border-warm) !important;">
                        @if($count > 0)
                            <a href="#customer-reviews" class="d-inline-flex align-items-center gap-2 text-decoration-none text-dark">
                                <span style="color: var(--gn-gold); font-size: 15px;">★</span>
                                <span class="fw-bold">{{ number_format($avg, 1) }}</span>
                                <span class="text-muted small">({{ $count }} ulasan)</span>
                            </a>
                        @else
                            <span class="text-muted small">Belum ada ulasan</span>
                        @endif

                        @if($detailSold > 0)
                            <span class="text-muted small">•</span>
                            <span class="small text-muted">Terjual <strong>{{ $detailSold }}</strong></span>
                        @endif

                        <span class="text-muted small">•</span>
                        @if($inStock)
                            <span class="gn-badge gn-badge-green">Stok: {{ $product->stock }} unit</span>
                        @else
                            <span class="gn-badge gn-badge-red">Stok Habis</span>
                        @endif
                    </div>

                    <div class="mb-4">
                        @if($detailHasDiscount)
                            <div class="d-flex align-items-center gap-2 mb-1">
                                <span class="text-muted text-decoration-line-through small">
                                    Rp {{ number_format($detailBase, 0, ',', '.') }}
                                </span>
                                <span class="gn-badge gn-badge-red">-{{ $detailPct }}%</span>
                            </div>
                            <div class="fw-bold" style="font-size: 28px; color: var(--gn-primary);">
                                Rp {{ number_format($detailEffective, 0, ',', '.') }}
                            </div>
                        @else
                            <div class="fw-bold" style="font-size: 28px; color: var(--gn-primary);">
                                Rp {{ number_format($detailEffective, 0, ',', '.') }}
                            </div>
                        @endif
                    </div>

                    <div class="mb-4">
                        <label for="quantity" class="form-label small fw-semibold text-secondary">Jumlah Pembelian</label>
                        <div class="d-inline-flex align-items-center border rounded-3 bg-white ms-2" style="border-color: var(--gn-border) !important;">
                            <button type="button" class="btn btn-sm px-3 py-2 border-0" id="btn-decrement" aria-label="Kurangi jumlah" {{ !$inStock ? 'disabled' : '' }}>−</button>
                            <input
                                type="number"
                                id="quantity"
                                value="1"
                                min="1"
                                max="{{ $product->stock ?? 0 }}"
                                class="form-control form-control-sm text-center border-0 fw-bold"
                                style="width: 56px;"
                                {{ !$inStock ? 'disabled' : '' }}>
                            <button type="button" class="btn btn-sm px-3 py-2 border-0" id="btn-increment" aria-label="Tambah jumlah" {{ !$inStock ? 'disabled' : '' }}>+</button>
                        </div>
                        <p id="stock-warning" class="text-danger mt-2 mb-0 small" role="alert" hidden>
                            Stok hanya tersedia {{ $product->stock ?? 0 }}.
                        </p>
                    </div>

                    <div class="d-flex flex-wrap gap-2 mb-4">
                        @auth
                            <form action="{{ route('add.cart', $product->product_id) }}" method="POST" id="form-add-cart" class="flex-grow-1" style="min-width: 180px;">
                                @csrf
                                <input type="hidden" name="quantity" id="add-cart-qty" value="1">
                                <button type="submit" id="add-cart-button" class="gn-btn gn-btn-outline gn-btn-lg w-100" {{ !$inStock ? 'disabled' : '' }}>
                                    {{ $inStock ? '+ Tambah ke Keranjang' : 'Stok Habis' }}
                                </button>
                            </form>

                            <form action="{{ route('buy.now', $product->product_id) }}" method="POST" id="form-buy-now" class="flex-grow-1" style="min-width: 180px;">
                                @csrf
                                <input type="hidden" name="quantity" id="buy-now-qty" value="1">
                                <button type="submit" id="buy-now-button" class="gn-btn gn-btn-primary gn-btn-lg w-100" {{ !$inStock ? 'disabled' : '' }}>
                                    Beli Sekarang
                                </button>
                            </form>
                        @else
                            @if($inStock)
                                <a href="{{ route('login') }}" class="gn-btn gn-btn-outline gn-btn-lg flex-grow-1">+ Tambah ke Keranjang</a>
                                <a href="{{ route('login') }}" class="gn-btn gn-btn-primary gn-btn-lg flex-grow-1">Beli Sekarang</a>
                            @else
                                <button type="button" class="gn-btn gn-btn-outline gn-btn-lg flex-grow-1" disabled>Stok Habis</button>
                                <button type="button" class="gn-btn gn-btn-primary gn-btn-lg flex-grow-1" disabled>Beli Sekarang</button>
                            @endif
                        @endauth
                    </div>

                    <div class="pt-3 border-top" style="border-color: var(--gn-border-warm) !important;">
                        <h2 class="h6 fw-bold mb-2" style="color: var(--gn-primary);">Deskripsi Produk</h2>
                        <p class="mb-0 small" style="color: var(--gn-text-secondary); line-height: 1.75; white-space: pre-line;">
                            {{ $product->product_desc ?? 'Produk ramah lingkungan pilihan Greenova yang dirancang untuk mendukung gaya hidup berkelanjutan.' }}
                        </p>
                    </div>
                </div>

            </div>
        </div>

        <section id="customer-reviews" class="mb-5">
            <div class="d-flex flex-wrap align-items-end justify-content-between gap-3 mb-4">
                <div>
                    <span class="gn-badge gn-badge-green mb-2">Ulasan Terverifikasi</span>
                    <h2 class="h4 fw-bold mb-0" style="color: var(--gn-primary);">Ulasan &amp; Rating Pembeli</h2>
                </div>
            </div>

            <div class="bg-white rounded-4 border p-4 mb-4" style="border-color: var(--gn-border-warm) !important;">
                <div class="row align-items-center g-4">
                    <div class="col-md-4 text-center text-md-start border-end-md">
                        <div class="fw-bold" style="font-size: 40px; line-height: 1; color: var(--gn-primary);">
                            {{ $count > 0 ? number_format($avg, 1) : '0.0' }}
                        </div>
                        <div class="my-2" style="font-size: 18px;">
                            @for($s = 1; $s <= 5; $s++)
                                <span style="color: {{ $s <= round($avg) ? 'var(--gn-gold)' : '#D1D5DB' }};">★</span>
                            @endfor
                        </div>
                        <div class="small text-muted">
                            @if($count > 0)
                                Berdasarkan <strong>{{ $count }}</strong> ulasan pembeli
                            @else
                                Belum ada ulasan untuk produk ini
                            @endif
                        </div>
                    </div>
                    <div class="col-md-8">
                        <div class="p-3 rounded-3" style="background: var(--gn-ivory);">
                            <div class="fw-bold small mb-1" style="color: var(--gn-primary);">Informasi Penilaian</div>
                            @if(isset($userCompletedOrder) && $userCompletedOrder)
                                <p class="small text-muted mb-2" style="line-height: 1.6;">
                                    Anda telah menyelesaikan pesanan untuk produk ini. Bagikan pengalaman Anda untuk membantu pembeli lain!
                                </p>
                                @if(isset($userExistingReview) && $userExistingReview)
                                    <button type="button"
                                            class="gn-btn gn-btn-outline gn-btn-sm btn-review-trigger"
                                            data-order-id="{{ $userCompletedOrder->orders_id }}"
                                            data-product-id="{{ $product->product_id }}"
                                            data-product-title="{{ $product->product_title }}"
                                            data-product-image="{{ $product->product_image ? asset('product_images/' . $product->product_image) : '' }}"
                                            data-is-edit="1"
                                            data-review-id="{{ $userExistingReview->id }}"
                                            data-rating="{{ $userExistingReview->rating }}"
                                            data-comment="{{ $userExistingReview->comment ?? '' }}"
                                            data-store-url="{{ route('product.review.store', $product->product_id) }}"
                                            data-update-url="{{ route('review.update', $userExistingReview->id) }}">
                                        <span style="color: var(--gn-gold);">★</span> {{ $userExistingReview->rating }}/5 • Ubah Penilaian Saya
                                    </button>
                                @else
                                    <button type="button"
                                            class="gn-btn gn-btn-primary gn-btn-sm btn-review-trigger"
                                            data-order-id="{{ $userCompletedOrder->orders_id }}"
                                            data-product-id="{{ $product->product_id }}"
                                            data-product-title="{{ $product->product_title }}"
                                            data-product-image="{{ $product->product_image ? asset('product_images/' . $product->product_image) : '' }}"
                                            data-is-edit="0"
                                            data-review-id=""
                                            data-rating="5"
                                            data-comment=""
                                            data-store-url="{{ route('product.review.store', $product->product_id) }}"
                                            data-update-url="">
                                        ★ Beri Penilaian Produk Ini
                                    </button>
                                @endif
                            @else
                                <p class="small text-muted mb-0" style="line-height: 1.6;">
                                    Penilaian dan ulasan hanya dapat diberikan oleh pelanggan melalui menu <strong>Pesanan Saya</strong> setelah transaksi berstatus <strong>Selesai</strong>.
                                </p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            @forelse($reviews as $review)
                <div class="bg-white rounded-4 border p-4 mb-3" style="border-color: var(--gn-border-warm) !important;">
                    <div class="d-flex justify-content-between align-items-start gap-3 mb-2">
                        <div class="d-flex align-items-center gap-3">
                            <span class="gn-avatar-dot" style="width: 36px; height: 36px;">
                                {{ strtoupper(substr($review->user->name ?? 'P', 0, 1)) }}
                            </span>
                            <div>
                                <div class="fw-bold small">{{ $review->user->name ?? 'Pelanggan Greenova' }}</div>
                                <span class="gn-badge gn-badge-green" style="font-size: 10.5px;">Pembeli Terverifikasi</span>
                            </div>
                        </div>
                        <span class="small text-muted">{{ $review->created_at->format('d M Y') }}</span>
                    </div>

                    <div class="mb-2">
                        @for($i = 1; $i <= 5; $i++)
                            <span style="color: {{ $i <= $review->rating ? 'var(--gn-gold)' : '#D1D5DB' }};">★</span>
                        @endfor
                        <span class="small fw-semibold ms-1">{{ $review->rating }}/5</span>
                    </div>

                    @if($review->comment)
                        <p class="small mb-0" style="color: var(--gn-text-secondary); line-height: 1.65;">{{ $review->comment }}</p>
                    @else
                        <p class="small text-muted fst-italic mb-0">Pengguna tidak menyertakan ulasan tertulis.</p>
                    @endif
                </div>
            @empty
                <div class="bg-white rounded-4 border p-4 text-center" style="border-color: var(--gn-border-warm) !important;">
                    <div class="fw-bold mb-1" style="color: var(--gn-primary);">Belum Ada Ulasan</div>
                    <p class="small text-muted mb-0">Ulasan akan tampil di sini setelah pembeli menyelesaikan pesanannya.</p>
                </div>
            @endforelse
        </section>

        @if(isset($relatedProducts) && $relatedProducts->isNotEmpty())
            <section class="pt-4 border-top" style="border-color: var(--gn-border-warm) !important;">
                <div class="d-flex align-items-center justify-content-between mb-4">
                    <h2 class="h4 fw-bold mb-0" style="color: var(--gn-primary);">Produk Serupa</h2>
                    @if($product->category)
                        <a href="{{ route('products', ['category' => $product->category->cat_id]) }}" class="gn-btn gn-btn-outline gn-btn-sm">
                            Lihat Kategori
                        </a>
                    @endif
                </div>
                <div class="gn-product-grid">
                    @foreach($relatedProducts as $rel)
                        @include('partials.product-card', ['product' => $rel])
                    @endforeach
                </div>
            </section>
        @endif

    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const quantityInput  = document.getElementById('quantity');
    const warning        = document.getElementById('stock-warning');
    const addCartButton  = document.getElementById('add-cart-button');
    const buyNowButton   = document.getElementById('buy-now-button');
    const addCartQty     = document.getElementById('add-cart-qty');
    const buyNowQty      = document.getElementById('buy-now-qty');
    const btnDecrement   = document.getElementById('btn-decrement');
    const btnIncrement   = document.getElementById('btn-increment');

    if (quantityInput) {
        const stock = Number(quantityInput.max || 0);

        function syncQuantity(val) {
            let qty = Number(val);
            if (isNaN(qty) || qty < 1) qty = 1;
            if (stock > 0 && qty > stock) qty = stock;
            quantityInput.value = qty;
            if (addCartQty) addCartQty.value = qty;
            if (buyNowQty)  buyNowQty.value  = qty;

            const invalid = stock < 1 || qty < 1 || qty > stock;
            if (warning) {
                warning.hidden = !invalid;
                if (qty > stock && stock > 0)
                    warning.textContent = 'Stok hanya tersedia ' + stock + '.';
                else if (stock < 1)
                    warning.textContent = 'Stok produk ini sedang habis.';
            }
            if (addCartButton) addCartButton.disabled = invalid;
            if (buyNowButton)  buyNowButton.disabled  = invalid;
            if (btnDecrement)  btnDecrement.disabled  = qty <= 1 || stock < 1;
            if (btnIncrement)  btnIncrement.disabled  = qty >= stock || stock < 1;
        }

        if (btnDecrement) btnDecrement.addEventListener('click', () => syncQuantity(Number(quantityInput.value) - 1));
        if (btnIncrement) btnIncrement.addEventListener('click', () => syncQuantity(Number(quantityInput.value) + 1));
        quantityInput.addEventListener('input', function () { syncQuantity(this.value); });

        const formAddCart = document.getElementById('form-add-cart');
        const formBuyNow  = document.getElementById('form-buy-now');
        if (formAddCart) formAddCart.addEventListener('submit', () => { if (addCartQty) addCartQty.value = quantityInput.value || 1; });
        if (formBuyNow)  formBuyNow.addEventListener('submit',  () => { if (buyNowQty) buyNowQty.value = quantityInput.value || 1; });

        syncQuantity(quantityInput.value);
    }
});
</script>

@include('order.review-modal')
@endsection
