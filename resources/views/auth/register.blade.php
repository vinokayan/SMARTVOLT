<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar - SmartVolt</title>
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

                <h1 class="sv-showcase-title">Mulai Hidup Hemat Energi Bareng SmartVolt</h1>
                <p class="sv-showcase-desc">
                 <p class="sv-showcase-desc">
    Buat akun SmartVolt kamu dan kendalikan energi rumah secara real-time — pantau pemakaian listrik, atur perangkat, dan hemat tagihan dengan lebih mudah.
</p>
                </p>

                <div class="sv-feature-stack">
                    <div class="sv-feature-tile">
                        <div class="tile-icon"><i class="bi bi-house-door-fill"></i></div>
                        <div>
                            <h4>Kontrol Energi per Ruangan</h4>
                            <p>Atur ruangan dan perangkat dengan mudah, sehingga setiap sudut rumah bisa dipantau dan dikendalikan dari satu tempat</p>
                        </div>
                    </div>

                    <div class="sv-feature-tile">
                        <div class="tile-icon"><i class="bi bi-cpu-fill"></i></div>
                        <div>
                            <h4>Siap Terhubung dengan IoT</h4>
                            <p>Pantau konsumsi listrik dan kendalikan perangkat elektronik dengan mudah melalui SmartVolt</p>
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
                    <div class="sv-badge"><i class="bi bi-person-plus-fill"></i> Buat Akun SmartVolt</div>
                    <h1>Buat Identitas SmartVolt Kamu</h1>
                    <p>Daftar sekarang untuk mulai memantau energi dan mengelola perangkat rumah dengan lebih pintar</p>

                    <div class="sv-mini-wave">
                        <span></span><span></span><span></span><span></span><span></span>
                    </div>
                </div>

                <div class="sv-auth-body">
                    <div class="sv-badge"><i class="bi bi-stars"></i> Akun SmartVolt Baru</div>
                    <h2 class="sv-auth-title">Buat akun</h2>
                    <p class="sv-auth-subtitle">
                         Isi data diri kamu untuk bergabung dengan SmartVolt dan mulai membangun sistem monitoring energi yang lebih pintar
                    </p>

                    @if ($errors->any())
                        <div class="sv-alert error">{{ $errors->first() }}</div>
                    @endif
                    <form id="registerForm" action="{{ url('/register') }}" method="POST">
                 
                        @csrf

                        <div class="sv-field-grid two-col">
                            <div class="sv-field sv-span-2">
                                <label class="sv-label" for="name">Nama Lengkap</label>
                                <div class="sv-input-wrap">
                                    <i class="bi bi-person-fill sv-input-icon"></i>
                                    <input
                                        id="name"
                                        type="text"
                                        name="name"
                                        class="sv-input"
                                        placeholder="Masukkan nama lengkap kamu"
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
                                        placeholder="Masukkan alamat email kamu"
                                        value="{{ old('email') }}"
                                        required
                                    >
                                </div>
                                @error('email')
                                    <div class="sv-error-text">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="sv-field">
                                <label class="sv-label" for="password">Kata Sandi</label>
                                <div class="sv-input-wrap">
                                    <i class="bi bi-lock-fill sv-input-icon"></i>
                                    <input
                                        id="password"
                                        type="password"
                                        name="password"
                                        class="sv-input"
                                        placeholder="Minimal 6 karakter"
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
                                <label class="sv-label" for="password_confirmation">Konfirmasi Kata Sandi</label>
                                <div class="sv-input-wrap">
                                    <i class="bi bi-shield-lock-fill sv-input-icon"></i>
                                    <input
                                        id="password_confirmation"
                                        type="password"
                                        name="password_confirmation"
                                        class="sv-input"
                                        placeholder="Masukkan ulang kata sandi kamu"
                                        required
                                    >
                                    <button type="button" class="sv-password-toggle" id="togglePasswordConfirm">
                                        <i class="bi bi-eye-fill" id="togglePasswordConfirmIcon"></i>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="sv-helper">
                            Kata sandi minimal 6 karakter dan harus sama dengan konfirmasinya
                        </div>

                        <button type="submit" class="sv-btn sv-auth-submit success" id="registerButton">
                            <span id="registerButtonText">Daftar</span>
                        </button>
                    </form>

                    <div class="sv-auth-foot">
                         Sudah punya akun?
                        <a href="{{ route('login') }}" class="sv-link">Masuk</a>
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
            registerButtonText.textContent = 'sedang membuat...';
        });
    </script>
</body>
</html>