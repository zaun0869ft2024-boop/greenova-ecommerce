@extends('layouts.admin')

@section('title', 'Kategori Produk - Admin Greenova')

@section('content')
<div style="display:flex; flex-wrap:wrap; align-items:flex-end; justify-content:space-between; gap:16px; margin-bottom:24px;">
    <div>
        <div style="font-size:12px; font-weight:700; text-transform:uppercase; letter-spacing:0.08em; color:#166534; margin-bottom:6px;">
            Katalog Admin
        </div>
        <h1 style="margin:0; font-size:26px; font-weight:800; color:#1F2937; letter-spacing:-0.02em;">Kategori Produk</h1>
        <p style="margin:4px 0 0; font-size:14px; color:#6B7280;">Kelola kategori produk beserta jumlah produk aktif di setiap kategori.</p>
    </div>
    <a href="{{ route('admin.categories.create') }}" class="gn-btn gn-btn-primary">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        Tambah Kategori
    </a>
</div>

<div class="gn-card" style="overflow:hidden;">
    <div style="overflow-x:auto;">
        <table class="table align-middle mb-0" style="width:100%; border-collapse:collapse;">
            <thead style="background:#F7F6F2; border-bottom:1px solid #E5E7EB;">
                <tr>
                    <th style="padding:14px 20px; font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:0.06em; color:#6B7280;">ID</th>
                    <th style="padding:14px 16px; font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:0.06em; color:#6B7280;">Nama Kategori</th>
                    <th style="padding:14px 16px; font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:0.06em; color:#6B7280;">Deskripsi</th>
                    <th style="padding:14px 16px; font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:0.06em; color:#6B7280;">Jumlah Produk</th>
                    <th style="padding:14px 20px; font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:0.06em; color:#6B7280; text-align:right;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($categories as $category)
                    <tr style="border-bottom:1px solid #F3F4F6;">
                        <td style="padding:16px 20px; font-size:13px; color:#6B7280; font-weight:600;">#{{ $category->cat_id }}</td>
                        <td style="padding:16px; font-weight:700; color:#1F2937; font-size:14px;">{{ $category->cat_title }}</td>
                        <td style="padding:16px; font-size:13px; color:#4B5563; max-width:340px;">{{ $category->cat_description ?: '-' }}</td>
                        <td style="padding:16px;">
                            <span class="gn-badge gn-badge-green">{{ $category->products_count ?? 0 }} Produk</span>
                        </td>
                        <td style="padding:16px 20px; text-align:right; white-space:nowrap;">
                            <div style="display:inline-flex; gap:8px; align-items:center;">
                                <a href="{{ route('admin.products.index', ['category' => $category->cat_id]) }}" class="gn-btn gn-btn-outline gn-btn-sm">
                                    Lihat Produk
                                </a>
                                <a href="{{ route('admin.categories.edit', $category->cat_id) }}" class="gn-btn gn-btn-outline gn-btn-sm">
                                    Edit
                                </a>
                                <form action="{{ route('admin.categories.destroy', $category->cat_id) }}" method="POST" style="display:inline;" onsubmit="return confirm('Hapus kategori ini?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="gn-btn gn-btn-danger gn-btn-sm">Hapus</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" style="padding:48px 20px; text-align:center; color:#6B7280;">
                            Belum ada kategori produk.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
