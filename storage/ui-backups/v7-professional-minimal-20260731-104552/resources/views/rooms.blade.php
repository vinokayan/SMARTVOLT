@extends('layouts.app')

@section('title', 'Ruangan & Perangkat')
@section('page-title', 'Ruangan & Perangkat')
@section('page-subtitle', 'Kontrol perangkat berdasarkan ruangan.')
@section('body-class', 'sv-rooms-page sv-rooms-streamlined')

@php
    $roomCollection = collect($rooms ?? []);
    $onlineIds = collect($onlineEspUnitIds ?? [])->map(fn ($value) => (string) $value);
    $allDevices = $roomCollection->pluck('devices')->flatten();
    $isOn = function ($status): bool {
        if (is_bool($status)) return $status;
        if (is_numeric($status)) return (int) $status === 1;
        return in_array(strtolower((string) $status), ['on', 'nyala', 'active', 'aktif', 'true', '1'], true);
    };
    $totalDevices = $allDevices->count();
    $onlineDevices = $allDevices->filter(function ($device) use ($onlineIds) {
        $espId = trim((string) ($device->esp_unit_id ?: $device->esp32_device_id));
        return $espId !== '' && $onlineIds->contains($espId);
    })->count();
    $roomTones = ['violet', 'blue', 'green', 'amber', 'cyan', 'rose'];
@endphp

@section('system-status')
    <span
        class="sv-badge {{ $onlineIds->isNotEmpty() ? 'sv-badge-success' : 'sv-badge-warning' }}"
        title="{{ $onlineIds->isNotEmpty() ? 'Perangkat dapat dikontrol melalui ESP32 yang terhubung.' : 'Tidak ada ESP32 yang mengirim telemetry terbaru.' }}"
    >
        <span class="sv-status-dot" aria-hidden="true"></span>
        {{ $onlineIds->isNotEmpty() ? $onlineIds->count() . ' ESP32 online' : 'Perangkat offline' }}
    </span>
@endsection

