<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Greenova — Autentikasi</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Plus+Jakarta+Sans:wght@500;600;700;800&display=swap" rel="stylesheet">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <link rel="stylesheet" href="{{ asset('css/greenova-ds.css') }}">
</head>
<body class="auth-page">
    <main class="auth-layout">
        <section class="auth-brand">
            <div class="auth-brand-content">
                <div class="brand-leaf d-inline-flex align-items-center justify-content-center mb-3" style="width: 56px; height: 56px; border-radius: 16px; background: rgba(255,255,255,0.14); color: #fff;">
                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.1" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M11 20A7 7 0 0 1 9.8 6.1C15.5 5 17 4.48 19 2c1 2 2 4.18 2 8 0 5.5-4.78 10-10 10Z"/>
                        <path d="M2 21c0-3 1.85-5.36 5.08-6C9.5 14.52 12 13 13 12"/>
                    </svg>
                </div>

                <h1>Greenova</h1>
                <p class="brand-type">SUSTAINABLE LIFESTYLE STORE</p>

                <div class="brand-divider"></div>

                <p class="brand-tagline">
                    Hidup Lebih Hijau,<br>
                    Dimulai dari Pilihan Sehari-Hari
                </p>
            </div>
        </section>

        <section class="auth-form-side">
            <div class="auth-card">
                {{ $slot }}
            </div>
        </section>
    </main>
</body>
</html>
