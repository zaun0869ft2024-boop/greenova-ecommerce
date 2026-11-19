@extends('layouts.app')

@section('title', ($activeCategory ? $activeCategory->cat_title . ' — ' : '') . 'Katalog Produk — Greenova')

@section('content')
<div class="py-4 py-lg-5">
    <div class="container">

        <div class="d-flex flex-wrap align-items-end justify-content-between gap-3 mb-4">
            <div>
                <h1 class="fw-bold mb-1" style="font-size: clamp(1.5rem, 2.4vw, 2rem); color: var(--gn-primary);">
                    {{ $activeCategory ? $activeCategory->cat_title : 'Katalog Produk' }}
                </h1>
                <p class="mb-0 text-muted small">
                    {{ $activeCategory && $activeCategory->cat_description
                        ? $activeCategory->cat_description
                        : 'Temukan pilihan produk ramah lingkungan untuk kebutuhan harian Anda.' }}
                </p>
            </div>

            <div class="gn-badge gn-badge-green" style="padding: 7px 14px; font-size: 13px;">
                {{ $products->total() }} Produk Ditemukan
            </div>
        </div>

        <!-- Primary Search & Filter Toolbar -->
        <div class="bg-white p-3 p-md-4 rounded-4 border mb-4" style="border-color: var(--gn-border-warm) !important; box-shadow: var(--gn-shadow-xs);">
            <form action="{{ route('products') }}" method="GET" id="catalogFilterForm" class="row g-3 align-items-end">
                <div class="col-12 col-lg-4">
                    <label for="catalog-search-input" class="form-label small fw-semibold text-muted mb-1">Cari Produk</label>
                    <input
                        type="search"
                        id="catalog-search-input"
                        name="search"
                        value="{{ $search ?? '' }}"
                        class="gn-input"
                        placeholder="Ketik nama atau deskripsi produk..."
                        aria-label="Cari produk">
                </div>

                <div class="col-6 col-lg-2">
                    <label for="catalog-category-select" class="form-label small fw-semibold text-muted mb-1">Kategori</label>
                    <select id="catalog-category-select" name="category" class="gn-select" aria-label="Kategori">
                        <option value="" {{ ($category ?? null) === null ? 'selected' : '' }}>Semua Kategori</option>
                        @foreach($categories as $item)
                            <option
                                value="{{ $item->cat_id }}"
                                {{ (string) ($category ?? '') === (string) $item->cat_id ? 'selected' : '' }}>
                                {{ $item->cat_title }} ({{ $item->products_count }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-6 col-lg-2">
                    <label for="catalog-sort-select" class="form-label small fw-semibold text-muted mb-1">Urutkan</label>
                    <select id="catalog-sort-select" name="sort" class="gn-select" aria-label="Urutkan Produk">
                        <option value="latest" {{ ($sort ?? 'latest') === 'latest' ? 'selected' : '' }}>Terbaru</option>
                        <option value="price_asc" {{ ($sort ?? 'latest') === 'price_asc' ? 'selected' : '' }}>Harga Terendah</option>
                        <option value="price_desc" {{ ($sort ?? 'latest') === 'price_desc' ? 'selected' : '' }}>Harga Tertinggi</option>
                        <option value="best_seller" {{ ($sort ?? 'latest') === 'best_seller' ? 'selected' : '' }}>Terlaris</option>
                        <option value="rating" {{ ($sort ?? 'latest') === 'rating' ? 'selected' : '' }}>Rating Tertinggi</option>
                    </select>
                </div>

                <div class="col-6 col-lg-2">
                    <label for="catalog-min-price" class="form-label small fw-semibold text-muted mb-1">Harga Min (Rp)</label>
                    <input type="number" id="catalog-min-price" name="min_price" value="{{ $minPrice ?? '' }}" class="gn-input" placeholder="0" min="0" step="1000">
                </div>

                <div class="col-6 col-lg-2">
                    <label for="catalog-max-price" class="form-label small fw-semibold text-muted mb-1">Harga Maks (Rp)</label>
                    <input type="number" id="catalog-max-price" name="max_price" value="{{ $maxPrice ?? '' }}" class="gn-input" placeholder="Tanpa batas" min="0" step="1000">
                </div>

                <div class="col-12 d-flex flex-wrap align-items-center justify-content-between gap-3 pt-2 border-top" style="border-color: #F1EFEA !important;">
                    <div class="d-flex flex-wrap align-items-center gap-3">
                        <div class="form-check form-switch mb-0">
                            <input class="form-check-input" type="checkbox" name="in_stock" value="1" id="inStockCheck" {{ !empty($inStock) ? 'checked' : '' }}>
                            <label class="form-check-label small fw-medium text-dark" for="inStockCheck">
                                Hanya tampilkan produk stok tersedia
                            </label>
                        </div>

                        @if(!empty($search))
                            <span class="badge bg-light text-dark border px-3 py-1 rounded-pill fw-medium d-inline-flex align-items-center gap-2">
                                <span>Pencarian: "{{ $search }}"</span>
                                <a href="{{ route('products', request()->except(['search', 'page'])) }}" class="text-danger text-decoration-none fw-bold" title="Hapus pencarian" aria-label="Hapus pencarian">&times;</a>
                            </span>
                        @endif
                    </div>

                    <div class="d-flex gap-2">
                        @if($search || $category || $minPrice || $maxPrice || $inStock || ($sort && $sort !== 'latest'))
                            <a href="{{ route('products') }}" class="gn-btn gn-btn-outline gn-btn-sm">
                                Reset Filter
                            </a>
                        @endif
                        <button type="submit" class="gn-btn gn-btn-primary gn-btn-sm px-4">
                            Terapkan Filter
                        </button>
                    </div>
                </div>
            </form>
        </div>

        @if($products->count() > 0)
            <div class="gn-product-grid">
                @foreach($products as $product)
                    @include('partials.product-card', ['product' => $product])
                @endforeach
            </div>

            @if($products->hasPages())
                <div class="catalog-pagination mt-5 d-flex justify-content-center">
                    {{ $products->links() }}
                </div>
            @endif
        @else
            <div class="bg-white rounded-4 border p-5 text-center" style="border-color: var(--gn-border-warm) !important;">
                <div class="mx-auto mb-3 d-inline-flex align-items-center justify-content-center rounded-circle"
                     style="width: 56px; height: 56px; background: var(--gn-accent-soft); color: var(--gn-primary);">
                    <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/>
                    </svg>
                </div>
                <h3 class="h5 fw-bold mb-2" style="color: var(--gn-primary);">Belum ada produk yang cocok</h3>
                <p class="text-muted small mb-4">Tidak ada produk yang memenuhi kriteria pencarian dan rentang harga saat ini.</p>
                <a href="{{ route('products') }}" class="gn-btn gn-btn-primary">
                    Tampilkan Semua Produk
                </a>
            </div>
        @endif

    </div>
</div>
@endsection
