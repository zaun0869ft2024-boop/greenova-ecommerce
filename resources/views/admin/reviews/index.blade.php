@extends('layouts.admin')

@section('title', 'Moderasi Ulasan - Admin Greenova')

@section('content')
<div style="display:flex; flex-wrap:wrap; align-items:flex-end; justify-content:space-between; gap:16px; margin-bottom:24px;">
    <div>
        <div style="font-size:12px; font-weight:700; text-transform:uppercase; letter-spacing:0.08em; color:#166534; margin-bottom:6px;">
            Kualitas Layanan
        </div>
        <h1 style="margin:0; font-size:26px; font-weight:800; color:#1F2937; letter-spacing:-0.02em;">Moderasi Ulasan</h1>
        <p style="margin:4px 0 0; font-size:14px; color:#6B7280;">Pantau rating produk dan kelola ulasan dari pelanggan.</p>
    </div>
</div>

<div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(220px, 1fr)); gap:16px; margin-bottom:20px;">
    <div class="gn-card" style="padding:20px;">
        <div style="font-size:12px; font-weight:700; text-transform:uppercase; letter-spacing:0.06em; color:#6B7280;">Total Ulasan</div>
        <div style="font-size:28px; font-weight:800; color:#244B35; margin-top:6px;">{{ $totalReviews ?? 0 }}</div>
    </div>
    <div class="gn-card" style="padding:20px;">
        <div style="font-size:12px; font-weight:700; text-transform:uppercase; letter-spacing:0.06em; color:#6B7280;">Rata-rata Rating</div>
        <div style="font-size:28px; font-weight:800; color:#D97706; margin-top:6px;">
            {{ number_format((float) ($averageRating ?? 0), 1) }} <span style="font-size:16px; color:#6B7280; font-weight:600;">/ 5.0</span>
        </div>
    </div>
</div>

<div class="gn-card" style="padding:18px 20px; margin-bottom:20px;">
    <form action="{{ route('admin.reviews.index') }}" method="GET" style="display:flex; flex-wrap:wrap; gap:12px; align-items:center;">
        <input type="text" name="search" value="{{ request('search') }}" class="gn-input" placeholder="Cari nama pelanggan, produk, atau komentar..." style="flex:1; min-width:220px;">
        <select name="rating" class="gn-select" style="width:180px;">
            <option value="">Semua Rating</option>
            @for($r = 5; $r >= 1; $r--)
                <option value="{{ $r }}" {{ (string) request('rating') === (string) $r ? 'selected' : '' }}>{{ $r }} Bintang</option>
            @endfor
        </select>
        <button type="submit" class="gn-btn gn-btn-primary">Filter</button>
        @if(request('search') || request('rating'))
            <a href="{{ route('admin.reviews.index') }}" class="gn-btn gn-btn-outline">Reset</a>
        @endif
    </form>
</div>

<div class="gn-card" style="overflow:hidden;">
    <div style="overflow-x:auto;">
        <table class="table align-middle mb-0" style="width:100%; border-collapse:collapse;">
            <thead style="background:#F7F6F2; border-bottom:1px solid #E5E7EB;">
                <tr>
                    <th style="padding:14px 20px; font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:0.06em; color:#6B7280;">#</th>
                    <th style="padding:14px 16px; font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:0.06em; color:#6B7280;">Pengguna</th>
                    <th style="padding:14px 16px; font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:0.06em; color:#6B7280;">Produk</th>
                    <th style="padding:14px 16px; font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:0.06em; color:#6B7280;">Rating</th>
                    <th style="padding:14px 16px; font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:0.06em; color:#6B7280;">Komentar</th>
                    <th style="padding:14px 16px; font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:0.06em; color:#6B7280;">Tanggal</th>
                    <th style="padding:14px 20px; font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:0.06em; color:#6B7280; text-align:right;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($reviews as $review)
                    <tr style="border-bottom:1px solid #F3F4F6;">
                        <td style="padding:16px 20px; color:#6B7280; font-size:13px;">{{ $reviews->firstItem() + $loop->index }}</td>
                        <td style="padding:16px;">
                            <div style="font-weight:700; color:#1F2937; font-size:14px;">{{ $review->user->name ?? '(Dihapus)' }}</div>
                            <div style="font-size:12px; color:#6B7280;">{{ $review->user->email ?? '' }}</div>
                        </td>
                        <td style="padding:16px;">
                            <a href="{{ route('product.detail', $review->product_id) }}" target="_blank" style="color:#244B35; font-weight:600; text-decoration:none;">
                                {{ Str::limit($review->product->product_title ?? '(Dihapus)', 40) }}
                            </a>
                        </td>
                        <td style="padding:16px; white-space:nowrap;">
                            <span class="gn-badge gn-badge-amber">{{ $review->rating }} / 5</span>
                        </td>
                        <td style="padding:16px; color:#374151; font-size:13px; max-width:340px;">
                            @if($review->comment)
                                {{ Str::limit($review->comment, 100) }}
                            @else
                                <span style="color:#9CA3AF; font-style:italic;">Tidak ada komentar.</span>
                            @endif
                        </td>
                        <td style="padding:16px; font-size:12px; color:#6B7280; white-space:nowrap;">
                            {{ $review->created_at->format('d M Y, H:i') }}
                        </td>
                        <td style="padding:16px 20px; text-align:right;">
                            <form action="{{ route('admin.reviews.destroy', $review->id) }}" method="POST" style="display:inline;" onsubmit="return confirm('Apakah Anda yakin ingin menghapus ulasan ini? Tindakan ini tidak dapat dibatalkan.');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="gn-btn gn-btn-danger gn-btn-sm">Hapus</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" style="padding:48px 20px; text-align:center; color:#6B7280;">
                            Tidak ada ulasan yang ditemukan.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($reviews->hasPages())
        <div style="padding:16px 20px; border-top:1px solid #F3F4F6;">
            {{ $reviews->appends(request()->query())->links() }}
        </div>
    @endif
</div>
@endsection
