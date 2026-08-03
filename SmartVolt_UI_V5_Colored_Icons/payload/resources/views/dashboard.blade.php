@extends('layouts.app')

@section('title', 'Beranda')
@section('page-title', 'Beranda')
@section('page-subtitle', 'Pantau dan kontrol penggunaan listrik rumah secara real-time.')
@section('body-class', 'sv-dashboard-page')

@php
    $stats = $dashboardData['stats'] ?? [];
    $system = $dashboardData['system'] ?? [];
    $chart = $dashboardData['chart'] ?? ['labels' => [], 'power' => [], 'energy' => []];
    $recentReadings = collect($dashboardData['recent_readings'] ?? []);
    $dashboardRooms = collect($dashboardData['rooms'] ?? []);
    $monthly = $dashboardData['monthly_estimation'] ?? [];

    $hour = now()->hour;
    $greeting = match (true) {
        $hour < 11 => 'Selamat pagi',
        $hour < 15 => 'Selamat siang',
        $hour < 18 => 'Selamat sore',
        default => 'Selamat malam',
    };

    $loadStatus = $stats['load_status'] ?? 'normal';
    $loadPercentage = min(100, max(0, (float) ($stats['load_percentage'] ?? 0)));
    $energyComparison = $stats['energy_comparison_percent'] ?? null;

    $statusBadgeClass = ($system['has_fresh_data'] ?? false)
        ? 'sv-badge-success'
        : (($system['has_data'] ?? false) ? 'sv-badge-warning' : 'sv-badge-neutral');

    $systemStatusLabel = ($system['has_fresh_data'] ?? false)
        ? 'Sistem Terhubung'
        : (($system['has_data'] ?? false) ? 'Data Terlambat' : 'Belum Terhubung');

    $introMessage = match ($loadStatus) {
        'danger' => 'Beban listrik tinggi. Matikan perangkat yang tidak diperlukan.',
        'warning' => 'Beban listrik mulai mendekati batas rumah.',
        default => (($system['has_fresh_data'] ?? false)
            ? 'Sistem kelistrikan rumah Anda dalam kondisi normal.'
            : 'Menunggu data terbaru dari perangkat SmartVolt.'),
    };
@endphp

@section('system-status')
    <span class="sv-badge {{ $statusBadgeClass }}" data-dashboard-system-badge>
        <span class="sv-status-dot" aria-hidden="true"></span>
        <span data-dashboard-system-label>{{ $systemStatusLabel }}</span>
    </span>
@endsection

