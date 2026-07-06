<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - SmartVolt</title>
    <link rel="stylesheet" href="{{ asset('assets/css/smartvolt-brand.css') }}?v={{ filemtime(public_path('assets/css/smartvolt-brand.css')) }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body class="brand-body">
    <div class="sv-auth-layout">
        <section class="sv-auth-panel" style="margin:auto;">
            <div class="sv-auth-card sv-glass">
                <div class="sv-auth-body">
                    <div class="sv-badge"><i class="bi bi-shield-lock-fill"></i> Reset password</div>
                    <h2 class="sv-auth-title">Buat password baru</h2>
                    <p class="sv-auth-subtitle">
                        Masukkan password baru untuk akun SmartVolt Anda.
                    </p>

                    @if ($errors->any())
                        <div class="sv-alert error">{{ $errors->first() }}</div>
                    @endif

                    <form method="POST" action="{{ route('password.update') }}">
                        @csrf

                        <input type="hidden" name="token" value="{{ $token }}">

                        <div class="sv-field">
                            <label class="sv-label" for="email">Email</label>
                            <div class="sv-input-wrap">
                                <i class="bi bi-envelope-fill sv-input-icon"></i>
                                <input
                                    id="email"
                                    type="email"
                                    name="email"
                                    class="sv-input"
                                    value="{{ old('email', $email) }}"
                                    required
                                >
                            </div>
                        </div>

                        <div class="sv-field">
                            <label class="sv-label" for="password">Password baru</label>
                            <div class="sv-input-wrap">
                                <i class="bi bi-lock-fill sv-input-icon"></i>
                                <input
                                    id="password"
                                    type="password"
                                    name="password"
                                    class="sv-input"
                                    required
                                >
                            </div>
                        </div>

                        <div class="sv-field">
                            <label class="sv-label" for="password_confirmation">Konfirmasi password</label>
                            <div class="sv-input-wrap">
                                <i class="bi bi-lock-fill sv-input-icon"></i>
                                <input
                                    id="password_confirmation"
                                    type="password"
                                    name="password_confirmation"
                                    class="sv-input"
                                    required
                                >
                            </div>
                        </div>

                        <button type="submit" class="sv-btn sv-auth-submit">
                            Reset password
                        </button>
                    </form>

                    <div class="sv-auth-foot">
                        <a href="{{ route('login') }}" class="sv-link">Kembali ke login</a>
                    </div>
                </div>
            </div>
        </section>
    </div>
</body>
</html>