@section('content')
    <section class="sv-room-summary-grid sv-room-summary-grid-three" aria-label="Ringkasan ruangan dan perangkat">
        <article class="sv-summary-card sv-summary-card--violet">
            <x-feature-icon name="rooms" tone="violet" :size="19" variant="soft" class="sv-summary-icon" />
            <div class="sv-summary-copy"><span>Ruangan</span><strong>{{ $roomCollection->count() }}</strong></div>
        </article>
        <article class="sv-summary-card sv-summary-card--cyan">
            <x-feature-icon name="plug" tone="cyan" :size="19" variant="soft" class="sv-summary-icon" />
            <div class="sv-summary-copy"><span>Total Perangkat</span><strong>{{ $totalDevices }}</strong></div>
        </article>
        <article class="sv-summary-card sv-summary-card--green">
            <x-feature-icon name="wifi" tone="green" :size="19" variant="soft" class="sv-summary-icon" />
            <div class="sv-summary-copy"><span>Perangkat Online</span><strong>{{ $onlineDevices }}</strong></div>
        </article>
    </section>

    @if($roomCollection->isEmpty())
        <section class="sv-card">
            <div class="sv-card-body">
                <div class="sv-empty-state sv-empty-state-compact">
                    <x-feature-icon name="rooms" tone="violet" :size="27" variant="soft" />
                    <h3>Belum ada ruangan</h3>
                    <a href="{{ route('settings', ['tab' => 'technician']) }}#technician" class="sv-button sv-button-primary">Tambah ruangan</a>
                </div>
            </div>
        </section>
    @else
        <section class="sv-room-grid sv-room-grid-three" aria-label="Daftar ruangan">
            @foreach($roomCollection as $room)
                @php
                    $devices = collect($room->devices ?? []);
                    $roomActiveCount = $devices->filter(fn ($device) => $isOn($device->status ?? null))->count();
                    $roomOnlineDevices = $devices->filter(function ($device) use ($onlineIds) {
                        $espId = trim((string) ($device->esp_unit_id ?: $device->esp32_device_id));
                        return $espId !== '' && $onlineIds->contains($espId);
                    });
                    $roomOnlineCount = $roomOnlineDevices->count();
                    $roomOnlineActiveCount = $roomOnlineDevices
                        ->filter(fn ($device) => $isOn($device->status ?? null))
                        ->count();
                    $power = (float) (($roomPower ?? collect())[$room->id] ?? 0);
                    $roomNameLower = strtolower((string) $room->name);
                    $roomIcon = str_contains($roomNameLower, 'dapur') ? 'kitchen'
                        : (str_contains($roomNameLower, 'kamar') ? 'bed'
                        : (str_contains($roomNameLower, 'tamu') ? 'sofa'
                        : (str_contains($roomNameLower, 'garasi') ? 'garage' : 'rooms')));
                    $roomTone = $roomTones[$loop->index % count($roomTones)];
                @endphp

                <article class="sv-room-card sv-room-card--{{ $roomTone }}" data-room-card="{{ $room->id }}">
                    <header class="sv-room-card-head">
                        <div class="sv-room-card-title">
                            <x-feature-icon :name="$roomIcon" :tone="$roomTone" :size="18" variant="soft" class="sv-room-icon" />
                            <div>
                                <h3>{{ ucwords(strtolower((string) $room->name)) }}</h3>
                                <p>{{ $devices->count() }} perangkat · {{ number_format($power, 1, ',', '.') }} W</p>
                            </div>
                        </div>
                        @if($devices->isEmpty())
                            <span class="sv-badge sv-badge-neutral">Belum ada perangkat</span>
                        @else
                            <span class="sv-badge {{ $roomOnlineCount > 0 ? 'sv-badge-success' : 'sv-badge-warning' }}">
                                <span class="sv-status-dot" aria-hidden="true"></span>
                                {{ $roomOnlineCount > 0 ? 'Online' : 'Offline' }}
                            </span>
                        @endif
                    </header>

                    <div class="sv-room-card-body">
                        @forelse($devices as $device)
                            @php
                                $deviceOn = $isOn($device->status ?? null);
                                $espId = trim((string) ($device->esp_unit_id ?: $device->esp32_device_id));
                                $espOnline = $espId !== '' && $onlineIds->contains($espId);
                                $deviceLabel = strtolower(($device->type ?? '') . ' ' . ($device->name ?? ''));
                                $deviceIcon = str_contains($deviceLabel, 'kipas') || str_contains($deviceLabel, 'fan') ? 'fan'
                                    : (str_contains($deviceLabel, 'lampu') || str_contains($deviceLabel, 'light') ? 'lightbulb' : 'plug');
                                $deviceTone = ! $espOnline
                                    ? 'amber'
                                    : ($deviceOn ? 'green' : ($deviceIcon === 'fan' ? 'cyan' : ($deviceIcon === 'lightbulb' ? 'amber' : 'blue')));
                            @endphp
                            <div class="sv-device-row" data-device-row="{{ $device->id }}">
                                <div class="sv-device-main">
                                    <x-feature-icon :name="$deviceIcon" :tone="$deviceTone" :size="16" variant="soft" class="sv-device-icon" />
                                    <div class="sv-device-copy">
                                        <strong>{{ $device->name }}</strong>
                                        <span>{{ $espOnline ? ($deviceOn ? 'Sedang menyala' : 'Siap digunakan') : 'Tidak dapat dikontrol' }}</span>
                                    </div>
                                </div>

                                <button
                                    type="button"
                                    class="sv-device-switch {{ $deviceOn ? 'is-on' : '' }} {{ ! $espOnline ? 'is-offline' : '' }}"
                                    data-device-toggle
                                    data-device-id="{{ $device->id }}"
                                    data-room-id="{{ $room->id }}"
                                    data-url="{{ route('devices.toggle', $device) }}"
                                    data-current-state="{{ $deviceOn ? 'on' : 'off' }}"
                                    aria-pressed="{{ $deviceOn ? 'true' : 'false' }}"
                                    {{ ! $espOnline ? 'disabled' : '' }}
                                    title="{{ $espOnline ? 'Ubah status perangkat' : 'Perangkat sedang offline' }}"
                                >
                                    <span data-switch-label>{{ $espOnline ? ($deviceOn ? 'Nyala' : 'Mati') : 'Offline' }}</span>
                                </button>
                            </div>
                        @empty
                            <div class="sv-empty-state sv-empty-state-compact">
                                <x-feature-icon name="plug" tone="cyan" :size="23" variant="soft" />
                                <h3>Belum ada perangkat</h3>
                                <a href="{{ route('settings', ['tab' => 'technician']) }}#room-{{ $room->id }}" class="sv-button sv-button-secondary sv-button-sm">Tambah perangkat</a>
                            </div>
                        @endforelse
                    </div>

                    <footer class="sv-room-card-foot">
                        @if($devices->isEmpty())
                            <span>Belum ada perangkat terdaftar</span>
                        @else
                            <span>
                                <strong data-room-active-count="{{ $room->id }}">{{ $roomOnlineActiveCount }}</strong> menyala ·
                                {{ $roomOnlineCount }} dari {{ $devices->count() }} online
                            </span>
                        @endif
                    </footer>
                </article>
            @endforeach
        </section>
    @endif
@endsection

@push('scripts')
    <script src="{{ asset('assets/js/smartvolt-rooms.js') }}?v=20260731-v8" defer></script>
@endpush