@section('content')
    <section class="sv-dashboard-intro" aria-labelledby="dashboard-greeting">
        <x-feature-icon name="home" tone="blue" :size="23" variant="solid" class="sv-dashboard-intro-icon" />
        <div class="sv-dashboard-intro-copy">
            <h2 id="dashboard-greeting">{{ $greeting }}, {{ auth()->user()->name ?? 'Pengguna' }}</h2>
            <p data-dashboard-intro-message>{{ $introMessage }}</p>
        </div>
        <x-feature-icon
            :name="($system['has_fresh_data'] ?? false) ? 'shield' : 'clock'"
            :tone="($system['has_fresh_data'] ?? false) ? 'green' : 'amber'"
            :size="20"
            variant="soft"
            class="sv-dashboard-intro-state {{ ($system['has_fresh_data'] ?? false) ? 'is-online' : 'is-waiting' }}"
        />
    </section>

    <section class="sv-dashboard-metrics" aria-label="Ringkasan penggunaan listrik">
        <article class="sv-metric-card sv-metric-card-blue">
            <div class="sv-metric-card-top">
                <x-feature-icon name="bolt" tone="blue" :size="22" variant="solid" class="sv-metric-card-icon" />
                <div>
                    <span class="sv-metric-label">Daya Saat Ini</span>
                    <div class="sv-metric-value">
                        <strong data-stat-current-power>{{ number_format((float) ($stats['current_power'] ?? 0), 1, ',', '.') }}</strong>
                        <small>W</small>
                    </div>
                </div>
            </div>
            <div class="sv-metric-foot">
                <span class="sv-metric-status {{ $loadStatus === 'danger' ? 'is-danger' : ($loadStatus === 'warning' ? 'is-warning' : 'is-success') }}" data-load-status-badge>
                    <span data-load-status-label>{{ $loadStatus === 'danger' ? 'Beban tinggi' : ($loadStatus === 'warning' ? 'Mendekati batas' : 'Penggunaan normal') }}</span>
                </span>
                <span><strong data-stat-load-percentage>{{ number_format($loadPercentage, 0, ',', '.') }}</strong>% batas daya</span>
            </div>
            <div class="sv-progress {{ $loadStatus === 'danger' ? 'is-danger' : ($loadStatus === 'warning' ? 'is-warning' : '') }}" data-load-progress style="--sv-progress: {{ $loadPercentage }}%"><span></span></div>
        </article>

        <article class="sv-metric-card sv-metric-card-green">
            <div class="sv-metric-card-top">
                <x-feature-icon name="energy" tone="green" :size="22" variant="solid" class="sv-metric-card-icon" />
                <div>
                    <span class="sv-metric-label">Energi Hari Ini</span>
                    <div class="sv-metric-value">
                        <strong data-stat-energy-today>{{ number_format((float) ($stats['total_energy_today'] ?? 0), 3, ',', '.') }}</strong>
                        <small>kWh</small>
                    </div>
                </div>
            </div>
            <p class="sv-metric-foot-text" data-energy-comparison>
                @if(is_numeric($energyComparison))
                    {{ $energyComparison > 0 ? 'Lebih tinggi' : ($energyComparison < 0 ? 'Lebih hemat' : 'Sama') }}
                    {{ number_format(abs((float) $energyComparison), 1, ',', '.') }}% dari kemarin
                @else
                    Perbandingan belum tersedia
                @endif
            </p>
        </article>

        <article class="sv-metric-card sv-metric-card-orange">
            <div class="sv-metric-card-top">
                <x-feature-icon name="money" tone="amber" :size="22" variant="solid" class="sv-metric-card-icon" />
                <div>
                    <span class="sv-metric-label">Estimasi Tagihan Bulan Ini</span>
                    <div class="sv-metric-value sv-metric-money">
                        <small>Rp</small>
                        <strong data-stat-monthly-cost>{{ number_format((float) ($stats['monthly_estimated_cost'] ?? 0), 0, ',', '.') }}</strong>
                    </div>
                </div>
            </div>
            <p class="sv-metric-foot-text">
                <span data-stat-monthly-energy>{{ number_format((float) ($stats['monthly_energy_usage'] ?? 0), 3, ',', '.') }}</span> kWh · {{ (int) ($stats['active_meters'] ?? 0) }} meter aktif
            </p>
        </article>

        <article class="sv-metric-card sv-metric-card-cyan">
            <div class="sv-metric-card-top">
                <x-feature-icon name="plug" tone="cyan" :size="22" variant="solid" class="sv-metric-card-icon" />
                <div>
                    <span class="sv-metric-label">Perangkat Aktif</span>
                    <div class="sv-metric-value">
                        <strong data-stat-active-devices>{{ (int) ($stats['active_devices'] ?? 0) }}</strong>
                        <small>dari <span data-stat-total-devices>{{ (int) ($stats['total_devices'] ?? 0) }}</span></small>
                    </div>
                </div>
            </div>
            <p class="sv-metric-foot-text">
                <span data-stat-online-active-devices>{{ (int) ($stats['online_active_devices'] ?? 0) }}</span> perangkat aktif pada ESP32 online
            </p>
        </article>
    </section>

    <section class="sv-system-strip" aria-label="Status sistem SmartVolt">
        <article class="sv-system-item {{ ($system['esp_online'] ?? false) ? 'is-success' : 'is-warning' }}" data-system-esp>
            <x-feature-icon name="sensor" :tone="($system['esp_online'] ?? false) ? 'green' : 'amber'" :size="18" variant="soft" class="sv-system-icon" />
            <div class="sv-system-copy">
                <strong data-system-esp-label>{{ ($system['esp_online'] ?? false) ? (($system['online_esp_count'] ?? 0) . ' ESP32 Terhubung') : 'ESP32 Belum Terhubung' }}</strong>
                <span>Mikrokontroler</span>
            </div>
        </article>

        <article class="sv-system-item {{ ($system['has_fresh_data'] ?? false) ? 'is-success' : 'is-warning' }}" data-system-pzem>
            <x-feature-icon name="activity" :tone="($system['has_fresh_data'] ?? false) ? 'green' : 'amber'" :size="18" variant="soft" class="sv-system-icon" />
            <div class="sv-system-copy">
                <strong>Sensor PZEM <span data-system-pzem-label>{{ $system['pzem_status'] ?? 'Belum tersedia' }}</span></strong>
                <span>Pembacaan listrik</span>
            </div>
        </article>

        <article class="sv-system-item {{ ($system['esp_online'] ?? false) ? 'is-success' : 'is-warning' }}" data-system-control>
            <x-feature-icon name="mqtt" :tone="($system['esp_online'] ?? false) ? 'cyan' : 'amber'" :size="18" variant="soft" class="sv-system-icon" />
            <div class="sv-system-copy">
                <strong>MQTT <span data-system-control-label>{{ $system['control_channel_status'] ?? 'Menunggu perangkat' }}</span></strong>
                <span>Kontrol perangkat</span>
            </div>
        </article>

        <article class="sv-system-item {{ ($system['has_data'] ?? false) ? 'is-success' : 'is-warning' }}" data-system-latest>
            <x-feature-icon name="clock" :tone="($system['has_data'] ?? false) ? 'blue' : 'amber'" :size="18" variant="soft" class="sv-system-icon" />
            <div class="sv-system-copy">
                <strong data-system-latest-label>{{ $system['latest_received_human'] ?? 'Belum ada data' }}</strong>
                <span>Data terakhir</span>
            </div>
        </article>
    </section>

    <section class="sv-dashboard-main-grid">
        <article class="sv-card sv-chart-card">
            <header class="sv-card-header">
                <div>
                    <h2>Penggunaan Daya</h2>
                    <p>Grafik pembacaan listrik hari ini.</p>
                </div>
                <div class="sv-chart-tabs" role="tablist" aria-label="Jenis grafik">
                    <button type="button" class="sv-chart-tab is-active" data-dashboard-chart-mode="power" aria-selected="true">Daya</button>
                    <button type="button" class="sv-chart-tab" data-dashboard-chart-mode="energy" aria-selected="false">Energi</button>
                </div>
            </header>

            <div class="sv-card-body">
                <div class="sv-chart-summary">
                    <div><span>Saat ini</span><strong><span data-chart-current>{{ number_format((float) ($stats['current_power'] ?? 0), 1, ',', '.') }}</span> <small data-chart-unit>W</small></strong></div>
                    <div><span>Rata-rata</span><strong><span data-chart-average>0</span> <small data-chart-average-unit>W</small></strong></div>
                    <div><span>Tertinggi</span><strong><span data-chart-maximum>0</span> <small data-chart-maximum-unit>W</small></strong></div>
                </div>

                <div class="sv-empty-state {{ collect($chart['labels'] ?? [])->isNotEmpty() ? 'is-hidden' : '' }}" data-dashboard-chart-empty>
                    <x-feature-icon name="chart" tone="green" :size="24" variant="soft" />
                    <h3>Belum ada data grafik</h3>
                    <p>Grafik muncul setelah ESP32 mengirim data PZEM.</p>
                </div>

                <div class="sv-chart-container {{ collect($chart['labels'] ?? [])->isEmpty() ? 'is-hidden' : '' }}" data-dashboard-chart-container>
                    <canvas id="dashboardEnergyChart" aria-label="Grafik penggunaan listrik SmartVolt"></canvas>
                </div>
            </div>
        </article>

        <article class="sv-card sv-control-card">
            <header class="sv-card-header">
                <div>
                    <h2>Kontrol Ruangan</h2>
                    <p>Nyalakan atau matikan perangkat.</p>
                </div>
                <a href="{{ route('rooms') }}" class="sv-text-button">Lihat semua</a>
            </header>

            <div class="sv-card-body">
                @if($dashboardRooms->isEmpty())
                    <div class="sv-empty-state">
                        <x-feature-icon name="rooms" tone="violet" :size="24" variant="soft" />
                        <h3>Belum ada ruangan</h3>
                        <p>Tambahkan ruangan dari menu Pengaturan.</p>
                    </div>
                @else
                    <div class="sv-room-control-list">
                        @foreach($dashboardRooms->take(4) as $room)
                            <details class="sv-room-control" data-room-id="{{ $room['id'] }}" {{ $loop->first ? 'open' : '' }}>
                                <summary>
                                    <span class="sv-room-summary-main">
                                        <x-feature-icon name="rooms" tone="violet" :size="17" variant="soft" class="sv-room-icon" />
                                        <span class="sv-room-summary-copy">
                                            <strong>{{ $room['name'] }}</strong>
                                            <span><span data-room-active-count="{{ $room['id'] }}">{{ $room['active_devices'] }}</span> aktif dari {{ $room['total_devices'] }}</span>
                                        </span>
                                    </span>
                                    <span class="sv-room-power">{{ number_format((float) ($room['current_power'] ?? 0), 1, ',', '.') }} W</span>
                                    <span class="sv-room-chevron"><x-icon name="chevron-right" :size="16" /></span>
                                </summary>

                                <div class="sv-room-devices">
                                    @forelse(collect($room['devices'] ?? []) as $device)
                                        @php
                                            $deviceOn = (bool) ($device['is_on'] ?? false);
                                            $espOnline = (bool) ($device['esp_online'] ?? false);
                                        @endphp
                                        <div class="sv-device-row" data-device-row="{{ $device['id'] }}">
                                            <div class="sv-device-main">
                                                <x-feature-icon name="lightbulb" tone="amber" :size="16" variant="soft" class="sv-device-icon" />
                                                <div class="sv-device-copy">
                                                    <strong>{{ $device['name'] }}</strong>
                                                    <span>Relay {{ $device['relay_code'] ?: '-' }} · ESP {{ $device['esp_unit_id'] ?: '-' }}</span>
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

    <section class="sv-dashboard-bottom-grid">
        <article class="sv-card">
            <header class="sv-card-header">
                <div>
                    <h2>Riwayat Pemakaian Terakhir</h2>
                    <p>Pembacaan terakhir dari setiap meter.</p>
                </div>
                <a href="{{ route('energy.history') }}" class="sv-text-button">Lihat riwayat</a>
            </header>

            <div class="sv-card-body sv-card-body-table">
                @if($recentReadings->isEmpty())
                    <div class="sv-empty-state">
                        <x-feature-icon name="document" tone="blue" :size="24" variant="soft" />
                        <h3>Belum ada pembacaan</h3>
                        <p>Data akan muncul setelah telemetry diterima.</p>
                    </div>
                @else
                    <div class="sv-table-wrap">
                        <table class="sv-table">
                            <thead>
                                <tr>
                                    <th>Ruangan</th>
                                    <th>Meter</th>
                                    <th>Waktu</th>
                                    <th class="is-numeric">Tegangan</th>
                                    <th class="is-numeric">Arus</th>
                                    <th class="is-numeric">Daya</th>
                                    <th class="is-numeric">Energi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recentReadings as $reading)
                                    <tr>
                                        <td><span class="sv-table-room"><x-feature-icon name="home" tone="violet" :size="14" variant="flat" />{{ $reading['room_name'] ?? '-' }}</span></td>
                                        <td>{{ $reading['meter_name'] ?? '-' }}</td>
                                        <td>{{ $reading['observed_at'] ?? '-' }}</td>
                                        <td class="is-numeric">{{ number_format((float) ($reading['voltage'] ?? 0), 1, ',', '.') }} V</td>
                                        <td class="is-numeric">{{ number_format((float) ($reading['current'] ?? 0), 3, ',', '.') }} A</td>
                                        <td class="is-numeric">{{ number_format((float) ($reading['power'] ?? 0), 1, ',', '.') }} W</td>
                                        <td class="is-numeric">{{ number_format((float) ($reading['energy'] ?? 0), 4, ',', '.') }} kWh</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </article>

        <article class="sv-card sv-billing-card">
            <header class="sv-card-header">
                <div>
                    <h2>Estimasi Pembayaran Listrik</h2>
                    <p>Berdasarkan tarif yang tersimpan.</p>
                </div>
            </header>

            <div class="sv-card-body">
                <div class="sv-billing-highlight">
                    <span>Bulan Ini</span>
                    <strong>Rp{{ number_format((float) ($monthly['estimated_cost'] ?? 0), 0, ',', '.') }}</strong>
                    <small>{{ number_format((float) ($monthly['usage_kwh'] ?? 0), 4, ',', '.') }} kWh</small>
                </div>

                <dl class="sv-billing-summary">
                    <div><dt>Periode</dt><dd>{{ $monthly['period'] ?? '-' }}</dd></div>
                    <div><dt>Tarif</dt><dd>Rp{{ number_format((float) ($monthly['tariff'] ?? 0), 0, ',', '.') }}/kWh</dd></div>
                    <div><dt>Meter aktif</dt><dd>{{ (int) ($monthly['meter_count'] ?? 0) }}</dd></div>
                </dl>

                <a href="{{ route('energy.history') }}" class="sv-button sv-button-secondary sv-button-full">
                    Lihat rincian pemakaian
                    <x-icon name="chevron-right" :size="17" />
                </a>
            </div>
        </article>
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
    <script src="{{ asset('assets/js/smartvolt-dashboard.js') }}?v=20260730-v5" defer></script>
@endpush
