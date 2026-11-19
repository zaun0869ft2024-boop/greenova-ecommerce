@extends('layouts.admin')

@section('title', 'Riwayat Pergerakan Stok')
@section('page-title', 'Riwayat Pergerakan Stok (Audit Trail)')

@section('content')
<div class="inventory-history-container">

    <!-- Header & Navigasi -->
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
        <div>
            <h4 class="fw-bold mb-1 text-dark">Audit Trail Pergerakan Stok</h4>
            <p class="text-muted small mb-0">Catatan kronologis setiap penambahan, pengurangan, dan penyesuaian stok produk.</p>
        </div>
        <a href="{{ route('admin.inventory.index') }}" class="btn btn-outline-success btn-sm rounded-3">
            &larr; Kembali ke Inventori
        </a>
    </div>

    <!-- Filter Card -->
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body p-3 p-lg-4">
            <form method="GET" action="{{ route('admin.inventory.history') }}" class="row g-2 align-items-end">
                <div class="col-md-4">
                    <label class="form-label small fw-semibold text-muted mb-1">Cari Produk</label>
                    <input type="text" name="search" value="{{ $search }}" class="form-control form-control-sm rounded-3" placeholder="Nama produk...">
                </div>
                <div class="col-6 col-md-2">
                    <label class="form-label small fw-semibold text-muted mb-1">Tipe Mutasi</label>
                    <select name="type" class="form-select form-select-sm rounded-3">
                        <option value="semua">Semua Tipe</option>
                        <option value="in" {{ $type === 'in' ? 'selected' : '' }}>+ Masuk (In)</option>
                        <option value="out" {{ $type === 'out' ? 'selected' : '' }}>- Keluar (Out)</option>
                        <option value="adjustment" {{ $type === 'adjustment' ? 'selected' : '' }}>Penyesuaian (Opname)</option>
                        <option value="purchase" {{ $type === 'purchase' ? 'selected' : '' }}>Pembelian Pelanggan</option>
                        <option value="cancellation" {{ $type === 'cancellation' ? 'selected' : '' }}>Pembatalan Pesanan</option>
                    </select>
                </div>
                <div class="col-6 col-md-2">
                    <label class="form-label small fw-semibold text-muted mb-1">Dari Tanggal</label>
                    <input type="date" name="start_date" value="{{ $startDate }}" class="form-control form-control-sm rounded-3">
                </div>
                <div class="col-6 col-md-2">
                    <label class="form-label small fw-semibold text-muted mb-1">Sampai Tanggal</label>
                    <input type="date" name="end_date" value="{{ $endDate }}" class="form-control form-control-sm rounded-3">
                </div>
                <div class="col-6 col-md-2 d-flex gap-2">
                    <button type="submit" class="btn btn-success btn-sm w-100 rounded-3">Terapkan</button>
                    @if($search || $type !== 'semua' || $startDate || $endDate)
                        <a href="{{ route('admin.inventory.history') }}" class="btn btn-light btn-sm rounded-3">Reset</a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <!-- Tabel Riwayat -->
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead class="table-light text-uppercase small text-muted" style="letter-spacing: 0.05em;">
                    <tr>
                        <th class="ps-4">Waktu</th>
                        <th>Produk</th>
                        <th>Tipe</th>
                        <th class="text-center">Perubahan</th>
                        <th class="text-center">Sebelum &rarr; Sesudah</th>
                        <th>Keterangan</th>
                        <th class="pe-4">Oleh</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($movements as $m)
                        @php
                            $qty = (int) $m->quantity;
                            $badgeClass = match($m->type) {
                                'in' => 'bg-success-subtle text-success',
                                'out' => 'bg-danger-subtle text-danger',
                                'purchase' => 'bg-primary-subtle text-primary',
                                'cancellation' => 'bg-warning-subtle text-warning-emphasis',
                                default => 'bg-info-subtle text-info',
                            };
                            $typeLabel = match($m->type) {
                                'in' => 'Barang Masuk',
                                'out' => 'Barang Keluar',
                                'purchase' => 'Penjualan',
                                'cancellation' => 'Pembatalan',
                                default => 'Penyesuaian',
                            };
                        @endphp
                        <tr>
                            <td class="ps-4 text-nowrap">
                                <div class="small fw-semibold text-dark">{{ $m->created_at->format('d M Y') }}</div>
                                <div class="small text-muted">{{ $m->created_at->format('H:i:s') }} WIB</div>
                            </td>
                            <td>
                                @if($m->product)
                                    <div class="fw-bold text-dark">{{ $m->product->product_title }}</div>
                                    <span class="small text-muted">ID: #{{ $m->product_id }}</span>
                                @else
                                    <span class="text-muted small">Produk dihapus (ID: {{ $m->product_id }})</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge {{ $badgeClass }} px-2 py-1 rounded-pill small fw-semibold">
                                    {{ $typeLabel }}
                                </span>
                            </td>
                            <td class="text-center fw-bold fs-6 {{ $qty > 0 ? 'text-success' : 'text-danger' }}">
                                {{ $qty > 0 ? "+{$qty}" : $qty }}
                            </td>
                            <td class="text-center text-nowrap">
                                <span class="text-muted small">{{ $m->stock_before }}</span>
                                <span class="text-muted mx-1">&rarr;</span>
                                <strong class="text-dark">{{ $m->stock_after }}</strong>
                            </td>
                            <td style="max-width: 250px;">
                                <div class="small text-secondary">{{ $m->description ?: '—' }}</div>
                            </td>
                            <td class="pe-4 text-nowrap">
                                <span class="small fw-medium text-dark">{{ $m->user->name ?? 'Sistem / Otomatis' }}</span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                Belum ada catatan pergerakan stok yang terekam.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($movements->hasPages())
            <div class="card-footer bg-white border-0 py-3 d-flex justify-content-center">
                {{ $movements->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
