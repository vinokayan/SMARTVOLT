<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pemakaian Listrik - SmartVolt</title>
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
        /* ============================================
           Estimasi Pembayaran Listrik
        ============================================ */
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
                            <h1 class="sv-page-title">Pemakaian Listrik</h1>
                            <p class="sv-page-sub">Riwayat pemantauan listrik dari perangkat SmartVolt</p>
                        </div>
                    </div>
                    <div class="sv-topbar-right" style="display: flex; gap: 12px;">
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
                        <h1 style="margin-bottom: 10px;">Pantau riwayat pemakaian listrik perangkat.</h1>
                        <p style="margin: 0; color: #b9cae3;">
                            Halaman ini menampilkan data tegangan, arus, daya, dan energi yang dikirim sensor ke sistem.
                        </p>
                    </div>
                </div>
                <div class="history-grid">
                    <div class="history-card sv-glass">
                        <div class="history-label">Total Data</div>
                        <div class="history-value">{{ $summary['total_logs'] ?? 0 }}</div>
                    </div>
                    <div class="history-card sv-glass">
                        <div class="history-label">Daya Tertinggi</div>
                        <div class="history-value">{{ number_format($summary['max_power'] ?? 0, 1) }} W</div>
                    </div>
                    <div class="history-card sv-glass">
                        <div class="history-label">Rata-rata Daya</div>
                        <div class="history-value">{{ number_format($summary['avg_power'] ?? 0, 1) }} W</div>
                    </div>
                    <div class="history-card sv-glass">
                        <div class="history-label">Rata-rata Tegangan</div>
                        <div class="history-value">{{ number_format($summary['avg_voltage'] ?? 0, 1) }} V</div>
                    </div>
                </div>
                {{-- ============================================
                     Estimasi Pembayaran Listrik
                ============================================ --}}
                <div class="estimation-panel sv-glass">
                    <div class="estimation-head">
                        <div>
                            <h3 style="margin: 0;">Estimasi Pembayaran Listrik</h3>
                            <div class="sv-panel-sub">Perkiraan biaya berdasarkan total pemakaian kWh dikalikan tarif listrik</div>
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
                                                Total energi yang digunakan pada periode {{ strtolower($item['label']) }}.
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
                                Data estimasi belum tersedia. Estimasi akan muncul setelah sensor mengirim data pemakaian listrik.
                            </div>
                        @endforelse
                    </div>
                </div>
                <div class="history-panel sv-glass">
                    <div class="sv-panel-head">
                        <div>
                            <h3>Grafik Pemakaian Listrik</h3>
                            <div class="sv-panel-sub">Data daya dan energi dari tabel energy_logs</div>
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
                            <div class="sv-panel-sub">Riwayat data yang tersimpan di database</div>
                        </div>
                    </div>
                    <div class="history-table-wrap">
                        <table class="history-table">
                            <thead>
                                <tr>
                                    <th>Ruangan</th>
                                    <th>Perangkat</th>
                                    <th>Waktu</th>
                                    <th>Tegangan</th>
                                    <th>Arus</th>
                                    <th>Daya</th>
                                    <th>Energi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($logs as $log)
                                    <tr>
                                        <td>{{ $log->room_name ?? '-' }}</td>
                                        <td>{{ $log->device_name ?? '-' }}</td>
                                        <td>{{ optional($log->created_at)->format('d/m/Y H:i:s') }}</td>
                                        <td>{{ number_format($log->voltage ?? 0, 2) }} V</td>
                                        <td>{{ number_format($log->current ?? 0, 2) }} A</td>
                                        <td>{{ number_format($log->power ?? 0, 2) }} W</td>
                                        <td>{{ number_format($log->energy_kwh ?? $log->energy ?? 0, 4) }} kWh</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7">
                                            <div class="history-empty">
                                                Belum ada data pemakaian listrik. Data akan muncul setelah ESP32 atau sensor mengirim data ke sistem.
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
        /* ============================================
           Toggle Estimasi Pembayaran
        ============================================ */
        function togglePaymentEstimation(key) {
            const item = document.getElementById(`estimation-item-${key}`);
            if (!item) {
                return;
            }
            item.classList.toggle('open');
        }
        /* ============================================
           Grafik Pemakaian Listrik
        ============================================ */
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
                            label: 'Daya (W)',
                            data: chartPower,
                            tension: 0.35
                        },
                        {
                            label: 'Energi (kWh)',
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
        /* ============================================
           Ekspor PDF
        ============================================ */
        async function exportPDF() {
            const btn = document.querySelector('button[onclick="exportPDF()"]');
            const originalHTML = btn.innerHTML;
            btn.innerHTML = '<i class="bi bi-hourglass-split"></i> Menyiapkan PDF...';
            btn.disabled = true;
            try {
                const response = await fetch('{{ route("energy.history.export") }}');
                if (!response.ok) throw new Error('Gagal mengambil data.');
                const data = await response.json();
                if (data.length === 0) {
                    alert('Tidak ada data untuk diekspor.');
                    return;
                }
                let html = `
                    <div style="padding: 30px; font-family: Arial, sans-serif; color: #000; background: #fff; width: 800px;">
                        <div style="text-align: center; margin-bottom: 25px;">
                            <h2 style="margin: 0; color: #111; font-size: 22px;">Laporan Pemakaian Listrik</h2>
                            <p style="margin: 5px 0 0 0; color: #555; font-size: 14px;">SmartVolt IoT Monitoring</p>
                            <p style="margin: 5px 0 0 0; color: #888; font-size: 12px;">Dibuat pada: ${new Date().toLocaleString('id-ID')}</p>
                        </div>
                        <table style="width: 100%; border-collapse: collapse; font-size: 12px; border: 1px solid #ddd;">
                            <thead>
                                <tr style="background-color: #f3f4f6; color: #111; text-align: left;">
                                    <th style="padding: 10px; border: 1px solid #ddd;">Ruangan</th>
                                    <th style="padding: 10px; border: 1px solid #ddd;">Perangkat</th>
                                    <th style="padding: 10px; border: 1px solid #ddd;">Waktu</th>
                                    <th style="padding: 10px; border: 1px solid #ddd;">Tegangan (V)</th>
                                    <th style="padding: 10px; border: 1px solid #ddd;">Arus (A)</th>
                                    <th style="padding: 10px; border: 1px solid #ddd;">Daya (W)</th>
                                    <th style="padding: 10px; border: 1px solid #ddd;">Energi (kWh)</th>
                                </tr>
                            </thead>
                            <tbody>
                `;
                data.forEach((row, index) => {
                    const bg = index % 2 === 0 ? '#ffffff' : '#fafafa';
                    html += `
                        <tr style="background-color: ${bg};">
                            <td style="padding: 8px 10px; border: 1px solid #ddd; color: #000;">${row['Room'] || row['Ruangan'] || '-'}</td>
                            <td style="padding: 8px 10px; border: 1px solid #ddd; color: #000;">${row['Device'] || row['Perangkat'] || '-'}</td>
                            <td style="padding: 8px 10px; border: 1px solid #ddd; color: #000;">${row['Waktu'] || row['Time'] || '-'}</td>
                            <td style="padding: 8px 10px; border: 1px solid #ddd; color: #000;">${row['Voltage (V)'] || row['Tegangan (V)'] || '0'}</td>
                            <td style="padding: 8px 10px; border: 1px solid #ddd; color: #000;">${row['Current (A)'] || row['Arus (A)'] || '0'}</td>
                            <td style="padding: 8px 10px; border: 1px solid #ddd; color: #000;">${row['Power (W)'] || row['Daya (W)'] || '0'}</td>
                            <td style="padding: 8px 10px; border: 1px solid #ddd; color: #000;">${row['Energy (kWh)'] || row['Energi (kWh)'] || '0'}</td>
                        </tr>
                    `;
                });
                html += `
                            </tbody>
                        </table>
                    </div>
                `;
                const opt = {
                    margin: [10, 10, 10, 10],
                    filename: 'Pemakaian_Listrik_SmartVolt.pdf',
                    image: { type: 'jpeg', quality: 0.98 },
                    html2canvas: { scale: 2, useCORS: true, backgroundColor: '#ffffff' },
                    jsPDF: { unit: 'mm', format: 'a4', orientation: 'portrait' }
                };
                await html2pdf().set(opt).from(html).save();
            } catch (error) {
                console.error('Gagal ekspor PDF:', error);
                alert('Gagal mengekspor data ke PDF.');
            } finally {
                btn.innerHTML = originalHTML;
                btn.disabled = false;
            }
        }
        /* ============================================
           Ekspor Excel
        ============================================ */
        async function exportExcel() {
            const btn = document.querySelector('button[onclick="exportExcel()"]');
            const originalHTML = btn.innerHTML;
            btn.innerHTML = '<i class="bi bi-hourglass-split"></i> Memuat...';
            btn.disabled = true;
            try {
                const response = await fetch('{{ route("energy.history.export") }}');
                if (!response.ok) throw new Error('Gagal mengambil data.');
                const data = await response.json();
                if (data.length === 0) {
                    alert('Tidak ada data untuk diekspor.');
                    return;
                }
                const worksheet = XLSX.utils.json_to_sheet(data);
                const workbook = XLSX.utils.book_new();
                XLSX.utils.book_append_sheet(workbook, worksheet, "Pemakaian Listrik");
                worksheet['!cols'] = [
                    {wch: 20},
                    {wch: 22},
                    {wch: 22},
                    {wch: 15},
                    {wch: 15},
                    {wch: 15},
                    {wch: 18}
                ];
                XLSX.writeFile(workbook, "Pemakaian_Listrik_SmartVolt.xlsx");
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