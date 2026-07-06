<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Beranda - SmartVolt</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="stylesheet" href="{{ asset('assets/css/smartvolt-brand.css') }}?v={{ filemtime(public_path('assets/css/smartvolt-brand.css')) }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .sv-chart-wrap {
            min-height: 320px;
            border: 1px dashed rgba(255,255,255,0.10);
            border-radius: 18px;
            padding: 16px;
            background: rgba(7, 18, 38, 0.35);
        }
        .sv-chart-canvas {
            width: 100% !important;
            height: 280px !important;
        }
        .sv-dashboard-alert {
            margin-bottom: 18px;
            border-radius: 18px;
            padding: 14px 16px;
            font-size: 14px;
            font-weight: 700;
            line-height: 1.6;
        }
        .sv-dashboard-alert.success {
            background: rgba(73, 212, 155, 0.14);
            color: #c8ffe7;
            border: 1px solid rgba(73, 212, 155, 0.18);
        }
        .sv-dashboard-alert.error {
            background: rgba(255, 97, 97, 0.14);
            color: #ffd7d7;
            border: 1px solid rgba(255, 97, 97, 0.18);
        }
        .sv-rooms-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 18px;
            align-items: start !important;
        }
        .sv-dashboard-room {
            cursor: pointer;
            align-self: start !important;
            align-items: stretch;
            flex-direction: column;
            transition: 0.2s ease;
        }
        .sv-dashboard-room:hover {
            transform: translateY(-1px);
            border-color: rgba(90, 198, 255, 0.28);
        }
        .sv-dashboard-room.is-open {
            border-color: rgba(90, 198, 255, 0.38);
            background: rgba(255,255,255,0.065);
        }
        .sv-dashboard-room > .sv-card-left {
            width: 100%;
        }
        .sv-dashboard-device-list {
            display: none !important;
            gap: 10px;
            margin-top: 14px;
            width: 100%;
        }
        .sv-dashboard-room.is-open .sv-dashboard-device-list {
            display: grid !important;
        }
        .sv-dashboard-device {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            padding: 12px;
            border-radius: 16px;
            background: rgba(255,255,255,0.045);
            border: 1px solid rgba(255,255,255,0.08);
        }
        .sv-dashboard-device-left {
            display: flex;
            align-items: center;
            gap: 10px;
            min-width: 0;
        }
        .sv-dashboard-device-icon {
            width: 38px;
            height: 38px;
            border-radius: 14px;
            background: rgba(90, 198, 255, 0.12);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #bff4ff;
            flex-shrink: 0;
        }
        .sv-dashboard-device-name {
            font-size: 14px;
            font-weight: 800;
            color: #eef5ff;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 145px;
        }
        .sv-dashboard-device-meta {
            font-size: 12px;
            color: #9fb4d4;
            margin-top: 2px;
        }
        .sv-dashboard-toggle {
            border: none;
            border-radius: 999px;
            padding: 9px 13px;
            font-size: 12px;
            font-weight: 900;
            cursor: pointer;
            color: #fff;
            min-width: 78px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 7px;
            transition: 0.2s ease;
        }
        .sv-dashboard-toggle.is-on {
            background: linear-gradient(135deg, #14b8a6, #22c55e);
        }
        .sv-dashboard-toggle.is-off {
            background: rgba(255,255,255,0.10);
            border: 1px solid rgba(255,255,255,0.14);
        }
        .sv-dashboard-toggle:hover {
            transform: translateY(-1px);
        }
        .sv-dashboard-toggle:disabled {
            opacity: 0.65;
            cursor: not-allowed;
            transform: none;
        }
        .sv-dashboard-dot {
            width: 8px;
            height: 8px;
            border-radius: 999px;
            background: currentColor;
        }
        @media (max-width: 920px) {
            .sv-stats {
                grid-template-columns: 1fr !important;
            }
            .sv-panels {
                grid-template-columns: 1fr !important;
            }
            .sv-rooms-grid {
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
                            <h1 class="sv-page-title">Beranda</h1>
                            <p class="sv-page-sub">
                                Halo, {{ $dashboardData['user']['name'] ?? 'User' }}
                            </p>
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
                @if(session('status'))
                    <div class="sv-dashboard-alert success">
                        <i class="bi bi-check-circle-fill"></i>
                        {{ session('status') }}
                    </div>
                @endif
                @if($errors->any())
                    <div class="sv-dashboard-alert error">
                        <i class="bi bi-exclamation-triangle-fill"></i>
                        {{ $errors->first() }}
                    </div>
                @endif
                <div class="sv-hero">
                    <div class="sv-hero-card sv-glass" style="padding: 28px;">
                        <div>
                            <div class="sv-live-chip">
                                <span class="sv-live-dot"></span>
                                Pemantauan listrik aktif
                            </div>
                            <h1 style="margin-bottom: 10px;">Pantau dan kendalikan listrik rumah dengan mudah.</h1>
                            <p style="color: #cfe3ff; line-height: 1.7; max-width: 760px;">
                                Ruangan dan perangkat yang tampil berasal dari data yang didaftarkan teknisi.
                            </p>
                        </div>
                    </div>
                </div>
                <div class="sv-stats" style="grid-template-columns: repeat(3, minmax(0, 1fr));">
                    <div class="sv-stat-card sv-glass energy">
                        <div class="label">Energi Hari Ini</div>
                        <div class="value">
                            <i class="bi bi-lightning-charge-fill"></i>
                            <span>{{ number_format($dashboardData['stats']['total_energy_today'] ?? 0, 3, ',', '.') }}</span>
                            <small>kWh</small>
                        </div>
                    </div>
                    <div class="sv-stat-card sv-glass power">
                        <div class="label">Daya Saat Ini</div>
                        <div class="value">
                            <i class="bi bi-plug-fill"></i>
                            <span>{{ number_format($dashboardData['stats']['current_power'] ?? 0, 0, ',', '.') }}</span>
                            <small>Watt</small>
                        </div>
                    </div>
                    <div class="sv-stat-card sv-glass active">
                        <div class="label">Perangkat Aktif</div>
                        <div class="value">
                            <i class="bi bi-broadcast-pin"></i>
                            <span id="activeDevicesText">{{ $dashboardData['stats']['active_devices'] ?? 0 }}</span>
                        </div>
                    </div>
                </div>
                <div class="sv-panels" style="grid-template-columns: 1.4fr 0.9fr;">
                    <div class="sv-panel sv-glass">
                        <div class="sv-panel-head">
                            <div>
                                <h3>Grafik Daya Real-Time</h3>
                                <div class="sv-panel-sub">Perubahan daya listrik yang terbaca dari perangkat.</div>
                            </div>
                        </div>
                        <div class="sv-chart-wrap">
                            <canvas
                                id="energyChart"
                                class="sv-chart-canvas"
                                data-labels='@json($dashboardData["chart"]["labels"] ?? [])'
                                data-power='@json($dashboardData["chart"]["power"] ?? [])'>
                            </canvas>
                        </div>
                    </div>
                    <div class="sv-panel sv-glass">
                        <div class="sv-panel-head">
                            <div>
                                <h3>Kontrol Ruangan</h3>
                                <div class="sv-panel-sub">Perangkat ditampilkan di dalam masing-masing ruangan.</div>
                            </div>
                        </div>
                        <div class="sv-rooms-grid">
                            @forelse($rooms as $room)
                                <div class="sv-room-card sv-dashboard-room" data-room-card="true">
                                    <div class="sv-card-left">
                                        <div class="sv-room-icon">
                                            <i class="bi bi-grid-1x2-fill"></i>
                                        </div>
                                        <div>
                                            <h4 class="sv-card-title">{{ $room->name }}</h4>
                                            <div class="sv-card-meta">{{ $room->devices->count() }} perangkat</div>
                                        </div>
                                    </div>
                                    @if($room->devices->count() > 0)
                                        <div class="sv-dashboard-device-list">
                                            @foreach($room->devices as $device)
                                                @php
                                                    $isOn = (bool) $device->status;
                                                    $type = strtolower($device->type ?? 'other');
                                                    $deviceIcon = match ($type) {
                                                        'light', 'lampu' => 'bi-lightbulb-fill',
                                                        'fan', 'kipas' => 'bi-fan',
                                                        'outlet', 'stop kontak' => 'bi-plug-fill',
                                                        default => 'bi-cpu-fill',
                                                    };
                                                    $deviceTypeLabel = match ($type) {
                                                        'light', 'lampu' => 'Lampu',
                                                        'fan', 'kipas' => 'Kipas',
                                                        'outlet', 'stop kontak' => 'Stop Kontak',
                                                        default => 'Perangkat',
                                                    };
                                                @endphp
                                                <div class="sv-dashboard-device">
                                                    <div class="sv-dashboard-device-left">
                                                        <div class="sv-dashboard-device-icon">
                                                            <i class="bi {{ $deviceIcon }}"></i>
                                                        </div>
                                                        <div>
                                                            <div class="sv-dashboard-device-name">{{ $device->name }}</div>
                                                            <div class="sv-dashboard-device-meta">{{ $deviceTypeLabel }}</div>
                                                        </div>
                                                    </div>
                                                    <form
                                                        action="{{ route('devices.toggle', $device->id) }}"
                                                        method="POST"
                                                        class="sv-device-toggle-form"
                                                        data-device-form="true"
                                                    >
                                                        @csrf
                                                        <button
                                                            type="submit"
                                                            class="sv-dashboard-toggle {{ $isOn ? 'is-on' : 'is-off' }}"
                                                            data-device-button="true"
                                                            data-current-status="{{ $isOn ? 'on' : 'off' }}"
                                                        >
                                                            <span class="sv-dashboard-dot"></span>
                                                            <span class="sv-toggle-label">{{ $isOn ? 'Nyala' : 'Mati' }}</span>
                                                        </button>
                                                    </form>
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                            @empty
                                <div class="sv-empty">Belum ada ruangan.</div>
                            @endforelse
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
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const roomCards = document.querySelectorAll('[data-room-card="true"]');
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            const activeDevicesText = document.getElementById('activeDevicesText');
            roomCards.forEach(function (card) {
                card.addEventListener('click', function (event) {
                    if (
                        event.target.closest('form') ||
                        event.target.closest('button') ||
                        event.target.closest('a')
                    ) {
                        return;
                    }
                    const alreadyOpen = card.classList.contains('is-open');
                    roomCards.forEach(function (otherCard) {
                        otherCard.classList.remove('is-open');
                    });
                    if (!alreadyOpen) {
                        card.classList.add('is-open');
                    }
                });
            });
            document.querySelectorAll('[data-device-form="true"]').forEach(function (form) {
                form.addEventListener('click', function (event) {
                    event.stopPropagation();
                });
                form.addEventListener('submit', async function (event) {
                    event.preventDefault();
                    event.stopPropagation();
                    const button = form.querySelector('[data-device-button="true"]');
                    const label = form.querySelector('.sv-toggle-label');
                    if (!button || !label) {
                        return;
                    }
                    const oldLabel = label.textContent.trim();
                    const oldStatus = button.dataset.currentStatus || 'off';
                    button.disabled = true;
                    label.textContent = 'Proses...';
                    try {
                        const response = await fetch(form.action, {
                            method: 'POST',
                            headers: {
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': csrfToken,
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        });
                        const result = await response.json();
                        if (!response.ok || !result.success) {
                            throw new Error(result.message || 'Gagal mengubah status perangkat.');
                        }
                        const isOn = result.status === 'on';
                        button.dataset.currentStatus = isOn ? 'on' : 'off';
                        button.classList.toggle('is-on', isOn);
                        button.classList.toggle('is-off', !isOn);
                        label.textContent = isOn ? 'Nyala' : 'Mati';
                        if (activeDevicesText) {
                            let activeCount = parseInt(activeDevicesText.textContent || '0', 10);
                            if (oldStatus === 'off' && isOn) {
                                activeCount += 1;
                            }
                            if (oldStatus === 'on' && !isOn) {
                                activeCount -= 1;
                            }
                            activeDevicesText.textContent = Math.max(activeCount, 0);
                        }
                    } catch (error) {
                        label.textContent = oldLabel;
                        alert(error.message || 'Gagal mengubah status perangkat.');
                    } finally {
                        button.disabled = false;
                    }
                });
            });
            const chartEl = document.getElementById('energyChart');
            if (chartEl) {
                const labels = JSON.parse(chartEl.dataset.labels || '[]');
                const power = JSON.parse(chartEl.dataset.power || '[]');
                const ctx = chartEl.getContext('2d');
                new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: labels.length ? labels : ['Belum ada data'],
                        datasets: [{
                            label: 'Daya (Watt)',
                            data: power.length ? power : [0],
                            tension: 0.35,
                            fill: true,
                            borderColor: '#67e8f9',
                            backgroundColor: 'rgba(103, 232, 249, 0.12)',
                            pointBackgroundColor: '#67e8f9',
                            pointBorderColor: '#67e8f9',
                            pointRadius: 4,
                            pointHoverRadius: 5,
                            borderWidth: 2
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                labels: {
                                    color: '#d9e7fb'
                                }
                            },
                            tooltip: {
                                callbacks: {
                                    label: function (context) {
                                        return 'Daya: ' + context.parsed.y + ' Watt';
                                    }
                                }
                            }
                        },
                        scales: {
                            x: {
                                title: {
                                    display: true,
                                    text: 'Waktu',
                                    color: '#9fb4d1'
                                },
                                ticks: { color: '#9fb4d1' },
                                grid: { color: 'rgba(255,255,255,0.06)' }
                            },
                            y: {
                                min: 0,
                                suggestedMax: 100,
                                title: {
                                    display: true,
                                    text: 'Daya (Watt)',
                                    color: '#9fb4d1'
                                },
                                ticks: {
                                    color: '#9fb4d1',
                                    callback: function (value) {
                                        return value + ' W';
                                    }
                                },
                                grid: { color: 'rgba(255,255,255,0.06)' }
                            }
                        }
                    }
                });
            }
        });
    </script>
</body>
</html>