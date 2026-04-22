<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - SmartVolt</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="dashboard-data-url" content="{{ route('dashboard.data') }}">
    <meta name="toggle-url-template" content="{{ url('/devices/__ID__/toggle') }}">

    <link rel="stylesheet" href="{{ asset('assets/css/smartvolt-brand.css') }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
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
                            <p class="sv-page-sub" id="welcomeText">Halo, {{ $dashboardData['user']['name'] ?? 'User' }}</p>
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
                <div class="sv-hero">
                    <div class="sv-hero-card sv-glass">
                        <div class="sv-hero-grid">
                            <div>
                                <div class="sv-live-chip">
                                    <span class="sv-live-dot"></span>
                                    Live energy monitoring
                                </div>

                                <h1>Monitor, control, and orchestrate your energy flow.</h1>
                                <p>
                                    Dashboard SmartVolt dirancang agar energi, room, dan device terasa seperti satu sistem hidup yang responsif, bukan daftar tabel biasa.
                                </p>
                            </div>

                            <div class="sv-energy-panel">
                                <h3>Current Power Snapshot</h3>
                                <div class="sv-energy-reading">
                                    <strong id="heroPowerText">{{ number_format((float) ($dashboardData['stats']['current_power'] ?? 0), 0) }}</strong>
                                    <span>Watt</span>
                                </div>

                                <div class="sv-pulse">
                                    <span></span><span></span><span></span><span></span><span></span><span></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="sv-stats">
                    <div class="sv-stat-card sv-glass energy">
                        <div class="label">Total Energy Today</div>
                        <div class="value">
                            <i class="bi bi-lightning-charge-fill"></i>
                            <span id="totalEnergyText">0.0</span>
                            <small>kWh</small>
                        </div>
                    </div>

                    <div class="sv-stat-card sv-glass power">
                        <div class="label">Current Power</div>
                        <div class="value">
                            <i class="bi bi-plug-fill"></i>
                            <span id="currentPowerText">0</span>
                            <small>Watt</small>
                        </div>
                    </div>

                    <div class="sv-stat-card sv-glass rooms">
                        <div class="label">Rooms</div>
                        <div class="value">
                            <i class="bi bi-grid-1x2-fill"></i>
                            <span id="totalRoomsText">0</span>
                        </div>
                    </div>

                    <div class="sv-stat-card sv-glass active">
                        <div class="label">Active Devices</div>
                        <div class="value">
                            <i class="bi bi-broadcast-pin"></i>
                            <span id="activeDevicesText">0</span>
                        </div>
                    </div>
                </div>

                <div class="sv-panels">
                    <div class="sv-panel sv-glass">
                        <div class="sv-panel-head">
                            <div>
                                <h3>Rooms</h3>
                                <div class="sv-panel-sub">Susunan ruangan yang aktif di SmartVolt</div>
                            </div>
                        </div>
                        <div id="roomsContainer" class="sv-rooms-grid"></div>
                    </div>

                    <div class="sv-panel sv-glass">
                        <div class="sv-panel-head">
                            <div>
                                <h3>Devices</h3>
                                <div class="sv-panel-sub">Kontrol cepat untuk perangkat yang terhubung</div>
                            </div>
                        </div>
                        <div id="devicesContainer" class="sv-devices-grid"></div>
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

    <script id="dashboard-data" type="application/json">@json($dashboardData)</script>

    <script>
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        const dashboardDataUrl = document.querySelector('meta[name="dashboard-data-url"]').getAttribute('content');
        const toggleUrlTemplate = document.querySelector('meta[name="toggle-url-template"]').getAttribute('content');
        const roomShowUrlTemplate = document.querySelector('meta[name="room-show-url-template"]').getAttribute('content');
        let dashboardData = JSON.parse(document.getElementById('dashboard-data').textContent);

        const roomIcons = {
            'living room': 'bi-lamp-fill',
            'bedroom': 'bi-bed-fill',
            'kitchen': 'bi-fork-knife',
            'bathroom': 'bi-droplet-fill',
            'garage': 'bi-house-gear-fill'
        };

        const deviceIcons = {
            'lamp': 'bi-lightbulb-fill',
            'television': 'bi-tv-fill',
            'tv': 'bi-tv-fill',
            'air conditioner': 'bi-snow',
            'ac': 'bi-snow',
            'a/c': 'bi-snow',
            'fan': 'bi-fan',
            'refrigerator': 'bi-safe2-fill',
            'kulkas': 'bi-safe2-fill'
        };

        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text ?? '';
            return div.innerHTML;
        }

        function formatNumber(value, decimals = 0) {
            return Number(value || 0).toLocaleString('id-ID', {
                minimumFractionDigits: decimals,
                maximumFractionDigits: decimals
            });
        }

        function getRoomIcon(name) {
            return roomIcons[(name || '').toLowerCase()] || 'bi-grid-1x2-fill';
        }

        function getDeviceIcon(type, name) {
            const key = (type || name || '').toLowerCase();
            return deviceIcons[key] || 'bi-cpu-fill';
        }

        function renderStats(data) {
            document.getElementById('totalEnergyText').textContent = formatNumber(data.stats.total_energy_today, 1);
            document.getElementById('currentPowerText').textContent = formatNumber(data.stats.current_power, 0);
            document.getElementById('heroPowerText').textContent = formatNumber(data.stats.current_power, 0);
            document.getElementById('totalRoomsText').textContent = formatNumber(data.stats.total_rooms, 0);
            document.getElementById('activeDevicesText').textContent = formatNumber(data.stats.active_devices, 0);
            document.getElementById('welcomeText').textContent = `Halo, ${data.user.name ?? 'User'}`;
        }

        function renderRooms(data) {
            const container = document.getElementById('roomsContainer');

            if (!data.rooms.length) {
                container.innerHTML = '<div class="sv-empty">Belum ada room.</div>';
                return;
            }

            container.innerHTML = data.rooms.map(room => {
                const roomUrl = roomShowUrlTemplate.replace('__ID__', room.id);

                return `
                    <a href="${roomUrl}" class="sv-room-card" style="text-decoration:none; color:inherit;">
                        <div class="sv-card-left">
                            <div class="sv-room-icon">
                                <i class="bi ${getRoomIcon(room.name)}"></i>
                            </div>
                            <div>
                                <h4 class="sv-card-title">${escapeHtml(room.name)}</h4>
                                <div class="sv-card-meta">${formatNumber(room.total_devices ?? room.devices_count ?? 0, 0)} device</div>
                            </div>
                        </div>
                        <i class="bi bi-chevron-right sv-chevron"></i>
                    </a>
                `;
            }).join('');
        }

        function isDeviceOn(status) {
            return status === true || status === 1 || status === '1' || status === 'on';
        }

        function renderDevices(data) {
            const container = document.getElementById('devicesContainer');

            if (!data.devices.length) {
                container.innerHTML = '<div class="sv-empty">Belum ada device.</div>';
                return;
            }

            container.innerHTML = data.devices.map(device => {
                const on = isDeviceOn(device.status);
                const label = on ? 'ON' : 'OFF';

                return `
                    <div class="sv-device-card">
                        <div class="sv-card-left">
                            <div class="sv-device-icon">
                                <i class="bi ${getDeviceIcon(device.type, device.name)}"></i>
                            </div>
                            <div>
                                <h4 class="sv-card-title">${escapeHtml(device.name)}</h4>
                                <div class="sv-device-meta">${escapeHtml(device.room_name || 'Tanpa Room')}</div>
                            </div>
                        </div>

                        <div class="sv-device-actions">
                            <span class="sv-chip ${on ? 'on' : ''}">
                                ${label}
                            </span>
                            <button type="button" class="sv-btn sv-switch ${on ? 'on' : ''}" onclick="toggleDevice(${device.id})"></button>
                        </div>
                    </div>
                `;
            }).join('');
        }

        function renderAll(data) {
            renderStats(data);
            renderRooms(data);
            renderDevices(data);
        }

        async function fetchDashboardData() {
            try {
                const response = await fetch(dashboardDataUrl, {
                    headers: { 'Accept': 'application/json' }
                });

                if (!response.ok) return;

                dashboardData = await response.json();
                renderAll(dashboardData);
            } catch (error) {
                console.error('Gagal mengambil data dashboard:', error);
            }
        }

        async function toggleDevice(id) {
            try {
                const toggleUrl = toggleUrlTemplate.replace('__ID__', id);

                const response = await fetch(toggleUrl, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                if (!response.ok) return;

                const result = await response.json();

                if (result.success) {
                    fetchDashboardData();
                }
            } catch (error) {
                console.error('Gagal toggle device:', error);
            }
        }

        renderAll(dashboardData);
        setInterval(fetchDashboardData, 5000);
    </script>
</body>
</html>