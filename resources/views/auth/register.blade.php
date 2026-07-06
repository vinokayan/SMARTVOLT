<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - SmartVolt</title>
    <link rel="stylesheet" href="{{ asset('assets/css/smartvolt-brand.css') }}?v={{ filemtime(public_path('assets/css/smartvolt-brand.css')) }}">
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

                <h1 class="sv-showcase-title">Start Your Smart Energy Journey Today</h1>
                <p class="sv-showcase-desc">
                 <p class="sv-showcase-desc">
    Create your SmartVolt workspace and take control of your home energy with real-time monitoring, device control, and smarter electricity management.
</p>
                </p>

                <div class="sv-feature-stack">
                    <div class="sv-feature-tile">
                        <div class="tile-icon"><i class="bi bi-house-door-fill"></i></div>
                        <div>
                            <h4>Room-Based Energy Control</h4>
                            <p>Organize rooms and devices easily so every part of your home can be monitored and controlled in one place</p>
                        </div>
                    </div>

                    <div class="sv-feature-tile">
                        <div class="tile-icon"><i class="bi bi-cpu-fill"></i></div>
                        <div>
                            <h4>Ready for IoT Integration</h4>
                            <p>Connect your ESP device, track sensor data, and control appliances directly from your SmartVolt dashboard</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="sv-energy-orbit">
                <div class="sv-orbit-ring r1"></div>
                <div class="sv-orbit-ring r2"></div>
                <div class="sv-orbit-ring r3"></div>
                <div class="sv-orbit-core"><i class="bi bi-plug-fill"></i></div>
                <div class="sv-orbit-dot d1"></div>
                <div class="sv-orbit-dot d2"></div>
            </div>
        </section>

        <section class="sv-auth-panel">
            <div class="sv-auth-card sv-glass">
                <div class="sv-auth-mobile-hero">
                    <div class="sv-badge"><i class="bi bi-person-plus-fill"></i> Create SmartVolt Account</div>
                    <h1>Create Your SmartVolt Identity</h1>
                    <p>Sign up to start monitoring energy and managing your devices smarte</p>

                    <div class="sv-mini-wave">
                        <span></span><span></span><span></span><span></span><span></span>
                    </div>
                </div>

                <div class="sv-auth-body">
                    <div class="sv-badge"><i class="bi bi-stars"></i> New SmartVolt Account</div>
                    <h2 class="sv-auth-title">Create account</h2>
                    <p class="sv-auth-subtitle">
                         Fill in your details to join SmartVolt and start building a smarter energy monitoring system
                    </p>

                    @if ($errors->any())
                        <div class="sv-alert error">{{ $errors->first() }}</div>
                    @endif
                    <form id="registerForm" action="{{ url('/register') }}" method="POST">
                 
                        @csrf

                        <div class="sv-field-grid two-col">
                            <div class="sv-field sv-span-2">
                                <label class="sv-label" for="name">Full Name</label>
                                <div class="sv-input-wrap">
                                    <i class="bi bi-person-fill sv-input-icon"></i>
                                    <input
                                        id="name"
                                        type="text"
                                        name="name"
                                        class="sv-input"
                                        placeholder="Enter your full name"
                                        value="{{ old('name') }}"
                                        required
                                    >
                                </div>
                                @error('name')
                                    <div class="sv-error-text">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="sv-field sv-span-2">
                                <label class="sv-label" for="email">Email</label>
                                <div class="sv-input-wrap">
                                    <i class="bi bi-envelope-fill sv-input-icon"></i>
                                    <input
                                        id="email"
                                        type="email"
                                        name="email"
                                        class="sv-input"
                                        placeholder="Enter your email address"
                                        value="{{ old('email') }}"
                                        required
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
                                        placeholder="Minimum 6 characters"
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

                            <div class="sv-field">
                                <label class="sv-label" for="password_confirmation">Confirm Password</label>
                                <div class="sv-input-wrap">
                                    <i class="bi bi-shield-lock-fill sv-input-icon"></i>
                                    <input
                                        id="password_confirmation"
                                        type="password"
                                        name="password_confirmation"
                                        class="sv-input"
                                        placeholder="Re-enter your password"
                                        required
                                    >
                                    <button type="button" class="sv-password-toggle" id="togglePasswordConfirm">
                                        <i class="bi bi-eye-fill" id="togglePasswordConfirmIcon"></i>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="sv-helper">
                            Password must be at least 6 characters and match the confirmation
                        </div>

                        <button type="submit" class="sv-btn sv-auth-submit success" id="registerButton">
                            <span id="registerButtonText">Register</span>
                        </button>
                    </form>

                    <div class="sv-auth-foot">
                         Already have an account?
                        <a href="{{ route('login') }}" class="sv-link">Login</a>
                    </div>
                </div>
            </div>
        </section>
    </div>

    <script>
        const registerForm = document.getElementById('registerForm');
        const registerButton = document.getElementById('registerButton');
        const registerButtonText = document.getElementById('registerButtonText');

        const passwordInput = document.getElementById('password');
        const passwordConfirmInput = document.getElementById('password_confirmation');

        const togglePassword = document.getElementById('togglePassword');
        const togglePasswordIcon = document.getElementById('togglePasswordIcon');

        const togglePasswordConfirm = document.getElementById('togglePasswordConfirm');
        const togglePasswordConfirmIcon = document.getElementById('togglePasswordConfirmIcon');

        togglePassword.addEventListener('click', function () {
            const isPassword = passwordInput.type === 'password';
            passwordInput.type = isPassword ? 'text' : 'password';
            togglePasswordIcon.className = isPassword ? 'bi bi-eye-slash-fill' : 'bi bi-eye-fill';
        });

        togglePasswordConfirm.addEventListener('click', function () {
            const isPassword = passwordConfirmInput.type === 'password';
            passwordConfirmInput.type = isPassword ? 'text' : 'password';
            togglePasswordConfirmIcon.className = isPassword ? 'bi bi-eye-slash-fill' : 'bi bi-eye-fill';
        });

        registerForm.addEventListener('submit', function () {
            registerButton.disabled = true;
            registerButtonText.textContent = 'creating...';
        });
    </script>
</body>
</html>