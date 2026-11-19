@extends('layouts.admin')

@section('title', 'Tambah Kategori - Admin Greenova')
@section('page-title', 'Tambah Kategori')

@section('content')
<div style="max-width:680px;">
    <div style="margin-bottom:24px;">
        <div style="font-size:12px; font-weight:700; text-transform:uppercase; letter-spacing:0.08em; color:#166534; margin-bottom:6px;">
            Katalog Admin
        </div>
        <h1 style="margin:0; font-size:26px; font-weight:800; color:#1F2937; letter-spacing:-0.02em;">Tambah Kategori</h1>
        <p style="margin:4px 0 0; font-size:14px; color:#6B7280;">Tambahkan kategori baru untuk mengelompokkan produk Greenova.</p>
    </div>

    <div class="gn-card" style="padding:28px;">
        <form action="{{ route('admin.categories.store') }}" method="POST">
            @csrf

            <div style="margin-bottom:18px;">
                <label style="display:block; font-size:13px; font-weight:600; color:#374151; margin-bottom:6px;">Nama Kategori</label>
                <input type="text" name="cat_title" class="gn-input" value="{{ old('cat_title') }}" required>
                @error('cat_title')
                    <div style="color:#DC2626; font-size:12px; margin-top:4px;">{{ $message }}</div>
                @enderror
            </div>

            <div style="margin-bottom:24px;">
                <label style="display:block; font-size:13px; font-weight:600; color:#374151; margin-bottom:6px;">Deskripsi</label>
                <textarea name="cat_description" class="gn-textarea" rows="4">{{ old('cat_description') }}</textarea>
                @error('cat_description')
                    <div style="color:#DC2626; font-size:12px; margin-top:4px;">{{ $message }}</div>
                @enderror
            </div>

            <div style="display:flex; gap:10px; align-items:center;">
                <button type="submit" class="gn-btn gn-btn-primary">Simpan Kategori</button>
                <a href="{{ route('admin.categories.index') }}" class="gn-btn gn-btn-outline">Batal</a>
            </div>
        </form>
    </div>
</div>
@endsection
