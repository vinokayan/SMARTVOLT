@extends('layouts.app')

@section('title', 'Beranda')
@section('page-title', 'Beranda')
@section('page-subtitle', 'Ringkasan kondisi listrik rumah saat ini.')
@section('body-class', 'sv-dashboard-page sv-dashboard-streamlined')

@php
    $stats = $dashboardData['stats'] ?? [];
    $system = $dashboardData['system'] ?? [];
    $chart = $dashboardData['chart'] ?? [
        'title' => 'Riwayat Daya Terakhir',
        'subtitle' => 'Menampilkan perubahan daya dalam 24 jam terakhir.',
        'labels' => [],
        'power' => [],
        'energy' => [],
    ];

    $dashboardRooms = collect($dashboardData['rooms'] ?? []);
    $systemConnected = (bool) ($system['connected'] ?? false);
    $currentPowerAvailable = (bool) ($stats['current_power_available'] ?? false);
    $energyTodayAvailable = (bool) ($stats['energy_today_available'] ?? false);
    $deviceStatusAvailable = (bool) ($stats['device_status_available'] ?? false);
    $activeDevices = $stats['active_devices'] ?? null;
    $activeDevicesDisplay = $deviceStatusAvailable
        && is_numeric($activeDevices)
        && (int) $activeDevices > 0
            ? (int) $activeDevices
            : '—';

    $loadStatus = $stats['load_status'] ?? 'unknown';
    $loadPercentage = is_numeric($stats['load_percentage'] ?? null)
        ? min(100, max(0, (float) $stats['load_percentage']))
        : 0;
    $energyComparison = $stats['energy_comparison_percent'] ?? null;
@endphp

@section('system-status')
    <span
        class="sv-badge {{ $systemConnected ? 'sv-badge-success' : 'sv-badge-neutral' }}"
        data-dashboard-system-badge
    >
        <span class="sv-status-dot" aria-hidden="true"></span>
        <span data-dashboard-system-label>
            {{ $systemConnected ? 'Terhubung' : 'Belum terhubung' }}
        </span>
    </span>
@endsection

