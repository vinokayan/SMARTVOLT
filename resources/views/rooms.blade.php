@extends('layouts.app')

@section('title', 'Ruangan & Perangkat')
@section('page-title', 'Ruangan & Perangkat')
@section('page-subtitle', 'Kontrol perangkat berdasarkan ruangan.')
@section('body-class', 'sv-rooms-page sv-rooms-streamlined')

@php
    $roomCollection = collect($roomPageData ?? []);
    $stats = $roomStats ?? [];
    $system = $roomSystem ?? [];

    $systemConnected = (bool) ($system['connected'] ?? false);
    $deviceStatusAvailable = (bool) ($stats['device_status_available'] ?? false);
    $activeDevices = $stats['active_devices'] ?? null;
    $activeDevicesDisplay = $deviceStatusAvailable
        && is_numeric($activeDevices)
        && (int) $activeDevices > 0
            ? (int) $activeDevices
            : '—';

    $roomTones = ['violet', 'blue', 'green', 'amber', 'cyan', 'rose'];
@endphp

@push('styles')
    <style>

        .sv-rooms-page .sv-device-switch.sv-device-power-button {
            width: auto;
            min-width: 104px;
            min-height: 40px;
            display: inline-flex;
            align-items: center;
            justify-content: flex-end;
            gap: 8px;
            padding: 5px 7px 5px 10px;
            white-space: nowrap;
        }

        .sv-rooms-page .sv-device-switch.sv-device-power-button [data-switch-label] {
            min-width: 34px;
            font-weight: 800;
            text-align: right;
        }

        /* Saat SmartVolt belum terhubung, switch ditampilkan Mati, abu-abu, dan tidak dapat ditekan. */
        .sv-rooms-page .sv-device-switch.is-offline {
            color: #667085;
            background: #f2f4f7 !important;
            border-color: #e4e7ec !important;
            cursor: not-allowed;
            opacity: 1;
        }

        .sv-rooms-page .sv-device-switch.is-offline .sv-switch-track {
            background: #d0d5dd;
        }

        .sv-rooms-page .sv-device-switch.is-offline .sv-switch-track span {
            transform: translateX(0);
        }
    </style>
@endpush

@section('system-status')
    <span
        class="sv-badge {{ $systemConnected ? 'sv-badge-success' : 'sv-badge-neutral' }}"
        data-rooms-system-badge
    >
        <span class="sv-status-dot" aria-hidden="true"></span>
        <span data-rooms-system-label>
            {{ $systemConnected ? 'Terhubung' : 'Belum terhubung' }}
        </span>
    </span>
@endsection

