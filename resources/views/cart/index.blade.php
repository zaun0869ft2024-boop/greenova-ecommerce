@extends('layouts.app')

@section('title', 'Keranjang Belanja — Greenova')

@section('content')
<div class="py-4 py-lg-5">
    <div class="container">

        <div class="d-flex flex-wrap align-items-end justify-content-between gap-3 mb-4">
            <div>
                <span class="gn-badge gn-badge-green mb-2">Keranjang Belanja</span>
                <h1 class="fw-bold mb-1" style="font-size: clamp(1.6rem, 2.8vw, 2.1rem); color: var(--gn-primary);">
                    Keranjang Saya
                </h1>
                <p class="mb-0 text-muted small">Pilih produk yang ingin Anda lanjutkan ke proses checkout.</p>
            </div>
            <span class="gn-badge gn-badge-neutral" style="padding: 8px 14px; font-size: 13px;">
                {{ count($cart) }} jenis produk
            </span>
        </div>

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

        @if(count($cart) > 0)
            <div class="row g-4 align-items-start">
                <div class="col-lg-8">
                    <div class="bg-white rounded-4 border p-3 p-md-4 mb-3" style="border-color: var(--gn-border-warm) !important;">
                        <label class="d-flex align-items-center gap-2 fw-semibold small mb-0" style="cursor: pointer; color: var(--gn-primary);">
                            <input
                                type="checkbox"
                                id="select-all"
                                class="form-check-input mt-0"
                                onchange="toggleAllProducts(this)">
                            <span>Pilih semua produk ({{ count($cart) }})</span>
                        </label>
                    </div>

                    <div class="d-flex flex-column gap-3">
                        @foreach($cart as $id => $item)
                            @php
                                $subtotal = (float) $item['price'] * (int) $item['quantity'];
                                $hasDisc = ($item['base_price'] ?? $item['price']) > $item['price'];
                            @endphp
                            <div class="bg-white rounded-4 border p-3 p-md-4" style="border-color: var(--gn-border-warm) !important;">
                                <div class="d-flex flex-column flex-sm-row align-items-start align-items-sm-center gap-3">
                                    <div class="d-flex align-items-center gap-3 flex-grow-1 w-100">
                                        <input
                                            type="checkbox"
                                            class="product-check form-check-input mt-0 flex-shrink-0"
                                            name="selected_products[]"
                                            value="{{ $id }}"
                                            form="checkout-selection-form"
                                            data-price="{{ $item['price'] }}"
                                            data-quantity="{{ $item['quantity'] }}"
                                            aria-label="Pilih {{ $item['name'] }}"
                                            {{ ((int) ($item['stock'] ?? 0)) >= ((int) $item['quantity']) ? 'checked' : '' }}
                                            onchange="updateSelectedTotal()">

                                        <a href="{{ route('product.detail', $id) }}" class="rounded-3 overflow-hidden border flex-shrink-0" style="width: 84px; height: 84px; background: var(--gn-surface); border-color: var(--gn-border-warm) !important;">
                                            <img
                                                src="{{ asset('product_images/' . $item['image']) }}"
                                                alt="{{ $item['name'] }}"
                                                class="w-100 h-100"
                                                style="object-fit: cover;"
                                                onerror="this.onerror=null; this.src='https://placehold.co/200x200/eaf2ec/244b35?text=Greenova';">
                                        </a>

                                        <div class="flex-grow-1 min-w-0">
                                            <h3 class="h6 fw-bold mb-1 text-truncate">
                                                <a href="{{ route('product.detail', $id) }}" class="text-decoration-none text-dark">
                                                    {{ $item['name'] }}
                                                </a>
                                            </h3>

                                            <div class="mb-2">
                                                @if($hasDisc)
                                                    <span class="small text-muted text-decoration-line-through me-1">
                                                        Rp {{ number_format($item['base_price'], 0, ',', '.') }}
                                                    </span>
                                                @endif
                                                <span class="fw-bold small" style="color: var(--gn-primary);">
                                                    Rp {{ number_format($item['price'], 0, ',', '.') }}
                                                </span>
                                                <span class="small text-muted ms-2">• Stok {{ $item['stock'] ?? 0 }}</span>
                                            </div>

                                            <div class="d-flex flex-wrap align-items-center gap-2">
                                                <form action="{{ route('update.cart', $id) }}" method="POST" class="cart-quantity-form d-inline-flex align-items-center gap-2 m-0">
                                                    @csrf
                                                    <div class="d-inline-flex align-items-center border rounded-3 bg-white" style="border-color: var(--gn-border) !important;">
                                                        <button type="button" class="btn btn-sm px-2 py-1 border-0" onclick="stepCartQty('{{ $id }}', -1)" aria-label="Kurangi jumlah">−</button>
                                                        <input
                                                            type="number"
                                                            id="quantity-{{ $id }}"
                                                            name="quantity"
                                                            value="{{ $item['quantity'] }}"
                                                            min="1"
                                                            max="{{ $item['stock'] ?? 0 }}"
                                                            class="form-control form-control-sm text-center border-0 fw-semibold"
                                                            style="width: 48px;"
                                                            required>
                                                        <button type="button" class="btn btn-sm px-2 py-1 border-0" onclick="stepCartQty('{{ $id }}', 1)" aria-label="Tambah jumlah">+</button>
                                                    </div>
                                                    <button type="submit" class="gn-btn gn-btn-outline gn-btn-sm">
                                                        Update
                                                    </button>
                                                </form>

                                                <form action="{{ route('remove.cart', $id) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus produk ini dari keranjang?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit"
                                                            class="gn-btn gn-btn-sm text-danger border-0"
                                                            style="background: var(--gn-danger-soft);">
                                                        Hapus
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="text-sm-end ms-sm-auto pt-2 pt-sm-0 border-top border-sm-0 w-100 w-sm-auto" style="border-color: var(--gn-border-warm) !important;">
                                        <div class="small text-muted">Subtotal</div>
                                        <div class="fw-bold" style="font-size: 16px; color: var(--gn-primary);">
                                            Rp {{ number_format($subtotal, 0, ',', '.') }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="bg-white rounded-4 border p-4 position-sticky" style="top: 88px; border-color: var(--gn-border-warm) !important; box-shadow: var(--gn-shadow-sm);">
                        <span class="gn-badge gn-badge-green mb-2">Ringkasan Belanja</span>
                        <h2 class="h5 fw-bold mb-3" style="color: var(--gn-primary);">Total Pilihan</h2>

                        <div class="d-flex justify-content-between align-items-center small text-muted mb-2">
                            <span>Produk dipilih</span>
                            <strong id="selected-count" class="text-dark">0 produk dipilih</strong>
                        </div>

                        <hr style="border-color: var(--gn-border-warm);">

                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <span class="fw-semibold">Total sementara</span>
                            <strong id="selected-total" style="font-size: 22px; color: var(--gn-primary);">Rp 0</strong>
                        </div>

                        <form id="checkout-selection-form" action="{{ route('checkout.select') }}" method="POST" class="mb-2">
                            @csrf
                            <button type="submit" id="checkout-button" class="gn-btn gn-btn-primary gn-btn-lg w-100" disabled>
                                Lanjut Checkout
                            </button>
                        </form>

                        <a href="{{ route('products') }}" class="gn-btn gn-btn-outline w-100">
                            Lanjut Belanja
                        </a>
                    </div>
                </div>
            </div>
        @else
            <div class="bg-white rounded-4 border p-5 text-center" style="border-color: var(--gn-border-warm) !important;">
                <div class="mx-auto mb-3 d-inline-flex align-items-center justify-content-center rounded-circle"
                     style="width: 60px; height: 60px; background: var(--gn-accent-soft); color: var(--gn-primary);">
                    <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="8" cy="21" r="1"/><circle cx="19" cy="21" r="1"/>
                        <path d="M2.05 2.05h2l2.66 12.42a2 2 0 0 0 2 1.58h9.78a2 2 0 0 0 1.95-1.57l1.65-7.43H5.12"/>
                    </svg>
                </div>
                <h2 class="h5 fw-bold mb-2" style="color: var(--gn-primary);">Keranjang masih kosong</h2>
                <p class="small text-muted mb-4">Jelajahi katalog produk ramah lingkungan Greenova untuk mulai berbelanja.</p>
                <a href="{{ route('products') }}" class="gn-btn gn-btn-primary">
                    Mulai Belanja
                </a>
            </div>
        @endif

    </div>
</div>

<script>
function stepCartQty(id, delta) {
    const input = document.getElementById('quantity-' + id);
    if (!input) return;
    const max = Number(input.max || 999);
    let next = Number(input.value || 1) + delta;
    if (next < 1) next = 1;
    if (max > 0 && next > max) next = max;
    input.value = next;
    updateSelectedTotal();
}

function updateSelectedTotal() {
    let total = 0;
    let count = 0;
    let valid = true;

    const checkboxes = [...document.querySelectorAll('.product-check')];
    checkboxes.forEach(function(checkbox) {
        if (!checkbox.checked) return;
        const id = checkbox.value;
        const input = document.getElementById('quantity-' + id);
        const quantity = Number(input.value);
        const stock = Number(input.max || 0);
        count++;
        if (!Number.isInteger(quantity) || quantity < 1 || quantity > stock) {
            valid = false;
            return;
        }
        total += Number(checkbox.dataset.price) * quantity;
    });

    const totalElement = document.getElementById('selected-total');
    const countElement = document.getElementById('selected-count');
    const checkoutButton = document.getElementById('checkout-button');

    if (totalElement) totalElement.textContent = 'Rp ' + total.toLocaleString('id-ID');
    if (countElement) countElement.textContent = count + ' produk dipilih';
    if (checkoutButton) checkoutButton.disabled = count === 0 || !valid;

    const selectAll = document.getElementById('select-all');
    if (selectAll) {
        selectAll.checked = checkboxes.length > 0 && checkboxes.every(c => c.checked);
        selectAll.indeterminate = checkboxes.some(c => c.checked) && !selectAll.checked;
    }
}

function toggleAllProducts(source) {
    document.querySelectorAll('.product-check').forEach(function(checkbox) {
        checkbox.checked = source.checked;
    });
    updateSelectedTotal();
}

document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.cart-quantity-form input[name="quantity"]').forEach(function(input) {
        input.addEventListener('input', updateSelectedTotal);
    });
    updateSelectedTotal();
});
</script>
@endsection
