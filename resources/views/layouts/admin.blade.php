
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Dashboard Admin') | Greenova</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Plus+Jakarta+Sans:wght@500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/greenova-ds.css') }}">
    <style>
        :root {
            --green: #244B35;
            --green-dark: #193A29;
            --sage: #EAF1E7;
            --cream: #F8F8F3;
            --text: #26352B;
            --muted: #8A948B;
            --border: #E8ECE5;
            --white: #FFFFFF;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: var(--gn-font-sans);
            background: var(--cream);
            color: var(--text);
        }

        a {
            text-decoration: none;
            color: inherit;
        }

        button {
            font: inherit;
        }

        .admin-layout {
            min-height: 100vh;
        }

        .admin-sidebar {
            position: fixed;
            inset: 0 auto 0 0;
            z-index: 100;
            width: 248px;
            padding: 20px 14px;
            display: flex;
            flex-direction: column;
            background: #1B3B2A;
            color: #FFFFFF;
            overflow-y: auto;
            border-right: 1px solid rgba(255,255,255,.08);
            transition: transform .25s ease;
        }

        .admin-brand {
            display: flex;
            align-items: center;
            gap: 11px;
            padding: 4px 10px 18px;
            border-bottom: 1px solid rgba(255,255,255,.1);
        }

        .brand-icon {
            display: grid;
            place-items: center;
            width: 38px;
            height: 38px;
            border-radius: 11px;
            background: #ECFDF5;
            color: #166534;
            flex-shrink: 0;
        }

        .brand-name {
            font-family: var(--gn-font-display, 'Plus Jakarta Sans', sans-serif);
            font-size: 19px;
            font-weight: 800;
            letter-spacing: -0.02em;
            line-height: 1.1;
        }

        .brand-caption {
            display: block;
            margin-top: 2px;
            color: rgba(255,255,255,.58);
            font-size: 10px;
            font-weight: 600;
            letter-spacing: 0.12em;
        }

        .sidebar-label {
            padding: 18px 10px 7px;
            color: rgba(255,255,255,.48);
            font-size: 10px;
            font-weight: 700;
            letter-spacing: 0.1em;
            text-transform: uppercase;
        }

        .sidebar-menu {
            display: flex;
            flex-direction: column;
            gap: 3px;
        }

        .sidebar-link {
            display: flex;
            align-items: center;
            gap: 11px;
            min-height: 38px;
            padding: 8px 12px;
            border-radius: 10px;
            color: rgba(255,255,255,.78);
            font-size: 13.5px;
            font-weight: 500;
            transition: background .15s ease, color .15s ease;
        }

        .sidebar-link svg {
            width: 17px;
            height: 17px;
            flex-shrink: 0;
            opacity: .85;
        }

        .sidebar-link:hover {
            background: rgba(255,255,255,.09);
            color: #FFFFFF;
        }

        .sidebar-link:hover svg {
            opacity: 1;
        }

        .sidebar-link.active {
            background: #ECFDF5;
            color: #166534;
            font-weight: 700;
        }

        .sidebar-link.active svg {
            opacity: 1;
            stroke: #166534;
        }

        .sidebar-bottom {
            margin-top: auto;
            padding-top: 14px;
            border-top: 1px solid rgba(255,255,255,.1);
        }

        .sidebar-logout {
            width: 100%;
            display: flex;
            align-items: center;
            gap: 11px;
            padding: 9px 12px;
            border: 0;
            border-radius: 10px;
            background: transparent;
            color: #FCA5A5;
            font-size: 13.5px;
            font-weight: 600;
            text-align: left;
            cursor: pointer;
            transition: background .15s ease, color .15s ease;
        }

        .sidebar-logout:hover {
            background: rgba(239,68,68,.14);
            color: #FECACA;
        }

        .admin-main {
            min-height: 100vh;
            margin-left: 248px;
        }

        .admin-topbar {
            position: sticky;
            top: 0;
            z-index: 50;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 20px;
            min-height: 82px;
            padding: 17px 36px;
            border-bottom: 1px solid var(--border);
            background: rgba(255,255,255,.94);
            backdrop-filter: blur(12px);
        }

        .topbar-left {
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .topbar-eyebrow {
            display: block;
            margin-bottom: 4px;
            color: var(--muted);
            font-size: 10px;
            letter-spacing: 1.6px;
            text-transform: uppercase;
        }

        .topbar-title {
            margin: 0;
            font-family: 'Manrope', sans-serif;
            font-size: 19px;
            font-weight: 800;
        }

        .admin-profile {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .profile-avatar {
            display: grid;
            place-items: center;
            width: 41px;
            height: 41px;
            border-radius: 50%;
            background: var(--sage);
            color: var(--green);
            font-size: 19px;
        }

        .profile-name {
            display: block;
            font-size: 12px;
            font-weight: 700;
        }

        .profile-role {
            display: block;
            margin-top: 4px;
            color: var(--muted);
            font-size: 11px;
        }

        .admin-content {
            padding: 34px 36px 50px;
        }

        .admin-alert {
            margin-bottom: 20px;
            padding: 14px 17px;
            border-radius: 12px;
            background: #EAF4E8;
            color: #285A36;
            font-size: 13px;
        }

        .admin-alert-error {
            background: #FEF2F2;
            color: #991B1B;
        }

        .admin-overlay {
            display: none;
        }

        .mobile-menu-button {
            display: none;
            width: 40px;
            height: 40px;
            border: 1px solid var(--border);
            border-radius: 11px;
            background: white;
            color: var(--green);
            font-size: 21px;
            cursor: pointer;
        }

        @media (max-width: 900px) {
            .admin-sidebar {
                transform: translateX(-100%);
            }

            .admin-sidebar.open {
                transform: translateX(0);
            }

            .admin-main {
                margin-left: 0;
            }

            .mobile-menu-button {
                display: grid;
                place-items: center;
            }

            .admin-overlay.show {
                position: fixed;
                inset: 0;
                z-index: 90;
                display: block;
                background: rgba(17, 34, 22, .45);
            }

            .admin-topbar {
                padding: 15px 22px;
            }

            .admin-content {
                padding: 26px 22px 40px;
            }
        }

        @media (max-width: 520px) {
            .admin-topbar {
                padding: 13px 15px;
            }

            .admin-content {
                padding: 22px 15px 35px;
            }

            .profile-name {
                max-width: 100px;
                overflow: hidden;
                text-overflow: ellipsis;
                white-space: nowrap;
            }

            .topbar-title {
                font-size: 16px;
            }
        }
    </style>

    @stack('styles')
</head>
<body>

<div class="admin-layout">

    <aside class="admin-sidebar" id="adminSidebar">

        <a href="{{ route('admin.dashboard') }}" class="admin-brand">
            <div class="brand-icon">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M11 20A7 7 0 0 1 9.8 6.1C15.5 5 17 4.48 19 2c1 2 2 4.18 2 8 0 5.5-4.78 10-10 10Z"/>
                    <path d="M2 21c0-3 1.85-5.36 5.08-6C9.5 14.52 12 13 13 12"/>
                </svg>
            </div>
            <div>
                <div class="brand-name">greenova</div>
                <span class="brand-caption">ADMINISTRATION</span>
            </div>
        </a>

        <div class="sidebar-label">MENU UTAMA</div>

        <nav class="sidebar-menu">
            <a href="{{ route('admin.dashboard') }}"
               class="sidebar-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="9" rx="1"/><rect x="14" y="3" width="7" height="5" rx="1"/><rect x="14" y="12" width="7" height="9" rx="1"/><rect x="3" y="16" width="7" height="5" rx="1"/></svg>
                <span>Dashboard</span>
            </a>

            <a href="{{ url('/admin/products') }}"
               class="sidebar-link {{ request()->is('admin/products*') ? 'active' : '' }}">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m7.5 4.27 9 5.15"/><path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z"/><path d="m3.3 7 8.7 5 8.7-5"/><path d="M12 22V12"/></svg>
                <span>Produk</span>
            </a>

            <a href="{{ url('/admin/categories') }}"
               class="sidebar-link {{ request()->is('admin/categories*') ? 'active' : '' }}">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 20h16a2 2 0 0 0 2-2V8a2 2 0 0 0-2-2h-7.93a2 2 0 0 1-1.66-.9l-.82-1.2A2 2 0 0 0 7.93 3H4a2 2 0 0 0-2 2v13c0 1.1.9 2 2 2Z"/></svg>
                <span>Kategori</span>
            </a>

            <a href="{{ url('/admin/orders') }}"
               class="sidebar-link {{ request()->is('admin/orders*') ? 'active' : '' }}">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4Z"/><path d="M3 6h18"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>
                <span>Pesanan</span>
            </a>

            <a href="{{ route('admin.inventory.index') }}"
               class="sidebar-link {{ request()->is('admin/inventory*') ? 'active' : '' }}">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/><polyline points="3.27 6.96 12 12.01 20.73 6.96"/><line x1="12" y1="22.08" x2="12" y2="12"/></svg>
                <span>Inventori &amp; Stok</span>
            </a>

            <a href="{{ route('admin.reports.transactions') }}"
               class="sidebar-link {{ request()->is('admin/reports*') ? 'active' : '' }}">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="20" x2="12" y2="10"/><line x1="18" y1="20" x2="18" y2="4"/><line x1="6" y1="20" x2="6" y2="16"/></svg>
                <span>Laporan Transaksi</span>
            </a>

            <a href="{{ url('/admin/users') }}"
               class="sidebar-link {{ request()->is('admin/users*') ? 'active' : '' }}">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                <span>Pengguna &amp; Peran</span>
            </a>

            <a href="{{ url('/admin/reviews') }}"
               class="sidebar-link {{ request()->is('admin/reviews*') ? 'active' : '' }}">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                <span>Moderasi Ulasan</span>
            </a>

            <a href="{{ url('/admin/discounts') }}"
               class="sidebar-link {{ request()->is('admin/discounts*') ? 'active' : '' }}">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12.586 2.586A2 2 0 0 0 11.172 2H4a2 2 0 0 0-2 2v7.172a2 2 0 0 0 .586 1.414l8.704 8.704a2.426 2.426 0 0 0 3.42 0l6.58-6.58a2.426 2.426 0 0 0 0-3.42z"/><circle cx="7.5" cy="7.5" r=".5" fill="currentColor"/></svg>
                <span>Diskon Produk</span>
            </a>

            <a href="{{ url('/admin/vouchers') }}"
               class="sidebar-link {{ request()->is('admin/vouchers*') ? 'active' : '' }}">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 9a3 3 0 0 1 0 6v2a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-2a3 3 0 0 1 0-6V7a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2Z"/><path d="M13 5v2"/><path d="M13 17v2"/><path d="M13 11v2"/></svg>
                <span>Voucher</span>
            </a>
        </nav>

        <div class="sidebar-label">Lainnya</div>

        <nav class="sidebar-menu">
            <a href="{{ route('home') }}" class="sidebar-link">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                <span>Kembali ke Toko</span>
            </a>
        </nav>

        <div class="sidebar-bottom">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="sidebar-logout">
                    <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
                    <span>Keluar dari Admin</span>
                </button>
            </form>
        </div>

    </aside>

    <div class="admin-overlay" id="adminOverlay"></div>

    <main class="admin-main">

        <header class="admin-topbar">
            <div class="topbar-left">
                <button type="button" class="mobile-menu-button"
                        id="mobileMenuButton" aria-label="Buka menu">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"><path d="M4 7h16M4 12h16M4 17h16"/></svg>
                </button>

                <div>
                    <span class="topbar-eyebrow">GREENOVA / ADMIN PANEL</span>
                    <h1 class="topbar-title">@yield('page-title', 'Dashboard')</h1>
                </div>
            </div>

            <div class="admin-profile">
                <div class="profile-avatar">{{ strtoupper(substr(auth()->user()->name ?? 'A', 0, 1)) }}</div>
                <div>
                    <span class="profile-name">
                        {{ auth()->user()->name ?? 'Administrator' }}
                    </span>
                    <span class="profile-role">{{ (auth()->user()->role ?? '') === 'staff' ? 'Staff' : 'Administrator' }}</span>
                </div>
            </div>
        </header>

        <section class="admin-content">

            @if (session('success'))
                <div class="admin-alert" role="status">
                    {{ session('success') }}
                </div>
            @endif

            @if (session('error'))
                <div class="admin-alert admin-alert-error" role="alert">
                    {{ session('error') }}
                </div>
            @endif
            
            @yield('content')

        </section>
    </main>

</div>

<script>
    const sidebar = document.getElementById('adminSidebar');
    const overlay = document.getElementById('adminOverlay');
    const menuButton = document.getElementById('mobileMenuButton');

    function closeAdminMenu() {
        sidebar.classList.remove('open');
        overlay.classList.remove('show');
    }

    menuButton.addEventListener('click', function () {
        sidebar.classList.toggle('open');
        overlay.classList.toggle('show');
    });

    overlay.addEventListener('click', closeAdminMenu);
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
@stack('scripts')

</body>
</html>
