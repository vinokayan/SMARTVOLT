<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings - SmartVolt</title>

    <link rel="stylesheet" href="{{ asset('assets/css/smartvolt-brand.css') }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

    <style>
        .sv-settings-tabs {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 10px;
            padding: 8px;
            border-radius: 24px;
            margin-bottom: 24px;
            max-width: 940px;
            margin-left: auto;
            margin-right: auto;
        }

        .sv-settings-tab {
            border: none;
            border-radius: 18px;
            padding: 16px 18px;
            background: transparent;
            color: #9eb1cc;
            font-size: 15px;
            font-weight: 800;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            transition: 0.2s ease;
        }

        .sv-settings-tab:hover {
            color: #eef5ff;
            background: rgba(255,255,255,0.04);
        }

        .sv-settings-tab.active {
            color: #ffffff;
            background: linear-gradient(135deg, rgba(62, 167, 255, 0.95), rgba(95, 124, 255, 0.95));
        }

        .sv-settings-content {
            max-width: 940px;
            margin: 0 auto;
        }

        .sv-settings-panel {
            padding: 22px;
            border-radius: 28px;
        }

        .sv-settings-panel[hidden] {
            display: none !important;
        }

        .sv-settings-title-row {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 16px;
            margin-bottom: 18px;
        }

        .sv-settings-title-row h3 {
            margin: 0;
            font-size: 18px;
            letter-spacing: -0.02em;
        }

        .sv-settings-icon {
            width: 52px;
            height: 52px;
            border-radius: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 22px;
            background: linear-gradient(135deg, rgba(66, 196, 255, 0.18), rgba(94, 255, 209, 0.14));
            color: #bff4ff;
            flex-shrink: 0;
        }

        .sv-form-stack {
            display: grid;
            gap: 14px;
        }

        .sv-form-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 14px;
        }

        .sv-form-group {
            display: grid;
            gap: 8px;
        }

        .sv-form-label {
            color: #b5c8e5;
            font-size: 13px;
            font-weight: 700;
            letter-spacing: 0.04em;
            text-transform: uppercase;
        }

        .sv-form-input {
            width: 100%;
            border: 1px solid rgba(255,255,255,0.08);
            background: rgba(255,255,255,0.06);
            color: #eef5ff;
            border-radius: 18px;
            padding: 14px 16px;
            font-size: 15px;
            outline: none;
        }

        .sv-form-input::placeholder {
            color: #8ca4c8;
        }

        .sv-form-input:focus {
            border-color: rgba(90, 198, 255, 0.45);
            box-shadow: 0 0 0 4px rgba(90, 198, 255, 0.10);
        }

        .sv-form-helper {
            color: #9eb1cc;
            font-size: 14px;
            line-height: 1.6;
            margin: 0;
        }

        .sv-form-divider {
            height: 1px;
            background: rgba(255,255,255,0.07);
            margin: 4px 0;
        }

        .sv-primary-btn {
            border: none;
            border-radius: 16px;
            padding: 13px 18px;
            font-size: 14px;
            font-weight: 800;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            transition: 0.2s ease;
            background: linear-gradient(135deg, #3ea7ff, #5f7cff);
            color: #fff;
        }

        .sv-primary-btn:hover {
            transform: translateY(-1px);
        }

        .sv-status-banner,
        .sv-error-banner {
            margin-bottom: 18px;
            border-radius: 18px;
            padding: 14px 16px;
            font-size: 14px;
            font-weight: 600;
        }

        .sv-status-banner {
            background: rgba(73, 212, 155, 0.14);
            color: #c8ffe7;
            border: 1px solid rgba(73, 212, 155, 0.18);
        }

        .sv-error-banner {
            background: rgba(255, 97, 97, 0.14);
            color: #ffd7d7;
            border: 1px solid rgba(255, 97, 97, 0.18);
        }

        .sv-system-summary {
            display: grid;
            grid-template-columns: 1fr;
            gap: 12px;
            margin-bottom: 18px;
        }

        .sv-system-card {
            padding: 14px 16px;
            border-radius: 18px;
            background: rgba(255,255,255,0.04);
            border: 1px solid rgba(255,255,255,0.06);
        }

        .sv-system-card .label {
            color: #9db0cd;
            font-size: 12px;
            font-weight: 700;
            margin-bottom: 8px;
            text-transform: uppercase;
            letter-spacing: 0.08em;
        }

        .sv-system-card .value {
            color: #edf5ff;
            font-size: 18px;
            font-weight: 800;
            letter-spacing: -0.02em;
        }

        @media (min-width: 860px) {
            .sv-form-grid {
                grid-template-columns: 1fr 1fr;
            }

            .sv-span-2 {
                grid-column: 1 / -1;
            }

            .sv-system-summary {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 720px) {
            .sv-settings-tabs {
                grid-template-columns: 1fr;
            }

            .sv-settings-title-row {
                flex-direction: column-reverse;
            }
        }
    </style>
</head>

<body class="sv-dashboard-body">
    <div class="sv-app">
        <aside class="sv-sidebar">
            <div class="brand">
                <div class="icon">
                    <i class="bi bi-lightning-charge-fill"></i>
                </div>
                <span>SmartVolt</span>
            </div>

            <p>Energy command center untuk monitoring, kontrol perangkat, dan insight konsumsi listrik.</p>

            <nav class="sv-nav">
                <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'active' : '' }}">
                    <i class="bi bi-house-door-fill"></i>
                    <span>Dashboard</span>
                </a>

                <a href="{{ route('rooms') }}" class="{{ request()->routeIs('rooms*') ? 'active' : '' }}">
                    <i class="bi bi-grid-1x2-fill"></i>
                    <span>Rooms</span>
                </a>

                <a href="{{ route('energy.history') }}" class="{{ request()->routeIs('energy.history') ? 'active' : '' }}">
                    <i class="bi bi-bar-chart-fill"></i>
                    <span>Energy History</span>
                </a>

                <a href="{{ route('settings') }}" class="{{ request()->routeIs('settings*') ? 'active' : '' }}">
                    <i class="bi bi-gear-fill"></i>
                    <span>Settings</span>
                </a>
            </nav>
        </aside>

        <main class="sv-main">
            <header class="sv-topbar">
                <div class="sv-topbar-inner">
                    <div class="sv-topbar-left">
                        <button class="sv-btn sv-iconbtn" type="button">
                            <i class="bi bi-list"></i>
                        </button>

                        <div>
                            <h1 class="sv-page-title">Settings</h1>
                            <p class="sv-page-sub">Halo, {{ auth()->user()->name ?? 'User' }}</p>
                        </div>
                    </div>
                </div>
            </header>

            <section class="sv-shell">
                @if(session('status'))
                    <div class="sv-status-banner">
                        <i class="bi bi-check-circle-fill"></i>
                        {{ session('status') }}
                    </div>
                @endif

                @if($errors->any())
                    <div class="sv-error-banner">
                        <i class="bi bi-exclamation-triangle-fill"></i>
                        {{ $errors->first() }}
                    </div>
                @endif

                <div class="sv-hero">
                    <div class="sv-hero-card sv-glass" style="padding: 28px;">
                        <div>
                            <div class="sv-live-chip">
                                <span class="sv-live-dot"></span>
                                System configuration
                            </div>

                            <h1 style="margin-bottom: 10px;">Kelola akun dan pengaturan sistem SmartVolt.</h1>
                            <p style="margin: 0; color: #b9cae3;">
                                Settings digunakan untuk mengatur informasi akun dan konfigurasi sistem monitoring energi.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="sv-settings-tabs sv-glass">
                    <button type="button" class="sv-settings-tab active" data-settings-tab="account">
                        <i class="bi bi-person-fill-gear"></i>
                        Account Settings
                    </button>

                    <button type="button" class="sv-settings-tab" data-settings-tab="system">
                        <i class="bi bi-cpu-fill"></i>
                        System Settings
                    </button>
                </div>

                <div class="sv-settings-content">
                    <div class="sv-settings-panel sv-glass" data-settings-panel="account">
                        <div class="sv-settings-title-row">
                            <div>
                                <h3>Account Settings</h3>
                                <div class="sv-panel-sub">Kelola nama, email, dan password akun.</div>
                            </div>

                            <div class="sv-settings-icon">
                                <i class="bi bi-person-fill-gear"></i>
                            </div>
                        </div>

                        <div class="sv-form-stack">
                            <form method="POST" action="{{ route('settings.profile.update') }}" class="sv-form-stack">
                                @csrf
                                @method('PUT')

                                <div class="sv-form-grid">
                                    <div class="sv-form-group">
                                        <label class="sv-form-label">Name</label>
                                        <input
                                            type="text"
                                            name="name"
                                            class="sv-form-input"
                                            value="{{ old('name', $user->name) }}"
                                            placeholder="Nama pengguna"
                                            required
                                        >
                                    </div>

                                    <div class="sv-form-group">
                                        <label class="sv-form-label">Email</label>
                                        <input
                                            type="email"
                                            name="email"
                                            class="sv-form-input"
                                            value="{{ old('email', $user->email) }}"
                                            placeholder="Email pengguna"
                                            required
                                        >
                                    </div>
                                </div>

                                <div>
                                    <button type="submit" class="sv-primary-btn">
                                        <i class="bi bi-check-circle-fill"></i>
                                        Save Account
                                    </button>
                                </div>
                            </form>

                            <div class="sv-form-divider"></div>

                            <form method="POST" action="{{ route('settings.password.update') }}" class="sv-form-stack">
                                @csrf
                                @method('PUT')

                                <div class="sv-form-grid">
                                    <div class="sv-form-group sv-span-2">
                                        <label class="sv-form-label">Current Password</label>
                                        <input
                                            type="password"
                                            name="current_password"
                                            class="sv-form-input"
                                            placeholder="Password lama"
                                        >
                                    </div>

                                    <div class="sv-form-group">
                                        <label class="sv-form-label">New Password</label>
                                        <input
                                            type="password"
                                            name="password"
                                            class="sv-form-input"
                                            placeholder="Password baru"
                                        >
                                    </div>

                                    <div class="sv-form-group">
                                        <label class="sv-form-label">Confirm Password</label>
                                        <input
                                            type="password"
                                            name="password_confirmation"
                                            class="sv-form-input"
                                            placeholder="Konfirmasi password baru"
                                        >
                                    </div>
                                </div>

                                <div>
                                    <button type="submit" class="sv-primary-btn">
                                        <i class="bi bi-shield-lock-fill"></i>
                                        Update Password
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <div class="sv-settings-panel sv-glass" data-settings-panel="system" hidden>
                        <div class="sv-settings-title-row">
                            <div>
                                <h3>System Settings</h3>
                                <div class="sv-panel-sub">Pengaturan perangkat IoT dan monitoring energi.</div>
                            </div>

                            <div class="sv-settings-icon">
                                <i class="bi bi-cpu-fill"></i>
                            </div>
                        </div>

                        <div class="sv-system-summary">
                            <div class="sv-system-card">
                                <div class="label">Device Status</div>
                                <div class="value">
                                    {{ $selectedDevice?->status ? 'ON' : 'OFF' }}
                                </div>
                            </div>

                            <div class="sv-system-card">
                                <div class="label">Last Connected</div>
                                <div class="value">
                                    {{ $latestLog?->created_at?->format('d/m/Y H:i') ?? 'Belum ada data' }}
                                </div>
                            </div>
                        </div>

                        <form method="POST" action="{{ route('settings.system.update') }}" class="sv-form-stack">
                            @csrf
                            @method('PUT')

                            <input type="hidden" name="device_id" value="{{ old('device_id', $selectedDevice?->id) }}">

                            <div class="sv-form-grid">
                                <div class="sv-form-group">
                                    <label class="sv-form-label">Device Name</label>
                                    <input
                                        type="text"
                                        name="device_name"
                                        class="sv-form-input"
                                        value="{{ old('device_name', $selectedDevice?->name) }}"
                                        placeholder="Contoh: Lampu Kamar"
                                        required
                                    >
                                </div>

                                <div class="sv-form-group">
                                    <label class="sv-form-label">ESP32 Device ID</label>
                                    <input
                                        type="text"
                                        name="esp32_device_id"
                                        class="sv-form-input"
                                        value="{{ old('esp32_device_id', $selectedDevice?->esp32_device_id) }}"
                                        placeholder="Contoh: esp32_001"
                                    >
                                </div>

                                <div class="sv-form-group sv-span-2">
                                    <label class="sv-form-label">Room</label>
                                    <select name="room_id" class="sv-form-input" required>
                                        <option value="">Pilih Room</option>

                                        @foreach($rooms as $room)
                                            <option
                                                value="{{ $room->id }}"
                                                {{ old('room_id', $selectedDevice?->room_id) == $room->id ? 'selected' : '' }}
                                            >
                                                {{ $room->name }}
                                            </option>
                                        @endforeach
                                    </select>

                                    @if($rooms->isEmpty())
                                        <p class="sv-form-helper">
                                            Buat room terlebih dahulu pada menu Rooms sebelum menyimpan system settings.
                                        </p>
                                    @endif
                                </div>

                                <div class="sv-form-group">
                                    <label class="sv-form-label">Electricity Tariff</label>
                                    <input
                                        type="number"
                                        name="electricity_tariff"
                                        class="sv-form-input"
                                        value="{{ old('electricity_tariff', $systemSetting->electricity_tariff) }}"
                                        placeholder="1444"
                                        min="0"
                                        step="0.01"
                                        required
                                    >
                                </div>

                                <div class="sv-form-group">
                                    <label class="sv-form-label">Power Limit</label>
                                    <input
                                        type="number"
                                        name="power_limit"
                                        class="sv-form-input"
                                        value="{{ old('power_limit', $systemSetting->power_limit) }}"
                                        placeholder="900"
                                        min="1"
                                        required
                                    >
                                </div>

                                <div class="sv-form-group sv-span-2">
                                    <label class="sv-form-label">Refresh Interval</label>
                                    <select name="refresh_interval" class="sv-form-input" required>
                                        @foreach([1, 3, 5, 10, 15, 30, 60] as $interval)
                                            <option
                                                value="{{ $interval }}"
                                                {{ old('refresh_interval', $systemSetting->refresh_interval) == $interval ? 'selected' : '' }}
                                            >
                                                {{ $interval }} detik
                                            </option>
                                        @endforeach
                                    </select>

                                    <p class="sv-form-helper">
                                        Refresh interval menentukan seberapa sering dashboard mengambil data terbaru dari sistem.
                                    </p>
                                </div>
                            </div>

                            <div>
                                <button type="submit" class="sv-primary-btn">
                                    <i class="bi bi-save-fill"></i>
                                    Save System
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </section>
        </main>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const tabs = document.querySelectorAll('[data-settings-tab]');
            const panels = document.querySelectorAll('[data-settings-panel]');

            function showSettingsPanel(target) {
                tabs.forEach(function (tab) {
                    tab.classList.toggle('active', tab.dataset.settingsTab === target);
                });

                panels.forEach(function (panel) {
                    panel.hidden = panel.dataset.settingsPanel !== target;
                });

                localStorage.setItem('smartvolt_settings_tab', target);
            }

            tabs.forEach(function (tab) {
                tab.addEventListener('click', function () {
                    showSettingsPanel(tab.dataset.settingsTab);
                });
            });

            const savedTab = localStorage.getItem('smartvolt_settings_tab');
            const defaultTab = ['account', 'system'].includes(savedTab) ? savedTab : 'account';

            showSettingsPanel(defaultTab);
        });
    </script>
</body>
</html>