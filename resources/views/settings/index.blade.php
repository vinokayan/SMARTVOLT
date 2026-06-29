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
        .sv-secondary-btn {
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
        }

        .sv-primary-btn {
            background: linear-gradient(135deg, #3ea7ff, #5f7cff);
        }

        .sv-secondary-btn {
            background: rgba(255,255,255,0.08);
            border: 1px solid rgba(255,255,255,0.12);
        }

        .sv-primary-btn:hover,
        .sv-secondary-btn:hover {
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
        .sv-drawer-head {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 14px;
            margin-bottom: 16px;
        }

        .sv-modal-head h3,
        .sv-drawer-head h3 {
            margin: 0;
            font-size: 20px;
        }

        .sv-close-btn {
            width: 38px;
            height: 38px;
            border-radius: 14px;
            border: 1px solid rgba(255,255,255,0.10);
            background: rgba(255,255,255,0.06);
            color: #fff;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .sv-drawer {
            position: fixed;
            right: 18px;
            top: 18px;
            bottom: 18px;
            width: min(480px, calc(100% - 36px));
            border-radius: 28px;
            background: rgba(15, 23, 42, 0.98);
            border: 1px solid rgba(255,255,255,0.12);
            box-shadow: 0 24px 80px rgba(0,0,0,0.45);
            z-index: 90;
            padding: 22px;
            overflow-y: auto;
            display: none;
        }

        .sv-drawer.show {
            display: block;
        }

        .sv-mini-title {
            margin: 20px 0 12px;
            font-size: 14px;
            color: #dbeafe;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: 0.05em;
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

        @media (max-width: 720px) {
            .sv-drawer {
                right: 10px;
                top: 10px;
                bottom: 10px;
                width: calc(100% - 20px);
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

                <a href="{{ route('rooms') }}" class="{{ request()->routeIs('rooms*') ? 'active' : '' }}">
                    <i class="bi bi-grid-1x2-fill"></i>
                    <span>Ruangan</span>
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
                    $selectedRoomId = old('room_id', session('selected_room_id'));
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

                        <div class="sv-feature-card sv-glass" onclick="openPinModal()">
                            <div>
                                <h3>Mode Lanjutan</h3>
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

                <a href="{{ route('rooms') }}" class="{{ request()->routeIs('rooms*') ? 'active' : '' }}">
                    <i class="bi bi-grid-1x2-fill"></i>
                    <span>Ruangan</span>
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
            Mode ini hanya untuk teknisi. Pastikan ruangan, kode device / relay, dan kode pengukur listrik diisi dengan benar.
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
        <aside id="techDrawer" class="sv-drawer">
            <div class="sv-drawer-head">
                <div>
                    <h3>Panel Teknisi</h3>
                    <div class="sv-panel-sub">Tambah ruangan dan perangkat.</div>
                </div>

                <button type="button" class="sv-close-btn" onclick="closeTechPanel()">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>

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

            <div class="sv-mini-title">Tambah Perangkat</div>

            <form action="{{ route('devices.store') }}" method="POST" class="sv-form-stack">
                @csrf
                <input type="hidden" name="return_to" value="settings">

                <div class="sv-form-group">
                    <label class="sv-form-label">Ruangan</label>
                    <select name="room_id" class="sv-form-select" required>
                        <option value="">Pilih ruangan</option>
                        @foreach($roomsForDevice as $room)
                            <option value="{{ $room->id }}" {{ (string) $selectedRoomId === (string) $room->id ? 'selected' : '' }}>
                                {{ $room->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

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
                    <label class="sv-form-label">Kode Device / Relay</label>
                    <input
                        type="text"
                        name="esp32_device_id"
                        class="sv-form-input"
                        placeholder="Contoh: 2"
                        required
                    >
                </div>

                <div class="sv-form-group">
                    <label class="sv-form-label">Kode Pengukur Listrik</label>
                    <input
                        type="text"
                        name="esp_unit_id"
                        class="sv-form-input"
                        placeholder="Contoh: SV-001"
                    >
                </div>

                <button type="submit" class="sv-primary-btn">
                    <i class="bi bi-plus-circle-fill"></i>
                    Tambah Perangkat
                </button>
            </form>

            <div class="sv-form-divider" style="margin: 22px 0;"></div>

            <form action="{{ route('advanced-mode.disable') }}" method="POST">
                @csrf

                
            </form>
        </aside>
    @endif

    <script>
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
            document.getElementById('techDrawer')?.classList.add('show');
        }

        function closeTechPanel() {
            document.getElementById('techDrawer')?.classList.remove('show');
        }

        @if(session('open_advanced_panel'))
            window.addEventListener('DOMContentLoaded', function () {
                openTechPanel();
            });
        @endif

        @if(old('return_to') === 'settings')
            window.addEventListener('DOMContentLoaded', function () {
                openTechPanel();
            });
        @endif

        @if($errors->has('advanced_mode'))
            window.addEventListener('DOMContentLoaded', function () {
                openPinModal();
            });
        @endif

        @if($errors->any() && old('return_to') !== 'settings' && ! $errors->has('advanced_mode'))
            window.addEventListener('DOMContentLoaded', function () {
                openAccountModal();
            });
        @endif
    </script>
</body>
</html>