# Kode Lengkap SmartVolt UI V7 — Professional Minimal

Kode berikut disusun berdasarkan paket SmartVolt terbaru dan hanya mengganti lapisan tampilan.
Backend, route, database, API IoT, MQTT, dan firmware tidak diubah.

## `payload/resources/views/layouts/app.blade.php`

```blade
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
    <link rel="stylesheet" href="{{ asset('assets/css/smartvolt-app.css') }}?v=20260731-v7-base">
    <link rel="stylesheet" href="{{ asset('assets/css/smartvolt-pro.css') }}?v=20260731-v7">

    @stack('styles')
    @stack('head-scripts')
</head>
@php
    $isTechnicianPage = request()->routeIs('settings*') && request('tab') === 'technician';
@endphp
<body class="sv-app-body sv-pro-app @yield('body-class')">
    <a class="sv-skip-link" href="#main-content">Lewati ke konten</a>

    <div class="sv-app-layout">
        <button type="button" class="sv-sidebar-overlay" data-sidebar-close aria-label="Tutup menu"></button>

        <aside class="sv-app-sidebar" data-sidebar>
            <div class="sv-sidebar-header">
                <a href="{{ route('dashboard') }}" class="sv-brand" aria-label="Beranda SmartVolt">
                    <span class="sv-brand-mark" aria-hidden="true">
                        <x-icon name="bolt" :size="22" />
                    </span>
                    <span class="sv-brand-copy">
                        <strong>SmartVolt</strong>
                        <small>Monitoring Energi</small>
                    </span>
                </a>

                <button type="button" class="sv-icon-button sv-sidebar-close" data-sidebar-close aria-label="Tutup menu">
                    <x-icon name="close" :size="20" />
                </button>
            </div>

            <nav class="sv-sidebar-nav" aria-label="Navigasi utama">
                <p class="sv-nav-label">Menu utama</p>

                <a href="{{ route('dashboard') }}" class="sv-nav-link {{ request()->routeIs('dashboard*') ? 'is-active' : '' }}">
                    <span class="sv-pro-nav-icon sv-pro-nav-icon--blue"><x-icon name="home" :size="19" /></span>
                    <span>Beranda</span>
                </a>

                <a href="{{ route('energy.history') }}" class="sv-nav-link {{ request()->routeIs('energy.history*') ? 'is-active' : '' }}">
                    <span class="sv-pro-nav-icon sv-pro-nav-icon--green"><x-icon name="chart" :size="19" /></span>
                    <span>Pemakaian Listrik</span>
                </a>

                <a href="{{ route('rooms') }}" class="sv-nav-link {{ request()->routeIs('rooms*') || request()->routeIs('devices*') ? 'is-active' : '' }}">
                    <span class="sv-pro-nav-icon sv-pro-nav-icon--violet"><x-icon name="rooms" :size="19" /></span>
                    <span>Ruangan & Perangkat</span>
                </a>

                <a href="{{ route('settings') }}" class="sv-nav-link {{ request()->routeIs('settings*') && !$isTechnicianPage ? 'is-active' : '' }}">
                    <span class="sv-pro-nav-icon sv-pro-nav-icon--slate"><x-icon name="settings" :size="19" /></span>
                    <span>Pengaturan</span>
                </a>

                <p class="sv-nav-label sv-nav-label-spaced">Sistem</p>

                <a href="{{ route('settings', ['tab' => 'technician']) }}#technician" class="sv-nav-link sv-nav-link-secondary {{ $isTechnicianPage ? 'is-active' : '' }}">
                    <span class="sv-pro-nav-icon sv-pro-nav-icon--amber"><x-icon name="tools" :size="19" /></span>
                    <span>Mode Teknisi</span>
                </a>
            </nav>

            <div class="sv-sidebar-footer-simple">
                <span class="sv-sidebar-footer-dot" aria-hidden="true"></span>
                <span>SmartVolt siap digunakan</span>
            </div>
        </aside>

        <main class="sv-app-main" id="main-content" tabindex="-1">
            <header class="sv-app-topbar">
                <div class="sv-topbar-inner">
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
                        @hasSection('page-actions')
                            <div class="sv-page-actions">@yield('page-actions')</div>
                        @endif

                        @hasSection('system-status')
                            <div class="sv-topbar-status">@yield('system-status')</div>
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
                                    <span>Pengaturan akun</span>
                                </a>

                                <form action="{{ route('logout') }}" method="POST">
                                    @csrf
                                    <button type="submit">
                                        <x-icon name="logout" :size="17" />
                                        <span>Keluar</span>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            <div class="sv-page-content">
                @if(session('status') || session('success'))
                    <div class="sv-alert sv-alert-success" role="status">
                        <x-icon name="check" :size="19" />
                        <div>{{ session('status') ?? session('success') }}</div>
                    </div>
                @endif

                @if($errors->any())
                    <div class="sv-alert sv-alert-danger" role="alert">
                        <x-icon name="warning" :size="19" />
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

    <script src="{{ asset('assets/js/smartvolt-app.js') }}?v=20260731-v7" defer></script>
    @stack('scripts')
</body>
</html>
```

## `payload/resources/views/components/feature-icon.blade.php`

```blade
@props([
    'name',
    'tone' => 'blue',
    'size' => 20,
    'variant' => 'soft',
    'label' => null,
])

<span
    {{ $attributes->class([
        'sv-feature-icon',
        'sv-feature-icon--' . $tone,
        'sv-feature-icon--' . $variant,
    ]) }}
    @if($label) role="img" aria-label="{{ $label }}" @else aria-hidden="true" @endif
>
    <x-icon :name="$name" :size="$size" />
</span>
```

## `payload/resources/views/components/icon.blade.php`

```blade
@props([
    'name',
    'size' => 20,
])

<svg
    {{ $attributes->merge(['class' => 'sv-icon']) }}
    width="{{ $size }}"
    height="{{ $size }}"
    viewBox="0 0 24 24"
    fill="none"
    xmlns="http://www.w3.org/2000/svg"
    aria-hidden="true"
    focusable="false"
>
    @switch($name)
        @case('bolt')
            <path d="M13.2 2.5 5.8 13h5.1l-.7 8.5L18.4 10h-5.1l-.1-7.5Z" fill="currentColor"/>
            @break
        @case('home')
            <path d="m3 10 9-7 9 7v10a1 1 0 0 1-1 1h-5v-7H9v7H4a1 1 0 0 1-1-1V10Z"/>
            @break
        @case('chart')
            <path d="M4 20V10m5 10V4m5 16v-7m5 7V7"/>
            @break
        @case('rooms')
            <rect x="3" y="3" width="8" height="8" rx="2"/>
            <rect x="13" y="3" width="8" height="8" rx="2"/>
            <rect x="3" y="13" width="8" height="8" rx="2"/>
            <rect x="13" y="13" width="8" height="8" rx="2"/>
            @break
        @case('settings')
            <circle cx="12" cy="12" r="3"/>
            <path d="M19.4 15a1.7 1.7 0 0 0 .3 1.9l.1.1-2.8 2.8-.1-.1a1.7 1.7 0 0 0-1.9-.3 1.7 1.7 0 0 0-1 1.6v.2h-4V21a1.7 1.7 0 0 0-1-1.6 1.7 1.7 0 0 0-1.9.3l-.1.1L4.2 17l.1-.1A1.7 1.7 0 0 0 4.6 15 1.7 1.7 0 0 0 3 14H2.8v-4H3a1.7 1.7 0 0 0 1.6-1 1.7 1.7 0 0 0-.3-1.9L4.2 7 7 4.2l.1.1A1.7 1.7 0 0 0 9 4.6 1.7 1.7 0 0 0 10 3V2.8h4V3a1.7 1.7 0 0 0 1 1.6 1.7 1.7 0 0 0 1.9-.3l.1-.1L19.8 7l-.1.1a1.7 1.7 0 0 0-.3 1.9 1.7 1.7 0 0 0 1.6 1h.2v4H21a1.7 1.7 0 0 0-1.6 1Z"/>
            @break
        @case('tools')
            <path d="M14.7 6.3a4 4 0 0 0-5-5l2.2 2.2-2.4 2.4-2.2-2.2a4 4 0 0 0 5 5L20 16.4a2.5 2.5 0 1 1-3.6 3.6l-7.7-7.7"/>
            <path d="m5 14-3 3 5 5 3-3"/>
            @break
        @case('bell')
            <path d="M18 8a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9"/>
            <path d="M10 21h4"/>
            @break
        @case('user')
            <circle cx="12" cy="8" r="4"/>
            <path d="M4 21a8 8 0 0 1 16 0"/>
            @break
        @case('logout')
            <path d="M10 17l5-5-5-5"/>
            <path d="M15 12H3"/>
            <path d="M14 3h5a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-5"/>
            @break
        @case('menu')
            <path d="M4 7h16M4 12h16M4 17h16"/>
            @break
        @case('close')
            <path d="m6 6 12 12M18 6 6 18"/>
            @break
        @case('wifi')
            <path d="M5 12.5a10 10 0 0 1 14 0"/>
            <path d="M8.5 16a5 5 0 0 1 7 0"/>
            <circle cx="12" cy="20" r="1"/>
            @break
        @case('wifi-off')
            <path d="m3 3 18 18"/>
            <path d="M8.5 16a5 5 0 0 1 4.5-1.4M5 12.5a10 10 0 0 1 4.4-2.4M14.8 10.4A10 10 0 0 1 19 12.5"/>
            @break
        @case('sensor')
            <rect x="4" y="4" width="16" height="16" rx="4"/>
            <path d="M8 8h8v8H8zM12 1v3M12 20v3M1 12h3M20 12h3"/>
            @break
        @case('mqtt')
            <path d="M4 19a15 15 0 0 1 15-15"/>
            <path d="M4 13a9 9 0 0 1 9-9"/>
            <path d="M4 7a3 3 0 0 1 3-3"/>
            <circle cx="5" cy="19" r="1.5" fill="currentColor" stroke="none"/>
            @break
        @case('clock')
            <circle cx="12" cy="12" r="9"/>
            <path d="M12 7v5l3 2"/>
            @break
        @case('plug')
            <path d="M8 2v6M16 2v6M6 8h12v2a6 6 0 0 1-12 0V8ZM12 16v6"/>
            @break
        @case('energy')
            <path d="M3 12h4l2-6 4 12 2-6h6"/>
            @break
        @case('money')
            <rect x="3" y="5" width="18" height="14" rx="3"/>
            <path d="M7 9h.01M17 15h.01M12 9v6M10 11h3a1 1 0 0 1 0 2h-3"/>
            @break
        @case('devices')
            <rect x="3" y="4" width="8" height="16" rx="2"/>
            <rect x="13" y="4" width="8" height="16" rx="2"/>
            <path d="M7 8h.01M17 8h.01M7 16h.01M17 16h.01"/>
            @break
        @case('chevron-down')
            <path d="m6 9 6 6 6-6"/>
            @break
        @case('chevron-right')
            <path d="m9 6 6 6-6 6"/>
            @break
        @case('plus')
            <path d="M12 5v14M5 12h14"/>
            @break
        @case('edit')
            <path d="M12 20h9"/>
            <path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L8 18l-4 1 1-4 11.5-11.5Z"/>
            @break
        @case('trash')
            <path d="M4 7h16M9 7V4h6v3M6 7l1 14h10l1-14M10 11v6M14 11v6"/>
            @break
        @case('check')
            <path d="m5 12 4 4L19 6"/>
            @break
        @case('warning')
            <path d="M12 3 2.5 20h19L12 3Z"/>
            <path d="M12 9v5M12 17h.01"/>
            @break
        @case('info')
            <circle cx="12" cy="12" r="9"/>
            <path d="M12 11v5M12 8h.01"/>
            @break
        @case('eye')
            <path d="M2.5 12s3.5-6 9.5-6 9.5 6 9.5 6-3.5 6-9.5 6S2.5 12 2.5 12Z"/>
            <circle cx="12" cy="12" r="2.5"/>
            @break
        @case('eye-off')
            <path d="m4 4 16 16"/>
            <path d="M10.6 6.2A10 10 0 0 1 12 6c6 0 9.5 6 9.5 6a16 16 0 0 1-2.4 3.1M6.2 7.3A15.7 15.7 0 0 0 2.5 12S6 18 12 18a9.8 9.8 0 0 0 2.1-.2"/>
            @break
        @case('lock')
            <rect x="5" y="10" width="14" height="10" rx="3"/>
            <path d="M8 10V7a4 4 0 0 1 8 0v3"/>
            @break
        @case('mail')
            <rect x="3" y="5" width="18" height="14" rx="3"/>
            <path d="m5 8 7 5 7-5"/>
            @break
        @case('calendar')
            <rect x="3" y="5" width="18" height="16" rx="3"/>
            <path d="M8 3v4M16 3v4M3 10h18"/>
            @break
        @case('filter')
            <path d="M4 5h16M7 12h10M10 19h4"/>
            @break
        @case('download')
            <path d="M12 3v12m0 0 5-5m-5 5-5-5"/>
            <path d="M5 21h14"/>
            @break
        @case('search')
            <circle cx="11" cy="11" r="7"/>
            <path d="m20 20-4-4"/>
            @break
        @case('shield')
            <path d="M12 3 5 6v5c0 4.6 2.7 8 7 10 4.3-2 7-5.4 7-10V6l-7-3Z"/>
            <path d="m9 12 2 2 4-5"/>
            @break
        @case('database')
            <ellipse cx="12" cy="5" rx="8" ry="3"/>
            <path d="M4 5v6c0 1.7 3.6 3 8 3s8-1.3 8-3V5M4 11v6c0 1.7 3.6 3 8 3s8-1.3 8-3v-6"/>
            @break
        @case('server')
            <rect x="3" y="4" width="18" height="6" rx="2"/>
            <rect x="3" y="14" width="18" height="6" rx="2"/>
            <path d="M7 7h.01M7 17h.01M11 7h6M11 17h6"/>
            @break
        @case('power')
            <path d="M12 2v10"/>
            <path d="M6.3 5.7a8 8 0 1 0 11.4 0"/>
            @break
        @case('lightbulb')
            <path d="M9 18h6M10 22h4"/>
            <path d="M8.5 15.5A7 7 0 1 1 15.5 15.5c-.9.7-1.5 1.5-1.5 2.5h-4c0-1-.6-1.8-1.5-2.5Z"/>
            @break
        @case('fan')
            <circle cx="12" cy="12" r="2"/>
            <path d="M12 10c-1-6 3-8 5-5 2 3-1 6-5 7M14 12c6-1 8 3 5 5-3 2-6-1-7-5M12 14c1 6-3 8-5 5-2-3 1-6 5-7M10 12c-6 1-8-3-5-5 3-2 6 1 7 5"/>
            @break
        @case('refresh')
            <path d="M20 6v5h-5M4 18v-5h5"/>
            <path d="M18.5 9A7 7 0 0 0 6 6.5L4 9M5.5 15A7 7 0 0 0 18 17.5l2-2.5"/>
            @break
        @case('document')
            <path d="M6 3h8l4 4v14H6V3Z"/>
            <path d="M14 3v5h5M9 13h6M9 17h6"/>
            @break
        @case('activity')
            <path d="M3 12h4l2-6 4 12 2-6h6"/>
            @break
        @case('gauge')
            <path d="M4 18a8 8 0 1 1 16 0"/>
            <path d="m12 14 4-4"/>
            <path d="M7 18h10"/>
            @break
        @case('kitchen')
            <path d="M5 3v18M19 3v18"/>
            <path d="M8 3v7a4 4 0 0 0 8 0V3"/>
            <path d="M9 14h6M8 21h8"/>
            @break
        @case('bed')
            <path d="M3 18V8M21 18v-6a3 3 0 0 0-3-3H8a5 5 0 0 0-5 5v4"/>
            <path d="M3 15h18M7 9V6h5a3 3 0 0 1 3 3"/>
            <path d="M5 18v3M19 18v3"/>
            @break
        @case('sofa')
            <path d="M5 12V8a3 3 0 0 1 3-3h8a3 3 0 0 1 3 3v4"/>
            <path d="M5 10a3 3 0 0 0-3 3v5h20v-5a3 3 0 0 0-3-3"/>
            <path d="M6 18v3M18 18v3"/>
            @break
        @case('garage')
            <path d="M3 10 12 3l9 7v11H3V10Z"/>
            <path d="M6 13h12v8H6v-8ZM8 16h8M8 19h8"/>
            @break
        @case('key')
            <circle cx="8" cy="15" r="4"/>
            <path d="m11 12 9-9M15 8l2 2M17 6l2 2"/>
            @break
        @default
            <circle cx="12" cy="12" r="9"/>
    @endswitch
</svg>
```

## `payload/resources/views/components/notification-bell.blade.php`

```blade
@php
    $notifications = $smartvoltNotifications ?? collect();
    $unreadCount = (int) ($smartvoltUnreadNotificationsCount ?? 0);
@endphp

<div class="sv-notification-menu" data-notification-menu>
    <button
        type="button"
        class="sv-icon-button sv-notification-trigger"
        aria-label="Buka notifikasi"
        aria-expanded="false"
        data-notification-trigger
    >
        <x-feature-icon name="bell" tone="blue" :size="18" variant="flat" />

        @if($unreadCount > 0)
            <span class="sv-notification-count">{{ $unreadCount > 9 ? '9+' : $unreadCount }}</span>
        @endif
    </button>

    <div class="sv-notification-dropdown" data-notification-dropdown>
        <div class="sv-notification-header">
            <div>
                <strong>Notifikasi</strong>
                <span>{{ $unreadCount }} belum dibaca</span>
            </div>

            @if($unreadCount > 0)
                <form action="{{ route('notifications.read-all') }}" method="POST">
                    @csrf
                    @method('PATCH')
                    <button type="submit" class="sv-text-button">Tandai semua</button>
                </form>
            @endif
        </div>

        <div class="sv-notification-list">
            @forelse($notifications as $notification)
                <form action="{{ route('notifications.read', $notification) }}" method="POST">
                    @csrf
                    @method('PATCH')

                    <button
                        type="submit"
                        class="sv-notification-item {{ $notification->read_at ? '' : 'is-unread' }} is-{{ $notification->severity }}"
                    >
                        <x-feature-icon
                            :name="$notification->severity === 'danger' ? 'warning' : 'info'"
                            :tone="$notification->severity === 'danger' ? 'rose' : ($notification->severity === 'warning' ? 'amber' : 'blue')"
                            :size="16"
                            variant="soft"
                            class="sv-notification-icon"
                        />

                        <span class="sv-notification-copy">
                            <strong>{{ $notification->title }}</strong>
                            <span>{{ $notification->message }}</span>
                            <small>{{ optional($notification->created_at)->diffForHumans() }}</small>
                        </span>
                    </button>
                </form>
            @empty
                <div class="sv-notification-empty">
                    <x-feature-icon name="bell" tone="blue" :size="21" variant="soft" />
                    <strong>Belum ada notifikasi</strong>
                    <span>Pemberitahuan sistem akan tampil di sini.</span>
                </div>
            @endforelse
        </div>
    </div>
</div>
```

## `payload/resources/views/dashboard.blade.php`

```blade
@extends('layouts.app')

@section('title', 'Beranda')
@section('page-title', 'Beranda')
@section('page-subtitle', 'Ringkasan listrik rumah saat ini.')
@section('body-class', 'sv-dashboard-page sv-pro-dashboard')

@php
    $stats = $dashboardData['stats'] ?? [];
    $system = $dashboardData['system'] ?? [];
    $chart = $dashboardData['chart'] ?? ['labels' => [], 'power' => [], 'energy' => []];
    $recentReadings = collect($dashboardData['recent_readings'] ?? [])->take(5);
    $dashboardRooms = collect($dashboardData['rooms'] ?? []);

    $loadStatus = $stats['load_status'] ?? 'normal';
    $loadPercentage = min(100, max(0, (float) ($stats['load_percentage'] ?? 0)));
    $energyComparison = $stats['energy_comparison_percent'] ?? null;

    $statusBadgeClass = ($system['has_fresh_data'] ?? false)
        ? 'sv-badge-success'
        : (($system['has_data'] ?? false) ? 'sv-badge-warning' : 'sv-badge-neutral');

    $systemStatusLabel = ($system['has_fresh_data'] ?? false)
        ? 'Sistem terhubung'
        : (($system['has_data'] ?? false) ? 'Data terlambat' : 'Belum terhubung');
@endphp

@section('system-status')
    <span class="sv-badge {{ $statusBadgeClass }}" data-dashboard-system-badge>
        <span class="sv-status-dot" aria-hidden="true"></span>
        <span data-dashboard-system-label>{{ $systemStatusLabel }}</span>
    </span>
@endsection

@section('content')
    <section class="sv-pro-metrics" aria-label="Ringkasan penggunaan listrik">
        <article class="sv-pro-metric sv-pro-metric--primary">
            <div class="sv-pro-metric-head">
                <span class="sv-pro-icon-tile sv-pro-icon-tile--blue"><x-icon name="bolt" :size="22" /></span>
                <span class="sv-pro-metric-label">Daya saat ini</span>
            </div>
            <div class="sv-pro-metric-value">
                <strong data-stat-current-power>{{ number_format((float) ($stats['current_power'] ?? 0), 1, ',', '.') }}</strong>
                <small>W</small>
            </div>
            <div class="sv-pro-metric-meta">
                <span class="sv-pro-state {{ $loadStatus === 'danger' ? 'is-danger' : ($loadStatus === 'warning' ? 'is-warning' : 'is-success') }}" data-load-status-badge>
                    <span data-load-status-label>{{ $loadStatus === 'danger' ? 'Beban tinggi' : ($loadStatus === 'warning' ? 'Mendekati batas' : 'Normal') }}</span>
                </span>
                <span><strong data-stat-load-percentage>{{ number_format($loadPercentage, 0, ',', '.') }}</strong>% batas</span>
            </div>
            <div class="sv-progress {{ $loadStatus === 'danger' ? 'is-danger' : ($loadStatus === 'warning' ? 'is-warning' : '') }}" data-load-progress style="--sv-progress: {{ $loadPercentage }}%"><span></span></div>
        </article>

        <article class="sv-pro-metric">
            <div class="sv-pro-metric-head">
                <span class="sv-pro-icon-tile sv-pro-icon-tile--green"><x-icon name="energy" :size="21" /></span>
                <span class="sv-pro-metric-label">Energi hari ini</span>
            </div>
            <div class="sv-pro-metric-value">
                <strong data-stat-energy-today>{{ number_format((float) ($stats['total_energy_today'] ?? 0), 3, ',', '.') }}</strong>
                <small>kWh</small>
            </div>
            <p class="sv-pro-metric-note" data-energy-comparison>
                @if(is_numeric($energyComparison))
                    {{ $energyComparison > 0 ? 'Naik' : ($energyComparison < 0 ? 'Lebih hemat' : 'Sama') }}
                    {{ number_format(abs((float) $energyComparison), 1, ',', '.') }}% dari kemarin
                @else
                    Belum ada perbandingan
                @endif
            </p>
        </article>

        <article class="sv-pro-metric">
            <div class="sv-pro-metric-head">
                <span class="sv-pro-icon-tile sv-pro-icon-tile--amber"><x-icon name="money" :size="21" /></span>
                <span class="sv-pro-metric-label">Estimasi bulan ini</span>
            </div>
            <div class="sv-pro-metric-value sv-pro-metric-value--money">
                <small>Rp</small>
                <strong data-stat-monthly-cost>{{ number_format((float) ($stats['monthly_estimated_cost'] ?? 0), 0, ',', '.') }}</strong>
            </div>
            <p class="sv-pro-metric-note"><span data-stat-monthly-energy>{{ number_format((float) ($stats['monthly_energy_usage'] ?? 0), 3, ',', '.') }}</span> kWh tercatat</p>
        </article>

        <article class="sv-pro-metric">
            <div class="sv-pro-metric-head">
                <span class="sv-pro-icon-tile sv-pro-icon-tile--cyan"><x-icon name="plug" :size="21" /></span>
                <span class="sv-pro-metric-label">Perangkat aktif</span>
            </div>
            <div class="sv-pro-metric-value">
                <strong data-stat-active-devices>{{ (int) ($stats['active_devices'] ?? 0) }}</strong>
                <small>dari <span data-stat-total-devices>{{ (int) ($stats['total_devices'] ?? 0) }}</span></small>
            </div>
            <p class="sv-pro-metric-note"><span data-stat-online-active-devices>{{ (int) ($stats['online_active_devices'] ?? 0) }}</span> siap dikontrol</p>
        </article>
    </section>

    <section class="sv-pro-system-strip" aria-label="Status sistem SmartVolt">
        <div class="sv-pro-system-item {{ ($system['esp_online'] ?? false) ? 'is-success' : 'is-warning' }}" data-system-esp>
            <span class="sv-pro-system-icon"><x-icon name="server" :size="18" /></span>
            <div><strong data-system-esp-label>{{ ($system['esp_online'] ?? false) ? (($system['online_esp_count'] ?? 0) . ' ESP32 terhubung') : 'ESP32 belum terhubung' }}</strong><span>Perangkat utama</span></div>
        </div>
        <div class="sv-pro-system-item {{ ($system['has_fresh_data'] ?? false) ? 'is-success' : 'is-warning' }}" data-system-pzem>
            <span class="sv-pro-system-icon"><x-icon name="sensor" :size="18" /></span>
            <div><strong>Sensor <span data-system-pzem-label>{{ $system['pzem_status'] ?? 'Belum tersedia' }}</span></strong><span>Pembacaan listrik</span></div>
        </div>
        <div class="sv-pro-system-item {{ ($system['esp_online'] ?? false) ? 'is-success' : 'is-warning' }}" data-system-control>
            <span class="sv-pro-system-icon"><x-icon name="mqtt" :size="18" /></span>
            <div><strong>Kontrol <span data-system-control-label>{{ $system['control_channel_status'] ?? 'Menunggu perangkat' }}</span></strong><span>Saluran MQTT</span></div>
        </div>
        <div class="sv-pro-system-item {{ ($system['has_data'] ?? false) ? 'is-success' : 'is-warning' }}" data-system-latest>
            <span class="sv-pro-system-icon"><x-icon name="clock" :size="18" /></span>
            <div><strong data-system-latest-label>{{ $system['latest_received_human'] ?? 'Belum ada data' }}</strong><span>Data terakhir</span></div>
        </div>
    </section>

    <section class="sv-pro-dashboard-grid">
        <article class="sv-card sv-pro-chart-card">
            <header class="sv-card-header">
                <div>
                    <h2>Daya 24 Jam Terakhir</h2>
                    <p>Perubahan daya terbaru dari meter utama.</p>
                </div>
                <a href="{{ route('energy.history') }}" class="sv-text-button">Lihat analisis</a>
            </header>
            <div class="sv-card-body">
                <div class="sv-chart-summary sv-chart-summary-compact">
                    <div><span>Saat ini</span><strong><span data-chart-current>{{ number_format((float) ($stats['current_power'] ?? 0), 1, ',', '.') }}</span> <small data-chart-unit>W</small></strong></div>
                    <div><span>Rata-rata</span><strong><span data-chart-average>0</span> <small data-chart-average-unit>W</small></strong></div>
                    <div><span>Tertinggi</span><strong><span data-chart-maximum>0</span> <small data-chart-maximum-unit>W</small></strong></div>
                </div>

                <div class="sv-empty-state {{ collect($chart['labels'] ?? [])->isNotEmpty() ? 'is-hidden' : '' }}" data-dashboard-chart-empty>
                    <span class="sv-pro-icon-tile sv-pro-icon-tile--blue"><x-icon name="chart" :size="22" /></span>
                    <h3>Belum ada data terbaru</h3>
                    <p>Grafik akan muncul setelah telemetry diterima.</p>
                </div>

                <div class="sv-chart-container {{ collect($chart['labels'] ?? [])->isEmpty() ? 'is-hidden' : '' }}" data-dashboard-chart-container>
                    <canvas id="dashboardEnergyChart" aria-label="Grafik daya SmartVolt"></canvas>
                </div>
            </div>
        </article>

        <article class="sv-card sv-pro-control-card">
            <header class="sv-card-header">
                <div>
                    <h2>Kontrol Cepat</h2>
                    <p>Perangkat yang paling sering digunakan.</p>
                </div>
                <a href="{{ route('rooms') }}" class="sv-text-button">Semua ruangan</a>
            </header>

            <div class="sv-card-body">
                @if($dashboardRooms->isEmpty())
                    <div class="sv-empty-state sv-empty-state-compact">
                        <span class="sv-pro-icon-tile sv-pro-icon-tile--violet"><x-icon name="rooms" :size="22" /></span>
                        <h3>Belum ada ruangan</h3>
                        <a href="{{ route('settings', ['tab' => 'technician']) }}#technician" class="sv-button sv-button-secondary sv-button-sm">Tambah ruangan</a>
                    </div>
                @else
                    <div class="sv-room-control-list">
                        @foreach($dashboardRooms->take(3) as $room)
                            <details class="sv-room-control" data-room-id="{{ $room['id'] }}" {{ $loop->first ? 'open' : '' }}>
                                <summary>
                                    <span class="sv-room-summary-main">
                                        <span class="sv-pro-icon-tile sv-pro-icon-tile--violet sv-pro-icon-tile--sm"><x-icon name="rooms" :size="17" /></span>
                                        <span class="sv-room-summary-copy">
                                            <strong>{{ $room['name'] }}</strong>
                                            <span><span data-room-active-count="{{ $room['id'] }}">{{ $room['active_devices'] }}</span> dari {{ $room['total_devices'] }} aktif</span>
                                        </span>
                                    </span>
                                    <span class="sv-room-power">{{ number_format((float) ($room['current_power'] ?? 0), 1, ',', '.') }} W</span>
                                    <span class="sv-room-chevron"><x-icon name="chevron-right" :size="16" /></span>
                                </summary>

                                <div class="sv-room-devices">
                                    @forelse(collect($room['devices'] ?? [])->take(4) as $device)
                                        @php
                                            $deviceOn = (bool) ($device['is_on'] ?? false);
                                            $espOnline = (bool) ($device['esp_online'] ?? false);
                                        @endphp
                                        <div class="sv-device-row" data-device-row="{{ $device['id'] }}">
                                            <div class="sv-device-main">
                                                <span class="sv-pro-device-icon {{ $deviceOn ? 'is-on' : '' }}"><x-icon name="lightbulb" :size="17" /></span>
                                                <div class="sv-device-copy">
                                                    <strong>{{ $device['name'] }}</strong>
                                                    <span>{{ $espOnline ? 'Siap dikontrol' : 'Perangkat offline' }}</span>
                                                </div>
                                            </div>

                                            <button
                                                type="button"
                                                class="sv-device-switch {{ $deviceOn ? 'is-on' : '' }} {{ ! $espOnline ? 'is-offline' : '' }}"
                                                data-device-toggle
                                                data-device-id="{{ $device['id'] }}"
                                                data-room-id="{{ $room['id'] }}"
                                                data-url="{{ route('devices.toggle', $device['id']) }}"
                                                data-current-state="{{ $deviceOn ? 'on' : 'off' }}"
                                                aria-pressed="{{ $deviceOn ? 'true' : 'false' }}"
                                                {{ ! $espOnline ? 'disabled' : '' }}
                                            >
                                                <span class="sv-switch-track"><span></span></span>
                                                <span data-switch-label>{{ $espOnline ? ($deviceOn ? 'Nyala' : 'Mati') : 'Offline' }}</span>
                                            </button>
                                        </div>
                                    @empty
                                        <div class="sv-empty-state sv-empty-state-compact"><p>Belum ada perangkat.</p></div>
                                    @endforelse
                                </div>
                            </details>
                        @endforeach
                    </div>
                @endif
            </div>
        </article>
    </section>

    <section class="sv-card sv-pro-activity-card">
        <header class="sv-card-header">
            <div>
                <h2>Aktivitas Terbaru</h2>
                <p>Lima pembacaan terakhir.</p>
            </div>
            <a href="{{ route('energy.history') }}" class="sv-text-button">Lihat riwayat</a>
        </header>

        <div class="sv-card-body sv-card-body-table">
            @if($recentReadings->isEmpty())
                <div class="sv-empty-state sv-empty-state-compact">
                    <span class="sv-pro-icon-tile sv-pro-icon-tile--blue"><x-icon name="document" :size="22" /></span>
                    <h3>Belum ada pembacaan</h3>
                </div>
            @else
                <div class="sv-table-wrap">
                    <table class="sv-table sv-table-compact">
                        <thead>
                            <tr>
                                <th>Waktu</th>
                                <th>Ruangan</th>
                                <th class="is-numeric">Daya</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($recentReadings as $reading)
                                <tr>
                                    <td>{{ $reading['observed_at'] ?? '-' }}</td>
                                    <td>{{ $reading['room_name'] ?? '-' }}</td>
                                    <td class="is-numeric">{{ number_format((float) ($reading['power'] ?? 0), 1, ',', '.') }} W</td>
                                    <td><span class="sv-badge sv-badge-success">Tercatat</span></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </section>

    <script type="application/json" id="smartvolt-dashboard-data">{!! json_encode([
        'endpoint' => route('dashboard.data'),
        'refreshInterval' => (int) ($dashboardData['settings']['refresh_interval'] ?? 30),
        'chart' => $chart,
        'stats' => $stats,
        'system' => $system,
    ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) !!}</script>
@endsection

@push('scripts')
    <script src="{{ asset('assets/js/chart.umd.min.js') }}?v=4.5.1" defer></script>
    <script src="{{ asset('assets/js/smartvolt-dashboard.js') }}?v=20260731-v7" defer></script>
@endpush
```

