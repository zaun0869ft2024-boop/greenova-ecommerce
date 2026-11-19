@extends('layouts.admin')

@section('title', 'Kelola Pesanan & Pembayaran - Admin Greenova')

@section('content')
@php
    $statusBadgeMap = [
        'Pending' => 'gn-badge-amber',
        'Diproses' => 'gn-badge-blue',
        'Dikirim' => 'gn-badge-blue',
        'Selesai' => 'gn-badge-green',
        'Dibatalkan' => 'gn-badge-red',
    ];
    $paymentBadgeMap = [
        'paid' => 'gn-badge-green',
        'pending' => 'gn-badge-amber',
        'unpaid' => 'gn-badge-neutral',
        'failed' => 'gn-badge-red',
        'expired' => 'gn-badge-neutral',
        'cancelled' => 'gn-badge-red',
    ];
@endphp

<div style="display:flex; flex-wrap:wrap; align-items:flex-end; justify-content:space-between; gap:16px; margin-bottom:24px;">
    <div>
        <div style="font-size:12px; font-weight:700; text-transform:uppercase; letter-spacing:0.08em; color:#166534; margin-bottom:6px;">
            Manajemen Transaksi
        </div>
        <h1 style="margin:0; font-size:26px; font-weight:800; color:#1F2937; letter-spacing:-0.02em;">Kelola Pesanan &amp; Pembayaran</h1>
        <p style="margin:4px 0 0; font-size:14px; color:#6B7280;">Pantau status pembayaran Midtrans dan alur pemenuhan pesanan secara terpisah dan aman.</p>
    </div>
</div>

<!-- Statistik Ringkas -->
<div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap:16px; margin-bottom:24px;">
    <div class="gn-card" style="padding:16px 20px;">
        <div style="font-size:12px; font-weight:600; color:#6B7280;">Total Pesanan</div>
        <div style="font-size:24px; font-weight:800; color:#1F2937; margin-top:4px;">{{ $statistics['total'] }}</div>
    </div>
    <div class="gn-card" style="padding:16px 20px;">
        <div style="font-size:12px; font-weight:600; color:#166534;">Pembayaran Lunas</div>
        <div style="font-size:24px; font-weight:800; color:#166534; margin-top:4px;">{{ $statistics['paid'] }}</div>
    </div>
    <div class="gn-card" style="padding:16px 20px;">
        <div style="font-size:12px; font-weight:600; color:#D97706;">Menunggu Pembayaran</div>
        <div style="font-size:24px; font-weight:800; color:#D97706; margin-top:4px;">{{ $statistics['pending_payment'] }}</div>
    </div>
    <div class="gn-card" style="padding:16px 20px;">
        <div style="font-size:12px; font-weight:600; color:#2563EB;">Pesanan Diproses</div>
        <div style="font-size:24px; font-weight:800; color:#2563EB; margin-top:4px;">{{ $statistics['order_diproses'] }}</div>
    </div>
    <div class="gn-card" style="padding:16px 20px;">
        <div style="font-size:12px; font-weight:600; color:#059669;">Pesanan Selesai</div>
        <div style="font-size:24px; font-weight:800; color:#059669; margin-top:4px;">{{ $statistics['order_selesai'] }}</div>
    </div>
</div>

