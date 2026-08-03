@extends('layouts.app')

@section('title', $room->name ?? 'Detail Ruangan')
@section('page-title', $room->name ?? 'Detail Ruangan')
@section('page-subtitle', 'Perangkat pada ruangan ini.')
@section('body-class', 'sv-room-detail-page')

@php
    $deviceCollection = collect($devices ?? $room->devices ?? []);
@endphp

@section('content')
    <section class="sv-card">
        <header class="sv-card-header">
            <div><h2>Perangkat</h2><p>{{ $deviceCollection->count() }} perangkat terdaftar.</p></div>
            <a href="{{ route('rooms') }}" class="sv-button sv-button-secondary sv-button-sm">Kembali</a>
        </header>
        <div class="sv-card-body">
            @if($deviceCollection->isEmpty())
                <div class="sv-empty-state sv-empty-state-compact">
                    <x-feature-icon name="plug" tone="cyan" :size="25" variant="soft" />
                    <h3>Belum ada perangkat</h3>
                    <a href="{{ route('technician.index') }}#room-{{ $room->id }}" class="sv-button sv-button-primary">Tambah melalui Mode Teknisi</a>
                </div>
            @else
                <div class="sv-device-directory">
                    @foreach($deviceCollection as $device)
                        @php
                            $label = strtolower(($device->type ?? '') . ' ' . ($device->name ?? ''));
                            $icon = str_contains($label, 'kipas') || str_contains($label, 'fan') ? 'fan' : (str_contains($label, 'lampu') ? 'lightbulb' : 'plug');
                        @endphp
                        <article class="sv-device-directory-card">
                            <x-feature-icon :name="$icon" :tone="$device->status ? 'green' : 'cyan'" :size="18" variant="soft" />
                            <div class="sv-device-directory-copy"><strong>{{ $device->name }}</strong><span>{{ $device->status ? 'Sedang menyala' : 'Sedang mati' }}</span></div>
                            <span class="sv-badge {{ $device->status ? 'sv-badge-success' : 'sv-badge-neutral' }}">{{ $device->status ? 'Nyala' : 'Mati' }}</span>
                        </article>
                    @endforeach
                </div>
            @endif
        </div>
    </section>
@endsection


