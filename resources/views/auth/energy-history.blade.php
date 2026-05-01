<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Energy History - SmartVolt</title>

    <link rel="stylesheet" href="{{ asset('assets/css/smartvolt-brand.css') }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
        .history-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 16px;
            margin-bottom: 20px;
        }

        .history-card {
            padding: 22px;
            border-radius: 24px;
        }

        .history-label {
            color: #9fb4d1;
            font-size: 13px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .06em;
            margin-bottom: 10px;
        }

        .history-value {
            color: #f2f7ff;
            font-size: 28px;
            font-weight: 900;
        }

        .history-panel {
            padding: 24px;
            border-radius: 28px;
            margin-bottom: 20px;
        }

        .history-chart-wrap {
            height: 320px;
            padding: 16px;
            border-radius: 20px;
            background: rgba(7, 18, 38, .35);
            border: 1px dashed rgba(255,255,255,.10);
        }

        .history-chart-wrap canvas {
            width: 100% !important;
            height: 285px !important;
        }

        .history-table-wrap {
            overflow-x: auto;
            border-radius: 20px;
            border: 1px solid rgba(255,255,255,.08);
        }

        .history-table {
            width: 100%;
            min-width: 850px;
            border-collapse: collapse;
        }

        .history-table th,
        .history-table td {
            padding: 14px 16px;
            border-bottom: 1px solid rgba(255,255,255,.07);
            text-align: left;
            color: #dbeafe;
        }

        .history-table th {
            color: #93c5fd;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: .07em;
            background: rgba(255,255,255,.04);
        }

        .history-empty {
            padding: 24px;
            color: #dbeafe;
            line-height: 1.7;
        }

        @media (max-width: 1000px) {
            .history-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 640px) {
            .history-grid {
                grid-template-columns: 1fr;
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
                        <button class="sv-icon-button" type="button">
                            <i class="bi bi-list"></i>
                        </button>

                        <div>
                            <h1 class="sv-page-title">Energy History</h1>
                            <p class="sv-page-sub">Riwayat monitoring konsumsi listrik</p>
                        </div>
                    </div>
                </div>
            </header>

            <section class="sv-shell">
                <div class="sv-hero">
                    <div class="sv-hero-card sv-glass" style="padding: 28px;">
                        <h1 style="margin-bottom: 10px;">Pantau riwayat energi perangkat listrik.</h1>
                        <p style="margin: 0; color: #b9cae3;">
                            Halaman ini menampilkan data tegangan, arus, daya, dan energi dari sensor yang masuk ke sistem.
                        </p>
                    </div>
                </div>

                <div class="history-grid">
                    <div class="history-card sv-glass">
                        <div class="history-label">Total Data</div>
                        <div class="history-value">{{ $summary['total_logs'] ?? 0 }}</div>
                    </div>

                    <div class="history-card sv-glass">
                        <div class="history-label">Max Power</div>
                        <div class="history-value">{{ number_format($summary['max_power'] ?? 0, 1) }} W</div>
                    </div>

                    <div class="history-card sv-glass">
                        <div class="history-label">Average Power</div>
                        <div class="history-value">{{ number_format($summary['avg_power'] ?? 0, 1) }} W</div>
                    </div>

                    <div class="history-card sv-glass">
                        <div class="history-label">Average Voltage</div>
                        <div class="history-value">{{ number_format($summary['avg_voltage'] ?? 0, 1) }} V</div>
                    </div>
                </div>

                <div class="history-panel sv-glass">
                    <div class="sv-panel-head">
                        <div>
                            <h3>Grafik Energy History</h3>
                            <div class="sv-panel-sub">Data power dan energy dari tabel energy_logs</div>
                        </div>
                    </div>

                    <div class="history-chart-wrap">
                        <canvas
    id="energyHistoryChart"
    data-labels='{{ json_encode($chart["labels"] ?? []) }}'
    data-power='{{ json_encode($chart["power"] ?? []) }}'
    data-energy='{{ json_encode($chart["energy"] ?? []) }}'>
</canvas>
                    </div>
                </div>

                <div class="history-panel sv-glass">
                    <div class="sv-panel-head">
                        <div>
                            <h3>Data Sensor</h3>
                            <div class="sv-panel-sub">Riwayat data yang masuk ke database</div>
                        </div>
                    </div>

                    <div class="history-table-wrap">
                        <table class="history-table">
                            <thead>
                                <tr>
                                    <th>Waktu</th>
                                    <th>Voltage</th>
                                    <th>Current</th>
                                    <th>Power</th>
                                    <th>Energy</th>
                                </tr>
                            </thead>

                            <tbody>
                                @forelse ($logs as $log)
                                    <tr>
                                        <td>{{ optional($log->created_at)->format('d/m/Y H:i:s') }}</td>
                                        <td>{{ number_format($log->voltage ?? 0, 2) }} V</td>
                                        <td>{{ number_format($log->current ?? 0, 2) }} A</td>
                                        <td>{{ number_format($log->power ?? 0, 2) }} W</td>
                                        <td>{{ number_format($log->energy_kwh ?? $log->energy ?? 0, 4) }} kWh</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5">
                                            <div class="history-empty">
                                                Belum ada data energy history. Nanti data akan muncul setelah ESP32/sensor mengirim data ke database.
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div style="margin-top: 18px;">
                        {{ $logs->links() }}
                    </div>
                </div>
            </section>
        </main>
    </div>

  <script>
    const chartEl = document.getElementById('energyHistoryChart');

    if (chartEl) {
        const chartLabels = JSON.parse(chartEl.dataset.labels || '[]');
        const chartPower = JSON.parse(chartEl.dataset.power || '[]');
        const chartEnergy = JSON.parse(chartEl.dataset.energy || '[]');

        const ctx = chartEl.getContext('2d');

        new Chart(ctx, {
            type: 'line',
            data: {
                labels: chartLabels,
                datasets: [
                    {
                        label: 'Power (W)',
                        data: chartPower,
                        tension: 0.35
                    },
                    {
                        label: 'Energy (kWh)',
                        data: chartEnergy,
                        tension: 0.35
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false
            }
        });
    }
</script>  
</body>
</html>