<!-- Filter & Pencarian Bar -->
<div class="gn-card" style="padding:16px 20px; margin-bottom:20px;">
    <form method="GET" action="{{ route('admin.orders.index') }}" style="display:flex; flex-wrap:wrap; align-items:center; justify-content:space-between; gap:12px;">
        <!-- Filter Tabs -->
        <div style="display:flex; flex-wrap:wrap; gap:8px;">
            <a href="{{ route('admin.orders.index', array_merge(request()->except(['payment_status', 'page']), ['payment_status' => 'semua'])) }}"
               class="gn-btn gn-btn-sm {{ ($paymentFilter ?? 'semua') === 'semua' ? 'gn-btn-primary' : 'gn-btn-outline' }}">
                Semua Pembayaran
            </a>
            <a href="{{ route('admin.orders.index', array_merge(request()->except(['payment_status', 'page']), ['payment_status' => 'paid'])) }}"
               class="gn-btn gn-btn-sm {{ ($paymentFilter ?? '') === 'paid' ? 'gn-btn-primary' : 'gn-btn-outline' }}">
                Lunas
            </a>
            <a href="{{ route('admin.orders.index', array_merge(request()->except(['payment_status', 'page']), ['payment_status' => 'pending'])) }}"
               class="gn-btn gn-btn-sm {{ ($paymentFilter ?? '') === 'pending' ? 'gn-btn-primary' : 'gn-btn-outline' }}">
                Menunggu Pembayaran
            </a>
            <a href="{{ route('admin.orders.index', array_merge(request()->except(['payment_status', 'page']), ['payment_status' => 'cancelled'])) }}"
               class="gn-btn gn-btn-sm {{ ($paymentFilter ?? '') === 'cancelled' ? 'gn-btn-primary' : 'gn-btn-outline' }}">
                Dibatalkan / Gagal
            </a>
        </div>

        <!-- Search Input -->
        <div style="display:flex; gap:8px; min-width:260px;">
            <input type="hidden" name="payment_status" value="{{ $paymentFilter ?? 'semua' }}">
            <input type="text"
                   name="search"
                   value="{{ $search ?? '' }}"
                   placeholder="Cari ID, referensi, pelanggan..."
                   class="gn-input"
                   style="font-size:13px; padding:6px 12px;">
            <button type="submit" class="gn-btn gn-btn-primary gn-btn-sm" style="padding:6px 14px;">
                Cari
            </button>
            @if(!empty($search) || ($paymentFilter ?? 'semua') !== 'semua' || ($orderFilter ?? 'semua') !== 'semua')
                <a href="{{ route('admin.orders.index') }}" class="gn-btn gn-btn-outline gn-btn-sm" title="Reset filter">
                    Reset
                </a>
            @endif
        </div>
    </form>
</div>

