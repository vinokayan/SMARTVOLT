@extends('layouts.app')

@section('title', $room->name ?? 'Detail Ruangan')
@section('page-title', $room->name ?? 'Detail Ruangan')
@section('page-subtitle', 'Ringkasan perangkat yang terdaftar pada ruangan ini.')
@section('body-class', 'sv-room-detail-page')

@php
    $deviceCollection = collect($devices ?? $room->devices ?? []);
    $activeCount = $deviceCollection->filter(fn ($device) => (bool) ($device->status ?? false))->count();
    $espCount = $deviceCollection
        ->map(fn ($device) => trim((string) ($device->esp_unit_id ?: $device->esp32_device_id)))
        ->filter()
        ->unique()
        ->count();
@endphp

@section('content')
    <div class="sv-page-heading">
        <div class="sv-page-heading-copy">
            <a href="{{ route('rooms') }}" class="sv-back-link"><x-icon name="chevron-right" :size="15" /> Kembali ke ruangan</a>
            <h2>{{ $room->name ?? 'Detail Ruangan' }}</h2>
            <p>Lihat perangkat, relay, dan unit ESP32 pada ruangan ini.</p>
        </div>
        <div class="sv-page-heading-actions">
            <a href="{{ route('settings', ['tab' => 'technician']) }}#technician" class="sv-button sv-button-primary">
                <x-icon name="tools" :size="17" /> Kelola perangkat
            </a>
        </div>
    </div>

    <section class="sv-room-summary-grid" aria-label="Ringkasan ruangan">
        <article class="sv-summary-card">
            <span class="sv-summary-icon"><x-icon name="devices" :size="20" /></span>
            <div class="sv-summary-copy"><span>Total perangkat</span><strong>{{ $deviceCollection->count() }}</strong></div>
        </article>
        <article class="sv-summary-card">
            <span class="sv-summary-icon"><x-icon name="power" :size="20" /></span>
            <div class="sv-summary-copy"><span>Sedang nyala</span><strong>{{ $activeCount }}</strong></div>
        </article>
        <article class="sv-summary-card">
            <span class="sv-summary-icon"><x-icon name="sensor" :size="20" /></span>
            <div class="sv-summary-copy"><span>Unit ESP32</span><strong>{{ $espCount }}</strong></div>
        </article>
        <article class="sv-summary-card">
            <span class="sv-summary-icon"><x-icon name="rooms" :size="20" /></span>
            <div class="sv-summary-copy"><span>Ruangan</span><strong>1</strong></div>
        </article>
    </section>

    <section class="sv-card">
        <header class="sv-card-header">
            <div>
                <h2>Perangkat di {{ $room->name ?? 'ruangan' }}</h2>
                <p>{{ $deviceCollection->count() }} perangkat terdaftar.</p>
            </div>
            <a href="{{ route('devices') }}" class="sv-text-button">Lihat semua perangkat</a>
        </header>
        <div class="sv-card-body">
            @forelse($deviceCollection as $device)
                <article class="sv-device-row sv-device-row-static">
                    <div class="sv-device-main">
                        <span class="sv-device-icon"><x-icon name="plug" :size="18" /></span>
                        <div class="sv-device-copy">
                            <strong>{{ $device->name }}</strong>
                            <span>
                                Relay {{ $device->relay_code ?: '-' }} ·
                                ESP {{ $device->esp_unit_id ?: $device->esp32_device_id ?: '-' }}
                            </span>
                        </div>
                    </div>
                    <span class="sv-badge {{ $device->status ? 'sv-badge-success' : 'sv-badge-neutral' }}">
                        {{ $device->status ? 'Nyala' : 'Mati' }}
                    </span>
                </article>
            @empty
                <div class="sv-empty-state">
                    <x-icon name="plug" :size="32" />
                    <h3>Belum ada perangkat</h3>
                    <p>Tambahkan perangkat melalui Mode Teknisi.</p>
                    <a href="{{ route('settings', ['tab' => 'technician']) }}#technician" class="sv-button sv-button-primary">Buka Mode Teknisi</a>
                </div>
            @endforelse
        </div>
    </section>
@endsection