## `payload/resources/views/auth/energy-history.blade.php`

```blade
@extends('layouts.app')

@section('title', 'Pemakaian Listrik')
@section('page-title', 'Pemakaian Listrik')
@section('page-subtitle', 'Analisis pemakaian berdasarkan meter dan periode.')
@section('body-class', 'sv-energy-history-page sv-energy-streamlined')

@php
    $meterCollection = collect($devices ?? []);
    $paymentCollection = collect($paymentEstimations ?? []);
    $chartData = $chart ?? ['labels' => [], 'power' => [], 'energy' => []];
    $selectedMeterId = $filters['meter_id'] ?? null;
    $fromDate = $filters['date_from'] ?? now()->toDateString();
    $toDate = $filters['date_to'] ?? now()->toDateString();
    $selectedEstimation = $paymentCollection->get('selected') ?? $paymentCollection->get('today') ?? [];
    $monthEstimation = $paymentCollection->get('month') ?? [];
@endphp

@section('system-status')
    <span class="sv-badge {{ ($summary['total_logs'] ?? 0) > 0 ? 'sv-badge-success' : 'sv-badge-neutral' }}">
        <span class="sv-status-dot" aria-hidden="true"></span>
        {{ ($summary['total_logs'] ?? 0) > 0 ? number_format((int) $summary['total_logs'], 0, ',', '.') . ' data' : 'Belum ada data' }}
    </span>
@endsection

@section('content')
    <section class="sv-card sv-filter-card">
        <div class="sv-card-body">
            <form action="{{ route('energy.history') }}" method="GET" class="sv-filter-grid sv-filter-grid-compact">
                <div class="sv-form-field">
                    <label for="device_id" class="sv-form-label">Meter listrik</label>
                    <select id="device_id" name="device_id" class="sv-form-control">
                        <option value="">Semua meter</option>
                        @foreach($meterCollection as $meter)
                            <option value="{{ $meter->id }}" {{ (string) $selectedMeterId === (string) $meter->id ? 'selected' : '' }}>
                                {{ $meter->room_name ?? $meter->room?->name ?? '-' }} · {{ $meter->meter_name ?? $meter->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="sv-form-field">
                    <label for="date_from" class="sv-form-label">Mulai</label>
                    <input type="date" id="date_from" name="date_from" class="sv-form-control" value="{{ $fromDate }}" required>
                </div>
                <div class="sv-form-field">
                    <label for="date_to" class="sv-form-label">Selesai</label>
                    <input type="date" id="date_to" name="date_to" class="sv-form-control" value="{{ $toDate }}" required>
                </div>
                <div class="sv-filter-actions">
                    <button type="submit" class="sv-button sv-button-primary">Terapkan</button>
                    <a href="{{ route('energy.history') }}" class="sv-button sv-button-ghost">Reset</a>
                </div>
            </form>

            <div class="sv-filter-footer">
                <div class="sv-quick-filters" aria-label="Pilihan periode cepat">
                    <button type="button" class="sv-quick-filter" data-date-range="today">Hari ini</button>
                    <button type="button" class="sv-quick-filter" data-date-range="7days">7 hari</button>
                    <button type="button" class="sv-quick-filter" data-date-range="month">Bulan ini</button>
                    <button type="button" class="sv-quick-filter" data-date-range="last-month">Bulan lalu</button>
                </div>
                <button type="button" class="sv-button sv-button-secondary sv-button-sm" data-export-url="{{ route('energy.history.export', request()->query()) }}" data-energy-export>
                    <x-icon name="download" :size="16" />
                    <span data-export-label>Unduh CSV</span>
                </button>
            </div>
        </div>
    </section>

    <section class="sv-history-metrics sv-history-metrics-three" aria-label="Ringkasan pemakaian listrik">
        <article class="sv-history-metric sv-history-metric--green">
            <x-feature-icon name="energy" tone="green" :size="20" variant="soft" />
            <div><span>Total Pemakaian</span><strong>{{ number_format((float) ($summary['usage_kwh'] ?? 0), 4, ',', '.') }} kWh</strong></div>
        </article>
        <article class="sv-history-metric sv-history-metric--amber">
            <x-feature-icon name="money" tone="amber" :size="20" variant="soft" />
            <div><span>Biaya Periode</span><strong>Rp{{ number_format((float) ($selectedEstimation['estimated_cost'] ?? (($summary['usage_kwh'] ?? 0) * ($electricityTariff ?? 0))), 0, ',', '.') }}</strong></div>
        </article>
        <article class="sv-history-metric sv-history-metric--blue">
            <x-feature-icon name="bolt" tone="blue" :size="20" variant="soft" />
            <div><span>Daya Tertinggi</span><strong>{{ number_format((float) ($summary['max_power'] ?? 0), 1, ',', '.') }} W</strong></div>
        </article>
    </section>

    <section class="sv-section-grid sv-section-grid-analysis">
        <article class="sv-card sv-history-chart-card">
            <header class="sv-card-header">
                <div>
                    <h2>Grafik Pemakaian</h2>
                    <p>{{ \Carbon\Carbon::parse($fromDate)->format('d/m/Y') }} – {{ \Carbon\Carbon::parse($toDate)->format('d/m/Y') }}</p>
                </div>
                <div class="sv-chart-tabs" role="tablist" aria-label="Jenis grafik">
                    <button type="button" class="sv-chart-tab is-active" data-history-chart-mode="power" aria-selected="true">Daya</button>
                    <button type="button" class="sv-chart-tab" data-history-chart-mode="energy" aria-selected="false">Energi</button>
                </div>
            </header>
            <div class="sv-card-body">
                <div class="sv-chart-meta-line">
                    <span>Rata-rata daya <strong>{{ number_format((float) ($summary['avg_power'] ?? 0), 1, ',', '.') }} W</strong></span>
                    <span>Rata-rata tegangan <strong>{{ number_format((float) ($summary['avg_voltage'] ?? 0), 1, ',', '.') }} V</strong></span>
                </div>

                <div class="sv-empty-state {{ collect($chartData['labels'] ?? [])->isNotEmpty() ? 'is-hidden' : '' }}" data-history-chart-empty>
                    <x-feature-icon name="chart" tone="green" :size="24" variant="soft" />
                    <h3>Tidak ada data pada periode ini</h3>
                    <p>Pilih periode lain atau periksa perangkat.</p>
                </div>
                <div class="sv-chart-container {{ collect($chartData['labels'] ?? [])->isEmpty() ? 'is-hidden' : '' }}" data-history-chart-container>
                    <canvas id="energyHistoryChart" aria-label="Grafik riwayat pemakaian listrik"></canvas>
                </div>
            </div>
        </article>

        <article class="sv-card sv-estimation-card">
            <header class="sv-card-header">
                <div>
                    <h2>Ringkasan Biaya</h2>
                    <p>Tarif Rp{{ number_format((float) ($electricityTariff ?? 0), 0, ',', '.') }}/kWh.</p>
                </div>
            </header>
            <div class="sv-card-body">
                <div class="sv-estimation-highlight">
                    <span>{{ $selectedEstimation['label'] ?? 'Periode Terpilih' }}</span>
                    <strong>Rp{{ number_format((float) ($selectedEstimation['estimated_cost'] ?? 0), 0, ',', '.') }}</strong>
                    <small>{{ number_format((float) ($selectedEstimation['usage_kwh'] ?? 0), 4, ',', '.') }} kWh</small>
                </div>

                @if(!empty($monthEstimation))
                    <dl class="sv-estimation-secondary">
                        <div><dt>{{ $monthEstimation['label'] ?? 'Bulan Ini' }}</dt><dd>Rp{{ number_format((float) ($monthEstimation['estimated_cost'] ?? 0), 0, ',', '.') }}</dd></div>
                        <div><dt>Periode</dt><dd>{{ $monthEstimation['period'] ?? '-' }}</dd></div>
                    </dl>
                @endif
            </div>
        </article>
    </section>

    <section class="sv-card">
        <header class="sv-card-header">
            <div>
                <h2>Riwayat Meter</h2>
                <p>Data terbaru setiap meter pada periode terpilih.</p>
            </div>
            <span class="sv-badge sv-badge-neutral">{{ $logs->total() }} meter</span>
        </header>
        <div class="sv-card-body">
            @if($logs->isEmpty())
                <div class="sv-empty-state sv-empty-state-compact">
                    <x-feature-icon name="document" tone="blue" :size="24" variant="soft" />
                    <h3>Data tidak ditemukan</h3>
                </div>
            @else
                <div class="sv-table-wrap">
                    <table class="sv-table">
                        <thead>
                            <tr>
                                <th>Ruangan</th>
                                <th>Meter</th>
                                <th>Waktu</th>
                                <th class="is-numeric">Tegangan</th>
                                <th class="is-numeric">Arus</th>
                                <th class="is-numeric">Daya</th>
                                <th class="is-numeric">Energi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($logs as $log)
                                <tr>
                                    <td>{{ $log->room_name ?? '-' }}</td>
                                    <td>{{ $log->meter_name ?? $log->device_name ?? '-' }}</td>
                                    <td>{{ $log->observed_at?->format('d/m/Y H:i:s') ?? '-' }}</td>
                                    <td class="is-numeric">{{ number_format((float) ($log->voltage ?? 0), 1, ',', '.') }} V</td>
                                    <td class="is-numeric">{{ number_format((float) ($log->current ?? 0), 3, ',', '.') }} A</td>
                                    <td class="is-numeric">{{ number_format((float) ($log->power ?? 0), 1, ',', '.') }} W</td>
                                    <td class="is-numeric">{{ number_format((float) ($log->energy ?? 0), 4, ',', '.') }} kWh</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="sv-pagination">{{ $logs->links() }}</div>
            @endif
        </div>
    </section>

    <script type="application/json" id="smartvolt-history-data">{!! json_encode([
        'chart' => $chartData,
        'exportUrl' => route('energy.history.export', request()->query()),
    ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) !!}</script>
@endsection

@push('scripts')
    <script src="{{ asset('assets/js/chart.umd.min.js') }}?v=4.5.1" defer></script>
    <script src="{{ asset('assets/js/smartvolt-energy-history.js') }}?v=20260731-v7" defer></script>
@endpush
```

## `payload/resources/views/rooms.blade.php`

```blade
@extends('layouts.app')

@section('title', 'Ruangan & Perangkat')
@section('page-title', 'Ruangan & Perangkat')
@section('page-subtitle', 'Kontrol perangkat berdasarkan ruangan.')
@section('body-class', 'sv-rooms-page sv-rooms-streamlined')

@php
    $roomCollection = collect($rooms ?? []);
    $onlineIds = collect($onlineEspUnitIds ?? [])->map(fn ($value) => (string) $value);
    $allDevices = $roomCollection->pluck('devices')->flatten();
    $isOn = function ($status): bool {
        if (is_bool($status)) return $status;
        if (is_numeric($status)) return (int) $status === 1;
        return in_array(strtolower((string) $status), ['on', 'nyala', 'active', 'aktif', 'true', '1'], true);
    };
    $activeDevices = $allDevices->filter(fn ($device) => $isOn($device->status ?? null))->count();
    $onlineDevices = $allDevices->filter(function ($device) use ($onlineIds) {
        $espId = trim((string) ($device->esp_unit_id ?: $device->esp32_device_id));
        return $espId !== '' && $onlineIds->contains($espId);
    })->count();
    $roomTones = ['violet', 'blue', 'green', 'amber', 'cyan', 'rose'];
@endphp

@section('system-status')
    <span class="sv-badge {{ $onlineIds->isNotEmpty() ? 'sv-badge-success' : 'sv-badge-warning' }}">
        <span class="sv-status-dot" aria-hidden="true"></span>
        {{ $onlineIds->isNotEmpty() ? $onlineIds->count() . ' ESP32 online' : 'Perangkat offline' }}
    </span>
@endsection

@section('content')
    <section class="sv-room-summary-grid sv-room-summary-grid-three" aria-label="Ringkasan ruangan dan perangkat">
        <article class="sv-summary-card sv-summary-card--violet">
            <x-feature-icon name="rooms" tone="violet" :size="19" variant="soft" class="sv-summary-icon" />
            <div class="sv-summary-copy"><span>Ruangan</span><strong>{{ $roomCollection->count() }}</strong></div>
        </article>
        <article class="sv-summary-card sv-summary-card--green">
            <x-feature-icon name="power" tone="green" :size="19" variant="soft" class="sv-summary-icon" />
            <div class="sv-summary-copy"><span>Perangkat Aktif</span><strong data-active-devices>{{ $activeDevices }}</strong></div>
        </article>
        <article class="sv-summary-card sv-summary-card--blue">
            <x-feature-icon name="wifi" tone="blue" :size="19" variant="soft" class="sv-summary-icon" />
            <div class="sv-summary-copy"><span>Perangkat Online</span><strong>{{ $onlineDevices }}</strong></div>
        </article>
    </section>

    @if($roomCollection->isEmpty())
        <section class="sv-card">
            <div class="sv-card-body">
                <div class="sv-empty-state sv-empty-state-compact">
                    <x-feature-icon name="rooms" tone="violet" :size="27" variant="soft" />
                    <h3>Belum ada ruangan</h3>
                    <a href="{{ route('settings', ['tab' => 'technician']) }}#technician" class="sv-button sv-button-primary">Tambah ruangan</a>
                </div>
            </div>
        </section>
    @else
        <section class="sv-room-grid sv-room-grid-three" aria-label="Daftar ruangan">
            @foreach($roomCollection as $room)
                @php
                    $devices = collect($room->devices ?? []);
                    $roomActiveCount = $devices->filter(fn ($device) => $isOn($device->status ?? null))->count();
                    $roomOnlineCount = $devices->filter(function ($device) use ($onlineIds) {
                        $espId = trim((string) ($device->esp_unit_id ?: $device->esp32_device_id));
                        return $espId !== '' && $onlineIds->contains($espId);
                    })->count();
                    $power = (float) (($roomPower ?? collect())[$room->id] ?? 0);
                    $roomNameLower = strtolower((string) $room->name);
                    $roomIcon = str_contains($roomNameLower, 'dapur') ? 'kitchen'
                        : (str_contains($roomNameLower, 'kamar') ? 'bed'
                        : (str_contains($roomNameLower, 'tamu') ? 'sofa'
                        : (str_contains($roomNameLower, 'garasi') ? 'garage' : 'rooms')));
                    $roomTone = $roomTones[$loop->index % count($roomTones)];
                @endphp

                <article class="sv-room-card sv-room-card--{{ $roomTone }}" data-room-card="{{ $room->id }}">
                    <header class="sv-room-card-head">
                        <div class="sv-room-card-title">
                            <x-feature-icon :name="$roomIcon" :tone="$roomTone" :size="18" variant="soft" class="sv-room-icon" />
                            <div>
                                <h3>{{ ucwords(strtolower((string) $room->name)) }}</h3>
                                <p>{{ $devices->count() }} perangkat · {{ number_format($power, 1, ',', '.') }} W</p>
                            </div>
                        </div>
                        <span class="sv-badge {{ $roomOnlineCount > 0 ? 'sv-badge-success' : 'sv-badge-warning' }}">
                            <span class="sv-status-dot" aria-hidden="true"></span>
                            {{ $roomOnlineCount > 0 ? 'Online' : 'Offline' }}
                        </span>
                    </header>

                    <div class="sv-room-card-body">
                        @forelse($devices as $device)
                            @php
                                $deviceOn = $isOn($device->status ?? null);
                                $espId = trim((string) ($device->esp_unit_id ?: $device->esp32_device_id));
                                $espOnline = $espId !== '' && $onlineIds->contains($espId);
                                $deviceLabel = strtolower(($device->type ?? '') . ' ' . ($device->name ?? ''));
                                $deviceIcon = str_contains($deviceLabel, 'kipas') || str_contains($deviceLabel, 'fan') ? 'fan'
                                    : (str_contains($deviceLabel, 'lampu') || str_contains($deviceLabel, 'light') ? 'lightbulb' : 'plug');
                                $deviceTone = $deviceOn ? 'green' : ($deviceIcon === 'fan' ? 'cyan' : ($deviceIcon === 'lightbulb' ? 'amber' : 'blue'));
                            @endphp
                            <div class="sv-device-row" data-device-row="{{ $device->id }}">
                                <div class="sv-device-main">
                                    <x-feature-icon :name="$deviceIcon" :tone="$deviceTone" :size="16" variant="soft" class="sv-device-icon" />
                                    <div class="sv-device-copy">
                                        <strong>{{ $device->name }}</strong>
                                        <span>{{ $espOnline ? ($deviceOn ? 'Sedang menyala' : 'Siap digunakan') : 'Tidak dapat dikontrol' }}</span>
                                    </div>
                                </div>

                                <button
                                    type="button"
                                    class="sv-device-switch {{ $deviceOn ? 'is-on' : '' }} {{ ! $espOnline ? 'is-offline' : '' }}"
                                    data-device-toggle
                                    data-device-id="{{ $device->id }}"
                                    data-room-id="{{ $room->id }}"
                                    data-url="{{ route('devices.toggle', $device) }}"
                                    data-current-state="{{ $deviceOn ? 'on' : 'off' }}"
                                    aria-pressed="{{ $deviceOn ? 'true' : 'false' }}"
                                    {{ ! $espOnline ? 'disabled' : '' }}
                                    title="{{ $espOnline ? 'Ubah status perangkat' : 'Perangkat sedang offline' }}"
                                >
                                    <span data-switch-label>{{ $espOnline ? ($deviceOn ? 'Nyala' : 'Mati') : 'Offline' }}</span>
                                </button>
                            </div>
                        @empty
                            <div class="sv-empty-state sv-empty-state-compact">
                                <x-feature-icon name="plug" tone="cyan" :size="23" variant="soft" />
                                <h3>Belum ada perangkat</h3>
                                <a href="{{ route('settings', ['tab' => 'technician']) }}#room-{{ $room->id }}" class="sv-text-button">Tambah perangkat</a>
                            </div>
                        @endforelse
                    </div>

                    <footer class="sv-room-card-foot">
                        <span><strong data-room-active-count="{{ $room->id }}">{{ $roomActiveCount }}</strong> dari {{ $devices->count() }} aktif</span>
                    </footer>
                </article>
            @endforeach
        </section>
    @endif
@endsection

@push('scripts')
    <script src="{{ asset('assets/js/smartvolt-rooms.js') }}?v=20260731-v7" defer></script>
@endpush
```

## `payload/resources/views/devices.blade.php`

```blade
@extends('layouts.app')

@section('title', 'Daftar Perangkat')
@section('page-title', 'Daftar Perangkat')
@section('page-subtitle', 'Perangkat yang terdaftar pada SmartVolt.')
@section('body-class', 'sv-devices-page sv-devices-streamlined')

@php
    $deviceCollection = collect($devices ?? []);
@endphp

@section('content')
    <section class="sv-card">
        <header class="sv-card-header">
            <div>
                <h2>Semua Perangkat</h2>
                <p>{{ $deviceCollection->count() }} perangkat terdaftar.</p>
            </div>
            <a href="{{ route('rooms') }}" class="sv-button sv-button-secondary sv-button-sm">Kembali ke ruangan</a>
        </header>

        <div class="sv-card-body">
            @if($deviceCollection->isEmpty())
                <div class="sv-empty-state sv-empty-state-compact">
                    <x-feature-icon name="devices" tone="cyan" :size="27" variant="soft" />
                    <h3>Belum ada perangkat</h3>
                    <a href="{{ route('settings', ['tab' => 'technician']) }}" class="sv-button sv-button-primary">Tambah perangkat</a>
                </div>
            @else
                <div class="sv-device-directory">
                    @foreach($deviceCollection as $device)
                        @php
                            $deviceLabel = strtolower(($device->type ?? '') . ' ' . ($device->name ?? ''));
                            $deviceIcon = str_contains($deviceLabel, 'kipas') || str_contains($deviceLabel, 'fan') ? 'fan'
                                : (str_contains($deviceLabel, 'lampu') || str_contains($deviceLabel, 'light') ? 'lightbulb' : 'plug');
                            $deviceTone = $device->status ? 'green' : ($deviceIcon === 'fan' ? 'cyan' : ($deviceIcon === 'lightbulb' ? 'amber' : 'blue'));
                        @endphp
                        <article class="sv-device-directory-card">
                            <x-feature-icon :name="$deviceIcon" :tone="$deviceTone" :size="18" variant="soft" />
                            <div class="sv-device-directory-copy">
                                <strong>{{ $device->name }}</strong>
                                <span>{{ $device->room?->name ?? 'Tanpa ruangan' }}</span>
                            </div>
                            <span class="sv-badge {{ $device->status ? 'sv-badge-success' : 'sv-badge-neutral' }}">
                                <span class="sv-status-dot" aria-hidden="true"></span>
                                {{ $device->status ? 'Nyala' : 'Mati' }}
                            </span>
                        </article>
                    @endforeach
                </div>
            @endif
        </div>
    </section>
@endsection
```

## `payload/resources/views/rooms-show.blade.php`

```blade
@extends('layouts.app')

@section('title', $room->name ?? 'Detail Ruangan')
@section('page-title', $room->name ?? 'Detail Ruangan')
@section('page-subtitle', 'Perangkat pada ruangan ini.')
@section('body-class', 'sv-room-detail-page')

@php
    $deviceCollection = collect($devices ?? $room->devices ?? []);
@endphp

@section('content')
    <section class="sv-card">
        <header class="sv-card-header">
            <div><h2>Perangkat</h2><p>{{ $deviceCollection->count() }} perangkat terdaftar.</p></div>
            <a href="{{ route('rooms') }}" class="sv-button sv-button-secondary sv-button-sm">Kembali</a>
        </header>
        <div class="sv-card-body">
            @if($deviceCollection->isEmpty())
                <div class="sv-empty-state sv-empty-state-compact">
                    <x-feature-icon name="plug" tone="cyan" :size="25" variant="soft" />
                    <h3>Belum ada perangkat</h3>
                    <a href="{{ route('settings', ['tab' => 'technician']) }}#room-{{ $room->id }}" class="sv-button sv-button-primary">Tambah melalui Mode Teknisi</a>
                </div>
            @else
                <div class="sv-device-directory">
                    @foreach($deviceCollection as $device)
                        @php
                            $label = strtolower(($device->type ?? '') . ' ' . ($device->name ?? ''));
                            $icon = str_contains($label, 'kipas') || str_contains($label, 'fan') ? 'fan' : (str_contains($label, 'lampu') ? 'lightbulb' : 'plug');
                        @endphp
                        <article class="sv-device-directory-card">
                            <x-feature-icon :name="$icon" :tone="$device->status ? 'green' : 'cyan'" :size="18" variant="soft" />
                            <div class="sv-device-directory-copy"><strong>{{ $device->name }}</strong><span>{{ $device->status ? 'Sedang menyala' : 'Sedang mati' }}</span></div>
                            <span class="sv-badge {{ $device->status ? 'sv-badge-success' : 'sv-badge-neutral' }}">{{ $device->status ? 'Nyala' : 'Mati' }}</span>
                        </article>
                    @endforeach
                </div>
            @endif
        </div>
    </section>
@endsection
```

## `payload/resources/views/settings/index.blade.php`

```blade
@extends('layouts.app')

@section('title', 'Pengaturan')
@section('page-title', request('tab') === 'technician' ? 'Mode Teknisi' : 'Pengaturan')
@section('page-subtitle', request('tab') === 'technician' ? 'Konfigurasi perangkat SmartVolt.' : 'Kelola akun dan pengaturan sistem.')
@section('body-class', 'sv-settings-page sv-settings-streamlined')

@php
    $advancedMode = (bool) session('advanced_mode');
    $requestedTab = request('tab');
    $activeTab = in_array($requestedTab, ['profile', 'security', 'system', 'technician'], true)
        ? $requestedTab
        : (session('open_advanced_panel') ? 'technician' : 'profile');

    if ($errors->has('advanced_mode')) {
        $activeTab = 'technician';
    } elseif ($errors->has('current_password') || $errors->has('password')) {
        $activeTab = 'security';
    } elseif ($errors->has('electricity_tariff') || $errors->has('power_limit') || $errors->has('refresh_interval')) {
        $activeTab = 'system';
    }

    $roomCollection = collect($rooms ?? []);
    $meterCollection = collect($energyMeters ?? []);
    $deviceCollection = collect($devices ?? []);
    $selectedRoomId = (int) (session('selected_room_id') ?? 0);
@endphp

@section('system-status')
    <span class="sv-badge {{ $advancedMode ? 'sv-badge-warning' : 'sv-badge-success' }}">
        <span class="sv-status-dot" aria-hidden="true"></span>
        {{ $advancedMode ? 'Mode teknisi aktif' : 'Mode pengguna' }}
    </span>
@endsection

@section('content')
    <div class="sv-settings-layout">
        <nav class="sv-settings-nav" data-tabs data-panels-root="settingsPanels" aria-label="Bagian pengaturan">
            <button type="button" class="sv-tab-button {{ $activeTab === 'profile' ? 'is-active' : '' }}" data-tab-target="tab-profile" data-tab-name="profile" aria-selected="{{ $activeTab === 'profile' ? 'true' : 'false' }}">
                <x-feature-icon name="user" tone="blue" :size="16" variant="soft" /> <span>Profil</span>
            </button>
            <button type="button" class="sv-tab-button {{ $activeTab === 'security' ? 'is-active' : '' }}" data-tab-target="tab-security" data-tab-name="security" aria-selected="{{ $activeTab === 'security' ? 'true' : 'false' }}">
                <x-feature-icon name="shield" tone="green" :size="16" variant="soft" /> <span>Keamanan</span>
            </button>
            <button type="button" class="sv-tab-button {{ $activeTab === 'system' ? 'is-active' : '' }}" data-tab-target="tab-system" data-tab-name="system" aria-selected="{{ $activeTab === 'system' ? 'true' : 'false' }}">
                <x-feature-icon name="energy" tone="cyan" :size="16" variant="soft" /> <span>Energi & Sistem</span>
            </button>
            <button type="button" class="sv-tab-button {{ $activeTab === 'technician' ? 'is-active' : '' }}" data-tab-target="tab-technician" data-tab-name="technician" aria-selected="{{ $activeTab === 'technician' ? 'true' : 'false' }}">
                <x-feature-icon name="tools" tone="amber" :size="16" variant="soft" /> <span>Mode Teknisi</span>
            </button>
        </nav>

        <div class="sv-settings-panels" id="settingsPanels">
            <section id="tab-profile" class="sv-tab-panel {{ $activeTab === 'profile' ? 'is-active' : '' }}" data-tab-panel>
                <article class="sv-card sv-settings-card sv-settings-card-compact">
                    <header class="sv-profile-summary">
                        <span class="sv-profile-avatar-large">{{ strtoupper(substr($user->name ?? 'U', 0, 1)) }}</span>
                        <div>
                            <h3>{{ $user->name ?? 'Pengguna SmartVolt' }}</h3>
                            <p>{{ $user->email ?? '-' }}</p>
                        </div>
                    </header>

                    <div class="sv-card-body">
                        <form action="{{ route('settings.profile.update') }}" method="POST" class="sv-stack" data-loading-form>
                            @csrf
                            @method('PUT')

                            <div class="sv-form-grid">
                                <div class="sv-form-field">
                                    <label for="profile_name" class="sv-form-label">Nama lengkap</label>
                                    <input type="text" id="profile_name" name="name" class="sv-form-control @error('name') is-invalid @enderror" value="{{ old('name', $user->name) }}" maxlength="255" autocomplete="name" required>
                                    @error('name')<p class="sv-form-error">{{ $message }}</p>@enderror
                                </div>
                                <div class="sv-form-field">
                                    <label for="profile_email" class="sv-form-label">Alamat email</label>
                                    <input type="email" id="profile_email" name="email" class="sv-form-control @error('email') is-invalid @enderror" value="{{ old('email', $user->email) }}" maxlength="255" autocomplete="email" required>
                                    @error('email')<p class="sv-form-error">{{ $message }}</p>@enderror
                                </div>
                            </div>

                            <div class="sv-form-actions">
                                <button type="submit" class="sv-button sv-button-primary" data-loading-text="Menyimpan...">
                                    <span class="sv-button-spinner" aria-hidden="true"></span>
                                    <span data-button-label>Simpan profil</span>
                                </button>
                            </div>
                        </form>
                    </div>
                </article>
            </section>

            <section id="tab-security" class="sv-tab-panel {{ $activeTab === 'security' ? 'is-active' : '' }}" data-tab-panel>
                <article class="sv-card sv-settings-card sv-settings-card-compact">
                    <header class="sv-card-header">
                        <div><h2>Ubah Kata Sandi</h2><p>Gunakan minimal delapan karakter.</p></div>
                    </header>
                    <div class="sv-card-body">
                        <form action="{{ route('settings.password.update') }}" method="POST" class="sv-stack" data-loading-form>
                            @csrf
                            @method('PUT')

                            <div class="sv-form-field">
                                <label for="current_password" class="sv-form-label">Kata sandi saat ini</label>
                                <div class="sv-password-wrap">
                                    <input type="password" id="current_password" name="current_password" class="sv-form-control @error('current_password') is-invalid @enderror" autocomplete="current-password" required>
                                    <button type="button" class="sv-password-toggle" data-password-toggle="current_password" aria-label="Tampilkan kata sandi"><x-icon name="eye" :size="17" /></button>
                                </div>
                                @error('current_password')<p class="sv-form-error">{{ $message }}</p>@enderror
                            </div>

                            <div class="sv-form-grid">
                                <div class="sv-form-field">
                                    <label for="new_password" class="sv-form-label">Kata sandi baru</label>
                                    <div class="sv-password-wrap">
                                        <input type="password" id="new_password" name="password" class="sv-form-control @error('password') is-invalid @enderror" minlength="8" autocomplete="new-password" required>
                                        <button type="button" class="sv-password-toggle" data-password-toggle="new_password" aria-label="Tampilkan kata sandi"><x-icon name="eye" :size="17" /></button>
                                    </div>
                                    @error('password')<p class="sv-form-error">{{ $message }}</p>@enderror
                                </div>
                                <div class="sv-form-field">
                                    <label for="new_password_confirmation" class="sv-form-label">Konfirmasi kata sandi</label>
                                    <div class="sv-password-wrap">
                                        <input type="password" id="new_password_confirmation" name="password_confirmation" class="sv-form-control" minlength="8" autocomplete="new-password" required>
                                        <button type="button" class="sv-password-toggle" data-password-toggle="new_password_confirmation" aria-label="Tampilkan kata sandi"><x-icon name="eye" :size="17" /></button>
                                    </div>
                                </div>
                            </div>

                            <div class="sv-form-actions">
                                <button type="submit" class="sv-button sv-button-primary" data-loading-text="Memperbarui...">
                                    <span class="sv-button-spinner" aria-hidden="true"></span>
                                    <span data-button-label>Perbarui kata sandi</span>
                                </button>
                            </div>
                        </form>
                    </div>
                </article>
            </section>

            <section id="tab-system" class="sv-tab-panel {{ $activeTab === 'system' ? 'is-active' : '' }}" data-tab-panel>
                <article class="sv-card sv-settings-card sv-settings-card-compact">
                    <header class="sv-card-header">
                        <div><h2>Energi dan Sistem</h2><p>Tarif, batas daya, dan interval pembaruan.</p></div>
                        @if(!$advancedMode)<span class="sv-badge sv-badge-warning">Terkunci</span>@endif
                    </header>
                    <div class="sv-card-body">
                        @if(!$advancedMode)
                            <div class="sv-inline-notice sv-inline-notice-warning">
                                <x-icon name="lock" :size="18" />
                                <span>Aktifkan Mode Teknisi untuk mengubah nilai.</span>
                            </div>
                        @endif

                        <form action="{{ route('settings.system.update') }}" method="POST" class="sv-stack" data-loading-form>
                            @csrf
                            @method('PUT')

                            <div class="sv-form-grid-three">
                                <div class="sv-form-field">
                                    <label for="electricity_tariff" class="sv-form-label">Tarif per kWh</label>
                                    <input type="number" step="0.01" min="0" id="electricity_tariff" name="electricity_tariff" class="sv-form-control @error('electricity_tariff') is-invalid @enderror" value="{{ old('electricity_tariff', $systemSetting->electricity_tariff) }}" {{ !$advancedMode ? 'disabled' : '' }} required>
                                    @error('electricity_tariff')<p class="sv-form-error">{{ $message }}</p>@enderror
                                </div>
                                <div class="sv-form-field">
                                    <label for="power_limit" class="sv-form-label">Batas daya (W)</label>
                                    <input type="number" min="1" id="power_limit" name="power_limit" class="sv-form-control @error('power_limit') is-invalid @enderror" value="{{ old('power_limit', $systemSetting->power_limit) }}" {{ !$advancedMode ? 'disabled' : '' }} required>
                                    @error('power_limit')<p class="sv-form-error">{{ $message }}</p>@enderror
                                </div>
                                <div class="sv-form-field">
                                    <label for="refresh_interval" class="sv-form-label">Interval (detik)</label>
                                    <input type="number" min="1" max="60" id="refresh_interval" name="refresh_interval" class="sv-form-control @error('refresh_interval') is-invalid @enderror" value="{{ old('refresh_interval', $systemSetting->refresh_interval) }}" {{ !$advancedMode ? 'disabled' : '' }} required>
                                    @error('refresh_interval')<p class="sv-form-error">{{ $message }}</p>@enderror
                                </div>
                            </div>

                            <div class="sv-form-actions">
                                @if($advancedMode)
                                    <button type="submit" class="sv-button sv-button-primary" data-loading-text="Menyimpan..."><span class="sv-button-spinner"></span><span data-button-label>Simpan pengaturan</span></button>
                                @else
                                    <a href="{{ route('settings', ['tab' => 'technician']) }}#technician" class="sv-button sv-button-warning">Aktifkan Mode Teknisi</a>
                                @endif
                            </div>
                        </form>
                    </div>
                </article>
            </section>

            <section id="tab-technician" class="sv-tab-panel {{ $activeTab === 'technician' ? 'is-active' : '' }}" data-tab-panel>
                @if(!$advancedMode)
                    <article class="sv-card sv-technician-lock-compact" id="technician">
                        <div class="sv-card-body">
                            <x-feature-icon name="lock" tone="amber" :size="25" variant="soft" />
                            <div><h3>Mode Teknisi terkunci</h3><p>Verifikasi PIN untuk mengubah konfigurasi perangkat.</p></div>
                            <button type="button" class="sv-button sv-button-warning" data-dialog-open="technicianPinDialog">Verifikasi PIN</button>
                        </div>
                    </article>
                @else
                    <div class="sv-technician-toolbar" id="technician">
                        <div>
                            <strong>Mode Teknisi aktif</strong>
                            <span>Perubahan akan diterapkan pada konfigurasi SmartVolt.</span>
                        </div>
                        <div class="sv-inline">
                            <button type="button" class="sv-button sv-button-primary sv-button-sm" data-dialog-open="addRoomDialog">Tambah ruangan</button>
                            <form action="{{ route('advanced-mode.disable') }}" method="POST" data-loading-form>
                                @csrf
                                <button type="submit" class="sv-button sv-button-secondary sv-button-sm" data-loading-text="Menonaktifkan..."><span data-button-label>Nonaktifkan</span></button>
                            </form>
                        </div>
                    </div>

                    <section class="sv-technician-summary sv-technician-summary-three">
                        <article class="sv-technician-summary-card sv-technician-summary-card--violet"><x-feature-icon name="rooms" tone="violet" :size="18" variant="soft" /><span>Ruangan</span><strong>{{ $roomCollection->count() }}</strong></article>
                        <article class="sv-technician-summary-card sv-technician-summary-card--green"><x-feature-icon name="sensor" tone="green" :size="18" variant="soft" /><span>Sensor</span><strong>{{ $meterCollection->count() }}</strong></article>
                        <article class="sv-technician-summary-card sv-technician-summary-card--cyan"><x-feature-icon name="devices" tone="cyan" :size="18" variant="soft" /><span>Relay</span><strong>{{ $deviceCollection->count() }}</strong></article>
                    </section>

                    @if($roomCollection->isEmpty())
                        <article class="sv-card"><div class="sv-card-body"><div class="sv-empty-state sv-empty-state-compact"><h3>Belum ada ruangan</h3><button type="button" class="sv-button sv-button-primary" data-dialog-open="addRoomDialog">Tambah ruangan</button></div></div></article>
                    @else
                        <section class="sv-tech-room-list">
                            @foreach($roomCollection as $room)
                                @php
                                    $roomMeters = collect($room->energyMeters ?? []);
                                    $roomDevices = collect($room->devices ?? []);
                                    $roomEspIds = $roomMeters->pluck('esp_unit_id')->filter()->unique()->values();
                                @endphp

                                <details class="sv-tech-room" id="room-{{ $room->id }}" {{ $selectedRoomId === (int) $room->id ? 'open' : '' }}>
                                    <summary>
                                        <span class="sv-tech-room-summary-main">
                                            <x-feature-icon name="rooms" tone="violet" :size="18" variant="soft" />
                                            <span><strong>{{ $room->name }}</strong><small>{{ $roomMeters->count() }} sensor · {{ $roomDevices->count() }} relay</small></span>
                                        </span>
                                        <x-icon name="chevron-down" :size="17" />
                                    </summary>

                                    <div class="sv-tech-room-body">
                                        <div class="sv-tech-two-column">
                                            <section class="sv-tech-panel">
                                                <header><h4>Ruangan</h4></header>
                                                <form action="{{ route('rooms.update', $room) }}" method="POST" class="sv-stack" data-loading-form>
                                                    @csrf
                                                    @method('PUT')
                                                    <input type="hidden" name="return_to" value="settings">
                                                    <div class="sv-form-field"><label class="sv-form-label">Nama ruangan</label><input type="text" name="name" value="{{ $room->name }}" class="sv-form-control" maxlength="100" required></div>
                                                    <div class="sv-inline sv-tech-actions-row">
                                                        <button type="submit" class="sv-button sv-button-secondary sv-button-sm" data-loading-text="Menyimpan..."><span data-button-label>Simpan</span></button>
                                                    </div>
                                                </form>
                                                <form action="{{ route('rooms.destroy', $room) }}" method="POST" data-confirm="Hapus ruangan {{ $room->name }} beserta relay di dalamnya?">
                                                    @csrf
                                                    @method('DELETE')
                                                    <input type="hidden" name="return_to" value="settings">
                                                    <button type="submit" class="sv-text-danger">Hapus ruangan</button>
                                                </form>
                                            </section>

                                            <section class="sv-tech-panel">
                                                <header><h4>Tambah Unit SmartVolt</h4></header>
                                                <form action="{{ route('technician.rooms.sensor.store', $room) }}" method="POST" class="sv-form-grid-two" data-loading-form data-sensor-form>
                                                    @csrf
                                                    <input type="hidden" name="return_to" value="settings">
                                                    <div class="sv-form-field"><label class="sv-form-label">Nama sensor</label><input type="text" name="sensor_name" class="sv-form-control" placeholder="Meter utama" maxlength="100" required></div>
                                                    <div class="sv-form-field"><label class="sv-form-label">ESP Unit ID</label><input type="text" name="esp_unit_id" class="sv-form-control" placeholder="2" maxlength="100" required></div>
                                                    <div class="sv-form-field"><label class="sv-form-label">Meter code</label><input type="text" name="meter_code" class="sv-form-control" placeholder="main" maxlength="50" required></div>
                                                    <div class="sv-form-field"><label class="sv-form-label">Jumlah relay</label><select name="relay_count" class="sv-form-control" data-relay-count required>@for($i = 1; $i <= 8; $i++)<option value="{{ $i }}">{{ $i }} relay</option>@endfor</select></div>
                                                    <input type="hidden" name="sensor_type" value="PZEM004T">
                                                    <div class="sv-form-actions sv-grid-full"><button type="submit" class="sv-button sv-button-primary sv-button-sm" data-loading-text="Menambahkan..."><span data-button-label>Tambah unit</span></button></div>
                                                </form>
                                            </section>
                                        </div>

                                        @if($roomEspIds->isNotEmpty())
                                            <details class="sv-tech-inline-details">
                                                <summary>Tambah relay ke unit yang sudah ada</summary>
                                                <form action="{{ route('technician.rooms.relay.store', $room) }}" method="POST" class="sv-form-grid-two" data-loading-form>
                                                    @csrf
                                                    <input type="hidden" name="return_to" value="settings">
                                                    <div class="sv-form-field"><label class="sv-form-label">ESP Unit ID</label><select name="esp_unit_id" class="sv-form-control" required>@foreach($roomEspIds as $espId)<option value="{{ $espId }}">{{ $espId }}</option>@endforeach</select></div>
                                                    <div class="sv-form-field"><label class="sv-form-label">Relay channel</label><input type="text" name="relay_code" class="sv-form-control" placeholder="3" maxlength="50" required></div>
                                                    <div class="sv-form-field"><label class="sv-form-label">Nama perangkat</label><input type="text" name="name" class="sv-form-control" placeholder="Lampu meja" maxlength="100" required></div>
                                                    <div class="sv-form-actions"><button type="submit" class="sv-button sv-button-secondary sv-button-sm" data-loading-text="Menambahkan..."><span data-button-label>Tambah relay</span></button></div>
                                                </form>
                                            </details>
                                        @endif

                                        <div class="sv-tech-two-column sv-tech-inventory-grid">
                                            <section class="sv-tech-panel">
                                                <header><h4>Sensor Listrik</h4><span class="sv-badge sv-badge-neutral">{{ $roomMeters->count() }}</span></header>
                                                <div class="sv-tech-item-list">
                                                    @forelse($roomMeters as $meter)
                                                        <article class="sv-tech-item">
                                                            <div class="sv-tech-item-head">
                                                                <div class="sv-tech-item-copy"><strong>{{ $meter->name }}</strong><span>ESP {{ $meter->esp_unit_id }} · {{ $meter->meter_code }}</span></div>
                                                                <span class="sv-badge {{ $meter->is_active ? 'sv-badge-success' : 'sv-badge-neutral' }}">{{ $meter->is_active ? 'Aktif' : 'Nonaktif' }}</span>
                                                            </div>
                                                            <div class="sv-tech-actions-row">
                                                                <form action="{{ route('technician.sensors.toggle', $meter) }}" method="POST" data-loading-form>@csrf @method('PATCH')<button type="submit" class="sv-button sv-button-secondary sv-button-sm" data-loading-text="Memproses..."><span data-button-label>{{ $meter->is_active ? 'Nonaktifkan' : 'Aktifkan' }}</span></button></form>
                                                                <details class="sv-tech-edit"><summary>Ubah</summary><div class="sv-tech-edit-body"><form action="{{ route('technician.sensors.update', $meter) }}" method="POST" class="sv-form-grid-two" data-loading-form>@csrf @method('PUT')<div class="sv-form-field"><label class="sv-form-label">Nama sensor</label><input type="text" name="name" value="{{ $meter->name }}" class="sv-form-control" maxlength="100" required></div><div class="sv-form-field"><label class="sv-form-label">Meter code</label><input type="text" name="meter_code" value="{{ $meter->meter_code }}" class="sv-form-control" maxlength="50" required></div><input type="hidden" name="sensor_type" value="{{ $meter->sensor_type ?: 'PZEM004T' }}"><label class="sv-checkbox"><input type="checkbox" name="is_active" value="1" {{ $meter->is_active ? 'checked' : '' }}><span></span><em>Sensor aktif</em></label><div class="sv-form-actions"><button type="submit" class="sv-button sv-button-primary sv-button-sm" data-loading-text="Menyimpan..."><span data-button-label>Simpan</span></button></div></form></div></details>
                                                                <form action="{{ route('technician.sensors.destroy', $meter) }}" method="POST" data-confirm="Hapus sensor {{ $meter->name }}?">@csrf @method('DELETE')<button type="submit" class="sv-text-danger">Hapus</button></form>
                                                            </div>
                                                        </article>
                                                    @empty
                                                        <div class="sv-empty-inline">Belum ada sensor.</div>
                                                    @endforelse
                                                </div>
                                            </section>

                                            <section class="sv-tech-panel">
                                                <header><h4>Relay dan Perangkat</h4><span class="sv-badge sv-badge-neutral">{{ $roomDevices->count() }}</span></header>
                                                <div class="sv-tech-item-list">
                                                    @forelse($roomDevices as $device)
                                                        <article class="sv-tech-item">
                                                            <div class="sv-tech-item-head">
                                                                <div class="sv-tech-item-copy"><strong>{{ $device->name }}</strong><span>ESP {{ $device->esp_unit_id ?: $device->esp32_device_id ?: '-' }} · Relay {{ $device->relay_code ?: '-' }}</span></div>
                                                                <span class="sv-badge {{ $device->status ? 'sv-badge-success' : 'sv-badge-neutral' }}">{{ $device->status ? 'Nyala' : 'Mati' }}</span>
                                                            </div>
                                                            <div class="sv-tech-actions-row">
                                                                <details class="sv-tech-edit"><summary>Ubah</summary><div class="sv-tech-edit-body"><form action="{{ route('devices.update', $device) }}" method="POST" class="sv-form-grid-two" data-loading-form>@csrf @method('PUT')<input type="hidden" name="return_to" value="settings"><div class="sv-form-field"><label class="sv-form-label">Nama perangkat</label><input type="text" name="name" value="{{ $device->name }}" class="sv-form-control" maxlength="100" required></div><div class="sv-form-field"><label class="sv-form-label">ESP Unit ID</label><input type="text" name="esp_unit_id" value="{{ $device->esp_unit_id ?: $device->esp32_device_id }}" class="sv-form-control" maxlength="100" required></div><div class="sv-form-field"><label class="sv-form-label">Relay channel</label><input type="text" name="relay_code" value="{{ $device->relay_code }}" class="sv-form-control" maxlength="100" required></div><div class="sv-form-field"><label class="sv-form-label">Device key</label><input type="text" name="device_key" value="{{ $device->device_key }}" class="sv-form-control" maxlength="100"></div><div class="sv-form-actions sv-grid-full"><button type="submit" class="sv-button sv-button-primary sv-button-sm" data-loading-text="Menyimpan..."><span data-button-label>Simpan</span></button></div></form></div></details>
                                                                <form action="{{ route('devices.destroy', $device) }}" method="POST" data-confirm="Hapus relay {{ $device->name }}?">@csrf @method('DELETE')<input type="hidden" name="return_to" value="settings"><button type="submit" class="sv-text-danger">Hapus</button></form>
                                                            </div>
                                                        </article>
                                                    @empty
                                                        <div class="sv-empty-inline">Belum ada relay.</div>
                                                    @endforelse
                                                </div>
                                            </section>
                                        </div>
                                    </div>
                                </details>
                            @endforeach
                        </section>
                    @endif
                @endif
            </section>
        </div>
    </div>

    <dialog class="sv-dialog" id="technicianPinDialog" {{ $errors->has('advanced_mode') ? 'data-auto-open-dialog=technicianPinDialog' : '' }}>
        <header class="sv-dialog-header">
            <div><h3>Verifikasi PIN Teknisi</h3><p>Masukkan PIN untuk membuka konfigurasi.</p></div>
            <button type="button" class="sv-dialog-close" data-dialog-close aria-label="Tutup"><x-icon name="close" :size="18" /></button>
        </header>
        <div class="sv-dialog-body">
            <form action="{{ route('advanced-mode.enable') }}" method="POST" data-loading-form>
                @csrf
                <div class="sv-form-field">
                    <label for="technician_pin" class="sv-form-label">PIN Teknisi</label>
                    <div class="sv-password-wrap">
                        <input type="password" id="technician_pin" name="pin" class="sv-form-control @error('advanced_mode') is-invalid @enderror" inputmode="numeric" autocomplete="off" required autofocus>
                        <button type="button" class="sv-password-toggle" data-password-toggle="technician_pin" aria-label="Tampilkan PIN"><x-icon name="eye" :size="18" /></button>
                    </div>
                    @error('advanced_mode')<p class="sv-form-error">{{ $message }}</p>@enderror
                </div>
                <div class="sv-dialog-actions"><button type="button" class="sv-button sv-button-secondary" data-dialog-close>Batal</button><button type="submit" class="sv-button sv-button-warning" data-loading-text="Memverifikasi..."><span class="sv-button-spinner"></span><span data-button-label>Verifikasi</span></button></div>
            </form>
        </div>
    </dialog>

    <dialog class="sv-dialog" id="addRoomDialog">
        <header class="sv-dialog-header">
            <div><h3>Tambah Ruangan</h3><p>Masukkan nama ruangan.</p></div>
            <button type="button" class="sv-dialog-close" data-dialog-close aria-label="Tutup"><x-icon name="close" :size="18" /></button>
        </header>
        <div class="sv-dialog-body">
            <form action="{{ route('rooms.store') }}" method="POST" data-loading-form>
                @csrf
                <input type="hidden" name="return_to" value="settings">
                <div class="sv-form-field"><label for="new_room_name" class="sv-form-label">Nama ruangan</label><input type="text" id="new_room_name" name="name" class="sv-form-control" maxlength="100" placeholder="Contoh: Ruang Tamu" required></div>
                <div class="sv-dialog-actions"><button type="button" class="sv-button sv-button-secondary" data-dialog-close>Batal</button><button type="submit" class="sv-button sv-button-primary" data-loading-text="Menyimpan..."><span class="sv-button-spinner"></span><span data-button-label>Simpan ruangan</span></button></div>
            </form>
        </div>
    </dialog>
@endsection

@push('scripts')
    <script src="{{ asset('assets/js/smartvolt-settings.js') }}?v=20260731-v7" defer></script>
@endpush
```

## `payload/resources/views/auth/login.blade.php`

```blade
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="color-scheme" content="light">
    <meta name="theme-color" content="#173B66">

    <title>Masuk | SmartVolt</title>

    <link rel="icon" href="{{ asset('favicon.ico') }}">

    <link rel="stylesheet" href="{{ asset('assets/css/smartvolt-auth.css') }}?v=20260730-v5">
</head>

<body class="sv-auth-page">

    <main class="sv-auth-shell">
        <section
            class="sv-auth-showcase"
            aria-label="Informasi SmartVolt"
        >
            <svg
                class="sv-auth-network"
                viewBox="0 0 900 900"
                aria-hidden="true"
                focusable="false"
            >
                <g fill="none" stroke="currentColor" stroke-width="1.5">
                    <path d="M70 130H280L360 210H590L690 110H840"/>
                    <path d="M40 390H190L290 290H470L560 380H820"/>
                    <path d="M110 680H300L400 580H620L720 680H860"/>
                    <path d="M240 80V260L160 340V520"/>
                    <path d="M650 80V250L740 340V520L650 610V820"/>
                    <path d="M470 210V470L390 550V790"/>
                </g>

                <g fill="currentColor">
                    <circle cx="70" cy="130" r="5"/>
                    <circle cx="360" cy="210" r="5"/>
                    <circle cx="690" cy="110" r="5"/>
                    <circle cx="190" cy="390" r="5"/>
                    <circle cx="470" cy="290" r="5"/>
                    <circle cx="560" cy="380" r="5"/>
                    <circle cx="300" cy="680" r="5"/>
                    <circle cx="620" cy="580" r="5"/>
                    <circle cx="740" cy="340" r="5"/>
                    <circle cx="390" cy="550" r="5"/>
                </g>
            </svg>

            <div class="sv-auth-showcase-content">
                <a
                    href="{{ url('/') }}"
                    class="sv-auth-brand"
                    aria-label="SmartVolt"
                >
                    <span class="sv-auth-brand-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24">
                            <path
                                d="M13.2 2.5 5.8 13h5.1l-.7 8.5L18.4 10h-5.1l-.1-7.5Z"
                                fill="currentColor"
                            />
                        </svg>
                    </span>

                    <span>
                        <strong>SmartVolt</strong>
                        <small>Calm Energy Control</small>
                    </span>
                </a>

                <div class="sv-auth-copy">
                    <p class="sv-auth-eyebrow">
                        Monitoring dan kontrol energi berbasis IoT
                    </p>

                    <h1>
                        Kendalikan energi rumah dengan lebih tenang.
                    </h1>

                    <p class="sv-auth-description">
                        Pantau daya, energi, estimasi biaya, dan perangkat
                        listrik melalui satu sistem yang jelas dan mudah
                        dipahami.
                    </p>
                </div>

                <div class="sv-auth-benefits">
                    <article class="sv-auth-benefit">
                        <span class="sv-auth-benefit-icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24">
                                <path d="M4 19V9m5 10V5m5 14v-7m5 7V3"/>
                            </svg>
                        </span>

                        <div>
                            <h2>Data listrik yang mudah dibaca</h2>
                            <p>
                                Lihat tegangan, arus, daya, energi, dan
                                perubahan penggunaan secara berkala.
                            </p>
                        </div>
                    </article>

                    <article class="sv-auth-benefit">
                        <span class="sv-auth-benefit-icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24">
                                <rect x="5" y="3" width="14" height="18" rx="3"/>
                                <path d="M9 8h6M9 12h6M9 16h3"/>
                            </svg>
                        </span>

                        <div>
                            <h2>Estimasi biaya yang transparan</h2>
                            <p>
                                Pemakaian kWh dihitung berdasarkan data meter
                                dan dikalikan dengan tarif listrik.
                            </p>
                        </div>
                    </article>

                    <article class="sv-auth-benefit">
                        <span class="sv-auth-benefit-icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24">
                                <path d="M8 12h8"/>
                                <rect x="3" y="7" width="6" height="10" rx="2"/>
                                <rect x="15" y="7" width="6" height="10" rx="2"/>
                            </svg>
                        </span>

                        <div>
                            <h2>Kontrol perangkat per ruangan</h2>
                            <p>
                                Nyalakan atau matikan perangkat melalui relay
                                ketika ESP32 dan MQTT terhubung.
                            </p>
                        </div>
                    </article>
                </div>

                <div class="sv-auth-flow" aria-label="Alur data SmartVolt">
                    <div class="sv-auth-flow-label">Alur sistem</div>

                    <div class="sv-auth-flow-items">
                        <span>
                            <strong>PZEM</strong>
                            <small>Membaca listrik</small>
                        </span>

                        <i aria-hidden="true">→</i>

                        <span>
                            <strong>ESP32</strong>
                            <small>Mengirim data</small>
                        </span>

                        <i aria-hidden="true">→</i>

                        <span>
                            <strong>SmartVolt</strong>
                            <small>Menampilkan hasil</small>
                        </span>
                    </div>
                </div>
            </div>
        </section>

        <section class="sv-auth-panel">
            <div class="sv-auth-panel-inner">
                <div class="sv-auth-mobile-brand">
                    <span class="sv-auth-brand-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24">
                            <path
                                d="M13.2 2.5 5.8 13h5.1l-.7 8.5L18.4 10h-5.1l-.1-7.5Z"
                                fill="currentColor"
                            />
                        </svg>
                    </span>

                    <span>
                        <strong>SmartVolt</strong>
                        <small>Monitoring energi rumah</small>
                    </span>
                </div>

                <div class="sv-auth-heading">
                    <span class="sv-auth-status-chip">
                        <span aria-hidden="true"></span>
                        Akses aman SmartVolt
                    </span>

                    <h2>Selamat datang kembali</h2>

                    <p>
                        Masuk untuk memantau dan mengontrol listrik rumah
                        Anda.
                    </p>
                </div>

                @if (session('status'))
                    <div
                        class="sv-auth-alert sv-auth-alert-success"
                        role="status"
                        aria-live="polite"
                    >
                        <span aria-hidden="true">
                            <svg viewBox="0 0 24 24">
                                <path d="m5 12 4 4L19 6"/>
                            </svg>
                        </span>

                        <div>{{ session('status') }}</div>
                    </div>
                @endif

                @if ($errors->any())
                    <div
                        class="sv-auth-alert sv-auth-alert-error"
                        role="alert"
                        aria-live="assertive"
                    >
                        <span aria-hidden="true">
                            <svg viewBox="0 0 24 24">
                                <circle cx="12" cy="12" r="9"/>
                                <path d="M12 7v6m0 4h.01"/>
                            </svg>
                        </span>

                        <div>
                            <strong>Periksa kembali data yang dimasukkan.</strong>
                            <p>{{ $errors->first() }}</p>
                        </div>
                    </div>
                @endif

                <form
                    id="loginForm"
                    class="sv-auth-form"
                    action="{{ route('login.process') }}"
                    method="POST"
                    novalidate
                >
                    @csrf

                    <div class="sv-auth-field">
                        <label for="email">Alamat email</label>

                        <div
                            class="sv-auth-input-group
                                @error('email') is-invalid @enderror"
                        >
                            <span class="sv-auth-input-icon" aria-hidden="true">
                                <svg viewBox="0 0 24 24">
                                    <rect x="3" y="5" width="18" height="14" rx="3"/>
                                    <path d="m5 8 7 5 7-5"/>
                                </svg>
                            </span>

                            <input
                                type="email"
                                id="email"
                                name="email"
                                value="{{ old('email') }}"
                                placeholder="nama@email.com"
                                autocomplete="email"
                                inputmode="email"
                                aria-describedby="emailHelp emailError"
                                @error('email') aria-invalid="true" @enderror
                                required
                                autofocus
                            >
                        </div>

                        @error('email')
                            <p
                                id="emailError"
                                class="sv-auth-field-error"
                            >
                                {{ $message }}
                            </p>
                        @else
                            <p
                                id="emailHelp"
                                class="sv-auth-field-help"
                            >
                                Gunakan email yang terdaftar pada akun
                                SmartVolt.
                            </p>
                        @enderror
                    </div>

                    <div class="sv-auth-field">
                        <label for="password">Kata sandi</label>

                        <div
                            class="sv-auth-input-group
                                @error('password') is-invalid @enderror"
                        >
                            <span class="sv-auth-input-icon" aria-hidden="true">
                                <svg viewBox="0 0 24 24">
                                    <rect x="5" y="10" width="14" height="10" rx="3"/>
                                    <path d="M8 10V7a4 4 0 0 1 8 0v3"/>
                                </svg>
                            </span>

                            <input
                                type="password"
                                id="password"
                                name="password"
                                placeholder="Masukkan kata sandi"
                                autocomplete="current-password"
                                aria-describedby="passwordError"
                                @error('password') aria-invalid="true" @enderror
                                required
                            >

                            <button
                                type="button"
                                id="togglePassword"
                                class="sv-auth-password-toggle"
                                aria-label="Tampilkan kata sandi"
                                aria-pressed="false"
                            >
                                <svg
                                    class="sv-auth-eye-show"
                                    viewBox="0 0 24 24"
                                    aria-hidden="true"
                                >
                                    <path d="M2.5 12s3.5-6 9.5-6 9.5 6 9.5 6-3.5 6-9.5 6S2.5 12 2.5 12Z"/>
                                    <circle cx="12" cy="12" r="2.5"/>
                                </svg>

                                <svg
                                    class="sv-auth-eye-hide"
                                    viewBox="0 0 24 24"
                                    aria-hidden="true"
                                >
                                    <path d="m4 4 16 16"/>
                                    <path d="M10.6 6.2A10 10 0 0 1 12 6c6 0 9.5 6 9.5 6a16 16 0 0 1-2.4 3.1M6.2 7.3A15.7 15.7 0 0 0 2.5 12S6 18 12 18a9.8 9.8 0 0 0 2.1-.2"/>
                                </svg>
                            </button>
                        </div>

                        @error('password')
                            <p
                                id="passwordError"
                                class="sv-auth-field-error"
                            >
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    <div class="sv-auth-options">
                        <label class="sv-auth-checkbox">
                            <input
                                type="checkbox"
                                name="remember"
                                value="1"
                                {{ old('remember') ? 'checked' : '' }}
                            >
                            <span aria-hidden="true"></span>
                            <em>Ingat saya</em>
                        </label>

                        <a
                            href="{{ route('password.request') }}"
                            class="sv-auth-link"
                        >
                            Lupa kata sandi?
                        </a>
                    </div>

                    <button
                        type="submit"
                        id="loginButton"
                        class="sv-auth-submit"
                    >
                        <span
                            class="sv-auth-button-spinner"
                            aria-hidden="true"
                        ></span>

                        <span id="loginButtonText">Masuk</span>
                    </button>
                </form>

                <p class="sv-auth-footer">
                    Belum memiliki akun?
                    <a
                        href="{{ route('register') }}"
                        class="sv-auth-link"
                    >
                        Daftar akun
                    </a>
                </p>

                <div class="sv-auth-security-note">
                    <svg viewBox="0 0 24 24" aria-hidden="true">
                        <path d="M12 3 5 6v5c0 4.6 2.7 8 7 10 4.3-2 7-5.4 7-10V6l-7-3Z"/>
                        <path d="m9 12 2 2 4-5"/>
                    </svg>

                    <p>
                        Akses akun dilindungi oleh sesi Laravel dan token
                        keamanan formulir.
                    </p>
                </div>
            </div>
        </section>
    </main>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const passwordInput = document.getElementById('password');
            const togglePassword = document.getElementById('togglePassword');

            if (passwordInput && togglePassword) {
                togglePassword.addEventListener('click', function () {
                    const shouldShow = passwordInput.type === 'password';

                    passwordInput.type = shouldShow ? 'text' : 'password';
                    togglePassword.classList.toggle('is-visible', shouldShow);
                    togglePassword.setAttribute(
                        'aria-label',
                        shouldShow
                            ? 'Sembunyikan kata sandi'
                            : 'Tampilkan kata sandi'
                    );
                    togglePassword.setAttribute(
                        'aria-pressed',
                        shouldShow ? 'true' : 'false'
                    );

                    passwordInput.focus();
                });
            }

            const form = document.getElementById('loginForm');
            const button = document.getElementById('loginButton');
            const buttonText = document.getElementById('loginButtonText');

            if (form && button && buttonText) {
                form.addEventListener('submit', function (event) {
                    if (! form.checkValidity()) {
                        event.preventDefault();
                        form.reportValidity();
                        return;
                    }

                    button.disabled = true;
                    button.classList.add('is-loading');
                    buttonText.textContent = 'Memeriksa akun...';
                    button.setAttribute('aria-busy', 'true');
                });
            }
        });
    </script>
</body>
</html>
```

## `payload/resources/views/auth/register.blade.php`

```blade
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="color-scheme" content="light">
    <meta name="theme-color" content="#173B66">

    <title>Daftar Akun | SmartVolt</title>

    <link rel="icon" href="{{ asset('favicon.ico') }}">

    <link rel="stylesheet" href="{{ asset('assets/css/smartvolt-auth.css') }}?v=20260730-v5">
</head>

<body class="sv-auth-page">
    <main class="sv-auth-shell">
        <section
            class="sv-auth-showcase"
            aria-label="Informasi SmartVolt"
        >
            <svg
                class="sv-auth-network"
                viewBox="0 0 900 900"
                aria-hidden="true"
                focusable="false"
            >
                <g fill="none" stroke="currentColor" stroke-width="1.5">
                    <path d="M70 130H280L360 210H590L690 110H840"/>
                    <path d="M40 390H190L290 290H470L560 380H820"/>
                    <path d="M110 680H300L400 580H620L720 680H860"/>
                    <path d="M240 80V260L160 340V520"/>
                    <path d="M650 80V250L740 340V520L650 610V820"/>
                    <path d="M470 210V470L390 550V790"/>
                </g>

                <g fill="currentColor">
                    <circle cx="70" cy="130" r="5"/>
                    <circle cx="360" cy="210" r="5"/>
                    <circle cx="690" cy="110" r="5"/>
                    <circle cx="190" cy="390" r="5"/>
                    <circle cx="470" cy="290" r="5"/>
                    <circle cx="560" cy="380" r="5"/>
                    <circle cx="300" cy="680" r="5"/>
                    <circle cx="620" cy="580" r="5"/>
                    <circle cx="740" cy="340" r="5"/>
                    <circle cx="390" cy="550" r="5"/>
                </g>
            </svg>

            <div class="sv-auth-showcase-content">
                <a
                    href="{{ url('/') }}"
                    class="sv-auth-brand"
                    aria-label="SmartVolt"
                >
                    <span class="sv-auth-brand-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24">
                            <path
                                d="M13.2 2.5 5.8 13h5.1l-.7 8.5L18.4 10h-5.1l-.1-7.5Z"
                                fill="currentColor"
                            />
                        </svg>
                    </span>

                    <span>
                        <strong>SmartVolt</strong>
                        <small>Calm Energy Control</small>
                    </span>
                </a>

                <div class="sv-auth-copy">
                    <p class="sv-auth-eyebrow">
                        Monitoring dan kontrol energi berbasis IoT
                    </p>

                    <h1>
                        Kendalikan energi rumah dengan lebih tenang.
                    </h1>

                    <p class="sv-auth-description">
                        Pantau daya, energi, estimasi biaya, dan perangkat
                        listrik melalui satu sistem yang jelas dan mudah
                        dipahami.
                    </p>
                </div>

                <div class="sv-auth-benefits">
                    <article class="sv-auth-benefit">
                        <span class="sv-auth-benefit-icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24">
                                <path d="M4 19V9m5 10V5m5 14v-7m5 7V3"/>
                            </svg>
                        </span>

                        <div>
                            <h2>Data listrik yang mudah dibaca</h2>
                            <p>
                                Lihat tegangan, arus, daya, energi, dan
                                perubahan penggunaan secara berkala.
                            </p>
                        </div>
                    </article>

                    <article class="sv-auth-benefit">
                        <span class="sv-auth-benefit-icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24">
                                <rect x="5" y="3" width="14" height="18" rx="3"/>
                                <path d="M9 8h6M9 12h6M9 16h3"/>
                            </svg>
                        </span>

                        <div>
                            <h2>Estimasi biaya yang transparan</h2>
                            <p>
                                Pemakaian kWh dihitung berdasarkan data meter
                                dan dikalikan dengan tarif listrik.
                            </p>
                        </div>
                    </article>

                    <article class="sv-auth-benefit">
                        <span class="sv-auth-benefit-icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24">
                                <path d="M8 12h8"/>
                                <rect x="3" y="7" width="6" height="10" rx="2"/>
                                <rect x="15" y="7" width="6" height="10" rx="2"/>
                            </svg>
                        </span>

                        <div>
                            <h2>Kontrol perangkat per ruangan</h2>
                            <p>
                                Nyalakan atau matikan perangkat melalui relay
                                ketika ESP32 dan MQTT terhubung.
                            </p>
                        </div>
                    </article>
                </div>

                <div class="sv-auth-flow" aria-label="Alur data SmartVolt">
                    <div class="sv-auth-flow-label">Alur sistem</div>

                    <div class="sv-auth-flow-items">
                        <span>
                            <strong>PZEM</strong>
                            <small>Membaca listrik</small>
                        </span>

                        <i aria-hidden="true">→</i>

                        <span>
                            <strong>ESP32</strong>
                            <small>Mengirim data</small>
                        </span>

                        <i aria-hidden="true">→</i>

                        <span>
                            <strong>SmartVolt</strong>
                            <small>Menampilkan hasil</small>
                        </span>
                    </div>
                </div>
            </div>
        </section>

        <section class="sv-auth-panel">
            <div class="sv-auth-panel-inner">
                <div class="sv-auth-mobile-brand">
                    <span class="sv-auth-brand-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24">
                            <path
                                d="M13.2 2.5 5.8 13h5.1l-.7 8.5L18.4 10h-5.1l-.1-7.5Z"
                                fill="currentColor"
                            />
                        </svg>
                    </span>

                    <span>
                        <strong>SmartVolt</strong>
                        <small>Monitoring energi rumah</small>
                    </span>
                </div>

                <div class="sv-auth-heading">
                    <span class="sv-auth-status-chip">
                        <span aria-hidden="true"></span>
                        Akses aman SmartVolt
                    </span>

                    <h2>Buat akun baru</h2>

                    <p>
                        Daftar untuk mulai memantau dan mengontrol listrik
                        rumah Anda.
                    </p>
                </div>

                @if (session('status'))
                    <div
                        class="sv-auth-alert sv-auth-alert-success"
                        role="status"
                        aria-live="polite"
                    >
                        <span aria-hidden="true">
                            <svg viewBox="0 0 24 24">
                                <path d="m5 12 4 4L19 6"/>
                            </svg>
                        </span>

                        <div>{{ session('status') }}</div>
                    </div>
                @endif

                @if ($errors->any())
                    <div
                        class="sv-auth-alert sv-auth-alert-error"
                        role="alert"
                        aria-live="assertive"
                    >
                        <span aria-hidden="true">
                            <svg viewBox="0 0 24 24">
                                <circle cx="12" cy="12" r="9"/>
                                <path d="M12 7v6m0 4h.01"/>
                            </svg>
                        </span>

                        <div>
                            <strong>Periksa kembali data yang dimasukkan.</strong>
                            <p>{{ $errors->first() }}</p>
                        </div>
                    </div>
                @endif

                <form
                    id="registerForm"
                    class="sv-auth-form"
                    action="{{ route('register.process') }}"
                    method="POST"
                    novalidate
                >
                    @csrf

                    <div class="sv-auth-field">
                        <label for="name">Nama lengkap</label>

                        <div
                            class="sv-auth-input-group
                                @error('name') is-invalid @enderror"
                        >
                            <span class="sv-auth-input-icon" aria-hidden="true">
                                <svg viewBox="0 0 24 24">
                                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                                    <circle cx="12" cy="7" r="4"/>
                                </svg>
                            </span>

                            <input
                                type="text"
                                id="name"
                                name="name"
                                value="{{ old('name') }}"
                                placeholder="Nama lengkap Anda"
                                autocomplete="name"
                                aria-describedby="nameHelp nameError"
                                @error('name') aria-invalid="true" @enderror
                                required
                                maxlength="100"
                                autofocus
                            >
                        </div>

                        @error('name')
                            <p
                                id="nameError"
                                class="sv-auth-field-error"
                            >
                                {{ $message }}
                            </p>
                        @else
                            <p
                                id="nameHelp"
                                class="sv-auth-field-help"
                            >
                                Masukkan nama lengkap Anda.
                            </p>
                        @enderror
                    </div>

                    <div class="sv-auth-field">
                        <label for="email">Alamat email</label>

                        <div
                            class="sv-auth-input-group
                                @error('email') is-invalid @enderror"
                        >
                            <span class="sv-auth-input-icon" aria-hidden="true">
                                <svg viewBox="0 0 24 24">
                                    <rect x="3" y="5" width="18" height="14" rx="3"/>
                                    <path d="m5 8 7 5 7-5"/>
                                </svg>
                            </span>

                            <input
                                type="email"
                                id="email"
                                name="email"
                                value="{{ old('email') }}"
                                placeholder="nama@email.com"
                                autocomplete="email"
                                inputmode="email"
                                aria-describedby="emailHelp emailError"
                                @error('email') aria-invalid="true" @enderror
                                required
                            >
                        </div>

                        @error('email')
                            <p
                                id="emailError"
                                class="sv-auth-field-error"
                            >
                                {{ $message }}
                            </p>
                        @else
                            <p
                                id="emailHelp"
                                class="sv-auth-field-help"
                            >
                                Gunakan email aktif untuk menerima notifikasi
                                dan tautan reset password.
                            </p>
                        @enderror
                    </div>

                    <div class="sv-auth-field">
                        <label for="password">Kata sandi</label>

                        <div
                            class="sv-auth-input-group
                                @error('password') is-invalid @enderror"
                        >
                            <span class="sv-auth-input-icon" aria-hidden="true">
                                <svg viewBox="0 0 24 24">
                                    <rect x="5" y="10" width="14" height="10" rx="3"/>
                                    <path d="M8 10V7a4 4 0 0 1 8 0v3"/>
                                </svg>
                            </span>

                            <input
                                type="password"
                                id="password"
                                name="password"
                                placeholder="Minimal 8 karakter"
                                autocomplete="new-password"
                                aria-describedby="passwordHelp passwordError"
                                @error('password') aria-invalid="true" @enderror
                                required
                                minlength="8"
                            >

                            <button
                                type="button"
                                id="togglePassword"
                                class="sv-auth-password-toggle"
                                aria-label="Tampilkan kata sandi"
                                aria-pressed="false"
                            >
                                <svg
                                    class="sv-auth-eye-show"
                                    viewBox="0 0 24 24"
                                    aria-hidden="true"
                                >
                                    <path d="M2.5 12s3.5-6 9.5-6 9.5 6 9.5 6-3.5 6-9.5 6S2.5 12 2.5 12Z"/>
                                    <circle cx="12" cy="12" r="2.5"/>
                                </svg>

                                <svg
                                    class="sv-auth-eye-hide"
                                    viewBox="0 0 24 24"
                                    aria-hidden="true"
                                >
                                    <path d="m4 4 16 16"/>
                                    <path d="M10.6 6.2A10 10 0 0 1 12 6c6 0 9.5 6 9.5 6a16 16 0 0 1-2.4 3.1M6.2 7.3A15.7 15.7 0 0 0 2.5 12S6 18 12 18a9.8 9.8 0 0 0 2.1-.2"/>
                                </svg>
                            </button>
                        </div>

                        <div class="sv-auth-password-strength" id="passwordStrength">
                            <div class="sv-auth-strength-bar">
                                <span data-strength="1"></span>
                                <span data-strength="2"></span>
                                <span data-strength="3"></span>
                                <span data-strength="4"></span>
                            </div>
                            <span class="sv-auth-strength-text" id="strengthText">
                                Minimal 6 karakter
                            </span>
                        </div>

                        @error('password')
                            <p
                                id="passwordError"
                                class="sv-auth-field-error"
                            >
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    <div class="sv-auth-field">
                        <label for="password_confirmation">Konfirmasi kata sandi</label>

                        <div
                            class="sv-auth-input-group
                                @error('password') is-invalid @enderror"
                        >
                            <span class="sv-auth-input-icon" aria-hidden="true">
                                <svg viewBox="0 0 24 24">
                                    <rect x="5" y="10" width="14" height="10" rx="3"/>
                                    <path d="M8 10V7a4 4 0 0 1 8 0v3"/>
                                </svg>
                            </span>

                            <input
                                type="password"
                                id="password_confirmation"
                                name="password_confirmation"
                                placeholder="Ulangi kata sandi"
                                autocomplete="new-password"
                                aria-describedby="passwordConfirmationHelp"
                                required
                                minlength="8"
                            >

                            <button
                                type="button"
                                id="togglePasswordConfirm"
                                class="sv-auth-password-toggle"
                                aria-label="Tampilkan konfirmasi kata sandi"
                                aria-pressed="false"
                            >
                                <svg
                                    class="sv-auth-eye-show"
                                    viewBox="0 0 24 24"
                                    aria-hidden="true"
                                >
                                    <path d="M2.5 12s3.5-6 9.5-6 9.5 6 9.5 6-3.5 6-9.5 6S2.5 12 2.5 12Z"/>
                                    <circle cx="12" cy="12" r="2.5"/>
                                </svg>

                                <svg
                                    class="sv-auth-eye-hide"
                                    viewBox="0 0 24 24"
                                    aria-hidden="true"
                                >
                                    <path d="m4 4 16 16"/>
                                    <path d="M10.6 6.2A10 10 0 0 1 12 6c6 0 9.5 6 9.5 6a16 16 0 0 1-2.4 3.1M6.2 7.3A15.7 15.7 0 0 0 2.5 12S6 18 12 18a9.8 9.8 0 0 0 2.1-.2"/>
                                </svg>
                            </button>
                        </div>

                        <p
                            id="passwordConfirmationHelp"
                            class="sv-auth-field-help"
                        >
                            Ketik ulang kata sandi yang sama.
                        </p>
                    </div>

                    <button
                        type="submit"
                        id="registerButton"
                        class="sv-auth-submit"
                    >
                        <span
                            class="sv-auth-button-spinner"
                            aria-hidden="true"
                        ></span>

                        <span id="registerButtonText">Daftar Akun</span>
                    </button>
                </form>

                <p class="sv-auth-footer">
                    Sudah memiliki akun?
                    <a
                        href="{{ route('login') }}"
                        class="sv-auth-link"
                    >
                        Masuk
                    </a>
                </p>

                <div class="sv-auth-security-note">
                    <svg viewBox="0 0 24 24" aria-hidden="true">
                        <path d="M12 3 5 6v5c0 4.6 2.7 8 7 10 4.3-2 7-5.4 7-10V6l-7-3Z"/>
                        <path d="m9 12 2 2 4-5"/>
                    </svg>

                    <p>
                        Data Anda dilindungi dan tidak akan dibagikan kepada
                        pihak ketiga tanpa izin Anda.
                    </p>
                </div>
            </div>
        </section>
    </main>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // =========================================================
            // Toggle password visibility
            // =========================================================

            const setupToggle = function (inputId, toggleId) {
                const input = document.getElementById(inputId);
                const toggle = document.getElementById(toggleId);

                if (! input || ! toggle) {
                    return;
                }

                toggle.addEventListener('click', function () {
                    const shouldShow = input.type === 'password';

                    input.type = shouldShow ? 'text' : 'password';
                    toggle.classList.toggle('is-visible', shouldShow);
                    toggle.setAttribute(
                        'aria-label',
                        shouldShow
                            ? 'Sembunyikan kata sandi'
                            : 'Tampilkan kata sandi'
                    );
                    toggle.setAttribute(
                        'aria-pressed',
                        shouldShow ? 'true' : 'false'
                    );

                    input.focus();
                });
            };

            setupToggle('password', 'togglePassword');
            setupToggle('password_confirmation', 'togglePasswordConfirm');

            // =========================================================
            // Password strength indicator
            // =========================================================

            const passwordInput = document.getElementById('password');
            const strengthBars = document.querySelectorAll(
                '#passwordStrength [data-strength]'
            );
            const strengthText = document.getElementById('strengthText');

            if (passwordInput && strengthBars.length && strengthText) {
                passwordInput.addEventListener('input', function () {
                    const value = passwordInput.value;
                    const strength = calculateStrength(value);

                    strengthBars.forEach(function (bar, index) {
                        const level = parseInt(
                            bar.getAttribute('data-strength'),
                            10
                        );

                        bar.classList.remove(
                            'is-active',
                            'weak',
                            'medium',
                            'strong'
                        );

                        if (level <= strength.level) {
                            bar.classList.add(
                                'is-active',
                                strength.class
                            );
                        }
                    });

                    strengthText.textContent = strength.label;
                    strengthText.className =
                        'sv-auth-strength-text ' + strength.class;
                });
            }

            function calculateStrength (password) {
                if (! password) {
                    return {
                        level: 0,
                        class: '',
                        label: 'Minimal 8 karakter'
                    };
                }

                let score = 0;

                if (password.length >= 6) {
                    score += 1;
                }

                if (password.length >= 10) {
                    score += 1;
                }

                if (/[A-Z]/.test(password) && /[a-z]/.test(password)) {
                    score += 1;
                }

                if (/\d/.test(password)) {
                    score += 1;
                }

                if (/[^A-Za-z0-9]/.test(password)) {
                    score += 1;
                }

                if (score <= 2) {
                    return {
                        level: 1,
                        class: 'is-weak',
                        label: 'Lemah — tambahkan variasi karakter'
                    };
                }

                if (score <= 3) {
                    return {
                        level: 2,
                        class: 'is-medium',
                        label: 'Sedang — tambahkan kombinasi huruf besar, angka, atau simbol'
                    };
                }

                if (score <= 4) {
                    return {
                        level: 3,
                        class: 'is-strong',
                        label: 'Kuat — kata sandi yang baik'
                    };
                }

                return {
                    level: 4,
                    class: 'is-strong',
                    label: 'Sangat kuat'
                };
            }

            // =========================================================
            // Submit handling
            // =========================================================

            const form = document.getElementById('registerForm');
            const button = document.getElementById('registerButton');
            const buttonText = document.getElementById('registerButtonText');

            if (form && button && buttonText) {
                form.addEventListener('submit', function (event) {
                    if (! form.checkValidity()) {
                        event.preventDefault();
                        form.reportValidity();
                        return;
                    }

                    button.disabled = true;
                    button.classList.add('is-loading');
                    buttonText.textContent = 'Mendaftarkan akun...';
                    button.setAttribute('aria-busy', 'true');
                });
            }
        });
    </script>
</body>
</html>
```

## `payload/resources/views/auth/forgot-password.blade.php`

```blade
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="color-scheme" content="light">
    <meta name="theme-color" content="#173B66">

    <title>Lupa Kata Sandi | SmartVolt</title>

    <link rel="icon" href="{{ asset('favicon.ico') }}">

    <link rel="stylesheet" href="{{ asset('assets/css/smartvolt-auth.css') }}?v=20260730-v5">
</head>

<body class="sv-auth-page">
    <main class="sv-auth-shell">
        <section
            class="sv-auth-showcase"
            aria-label="Informasi SmartVolt"
        >
            <svg
                class="sv-auth-network"
                viewBox="0 0 900 900"
                aria-hidden="true"
                focusable="false"
            >
                <g fill="none" stroke="currentColor" stroke-width="1.5">
                    <path d="M70 130H280L360 210H590L690 110H840"/>
                    <path d="M40 390H190L290 290H470L560 380H820"/>
                    <path d="M110 680H300L400 580H620L720 680H860"/>
                    <path d="M240 80V260L160 340V520"/>
                    <path d="M650 80V250L740 340V520L650 610V820"/>
                    <path d="M470 210V470L390 550V790"/>
                </g>

                <g fill="currentColor">
                    <circle cx="70" cy="130" r="5"/>
                    <circle cx="360" cy="210" r="5"/>
                    <circle cx="690" cy="110" r="5"/>
                    <circle cx="190" cy="390" r="5"/>
                    <circle cx="470" cy="290" r="5"/>
                    <circle cx="560" cy="380" r="5"/>
                    <circle cx="300" cy="680" r="5"/>
                    <circle cx="620" cy="580" r="5"/>
                    <circle cx="740" cy="340" r="5"/>
                    <circle cx="390" cy="550" r="5"/>
                </g>
            </svg>

            <div class="sv-auth-showcase-content">
                <a
                    href="{{ url('/') }}"
                    class="sv-auth-brand"
                    aria-label="SmartVolt"
                >
                    <span class="sv-auth-brand-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24">
                            <path
                                d="M13.2 2.5 5.8 13h5.1l-.7 8.5L18.4 10h-5.1l-.1-7.5Z"
                                fill="currentColor"
                            />
                        </svg>
                    </span>

                    <span>
                        <strong>SmartVolt</strong>
                        <small>Calm Energy Control</small>
                    </span>
                </a>

                <div class="sv-auth-copy">
                    <p class="sv-auth-eyebrow">
                        Monitoring dan kontrol energi berbasis IoT
                    </p>

                    <h1>
                        Kendalikan energi rumah dengan lebih tenang.
                    </h1>

                    <p class="sv-auth-description">
                        Pantau daya, energi, estimasi biaya, dan perangkat
                        listrik melalui satu sistem yang jelas dan mudah
                        dipahami.
                    </p>
                </div>

                <div class="sv-auth-benefits">
                    <article class="sv-auth-benefit">
                        <span class="sv-auth-benefit-icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24">
                                <path d="M4 19V9m5 10V5m5 14v-7m5 7V3"/>
                            </svg>
                        </span>

                        <div>
                            <h2>Data listrik yang mudah dibaca</h2>
                            <p>
                                Lihat tegangan, arus, daya, energi, dan
                                perubahan penggunaan secara berkala.
                            </p>
                        </div>
                    </article>

                    <article class="sv-auth-benefit">
                        <span class="sv-auth-benefit-icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24">
                                <rect x="5" y="3" width="14" height="18" rx="3"/>
                                <path d="M9 8h6M9 12h6M9 16h3"/>
                            </svg>
                        </span>

                        <div>
                            <h2>Estimasi biaya yang transparan</h2>
                            <p>
                                Pemakaian kWh dihitung berdasarkan data meter
                                dan dikalikan dengan tarif listrik.
                            </p>
                        </div>
                    </article>

                    <article class="sv-auth-benefit">
                        <span class="sv-auth-benefit-icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24">
                                <path d="M8 12h8"/>
                                <rect x="3" y="7" width="6" height="10" rx="2"/>
                                <rect x="15" y="7" width="6" height="10" rx="2"/>
                            </svg>
                        </span>

                        <div>
                            <h2>Kontrol perangkat per ruangan</h2>
                            <p>
                                Nyalakan atau matikan perangkat melalui relay
                                ketika ESP32 dan MQTT terhubung.
                            </p>
                        </div>
                    </article>
                </div>

                <div class="sv-auth-flow" aria-label="Alur data SmartVolt">
                    <div class="sv-auth-flow-label">Alur sistem</div>

                    <div class="sv-auth-flow-items">
                        <span>
                            <strong>PZEM</strong>
                            <small>Membaca listrik</small>
                        </span>

                        <i aria-hidden="true">→</i>

                        <span>
                            <strong>ESP32</strong>
                            <small>Mengirim data</small>
                        </span>

                        <i aria-hidden="true">→</i>

                        <span>
                            <strong>SmartVolt</strong>
                            <small>Menampilkan hasil</small>
                        </span>
                    </div>
                </div>
            </div>
        </section>

        <section class="sv-auth-panel">
            <div class="sv-auth-panel-inner">
                <div class="sv-auth-mobile-brand">
                    <span class="sv-auth-brand-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24">
                            <path
                                d="M13.2 2.5 5.8 13h5.1l-.7 8.5L18.4 10h-5.1l-.1-7.5Z"
                                fill="currentColor"
                            />
                        </svg>
                    </span>

                    <span>
                        <strong>SmartVolt</strong>
                        <small>Monitoring energi rumah</small>
                    </span>
                </div>

                <div class="sv-auth-heading">
                    <span class="sv-auth-status-chip">
                        <span aria-hidden="true"></span>
                        Akses aman SmartVolt
                    </span>

                    <h2>Lupa kata sandi</h2>

                    <p>
                        Masukkan alamat email akun SmartVolt. Tautan
                        pengaturan ulang akan dikirim ke email tersebut.
                    </p>
                </div>

                @if (session('status'))
                    <div
                        class="sv-auth-alert sv-auth-alert-success"
                        role="status"
                        aria-live="polite"
                    >
                        <span aria-hidden="true">
                            <svg viewBox="0 0 24 24">
                                <path d="m5 12 4 4L19 6"/>
                            </svg>
                        </span>

                        <div>{{ session('status') }}</div>
                    </div>
                @endif

                @if ($errors->any())
                    <div
                        class="sv-auth-alert sv-auth-alert-error"
                        role="alert"
                        aria-live="assertive"
                    >
                        <span aria-hidden="true">
                            <svg viewBox="0 0 24 24">
                                <circle cx="12" cy="12" r="9"/>
                                <path d="M12 7v6m0 4h.01"/>
                            </svg>
                        </span>

                        <div>
                            <strong>Periksa kembali data yang dimasukkan.</strong>
                            <p>{{ $errors->first() }}</p>
                        </div>
                    </div>
                @endif

                <form
                    id="forgotForm"
                    class="sv-auth-form"
                    action="{{ route('password.email') }}"
                    method="POST"
                    novalidate
                >
                    @csrf

                    <div class="sv-auth-field">
                        <label for="forgot_email">Alamat email</label>

                        <div
                            class="sv-auth-input-group
                                @error('email') is-invalid @enderror"
                        >
                            <span class="sv-auth-input-icon" aria-hidden="true">
                                <svg viewBox="0 0 24 24">
                                    <rect x="3" y="5" width="18" height="14" rx="3"/>
                                    <path d="m5 8 7 5 7-5"/>
                                </svg>
                            </span>

                            <input
                                type="email"
                                id="forgot_email"
                                name="email"
                                value="{{ old('email') }}"
                                placeholder="nama@email.com"
                                autocomplete="email"
                                inputmode="email"
                                aria-describedby="forgotEmailHelp forgotEmailError"
                                @error('email') aria-invalid="true" @enderror
                                required
                                autofocus
                            >
                        </div>

                        @error('email')
                            <p
                                id="forgotEmailError"
                                class="sv-auth-field-error"
                            >
                                {{ $message }}
                            </p>
                        @else
                            <p
                                id="forgotEmailHelp"
                                class="sv-auth-field-help"
                            >
                                Pastikan email sesuai dengan akun yang
                                terdaftar.
                            </p>
                        @enderror
                    </div>

                    <button
                        type="submit"
                        id="forgotButton"
                        class="sv-auth-submit"
                    >
                        <span
                            class="sv-auth-button-spinner"
                            aria-hidden="true"
                        ></span>

                        <span id="forgotButtonText">
                            Kirim tautan pengaturan ulang
                        </span>
                    </button>
                </form>

                <p class="sv-auth-footer">
                    <a
                        href="{{ route('login') }}"
                        class="sv-auth-link sv-auth-link-back"
                    >
                        <span aria-hidden="true">←</span>
                        Kembali ke halaman masuk
                    </a>
                </p>

                <div class="sv-auth-security-note">
                    <svg viewBox="0 0 24 24" aria-hidden="true">
                        <path d="M12 3 5 6v5c0 4.6 2.7 8 7 10 4.3-2 7-5.4 7-10V6l-7-3Z"/>
                        <path d="m9 12 2 2 4-5"/>
                    </svg>

                    <p>
                        Akses akun dilindungi oleh sesi Laravel dan token
                        keamanan formulir.
                    </p>
                </div>
            </div>
        </section>
    </main>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const form = document.getElementById('forgotForm');
            const button = document.getElementById('forgotButton');
            const buttonText = document.getElementById('forgotButtonText');

            if (form && button && buttonText) {
                form.addEventListener('submit', function (event) {
                    if (! form.checkValidity()) {
                        event.preventDefault();
                        form.reportValidity();
                        return;
                    }

                    button.disabled = true;
                    button.classList.add('is-loading');
                    buttonText.textContent = 'Mengirim tautan...';
                    button.setAttribute('aria-busy', 'true');
                });
            }
        });
    </script>
</body>
</html>
```

## `payload/resources/views/auth/reset_password.blade.php`

```blade
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="color-scheme" content="light">
    <meta name="theme-color" content="#173B66">

    <title>Atur Ulang Kata Sandi | SmartVolt</title>

    <link rel="icon" href="{{ asset('favicon.ico') }}">

    <link rel="stylesheet" href="{{ asset('assets/css/smartvolt-auth.css') }}?v=20260730-v5">
</head>

<body class="sv-auth-page">
    <main class="sv-auth-shell">
        <section
            class="sv-auth-showcase"
            aria-label="Informasi SmartVolt"
        >
            <svg
                class="sv-auth-network"
                viewBox="0 0 900 900"
                aria-hidden="true"
                focusable="false"
            >
                <g fill="none" stroke="currentColor" stroke-width="1.5">
                    <path d="M70 130H280L360 210H590L690 110H840"/>
                    <path d="M40 390H190L290 290H470L560 380H820"/>
                    <path d="M110 680H300L400 580H620L720 680H860"/>
                    <path d="M240 80V260L160 340V520"/>
                    <path d="M650 80V250L740 340V520L650 610V820"/>
                    <path d="M470 210V470L390 550V790"/>
                </g>

                <g fill="currentColor">
                    <circle cx="70" cy="130" r="5"/>
                    <circle cx="360" cy="210" r="5"/>
                    <circle cx="690" cy="110" r="5"/>
                    <circle cx="190" cy="390" r="5"/>
                    <circle cx="470" cy="290" r="5"/>
                    <circle cx="560" cy="380" r="5"/>
                    <circle cx="300" cy="680" r="5"/>
                    <circle cx="620" cy="580" r="5"/>
                    <circle cx="740" cy="340" r="5"/>
                    <circle cx="390" cy="550" r="5"/>
                </g>
            </svg>

            <div class="sv-auth-showcase-content">
                <a
                    href="{{ url('/') }}"
                    class="sv-auth-brand"
                    aria-label="SmartVolt"
                >
                    <span class="sv-auth-brand-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24">
                            <path
                                d="M13.2 2.5 5.8 13h5.1l-.7 8.5L18.4 10h-5.1l-.1-7.5Z"
                                fill="currentColor"
                            />
                        </svg>
                    </span>

                    <span>
                        <strong>SmartVolt</strong>
                        <small>Calm Energy Control</small>
                    </span>
                </a>

                <div class="sv-auth-copy">
                    <p class="sv-auth-eyebrow">
                        Monitoring dan kontrol energi berbasis IoT
                    </p>

                    <h1>
                        Kendalikan energi rumah dengan lebih tenang.
                    </h1>

                    <p class="sv-auth-description">
                        Pantau daya, energi, estimasi biaya, dan perangkat
                        listrik melalui satu sistem yang jelas dan mudah
                        dipahami.
                    </p>
                </div>

                <div class="sv-auth-benefits">
                    <article class="sv-auth-benefit">
                        <span class="sv-auth-benefit-icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24">
                                <path d="M4 19V9m5 10V5m5 14v-7m5 7V3"/>
                            </svg>
                        </span>

                        <div>
                            <h2>Data listrik yang mudah dibaca</h2>
                            <p>
                                Lihat tegangan, arus, daya, energi, dan
                                perubahan penggunaan secara berkala.
                            </p>
                        </div>
                    </article>

                    <article class="sv-auth-benefit">
                        <span class="sv-auth-benefit-icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24">
                                <rect x="5" y="3" width="14" height="18" rx="3"/>
                                <path d="M9 8h6M9 12h6M9 16h3"/>
                            </svg>
                        </span>

                        <div>
                            <h2>Estimasi biaya yang transparan</h2>
                            <p>
                                Pemakaian kWh dihitung berdasarkan data meter
                                dan dikalikan dengan tarif listrik.
                            </p>
                        </div>
                    </article>

                    <article class="sv-auth-benefit">
                        <span class="sv-auth-benefit-icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24">
                                <path d="M8 12h8"/>
                                <rect x="3" y="7" width="6" height="10" rx="2"/>
                                <rect x="15" y="7" width="6" height="10" rx="2"/>
                            </svg>
                        </span>

                        <div>
                            <h2>Kontrol perangkat per ruangan</h2>
                            <p>
                                Nyalakan atau matikan perangkat melalui relay
                                ketika ESP32 dan MQTT terhubung.
                            </p>
                        </div>
                    </article>
                </div>

                <div class="sv-auth-flow" aria-label="Alur data SmartVolt">
                    <div class="sv-auth-flow-label">Alur sistem</div>

                    <div class="sv-auth-flow-items">
                        <span>
                            <strong>PZEM</strong>
                            <small>Membaca listrik</small>
                        </span>

                        <i aria-hidden="true">→</i>

                        <span>
                            <strong>ESP32</strong>
                            <small>Mengirim data</small>
                        </span>

                        <i aria-hidden="true">→</i>

                        <span>
                            <strong>SmartVolt</strong>
                            <small>Menampilkan hasil</small>
                        </span>
                    </div>
                </div>
            </div>
        </section>

        <section class="sv-auth-panel">
            <div class="sv-auth-panel-inner">
                <div class="sv-auth-mobile-brand">
                    <span class="sv-auth-brand-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24">
                            <path
                                d="M13.2 2.5 5.8 13h5.1l-.7 8.5L18.4 10h-5.1l-.1-7.5Z"
                                fill="currentColor"
                            />
                        </svg>
                    </span>

                    <span>
                        <strong>SmartVolt</strong>
                        <small>Monitoring energi rumah</small>
                    </span>
                </div>

                <div class="sv-auth-heading">
                    <span class="sv-auth-status-chip">
                        <span aria-hidden="true"></span>
                        Akses aman SmartVolt
                    </span>

                    <h2>Buat kata sandi baru</h2>

                    <p>
                        Masukkan kata sandi baru untuk akun SmartVolt Anda.
                    </p>
                </div>

                @if (session('status'))
                    <div
                        class="sv-auth-alert sv-auth-alert-success"
                        role="status"
                        aria-live="polite"
                    >
                        <span aria-hidden="true">
                            <svg viewBox="0 0 24 24">
                                <path d="m5 12 4 4L19 6"/>
                            </svg>
                        </span>

                        <div>{{ session('status') }}</div>
                    </div>
                @endif

                @if ($errors->any())
                    <div
                        class="sv-auth-alert sv-auth-alert-error"
                        role="alert"
                        aria-live="assertive"
                    >
                        <span aria-hidden="true">
                            <svg viewBox="0 0 24 24">
                                <circle cx="12" cy="12" r="9"/>
                                <path d="M12 7v6m0 4h.01"/>
                            </svg>
                        </span>

                        <div>
                            <strong>Periksa kembali data yang dimasukkan.</strong>
                            <p>{{ $errors->first() }}</p>
                        </div>
                    </div>
                @endif

                <form
                    id="resetForm"
                    class="sv-auth-form"
                    action="{{ route('password.update') }}"
                    method="POST"
                    novalidate
                >
                    @csrf

                    <input
                        type="hidden"
                        name="token"
                        value="{{ $token }}"
                    >

                    <div class="sv-auth-field">
                        <label for="email">Alamat email</label>

                        <div
                            class="sv-auth-input-group
                                @error('email') is-invalid @enderror"
                        >
                            <span class="sv-auth-input-icon" aria-hidden="true">
                                <svg viewBox="0 0 24 24">
                                    <rect x="3" y="5" width="18" height="14" rx="3"/>
                                    <path d="m5 8 7 5 7-5"/>
                                </svg>
                            </span>

                            <input
                                type="email"
                                id="email"
                                name="email"
                                value="{{ old('email', $email ?? '') }}"
                                placeholder="nama@email.com"
                                autocomplete="email"
                                inputmode="email"
                                aria-describedby="emailHelp emailError"
                                @error('email') aria-invalid="true" @enderror
                                required
                                readonly
                            >
                        </div>

                        @error('email')
                            <p
                                id="emailError"
                                class="sv-auth-field-error"
                            >
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    <div class="sv-auth-field">
                        <label for="password">Kata sandi baru</label>

                        <div
                            class="sv-auth-input-group
                                @error('password') is-invalid @enderror"
                        >
                            <span class="sv-auth-input-icon" aria-hidden="true">
                                <svg viewBox="0 0 24 24">
                                    <rect x="5" y="10" width="14" height="10" rx="3"/>
                                    <path d="M8 10V7a4 4 0 0 1 8 0v3"/>
                                </svg>
                            </span>

                            <input
                                type="password"
                                id="password"
                                name="password"
                                placeholder="Minimal 8 karakter"
                                autocomplete="new-password"
                                aria-describedby="passwordHelp passwordError"
                                @error('password') aria-invalid="true" @enderror
                                required
                                minlength="8"
                            >

                            <button
                                type="button"
                                id="togglePassword"
                                class="sv-auth-password-toggle"
                                aria-label="Tampilkan kata sandi"
                                aria-pressed="false"
                            >
                                <svg
                                    class="sv-auth-eye-show"
                                    viewBox="0 0 24 24"
                                    aria-hidden="true"
                                >
                                    <path d="M2.5 12s3.5-6 9.5-6 9.5 6 9.5 6-3.5 6-9.5 6S2.5 12 2.5 12Z"/>
                                    <circle cx="12" cy="12" r="2.5"/>
                                </svg>

                                <svg
                                    class="sv-auth-eye-hide"
                                    viewBox="0 0 24 24"
                                    aria-hidden="true"
                                >
                                    <path d="m4 4 16 16"/>
                                    <path d="M10.6 6.2A10 10 0 0 1 12 6c6 0 9.5 6 9.5 6a16 16 0 0 1-2.4 3.1M6.2 7.3A15.7 15.7 0 0 0 2.5 12S6 18 12 18a9.8 9.8 0 0 0 2.1-.2"/>
                                </svg>
                            </button>
                        </div>

                        <div class="sv-auth-password-strength" id="passwordStrength">
                            <div class="sv-auth-strength-bar">
                                <span data-strength="1"></span>
                                <span data-strength="2"></span>
                                <span data-strength="3"></span>
                                <span data-strength="4"></span>
                            </div>
                            <span class="sv-auth-strength-text" id="strengthText">
                                Minimal 8 karakter
                            </span>
                        </div>

                        @error('password')
                            <p
                                id="passwordError"
                                class="sv-auth-field-error"
                            >
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    <div class="sv-auth-field">
                        <label for="password_confirmation">Konfirmasi kata sandi</label>

                        <div
                            class="sv-auth-input-group"
                        >
                            <span class="sv-auth-input-icon" aria-hidden="true">
                                <svg viewBox="0 0 24 24">
                                    <rect x="5" y="10" width="14" height="10" rx="3"/>
                                    <path d="M8 10V7a4 4 0 0 1 8 0v3"/>
                                </svg>
                            </span>

                            <input
                                type="password"
                                id="password_confirmation"
                                name="password_confirmation"
                                placeholder="Ulangi kata sandi baru"
                                autocomplete="new-password"
                                aria-describedby="passwordConfirmationHelp"
                                required
                                minlength="8"
                            >

                            <button
                                type="button"
                                id="togglePasswordConfirm"
                                class="sv-auth-password-toggle"
                                aria-label="Tampilkan konfirmasi kata sandi"
                                aria-pressed="false"
                            >
                                <svg
                                    class="sv-auth-eye-show"
                                    viewBox="0 0 24 24"
                                    aria-hidden="true"
                                >
                                    <path d="M2.5 12s3.5-6 9.5-6 9.5 6 9.5 6-3.5 6-9.5 6S2.5 12 2.5 12Z"/>
                                    <circle cx="12" cy="12" r="2.5"/>
                                </svg>

                                <svg
                                    class="sv-auth-eye-hide"
                                    viewBox="0 0 24 24"
                                    aria-hidden="true"
                                >
                                    <path d="m4 4 16 16"/>
                                    <path d="M10.6 6.2A10 10 0 0 1 12 6c6 0 9.5 6 9.5 6a16 16 0 0 1-2.4 3.1M6.2 7.3A15.7 15.7 0 0 0 2.5 12S6 18 12 18a9.8 9.8 0 0 0 2.1-.2"/>
                                </svg>
                            </button>
                        </div>

                        <p
                            id="passwordConfirmationHelp"
                            class="sv-auth-field-help"
                        >
                            Ketik ulang kata sandi baru yang sama.
                        </p>
                    </div>

                    <button
                        type="submit"
                        id="resetButton"
                        class="sv-auth-submit"
                    >
                        <span
                            class="sv-auth-button-spinner"
                            aria-hidden="true"
                        ></span>

                        <span id="resetButtonText">Simpan Kata Sandi Baru</span>
                    </button>
                </form>

                <p class="sv-auth-footer">
                    <a
                        href="{{ route('login') }}"
                        class="sv-auth-link sv-auth-link-back"
                    >
                        <span aria-hidden="true">←</span>
                        Kembali ke halaman masuk
                    </a>
                </p>

                <div class="sv-auth-security-note">
                    <svg viewBox="0 0 24 24" aria-hidden="true">
                        <path d="M12 3 5 6v5c0 4.6 2.7 8 7 10 4.3-2 7-5.4 7-10V6l-7-3Z"/>
                        <path d="m9 12 2 2 4-5"/>
                    </svg>

                    <p>
                        Akses akun dilindungi oleh sesi Laravel dan token
                        keamanan formulir.
                    </p>
                </div>
            </div>
        </section>
    </main>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // =========================================================
            // Toggle password visibility
            // =========================================================

            const setupToggle = function (inputId, toggleId) {
                const input = document.getElementById(inputId);
                const toggle = document.getElementById(toggleId);

                if (! input || ! toggle) {
                    return;
                }

                toggle.addEventListener('click', function () {
                    const shouldShow = input.type === 'password';

                    input.type = shouldShow ? 'text' : 'password';
                    toggle.classList.toggle('is-visible', shouldShow);
                    toggle.setAttribute(
                        'aria-label',
                        shouldShow
                            ? 'Sembunyikan kata sandi'
                            : 'Tampilkan kata sandi'
                    );
                    toggle.setAttribute(
                        'aria-pressed',
                        shouldShow ? 'true' : 'false'
                    );

                    input.focus();
                });
            };

            setupToggle('password', 'togglePassword');
            setupToggle('password_confirmation', 'togglePasswordConfirm');

            // =========================================================
            // Password strength indicator
            // =========================================================

            const passwordInput = document.getElementById('password');
            const strengthBars = document.querySelectorAll(
                '#passwordStrength [data-strength]'
            );
            const strengthText = document.getElementById('strengthText');

            if (passwordInput && strengthBars.length && strengthText) {
                passwordInput.addEventListener('input', function () {
                    const value = passwordInput.value;
                    const strength = calculateStrength(value);

                    strengthBars.forEach(function (bar, index) {
                        const level = parseInt(
                            bar.getAttribute('data-strength'),
                            10
                        );

                        bar.classList.remove(
                            'is-active',
                            'weak',
                            'medium',
                            'strong'
                        );

                        if (level <= strength.level) {
                            bar.classList.add(
                                'is-active',
                                strength.class
                            );
                        }
                    });

                    strengthText.textContent = strength.label;
                    strengthText.className =
                        'sv-auth-strength-text ' + strength.class;
                });
            }

            function calculateStrength (password) {
                if (! password) {
                    return {
                        level: 0,
                        class: '',
                        label: 'Minimal 8 karakter'
                    };
                }

                let score = 0;

                if (password.length >= 6) {
                    score += 1;
                }

                if (password.length >= 10) {
                    score += 1;
                }

                if (/[A-Z]/.test(password) && /[a-z]/.test(password)) {
                    score += 1;
                }

                if (/\d/.test(password)) {
                    score += 1;
                }

                if (/[^A-Za-z0-9]/.test(password)) {
                    score += 1;
                }

                if (score <= 2) {
                    return {
                        level: 1,
                        class: 'is-weak',
                        label: 'Lemah — tambahkan variasi karakter'
                    };
                }

                if (score <= 3) {
                    return {
                        level: 2,
                        class: 'is-medium',
                        label: 'Sedang — tambahkan kombinasi huruf besar, angka, atau simbol'
                    };
                }

                if (score <= 4) {
                    return {
                        level: 3,
                        class: 'is-strong',
                        label: 'Kuat — kata sandi yang baik'
                    };
                }

                return {
                    level: 4,
                    class: 'is-strong',
                    label: 'Sangat kuat'
                };
            }

            // =========================================================
            // Submit handling
            // =========================================================

            const form = document.getElementById('resetForm');
            const button = document.getElementById('resetButton');
            const buttonText = document.getElementById('resetButtonText');

            if (form && button && buttonText) {
                form.addEventListener('submit', function (event) {
                    if (! form.checkValidity()) {
                        event.preventDefault();
                        form.reportValidity();
                        return;
                    }

                    button.disabled = true;
                    button.classList.add('is-loading');
                    buttonText.textContent = 'Menyimpan kata sandi baru...';
                    button.setAttribute('aria-busy', 'true');
                });
            }
        });
    </script>
</body>
</html>
```

## `payload/public/assets/css/smartvolt-pro.css`