@section('content')
    <section
        class="sv-room-summary-grid sv-room-summary-grid-three"
        aria-label="Ringkasan ruangan dan perangkat"
    >
        <article class="sv-summary-card sv-summary-card--violet">
            <x-feature-icon name="rooms" tone="violet" :size="19" variant="soft" class="sv-summary-icon" />
            <div class="sv-summary-copy">
                <span>Ruangan</span>
                <strong data-total-rooms>{{ (int) ($stats['total_rooms'] ?? 0) }}</strong>
            </div>
        </article>

        <article class="sv-summary-card sv-summary-card--blue">
            <x-feature-icon name="plug" tone="blue" :size="19" variant="soft" class="sv-summary-icon" />
            <div class="sv-summary-copy">
                <span>Perangkat Terdaftar</span>
                <strong data-total-devices>{{ (int) ($stats['total_devices'] ?? 0) }}</strong>
            </div>
        </article>

        <article class="sv-summary-card sv-summary-card--green">
            <x-feature-icon name="power" tone="green" :size="19" variant="soft" class="sv-summary-icon" />
            <div class="sv-summary-copy">
                <span>Perangkat Aktif</span>
                <strong data-active-devices>{{ $activeDevicesDisplay }}</strong>
            </div>
        </article>
    </section>

    @if($roomCollection->isEmpty())
        <section class="sv-card">
            <div class="sv-card-body">
                <div class="sv-empty-state sv-empty-state-compact">
                    <x-feature-icon name="rooms" tone="violet" :size="27" variant="soft" />
                    <h3>Belum ada ruangan</h3>
                    <a href="{{ route('technician.index') }}" class="sv-button sv-button-primary">Tambah ruangan</a>
                </div>
            </div>
        </section>
    @else
        <section class="sv-room-grid sv-room-grid-three" aria-label="Daftar ruangan">
            @foreach($roomCollection as $room)
                @php
                    $devices = collect($room['devices'] ?? []);
                    $roomStatusAvailable = (bool) ($room['status_available'] ?? false);
                    $roomActiveDevices = $room['active_devices'] ?? null;
                    $roomActiveDisplay = $roomStatusAvailable
                        && is_numeric($roomActiveDevices)
                        && (int) $roomActiveDevices > 0
                            ? (int) $roomActiveDevices
                            : '—';

                    $roomPower = $room['current_power'] ?? null;
                    $connectionLabel = $room['connection_label'] ?? 'Belum terhubung';
                    $onlineDevices = (int) ($room['online_devices'] ?? 0);
                    $totalDevices = (int) ($room['total_devices'] ?? 0);

                    $connectionClass = $totalDevices === 0
                        ? 'sv-badge-neutral'
                        : ($onlineDevices === $totalDevices
                            ? 'sv-badge-success'
                            : ($onlineDevices > 0
                                ? 'sv-badge-warning'
                                : 'sv-badge-neutral'));

                    $roomNameLower = strtolower((string) ($room['name'] ?? ''));
                    $roomIcon = str_contains($roomNameLower, 'dapur') ? 'kitchen'
                        : (str_contains($roomNameLower, 'kamar') ? 'bed'
                        : (str_contains($roomNameLower, 'tamu') ? 'sofa'
                        : (str_contains($roomNameLower, 'garasi') ? 'garage' : 'rooms')));
                    $roomTone = $roomTones[$loop->index % count($roomTones)];
                @endphp

                <article class="sv-room-card sv-room-card--{{ $roomTone }}" data-room-card="{{ $room['id'] }}">
                    <header class="sv-room-card-head">
                        <div class="sv-room-card-title">
                            <x-feature-icon :name="$roomIcon" :tone="$roomTone" :size="18" variant="soft" class="sv-room-icon" />
                            <div>
                                <h3>{{ ucwords(strtolower((string) ($room['name'] ?? 'Tanpa nama'))) }}</h3>
                                <p>
                                    <span data-room-total-devices="{{ $room['id'] }}">{{ $totalDevices }}</span>
                                    perangkat terdaftar ·
                                    <span data-room-power="{{ $room['id'] }}">
                                        {{ is_numeric($roomPower)
                                            ? number_format((float) $roomPower, 1, ',', '.') . ' W'
                                            : '— W' }}
                                    </span>
                                </p>
                            </div>
                        </div>

                        <span
                            class="sv-badge {{ $connectionClass }}"
                            data-room-connection-badge="{{ $room['id'] }}"
                        >
                            <span class="sv-status-dot" aria-hidden="true"></span>
                            <span data-room-connection-label="{{ $room['id'] }}">{{ $connectionLabel }}</span>
                        </span>
                    </header>

                    <div class="sv-room-card-body">
                        @forelse($devices as $device)
                            @php
                                $espOnline = (bool) ($device['esp_online'] ?? false);
                                $statusAvailable = (bool) ($device['status_available'] ?? false);
                                $commandPending = (bool) ($device['command_pending'] ?? false);
                                $deviceOn = $statusAvailable && ($device['is_on'] ?? null) === true;
                                $controlAvailable = $espOnline && $statusAvailable;

                                $deviceLabel = strtolower(($device['type'] ?? '') . ' ' . ($device['name'] ?? ''));
                                $deviceIcon = str_contains($deviceLabel, 'kipas') || str_contains($deviceLabel, 'fan')
                                    ? 'fan'
                                    : (str_contains($deviceLabel, 'lampu') || str_contains($deviceLabel, 'light')
                                        ? 'lightbulb'
                                        : 'plug');
                                $deviceTone = $deviceOn
                                    ? 'green'
                                    : ($deviceIcon === 'fan' ? 'cyan' : ($deviceIcon === 'lightbulb' ? 'amber' : 'blue'));

                                $statusMessage = $device['status_message']
                                    ?? (!$espOnline
                                        ? 'Belum terhubung ke SmartVolt'
                                        : (!$statusAvailable
                                            ? 'Menunggu pembaruan status'
                                            : ($commandPending
                                                ? 'Perintah sedang diproses'
                                                : ($deviceOn
                                                    ? 'Perangkat sedang menyala'
                                                    : 'Perangkat sedang mati'))));

                                $switchLabel = !$controlAvailable
                                    ? 'Mati'
                                    : ($commandPending
                                        ? 'Memproses'
                                        : ($deviceOn ? 'Nyala' : 'Mati'));

                                $assistiveText = !$espOnline
                                    ? ($device['name'] . ' belum terhubung ke SmartVolt')
                                    : (!$statusAvailable
                                        ? ('Status terbaru ' . $device['name'] . ' belum tersedia')
                                        : ($commandPending
                                            ? ('Perintah ' . $device['name'] . ' sedang diproses')
                                            : (($deviceOn ? 'Matikan ' : 'Nyalakan ') . $device['name'])));
                            @endphp

                            <div class="sv-device-row" data-device-row="{{ $device['id'] }}">
                                <div class="sv-device-main">
                                    <x-feature-icon :name="$deviceIcon" :tone="$deviceTone" :size="16" variant="soft" class="sv-device-icon" />
                                    <div class="sv-device-copy">
                                        <strong>{{ $device['name'] }}</strong>
                                        <span data-device-status="{{ $device['id'] }}">{{ $statusMessage }}</span>
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
                                    <span data-switch-label>{{ $switchLabel }}</span>
                                    <span class="sv-switch-track" aria-hidden="true"><span></span></span>
                                </button>
                            </div>
                        @empty
                            <div class="sv-empty-state sv-empty-state-compact">
                                <x-feature-icon name="plug" tone="cyan" :size="23" variant="soft" />
                                <h3>Belum ada perangkat</h3>
                                <a href="{{ route('technician.index') }}#room-{{ $room['id'] }}" class="sv-text-button">Tambah perangkat</a>
                            </div>
                        @endforelse
                    </div>

                    <footer class="sv-room-card-foot">
                        <span>
                            Perangkat aktif:
                            <strong data-room-active-count="{{ $room['id'] }}">{{ $roomActiveDisplay }}</strong>
                            · {{ $totalDevices }} perangkat terdaftar
                        </span>
                    </footer>
                </article>
            @endforeach
        </section>
    @endif

    <script type="application/json" id="smartvolt-rooms-data">{!! json_encode([
        'endpoint' => route('rooms.data'),
        'refreshInterval' => (int) ($refreshInterval ?? 30),
        'stats' => $stats,
        'system' => $system,
        'rooms' => $roomPageData ?? [],
    ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) !!}</script>
@endsection

@push('scripts')
    <script src="{{ asset('assets/js/smartvolt-rooms.js') }}?v=20260803-v3" defer></script>
@endpush
