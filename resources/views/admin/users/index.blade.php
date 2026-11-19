@extends('layouts.admin')

@section('title', 'Kelola Pengguna & Peran')
@section('page-title', 'Kelola Pengguna')

@section('content')
<div class="users-container">

    <!-- KPI Ringkasan Role -->
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 rounded-4 shadow-sm p-4 bg-white">
                <span class="small fw-bold text-muted text-uppercase" style="letter-spacing: 0.05em;">Total Pengguna</span>
                <div class="fw-bold fs-3 mt-1 text-dark">{{ $roleStats['total'] }}</div>
                <span class="small text-muted">Seluruh akun terdaftar</span>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 rounded-4 shadow-sm p-4 bg-white">
                <span class="small fw-bold text-muted text-uppercase" style="letter-spacing: 0.05em;">Administrator</span>
                <div class="fw-bold fs-3 mt-1" style="color: #6D28D9;">{{ $roleStats['admin'] }}</div>
                <span class="small text-muted">Akses penuh sistem</span>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 rounded-4 shadow-sm p-4 bg-white">
                <span class="small fw-bold text-muted text-uppercase" style="letter-spacing: 0.05em;">Staff Operasional</span>
                <div class="fw-bold fs-3 mt-1 text-primary">{{ $roleStats['staff'] }}</div>
                <span class="small text-muted">Pengelola inventori &amp; pesanan</span>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 rounded-4 shadow-sm p-4 bg-white">
                <span class="small fw-bold text-muted text-uppercase" style="letter-spacing: 0.05em;">Pelanggan (Customer)</span>
                <div class="fw-bold fs-3 mt-1 text-success">{{ $roleStats['customer'] }}</div>
                <span class="small text-muted">Akun pembeli Greenova</span>
            </div>
        </div>
    </div>

    <!-- Alert Notifikasi -->

    <!-- Toolbar Filter -->
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body p-3 p-lg-4">
            <form method="GET" action="{{ route('admin.users.index') }}" class="row g-2 align-items-center">
                <div class="col-md-6">
                    <input type="text" name="search" value="{{ $search }}" class="form-control form-control-sm rounded-3" placeholder="Cari nama, email, atau nomor telepon...">
                </div>
                <div class="col-6 col-md-3">
                    <select name="role" class="form-select form-select-sm rounded-3" onchange="this.form.submit()">
                        <option value="semua">Semua Role</option>
                        <option value="admin" {{ $role === 'admin' ? 'selected' : '' }}>Administrator</option>
                        <option value="staff" {{ $role === 'staff' ? 'selected' : '' }}>Staff</option>
                        <option value="user" {{ $role === 'user' ? 'selected' : '' }}>Customer (User)</option>
                    </select>
                </div>
                <div class="col-6 col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-success btn-sm w-100 rounded-3">Cari</button>
                    @if($search || $role !== 'semua')
                        <a href="{{ route('admin.users.index') }}" class="btn btn-light btn-sm rounded-3">Reset</a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <!-- Tabel Pengguna -->
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead class="table-light text-uppercase small text-muted" style="letter-spacing: 0.05em;">
                    <tr>
                        <th class="ps-4">Pengguna</th>
                        <th>Kontak &amp; Alamat</th>
                        <th>Peran (Role)</th>
                        <th>Tanggal Terdaftar</th>
                        <th class="text-end pe-4">Kelola Role</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $user)
                        @php
                            $userRole = strtolower(trim($user->role ?? 'user'));
                            $roleBadge = match($userRole) {
                                'admin' => 'bg-purple-subtle text-purple border-purple',
                                'staff' => 'bg-primary-subtle text-primary border-primary',
                                default => 'bg-success-subtle text-success border-success',
                            };
                            $roleName = match($userRole) {
                                'admin' => 'Administrator',
                                'staff' => 'Staff',
                                default => 'Customer',
                            };
                        @endphp
                        <tr>
                            <td class="ps-4">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="rounded-circle bg-light d-flex align-items-center justify-content-center fw-bold text-dark border flex-shrink-0" style="width: 40px; height: 40px;">
                                        {{ strtoupper(mb_substr($user->name, 0, 1)) }}
                                    </div>
                                    <div>
                                        <div class="fw-bold text-dark">{{ $user->name }}</div>
                                        <span class="text-muted small">{{ $user->email }}</span>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="small text-dark">{{ $user->phone ?: 'Belum diisi' }}</div>
                                <span class="small text-muted text-truncate d-block" style="max-width: 220px;">{{ $user->address ?: '—' }}</span>
                            </td>
                            <td>
                                <span class="badge px-3 py-1 rounded-pill fw-semibold" style="font-size: 11.5px;
                                    @if($userRole === 'admin') background: #F3E8FF; color: #6D28D9;
                                    @elseif($userRole === 'staff') background: #EFF6FF; color: #1D4ED8;
                                    @else background: #ECFDF5; color: #166534; @endif">
                                    {{ $roleName }}
                                </span>
                            </td>
                            <td class="text-nowrap text-muted small">
                                {{ $user->created_at ? $user->created_at->format('d M Y, H:i') : '—' }}
                            </td>
                            <td class="text-end pe-4">
                                @if(auth()->user()->role === 'admin')
                                    @if((int) $user->id === (int) auth()->id())
                                        <span class="badge bg-light text-muted border">Akun Anda</span>
                                    @else
                                        <form method="POST" action="{{ route('admin.users.update_role', $user->id) }}" class="d-inline-flex align-items-center gap-2">
                                            @csrf
                                            @method('PATCH')
                                            <select name="role" class="form-select form-select-sm rounded-3" style="width: auto;" onchange="if(confirm('Apakah Anda yakin ingin mengubah peran pengguna ini?')) { this.form.submit(); } else { this.value='{{ $userRole }}'; }">
                                                <option value="user" {{ $userRole === 'user' ? 'selected' : '' }}>Customer</option>
                                                <option value="staff" {{ $userRole === 'staff' ? 'selected' : '' }}>Staff</option>
                                                <option value="admin" {{ $userRole === 'admin' ? 'selected' : '' }}>Admin</option>
                                            </select>
                                        </form>
                                    @endif
                                @else
                                    <span class="small text-muted">Hanya Admin</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-5 text-muted">
                                Tidak ada akun yang sesuai dengan filter pencarian.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($users->hasPages())
            <div class="card-footer bg-white border-0 py-3 d-flex justify-content-center">
                {{ $users->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
