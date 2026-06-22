<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Energy History - SmartVolt</title>

    <link rel="stylesheet" href="{{ asset('assets/css/smartvolt-brand.css') }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/xlsx/dist/xlsx.full.min.js"></script>

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

        /* Fix pagination SVG size */
        nav[role="navigation"] svg {
            width: 20px !important;
            height: 20px !important;
        }
        
        nav[role="navigation"] {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        nav[role="navigation"] p {
            font-size: 14px;
            color: #9fb4d1;
        }

        nav[role="navigation"] a, nav[role="navigation"] span[aria-disabled] {
            padding: 8px 12px;
            background: rgba(255,255,255, 0.05);
            border-radius: 8px;
            color: #fff;
            text-decoration: none;
            transition: all 0.2s ease;
            display: inline-flex;
            align-items: center;
            border: 1px solid rgba(255,255,255,0.1);
        }
        
        nav[role="navigation"] a:hover {
            background: rgba(255,255,255, 0.1);
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

            <p>Energy command center for monitoring, device control, and electricity consumption insights.</p>

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
                <div class="sv-topbar-inner" style="display: flex; justify-content: space-between; align-items: center; width: 100%;">
                    <div class="sv-topbar-left" style="display: flex; align-items: center; gap: 16px;">
                        <div>
                            <h1 class="sv-page-title">Energy History</h1>
                            <p class="sv-page-sub">Electricity consumption monitoring history</p>
                        </div>
                    </div>
                    
                    <div class="sv-topbar-right" style="display: flex; gap: 12px;">
                        <button type="button" class="sv-btn-export" onclick="exportExcel()" style="background: rgba(16, 185, 129, 0.15); border: 1px solid rgba(16, 185, 129, 0.4); color: #34d399; padding: 8px 16px; border-radius: 12px; font-weight: 600; cursor: pointer; display: flex; align-items: center; gap: 8px; transition: all 0.3s ease;">
                            <i class="bi bi-file-earmark-excel-fill"></i> Export Excel
                        </button>
                        <button type="button" class="sv-btn-export" onclick="exportPDF()" style="background: rgba(239, 68, 68, 0.15); border: 1px solid rgba(239, 68, 68, 0.4); color: #f87171; padding: 8px 16px; border-radius: 12px; font-weight: 600; cursor: pointer; display: flex; align-items: center; gap: 8px; transition: all 0.3s ease;">
                            <i class="bi bi-file-earmark-pdf-fill"></i> Export PDF
                        </button>
                    </div>
                </div>
            </header>

            <section class="sv-shell">
                <div class="sv-hero">
                    <div class="sv-hero-card sv-glass" style="padding: 28px;">
                        <h1 style="margin-bottom: 10px;">Monitor the energy history of electrical devices.</h1>
                        <p style="margin: 0; color: #b9cae3;">
                            This page displays voltage, current, power, and energy data from sensors sent to the system.
                        </p>
                    </div>
                </div>

                <div class="history-grid">
                    <div class="history-card sv-glass">
                        <div class="history-label">Total Records</div>
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
                            <h3>Energy History Chart</h3>
                            <div class="sv-panel-sub">Power and energy data from the energy_logs table</div>
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
                            <h3>Sensor Data</h3>
                            <div class="sv-panel-sub">History of data stored in the database</div>
                        </div>
                    </div>

                    <div class="history-table-wrap">
                        <table class="history-table">
                            <thead>
                                <tr>
                                    <th>Time</th>
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
                                                No energy history data yet. Data will appear after the ESP32/sensor sends data to the database.
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

    async function exportPDF() {
        const btn = document.querySelector('button[onclick="exportPDF()"]');
        const originalHTML = btn.innerHTML;
        btn.innerHTML = '<i class="bi bi-hourglass-split"></i> Preparing PDF...';
        btn.disabled = true;

        try {
            const response = await fetch('{{ route("energy.history.export") }}');
            if (!response.ok) throw new Error('Network response was not ok');
            const data = await response.json();
            
            if (data.length === 0) {
                alert('No data available to export.');
                return;
            }

            // Create a clean, minimalist container for PDF
            const container = document.createElement('div');
            container.style.padding = '20px';
            container.style.fontFamily = 'Arial, sans-serif';
            container.style.color = '#333';
            container.style.background = '#fff';
            
            let html = `
                <div style="padding: 30px; font-family: Arial, sans-serif; color: #000; background: #fff; width: 800px;">
                    <div style="text-align: center; margin-bottom: 25px;">
                        <h2 style="margin: 0; color: #111; font-size: 22px;">Energy History Report</h2>
                        <p style="margin: 5px 0 0 0; color: #555; font-size: 14px;">SmartVolt IoT Monitoring</p>
                        <p style="margin: 5px 0 0 0; color: #888; font-size: 12px;">Generated on: ${new Date().toLocaleString('en-US')}</p>
                    </div>
                    <table style="width: 100%; border-collapse: collapse; font-size: 12px; border: 1px solid #ddd;">
                        <thead>
                            <tr style="background-color: #f3f4f6; color: #111; text-align: left;">
                                <th style="padding: 10px; border: 1px solid #ddd;">Time</th>
                                <th style="padding: 10px; border: 1px solid #ddd;">Voltage (V)</th>
                                <th style="padding: 10px; border: 1px solid #ddd;">Current (A)</th>
                                <th style="padding: 10px; border: 1px solid #ddd;">Power (W)</th>
                                <th style="padding: 10px; border: 1px solid #ddd;">Energy (kWh)</th>
                            </tr>
                        </thead>
                        <tbody>
            `;

            data.forEach((row, index) => {
                const bg = index % 2 === 0 ? '#ffffff' : '#fafafa';
                html += `
                    <tr style="background-color: ${bg};">
                        <td style="padding: 8px 10px; border: 1px solid #ddd; color: #000;">${row['Waktu']}</td>
                        <td style="padding: 8px 10px; border: 1px solid #ddd; color: #000;">${row['Voltage (V)']}</td>
                        <td style="padding: 8px 10px; border: 1px solid #ddd; color: #000;">${row['Current (A)']}</td>
                        <td style="padding: 8px 10px; border: 1px solid #ddd; color: #000;">${row['Power (W)']}</td>
                        <td style="padding: 8px 10px; border: 1px solid #ddd; color: #000;">${row['Energy (kWh)']}</td>
                    </tr>
                `;
            });

            html += `
                        </tbody>
                    </table>
                </div>
            `;

            const opt = {
                margin:       [10, 10, 10, 10], // top, left, bottom, right
                filename:     'Energy_History_SmartVolt.pdf',
                image:        { type: 'jpeg', quality: 0.98 },
                html2canvas:  { scale: 2, useCORS: true, logging: true, backgroundColor: '#ffffff' },
                jsPDF:        { unit: 'mm', format: 'a4', orientation: 'portrait' }
            };

            await html2pdf().set(opt).from(html).save();

        } catch (error) {
            console.error('Export failed:', error);
            alert('Failed to export data to PDF.');
        } finally {
            btn.innerHTML = originalHTML;
            btn.disabled = false;
        }
    }

    async function exportExcel() {
        const btn = document.querySelector('button[onclick="exportExcel()"]');
        const originalHTML = btn.innerHTML;
        btn.innerHTML = '<i class="bi bi-hourglass-split"></i> Loading...';
        btn.disabled = true;

        try {
            const response = await fetch('{{ route("energy.history.export") }}');
            if (!response.ok) throw new Error('Network response was not ok');
            const data = await response.json();
            
            if (data.length === 0) {
                alert('No data available to export.');
                return;
            }

            const worksheet = XLSX.utils.json_to_sheet(data);
            const workbook = XLSX.utils.book_new();
            XLSX.utils.book_append_sheet(workbook, worksheet, "Energy History");
            
            // Adjust column widths
            const colWidths = [
                {wch: 22}, // Time
                {wch: 15}, // Voltage
                {wch: 15}, // Current
                {wch: 15}, // Power
                {wch: 18}  // Energy
            ];
            worksheet['!cols'] = colWidths;

            XLSX.writeFile(workbook, "Energy_History_SmartVolt.xlsx");
        } catch (error) {
            console.error('Export failed:', error);
            alert('Failed to export data.');
        } finally {
            btn.innerHTML = originalHTML;
            btn.disabled = false;
        }
    }
</script>  
</body>
</html>