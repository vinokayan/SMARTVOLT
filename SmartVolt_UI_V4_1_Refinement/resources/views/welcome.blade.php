<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#0b3b75">
    <title>SmartVolt</title>
    <link rel="stylesheet" href="{{ asset('assets/css/smartvolt-auth.css') }}?v=20260729">
    <link rel="stylesheet" href="{{ asset('assets/css/smartvolt-auth-v4.css') }}?v=20260730-4">
</head>
<body class="sv-auth-page">
    <main class="sv-simple-landing">
        <section class="sv-simple-landing-card">
            <span class="sv-simple-brand-mark"><x-icon name="bolt" :size="32" /></span>
            <h1>SmartVolt</h1>
            <p>Pantau penggunaan listrik dan kendalikan perangkat rumah dalam satu tempat.</p>
            <div class="sv-simple-landing-actions">
                @auth
                    <a href="{{ route('dashboard') }}" class="sv-auth-submit">Buka Beranda</a>
                @else
                    <a href="{{ route('login') }}" class="sv-auth-submit">Masuk</a>
                    <a href="{{ route('register') }}" class="sv-auth-link">Buat akun</a>
                @endauth
            </div>
        </section>
    </main>
</body>
</html>
