@extends('layouts.app')

@section('title', 'Ruangan & Perangkat')
@section('page-title', 'Ruangan & Perangkat')
@section('page-subtitle', 'Pantau dan kontrol perangkat listrik berdasarkan ruangan.')
@section('body-class', 'sv-rooms-page')

@php
    $roomCollection = collect($rooms ?? []);
    $onlineIds = collect($onlineEspUnitIds ?? [])->map(fn ($value) => (string) $value);
    $allDevices = $roomCollection->pluck('devices')->flatten();
    $isOn = function ($status): bool {
        if (is_bool($status)) {
            return $status;
        }
        if (is_numeric($status)) {
            return (int) $status === 1;
        }
        return in_array(strtolower((string) $status), ['on', 'nyala', 'active', 'aktif', 'true', '1'], true);
    };
    $activeDevices = $allDevices->filter(fn ($device) => $isOn($device->status ?? null))->count();
    $onlineDevices = $allDevices->filter(function ($device) use ($onlineIds) {
        $espId = trim((string) ($device->esp_unit_id ?: $device->esp32_device_id));
        return $espId !== '' && $onlineIds->contains($espId);
    })->count();
@endphp

@section('system-status')
    <span class="sv-badge {{ $onlineIds->isNotEmpty() ? 'sv-badge-success' : 'sv-badge-warning' }}">
        <span aria-hidden="true">●</span>
        {{ $onlineIds->isNotEmpty() ? $onlineIds->count() . ' ESP32 online' : 'Perangkat offline' }}
    </span>
@endsection

@section('content')
    <div class="sv-page-heading">
        <div class="sv-page-heading-copy">
            <h2>Kontrol perangkat per ruangan</h2>
            <p>Perangkat dapat dikontrol saat ESP32 terhubung.</p>
        </div>
        <div class="sv-page-heading-actions">
            <a href="{{ route('settings', ['tab' => 'technician']) }}#technician" class="sv-button sv-button-primary">
                <x-icon name="tools" :size="17" />
                Kelola konfigurasi
            </a>
        </div>
    </div>

    <section class="sv-room-summary-grid" aria-label="Ringkasan ruangan dan perangkat">
        <article class="sv-summary-card">
            <span class="sv-summary-icon"><x-icon name="rooms" :size="20" /></span>
            <div class="sv-summary-copy">
                <span>Total Ruangan</span>
                <strong>{{ $roomCollection->count() }}</strong>
            </div>
        </article>
        <article class="sv-summary-card">
            <span class="sv-summary-icon"><x-icon name="devices" :size="20" /></span>
            <div class="sv-summary-copy">
                <span>Total Perangkat</span>
                <strong data-total-devices>{{ $allDevices->count() }}</strong>
            </div>
        </article>
        <article class="sv-summary-card">
            <span class="sv-summary-icon"><x-icon name="power" :size="20" /></span>
            <div class="sv-summary-copy">
                <span>Perangkat Aktif</span>
                <strong data-active-devices>{{ $activeDevices }}</strong>
            </div>
        </article>
        <article class="sv-summary-card">
            <span class="sv-summary-icon"><x-icon name="wifi" :size="20" /></span>
            <div class="sv-summary-copy">
                <span>Perangkat Online</span>
                <strong>{{ $onlineDevices }}</strong>
            </div>
        </article>
    </section>

    @if($roomCollection->isEmpty())
        <section class="sv-card">
            <div class="sv-card-body">
                <div class="sv-empty-state">
                    <x-icon name="rooms" :size="34" />
                    <h3>Belum ada ruangan</h3>
                    <p>Tambahkan ruangan dan perangkat melalui Mode Teknisi.</p>
                    <a href="{{ route('settings', ['tab' => 'technician']) }}#technician" class="sv-button sv-button-primary">Buka Mode Teknisi</a>
                </div>
            </div>
        </section>
    @else
        <section class="sv-room-grid" aria-label="Daftar ruangan">
            @foreach($roomCollection as $room)
                @php
                    $devices = collect($room->devices ?? []);
                    $roomActiveCount = $devices->filter(fn ($device) => $isOn($device->status ?? null))->count();
                    $roomOnlineCount = $devices->filter(function ($device) use ($onlineIds) {
                        $espId = trim((string) ($device->esp_unit_id ?: $device->esp32_device_id));
                        return $espId !== '' && $onlineIds->contains($espId);
                    })->count();
                    $power = (float) (($roomPower ?? collect())[$room->id] ?? 0);
                @endphp

                <article class="sv-room-card" data-room-card="{{ $room->id }}">
                    <header class="sv-room-card-head">
                        <div class="sv-room-card-title">
                            <span class="sv-room-icon"><x-icon name="rooms" :size="19" /></span>
                            <div>
                                <h3>{{ $room->name }}</h3>
                                <p>
                                    {{ $devices->count() }} perangkat ·
                                    <span data-room-active-count="{{ $room->id }}">{{ $roomActiveCount }}</span> aktif ·
                                    {{ number_format($power, 1, ',', '.') }} W
                                </p>
                            </div>
                        </div>
                        <span class="sv-badge {{ $roomOnlineCount > 0 ? 'sv-badge-success' : 'sv-badge-warning' }}">
                            {{ $roomOnlineCount > 0 ? $roomOnlineCount . ' online' : 'Offline' }}
                        </span>
                    </header>

                    <div class="sv-room-card-body">
                        @forelse($devices as $device)
                            @php
                                $deviceOn = $isOn($device->status ?? null);
                                $espId = trim((string) ($device->esp_unit_id ?: $device->esp32_device_id));
                                $espOnline = $espId !== '' && $onlineIds->contains($espId);
                            @endphp
                            <div class="sv-device-row" data-device-row="{{ $device->id }}">
                                <div class="sv-device-main">
                                    <span class="sv-device-icon"><x-icon name="lightbulb" :size="17" /></span>
                                    <div class="sv-device-copy">
                                        <strong>{{ $device->name }}</strong>
                                        <span>
                                            Relay {{ $device->relay_code ?: '-' }} · ESP {{ $espId !== '' ? $espId : '-' }} ·
                                            {{ $espOnline ? 'Siap dikontrol' : 'ESP32 offline' }}
                                        </span>
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
                                    title="{{ $espOnline ? 'Ubah status perangkat' : 'ESP32 belum online' }}"
                                >
                                    <span data-switch-label>{{ $espOnline ? ($deviceOn ? 'Nyala' : 'Mati') : 'Offline' }}</span>
                                </button>
                            </div>
                        @empty
                            <div class="sv-empty-state">
                                <x-icon name="plug" :size="28" />
                                <h3>Belum ada perangkat</h3>
                                <p>Tambahkan perangkat melalui Mode Teknisi.</p>
                            </div>
                        @endforelse
                    </div>

                    <footer class="sv-room-card-foot">
                        <span>{{ $roomOnlineCount > 0 ? 'Perangkat dapat dikontrol' : 'Menunggu telemetry ESP32' }}</span>
                        <a href="{{ route('settings', ['tab' => 'technician']) }}#room-{{ $room->id }}" class="sv-text-button">Konfigurasi</a>
                    </footer>
                </article>
            @endforeach
        </section>
    @endif
@endsection

@push('scripts')
    <script src="{{ asset('assets/js/smartvolt-rooms.js') }}?v=20260727" defer></script>
@endpush
