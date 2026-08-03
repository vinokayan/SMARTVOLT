@extends('layouts.app')

@section('title', $room->name ?? 'Detail Ruangan')
@section('page-title', $room->name ?? 'Detail Ruangan')
@section('page-subtitle', 'Perangkat yang terdaftar pada ruangan ini.')
@section('body-class', 'sv-room-detail-page')

@php $deviceCollection = collect($devices ?? $room->devices ?? []); @endphp

@section('content')
    <div class="sv-page-heading">
        <div class="sv-page-heading-copy">
            <a href="{{ route('rooms') }}" class="sv-back-link"><x-icon name="chevron-right" :size="15" /> Kembali ke ruangan</a>
            <h2>{{ $room->name }}</h2>
            <p>{{ $deviceCollection->count() }} perangkat terdaftar.</p>
        </div>
        <div class="sv-page-heading-actions">
            <a href="{{ route('settings', ['tab' => 'technician']) }}#technician" class="sv-button sv-button-primary"><x-icon name="tools" :size="17" /> Kelola perangkat</a>
        </div>
    </div>

    <section class="sv-card">
        <header class="sv-card-header"><div><h2>Perangkat</h2><p>Status tersimpan pada sistem.</p></div></header>
        <div class="sv-card-body">
            @forelse($deviceCollection as $device)
                <article class="sv-device-row sv-device-row-static">
                    <div class="sv-device-main">
                        <span class="sv-device-icon"><x-icon name="plug" :size="18" /></span>
                        <div class="sv-device-copy">
                            <strong>{{ $device->name }}</strong>
                            <span>Relay {{ $device->relay_code ?: '-' }} · ESP {{ $device->esp_unit_id ?: $device->esp32_device_id ?: '-' }}</span>
                        </div>
                    </div>
                    <span class="sv-badge {{ $device->status ? 'sv-badge-success' : 'sv-badge-neutral' }}">{{ $device->status ? 'Nyala' : 'Mati' }}</span>
                </article>
            @empty
                <div class="sv-empty-state"><x-icon name="plug" :size="32" /><h3>Belum ada perangkat</h3><p>Tambahkan perangkat melalui Mode Teknisi.</p></div>
            @endforelse
        </div>
    </section>
@endsection
