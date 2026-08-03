@extends('layouts.app')

@section('title', $room->name ?? 'Detail Ruangan')
@section('page-title', $room->name ?? 'Detail Ruangan')
@section('page-subtitle', 'Kelola perangkat yang terhubung pada ruangan ini.')
@section('body-class', 'sv-room-detail-page')

@php
    $deviceCollection = collect($devices ?? $room->devices ?? []);
@endphp

@section('content')
    <div class="sv-page-heading">
        <div class="sv-page-heading-copy">
            <h2>Detail Ruangan</h2>
            <p>{{ $deviceCollection->count() }} perangkat terdaftar.</p>
        </div>
        <div class="sv-page-heading-actions">
            <a href="{{ route('rooms') }}" class="sv-button sv-button-secondary">
                <x-feature-icon name="chevron-right" tone="slate" :size="15" variant="flat" class="sv-icon-rotate-180" />
                Kembali
            </a>
        </div>
    </div>

    <section class="sv-section-grid sv-section-grid-two sv-room-detail-grid">
        <article class="sv-card">
            <header class="sv-card-header sv-card-header--with-icon">
                <x-feature-icon name="plus" tone="blue" :size="18" variant="soft" />
                <div><h2>Tambah Perangkat</h2><p>Daftarkan relay baru pada {{ $room->name }}.</p></div>
            </header>
            <div class="sv-card-body">
                <form action="{{ route('devices.store', $room) }}" method="POST" class="sv-stack" data-loading-form>
                    @csrf
                    <input type="hidden" name="room_id" value="{{ $room->id }}">
                    <div class="sv-form-grid">
                        <div class="sv-form-field">
                            <label class="sv-form-label" for="device_name">Nama perangkat</label>
                            <input id="device_name" type="text" name="name" class="sv-form-control" placeholder="Contoh: Lampu utama" required>
                        </div>
                        <div class="sv-form-field">
                            <label class="sv-form-label" for="device_type">Jenis perangkat</label>
                            <input id="device_type" type="text" name="type" class="sv-form-control" placeholder="lampu / fan / perangkat">
                        </div>
                        <div class="sv-form-field">
                            <label class="sv-form-label" for="esp_unit_id">ESP Unit ID</label>
                            <input id="esp_unit_id" type="text" name="esp_unit_id" class="sv-form-control" placeholder="Contoh: 2" required>
                        </div>
                        <div class="sv-form-field">
                            <label class="sv-form-label" for="relay_code">Relay channel</label>
                            <input id="relay_code" type="text" name="relay_code" class="sv-form-control" placeholder="Contoh: 1" required>
                        </div>
                    </div>
                    <div class="sv-form-actions">
                        <button type="submit" class="sv-button sv-button-primary" data-loading-text="Menyimpan...">
                            <x-feature-icon name="plus" tone="blue" :size="15" variant="flat" />
                            <span data-button-label>Tambah perangkat</span>
                        </button>
                    </div>
                </form>
            </div>
        </article>

        <article class="sv-card">
            <header class="sv-card-header sv-card-header--with-icon">
                <x-feature-icon name="devices" tone="cyan" :size="18" variant="soft" />
                <div><h2>Perangkat Ruangan</h2><p>Status tersimpan pada sistem.</p></div>
            </header>
            <div class="sv-card-body">
                @forelse($deviceCollection as $device)
                    @php
                        $label = strtolower(($device->type ?? '') . ' ' . ($device->name ?? ''));
                        $icon = str_contains($label, 'kipas') || str_contains($label, 'fan') ? 'fan' : (str_contains($label, 'lampu') ? 'lightbulb' : 'plug');
                    @endphp
                    <article class="sv-device-detail-item">
                        <div class="sv-device-main">
                            <x-feature-icon :name="$icon" :tone="$device->status ? 'green' : 'cyan'" :size="17" variant="soft" />
                            <div class="sv-device-copy">
                                <strong>{{ $device->name }}</strong>
                                <span>ESP {{ $device->esp_unit_id ?: $device->esp32_device_id ?: '-' }} · Relay {{ $device->relay_code ?: '-' }}</span>
                            </div>
                        </div>
                        <div class="sv-inline">
                            <form action="{{ route('devices.toggle', $device) }}" method="POST">
                                @csrf
                                <button type="submit" class="sv-button {{ $device->status ? 'sv-button-secondary' : 'sv-button-primary' }} sv-button-sm">
                                    <x-feature-icon name="power" :tone="$device->status ? 'rose' : 'green'" :size="14" variant="flat" />
                                    {{ $device->status ? 'Matikan' : 'Nyalakan' }}
                                </button>
                            </form>
                            <form action="{{ route('devices.destroy', $device) }}" method="POST" data-confirm="Hapus perangkat {{ $device->name }}?">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="sv-button sv-button-danger sv-button-sm">
                                    <x-feature-icon name="trash" tone="rose" :size="14" variant="flat" />
                                    Hapus
                                </button>
                            </form>
                        </div>
                    </article>
                @empty
                    <div class="sv-empty-state">
                        <x-feature-icon name="plug" tone="cyan" :size="25" variant="soft" />
                        <h3>Belum ada perangkat</h3>
                        <p>Tambahkan perangkat melalui formulir di samping.</p>
                    </div>
                @endforelse
            </div>
        </article>
    </section>
@endsection
