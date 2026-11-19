@php
    $discount = $product->activeDiscount();
    $effectivePrice = $product->effectivePrice();
    $basePrice = (int) $product->product_price;
    $hasDiscount = $discount && $effectivePrice < $basePrice;
    $pct = $hasDiscount ? $discount->discountPercent($basePrice) : 0;
    $sold = $product->soldCount();
    $avg = $product->averageRating();
    $cnt = $product->reviewsCount();
    $inStock = (int) ($product->stock ?? 0) > 0;
@endphp

<article class="gn-product-card">
    <a href="{{ route('product.detail', $product->product_id) }}" class="gn-product-thumb">
        <img
            src="{{ asset('product_images/' . $product->product_image) }}"
            alt="{{ $product->product_title }}"
            loading="lazy"
            onerror="this.onerror=null; this.src='https://placehold.co/600x450/eaf2ec/244b35?text=Greenova';">

        @if($hasDiscount && $inStock)
            <span class="gn-product-discount-tag">-{{ $pct }}%</span>
        @endif

        @if(!$inStock)
            <span class="gn-product-stock-tag">Stok Habis</span>
        @endif
    </a>

    <div class="gn-product-body">
        <div class="gn-product-cat">
            {{ $product->category->cat_title ?? 'Pilihan Greenova' }}
        </div>

        <h3 class="gn-product-title">
            <a href="{{ route('product.detail', $product->product_id) }}">
                {{ $product->product_title }}
            </a>
        </h3>

        <div class="gn-product-meta">
            @if($inStock)
                <span class="{{ (int) $product->stock <= 5 ? 'text-warning-emphasis fw-semibold' : '' }}">
                    Stok: {{ $product->stock }}
                </span>
            @else
                <span class="text-danger fw-semibold">Stok habis</span>
            @endif

            @if($cnt > 0)
                <span class="gn-rating-inline" title="{{ number_format($avg, 1) }} dari {{ $cnt }} ulasan">
                    <span class="gn-rating-star">★</span>
                    <span>{{ number_format($avg, 1) }}</span>
                    <span class="text-muted fw-normal">({{ $cnt }})</span>
                </span>
            @elseif($sold > 0)
                <span>Terjual {{ $sold }}</span>
            @endif
        </div>

        <div class="gn-product-price-row">
            @if($hasDiscount)
                <span class="gn-price-original">Rp {{ number_format($basePrice, 0, ',', '.') }}</span>
            @endif
            <span class="gn-price-current">Rp {{ number_format($effectivePrice, 0, ',', '.') }}</span>
        </div>

        <div class="gn-product-actions">
            @if($inStock)
                @auth
                    <form action="{{ route('add.cart', $product->product_id) }}" method="POST" class="w-100 m-0">
                        @csrf
                        <button type="submit" class="gn-btn gn-btn-primary gn-btn-sm w-100">
                            + Keranjang
                        </button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="gn-btn gn-btn-primary gn-btn-sm w-100">
                        + Keranjang
                    </a>
                @endauth
            @else
                <a href="{{ route('product.detail', $product->product_id) }}" class="gn-btn gn-btn-outline gn-btn-sm w-100">
                    Lihat Detail
                </a>
            @endif
        </div>
    </div>
</article>
