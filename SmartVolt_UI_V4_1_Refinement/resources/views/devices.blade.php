@extends('layouts.app')

@section('title', 'Perangkat')
@section('page-title', 'Perangkat')
@section('page-subtitle', 'Daftar relay dan perangkat SmartVolt.')
@section('body-class', 'sv-devices-page')

@php
    $deviceCollection = collect($devices ?? []);
    $activeCount = $deviceCollection->filter(fn ($device) => (bool) ($device->status ?? false))->count();
    $roomCount = $deviceCollection->pluck('room_id')->filter()->unique()->count();
    $espCount = $deviceCollection->map(fn ($device) => trim((string) ($device->esp_unit_id ?: $device->esp32_device_id)))->filter()->unique()->count();
@endphp

@section('content')
    <div class="sv-page-heading">
        <div class="sv-page-heading-copy">
            <h2>Daftar perangkat</h2>
            <p>Informasi perangkat yang terhubung ke relay SmartVolt.</p>
        </div>
        <div class="sv-page-heading-actions">
            <a href="{{ route('rooms') }}" class="sv-button sv-button-primary">
                <x-icon name="rooms" :size="17" /> Kontrol ruangan
            </a>
            <a href="{{ route('settings', ['tab' => 'technician']) }}#technician" class="sv-button sv-button-secondary">
                <x-icon name="tools" :size="17" /> Konfigurasi
            </a>
        </div>
    </div>

    <section class="sv-room-summary-grid sv-device-summary-grid" aria-label="Ringkasan perangkat">
        <article class="sv-summary-card">
            <span class="sv-summary-icon"><x-icon name="devices" :size="20" /></span>
            <div class="sv-summary-copy"><span>Total perangkat</span><strong>{{ $deviceCollection->count() }}</strong></div>
        </article>
        <article class="sv-summary-card">
            <span class="sv-summary-icon"><x-icon name="power" :size="20" /></span>
            <div class="sv-summary-copy"><span>Sedang nyala</span><strong>{{ $activeCount }}</strong></div>
        </article>
        <article class="sv-summary-card">
            <span class="sv-summary-icon"><x-icon name="rooms" :size="20" /></span>
            <div class="sv-summary-copy"><span>Ruangan</span><strong>{{ $roomCount }}</strong></div>
        </article>
        <article class="sv-summary-card">
            <span class="sv-summary-icon"><x-icon name="sensor" :size="20" /></span>
            <div class="sv-summary-copy"><span>Unit ESP32</span><strong>{{ $espCount }}</strong></div>
        </article>
    </section>

    <section class="sv-card">
        <header class="sv-card-header">
            <div>
                <h2>Perangkat terdaftar</h2>
                <p>{{ $deviceCollection->count() }} perangkat ditemukan.</p>
            </div>
        </header>

        @if($deviceCollection->isNotEmpty())
            <div class="sv-device-toolbar">
                <label class="sv-device-search" for="deviceSearch">
                    <x-icon name="search" :size="17" />
                    <input type="search" id="deviceSearch" placeholder="Cari perangkat, ruangan, ESP32, atau relay" autocomplete="off" data-device-search>
                </label>
                <span class="sv-device-filter-result"><strong data-device-visible-count>{{ $deviceCollection->count() }}</strong> dari {{ $deviceCollection->count() }} perangkat</span>
            </div>
        @endif

        <div class="sv-card-body sv-card-body-table">
            @if($deviceCollection->isEmpty())
                <div class="sv-empty-state">
                    <x-icon name="devices" :size="34" />
                    <h3>Belum ada perangkat</h3>
                    <p>Tambahkan relay melalui Mode Teknisi.</p>
                    <a href="{{ route('settings', ['tab' => 'technician']) }}#technician" class="sv-button sv-button-primary">Buka Mode Teknisi</a>
                </div>
            @else
                <div class="sv-device-cards-mobile">
                    @foreach($deviceCollection as $device)
                        @php $espId = $device->esp_unit_id ?: $device->esp32_device_id ?: '-'; @endphp
                        <article class="sv-device-list-card" data-device-filter-item data-device-search-text="{{ strtolower(($device->name ?? '') . ' ' . ($device->room?->name ?? '') . ' ' . ($device->esp_unit_id ?: $device->esp32_device_id ?: '') . ' ' . ($device->relay_code ?? '')) }}">
                            <div class="sv-device-list-head">
                                <span class="sv-device-icon"><x-icon name="plug" :size="18" /></span>
                                <div><strong>{{ $device->name }}</strong><span>{{ $device->room?->name ?? 'Tanpa ruangan' }}</span></div>
                                <span class="sv-badge {{ $device->status ? 'sv-badge-success' : 'sv-badge-neutral' }}">{{ $device->status ? 'Nyala' : 'Mati' }}</span>
                            </div>
                            <dl class="sv-device-meta">
                                <div><dt>ESP32</dt><dd>{{ $espId }}</dd></div>
                                <div><dt>Relay</dt><dd>{{ $device->relay_code ?: '-' }}</dd></div>
                            </dl>
                        </article>
                    @endforeach
                </div>

                <div class="sv-table-wrap sv-device-table-desktop">
                    <table class="sv-table">
                        <thead>
                            <tr><th>Perangkat</th><th>Ruangan</th><th>ESP32</th><th>Relay</th><th>Status</th></tr>
                        </thead>
                        <tbody>
                            @foreach($deviceCollection as $device)
                                <tr data-device-filter-item data-device-search-text="{{ strtolower(($device->name ?? '') . ' ' . ($device->room?->name ?? '') . ' ' . ($device->esp_unit_id ?: $device->esp32_device_id ?: '') . ' ' . ($device->relay_code ?? '')) }}">
                                    <td><div class="sv-table-primary"><span class="sv-device-icon"><x-icon name="plug" :size="16" /></span><strong>{{ $device->name }}</strong></div></td>
                                    <td>{{ $device->room?->name ?? '-' }}</td>
                                    <td>{{ $device->esp_unit_id ?: $device->esp32_device_id ?: '-' }}</td>
                                    <td>{{ $device->relay_code ?: '-' }}</td>
                                    <td><span class="sv-badge {{ $device->status ? 'sv-badge-success' : 'sv-badge-neutral' }}">{{ $device->status ? 'Nyala' : 'Mati' }}</span></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </section>
@endsection

@push('scripts')
    <script src="{{ asset('assets/js/smartvolt-devices.js') }}?v=20260730-4" defer></script>
@endpush
