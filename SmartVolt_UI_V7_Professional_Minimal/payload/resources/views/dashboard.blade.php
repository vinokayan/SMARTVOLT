@extends('layouts.app')

@section('title', 'Beranda')
@section('page-title', 'Beranda')
@section('page-subtitle', 'Ringkasan listrik rumah saat ini.')
@section('body-class', 'sv-dashboard-page sv-pro-dashboard')

@php
    $stats = $dashboardData['stats'] ?? [];
    $system = $dashboardData['system'] ?? [];
    $chart = $dashboardData['chart'] ?? ['labels' => [], 'power' => [], 'energy' => []];
    $recentReadings = collect($dashboardData['recent_readings'] ?? [])->take(5);
    $dashboardRooms = collect($dashboardData['rooms'] ?? []);

    $loadStatus = $stats['load_status'] ?? 'normal';
    $loadPercentage = min(100, max(0, (float) ($stats['load_percentage'] ?? 0)));
    $energyComparison = $stats['energy_comparison_percent'] ?? null;

    $statusBadgeClass = ($system['has_fresh_data'] ?? false)
        ? 'sv-badge-success'
        : (($system['has_data'] ?? false) ? 'sv-badge-warning' : 'sv-badge-neutral');

    $systemStatusLabel = ($system['has_fresh_data'] ?? false)
        ? 'Sistem terhubung'
        : (($system['has_data'] ?? false) ? 'Data terlambat' : 'Belum terhubung');
@endphp

@section('system-status')
    <span class="sv-badge {{ $statusBadgeClass }}" data-dashboard-system-badge>
        <span class="sv-status-dot" aria-hidden="true"></span>
        <span data-dashboard-system-label>{{ $systemStatusLabel }}</span>
    </span>
@endsection

@section('content')
    <section class="sv-pro-metrics" aria-label="Ringkasan penggunaan listrik">
        <article class="sv-pro-metric sv-pro-metric--primary">
            <div class="sv-pro-metric-head">
                <span class="sv-pro-icon-tile sv-pro-icon-tile--blue"><x-icon name="bolt" :size="22" /></span>
                <span class="sv-pro-metric-label">Daya saat ini</span>
            </div>
            <div class="sv-pro-metric-value">
                <strong data-stat-current-power>{{ number_format((float) ($stats['current_power'] ?? 0), 1, ',', '.') }}</strong>
                <small>W</small>
            </div>
            <div class="sv-pro-metric-meta">
                <span class="sv-pro-state {{ $loadStatus === 'danger' ? 'is-danger' : ($loadStatus === 'warning' ? 'is-warning' : 'is-success') }}" data-load-status-badge>
                    <span data-load-status-label>{{ $loadStatus === 'danger' ? 'Beban tinggi' : ($loadStatus === 'warning' ? 'Mendekati batas' : 'Normal') }}</span>
                </span>
                <span><strong data-stat-load-percentage>{{ number_format($loadPercentage, 0, ',', '.') }}</strong>% batas</span>
            </div>
            <div class="sv-progress {{ $loadStatus === 'danger' ? 'is-danger' : ($loadStatus === 'warning' ? 'is-warning' : '') }}" data-load-progress style="--sv-progress: {{ $loadPercentage }}%"><span></span></div>
        </article>

        <article class="sv-pro-metric">
            <div class="sv-pro-metric-head">
                <span class="sv-pro-icon-tile sv-pro-icon-tile--green"><x-icon name="energy" :size="21" /></span>
                <span class="sv-pro-metric-label">Energi hari ini</span>
            </div>
            <div class="sv-pro-metric-value">
                <strong data-stat-energy-today>{{ number_format((float) ($stats['total_energy_today'] ?? 0), 3, ',', '.') }}</strong>
                <small>kWh</small>
            </div>
            <p class="sv-pro-metric-note" data-energy-comparison>
                @if(is_numeric($energyComparison))
                    {{ $energyComparison > 0 ? 'Naik' : ($energyComparison < 0 ? 'Lebih hemat' : 'Sama') }}
                    {{ number_format(abs((float) $energyComparison), 1, ',', '.') }}% dari kemarin
                @else
                    Belum ada perbandingan
                @endif
            </p>
        </article>

        <article class="sv-pro-metric">
            <div class="sv-pro-metric-head">
                <span class="sv-pro-icon-tile sv-pro-icon-tile--amber"><x-icon name="money" :size="21" /></span>
                <span class="sv-pro-metric-label">Estimasi bulan ini</span>
            </div>
            <div class="sv-pro-metric-value sv-pro-metric-value--money">
                <small>Rp</small>
                <strong data-stat-monthly-cost>{{ number_format((float) ($stats['monthly_estimated_cost'] ?? 0), 0, ',', '.') }}</strong>
            </div>
            <p class="sv-pro-metric-note"><span data-stat-monthly-energy>{{ number_format((float) ($stats['monthly_energy_usage'] ?? 0), 3, ',', '.') }}</span> kWh tercatat</p>
        </article>

        <article class="sv-pro-metric">
            <div class="sv-pro-metric-head">
                <span class="sv-pro-icon-tile sv-pro-icon-tile--cyan"><x-icon name="plug" :size="21" /></span>
                <span class="sv-pro-metric-label">Perangkat aktif</span>
            </div>
            <div class="sv-pro-metric-value">
                <strong data-stat-active-devices>{{ (int) ($stats['active_devices'] ?? 0) }}</strong>
                <small>dari <span data-stat-total-devices>{{ (int) ($stats['total_devices'] ?? 0) }}</span></small>
            </div>
            <p class="sv-pro-metric-note"><span data-stat-online-active-devices>{{ (int) ($stats['online_active_devices'] ?? 0) }}</span> siap dikontrol</p>
        </article>
    </section>

    <section class="sv-pro-system-strip" aria-label="Status sistem SmartVolt">
        <div class="sv-pro-system-item {{ ($system['esp_online'] ?? false) ? 'is-success' : 'is-warning' }}" data-system-esp>
            <span class="sv-pro-system-icon"><x-icon name="server" :size="18" /></span>
            <div><strong data-system-esp-label>{{ ($system['esp_online'] ?? false) ? (($system['online_esp_count'] ?? 0) . ' ESP32 terhubung') : 'ESP32 belum terhubung' }}</strong><span>Perangkat utama</span></div>
        </div>
        <div class="sv-pro-system-item {{ ($system['has_fresh_data'] ?? false) ? 'is-success' : 'is-warning' }}" data-system-pzem>
            <span class="sv-pro-system-icon"><x-icon name="sensor" :size="18" /></span>
            <div><strong>Sensor <span data-system-pzem-label>{{ $system['pzem_status'] ?? 'Belum tersedia' }}</span></strong><span>Pembacaan listrik</span></div>
        </div>
        <div class="sv-pro-system-item {{ ($system['esp_online'] ?? false) ? 'is-success' : 'is-warning' }}" data-system-control>
            <span class="sv-pro-system-icon"><x-icon name="mqtt" :size="18" /></span>
            <div><strong>Kontrol <span data-system-control-label>{{ $system['control_channel_status'] ?? 'Menunggu perangkat' }}</span></strong><span>Saluran MQTT</span></div>
        </div>
        <div class="sv-pro-system-item {{ ($system['has_data'] ?? false) ? 'is-success' : 'is-warning' }}" data-system-latest>
            <span class="sv-pro-system-icon"><x-icon name="clock" :size="18" /></span>
            <div><strong data-system-latest-label>{{ $system['latest_received_human'] ?? 'Belum ada data' }}</strong><span>Data terakhir</span></div>
        </div>
    </section>

    <section class="sv-pro-dashboard-grid">
        <article class="sv-card sv-pro-chart-card">
            <header class="sv-card-header">
                <div>
                    <h2>Daya 24 Jam Terakhir</h2>
                    <p>Perubahan daya terbaru dari meter utama.</p>
                </div>
                <a href="{{ route('energy.history') }}" class="sv-text-button">Lihat analisis</a>
            </header>
            <div class="sv-card-body">
                <div class="sv-chart-summary sv-chart-summary-compact">
                    <div><span>Saat ini</span><strong><span data-chart-current>{{ number_format((float) ($stats['current_power'] ?? 0), 1, ',', '.') }}</span> <small data-chart-unit>W</small></strong></div>
                    <div><span>Rata-rata</span><strong><span data-chart-average>0</span> <small data-chart-average-unit>W</small></strong></div>
                    <div><span>Tertinggi</span><strong><span data-chart-maximum>0</span> <small data-chart-maximum-unit>W</small></strong></div>
                </div>

                <div class="sv-empty-state {{ collect($chart['labels'] ?? [])->isNotEmpty() ? 'is-hidden' : '' }}" data-dashboard-chart-empty>
                    <span class="sv-pro-icon-tile sv-pro-icon-tile--blue"><x-icon name="chart" :size="22" /></span>
                    <h3>Belum ada data terbaru</h3>
                    <p>Grafik akan muncul setelah telemetry diterima.</p>
                </div>

                <div class="sv-chart-container {{ collect($chart['labels'] ?? [])->isEmpty() ? 'is-hidden' : '' }}" data-dashboard-chart-container>
                    <canvas id="dashboardEnergyChart" aria-label="Grafik daya SmartVolt"></canvas>
                </div>
            </div>
        </article>

        <article class="sv-card sv-pro-control-card">
            <header class="sv-card-header">
                <div>
                    <h2>Kontrol Cepat</h2>
                    <p>Perangkat yang paling sering digunakan.</p>
                </div>
                <a href="{{ route('rooms') }}" class="sv-text-button">Semua ruangan</a>
            </header>

            <div class="sv-card-body">
                @if($dashboardRooms->isEmpty())
                    <div class="sv-empty-state sv-empty-state-compact">
                        <span class="sv-pro-icon-tile sv-pro-icon-tile--violet"><x-icon name="rooms" :size="22" /></span>
                        <h3>Belum ada ruangan</h3>
                        <a href="{{ route('settings', ['tab' => 'technician']) }}#technician" class="sv-button sv-button-secondary sv-button-sm">Tambah ruangan</a>
                    </div>
                @else
                    <div class="sv-room-control-list">
                        @foreach($dashboardRooms->take(3) as $room)
                            <details class="sv-room-control" data-room-id="{{ $room['id'] }}" {{ $loop->first ? 'open' : '' }}>
                                <summary>
                                    <span class="sv-room-summary-main">
                                        <span class="sv-pro-icon-tile sv-pro-icon-tile--violet sv-pro-icon-tile--sm"><x-icon name="rooms" :size="17" /></span>
                                        <span class="sv-room-summary-copy">
                                            <strong>{{ $room['name'] }}</strong>
                                            <span><span data-room-active-count="{{ $room['id'] }}">{{ $room['active_devices'] }}</span> dari {{ $room['total_devices'] }} aktif</span>
                                        </span>
                                    </span>
                                    <span class="sv-room-power">{{ number_format((float) ($room['current_power'] ?? 0), 1, ',', '.') }} W</span>
                                    <span class="sv-room-chevron"><x-icon name="chevron-right" :size="16" /></span>
                                </summary>

                                <div class="sv-room-devices">
                                    @forelse(collect($room['devices'] ?? [])->take(4) as $device)
                                        @php
                                            $deviceOn = (bool) ($device['is_on'] ?? false);
                                            $espOnline = (bool) ($device['esp_online'] ?? false);
                                        @endphp
                                        <div class="sv-device-row" data-device-row="{{ $device['id'] }}">
                                            <div class="sv-device-main">
                                                <span class="sv-pro-device-icon {{ $deviceOn ? 'is-on' : '' }}"><x-icon name="lightbulb" :size="17" /></span>
                                                <div class="sv-device-copy">
                                                    <strong>{{ $device['name'] }}</strong>
                                                    <span>{{ $espOnline ? 'Siap dikontrol' : 'Perangkat offline' }}</span>
                                                </div>
                                            </div>

                                            <button
                                                type="button"
                                                class="sv-device-switch {{ $deviceOn ? 'is-on' : '' }} {{ ! $espOnline ? 'is-offline' : '' }}"
                                                data-device-toggle
                                                data-device-id="{{ $device['id'] }}"
                                                data-room-id="{{ $room['id'] }}"
                                                data-url="{{ route('devices.toggle', $device['id']) }}"
                                                data-current-state="{{ $deviceOn ? 'on' : 'off' }}"
                                                aria-pressed="{{ $deviceOn ? 'true' : 'false' }}"
                                                {{ ! $espOnline ? 'disabled' : '' }}
                                            >
                                                <span class="sv-switch-track"><span></span></span>
                                                <span data-switch-label>{{ $espOnline ? ($deviceOn ? 'Nyala' : 'Mati') : 'Offline' }}</span>
                                            </button>
                                        </div>
                                    @empty
                                        <div class="sv-empty-state sv-empty-state-compact"><p>Belum ada perangkat.</p></div>
                                    @endforelse
                                </div>
                            </details>
                        @endforeach
                    </div>
                @endif
            </div>
        </article>
    </section>

    <section class="sv-card sv-pro-activity-card">
        <header class="sv-card-header">
            <div>
                <h2>Aktivitas Terbaru</h2>
                <p>Lima pembacaan terakhir.</p>
            </div>
            <a href="{{ route('energy.history') }}" class="sv-text-button">Lihat riwayat</a>
        </header>

        <div class="sv-card-body sv-card-body-table">
            @if($recentReadings->isEmpty())
                <div class="sv-empty-state sv-empty-state-compact">
                    <span class="sv-pro-icon-tile sv-pro-icon-tile--blue"><x-icon name="document" :size="22" /></span>
                    <h3>Belum ada pembacaan</h3>
                </div>
            @else
                <div class="sv-table-wrap">
                    <table class="sv-table sv-table-compact">
                        <thead>
                            <tr>
                                <th>Waktu</th>
                                <th>Ruangan</th>
                                <th class="is-numeric">Daya</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($recentReadings as $reading)
                                <tr>
                                    <td>{{ $reading['observed_at'] ?? '-' }}</td>
                                    <td>{{ $reading['room_name'] ?? '-' }}</td>
                                    <td class="is-numeric">{{ number_format((float) ($reading['power'] ?? 0), 1, ',', '.') }} W</td>
                                    <td><span class="sv-badge sv-badge-success">Tercatat</span></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </section>

    <script type="application/json" id="smartvolt-dashboard-data">{!! json_encode([
        'endpoint' => route('dashboard.data'),
        'refreshInterval' => (int) ($dashboardData['settings']['refresh_interval'] ?? 30),
        'chart' => $chart,
        'stats' => $stats,
        'system' => $system,
    ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) !!}</script>
@endsection

@push('scripts')
    <script src="{{ asset('assets/js/chart.umd.min.js') }}?v=4.5.1" defer></script>
    <script src="{{ asset('assets/js/smartvolt-dashboard.js') }}?v=20260731-v7" defer></script>
@endpush
