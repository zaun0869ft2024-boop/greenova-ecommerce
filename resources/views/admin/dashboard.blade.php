@extends('layouts.admin')

@section('title', 'Business Analytics Dashboard')
@section('page-title', 'Business Analytics Dashboard')

@section('content')
<div class="analytics-dashboard">

    <!-- Welcome & Period Filter Toolbar -->
    <div class="card border-0 rounded-4 shadow-sm mb-4" style="background: linear-gradient(135deg, #1B3B2A 0%, #244B35 100%); color: #fff;">
        <div class="card-body p-4 p-lg-5">
            <div class="row align-items-center g-4">
                <div class="col-lg-7">
                    <span class="badge px-3 py-1 mb-2 fw-semibold" style="background: rgba(255,255,255,0.18); color: #ECFDF5; letter-spacing: 0.05em;">
                        BUSINESS INTELLIGENCE &bull; GREENOVA
                    </span>
                    <h2 class="fw-bold mb-2 text-white" style="font-size: clamp(1.4rem, 2.5vw, 2rem);">
                        Selamat datang, {{ auth()->user()->name }}!
                    </h2>
                    <p class="mb-0 small" style="color: rgba(255,255,255,0.85); line-height: 1.6; max-width: 580px;">
                        Laporan metrik operasional dan penjualan terverifikasi. Periode aktif:
                        <strong class="text-white">{{ $startDate->format('d M Y') }}</strong> s/d <strong class="text-white">{{ $endDate->format('d M Y') }}</strong>.
                    </p>
                </div>

                <div class="col-lg-5 text-lg-end">
                    <form method="GET" action="{{ route('admin.dashboard') }}" id="periodFilterForm" class="bg-white p-3 rounded-4 shadow-sm text-dark text-start d-inline-block w-100" style="max-width: 440px;">
                        <label class="form-label small fw-bold text-muted mb-2">Filter Rentang Waktu</label>
                        <div class="d-flex flex-wrap gap-1 mb-2">
                            <button type="submit" name="period" value="today" class="btn btn-sm rounded-pill {{ $period === 'today' ? 'btn-success fw-bold' : 'btn-light' }}">Hari Ini</button>
                            <button type="submit" name="period" value="7_days" class="btn btn-sm rounded-pill {{ $period === '7_days' ? 'btn-success fw-bold' : 'btn-light' }}">7 Hari</button>
                            <button type="submit" name="period" value="30_days" class="btn btn-sm rounded-pill {{ $period === '30_days' ? 'btn-success fw-bold' : 'btn-light' }}">30 Hari</button>
                            <button type="submit" name="period" value="this_month" class="btn btn-sm rounded-pill {{ $period === 'this_month' ? 'btn-success fw-bold' : 'btn-light' }}">Bulan Ini</button>
                            <button type="submit" name="period" value="this_year" class="btn btn-sm rounded-pill {{ $period === 'this_year' ? 'btn-success fw-bold' : 'btn-light' }}">Tahun Ini</button>
                        </div>
                        <div class="row g-2 align-items-center pt-2 border-top">
                            <div class="col-6">
                                <input type="date" name="start_date" value="{{ $customStart ?? $startDate->toDateString() }}" class="form-control form-control-sm rounded-3">
                            </div>
                            <div class="col-6">
                                <input type="date" name="end_date" value="{{ $customEnd ?? $endDate->toDateString() }}" class="form-control form-control-sm rounded-3">
                            </div>
                            <div class="col-12 mt-2">
                                <button type="submit" name="period" value="custom" class="btn btn-outline-success btn-sm w-100 rounded-3 fw-semibold">Terapkan Rentang Khusus</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- KPI Summary Grid -->
    <div class="row g-3 mb-4">
        <!-- 1. Total Revenue -->
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 rounded-4 shadow-sm h-100 p-4 bg-white">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="small fw-bold text-muted text-uppercase" style="letter-spacing: 0.05em;">Pendapatan Bersih</span>
                    <span class="badge bg-success-subtle text-success px-2 py-1 rounded-pill small">Selesai/Lunas</span>
                </div>
                <div class="fw-bold mb-1" style="font-size: 24px; color: var(--gn-primary, #166534);">
                    Rp {{ number_format($totalRevenue, 0, ',', '.') }}
                </div>
                <div class="small text-muted">
                    Dari <strong>{{ $successfulCount }}</strong> transaksi sukses ({{ $totalSoldUnits }} unit)
                </div>
            </div>
        </div>

        <!-- 2. Total Orders -->
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 rounded-4 shadow-sm h-100 p-4 bg-white">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="small fw-bold text-muted text-uppercase" style="letter-spacing: 0.05em;">Total Pesanan</span>
                    <span class="badge bg-primary-subtle text-primary px-2 py-1 rounded-pill small">Periode</span>
                </div>
                <div class="fw-bold mb-1 text-dark" style="font-size: 26px;">
                    {{ number_format($totalOrders, 0, ',', '.') }}
                </div>
                <div class="small text-muted d-flex gap-2">
                    <span class="text-success">&bull; {{ $successfulCount }} sukses</span>
                    <span class="text-warning">&bull; {{ $pendingCount }} pending</span>
                    <span class="text-danger">&bull; {{ $cancelledCount }} batal</span>
                </div>
            </div>
        </div>

        <!-- 3. Customers & Products -->
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 rounded-4 shadow-sm h-100 p-4 bg-white">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="small fw-bold text-muted text-uppercase" style="letter-spacing: 0.05em;">Katalog &amp; User</span>
                    <a href="{{ route('admin.products.index') }}" class="small text-decoration-none fw-semibold text-success">Kelola &rarr;</a>
                </div>
                <div class="fw-bold mb-1 text-dark" style="font-size: 26px;">
                    {{ $totalProducts }} <span class="fs-6 fw-normal text-muted">produk</span>
                </div>
                <div class="small text-muted">
                    {{ $totalCustomers }} pelanggan terdaftar &bull; {{ $totalCategories }} kategori
                </div>
            </div>
        </div>

        <!-- 4. Low Stock Alert -->
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 rounded-4 shadow-sm h-100 p-4 bg-white">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="small fw-bold text-muted text-uppercase" style="letter-spacing: 0.05em;">Peringatan Stok</span>
                    <a href="{{ route('admin.inventory.index', ['stock_status' => 'low']) }}" class="badge bg-warning-subtle text-warning-emphasis px-2 py-1 rounded-pill text-decoration-none">
                        Restock
                    </a>
                </div>
                <div class="fw-bold mb-1 {{ $lowStockCount > 0 ? 'text-warning-emphasis' : 'text-success' }}" style="font-size: 26px;">
                    {{ $lowStockCount }} <span class="fs-6 fw-normal text-muted">item menipis</span>
                </div>
                <div class="small text-muted">
                    {{ $activeDiscounts + $activeVouchers }} promo &amp; voucher aktif
                </div>
            </div>
        </div>
    </div>

    <!-- Charts & Analytics Section -->
    <div class="row g-4 mb-4">
        <!-- Interactive Chart: Revenue & Order Trends -->
        <div class="col-lg-8">
            <div class="card border-0 rounded-4 shadow-sm p-4 bg-white h-100">
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
                    <div>
                        <h5 class="fw-bold mb-1 text-dark">Trend Penjualan &amp; Pendapatan</h5>
                        <p class="text-muted small mb-0">Pergerakan transaksi harian selama periode aktif.</p>
                    </div>
                    <div class="d-flex align-items-center gap-3 small">
                        <span class="d-inline-flex align-items-center gap-1">
                            <span class="rounded-circle" style="width: 10px; height: 10px; background: #166534;"></span>
                            Pendapatan (Rp)
                        </span>
                        <span class="d-inline-flex align-items-center gap-1">
                            <span class="rounded-circle" style="width: 10px; height: 10px; background: #3B82F6;"></span>
                            Jumlah Pesanan
                        </span>
                    </div>
                </div>

                <div style="position: relative; height: 280px; width: 100%;">
                    <canvas id="salesTrendChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Transaction Status Breakdown Pie/Doughnut -->
        <div class="col-lg-4">
            <div class="card border-0 rounded-4 shadow-sm p-4 bg-white h-100">
                <div class="mb-3">
                    <h5 class="fw-bold mb-1 text-dark">Status Transaksi</h5>
                    <p class="text-muted small mb-0">Komposisi pesanan dalam rentang waktu.</p>
                </div>

                <div style="position: relative; height: 210px; width: 100%;" class="d-flex align-items-center justify-content-center">
                    <canvas id="orderStatusChart"></canvas>
                </div>

                <div class="mt-3 pt-3 border-top d-flex justify-content-around text-center small">
                    <div>
                        <div class="fw-bold text-success fs-6">{{ $successfulCount }}</div>
                        <div class="text-muted" style="font-size: 11px;">Sukses</div>
                    </div>
                    <div>
                        <div class="fw-bold text-warning fs-6">{{ $pendingCount }}</div>
                        <div class="text-muted" style="font-size: 11px;">Pending</div>
                    </div>
                    <div>
                        <div class="fw-bold text-danger fs-6">{{ $cancelledCount }}</div>
                        <div class="text-muted" style="font-size: 11px;">Batal</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Product Performance & Low Stock Grid -->
    <div class="row g-4 mb-4">
        <!-- Best-Selling Products -->
        <div class="col-lg-7">
            <div class="card border-0 rounded-4 shadow-sm p-4 bg-white h-100">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <h5 class="fw-bold mb-1 text-dark">Produk Terlaris (Best Seller)</h5>
                        <p class="text-muted small mb-0">Dihitung dari transaksi riil yang telah selesai.</p>
                    </div>
                    <a href="{{ route('admin.products.index') }}" class="btn btn-outline-success btn-sm rounded-3">Semua Produk</a>
                </div>

                <div class="table-responsive">
                    <table class="table align-middle mb-0 small">
                        <thead class="table-light text-muted">
                            <tr>
                                <th>Produk</th>
                                <th>Kategori</th>
                                <th class="text-center">Terjual</th>
                                <th class="text-end">Omzet Produk</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($topProducts as $item)
                                @php
                                    $p = $item->product;
                                @endphp
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="rounded-3 overflow-hidden bg-light flex-shrink-0" style="width: 36px; height: 36px;">
                                                <img src="{{ $p && $p->product_image ? asset('product_images/' . $p->product_image) : 'https://placehold.co/80x80/eaf2ec/244b35?text=P' }}"
                                                     alt="{{ $p->product_title ?? 'Produk' }}"
                                                     class="w-100 h-100 object-fit-cover"
                                                     onerror="this.onerror=null; this.src='https://placehold.co/80x80/eaf2ec/244b35?text=P';">
                                            </div>
                                            <div class="fw-semibold text-dark text-truncate" style="max-width: 200px;">
                                                {{ $p->product_title ?? 'Produk ID #' . $item->product_id }}
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark border">{{ $p->category->cat_title ?? 'Umum' }}</span>
                                    </td>
                                    <td class="text-center fw-bold text-success fs-6">
                                        {{ $item->total_sold }} <span class="small fw-normal text-muted">unit</span>
                                    </td>
                                    <td class="text-end fw-bold text-dark">
                                        Rp {{ number_format((float) $item->product_revenue, 0, ',', '.') }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center py-4 text-muted">Belum ada data penjualan pada periode ini.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Low Stock Alert Box -->
        <div class="col-lg-5">
            <div class="card border-0 rounded-4 shadow-sm p-4 bg-white h-100">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <h5 class="fw-bold mb-1 text-dark">Peringatan Stok Menipis</h5>
                        <p class="text-muted small mb-0">Prioritas restock gudang Greenova.</p>
                    </div>
                    <a href="{{ route('admin.inventory.index') }}" class="btn btn-outline-warning btn-sm rounded-3">Buka Inventori</a>
                </div>

                <div class="table-responsive">
                    <table class="table align-middle mb-0 small">
                        <thead class="table-light text-muted">
                            <tr>
                                <th>Produk</th>
                                <th class="text-center">Sisa Stok</th>
                                <th class="text-end">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($lowStockProducts as $low)
                                <tr>
                                    <td>
                                        <div class="fw-semibold text-dark text-truncate" style="max-width: 170px;">
                                            {{ $low->product_title }}
                                        </div>
                                        <span class="text-muted small">Rp {{ number_format($low->product_price, 0, ',', '.') }}</span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge {{ $low->stock <= 0 ? 'bg-danger text-white' : 'bg-warning-subtle text-warning-emphasis' }} px-2 py-1 rounded-pill fw-bold">
                                            {{ $low->stock }} unit
                                        </span>
                                    </td>
                                    <td class="text-end">
                                        <a href="{{ route('admin.inventory.index', ['search' => $low->product_title]) }}" class="btn btn-sm btn-outline-success rounded-3 py-1 px-2">
                                            Restock
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-center py-4 text-success fw-medium">
                                        ✓ Semua stok produk saat ini dalam level aman (>10).
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Orders Table -->
    <div class="card border-0 rounded-4 shadow-sm p-4 bg-white mb-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <h5 class="fw-bold mb-1 text-dark">Pesanan Terbaru</h5>
                <p class="text-muted small mb-0">Transaksi terkini pelanggan yang masuk ke sistem.</p>
            </div>
            <a href="{{ route('admin.orders.index') }}" class="btn btn-outline-success btn-sm rounded-3">Lihat Semua Pesanan</a>
        </div>

        <div class="table-responsive">
            <table class="table align-middle mb-0 small">
                <thead class="table-light text-muted">
                    <tr>
                        <th>ID Pesanan</th>
                        <th>Waktu</th>
                        <th>Pelanggan</th>
                        <th>Produk</th>
                        <th>Total</th>
                        <th>Pembayaran</th>
                        <th>Fulfillment</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentOrders as $order)
                        @php
                            $sub = (float) ($order->subtotal ?? ($order->unit_price ? $order->unit_price * $order->qty : 0));
                            $st = strtolower(trim($order->status ?? 'pending'));
                            $paySt = strtolower(trim($order->payment_status ?? 'pending'));
                            $fulfillClass = match($st) {
                                'selesai' => 'bg-success-subtle text-success',
                                'diproses' => 'bg-primary-subtle text-primary',
                                'dikirim' => 'bg-info-subtle text-info',
                                'dibatalkan' => 'bg-danger-subtle text-danger',
                                default => 'bg-warning-subtle text-warning-emphasis',
                            };
                            $payClass = match($paySt) {
                                'paid' => 'bg-success-subtle text-success',
                                'pending' => 'bg-warning-subtle text-warning-emphasis',
                                'failed', 'cancelled', 'expired' => 'bg-danger-subtle text-danger',
                                default => 'bg-light text-muted',
                            };
                            $payLabel = match($paySt) {
                                'paid' => 'Lunas',
                                'pending' => 'Menunggu Bayar',
                                'unpaid' => 'Belum Bayar',
                                'failed' => 'Gagal',
                                'expired' => 'Kedaluwarsa',
                                'cancelled' => 'Dibatalkan',
                                default => ucfirst($paySt),
                            };
                        @endphp
                        <tr>
                            <td class="fw-bold text-dark">#{{ $order->orders_id }}</td>
                            <td class="text-nowrap text-muted">{{ \Carbon\Carbon::parse($order->date)->format('d M Y') }}</td>
                            <td>
                                <div class="fw-semibold text-dark">{{ $order->recipient_name ?: ($order->user->name ?? 'Pelanggan') }}</div>
                                <span class="small text-muted">{{ $order->user->email ?? '—' }}</span>
                            </td>
                            <td>
                                <div class="text-truncate text-dark" style="max-width: 180px;">
                                    {{ $order->product->product_title ?? 'Produk #' . $order->product_id }}
                                </div>
                                <span class="small text-muted">{{ $order->qty }} unit</span>
                            </td>
                            <td class="fw-bold text-dark">
                                Rp {{ number_format($sub, 0, ',', '.') }}
                            </td>
                            <td>
                                <span class="badge {{ $payClass }} px-2 py-1 rounded-pill fw-semibold" style="font-size: 11px;">
                                    {{ $payLabel }}
                                </span>
                            </td>
                            <td>
                                <span class="badge {{ $fulfillClass }} px-2 py-1 rounded-pill fw-semibold" style="font-size: 11px;">
                                    {{ ucfirst($order->status ?? 'Pending') }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-4 text-muted">Belum ada pesanan terbaru.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>

<!-- Script Chart.js CDN for Interactive Visual Charts -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const labels = @json($chartLabels);
    const revenues = @json($chartRevenues);
    const orders = @json($chartOrders);

    // 1. Sales Trend Line Chart
    const ctxTrend = document.getElementById('salesTrendChart');
    if (ctxTrend) {
        new Chart(ctxTrend, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: 'Pendapatan (Rp)',
                        data: revenues,
                        borderColor: '#166534',
                        backgroundColor: 'rgba(22, 101, 52, 0.08)',
                        tension: 0.35,
                        fill: true,
                        yAxisID: 'y',
                    },
                    {
                        label: 'Jumlah Pesanan',
                        data: orders,
                        borderColor: '#3B82F6',
                        backgroundColor: 'transparent',
                        borderDash: [5, 5],
                        tension: 0.35,
                        fill: false,
                        yAxisID: 'y1',
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: {
                    mode: 'index',
                    intersect: false,
                },
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                if (context.datasetIndex === 0) {
                                    return 'Pendapatan: Rp ' + Number(context.parsed.y).toLocaleString('id-ID');
                                }
                                return 'Pesanan: ' + context.parsed.y + ' transaksi';
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        type: 'linear',
                        display: true,
                        position: 'left',
                        ticks: {
                            callback: function(value) {
                                return 'Rp ' + (value >= 1000000 ? (value / 1000000) + 'jt' : (value / 1000) + 'k');
                            }
                        },
                        grid: { color: '#F3F4F6' }
                    },
                    y1: {
                        type: 'linear',
                        display: true,
                        position: 'right',
                        grid: { drawOnChartArea: false },
                        ticks: { precision: 0 }
                    },
                    x: {
                        grid: { display: false }
                    }
                }
            }
        });
    }

    // 2. Status Doughnut Chart
    const ctxStatus = document.getElementById('orderStatusChart');
    if (ctxStatus) {
        const succ = {{ $successfulCount }};
        const pend = {{ $pendingCount }};
        const canc = {{ $cancelledCount }};
        const total = succ + pend + canc;

        new Chart(ctxStatus, {
            type: 'doughnut',
            data: {
                labels: ['Sukses', 'Pending', 'Batal'],
                datasets: [{
                    data: total > 0 ? [succ, pend, canc] : [1, 0, 0],
                    backgroundColor: total > 0 ? ['#166534', '#F59E0B', '#EF4444'] : ['#E5E7EB', '#E5E7EB', '#E5E7EB'],
                    borderWidth: 0,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '72%',
                plugins: {
                    legend: { display: false }
                }
            }
        });
    }
});
</script>
@endsection
