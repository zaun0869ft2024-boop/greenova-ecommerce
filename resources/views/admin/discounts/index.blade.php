@extends('layouts.admin')

@section('title', 'Kelola Diskon Produk - Admin Greenova')

@section('content')
<div style="display:flex; flex-wrap:wrap; align-items:flex-end; justify-content:space-between; gap:16px; margin-bottom:24px;">
    <div>
        <div style="font-size:12px; font-weight:700; text-transform:uppercase; letter-spacing:0.08em; color:#166534; margin-bottom:6px;">
            Promosi Admin
        </div>
        <h1 style="margin:0; font-size:26px; font-weight:800; color:#1F2937; letter-spacing:-0.02em;">Kelola Diskon Produk</h1>
        <p style="margin:4px 0 0; font-size:14px; color:#6B7280;">Atur potongan harga langsung pada produk tertentu beserta periode berlakunya.</p>
    </div>
    <a href="{{ url('/admin/discounts/create') }}" class="gn-btn gn-btn-primary">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        Tambah Diskon
    </a>
</div>

<div class="gn-card" style="overflow:hidden;">
    <div style="overflow-x:auto;">
        <table class="table align-middle mb-0" style="width:100%; border-collapse:collapse;">
            <thead style="background:#F7F6F2; border-bottom:1px solid #E5E7EB;">
                <tr>
                    <th style="padding:14px 20px; font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:0.06em; color:#6B7280;">No</th>
                    <th style="padding:14px 16px; font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:0.06em; color:#6B7280;">Produk</th>
                    <th style="padding:14px 16px; font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:0.06em; color:#6B7280;">Tipe</th>
                    <th style="padding:14px 16px; font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:0.06em; color:#6B7280;">Nilai Diskon</th>
                    <th style="padding:14px 16px; font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:0.06em; color:#6B7280;">Mulai</th>
                    <th style="padding:14px 16px; font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:0.06em; color:#6B7280;">Berakhir</th>
                    <th style="padding:14px 16px; font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:0.06em; color:#6B7280;">Status</th>
                    <th style="padding:14px 20px; font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:0.06em; color:#6B7280; text-align:right;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($discounts as $discount)
                    @php
                        $now = now();
                        $expired = $discount->ends_at && \Carbon\Carbon::parse($discount->ends_at)->lt($now);
                    @endphp
                    <tr style="border-bottom:1px solid #F3F4F6;">
                        <td style="padding:16px 20px; color:#6B7280; font-size:13px;">{{ $loop->iteration + ($discounts->currentPage() - 1) * $discounts->perPage() }}</td>
                        <td style="padding:16px; font-weight:700; color:#1F2937; font-size:14px;">{{ $discount->product->product_title ?? '-' }}</td>
                        <td style="padding:16px; font-size:13px; color:#4B5563;">{{ $discount->discount_type === 'percent' ? 'Persentase' : 'Nominal Rupiah' }}</td>
                        <td style="padding:16px; font-weight:700; color:#244B35;">
                            @if($discount->discount_type === 'percent')
                                {{ $discount->discount_value }}%
                            @else
                                Rp {{ number_format($discount->discount_value, 0, ',', '.') }}
                            @endif
                        </td>
                        <td style="padding:16px; font-size:12px; color:#6B7280; white-space:nowrap;">{{ $discount->starts_at ? \Carbon\Carbon::parse($discount->starts_at)->format('d/m/Y H:i') : '-' }}</td>
                        <td style="padding:16px; font-size:12px; color:#6B7280; white-space:nowrap;">{{ $discount->ends_at ? \Carbon\Carbon::parse($discount->ends_at)->format('d/m/Y H:i') : '-' }}</td>
                        <td style="padding:16px;">
                            @if(!$discount->is_active)
                                <span class="gn-badge gn-badge-neutral">Nonaktif</span>
                            @elseif($expired)
                                <span class="gn-badge gn-badge-red">Kadaluarsa</span>
                            @else
                                <span class="gn-badge gn-badge-green">Aktif</span>
                            @endif
                        </td>
                        <td style="padding:16px 20px; text-align:right; white-space:nowrap;">
                            <div style="display:inline-flex; gap:8px;">
                                <a href="{{ url('/admin/discounts/' . $discount->id . '/edit') }}" class="gn-btn gn-btn-outline gn-btn-sm">Edit</a>
                                <form action="{{ url('/admin/discounts/' . $discount->id) }}" method="POST" style="display:inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="gn-btn gn-btn-danger gn-btn-sm" onclick="return confirm('Hapus diskon ini?')">Hapus</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" style="padding:48px 20px; text-align:center; color:#6B7280;">
                            Belum ada data diskon.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($discounts->hasPages())
        <div style="padding:16px 20px; border-top:1px solid #F3F4F6;">
            {{ $discounts->links() }}
        </div>
    @endif
</div>
@endsection
