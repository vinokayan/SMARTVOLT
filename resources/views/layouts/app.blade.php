@php
    $isTechnicianPage = request()->routeIs('technician.*');
@endphp

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1, viewport-fit=cover"
    >

    <meta name="color-scheme" content="light">
    <meta name="theme-color" content="#ffffff">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'SmartVolt') | SmartVolt</title>

    <link rel="icon" href="/favicon.ico">

    {{-- CSS utama seluruh aplikasi --}}
    <link
        rel="stylesheet"
        href="{{ asset('assets/css/smartvolt-app.css') }}?v=20260803-3"
    >


    @stack('styles')

    {{--
        Skip link disembunyikan pada tampilan normal dan hanya muncul
        ketika menerima fokus dari keyboard (tombol Tab).
    --}}
    <style>
        .sv-skip-link {
            position: fixed;
            top: 12px;
            left: 12px;
            z-index: 100000;
            display: inline-flex;
            align-items: center;
            min-height: 44px;
            padding: 10px 16px;
            border: 2px solid #0f172a;
            border-radius: 10px;
            color: #0f172a;
            background: #ffffff;
            box-shadow: 0 10px 30px rgba(15, 23, 42, 0.18);
            font-weight: 700;
            text-decoration: none;
            opacity: 0;
            pointer-events: none;
            transform: translateY(calc(-100% - 28px));
            transition:
                transform 0.18s ease,
                opacity 0.18s ease;
        }

        .sv-skip-link:focus,
        .sv-skip-link:focus-visible {
            opacity: 1;
            pointer-events: auto;
            transform: translateY(0);
            outline: 3px solid rgba(37, 99, 235, 0.35);
            outline-offset: 3px;
        }
    </style>

    @stack('head-scripts')
</head>

