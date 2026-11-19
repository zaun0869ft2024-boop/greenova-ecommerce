@extends('layouts.admin')

@section('title', 'Kelola Produk - Admin Greenova')

@section('content')
<div style="display:flex; flex-wrap:wrap; align-items:flex-end; justify-content:space-between; gap:16px; margin-bottom:24px;">
    <div>
        <div style="font-size:12px; font-weight:700; text-transform:uppercase; letter-spacing:0.08em; color:#166534; margin-bottom:6px;">
            Katalog Admin
        </div>
        <h1 style="margin:0; font-size:26px; font-weight:800; color:#1F2937; letter-spacing:-0.02em;">Kelola Produk</h1>
        <p style="margin:4px 0 0; font-size:14px; color:#6B7280;">Tambah, impor CSV, filter kategori, dan kelola stok produk Greenova.</p>
    </div>
    <a href="{{ route('admin.products.create') }}" class="gn-btn gn-btn-primary">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        Tambah Produk
    </a>
</div>

<div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(220px, 1fr)); gap:16px; margin-bottom:20px;">
    <div class="gn-card" style="padding:20px;">
        <div style="font-size:12px; font-weight:700; text-transform:uppercase; letter-spacing:0.06em; color:#6B7280;">Total Produk</div>
        <div style="font-size:28px; font-weight:800; color:#244B35; margin-top:6px;">{{ $totalProducts ?? 0 }}</div>
    </div>
    <div class="gn-card" style="padding:20px;">
        <div style="font-size:12px; font-weight:700; text-transform:uppercase; letter-spacing:0.06em; color:#6B7280;">Total Kategori</div>
        <div style="font-size:28px; font-weight:800; color:#166534; margin-top:6px;">{{ $totalCategories ?? 0 }}</div>
    </div>
</div>

<div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(min(100%, 300px), 1fr)); gap:16px; margin-bottom:20px;">
    <div class="gn-card" style="padding:20px;">
        <div style="font-size:14px; font-weight:700; color:#1F2937; margin-bottom:4px;">Import Produk via CSV</div>
        <p style="font-size:12px; color:#6B7280; margin:0 0 12px;">Unggah file CSV produk dengan format kolom standar Greenova.</p>
        <form action="{{ route('admin.products.import') }}" method="POST" enctype="multipart/form-data" style="display:flex; flex-wrap:wrap; gap:10px; align-items:center;">
            @csrf
            <input type="file" name="file" accept=".csv" required class="gn-input" style="flex:1; min-width:180px; padding:8px 12px;">
            <button type="submit" class="gn-btn gn-btn-outline">Upload CSV</button>
        </form>
    </div>

    <div class="gn-card" style="padding:20px;">
        <div style="font-size:14px; font-weight:700; color:#1F2937; margin-bottom:4px;">Cari &amp; Filter Produk</div>
        <p style="font-size:12px; color:#6B7280; margin:0 0 12px;">Saring produk berdasarkan nama atau kategori.</p>
        <form action="{{ route('admin.products.index') }}" method="GET" style="display:flex; flex-wrap:wrap; gap:10px; align-items:center;">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama produk..." class="gn-input" style="flex:1; min-width:160px;">
            <select name="category" class="gn-select" style="width:170px;">
                <option value="">Semua Kategori</option>
                @foreach($categories as $cat)
                    <option value="{{ $cat->cat_id }}" {{ (string) request('category') === (string) $cat->cat_id ? 'selected' : '' }}>
                        {{ $cat->cat_title }}
                    </option>
                @endforeach
            </select>
            <button type="submit" class="gn-btn gn-btn-primary">Filter</button>
            @if(request('search') || request('category'))
                <a href="{{ route('admin.products.index') }}" class="gn-btn gn-btn-outline">Reset</a>
            @endif
        </form>
    </div>
</div>

<div class="gn-card" style="overflow:hidden;">
    <div style="overflow-x:auto;">
        <table class="table align-middle mb-0" style="width:100%; border-collapse:collapse;">
            <thead style="background:#F7F6F2; border-bottom:1px solid #E5E7EB;">
                <tr>
                    <th style="padding:14px 20px; font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:0.06em; color:#6B7280;">Produk</th>
                    <th style="padding:14px 16px; font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:0.06em; color:#6B7280;">Kategori</th>
                    <th style="padding:14px 16px; font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:0.06em; color:#6B7280;">Harga</th>
                    <th style="padding:14px 16px; font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:0.06em; color:#6B7280;">Stok</th>
                    <th style="padding:14px 20px; font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:0.06em; color:#6B7280; text-align:right;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($products as $product)
                    @php
                        $img = $product->product_image ?: '';
                        $imgUrl = $img
                            ? (str_starts_with($img, 'http') ? $img : asset('product_images/' . $img))
                            : 'https://placehold.co/100x100/eaf2ec/244b35?text=Produk';
                        $stock = (int) ($product->stock ?? 0);
                    @endphp
                    <tr style="border-bottom:1px solid #F3F4F6;">
                        <td style="padding:14px 20px;">
                            <div style="display:flex; align-items:center; gap:12px;">
                                <img src="{{ $imgUrl }}" alt="{{ $product->product_title }}" onerror="this.onerror=null; this.src='https://placehold.co/100x100/eaf2ec/244b35?text=Produk';" style="width:48px; height:48px; border-radius:10px; object-fit:cover; border:1px solid #E5E7EB; flex-shrink:0;">
                                <div>
                                    <div style="font-weight:700; color:#1F2937; font-size:14px;">{{ $product->product_title }}</div>
                                    <div style="font-size:12px; color:#6B7280;">ID #{{ $product->product_id }}</div>
                                </div>
                            </div>
                        </td>
                        <td style="padding:14px 16px;">
                            <span class="gn-badge gn-badge-green">{{ $product->category->cat_title ?? 'Tanpa Kategori' }}</span>
                        </td>
                        <td style="padding:14px 16px; font-weight:700; color:#244B35; white-space:nowrap;">
                            Rp {{ number_format((float) $product->product_price, 0, ',', '.') }}
                        </td>
                        <td style="padding:14px 16px;">
                            @if($stock <= 0)
                                <span class="gn-badge gn-badge-red">Habis (0)</span>
                            @elseif($stock <= 5)
                                <span class="gn-badge gn-badge-amber">Menipis ({{ $stock }})</span>
                            @else
                                <span class="gn-badge gn-badge-neutral">{{ $stock }} unit</span>
                            @endif
                        </td>
                        <td style="padding:14px 20px; text-align:right; white-space:nowrap;">
                            <div style="display:inline-flex; gap:8px;">
                                <a href="{{ route('admin.products.edit', $product->product_id) }}" class="gn-btn gn-btn-outline gn-btn-sm">Edit</a>
                                <form action="{{ route('admin.products.destroy', $product->product_id) }}" method="POST" style="display:inline;" onsubmit="return confirm('Hapus produk ini?');">
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
                            Tidak ada produk ditemukan.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($products->hasPages())
        <div style="padding:16px 20px; border-top:1px solid #F3F4F6;">
            {{ $products->appends(request()->query())->links() }}
        </div>
    @endif
</div>
@endsection
