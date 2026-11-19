@extends('layouts.admin')

@section('title', 'Kelola Voucher - Admin Greenova')

@section('content')
<div style="display:flex; flex-wrap:wrap; align-items:flex-end; justify-content:space-between; gap:16px; margin-bottom:24px;">
    <div>
        <div style="font-size:12px; font-weight:700; text-transform:uppercase; letter-spacing:0.08em; color:#166534; margin-bottom:6px;">
            Promosi Admin
        </div>
        <h1 style="margin:0; font-size:26px; font-weight:800; color:#1F2937; letter-spacing:-0.02em;">Kelola Voucher</h1>
        <p style="margin:4px 0 0; font-size:14px; color:#6B7280;">Kelola kode voucher belanja, kuota pemakaian, dan batas masa berlaku.</p>
    </div>
    <a href="{{ url('/admin/vouchers/create') }}" class="gn-btn gn-btn-primary">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        Tambah Voucher
    </a>
</div>

<div class="gn-card" style="overflow:hidden;">
    <div style="overflow-x:auto;">
        <table class="table align-middle mb-0" style="width:100%; border-collapse:collapse;">
            <thead style="background:#F7F6F2; border-bottom:1px solid #E5E7EB;">
                <tr>
                    <th style="padding:14px 20px; font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:0.06em; color:#6B7280;">No</th>
                    <th style="padding:14px 16px; font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:0.06em; color:#6B7280;">Kode</th>
                    <th style="padding:14px 16px; font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:0.06em; color:#6B7280;">Tipe</th>
                    <th style="padding:14px 16px; font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:0.06em; color:#6B7280;">Nilai</th>
                    <th style="padding:14px 16px; font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:0.06em; color:#6B7280;">Min Beli</th>
                    <th style="padding:14px 16px; font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:0.06em; color:#6B7280;">Maks Potongan</th>
                    <th style="padding:14px 16px; font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:0.06em; color:#6B7280;">Penggunaan</th>
                    <th style="padding:14px 16px; font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:0.06em; color:#6B7280;">Periode</th>
                    <th style="padding:14px 16px; font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:0.06em; color:#6B7280;">Status</th>
                    <th style="padding:14px 20px; font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:0.06em; color:#6B7280; text-align:right;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($vouchers as $voucher)
                    @php
                        $now = now();
                        $expired = $voucher->ends_at && \Carbon\Carbon::parse($voucher->ends_at)->lt($now);
                    @endphp
                    <tr style="border-bottom:1px solid #F3F4F6;">
                        <td style="padding:16px 20px; color:#6B7280; font-size:13px;">{{ $loop->iteration + ($vouchers->currentPage() - 1) * $vouchers->perPage() }}</td>
                        <td style="padding:16px;">
                            <span style="display:inline-block; background:#ECFDF5; color:#166534; border:1px solid #BBF7D0; padding:4px 10px; border-radius:8px; font-weight:700; font-size:12px; font-family:monospace;">
                                {{ $voucher->code }}
                            </span>
                        </td>
                        <td style="padding:16px; font-size:13px; color:#4B5563;">{{ $voucher->discount_type === 'percent' ? 'Persentase' : 'Nominal' }}</td>
                        <td style="padding:16px; font-weight:700; color:#244B35;">
                            @if($voucher->discount_type === 'percent')
                                {{ $voucher->discount_value }}%
                            @else
                                Rp {{ number_format($voucher->discount_value, 0, ',', '.') }}
                            @endif
                        </td>
                        <td style="padding:16px; font-size:13px; color:#4B5563;">Rp {{ number_format($voucher->min_purchase ?? 0, 0, ',', '.') }}</td>
                        <td style="padding:16px; font-size:13px; color:#4B5563;">
                            @if($voucher->max_discount)
                                Rp {{ number_format($voucher->max_discount, 0, ',', '.') }}
                            @else
                                <span style="color:#9CA3AF;">Tanpa batas</span>
                            @endif
                        </td>
                        <td style="padding:16px; font-size:13px; font-weight:600; color:#1F2937;">
                            {{ $voucher->used_count }} / {{ $voucher->usage_limit ?? 'Tanpa batas' }}
                        </td>
                        <td style="padding:16px; font-size:12px; color:#6B7280; white-space:nowrap;">
                            {{ $voucher->starts_at ? \Carbon\Carbon::parse($voucher->starts_at)->format('d/m/Y') : '-' }}
                            s/d
                            {{ $voucher->ends_at ? \Carbon\Carbon::parse($voucher->ends_at)->format('d/m/Y') : '-' }}
                        </td>
                        <td style="padding:16px;">
                            @if(!$voucher->is_active)
                                <span class="gn-badge gn-badge-neutral">Nonaktif</span>
                            @elseif($expired)
                                <span class="gn-badge gn-badge-red">Kadaluarsa</span>
                            @else
                                <span class="gn-badge gn-badge-green">Aktif</span>
                            @endif
                        </td>
                        <td style="padding:16px 20px; text-align:right; white-space:nowrap;">
                            <div style="display:inline-flex; gap:8px;">
                                <a href="{{ url('/admin/vouchers/' . $voucher->id . '/edit') }}" class="gn-btn gn-btn-outline gn-btn-sm">Edit</a>
                                <form action="{{ url('/admin/vouchers/' . $voucher->id) }}" method="POST" style="display:inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="gn-btn gn-btn-danger gn-btn-sm" onclick="return confirm('Hapus voucher ini?')">Hapus</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="10" style="padding:48px 20px; text-align:center; color:#6B7280;">
                            Belum ada data voucher.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($vouchers->hasPages())
        <div style="padding:16px 20px; border-top:1px solid #F3F4F6;">
            {{ $vouchers->links() }}
        </div>
    @endif
</div>
@endsection
