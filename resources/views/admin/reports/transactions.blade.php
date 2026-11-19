@extends('layouts.admin')

@section('title', 'Laporan Transaksi & Pendapatan')
@section('page-title', 'Laporan Transaksi')

@section('content')
<div class="reports-container">

    <!-- KPI Ringkasan Filter Aktif -->
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-3">
            <div class="metric-card bg-white p-4 rounded-4 shadow-sm border-0">
                <span class="small fw-bold text-muted text-uppercase" style="letter-spacing: 0.05em;">Total Pendapatan</span>
                <div class="fw-bold fs-4 mt-1" style="color: var(--gn-primary, #166534);">
                    Rp {{ number_format($totalRevenue, 0, ',', '.') }}
                </div>
                <span class="small text-muted">Dari pesanan lunas/selesai</span>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="metric-card bg-white p-4 rounded-4 shadow-sm border-0">
                <span class="small fw-bold text-muted text-uppercase" style="letter-spacing: 0.05em;">Jumlah Transaksi</span>
                <div class="fw-bold fs-4 mt-1 text-dark">
                    {{ number_format($totalOrders, 0, ',', '.') }} <span class="fs-6 fw-normal text-muted">pesanan</span>
                </div>
                <span class="small text-muted">{{ number_format($totalUnits, 0, ',', '.') }} unit produk dibeli</span>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="metric-card bg-white p-4 rounded-4 shadow-sm border-0">
                <span class="small fw-bold text-muted text-uppercase" style="letter-spacing: 0.05em;">Nilai Rata-Rata (AOV)</span>
                <div class="fw-bold fs-4 mt-1 text-dark">
                    Rp {{ number_format($averageOrderValue, 0, ',', '.') }}
                </div>
                <span class="small text-muted">Per transaksi lunas</span>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="metric-card bg-white p-4 rounded-4 shadow-sm border-0">
                <span class="small fw-bold text-muted text-uppercase" style="letter-spacing: 0.05em;">Status Pembayaran</span>
                <div class="d-flex align-items-center gap-2 mt-1">
                    <span class="badge bg-success-subtle text-success">{{ $paidOrdersCount }} Lunas</span>
                    <span class="badge bg-warning-subtle text-warning-emphasis">{{ $pendingOrdersCount }} Pending</span>
                    <span class="badge bg-danger-subtle text-danger">{{ $cancelledOrdersCount }} Batal</span>
                </div>
                <span class="small text-muted mt-1 d-block">Komposisi dalam filter</span>
            </div>
        </div>
    </div>

    <!-- Toolbar Filter & Ekspor -->
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body p-3 p-lg-4">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-3">
                <h5 class="fw-bold mb-0 text-dark">Filter &amp; Parameter Laporan</h5>
                <div class="d-flex gap-2">
                    <a href="{{ route('admin.reports.export_csv', request()->all()) }}" class="btn btn-outline-success btn-sm rounded-3">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="me-1"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                        Ekspor CSV
                    </a>
                    <a href="{{ route('admin.reports.print', request()->all()) }}" target="_blank" class="btn btn-success btn-sm rounded-3">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="me-1"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
                        Cetak / PDF
                    </a>
                </div>
            </div>

            <form method="GET" action="{{ route('admin.reports.transactions') }}" class="row g-2">
                <div class="col-md-3">
                    <label class="form-label small fw-semibold text-muted mb-1">Cari Data</label>
                    <input type="text" name="search" value="{{ $search }}" class="form-control form-control-sm rounded-3" placeholder="ID, ref, pelanggan, produk...">
                </div>

                <div class="col-6 col-md-2">
                    <label class="form-label small fw-semibold text-muted mb-1">Dari Tanggal</label>
                    <input type="date" name="start_date" value="{{ $startDate }}" class="form-control form-control-sm rounded-3">
                </div>

                <div class="col-6 col-md-2">
                    <label class="form-label small fw-semibold text-muted mb-1">Sampai Tanggal</label>
                    <input type="date" name="end_date" value="{{ $endDate }}" class="form-control form-control-sm rounded-3">
                </div>

                <div class="col-6 col-md-2">
                    <label class="form-label small fw-semibold text-muted mb-1">Status Pembayaran</label>
                    <select name="payment_status" class="form-select form-select-sm rounded-3">
                        <option value="semua">Semua Status</option>
                        <option value="paid" {{ $paymentStatus === 'paid' ? 'selected' : '' }}>Lunas (Paid)</option>
                        <option value="pending" {{ $paymentStatus === 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="unpaid" {{ $paymentStatus === 'unpaid' ? 'selected' : '' }}>Belum Bayar (COD)</option>
                        <option value="cancelled" {{ $paymentStatus === 'cancelled' ? 'selected' : '' }}>Dibatalkan</option>
                        <option value="failed" {{ $paymentStatus === 'failed' ? 'selected' : '' }}>Gagal</option>
                        <option value="expired" {{ $paymentStatus === 'expired' ? 'selected' : '' }}>Kedaluwarsa</option>
                    </select>
                </div>

                <div class="col-6 col-md-2">
                    <label class="form-label small fw-semibold text-muted mb-1">Status Pesanan</label>
                    <select name="order_status" class="form-select form-select-sm rounded-3">
                        <option value="semua">Semua Pesanan</option>
                        <option value="Pending" {{ $orderStatus === 'Pending' ? 'selected' : '' }}>Pending</option>
                        <option value="Diproses" {{ $orderStatus === 'Diproses' ? 'selected' : '' }}>Diproses</option>
                        <option value="Dikirim" {{ $orderStatus === 'Dikirim' ? 'selected' : '' }}>Dikirim</option>
                        <option value="Selesai" {{ $orderStatus === 'Selesai' ? 'selected' : '' }}>Selesai</option>
                        <option value="Dibatalkan" {{ $orderStatus === 'Dibatalkan' ? 'selected' : '' }}>Dibatalkan</option>
                    </select>
                </div>

                <div class="col-md-1 d-flex align-items-end">
                    <button type="submit" class="btn btn-success btn-sm w-100 rounded-3 py-2">Filter</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Tabel Transaksi -->
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
        <div class="table-responsive">
            <table class="table align-middle mb-0 small">
                <thead class="table-light text-uppercase text-muted" style="letter-spacing: 0.05em;">
                    <tr>
                        <th class="ps-4">Pesanan</th>
                        <th>Tanggal</th>
                        <th>Pelanggan</th>
                        <th>Produk &amp; Qty</th>
                        <th>Subtotal</th>
                        <th>Metode Bayar</th>
                        <th>Status Bayar</th>
                        <th class="pe-4">Status Pesanan</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($orders as $order)
                        @php
                            $sub = (float) ($order->subtotal ?? ($order->unit_price ? $order->unit_price * $order->qty : 0));
                            $paySt = strtolower(trim($order->payment_status ?? 'pending'));
                            $ordSt = strtolower(trim($order->status ?? 'pending'));
                            $payBadge = match($paySt) {
                                'paid' => 'bg-success-subtle text-success',
                                'pending' => 'bg-warning-subtle text-warning-emphasis',
                                'failed', 'cancelled', 'expired' => 'bg-danger-subtle text-danger',
                                default => 'bg-light text-muted',
                            };
                            $ordBadge = match($ordSt) {
                                'selesai' => 'bg-success-subtle text-success',
                                'diproses' => 'bg-primary-subtle text-primary',
                                'dikirim' => 'bg-info-subtle text-info',
                                'dibatalkan' => 'bg-danger-subtle text-danger',
                                default => 'bg-warning-subtle text-warning-emphasis',
                            };
                        @endphp
                        <tr>
                            <td class="ps-4">
                                <div class="fw-bold text-dark">#{{ $order->orders_id }}</div>
                                @if(!empty($order->payment_reference))
                                    <span class="font-monospace text-muted" style="font-size: 11px;">{{ $order->payment_reference }}</span>
                                @endif
                            </td>
                            <td class="text-nowrap text-muted">
                                {{ \Carbon\Carbon::parse($order->date)->format('d/m/Y') }}
                            </td>
                            <td>
                                <div class="fw-semibold text-dark">{{ $order->recipient_name ?: ($order->user->name ?? 'Pelanggan') }}</div>
                                <span class="text-muted small">{{ $order->user->email ?? '—' }}</span>
                            </td>
                            <td>
                                <div class="text-truncate fw-medium text-dark" style="max-width: 200px;">
                                    {{ $order->product->product_title ?? ('Produk #' . $order->product_id) }}
                                </div>
                                <span class="text-muted small">{{ $order->qty }} unit @ Rp {{ number_format($order->unit_price ?? $order->product->product_price ?? 0, 0, ',', '.') }}</span>
                            </td>
                            <td class="fw-bold text-dark text-nowrap">
                                Rp {{ number_format($sub, 0, ',', '.') }}
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border text-uppercase" style="font-size: 10px;">
                                    {{ $order->payment_method ?: 'manual' }}
                                </span>
                            </td>
                            <td>
                                <span class="badge {{ $payBadge }} px-2 py-1 rounded-pill text-uppercase" style="font-size: 10.5px;">
                                    {{ $paySt }}
                                </span>
                            </td>
                            <td class="pe-4">
                                <span class="badge {{ $ordBadge }} px-2 py-1 rounded-pill fw-semibold" style="font-size: 11px;">
                                    {{ ucfirst($order->status ?? 'Pending') }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">
                                Tidak ada transaksi yang sesuai dengan parameter filter.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($orders->hasPages())
            <div class="card-footer bg-white border-0 py-3 d-flex justify-content-center">
                {{ $orders->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
