<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="color-scheme" content="light">
    <meta name="theme-color" content="#ffffff">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'SmartVolt') | SmartVolt</title>

    <link rel="icon" href="{{ asset('favicon.ico') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/smartvolt-app.css') }}?v=20260729">
    <link rel="stylesheet" href="{{ asset('assets/css/smartvolt-v4.css') }}?v=20260730-4">

    @stack('styles')
    @stack('head-scripts')
</head>
<body class="sv-app-body @yield('body-class')">
    <a class="sv-skip-link" href="#main-content">Lewati ke konten</a>
    <div class="sv-app-layout">
        <button type="button" class="sv-sidebar-overlay" data-sidebar-close aria-label="Tutup menu"></button>

        <aside class="sv-app-sidebar" data-sidebar>
            <div class="sv-sidebar-header">
                <a href="{{ route('dashboard') }}" class="sv-brand" aria-label="Beranda SmartVolt">
                    <span class="sv-brand-mark" aria-hidden="true">
                        <x-icon name="bolt" :size="25" />
                    </span>
                    <span class="sv-brand-copy">
                        <strong>Smart<span>Volt</span></strong>
                        <small>Energi Rumah</small>
                    </span>
                </a>

                <button type="button" class="sv-icon-button sv-sidebar-close" data-sidebar-close aria-label="Tutup menu">
                    <x-icon name="close" :size="20" />
                </button>
            </div>

            <nav class="sv-sidebar-nav" aria-label="Navigasi utama">
                <a href="{{ route('dashboard') }}" class="sv-nav-link {{ request()->routeIs('dashboard*') ? 'is-active' : '' }}">
                    <x-icon name="home" :size="20" />
                    <span>Beranda</span>
                </a>

                <a href="{{ route('energy.history') }}" class="sv-nav-link {{ request()->routeIs('energy.history*') ? 'is-active' : '' }}">
                    <x-icon name="chart" :size="20" />
                    <span>Pemakaian Listrik</span>
                </a>

                <a href="{{ route('rooms') }}" class="sv-nav-link {{ request()->routeIs('rooms*') || request()->routeIs('devices*') ? 'is-active' : '' }}">
                    <x-icon name="rooms" :size="20" />
                    <span>Ruangan & Perangkat</span>
                </a>

                <a href="{{ route('settings') }}" class="sv-nav-link {{ request()->routeIs('settings*') && request('tab') !== 'technician' ? 'is-active' : '' }}">
                    <x-icon name="settings" :size="20" />
                    <span>Pengaturan</span>
                </a>

                <div class="sv-sidebar-divider"></div>
                <p class="sv-nav-label">Akses teknis</p>

                <a href="{{ route('settings', ['tab' => 'technician']) }}#technician" class="sv-nav-link sv-nav-link-technical {{ request()->routeIs('settings*') && request('tab') === 'technician' ? 'is-active' : '' }}">
                    <x-icon name="tools" :size="20" />
                    <span>Mode Teknisi</span>
                    <x-icon name="chevron-right" :size="16" />
                </a>
            </nav>

            <div class="sv-sidebar-foot">
                <div class="sv-sidebar-energy-card">
                    <span class="sv-sidebar-energy-icon" aria-hidden="true">
                        <x-icon name="energy" :size="24" />
                    </span>
                    <div>
                        <strong>SmartVolt</strong>
                        <p>Pantau, hemat, dan kendalikan.</p>
                    </div>
                </div>
            </div>
        </aside>

        <main class="sv-app-main">
            <header class="sv-app-topbar">
                <div class="sv-topbar-left">
                    <button type="button" class="sv-icon-button sv-mobile-menu" data-sidebar-open aria-label="Buka menu">
                        <x-icon name="menu" :size="22" />
                    </button>

                    <div class="sv-topbar-heading">
                        <h1 class="sv-page-title">@yield('page-title', 'SmartVolt')</h1>
                        <p class="sv-page-subtitle">@yield('page-subtitle')</p>
                    </div>
                </div>

                <div class="sv-topbar-actions">
                    @hasSection('system-status')
                        <div class="sv-topbar-status">
                            @yield('system-status')
                        </div>
                    @endif

                    @include('components.notification-bell')

                    <div class="sv-profile-menu" data-profile-menu>
                        <button type="button" class="sv-profile-trigger" data-profile-trigger aria-expanded="false">
                            <span class="sv-avatar">{{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 1)) }}</span>
                            <span class="sv-profile-copy">
                                <strong>{{ auth()->user()->name ?? 'Pengguna' }}</strong>
                                <small>Pemilik Rumah</small>
                            </span>
                            <x-icon name="chevron-down" :size="16" />
                        </button>

                        <div class="sv-profile-dropdown" data-profile-dropdown>
                            <div class="sv-profile-dropdown-head">
                                <strong>{{ auth()->user()->name ?? 'Pengguna' }}</strong>
                                <span>{{ auth()->user()->email ?? '' }}</span>
                            </div>

                            <a href="{{ route('settings') }}">
                                <x-icon name="settings" :size="17" />
                                Pengaturan akun
                            </a>

                            <form action="{{ route('logout') }}" method="POST">
                                @csrf
                                <button type="submit">
                                    <x-icon name="logout" :size="17" />
                                    Keluar
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </header>

            <div class="sv-page-content" id="main-content">
                @if(session('status') || session('success'))
                    <div class="sv-alert sv-alert-success" role="status">
                        <x-icon name="check" :size="20" />
                        <div>{{ session('status') ?? session('success') }}</div>
                    </div>
                @endif

                @if($errors->any())
                    <div class="sv-alert sv-alert-danger" role="alert">
                        <x-icon name="warning" :size="20" />
                        <div>
                            <strong>Periksa kembali data yang dimasukkan.</strong>
                            <p>{{ $errors->first() }}</p>
                        </div>
                    </div>
                @endif

                @yield('content')
            </div>

            <nav class="sv-bottom-navigation" aria-label="Navigasi mobile">
                <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard*') ? 'is-active' : '' }}">
                    <x-icon name="home" :size="20" />
                    <span>Beranda</span>
                </a>
                <a href="{{ route('energy.history') }}" class="{{ request()->routeIs('energy.history*') ? 'is-active' : '' }}">
                    <x-icon name="chart" :size="20" />
                    <span>Pemakaian</span>
                </a>
                <a href="{{ route('rooms') }}" class="{{ request()->routeIs('rooms*') || request()->routeIs('devices*') ? 'is-active' : '' }}">
                    <x-icon name="rooms" :size="20" />
                    <span>Perangkat</span>
                </a>
                <a href="{{ route('settings') }}" class="{{ request()->routeIs('settings*') ? 'is-active' : '' }}">
                    <x-icon name="settings" :size="20" />
                    <span>Pengaturan</span>
                </a>
            </nav>
        </main>
    </div>

    <div class="sv-toast-region" data-toast-region aria-live="polite" aria-atomic="true"></div>

    <script src="{{ asset('assets/js/smartvolt-app.js') }}?v=20260730-4" defer></script>
    @stack('scripts')
</body>
</html>
