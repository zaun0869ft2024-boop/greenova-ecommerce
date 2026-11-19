<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Transaksi Penjualan Greenova</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            font-family: Arial, sans-serif;
            color: #333;
            background: #fff;
            font-size: 12px;
        }
        .report-header {
            border-bottom: 2px solid #166534;
            padding-bottom: 12px;
            margin-bottom: 20px;
        }
        .report-logo {
            font-size: 22px;
            font-weight: bold;
            color: #166534;
        }
        .table th {
            background-color: #f3f6f0 !important;
            color: #166534;
            text-transform: uppercase;
            font-size: 11px;
        }
        @media print {
            .no-print {
                display: none !important;
            }
            body {
                padding: 0;
            }
        }
    </style>
</head>
<body class="p-4">

<div class="no-print mb-4 d-flex justify-content-between align-items-center bg-light p-3 rounded">
    <div>
        <strong>Mode Cetak Laporan Transaksi</strong> &bull; Silakan gunakan tombol cetak atau simpan sebagai PDF.
    </div>
    <div>
        <button onclick="window.print()" class="btn btn-success btn-sm px-3">Cetak / Simpan PDF</button>
        <button onclick="window.close()" class="btn btn-secondary btn-sm px-3">Tutup</button>
    </div>
</div>

<div class="report-header d-flex justify-content-between align-items-end">
    <div>
        <div class="report-logo">GREENOVA</div>
        <div class="small text-muted">Sustainable Lifestyle E-Commerce</div>
        <div class="small text-muted">Laporan Transaksi Resmi Penjualan</div>
    </div>
    <div class="text-end small">
        <div><strong>Waktu Cetak:</strong> {{ now()->format('d M Y, H:i') }} WIB</div>
        <div><strong>Total Transaksi:</strong> {{ $totalOrders }} pesanan ({{ $totalUnits }} unit)</div>
        <div><strong>Total Omzet Terverifikasi:</strong> Rp {{ number_format($totalRevenue, 0, ',', '.') }}</div>
    </div>
</div>

<table class="table table-bordered table-sm align-middle">
    <thead>
        <tr>
            <th>No</th>
            <th>ID / Ref</th>
            <th>Tanggal</th>
            <th>Pelanggan</th>
            <th>Produk</th>
            <th>Qty</th>
            <th>Harga</th>
            <th>Subtotal</th>
            <th>Bayar</th>
            <th>Status</th>
        </tr>
    </thead>
    <tbody>
        @forelse($orders as $idx => $order)
            @php
                $sub = (float) ($order->subtotal ?? ($order->unit_price ? $order->unit_price * $order->qty : 0));
            @endphp
            <tr>
                <td class="text-center">{{ $idx + 1 }}</td>
                <td>
                    <strong>#{{ $order->orders_id }}</strong><br>
                    <small class="text-muted">{{ $order->payment_reference ?: '—' }}</small>
                </td>
                <td>{{ \Carbon\Carbon::parse($order->date)->format('d/m/Y') }}</td>
                <td>
                    {{ $order->recipient_name ?: ($order->user->name ?? 'Pelanggan') }}<br>
                    <small class="text-muted">{{ $order->user->email ?? '' }}</small>
                </td>
                <td>{{ $order->product->product_title ?? ('Produk #' . $order->product_id) }}</td>
                <td class="text-center">{{ $order->qty }}</td>
                <td class="text-end">Rp {{ number_format($order->unit_price ?? $order->product->product_price ?? 0, 0, ',', '.') }}</td>
                <td class="text-end fw-bold">Rp {{ number_format($sub, 0, ',', '.') }}</td>
                <td class="text-center text-uppercase">{{ $order->payment_status ?: 'unpaid' }}</td>
                <td class="text-center">{{ ucfirst($order->status ?? 'Pending') }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="10" class="text-center py-4">Tidak ada data transaksi.</td>
            </tr>
        @endforelse
    </tbody>
    <tfoot>
        <tr class="table-light fw-bold">
            <td colspan="7" class="text-end">Total Nilai Transaksi:</td>
            <td class="text-end">Rp {{ number_format($totalRevenue, 0, ',', '.') }}</td>
            <td colspan="2"></td>
        </tr>
    </tfoot>
</table>

<div class="mt-4 pt-3 border-top small text-muted d-flex justify-content-between">
    <span>Laporan dihasilkan secara otomatis oleh Sistem Greenova E-Commerce.</span>
    <span>Halaman 1 dari 1</span>
</div>

</body>
</html>
