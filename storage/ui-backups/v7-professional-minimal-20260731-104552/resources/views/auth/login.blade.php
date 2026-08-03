<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="color-scheme" content="light">
    <meta name="theme-color" content="#173B66">

    <title>Masuk | SmartVolt</title>

    <link rel="icon" href="{{ asset('favicon.ico') }}">

    <link rel="stylesheet" href="{{ asset('assets/css/smartvolt-auth.css') }}?v=20260730-v5">
</head>

<body class="sv-auth-page">

    <main class="sv-auth-shell">
        <section
            class="sv-auth-showcase"
            aria-label="Informasi SmartVolt"
        >
            <svg
                class="sv-auth-network"
                viewBox="0 0 900 900"
                aria-hidden="true"
                focusable="false"
            >
                <g fill="none" stroke="currentColor" stroke-width="1.5">
                    <path d="M70 130H280L360 210H590L690 110H840"/>
                    <path d="M40 390H190L290 290H470L560 380H820"/>
                    <path d="M110 680H300L400 580H620L720 680H860"/>
                    <path d="M240 80V260L160 340V520"/>
                    <path d="M650 80V250L740 340V520L650 610V820"/>
                    <path d="M470 210V470L390 550V790"/>
                </g>

                <g fill="currentColor">
                    <circle cx="70" cy="130" r="5"/>
                    <circle cx="360" cy="210" r="5"/>
                    <circle cx="690" cy="110" r="5"/>
                    <circle cx="190" cy="390" r="5"/>
                    <circle cx="470" cy="290" r="5"/>
                    <circle cx="560" cy="380" r="5"/>
                    <circle cx="300" cy="680" r="5"/>
                    <circle cx="620" cy="580" r="5"/>
                    <circle cx="740" cy="340" r="5"/>
                    <circle cx="390" cy="550" r="5"/>
                </g>
            </svg>

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
                        <small>Calm Energy Control</small>
                    </span>
                </a>

                <div class="sv-auth-copy">
                    <p class="sv-auth-eyebrow">
                        Monitoring dan kontrol energi berbasis IoT
                    </p>

                    <h1>
                        Kendalikan energi rumah dengan lebih tenang.
                    </h1>

                    <p class="sv-auth-description">
                        Pantau daya, energi, estimasi biaya, dan perangkat
                        listrik melalui satu sistem yang jelas dan mudah
                        dipahami.
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
                            <h2>Data listrik yang mudah dibaca</h2>
                            <p>
                                Lihat tegangan, arus, daya, energi, dan
                                perubahan penggunaan secara berkala.
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
                            <h2>Estimasi biaya yang transparan</h2>
                            <p>
                                Pemakaian kWh dihitung berdasarkan data meter
                                dan dikalikan dengan tarif listrik.
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
                            <h2>Kontrol perangkat per ruangan</h2>
                            <p>
                                Nyalakan atau matikan perangkat melalui relay
                                ketika ESP32 dan MQTT terhubung.
                            </p>
                        </div>
                    </article>
                </div>

                <div class="sv-auth-flow" aria-label="Alur data SmartVolt">
                    <div class="sv-auth-flow-label">Alur sistem</div>

                    <div class="sv-auth-flow-items">
                        <span>
                            <strong>PZEM</strong>
                            <small>Membaca listrik</small>
                        </span>

                        <i aria-hidden="true">→</i>

                        <span>
                            <strong>ESP32</strong>
                            <small>Mengirim data</small>
                        </span>

                        <i aria-hidden="true">→</i>

                        <span>
                            <strong>SmartVolt</strong>
                            <small>Menampilkan hasil</small>
                        </span>
                    </div>
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
                        <small>Monitoring energi rumah</small>
                    </span>
                </div>

                <div class="sv-auth-heading">
                    <span class="sv-auth-status-chip">
                        <span aria-hidden="true"></span>
                        Akses aman SmartVolt
                    </span>

                    <h2>Selamat datang kembali</h2>

                    <p>
                        Masuk untuk memantau dan mengontrol listrik rumah
                        Anda.
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
                            <strong>Periksa kembali data yang dimasukkan.</strong>
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
                                Gunakan email yang terdaftar pada akun
                                SmartVolt.
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
                        Akses akun dilindungi oleh sesi Laravel dan token
                        keamanan formulir.
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
