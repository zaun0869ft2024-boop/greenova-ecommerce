@extends('layouts.app')

@section('title', 'Profil Saya — Greenova')

@section('content')
<div class="py-4 py-lg-5">
    <div class="container">

        <div class="mb-4">
            <span class="gn-badge gn-badge-green mb-2">Pengaturan Akun</span>
            <h1 class="fw-bold mb-1" style="font-size: clamp(1.6rem, 2.8vw, 2.1rem); color: var(--gn-primary);">
                Profil Saya
            </h1>
            <p class="mb-0 text-muted small">Kelola informasi akun dan alamat pengiriman default Anda.</p>
        </div>

        <div class="row g-4 align-items-start">
            <div class="col-lg-4">
                <div class="bg-white rounded-4 border p-4 text-center" style="border-color: var(--gn-border-warm) !important;">
                    <div class="mx-auto mb-3 d-inline-flex align-items-center justify-content-center rounded-circle fw-bold text-white"
                         style="width: 72px; height: 72px; font-size: 26px; background: var(--gn-primary);">
                        {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                    </div>

                    <h2 class="h5 fw-bold mb-1 text-dark">{{ Auth::user()->name }}</h2>
                    <p class="small text-muted mb-3">{{ Auth::user()->email }}</p>

                    <span class="gn-badge gn-badge-green mb-4">
                        {{ Auth::user()->role === 'admin' ? 'Administrator Greenova' : (Auth::user()->role === 'staff' ? 'Staff Greenova' : 'Pelanggan Greenova') }}
                    </span>

                    <div class="d-grid gap-2 text-start pt-3 border-top" style="border-color: var(--gn-border-warm) !important;">
                        <a href="{{ route('dashboard') }}" class="gn-btn gn-btn-outline gn-btn-sm">Dashboard Akun</a>
                        <a href="{{ route('orders') }}" class="gn-btn gn-btn-outline gn-btn-sm">Riwayat Pesanan Saya</a>
                        <a href="{{ route('cart') }}" class="gn-btn gn-btn-outline gn-btn-sm">Keranjang Belanja</a>
                    </div>
                </div>
            </div>

            <div class="col-lg-8">
                <div class="bg-white rounded-4 border p-4 p-md-5" style="border-color: var(--gn-border-warm) !important;">
                    <h2 class="h5 fw-bold mb-1" style="color: var(--gn-primary);">Edit Informasi Akun</h2>
                    <p class="small text-muted mb-4">Perubahan data akan langsung digunakan pada proses checkout berikutnya.</p>

                    @if (session('status') === 'profile-updated')
                        <div class="alert alert-success border-0 rounded-3 shadow-sm mb-4" role="status">
                            Profil berhasil diperbarui.
                        </div>
                    @endif

                    <form method="POST" action="{{ route('profile.update') }}" class="row g-3">
                        @csrf
                        @method('PATCH')

                        <div class="col-md-6">
                            <label for="name" class="form-label small fw-semibold">Nama Lengkap</label>
                            <input
                                type="text"
                                id="name"
                                name="name"
                                class="gn-input"
                                value="{{ old('name', Auth::user()->name) }}"
                                placeholder="Masukkan nama lengkap"
                                required>
                            @error('name')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="email" class="form-label small fw-semibold">Alamat Email</label>
                            <input
                                type="email"
                                id="email"
                                name="email"
                                class="gn-input"
                                value="{{ old('email', Auth::user()->email) }}"
                                placeholder="nama@email.com"
                                required>
                            @error('email')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12">
                            <label for="phone" class="form-label small fw-semibold">Nomor Telepon</label>
                            <input
                                type="tel"
                                id="phone"
                                name="phone"
                                class="gn-input"
                                value="{{ old('phone', Auth::user()->phone) }}"
                                placeholder="Contoh: 081234567890"
                                maxlength="20">
                            @error('phone')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12">
                            <label for="address" class="form-label small fw-semibold">Alamat Pengiriman</label>
                            <textarea
                                id="address"
                                name="address"
                                rows="3"
                                class="gn-textarea"
                                placeholder="Masukkan alamat lengkap pengiriman">{{ old('address', Auth::user()->address) }}</textarea>
                            @error('address')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12 d-flex flex-wrap justify-content-end gap-2 pt-2">
                            <a href="{{ route('dashboard') }}" class="gn-btn gn-btn-outline">Kembali</a>
                            <button type="submit" class="gn-btn gn-btn-primary">Simpan Perubahan</button>
                        </div>
                    </form>
                </div>

                <div class="bg-white rounded-4 border p-4 p-md-5 mt-4" style="border-color: var(--gn-border-warm) !important;">
                    <h2 class="h5 fw-bold mb-1" style="color: var(--gn-primary);">Keamanan &amp; Password</h2>
                    <p class="small text-muted mb-4">Gunakan kombinasi password yang kuat untuk menjaga keamanan akun Anda.</p>

                    @if (session('status') === 'password-updated')
                        <div class="alert alert-success border-0 rounded-3 shadow-sm mb-4" role="status">
                            Password akun berhasil diperbarui.
                        </div>
                    @endif

                    <form method="POST" action="{{ route('password.update') }}" class="row g-3">
                        @csrf
                        @method('PUT')

                        <div class="col-12">
                            <label for="update_password_current_password" class="form-label small fw-semibold">Password Saat Ini</label>
                            <input
                                type="password"
                                id="update_password_current_password"
                                name="current_password"
                                class="gn-input"
                                autocomplete="current-password"
                                placeholder="Masukkan password saat ini">
                            @if($errors->updatePassword->has('current_password'))
                                <div class="text-danger small mt-1">{{ $errors->updatePassword->first('current_password') }}</div>
                            @endif
                        </div>

                        <div class="col-md-6">
                            <label for="update_password_password" class="form-label small fw-semibold">Password Baru</label>
                            <input
                                type="password"
                                id="update_password_password"
                                name="password"
                                class="gn-input"
                                autocomplete="new-password"
                                placeholder="Minimal 8 karakter">
                            @if($errors->updatePassword->has('password'))
                                <div class="text-danger small mt-1">{{ $errors->updatePassword->first('password') }}</div>
                            @endif
                        </div>

                        <div class="col-md-6">
                            <label for="update_password_password_confirmation" class="form-label small fw-semibold">Konfirmasi Password Baru</label>
                            <input
                                type="password"
                                id="update_password_password_confirmation"
                                name="password_confirmation"
                                class="gn-input"
                                autocomplete="new-password"
                                placeholder="Ulangi password baru">
                            @if($errors->updatePassword->has('password_confirmation'))
                                <div class="text-danger small mt-1">{{ $errors->updatePassword->first('password_confirmation') }}</div>
                            @endif
                        </div>

                        <div class="col-12 d-flex justify-content-end pt-2">
                            <button type="submit" class="gn-btn gn-btn-primary">Perbarui Password</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection
