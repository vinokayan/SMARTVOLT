<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    >
    <title>Pemakaian Listrik Ruangan - SmartVolt</title>

    <link rel="stylesheet" href="{{ asset('assets/css/smartvolt-brand.css') }}?v={{ filemtime(public_path('assets/css/smartvolt-brand.css')) }}">
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
            min-width: 1100px;
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

        nav[role="navigation"] a,
        nav[role="navigation"] span[aria-disabled] {
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

        .estimation-panel {
            padding: 24px;
            border-radius: 28px;
            margin-bottom: 20px;
        }

        .estimation-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 12px;
            margin-bottom: 18px;
        }

        .estimation-tariff {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 16px;
            border-radius: 999px;
            background: rgba(59, 130, 246, 0.15);
            border: 1px solid rgba(59, 130, 246, 0.4);
            color: #93c5fd;
            font-weight: 700;
            font-size: 13px;
        }

        .estimation-list {
            display: flex;
            flex-direction: column;
            gap: 14px;
        }

        .estimation-item {
            border-radius: 20px;
            border: 1px solid rgba(255,255,255,.08);
            background: rgba(7, 18, 38, .35);
            overflow: hidden;
        }

        .estimation-toggle {
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            padding: 18px 22px;
            background: transparent;
            border: none;
            cursor: pointer;
            text-align: left;
            transition: background .2s ease;
        }

        .estimation-toggle:hover {
            background: rgba(255,255,255,.04);
        }

        .estimation-toggle-title {
            color: #f2f7ff;
            font-size: 18px;
            font-weight: 800;
            margin: 0;
        }

        .estimation-toggle-period {
            color: #9fb4d1;
            font-size: 13px;
            margin: 4px 0 0;
        }

        .estimation-toggle-right {
            text-align: right;
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .estimation-toggle-cost-label {
            color: #9fb4d1;
            font-size: 12px;
            margin: 0;
        }

        .estimation-toggle-cost {
            color: #34d399;
            font-size: 20px;
            font-weight: 900;
            margin: 2px 0 0;
        }

        .estimation-chevron {
            color: #9fb4d1;
            font-size: 18px;
            transition: transform .25s ease;
        }

        .estimation-item.open .estimation-chevron {
            transform: rotate(180deg);
        }

        .estimation-detail {
            display: none;
            border-top: 1px solid rgba(255,255,255,.07);
            padding: 20px 22px;
            background: rgba(7, 18, 38, .45);
        }

        .estimation-item.open .estimation-detail {
            display: block;
        }

        .estimation-detail-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 14px;
        }

        .estimation-detail-card {
            padding: 18px;
            border-radius: 16px;
            background: rgba(255,255,255,.04);
            border: 1px solid rgba(255,255,255,.08);
        }

        .estimation-detail-label {
            color: #9fb4d1;
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .06em;
            margin: 0 0 8px;
        }

        .estimation-detail-value {
            color: #f2f7ff;
            font-size: 26px;
            font-weight: 900;
            margin: 0;
        }

        .estimation-detail-value.cost {
            color: #34d399;
        }

        .estimation-detail-note {
            color: #9fb4d1;
            font-size: 12px;
            margin: 8px 0 0;
        }

        .estimation-formula {
            margin-top: 14px;
            padding: 16px 18px;
            border-radius: 16px;
            background: rgba(255,255,255,.04);
            border: 1px dashed rgba(255,255,255,.12);
        }

        .estimation-formula p {
            margin: 0;
        }

        .estimation-formula .formula-label {
            color: #9fb4d1;
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .06em;
            margin-bottom: 6px;
        }

        .estimation-formula .formula-text {
            color: #dbeafe;
            font-size: 15px;
            font-weight: 700;
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

            .estimation-detail-grid {
                grid-template-columns: 1fr;
            }

            .estimation-toggle {
                flex-direction: column;
                align-items: flex-start;
            }

            .estimation-toggle-right {
                width: 100%;
                justify-content: space-between;
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
                <div class="sv-topbar-inner" style="display: flex; justify-content: space-between; align-items: center; width: 100%;">
                    <div class="sv-topbar-left" style="display: flex; align-items: center; gap: 16px;">
                        <div>
                            <h1 class="sv-page-title">Pemakaian Listrik Ruangan</h1>
                            <p class="sv-page-sub">Riwayat pembacaan PZEM untuk setiap ruangan atau panel listrik.</p>
                        </div>
                    </div>

                    <div class="sv-topbar-right" style="display: flex; gap: 12px;">
                        @include('components.notification-bell')

                        <button type="button" class="sv-btn-export" onclick="exportExcel()" style="background: rgba(16, 185, 129, 0.15); border: 1px solid rgba(16, 185, 129, 0.4); color: #34d399; padding: 8px 16px; border-radius: 12px; font-weight: 600; cursor: pointer; display: flex; align-items: center; gap: 8px;">
                            <i class="bi bi-file-earmark-excel-fill"></i>
                            Ekspor Excel
                        </button>

                        <button type="button" class="sv-btn-export" onclick="exportPDF()" style="background: rgba(239, 68, 68, 0.15); border: 1px solid rgba(239, 68, 68, 0.4); color: #f87171; padding: 8px 16px; border-radius: 12px; font-weight: 600; cursor: pointer; display: flex; align-items: center; gap: 8px;">
                            <i class="bi bi-file-earmark-pdf-fill"></i>
                            Ekspor PDF
                        </button>
                    </div>
                </div>
            </header>

            <section class="sv-shell">
                <div class="sv-hero">
                    <div class="sv-hero-card sv-glass" style="padding: 28px;">
                        <h1 style="margin-bottom: 10px;">Pemakaian Listrik Ruangan</h1>
                        <p style="margin: 0; color: #b9cae3;">
                            Riwayat pembacaan PZEM untuk setiap ruangan atau panel listrik.
                        </p>
                    </div>
                </div>

                <div class="history-grid">
                    <div class="history-card sv-glass">
                        <div class="history-label">Total Data</div>
                        <div class="history-value">{{ number_format($summary['total_logs'] ?? 0, 0, ',', '.') }}</div>
                    </div>

                    <div class="history-card sv-glass">
                        <div class="history-label">Daya Total Tertinggi</div>
                        <div class="history-value">{{ number_format($summary['max_power'] ?? 0, 1, ',', '.') }} W</div>
                    </div>

                    <div class="history-card sv-glass">
                        <div class="history-label">Rata-rata Daya Total</div>
                        <div class="history-value">{{ number_format($summary['avg_power'] ?? 0, 1, ',', '.') }} W</div>
                    </div>

                    <div class="history-card sv-glass">
                        <div class="history-label">Rata-rata Tegangan</div>
                        <div class="history-value">{{ number_format($summary['avg_voltage'] ?? 0, 1, ',', '.') }} V</div>
                    </div>
                </div>

                <div class="estimation-panel sv-glass">
                    <div class="estimation-head">
                        <div>
                            <h3 style="margin: 0;">Estimasi Pembayaran Listrik</h3>
                            <div class="sv-panel-sub">
                                Perkiraan biaya berdasarkan total pemakaian kWh ruangan atau panel dikalikan tarif listrik.
                            </div>
                        </div>

                        <div class="estimation-tariff">
                            <i class="bi bi-cash-coin"></i>
                            Tarif: Rp {{ number_format($electricityTariff ?? 0, 0, ',', '.') }} / kWh
                        </div>
                    </div>

                    <div class="estimation-list">
                        @forelse (($paymentEstimations ?? []) as $key => $item)
                            <div class="estimation-item" id="estimation-item-{{ $key }}">
                                <button
                                    type="button"
                                    class="estimation-toggle"
                                    onclick="togglePaymentEstimation('{{ $key }}')"
                                >
                                    <div>
                                        <p class="estimation-toggle-title">{{ $item['label'] }}</p>
                                        <p class="estimation-toggle-period">{{ $item['period'] }}</p>
                                    </div>

                                    <div class="estimation-toggle-right">
                                        <div>
                                            <p class="estimation-toggle-cost-label">Estimasi Pembayaran</p>
                                            <p class="estimation-toggle-cost">
                                                Rp {{ number_format($item['estimated_cost'], 0, ',', '.') }}
                                            </p>
                                        </div>

                                        <i class="bi bi-chevron-down estimation-chevron"></i>
                                    </div>
                                </button>

                                <div class="estimation-detail">
                                    <div class="estimation-detail-grid">
                                        <div class="estimation-detail-card">
                                            <p class="estimation-detail-label">Total Pemakaian</p>
                                            <p class="estimation-detail-value">
                                                {{ number_format($item['usage_kwh'], 4, ',', '.') }} kWh
                                            </p>
                                            <p class="estimation-detail-note">
                                                Total energi ruangan atau panel pada periode {{ strtolower($item['label']) }}.
                                            </p>
                                        </div>

                                        <div class="estimation-detail-card">
                                            <p class="estimation-detail-label">Estimasi Biaya</p>
                                            <p class="estimation-detail-value cost">
                                                Rp {{ number_format($item['estimated_cost'], 0, ',', '.') }}
                                            </p>
                                            <p class="estimation-detail-note">
                                                Dihitung dari total kWh dikali tarif listrik per kWh.
                                            </p>
                                        </div>
                                    </div>

                                    <div class="estimation-formula">
                                        <p class="formula-label">Rumus Perhitungan</p>
                                        <p class="formula-text">
                                            {{ number_format($item['usage_kwh'], 4, ',', '.') }} kWh
                                            &times;
                                            Rp {{ number_format($item['tariff'], 0, ',', '.') }} / kWh
                                            =
                                            Rp {{ number_format($item['estimated_cost'], 0, ',', '.') }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="history-empty">
                                Data estimasi belum tersedia. Estimasi akan muncul setelah PZEM mengirim data pemakaian listrik ruangan.
                            </div>
                        @endforelse
                    </div>
                </div>

                <div class="history-panel sv-glass">
                    <div class="sv-panel-head">
                        <div>
                            <h3>Grafik Daya Ruangan</h3>
                            <div class="sv-panel-sub">Data daya total dan energi terbaru dari PZEM ruangan atau panel.</div>
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
                            <h3>Pemakaian Listrik Ruangan</h3>
                            <div class="sv-panel-sub">Riwayat pembacaan PZEM untuk setiap ruangan atau panel listrik.</div>
                        </div>
                    </div>

                    <div class="history-table-wrap">
                        <table class="history-table">
                            <thead>
                                <tr>
                                    <th>Ruangan</th>
                                    <th>Meter Ruangan</th>
                                    <th>Waktu</th>
                                    <th>Tegangan</th>
                                    <th>Arus</th>
                                    <th>Daya Total</th>
                                    <th>Energi</th>
                                </tr>
                            </thead>

                            <tbody>
                                @forelse ($logs as $log)
                                    @php
                                        $waktuLog = $log->observed_at ?? $log->updated_at ?? null;
                                    @endphp

                                    <tr>
                                        <td>{{ $log->room_name ?? '-' }}</td>
                                        <td>{{ $log->meter_name ?? $log->device_name ?? '-' }}</td>

                                      <td>
    @if($waktuLog)
        @php
            $waktuMentah = $waktuLog instanceof \Carbon\Carbon
                ? $waktuLog->format('Y-m-d H:i:s')
                : substr((string) $waktuLog, 0, 19);

            $waktuWib = \Carbon\Carbon::createFromFormat(
                'Y-m-d H:i:s',
                $waktuMentah,
                'UTC'
            )->timezone('Asia/Jakarta');
        @endphp

        {{ $waktuWib->format('d/m/Y H:i:s') }}
    @else
        -
    @endif
</td>

                                        <td>{{ number_format($log->voltage ?? 0, 2, ',', '.') }} V</td>
                                        <td>{{ number_format($log->current ?? 0, 2, ',', '.') }} A</td>
                                        <td>{{ number_format($log->power ?? 0, 2, ',', '.') }} W</td>
                                        <td>{{ number_format($log->energy_kwh ?? $log->energy ?? 0, 4, ',', '.') }} kWh</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7">
                                            <div class="history-empty">
                                                Belum ada data listrik ruangan. Pastikan PZEM sudah ditambahkan di Panel Teknisi dan ESP32 sudah mengirim data.
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if (method_exists($logs, 'hasPages') && $logs->hasPages())
                        <div style="margin-top: 18px;">
                            {{ $logs->links() }}
                        </div>
                    @endif
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
        function togglePaymentEstimation(key) {
            const item = document.getElementById(`estimation-item-${key}`);

            if (!item) {
                return;
            }

            item.classList.toggle('open');
        }

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
                            label: 'Daya Total Ruangan (W)',
                            data: chartPower,
                            tension: 0.35
                        },
                        {
                            label: 'Energi Kumulatif PZEM (kWh)',
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

        function normalizeExportReport(data) {
            const report = {
                generatedAt: new Date().toLocaleString('id-ID'),
                devices: [],
                summary: []
            };

            if (data && !Array.isArray(data) && (data.devices || data.summary)) {
                report.generatedAt = data.generated_at || report.generatedAt;
                report.devices = data.devices || [];
                report.summary = data.summary || [];

                return report;
            }

            const rows = Array.isArray(data) ? data : [];

            report.devices = rows
                .filter((row) => {
                    if (row.type === 'summary') {
                        return false;
                    }

                    if (row.type === 'device') {
                        return true;
                    }

                    const room = row.Ruangan || row.Room || '';
                    const meter = row['Meter Ruangan'] || row.Perangkat || row.Device || '';
                    const summary = row['Ringkasan Pemakaian'] || '';

                    return room &&
                        meter &&
                        room !== '-' &&
                        room !== 'TOTAL' &&
                        meter !== '-' &&
                        room !== 'RINGKASAN PEMAKAIAN' &&
                        summary !== 'RINGKASAN PEMAKAIAN';
                })
                .map((row) => ({
                    ruangan: row.ruangan || row.Ruangan || row.Room || '-',
                    meter_ruangan: row.meter_ruangan || row['Meter Ruangan'] || row.perangkat || row.Perangkat || row.Device || '-',
                    waktu: row.waktu || row.Waktu || row.Time || '-',
                    tegangan: row.tegangan || row['Tegangan (V)'] || row.Voltage || row['Voltage (V)'] || '0',
                    arus: row.arus || row['Arus (A)'] || row.Current || row['Current (A)'] || '0',
                    daya_total: row.daya_total || row['Daya Total (W)'] || row['Daya (W)'] || row.Power || row['Power (W)'] || '0',
                    energi: row.energi || row['Energi (kWh)'] || row['Energy (kWh)'] || '0'
                }));

            const typedSummaryRows = rows.filter((row) => row.type === 'summary');

            if (typedSummaryRows.length) {
                report.summary = typedSummaryRows.map((row) => ({
                    label: row.label || '-',
                    hari_ini: row.hari_ini || row['Hari Ini'] || '-',
                    minggu_ini: row.minggu_ini || row['Minggu Ini'] || '-',
                    bulan_ini: row.bulan_ini || row['Bulan Ini'] || '-'
                }));
            } else {
                const findSummaryRow = (label) => rows.find((row) => {
                    return row.Ruangan === label ||
                        row.Room === label ||
                        row['Ringkasan Pemakaian'] === label;
                }) || {};

                ['Energi (kWh)', 'Tarif / kWh', 'Estimasi Pembayaran'].forEach((label) => {
                    const row = findSummaryRow(label);

                    report.summary.push({
                        label,
                        hari_ini: row['Hari Ini'] || '-',
                        minggu_ini: row['Minggu Ini'] || '-',
                        bulan_ini: row['Bulan Ini'] || '-'
                    });
                });
            }

            return report;
        }

        function buildReportHtml(report) {
            const deviceRows = report.devices.length
                ? report.devices
                : [
                    {
                        ruangan: '-',
                        meter_ruangan: 'Tidak ada data',
                        waktu: '-',
                        tegangan: '-',
                        arus: '-',
                        daya_total: '-',
                        energi: '-'
                    }
                ];

            return `
                <div style="padding: 28px; font-family: Arial, sans-serif; color: #111; background: #fff; width: 980px;">
                    <div style="text-align: center; margin-bottom: 24px;">
                        <h2 style="margin: 0; font-size: 24px; font-weight: 800;">
                            Laporan Pemakaian Listrik Ruangan
                        </h2>
                        <p style="margin: 6px 0 0; color: #555; font-size: 15px;">
                            Riwayat pembacaan PZEM untuk setiap ruangan atau panel listrik
                        </p>
                        <p style="margin: 6px 0 0; color: #777; font-size: 12px;">
                            Dibuat pada: ${report.generatedAt}
                        </p>
                    </div>

                    <h3 style="margin: 0 0 10px; font-size: 14px; letter-spacing: .04em;">
                        DATA METER RUANGAN
                    </h3>

                    <table style="width: 100%; border-collapse: collapse; font-size: 12px; margin-bottom: 28px;">
                        <thead>
                            <tr style="background: #f3f4f6;">
                                <th style="padding: 9px; border: 1px solid #d9dce1; text-align: left;">Ruangan</th>
                                <th style="padding: 9px; border: 1px solid #d9dce1; text-align: left;">Meter Ruangan</th>
                                <th style="padding: 9px; border: 1px solid #d9dce1; text-align: left;">Waktu</th>
                                <th style="padding: 9px; border: 1px solid #d9dce1; text-align: left;">Tegangan (V)</th>
                                <th style="padding: 9px; border: 1px solid #d9dce1; text-align: left;">Arus (A)</th>
                                <th style="padding: 9px; border: 1px solid #d9dce1; text-align: left;">Daya Total (W)</th>
                                <th style="padding: 9px; border: 1px solid #d9dce1; text-align: left;">Energi (kWh)</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${deviceRows.map((row, index) => `
                                <tr style="background: ${index % 2 === 0 ? '#fff' : '#fafafa'};">
                                    <td style="padding: 8px 9px; border: 1px solid #d9dce1;">${row.ruangan}</td>
                                    <td style="padding: 8px 9px; border: 1px solid #d9dce1;">${row.meter_ruangan}</td>
                                    <td style="padding: 8px 9px; border: 1px solid #d9dce1;">${row.waktu}</td>
                                    <td style="padding: 8px 9px; border: 1px solid #d9dce1;">${row.tegangan}</td>
                                    <td style="padding: 8px 9px; border: 1px solid #d9dce1;">${row.arus}</td>
                                    <td style="padding: 8px 9px; border: 1px solid #d9dce1;">${row.daya_total}</td>
                                    <td style="padding: 8px 9px; border: 1px solid #d9dce1;">${row.energi}</td>
                                </tr>
                            `).join('')}
                        </tbody>
                    </table>

                    <h3 style="margin: 0 0 10px; font-size: 14px; letter-spacing: .04em;">
                        RINGKASAN PEMAKAIAN
                    </h3>

                    <table style="width: 620px; border-collapse: collapse; font-size: 12px;">
                        <thead>
                            <tr style="background: #f3f4f6;">
                                <th style="padding: 9px; border: 1px solid #d9dce1; text-align: left;"></th>
                                <th style="padding: 9px; border: 1px solid #d9dce1; text-align: left;">Hari Ini</th>
                                <th style="padding: 9px; border: 1px solid #d9dce1; text-align: left;">Minggu Ini</th>
                                <th style="padding: 9px; border: 1px solid #d9dce1; text-align: left;">Bulan Ini</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${report.summary.map((row) => `
                                <tr>
                                    <td style="padding: 8px 9px; border: 1px solid #d9dce1; font-weight: 700;">${row.label}</td>
                                    <td style="padding: 8px 9px; border: 1px solid #d9dce1;">${row.hari_ini}</td>
                                    <td style="padding: 8px 9px; border: 1px solid #d9dce1;">${row.minggu_ini}</td>
                                    <td style="padding: 8px 9px; border: 1px solid #d9dce1;">${row.bulan_ini}</td>
                                </tr>
                            `).join('')}
                        </tbody>
                    </table>
                </div>
            `;
        }

        async function exportPDF() {
            const btn = document.querySelector('button[onclick="exportPDF()"]');
            const originalHTML = btn.innerHTML;

            btn.innerHTML = '<i class="bi bi-hourglass-split"></i> Menyiapkan PDF...';
            btn.disabled = true;

            try {
                const response = await fetch('{{ route("energy.history.export") }}');

                if (!response.ok) {
                    throw new Error('Gagal mengambil data.');
                }

                const data = await response.json();
                const report = normalizeExportReport(data);

                if (report.devices.length === 0 && report.summary.length === 0) {
                    alert('Tidak ada data untuk diekspor.');
                    return;
                }

                const opt = {
                    margin: [10, 10, 10, 10],
                    filename: 'Pemakaian_Listrik_Ruangan_SmartVolt.pdf',
                    image: { type: 'jpeg', quality: 0.98 },
                    html2canvas: {
                        scale: 2,
                        useCORS: true,
                        backgroundColor: '#ffffff'
                    },
                    jsPDF: {
                        unit: 'mm',
                        format: 'a4',
                        orientation: 'landscape'
                    }
                };

                await html2pdf().set(opt).from(buildReportHtml(report)).save();
            } catch (error) {
                console.error('Gagal ekspor PDF:', error);
                alert('Gagal mengekspor data ke PDF.');
            } finally {
                btn.innerHTML = originalHTML;
                btn.disabled = false;
            }
        }

        async function exportExcel() {
            const btn = document.querySelector('button[onclick="exportExcel()"]');
            const originalHTML = btn.innerHTML;

            btn.innerHTML = '<i class="bi bi-hourglass-split"></i> Memuat...';
            btn.disabled = true;

            try {
                const response = await fetch('{{ route("energy.history.export") }}');

                if (!response.ok) {
                    throw new Error('Gagal mengambil data.');
                }

                const data = await response.json();
                const report = normalizeExportReport(data);

                if (report.devices.length === 0 && report.summary.length === 0) {
                    alert('Tidak ada data untuk diekspor.');
                    return;
                }

                const deviceRows = report.devices.length
                    ? report.devices
                    : [
                        {
                            ruangan: '-',
                            meter_ruangan: 'Tidak ada data',
                            waktu: '-',
                            tegangan: '-',
                            arus: '-',
                            daya_total: '-',
                            energi: '-'
                        }
                    ];

                const rows = [
                    ['Laporan Pemakaian Listrik Ruangan'],
                    ['Riwayat pembacaan PZEM untuk setiap ruangan atau panel listrik'],
                    ['Dibuat pada', report.generatedAt],
                    [],
                    ['DATA METER RUANGAN'],
                    ['Ruangan', 'Meter Ruangan', 'Waktu', 'Tegangan (V)', 'Arus (A)', 'Daya Total (W)', 'Energi (kWh)'],
                    ...deviceRows.map((row) => [
                        row.ruangan,
                        row.meter_ruangan,
                        row.waktu,
                        row.tegangan,
                        row.arus,
                        row.daya_total,
                        row.energi
                    ]),
                    [],
                    ['RINGKASAN PEMAKAIAN'],
                    ['', 'Hari Ini', 'Minggu Ini', 'Bulan Ini'],
                    ...report.summary.map((row) => [
                        row.label,
                        row.hari_ini,
                        row.minggu_ini,
                        row.bulan_ini
                    ])
                ];

                const worksheet = XLSX.utils.aoa_to_sheet(rows);
                const workbook = XLSX.utils.book_new();

                worksheet['!cols'] = [
                    { wch: 24 },
                    { wch: 28 },
                    { wch: 22 },
                    { wch: 16 },
                    { wch: 16 },
                    { wch: 18 },
                    { wch: 18 }
                ];

                XLSX.utils.book_append_sheet(workbook, worksheet, 'Pemakaian Listrik Ruangan');
                XLSX.writeFile(workbook, 'Pemakaian_Listrik_Ruangan_SmartVolt.xlsx');
            } catch (error) {
                console.error('Gagal ekspor Excel:', error);
                alert('Gagal mengekspor data.');
            } finally {
                btn.innerHTML = originalHTML;
                btn.disabled = false;
            }
        }
    </script>
</body>
</html>