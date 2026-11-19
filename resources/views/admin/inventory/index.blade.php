@extends('layouts.admin')

@section('title', 'Manajemen Inventori & Stok')
@section('page-title', 'Manajemen Inventori')

@section('content')
<div class="inventory-container">

    <!-- KPI Ringkasan Inventori -->
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-3">
            <div class="metric-card">
                <div class="metric-icon" style="background: #ECFDF5; color: #166534;">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/>
                        <polyline points="3.27 6.96 12 12.01 20.73 6.96"/>
                        <line x1="12" y1="22.08" x2="12" y2="12"/>
                    </svg>
                </div>
                <div class="metric-info">
                    <span class="metric-label">TOTAL UNIT STOK</span>
                    <strong class="metric-value">{{ number_format($totalItems, 0, ',', '.') }}</strong>
                    <span class="small text-muted">Seluruh produk</span>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="metric-card">
                <div class="metric-icon" style="background: #FEF3C7; color: #B45309;">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"/>
                        <line x1="12" y1="9" x2="12" y2="13"/>
                        <line x1="12" y1="17" x2="12.01" y2="17"/>
                    </svg>
                </div>
                <div class="metric-info">
                    <span class="metric-label">STOK MENIPIS (&le;10)</span>
                    <strong class="metric-value text-warning">{{ $lowStockCount }}</strong>
                    <span class="small text-muted">Perlu restock segera</span>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="metric-card">
                <div class="metric-icon" style="background: #FEE2E2; color: #B91C1C;">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10"/>
                        <line x1="4.93" y1="4.93" x2="19.07" y2="19.07"/>
                    </svg>
                </div>
                <div class="metric-info">
                    <span class="metric-label">STOK HABIS (0)</span>
                    <strong class="metric-value text-danger">{{ $outOfStockCount }}</strong>
                    <span class="small text-muted">Produk tidak bisa dibeli</span>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="metric-card">
                <div class="metric-icon" style="background: #EFF6FF; color: #1D4ED8;">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="12" y1="1" x2="12" y2="23"/>
                        <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>
                    </svg>
                </div>
                <div class="metric-info">
                    <span class="metric-label">ESTIMASI NILAI STOK</span>
                    <strong class="metric-value">Rp {{ number_format($totalStockValue, 0, ',', '.') }}</strong>
                    <span class="small text-muted">Berdasarkan harga dasar</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Alert Notifikasi -->

    <!-- Toolbar & Filter -->
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body p-3 p-lg-4">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-3">
                <div class="d-flex align-items-center gap-2">
                    <h5 class="fw-bold mb-0 text-dark">Daftar Stok Produk</h5>
                    <span class="badge bg-light text-dark border">{{ $products->total() }} Produk</span>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <a href="{{ route('admin.inventory.history') }}" class="btn btn-outline-success btn-sm rounded-3">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="me-1"><path d="M12 8v4l3 3"/><circle cx="12" cy="12" r="10"/></svg>
                        Riwayat Pergerakan Stok
                    </a>
                </div>
            </div>

            <form method="GET" action="{{ route('admin.inventory.index') }}" class="row g-2">
                <div class="col-md-5">
                    <input type="text" name="search" value="{{ $search }}" class="form-control form-control-sm rounded-3" placeholder="Cari nama produk...">
                </div>
                <div class="col-6 col-md-3">
                    <select name="category" class="form-select form-select-sm rounded-3" onchange="this.form.submit()">
                        <option value="">Semua Kategori</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->cat_id }}" {{ (string)$categoryId === (string)$cat->cat_id ? 'selected' : '' }}>
                                {{ $cat->cat_title }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-6 col-md-2">
                    <select name="stock_status" class="form-select form-select-sm rounded-3" onchange="this.form.submit()">
                        <option value="semua" {{ $stockFilter === 'semua' ? 'selected' : '' }}>Semua Stok</option>
                        <option value="low" {{ $stockFilter === 'low' ? 'selected' : '' }}>Stok Menipis (&le;10)</option>
                        <option value="out" {{ $stockFilter === 'out' ? 'selected' : '' }}>Stok Habis (0)</option>
                        <option value="healthy" {{ $stockFilter === 'healthy' ? 'selected' : '' }}>Stok Aman (&gt;10)</option>
                    </select>
                </div>
                <div class="col-md-2 d-flex gap-2">
                    <button type="submit" class="btn btn-success btn-sm w-100 rounded-3">Filter</button>
                    @if($search || $categoryId || $stockFilter !== 'semua')
                        <a href="{{ route('admin.inventory.index') }}" class="btn btn-light btn-sm rounded-3">Reset</a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <!-- Tabel Produk & Penyesuaian Stok -->
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead class="table-light text-uppercase small text-muted" style="letter-spacing: 0.05em;">
                    <tr>
                        <th class="ps-4">Produk</th>
                        <th>Kategori</th>
                        <th>Harga</th>
                        <th class="text-center">Stok Saat Ini</th>
                        <th>Status</th>
                        <th class="text-end pe-4">Aksi Penyesuaian</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($products as $product)
                        @php
                            $stk = (int) ($product->stock ?? 0);
                        @endphp
                        <tr>
                            <td class="ps-4">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="rounded-3 overflow-hidden bg-light border flex-shrink-0" style="width: 44px; height: 44px;">
                                        <img src="{{ $product->product_image ? asset('product_images/' . $product->product_image) : 'https://placehold.co/100x100?text=No+Img' }}"
                                             alt="{{ $product->product_title }}"
                                             class="w-100 h-100 object-fit-cover"
                                             onerror="this.onerror=null; this.src='https://placehold.co/100x100?text=No+Img';">
                                    </div>
                                    <div>
                                        <div class="fw-bold text-dark">{{ $product->product_title }}</div>
                                        <span class="text-muted small">ID: #{{ $product->product_id }}</span>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border">
                                    {{ $product->category->cat_title ?? 'Tanpa Kategori' }}
                                </span>
                            </td>
                            <td>
                                <div class="fw-semibold text-dark">
                                    Rp {{ number_format($product->product_price, 0, ',', '.') }}
                                </div>
                            </td>
                            <td class="text-center">
                                <span class="fw-bold fs-6 {{ $stk <= 0 ? 'text-danger' : ($stk <= 10 ? 'text-warning' : 'text-success') }}">
                                    {{ $stk }}
                                </span>
                                <span class="text-muted small">unit</span>
                            </td>
                            <td>
                                @if($stk <= 0)
                                    <span class="badge bg-danger-subtle text-danger px-2 py-1 rounded-pill fw-semibold">Habis</span>
                                @elseif($stk <= 10)
                                    <span class="badge bg-warning-subtle text-warning-emphasis px-2 py-1 rounded-pill fw-semibold">Menipis (&le;10)</span>
                                @else
                                    <span class="badge bg-success-subtle text-success px-2 py-1 rounded-pill fw-semibold">Aman</span>
                                @endif
                            </td>
                            <td class="text-end pe-4">
                                <button type="button"
                                        class="btn btn-outline-success btn-sm rounded-3 px-3"
                                        data-bs-toggle="modal"
                                        data-bs-target="#adjustModal"
                                        data-product-id="{{ $product->product_id }}"
                                        data-product-name="{{ $product->product_title }}"
                                        data-current-stock="{{ $stk }}">
                                    Sesuaikan Stok
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">
                                Tidak ada produk yang sesuai dengan filter.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($products->hasPages())
            <div class="card-footer bg-white border-0 py-3 d-flex justify-content-center">
                {{ $products->links() }}
            </div>
        @endif
    </div>

</div>

<!-- Modal Sesuaikan Stok -->
<div class="modal fade" id="adjustModal" tabindex="-1" aria-labelledby="adjustModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form method="POST" action="{{ route('admin.inventory.adjust') }}" class="modal-content rounded-4 border-0 shadow">
            @csrf
            <input type="hidden" name="product_id" id="modalProductId">

            <div class="modal-header border-bottom px-4 pt-4 pb-3">
                <div>
                    <h5 class="modal-title fw-bold text-dark" id="adjustModalLabel">Penyesuaian Stok Produk</h5>
                    <p class="text-muted small mb-0" id="modalProductName"></p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body px-4 py-3">
                <div class="mb-3 p-3 bg-light rounded-3 d-flex justify-content-between align-items-center">
                    <span class="small text-muted fw-semibold">Stok Saat Ini:</span>
                    <strong class="fs-5 text-dark" id="modalCurrentStock">0</strong>
                </div>

                <div class="mb-3">
                    <label class="form-label small fw-bold text-dark">Jenis Penyesuaian</label>
                    <div class="d-flex gap-2">
                        <div class="flex-fill">
                            <input type="radio" class="btn-check" name="action_type" id="typeIn" value="in" checked>
                            <label class="btn btn-outline-success w-100 rounded-3 small py-2 fw-semibold" for="typeIn">
                                + Tambah (In)
                            </label>
                        </div>
                        <div class="flex-fill">
                            <input type="radio" class="btn-check" name="action_type" id="typeOut" value="out">
                            <label class="btn btn-outline-danger w-100 rounded-3 small py-2 fw-semibold" for="typeOut">
                                - Kurangi (Out)
                            </label>
                        </div>
                        <div class="flex-fill">
                            <input type="radio" class="btn-check" name="action_type" id="typeSet" value="set">
                            <label class="btn btn-outline-primary w-100 rounded-3 small py-2 fw-semibold" for="typeSet">
                                = Tetapkan (Set)
                            </label>
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="modalQuantity" class="form-label small fw-bold text-dark" id="modalQuantityLabel">Jumlah Unit</label>
                    <input type="number" name="quantity" id="modalQuantity" class="form-control rounded-3" min="0" required placeholder="Masukkan jumlah unit...">
                </div>

                <div class="mb-3">
                    <label for="modalReason" class="form-label small fw-bold text-dark">Alasan Penyesuaian (Audit Trail)</label>
                    <textarea name="reason" id="modalReason" rows="2" class="form-control rounded-3" required placeholder="Contoh: Penerimaan barang supplier / Barang rusak / Stock opname bulanan..."></textarea>
                </div>
            </div>

            <div class="modal-footer border-top px-4 py-3 bg-light rounded-bottom-4">
                <button type="button" class="btn btn-light rounded-3" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-success rounded-3 px-4">Simpan Perubahan Stok</button>
            </div>
        </form>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const adjustModal = document.getElementById('adjustModal');
        if (adjustModal) {
            adjustModal.addEventListener('show.bs.modal', function (event) {
                const button = event.relatedTarget;
                const productId = button.getAttribute('data-product-id');
                const productName = button.getAttribute('data-product-name');
                const currentStock = button.getAttribute('data-current-stock');

                document.getElementById('modalProductId').value = productId;
                document.getElementById('modalProductName').textContent = productName;
                document.getElementById('modalCurrentStock').textContent = currentStock + ' unit';
                document.getElementById('modalQuantity').value = '';
                document.getElementById('modalReason').value = '';
            });
        }
    });
</script>
@endsection
