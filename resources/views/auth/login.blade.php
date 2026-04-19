<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - SmartVolt</title>
    <link rel="stylesheet" href="{{ asset('assets/css/smartvolt-brand.css') }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body class="brand-body">
    <div class="sv-auth-layout">
        <section class="sv-auth-showcase">
            <div class="sv-showcase-inner">
                <div class="sv-brandmark">
                    <div class="icon"><i class="bi bi-lightning-charge-fill"></i></div>
                    <span>SmartVolt</span>
                </div>

                <h1 class="sv-showcase-title">Control energy like it is a living system.</h1>
                <p class="sv-showcase-desc">
                    SmartVolt menyatukan monitoring konsumsi listrik, status perangkat, dan kontrol real-time ke dalam satu pengalaman yang terasa seperti produk, bukan panel admin biasa.
                </p>

                <div class="sv-feature-stack">
                    <div class="sv-feature-tile">
                        <div class="tile-icon"><i class="bi bi-broadcast-pin"></i></div>
                        <div>
                            <h4>Live monitoring</h4>
                            <p>Lihat perubahan daya, energi, dan status perangkat secara cepat dari satu dashboard yang bersih.</p>
                        </div>
                    </div>

                    <div class="sv-feature-tile">
                        <div class="tile-icon"><i class="bi bi-grid-1x2-fill"></i></div>
                        <div>
                            <h4>Room orchestration</h4>
                            <p>Kelola banyak ruangan dan device tanpa membuat antarmuka terasa penuh atau membingungkan.</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="sv-energy-orbit">
                <div class="sv-orbit-ring r1"></div>
                <div class="sv-orbit-ring r2"></div>
                <div class="sv-orbit-ring r3"></div>
                <div class="sv-orbit-core"><i class="bi bi-lightning-charge-fill"></i></div>
                <div class="sv-orbit-dot d1"></div>
                <div class="sv-orbit-dot d2"></div>
            </div>
        </section>

        <section class="sv-auth-panel">
            <div class="sv-auth-card sv-glass">
                <div class="sv-auth-mobile-hero">
                    <div class="sv-badge"><i class="bi bi-lightning-charge-fill"></i> SmartVolt Energy Cloud</div>
                    <h1>Masuk ke pusat kontrol energi.</h1>
                    <p>Login untuk membuka dashboard monitoring dan kontrol SmartVolt.</p>

                    <div class="sv-mini-wave">
                        <span></span><span></span><span></span><span></span><span></span>
                    </div>
                </div>

                <div class="sv-auth-body">
                    <div class="sv-badge"><i class="bi bi-shield-check"></i> Secure access</div>
                    <h2 class="sv-auth-title">Welcome back</h2>
                    <p class="sv-auth-subtitle">
                        Masuk untuk memantau energi, mengontrol device, dan melanjutkan aktivitas terakhir Anda.
                    </p>

                    @if (session('status'))
                        <div class="sv-alert success">{{ session('status') }}</div>
                    @endif

                    @if ($errors->any())
                        <div class="sv-alert error">{{ $errors->first() }}</div>
                    @endif

                    <form action="{{ route('login.process') }}" method="POST" id="loginForm">
                        @csrf

                        <div class="sv-field">
                            <label class="sv-label" for="email">Email</label>
                            <div class="sv-input-wrap">
                                <i class="bi bi-envelope-fill sv-input-icon"></i>
                                <input
                                    id="email"
                                    type="email"
                                    name="email"
                                    class="sv-input"
                                    placeholder="nama@smartvolt.com"
                                    value="{{ old('email') }}"
                                    required
                                    autofocus
                                >
                            </div>
                            @error('email')
                                <div class="sv-error-text">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="sv-field">
                            <label class="sv-label" for="password">Password</label>
                            <div class="sv-input-wrap">
                                <i class="bi bi-lock-fill sv-input-icon"></i>
                                <input
                                    id="password"
                                    type="password"
                                    name="password"
                                    class="sv-input"
                                    placeholder="Masukkan password"
                                    required
                                >
                                <button type="button" class="sv-password-toggle" id="togglePassword">
                                    <i class="bi bi-eye-fill" id="togglePasswordIcon"></i>
                                </button>
                            </div>
                            @error('password')
                                <div class="sv-error-text">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="sv-auth-row">
                            <label class="sv-check">
                                <input type="checkbox" name="remember" value="1" {{ old('remember') ? 'checked' : '' }}>
                                <span>Remember me</span>
                            </label>

                            
                        </div>

                        <button type="submit" class="sv-btn sv-auth-submit" id="loginButton">
                            <span id="loginButtonText">Login</span>
                        </button>
                    </form>

                    <div class="sv-auth-foot">
                        Belum punya akun?
                        <a href="{{ route('register') }}" class="sv-link">Register</a>
                    </div>
                </div>
            </div>
        </section>
    </div>

    <script>
        const loginForm = document.getElementById('loginForm');
        const loginButton = document.getElementById('loginButton');
        const loginButtonText = document.getElementById('loginButtonText');
        const passwordInput = document.getElementById('password');
        const togglePassword = document.getElementById('togglePassword');
        const togglePasswordIcon = document.getElementById('togglePasswordIcon');

        togglePassword.addEventListener('click', function () {
            const isPassword = passwordInput.type === 'password';
            passwordInput.type = isPassword ? 'text' : 'password';
            togglePasswordIcon.className = isPassword ? 'bi bi-eye-slash-fill' : 'bi bi-eye-fill';
        });

        loginForm.addEventListener('submit', function () {
            loginButton.disabled = true;
            loginButtonText.textContent = 'Loading...';
        });
    </script>
</body>
</html>