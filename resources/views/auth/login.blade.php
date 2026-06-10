<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | SmartVolt</title>

    <link rel="stylesheet" href="{{ asset('assets/css/smartvolt-brand.css') }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
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
                    Control energy like it is a living system.
                </h1>

                <p class="sv-showcase-desc">
                    SmartVolt helps you monitor electricity usage, view device status, and control everything in real time through a modern and easy-to-use dashboard
                </p>

                <div class="sv-feature-stack">
                    <div class="sv-feature-tile">
                        <div class="tile-icon">
                            <i class="bi bi-activity"></i>
                        </div>
                        <div>
                            <h4>Real-Time Energy Monitoring</h4>
                            <p>Track power, energy, and device status instantly to keep your electricity usage under control</p>
                        </div>
                    </div>

                    <div class="sv-feature-tile">
                        <div class="tile-icon">
                            <i class="bi bi-grid-3x3-gap-fill"></i>
                        </div>
                        <div>
                            <h4>Room orchestration</h4>
                            <p>Manage devices in every room easily without turning them on or off manually</p>
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
                <div class="sv-auth-mobile-hero">
                    <div class="sv-badge">
                        <i class="bi bi-shield-check"></i> Secure SmartVolt Access
                    </div>

                    @if ($mode !== 'forgot')
                        <h1>Welcome back</h1>
                        <p>Sign in to start monitoring energy, controlling devices, and managing your home electricity more efficiently</p>
                    @else
                        <h1>Lupa Password</h1>
                        <p>Enter your email address</p>
                    @endif

                    <div class="sv-mini-wave">
                        <span></span><span></span><span></span><span></span><span></span>
                    </div>
                </div>

                <div class="sv-auth-body">
                    <div class="sv-badge">
                        <i class="bi bi-shield-check"></i> Secure SmartVolt Access
                    </div>

                    @if ($mode !== 'forgot')
                        <h2 class="sv-auth-title">Welcome back</h2>

                        <p class="sv-auth-subtitle">
                            Sign in to start monitoring energy, controlling devices, and managing your home electricity more efficiently
                        </p>
                    @else
                        <h2 class="sv-auth-title">Lupa Password</h2>

                        <p class="sv-auth-subtitle">
                            Enter your email address
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
                                    <label class="sv-label" for="email">Email</label>
                                    <div class="sv-input-wrap">
                                        <i class="bi bi-envelope-fill sv-input-icon"></i>
                                        <input
                                            type="email"
                                            id="email"
                                            name="email"
                                            class="sv-input"
                                            value="{{ old('email') }}"
                                            placeholder="Masukkan email"
                                            required
                                            autofocus
                                        >
                                    </div>
                                </div>

                                <div class="sv-field">
                                    <label class="sv-label" for="password">Password</label>
                                    <div class="sv-input-wrap">
                                        <i class="bi bi-lock-fill sv-input-icon"></i>
                                        <input
                                            type="password"
                                            id="password"
                                            name="password"
                                            class="sv-input"
                                            placeholder="Masukkan password"
                                            required
                                        >
                                        <button type="button" class="sv-password-toggle" id="togglePassword">
                                            <i class="bi bi-eye-fill" id="togglePasswordIcon"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <div class="sv-helper">
                                <label>
                                    <input type="checkbox" name="remember" value="1">
                                    <span>Remember me</span>
                                </label>

                                <span>&nbsp;&nbsp;|&nbsp;&nbsp;</span>

                                <a href="{{ url('/login?mode=forgot') }}" class="sv-link">
                                    Forgot Password?
                                </a>
                            </div>

                            <button type="submit" class="sv-btn sv-auth-submit success" id="loginButton">
                                <span id="loginButtonText">Login</span>
                            </button>
                        </form>

                        <div class="sv-auth-foot">
                            Don’t have an account?
                            <a href="{{ url('/register') }}" class="sv-link">Register now</a>
                        </div>
                    @else
                        <form id="forgotForm" action="{{ url('/forgot-password') }}" method="POST">
                            @csrf

                            <div class="sv-field-grid">
                                <div class="sv-field">
                                    <label class="sv-label" for="forgot_email">Email</label>
                                    <div class="sv-input-wrap">
                                        <i class="bi bi-envelope-fill sv-input-icon"></i>
                                        <input
                                            type="email"
                                            id="forgot_email"
                                            name="email"
                                            class="sv-input"
                                            value="{{ old('email') }}"
                                            placeholder="Masukkan email Anda"
                                            required
                                            autofocus
                                        >
                                    </div>
                                </div>
                            </div>

                            <button type="submit" class="sv-btn sv-auth-submit success" id="forgotButton">
                                <span id="forgotButtonText">Send reset link</span>
                            </button>

                            <div class="sv-auth-foot">
                                <a href="{{ url('/login') }}" class="sv-link">Back to Login</a>
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
            });
        }

        const loginForm = document.getElementById('loginForm');
        const loginButton = document.getElementById('loginButton');
        const loginButtonText = document.getElementById('loginButtonText');

        if (loginForm && loginButton && loginButtonText) {
            loginForm.addEventListener('submit', function () {
                loginButton.disabled = true;
                loginButtonText.textContent = 'Loading...';
            });
        }

        const forgotForm = document.getElementById('forgotForm');
        const forgotButton = document.getElementById('forgotButton');
        const forgotButtonText = document.getElementById('forgotButtonText');

        if (forgotForm && forgotButton && forgotButtonText) {
            forgotForm.addEventListener('submit', function () {
                forgotButton.disabled = true;
                forgotButtonText.textContent = 'Loading...';
            });
        }
    </script>
</body>
</html>