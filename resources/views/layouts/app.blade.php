<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Greenova — Sustainable Lifestyle E-Commerce')</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Plus+Jakarta+Sans:wght@500;600;700;800&display=swap" rel="stylesheet">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <link rel="stylesheet" href="{{ asset('css/greenova-ds.css') }}">
</head>
<body>

<header class="gn-header">
    <div class="container">
        <div class="gn-header-inner">
            <a href="{{ route('home') }}" class="gn-brand">
                <span class="gn-brand-mark" aria-hidden="true">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M11 20A7 7 0 0 1 9.8 6.1C15.5 5 17 4.48 19 2c1 2 2 4.18 2 8 0 5.5-4.78 10-10 10Z"/>
                        <path d="M2 21c0-3 1.85-5.36 5.08-6C9.5 14.52 12 13 13 12"/>
                    </svg>
                </span>
                <span>Greenova</span>
            </a>

            <div class="d-flex align-items-center gap-2 ms-auto d-lg-none">
                <a href="{{ route('products') }}#catalog-search-input"
                   class="gn-search-icon-btn"
                   id="gnMobileSearchBtn"
                   aria-label="Cari produk"
                   title="Cari produk">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.1" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/>
                    </svg>
                </a>

                <button type="button" class="gn-mobile-toggle" id="gnMobileToggle" aria-label="Buka menu navigasi" aria-expanded="false">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round">
                        <path d="M4 7h16M4 12h16M4 17h16"/>
                    </svg>
                </button>
            </div>

            <div class="gn-nav-wrap" id="gnNavWrap">
                <nav class="gn-nav" aria-label="Navigasi utama">
                    <a href="{{ route('home') }}"
                       class="gn-nav-link {{ request()->routeIs('home') ? 'active' : '' }}"
                       @if(request()->routeIs('home')) aria-current="page" @endif>
                        Beranda
                    </a>

                    <a href="{{ route('products') }}"
                       class="gn-nav-link {{ request()->routeIs('products', 'product.detail') ? 'active' : '' }}"
                       @if(request()->routeIs('products', 'product.detail')) aria-current="page" @endif>
                        Katalog Produk
                    </a>

                    <button type="button"
                            class="gn-search-icon-btn d-none d-lg-inline-flex"
                            id="gnSearchToggle"
                            aria-label="Cari produk"
                            aria-expanded="false"
                            title="Cari produk">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.1" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/>
                        </svg>
                        <span>Cari</span>
                    </button>

                    @auth
                        <a href="{{ route('cart') }}"
                           class="gn-nav-link {{ request()->routeIs('cart') ? 'active' : '' }}"
                           @if(request()->routeIs('cart')) aria-current="page" @endif>
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <circle cx="8" cy="21" r="1"/><circle cx="19" cy="21" r="1"/>
                                <path d="M2.05 2.05h2l2.66 12.42a2 2 0 0 0 2 1.58h9.78a2 2 0 0 0 1.95-1.57l1.65-7.43H5.12"/>
                            </svg>
                            <span>Keranjang</span>
                            @if(isset($cartCount) && $cartCount > 0)
                                <span class="gn-cart-badge">{{ $cartCount }}</span>
                            @endif
                        </a>

                        <a href="{{ route('orders') }}"
                           class="gn-nav-link {{ request()->routeIs('orders', 'orders.show', 'checkout', 'order.success') ? 'active' : '' }}"
                           @if(request()->routeIs('orders', 'orders.show', 'checkout', 'order.success')) aria-current="page" @endif>
                            Pesanan
                        </a>

                        <div class="dropdown">
                            <button class="gn-account-btn dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <span class="gn-avatar-dot">{{ strtoupper(mb_substr(Auth::user()->name, 0, 1)) }}</span>
                                <span>{{ Str::limit(Auth::user()->name, 14) }}</span>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0 mt-2" style="min-width: 200px; border-radius: 12px;">
                                <li class="px-3 py-2 border-bottom">
                                    <div class="fw-bold small text-dark">{{ Auth::user()->name }}</div>
                                    <div class="text-muted" style="font-size: 11px;">{{ Auth::user()->email }}</div>
                                </li>
                                <li><a class="dropdown-item py-2 small fw-semibold" href="{{ route('dashboard') }}">Dashboard Akun</a></li>
                                <li><a class="dropdown-item py-2 small" href="{{ route('profile.edit') }}">Profil Saya</a></li>
                                <li><a class="dropdown-item py-2 small" href="{{ route('orders') }}">Riwayat Pesanan</a></li>
                                @if(in_array(Auth::user()->role, ['admin', 'staff'], true))
                                    <li><a class="dropdown-item py-2 small fw-semibold text-success" href="{{ route('admin.dashboard') }}">Panel Admin &amp; Staff</a></li>
                                @endif
                                <li><hr class="dropdown-divider my-1"></li>
                                <li>
                                    <form action="{{ route('logout') }}" method="POST" onsubmit="return confirm('Apakah kamu yakin ingin keluar dari Greenova?');">
                                        @csrf
                                        <button type="submit" class="dropdown-item py-2 small text-danger">Keluar</button>
                                    </form>
                                </li>
                            </ul>
                        </div>
                    @else
                        <a href="{{ route('login') }}" class="gn-btn gn-btn-outline gn-btn-sm">Masuk</a>
                        <a href="{{ route('register') }}" class="gn-btn gn-btn-primary gn-btn-sm">Daftar</a>
                    @endauth
                </nav>
            </div>
        </div>
    </div>

    @unless(request()->routeIs('products'))
        <div class="gn-search-drawer" id="gnSearchDrawer">
            <div class="container">
                <form action="{{ route('products') }}" method="GET" class="d-flex align-items-center gap-2" role="search">
                    <input
                        type="search"
                        id="gnHeaderSearchInput"
                        name="search"
                        value="{{ request('search') }}"
                        class="gn-input"
                        placeholder="Cari nama atau kategori produk ramah lingkungan..."
                        aria-label="Cari produk">
                    <button type="submit" class="gn-btn gn-btn-primary flex-shrink-0">
                        Cari Produk
                    </button>
                </form>
            </div>
        </div>
    @endunless
