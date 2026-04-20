<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rooms - SmartVolt</title>
    <link rel="stylesheet" href="{{ asset('assets/css/smartvolt-brand.css') }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

    <style>
        .sv-room-page-grid {
            display: grid;
            grid-template-columns: 1.25fr 0.85fr;
            gap: 24px;
        }

        .sv-room-list {
            display: grid;
            gap: 16px;
        }

        .sv-room-manage-card {
            padding: 20px;
            border-radius: 24px;
            background: rgba(9, 20, 40, 0.45);
            border: 1px solid rgba(255,255,255,0.06);
            box-shadow: inset 0 1px 0 rgba(255,255,255,0.04);
        }

        .sv-room-manage-top {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 18px;
        }

        .sv-room-manage-left {
            display: flex;
            align-items: center;
            gap: 14px;
            min-width: 0;
        }

        .sv-room-manage-icon {
            width: 54px;
            height: 54px;
            border-radius: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 22px;
            background: linear-gradient(135deg, rgba(66, 196, 255, 0.18), rgba(94, 255, 209, 0.14));
            color: #bff4ff;
            flex-shrink: 0;
        }

        .sv-room-manage-title {
            font-size: 20px;
            font-weight: 800;
            color: #edf5ff;
            margin: 0 0 4px;
            letter-spacing: -0.02em;
        }

        .sv-room-manage-meta {
            color: #a9bad5;
            font-size: 14px;
            line-height: 1.5;
        }

        .sv-room-manage-actions {
            display: flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
            justify-content: flex-end;
        }

        .sv-mini-btn {
            border: none;
            border-radius: 14px;
            padding: 10px 14px;
            font-size: 13px;
            font-weight: 700;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: 0.2s ease;
        }

        .sv-mini-btn:hover {
            transform: translateY(-1px);
        }

        .sv-mini-btn.edit {
            background: rgba(75, 152, 255, 0.16);
            color: #cbe1ff;
            border: 1px solid rgba(75, 152, 255, 0.22);
        }

        .sv-mini-btn.delete {
            background: rgba(255, 96, 96, 0.14);
            color: #ffd5d5;
            border: 1px solid rgba(255, 96, 96, 0.18);
        }

        .sv-inline-edit {
            display: none;
            margin-top: 16px;
            padding-top: 16px;
            border-top: 1px solid rgba(255,255,255,0.06);
        }

        .sv-inline-edit.show {
            display: block;
        }

        .sv-form-stack {
            display: grid;
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

        .sv-form-actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .sv-primary-btn,
        .sv-secondary-btn {
            border: none;
            border-radius: 16px;
            padding: 13px 18px;
            font-size: 14px;
            font-weight: 800;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: 0.2s ease;
        }

        .sv-primary-btn {
            background: linear-gradient(135deg, #3ea7ff, #5f7cff);
            color: #fff;
        }

        .sv-secondary-btn {
            background: rgba(255,255,255,0.07);
            color: #d6e4f8;
            border: 1px solid rgba(255,255,255,0.08);
        }

        .sv-primary-btn:hover,
        .sv-secondary-btn:hover {
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

        .sv-form-card {
            display: grid;
            gap: 16px;
        }

        .sv-form-helper {
            color: #9eb1cc;
            font-size: 14px;
            line-height: 1.6;
        }

        .sv-room-highlight {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 14px;
        }

        .sv-room-highlight .sv-card-title-big {
            font-size: 42px;
            font-weight: 900;
            line-height: 1;
            letter-spacing: -0.03em;
            color: #f3f8ff;
        }

        .sv-room-highlight .sv-card-sub {
            color: #9fb3cf;
            font-size: 15px;
            margin-top: 8px;
        }

        .sv-room-highlight-icon {
            width: 86px;
            height: 86px;
            border-radius: 28px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 34px;
            color: #dff8ff;
            background: linear-gradient(135deg, rgba(72, 194, 255, 0.16), rgba(84, 255, 208, 0.14));
        }

        @media (max-width: 1080px) {
            .sv-room-page-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 720px) {
            .sv-room-manage-top {
                flex-direction: column;
                align-items: stretch;
            }

            .sv-room-manage-actions {
                justify-content: flex-start;
            }

            .sv-room-highlight {
                align-items: flex-start;
                flex-direction: column;
            }
        }
    </style>
</head>
<body class="sv-dashboard-body">
    <div class="sv-app">
        <aside class="sv-sidebar">
            <div class="brand">
                <div class="icon"><i class="bi bi-lightning-charge-fill"></i></div>
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
                <a href="{{ route('devices') }}" class="{{ request()->routeIs('devices*') ? 'active' : '' }}">
                    <i class="bi bi-cpu-fill"></i>
                    <span>Devices</span>
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
                            <h1 class="sv-page-title">SmartVolt Control Hub</h1>
                            <p class="sv-page-sub">Halo, {{ auth()->user()->name ?? 'User' }}</p>
                        </div>
                    </div>

                    <div class="sv-topbar-right">
                        <div class="sv-action-cluster">
                            <button class="sv-btn sv-notify-btn" type="button" aria-label="Notifications">
                                <i class="bi bi-bell"></i>
                                <span class="sv-notify-dot"></span>
                            </button>

                            <form action="{{ route('logout') }}" method="POST" class="sv-logout-form">
                                @csrf
                                <button type="submit" class="sv-btn sv-logout-btn">
                                    <i class="bi bi-box-arrow-right"></i>
                                    <span>Logout</span>
                                </button>
                            </form>
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
                    <div class="sv-hero-card sv-glass">
                        <div class="sv-hero-grid">
                            <div>
                                <div class="sv-live-chip">
                                    <span class="sv-live-dot"></span>
                                    Room management
                                </div>

                                <h1>Organize, manage, and structure your smart home by room.</h1>
                                <p>
                                    Kelola ruangan seperti ruang tamu, kamar tidur, dapur, dan area lain agar device dapat dipantau dan dikontrol secara lebih terstruktur.
                                </p>
                            </div>

                            <div class="sv-energy-panel">
                                <h3>Current Rooms Snapshot</h3>

                                <div class="sv-pulse">
                                    <span></span><span></span><span></span><span></span><span></span><span></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="sv-stats">
                    <div class="sv-stat-card sv-glass rooms">
                        <div class="label">Total Rooms</div>
                        <div class="value">
                            <i class="bi bi-grid-1x2-fill"></i>
                            <span>{{ $rooms->count() }}</span>
                        </div>
                    </div>

                    <div class="sv-stat-card sv-glass active">
                        <div class="label">Connected Devices</div>
                        <div class="value">
                            <i class="bi bi-broadcast-pin"></i>
                            <span>{{ $rooms->sum('devices_count') }}</span>
                        </div>
                    </div>
                </div>

                <div class="sv-room-page-grid">
                    <div class="sv-panel sv-glass">
                        <div class="sv-panel-head">
                            <div>
                                <h3>Rooms</h3>
                                <div class="sv-panel-sub">Susunan ruangan aktif yang terdaftar di SmartVolt</div>
                            </div>
                        </div>

                        @if($rooms->isEmpty())
                            <div class="sv-empty">Belum ada room.</div>
                        @else
                            <div class="sv-room-list">
                                @foreach($rooms as $room)
                                    <div class="sv-room-manage-card">
                                        <div class="sv-room-manage-top">
                                            <div class="sv-room-manage-left">
                                                <div class="sv-room-manage-icon">
                                                    <i class="bi bi-grid-1x2-fill"></i>
                                                </div>
                                                <div>
                                                   <a href="{{ route('rooms.show', $room) }}" style="text-decoration:none; color:inherit;">
    <h4 class="sv-room-manage-title">{{ $room->name }}</h4>
</a>
                                                    <div class="sv-room-manage-meta">
                                                        {{ $room->devices_count ?? 0 }} device terhubung
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="sv-room-manage-actions">
                                                <button
                                                    type="button"
                                                    class="sv-mini-btn edit"
                                                    onclick="toggleEditForm('edit-room-{{ $room->id }}')">
                                                    <i class="bi bi-pencil-square"></i>
                                                    Edit
                                                </button>

                                                <form action="{{ route('rooms.destroy', $room->id) }}" method="POST" onsubmit="return confirm('Hapus room ini?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="sv-mini-btn delete">
                                                        <i class="bi bi-trash-fill"></i>
                                                        Hapus
                                                    </button>
                                                </form>
                                            </div>
                                        </div>

                                        <div id="edit-room-{{ $room->id }}" class="sv-inline-edit">
                                            <form action="{{ route('rooms.update', $room->id) }}" method="POST" class="sv-form-stack">
                                                @csrf
                                                @method('PUT')

                                                <div class="sv-form-group">
                                                    <label class="sv-form-label">Nama Room</label>
                                                    <input
                                                        type="text"
                                                        name="name"
                                                        class="sv-form-input"
                                                        value="{{ $room->name }}"
                                                        required
                                                    >
                                                </div>

                                                <div class="sv-form-actions">
                                                    <button type="submit" class="sv-primary-btn">
                                                        <i class="bi bi-check2-circle"></i>
                                                        Simpan Perubahan
                                                    </button>

                                                    <button
                                                        type="button"
                                                        class="sv-secondary-btn"
                                                        onclick="toggleEditForm('edit-room-{{ $room->id }}')">
                                                        <i class="bi bi-x-circle"></i>
                                                        Tutup
                                                    </button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>

                    <div class="sv-panel sv-glass">
                        <div class="sv-panel-head">
                            <div>
                                <h3>Add Room</h3>
                                <div class="sv-panel-sub">Tambahkan ruangan baru untuk mengelompokkan perangkat listrik</div>
                            </div>
                        </div>

                        <div class="sv-form-card">
                            <div class="sv-room-highlight">
                                <div class="sv-room-highlight-icon">
                                    <i class="bi bi-house-add-fill"></i>
                                </div>
                            </div>

                            <div class="sv-form-helper">
                                Gunakan nama ruangan yang jelas seperti <strong>Ruang Tamu</strong>, <strong>Kamar Tidur</strong>, atau <strong>Dapur</strong> agar pengelolaan device lebih mudah.
                            </div>

                            <form action="{{ route('rooms.store') }}" method="POST" class="sv-form-stack">
                                @csrf

                                <div class="sv-form-group">
                                    <label class="sv-form-label">Nama Room</label>
                                    <input
                                        type="text"
                                        name="name"
                                        class="sv-form-input"
                                        placeholder="Contoh: Ruang Tamu"
                                        value="{{ old('name') }}"
                                        required
                                    >
                                </div>

                                <button type="submit" class="sv-primary-btn">
                                    <i class="bi bi-plus-circle-fill"></i>
                                    Tambah Room
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </section>

            <nav class="sv-bottomnav">
                <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'active' : '' }}">
                    <i class="bi bi-house-door-fill"></i>
                    <span>Dashboard</span>
                </a>
                <a href="{{ route('rooms') }}" class="{{ request()->routeIs('rooms*') ? 'active' : '' }}">
                    <i class="bi bi-grid-1x2-fill"></i>
                    <span>Rooms</span>
                </a>
                <a href="{{ route('devices') }}" class="{{ request()->routeIs('devices*') ? 'active' : '' }}">
                    <i class="bi bi-cpu-fill"></i>
                    <span>Devices</span>
                </a>
                <a href="{{ route('energy.history') }}" class="{{ request()->routeIs('energy.history') ? 'active' : '' }}">
                    <i class="bi bi-bar-chart-fill"></i>
                    <span>History</span>
                </a>
                <a href="{{ route('settings') }}" class="{{ request()->routeIs('settings*') ? 'active' : '' }}">
                    <i class="bi bi-gear-fill"></i>
                    <span>Settings</span>
                </a>
            </nav>
        </main>
    </div>

    <script>
        function toggleEditForm(id) {
            const form = document.getElementById(id);
            if (form) {
                form.classList.toggle('show');
            }
        }
    </script>
</body>
</html>