<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="color-scheme" content="light">
    <meta name="theme-color" content="#173B66">

    <title>Masuk | SmartVolt</title>

    <link rel="icon" href="{{ asset('favicon.ico') }}">

    <link rel="stylesheet" href="{{ asset('assets/css/smartvolt-auth.css') }}?v=20260801-google-v1">
</head>

<body class="sv-auth-page sv-login-page">

    <main class="sv-auth-shell">
        <section
            class="sv-auth-showcase"
            aria-label="Tentang SmartVolt"
        >
            <div class="sv-login-glow sv-login-glow-one" aria-hidden="true"></div>
            <div class="sv-login-glow sv-login-glow-two" aria-hidden="true"></div>

            <img
                class="sv-login-home-art"
                src="{{ asset('assets/images/smartvolt-home-night.svg') }}"
                alt=""
                aria-hidden="true"
            >

            <div class="sv-auth-showcase-content">
                <a
                    href="{{ url('/') }}"
                    class="sv-auth-brand"
                    aria-label="SmartVolt"
                >
                    <span class="sv-auth-brand-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24">
                            <path
                                d="M13.2 2.5 5.8 13h5.1l-.7 8.5L18.4 10h-5.1l-.1-7.5Z"
                                fill="currentColor"
                            />
                        </svg>
                    </span>

                    <span>
                        <strong>SmartVolt</strong>
                        <small>Solusi listrik rumah Anda</small>
                    </span>
                </a>

                <div class="sv-auth-copy">
                    <p class="sv-auth-eyebrow">
                        Pantau dan atur listrik rumah dengan mudah
                    </p>

                    <h1>
                        Kendalikan listrik rumah dengan lebih tenang
                    </h1>

                    <p class="sv-auth-description">
                        Pantau pemakaian listrik, lihat perkiraan biaya,
                        dan atur perangkat rumah dari satu tempat yang
                        mudah dipahami
                    </p>
                </div>

                <div class="sv-auth-benefits">
                    <article class="sv-auth-benefit">
                        <span class="sv-auth-benefit-icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24">
                                <path d="M4 19V9m5 10V5m5 14v-7m5 7V3"/>
                            </svg>
                        </span>

                        <div>
                            <h2>Data listrik mudah dipahami</h2>
                            <p>
                                Lihat pemakaian listrik dan perubahan
                                penggunaan secara lebih jelas
                            </p>
                        </div>
                    </article>

                    <article class="sv-auth-benefit">
                        <span class="sv-auth-benefit-icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24">
                                <rect x="5" y="3" width="14" height="18" rx="3"/>
                                <path d="M9 8h6M9 12h6M9 16h3"/>
                            </svg>
                        </span>

                        <div>
                            <h2>Perkiraan biaya lebih jelas</h2>
                            <p>
                                Pantau perkiraan biaya listrik agar
                                pengeluaran lebih mudah dikontrol
                            </p>
                        </div>
                    </article>

                    <article class="sv-auth-benefit">
                        <span class="sv-auth-benefit-icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24">
                                <path d="M8 12h8"/>
                                <rect x="3" y="7" width="6" height="10" rx="2"/>
                                <rect x="15" y="7" width="6" height="10" rx="2"/>
                            </svg>
                        </span>

                        <div>
                            <h2>Kontrol perangkat lebih praktis</h2>
                            <p>
                                Nyalakan atau matikan perangkat rumah
                                langsung dari satu halaman
                            </p>
                        </div>
                    </article>
                </div>

                <div class="sv-login-steps" aria-label="Kemudahan SmartVolt">
                    <span>
                        <i aria-hidden="true">
                            <svg viewBox="0 0 24 24">
                                <rect x="3" y="4" width="18" height="14" rx="3"/>
                                <path d="M8 21h8M12 18v3"/>
                            </svg>
                        </i>
                        <strong>Pantau listrik</strong>
                    </span>

                    <b aria-hidden="true">→</b>

                    <span>
                        <i aria-hidden="true">
                            <svg viewBox="0 0 24 24">
                                <path d="M7 3h10a2 2 0 0 1 2 2v16H5V5a2 2 0 0 1 2-2Z"/>
                                <path d="M8 8h8M8 12h8M8 16h5"/>
                            </svg>
                        </i>
                        <strong>Lihat biaya</strong>
                    </span>

                    <b aria-hidden="true">→</b>

                    <span>
                        <i aria-hidden="true">
                            <svg viewBox="0 0 24 24">
                                <path d="M9 3v6m6-6v6"/>
                                <path d="M7 8h10v3a5 5 0 0 1-10 0V8Z"/>
                                <path d="M12 16v5"/>
                            </svg>
                        </i>
                        <strong>Atur perangkat</strong>
                    </span>
                </div>
            </div>
        </section>

        <section class="sv-auth-panel">
            <div class="sv-auth-panel-inner">
                <div class="sv-auth-mobile-brand">
                    <span class="sv-auth-brand-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24">
                            <path
                                d="M13.2 2.5 5.8 13h5.1l-.7 8.5L18.4 10h-5.1l-.1-7.5Z"
                                fill="currentColor"
                            />
                        </svg>
                    </span>

                    <span>
                        <strong>SmartVolt</strong>
                        <small>Solusi listrik rumah Anda</small>
                    </span>
                </div>

                <div class="sv-auth-heading">
                    <span class="sv-auth-status-chip">
                        <span aria-hidden="true"></span>
                        Akses aman SmartVolt
                    </span>

                    <h2>Selamat datang kembali</h2>

                    <p>
                        Masuk untuk memantau dan mengatur listrik rumah Anda
                    </p>
                </div>

                @if (session('status'))
                    <div
                        class="sv-auth-alert sv-auth-alert-success"
                        role="status"
                        aria-live="polite"
                    >
                        <span aria-hidden="true">
                            <svg viewBox="0 0 24 24">
                                <path d="m5 12 4 4L19 6"/>
                            </svg>
                        </span>

                        <div>{{ session('status') }}</div>
                    </div>
                @endif

                @if ($errors->any())
                    <div
                        class="sv-auth-alert sv-auth-alert-error"
                        role="alert"
                        aria-live="assertive"
                    >
                        <span aria-hidden="true">
                            <svg viewBox="0 0 24 24">
                                <circle cx="12" cy="12" r="9"/>
                                <path d="M12 7v6m0 4h.01"/>
                            </svg>
                        </span>

                        <div>
                            <strong>Periksa kembali data yang dimasukkan</strong>
                            <p>{{ $errors->first() }}</p>
                        </div>
                    </div>
                @endif

                <form
                    id="loginForm"
                    class="sv-auth-form"
                    action="{{ route('login.process') }}"
                    method="POST"
                    novalidate
                >
                    @csrf

                    <div class="sv-auth-field">
                        <label for="email">Alamat email</label>

                        <div
                            class="sv-auth-input-group
                                @error('email') is-invalid @enderror"
                        >
                            <span class="sv-auth-input-icon" aria-hidden="true">
                                <svg viewBox="0 0 24 24">
                                    <rect x="3" y="5" width="18" height="14" rx="3"/>
                                    <path d="m5 8 7 5 7-5"/>
                                </svg>
                            </span>

                            <input
                                type="email"
                                id="email"
                                name="email"
                                value="{{ old('email') }}"
                                placeholder="nama@email.com"
                                autocomplete="email"
                                inputmode="email"
                                aria-describedby="emailHelp emailError"
                                @error('email') aria-invalid="true" @enderror
                                required
                                autofocus
                            >
                        </div>

                        @error('email')
                            <p
                                id="emailError"
                                class="sv-auth-field-error"
                            >
                                {{ $message }}
                            </p>
                        @else
                            <p
                                id="emailHelp"
                                class="sv-auth-field-help"
                            >
                                Gunakan email yang sudah terdaftar di SmartVolt
                            </p>
                        @enderror
                    </div>

                    <div class="sv-auth-field">
                        <label for="password">Kata sandi</label>

                        <div
                            class="sv-auth-input-group
                                @error('password') is-invalid @enderror"
                        >
                            <span class="sv-auth-input-icon" aria-hidden="true">
                                <svg viewBox="0 0 24 24">
                                    <rect x="5" y="10" width="14" height="10" rx="3"/>
                                    <path d="M8 10V7a4 4 0 0 1 8 0v3"/>
                                </svg>
                            </span>

                            <input
                                type="password"
                                id="password"
                                name="password"
                                placeholder="Masukkan kata sandi"
                                autocomplete="current-password"
                                aria-describedby="passwordError"
                                @error('password') aria-invalid="true" @enderror
                                required
                            >

                            <button
                                type="button"
                                id="togglePassword"
                                class="sv-auth-password-toggle"
                                aria-label="Tampilkan kata sandi"
                                aria-pressed="false"
                            >
                                <svg
                                    class="sv-auth-eye-show"
                                    viewBox="0 0 24 24"
                                    aria-hidden="true"
                                >
                                    <path d="M2.5 12s3.5-6 9.5-6 9.5 6 9.5 6-3.5 6-9.5 6S2.5 12 2.5 12Z"/>
                                    <circle cx="12" cy="12" r="2.5"/>
                                </svg>

                                <svg
                                    class="sv-auth-eye-hide"
                                    viewBox="0 0 24 24"
                                    aria-hidden="true"
                                >
                                    <path d="m4 4 16 16"/>
                                    <path d="M10.6 6.2A10 10 0 0 1 12 6c6 0 9.5 6 9.5 6a16 16 0 0 1-2.4 3.1M6.2 7.3A15.7 15.7 0 0 0 2.5 12S6 18 12 18a9.8 9.8 0 0 0 2.1-.2"/>
                                </svg>
                            </button>
                        </div>

                        @error('password')
                            <p
                                id="passwordError"
                                class="sv-auth-field-error"
                            >
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    <div class="sv-auth-options">
                        <label class="sv-auth-checkbox">
                            <input
                                type="checkbox"
                                name="remember"
                                value="1"
                                {{ old('remember') ? 'checked' : '' }}
                            >
                            <span aria-hidden="true"></span>
                            <em>Ingat saya</em>
                        </label>

                        <a
                            href="{{ route('password.request') }}"
                            class="sv-auth-link"
                        >
                            Lupa kata sandi?
                        </a>
                    </div>

                    <button
                        type="submit"
                        id="loginButton"
                        class="sv-auth-submit"
                    >
                        <span
                            class="sv-auth-button-spinner"
                            aria-hidden="true"
                        ></span>

                        <span id="loginButtonText">Masuk</span>
                    </button>
                </form>

                <div class="sv-auth-divider" aria-hidden="true">
                    <span>atau lanjutkan dengan</span>
                </div>

                <a
                    href="{{ route('google.redirect') }}"
                    class="sv-auth-google-button"
                    aria-label="Masuk dengan Google"
                >
                    <svg viewBox="0 0 24 24" aria-hidden="true">
                        <path fill="#4285F4" d="M21.6 12.23c0-.71-.06-1.4-.18-2.07H12v3.92h5.38a4.6 4.6 0 0 1-2 3.02v2.55h3.24c1.9-1.75 2.98-4.33 2.98-7.42Z"/>
                        <path fill="#34A853" d="M12 22c2.7 0 4.97-.9 6.63-2.35l-3.24-2.55c-.9.6-2.05.96-3.39.96-2.61 0-4.82-1.76-5.61-4.13H3.04v2.63A10 10 0 0 0 12 22Z"/>
                        <path fill="#FBBC05" d="M6.39 13.93A6.01 6.01 0 0 1 6.08 12c0-.67.11-1.32.31-1.93V7.44H3.04A10 10 0 0 0 2 12c0 1.61.38 3.14 1.04 4.56l3.35-2.63Z"/>
                        <path fill="#EA4335" d="M12 5.94c1.47 0 2.79.5 3.83 1.5l2.87-2.87A9.65 9.65 0 0 0 12 2a10 10 0 0 0-8.96 5.44l3.35 2.63C7.18 7.7 9.39 5.94 12 5.94Z"/>
                    </svg>

                    <span>Masuk dengan Google</span>
                </a>

                <p class="sv-auth-footer">
                    Belum memiliki akun?
                    <a
                        href="{{ route('register') }}"
                        class="sv-auth-link"
                    >
                        Daftar akun
                    </a>
                </p>

                <div class="sv-auth-security-note">
                    <svg viewBox="0 0 24 24" aria-hidden="true">
                        <path d="M12 3 5 6v5c0 4.6 2.7 8 7 10 4.3-2 7-5.4 7-10V6l-7-3Z"/>
                        <path d="m9 12 2 2 4-5"/>
                    </svg>

                    <p>
                        Akun Anda dilindungi untuk menjaga keamanan data Anda
                    </p>
                </div>
            </div>
        </section>
    </main>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const passwordInput = document.getElementById('password');
            const togglePassword = document.getElementById('togglePassword');

            if (passwordInput && togglePassword) {
                togglePassword.addEventListener('click', function () {
                    const shouldShow = passwordInput.type === 'password';

                    passwordInput.type = shouldShow ? 'text' : 'password';
                    togglePassword.classList.toggle('is-visible', shouldShow);
                    togglePassword.setAttribute(
                        'aria-label',
                        shouldShow
                            ? 'Sembunyikan kata sandi'
                            : 'Tampilkan kata sandi'
                    );
                    togglePassword.setAttribute(
                        'aria-pressed',
                        shouldShow ? 'true' : 'false'
                    );

                    passwordInput.focus();
                });
            }

            const form = document.getElementById('loginForm');
            const button = document.getElementById('loginButton');
            const buttonText = document.getElementById('loginButtonText');

            if (form && button && buttonText) {
                form.addEventListener('submit', function (event) {
                    if (! form.checkValidity()) {
                        event.preventDefault();
                        form.reportValidity();
                        return;
                    }

                    button.disabled = true;
                    button.classList.add('is-loading');
                    buttonText.textContent = 'Memeriksa akun...';
                    button.setAttribute('aria-busy', 'true');
                });
            }
        });
    </script>
</body>
</html>
