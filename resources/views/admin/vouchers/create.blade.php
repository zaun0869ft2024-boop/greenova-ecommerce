@extends('layouts.admin')

@section('title', 'Tambah Voucher')
@section('page-title', 'Tambah Voucher')

@section('content')

<style>
    .form-card {
        max-width: 680px;
        background: #fff;
        border: 1px solid #E8ECE5;
        border-radius: 20px;
        padding: 36px;
        box-shadow: 0 5px 20px rgba(35,65,40,.04);
    }
    .form-card h2 { margin: 0 0 6px; color: #244B35; font-size: 20px; font-weight: 800; }
    .form-card .sub { color: #8A948B; font-size: 13px; margin-bottom: 28px; }
    .form-label { color: #334638; font-size: 13px; font-weight: 600; }
    .flash-success {
        padding: 14px 18px; margin-bottom: 20px;
        border: 1px solid #CFE5CE; border-radius: 12px;
        background: #EFF8ED; color: #245B35; font-size: 13px;
    }
    .flash-error {
        padding: 14px 18px; margin-bottom: 20px;
        border: 1px solid #FCA5A5; border-radius: 12px;
        background: #FEF2F2; color: #991B1B; font-size: 13px;
    }
</style>

<div class="form-card">
    <h2>Tambah Voucher</h2>
    <p class="sub">Buat kode voucher diskon baru untuk pelanggan.</p>


    @if($errors->any())
        <div class="flash-error">
            <ul class="mb-0 ps-3">
                @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
            </ul>
        </div>
    @endif

    <form action="{{ url('/admin/vouchers') }}" method="POST">
        @csrf

        <div class="mb-3">
            <label class="form-label">Kode Voucher <span class="text-danger">*</span></label>
            <input type="text" name="code" class="form-control"
                   value="{{ old('code') }}" placeholder="Misal: GREENOVA10"
                   style="font-family:monospace; letter-spacing:1px; text-transform:uppercase;" required>
            <div class="form-text">Kode unik yang digunakan pelanggan saat checkout.</div>
        </div>

        <div class="mb-3">
            <label class="form-label">Tipe Diskon <span class="text-danger">*</span></label>
            <select name="discount_type" class="form-select" required>
                <option value="">— Pilih Tipe —</option>
                <option value="percent" {{ old('discount_type') === 'percent' ? 'selected' : '' }}>Persentase (%)</option>
                <option value="nominal" {{ old('discount_type') === 'nominal' ? 'selected' : '' }}>Nominal Rupiah (Rp)</option>
            </select>
        </div>

        <div class="mb-3">
            <label class="form-label">Nilai Diskon <span class="text-danger">*</span></label>
            <input type="number" name="discount_value" class="form-control"
                   value="{{ old('discount_value') }}" min="0" step="0.01" required>
        </div>

        <div class="row g-3 mb-3">
            <div class="col-md-6">
                <label class="form-label">Minimal Pembelian (Rp)</label>
                <input type="number" name="min_purchase" class="form-control"
                       value="{{ old('min_purchase', 0) }}" min="0" step="1">
                <div class="form-text">Default 0 = semua transaksi bisa pakai</div>
            </div>
            <div class="col-md-6">
                <label class="form-label">Maksimal Potongan (Rp)</label>
                <input type="number" name="max_discount" class="form-control"
                       value="{{ old('max_discount') }}" min="0" step="1">
                <div class="form-text">Kosongkan = tidak ada batas maksimal</div>
            </div>
        </div>

        <div class="mb-3">
            <label class="form-label">Batas Penggunaan</label>
            <input type="number" name="usage_limit" class="form-control"
                   value="{{ old('usage_limit') }}" min="1" step="1">
            <div class="form-text">Kosongkan = penggunaan tidak terbatas</div>
        </div>

        <div class="row g-3 mb-3">
            <div class="col-md-6">
                <label class="form-label">Tanggal Mulai</label>
                <input type="datetime-local" name="starts_at" class="form-control"
                       value="{{ old('starts_at') }}">
            </div>
            <div class="col-md-6">
                <label class="form-label">Tanggal Berakhir</label>
                <input type="datetime-local" name="ends_at" class="form-control"
                       value="{{ old('ends_at') }}">
            </div>
        </div>

        <div class="mb-4">
            <div class="form-check">
                <input class="form-check-input" type="checkbox" name="is_active" id="is_active"
                       value="1" {{ old('is_active', '1') ? 'checked' : '' }}>
                <label class="form-check-label" for="is_active">
                    Aktifkan voucher ini
                </label>
            </div>
        </div>

        <div class="d-flex gap-2">
            <button type="submit" class="btn btn-success" style="background:#244B35; border-color:#244B35; padding: 10px 24px;">
                Simpan Voucher
            </button>
            <a href="{{ url('/admin/vouchers') }}" class="btn btn-outline-secondary" style="padding: 10px 24px;">
                Batal
            </a>
        </div>
    </form>
</div>

@endsection