```css
/* =========================================================
   SmartVolt UI V7 — Professional Minimal
   Loaded after smartvolt-app.css to preserve project behavior.
   ========================================================= */

:root {
    --svp-bg: #f6f8fb;
    --svp-surface: #ffffff;
    --svp-surface-soft: #f8fafc;
    --svp-text: #172033;
    --svp-heading: #101828;
    --svp-muted: #667085;
    --svp-muted-2: #98a2b3;
    --svp-border: #e3e8ef;
    --svp-border-strong: #d4dbe5;
    --svp-blue: #2563eb;
    --svp-blue-dark: #1d4ed8;
    --svp-blue-soft: #eef4ff;
    --svp-green: #16803c;
    --svp-green-soft: #edf8f1;
    --svp-amber: #b76e00;
    --svp-amber-soft: #fff7e5;
    --svp-red: #c83c3c;
    --svp-red-soft: #fff0f0;
    --svp-violet: #7457d7;
    --svp-violet-soft: #f4f0ff;
    --svp-cyan: #087f8c;
    --svp-cyan-soft: #eaf8fa;
    --svp-slate: #475467;
    --svp-radius-sm: 10px;
    --svp-radius: 14px;
    --svp-radius-lg: 18px;
    --svp-shadow: 0 1px 2px rgba(16, 24, 40, .03), 0 8px 28px rgba(16, 24, 40, .05);
    --svp-shadow-float: 0 16px 48px rgba(16, 24, 40, .12);
    --svp-content: 1480px;
    --svp-sidebar: 248px;
    --svp-topbar: 76px;
}

body.sv-pro-app {
    color: var(--svp-text);
    background: var(--svp-bg);
    font-size: 14px;
    line-height: 1.55;
}

body.sv-pro-app :focus-visible {
    outline: 3px solid rgba(37, 99, 235, .2);
    outline-offset: 2px;
}

.sv-skip-link {
    position: fixed;
    top: 10px;
    left: 10px;
    z-index: 999;
    padding: 10px 14px;
    border-radius: 9px;
    color: #fff;
    background: var(--svp-blue);
    transform: translateY(-150%);
    transition: transform .15s ease;
}

.sv-skip-link:focus {
    transform: translateY(0);
}

/* Shell */
body.sv-pro-app .sv-app-sidebar {
    width: var(--svp-sidebar);
    border-right: 1px solid var(--svp-border);
    background: rgba(255, 255, 255, .98);
    box-shadow: none;
}

body.sv-pro-app .sv-sidebar-header {
    min-height: var(--svp-topbar);
    padding: 0 20px;
    border-bottom: 1px solid var(--svp-border);
}

body.sv-pro-app .sv-brand {
    gap: 12px;
}

body.sv-pro-app .sv-brand-mark {
    width: 40px;
    height: 40px;
    flex-basis: 40px;
    border-radius: 12px;
    color: #fff;
    background: var(--svp-blue);
    box-shadow: 0 7px 18px rgba(37, 99, 235, .18);
}

body.sv-pro-app .sv-brand-copy strong {
    color: var(--svp-heading);
    font-size: 18px;
    font-weight: 780;
    letter-spacing: -.03em;
}

body.sv-pro-app .sv-brand-copy small {
    margin-top: 1px;
    color: var(--svp-muted);
    font-size: 11px;
    font-weight: 600;
}

body.sv-pro-app .sv-sidebar-nav {
    padding: 22px 14px 16px;
    gap: 5px;
}

body.sv-pro-app .sv-nav-label {
    margin: 0 10px 8px;
    color: var(--svp-muted-2);
    font-size: 10px;
    font-weight: 750;
    letter-spacing: .1em;
}

body.sv-pro-app .sv-nav-label-spaced {
    margin-top: 22px;
}

body.sv-pro-app .sv-nav-link {
    min-height: 46px;
    padding: 8px 11px;
    gap: 11px;
    border: 1px solid transparent;
    border-radius: 11px;
    color: #475467;
    font-size: 13.5px;
    font-weight: 650;
}

body.sv-pro-app .sv-nav-link::before {
    display: none;
}

body.sv-pro-app .sv-nav-link:hover {
    color: var(--svp-heading);
    background: #f7f9fc;
}

body.sv-pro-app .sv-nav-link.is-active {
    color: var(--svp-blue-dark);
    border-color: #dce8ff;
    background: var(--svp-blue-soft);
}

body.sv-pro-app .sv-nav-link-secondary:not(.is-active) {
    color: #596579;
}

.sv-pro-nav-icon {
    display: grid;
    width: 30px;
    height: 30px;
    flex: 0 0 30px;
    place-items: center;
    border-radius: 9px;
    color: #667085;
    background: #f4f6f9;
    transition: color .15s ease, background-color .15s ease;
}

.sv-pro-nav-icon--blue { color: #3974d7; background: #eef4ff; }
.sv-pro-nav-icon--green { color: #2f8b50; background: #eef8f1; }
.sv-pro-nav-icon--violet { color: #765ed0; background: #f4f0ff; }
.sv-pro-nav-icon--slate { color: #596579; background: #eef1f5; }
.sv-pro-nav-icon--amber { color: #b27516; background: #fff7e8; }

.sv-nav-link.is-active .sv-pro-nav-icon--blue,
.sv-nav-link:hover .sv-pro-nav-icon--blue { color: var(--svp-blue); background: #e3edff; }
.sv-nav-link.is-active .sv-pro-nav-icon--green,
.sv-nav-link:hover .sv-pro-nav-icon--green { color: var(--svp-green); background: #e4f5ea; }
.sv-nav-link.is-active .sv-pro-nav-icon--violet,
.sv-nav-link:hover .sv-pro-nav-icon--violet { color: var(--svp-violet); background: #ece6ff; }
.sv-nav-link.is-active .sv-pro-nav-icon--slate,
.sv-nav-link:hover .sv-pro-nav-icon--slate { color: var(--svp-slate); background: #e6ebf0; }
.sv-nav-link.is-active .sv-pro-nav-icon--amber,
.sv-nav-link:hover .sv-pro-nav-icon--amber { color: var(--svp-amber); background: #ffefc9; }

body.sv-pro-app .sv-sidebar-footer-simple {
    margin: 0 14px 16px;
    padding: 12px 13px;
    gap: 9px;
    border: 1px solid var(--svp-border);
    border-radius: 11px;
    color: var(--svp-muted);
    background: var(--svp-surface-soft);
    font-size: 11.5px;
    font-weight: 600;
}

body.sv-pro-app .sv-sidebar-footer-dot {
    width: 8px;
    height: 8px;
    background: #22a447;
    box-shadow: 0 0 0 4px rgba(34, 164, 71, .12);
}

body.sv-pro-app .sv-app-main {
    min-height: 100svh;
    margin-left: var(--svp-sidebar);
}

body.sv-pro-app .sv-app-topbar {
    min-height: var(--svp-topbar);
    border-bottom: 1px solid var(--svp-border);
    background: rgba(255, 255, 255, .94);
    backdrop-filter: blur(14px);
}

body.sv-pro-app .sv-topbar-inner {
    width: min(100%, calc(var(--svp-content) + 56px));
    min-height: var(--svp-topbar);
    margin: 0 auto;
    padding: 0 28px;
}

body.sv-pro-app .sv-topbar-left {
    min-width: 0;
    gap: 12px;
}

body.sv-pro-app .sv-page-title {
    margin: 0;
    color: var(--svp-heading);
    font-size: 24px;
    font-weight: 760;
    letter-spacing: -.035em;
    line-height: 1.2;
}

body.sv-pro-app .sv-page-subtitle {
    margin: 4px 0 0;
    color: var(--svp-muted);
    font-size: 12.5px;
    line-height: 1.35;
}

body.sv-pro-app .sv-topbar-actions {
    gap: 9px;
}

body.sv-pro-app .sv-icon-button {
    width: 40px;
    height: 40px;
    border: 1px solid var(--svp-border);
    border-radius: 11px;
    color: var(--svp-slate);
    background: #fff;
}

body.sv-pro-app .sv-icon-button:hover {
    color: var(--svp-blue);
    border-color: #cddcff;
    background: var(--svp-blue-soft);
}

body.sv-pro-app .sv-profile-trigger {
    min-height: 44px;
    padding: 5px 8px 5px 6px;
    border: 1px solid var(--svp-border);
    border-radius: 12px;
    background: #fff;
}

body.sv-pro-app .sv-avatar {
    width: 32px;
    height: 32px;
    flex-basis: 32px;
    border-radius: 9px;
    color: #fff;
    background: var(--svp-blue);
    font-size: 12px;
}

body.sv-pro-app .sv-profile-copy strong { font-size: 12.5px; }
body.sv-pro-app .sv-profile-copy small { font-size: 10.5px; color: var(--svp-muted); }

body.sv-pro-app .sv-profile-dropdown {
    width: 260px;
    padding: 8px;
    border: 1px solid var(--svp-border);
    border-radius: 14px;
    box-shadow: var(--svp-shadow-float);
}

body.sv-pro-app .sv-page-content {
    width: min(100%, calc(var(--svp-content) + 56px));
    margin: 0 auto;
    padding: 28px 28px 44px;
}

/* Shared surfaces */
body.sv-pro-app .sv-card,
body.sv-pro-app .sv-filter-card,
body.sv-pro-app .sv-settings-card,
body.sv-pro-app .sv-tech-room,
body.sv-pro-app .sv-tech-panel {
    border: 1px solid var(--svp-border);
    border-radius: var(--svp-radius);
    background: var(--svp-surface);
    box-shadow: var(--svp-shadow);
}

body.sv-pro-app .sv-card-header {
    min-height: 68px;
    padding: 18px 20px;
    align-items: center;
    border-bottom: 1px solid var(--svp-border);
}

body.sv-pro-app .sv-card-header h2,
body.sv-pro-app .sv-card-header h3 {
    margin: 0;
    color: var(--svp-heading);
    font-size: 16px;
    font-weight: 730;
    letter-spacing: -.015em;
}

body.sv-pro-app .sv-card-header p {
    margin: 3px 0 0;
    color: var(--svp-muted);
    font-size: 12.5px;
}

body.sv-pro-app .sv-card-body {
    padding: 20px;
}

body.sv-pro-app .sv-card-body-table {
    padding: 0;
}

body.sv-pro-app .sv-text-button {
    color: var(--svp-blue);
    font-size: 12.5px;
    font-weight: 700;
}

body.sv-pro-app .sv-text-button:hover {
    color: var(--svp-blue-dark);
    text-decoration: underline;
    text-underline-offset: 3px;
}

body.sv-pro-app .sv-button {
    min-height: 42px;
    padding: 0 15px;
    border-radius: 10px;
    font-size: 13px;
    font-weight: 700;
}

body.sv-pro-app .sv-button-sm {
    min-height: 36px;
    padding-inline: 12px;
    font-size: 12px;
}

body.sv-pro-app .sv-button-primary {
    border-color: var(--svp-blue);
    background: var(--svp-blue);
    box-shadow: 0 5px 14px rgba(37, 99, 235, .15);
}

body.sv-pro-app .sv-button-primary:hover {
    border-color: var(--svp-blue-dark);
    background: var(--svp-blue-dark);
}

body.sv-pro-app .sv-button-secondary,
body.sv-pro-app .sv-button-ghost {
    color: var(--svp-slate);
    border-color: var(--svp-border-strong);
    background: #fff;
}

body.sv-pro-app .sv-button-secondary:hover,
body.sv-pro-app .sv-button-ghost:hover {
    color: var(--svp-blue);
    border-color: #c8d8ff;
    background: var(--svp-blue-soft);
}

body.sv-pro-app .sv-badge {
    min-height: 28px;
    padding: 4px 9px;
    gap: 6px;
    border-radius: 999px;
    font-size: 11px;
    font-weight: 700;
}

body.sv-pro-app .sv-badge-success { color: #137136; background: #edf8f1; border-color: #cdebd7; }
body.sv-pro-app .sv-badge-warning { color: #986000; background: #fff7e5; border-color: #f3dfad; }
body.sv-pro-app .sv-badge-danger { color: #b23434; background: #fff0f0; border-color: #f4cccc; }
body.sv-pro-app .sv-badge-neutral { color: #596579; background: #f3f5f8; border-color: #e0e5ec; }

body.sv-pro-app .sv-status-dot {
    width: 7px;
    height: 7px;
}

body.sv-pro-app .sv-feature-icon {
    border-radius: 10px;
}

body.sv-pro-app .sv-feature-icon--soft {
    background: #f3f5f8;
}

body.sv-pro-app .sv-feature-icon--blue { color: var(--svp-blue); background: var(--svp-blue-soft); }
body.sv-pro-app .sv-feature-icon--green { color: var(--svp-green); background: var(--svp-green-soft); }
body.sv-pro-app .sv-feature-icon--amber { color: var(--svp-amber); background: var(--svp-amber-soft); }
body.sv-pro-app .sv-feature-icon--violet { color: var(--svp-violet); background: var(--svp-violet-soft); }
body.sv-pro-app .sv-feature-icon--cyan { color: var(--svp-cyan); background: var(--svp-cyan-soft); }
body.sv-pro-app .sv-feature-icon--slate { color: var(--svp-slate); background: #eef1f5; }
body.sv-pro-app .sv-feature-icon--rose { color: #bb4e70; background: #fff0f4; }

.sv-pro-icon-tile {
    display: grid;
    width: 42px;
    height: 42px;
    flex: 0 0 42px;
    place-items: center;
    border-radius: 12px;
}

.sv-pro-icon-tile--sm { width: 34px; height: 34px; flex-basis: 34px; border-radius: 9px; }
.sv-pro-icon-tile--blue { color: var(--svp-blue); background: var(--svp-blue-soft); }
.sv-pro-icon-tile--green { color: var(--svp-green); background: var(--svp-green-soft); }
.sv-pro-icon-tile--amber { color: var(--svp-amber); background: var(--svp-amber-soft); }
.sv-pro-icon-tile--violet { color: var(--svp-violet); background: var(--svp-violet-soft); }
.sv-pro-icon-tile--cyan { color: var(--svp-cyan); background: var(--svp-cyan-soft); }

/* Forms */
body.sv-pro-app .sv-form-label {
    margin-bottom: 7px;
    color: #344054;
    font-size: 12.5px;
    font-weight: 700;
}

body.sv-pro-app .sv-form-control {
    min-height: 44px;
    padding: 10px 12px;
    border: 1px solid var(--svp-border-strong);
    border-radius: 10px;
    color: var(--svp-text);
    background: #fff;
    font-size: 13.5px;
}

body.sv-pro-app .sv-form-control::placeholder { color: #a4adbb; }
body.sv-pro-app .sv-form-control:hover { border-color: #b9c5d5; }
body.sv-pro-app .sv-form-control:focus {
    border-color: #8bb0ff;
    box-shadow: 0 0 0 4px rgba(37, 99, 235, .10);
}

body.sv-pro-app .sv-form-grid-two {
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 16px;
}

body.sv-pro-app .sv-form-grid-three {
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 16px;
}

body.sv-pro-app .sv-checkbox {
    min-height: 42px;
    font-size: 13px;
}

/* Tables */
body.sv-pro-app .sv-table-wrap {
    border-radius: 0 0 var(--svp-radius) var(--svp-radius);
}

body.sv-pro-app .sv-table {
    font-size: 12.5px;
}

body.sv-pro-app .sv-table th {
    height: 46px;
    padding: 0 16px;
    color: #667085;
    background: #f8fafc;
    font-size: 10.5px;
    font-weight: 760;
    letter-spacing: .055em;
    text-transform: uppercase;
}

body.sv-pro-app .sv-table td {
    height: 52px;
    padding: 10px 16px;
    border-top-color: #edf0f4;
    color: #344054;
}

body.sv-pro-app .sv-table tbody tr:hover td {
    background: #fbfcfe;
}

/* Dashboard */
.sv-pro-metrics {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 16px;
    margin-bottom: 16px;
}

.sv-pro-metric {
    position: relative;
    min-width: 0;
    min-height: 158px;
    padding: 18px;
    overflow: hidden;
    border: 1px solid var(--svp-border);
    border-radius: var(--svp-radius);
    background: #fff;
    box-shadow: var(--svp-shadow);
}

.sv-pro-metric::after {
    position: absolute;
    inset: auto 0 0;
    height: 3px;
    content: "";
    background: #dfe5ed;
}

.sv-pro-metric:nth-child(1)::after { background: var(--svp-blue); }
.sv-pro-metric:nth-child(2)::after { background: #2aa75b; }
.sv-pro-metric:nth-child(3)::after { background: #d08a18; }
.sv-pro-metric:nth-child(4)::after { background: #1596a6; }

.sv-pro-metric-head {
    display: flex;
    align-items: center;
    gap: 11px;
}

.sv-pro-metric-label {
    color: #475467;
    font-size: 12.5px;
    font-weight: 700;
}

.sv-pro-metric-value {
    display: flex;
    margin-top: 17px;
    align-items: baseline;
    gap: 6px;
    color: var(--svp-heading);
}

.sv-pro-metric-value strong {
    font-size: clamp(27px, 2vw, 34px);
    font-weight: 760;
    letter-spacing: -.045em;
    line-height: 1;
}

.sv-pro-metric-value small {
    color: var(--svp-muted);
    font-size: 12px;
    font-weight: 650;
}

.sv-pro-metric-value--money small {
    color: var(--svp-heading);
    font-size: 15px;
    font-weight: 730;
}

.sv-pro-metric-note,
.sv-pro-metric-meta {
    margin: 14px 0 0;
    color: var(--svp-muted);
    font-size: 11.5px;
}

.sv-pro-metric-meta {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 8px;
}

.sv-pro-state {
    display: inline-flex;
    align-items: center;
    font-weight: 700;
}

.sv-pro-state.is-success { color: var(--svp-green); }
.sv-pro-state.is-warning { color: var(--svp-amber); }
.sv-pro-state.is-danger { color: var(--svp-red); }

.sv-pro-metric .sv-progress {
    height: 5px;
    margin-top: 10px;
    border-radius: 999px;
    background: #eef1f5;
}

.sv-pro-system-strip {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 0;
    margin-bottom: 18px;
    overflow: hidden;
    border: 1px solid var(--svp-border);
    border-radius: var(--svp-radius);
    background: #fff;
    box-shadow: 0 1px 2px rgba(16, 24, 40, .025);
}

.sv-pro-system-item {
    display: flex;
    min-width: 0;
    min-height: 72px;
    padding: 14px 16px;
    align-items: center;
    gap: 11px;
    border-right: 1px solid var(--svp-border);
}

.sv-pro-system-item:last-child { border-right: 0; }

.sv-pro-system-icon {
    display: grid;
    width: 34px;
    height: 34px;
    flex: 0 0 34px;
    place-items: center;
    border-radius: 10px;
    color: #667085;
    background: #f3f5f8;
}

.sv-pro-system-item.is-success .sv-pro-system-icon { color: var(--svp-green); background: var(--svp-green-soft); }
.sv-pro-system-item.is-warning .sv-pro-system-icon { color: var(--svp-amber); background: var(--svp-amber-soft); }

.sv-pro-system-item > div { min-width: 0; }
.sv-pro-system-item strong,
.sv-pro-system-item span { display: block; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.sv-pro-system-item strong { color: #344054; font-size: 12px; font-weight: 720; }
.sv-pro-system-item > div > span { margin-top: 2px; color: var(--svp-muted); font-size: 10.5px; }

.sv-pro-dashboard-grid {
    display: grid;
    grid-template-columns: minmax(0, 1.65fr) minmax(330px, .85fr);
    gap: 18px;
    margin-bottom: 18px;
}

.sv-pro-chart-card .sv-chart-container { min-height: 330px; }
.sv-pro-control-card .sv-card-body { padding: 12px 16px 16px; }

body.sv-pro-app .sv-chart-summary {
    margin-bottom: 14px;
    gap: 10px;
}

body.sv-pro-app .sv-chart-summary > div {
    padding: 11px 13px;
    border: 1px solid var(--svp-border);
    border-radius: 10px;
    background: var(--svp-surface-soft);
}

body.sv-pro-app .sv-chart-summary span { font-size: 10.5px; }
body.sv-pro-app .sv-chart-summary strong { font-size: 15px; }

body.sv-pro-app .sv-room-control {
    border: 1px solid var(--svp-border);
    border-radius: 11px;
    background: #fff;
}

body.sv-pro-app .sv-room-control + .sv-room-control { margin-top: 9px; }
body.sv-pro-app .sv-room-control > summary { min-height: 58px; padding: 9px 10px; }
body.sv-pro-app .sv-room-devices { padding: 2px 10px 10px; }

.sv-pro-device-icon {
    display: grid;
    width: 32px;
    height: 32px;
    flex: 0 0 32px;
    place-items: center;
    border-radius: 9px;
    color: #667085;
    background: #f2f4f7;
}
.sv-pro-device-icon.is-on { color: var(--svp-green); background: var(--svp-green-soft); }

body.sv-pro-app .sv-device-row {
    min-height: 52px;
    padding: 8px 2px;
    border-top: 1px solid #edf0f4;
}

body.sv-pro-app .sv-device-copy strong { font-size: 12.5px; }
body.sv-pro-app .sv-device-copy span { font-size: 10.5px; }

body.sv-pro-app .sv-device-switch {
    min-width: 76px;
    min-height: 34px;
    border-radius: 9px;
    font-size: 10.5px;
}

.sv-pro-activity-card { margin-top: 0; }

/* Energy history */
body.sv-pro-app .sv-filter-card {
    margin-bottom: 16px;
    box-shadow: 0 1px 2px rgba(16, 24, 40, .025);
}

body.sv-pro-app .sv-filter-card .sv-card-body { padding: 18px; }

body.sv-pro-app .sv-filter-grid-compact {
    grid-template-columns: minmax(240px, 1.4fr) minmax(150px, .75fr) minmax(150px, .75fr) auto;
    gap: 14px;
    align-items: end;
}

body.sv-pro-app .sv-filter-actions {
    display: flex;
    gap: 8px;
}

body.sv-pro-app .sv-filter-footer {
    margin-top: 14px;
    padding-top: 14px;
    border-top: 1px solid var(--svp-border);
}

body.sv-pro-app .sv-quick-filters { gap: 6px; }
body.sv-pro-app .sv-quick-filter {
    min-height: 34px;
    padding: 0 11px;
    border: 1px solid var(--svp-border);
    border-radius: 9px;
    color: #596579;
    background: #fff;
    font-size: 11.5px;
    font-weight: 650;
}
body.sv-pro-app .sv-quick-filter:hover,
body.sv-pro-app .sv-quick-filter.is-active { color: var(--svp-blue); border-color: #c8d8ff; background: var(--svp-blue-soft); }

body.sv-pro-app .sv-history-metrics-three {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 14px;
    margin-bottom: 16px;
}

body.sv-pro-app .sv-history-metric {
    min-height: 92px;
    padding: 16px;
    gap: 12px;
    border: 1px solid var(--svp-border);
    border-radius: var(--svp-radius);
    background: #fff;
    box-shadow: var(--svp-shadow);
}

body.sv-pro-app .sv-history-metric::before { display: none; }
body.sv-pro-app .sv-history-metric span { font-size: 11.5px; }
body.sv-pro-app .sv-history-metric strong { margin-top: 5px; color: var(--svp-heading); font-size: 20px; letter-spacing: -.025em; }

body.sv-pro-app .sv-section-grid-analysis {
    grid-template-columns: minmax(0, 1.65fr) minmax(300px, .75fr);
    gap: 16px;
    margin-bottom: 16px;
}

body.sv-pro-app .sv-history-chart-card .sv-chart-container { min-height: 350px; }
body.sv-pro-app .sv-chart-tabs { padding: 3px; border: 1px solid var(--svp-border); border-radius: 10px; background: #f7f9fc; }
body.sv-pro-app .sv-chart-tab { min-height: 32px; padding: 0 12px; border-radius: 7px; font-size: 11.5px; }
body.sv-pro-app .sv-chart-tab.is-active { color: var(--svp-blue); background: #fff; box-shadow: 0 1px 3px rgba(16,24,40,.08); }

body.sv-pro-app .sv-estimation-highlight {
    padding: 18px;
    border: 1px solid #dce8ff;
    border-radius: 12px;
    background: #f5f8ff;
}
body.sv-pro-app .sv-estimation-highlight strong { font-size: 27px; color: var(--svp-heading); }
body.sv-pro-app .sv-estimation-secondary { margin-top: 14px; }

/* Rooms and devices */
body.sv-pro-app .sv-room-summary-grid-three {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 14px;
    margin-bottom: 16px;
}

body.sv-pro-app .sv-summary-card {
    min-height: 86px;
    padding: 15px 17px;
    border: 1px solid var(--svp-border);
    border-radius: var(--svp-radius);
    background: #fff;
    box-shadow: var(--svp-shadow);
}
body.sv-pro-app .sv-summary-card::before { display: none; }
body.sv-pro-app .sv-summary-copy span { font-size: 11.5px; }
body.sv-pro-app .sv-summary-copy strong { font-size: 22px; }

body.sv-pro-app .sv-room-grid-three {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 16px;
}

body.sv-pro-app .sv-room-card {
    min-width: 0;
    overflow: hidden;
    border: 1px solid var(--svp-border);
    border-radius: var(--svp-radius);
    background: #fff;
    box-shadow: var(--svp-shadow);
}

body.sv-pro-app .sv-room-card::before { display: none; }

body.sv-pro-app .sv-room-card-head {
    min-height: 72px;
    padding: 15px 16px;
    border-bottom: 1px solid var(--svp-border);
}

body.sv-pro-app .sv-room-card-title h3 {
    color: var(--svp-heading);
    font-size: 15px;
    font-weight: 730;
}
body.sv-pro-app .sv-room-card-title p { margin-top: 3px; color: var(--svp-muted); font-size: 11px; }
body.sv-pro-app .sv-room-card-body { padding: 6px 14px 10px; }
body.sv-pro-app .sv-room-card-foot { min-height: 43px; padding: 10px 15px; border-top: 1px solid var(--svp-border); background: #fbfcfe; font-size: 11.5px; }

body.sv-pro-app .sv-device-directory {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 12px;
}

body.sv-pro-app .sv-device-directory-card {
    min-height: 70px;
    padding: 13px;
    border: 1px solid var(--svp-border);
    border-radius: 11px;
    background: #fff;
}

/* Settings */
body.sv-pro-app .sv-settings-layout {
    display: grid;
    grid-template-columns: 230px minmax(0, 1fr);
    gap: 18px;
    align-items: start;
}

body.sv-pro-app .sv-settings-nav {
    position: sticky;
    top: calc(var(--svp-topbar) + 20px);
    padding: 8px;
    border: 1px solid var(--svp-border);
    border-radius: var(--svp-radius);
    background: #fff;
    box-shadow: var(--svp-shadow);
}

body.sv-pro-app .sv-tab-button {
    min-height: 44px;
    padding: 9px 10px;
    border: 1px solid transparent;
    border-radius: 9px;
    color: #596579;
    font-size: 12.5px;
    font-weight: 650;
}
body.sv-pro-app .sv-tab-button:hover { color: var(--svp-heading); background: #f7f9fc; }
body.sv-pro-app .sv-tab-button.is-active { color: var(--svp-blue); border-color: #dce8ff; background: var(--svp-blue-soft); }
body.sv-pro-app .sv-tab-button .sv-feature-icon { width: 30px; height: 30px; }

body.sv-pro-app .sv-settings-panels { min-width: 0; }
body.sv-pro-app .sv-settings-card { overflow: hidden; }
body.sv-pro-app .sv-profile-summary { gap: 14px; }
body.sv-pro-app .sv-profile-avatar-large { width: 58px; height: 58px; border-radius: 16px; background: var(--svp-blue); }

body.sv-pro-app .sv-technician-summary-three {
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 12px;
}

body.sv-pro-app .sv-technician-summary-card {
    min-height: 84px;
    padding: 14px;
    border: 1px solid var(--svp-border);
    border-radius: 11px;
    background: #fff;
}
body.sv-pro-app .sv-technician-summary-card::before { display: none; }

body.sv-pro-app .sv-technician-toolbar {
    padding: 14px 16px;
    border: 1px solid var(--svp-border);
    border-radius: 12px;
    background: #fff;
}

body.sv-pro-app .sv-tech-room-list { display: grid; gap: 12px; }
body.sv-pro-app .sv-tech-room { overflow: hidden; box-shadow: none; }
body.sv-pro-app .sv-tech-room > summary {
    min-height: 62px;
    padding: 12px 15px;
    background: #fff;
}
body.sv-pro-app .sv-tech-room[open] > summary { background: #fbfcfe; border-bottom: 1px solid var(--svp-border); }
body.sv-pro-app .sv-tech-room-body { padding: 16px; }
body.sv-pro-app .sv-tech-two-column,
body.sv-pro-app .sv-tech-inventory-grid {
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 14px;
}

body.sv-pro-app .sv-tech-panel {
    padding: 0;
    box-shadow: none;
}
body.sv-pro-app .sv-tech-panel > header { min-height: 54px; padding: 12px 14px; border-bottom: 1px solid var(--svp-border); }
body.sv-pro-app .sv-tech-panel > header h4 { font-size: 13.5px; }
body.sv-pro-app .sv-tech-item-list { padding: 10px; }
body.sv-pro-app .sv-tech-item { padding: 12px; border: 1px solid var(--svp-border); border-radius: 10px; background: #fff; }
body.sv-pro-app .sv-tech-item + .sv-tech-item { margin-top: 8px; }
body.sv-pro-app .sv-tech-item-copy strong { font-size: 12.5px; }
body.sv-pro-app .sv-tech-item-copy span { font-size: 10.5px; }
body.sv-pro-app .sv-tech-actions-row { gap: 8px; }
body.sv-pro-app .sv-tech-edit > summary { min-height: 34px; padding: 0 10px; border: 1px solid var(--svp-border-strong); border-radius: 8px; font-size: 11.5px; }
body.sv-pro-app .sv-tech-edit-body { padding: 13px; border: 1px solid var(--svp-border); border-radius: 10px; background: #f8fafc; }

body.sv-pro-app .sv-dialog {
    width: min(560px, calc(100vw - 28px));
    border: 1px solid var(--svp-border);
    border-radius: 16px;
    box-shadow: var(--svp-shadow-float);
}

/* Empty states */
body.sv-pro-app .sv-empty-state {
    min-height: 220px;
    padding: 28px 20px;
}
body.sv-pro-app .sv-empty-state-compact { min-height: 128px; padding: 18px; }
body.sv-pro-app .sv-empty-state h3 { margin: 10px 0 0; font-size: 14px; }
body.sv-pro-app .sv-empty-state p { max-width: 360px; margin: 5px auto 0; color: var(--svp-muted); font-size: 12px; }

/* Alerts */
body.sv-pro-app .sv-alert {
    margin-bottom: 16px;
    padding: 12px 14px;
    border-radius: 11px;
    font-size: 12.5px;
}

/* Bottom navigation */
body.sv-pro-app .sv-bottom-navigation {
    border-top: 1px solid var(--svp-border);
    background: rgba(255,255,255,.96);
    backdrop-filter: blur(14px);
}
body.sv-pro-app .sv-bottom-navigation a {
    min-height: 58px;
    color: #7b8798;
    font-size: 9.5px;
    font-weight: 650;
}
body.sv-pro-app .sv-bottom-navigation a.is-active { color: var(--svp-blue); }
body.sv-pro-app .sv-bottom-navigation a::before { background: var(--svp-blue); }

/* Responsive */
@media (max-width: 1399px) {
    :root { --svp-content: 1320px; }
    .sv-pro-metrics { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    body.sv-pro-app .sv-room-grid-three { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    body.sv-pro-app .sv-device-directory { grid-template-columns: repeat(2, minmax(0, 1fr)); }
}

@media (max-width: 1180px) {
    .sv-pro-dashboard-grid,
    body.sv-pro-app .sv-section-grid-analysis { grid-template-columns: minmax(0, 1fr); }
    .sv-pro-system-strip { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    .sv-pro-system-item:nth-child(2) { border-right: 0; }
    .sv-pro-system-item:nth-child(-n+2) { border-bottom: 1px solid var(--svp-border); }
    body.sv-pro-app .sv-filter-grid-compact { grid-template-columns: 1fr 1fr 1fr; }
    body.sv-pro-app .sv-filter-actions { grid-column: 1 / -1; justify-content: flex-end; }
}

@media (max-width: 1023px) {
    body.sv-pro-app .sv-app-main { margin-left: 0; }
    body.sv-pro-app .sv-app-sidebar { width: min(310px, 88vw); box-shadow: var(--svp-shadow-float); }
    body.sv-pro-app .sv-topbar-inner,
    body.sv-pro-app .sv-page-content { width: 100%; }
    body.sv-pro-app .sv-topbar-inner { padding-inline: 20px; }
    body.sv-pro-app .sv-page-content { padding: 22px 20px 88px; }
    body.sv-pro-app .sv-page-title { font-size: 22px; }
    body.sv-pro-app .sv-profile-copy { display: none; }
    body.sv-pro-app .sv-profile-trigger { width: 44px; padding: 5px; }
    body.sv-pro-app .sv-profile-trigger > .sv-icon { display: none; }
    body.sv-pro-app .sv-settings-layout { grid-template-columns: 1fr; }
    body.sv-pro-app .sv-settings-nav { position: static; display: flex; overflow-x: auto; gap: 6px; padding: 6px; }
    body.sv-pro-app .sv-tab-button { flex: 0 0 auto; }
    body.sv-pro-app .sv-tech-two-column,
    body.sv-pro-app .sv-tech-inventory-grid { grid-template-columns: 1fr; }
}

@media (max-width: 767px) {
    body.sv-pro-app .sv-app-topbar { min-height: 66px; }
    body.sv-pro-app .sv-topbar-inner { min-height: 66px; padding-inline: 14px; }
    body.sv-pro-app .sv-page-content { padding: 16px 14px 86px; }
    body.sv-pro-app .sv-page-title { font-size: 20px; }
    body.sv-pro-app .sv-page-subtitle { display: none; }
    body.sv-pro-app .sv-topbar-status { display: none; }
    body.sv-pro-app .sv-card-header { min-height: 62px; padding: 15px 16px; gap: 10px; }
    body.sv-pro-app .sv-card-header h2 { font-size: 15px; }
    body.sv-pro-app .sv-card-body { padding: 16px; }

    .sv-pro-metrics,
    body.sv-pro-app .sv-history-metrics-three,
    body.sv-pro-app .sv-room-summary-grid-three { grid-template-columns: 1fr 1fr; gap: 11px; }
    .sv-pro-metric { min-height: 145px; padding: 15px; }
    .sv-pro-icon-tile { width: 38px; height: 38px; flex-basis: 38px; }
    .sv-pro-metric-value { margin-top: 14px; }
    .sv-pro-metric-value strong { font-size: 27px; }

    .sv-pro-system-strip { grid-template-columns: 1fr; }
    .sv-pro-system-item { min-height: 64px; border-right: 0; border-bottom: 1px solid var(--svp-border); }
    .sv-pro-system-item:last-child { border-bottom: 0; }

    .sv-pro-dashboard-grid { gap: 14px; }
    .sv-pro-chart-card .sv-chart-container,
    body.sv-pro-app .sv-history-chart-card .sv-chart-container { min-height: 260px; }

    body.sv-pro-app .sv-filter-grid-compact { grid-template-columns: 1fr; }
    body.sv-pro-app .sv-filter-actions { grid-column: auto; justify-content: stretch; }
    body.sv-pro-app .sv-filter-actions .sv-button { flex: 1; }
    body.sv-pro-app .sv-filter-footer { align-items: stretch; gap: 12px; }
    body.sv-pro-app .sv-quick-filters { overflow-x: auto; flex-wrap: nowrap; padding-bottom: 2px; }
    body.sv-pro-app .sv-quick-filter { flex: 0 0 auto; }

    body.sv-pro-app .sv-room-grid-three,
    body.sv-pro-app .sv-device-directory { grid-template-columns: 1fr; }

    body.sv-pro-app .sv-form-grid-two,
    body.sv-pro-app .sv-form-grid-three { grid-template-columns: 1fr; }
    body.sv-pro-app .sv-grid-full { grid-column: auto; }

    body.sv-pro-app .sv-table-wrap { overflow-x: auto; }
    body.sv-pro-app .sv-table { min-width: 680px; }

    body.sv-pro-app .sv-technician-summary-three { grid-template-columns: 1fr; }
    body.sv-pro-app .sv-settings-nav { margin-inline: -2px; }
}

@media (max-width: 520px) {
    .sv-pro-metrics,
    body.sv-pro-app .sv-history-metrics-three,
    body.sv-pro-app .sv-room-summary-grid-three { grid-template-columns: 1fr; }
    .sv-pro-metric { min-height: 136px; }
    .sv-pro-metric-head { justify-content: flex-start; }
    body.sv-pro-app .sv-chart-summary { grid-template-columns: repeat(3, minmax(0, 1fr)); }
    body.sv-pro-app .sv-chart-summary > div { padding: 9px; }
    body.sv-pro-app .sv-chart-summary strong { font-size: 13px; }
    body.sv-pro-app .sv-card-header { align-items: flex-start; }
    body.sv-pro-app .sv-card-header > :last-child { flex: 0 0 auto; }
}

@media (prefers-reduced-motion: reduce) {
    *, *::before, *::after {
        scroll-behavior: auto !important;
        animation-duration: .01ms !important;
        animation-iteration-count: 1 !important;
        transition-duration: .01ms !important;
    }
}
```

