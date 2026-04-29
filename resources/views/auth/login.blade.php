<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | SmartVolt</title>

    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: Inter, Arial, sans-serif;
        }

        body {
            min-height: 100vh;
            background: #071121;
            color: #f8fbff;
        }

        .auth-page {
            min-height: 100vh;
            display: grid;
            grid-template-columns: 1.15fr 0.95fr;
            background:
                radial-gradient(circle at 42% 78%, rgba(35, 191, 255, 0.16), transparent 28%),
                radial-gradient(circle at 82% 22%, rgba(49, 231, 187, 0.12), transparent 24%),
                linear-gradient(135deg, #081a31 0%, #071121 50%, #050b17 100%);
            overflow: hidden;
        }

        .auth-left {
            position: relative;
            padding: 64px 56px;
            background:
                linear-gradient(rgba(255,255,255,0.025) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255,255,255,0.025) 1px, transparent 1px),
                linear-gradient(135deg, rgba(59, 130, 246, 0.08), rgba(0, 0, 0, 0));
            background-size: 28px 28px, 28px 28px, auto;
            border-right: 1px solid rgba(255, 255, 255, 0.04);
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 14px;
            margin-bottom: 54px;
        }

        .brand-icon {
            width: 54px;
            height: 54px;
            border-radius: 18px;
            display: grid;
            place-items: center;
            font-size: 28px;
            font-weight: 900;
            background: linear-gradient(135deg, #2563eb, #32d6c0);
            box-shadow: 0 18px 45px rgba(37, 99, 235, 0.25);
        }

        .brand-name {
            font-size: 25px;
            font-weight: 900;
            letter-spacing: -0.5px;
        }

        .hero-title {
            max-width: 620px;
            font-size: clamp(38px, 5vw, 58px);
            line-height: 0.98;
            font-weight: 950;
            letter-spacing: -2px;
            margin-bottom: 24px;
        }

        .hero-text {
            max-width: 640px;
            color: #d7e7ff;
            font-size: 17px;
            line-height: 1.8;
            margin-bottom: 34px;
        }

        .feature-list {
            max-width: 560px;
            display: grid;
            gap: 14px;
        }

        .feature-card {
            display: flex;
            gap: 16px;
            align-items: center;
            padding: 18px;
            border-radius: 20px;
            background: rgba(255,255,255,0.065);
            border: 1px solid rgba(255,255,255,0.07);
            backdrop-filter: blur(14px);
        }

        .feature-icon {
            width: 56px;
            height: 56px;
            border-radius: 17px;
            display: grid;
            place-items: center;
            font-size: 24px;
            background: rgba(54, 211, 190, 0.18);
            color: #6df7dd;
            flex-shrink: 0;
        }

        .feature-card h4 {
            font-size: 15px;
            font-weight: 850;
            margin-bottom: 7px;
        }

        .feature-card p {
            font-size: 14px;
            line-height: 1.5;
            color: #c8d8ec;
        }

        .orbit {
            position: absolute;
            right: 70px;
            bottom: 52px;
            width: 300px;
            height: 300px;
            border-radius: 50%;
            border: 1px solid rgba(92, 206, 255, 0.12);
            display: grid;
            place-items: center;
        }

        .orbit::before,
        .orbit::after {
            content: "";
            position: absolute;
            border-radius: 50%;
            border: 1px solid rgba(255,255,255,0.08);
        }

        .orbit::before {
            width: 225px;
            height: 225px;
        }

        .orbit::after {
            width: 140px;
            height: 140px;
        }

        .power-core {
            width: 88px;
            height: 88px;
            border-radius: 50%;
            display: grid;
            place-items: center;
            font-size: 34px;
            font-weight: 900;
            color: #061322;
            background: linear-gradient(135deg, #7cfce0, #2da8ff);
            box-shadow: 0 0 45px rgba(56, 189, 248, 0.42);
            z-index: 2;
        }

        .dot {
            position: absolute;
            width: 13px;
            height: 13px;
            border-radius: 999px;
            z-index: 3;
        }

        .dot-yellow {
            left: 34px;
            top: 126px;
            background: #ffd84d;
            box-shadow: 0 0 18px rgba(255, 216, 77, 0.7);
        }

        .dot-green {
            right: 66px;
            top: 158px;
            background: #74ffd7;
            box-shadow: 0 0 18px rgba(116, 255, 215, 0.7);
        }

        .auth-right {
            display: grid;
            place-items: center;
            padding: 42px;
            background: rgba(0,0,0,0.18);
        }

        .login-card {
            width: min(100%, 560px);
            padding: 26px;
            border-radius: 30px;
            background: rgba(15, 30, 55, 0.78);
            border: 1px solid rgba(255,255,255,0.075);
            box-shadow: 0 28px 90px rgba(0, 0, 0, 0.36);
        }

        .secure-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 13px;
            border-radius: 999px;
            background: rgba(255,255,255,0.08);
            color: #dbeafe;
            font-size: 12px;
            font-weight: 850;
            letter-spacing: 0.6px;
            text-transform: uppercase;
            margin-bottom: 8px;
        }

        .login-card h1 {
            font-size: 30px;
            line-height: 1.1;
            font-weight: 950;
            letter-spacing: -0.8px;
            margin-bottom: 10px;
        }

        .login-subtitle {
            color: #aebed4;
            font-size: 15px;
            line-height: 1.6;
            margin-bottom: 22px;
        }

        .alert {
            padding: 13px 15px;
            border-radius: 15px;
            font-size: 14px;
            line-height: 1.5;
            margin-bottom: 16px;
        }

        .alert-danger {
            background: rgba(248, 113, 113, 0.12);
            border: 1px solid rgba(248, 113, 113, 0.22);
            color: #fecaca;
        }

        .alert-success {
            background: rgba(52, 211, 153, 0.12);
            border: 1px solid rgba(52, 211, 153, 0.22);
            color: #bbf7d0;
        }

        .form-group {
            margin-bottom: 16px;
        }

        .form-group label {
            display: block;
            color: #cbd8ea;
            font-size: 13px;
            font-weight: 850;
            letter-spacing: 0.7px;
            text-transform: uppercase;
            margin-bottom: 9px;
        }

        .input-wrap {
            position: relative;
        }

        .input-icon {
            position: absolute;
            left: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: #7f9cc2;
            font-size: 18px;
            pointer-events: none;
        }

        .input-control {
            width: 100%;
            height: 56px;
            border: none;
            outline: none;
            border-radius: 18px;
            padding: 0 52px 0 48px;
            background: #eaf2ff;
            color: #101827;
            font-size: 15px;
            font-weight: 600;
        }

        .input-control::placeholder {
            color: #8191a8;
        }

        .toggle-password {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            border: none;
            background: transparent;
            color: #7f9cc2;
            font-size: 18px;
            cursor: pointer;
        }

        .login-options {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 16px;
            margin: 5px 4px 18px;
            flex-wrap: wrap;
        }

        .remember {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: #bdcce0;
            font-size: 14px;
            cursor: pointer;
        }

        .remember input {
            width: 15px;
            height: 15px;
            accent-color: #38bdf8;
        }

        .forgot-link {
            color: #6ef1d2;
            text-decoration: none;
            font-size: 14px;
            font-weight: 850;
        }

        .forgot-link:hover {
            color: #38bdf8;
            text-decoration: underline;
        }

        .login-button {
            width: 100%;
            height: 58px;
            border: none;
            border-radius: 17px;
            cursor: pointer;
            font-size: 15px;
            font-weight: 950;
            letter-spacing: 4px;
            color: #061322;
            background: linear-gradient(135deg, #38bdf8, #5b82ff);
            box-shadow: 0 18px 38px rgba(59, 130, 246, 0.28);
            transition: transform 0.15s ease, box-shadow 0.15s ease;
        }

        .login-button:hover {
            transform: translateY(-1px);
            box-shadow: 0 22px 45px rgba(59, 130, 246, 0.36);
        }

        .auth-footer {
            margin-top: 18px;
            text-align: center;
            color: #aebed4;
            font-size: 14px;
        }

        .auth-footer a {
            color: #6ef1d2;
            text-decoration: none;
            font-weight: 900;
        }

        .auth-footer a:hover {
            color: #38bdf8;
            text-decoration: underline;
        }

        @media (max-width: 980px) {
            .auth-page {
                grid-template-columns: 1fr;
            }

            .auth-left {
                min-height: auto;
                padding: 42px 26px;
            }

            .auth-right {
                padding: 30px 20px 42px;
            }

            .orbit {
                display: none;
            }

            .hero-title {
                font-size: 40px;
            }
        }

        @media (max-width: 520px) {
            .brand {
                margin-bottom: 34px;
            }

            .hero-title {
                font-size: 34px;
            }

            .feature-card {
                align-items: flex-start;
            }

            .login-card {
                padding: 22px;
                border-radius: 24px;
            }

            .login-options {
                align-items: flex-start;
                flex-direction: column;
                gap: 10px;
            }
        }
    </style>
</head>
<body>
    <main class="auth-page">
        <section class="auth-left">
            <div class="brand">
                <div class="brand-icon">⚡</div>
                <div class="brand-name">SmartVolt</div>
            </div>

            <h1 class="hero-title">
                Control energy like it is a living system.
            </h1>

            <p class="hero-text">
                SmartVolt menyatukan monitoring konsumsi listrik, status perangkat,
                dan kontrol real-time ke dalam satu pengalaman yang terasa seperti
                produk, bukan panel admin biasa.
            </p>

            <div class="feature-list">
                <div class="feature-card">
                    <div class="feature-icon">⌁</div>
                    <div>
                        <h4>Live monitoring</h4>
                        <p>Lihat perubahan daya, energi, dan status perangkat secara cepat dari satu dashboard yang bersih.</p>
                    </div>
                </div>

                <div class="feature-card">
                    <div class="feature-icon">▦</div>
                    <div>
                        <h4>Room orchestration</h4>
                        <p>Kelola banyak ruangan dan device tanpa membuat antarmuka terasa penuh atau membingungkan.</p>
                    </div>
                </div>
            </div>

            <div class="orbit">
                <div class="dot dot-yellow"></div>
                <div class="dot dot-green"></div>
                <div class="power-core">⚡</div>
            </div>
        </section>

        <section class="auth-right">
            <div class="login-card">
                <div class="secure-badge">♡ Secure Access</div>

                @php
                    $mode = request('mode');
                @endphp

                @if ($mode !== 'forgot')
                    <h1>Welcome back</h1>

                    <p class="login-subtitle">
                        Masuk untuk memantau energi, mengontrol device, dan melanjutkan
                        aktivitas terakhir Anda.
                    </p>
                @else
                    <h1>Lupa Password</h1>

                    <p class="login-subtitle">
                        Masukkan email Anda untuk menerima link reset password.
                    </p>
                @endif

                @if (session('status'))
                    <div class="alert alert-success">
                        {{ session('status') }}
                    </div>
                @endif

                @if ($errors->any())
                    <div class="alert alert-danger">
                        @foreach ($errors->all() as $error)
                            <div>{{ $error }}</div>
                        @endforeach
                    </div>
                @endif

                @if ($mode !== 'forgot')
                    <form action="{{ url('/login') }}" method="POST">
                        @csrf

                        <div class="form-group">
                            <label for="email">Email</label>
                            <div class="input-wrap">
                                <span class="input-icon">✉</span>
                                <input
                                    type="email"
                                    id="email"
                                    name="email"
                                    class="input-control"
                                    value="{{ old('email') }}"
                                    placeholder="Masukkan email"
                                    required
                                    autofocus
                                >
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="password">Password</label>
                            <div class="input-wrap">
                                <span class="input-icon">🔒</span>
                                <input
                                    type="password"
                                    id="password"
                                    name="password"
                                    class="input-control"
                                    placeholder="Masukkan password"
                                    required
                                >
                                <button type="button" class="toggle-password" onclick="togglePassword()">
                                    👁
                                </button>
                            </div>
                        </div>

                        <div class="login-options">
                            <label class="remember">
                                <input type="checkbox" name="remember" value="1">
                                <span>Remember me</span>
                            </label>

                            <a href="{{ url('/login?mode=forgot') }}" class="forgot-link">
                                Forgot Password?
                            </a>
                        </div>

                        <button type="submit" class="login-button">LOGIN</button>
                    </form>
               @else
    <form action="{{ url('/forgot-password') }}" method="POST">
        @csrf

        <div class="form-group">
            <label for="forgot_email">Email</label>
            <div class="input-wrap">
                <span class="input-icon">✉</span>
                <input
                    type="email"
                    id="forgot_email"
                    name="email"
                    class="input-control"
                    value="{{ old('email') }}"
                    placeholder="Masukkan email Anda"
                    required
                    autofocus
                >
            </div>
        </div>

        <button type="submit" class="login-button">
            KIRIM LINK
        </button>

        <div class="auth-footer">
            <a href="{{ url('/login') }}">Kembali ke login</a>
        </div>
    </form>
@endif

                @if ($mode !== 'forgot')
                    <div class="auth-footer">
                        Belum punya akun?
                        <a href="{{ url('/register') }}">Register</a>
                    </div>
                @endif
            </div>
        </section>
    </main>

    <script>
        function togglePassword() {
            const passwordInput = document.getElementById('password');

            if (passwordInput) {
                passwordInput.type = passwordInput.type === 'password' ? 'text' : 'password';
            }
        }
    </script>
</body>
</html>