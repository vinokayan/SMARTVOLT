<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengaturan - SmartVolt</title>

    <link rel="stylesheet" href="{{ asset('assets/css/smartvolt-brand.css') }}?v={{ filemtime(public_path('assets/css/smartvolt-brand.css')) }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

   <style>
    .sv-settings-content {
        max-width: 980px;
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
        background: linear-gradient(180deg, rgba(255,255,255,0.06), rgba(255,255,255,0.035));
        border-radius: 28px;
        padding: 24px;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 18px;
        transition: 0.22s ease;
        box-shadow: 0 18px 50px rgba(0,0,0,0.18);
    }

    .sv-feature-card:hover {
        transform: translateY(-3px);
        border-color: rgba(90, 198, 255, 0.28);
        background: linear-gradient(180deg, rgba(255,255,255,0.08), rgba(255,255,255,0.045));
    }

    .sv-feature-card h3 {
        margin: 0 0 8px;
        font-size: 20px;
    }

    .sv-feature-card p,
    .sv-panel-sub {
        margin: 0;
        color: #9fb4d4;
        font-size: 14px;
        line-height: 1.6;
    }

    .sv-feature-icon {
        width: 58px;
        height: 58px;
        border-radius: 20px;
        background: linear-gradient(135deg, rgba(66, 196, 255, 0.20), rgba(94, 255, 209, 0.14));
        color: #bff4ff;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
        flex-shrink: 0;
        box-shadow: inset 0 1px 0 rgba(255,255,255,0.12);
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
        font-size: 12px;
        font-weight: 800;
        letter-spacing: 0.06em;
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
        transition: 0.18s ease;
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
        background: linear-gradient(90deg, rgba(255,255,255,0.06), rgba(255,255,255,0.10), rgba(255,255,255,0.06));
        margin: 18px 0;
    }

    .sv-primary-btn,
    .sv-secondary-btn,
    .sv-danger-btn,
    .sv-success-btn {
        border-radius: 16px;
        padding: 12px 18px;
        font-size: 14px;
        font-weight: 800;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        transition: 0.18s ease;
        color: #fff;
        text-decoration: none;
        border: none;
        white-space: nowrap;
    }

    .sv-primary-btn {
        background: linear-gradient(135deg, #3ea7ff, #5f7cff);
        box-shadow: 0 10px 24px rgba(76, 120, 255, 0.26);
    }

    .sv-secondary-btn {
        background: rgba(255,255,255,0.08);
        border: 1px solid rgba(255,255,255,0.12);
    }

    .sv-danger-btn {
        background: rgba(239, 68, 68, 0.14);
        border: 1px solid rgba(239, 68, 68, 0.22);
        color: #fecaca;
    }

    .sv-success-btn {
        background: rgba(16, 185, 129, 0.14);
        border: 1px solid rgba(16, 185, 129, 0.22);
        color: #bbf7d0;
    }

    .sv-primary-btn:hover,
    .sv-secondary-btn:hover,
    .sv-danger-btn:hover,
    .sv-success-btn:hover {
        transform: translateY(-1px);
        filter: brightness(1.04);
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
        background: rgba(2, 6, 23, 0.18);
        z-index: 80;
        display: none;
        backdrop-filter: blur(4px);
    }

    .sv-modal-backdrop.show {
        display: block;
    }

    .sv-modal {
        width: min(760px, calc(100% - 28px));
        max-height: calc(100vh - 36px);
        overflow-y: auto;
        border-radius: 28px;
        padding: 24px;
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
        margin-bottom: 18px;
    }

    .sv-modal-head h3,
    .sv-tech-head h3 {
        margin: 0;
        font-size: 24px;
    }

    .sv-close-btn {
        width: 44px;
        height: 44px;
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
        transition: 0.18s ease;
    }

    .sv-close-btn:hover {
        background: rgba(255,255,255,0.10);
    }

    .sv-tech-screen {
        position: fixed;
        inset: 0;
        z-index: 95;
        display: none;
        overflow-y: auto;
        padding: 20px;
        background:
            radial-gradient(circle at top left, rgba(56, 189, 248, 0.14), transparent 32%),
            radial-gradient(circle at bottom right, rgba(99, 102, 241, 0.16), transparent 34%),
            linear-gradient(180deg, rgba(2, 6, 23, 0.96), rgba(2, 6, 23, 0.98));
        backdrop-filter: blur(8px);
    }

    .sv-tech-screen.show {
        display: block;
    }

    .sv-tech-container {
        max-width: 1380px;
        margin: 0 auto;
    }

    .sv-tech-card {
        border-radius: 32px;
        background: linear-gradient(180deg, rgba(15, 23, 42, 0.92), rgba(15, 23, 42, 0.84));
        border: 1px solid rgba(255,255,255,0.10);
        padding: 22px;
        box-shadow: 0 36px 90px rgba(0,0,0,0.34);
    }

    .sv-tech-layout {
        display: grid;
        grid-template-columns: 320px minmax(0, 1fr);
        gap: 22px;
        align-items: start;
    }

    .sv-tech-section {
        border-radius: 26px;
        background: linear-gradient(180deg, rgba(255,255,255,0.045), rgba(255,255,255,0.03));
        border: 1px solid rgba(255,255,255,0.08);
        padding: 20px;
    }

    .sv-tech-sidebar {
        position: sticky;
        top: 8px;
    }

    .sv-mini-title {
        margin: 0 0 14px;
        font-size: 13px;
        color: #dbeafe;
        font-weight: 900;
        text-transform: uppercase;
        letter-spacing: 0.08em;
    }

    .sv-room-list,
    .sv-card-list {
        display: grid;
        gap: 14px;
        margin-top: 14px;
    }

    .sv-room-row {
        cursor: pointer;
        transition: 0.18s ease;
        padding: 16px;
        border-radius: 20px;
        background: rgba(255,255,255,0.04);
        border: 1px solid rgba(255,255,255,0.07);
        box-shadow: inset 0 1px 0 rgba(255,255,255,0.03);
    }

    .sv-room-row.active,
    .sv-room-row:hover {
        border-color: rgba(90, 198, 255, 0.34);
        background: linear-gradient(180deg, rgba(36, 106, 180, 0.18), rgba(53, 84, 140, 0.10));
        transform: translateY(-1px);
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

    .sv-room-row h4 {
        margin: 0;
        font-size: 17px;
        color: #eef5ff;
    }

    .sv-room-row p {
        margin: 6px 0 0;
        color: #9fb4d4;
        font-size: 13px;
        line-height: 1.5;
    }

    .sv-room-detail-panel {
        display: none;
        gap: 18px;
    }

    .sv-room-detail-panel.active {
        display: grid;
    }

    .sv-room-hero {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 18px;
        margin-bottom: 16px;
    }

    .sv-room-kicker {
        display: inline-block;
        margin-bottom: 8px;
        font-size: 11px;
        font-weight: 800;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        color: #8dc4ff;
    }

    .sv-room-hero h4 {
        margin: 0;
        color: #eef5ff;
        font-size: 30px;
        line-height: 1.1;
    }

    .sv-room-hero p {
        margin: 8px 0 0;
        color: #a8bdd9;
        font-size: 14px;
        line-height: 1.6;
        max-width: 720px;
    }

    .sv-room-stat-wrap {
        display: flex;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
        justify-content: flex-end;
    }

    .sv-stat-pill {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 10px 14px;
        border-radius: 999px;
        background: rgba(255,255,255,0.06);
        border: 1px solid rgba(255,255,255,0.08);
        color: #e6f0ff;
        font-size: 13px;
        font-weight: 700;
    }

    .sv-tech-toolbar {
        display: flex;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
        margin-bottom: 16px;
    }

    .sv-edit-panel {
        display: none;
        border-radius: 22px;
        padding: 18px;
        background: linear-gradient(180deg, rgba(255,255,255,0.045), rgba(255,255,255,0.03));
        border: 1px solid rgba(255,255,255,0.08);
        margin-top: 14px;
    }

    .sv-edit-panel.show {
        display: block;
    }


    .sv-edit-panel-head {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 14px;
        margin-bottom: 14px;
    }

    .sv-edit-panel-head .sv-block-title {
        margin-bottom: 0;
    }

    .sv-panel-close-btn {
        width: 40px;
        height: 40px;
        border-radius: 14px;
        border: 1px solid rgba(255,255,255,0.10);
        background: rgba(255,255,255,0.06);
        color: #eaf3ff;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        font-size: 17px;
        transition: 0.18s ease;
    }

    .sv-panel-close-btn:hover,
    .sv-panel-close-btn:focus-visible {
        background: rgba(239, 68, 68, 0.14);
        border-color: rgba(248, 113, 113, 0.30);
        color: #fecaca;
        outline: none;
    }

    .sv-form-actions {
        display: flex;
        align-items: center;
        justify-content: flex-end;
        gap: 12px;
        flex-wrap: wrap;
    }

    .sv-form-actions .sv-primary-btn,
    .sv-form-actions .sv-secondary-btn,
    .sv-form-actions .sv-success-btn,
    .sv-form-actions .sv-danger-btn {
        min-width: 160px;
    }

    .sv-block-title {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 12px;
    }

    .sv-block-title i {
        font-size: 18px;
        color: #8fd6ff;
    }

    .sv-block-title h5 {
        margin: 0;
        font-size: 18px;
        color: #f2f7ff;
    }

    .sv-block-title p {
        margin: 4px 0 0;
        color: #9fb4d4;
        font-size: 13px;
    }

    .sv-info-card {
        display: grid;
        gap: 14px;
        padding: 18px;
        border-radius: 24px;
        background: linear-gradient(180deg, rgba(255,255,255,0.05), rgba(255,255,255,0.03));
        border: 1px solid rgba(255,255,255,0.08);
        box-shadow: inset 0 1px 0 rgba(255,255,255,0.03);
    }

    .sv-sensor-card {
        border-color: rgba(56, 189, 248, 0.18);
        background: linear-gradient(180deg, rgba(56, 189, 248, 0.08), rgba(255,255,255,0.03));
    }

    .sv-card-head {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 14px;
    }

    .sv-card-head h4 {
        margin: 0;
        color: #eef5ff;
        font-size: 17px;
    }

    .sv-card-head p {
        margin: 6px 0 0;
        color: #9fb4d4;
        font-size: 13px;
    }

    .sv-summary-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 12px;
    }

    .sv-summary-grid div {
        border-radius: 18px;
        padding: 14px;
        background: rgba(255,255,255,0.05);
        border: 1px solid rgba(255,255,255,0.07);
    }

    .sv-summary-grid span {
        display: block;
        margin-bottom: 8px;
        color: #9fb4d4;
        font-size: 11px;
        font-weight: 900;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }

    .sv-summary-grid strong {
        color: #eef5ff;
        font-size: 15px;
        word-break: break-word;
        line-height: 1.4;
    }

    .sv-empty-state {
        display: flex;
        align-items: flex-start;
        gap: 14px;
        border-radius: 22px;
        padding: 18px;
        background: rgba(255,255,255,0.035);
        border: 1px dashed rgba(255,255,255,0.10);
    }

    .sv-empty-state i {
        width: 42px;
        height: 42px;
        border-radius: 14px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: rgba(255,255,255,0.06);
        color: #9fdcff;
        font-size: 18px;
        flex-shrink: 0;
    }

    .sv-empty-state h6 {
        margin: 0 0 5px;
        font-size: 15px;
        color: #eef5ff;
    }

    .sv-empty-state p {
        margin: 0;
        color: #9fb4d4;
        font-size: 13px;
        line-height: 1.6;
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

    @media (max-width: 1180px) {
        .sv-tech-layout {
            grid-template-columns: 1fr;
        }

        .sv-tech-sidebar {
            position: static;
        }
    }

    @media (max-width: 980px) {
        .sv-summary-grid {
            grid-template-columns: 1fr 1fr;
        }

        .sv-room-hero {
            flex-direction: column;
            align-items: flex-start;
        }

        .sv-room-stat-wrap {
            justify-content: flex-start;
        }
    }

    @media (max-width: 720px) {
        .sv-tech-screen {
            padding: 10px;
        }

        .sv-tech-card,
        .sv-tech-section,
        .sv-modal {
            padding: 16px;
        }

        .sv-tech-head,
        .sv-card-head {
            flex-direction: column;
        }


        .sv-edit-panel-head {
            align-items: flex-start;
        }

        .sv-form-actions {
            flex-direction: column;
            align-items: stretch;
        }

        .sv-form-actions .sv-primary-btn,
        .sv-form-actions .sv-secondary-btn,
        .sv-form-actions .sv-success-btn,
        .sv-form-actions .sv-danger-btn {
            width: 100%;
            min-width: 0;
        }

        .sv-tech-toolbar {
            flex-direction: column;
            align-items: stretch;
        }

        .sv-tech-toolbar .sv-primary-btn,
        .sv-tech-toolbar .sv-secondary-btn,
        .sv-tech-toolbar .sv-danger-btn,
        .sv-tech-toolbar .sv-success-btn {
            width: 100%;
        }

        .sv-summary-grid {
            grid-template-columns: 1fr;
        }

        .sv-room-hero h4 {
            font-size: 24px;
        }

        .sv-feature-card {
            padding: 18px;
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

                    <div class="sv-topbar-right">
                        <div class="sv-action-cluster">
                            @include('components.notification-bell')

                            <form action="{{ route('logout') }}" method="POST" class="sv-logout-form">
                                @csrf
                                <button type="submit" class="sv-btn sv-logout-btn">
                                    <i class="bi bi-box-arrow-right"></i>
                                    <span>Keluar</span>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </header>

            <section class="sv-shell">
                @php
                    $advancedMode = session('advanced_mode', false);
                    $roomsForDevice = $rooms ?? collect();
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
                        <div class="sv-feature-card sv-glass js-open-account-modal">
                            <div>
                                <h3>Pengaturan Akun</h3>
                                <p>Ubah nama, email, dan password.</p>
                            </div>

                            <div class="sv-feature-icon">
                                <i class="bi bi-person-fill-gear"></i>
                            </div>
                        </div>

                        <div class="sv-feature-card sv-glass js-open-tech-or-pin">
                            <div>
                                <h3>Konfigurasi Sistem</h3>
                                <p>Masuk hanya untuk teknisi. Kelola ruangan, sensor listrik, ESP, dan relay.</p>
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

    <div id="accountBackdrop" class="sv-modal-backdrop js-close-account-modal"></div>

    <div id="accountModal" class="sv-modal">
        <div class="sv-modal-head">
            <div>
                <h3>Pengaturan Akun</h3>
                <div class="sv-panel-sub">Nama, email, dan password.</div>
            </div>

            <button type="button" class="sv-close-btn js-close-account-modal" aria-label="Tutup pengaturan akun" title="Tutup">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>

        <div class="sv-form-stack">
            <form id="accountProfileForm" method="POST" action="{{ route('settings.profile.update') }}" class="sv-form-stack">
                @csrf
                @method('PUT')

                <div class="sv-form-grid">
                    <div class="sv-form-group">
                        <label class="sv-form-label">Nama</label>
                        <input type="text" name="name" class="sv-form-input" value="{{ old('name', $user->name) }}" required>
                    </div>

                    <div class="sv-form-group">
                        <label class="sv-form-label">Email</label>
                        <input type="email" name="email" class="sv-form-input" value="{{ old('email', $user->email) }}" required>
                    </div>
                </div>

                <div class="sv-form-actions">
                    <button
                        type="button"
                        class="sv-secondary-btn js-close-account-modal js-reset-form"
                        data-form-id="accountProfileForm"
                    >
                        <i class="bi bi-x-circle"></i>
                        Batal
                    </button>

                    <button type="submit" class="sv-primary-btn">
                        <i class="bi bi-check-circle-fill"></i>
                        Simpan
                    </button>
                </div>
            </form>

            <div class="sv-form-divider"></div>

            <form id="accountPasswordForm" method="POST" action="{{ route('settings.password.update') }}" class="sv-form-stack">
                @csrf
                @method('PUT')

                <div class="sv-form-grid">
                    <div class="sv-form-group sv-span-2">
                        <label class="sv-form-label">Password Saat Ini</label>
                        <input type="password" name="current_password" class="sv-form-input">
                    </div>

                    <div class="sv-form-group">
                        <label class="sv-form-label">Password Baru</label>
                        <input type="password" name="password" class="sv-form-input">
                    </div>

                    <div class="sv-form-group">
                        <label class="sv-form-label">Konfirmasi Password</label>
                        <input type="password" name="password_confirmation" class="sv-form-input">
                    </div>
                </div>

                <div class="sv-form-actions">
                    <button
                        type="button"
                        class="sv-secondary-btn js-close-account-modal js-reset-form"
                        data-form-id="accountPasswordForm"
                    >
                        <i class="bi bi-x-circle"></i>
                        Batal
                    </button>

                    <button type="submit" class="sv-primary-btn">
                        <i class="bi bi-shield-lock-fill"></i>
                        Ubah Password
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div id="pinBackdrop" class="sv-modal-backdrop js-close-pin-modal"></div>

    <div id="pinModal" class="sv-modal sv-pin-modal">
        <div class="sv-modal-head">
            <div>
                <h3>Mode Lanjutan</h3>
                <div class="sv-panel-sub">Masukkan PIN teknisi.</div>
            </div>

            <button type="button" class="sv-close-btn js-close-pin-modal" aria-label="Tutup verifikasi PIN" title="Tutup">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>

        <div class="sv-warning-banner">
            Mode ini hanya untuk teknisi. Pastikan ruangan, sensor listrik, ESP Unit ID, dan relay diisi dengan benar.
        </div>

        <form id="technicianPinForm" action="{{ route('advanced-mode.enable') }}" method="POST" class="sv-form-stack">
            @csrf

            <div class="sv-form-group">
                <label class="sv-form-label">PIN</label>
                <input type="password" name="pin" class="sv-form-input" placeholder="Masukkan PIN" required autofocus>
            </div>

            <div class="sv-form-actions">
                <button
                    type="button"
                    class="sv-secondary-btn js-close-pin-modal js-reset-form"
                    data-form-id="technicianPinForm"
                >
                    <i class="bi bi-x-circle"></i>
                    Batal
                </button>

                <button type="submit" class="sv-primary-btn">
                    <i class="bi bi-box-arrow-in-right"></i>
                    Masuk
                </button>
            </div>
        </form>
    </div>

    @if($advancedMode)
    <section id="techScreen" class="sv-tech-screen">
        <div class="sv-tech-container">
            <div class="sv-tech-card">
                <div class="sv-tech-head">
                    <div>
                        <h3>Panel Teknisi</h3>
                        <div class="sv-panel-sub">
                            Kelola ruangan, sensor listrik, ESP, dan relay SmartVolt dengan lebih rapi dan terstruktur.
                        </div>
                    </div>

                    <button type="button" class="sv-close-btn js-close-tech-panel" aria-label="Tutup panel teknisi" title="Tutup">
                        <i class="bi bi-x-lg"></i>
                    </button>
                </div>

                <div class="sv-tech-layout">
                    <div class="sv-tech-section sv-tech-sidebar">
                        <div class="sv-block-title">
                            <i class="bi bi-plus-square-fill"></i>
                            <div>
                                <h5>Tambah Ruangan</h5>
                                <p>Buat ruangan baru sebelum menambahkan sensor listrik dan relay.</p>
                            </div>
                        </div>

                        <form id="createRoomForm" action="{{ route('rooms.store') }}" method="POST" class="sv-form-stack">
                            @csrf
                            <input type="hidden" name="return_to" value="settings">

                            <div class="sv-form-group">
                                <label class="sv-form-label">Nama Ruangan</label>
                                <input type="text" name="name" class="sv-form-input" placeholder="Contoh: Kamar, Dapur" required>
                            </div>

                            <div class="sv-form-actions">
                                <button
                                    type="button"
                                    class="sv-secondary-btn js-reset-form"
                                    data-form-id="createRoomForm"
                                >
                                    <i class="bi bi-arrow-counterclockwise"></i>
                                    Bersihkan
                                </button>

                                <button type="submit" class="sv-primary-btn">
                                    <i class="bi bi-plus-circle-fill"></i>
                                    Tambah Ruangan
                                </button>
                            </div>
                        </form>

                        <div class="sv-form-divider"></div>

                        <div class="sv-block-title" style="margin-bottom: 0;">
                            <i class="bi bi-grid-1x2-fill"></i>
                            <div>
                                <h5>Ruangan Terdaftar</h5>
                                <p>Pilih ruangan untuk melihat sensor listrik dan relay yang terpasang.</p>
                            </div>
                        </div>

                        <div class="sv-room-list">
                            @forelse($roomsForDevice as $room)
                                @php
                                    $deviceCount = $room->devices_count ?? $room->devices->count();
                                    $sensorCount = $room->energy_meters_count ?? $room->energyMeters->count();
                                @endphp

                                <div
                                    id="tech-room-tab-{{ $room->id }}"
                                    class="sv-room-row {{ (string) $selectedRoomId === (string) $room->id ? 'active' : '' }}"
                                >
                                    <button
                                        type="button"
                                        class="js-show-tech-room"
                                        data-room-id="{{ $room->id }}"
                                    >
                                        <h4>{{ $room->name }}</h4>
                                        <p>{{ $sensorCount }} sensor listrik, {{ $deviceCount }} relay</p>
                                    </button>
                                </div>
                            @empty
                                <div class="sv-empty-state">
                                    <i class="bi bi-house-door"></i>
                                    <div>
                                        <h6>Belum ada ruangan</h6>
                                        <p>Tambahkan ruangan terlebih dahulu agar sensor listrik dan relay bisa dikelola.</p>
                                    </div>
                                </div>
                            @endforelse
                        </div>
                    </div>

                    <div class="sv-tech-section">
                        @forelse($roomsForDevice as $room)
                            @php
                                $roomDevices = $room->devices ?? collect();
                                $roomSensors = $room->energyMeters ?? collect();
                            @endphp

                            <div
                                id="tech-room-panel-{{ $room->id }}"
                                class="sv-room-detail-panel {{ (string) $selectedRoomId === (string) $room->id ? 'active' : '' }}"
                            >
                                <div>
                                    <div class="sv-room-hero">
                                        <div>
                                            <span class="sv-room-kicker">Ruangan aktif</span>
                                            <h4>{{ $room->name }}</h4>
                                            <p>Kelola sensor listrik ruangan dan perangkat relay yang ada di ruangan ini.</p>
                                        </div>

                                        <div class="sv-room-stat-wrap">
                                            <span class="sv-stat-pill">
                                                <i class="bi bi-cpu-fill"></i>
                                                {{ $roomSensors->count() }} Sensor
                                            </span>
                                            <span class="sv-stat-pill">
                                                <i class="bi bi-toggle-on"></i>
                                                {{ $roomDevices->count() }} Relay
                                            </span>
                                        </div>
                                    </div>

                                    <div class="sv-tech-toolbar">
                                        <button
                                            type="button"
                                            class="sv-secondary-btn js-toggle-tech-box"
                                            data-target="edit-room-{{ $room->id }}"
                                        >
                                            <i class="bi bi-pencil-square"></i>
                                            Ubah Ruangan
                                        </button>

                                        <button
                                            type="button"
                                            class="sv-primary-btn js-toggle-tech-box"
                                            data-target="add-sensor-{{ $room->id }}"
                                        >
                                            <i class="bi bi-cpu-fill"></i>
                                            Tambah ESP + Sensor Listrik
                                        </button>

                                        <button
                                            type="button"
                                            class="sv-success-btn js-toggle-tech-box"
                                            data-target="add-relay-{{ $room->id }}"
                                        >
                                            <i class="bi bi-toggle-on"></i>
                                            Tambah Relay
                                        </button>

                                        <form
                                            action="{{ route('rooms.destroy', $room->id) }}"
                                            method="POST"
                                            class="js-confirm-submit"
                                            data-confirm="Hapus ruangan ini beserta semua perangkat relay di dalamnya? Sensor listrik yang punya riwayat sebaiknya dinonaktifkan terlebih dahulu."
                                        >
                                            @csrf
                                            @method('DELETE')
                                            <input type="hidden" name="return_to" value="settings">

                                            <button type="submit" class="sv-danger-btn">
                                                <i class="bi bi-trash-fill"></i>
                                                Hapus Ruangan
                                            </button>
                                        </form>
                                    </div>

                                    <div id="edit-room-{{ $room->id }}" class="sv-edit-panel">
                                        <div class="sv-edit-panel-head">
                                            <div class="sv-block-title">
                                                <i class="bi bi-pencil-square"></i>
                                                <div>
                                                    <h5>Ubah Ruangan</h5>
                                                    <p>Perbarui nama ruangan ini.</p>
                                                </div>
                                            </div>

                                            <button
                                                type="button"
                                                class="sv-panel-close-btn js-close-tech-box"
                                                data-target="edit-room-{{ $room->id }}"
                                                data-form-id="edit-room-form-{{ $room->id }}"
                                                aria-label="Batalkan perubahan ruangan"
                                                title="Batal"
                                            >
                                                <i class="bi bi-x-lg"></i>
                                            </button>
                                        </div>

                                        <form id="edit-room-form-{{ $room->id }}" action="{{ route('rooms.update', $room->id) }}" method="POST" class="sv-form-stack">
                                            @csrf
                                            @method('PUT')
                                            <input type="hidden" name="return_to" value="settings">

                                            <div class="sv-form-group">
                                                <label class="sv-form-label">Nama Ruangan</label>
                                                <input type="text" name="name" class="sv-form-input" value="{{ old('name', $room->name) }}" required>
                                            </div>

                                            <div class="sv-form-actions">
                                                <button
                                                    type="button"
                                                    class="sv-secondary-btn js-close-tech-box"
                                                    data-target="edit-room-{{ $room->id }}"
                                                    data-form-id="edit-room-form-{{ $room->id }}"
                                                >
                                                    <i class="bi bi-x-circle"></i>
                                                    Batal
                                                </button>

                                                <button type="submit" class="sv-primary-btn">
                                                    <i class="bi bi-check-circle-fill"></i>
                                                    Simpan Perubahan
                                                </button>
                                            </div>
                                        </form>
                                    </div>

                                    <div id="add-sensor-{{ $room->id }}" class="sv-edit-panel">
                                        <div class="sv-edit-panel-head">
                                            <div class="sv-block-title">
                                                <i class="bi bi-cpu-fill"></i>
                                                <div>
                                                    <h5>Tambah ESP + Sensor Listrik</h5>
                                                    <p>Daftarkan ESP dan sensor listrik ruangan sekaligus bersama relay awalnya.</p>
                                                </div>
                                            </div>

                                            <button
                                                type="button"
                                                class="sv-panel-close-btn js-close-tech-box"
                                                data-target="add-sensor-{{ $room->id }}"
                                                data-form-id="add-sensor-form-{{ $room->id }}"
                                                aria-label="Batalkan penambahan sensor listrik"
                                                title="Batal"
                                            >
                                                <i class="bi bi-x-lg"></i>
                                            </button>
                                        </div>

                                        <form id="add-sensor-form-{{ $room->id }}" action="{{ route('technician.rooms.sensor.store', $room->id) }}" method="POST" class="sv-form-stack">
                                            @csrf

                                            <div class="sv-form-grid">
                                                <div class="sv-form-group">
                                                    <label class="sv-form-label">Nama Sensor Listrik</label>
                                                    <input type="text" name="sensor_name" class="sv-form-input" placeholder="Contoh: Sensor Listrik Dapur" required>
                                                </div>

                                                <div class="sv-form-group">
                                                    <label class="sv-form-label">ESP Unit ID</label>
                                                    <input type="text" name="esp_unit_id" class="sv-form-input" placeholder="Contoh: 2" required>
                                                </div>

                                                <div class="sv-form-group">
                                                    <label class="sv-form-label">Meter Code</label>
                                                    <input type="text" name="meter_code" class="sv-form-input" value="main" required>
                                                </div>

                                                <div class="sv-form-group">
                                                    <label class="sv-form-label">Tipe Sensor</label>
                                                    <input type="text" name="sensor_type" class="sv-form-input" value="PZEM004T">
                                                </div>

                                                <div class="sv-form-group sv-span-2">
                                                    <label class="sv-form-label">Jumlah Relay</label>
                                                    <select
                                                        name="relay_count"
                                                        class="sv-form-select js-relay-count-select"
                                                        data-room-id="{{ $room->id }}"
                                                        required
                                                    >
                                                        @for($i = 1; $i <= 8; $i++)
                                                            <option value="{{ $i }}" {{ $i === 2 ? 'selected' : '' }}>
                                                                {{ $i }} channel relay
                                                            </option>
                                                        @endfor
                                                    </select>
                                                </div>

                                                @for($i = 1; $i <= 8; $i++)
                                                    <div
                                                        class="sv-form-group relay-input relay-input-room-{{ $room->id }}"
                                                        data-channel="{{ $i }}"
                                                        style="{{ $i <= 2 ? '' : 'display: none;' }}"
                                                    >
                                                        <label class="sv-form-label">Nama Relay {{ $i }}</label>
                                                        <input
                                                            type="text"
                                                            name="relay_names[{{ $i }}]"
                                                            class="sv-form-input"
                                                            placeholder="Contoh: Lampu Dapur"
                                                        >
                                                    </div>
                                                @endfor
                                            </div>

                                            <div class="sv-form-actions">
                                                <button
                                                    type="button"
                                                    class="sv-secondary-btn js-close-tech-box"
                                                    data-target="add-sensor-{{ $room->id }}"
                                                    data-form-id="add-sensor-form-{{ $room->id }}"
                                                >
                                                    <i class="bi bi-x-circle"></i>
                                                    Batal
                                                </button>

                                                <button type="submit" class="sv-primary-btn">
                                                    <i class="bi bi-check-circle-fill"></i>
                                                    Simpan ESP + Sensor Listrik
                                                </button>
                                            </div>
                                        </form>
                                    </div>

                                    <div id="add-relay-{{ $room->id }}" class="sv-edit-panel">
                                        <div class="sv-edit-panel-head">
                                            <div class="sv-block-title">
                                                <i class="bi bi-toggle-on"></i>
                                                <div>
                                                    <h5>Tambah Relay</h5>
                                                    <p>Tambahkan relay baru ke ESP yang sudah terdaftar pada ruangan ini.</p>
                                                </div>
                                            </div>

                                            <button
                                                type="button"
                                                class="sv-panel-close-btn js-close-tech-box"
                                                data-target="add-relay-{{ $room->id }}"
                                                data-form-id="add-relay-form-{{ $room->id }}"
                                                aria-label="Batalkan penambahan relay"
                                                title="Batal"
                                            >
                                                <i class="bi bi-x-lg"></i>
                                            </button>
                                        </div>

                                        @if($roomSensors->isNotEmpty())
                                            <form id="add-relay-form-{{ $room->id }}" action="{{ route('technician.rooms.relay.store', $room->id) }}" method="POST" class="sv-form-stack">
                                                @csrf

                                                <div class="sv-form-grid">
                                                    <div class="sv-form-group">
                                                        <label class="sv-form-label">ESP Unit ID</label>
                                                        <select name="esp_unit_id" class="sv-form-select" required>
                                                            @foreach($roomSensors as $sensor)
                                                                <option value="{{ $sensor->esp_unit_id }}">
                                                                    {{ $sensor->esp_unit_id }} - {{ $sensor->name }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    </div>

                                                    <div class="sv-form-group">
                                                        <label class="sv-form-label">Relay Channel</label>
                                                        <input type="text" name="relay_code" class="sv-form-input" placeholder="Contoh: 3" required>
                                                    </div>

                                                    <div class="sv-form-group sv-span-2">
                                                        <label class="sv-form-label">Nama Perangkat Relay</label>
                                                        <input type="text" name="name" class="sv-form-input" placeholder="Contoh: Stop Kontak Dapur" required>
                                                    </div>
                                                </div>

                                                <div class="sv-form-actions">
                                                    <button
                                                        type="button"
                                                        class="sv-secondary-btn js-close-tech-box"
                                                        data-target="add-relay-{{ $room->id }}"
                                                        data-form-id="add-relay-form-{{ $room->id }}"
                                                    >
                                                        <i class="bi bi-x-circle"></i>
                                                        Batal
                                                    </button>

                                                    <button type="submit" class="sv-success-btn">
                                                        <i class="bi bi-check-circle-fill"></i>
                                                        Simpan Relay
                                                    </button>
                                                </div>
                                            </form>
                                        @else
                                            <div class="sv-empty-state">
                                                <i class="bi bi-exclamation-circle"></i>
                                                <div>
                                                    <h6>Sensor listrik belum tersedia</h6>
                                                    <p>Tambahkan ESP + Sensor Listrik terlebih dahulu sebelum menambahkan relay.</p>
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                </div>

                                <div class="sv-form-divider"></div>

                                <div>
                                    <div class="sv-block-title">
                                        <i class="bi bi-cpu-fill"></i>
                                        <div>
                                            <h5>Sensor Listrik Ruangan</h5>
                                            <p>Daftar sensor listrik yang memantau pemakaian listrik ruangan ini.</p>
                                        </div>
                                    </div>

                                    <div class="sv-card-list">
                                        @forelse($roomSensors as $sensor)
                                            <div class="sv-info-card sv-sensor-card">
                                                <div class="sv-card-head">
                                                    <div>
                                                        <h4>{{ $sensor->name }}</h4>
                                                        <p>{{ $room->name }}</p>
                                                    </div>

                                                    <div class="sv-tech-toolbar" style="margin-bottom: 0;">
                                                        <button
                                                            type="button"
                                                            class="sv-secondary-btn js-toggle-tech-box"
                                                            data-target="edit-sensor-{{ $sensor->id }}"
                                                        >
                                                            <i class="bi bi-pencil-square"></i>
                                                            Ubah
                                                        </button>

                                                        <form action="{{ route('technician.sensors.toggle', $sensor->id) }}" method="POST">
                                                            @csrf
                                                            @method('PATCH')

                                                            <button type="submit" class="{{ $sensor->is_active ? 'sv-secondary-btn' : 'sv-success-btn' }}">
                                                                <i class="bi {{ $sensor->is_active ? 'bi-pause-circle-fill' : 'bi-play-circle-fill' }}"></i>
                                                                {{ $sensor->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                                            </button>
                                                        </form>

                                                        <form
                                                            action="{{ route('technician.sensors.destroy', $sensor->id) }}"
                                                            method="POST"
                                                            class="js-confirm-submit"
                                                            data-confirm="Hapus sensor listrik ini? Jika sudah punya riwayat, sensor akan dinonaktifkan agar data lama tetap aman."
                                                        >
                                                            @csrf
                                                            @method('DELETE')

                                                            <button type="submit" class="sv-danger-btn">
                                                                <i class="bi bi-trash-fill"></i>
                                                                Hapus
                                                            </button>
                                                        </form>
                                                    </div>
                                                </div>

                                                <div class="sv-summary-grid">
                                                    <div>
                                                        <span>ESP Unit ID</span>
                                                        <strong>{{ $sensor->esp_unit_id }}</strong>
                                                    </div>

                                                    <div>
                                                        <span>Meter Code</span>
                                                        <strong>{{ $sensor->meter_code }}</strong>
                                                    </div>

                                                    <div>
                                                        <span>Status Sensor</span>
                                                        <strong>{{ $sensor->is_active ? 'Aktif' : 'Nonaktif' }}</strong>
                                                    </div>

                                                    <div>
                                                        <span>Riwayat Data</span>
                                                        <strong>{{ $sensor->readings_count ?? 0 }} data</strong>
                                                    </div>
                                                </div>

                                                <div id="edit-sensor-{{ $sensor->id }}" class="sv-edit-panel">
                                                    <div class="sv-edit-panel-head">
                                                        <div class="sv-block-title">
                                                            <i class="bi bi-pencil-square"></i>
                                                            <div>
                                                                <h5>Ubah Sensor Listrik</h5>
                                                                <p>Perbarui nama, meter code, dan status sensor.</p>
                                                            </div>
                                                        </div>

                                                        <button
                                                            type="button"
                                                            class="sv-panel-close-btn js-close-tech-box"
                                                            data-target="edit-sensor-{{ $sensor->id }}"
                                                            data-form-id="edit-sensor-form-{{ $sensor->id }}"
                                                            aria-label="Batalkan perubahan sensor listrik"
                                                            title="Batal"
                                                        >
                                                            <i class="bi bi-x-lg"></i>
                                                        </button>
                                                    </div>

                                                    <form id="edit-sensor-form-{{ $sensor->id }}" action="{{ route('technician.sensors.update', $sensor->id) }}" method="POST" class="sv-form-stack">
                                                        @csrf
                                                        @method('PUT')

                                                        <div class="sv-form-grid">
                                                            <div class="sv-form-group">
                                                                <label class="sv-form-label">Nama Sensor Listrik</label>
                                                                <input type="text" name="name" class="sv-form-input" value="{{ old('name', $sensor->name) }}" required>
                                                            </div>

                                                            <div class="sv-form-group">
                                                                <label class="sv-form-label">ESP Unit ID</label>
                                                                <input type="text" class="sv-form-input" value="{{ $sensor->esp_unit_id }}" disabled>
                                                            </div>

                                                            <div class="sv-form-group">
                                                                <label class="sv-form-label">Meter Code</label>
                                                                <input type="text" name="meter_code" class="sv-form-input" value="{{ old('meter_code', $sensor->meter_code) }}" required>
                                                            </div>

                                                            <div class="sv-form-group">
                                                                <label class="sv-form-label">Tipe Sensor</label>
                                                                <input type="text" name="sensor_type" class="sv-form-input" value="{{ old('sensor_type', $sensor->sensor_type) }}">
                                                            </div>

                                                            <div class="sv-form-group sv-span-2">
                                                                <label style="display: flex; align-items: center; gap: 10px; color: #dbeafe;">
                                                                    <input type="checkbox" name="is_active" value="1" {{ $sensor->is_active ? 'checked' : '' }}>
                                                                    Sensor aktif
                                                                </label>
                                                            </div>
                                                        </div>

                                                        <div class="sv-form-actions">
                                                            <button
                                                                type="button"
                                                                class="sv-secondary-btn js-close-tech-box"
                                                                data-target="edit-sensor-{{ $sensor->id }}"
                                                                data-form-id="edit-sensor-form-{{ $sensor->id }}"
                                                            >
                                                                <i class="bi bi-x-circle"></i>
                                                                Batal
                                                            </button>

                                                            <button type="submit" class="sv-primary-btn">
                                                                <i class="bi bi-check-circle-fill"></i>
                                                                Simpan Sensor Listrik
                                                            </button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        @empty
                                            <div class="sv-empty-state">
                                                <i class="bi bi-cpu"></i>
                                                <div>
                                                    <h6>Belum ada sensor listrik</h6>
                                                    <p>Tambahkan ESP + Sensor Listrik terlebih dahulu agar pemakaian listrik ruangan bisa dipantau.</p>
                                                </div>
                                            </div>
                                        @endforelse
                                    </div>
                                </div>

                                <div class="sv-form-divider"></div>

                                <div>
                                    <div class="sv-block-title">
                                        <i class="bi bi-toggle-on"></i>
                                        <div>
                                            <h5>Perangkat / Relay Terdaftar</h5>
                                            <p>Daftar relay perangkat yang terhubung pada ruangan ini.</p>
                                        </div>
                                    </div>

                                    <div class="sv-card-list">
                                        @forelse($roomDevices as $device)
                                            <div class="sv-info-card">
                                                <div class="sv-card-head">
                                                    <div>
                                                        <h4>{{ $device->name }}</h4>
                                                        <p>{{ $room->name }}</p>
                                                    </div>

                                                    <div class="sv-tech-toolbar" style="margin-bottom: 0;">
                                                        <button
                                                            type="button"
                                                            class="sv-secondary-btn js-toggle-tech-box"
                                                            data-target="edit-device-{{ $device->id }}"
                                                        >
                                                            <i class="bi bi-pencil-square"></i>
                                                            Ubah
                                                        </button>

                                                        <form
                                                            action="{{ route('devices.destroy', $device->id) }}"
                                                            method="POST"
                                                            class="js-confirm-submit"
                                                            data-confirm="Hapus relay ini?"
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

                                                <div class="sv-summary-grid" style="grid-template-columns: repeat(3, minmax(0, 1fr));">
                                                    <div>
                                                        <span>Relay Channel</span>
                                                        <strong>{{ $device->relay_code ?? '-' }}</strong>
                                                    </div>

                                                    <div>
                                                        <span>ESP Unit ID</span>
                                                        <strong>{{ $device->esp_unit_id ?? $device->esp32_device_id ?? '-' }}</strong>
                                                    </div>

                                                    <div>
                                                        <span>Status Relay</span>
                                                        <strong>{{ $device->status ? 'Nyala' : 'Mati' }}</strong>
                                                    </div>
                                                </div>

                                                <div id="edit-device-{{ $device->id }}" class="sv-edit-panel">
                                                    <div class="sv-edit-panel-head">
                                                        <div class="sv-block-title">
                                                            <i class="bi bi-pencil-square"></i>
                                                            <div>
                                                                <h5>Ubah Relay</h5>
                                                                <p>Perbarui nama relay, channel relay, dan ESP Unit ID.</p>
                                                            </div>
                                                        </div>

                                                        <button
                                                            type="button"
                                                            class="sv-panel-close-btn js-close-tech-box"
                                                            data-target="edit-device-{{ $device->id }}"
                                                            data-form-id="edit-device-form-{{ $device->id }}"
                                                            aria-label="Batalkan perubahan relay"
                                                            title="Batal"
                                                        >
                                                            <i class="bi bi-x-lg"></i>
                                                        </button>
                                                    </div>

                                                    <form id="edit-device-form-{{ $device->id }}" action="{{ route('devices.update', $device->id) }}" method="POST" class="sv-form-stack">
                                                        @csrf
                                                        @method('PUT')
                                                        <input type="hidden" name="return_to" value="settings">

                                                        <div class="sv-form-grid">
                                                            <div class="sv-form-group">
                                                                <label class="sv-form-label">Nama Perangkat Relay</label>
                                                                <input type="text" name="name" class="sv-form-input" value="{{ old('name', $device->name) }}" required>
                                                            </div>

                                                            <div class="sv-form-group">
                                                                <label class="sv-form-label">Relay Channel</label>
                                                                <input type="text" name="relay_code" class="sv-form-input" value="{{ old('relay_code', $device->relay_code ?? $device->esp32_device_id) }}" required>
                                                            </div>

                                                            <div class="sv-form-group">
                                                                <label class="sv-form-label">ESP Unit ID</label>
                                                                <input type="text" name="esp_unit_id" class="sv-form-input" value="{{ old('esp_unit_id', $device->esp_unit_id ?? $device->esp32_device_id) }}" required>
                                                            </div>
                                                        </div>

                                                        <div class="sv-form-actions">
                                                            <button
                                                                type="button"
                                                                class="sv-secondary-btn js-close-tech-box"
                                                                data-target="edit-device-{{ $device->id }}"
                                                                data-form-id="edit-device-form-{{ $device->id }}"
                                                            >
                                                                <i class="bi bi-x-circle"></i>
                                                                Batal
                                                            </button>

                                                            <button type="submit" class="sv-primary-btn">
                                                                <i class="bi bi-check-circle-fill"></i>
                                                                Simpan Relay
                                                            </button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        @empty
                                            <div class="sv-empty-state">
                                                <i class="bi bi-toggle-off"></i>
                                                <div>
                                                    <h6>Belum ada relay</h6>
                                                    <p>Belum ada perangkat relay di ruangan ini. Tambahkan relay agar perangkat dapat dikontrol dari sistem.</p>
                                                </div>
                                            </div>
                                        @endforelse
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="sv-empty-state">
                                <i class="bi bi-house-add-fill"></i>
                                <div>
                                    <h6>Belum ada ruangan untuk dikelola</h6>
                                    <p>Tambahkan ruangan terlebih dahulu agar panel teknisi dapat digunakan.</p>
                                </div>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </section>
@endif

    <div
        id="settingsRuntimeConfig"
        data-advanced-mode="{{ $advancedMode ? '1' : '0' }}"
        data-should-open-tech-panel="{{ (session('open_advanced_panel') || old('return_to') === 'settings') ? '1' : '0' }}"
        data-should-open-pin-modal="{{ $errors->has('advanced_mode') ? '1' : '0' }}"
        data-should-open-account-modal="{{ ($errors->any() && old('return_to') !== 'settings' && ! $errors->has('advanced_mode')) ? '1' : '0' }}"
        data-selected-tech-room-id="{{ (string) $selectedRoomId }}"
        hidden
    ></div>


    <script>
        const settingsRuntimeConfig = document.getElementById('settingsRuntimeConfig');

        const advancedMode =
            settingsRuntimeConfig &&
            settingsRuntimeConfig.dataset.advancedMode === '1';

        const shouldOpenTechPanel =
            settingsRuntimeConfig &&
            settingsRuntimeConfig.dataset.shouldOpenTechPanel === '1';

        const shouldOpenPinModal =
            settingsRuntimeConfig &&
            settingsRuntimeConfig.dataset.shouldOpenPinModal === '1';

        const shouldOpenAccountModal =
            settingsRuntimeConfig &&
            settingsRuntimeConfig.dataset.shouldOpenAccountModal === '1';

        const selectedTechRoomId =
            settingsRuntimeConfig
                ? settingsRuntimeConfig.dataset.selectedTechRoomId || ''
                : '';

        function openAccountModal() {
            document.getElementById('accountBackdrop')?.classList.add('show');
            document.getElementById('accountModal')?.classList.add('show');
        }

        function closeAccountModal() {
            document.getElementById('accountBackdrop')?.classList.remove('show');
            document.getElementById('accountModal')?.classList.remove('show');
            resetFormById('accountProfileForm');
            resetFormById('accountPasswordForm');
        }

        function openPinModal() {
            document.getElementById('pinBackdrop')?.classList.add('show');
            document.getElementById('pinModal')?.classList.add('show');
        }

        function closePinModal() {
            document.getElementById('pinBackdrop')?.classList.remove('show');
            document.getElementById('pinModal')?.classList.remove('show');
            resetFormById('technicianPinForm');
        }

        function openTechPanel() {
            document.getElementById('techScreen')?.classList.add('show');
            openSelectedTechRoom();
        }

        function resetFormById(formId) {
            if (!formId) {
                return;
            }

            const form = document.getElementById(formId);

            if (!form) {
                return;
            }

            form.reset();

            form.querySelectorAll('.js-relay-count-select').forEach(function (select) {
                const roomId = select.dataset.roomId;

                if (roomId) {
                    updateRelayInputs(roomId, select.value);
                }
            });
        }

        function closeTechBox(id, formId = '') {
            const box = document.getElementById(id);

            if (box) {
                box.classList.remove('show');
            }

            resetFormById(formId);
        }

        function closeAllTechBoxes(exceptId = '') {
            document.querySelectorAll('.sv-edit-panel.show').forEach(function (panel) {
                if (panel.id === exceptId) {
                    return;
                }

                panel.classList.remove('show');

                const form = panel.querySelector('form[id]');
                if (form) {
                    resetFormById(form.id);
                }
            });
        }

        function closeTechPanel() {
            closeAllTechBoxes();
            document.getElementById('techScreen')?.classList.remove('show');
        }

        function showTechRoom(roomId) {
            closeAllTechBoxes();
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

            if (!box) {
                return;
            }

            const shouldOpen = !box.classList.contains('show');
            closeAllTechBoxes(id);

            if (!shouldOpen) {
                const form = box.querySelector('form[id]');
                closeTechBox(id, form ? form.id : '');
                return;
            }

            box.classList.add('show');

            window.requestAnimationFrame(function () {
                box.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            });
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

        function updateRelayInputs(roomId, count) {
            const inputs = document.querySelectorAll('.relay-input-room-' + roomId);
            const max = parseInt(count, 10);

            inputs.forEach(function (input) {
                const channel = parseInt(input.dataset.channel, 10);

                if (channel <= max) {
                    input.style.display = '';
                } else {
                    input.style.display = 'none';
                }
            });
        }

        window.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('.js-open-account-modal').forEach(function (button) {
                button.addEventListener('click', function () {
                    openAccountModal();
                });
            });

            document.querySelectorAll('.js-open-tech-or-pin').forEach(function (button) {
                button.addEventListener('click', function () {
                    if (advancedMode) {
                        openTechPanel();
                    } else {
                        openPinModal();
                    }
                });
            });

            document.querySelectorAll('.js-close-account-modal').forEach(function (button) {
                button.addEventListener('click', function () {
                    closeAccountModal();
                });
            });

            document.querySelectorAll('.js-close-pin-modal').forEach(function (button) {
                button.addEventListener('click', function () {
                    closePinModal();
                });
            });

            document.querySelectorAll('.js-close-tech-panel').forEach(function (button) {
                button.addEventListener('click', function () {
                    closeTechPanel();
                });
            });

            document.querySelectorAll('.js-show-tech-room').forEach(function (button) {
                button.addEventListener('click', function () {
                    const roomId = button.dataset.roomId;

                    if (roomId) {
                        showTechRoom(roomId);
                    }
                });
            });

            document.querySelectorAll('.js-toggle-tech-box').forEach(function (button) {
                button.addEventListener('click', function () {
                    const targetId = button.dataset.target;

                    if (targetId) {
                        toggleTechBox(targetId);
                    }
                });
            });

            document.querySelectorAll('.js-close-tech-box').forEach(function (button) {
                button.addEventListener('click', function () {
                    const targetId = button.dataset.target || '';
                    const formId = button.dataset.formId || '';

                    if (targetId) {
                        closeTechBox(targetId, formId);
                    }
                });
            });

            document.querySelectorAll('.js-reset-form').forEach(function (button) {
                button.addEventListener('click', function () {
                    resetFormById(button.dataset.formId || '');
                });
            });

            document.querySelectorAll('.js-relay-count-select').forEach(function (select) {
                const roomId = select.dataset.roomId;

                if (roomId) {
                    updateRelayInputs(roomId, select.value);
                }

                select.addEventListener('change', function () {
                    if (roomId) {
                        updateRelayInputs(roomId, select.value);
                    }
                });
            });

            document.querySelectorAll('.js-confirm-submit').forEach(function (form) {
                form.addEventListener('submit', function (event) {
                    const message = form.dataset.confirm || 'Lanjutkan tindakan ini?';

                    if (!confirm(message)) {
                        event.preventDefault();
                    }
                });
            });

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

            document.addEventListener('keydown', function (event) {
                if (event.key !== 'Escape') {
                    return;
                }

                const openInnerPanel = document.querySelector('.sv-edit-panel.show');
                if (openInnerPanel) {
                    const form = openInnerPanel.querySelector('form[id]');
                    closeTechBox(openInnerPanel.id, form ? form.id : '');
                    return;
                }

                if (document.getElementById('accountModal')?.classList.contains('show')) {
                    closeAccountModal();
                    return;
                }

                if (document.getElementById('pinModal')?.classList.contains('show')) {
                    closePinModal();
                    return;
                }

                if (document.getElementById('techScreen')?.classList.contains('show')) {
                    closeTechPanel();
                }
            });
        });
    </script>
</body>
</html>