</header>

<main>
    @yield('content')
</main>

<footer class="gn-footer">
    <div class="container">
        <div class="gn-footer-grid">
            <div>
                <a href="{{ route('home') }}" class="gn-brand text-white mb-3">
                    <span class="gn-brand-mark" style="background: rgba(255,255,255,0.16);" aria-hidden="true">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M11 20A7 7 0 0 1 9.8 6.1C15.5 5 17 4.48 19 2c1 2 2 4.18 2 8 0 5.5-4.78 10-10 10Z"/>
                            <path d="M2 21c0-3 1.85-5.36 5.08-6C9.5 14.52 12 13 13 12"/>
                        </svg>
                    </span>
                    <span>Greenova</span>
                </a>
                <p class="small mb-0" style="max-width: 340px; line-height: 1.7; color: rgba(255,255,255,0.78);">
                    Toko produk rumah tangga, kebutuhan harian, dan gaya hidup ramah lingkungan dengan informasi stok dan harga yang transparan.
                </p>
            </div>

            <div>
                <h6>Belanja</h6>
                <a href="{{ route('home') }}" class="gn-footer-link">Beranda</a>
                <a href="{{ route('products') }}" class="gn-footer-link">Semua Produk</a>
                <a href="{{ route('products', ['sort' => 'best_seller']) }}" class="gn-footer-link">Produk Terlaris</a>
                <a href="{{ route('products', ['sort' => 'latest']) }}" class="gn-footer-link">Koleksi Terbaru</a>
            </div>

            <div>
                <h6>Akun &amp; Pesanan</h6>
                @auth
                    <a href="{{ route('dashboard') }}" class="gn-footer-link">Dashboard Akun</a>
                    <a href="{{ route('cart') }}" class="gn-footer-link">Keranjang Belanja</a>
                    <a href="{{ route('orders') }}" class="gn-footer-link">Riwayat Pesanan</a>
                    <a href="{{ route('profile.edit') }}" class="gn-footer-link">Pengaturan Profil</a>
                    @if(in_array(Auth::user()->role, ['admin', 'staff'], true))
                        <a href="{{ route('admin.dashboard') }}" class="gn-footer-link">Panel Admin</a>
                    @endif
                @else
                    <a href="{{ route('login') }}" class="gn-footer-link">Masuk ke Akun</a>
                    <a href="{{ route('register') }}" class="gn-footer-link">Daftar Akun Baru</a>
                @endauth
            </div>

            <div>
                <h6>Layanan &amp; Bantuan</h6>
                <a href="#greenovaChatbot"
                   onclick="const t=document.getElementById('chatbotToggle'); if(t){t.click(); return false;}"
                   class="gn-footer-link">
                    Bantuan Greenova Assistant
                </a>
                <span class="gn-footer-link" style="cursor: default;">Pembayaran Online Midtrans</span>
                <span class="gn-footer-link" style="cursor: default;">Ulasan Pembeli Terverifikasi</span>
            </div>
        </div>

        <div class="gn-footer-bottom">
            <span>© {{ date('Y') }} Greenova. Seluruh hak cipta dilindungi.</span>
            <span>Belanja praktis untuk kebutuhan harian yang lebih berkelanjutan.</span>
        </div>
    </div>
</footer>

@include('partials.chatbot')

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const toggle = document.getElementById('gnMobileToggle');
        const navWrap = document.getElementById('gnNavWrap');
        if (toggle && navWrap) {
            toggle.addEventListener('click', function () {
                const isOpen = navWrap.classList.toggle('open');
                toggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
            });
        }

        const searchToggle = document.getElementById('gnSearchToggle');
        const searchDrawer = document.getElementById('gnSearchDrawer');
        const catalogSearch = document.getElementById('catalog-search-input');

        if (searchToggle) {
            searchToggle.addEventListener('click', function () {
                if (catalogSearch) {
                    catalogSearch.focus();
                    catalogSearch.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    return;
                }
                if (searchDrawer) {
                    const isOpen = searchDrawer.classList.toggle('open');
                    searchToggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
                    if (isOpen) {
                        const input = document.getElementById('gnHeaderSearchInput');
                        if (input) input.focus();
                    }
                }
            });
        }
    });
</script>
</body>
</html>