@section('content')
    <section class="sv-dashboard-metrics" aria-label="Ringkasan penggunaan listrik">
        <article class="sv-metric-card sv-metric-card-blue">
            <div class="sv-metric-card-top">
                <x-feature-icon name="bolt" tone="blue" :size="22" variant="solid" class="sv-metric-card-icon" />
                <div>
                    <span class="sv-metric-label">Daya Saat Ini</span>
                    <div class="sv-metric-value">
                        <strong data-stat-current-power>
                            {{ $currentPowerAvailable
                                ? number_format((float) ($stats['current_power'] ?? 0), 1, ',', '.')
                                : '—' }}
                        </strong>
                        <small>W</small>
                    </div>
                </div>
            </div>

            <div class="sv-metric-foot">
                <span
                    class="sv-metric-status {{ $loadStatus === 'danger' ? 'is-danger' : ($loadStatus === 'warning' ? 'is-warning' : ($loadStatus === 'normal' ? 'is-success' : '')) }}"
                    data-load-status-badge
                >
                    <span data-load-status-label>
                        @if(! $currentPowerAvailable)
                            Data terbaru belum tersedia
                        @elseif($loadStatus === 'danger')
                            Pemakaian sangat tinggi
                        @elseif($loadStatus === 'warning')
                            Mendekati batas
                        @else
                            Pemakaian normal
                        @endif
                    </span>
                </span>

                <span data-load-percentage-wrap {{ ! $currentPowerAvailable ? 'hidden' : '' }}>
                    <strong data-stat-load-percentage>{{ number_format($loadPercentage, 0, ',', '.') }}</strong>% batas
                </span>
            </div>

            <div
                class="sv-progress {{ $loadStatus === 'danger' ? 'is-danger' : ($loadStatus === 'warning' ? 'is-warning' : '') }}"
                data-load-progress
                style="--sv-progress: {{ $loadPercentage }}%"
                {{ ! $currentPowerAvailable ? 'hidden' : '' }}
            ><span></span></div>
        </article>

        <article class="sv-metric-card sv-metric-card-green">
            <div class="sv-metric-card-top">
                <x-feature-icon name="energy" tone="green" :size="22" variant="solid" class="sv-metric-card-icon" />
                <div>
                    <span class="sv-metric-label">Energi Hari Ini</span>
                    <div class="sv-metric-value">
                        <strong data-stat-energy-today>
                            {{ $energyTodayAvailable
                                ? number_format((float) ($stats['total_energy_today'] ?? 0), 3, ',', '.')
                                : '—' }}
                        </strong>
                        <small>kWh</small>
                    </div>
                </div>
            </div>

            <p class="sv-metric-foot-text" data-energy-comparison>
                @if(! $energyTodayAvailable)
                    Data hari ini belum tersedia
                @elseif(is_numeric($energyComparison))
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
                    <span class="sv-metric-label">Estimasi Bulan Ini</span>
                    <div class="sv-metric-value sv-metric-money">
                        <small>Rp</small>
                        <strong data-stat-monthly-cost>{{ number_format((float) ($stats['monthly_estimated_cost'] ?? 0), 0, ',', '.') }}</strong>
                    </div>
                </div>
            </div>

            <p class="sv-metric-foot-text">
                <span data-stat-monthly-energy>{{ number_format((float) ($stats['monthly_energy_usage'] ?? 0), 3, ',', '.') }}</span> kWh bulan ini
            </p>
        </article>

        <article class="sv-metric-card sv-metric-card-violet">
            <div class="sv-metric-card-top">
                <x-feature-icon name="plug" tone="violet" :size="22" variant="solid" class="sv-metric-card-icon" />
                <div>
                    <span class="sv-metric-label">Perangkat Terdaftar</span>
                    <div class="sv-metric-value">
                        <strong data-stat-total-devices>{{ (int) ($stats['total_devices'] ?? 0) }}</strong>
                        <small>perangkat</small>
                    </div>
                </div>
            </div>
            <p class="sv-metric-foot-text">Perangkat yang tersimpan di SmartVolt</p>
        </article>

        <article class="sv-metric-card sv-metric-card-cyan">
            <div class="sv-metric-card-top">
                <x-feature-icon name="lightbulb" tone="cyan" :size="22" variant="solid" class="sv-metric-card-icon" />
                <div>
                    <span class="sv-metric-label">Perangkat Aktif</span>
                    <div class="sv-metric-value">
                        <strong data-stat-active-devices>{{ $activeDevicesDisplay }}</strong>
                        <small>perangkat</small>
                    </div>
                </div>
            </div>

            <p class="sv-metric-foot-text" data-active-devices-message>
                {{ $systemConnected
                    ? ($deviceStatusAvailable ? 'Sesuai kondisi perangkat saat ini' : 'Menyiapkan informasi perangkat')
                    : 'SmartVolt belum terhubung' }}
            </p>
        </article>
    </section>

    <section class="sv-system-summary-card {{ $systemConnected ? 'is-connected' : 'is-disconnected' }}" aria-label="Status SmartVolt">
        <div class="sv-system-summary-main">
            <x-feature-icon
                :name="$systemConnected ? 'shield' : 'clock'"
                :tone="$systemConnected ? 'green' : 'amber'"
                :size="19"
                variant="soft"
            />
            <div>
                <strong data-system-status-title>{{ $system['status_title'] ?? 'SmartVolt belum terhubung' }}</strong>
                <span data-system-status-message>{{ $system['status_message'] ?? 'Hubungkan SmartVolt untuk melihat kondisi terbaru.' }}</span>
            </div>
        </div>
    </section>

    <section class="sv-dashboard-main-grid">
        <article class="sv-card sv-chart-card sv-dashboard-chart-panel">
            <header class="sv-card-header">
                <div>
                    <h2 data-chart-title>{{ $chart['title'] ?? 'Riwayat Daya Terakhir' }}</h2>
                    <p data-chart-subtitle>{{ $chart['subtitle'] ?? 'Menampilkan perubahan daya dalam 24 jam terakhir.' }}</p>
                </div>
                <div class="sv-card-header-actions">
                    <div class="sv-chart-tabs" role="tablist" aria-label="Jenis grafik">
                        <button type="button" class="sv-chart-tab is-active" data-dashboard-chart-mode="power" aria-selected="true">Daya</button>
                        <button type="button" class="sv-chart-tab" data-dashboard-chart-mode="energy" aria-selected="false">Energi</button>
                    </div>
                    <a href="{{ route('energy.history') }}" class="sv-text-button">Analisis lengkap</a>
                </div>
            </header>

            <div class="sv-card-body">
                <div class="sv-chart-summary sv-chart-summary-compact">
                    <div>
                        <span>Terakhir</span>
                        <strong><span data-chart-current>0</span> <small data-chart-unit>W</small></strong>
                    </div>
                    <div>
                        <span>Rata-rata</span>
                        <strong><span data-chart-average>0</span> <small data-chart-average-unit>W</small></strong>
                    </div>
                    <div>
                        <span>Tertinggi</span>
                        <strong><span data-chart-maximum>0</span> <small data-chart-maximum-unit>W</small></strong>
                    </div>
                </div>

                <div class="sv-empty-state {{ collect($chart['labels'] ?? [])->isNotEmpty() ? 'is-hidden' : '' }}" data-dashboard-chart-empty>
                    <x-feature-icon name="chart" tone="blue" :size="24" variant="soft" />
                    <h3>Belum ada riwayat daya</h3>
                    <p>Grafik akan muncul setelah data pemakaian tersedia.</p>
                </div>

                <div class="sv-chart-container {{ collect($chart['labels'] ?? [])->isEmpty() ? 'is-hidden' : '' }}" data-dashboard-chart-container>
                    <canvas id="dashboardEnergyChart" aria-label="Grafik daya SmartVolt"></canvas>
                </div>
            </div>
        </article>

        <article class="sv-card sv-control-card sv-dashboard-control-panel">
            <header class="sv-card-header">
                <div>
                    <h2>Kontrol Perangkat</h2>
                    <p>{{ $systemConnected ? 'Nyalakan atau matikan perangkat dengan cepat.' : 'Hubungkan SmartVolt untuk menggunakan kontrol perangkat.' }}</p>
                </div>
                <a href="{{ route('rooms') }}" class="sv-text-button">Lihat semua</a>
            </header>

            <div class="sv-card-body">
                @if($dashboardRooms->isEmpty())
                    <div class="sv-empty-state sv-empty-state-compact">
                        <x-feature-icon name="rooms" tone="violet" :size="24" variant="soft" />
                        <h3>Belum ada perangkat</h3>
                        <p>Tambahkan perangkat agar dapat dikendalikan dari Beranda.</p>
                    </div>
                @else
                    <div class="sv-room-control-list">
                        @foreach($dashboardRooms->take(3) as $room)
                            @php
                                $roomStatusAvailable = (bool) ($room['status_available'] ?? false);
                                $roomActive = $room['active_devices'] ?? null;
                                $roomActiveDisplay = $roomStatusAvailable
                                    && is_numeric($roomActive)
                                    && (int) $roomActive > 0
                                        ? (int) $roomActive
                                        : '—';
                                $roomPower = $room['current_power'] ?? null;
                            @endphp

                            <details class="sv-room-control" data-room-id="{{ $room['id'] }}" {{ $loop->first ? 'open' : '' }}>
                                <summary>
                                    <span class="sv-room-summary-main">
                                        <x-feature-icon name="rooms" tone="violet" :size="17" variant="soft" class="sv-room-icon" />
                                        <span class="sv-room-summary-copy">
                                            <strong>{{ $room['name'] }}</strong>
                                            <span>{{ (int) ($room['total_devices'] ?? 0) }} perangkat terdaftar</span>
                                            <small>
                                                Perangkat aktif:
                                                <strong data-room-active-count="{{ $room['id'] }}">{{ $roomActiveDisplay }}</strong>
                                            </small>
                                        </span>
                                    </span>

                                    <span class="sv-room-power" data-room-power="{{ $room['id'] }}">
                                        Daya saat ini: {{ is_numeric($roomPower)
                                            ? number_format((float) $roomPower, 1, ',', '.') . ' W'
                                            : '—' }}
                                    </span>

                                    <span class="sv-room-chevron"><x-icon name="chevron-right" :size="16" /></span>
                                </summary>

                                <div class="sv-room-devices">
                                    @forelse(collect($room['devices'] ?? [])->take(4) as $device)
                                        @php
                                            $espOnline = (bool) ($device['esp_online'] ?? false);
                                            $statusAvailable = (bool) ($device['status_available'] ?? false);
                                            $commandPending = (bool) ($device['command_pending'] ?? false);
                                            $deviceOn = $statusAvailable && ($device['is_on'] ?? null) === true;
                                            $controlAvailable = $espOnline && $statusAvailable;
                                            $deviceStatusText = $device['status_message']
                                                ?? (!$espOnline
                                                    ? 'SmartVolt belum terhubung'
                                                    : (!$statusAvailable
                                                        ? 'Menyiapkan perangkat'
                                                        : ($commandPending
                                                            ? 'Sedang memproses'
                                                            : ($deviceOn ? 'Sedang menyala' : 'Siap digunakan'))));
                                            $switchLabel = $commandPending && $controlAvailable
                                                ? 'Memproses'
                                                : ($deviceOn ? 'Nyala' : 'Mati');
                                            $assistiveText = !$controlAvailable
                                                ? ($device['name'] . ' belum dapat digunakan')
                                                : ($commandPending
                                                    ? ($device['name'] . ' sedang diproses')
                                                    : (($deviceOn ? 'Matikan ' : 'Nyalakan ') . $device['name']));
                                        @endphp

                                        <div class="sv-device-row" data-device-row="{{ $device['id'] }}">
                                            <div class="sv-device-main">
                                                <x-feature-icon
                                                    name="lightbulb"
                                                    :tone="$deviceOn ? 'green' : 'amber'"
                                                    :size="16"
                                                    variant="soft"
                                                    class="sv-device-icon"
                                                />
                                                <div class="sv-device-copy">
                                                    <strong>{{ $device['name'] }}</strong>
                                                    <span data-device-status="{{ $device['id'] }}">{{ $deviceStatusText }}</span>
                                                </div>
                                            </div>

                                            <button
                                                type="button"
                                                class="sv-device-switch sv-device-power-button {{ $deviceOn ? 'is-on' : '' }} {{ ! $controlAvailable ? 'is-offline' : '' }} {{ $commandPending && $controlAvailable ? 'is-loading' : '' }}"
                                                data-device-toggle
                                                data-device-id="{{ $device['id'] }}"
                                                data-room-id="{{ $room['id'] }}"
                                                data-url="{{ route('devices.toggle', $device['id']) }}"
                                                data-current-state="{{ $controlAvailable ? ($deviceOn ? 'on' : 'off') : 'off' }}"
                                                data-status-available="{{ $statusAvailable ? 'true' : 'false' }}"
                                                data-esp-online="{{ $espOnline ? 'true' : 'false' }}"
                                                aria-pressed="{{ $deviceOn ? 'true' : 'false' }}"
                                                aria-label="{{ $assistiveText }}"
                                                title="{{ $assistiveText }}"
                                                {{ ! $controlAvailable || $commandPending ? 'disabled' : '' }}
                                            >
                                                <span class="sv-device-power-label" data-switch-label>{{ $switchLabel }}</span>
                                                <span class="sv-switch-track" aria-hidden="true"><span></span></span>
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

    <section class="sv-card sv-recent-activity-card" aria-labelledby="room-information-title">
        <header class="sv-card-header">
            <div>
                <h2 id="room-information-title">Informasi Ruangan</h2>
                <p>Menampilkan ruangan yang sudah memiliki perangkat.</p>
            </div>
            <a href="{{ route('rooms') }}" class="sv-text-button">Lihat semua</a>
        </header>

        <div class="sv-card-body sv-card-body-table">
            @if($dashboardRooms->isEmpty())
                <div class="sv-empty-state sv-empty-state-compact">
                    <x-feature-icon name="rooms" tone="violet" :size="24" variant="soft" />
                    <h3>Belum ada informasi ruangan</h3>
                    <p>Informasi akan muncul setelah perangkat ditambahkan.</p>
                </div>
            @else
                <div class="sv-table-wrap">
                    <table class="sv-table sv-table-compact" aria-label="Informasi terakhir setiap ruangan">
                        <thead>
                            <tr>
                                <th>Ruangan</th>
                                <th>Perangkat</th>
                                <th>Kondisi</th>
                                <th class="is-numeric">Daya Terakhir</th>
                                <th>Diperbarui</th>
                            </tr>
                        </thead>
                        <tbody data-room-information-body>
                            @foreach($dashboardRooms as $room)
                                @php
                                    $roomId = (int) ($room['id'] ?? 0);
                                    $totalDevices = (int) ($room['total_devices'] ?? 0);
                                    $activeRoomDevices = $room['active_devices'] ?? null;
                                    $statusAvailable = (bool) ($room['status_available'] ?? false);
                                    $conditionLabel = $room['condition_label'] ?? 'Belum terhubung';
                                    $lastPower = $room['last_power'] ?? null;
                                    $updatedHuman = $room['updated_human'] ?? 'Belum ada data';
                                    $conditionClass = ! $statusAvailable
                                        ? 'sv-badge-neutral'
                                        : (is_numeric($activeRoomDevices) && (int) $activeRoomDevices > 0
                                            ? 'sv-badge-success'
                                            : 'sv-badge-neutral');
                                @endphp

                                <tr data-room-information-row="{{ $roomId }}">
                                    <td><strong>{{ $room['name'] ?? 'Tanpa nama' }}</strong></td>
                                    <td><span data-room-summary-total="{{ $roomId }}">{{ $totalDevices }}</span> perangkat</td>
                                    <td>
                                        <span class="sv-badge {{ $conditionClass }}" data-room-summary-condition="{{ $roomId }}">
                                            {{ $conditionLabel }}
                                        </span>
                                    </td>
                                    <td class="is-numeric" data-room-summary-last-power="{{ $roomId }}">
                                        {{ is_numeric($lastPower) ? number_format((float) $lastPower, 1, ',', '.') . ' W' : '—' }}
                                    </td>
                                    <td data-room-summary-updated="{{ $roomId }}">{{ $updatedHuman }}</td>
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
        'rooms' => $dashboardData['rooms'] ?? [],
    ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) !!}</script>
@endsection

@push('scripts')
    <script src="{{ asset('assets/js/chart.umd.min.js') }}?v=4.5.1" defer></script>
    <script src="{{ asset('assets/js/smartvolt-dashboard.js') }}?v=20260803-final" defer></script>
@endpush
