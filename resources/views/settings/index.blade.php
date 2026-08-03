@extends('layouts.app')

@section('title', 'Pengaturan')
@section('page-title', 'Pengaturan')
@section('page-subtitle', 'Kelola profil, keamanan akun, dan pengaturan listrik.')
@section('body-class', 'sv-settings-page')

@section('system-status')
    <span class="sv-badge {{ ($systemConnected ?? false) ? 'sv-badge-success' : 'sv-badge-neutral' }}">
        <span aria-hidden="true">●</span>
        {{ ($systemConnected ?? false) ? 'Terhubung' : 'Belum terhubung' }}
    </span>
@endsection

@php
    $requestedTab = request('tab');
    $activeTab = in_array(
        $requestedTab,
        ['profile', 'security', 'system'],
        true
    ) ? $requestedTab : 'profile';

    if (
        $errors->has('current_password')
        || $errors->has('password')
    ) {
        $activeTab = 'security';
    } elseif (
        $errors->has('electricity_tariff')
        || $errors->has('power_limit')
        || $errors->has('refresh_interval')
    ) {
        $activeTab = 'system';
    }
@endphp

@section('content')
    <div class="sv-settings-layout">
        <nav
            class="sv-settings-nav"
            data-tabs
            data-panels-root="settingsPanels"
            aria-label="Bagian pengaturan"
        >
            <button
                type="button"
                class="sv-tab-button {{ $activeTab === 'profile' ? 'is-active' : '' }}"
                data-tab-target="tab-profile"
                data-tab-name="profile"
                aria-selected="{{ $activeTab === 'profile' ? 'true' : 'false' }}"
            >
                <x-icon name="user" :size="17" />
                Profil
            </button>

            <button
                type="button"
                class="sv-tab-button {{ $activeTab === 'security' ? 'is-active' : '' }}"
                data-tab-target="tab-security"
                data-tab-name="security"
                aria-selected="{{ $activeTab === 'security' ? 'true' : 'false' }}"
            >
                <x-icon name="shield" :size="17" />
                Keamanan
            </button>

            <button
                type="button"
                class="sv-tab-button {{ $activeTab === 'system' ? 'is-active' : '' }}"
                data-tab-target="tab-system"
                data-tab-name="system"
                aria-selected="{{ $activeTab === 'system' ? 'true' : 'false' }}"
            >
                <x-icon name="energy" :size="17" />
                Energi &amp; Sistem
            </button>
        </nav>

        <div class="sv-settings-panels" id="settingsPanels">
            <section
                id="tab-profile"
                class="sv-tab-panel {{ $activeTab === 'profile' ? 'is-active' : '' }}"
                data-tab-panel
            >
                <article class="sv-card sv-settings-card">
                    <header class="sv-profile-summary">
                        <span class="sv-profile-avatar-large">
                            {{ strtoupper(substr($user->name ?? 'U', 0, 1)) }}
                        </span>

                        <div>
                            <h3>{{ $user->name ?? 'Pengguna SmartVolt' }}</h3>
                            <p>{{ $user->email ?? '-' }} · Pemilik rumah</p>
                        </div>
                    </header>

                    <div class="sv-card-body">
                        <form
                            action="{{ route('settings.profile.update') }}"
                            method="POST"
                            class="sv-stack"
                            data-loading-form
                        >
                            @csrf
                            @method('PUT')

                            <div class="sv-form-grid">
                                <div class="sv-form-field">
                                    <label for="profile_name" class="sv-form-label">
                                        Nama lengkap
                                    </label>

                                    <input
                                        type="text"
                                        id="profile_name"
                                        name="name"
                                        class="sv-form-control @error('name') is-invalid @enderror"
                                        value="{{ old('name', $user->name) }}"
                                        maxlength="255"
                                        autocomplete="name"
                                        required
                                    >

                                    @error('name')
                                        <p class="sv-form-error">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div class="sv-form-field">
                                    <label for="profile_email" class="sv-form-label">
                                        Alamat email
                                    </label>

                                    <input
                                        type="email"
                                        id="profile_email"
                                        name="email"
                                        class="sv-form-control @error('email') is-invalid @enderror"
                                        value="{{ old('email', $user->email) }}"
                                        maxlength="255"
                                        autocomplete="email"
                                        required
                                    >

                                    @error('email')
                                        <p class="sv-form-error">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>

                            <div class="sv-form-actions">
                                <button
                                    type="submit"
                                    class="sv-button sv-button-primary"
                                    data-loading-text="Menyimpan..."
                                >
                                    <span class="sv-button-spinner" aria-hidden="true"></span>
                                    <span data-button-label>Simpan profil</span>
                                </button>
                            </div>
                        </form>
                    </div>
                </article>
            </section>

            <section
                id="tab-security"
                class="sv-tab-panel {{ $activeTab === 'security' ? 'is-active' : '' }}"
                data-tab-panel
            >
                <article class="sv-card sv-settings-card">
                    <header class="sv-card-header">
                        <div>
                            <h2>Keamanan Akun</h2>
                            <p>
                                Gunakan kata sandi yang tidak dipakai pada akun lain
                                dan terdiri dari minimal delapan karakter.
                            </p>
                        </div>
                    </header>

                    <div class="sv-card-body">
                        <div class="sv-security-note">
                            <span class="sv-security-note-icon">
                                <x-icon name="shield" :size="19" />
                            </span>

                            <div>
                                <strong>Masukkan kata sandi saat ini sebelum menggantinya</strong>
                                <p>
                                    Langkah ini memastikan perubahan dilakukan oleh pemilik akun.
                                </p>
                            </div>
                        </div>

                        <form
                            action="{{ route('settings.password.update') }}"
                            method="POST"
                            class="sv-stack"
                            data-loading-form
                            style="margin-top: 16px;"
                        >
                            @csrf
                            @method('PUT')

                            <div class="sv-form-field">
                                <label for="current_password" class="sv-form-label">
                                    Kata sandi saat ini
                                </label>

                                <div class="sv-password-wrap">
                                    <input
                                        type="password"
                                        id="current_password"
                                        name="current_password"
                                        class="sv-form-control @error('current_password') is-invalid @enderror"
                                        autocomplete="current-password"
                                        required
                                    >

                                    <button
                                        type="button"
                                        class="sv-password-toggle"
                                        data-password-toggle="current_password"
                                        aria-label="Tampilkan kata sandi"
                                    >
                                        <x-icon name="eye" :size="18" />
                                    </button>
                                </div>

                                @error('current_password')
                                    <p class="sv-form-error">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="sv-form-grid">
                                <div class="sv-form-field">
                                    <label for="new_password" class="sv-form-label">
                                        Kata sandi baru
                                    </label>

                                    <div class="sv-password-wrap">
                                        <input
                                            type="password"
                                            id="new_password"
                                            name="password"
                                            class="sv-form-control @error('password') is-invalid @enderror"
                                            minlength="8"
                                            autocomplete="new-password"
                                            required
                                        >

                                        <button
                                            type="button"
                                            class="sv-password-toggle"
                                            data-password-toggle="new_password"
                                            aria-label="Tampilkan kata sandi"
                                        >
                                            <x-icon name="eye" :size="18" />
                                        </button>
                                    </div>

                                    @error('password')
                                        <p class="sv-form-error">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div class="sv-form-field">
                                    <label for="new_password_confirmation" class="sv-form-label">
                                        Ulangi kata sandi baru
                                    </label>

                                    <div class="sv-password-wrap">
                                        <input
                                            type="password"
                                            id="new_password_confirmation"
                                            name="password_confirmation"
                                            class="sv-form-control"
                                            minlength="8"
                                            autocomplete="new-password"
                                            required
                                        >

                                        <button
                                            type="button"
                                            class="sv-password-toggle"
                                            data-password-toggle="new_password_confirmation"
                                            aria-label="Tampilkan kata sandi"
                                        >
                                            <x-icon name="eye" :size="18" />
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <div class="sv-form-actions">
                                <button
                                    type="submit"
                                    class="sv-button sv-button-primary"
                                    data-loading-text="Memperbarui..."
                                >
                                    <span class="sv-button-spinner" aria-hidden="true"></span>
                                    <span data-button-label>Perbarui kata sandi</span>
                                </button>
                            </div>
                        </form>
                    </div>
                </article>
            </section>

            <section
                id="tab-system"
                class="sv-tab-panel {{ $activeTab === 'system' ? 'is-active' : '' }}"
                data-tab-panel
            >
                <article class="sv-card sv-settings-card">
                    <header class="sv-card-header">
                        <div>
                            <h2>Energi dan Sistem</h2>
                            <p>
                                Atur tarif listrik, batas daya rumah, dan waktu pembaruan data.
                            </p>
                        </div>
                    </header>

                    <div class="sv-card-body">
                        <form
                            action="{{ route('settings.system.update') }}"
                            method="POST"
                            class="sv-stack"
                            data-loading-form
                        >
                            @csrf
                            @method('PUT')

                            <div class="sv-form-grid-three">
                                <div class="sv-form-field">
                                    <label for="electricity_tariff" class="sv-form-label">
                                        Tarif listrik per kWh
                                    </label>

                                    <input
                                        type="number"
                                        step="0.01"
                                        min="0"
                                        id="electricity_tariff"
                                        name="electricity_tariff"
                                        class="sv-form-control @error('electricity_tariff') is-invalid @enderror"
                                        value="{{ old('electricity_tariff', $systemSetting->electricity_tariff) }}"
                                        required
                                    >

                                    <p class="sv-form-help">
                                        Contoh: 1444 untuk tarif Rp1.444 per kWh.
                                    </p>

                                    @error('electricity_tariff')
                                        <p class="sv-form-error">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div class="sv-form-field">
                                    <label for="power_limit" class="sv-form-label">
                                        Batas daya rumah
                                    </label>

                                    <input
                                        type="number"
                                        min="1"
                                        id="power_limit"
                                        name="power_limit"
                                        class="sv-form-control @error('power_limit') is-invalid @enderror"
                                        value="{{ old('power_limit', $systemSetting->power_limit) }}"
                                        required
                                    >

                                    <p class="sv-form-help">
                                        Masukkan dalam Watt, misalnya 900 atau 1300.
                                    </p>

                                    @error('power_limit')
                                        <p class="sv-form-error">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div class="sv-form-field">
                                    <label for="refresh_interval" class="sv-form-label">
                                        Waktu pembaruan data
                                    </label>

                                    <input
                                        type="number"
                                        min="10"
                                        max="60"
                                        id="refresh_interval"
                                        name="refresh_interval"
                                        class="sv-form-control @error('refresh_interval') is-invalid @enderror"
                                        value="{{ old('refresh_interval', $systemSetting->refresh_interval) }}"
                                        required
                                    >

                                    <p class="sv-form-help">
                                        Pilih antara 10 sampai 60 detik.
                                    </p>

                                    @error('refresh_interval')
                                        <p class="sv-form-error">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>

                            <div class="sv-form-actions">
                                <button
                                    type="submit"
                                    class="sv-button sv-button-primary"
                                    data-loading-text="Menyimpan..."
                                >
                                    <span class="sv-button-spinner" aria-hidden="true"></span>
                                    <span data-button-label>Simpan pengaturan</span>
                                </button>
                            </div>
                        </form>
                    </div>
                </article>
            </section>
        </div>
    </div>
@endsection

@push('scripts')
    <script
        src="{{ asset('assets/js/smartvolt-settings.js') }}?v=20260803-5"
        defer
    ></script>
@endpush
