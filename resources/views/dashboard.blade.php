<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - SmartVolt</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="dashboard-data-url" content="{{ route('dashboard.data') }}">

    <link rel="stylesheet" href="{{ asset('assets/css/smartvolt-brand.css') }}">
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
    </style>
</head>
<body class="sv-dashboard-body">
    <div class="sv-app">
        <aside class="sv-sidebar">
            <div class="brand">
                <div class="icon"><i class="bi bi-lightning-charge-fill"></i></div>
                <span>SmartVolt</span>
            </div>
            <p>Monitoring energi rumah tangga berbasis IoT.</p>

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
                        <div>
                            <h1 class="sv-page-title">Dashboard</h1>
                            <p class="sv-page-sub" id="welcomeText">Halo, {{ $dashboardData['user']['name'] ?? 'User' }}</p>
                        </div>
                    </div>
                </div>
            </header>

            <section class="sv-shell">
                <div class="sv-hero">
                    <div class="sv-hero-card sv-glass" style="padding: 28px;">
                        <div>
                            <div class="sv-live-chip">
                                <span class="sv-live-dot"></span>
                                Smart energy monitoring
                            </div>

                            <h1 style="margin-bottom: 10px;">Pantau energi rumah dengan lebih sederhana.</h1>
                            <p style="margin: 0; color: #b9cae3;">
                                Dashboard ini menampilkan ringkasan energi, grafik konsumsi, dan kondisi room secara singkat.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="sv-stats" style="grid-template-columns: repeat(3, minmax(0, 1fr));">
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

                    <div class="sv-stat-card sv-glass active">
                        <div class="label">Active Devices</div>
                        <div class="value">
                            <i class="bi bi-broadcast-pin"></i>
                            <span id="activeDevicesText">0</span>
                        </div>
                    </div>
                </div>

                <div class="sv-panels" style="grid-template-columns: 1.4fr 0.9fr;">
                    <div class="sv-panel sv-glass">
                        <div class="sv-panel-head">
                            <div>
                                <h3>Grafik Energi</h3>
                                <div class="sv-panel-sub">Grafik monitoring energi dari sistem Anda</div>
                            </div>
                        </div>

                        <div class="sv-chart-wrap">
                            <canvas id="energyChart" class="sv-chart-canvas"></canvas>
                        </div>
                    </div>

                    <div class="sv-panel sv-glass">
                        <div class="sv-panel-head">
                            <div>
                                <h3>Rooms</h3>
                                <div class="sv-panel-sub">Ringkasan jumlah device per room</div>
                            </div>
                        </div>

                        <div id="roomsContainer" class="sv-rooms-grid"></div>
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
        const dashboardDataUrl = document.querySelector('meta[name="dashboard-data-url"]').getAttribute('content');
        let dashboardData = JSON.parse(document.getElementById('dashboard-data').textContent);

        const roomIcons = {
            'living room': 'bi-lamp-fill',
            'bedroom': 'bi-bed-fill',
            'kitchen': 'bi-fork-knife',
            'bathroom': 'bi-droplet-fill',
            'garage': 'bi-house-gear-fill',
            'kamar': 'bi-bed-fill',
            'ruang tamu': 'bi-tv-fill',
            'dapur': 'bi-fork-knife',
            'kamar mandi': 'bi-droplet-fill'
        };

        let energyChartInstance = null;

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

        function renderStats(data) {
            document.getElementById('totalEnergyText').textContent = formatNumber(data.stats?.total_energy_today, 1);
            document.getElementById('currentPowerText').textContent = formatNumber(data.stats?.current_power, 0);
            document.getElementById('activeDevicesText').textContent = formatNumber(data.stats?.active_devices, 0);
            document.getElementById('welcomeText').textContent = `Halo, ${data.user?.name ?? 'User'}`;
        }

        function renderRooms(data) {
            const container = document.getElementById('roomsContainer');

            if (!data.rooms || !data.rooms.length) {
                container.innerHTML = '<div class="sv-empty">Belum ada room.</div>';
                return;
            }

            container.innerHTML = data.rooms.map(room => {
                return `
                    <div class="sv-room-card">
                        <div class="sv-card-left">
                            <div class="sv-room-icon">
                                <i class="bi ${getRoomIcon(room.name)}"></i>
                            </div>
                            <div>
                                <h4 class="sv-card-title">${escapeHtml(room.name)}</h4>
                                <div class="sv-card-meta">${formatNumber(room.total_devices ?? 0, 0)} device</div>
                            </div>
                        </div>
                    </div>
                `;
            }).join('');
        }

        function buildEnergyChart(data) {
            const ctx = document.getElementById('energyChart').getContext('2d');

            const labels = ['00:00', '04:00', '08:00', '12:00', '16:00', '20:00', '24:00'];

            const currentPower = Number(data.stats?.current_power || 0);
            const totalEnergy = Number(data.stats?.total_energy_today || 0);

            const values = [
                0,
                currentPower * 0.35,
                currentPower * 0.60,
                currentPower * 0.90,
                currentPower * 0.70,
                currentPower * 0.85,
                totalEnergy > 0 ? totalEnergy * 100 : currentPower * 0.50
            ];

            if (energyChartInstance) {
                energyChartInstance.destroy();
            }

            energyChartInstance = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Konsumsi Energi',
                        data: values,
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
                        }
                    },
                    scales: {
                        x: {
                            ticks: {
                                color: '#9fb4d1'
                            },
                            grid: {
                                color: 'rgba(255,255,255,0.06)'
                            }
                        },
                        y: {
                            ticks: {
                                color: '#9fb4d1'
                            },
                            grid: {
                                color: 'rgba(255,255,255,0.06)'
                            }
                        }
                    }
                }
            });
        }

        function renderAll(data) {
            renderStats(data);
            renderRooms(data);
            buildEnergyChart(data);
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

        renderAll(dashboardData);
        setInterval(fetchDashboardData, 5000);
    </script>
    <form action="{{ route('logout') }}" method="POST" style="display:inline;">
    @csrf
    <button type="submit" class="sv-btn sv-logout-btn">
        <i class="bi bi-box-arrow-right"></i>
        <span>Logout</span>
    </button>
</form>
</body>
</html>