@extends('layouts.app')

@section('title', 'Daftar Perangkat')
@section('page-title', 'Daftar Perangkat')
@section('page-subtitle', 'Perangkat yang terdaftar pada SmartVolt.')
@section('body-class', 'sv-devices-page sv-devices-streamlined')

@php
    $deviceCollection = collect($devices ?? []);
@endphp

@section('content')
    <section class="sv-card">
        <header class="sv-card-header">
            <div>
                <h2>Semua Perangkat</h2>
                <p>{{ $deviceCollection->count() }} perangkat terdaftar.</p>
            </div>
            <a href="{{ route('rooms') }}" class="sv-button sv-button-secondary sv-button-sm">Kembali ke ruangan</a>
        </header>

        <div class="sv-card-body">
            @if($deviceCollection->isEmpty())
                <div class="sv-empty-state sv-empty-state-compact">
                    <x-feature-icon name="devices" tone="cyan" :size="27" variant="soft" />
                    <h3>Belum ada perangkat</h3>
                    <a href="{{ route('settings', ['tab' => 'technician']) }}" class="sv-button sv-button-primary">Tambah perangkat</a>
                </div>
            @else
                <div class="sv-device-directory">
                    @foreach($deviceCollection as $device)
                        @php
                            $deviceLabel = strtolower(($device->type ?? '') . ' ' . ($device->name ?? ''));
                            $deviceIcon = str_contains($deviceLabel, 'kipas') || str_contains($deviceLabel, 'fan') ? 'fan'
                                : (str_contains($deviceLabel, 'lampu') || str_contains($deviceLabel, 'light') ? 'lightbulb' : 'plug');
                            $deviceTone = $device->status ? 'green' : ($deviceIcon === 'fan' ? 'cyan' : ($deviceIcon === 'lightbulb' ? 'amber' : 'blue'));
                        @endphp
                        <article class="sv-device-directory-card">
                            <x-feature-icon :name="$deviceIcon" :tone="$deviceTone" :size="18" variant="soft" />
                            <div class="sv-device-directory-copy">
                                <strong>{{ $device->name }}</strong>
                                <span>{{ $device->room?->name ?? 'Tanpa ruangan' }}</span>
                            </div>
                            <span class="sv-badge {{ $device->status ? 'sv-badge-success' : 'sv-badge-neutral' }}">
                                <span class="sv-status-dot" aria-hidden="true"></span>
                                {{ $device->status ? 'Nyala' : 'Mati' }}
                            </span>
                        </article>
                    @endforeach
                </div>
            @endif
        </div>
    </section>
@endsection
