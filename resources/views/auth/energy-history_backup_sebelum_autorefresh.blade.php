<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pemakaian Listrik Ruangan - SmartVolt</title>

    @php
        $brandCssPath = public_path('assets/css/smartvolt-brand.css');
        $brandCssVersion = file_exists($brandCssPath)
            ? filemtime($brandCssPath)
            : '1';
    @endphp

    <link rel="stylesheet" href="{{ asset('assets/css/smartvolt-brand.css') }}?v={{ $brandCssVersion }}">
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


        .history-filter-panel {
            padding: 20px 24px;
            border-radius: 24px;
            margin-bottom: 20px;
        }

        .history-filter-form {
            display: grid;
            grid-template-columns: minmax(220px, 1.4fr) repeat(2, minmax(170px, .8fr)) auto;
            gap: 14px;
            align-items: end;
        }

        .history-filter-field {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .history-filter-field label {
            color: #9fb4d1;
            font-size: 12px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: .06em;
        }

        .history-filter-field select,
        .history-filter-field input {
            width: 100%;
            height: 44px;
            padding: 0 14px;
            border-radius: 12px;
            border: 1px solid rgba(255,255,255,.12);
            background: rgba(7, 18, 38, .65);
            color: #f2f7ff;
            outline: none;
        }

        .history-filter-field select:focus,
        .history-filter-field input:focus {
            border-color: rgba(96, 165, 250, .75);
            box-shadow: 0 0 0 3px rgba(59, 130, 246, .13);
        }

        .history-filter-actions {
            display: flex;
            gap: 10px;
        }

        .history-filter-button {
            height: 44px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 0 16px;
            border-radius: 12px;
            border: 1px solid rgba(59, 130, 246, .45);
            background: rgba(59, 130, 246, .16);
            color: #bfdbfe;
            font-weight: 800;
            cursor: pointer;
            text-decoration: none;
            white-space: nowrap;
        }

        .history-filter-button.secondary {
            border-color: rgba(255,255,255,.12);
            background: rgba(255,255,255,.05);
            color: #dbeafe;
        }

        .history-period-note {
            margin-top: 12px;
            color: #9fb4d1;
            font-size: 13px;
        }

        .library-warning {
            display: none;
            margin-bottom: 20px;
            padding: 14px 16px;
            border-radius: 14px;
            border: 1px solid rgba(245, 158, 11, .45);
            background: rgba(245, 158, 11, .12);
            color: #fde68a;
            line-height: 1.6;
        }

        @media (max-width: 1000px) {
            .history-grid {
                grid-template-columns: repeat(2, 1fr);
            }

            .history-filter-form {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .history-filter-actions {
                grid-column: 1 / -1;
            }
        }

        @media (max-width: 640px) {
            .history-grid {
                grid-template-columns: 1fr;
            }

            .estimation-detail-grid,
            .history-filter-form {
                grid-template-columns: 1fr;
            }

            .history-filter-actions {
                grid-column: auto;
                flex-direction: column;
            }

            .history-filter-button {
                width: 100%;
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

                <div id="localLibraryWarning" class="library-warning" role="alert"></div>

                <div class="history-filter-panel sv-glass">
                    <form
                        method="GET"
                        action="{{ route('energy.history') }}"
                        class="history-filter-form"
                    >
                        <div class="history-filter-field">
                            <label for="device_id">Meter Ruangan</label>
                            <select id="device_id" name="device_id">
                                <option value="">Semua meter ruangan</option>

                                @foreach ($devices as $meter)
                                    <option
                                        value="{{ $meter->id }}"
                                        @selected(
                                            (string) ($filters['meter_id'] ?? '')
                                            === (string) $meter->id
                                        )
                                    >
                                        {{ $meter->room_name }} — {{ $meter->meter_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="history-filter-field">
                            <label for="date_from">Tanggal Mulai</label>
                            <input
                                id="date_from"
                                type="date"
                                name="date_from"
                                value="{{ $filters['date_from'] ?? '' }}"
                            >
                        </div>

                        <div class="history-filter-field">
                            <label for="date_to">Tanggal Selesai</label>
                            <input
                                id="date_to"
                                type="date"
                                name="date_to"
                                value="{{ $filters['date_to'] ?? '' }}"
                            >
                        </div>

                        <div class="history-filter-actions">
                            <button type="submit" class="history-filter-button">
                                <i class="bi bi-funnel-fill"></i>
                                Terapkan
                            </button>

                            <a
                                href="{{ route('energy.history') }}"
                                class="history-filter-button secondary"
                            >
                                <i class="bi bi-arrow-counterclockwise"></i>
                                Hari Ini
                            </a>
                        </div>
                    </form>

                    <div class="history-period-note">
                        Ringkasan, grafik, dan tabel menggunakan periode
                        {{ \Carbon\Carbon::parse($filters['date_from'])->format('d/m/Y') }}
                        sampai
                        {{ \Carbon\Carbon::parse($filters['date_to'])->format('d/m/Y') }}.
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
                            aria-label="Grafik daya dan energi PZEM"
                            role="img"
                        ></canvas>
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
                                    <th>Waktu Data Terakhir</th>
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
                                            @if ($waktuLog)
                                                @php
                                                    /*
                                                     * observed_at sudah disimpan dalam
                                                     * Asia/Jakarta. Jangan dikonversi dari
                                                     * UTC lagi karena akan menambah 7 jam.
                                                     */
                                                    $waktuTampil = $waktuLog instanceof \Carbon\CarbonInterface
                                                        ? $waktuLog->format('d/m/Y H:i:s')
                                                        : \Carbon\Carbon::parse(
                                                            (string) $waktuLog,
                                                            config('app.timezone', 'Asia/Jakarta')
                                                        )->format('d/m/Y H:i:s');
                                                @endphp

                                                {{ $waktuTampil }}
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

    @php
        /*
         * Data PHP dikodekan lebih dahulu, lalu ditempatkan pada elemen
         * application/json. Cara ini aman untuk Blade dan tidak dibaca
         * VS Code sebagai JavaScript decorator.
         */
        $smartVoltPageData = [
            'chart' => [
                'labels' => $chart['labels'] ?? [],
                'power' => $chart['power'] ?? [],
                'energy' => $chart['energy'] ?? [],
            ],
            'paymentEstimations' => $paymentEstimations ?? [],
            'electricityTariff' => (float) ($electricityTariff ?? 0),
            'exportRoute' => route('energy.history.export'),
        ];

        $smartVoltPageDataJson = json_encode(
            $smartVoltPageData,
            JSON_UNESCAPED_UNICODE
                | JSON_UNESCAPED_SLASHES
                | JSON_HEX_TAG
                | JSON_HEX_AMP
                | JSON_HEX_APOS
                | JSON_HEX_QUOT
        );
    @endphp

    <script id="smartVoltPageData" type="application/json">{!! $smartVoltPageDataJson !!}</script>

    <script>
        const smartVoltPageDataElement = document.getElementById(
            'smartVoltPageData'
        );

        let smartVoltPageData = {};

        try {
            smartVoltPageData = JSON.parse(
                smartVoltPageDataElement?.textContent || '{}'
            );
        } catch (error) {
            console.error(
                'Data halaman SmartVolt tidak valid.',
                error
            );
        }

        const smartVoltChart = smartVoltPageData.chart || {
            labels: [],
            power: [],
            energy: [],
        };

        const smartVoltPaymentEstimations =
            smartVoltPageData.paymentEstimations || {};

        const smartVoltElectricityTariff = Number(
            smartVoltPageData.electricityTariff || 0
        );

        const smartVoltExportRoute =
            smartVoltPageData.exportRoute || '';

        function togglePaymentEstimation(key) {
            const item = document.getElementById(
                `estimation-item-${key}`
            );

            if (! item) {
                return;
            }

            const button = item.querySelector(
                '.estimation-toggle'
            );

            const isOpen = item.classList.toggle('open');

            if (button) {
                button.setAttribute(
                    'aria-expanded',
                    isOpen ? 'true' : 'false'
                );
            }
        }

        function showLocalLibraryWarning(messages) {
            const warning = document.getElementById(
                'localLibraryWarning'
            );

            if (! warning || messages.length === 0) {
                return;
            }

            warning.style.display = 'block';
            warning.textContent = messages.join(' ');
        }

        function initializeChart() {
            const chartElement = document.getElementById(
                'energyHistoryChart'
            );

            if (! chartElement) {
                return;
            }

            if (typeof Chart === 'undefined') {
                showLocalLibraryWarning([
                    'Grafik tidak dapat dimuat.',
                    'Pastikan Chart.js tersedia atau komputer mempunyai akses internet.',
                ]);

                return;
            }

            const context = chartElement.getContext('2d');

            new Chart(context, {
                type: 'line',
                data: {
                    labels: smartVoltChart.labels,
                    datasets: [
                        {
                            label: 'Daya Total Ruangan (W)',
                            data: smartVoltChart.power,
                            tension: 0.35,
                            spanGaps: true,
                        },
                        {
                            label: 'Energi Kumulatif PZEM (kWh)',
                            data: smartVoltChart.energy,
                            tension: 0.35,
                            spanGaps: true,
                        },
                    ],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: {
                        mode: 'index',
                        intersect: false,
                    },
                    plugins: {
                        legend: {
                            labels: {
                                color: '#b9cae3',
                            },
                        },
                        tooltip: {
                            callbacks: {
                                label(context) {
                                    const suffix = context.datasetIndex === 0
                                        ? ' W'
                                        : ' kWh';

                                    return `${context.dataset.label}: ${context.parsed.y}${suffix}`;
                                },
                            },
                        },
                    },
                    scales: {
                        x: {
                            ticks: {
                                color: '#7f93ae',
                            },
                            grid: {
                                color: 'rgba(255,255,255,.04)',
                            },
                        },
                        y: {
                            beginAtZero: true,
                            ticks: {
                                color: '#7f93ae',
                            },
                            grid: {
                                color: 'rgba(255,255,255,.05)',
                            },
                        },
                    },
                },
            });
        }

        function buildExportUrl() {
            const url = new URL(
                smartVoltExportRoute,
                window.location.origin
            );

            const currentParameters = new URLSearchParams(
                window.location.search
            );

            currentParameters.forEach((value, key) => {
                url.searchParams.set(key, value);
            });

            return url.toString();
        }

        function formatNumber(value, digits = 0) {
            const numericValue = Number(value || 0);

            return new Intl.NumberFormat('id-ID', {
                minimumFractionDigits: digits,
                maximumFractionDigits: digits,
            }).format(numericValue);
        }

        function escapeHtml(value) {
            return String(value ?? '')
                .replaceAll('&', '&amp;')
                .replaceAll('<', '&lt;')
                .replaceAll('>', '&gt;')
                .replaceAll('"', '&quot;')
                .replaceAll("'", '&#039;');
        }

        function getPaymentItem(key) {
            return smartVoltPaymentEstimations[key] || {
                usage_kwh: 0,
                estimated_cost: 0,
                tariff: smartVoltElectricityTariff,
            };
        }

        function buildPaymentSummaryRows() {
            const today = getPaymentItem('today');
            const week = getPaymentItem('week');
            const month = getPaymentItem('month');

            return [
                {
                    label: 'Energi (kWh)',
                    hari_ini: `${formatNumber(today.usage_kwh, 4)} kWh`,
                    minggu_ini: `${formatNumber(week.usage_kwh, 4)} kWh`,
                    bulan_ini: `${formatNumber(month.usage_kwh, 4)} kWh`,
                },
                {
                    label: 'Tarif / kWh',
                    hari_ini: `Rp ${formatNumber(today.tariff || smartVoltElectricityTariff)}`,
                    minggu_ini: `Rp ${formatNumber(week.tariff || smartVoltElectricityTariff)}`,
                    bulan_ini: `Rp ${formatNumber(month.tariff || smartVoltElectricityTariff)}`,
                },
                {
                    label: 'Estimasi Pembayaran',
                    hari_ini: `Rp ${formatNumber(today.estimated_cost)}`,
                    minggu_ini: `Rp ${formatNumber(week.estimated_cost)}`,
                    bulan_ini: `Rp ${formatNumber(month.estimated_cost)}`,
                },
            ];
        }

        function normalizeExportReport(data) {
            const rows = Array.isArray(data) ? data : [];

            const devices = rows
                .filter((row) => {
                    const room = row.Ruangan || row.Room || '';

                    return room
                        && room !== '-'
                        && room !== 'TOTAL';
                })
                .map((row) => ({
                    ruangan: row.Ruangan || row.Room || '-',
                    meter_ruangan:
                        row['Meter Ruangan']
                        || row.Perangkat
                        || row.Device
                        || '-',
                    waktu: row.Waktu || row.Time || '-',
                    tegangan:
                        row['Tegangan (V)']
                        || row.Voltage
                        || row['Voltage (V)']
                        || '0',
                    arus:
                        row['Arus (A)']
                        || row.Current
                        || row['Current (A)']
                        || '0',
                    daya_total:
                        row['Daya Total (W)']
                        || row['Daya (W)']
                        || row.Power
                        || row['Power (W)']
                        || '0',
                    energi:
                        row['Energi (kWh)']
                        || row['Energy (kWh)']
                        || '0',
                }));

            return {
                generatedAt: new Date().toLocaleString(
                    'id-ID'
                ),
                devices,
                summary: buildPaymentSummaryRows(),
            };
        }

        function buildReportHtml(report) {
            const deviceRows = report.devices.length > 0
                ? report.devices
                : [
                    {
                        ruangan: '-',
                        meter_ruangan: 'Tidak ada data',
                        waktu: '-',
                        tegangan: '-',
                        arus: '-',
                        daya_total: '-',
                        energi: '-',
                    },
                ];

            const deviceTableRows = deviceRows
                .map((row, index) => `
                    <tr style="background: ${index % 2 === 0 ? '#fff' : '#fafafa'};">
                        <td style="padding: 8px 9px; border: 1px solid #d9dce1;">
                            ${escapeHtml(row.ruangan)}
                        </td>
                        <td style="padding: 8px 9px; border: 1px solid #d9dce1;">
                            ${escapeHtml(row.meter_ruangan)}
                        </td>
                        <td style="padding: 8px 9px; border: 1px solid #d9dce1;">
                            ${escapeHtml(row.waktu)}
                        </td>
                        <td style="padding: 8px 9px; border: 1px solid #d9dce1;">
                            ${escapeHtml(row.tegangan)}
                        </td>
                        <td style="padding: 8px 9px; border: 1px solid #d9dce1;">
                            ${escapeHtml(row.arus)}
                        </td>
                        <td style="padding: 8px 9px; border: 1px solid #d9dce1;">
                            ${escapeHtml(row.daya_total)}
                        </td>
                        <td style="padding: 8px 9px; border: 1px solid #d9dce1;">
                            ${escapeHtml(row.energi)}
                        </td>
                    </tr>
                `)
                .join('');

            const summaryRows = report.summary
                .map((row) => `
                    <tr>
                        <td style="padding: 8px 9px; border: 1px solid #d9dce1; font-weight: 700;">
                            ${escapeHtml(row.label)}
                        </td>
                        <td style="padding: 8px 9px; border: 1px solid #d9dce1;">
                            ${escapeHtml(row.hari_ini)}
                        </td>
                        <td style="padding: 8px 9px; border: 1px solid #d9dce1;">
                            ${escapeHtml(row.minggu_ini)}
                        </td>
                        <td style="padding: 8px 9px; border: 1px solid #d9dce1;">
                            ${escapeHtml(row.bulan_ini)}
                        </td>
                    </tr>
                `)
                .join('');

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
                            Dibuat pada: ${escapeHtml(report.generatedAt)}
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
                            ${deviceTableRows}
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
                            ${summaryRows}
                        </tbody>
                    </table>
                </div>
            `;
        }

        async function fetchExportReport() {
            const response = await fetch(buildExportUrl(), {
                headers: {
                    Accept: 'application/json',
                },
            });

            if (! response.ok) {
                throw new Error(
                    `Gagal mengambil data ekspor (${response.status}).`
                );
            }

            const data = await response.json();

            return normalizeExportReport(data);
        }

        async function exportPDF() {
            const button = document.querySelector(
                'button[onclick="exportPDF()"]'
            );

            if (! button) {
                return;
            }

            if (typeof html2pdf === 'undefined') {
                alert(
                    'Library PDF belum tersedia. Pastikan html2pdf.js dapat dimuat.'
                );
                return;
            }

            const originalHtml = button.innerHTML;

            button.innerHTML =
                '<i class="bi bi-hourglass-split"></i> Menyiapkan PDF...';
            button.disabled = true;

            try {
                const report = await fetchExportReport();

                const options = {
                    margin: [10, 10, 10, 10],
                    filename:
                        'Pemakaian_Listrik_Ruangan_SmartVolt.pdf',
                    image: {
                        type: 'jpeg',
                        quality: 0.98,
                    },
                    html2canvas: {
                        scale: 2,
                        useCORS: true,
                        backgroundColor: '#ffffff',
                    },
                    jsPDF: {
                        unit: 'mm',
                        format: 'a4',
                        orientation: 'landscape',
                    },
                };

                await html2pdf()
                    .set(options)
                    .from(buildReportHtml(report))
                    .save();
            } catch (error) {
                console.error('Gagal ekspor PDF:', error);
                alert('Gagal mengekspor data ke PDF.');
            } finally {
                button.innerHTML = originalHtml;
                button.disabled = false;
            }
        }

        async function exportExcel() {
            const button = document.querySelector(
                'button[onclick="exportExcel()"]'
            );

            if (! button) {
                return;
            }

            if (typeof XLSX === 'undefined') {
                alert(
                    'Library Excel belum tersedia. Pastikan XLSX dapat dimuat.'
                );
                return;
            }

            const originalHtml = button.innerHTML;

            button.innerHTML =
                '<i class="bi bi-hourglass-split"></i> Memuat...';
            button.disabled = true;

            try {
                const report = await fetchExportReport();

                const deviceRows = report.devices.length > 0
                    ? report.devices
                    : [
                        {
                            ruangan: '-',
                            meter_ruangan: 'Tidak ada data',
                            waktu: '-',
                            tegangan: '-',
                            arus: '-',
                            daya_total: '-',
                            energi: '-',
                        },
                    ];

                const rows = [
                    ['Laporan Pemakaian Listrik Ruangan'],
                    [
                        'Riwayat pembacaan PZEM untuk setiap ruangan atau panel listrik',
                    ],
                    ['Dibuat pada', report.generatedAt],
                    [],
                    ['DATA METER RUANGAN'],
                    [
                        'Ruangan',
                        'Meter Ruangan',
                        'Waktu',
                        'Tegangan (V)',
                        'Arus (A)',
                        'Daya Total (W)',
                        'Energi (kWh)',
                    ],
                    ...deviceRows.map((row) => [
                        row.ruangan,
                        row.meter_ruangan,
                        row.waktu,
                        row.tegangan,
                        row.arus,
                        row.daya_total,
                        row.energi,
                    ]),
                    [],
                    ['RINGKASAN PEMAKAIAN'],
                    [
                        '',
                        'Hari Ini',
                        'Minggu Ini',
                        'Bulan Ini',
                    ],
                    ...report.summary.map((row) => [
                        row.label,
                        row.hari_ini,
                        row.minggu_ini,
                        row.bulan_ini,
                    ]),
                ];

                const worksheet = XLSX.utils.aoa_to_sheet(
                    rows
                );

                const workbook = XLSX.utils.book_new();

                worksheet['!cols'] = [
                    { wch: 24 },
                    { wch: 28 },
                    { wch: 22 },
                    { wch: 18 },
                    { wch: 18 },
                    { wch: 20 },
                    { wch: 18 },
                ];

                XLSX.utils.book_append_sheet(
                    workbook,
                    worksheet,
                    'Pemakaian Listrik Ruangan'
                );

                XLSX.writeFile(
                    workbook,
                    'Pemakaian_Listrik_Ruangan_SmartVolt.xlsx'
                );
            } catch (error) {
                console.error('Gagal ekspor Excel:', error);
                alert('Gagal mengekspor data ke Excel.');
            } finally {
                button.innerHTML = originalHtml;
                button.disabled = false;
            }
        }

        document.addEventListener('DOMContentLoaded', () => {
            initializeChart();

            const unavailableLibraries = [];

            if (typeof html2pdf === 'undefined') {
                unavailableLibraries.push(
                    'Fitur PDF tidak tersedia.'
                );
            }

            if (typeof XLSX === 'undefined') {
                unavailableLibraries.push(
                    'Fitur Excel tidak tersedia.'
                );
            }

            showLocalLibraryWarning(unavailableLibraries);
        });

        /*
         * Website memperbarui data setiap 60 detik, sama dengan interval
         * pengiriman telemetry ESP32 ke API.
         *
         * Yang ditampilkan tetap hanya satu waktu, yaitu observed_at
         * atau waktu data telemetry terakhir. Website tidak membuat
         * jam digital terpisah dan tidak mengubah waktu data menjadi
         * waktu komputer saat ini.
         */
        const ENERGY_PAGE_REFRESH_INTERVAL_MS = 60 * 1000;

        window.setInterval(() => {
            /*
             * Jangan memuat ulang halaman ketika tab tidak sedang
             * dilihat atau ketika pengguna sedang mengisi filter.
             */
            const activeElement = document.activeElement;
            const isEditingFilter = activeElement
                && ['INPUT', 'SELECT', 'TEXTAREA'].includes(
                    activeElement.tagName
                );

            if (document.hidden || isEditingFilter) {
                return;
            }

            window.location.reload();
        }, ENERGY_PAGE_REFRESH_INTERVAL_MS);

        /*
         * Ketika pengguna kembali ke tab setelah meninggalkannya,
         * langsung ambil data terbaru tanpa menunggu interval berikutnya.
         */
        document.addEventListener('visibilitychange', () => {
            if (! document.hidden) {
                window.location.reload();
            }
        });
    </script>
</body>
</html>