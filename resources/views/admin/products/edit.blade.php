@extends('layouts.admin')

@section('title', 'Edit Produk - Admin Greenova')
@section('page-title', 'Edit Produk')

@section('content')
<div style="max-width:740px;">
    <div style="margin-bottom:24px;">
        <div style="font-size:12px; font-weight:700; text-transform:uppercase; letter-spacing:0.08em; color:#166534; margin-bottom:6px;">
            Katalog Admin
        </div>
        <h1 style="margin:0; font-size:26px; font-weight:800; color:#1F2937; letter-spacing:-0.02em;">Edit Produk</h1>
        <p style="margin:4px 0 0; font-size:14px; color:#6B7280;">Perbarui informasi, kategori, harga, stok, dan gambar produk.</p>
    </div>

    <div class="gn-card" style="padding:28px;">
        <form action="{{ route('admin.products.update', $product->product_id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div style="margin-bottom:18px;">
                <label style="display:block; font-size:13px; font-weight:600; color:#374151; margin-bottom:6px;">Nama Produk</label>
                <input type="text" name="product_title" class="gn-input" value="{{ old('product_title', $product->product_title) }}" required>
                @error('product_title')
                    <div style="color:#DC2626; font-size:12px; margin-top:4px;">{{ $message }}</div>
                @enderror
            </div>

            <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(180px, 1fr)); gap:16px; margin-bottom:18px;">
                <div>
                    <label style="display:block; font-size:13px; font-weight:600; color:#374151; margin-bottom:6px;">Kategori</label>
                    <select name="product_cat" class="gn-select" required>
                        <option value="">Pilih kategori</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->cat_id }}" {{ old('product_cat', $product->product_cat) == $category->cat_id ? 'selected' : '' }}>
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
                    <input type="number" name="product_price" class="gn-input" value="{{ old('product_price', $product->product_price) }}" min="0" step="1" required>
                    @error('product_price')
                        <div style="color:#DC2626; font-size:12px; margin-top:4px;">{{ $message }}</div>
                    @enderror
                </div>

                <div>
                    <label style="display:block; font-size:13px; font-weight:600; color:#374151; margin-bottom:6px;">Stok Produk</label>
                    <input type="number" name="stock" class="gn-input" value="{{ old('stock', $product->stock ?? 0) }}" min="0" step="1" required>
                    @error('stock')
                        <div style="color:#DC2626; font-size:12px; margin-top:4px;">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div style="margin-bottom:18px;">
                <label style="display:block; font-size:13px; font-weight:600; color:#374151; margin-bottom:6px;">Deskripsi Produk</label>
                <textarea name="product_desc" class="gn-textarea" rows="4">{{ old('product_desc', $product->product_desc) }}</textarea>
                @error('product_desc')
                    <div style="color:#DC2626; font-size:12px; margin-top:4px;">{{ $message }}</div>
                @enderror
            </div>

            <div style="margin-bottom:24px;">
                <label style="display:block; font-size:13px; font-weight:600; color:#374151; margin-bottom:6px;">Gambar Produk</label>
                <input type="file" name="product_image" class="gn-input" accept=".jpg,.jpeg,.png,.webp" style="padding:8px 12px;">
                <div style="font-size:12px; color:#6B7280; margin-top:6px;">Kosongkan jika tidak ingin mengganti gambar.</div>
                @error('product_image')
                    <div style="color:#DC2626; font-size:12px; margin-top:4px;">{{ $message }}</div>
                @enderror

                @if($product->product_image)
                    <div style="margin-top:12px;">
                        <img src="{{ asset('product_images/' . $product->product_image) }}" alt="{{ $product->product_title }}" onerror="this.onerror=null; this.src='https://placehold.co/150x150/eaf2ec/244b35?text=Produk';" style="width:110px; height:110px; object-fit:cover; border-radius:12px; border:1px solid #E5E7EB;">
                    </div>
                @endif
            </div>

            <div style="display:flex; gap:10px; align-items:center;">
                <button type="submit" class="gn-btn gn-btn-primary">Update Produk</button>
                <a href="{{ route('admin.products.index') }}" class="gn-btn gn-btn-outline">Batal</a>
            </div>
        </form>
    </div>
</div>
@endsection
