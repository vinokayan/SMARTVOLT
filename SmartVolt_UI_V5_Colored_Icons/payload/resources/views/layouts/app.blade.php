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
    <link rel="stylesheet" href="{{ asset('assets/css/smartvolt-app.css') }}?v=20260730-v5">

    @stack('styles')
    @stack('head-scripts')
</head>
@php
    $routeName = request()->route()?->getName() ?? '';
    $isTechnicianPage = request()->routeIs('settings*') && request('tab') === 'technician';

    [$pageIcon, $pageIconTone] = match (true) {
        request()->routeIs('dashboard*') => ['home', 'blue'],
        request()->routeIs('energy.history*') => ['chart', 'green'],
        request()->routeIs('rooms*') => ['rooms', 'violet'],
        request()->routeIs('devices*') => ['devices', 'cyan'],
        $isTechnicianPage => ['tools', 'amber'],
        request()->routeIs('settings*') => ['settings', 'slate'],
        default => ['bolt', 'blue'],
    };
@endphp
<body class="sv-app-body @yield('body-class')">
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
                        <small>Energi Cerdas</small>
                    </span>
                </a>

                <button type="button" class="sv-icon-button sv-sidebar-close" data-sidebar-close aria-label="Tutup menu">
                    <x-icon name="close" :size="20" />
                </button>
            </div>

            <nav class="sv-sidebar-nav" aria-label="Navigasi utama">
                <a href="{{ route('dashboard') }}" class="sv-nav-link sv-nav-link--blue {{ request()->routeIs('dashboard*') ? 'is-active' : '' }}">
                    <x-feature-icon name="home" tone="blue" :size="18" variant="flat" class="sv-nav-feature-icon" />
                    <span>Beranda</span>
                </a>

                <a href="{{ route('energy.history') }}" class="sv-nav-link sv-nav-link--green {{ request()->routeIs('energy.history*') ? 'is-active' : '' }}">
                    <x-feature-icon name="chart" tone="green" :size="18" variant="flat" class="sv-nav-feature-icon" />
                    <span>Pemakaian Listrik</span>
                </a>

                <a href="{{ route('rooms') }}" class="sv-nav-link sv-nav-link--violet {{ request()->routeIs('rooms*') || request()->routeIs('devices*') ? 'is-active' : '' }}">
                    <x-feature-icon name="rooms" tone="violet" :size="18" variant="flat" class="sv-nav-feature-icon" />
                    <span>Ruangan & Perangkat</span>
                </a>

                <a href="{{ route('settings') }}" class="sv-nav-link sv-nav-link--slate {{ request()->routeIs('settings*') && !$isTechnicianPage ? 'is-active' : '' }}">
                    <x-feature-icon name="settings" tone="slate" :size="18" variant="flat" class="sv-nav-feature-icon" />
                    <span>Pengaturan</span>
                </a>

                <div class="sv-sidebar-divider"></div>
                <p class="sv-nav-label">Akses teknis</p>

                <a href="{{ route('settings', ['tab' => 'technician']) }}#technician" class="sv-nav-link sv-nav-link-technical sv-nav-link--amber {{ $isTechnicianPage ? 'is-active' : '' }}">
                    <x-feature-icon name="tools" tone="amber" :size="18" variant="flat" class="sv-nav-feature-icon" />
                    <span>Mode Teknisi</span>
                    <x-icon name="chevron-right" :size="16" class="sv-nav-chevron" />
                </a>
            </nav>

            <div class="sv-sidebar-foot">
                <div class="sv-sidebar-energy-card">
                    <x-feature-icon name="energy" tone="cyan" :size="22" variant="solid" />
                    <div>
                        <strong>SmartVolt</strong>
                        <p>Energi cerdas, hidup nyaman.</p>
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

                    <x-feature-icon :name="$pageIcon" :tone="$pageIconTone" :size="20" class="sv-page-heading-icon" />

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
                                <x-feature-icon name="settings" tone="slate" :size="16" variant="flat" />
                                <span>Pengaturan akun</span>
                            </a>

                            <form action="{{ route('logout') }}" method="POST">
                                @csrf
                                <button type="submit">
                                    <x-feature-icon name="logout" tone="rose" :size="16" variant="flat" />
                                    <span>Keluar</span>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </header>

            <div class="sv-page-content">
                @if(session('status') || session('success'))
                    <div class="sv-alert sv-alert-success" role="status">
                        <x-feature-icon name="check" tone="green" :size="18" variant="flat" />
                        <div>{{ session('status') ?? session('success') }}</div>
                    </div>
                @endif

                @if($errors->any())
                    <div class="sv-alert sv-alert-danger" role="alert">
                        <x-feature-icon name="warning" tone="rose" :size="18" variant="flat" />
                        <div>
                            <strong>Periksa kembali data yang dimasukkan.</strong>
                            <p>{{ $errors->first() }}</p>
                        </div>
                    </div>
                @endif

                @yield('content')
            </div>

            <nav class="sv-bottom-navigation" aria-label="Navigasi mobile">
                <a href="{{ route('dashboard') }}" class="sv-bottom-link--blue {{ request()->routeIs('dashboard*') ? 'is-active' : '' }}">
                    <x-feature-icon name="home" tone="blue" :size="18" variant="flat" />
                    <span>Beranda</span>
                </a>
                <a href="{{ route('energy.history') }}" class="sv-bottom-link--green {{ request()->routeIs('energy.history*') ? 'is-active' : '' }}">
                    <x-feature-icon name="chart" tone="green" :size="18" variant="flat" />
                    <span>Pemakaian</span>
                </a>
                <a href="{{ route('rooms') }}" class="sv-bottom-link--violet {{ request()->routeIs('rooms*') || request()->routeIs('devices*') ? 'is-active' : '' }}">
                    <x-feature-icon name="rooms" tone="violet" :size="18" variant="flat" />
                    <span>Perangkat</span>
                </a>
                <a href="{{ route('settings') }}" class="sv-bottom-link--slate {{ request()->routeIs('settings*') ? 'is-active' : '' }}">
                    <x-feature-icon name="settings" tone="slate" :size="18" variant="flat" />
                    <span>Pengaturan</span>
                </a>
            </nav>
        </main>
    </div>

    <div class="sv-toast-region" data-toast-region aria-live="polite" aria-atomic="true"></div>

    <script src="{{ asset('assets/js/smartvolt-app.js') }}?v=20260730-v5" defer></script>
    @stack('scripts')
</body>
</html>
