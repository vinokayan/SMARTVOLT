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
            <p>Kontrol langsung tersedia pada halaman Ruangan & Perangkat karena status online diperiksa per ESP32.</p>
        </div>
        <div class="sv-page-heading-actions">
            <a href="{{ route('rooms') }}" class="sv-button sv-button-primary"><x-icon name="rooms" :size="17" /> Buka kontrol ruangan</a>
        </div>
    </div>

    <section class="sv-card">
        <header class="sv-card-header">
            <div><h2>Daftar Relay</h2><p>{{ $deviceCollection->count() }} perangkat terdaftar.</p></div>
            <a href="{{ route('settings', ['tab' => 'technician']) }}" class="sv-button sv-button-secondary sv-button-sm"><x-icon name="tools" :size="15" /> Konfigurasi</a>
        </header>
        <div class="sv-card-body">
            @if($deviceCollection->isEmpty())
                <div class="sv-empty-state">
                    <x-icon name="devices" :size="32" />
                    <h3>Belum ada perangkat</h3>
                    <p>Daftarkan relay melalui Mode Teknisi agar perangkat dapat dikontrol.</p>
                    <a href="{{ route('settings', ['tab' => 'technician']) }}" class="sv-button sv-button-primary">Buka Mode Teknisi</a>
                </div>
            @else
                <div class="sv-table-wrap">
                    <table class="sv-table">
                        <thead><tr><th>Perangkat</th><th>Ruangan</th><th>ESP Unit ID</th><th>Relay</th><th>Status tersimpan</th></tr></thead>
                        <tbody>
                            @foreach($deviceCollection as $device)
                                <tr>
                                    <td>{{ $device->name }}</td>
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
