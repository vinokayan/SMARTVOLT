<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk | SmartVolt</title>

    <link rel="stylesheet" href="{{ asset('assets/css/smartvolt-brand.css') }}?v={{ filemtime(public_path('assets/css/smartvolt-brand.css')) }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

    <style>
        .sv-auth-title,
        .sv-showcase-title,
        .sv-auth-subtitle,
        .sv-showcase-desc,
        .sv-feature-tile p {
            overflow-wrap: anywhere;
        }

        .sv-helper {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: .5rem;
        }

        .sv-helper-separator {
            opacity: .55;
        }

        .sv-field-hint {
            display: block;
            margin-top: .45rem;
            font-size: .82rem;
            line-height: 1.4;
            opacity: .75;
        }

        @media (max-width: 575.98px) {
            .sv-helper {
                align-items: flex-start;
                flex-direction: column;
            }

            .sv-helper-separator {
                display: none;
            }

            .sv-auth-foot {
                text-align: center;
            }
        }
    </style>
</head>
<body class="brand-body">
    @php
        $mode = request('mode');
    @endphp

    <div class="sv-auth-layout">
        <section class="sv-auth-showcase">
            <div class="sv-showcase-inner">
                <div class="sv-brandmark">
                    <div class="icon">
                        <i class="bi bi-lightning-charge-fill"></i>
                    </div>
                    <span>SmartVolt</span>
                </div>

                <h1 class="sv-showcase-title">
                    Kelola listrik rumah tanpa ribet
                </h1>

                <p class="sv-showcase-desc">
                    Pantau pemakaian energi, cek status perangkat, dan kendalikan ruangan dari satu dashboard SmartVolt yang praktis.
                </p>

                <div class="sv-feature-stack">
                    <div class="sv-feature-tile">
                        <div class="tile-icon">
                            <i class="bi bi-activity"></i>
                        </div>
                        <div>
                            <h4>Pantauan Langsung</h4>
                            <p>Lihat penggunaan daya dan status perangkat secara real-time agar konsumsi listrik lebih terkendali.</p>
                        </div>
                    </div>

                    <div class="sv-feature-tile">
                        <div class="tile-icon">
                            <i class="bi bi-grid-3x3-gap-fill"></i>
                        </div>
                        <div>
                            <h4>Kontrol Tiap Ruangan</h4>
                            <p>Nyalakan, matikan, dan kelola perangkat rumah dengan lebih mudah dari mana saja.</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="sv-energy-orbit">
                <div class="sv-orbit-ring r1"></div>
                <div class="sv-orbit-ring r2"></div>
                <div class="sv-orbit-ring r3"></div>
                <div class="sv-orbit-core">
                    <i class="bi bi-lightning-charge-fill"></i>
                </div>
                <div class="sv-orbit-dot d1"></div>
                <div class="sv-orbit-dot d2"></div>
            </div>
        </section>

        <section class="sv-auth-panel">
            <div class="sv-auth-card sv-glass">
                <div class="sv-auth-body">
                    <div class="sv-badge">
                        <i class="bi bi-shield-check"></i> Akses Aman SmartVolt
                    </div>

                    @if ($mode !== 'forgot')
                        <h2 class="sv-auth-title">Masuk ke SmartVolt</h2>

                        <p class="sv-auth-subtitle">
                            Lanjutkan memantau energi, mengontrol perangkat, dan membuat penggunaan listrik rumah jadi lebih efisien.
                        </p>
                    @else
                        <h2 class="sv-auth-title">Atur Ulang Kata Sandi</h2>

                        <p class="sv-auth-subtitle">
                            Masukkan email akun Anda. Kami akan mengirimkan tautan untuk membuat kata sandi baru.
                        </p>
                    @endif

                    @if (session('status'))
                        <div class="sv-alert">
                            {{ session('status') }}
                        </div>
                    @endif

                    @if ($errors->any())
                        <div class="sv-alert error">
                            @foreach ($errors->all() as $error)
                                <div>{{ $error }}</div>
                            @endforeach
                        </div>
                    @endif

                    @if ($mode !== 'forgot')
                        <form id="loginForm" action="{{ url('/login') }}" method="POST">
                            @csrf

                            <div class="sv-field-grid">
                                <div class="sv-field">
                                    <label class="sv-label" for="email">Alamat Email</label>
                                    <div class="sv-input-wrap">
                                        <i class="bi bi-envelope-fill sv-input-icon"></i>
                                        <input
                                            type="email"
                                            id="email"
                                            name="email"
                                            class="sv-input"
                                            value="{{ old('email') }}"
                                            placeholder="contoh@email.com"
                                            autocomplete="email"
                                            inputmode="email"
                                            required
                                            autofocus
                                        >
                                    </div>
                                    <small class="sv-field-hint">Gunakan email yang terdaftar di akun SmartVolt Anda.</small>
                                </div>

                                <div class="sv-field">
                                    <label class="sv-label" for="password">Kata Sandi</label>
                                    <div class="sv-input-wrap">
                                        <i class="bi bi-lock-fill sv-input-icon"></i>
                                        <input
                                            type="password"
                                            id="password"
                                            name="password"
                                            class="sv-input"
                                            placeholder="Masukkan kata sandi"
                                            autocomplete="current-password"
                                            required
                                        >
                                        <button type="button" class="sv-password-toggle" id="togglePassword" aria-label="Tampilkan kata sandi">
                                            <i class="bi bi-eye-fill" id="togglePasswordIcon"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <div class="sv-helper">
                                <label>
                                    <input type="checkbox" name="remember" value="1">
                                    <span>Ingat saya</span>
                                </label>

                                <span class="sv-helper-separator">|</span>

                                <a href="{{ url('/login?mode=forgot') }}" class="sv-link">
                                    Lupa kata sandi?
                                </a>
                            </div>

                            <button type="submit" class="sv-btn sv-auth-submit success" id="loginButton">
                                <span id="loginButtonText">Masuk</span>
                            </button>
                        </form>

                        <div class="sv-auth-foot">
                            Belum punya akun?
                            <a href="{{ url('/register') }}" class="sv-link">Daftar sekarang</a>
                        </div>
                    @else
                        <form id="forgotForm" action="{{ url('/forgot-password') }}" method="POST">
                            @csrf

                            <div class="sv-field-grid">
                                <div class="sv-field">
                                    <label class="sv-label" for="forgot_email">Alamat Email</label>
                                    <div class="sv-input-wrap">
                                        <i class="bi bi-envelope-fill sv-input-icon"></i>
                                        <input
                                            type="email"
                                            id="forgot_email"
                                            name="email"
                                            class="sv-input"
                                            value="{{ old('email') }}"
                                            placeholder="contoh@email.com"
                                            autocomplete="email"
                                            inputmode="email"
                                            required
                                            autofocus
                                        >
                                    </div>
                                    <small class="sv-field-hint">Pastikan email sesuai dengan akun SmartVolt yang terdaftar.</small>
                                </div>
                            </div>

                            <button type="submit" class="sv-btn sv-auth-submit success" id="forgotButton">
                                <span id="forgotButtonText">Kirim Tautan Reset</span>
                            </button>

                            <div class="sv-auth-foot">
                                <a href="{{ url('/login') }}" class="sv-link">Kembali ke halaman masuk</a>
                            </div>
                        </form>
                    @endif
                </div>
            </div>
        </section>
    </div>

    <script>
        const passwordInput = document.getElementById('password');
        const togglePassword = document.getElementById('togglePassword');
        const togglePasswordIcon = document.getElementById('togglePasswordIcon');

        if (togglePassword && passwordInput && togglePasswordIcon) {
            togglePassword.addEventListener('click', function () {
                const isPassword = passwordInput.type === 'password';
                passwordInput.type = isPassword ? 'text' : 'password';
                togglePasswordIcon.className = isPassword ? 'bi bi-eye-slash-fill' : 'bi bi-eye-fill';
                togglePassword.setAttribute('aria-label', isPassword ? 'Sembunyikan kata sandi' : 'Tampilkan kata sandi');
            });
        }

        const loginForm = document.getElementById('loginForm');
        const loginButton = document.getElementById('loginButton');
        const loginButtonText = document.getElementById('loginButtonText');

        if (loginForm && loginButton && loginButtonText) {
            loginForm.addEventListener('submit', function () {
                loginButton.disabled = true;
                loginButtonText.textContent = 'Memproses...';
            });
        }

        const forgotForm = document.getElementById('forgotForm');
        const forgotButton = document.getElementById('forgotButton');
        const forgotButtonText = document.getElementById('forgotButtonText');

        if (forgotForm && forgotButton && forgotButtonText) {
            forgotForm.addEventListener('submit', function () {
                forgotButton.disabled = true;
                forgotButtonText.textContent = 'Mengirim...';
            });
        }
    </script>
</body>
</html>
```