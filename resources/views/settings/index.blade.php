<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengaturan - SmartVolt</title>

    <link rel="stylesheet" href="{{ asset('assets/css/smartvolt-brand.css') }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

    <style>
        .sv-settings-content {
            max-width: 940px;
            margin: 0 auto;
            display: grid;
            gap: 22px;
        }

        .sv-settings-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 18px;
        }

        .sv-feature-card {
            border: 1px solid rgba(255,255,255,0.10);
            background: rgba(255,255,255,0.045);
            border-radius: 26px;
            padding: 22px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            transition: 0.2s ease;
        }

        .sv-feature-card:hover {
            transform: translateY(-2px);
            border-color: rgba(90, 198, 255, 0.28);
            background: rgba(255,255,255,0.065);
        }

        .sv-feature-card h3 {
            margin: 0 0 6px;
            font-size: 20px;
        }

        .sv-feature-card p {
            margin: 0;
            color: #9fb4d4;
            font-size: 14px;
            line-height: 1.5;
            max-width: 520px;
        }

        .sv-feature-icon {
            width: 56px;
            height: 56px;
            border-radius: 20px;
            background: linear-gradient(135deg, rgba(66, 196, 255, 0.20), rgba(94, 255, 209, 0.14));
            color: #bff4ff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            flex-shrink: 0;
        }

        .sv-panel-sub {
            color: #9fb4d4;
            font-size: 14px;
            line-height: 1.6;
            margin-top: 6px;
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
            font-weight: 800;
            letter-spacing: 0.04em;
            text-transform: uppercase;
        }

        .sv-form-input,
        .sv-form-select {
            width: 100%;
            border: 1px solid rgba(255,255,255,0.08);
            background: rgba(255,255,255,0.06);
            color: #eef5ff;
            border-radius: 18px;
            padding: 14px 16px;
            font-size: 15px;
            outline: none;
        }

        .sv-form-select option {
            color: #111827;
        }

        .sv-form-input::placeholder {
            color: #8ca4c8;
        }

        .sv-form-input:focus,
        .sv-form-select:focus {
            border-color: rgba(90, 198, 255, 0.45);
            box-shadow: 0 0 0 4px rgba(90, 198, 255, 0.10);
        }

        .sv-form-divider {
            height: 1px;
            background: rgba(255,255,255,0.07);
            margin: 8px 0;
        }

        .sv-primary-btn,
        .sv-secondary-btn,
        .sv-danger-btn {
            border: none;
            border-radius: 16px;
            padding: 13px 18px;
            font-size: 14px;
            font-weight: 900;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            transition: 0.2s ease;
            color: #fff;
            text-decoration: none;
        }

        .sv-primary-btn {
            background: linear-gradient(135deg, #3ea7ff, #5f7cff);
        }

        .sv-secondary-btn {
            background: rgba(255,255,255,0.08);
            border: 1px solid rgba(255,255,255,0.12);
        }

        .sv-danger-btn {
            background: rgba(239, 68, 68, 0.16);
            border: 1px solid rgba(239, 68, 68, 0.32);
            color: #fecaca;
        }

        .sv-primary-btn:hover,
        .sv-secondary-btn:hover,
        .sv-danger-btn:hover {
            transform: translateY(-1px);
        }

        .sv-status-banner,
        .sv-error-banner,
        .sv-warning-banner {
            margin-bottom: 18px;
            border-radius: 18px;
            padding: 14px 16px;
            font-size: 14px;
            font-weight: 700;
            line-height: 1.6;
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

        .sv-warning-banner {
            background: rgba(245, 158, 11, 0.14);
            color: #fff1c7;
            border: 1px solid rgba(245, 158, 11, 0.22);
        }

        .sv-modal-backdrop {
            position: fixed;
            inset: 0;
            background: rgba(2, 6, 23, 0.10);
            z-index: 80;
            display: none;
        }

        .sv-modal-backdrop.show {
            display: block;
        }

        .sv-modal {
            width: min(760px, calc(100% - 28px));
            max-height: calc(100vh - 36px);
            overflow-y: auto;
            border-radius: 26px;
            padding: 22px;
            background: rgba(15, 23, 42, 0.98);
            border: 1px solid rgba(255,255,255,0.12);
            box-shadow: 0 24px 70px rgba(0,0,0,0.40);
            position: fixed;
            left: 50%;
            top: 50%;
            transform: translate(-50%, -50%);
            z-index: 90;
            display: none;
        }

        .sv-modal.show {
            display: block;
        }

        .sv-pin-modal {
            width: min(440px, calc(100% - 28px));
        }

        .sv-modal-head,
        .sv-tech-head {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 14px;
            margin-bottom: 16px;
        }

        .sv-modal-head h3,
        .sv-tech-head h3 {
            margin: 0;
            font-size: 22px;
        }

        .sv-close-btn {
            width: 42px;
            height: 42px;
            border-radius: 16px;
            border: 1px solid rgba(255,255,255,0.10);
            background: rgba(255,255,255,0.06);
            color: #fff;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            font-size: 18px;
        }

        .sv-tech-screen {
            position: fixed;
            inset: 0;
            z-index: 95;
            background:
                radial-gradient(circle at top left, rgba(56, 189, 248, 0.16), transparent 34%),
                radial-gradient(circle at bottom right, rgba(99, 102, 241, 0.16), transparent 36%),
                rgba(2, 6, 23, 0.98);
            display: none;
            overflow-y: auto;
            padding: 26px;
        }

        .sv-tech-screen.show {
            display: block;
        }

        .sv-tech-container {
            max-width: 1180px;
            margin: 0 auto;
        }

        .sv-tech-card {
            border-radius: 28px;
            background: rgba(15, 23, 42, 0.82);
            border: 1px solid rgba(255,255,255,0.10);
            padding: 24px;
            box-shadow: 0 28px 90px rgba(0,0,0,0.34);
        }

        .sv-tech-layout {
            display: grid;
            grid-template-columns: 0.95fr 1.35fr;
            gap: 18px;
            align-items: start;
        }

        .sv-tech-section {
            border-radius: 24px;
            background: rgba(255,255,255,0.045);
            border: 1px solid rgba(255,255,255,0.08);
            padding: 20px;
        }

        .sv-mini-title {
            margin: 0 0 14px;
            font-size: 14px;
            color: #dbeafe;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .sv-room-list,
        .sv-device-list {
            display: grid;
            gap: 12px;
            margin-top: 16px;
        }

        .sv-room-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            padding: 14px;
            border-radius: 18px;
            background: rgba(255,255,255,0.045);
            border: 1px solid rgba(255,255,255,0.08);
        }

        .sv-room-row h4 {
            margin: 0;
            font-size: 15px;
            color: #eef5ff;
        }

        .sv-room-row p {
            margin: 4px 0 0;
            color: #9fb4d4;
            font-size: 13px;
        }

        .sv-device-edit-card {
            display: grid;
            gap: 14px;
            padding: 16px;
            border-radius: 20px;
            background: rgba(255,255,255,0.045);
            border: 1px solid rgba(255,255,255,0.08);
        }

        .sv-device-edit-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
        }

        .sv-device-edit-head h4 {
            margin: 0;
            color: #eef5ff;
            font-size: 16px;
        }

        .sv-device-edit-head p {
            margin: 4px 0 0;
            color: #9fb4d4;
            font-size: 13px;
        }

        @media (min-width: 860px) {
            .sv-settings-grid {
                grid-template-columns: 1fr 1fr;
            }

            .sv-form-grid {
                grid-template-columns: 1fr 1fr;
            }

            .sv-span-2 {
                grid-column: 1 / -1;
            }
        }

        @media (max-width: 980px) {
            .sv-tech-layout {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 720px) {
            .sv-tech-screen {
                padding: 12px;
            }

            .sv-tech-card,
            .sv-tech-section {
                padding: 16px;
            }

            .sv-room-row,
            .sv-device-edit-head {
                align-items: stretch;
                flex-direction: column;
            }
        }

        /* ==========================
           PANEL TEKNISI REVISI
           ========================== */

        .sv-tech-layout {
            grid-template-columns: 360px 1fr;
            gap: 18px;
        }

        .sv-room-row {
            cursor: pointer;
            transition: 0.2s ease;
        }

        .sv-room-row.active,
        .sv-room-row:hover {
            border-color: rgba(90, 198, 255, 0.36);
            background: rgba(90, 198, 255, 0.08);
        }

        .sv-room-row button {
            width: 100%;
            border: none;
            background: transparent;
            color: inherit;
            text-align: left;
            padding: 0;
            cursor: pointer;
        }

        .sv-room-detail-panel {
            display: none;
        }

        .sv-room-detail-panel.active {
            display: grid;
            gap: 18px;
        }

        .sv-selected-room-head {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 14px;
            margin-bottom: 16px;
        }

        .sv-selected-room-head h4 {
            margin: 0;
            color: #eef5ff;
            font-size: 18px;
        }

        .sv-selected-room-head p {
            margin: 5px 0 0;
            color: #9fb4d4;
            font-size: 13px;
        }

        .sv-tech-actions {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            gap: 10px;
            flex-wrap: wrap;
        }

        .sv-edit-panel {
            display: none;
            border-radius: 20px;
            padding: 16px;
            background: rgba(255,255,255,0.045);
            border: 1px solid rgba(255,255,255,0.08);
        }

        .sv-edit-panel.show {
            display: block;
        }

        .sv-device-summary-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 10px;
        }

        .sv-device-summary-grid div {
            border-radius: 16px;
            padding: 12px;
            background: rgba(255,255,255,0.055);
            border: 1px solid rgba(255,255,255,0.08);
        }

        .sv-device-summary-grid span {
            display: block;
            margin-bottom: 6px;
            color: #9fb4d4;
            font-size: 11px;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }

        .sv-device-summary-grid strong {
            color: #eef5ff;
            font-size: 14px;
            word-break: break-word;
        }

        .sv-device-edit-card .sv-form-stack {
            margin-top: 0;
        }

        @media (max-width: 980px) {
            .sv-tech-layout {
                grid-template-columns: 1fr;
            }

            .sv-device-summary-grid {
                grid-template-columns: 1fr 1fr;
            }
        }

        @media (max-width: 720px) {
            .sv-selected-room-head {
                flex-direction: column;
            }

            .sv-tech-actions {
                justify-content: flex-start;
            }

            .sv-device-summary-grid {
                grid-template-columns: 1fr;
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

            <p>Kontrol perangkat dan pantau pemakaian listrik rumah dengan mudah.</p>

            <nav class="sv-nav">
                <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'active' : '' }}">
                    <i class="bi bi-house-door-fill"></i>
                    <span>Beranda</span>
                </a>

                <a href="{{ route('energy.history') }}" class="{{ request()->routeIs('energy.history') ? 'active' : '' }}">
                    <i class="bi bi-bar-chart-fill"></i>
                    <span>Pemakaian Listrik</span>
                </a>

                <a href="{{ route('settings') }}" class="{{ request()->routeIs('settings*') ? 'active' : '' }}">
                    <i class="bi bi-gear-fill"></i>
                    <span>Pengaturan</span>
                </a>
            </nav>
        </aside>

        <main class="sv-main">
            <header class="sv-topbar">
                <div class="sv-topbar-inner">
                    <div class="sv-topbar-left">
                        <div>
                            <h1 class="sv-page-title">Pengaturan</h1>
                            <p class="sv-page-sub">Halo, {{ auth()->user()->name ?? 'User' }}</p>
                        </div>
                    </div>
                </div>
            </header>

            <section class="sv-shell">
                @php
                    $advancedMode = session('advanced_mode', false);
                    $roomsForDevice = $rooms ?? collect();
                    $devicesForEdit = $devices ?? collect();
                    $selectedRoomId = old('room_id', session('selected_room_id', session('open_room_id')));

                    if (! $selectedRoomId && $roomsForDevice->isNotEmpty()) {
                        $selectedRoomId = $roomsForDevice->first()->id;
                    }
                @endphp

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
                                Pengaturan
                            </div>

                            <h1 style="margin-bottom: 10px;">Atur SmartVolt</h1>
                            <p style="color: #cfe3ff; line-height: 1.7; max-width: 760px;">
                                Pilih pengaturan akun atau buka mode teknisi.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="sv-settings-content">
                    <div class="sv-settings-grid">
                        <div class="sv-feature-card sv-glass" onclick="openAccountModal()">
                            <div>
                                <h3>Pengaturan Akun</h3>
                                <p>Ubah nama, email, dan password.</p>
                            </div>

                            <div class="sv-feature-icon">
                                <i class="bi bi-person-fill-gear"></i>
                            </div>
                        </div>

                        <div class="sv-feature-card sv-glass" onclick="{{ $advancedMode ? 'openTechPanel()' : 'openPinModal()' }}">
                            <div>
                                <h3>Konfigurasi Sistem</h3>
                                <p>Masuk hanya untuk teknisi. Perubahan yang salah dapat membuat perangkat tidak berjalan.</p>
                            </div>

                            <div class="sv-feature-icon">
                                <i class="bi bi-sliders"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <nav class="sv-bottomnav">
                <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'active' : '' }}">
                    <i class="bi bi-house-door-fill"></i>
                    <span>Beranda</span>
                </a>

                <a href="{{ route('energy.history') }}" class="{{ request()->routeIs('energy.history') ? 'active' : '' }}">
                    <i class="bi bi-bar-chart-fill"></i>
                    <span>Listrik</span>
                </a>

                <a href="{{ route('settings') }}" class="{{ request()->routeIs('settings*') ? 'active' : '' }}">
                    <i class="bi bi-gear-fill"></i>
                    <span>Pengaturan</span>
                </a>
            </nav>
        </main>
    </div>

    <div id="accountBackdrop" class="sv-modal-backdrop" onclick="closeAccountModal()"></div>

    <div id="accountModal" class="sv-modal">
        <div class="sv-modal-head">
            <div>
                <h3>Pengaturan Akun</h3>
                <div class="sv-panel-sub">Nama, email, dan password.</div>
            </div>

            <button type="button" class="sv-close-btn" onclick="closeAccountModal()">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>

        <div class="sv-form-stack">
            <form method="POST" action="{{ route('settings.profile.update') }}" class="sv-form-stack">
                @csrf
                @method('PUT')

                <div class="sv-form-grid">
                    <div class="sv-form-group">
                        <label class="sv-form-label">Nama</label>
                        <input
                            type="text"
                            name="name"
                            class="sv-form-input"
                            value="{{ old('name', $user->name) }}"
                            placeholder="Masukkan nama"
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
                            placeholder="Masukkan email"
                            required
                        >
                    </div>
                </div>

                <button type="submit" class="sv-primary-btn">
                    <i class="bi bi-check-circle-fill"></i>
                    Simpan
                </button>
            </form>

            <div class="sv-form-divider"></div>

            <form method="POST" action="{{ route('settings.password.update') }}" class="sv-form-stack">
                @csrf
                @method('PUT')

                <div class="sv-form-grid">
                    <div class="sv-form-group sv-span-2">
                        <label class="sv-form-label">Password Saat Ini</label>
                        <input
                            type="password"
                            name="current_password"
                            class="sv-form-input"
                            placeholder="Password saat ini"
                        >
                    </div>

                    <div class="sv-form-group">
                        <label class="sv-form-label">Password Baru</label>
                        <input
                            type="password"
                            name="password"
                            class="sv-form-input"
                            placeholder="Password baru"
                        >
                    </div>

                    <div class="sv-form-group">
                        <label class="sv-form-label">Konfirmasi Password</label>
                        <input
                            type="password"
                            name="password_confirmation"
                            class="sv-form-input"
                            placeholder="Ulangi password baru"
                        >
                    </div>
                </div>

                <button type="submit" class="sv-primary-btn">
                    <i class="bi bi-shield-lock-fill"></i>
                    Ubah Password
                </button>
            </form>
        </div>
    </div>

    <div id="pinBackdrop" class="sv-modal-backdrop" onclick="closePinModal()"></div>

    <div id="pinModal" class="sv-modal sv-pin-modal">
        <div class="sv-modal-head">
            <div>
                <h3>Mode Lanjutan</h3>
                <div class="sv-panel-sub">Masukkan PIN teknisi.</div>
            </div>

            <button type="button" class="sv-close-btn" onclick="closePinModal()">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>

        <div class="sv-warning-banner">
            Mode ini hanya untuk teknisi. Pastikan ruangan, relay, dan kode sensor diisi dengan benar.
        </div>

        <form action="{{ route('advanced-mode.enable') }}" method="POST" class="sv-form-stack">
            @csrf

            <div class="sv-form-group">
                <label class="sv-form-label">PIN</label>
                <input
                    type="password"
                    name="pin"
                    class="sv-form-input"
                    placeholder="Masukkan PIN"
                    required
                    autofocus
                >
            </div>

            <button type="submit" class="sv-primary-btn">
                Masuk
            </button>
        </form>
    </div>

    @if($advancedMode)
        <section id="techScreen" class="sv-tech-screen">
            <div class="sv-tech-container">
                <div class="sv-tech-card">
                    <div class="sv-tech-head">
                        <div>
                            <h3>Panel Teknisi</h3>
                            <div class="sv-panel-sub">Kelola ruangan dan perangkat SmartVolt.</div>
                        </div>

                        <button type="button" class="sv-close-btn" onclick="closeTechPanel()">
                            <i class="bi bi-x-lg"></i>
                        </button>
                    </div>

                    <div class="sv-tech-layout">
                        <div class="sv-tech-section">
                            <div class="sv-mini-title">Tambah Ruangan</div>

                            <form action="{{ route('rooms.store') }}" method="POST" class="sv-form-stack">
                                @csrf
                                <input type="hidden" name="return_to" value="settings">

                                <div class="sv-form-group">
                                    <label class="sv-form-label">Nama Ruangan</label>
                                    <input
                                        type="text"
                                        name="name"
                                        class="sv-form-input"
                                        placeholder="Contoh: Kamar, Dapur"
                                        required
                                    >
                                </div>

                                <button type="submit" class="sv-primary-btn">
                                    <i class="bi bi-plus-circle-fill"></i>
                                    Tambah Ruangan
                                </button>
                            </form>

                            <div class="sv-form-divider" style="margin: 22px 0;"></div>

                            <div class="sv-mini-title">Ruangan Terdaftar</div>

                            <div class="sv-room-list">
                                @forelse($roomsForDevice as $room)
                                    @php
                                        $roomDevices = $room->devices ?? collect();
                                        $deviceCount = $room->devices_count ?? $roomDevices->count();
                                    @endphp

                                    <div
                                        id="tech-room-tab-{{ $room->id }}"
                                        class="sv-room-row {{ (string) $selectedRoomId === (string) $room->id ? 'active' : '' }}"
                                    >
                                        <button type="button" onclick="showTechRoom({{ $room->id }})">
                                            <h4>{{ $room->name }}</h4>
                                            <p>{{ $deviceCount }} perangkat</p>
                                        </button>
                                    </div>
                                @empty
                                    <div class="sv-panel-sub">Belum ada ruangan.</div>
                                @endforelse
                            </div>
                        </div>

                        <div class="sv-tech-section">
                            @forelse($roomsForDevice as $room)
                                @php
                                    $roomDevices = $room->devices ?? collect();
                                @endphp

                                <div
                                    id="tech-room-panel-{{ $room->id }}"
                                    class="sv-room-detail-panel {{ (string) $selectedRoomId === (string) $room->id ? 'active' : '' }}"
                                >
                                    <div>
                                        <div class="sv-selected-room-head">
                                            <div>
                                                <h4>{{ $room->name }}</h4>
                                                <p>Kelola perangkat yang berada di ruangan ini.</p>
                                            </div>

                                            <div class="sv-tech-actions">
                                                <button
                                                    type="button"
                                                    class="sv-secondary-btn"
                                                    onclick="toggleTechBox('edit-room-{{ $room->id }}')"
                                                >
                                                    <i class="bi bi-pencil-square"></i>
                                                    Ubah
                                                </button>

                                                <button
                                                    type="button"
                                                    class="sv-primary-btn"
                                                    onclick="toggleTechBox('add-device-{{ $room->id }}')"
                                                >
                                                    <i class="bi bi-plus-circle-fill"></i>
                                                    Tambah
                                                </button>

                                                <form
                                                    action="{{ route('rooms.destroy', $room->id) }}"
                                                    method="POST"
                                                    onsubmit="return confirm('Hapus ruangan ini beserta semua perangkat di dalamnya?');"
                                                >
                                                    @csrf
                                                    @method('DELETE')
                                                    <input type="hidden" name="return_to" value="settings">

                                                    <button type="submit" class="sv-danger-btn">
                                                        <i class="bi bi-trash-fill"></i>
                                                        Hapus
                                                    </button>
                                                </form>
                                            </div>
                                        </div>

                                        <div id="edit-room-{{ $room->id }}" class="sv-edit-panel">
                                            <form action="{{ route('rooms.update', $room->id) }}" method="POST" class="sv-form-stack">
                                                @csrf
                                                @method('PUT')
                                                <input type="hidden" name="return_to" value="settings">

                                                <div class="sv-form-group">
                                                    <label class="sv-form-label">Nama Ruangan</label>
                                                    <input
                                                        type="text"
                                                        name="name"
                                                        class="sv-form-input"
                                                        value="{{ old('name', $room->name) }}"
                                                        required
                                                    >
                                                </div>

                                                <button type="submit" class="sv-primary-btn">
                                                    <i class="bi bi-check-circle-fill"></i>
                                                    Simpan
                                                </button>
                                            </form>
                                        </div>
                                    </div>

                                    <div id="add-device-{{ $room->id }}" class="sv-edit-panel">
                                        <div class="sv-mini-title">Tambah Perangkat di {{ $room->name }}</div>

                                        <form action="{{ route('devices.store', $room->id) }}" method="POST" class="sv-form-stack">
                                            @csrf
                                            <input type="hidden" name="return_to" value="settings">

                                            <div class="sv-form-grid">
                                                <div class="sv-form-group">
                                                    <label class="sv-form-label">Nama Perangkat</label>
                                                    <input
                                                        type="text"
                                                        name="name"
                                                        class="sv-form-input"
                                                        placeholder="Contoh: Lampu Utama"
                                                        required
                                                    >
                                                </div>

                                                <div class="sv-form-group">
                                                    <label class="sv-form-label">Kode Relay</label>
                                                    <input
                                                        type="text"
                                                        name="relay_code"
                                                        class="sv-form-input"
                                                        placeholder="Contoh: RELAY-1"
                                                        required
                                                    >
                                                </div>

                                                <div class="sv-form-group sv-span-2">
                                                    <label class="sv-form-label">Kode Sensor</label>
                                                    <input
                                                        type="text"
                                                        name="esp_unit_id"
                                                        class="sv-form-input"
                                                        placeholder="Contoh: SV-001"
                                                        required
                                                    >
                                                </div>
                                            </div>

                                            <button type="submit" class="sv-primary-btn">
                                                <i class="bi bi-check-circle-fill"></i>
                                                Simpan Perangkat
                                            </button>
                                        </form>
                                    </div>

                                    <div class="sv-form-divider" style="margin: 4px 0;"></div>

                                    <div>
                                        <div class="sv-mini-title">Perangkat Terdaftar</div>

                                        <div class="sv-device-list">
                                            @forelse($roomDevices as $device)
                                                <div class="sv-device-edit-card">
                                                    <div class="sv-device-edit-head">
                                                        <div>
                                                            <h4>{{ $device->name }}</h4>
                                                            <p>{{ $room->name }}</p>
                                                        </div>

                                                        <div class="sv-tech-actions">
                                                            <button
                                                                type="button"
                                                                class="sv-secondary-btn"
                                                                onclick="toggleTechBox('edit-device-{{ $device->id }}')"
                                                            >
                                                                <i class="bi bi-pencil-square"></i>
                                                                Ubah
                                                            </button>

                                                            <form
                                                                action="{{ route('devices.destroy', $device->id) }}"
                                                                method="POST"
                                                                onsubmit="return confirm('Hapus perangkat ini?');"
                                                            >
                                                                @csrf
                                                                @method('DELETE')
                                                                <input type="hidden" name="return_to" value="settings">

                                                                <button type="submit" class="sv-danger-btn">
                                                                    <i class="bi bi-trash-fill"></i>
                                                                    Hapus
                                                                </button>
                                                            </form>
                                                        </div>
                                                    </div>

                                                    <div class="sv-device-summary-grid">
                                                        <div>
                                                            <span>Kode Relay</span>
                                                            <strong>{{ $device->relay_code ?? $device->esp32_device_id ?? '-' }}</strong>
                                                        </div>

                                                        <div>
                                                            <span>Kode Sensor</span>
                                                            <strong>{{ $device->esp_unit_id ?? '-' }}</strong>
                                                        </div>

                                                        <div>
                                                            <span>Status</span>
                                                            <strong>{{ $device->status ? 'Nyala' : 'Mati' }}</strong>
                                                        </div>
                                                    </div>

                                                    <div id="edit-device-{{ $device->id }}" class="sv-edit-panel">
                                                        <form action="{{ route('devices.update', $device->id) }}" method="POST" class="sv-form-stack">
                                                            @csrf
                                                            @method('PUT')
                                                            <input type="hidden" name="return_to" value="settings">

                                                            <div class="sv-form-grid">
                                                                <div class="sv-form-group">
                                                                    <label class="sv-form-label">Nama Perangkat</label>
                                                                    <input
                                                                        type="text"
                                                                        name="name"
                                                                        class="sv-form-input"
                                                                        value="{{ old('name', $device->name) }}"
                                                                        required
                                                                    >
                                                                </div>

                                                                <div class="sv-form-group">
                                                                    <label class="sv-form-label">Kode Relay</label>
                                                                    <input
                                                                        type="text"
                                                                        name="relay_code"
                                                                        class="sv-form-input"
                                                                        value="{{ old('relay_code', $device->relay_code ?? $device->esp32_device_id) }}"
                                                                        required
                                                                    >
                                                                </div>

                                                                <div class="sv-form-group">
                                                                    <label class="sv-form-label">Kode Sensor</label>
                                                                    <input
                                                                        type="text"
                                                                        name="esp_unit_id"
                                                                        class="sv-form-input"
                                                                        value="{{ old('esp_unit_id', $device->esp_unit_id) }}"
                                                                        placeholder="Contoh: SV-001"
                                                                        required
                                                                    >
                                                                </div>
                                                            </div>

                                                            <button type="submit" class="sv-primary-btn">
                                                                <i class="bi bi-check-circle-fill"></i>
                                                                Simpan
                                                            </button>
                                                        </form>
                                                    </div>
                                                </div>
                                            @empty
                                                <div class="sv-panel-sub">Belum ada perangkat di ruangan ini.</div>
                                            @endforelse
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="sv-panel-sub">
                                    Belum ada ruangan. Tambahkan ruangan terlebih dahulu sebelum menambah perangkat.
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </section>
    @endif

    <script>
        const shouldOpenTechPanel = {{ \Illuminate\Support\Js::from(session('open_advanced_panel') || old('return_to') === 'settings') }};
        const shouldOpenPinModal = {{ \Illuminate\Support\Js::from($errors->has('advanced_mode')) }};
        const shouldOpenAccountModal = {{ \Illuminate\Support\Js::from($errors->any() && old('return_to') !== 'settings' && ! $errors->has('advanced_mode')) }};
        const selectedTechRoomId = {{ \Illuminate\Support\Js::from((string) $selectedRoomId) }};

        function openAccountModal() {
            document.getElementById('accountBackdrop')?.classList.add('show');
            document.getElementById('accountModal')?.classList.add('show');
        }

        function closeAccountModal() {
            document.getElementById('accountBackdrop')?.classList.remove('show');
            document.getElementById('accountModal')?.classList.remove('show');
        }

        function openPinModal() {
            document.getElementById('pinBackdrop')?.classList.add('show');
            document.getElementById('pinModal')?.classList.add('show');
        }

        function closePinModal() {
            document.getElementById('pinBackdrop')?.classList.remove('show');
            document.getElementById('pinModal')?.classList.remove('show');
        }

        function openTechPanel() {
            document.getElementById('techScreen')?.classList.add('show');
            openSelectedTechRoom();
        }

        function closeTechPanel() {
            document.getElementById('techScreen')?.classList.remove('show');
        }

        function showTechRoom(roomId) {
            const tabs = document.querySelectorAll('.sv-room-row');
            const panels = document.querySelectorAll('.sv-room-detail-panel');

            tabs.forEach(function (tab) {
                tab.classList.remove('active');
            });

            panels.forEach(function (panel) {
                panel.classList.remove('active');
            });

            const activeTab = document.getElementById('tech-room-tab-' + roomId);
            const activePanel = document.getElementById('tech-room-panel-' + roomId);

            if (activeTab) {
                activeTab.classList.add('active');
            }

            if (activePanel) {
                activePanel.classList.add('active');
            }
        }

        function toggleTechBox(id) {
            const box = document.getElementById(id);

            if (box) {
                box.classList.toggle('show');
            }
        }

        function openSelectedTechRoom() {
            if (selectedTechRoomId) {
                showTechRoom(selectedTechRoomId);
                return;
            }

            const firstRoom = document.querySelector('.sv-room-row');

            if (firstRoom) {
                const firstRoomId = firstRoom.id.replace('tech-room-tab-', '');
                showTechRoom(firstRoomId);
            }
        }

        window.addEventListener('DOMContentLoaded', function () {
            openSelectedTechRoom();

            if (shouldOpenTechPanel) {
                openTechPanel();
            }

            if (shouldOpenPinModal) {
                openPinModal();
            }

            if (shouldOpenAccountModal) {
                openAccountModal();
            }
        });
    </script>
</body>
</html>