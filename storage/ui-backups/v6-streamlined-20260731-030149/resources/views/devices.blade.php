@extends('layouts.app')

@section('title', 'Daftar Perangkat')
@section('page-title', 'Daftar Perangkat')
@section('page-subtitle', 'Informasi relay yang terdaftar pada seluruh ruangan.')
@section('body-class', 'sv-devices-page')

@php
    $deviceCollection = collect($devices ?? []);
@endphp

@section('content')
    <div class="sv-page-heading">
        <div class="sv-page-heading-copy">
            <h2>Perangkat SmartVolt</h2>
            <p>{{ $deviceCollection->count() }} perangkat terdaftar pada sistem.</p>
        </div>
        <div class="sv-page-heading-actions">
            <a href="{{ route('rooms') }}" class="sv-button sv-button-primary">
                <x-feature-icon name="rooms" tone="violet" :size="16" variant="flat" />
                Buka kontrol ruangan
            </a>
        </div>
    </div>

    <section class="sv-card">
        <header class="sv-card-header sv-card-header--with-icon">
            <x-feature-icon name="devices" tone="cyan" :size="19" variant="soft" />
            <div class="sv-card-header-copy">
                <h2>Daftar Relay</h2>
                <p>Informasi perangkat, ruangan, ESP32, dan relay.</p>
            </div>
            <a href="{{ route('settings', ['tab' => 'technician']) }}" class="sv-button sv-button-secondary sv-button-sm">
                <x-feature-icon name="tools" tone="amber" :size="14" variant="flat" />
                Konfigurasi
            </a>
        </header>

        <div class="sv-card-body">
            @if($deviceCollection->isEmpty())
                <div class="sv-empty-state">
                    <x-feature-icon name="devices" tone="cyan" :size="27" variant="soft" />
                    <h3>Belum ada perangkat</h3>
                    <p>Daftarkan relay melalui Mode Teknisi.</p>
                    <a href="{{ route('settings', ['tab' => 'technician']) }}" class="sv-button sv-button-primary">
                        <x-feature-icon name="tools" tone="amber" :size="15" variant="flat" />
                        Buka Mode Teknisi
                    </a>
                </div>
            @else
                <div class="sv-table-wrap sv-device-table-desktop">
                    <table class="sv-table">
                        <thead>
                            <tr>
                                <th>Perangkat</th>
                                <th>Ruangan</th>
                                <th>ESP Unit ID</th>
                                <th>Relay</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($deviceCollection as $device)
                                @php
                                    $deviceLabel = strtolower(($device->type ?? '') . ' ' . ($device->name ?? ''));
                                    $deviceIcon = str_contains($deviceLabel, 'kipas') || str_contains($deviceLabel, 'fan') ? 'fan'
                                        : (str_contains($deviceLabel, 'lampu') || str_contains($deviceLabel, 'light') ? 'lightbulb' : 'plug');
                                    $deviceTone = $device->status ? 'green' : ($deviceIcon === 'fan' ? 'cyan' : ($deviceIcon === 'lightbulb' ? 'amber' : 'blue'));
                                @endphp
                                <tr>
                                    <td>
                                        <span class="sv-table-feature">
                                            <x-feature-icon :name="$deviceIcon" :tone="$deviceTone" :size="15" variant="soft" />
                                            <strong>{{ $device->name }}</strong>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="sv-table-feature">
                                            <x-feature-icon name="rooms" tone="violet" :size="14" variant="flat" />
                                            {{ $device->room?->name ?? '-' }}
                                        </span>
                                    </td>
                                    <td>{{ $device->esp_unit_id ?: $device->esp32_device_id ?: '-' }}</td>
                                    <td>{{ $device->relay_code ?: '-' }}</td>
                                    <td>
                                        <span class="sv-badge {{ $device->status ? 'sv-badge-success' : 'sv-badge-neutral' }}">
                                            <span class="sv-status-dot" aria-hidden="true"></span>
                                            {{ $device->status ? 'Nyala' : 'Mati' }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="sv-device-mobile-list">
                    @foreach($deviceCollection as $device)
                        @php
                            $deviceLabel = strtolower(($device->type ?? '') . ' ' . ($device->name ?? ''));
                            $deviceIcon = str_contains($deviceLabel, 'kipas') || str_contains($deviceLabel, 'fan') ? 'fan'
                                : (str_contains($deviceLabel, 'lampu') || str_contains($deviceLabel, 'light') ? 'lightbulb' : 'plug');
                            $deviceTone = $device->status ? 'green' : ($deviceIcon === 'fan' ? 'cyan' : ($deviceIcon === 'lightbulb' ? 'amber' : 'blue'));
                        @endphp
                        <article class="sv-device-mobile-card">
                            <header>
                                <x-feature-icon :name="$deviceIcon" :tone="$deviceTone" :size="18" variant="soft" />
                                <div><strong>{{ $device->name }}</strong><span>{{ $device->room?->name ?? 'Tanpa ruangan' }}</span></div>
                                <span class="sv-badge {{ $device->status ? 'sv-badge-success' : 'sv-badge-neutral' }}">{{ $device->status ? 'Nyala' : 'Mati' }}</span>
                            </header>
                            <dl>
                                <div><dt>ESP Unit ID</dt><dd>{{ $device->esp_unit_id ?: $device->esp32_device_id ?: '-' }}</dd></div>
                                <div><dt>Relay</dt><dd>{{ $device->relay_code ?: '-' }}</dd></div>
                            </dl>
                        </article>
                    @endforeach
                </div>
            @endif
        </div>
    </section>
@endsection