<body class="sv-app-body @yield('body-class')">
    <a href="#main-content" class="sv-skip-link">
        Lewati ke konten
    </a>

    <div class="sv-app-layout">
        <button
            type="button"
            class="sv-sidebar-overlay"
            data-sidebar-close
            aria-label="Tutup menu"
        ></button>

        <aside
            class="sv-app-sidebar"
            data-sidebar
            aria-label="Menu utama"
        >
            <div class="sv-sidebar-header">
                <a
                    href="{{ route('dashboard') }}"
                    class="sv-brand"
                    aria-label="Beranda SmartVolt"
                >
                    <span class="sv-brand-mark" aria-hidden="true">
                        <x-icon name="bolt" :size="24" />
                    </span>

                    <span class="sv-brand-copy">
                        <strong>
                            Smart<span>Volt</span>
                        </strong>

                        <small>Energi Cerdas</small>
                    </span>
                </a>

                <button
                    type="button"
                    class="sv-icon-button sv-sidebar-close"
                    data-sidebar-close
                    aria-label="Tutup menu"
                >
                    <x-icon name="close" :size="20" />
                </button>
            </div>

            <nav class="sv-sidebar-nav" aria-label="Navigasi utama">
                <a
                    href="{{ route('dashboard') }}"
                    class="sv-nav-link sv-nav-link--blue
                        {{ request()->routeIs('dashboard*') ? 'is-active' : '' }}"
                >
                    <x-feature-icon
                        name="home"
                        tone="blue"
                        :size="18"
                        variant="flat"
                        class="sv-nav-feature-icon"
                    />

                    <span>Beranda</span>
                </a>

                <a
                    href="{{ route('energy.history') }}"
                    class="sv-nav-link sv-nav-link--green
                        {{ request()->routeIs('energy.history*') ? 'is-active' : '' }}"
                >
                    <x-feature-icon
                        name="chart"
                        tone="green"
                        :size="18"
                        variant="flat"
                        class="sv-nav-feature-icon"
                    />

                    <span>Pemakaian Listrik</span>
                </a>

                <a
                    href="{{ route('rooms') }}"
                    class="sv-nav-link sv-nav-link--violet
                        {{
                            request()->routeIs('rooms*')
                            || request()->routeIs('devices*')
                                ? 'is-active'
                                : ''
                        }}"
                >
                    <x-feature-icon
                        name="rooms"
                        tone="violet"
                        :size="18"
                        variant="flat"
                        class="sv-nav-feature-icon"
                    />

                    <span>Ruangan &amp; Perangkat</span>
                </a>

                <a
                    href="{{ route('settings') }}"
                    class="sv-nav-link sv-nav-link--slate
                        {{ request()->routeIs('settings*') ? 'is-active' : '' }}"
                >
                    <x-feature-icon
                        name="settings"
                        tone="slate"
                        :size="18"
                        variant="flat"
                        class="sv-nav-feature-icon"
                    />

                    <span>Pengaturan</span>
                </a>

                <div class="sv-sidebar-divider"></div>

                <p class="sv-nav-label">Akses teknis</p>

                <a
                    href="{{ route('technician.index') }}"
                    class="sv-nav-link sv-nav-link--amber
                        {{ $isTechnicianPage ? 'is-active' : '' }}"
                >
                    <x-feature-icon
                        name="tools"
                        tone="amber"
                        :size="18"
                        variant="flat"
                        class="sv-nav-feature-icon"
                    />

                    <span>Mode Teknisi</span>
                </a>
            </nav>

            <div
                class="sv-sidebar-footer-simple"
                aria-label="SmartVolt"
            >
                <span>SmartVolt</span>
            </div>
        </aside>

        <main class="sv-app-main" id="main-content" tabindex="-1">
            <header class="sv-app-topbar">
                <div class="sv-topbar-inner">
                    <div class="sv-topbar-left">
                        <button
                            type="button"
                            class="sv-icon-button sv-mobile-menu"
                            data-sidebar-open
                            aria-label="Buka menu"
                        >
                            <x-icon name="menu" :size="22" />
                        </button>

                        <div class="sv-topbar-heading">
                            <h1 class="sv-page-title">
                                @yield('page-title', 'SmartVolt')
                            </h1>

                            @hasSection('page-subtitle')
                                <p class="sv-page-subtitle">
                                    @yield('page-subtitle')
                                </p>
                            @endif
                        </div>
                    </div>

                    <div class="sv-topbar-actions">
                        @hasSection('system-status')
                            <div class="sv-topbar-status">
                                @yield('system-status')
                            </div>
                        @endif

                        @include('components.notification-bell')

                        <div
                            class="sv-profile-menu"
                            data-profile-menu
                        >
                            <button
                                type="button"
                                class="sv-profile-trigger"
                                data-profile-trigger
                                aria-expanded="false"
                            >
                                <span class="sv-avatar">
                                    {{
                                        strtoupper(
                                            substr(
                                                auth()->user()->name ?? 'U',
                                                0,
                                                1
                                            )
                                        )
                                    }}
                                </span>

                                <span class="sv-profile-copy">
                                    <strong>
                                        {{ auth()->user()->name ?? 'Pengguna' }}
                                    </strong>

                                    <small>Pemilik Rumah</small>
                                </span>

                                <x-icon
                                    name="chevron-down"
                                    :size="16"
                                />
                            </button>

                            <div
                                class="sv-profile-dropdown"
                                data-profile-dropdown
                            >
                                <div class="sv-profile-dropdown-head">
                                    <strong>
                                        {{ auth()->user()->name ?? 'Pengguna' }}
                                    </strong>

                                    <span>
                                        {{ auth()->user()->email ?? '' }}
                                    </span>
                                </div>

                                <a href="{{ route('settings') }}">
                                    <x-icon
                                        name="settings"
                                        :size="17"
                                    />

                                    <span>Pengaturan akun</span>
                                </a>

                                <form
                                    action="{{ route('logout') }}"
                                    method="POST"
                                >
                                    @csrf

                                    <button type="submit">
                                        <x-icon
                                            name="logout"
                                            :size="17"
                                        />

                                        <span>Keluar</span>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            <div class="sv-page-content">
                @if (session('status') || session('success'))
                    <div
                        class="sv-alert sv-alert-success"
                        role="status"
                    >
                        <x-icon name="check" :size="19" />

                        <div>
                            {{ session('status') ?? session('success') }}
                        </div>
                    </div>
                @endif

                @if ($errors->getBag('default')->any())
                    <div
                        class="sv-alert sv-alert-danger"
                        role="alert"
                    >
                        <x-icon name="warning" :size="19" />

                        <div>
                            <strong>
                                Periksa kembali data yang dimasukkan.
                            </strong>

                            <p>{{ $errors->getBag('default')->first() }}</p>
                        </div>
                    </div>
                @endif

                @yield('content')
            </div>

            <nav
                class="sv-bottom-navigation"
                aria-label="Navigasi mobile"
            >
                <a
                    href="{{ route('dashboard') }}"
                    class="sv-bottom-link sv-bottom-link--blue
                        {{ request()->routeIs('dashboard*') ? 'is-active' : '' }}"
                >
                    <x-feature-icon
                        name="home"
                        tone="blue"
                        :size="18"
                        variant="flat"
                    />

                    <span>Beranda</span>
                </a>

                <a
                    href="{{ route('energy.history') }}"
                    class="sv-bottom-link sv-bottom-link--green
                        {{ request()->routeIs('energy.history*') ? 'is-active' : '' }}"
                >
                    <x-feature-icon
                        name="chart"
                        tone="green"
                        :size="18"
                        variant="flat"
                    />

                    <span>Pemakaian</span>
                </a>

                <a
                    href="{{ route('rooms') }}"
                    class="sv-bottom-link sv-bottom-link--violet
                        {{
                            request()->routeIs('rooms*')
                            || request()->routeIs('devices*')
                                ? 'is-active'
                                : ''
                        }}"
                >
                    <x-feature-icon
                        name="rooms"
                        tone="violet"
                        :size="18"
                        variant="flat"
                    />

                    <span>Perangkat</span>
                </a>

                <a
                    href="{{ route('settings') }}"
                    class="sv-bottom-link sv-bottom-link--slate
                        {{ request()->routeIs('settings*') ? 'is-active' : '' }}"
                >
                    <x-feature-icon
                        name="settings"
                        tone="slate"
                        :size="18"
                        variant="flat"
                    />

                    <span>Pengaturan</span>
                </a>
            </nav>
        </main>
    </div>

    <div
        class="sv-toast-region"
        data-toast-region
        aria-live="polite"
        aria-atomic="true"
    ></div>

    <script
        src="{{ asset('assets/js/smartvolt-app.js') }}?v=20260803-2"
        defer
    ></script>

    @stack('scripts')
</body>
</html>