
<x-guest-layout>
    <div class="auth-title">
        <span class="auth-eyebrow">WELCOME BACK</span>
        <h2>Selamat Datang Kembali</h2>
        <p>Masuk ke akunmu dan temukan pilihan hijau untuk ruang yang lebih nyaman.</p>
    </div>

    @if (session('success'))
        <div class="auth-success">
            {{ session('success') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="auth-alert">
            Email atau password tidak sesuai. Silakan periksa kembali.
        </div>
    @endif

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <div class="mb-3">
            <label for="email">Alamat Email</label>
            <input
                id="email"
                type="email"
                name="email"
                class="form-control"
                value="{{ old('email') }}"
                placeholder="nama@email.com"
                autocomplete="username"
                required
                autofocus
            >

            @error('email')
                <div class="auth-error">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <div class="auth-label-row">
                <label for="password">Password</label>

                @if (Route::has('password.request'))
                    <a href="{{ route('password.request') }}">Lupa password?</a>
                @endif
            </div>

            <div class="password-wrapper">
                <input
                    id="password"
                    type="password"
                    name="password"
                    class="form-control password-input"
                    placeholder="Masukkan password"
                    autocomplete="current-password"
                    required
                >

                <button
                    type="button"
                    class="password-toggle"
                    onclick="togglePassword('password', this)"
                    aria-label="Tampilkan password"
                    aria-pressed="false"
                >
                    <svg viewBox="0 0 24 24" fill="none"
                         stroke="currentColor" stroke-width="1.8"
                         stroke-linecap="round" stroke-linejoin="round">
                        <path d="M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7S2 12 2 12z"/>
                        <circle cx="12" cy="12" r="3"/>
                    </svg>
                </button>
            </div>

            @error('password')
                <div class="auth-error">{{ $message }}</div>
            @enderror
        </div>

        <div class="auth-remember">
            <label>
                <input type="checkbox" name="remember">
                <span>Ingat saya</span>
            </label>
        </div>

        <button type="submit" class="auth-button">
            Masuk ke Greenova
        </button>
    </form>

    <div class="auth-divider">
        <span>atau masuk dengan</span>
    </div>

    <a href="{{ route('auth.google') }}" class="google-auth-button">
        <svg viewBox="0 0 48 48" aria-hidden="true">
            <path fill="#EA4335" d="M24 9.5c3.54 0 6.71 1.22 9.21 3.6l6.85-6.85C35.9 2.38 30.47 0 24 0 14.62 0 6.51 5.38 2.56 13.22l7.98 6.19C12.43 13.72 17.74 9.5 24 9.5z" transform="translate(0 4)"/>
            <path fill="#4285F4" d="M46.98 24.55c0-1.57-.15-3.09-.38-4.55H24v9.02h12.94c-.58 2.96-2.26 5.48-4.76 7.18l7.73 6C44.42 37.97 46.98 31.84 46.98 24.55z" transform="translate(0 0) scale(.96)"/>
            <path fill="#FBBC05" d="M10.53 28.59A14.4 14.4 0 0 1 9.75 24c0-1.59.27-3.13.76-4.59l-7.98-6.19A23.9 23.9 0 0 0 0 24c0 3.87.93 7.52 2.56 10.78l7.97-6.19z" transform="translate(2 0)"/>
            <path fill="#34A853" d="M24 48c6.48 0 11.93-2.13 15.9-5.8l-7.73-6c-2.15 1.45-4.9 2.3-8.17 2.3-6.26 0-11.57-4.22-13.47-9.91l-7.98 6.19C6.51 42.62 14.62 48 24 48z" transform="translate(0 -2) scale(.96)"/>
        </svg>
        <span>Lanjutkan dengan Google</span>
    </a>

    <div class="auth-link">
        Belum memiliki akun?
        <a href="{{ route('register') }}">Daftar sekarang</a>
    </div>

    <style>
        .password-wrapper {
            position: relative;
            width: 100%;
        }

        .password-wrapper .password-input {
            width: 100%;
            padding-right: 48px;
        }

        .password-toggle {
            position: absolute;
            top: 50%;
            right: 12px;
            transform: translateY(-50%);
            display: flex;
            align-items: center;
            justify-content: center;
            width: 28px;
            height: 28px;
            padding: 3px;
            border: none;
            border-radius: 6px;
            background: transparent;
            color: #64748b;
            cursor: pointer;
        }

        .password-toggle:hover {
            color: #166534;
            background: #f0fdf4;
        }

        .password-toggle:focus-visible {
            outline: 2px solid #166534;
            outline-offset: 2px;
        }

        .password-toggle svg {
            width: 20px;
            height: 20px;
            pointer-events: none;
        }
    </style>

    <script>
        function togglePassword(inputId, button) {
            const input = document.getElementById(inputId);

            if (!input) return;

            const isHidden = input.type === 'password';

            input.type = isHidden ? 'text' : 'password';

            button.setAttribute('aria-pressed', String(isHidden));
            button.setAttribute(
                'aria-label',
                isHidden ? 'Sembunyikan password' : 'Tampilkan password'
            );

            button.innerHTML = isHidden
                ? `<svg viewBox="0 0 24 24" fill="none"
                       stroke="currentColor" stroke-width="1.8"
                       stroke-linecap="round" stroke-linejoin="round">
                       <path d="M3 3l18 18"/>
                       <path d="M10.6 10.6a2 2 0 0 0 2.8 2.8"/>
                       <path d="M9.9 5.2A10.8 10.8 0 0 1 12 5
                           c6.4 0 10 7 10 7s-1.4 2.7-4 4.7"/>
                       <path d="M6.2 6.2C3.5 8 2 12 2 12
                           s3.6 7 10 7a10.8 10.8 0 0 0 4-.8"/>
                   </svg>`
                : `<svg viewBox="0 0 24 24" fill="none"
                       stroke="currentColor" stroke-width="1.8"
                       stroke-linecap="round" stroke-linejoin="round">
                       <path d="M2 12s3.6-7 10-7 10 7 10 7
                           -3.6 7-10 7S2 12 2 12z"/>
                       <circle cx="12" cy="12" r="3"/>
                   </svg>`;
        }
    </script>
</x-guest-layout>