## `payload/public/assets/css/smartvolt-auth.css`

```css
:root {
    --sv-bg: #f5f7fa;
    --sv-surface: #ffffff;
    --sv-text: #172033;
    --sv-muted: #667085;
    --sv-border: #dde3ea;
    --sv-border-strong: #b8c5d4;
    --sv-primary: #2563eb;
    --sv-primary-dark: #1d4ed8;
    --sv-navy: #173b66;
    --sv-navy-dark: #102f54;
    --sv-success: #2e7d32;
    --sv-success-bg: #eaf6ec;
    --sv-danger: #c83c3c;
    --sv-danger-bg: #fdecec;
    --sv-warning: #b76e00;
    --sv-warning-bg: #fff4e5;
    --sv-shadow: 0 24px 60px rgba(23, 59, 102, 0.12);
    --sv-radius: 10px;
    --sv-radius-lg: 14px;
}

*,
*::before,
*::after {
    box-sizing: border-box;
}

html {
    min-width: 320px;
    min-height: 100%;
    background: var(--sv-bg);
}

body.sv-auth-page {
    min-width: 320px;
    min-height: 100vh;
    min-height: 100svh;
    margin: 0;
    color: var(--sv-text);
    background: var(--sv-bg);
    font-family:
        Inter,
        "Segoe UI",
        Roboto,
        Helvetica,
        Arial,
        sans-serif;
    -webkit-font-smoothing: antialiased;
    text-rendering: optimizeLegibility;
}

button,
input {
    font: inherit;
}

button,
a,
input {
    -webkit-tap-highlight-color: transparent;
}

a {
    color: inherit;
    text-decoration: none;
}

svg {
    display: block;
}

/* =========================================================
   Layout utama
   ========================================================= */

.sv-auth-shell {
    display: grid;
    min-height: 100vh;
    min-height: 100svh;
    grid-template-columns: minmax(0, 55fr) minmax(420px, 45fr);
}

/* =========================================================
   Panel informasi (kiri)
   ========================================================= */

.sv-auth-showcase {
    position: relative;
    isolation: isolate;
    display: flex;
    min-height: 100vh;
    min-height: 100svh;
    overflow: hidden;
    color: #ffffff;
    background: var(--sv-navy);
}

.sv-auth-network {
    position: absolute;
    z-index: -1;
    right: -14%;
    bottom: -20%;
    width: min(760px, 82vw);
    color: rgba(255, 255, 255, 0.08);
    transform: rotate(-4deg);
    pointer-events: none;
}

.sv-auth-showcase::after {
    position: absolute;
    z-index: -1;
    right: -120px;
    bottom: -190px;
    width: 480px;
    height: 480px;
    border: 1px solid rgba(255, 255, 255, 0.08);
    border-radius: 50%;
    content: "";
}

.sv-auth-showcase-content {
    display: flex;
    width: min(100%, 820px);
    min-height: 100%;
    margin-inline: auto;
    padding: clamp(38px, 5vw, 78px);
    flex-direction: column;
}

.sv-auth-brand {
    display: inline-flex;
    width: fit-content;
    align-items: center;
    gap: 13px;
    color: #ffffff;
}

.sv-auth-brand-icon {
    display: grid;
    width: 48px;
    height: 48px;
    flex: 0 0 48px;
    place-items: center;
    border: 1px solid rgba(255, 255, 255, 0.18);
    border-radius: 13px;
    color: #ffffff;
    background: rgba(255, 255, 255, 0.1);
}

.sv-auth-brand-icon svg {
    width: 25px;
    height: 25px;
}

.sv-auth-brand strong {
    display: block;
    font-size: 21px;
    font-weight: 750;
    letter-spacing: -0.02em;
}

.sv-auth-brand small {
    display: block;
    margin-top: 2px;
    color: rgba(255, 255, 255, 0.68);
    font-size: 12px;
    font-weight: 500;
    letter-spacing: 0.02em;
}

.sv-auth-copy {
    max-width: 690px;
    margin-top: clamp(72px, 10vh, 132px);
}

.sv-auth-eyebrow {
    margin: 0 0 16px;
    color: #bfdbfe;
    font-size: 13px;
    font-weight: 700;
    letter-spacing: 0.09em;
    text-transform: uppercase;
}

.sv-auth-copy h1 {
    max-width: 650px;
    margin: 0;
    font-size: clamp(38px, 4.5vw, 66px);
    font-weight: 720;
    line-height: 1.06;
    letter-spacing: -0.045em;
}

.sv-auth-description {
    max-width: 620px;
    margin: 22px 0 0;
    color: rgba(255, 255, 255, 0.76);
    font-size: clamp(16px, 1.4vw, 19px);
    line-height: 1.72;
}

.sv-auth-benefits {
    display: grid;
    max-width: 650px;
    margin-top: 40px;
    gap: 18px;
}

.sv-auth-benefit {
    display: grid;
    grid-template-columns: 42px minmax(0, 1fr);
    align-items: start;
    gap: 14px;
}

.sv-auth-benefit-icon {
    display: grid;
    width: 42px;
    height: 42px;
    place-items: center;
    border: 1px solid rgba(255, 255, 255, 0.14);
    border-radius: 12px;
    color: #dbeafe;
    background: rgba(255, 255, 255, 0.08);
}

.sv-auth-benefit-icon svg {
    width: 21px;
    height: 21px;
    fill: none;
    stroke: currentColor;
    stroke-linecap: round;
    stroke-linejoin: round;
    stroke-width: 1.8;
}

.sv-auth-benefit h2 {
    margin: 1px 0 5px;
    font-size: 15px;
    font-weight: 700;
    line-height: 1.35;
}

.sv-auth-benefit p {
    max-width: 560px;
    margin: 0;
    color: rgba(255, 255, 255, 0.65);
    font-size: 13px;
    line-height: 1.62;
}

.sv-auth-flow {
    width: min(100%, 650px);
    margin-top: auto;
    padding-top: 42px;
}

.sv-auth-flow-label {
    margin-bottom: 10px;
    color: rgba(255, 255, 255, 0.48);
    font-size: 11px;
    font-weight: 700;
    letter-spacing: 0.1em;
    text-transform: uppercase;
}

.sv-auth-flow-items {
    display: grid;
    padding: 14px 16px;
    grid-template-columns: 1fr auto 1fr auto 1fr;
    align-items: center;
    gap: 14px;
    border: 1px solid rgba(255, 255, 255, 0.12);
    border-radius: 14px;
    background: rgba(7, 30, 55, 0.28);
}

.sv-auth-flow-items span {
    min-width: 0;
}

.sv-auth-flow-items strong,
.sv-auth-flow-items small {
    display: block;
}

.sv-auth-flow-items strong {
    font-size: 13px;
    font-weight: 700;
}

.sv-auth-flow-items small {
    margin-top: 3px;
    overflow: hidden;
    color: rgba(255, 255, 255, 0.55);
    font-size: 11px;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.sv-auth-flow-items i {
    color: rgba(255, 255, 255, 0.45);
    font-size: 15px;
    font-style: normal;
}

/* =========================================================
   Panel formulir (kanan)
   ========================================================= */

.sv-auth-panel {
    display: flex;
    min-height: 100vh;
    min-height: 100svh;
    align-items: center;
    justify-content: center;
    padding: clamp(30px, 5vw, 72px);
    background: var(--sv-surface);
}

.sv-auth-panel-inner {
    width: min(100%, 470px);
}

.sv-auth-mobile-brand {
    display: none;
}

.sv-auth-status-chip {
    display: inline-flex;
    min-height: 30px;
    padding: 6px 10px;
    align-items: center;
    gap: 8px;
    border: 1px solid #cfe0f5;
    border-radius: 999px;
    color: #255488;
    background: #f2f7fd;
    font-size: 12px;
    font-weight: 650;
}

.sv-auth-status-chip > span {
    width: 7px;
    height: 7px;
    border-radius: 50%;
    background: var(--sv-success);
    box-shadow: 0 0 0 4px rgba(46, 125, 50, 0.1);
}

.sv-auth-heading {
    margin-bottom: 30px;
}

.sv-auth-heading h2 {
    margin: 19px 0 10px;
    color: var(--sv-text);
    font-size: clamp(31px, 3.2vw, 42px);
    font-weight: 740;
    line-height: 1.08;
    letter-spacing: -0.04em;
}

.sv-auth-heading p {
    max-width: 430px;
    margin: 0;
    color: var(--sv-muted);
    font-size: 15px;
    line-height: 1.7;
}

/* =========================================================
   Alert
   ========================================================= */

.sv-auth-alert {
    display: grid;
    margin-bottom: 22px;
    padding: 13px 14px;
    grid-template-columns: 22px minmax(0, 1fr);
    align-items: start;
    gap: 10px;
    border: 1px solid;
    border-radius: var(--sv-radius);
    font-size: 13px;
    line-height: 1.55;
}

.sv-auth-alert > span {
    margin-top: 1px;
}

.sv-auth-alert svg {
    width: 19px;
    height: 19px;
    fill: none;
    stroke: currentColor;
    stroke-linecap: round;
    stroke-linejoin: round;
    stroke-width: 1.9;
}

.sv-auth-alert strong {
    display: block;
    margin-bottom: 2px;
    font-weight: 700;
}

.sv-auth-alert p {
    margin: 0;
}

.sv-auth-alert-success {
    border-color: #cce6cf;
    color: #285f2d;
    background: var(--sv-success-bg);
}

.sv-auth-alert-error {
    border-color: #f1cccc;
    color: #8f2e2e;
    background: var(--sv-danger-bg);
}

/* =========================================================
   Form
   ========================================================= */

.sv-auth-form {
    display: grid;
    gap: 20px;
}

.sv-auth-field {
    min-width: 0;
}

.sv-auth-field > label {
    display: block;
    margin-bottom: 8px;
    color: #344054;
    font-size: 13px;
    font-weight: 680;
}

.sv-auth-input-group {
    position: relative;
}

.sv-auth-input-icon {
    position: absolute;
    z-index: 1;
    top: 50%;
    left: 15px;
    color: #7a8ca3;
    transform: translateY(-50%);
    pointer-events: none;
}

.sv-auth-input-icon svg {
    width: 20px;
    height: 20px;
    fill: none;
    stroke: currentColor;
    stroke-linecap: round;
    stroke-linejoin: round;
    stroke-width: 1.65;
}

.sv-auth-input-group input {
    width: 100%;
    height: 50px;
    padding: 0 48px 0 47px;
    border: 1px solid var(--sv-border);
    border-radius: var(--sv-radius);
    outline: none;
    color: var(--sv-text);
    background: #ffffff;
    font-size: 15px;
    transition:
        border-color 150ms ease,
        box-shadow 150ms ease,
        background-color 150ms ease;
}

.sv-auth-input-group input::placeholder {
    color: #98a2b3;
}

.sv-auth-input-group input:hover {
    border-color: var(--sv-border-strong);
}

.sv-auth-input-group input:focus {
    border-color: var(--sv-primary);
    box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.1);
}

.sv-auth-input-group.is-invalid input {
    border-color: var(--sv-danger);
    background: #fffafa;
}

.sv-auth-input-group.is-invalid input:focus {
    box-shadow: 0 0 0 4px rgba(200, 60, 60, 0.1);
}

.sv-auth-password-toggle {
    position: absolute;
    top: 50%;
    right: 8px;
    display: grid;
    width: 36px;
    height: 36px;
    padding: 0;
    place-items: center;
    border: 0;
    border-radius: 8px;
    color: #667085;
    background: transparent;
    transform: translateY(-50%);
    cursor: pointer;
}

.sv-auth-password-toggle:hover {
    color: var(--sv-primary);
    background: #f2f6fc;
}

.sv-auth-password-toggle:focus-visible {
    outline: 3px solid rgba(37, 99, 235, 0.18);
    outline-offset: 1px;
}

.sv-auth-password-toggle svg {
    width: 20px;
    height: 20px;
    fill: none;
    stroke: currentColor;
    stroke-linecap: round;
    stroke-linejoin: round;
    stroke-width: 1.7;
}

.sv-auth-eye-hide,
.sv-auth-password-toggle.is-visible .sv-auth-eye-show {
    display: none;
}

.sv-auth-password-toggle.is-visible .sv-auth-eye-hide {
    display: block;
}

.sv-auth-field-help,
.sv-auth-field-error {
    margin: 7px 0 0;
    font-size: 12px;
    line-height: 1.5;
}

.sv-auth-field-help {
    color: #7a8699;
}

.sv-auth-field-error {
    color: var(--sv-danger);
    font-weight: 600;
}

/* =========================================================
   Password strength indicator
   ========================================================= */

.sv-auth-password-strength {
    display: grid;
    margin-top: 8px;
    gap: 4px;
}

.sv-auth-strength-bar {
    display: flex;
    gap: 4px;
}

.sv-auth-strength-bar span {
    flex: 1;
    height: 4px;
    border-radius: 4px;
    background: #e0e5ec;
    transition: background-color 200ms ease;
}

.sv-auth-strength-bar span.is-active.weak {
    background: var(--sv-danger);
}

.sv-auth-strength-bar span.is-active.medium {
    background: var(--sv-warning);
}

.sv-auth-strength-bar span.is-active.strong {
    background: var(--sv-success);
}

.sv-auth-strength-text {
    font-size: 11px;
    color: var(--sv-muted);
}

.sv-auth-strength-text.is-weak {
    color: var(--sv-danger);
}

.sv-auth-strength-text.is-medium {
    color: var(--sv-warning);
}

.sv-auth-strength-text.is-strong {
    color: var(--sv-success);
}

/* =========================================================
   Options row (remember me + forgot password)
   ========================================================= */

.sv-auth-options {
    display: flex;
    margin-top: -2px;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
}

/* =========================================================
   Checkbox
   ========================================================= */

.sv-auth-checkbox {
    display: inline-flex;
    min-height: 32px;
    align-items: center;
    gap: 9px;
    color: #475467;
    cursor: pointer;
    user-select: none;
}

.sv-auth-checkbox input {
    position: absolute;
    width: 1px;
    height: 1px;
    overflow: hidden;
    opacity: 0;
    pointer-events: none;
}

.sv-auth-checkbox > span {
    position: relative;
    width: 18px;
    height: 18px;
    flex: 0 0 18px;
    border: 1px solid #b8c5d4;
    border-radius: 5px;
    background: #ffffff;
    transition:
        border-color 150ms ease,
        background-color 150ms ease,
        box-shadow 150ms ease;
}

.sv-auth-checkbox > span::after {
    position: absolute;
    top: 3px;
    left: 5px;
    width: 4px;
    height: 8px;
    border-right: 2px solid #ffffff;
    border-bottom: 2px solid #ffffff;
    content: "";
    opacity: 0;
    transform: rotate(45deg) scale(0.7);
    transition:
        opacity 120ms ease,
        transform 120ms ease;
}

.sv-auth-checkbox input:checked + span {
    border-color: var(--sv-primary);
    background: var(--sv-primary);
}

.sv-auth-checkbox input:checked + span::after {
    opacity: 1;
    transform: rotate(45deg) scale(1);
}

.sv-auth-checkbox input:focus-visible + span {
    box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.12);
}

.sv-auth-checkbox em {
    font-size: 13px;
    font-style: normal;
}

/* =========================================================
   Links
   ========================================================= */

.sv-auth-link {
    color: var(--sv-primary);
    font-size: 13px;
    font-weight: 680;
    text-underline-offset: 3px;
}

.sv-auth-link:hover {
    color: var(--sv-primary-dark);
    text-decoration: underline;
}

.sv-auth-link:focus-visible {
    border-radius: 4px;
    outline: 3px solid rgba(37, 99, 235, 0.15);
    outline-offset: 3px;
}

.sv-auth-link-back {
    display: inline-flex;
    align-items: center;
    gap: 7px;
}

/* =========================================================
   Submit button
   ========================================================= */

.sv-auth-submit {
    position: relative;
    display: inline-flex;
    width: 100%;
    min-height: 50px;
    margin-top: 2px;
    padding: 12px 18px;
    align-items: center;
    justify-content: center;
    gap: 10px;
    border: 1px solid var(--sv-primary);
    border-radius: var(--sv-radius);
    color: #ffffff;
    background: var(--sv-primary);
    box-shadow: 0 10px 24px rgba(37, 99, 235, 0.18);
    font-size: 14px;
    font-weight: 720;
    cursor: pointer;
    transition:
        transform 150ms ease,
        background-color 150ms ease,
        border-color 150ms ease,
        box-shadow 150ms ease;
}

.sv-auth-submit:hover:not(:disabled) {
    border-color: var(--sv-primary-dark);
    background: var(--sv-primary-dark);
    box-shadow: 0 12px 28px rgba(37, 99, 235, 0.22);
    transform: translateY(-1px);
}

.sv-auth-submit:focus-visible {
    outline: 4px solid rgba(37, 99, 235, 0.18);
    outline-offset: 2px;
}

.sv-auth-submit:disabled {
    cursor: wait;
    opacity: 0.75;
    transform: none;
}

.sv-auth-button-spinner {
    display: none;
    width: 17px;
    height: 17px;
    border: 2px solid rgba(255, 255, 255, 0.45);
    border-top-color: #ffffff;
    border-radius: 50%;
    animation: sv-auth-spin 700ms linear infinite;
}

.sv-auth-submit.is-loading .sv-auth-button-spinner {
    display: inline-block;
}

@keyframes sv-auth-spin {
    to {
        transform: rotate(360deg);
    }
}

/* =========================================================
   Footer links
   ========================================================= */

.sv-auth-footer {
    margin: 23px 0 0;
    color: var(--sv-muted);
    font-size: 13px;
    text-align: center;
}

.sv-auth-security-note {
    display: grid;
    margin-top: 32px;
    padding-top: 22px;
    grid-template-columns: 20px minmax(0, 1fr);
    align-items: start;
    gap: 10px;
    border-top: 1px solid #e7ebf0;
    color: #7a8699;
}

.sv-auth-security-note svg {
    width: 19px;
    height: 19px;
    fill: none;
    stroke: #54718f;
    stroke-linecap: round;
    stroke-linejoin: round;
    stroke-width: 1.7;
}

.sv-auth-security-note p {
    margin: 0;
    font-size: 11px;
    line-height: 1.55;
}

/* =========================================================
   Responsif
   ========================================================= */

@media (max-width: 1180px) {
    .sv-auth-shell {
        grid-template-columns: minmax(0, 52fr) minmax(400px, 48fr);
    }

    .sv-auth-showcase-content {
        padding: 44px;
    }

    .sv-auth-copy {
        margin-top: 72px;
    }

    .sv-auth-copy h1 {
        font-size: clamp(38px, 5vw, 54px);
    }
}

@media (max-width: 920px) {
    .sv-auth-shell {
        display: block;
        min-height: 100vh;
        min-height: 100svh;
    }

    .sv-auth-showcase {
        display: none;
    }

    .sv-auth-panel {
        min-height: 100vh;
        min-height: 100svh;
        padding: 40px 24px;
        align-items: center;
    }

    .sv-auth-panel-inner {
        width: min(100%, 500px);
    }

    .sv-auth-mobile-brand {
        display: inline-flex;
        margin-bottom: 44px;
        align-items: center;
        gap: 12px;
        color: var(--sv-navy);
    }

    .sv-auth-mobile-brand .sv-auth-brand-icon {
        width: 44px;
        height: 44px;
        flex-basis: 44px;
        border-color: #c8d6e6;
        color: #ffffff;
        background: var(--sv-navy);
    }

    .sv-auth-mobile-brand small {
        color: #718096;
    }
}

@media (max-width: 560px) {
    .sv-auth-panel {
        padding:
            max(24px, env(safe-area-inset-top))
            18px
            max(24px, env(safe-area-inset-bottom));
        align-items: flex-start;
    }

    .sv-auth-mobile-brand {
        margin-top: 4px;
        margin-bottom: 36px;
    }

    .sv-auth-heading {
        margin-bottom: 25px;
    }

    .sv-auth-heading h2 {
        margin-top: 16px;
        font-size: 31px;
    }

    .sv-auth-heading p {
        font-size: 14px;
    }

    .sv-auth-submit {
        min-height: 52px;
    }

    .sv-auth-security-note {
        margin-top: 28px;
    }
}

@media (max-width: 380px) {
    .sv-auth-footer {
        text-align: left;
    }

    .sv-auth-options {
        align-items: stretch;
        flex-direction: column;
        gap: 5px;
    }

    .sv-auth-link {
        min-height: 34px;
        display: inline-flex;
        align-items: center;
    }
}

@media (prefers-reduced-motion: reduce) {
    *,
    *::before,
    *::after {
        animation-duration: 0.01ms !important;
        animation-iteration-count: 1 !important;
        scroll-behavior: auto !important;
        transition-duration: 0.01ms !important;
    }
}

/* =========================================================
   SmartVolt Auth V5 — colored icon refinement
   ========================================================= */
.sv-auth-benefit:nth-child(1) .sv-auth-benefit-icon {
    color: #bfdbfe;
    background: rgba(37, 99, 235, 0.18);
    border-color: rgba(147, 197, 253, 0.24);
}

.sv-auth-benefit:nth-child(2) .sv-auth-benefit-icon {
    color: #fde68a;
    background: rgba(217, 119, 6, 0.18);
    border-color: rgba(253, 230, 138, 0.24);
}

.sv-auth-benefit:nth-child(3) .sv-auth-benefit-icon {
    color: #a7f3d0;
    background: rgba(21, 148, 85, 0.18);
    border-color: rgba(167, 243, 208, 0.24);
}

.sv-auth-input-icon {
    color: #2563eb;
    background: #eef5ff;
    border-radius: 8px;
}

.sv-auth-field:nth-of-type(2) .sv-auth-input-icon {
    color: #7c3aed;
    background: #f4efff;
}

.sv-auth-alert-success > span:first-child {
    color: #159455;
    background: #eaf8f0;
}

.sv-auth-alert-error > span:first-child {
    color: #dc4c64;
    background: #fff0f3;
}

/* =========================================================
   SmartVolt Auth V7 — Professional Minimal
   ========================================================= */
.sv-auth-shell {
    background: #f6f8fb;
}

.sv-auth-showcase {
    flex: 0 0 54%;
    color: #ffffff;
    background: #173b66;
}

.sv-auth-showcase::before {
    opacity: .55;
}

.sv-auth-network {
    opacity: .08;
}

.sv-auth-showcase-content {
    width: min(660px, 100%);
    padding: 52px clamp(34px, 5vw, 76px);
}

.sv-auth-copy {
    margin-top: clamp(54px, 9vh, 94px);
}

.sv-auth-eyebrow {
    font-size: 11px;
    letter-spacing: .09em;
}

.sv-auth-copy h1 {
    max-width: 620px;
    font-size: clamp(34px, 4vw, 52px);
    line-height: 1.08;
    letter-spacing: -.045em;
}

.sv-auth-description {
    max-width: 560px;
    font-size: 15px;
    line-height: 1.7;
}

.sv-auth-benefits {
    display: grid;
    grid-template-columns: 1fr;
    max-width: 590px;
    gap: 10px;
    margin-top: 34px;
}

.sv-auth-benefit {
    min-height: 68px;
    padding: 13px 14px;
    border-color: rgba(255,255,255,.10);
    border-radius: 12px;
    background: rgba(255,255,255,.055);
}

.sv-auth-benefit-icon {
    width: 38px;
    height: 38px;
    flex-basis: 38px;
    border-radius: 10px;
}

.sv-auth-benefit h2 {
    font-size: 13px;
}

.sv-auth-benefit p {
    margin-top: 3px;
    font-size: 11.5px;
    line-height: 1.5;
}

.sv-auth-flow {
    display: none;
}

.sv-auth-panel {
    flex: 1 1 46%;
    padding: 42px clamp(26px, 4vw, 62px);
    background: #ffffff;
}

.sv-auth-panel-inner {
    width: min(430px, 100%);
}

.sv-auth-heading {
    margin-bottom: 24px;
}

.sv-auth-heading h2 {
    margin-top: 13px;
    font-size: 30px;
    line-height: 1.18;
    letter-spacing: -.035em;
}

.sv-auth-heading p {
    margin-top: 8px;
    font-size: 13.5px;
}

.sv-auth-status-chip {
    min-height: 28px;
    padding: 5px 9px;
    font-size: 10.5px;
}

.sv-auth-field label {
    margin-bottom: 7px;
    font-size: 12.5px;
}

.sv-auth-input-group {
    min-height: 48px;
    border-radius: 10px;
}

.sv-auth-input-icon {
    width: 34px;
    height: 34px;
    margin-left: 6px;
    flex-basis: 34px;
}

.sv-auth-input-group input {
    font-size: 13.5px;
}

.sv-auth-submit {
    min-height: 48px;
    border-radius: 10px;
    font-size: 13px;
}

.sv-auth-security-note {
    margin-top: 22px;
    font-size: 11.5px;
}

@media (max-width: 980px) {
    .sv-auth-showcase {
        display: none;
    }

    .sv-auth-panel {
        min-height: 100svh;
        padding: max(30px, env(safe-area-inset-top)) 22px max(30px, env(safe-area-inset-bottom));
        align-items: center;
    }

    .sv-auth-mobile-brand {
        margin-bottom: 34px;
    }
}

@media (max-width: 560px) {
    .sv-auth-panel {
        align-items: flex-start;
    }

    .sv-auth-heading h2 {
        font-size: 27px;
    }
}
```

## `payload/public/assets/js/smartvolt-app.js`

