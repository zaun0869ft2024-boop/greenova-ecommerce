
<x-guest-layout>
    <div class="auth-title">
        <span class="auth-eyebrow">JOIN GREENOVA</span>
        <h2>Buat Akun Baru</h2>
    </div>

    @if ($errors->any())
        <div class="auth-alert">
            Periksa kembali data yang kamu masukkan.

            @foreach ($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </div>
    @endif

    <form method="POST" action="{{ route('register') }}">
        @csrf

        <div class="mb-3">
            <label for="name">Nama Lengkap</label>
            <input
                id="name"
                type="text"
                name="name"
                class="form-control"
                value="{{ old('name') }}"
                placeholder="Masukkan nama lengkap"
                autocomplete="name"
                required
                autofocus
            >

            @error('name')
                <div class="auth-error">{{ $message }}</div>
            @enderror
        </div>

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
            >

            @error('email')
                <div class="auth-error">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label for="password">Password</label>

            <div class="password-wrapper">
                <input
                    id="password"
                    type="password"
                    name="password"
                    class="form-control password-input"
                    placeholder="Buat password"
                    autocomplete="new-password"
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

        <div class="mb-3">
            <label for="password_confirmation">Konfirmasi Password</label>

            <div class="password-wrapper">
                <input
                    id="password_confirmation"
                    type="password"
                    name="password_confirmation"
                    class="form-control password-input"
                    placeholder="Ulangi password"
                    autocomplete="new-password"
                    required
                >

                <button
                    type="button"
                    class="password-toggle"
                    onclick="togglePassword('password_confirmation', this)"
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
        </div>

        <button type="submit" class="auth-button">
            Buat Akun Greenova
        </button>
    </form>

    <div class="auth-divider">
        <span>atau daftar dengan</span>
    </div>

    <a href="{{ route('auth.google') }}" class="google-auth-button">
        <svg viewBox="0 0 48 48" aria-hidden="true">
            <path fill="#4285F4" d="M43.6 24.5c0-1.4-.1-2.8-.4-4.1H24v7.8h11a9.4 9.4 0 0 1-4.1 6.2v5.1h6.6c3.9-3.6 6.1-8.8 6.1-15z"/>
            <path fill="#34A853" d="M24 44c5.5 0 10.1-1.8 13.5-4.9l-6.6-5.1c-1.8 1.2-4.1 2-6.9 2-5.3 0-9.8-3.6-11.4-8.4H5.8v5.3A20 20 0 0 0 24 44z"/>
            <path fill="#FBBC05" d="M12.6 27.6a12 12 0 0 1 0-7.2v-5.3H5.8a20 20 0 0 0 0 17.8z"/>
            <path fill="#EA4335" d="M24 12.1c3 0 5.7 1 7.8 3.1l5.8-5.8C34.1 6.1 29.5 4 24 4A20 20 0 0 0 5.8 15.1l6.8 5.3c1.6-4.8 6.1-8.3 11.4-8.3z"/>
        </svg>
        <span>Lanjutkan dengan Google</span>
    </a>

    <div class="auth-link">
        Sudah memiliki akun?
        <a href="{{ route('login') }}">Masuk sekarang</a>
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
