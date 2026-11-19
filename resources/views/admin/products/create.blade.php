@extends('layouts.admin')

@section('title', 'Tambah Produk - Admin Greenova')
@section('page-title', 'Tambah Produk')

@section('content')
<div style="max-width:740px;">
    <div style="margin-bottom:24px;">
        <div style="font-size:12px; font-weight:700; text-transform:uppercase; letter-spacing:0.08em; color:#166534; margin-bottom:6px;">
            Katalog Admin
        </div>
        <h1 style="margin:0; font-size:26px; font-weight:800; color:#1F2937; letter-spacing:-0.02em;">Tambah Produk</h1>
        <p style="margin:4px 0 0; font-size:14px; color:#6B7280;">Tambahkan produk baru ke katalog Greenova.</p>
    </div>

    <div class="gn-card" style="padding:28px;">
        <form action="{{ route('admin.products.store') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <div style="margin-bottom:18px;">
                <label style="display:block; font-size:13px; font-weight:600; color:#374151; margin-bottom:6px;">Nama Produk</label>
                <input type="text" name="product_title" class="gn-input" value="{{ old('product_title') }}" required>
                @error('product_title')
                    <div style="color:#DC2626; font-size:12px; margin-top:4px;">{{ $message }}</div>
                @enderror
            </div>

            <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(220px, 1fr)); gap:16px; margin-bottom:18px;">
                <div>
                    <label style="display:block; font-size:13px; font-weight:600; color:#374151; margin-bottom:6px;">Kategori</label>
                    <select name="product_cat" class="gn-select" required>
                        <option value="">Pilih kategori</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->cat_id }}" {{ old('product_cat') == $category->cat_id ? 'selected' : '' }}>
                                {{ $category->cat_title }}
                            </option>
                        @endforeach
                    </select>
                    @error('product_cat')
                        <div style="color:#DC2626; font-size:12px; margin-top:4px;">{{ $message }}</div>
                    @enderror
                </div>

                <div>
                    <label style="display:block; font-size:13px; font-weight:600; color:#374151; margin-bottom:6px;">Harga (Rp)</label>
                    <input type="number" name="product_price" class="gn-input" min="0" step="1" value="{{ old('product_price') }}" required>
                    @error('product_price')
                        <div style="color:#DC2626; font-size:12px; margin-top:4px;">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div style="margin-bottom:18px;">
                <label style="display:block; font-size:13px; font-weight:600; color:#374151; margin-bottom:6px;">Deskripsi Produk</label>
                <textarea name="product_desc" class="gn-textarea" rows="4">{{ old('product_desc') }}</textarea>
                @error('product_desc')
                    <div style="color:#DC2626; font-size:12px; margin-top:4px;">{{ $message }}</div>
                @enderror
            </div>

            <div style="margin-bottom:24px;">
                <label style="display:block; font-size:13px; font-weight:600; color:#374151; margin-bottom:6px;">Gambar Produk</label>
                <input type="file" name="product_image" class="gn-input" accept=".jpg,.jpeg,.png,.webp" required style="padding:8px 12px;">
                <div style="font-size:12px; color:#6B7280; margin-top:6px;">Format JPG, JPEG, PNG, atau WEBP. Maksimal 2 MB.</div>
                @error('product_image')
                    <div style="color:#DC2626; font-size:12px; margin-top:4px;">{{ $message }}</div>
                @enderror
            </div>

            <div style="display:flex; gap:10px; align-items:center;">
                <button type="submit" class="gn-btn gn-btn-primary">Simpan Produk</button>
                <a href="{{ route('admin.products.index') }}" class="gn-btn gn-btn-outline">Batal</a>
            </div>
        </form>
    </div>
</div>
@endsection