<!-- Tabel Utama Pesanan -->
<div class="gn-card" style="overflow:hidden;">
    <div style="overflow-x:auto;">
        <table class="table align-middle mb-0" style="width:100%; border-collapse:collapse;">
            <thead style="background:#F7F6F2; border-bottom:1px solid #E5E7EB;">
                <tr>
                    <th style="padding:14px 20px; font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:0.06em; color:#6B7280;">Order &amp; Ref</th>
                    <th style="padding:14px 16px; font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:0.06em; color:#6B7280;">Pelanggan</th>
                    <th style="padding:14px 16px; font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:0.06em; color:#6B7280;">Produk</th>
                    <th style="padding:14px 16px; font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:0.06em; color:#6B7280;">Qty</th>
                    <th style="padding:14px 16px; font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:0.06em; color:#6B7280;">Total</th>
                    <th style="padding:14px 16px; font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:0.06em; color:#6B7280;">Status Pembayaran</th>
                    <th style="padding:14px 16px; font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:0.06em; color:#6B7280;">Status Pemenuhan</th>
                    <th style="padding:14px 20px; font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:0.06em; color:#6B7280;">Tanggal</th>
                </tr>
            </thead>
            <tbody>
                @forelse($orders as $order)
                    @php
                        $orderBadge = $statusBadgeMap[$order->status] ?? 'gn-badge-neutral';
                        $payStatus = strtolower(trim((string)($order->payment_status ?? 'unpaid')));
                        $payBadge = $paymentBadgeMap[$payStatus] ?? 'gn-badge-neutral';
                        $payLabel = app(\App\Services\PaymentService::class)->labelForOrder($order);
                        $isMidtrans = in_array(strtolower((string)$order->payment_method), ['midtrans']) || in_array(strtolower((string)$order->payment_gateway), ['midtrans']);
                    @endphp
                    <tr style="border-bottom:1px solid #F3F4F6;">
                        <td style="padding:16px 20px; font-weight:700; color:#244B35; white-space:nowrap;">
                            #{{ $order->orders_id }}
                            @if(!empty($order->payment_reference))
                                <div style="font-size:11px; font-weight:500; font-family:monospace; color:#6B7280; margin-top:2px;">
                                    Ref: {{ $order->payment_reference }}
                                </div>
                            @endif
                        </td>
                        <td style="padding:16px;">
                            <div style="font-weight:600; color:#1F2937; font-size:14px;">{{ $order->user->name ?? 'User #' . $order->user_id }}</div>
                            @if(!empty($order->recipient_name))
                                <div style="font-size:12px; color:#6B7280;">Penerima: {{ $order->recipient_name }}</div>
                            @endif
                        </td>
                        <td style="padding:16px; font-size:13px; color:#1F2937; font-weight:500;">
                            {{ $order->product->product_title ?? 'Produk #' . $order->product_id }}
                        </td>
                        <td style="padding:16px; font-size:13px; color:#4B5563; font-weight:600;">{{ $order->qty }}</td>
                        <td style="padding:16px; font-weight:700; color:#244B35; white-space:nowrap;">
                            Rp {{ number_format((float) $order->subtotal, 0, ',', '.') }}
                        </td>
                        <td style="padding:16px; white-space:nowrap;">
                            <div style="display:flex; align-items:center; gap:6px;">
                                <span class="gn-badge {{ $payBadge }}">{{ $payLabel }}</span>
                                @if($isMidtrans && $payStatus === 'pending')
                                    <form action="{{ route('admin.orders.sync_midtrans', $order->orders_id) }}" method="POST" style="margin:0;">
                                        @csrf
                                        <button type="submit" class="gn-btn gn-btn-outline gn-btn-sm" style="padding:2px 8px; font-size:11px;" title="Cek status langsung ke Midtrans API">
                                            ↻ Cek Midtrans
                                        </button>
                                    </form>
                                @endif
                            </div>
                            @if(!empty($order->payment_method))
                                <div style="font-size:11px; color:#6B7280; margin-top:4px; text-transform:uppercase; font-weight:600;">
                                    {{ str_replace('_', ' ', $order->payment_method) }}
                                </div>
                            @endif
                            @if(!empty($order->paid_at))
                                <div style="font-size:11px; color:#166534; margin-top:2px; font-weight:500;">
                                    Lunas: {{ \Carbon\Carbon::parse($order->paid_at)->format('d/m/y H:i') }}
                                </div>
                            @endif
                        </td>
                        <td style="padding:16px;">
                            <div style="display:flex; align-items:center; gap:8px; flex-wrap:wrap;">
                                <span class="gn-badge {{ $orderBadge }}">{{ $order->status }}</span>
                                <form action="{{ route('admin.orders.status', $order->orders_id) }}" method="POST" style="display:inline-flex; gap:6px; align-items:center;">
                                    @csrf
                                    @method('PUT')
                                    <select name="status" class="gn-select" style="padding:6px 10px; font-size:12px; min-width:120px;" onchange="this.form.submit()">
                                        @foreach(['Pending', 'Diproses', 'Dikirim', 'Selesai', 'Dibatalkan'] as $st)
                                            <option value="{{ $st }}" {{ $order->status === $st ? 'selected' : '' }}>{{ $st }}</option>
                                        @endforeach
                                    </select>
                                </form>
                            </div>
                        </td>
                        <td style="padding:16px 20px; font-size:12px; color:#6B7280; white-space:nowrap;">
                            {{ $order->date ? \Carbon\Carbon::parse($order->date)->format('d M Y, H:i') : '-' }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" style="padding:48px 20px; text-align:center; color:#6B7280;">
                            Belum ada pesanan yang sesuai dengan filter.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if(method_exists($orders, 'links') && $orders->hasPages())
        <div style="padding:16px 20px; border-top:1px solid #F3F4F6;">
            {{ $orders->links() }}
        </div>
    @endif
</div>
@endsection