```javascript
(() => {
    'use strict';

    const body = document.body;
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';

    const closeAllMenus = (except = null) => {
        document.querySelectorAll('[data-profile-menu].is-open, [data-notification-menu].is-open')
            .forEach((menu) => {
                if (menu === except) {
                    return;
                }

                menu.classList.remove('is-open');
                menu.querySelector('[aria-expanded="true"]')?.setAttribute('aria-expanded', 'false');
            });
    };

    const openSidebar = () => {
        body.classList.add('sv-sidebar-open', 'sv-is-locked');
    };

    const closeSidebar = () => {
        body.classList.remove('sv-sidebar-open', 'sv-is-locked');
    };

    document.addEventListener('click', (event) => {
        if (event.target.closest('[data-sidebar-open]')) {
            openSidebar();
            return;
        }

        if (event.target.closest('[data-sidebar-close]')) {
            closeSidebar();
            return;
        }

        const profileTrigger = event.target.closest('[data-profile-trigger]');
        if (profileTrigger) {
            const menu = profileTrigger.closest('[data-profile-menu]');
            const nextState = !menu.classList.contains('is-open');

            closeAllMenus(menu);
            menu.classList.toggle('is-open', nextState);
            profileTrigger.setAttribute('aria-expanded', nextState ? 'true' : 'false');
            return;
        }

        const notificationTrigger = event.target.closest('[data-notification-trigger]');
        if (notificationTrigger) {
            const menu = notificationTrigger.closest('[data-notification-menu]');
            const nextState = !menu.classList.contains('is-open');

            closeAllMenus(menu);
            menu.classList.toggle('is-open', nextState);
            notificationTrigger.setAttribute('aria-expanded', nextState ? 'true' : 'false');
            return;
        }

        if (!event.target.closest('[data-profile-menu], [data-notification-menu]')) {
            closeAllMenus();
        }

        const openDialogButton = event.target.closest('[data-dialog-open]');
        if (openDialogButton) {
            const dialog = document.getElementById(openDialogButton.dataset.dialogOpen || '');
            if (dialog instanceof HTMLDialogElement) {
                dialog.showModal();
                body.classList.add('sv-is-locked');
            }
            return;
        }

        const closeDialogButton = event.target.closest('[data-dialog-close]');
        if (closeDialogButton) {
            const dialog = closeDialogButton.closest('dialog');
            if (dialog instanceof HTMLDialogElement) {
                dialog.close();
            }
            return;
        }

        const passwordToggle = event.target.closest('[data-password-toggle]');
        if (passwordToggle) {
            const targetId = passwordToggle.dataset.passwordToggle;
            const input = document.getElementById(targetId);
            if (!(input instanceof HTMLInputElement)) {
                return;
            }

            const shouldShow = input.type === 'password';
            input.type = shouldShow ? 'text' : 'password';
            passwordToggle.setAttribute(
                'aria-label',
                shouldShow ? 'Sembunyikan kata sandi' : 'Tampilkan kata sandi'
            );
            passwordToggle.setAttribute('aria-pressed', shouldShow ? 'true' : 'false');
            passwordToggle.classList.toggle('is-visible', shouldShow);
            input.focus();
            return;
        }

        const tabButton = event.target.closest('[data-tab-target]');
        if (tabButton) {
            const group = tabButton.closest('[data-tabs]');
            const targetId = tabButton.dataset.tabTarget;

            if (!group || !targetId) {
                return;
            }

            group.querySelectorAll('[data-tab-target]').forEach((button) => {
                const active = button === tabButton;
                button.classList.toggle('is-active', active);
                button.setAttribute('aria-selected', active ? 'true' : 'false');
            });

            const panelsRootId = group.dataset.panelsRoot;
            const panelsRoot = panelsRootId
                ? document.getElementById(panelsRootId)
                : group.parentElement;

            panelsRoot?.querySelectorAll('[data-tab-panel]').forEach((panel) => {
                panel.classList.toggle('is-active', panel.id === targetId);
            });

            const url = new URL(window.location.href);
            url.searchParams.set('tab', tabButton.dataset.tabName || targetId.replace(/^tab-/, ''));
            history.replaceState({}, '', url);
        }
    });

    document.addEventListener('submit', (event) => {
        const form = event.target;
        if (!(form instanceof HTMLFormElement)) {
            return;
        }

        const confirmMessage = form.dataset.confirm;
        if (confirmMessage && !window.confirm(confirmMessage)) {
            event.preventDefault();
            return;
        }

        if (!form.hasAttribute('data-loading-form')) {
            return;
        }

        if (!form.checkValidity()) {
            return;
        }

        const submitButton = form.querySelector('button[type="submit"]');
        if (!(submitButton instanceof HTMLButtonElement)) {
            return;
        }

        submitButton.disabled = true;
        submitButton.classList.add('is-loading');
        submitButton.setAttribute('aria-busy', 'true');

        const label = submitButton.querySelector('[data-button-label]');
        if (label && submitButton.dataset.loadingText) {
            label.textContent = submitButton.dataset.loadingText;
        }
    });

    document.querySelectorAll('dialog').forEach((dialog) => {
        dialog.addEventListener('close', () => {
            if (!document.querySelector('dialog[open]')) {
                body.classList.remove('sv-is-locked');
            }
        });

        dialog.addEventListener('click', (event) => {
            if (event.target === dialog) {
                dialog.close();
            }
        });
    });

    document.querySelectorAll('[data-auto-open-dialog]').forEach((element) => {
        const dialog = document.getElementById(element.dataset.autoOpenDialog || '');
        if (dialog instanceof HTMLDialogElement) {
            dialog.showModal();
            body.classList.add('sv-is-locked');
        }
    });

    document.addEventListener('keydown', (event) => {
        if (event.key !== 'Escape') {
            return;
        }

        closeSidebar();
        closeAllMenus();
    });

    const toastIcon = (type) => {
        if (type === 'success') {
            return '<svg class="sv-icon" width="19" height="19" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="m5 12 4 4L19 6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>';
        }

        if (type === 'warning' || type === 'error') {
            return '<svg class="sv-icon" width="19" height="19" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M12 3 2.5 20h19L12 3Z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/><path d="M12 9v5M12 17h.01" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>';
        }

        return '<svg class="sv-icon" width="19" height="19" viewBox="0 0 24 24" fill="none" aria-hidden="true"><circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="1.8"/><path d="M12 11v5M12 8h.01" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>';
    };

    const toast = (message, options = {}) => {
        const region = document.querySelector('[data-toast-region]');
        if (!region) {
            return;
        }

        const type = ['success', 'error', 'warning', 'info'].includes(options.type)
            ? options.type
            : 'info';

        const title = options.title
            || (type === 'success'
                ? 'Berhasil'
                : type === 'error'
                    ? 'Tidak berhasil'
                    : type === 'warning'
                        ? 'Perlu perhatian'
                        : 'Informasi');

        const item = document.createElement('div');
        item.className = `sv-toast is-${type}`;
        item.setAttribute('role', type === 'error' ? 'alert' : 'status');
        item.innerHTML = `
            <span class="sv-text-${type === 'error' ? 'danger' : type}">${toastIcon(type)}</span>
            <span class="sv-toast-copy">
                <strong></strong>
                <span></span>
            </span>
            <button type="button" class="sv-toast-close" aria-label="Tutup notifikasi">
                <svg class="sv-icon" width="15" height="15" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                    <path d="m6 6 12 12M18 6 6 18" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                </svg>
            </button>
        `;

        item.querySelector('strong').textContent = title;
        item.querySelector('.sv-toast-copy span').textContent = String(message || '');
        item.querySelector('.sv-toast-close').addEventListener('click', () => item.remove());

        region.appendChild(item);
        requestAnimationFrame(() => item.classList.add('is-visible'));

        window.setTimeout(() => {
            item.classList.remove('is-visible');
            window.setTimeout(() => item.remove(), 180);
        }, Math.max(2500, Number(options.duration) || 4500));
    };

    const safeJson = async (response) => {
        const text = await response.text();

        if (!text) {
            return {};
        }

        try {
            return JSON.parse(text);
        } catch {
            throw new Error('Respons server tidak dapat dibaca.');
        }
    };

    const fetchJson = async (url, options = {}, timeoutMs = 12000) => {
        const controller = new AbortController();
        const timeout = window.setTimeout(() => controller.abort(), timeoutMs);

        try {
            const response = await fetch(url, {
                credentials: 'same-origin',
                ...options,
                signal: controller.signal,
                headers: {
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    ...(csrfToken ? {'X-CSRF-TOKEN': csrfToken} : {}),
                    ...(options.headers || {}),
                },
            });

            const data = await safeJson(response);

            if (!response.ok) {
                const error = new Error(
                    data.message
                    || (response.status === 419
                        ? 'Sesi keamanan telah berakhir. Muat ulang halaman lalu coba kembali.'
                        : `Permintaan gagal dengan status ${response.status}.`)
                );
                error.status = response.status;
                error.data = data;
                throw error;
            }

            return data;
        } catch (error) {
            if (error.name === 'AbortError') {
                throw new Error('Server terlalu lama merespons. Periksa koneksi lalu coba kembali.');
            }

            throw error;
        } finally {
            window.clearTimeout(timeout);
        }
    };

    window.SmartVolt = Object.freeze({
        csrfToken,
        toast,
        fetchJson,
        closeSidebar,
    });
})();
```

## `payload/public/assets/js/smartvolt-dashboard.js`

```javascript
(() => {
    'use strict';

    const dataNode = document.getElementById('smartvolt-dashboard-data');
    if (!dataNode || !window.SmartVolt) {
        return;
    }

    let config = {};
    try {
        config = JSON.parse(dataNode.textContent || '{}');
    } catch {
        window.SmartVolt.toast('Dashboard tidak dapat dimuat.', {type: 'error'});
        return;
    }

    const formatNumber = (value, digits = 0) => Number(value || 0).toLocaleString('id-ID', {
        minimumFractionDigits: digits,
        maximumFractionDigits: digits,
    });

    const setText = (selector, value) => {
        document.querySelectorAll(selector).forEach((element) => {
            element.textContent = String(value);
        });
    };

    const toNumbers = (values) => Array.isArray(values)
        ? values.map((value) => Number(value || 0))
        : [];

    const chartCanvas = document.getElementById('dashboardEnergyChart');
    const chartContainer = document.querySelector('[data-dashboard-chart-container]');
    const chartEmpty = document.querySelector('[data-dashboard-chart-empty]');

    let chartMode = 'power';
    let chartInstance = null;
    let dashboardChart = config.chart || {labels: [], power: [], energy: []};

    const currentChartValues = () => toNumbers(dashboardChart[chartMode]);

    const updateChartSummary = () => {
        const values = currentChartValues();
        const unit = chartMode === 'power' ? 'W' : 'kWh';
        const digits = chartMode === 'power' ? 1 : 4;
        const average = values.length
            ? values.reduce((total, value) => total + value, 0) / values.length
            : 0;
        const maximum = values.length ? Math.max(...values) : 0;
        const current = values.length ? values[values.length - 1] : 0;

        setText('[data-chart-current]', formatNumber(current, digits));
        setText('[data-chart-average]', formatNumber(average, digits));
        setText('[data-chart-maximum]', formatNumber(maximum, digits));
        setText('[data-chart-unit], [data-chart-average-unit], [data-chart-maximum-unit]', unit);
    };

    const renderChart = () => {
        const labels = Array.isArray(dashboardChart.labels) ? dashboardChart.labels : [];
        const values = currentChartValues();
        const hasData = labels.length > 0 && values.length > 0;

        chartContainer?.classList.toggle('is-hidden', !hasData);
        chartEmpty?.classList.toggle('is-hidden', hasData);
        updateChartSummary();

        if (!hasData || !(chartCanvas instanceof HTMLCanvasElement) || typeof window.Chart !== 'function') {
            chartInstance?.destroy();
            chartInstance = null;
            return;
        }

        const datasetLabel = chartMode === 'power' ? 'Daya' : 'Energi';
        const unit = chartMode === 'power' ? 'W' : 'kWh';
        const borderColor = chartMode === 'power' ? '#1267e8' : '#0aa5b8';
        const backgroundColor = chartMode === 'power'
            ? 'rgba(18, 103, 232, 0.10)'
            : 'rgba(10, 165, 184, 0.10)';

        if (chartInstance) {
            chartInstance.data.labels = labels;
            chartInstance.data.datasets[0].label = datasetLabel;
            chartInstance.data.datasets[0].data = values;
            chartInstance.data.datasets[0].borderColor = borderColor;
            chartInstance.data.datasets[0].backgroundColor = backgroundColor;
            chartInstance.options.scales.y.title.text = unit;
            chartInstance.update('none');
            return;
        }

        chartInstance = new window.Chart(chartCanvas, {
            type: 'line',
            data: {
                labels,
                datasets: [{
                    label: datasetLabel,
                    data: values,
                    borderColor,
                    backgroundColor,
                    borderWidth: 2.2,
                    pointRadius: labels.length > 18 ? 0 : 2.5,
                    pointHoverRadius: 4,
                    pointBackgroundColor: '#ffffff',
                    pointBorderColor: borderColor,
                    pointBorderWidth: 2,
                    fill: true,
                    tension: 0.34,
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: {mode: 'index', intersect: false},
                plugins: {
                    legend: {display: false},
                    tooltip: {
                        displayColors: false,
                        padding: 10,
                        callbacks: {
                            label: (context) => `${datasetLabel}: ${formatNumber(context.parsed.y, chartMode === 'power' ? 1 : 4)} ${unit}`,
                        },
                    },
                },
                scales: {
                    x: {
                        grid: {display: false},
                        border: {display: false},
                        ticks: {color: '#8390a4', maxTicksLimit: 8, font: {size: 10}},
                    },
                    y: {
                        beginAtZero: true,
                        border: {display: false},
                        grid: {color: '#edf1f5'},
                        ticks: {color: '#8390a4', font: {size: 10}},
                        title: {display: true, text: unit, color: '#68758a', font: {size: 10}},
                    },
                },
            },
        });
    };

    document.querySelectorAll('[data-dashboard-chart-mode]').forEach((button) => {
        button.addEventListener('click', () => {
            chartMode = button.dataset.dashboardChartMode === 'energy' ? 'energy' : 'power';

            document.querySelectorAll('[data-dashboard-chart-mode]').forEach((item) => {
                const active = item === button;
                item.classList.toggle('is-active', active);
                item.setAttribute('aria-selected', active ? 'true' : 'false');
            });

            renderChart();
        });
    });

    const loadLabel = (status) => status === 'danger'
        ? 'Beban tinggi'
        : status === 'warning'
            ? 'Mendekati batas'
            : 'Penggunaan normal';

    const updateStatusClass = (element, state) => {
        if (!element) {
            return;
        }
        element.classList.remove('is-success', 'is-warning', 'is-danger');
        element.classList.add(state);
    };

    const updateDashboard = (data) => {
        const stats = data.stats || {};
        const system = data.system || {};

        setText('[data-stat-current-power]', formatNumber(stats.current_power, 1));
        setText('[data-stat-energy-today]', formatNumber(stats.total_energy_today, 3));
        setText('[data-stat-monthly-cost]', formatNumber(stats.monthly_estimated_cost, 0));
        setText('[data-stat-monthly-energy]', formatNumber(stats.monthly_energy_usage, 3));
        setText('[data-stat-active-devices]', Number(stats.active_devices || 0));
        setText('[data-stat-total-devices]', Number(stats.total_devices || 0));
        setText('[data-stat-online-active-devices]', Number(stats.online_active_devices || 0));
        setText('[data-stat-load-percentage]', formatNumber(stats.load_percentage, 0));

        const progress = document.querySelector('[data-load-progress]');
        if (progress) {
            progress.style.setProperty('--sv-progress', `${Math.min(100, Math.max(0, Number(stats.load_percentage || 0)))}%`);
            progress.classList.toggle('is-warning', stats.load_status === 'warning');
            progress.classList.toggle('is-danger', stats.load_status === 'danger');
        }

        const loadBadge = document.querySelector('[data-load-status-badge]');
        if (loadBadge) {
            loadBadge.classList.remove('is-success', 'is-warning', 'is-danger');
            loadBadge.classList.add(
                stats.load_status === 'danger'
                    ? 'is-danger'
                    : stats.load_status === 'warning'
                        ? 'is-warning'
                        : 'is-success'
            );
        }
        setText('[data-load-status-label]', loadLabel(stats.load_status));

        const comparison = Number(stats.energy_comparison_percent);
        setText(
            '[data-energy-comparison]',
            Number.isFinite(comparison)
                ? `${comparison > 0 ? 'Lebih tinggi' : comparison < 0 ? 'Lebih hemat' : 'Sama'} ${formatNumber(Math.abs(comparison), 1)}% dari kemarin`
                : 'Perbandingan belum tersedia'
        );

        setText('[data-system-esp-label]', system.esp_online ? `${Number(system.online_esp_count || 0)} ESP32 Terhubung` : 'ESP32 Belum Terhubung');
        setText('[data-system-pzem-label]', system.pzem_status || 'Belum tersedia');
        setText('[data-system-control-label]', system.control_channel_status || 'Menunggu perangkat');
        setText('[data-system-latest-label]', system.latest_received_human || 'Belum ada data');

        updateStatusClass(document.querySelector('[data-system-esp]'), system.esp_online ? 'is-success' : 'is-warning');
        updateStatusClass(document.querySelector('[data-system-pzem]'), system.has_fresh_data ? 'is-success' : 'is-warning');
        updateStatusClass(document.querySelector('[data-system-control]'), system.esp_online ? 'is-success' : 'is-warning');
        updateStatusClass(document.querySelector('[data-system-latest]'), system.has_data ? 'is-success' : 'is-warning');

        const headerBadge = document.querySelector('[data-dashboard-system-badge]');
        if (headerBadge) {
            headerBadge.classList.remove('sv-badge-success', 'sv-badge-warning', 'sv-badge-neutral');
            headerBadge.classList.add(system.has_fresh_data ? 'sv-badge-success' : system.has_data ? 'sv-badge-warning' : 'sv-badge-neutral');
        }
        setText('[data-dashboard-system-label]', system.has_fresh_data ? 'Sistem Terhubung' : system.has_data ? 'Data Terlambat' : 'Belum Terhubung');

        const intro = stats.load_status === 'danger'
            ? 'Beban listrik tinggi. Matikan perangkat yang tidak diperlukan.'
            : stats.load_status === 'warning'
                ? 'Beban listrik mulai mendekati batas rumah.'
                : system.has_fresh_data
                    ? 'Sistem kelistrikan rumah Anda dalam kondisi normal.'
                    : 'Menunggu data terbaru dari perangkat SmartVolt.';
        setText('[data-dashboard-intro-message]', intro);

        dashboardChart = data.chart || dashboardChart;
        renderChart();
    };

    const setSwitchState = (button, isOn, online = true) => {
        button.dataset.currentState = isOn ? 'on' : 'off';
        button.classList.toggle('is-on', isOn);
        button.classList.toggle('is-offline', !online);
        button.setAttribute('aria-pressed', isOn ? 'true' : 'false');

        const label = button.querySelector('[data-switch-label]');
        if (label) {
            label.textContent = online ? (isOn ? 'Nyala' : 'Mati') : 'Offline';
        }
        button.disabled = !online;
    };

    document.addEventListener('click', async (event) => {
        const button = event.target.closest('[data-device-toggle]');
        if (!(button instanceof HTMLButtonElement) || button.disabled || button.classList.contains('is-loading')) {
            return;
        }

        const originalState = button.dataset.currentState === 'on';
        const label = button.querySelector('[data-switch-label]');
        button.classList.add('is-loading');
        button.disabled = true;
        if (label) {
            label.textContent = 'Mengirim';
        }

        try {
            const response = await window.SmartVolt.fetchJson(button.dataset.url, {method: 'POST'});
            const nextState = response.status === 'on' || response.status === true;
            setSwitchState(button, nextState, response.esp_online !== false);
            window.SmartVolt.toast(response.message || 'Status perangkat diperbarui.', {type: 'success'});

            const roomId = button.dataset.roomId;
            if (roomId) {
                const activeInRoom = document.querySelectorAll(`[data-room-id="${roomId}"] [data-device-toggle].is-on`).length;
                setText(`[data-room-active-count="${roomId}"]`, activeInRoom);
            }
        } catch (error) {
            setSwitchState(button, originalState, error.status !== 409);
            window.SmartVolt.toast(error.message || 'Perintah perangkat tidak berhasil.', {type: 'error'});
        } finally {
            button.classList.remove('is-loading');
            if (!button.classList.contains('is-offline')) {
                button.disabled = false;
            }
        }
    });

    let refreshInProgress = false;
    const refreshDashboard = async () => {
        if (refreshInProgress || document.hidden || !config.endpoint) {
            return;
        }

        refreshInProgress = true;
        try {
            const data = await window.SmartVolt.fetchJson(config.endpoint, {method: 'GET'}, 10000);
            updateDashboard(data);
        } catch (error) {
            console.warn('Pembaruan Dashboard gagal:', error.message);
        } finally {
            refreshInProgress = false;
        }
    };

    renderChart();

    const intervalSeconds = Math.min(60, Math.max(10, Number(config.refreshInterval || 30)));
    window.setInterval(refreshDashboard, intervalSeconds * 1000);
})();
```

## `payload/public/assets/js/smartvolt-energy-history.js`

```javascript
(() => {
    'use strict';

    const dataNode = document.getElementById('smartvolt-history-data');
    if (!dataNode || !window.SmartVolt) {
        return;
    }

    let config = {};
    try {
        config = JSON.parse(dataNode.textContent || '{}');
    } catch {
        window.SmartVolt.toast('Konfigurasi riwayat tidak dapat dibaca.', {type: 'error'});
        return;
    }

    const pad = (value) => String(value).padStart(2, '0');
    const dateString = (date) => `${date.getFullYear()}-${pad(date.getMonth() + 1)}-${pad(date.getDate())}`;

    document.querySelectorAll('[data-date-range]').forEach((button) => {
        button.addEventListener('click', () => {
            const from = document.getElementById('date_from');
            const to = document.getElementById('date_to');
            if (!(from instanceof HTMLInputElement) || !(to instanceof HTMLInputElement)) {
                return;
            }

            const now = new Date();
            const start = new Date(now.getFullYear(), now.getMonth(), now.getDate());
            const end = new Date(start);

            switch (button.dataset.dateRange) {
                case '7days':
                    start.setDate(start.getDate() - 6);
                    break;
                case 'month':
                    start.setDate(1);
                    break;
                case 'last-month':
                    start.setMonth(start.getMonth() - 1, 1);
                    end.setDate(0);
                    break;
                default:
                    break;
            }

            from.value = dateString(start);
            to.value = dateString(end);
            from.form?.requestSubmit();
        });
    });

    const chartData = config.chart || {labels: [], power: [], energy: []};
    const chartCanvas = document.getElementById('energyHistoryChart');
    const chartContainer = document.querySelector('[data-history-chart-container]');
    const chartEmpty = document.querySelector('[data-history-chart-empty]');
    let mode = 'power';
    let chart = null;

    const number = (value, digits = 0) => Number(value || 0).toLocaleString('id-ID', {
        minimumFractionDigits: digits,
        maximumFractionDigits: digits,
    });

    const render = () => {
        const labels = Array.isArray(chartData.labels) ? chartData.labels : [];
        const values = Array.isArray(chartData[mode]) ? chartData[mode].map((value) => Number(value || 0)) : [];
        const hasData = labels.length > 0 && values.length > 0;
        chartContainer?.classList.toggle('is-hidden', !hasData);
        chartEmpty?.classList.toggle('is-hidden', hasData);

        if (!hasData || !(chartCanvas instanceof HTMLCanvasElement) || typeof window.Chart !== 'function') {
            chart?.destroy();
            chart = null;
            return;
        }

        const label = mode === 'power' ? 'Daya' : 'Energi kumulatif';
        const unit = mode === 'power' ? 'W' : 'kWh';

        chart?.destroy();
        chart = new window.Chart(chartCanvas, {
            type: 'line',
            data: {
                labels,
                datasets: [{
                    label,
                    data: values,
                    borderColor: '#2563eb',
                    backgroundColor: 'rgba(37, 99, 235, 0.08)',
                    borderWidth: 2,
                    tension: 0.28,
                    pointRadius: 3,
                    pointHoverRadius: 5,
                    fill: true,
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: {mode: 'index', intersect: false},
                plugins: {
                    legend: {display: false},
                    tooltip: {callbacks: {label: (context) => `${label}: ${number(context.parsed.y, mode === 'power' ? 1 : 4)} ${unit}`}},
                },
                scales: {
                    x: {grid: {display: false}, ticks: {color: '#7a8699', font: {size: 10}}},
                    y: {beginAtZero: true, grid: {color: '#edf1f5'}, ticks: {color: '#7a8699', font: {size: 10}}},
                },
            },
        });
    };

    document.querySelectorAll('[data-history-chart-mode]').forEach((button) => {
        button.addEventListener('click', () => {
            mode = button.dataset.historyChartMode === 'energy' ? 'energy' : 'power';
            document.querySelectorAll('[data-history-chart-mode]').forEach((item) => {
                const active = item === button;
                item.classList.toggle('is-active', active);
                item.setAttribute('aria-selected', active ? 'true' : 'false');
            });
            render();
        });
    });

    const csvEscape = (value) => {
        const text = String(value ?? '');
        return `"${text.replaceAll('"', '""')}"`;
    };

    const downloadCsv = (rows) => {
        if (!Array.isArray(rows) || rows.length === 0) {
            throw new Error('Tidak ada data yang dapat diekspor.');
        }
        const headers = Object.keys(rows[0]);
        const lines = [
            headers.map(csvEscape).join(','),
            ...rows.map((row) => headers.map((header) => csvEscape(row[header])).join(',')),
        ];
        const blob = new Blob([`\uFEFF${lines.join('\r\n')}`], {type: 'text/csv;charset=utf-8'});
        const url = URL.createObjectURL(blob);
        const link = document.createElement('a');
        link.href = url;
        link.download = `smartvolt-pemakaian-${dateString(new Date())}.csv`;
        document.body.appendChild(link);
        link.click();
        link.remove();
        URL.revokeObjectURL(url);
    };

    document.querySelectorAll('[data-energy-export]').forEach((button) => {
        button.addEventListener('click', async () => {
            if (button.disabled) {
                return;
            }
            button.disabled = true;
            const label = button.querySelector('[data-export-label]');
            if (label) {
                label.textContent = 'Menyiapkan...';
            }

            try {
                const rows = await window.SmartVolt.fetchJson(button.dataset.exportUrl || config.exportUrl, {method: 'GET'}, 15000);
                downloadCsv(rows);
                window.SmartVolt.toast('Data pemakaian berhasil diunduh dalam format CSV.', {type: 'success'});
            } catch (error) {
                window.SmartVolt.toast(error.message || 'Data tidak dapat diekspor.', {type: 'error'});
            } finally {
                button.disabled = false;
                if (label) {
                    label.textContent = 'Unduh CSV';
                }
            }
        });
    });

    render();
})();
```

## `payload/public/assets/js/smartvolt-rooms.js`

```javascript
(() => {
    'use strict';

    if (!window.SmartVolt) {
        return;
    }

    const updateTotals = () => {
        const active = document.querySelectorAll('[data-device-toggle].is-on').length;
        document.querySelectorAll('[data-active-devices]').forEach((element) => {
            element.textContent = String(active);
        });

        document.querySelectorAll('[data-room-card]').forEach((roomCard) => {
            const roomId = roomCard.dataset.roomCard;
            const activeInRoom = roomCard.querySelectorAll('[data-device-toggle].is-on').length;
            document.querySelectorAll(`[data-room-active-count="${CSS.escape(roomId)}"]`).forEach((element) => {
                element.textContent = String(activeInRoom);
            });
        });
    };

    const setSwitchState = (button, state, online = true) => {
        button.dataset.currentState = state ? 'on' : 'off';
        button.classList.toggle('is-on', state);
        button.classList.toggle('is-offline', !online);
        button.setAttribute('aria-pressed', state ? 'true' : 'false');
        const label = button.querySelector('[data-switch-label]');
        if (label) {
            label.textContent = online ? (state ? 'Nyala' : 'Mati') : 'Offline';
        }
        button.disabled = !online;
    };

    document.addEventListener('click', async (event) => {
        const button = event.target.closest('[data-device-toggle]');
        if (!(button instanceof HTMLButtonElement) || button.disabled || button.classList.contains('is-loading')) {
            return;
        }

        const previousState = button.dataset.currentState === 'on';
        const label = button.querySelector('[data-switch-label]');
        button.disabled = true;
        button.classList.add('is-loading');
        if (label) {
            label.textContent = 'Mengirim';
        }

        try {
            const data = await window.SmartVolt.fetchJson(button.dataset.url, {method: 'POST'});
            const nextState = data.status === 'on' || data.status === true;
            setSwitchState(button, nextState, data.esp_online !== false);
            updateTotals();
            window.SmartVolt.toast(data.message || 'Perintah berhasil dikirim.', {type: 'success'});
        } catch (error) {
            setSwitchState(button, previousState, error.status !== 409);
            window.SmartVolt.toast(error.message || 'Perintah perangkat tidak berhasil.', {type: 'error'});
        } finally {
            button.classList.remove('is-loading');
            if (!button.classList.contains('is-offline')) {
                button.disabled = false;
            }
        }
    });
})();
```

## `payload/public/assets/js/smartvolt-settings.js`

```javascript
(() => {
    'use strict';

    const syncRelayNameFields = (select) => {
        const form = select.closest('[data-sensor-form]');
        const container = form?.querySelector('[data-relay-names]');
        if (!container) {
            return;
        }

        const count = Math.min(8, Math.max(1, Number(select.value || 1)));
        container.querySelectorAll('[data-relay-name-item]').forEach((item) => {
            const index = Number(item.dataset.relayNameItem || 0);
            const visible = index <= count;
            item.hidden = !visible;
            const input = item.querySelector('input');
            if (input) {
                input.required = visible;
                input.disabled = !visible;
            }
        });
    };

    document.querySelectorAll('[data-relay-count]').forEach((select) => {
        syncRelayNameFields(select);
        select.addEventListener('change', () => syncRelayNameFields(select));
    });

    const requestedTab = new URL(window.location.href).searchParams.get('tab');
    if (requestedTab) {
        const button = document.querySelector(`[data-tab-name="${CSS.escape(requestedTab)}"]`);
        if (button && !button.classList.contains('is-active')) {
            button.click();
        }
    }

    const hash = window.location.hash;
    if (hash && hash.startsWith('#room-')) {
        const room = document.querySelector(hash);
        if (room instanceof HTMLDetailsElement) {
            room.open = true;
            window.setTimeout(() => room.scrollIntoView({behavior: 'smooth', block: 'start'}), 150);
        }
    }
})();
```

## `install-smartvolt-ui-v7.ps1`

```powershell
param(
    [string]$ProjectRoot = "C:\SMARTVOLT"
)

$ErrorActionPreference = "Stop"
$ScriptRoot = Split-Path -Parent $MyInvocation.MyCommand.Path
$PayloadRoot = Join-Path $ScriptRoot "payload"

Write-Host ""
Write-Host "SmartVolt UI V7 - Professional Minimal" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan

if (-not (Test-Path $PayloadRoot)) {
    throw "Folder payload tidak ditemukan: $PayloadRoot"
}

if (-not (Test-Path $ProjectRoot)) {
    throw "Folder proyek tidak ditemukan: $ProjectRoot"
}

$ArtisanPath = Join-Path $ProjectRoot "artisan"
if (-not (Test-Path $ArtisanPath)) {
    throw "Folder tujuan bukan proyek Laravel SmartVolt karena file artisan tidak ditemukan."
}

$Timestamp = Get-Date -Format "yyyyMMdd-HHmmss"
$BackupRoot = Join-Path $ProjectRoot "storage\ui-backups\v7-professional-minimal-$Timestamp"
New-Item -ItemType Directory -Path $BackupRoot -Force | Out-Null

$Files = Get-ChildItem -Path $PayloadRoot -File -Recurse

foreach ($File in $Files) {
    $RelativePath = $File.FullName.Substring($PayloadRoot.Length).TrimStart('\', '/')
    $TargetPath = Join-Path $ProjectRoot $RelativePath

    if (Test-Path $TargetPath) {
        $BackupPath = Join-Path $BackupRoot $RelativePath
        $BackupDirectory = Split-Path -Parent $BackupPath
        New-Item -ItemType Directory -Path $BackupDirectory -Force | Out-Null
        Copy-Item -Path $TargetPath -Destination $BackupPath -Force
    }

    $TargetDirectory = Split-Path -Parent $TargetPath
    New-Item -ItemType Directory -Path $TargetDirectory -Force | Out-Null
    Copy-Item -Path $File.FullName -Destination $TargetPath -Force

    Write-Host "[OK] $RelativePath" -ForegroundColor Green
}

Write-Host ""
Write-Host "File lama dicadangkan ke:" -ForegroundColor Yellow
Write-Host $BackupRoot

$PhpCommand = Get-Command php -ErrorAction SilentlyContinue
if ($PhpCommand) {
    Push-Location $ProjectRoot
    try {
        php artisan optimize:clear
    }
    finally {
        Pop-Location
    }
} else {
    Write-Host "PHP tidak ditemukan di PATH. Jalankan 'php artisan optimize:clear' secara manual." -ForegroundColor Yellow
}

Write-Host ""
Write-Host "Pemasangan SmartVolt UI V7 berhasil." -ForegroundColor Green
Write-Host "Buka website lalu tekan Ctrl + F5." -ForegroundColor Cyan
```

## `install-smartvolt-ui-v7.cmd`

```bat
@echo off
setlocal
cd /d "%~dp0"
powershell.exe -NoProfile -ExecutionPolicy Bypass -File "%~dp0install-smartvolt-ui-v7.ps1" -ProjectRoot "C:\SMARTVOLT"
echo.
pause
```
