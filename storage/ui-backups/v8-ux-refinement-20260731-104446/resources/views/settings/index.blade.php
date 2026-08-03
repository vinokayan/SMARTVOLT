@extends('layouts.app')

@section('title', 'Pengaturan')
@section('page-title', request('tab') === 'technician' ? 'Mode Teknisi' : 'Pengaturan')
@section('page-subtitle', request('tab') === 'technician' ? 'Konfigurasi perangkat SmartVolt.' : 'Kelola akun dan pengaturan sistem.')
@section('body-class', 'sv-settings-page sv-settings-streamlined')

@php
    $advancedMode = (bool) session('advanced_mode');
    $requestedTab = request('tab');
    $activeTab = in_array($requestedTab, ['profile', 'security', 'system', 'technician'], true)
        ? $requestedTab
        : (session('open_advanced_panel') ? 'technician' : 'profile');

    if ($errors->has('advanced_mode')) {
        $activeTab = 'technician';
    } elseif ($errors->has('current_password') || $errors->has('password')) {
        $activeTab = 'security';
    } elseif ($errors->has('electricity_tariff') || $errors->has('power_limit') || $errors->has('refresh_interval')) {
        $activeTab = 'system';
    }

    $roomCollection = collect($rooms ?? []);
    $meterCollection = collect($energyMeters ?? []);
    $deviceCollection = collect($devices ?? []);
    $selectedRoomId = (int) (session('selected_room_id') ?? 0);
@endphp

@section('system-status')
    <span class="sv-badge {{ $advancedMode ? 'sv-badge-warning' : 'sv-badge-success' }}">
        <span class="sv-status-dot" aria-hidden="true"></span>
        {{ $advancedMode ? 'Mode teknisi aktif' : 'Mode pengguna' }}
    </span>
@endsection

@section('content')
    <div class="sv-settings-layout">
        <nav class="sv-settings-nav" data-tabs data-panels-root="settingsPanels" aria-label="Bagian pengaturan">
            <button type="button" class="sv-tab-button {{ $activeTab === 'profile' ? 'is-active' : '' }}" data-tab-target="tab-profile" data-tab-name="profile" aria-selected="{{ $activeTab === 'profile' ? 'true' : 'false' }}">
                <x-feature-icon name="user" tone="blue" :size="16" variant="soft" /> <span>Profil</span>
            </button>
            <button type="button" class="sv-tab-button {{ $activeTab === 'security' ? 'is-active' : '' }}" data-tab-target="tab-security" data-tab-name="security" aria-selected="{{ $activeTab === 'security' ? 'true' : 'false' }}">
                <x-feature-icon name="shield" tone="green" :size="16" variant="soft" /> <span>Keamanan</span>
            </button>
            <button type="button" class="sv-tab-button {{ $activeTab === 'system' ? 'is-active' : '' }}" data-tab-target="tab-system" data-tab-name="system" aria-selected="{{ $activeTab === 'system' ? 'true' : 'false' }}">
                <x-feature-icon name="energy" tone="cyan" :size="16" variant="soft" /> <span>Energi & Sistem</span>
            </button>
            <button type="button" class="sv-tab-button {{ $activeTab === 'technician' ? 'is-active' : '' }}" data-tab-target="tab-technician" data-tab-name="technician" aria-selected="{{ $activeTab === 'technician' ? 'true' : 'false' }}">
                <x-feature-icon name="tools" tone="amber" :size="16" variant="soft" /> <span>Mode Teknisi</span>
            </button>
        </nav>

        <div class="sv-settings-panels" id="settingsPanels">
            <section id="tab-profile" class="sv-tab-panel {{ $activeTab === 'profile' ? 'is-active' : '' }}" data-tab-panel>
                <article class="sv-card sv-settings-card sv-settings-card-compact">
                    <header class="sv-profile-summary">
                        <span class="sv-profile-avatar-large">{{ strtoupper(substr($user->name ?? 'U', 0, 1)) }}</span>
                        <div>
                            <h3>{{ $user->name ?? 'Pengguna SmartVolt' }}</h3>
                            <p>{{ $user->email ?? '-' }}</p>
                        </div>
                    </header>

                    <div class="sv-card-body">
                        <form action="{{ route('settings.profile.update') }}" method="POST" class="sv-stack" data-loading-form>
                            @csrf
                            @method('PUT')

                            <div class="sv-form-grid">
                                <div class="sv-form-field">
                                    <label for="profile_name" class="sv-form-label">Nama lengkap</label>
                                    <input type="text" id="profile_name" name="name" class="sv-form-control @error('name') is-invalid @enderror" value="{{ old('name', $user->name) }}" maxlength="255" autocomplete="name" required>
                                    @error('name')<p class="sv-form-error">{{ $message }}</p>@enderror
                                </div>
                                <div class="sv-form-field">
                                    <label for="profile_email" class="sv-form-label">Alamat email</label>
                                    <input type="email" id="profile_email" name="email" class="sv-form-control @error('email') is-invalid @enderror" value="{{ old('email', $user->email) }}" maxlength="255" autocomplete="email" required>
                                    @error('email')<p class="sv-form-error">{{ $message }}</p>@enderror
                                </div>
                            </div>

                            <div class="sv-form-actions">
                                <button type="submit" class="sv-button sv-button-primary" data-loading-text="Menyimpan...">
                                    <span class="sv-button-spinner" aria-hidden="true"></span>
                                    <span data-button-label>Simpan profil</span>
                                </button>
                            </div>
                        </form>
                    </div>
                </article>
            </section>

            <section id="tab-security" class="sv-tab-panel {{ $activeTab === 'security' ? 'is-active' : '' }}" data-tab-panel>
                <article class="sv-card sv-settings-card sv-settings-card-compact">
                    <header class="sv-card-header">
                        <div><h2>Ubah Kata Sandi</h2><p>Gunakan minimal delapan karakter.</p></div>
                    </header>
                    <div class="sv-card-body">
                        <form action="{{ route('settings.password.update') }}" method="POST" class="sv-stack" data-loading-form>
                            @csrf
                            @method('PUT')

                            <div class="sv-form-field">
                                <label for="current_password" class="sv-form-label">Kata sandi saat ini</label>
                                <div class="sv-password-wrap">
                                    <input type="password" id="current_password" name="current_password" class="sv-form-control @error('current_password') is-invalid @enderror" autocomplete="current-password" required>
                                    <button type="button" class="sv-password-toggle" data-password-toggle="current_password" aria-label="Tampilkan kata sandi"><x-icon name="eye" :size="17" /></button>
                                </div>
                                @error('current_password')<p class="sv-form-error">{{ $message }}</p>@enderror
                            </div>

                            <div class="sv-form-grid">
                                <div class="sv-form-field">
                                    <label for="new_password" class="sv-form-label">Kata sandi baru</label>
                                    <div class="sv-password-wrap">
                                        <input type="password" id="new_password" name="password" class="sv-form-control @error('password') is-invalid @enderror" minlength="8" autocomplete="new-password" required>
                                        <button type="button" class="sv-password-toggle" data-password-toggle="new_password" aria-label="Tampilkan kata sandi"><x-icon name="eye" :size="17" /></button>
                                    </div>
                                    @error('password')<p class="sv-form-error">{{ $message }}</p>@enderror
                                </div>
                                <div class="sv-form-field">
                                    <label for="new_password_confirmation" class="sv-form-label">Konfirmasi kata sandi</label>
                                    <div class="sv-password-wrap">
                                        <input type="password" id="new_password_confirmation" name="password_confirmation" class="sv-form-control" minlength="8" autocomplete="new-password" required>
                                        <button type="button" class="sv-password-toggle" data-password-toggle="new_password_confirmation" aria-label="Tampilkan kata sandi"><x-icon name="eye" :size="17" /></button>
                                    </div>
                                </div>
                            </div>

                            <div class="sv-form-actions">
                                <button type="submit" class="sv-button sv-button-primary" data-loading-text="Memperbarui...">
                                    <span class="sv-button-spinner" aria-hidden="true"></span>
                                    <span data-button-label>Perbarui kata sandi</span>
                                </button>
                            </div>
                        </form>
                    </div>
                </article>
            </section>

            <section id="tab-system" class="sv-tab-panel {{ $activeTab === 'system' ? 'is-active' : '' }}" data-tab-panel>
                <article class="sv-card sv-settings-card sv-settings-card-compact">
                    <header class="sv-card-header">
                        <div><h2>Energi dan Sistem</h2><p>Tarif, batas daya, dan interval pembaruan.</p></div>
                        @if(!$advancedMode)<span class="sv-badge sv-badge-warning">Terkunci</span>@endif
                    </header>
                    <div class="sv-card-body">
                        @if(!$advancedMode)
                            <div class="sv-inline-notice sv-inline-notice-warning">
                                <x-icon name="lock" :size="18" />
                                <span>Aktifkan Mode Teknisi untuk mengubah nilai.</span>
                            </div>
                        @endif

                        <form action="{{ route('settings.system.update') }}" method="POST" class="sv-stack" data-loading-form>
                            @csrf
                            @method('PUT')

                            <div class="sv-form-grid-three">
                                <div class="sv-form-field">
                                    <label for="electricity_tariff" class="sv-form-label">Tarif per kWh</label>
                                    <input type="number" step="0.01" min="0" id="electricity_tariff" name="electricity_tariff" class="sv-form-control @error('electricity_tariff') is-invalid @enderror" value="{{ old('electricity_tariff', $systemSetting->electricity_tariff) }}" {{ !$advancedMode ? 'disabled' : '' }} required>
                                    @error('electricity_tariff')<p class="sv-form-error">{{ $message }}</p>@enderror
                                </div>
                                <div class="sv-form-field">
                                    <label for="power_limit" class="sv-form-label">Batas daya (W)</label>
                                    <input type="number" min="1" id="power_limit" name="power_limit" class="sv-form-control @error('power_limit') is-invalid @enderror" value="{{ old('power_limit', $systemSetting->power_limit) }}" {{ !$advancedMode ? 'disabled' : '' }} required>
                                    @error('power_limit')<p class="sv-form-error">{{ $message }}</p>@enderror
                                </div>
                                <div class="sv-form-field">
                                    <label for="refresh_interval" class="sv-form-label">Interval (detik)</label>
                                    <input type="number" min="1" max="60" id="refresh_interval" name="refresh_interval" class="sv-form-control @error('refresh_interval') is-invalid @enderror" value="{{ old('refresh_interval', $systemSetting->refresh_interval) }}" {{ !$advancedMode ? 'disabled' : '' }} required>
                                    @error('refresh_interval')<p class="sv-form-error">{{ $message }}</p>@enderror
                                </div>
                            </div>

                            <div class="sv-form-actions">
                                @if($advancedMode)
                                    <button type="submit" class="sv-button sv-button-primary" data-loading-text="Menyimpan..."><span class="sv-button-spinner"></span><span data-button-label>Simpan pengaturan</span></button>
                                @else
                                    <a href="{{ route('settings', ['tab' => 'technician']) }}#technician" class="sv-button sv-button-warning">Aktifkan Mode Teknisi</a>
                                @endif
                            </div>
                        </form>
                    </div>
                </article>
            </section>

            <section id="tab-technician" class="sv-tab-panel {{ $activeTab === 'technician' ? 'is-active' : '' }}" data-tab-panel>
                @if(!$advancedMode)
                    <article class="sv-card sv-technician-lock-compact" id="technician">
                        <div class="sv-card-body">
                            <x-feature-icon name="lock" tone="amber" :size="25" variant="soft" />
                            <div><h3>Mode Teknisi terkunci</h3><p>Verifikasi PIN untuk mengubah konfigurasi perangkat.</p></div>
                            <button type="button" class="sv-button sv-button-warning" data-dialog-open="technicianPinDialog">Verifikasi PIN</button>
                        </div>
                    </article>
                @else
                    <div class="sv-technician-toolbar" id="technician">
                        <div>
                            <strong>Mode Teknisi aktif</strong>
                            <span>Perubahan akan diterapkan pada konfigurasi SmartVolt.</span>
                        </div>
                        <div class="sv-inline">
                            <button type="button" class="sv-button sv-button-primary sv-button-sm" data-dialog-open="addRoomDialog">Tambah ruangan</button>
                            <form action="{{ route('advanced-mode.disable') }}" method="POST" data-loading-form>
                                @csrf
                                <button type="submit" class="sv-button sv-button-secondary sv-button-sm" data-loading-text="Menonaktifkan..."><span data-button-label>Nonaktifkan</span></button>
                            </form>
                        </div>
                    </div>

                    <section class="sv-technician-summary sv-technician-summary-three">
                        <article class="sv-technician-summary-card sv-technician-summary-card--violet"><x-feature-icon name="rooms" tone="violet" :size="18" variant="soft" /><span>Ruangan</span><strong>{{ $roomCollection->count() }}</strong></article>
                        <article class="sv-technician-summary-card sv-technician-summary-card--green"><x-feature-icon name="sensor" tone="green" :size="18" variant="soft" /><span>Sensor</span><strong>{{ $meterCollection->count() }}</strong></article>
                        <article class="sv-technician-summary-card sv-technician-summary-card--cyan"><x-feature-icon name="devices" tone="cyan" :size="18" variant="soft" /><span>Relay</span><strong>{{ $deviceCollection->count() }}</strong></article>
                    </section>

                    @if($roomCollection->isEmpty())
                        <article class="sv-card"><div class="sv-card-body"><div class="sv-empty-state sv-empty-state-compact"><h3>Belum ada ruangan</h3><button type="button" class="sv-button sv-button-primary" data-dialog-open="addRoomDialog">Tambah ruangan</button></div></div></article>
                    @else
                        <section class="sv-tech-room-list">
                            @foreach($roomCollection as $room)
                                @php
                                    $roomMeters = collect($room->energyMeters ?? []);
                                    $roomDevices = collect($room->devices ?? []);
                                    $roomEspIds = $roomMeters->pluck('esp_unit_id')->filter()->unique()->values();
                                @endphp

                                <details class="sv-tech-room" id="room-{{ $room->id }}" {{ $selectedRoomId === (int) $room->id ? 'open' : '' }}>
                                    <summary>
                                        <span class="sv-tech-room-summary-main">
                                            <x-feature-icon name="rooms" tone="violet" :size="18" variant="soft" />
                                            <span><strong>{{ $room->name }}</strong><small>{{ $roomMeters->count() }} sensor · {{ $roomDevices->count() }} relay</small></span>
                                        </span>
                                        <x-icon name="chevron-down" :size="17" />
                                    </summary>

                                    <div class="sv-tech-room-body">
                                        <div class="sv-tech-two-column">
                                            <section class="sv-tech-panel">
                                                <header><h4>Ruangan</h4></header>
                                                <form action="{{ route('rooms.update', $room) }}" method="POST" class="sv-stack" data-loading-form>
                                                    @csrf
                                                    @method('PUT')
                                                    <input type="hidden" name="return_to" value="settings">
                                                    <div class="sv-form-field"><label class="sv-form-label">Nama ruangan</label><input type="text" name="name" value="{{ $room->name }}" class="sv-form-control" maxlength="100" required></div>
                                                    <div class="sv-inline sv-tech-actions-row">
                                                        <button type="submit" class="sv-button sv-button-secondary sv-button-sm" data-loading-text="Menyimpan..."><span data-button-label>Simpan</span></button>
                                                    </div>
                                                </form>
                                                <form action="{{ route('rooms.destroy', $room) }}" method="POST" data-confirm="Hapus ruangan {{ $room->name }} beserta relay di dalamnya?">
                                                    @csrf
                                                    @method('DELETE')
                                                    <input type="hidden" name="return_to" value="settings">
                                                    <button type="submit" class="sv-text-danger">Hapus ruangan</button>
                                                </form>
                                            </section>

                                            <section class="sv-tech-panel">
                                                <header><h4>Tambah Unit SmartVolt</h4></header>
                                                <form action="{{ route('technician.rooms.sensor.store', $room) }}" method="POST" class="sv-form-grid-two" data-loading-form data-sensor-form>
                                                    @csrf
                                                    <input type="hidden" name="return_to" value="settings">
                                                    <div class="sv-form-field"><label class="sv-form-label">Nama sensor</label><input type="text" name="sensor_name" class="sv-form-control" placeholder="Meter utama" maxlength="100" required></div>
                                                    <div class="sv-form-field"><label class="sv-form-label">ESP Unit ID</label><input type="text" name="esp_unit_id" class="sv-form-control" placeholder="2" maxlength="100" required></div>
                                                    <div class="sv-form-field"><label class="sv-form-label">Meter code</label><input type="text" name="meter_code" class="sv-form-control" placeholder="main" maxlength="50" required></div>
                                                    <div class="sv-form-field"><label class="sv-form-label">Jumlah relay</label><select name="relay_count" class="sv-form-control" data-relay-count required>@for($i = 1; $i <= 8; $i++)<option value="{{ $i }}">{{ $i }} relay</option>@endfor</select></div>
                                                    <input type="hidden" name="sensor_type" value="PZEM004T">
                                                    <div class="sv-form-actions sv-grid-full"><button type="submit" class="sv-button sv-button-primary sv-button-sm" data-loading-text="Menambahkan..."><span data-button-label>Tambah unit</span></button></div>
                                                </form>
                                            </section>
                                        </div>

                                        @if($roomEspIds->isNotEmpty())
                                            <details class="sv-tech-inline-details">
                                                <summary>Tambah relay ke unit yang sudah ada</summary>
                                                <form action="{{ route('technician.rooms.relay.store', $room) }}" method="POST" class="sv-form-grid-two" data-loading-form>
                                                    @csrf
                                                    <input type="hidden" name="return_to" value="settings">
                                                    <div class="sv-form-field"><label class="sv-form-label">ESP Unit ID</label><select name="esp_unit_id" class="sv-form-control" required>@foreach($roomEspIds as $espId)<option value="{{ $espId }}">{{ $espId }}</option>@endforeach</select></div>
                                                    <div class="sv-form-field"><label class="sv-form-label">Relay channel</label><input type="text" name="relay_code" class="sv-form-control" placeholder="3" maxlength="50" required></div>
                                                    <div class="sv-form-field"><label class="sv-form-label">Nama perangkat</label><input type="text" name="name" class="sv-form-control" placeholder="Lampu meja" maxlength="100" required></div>
                                                    <div class="sv-form-actions"><button type="submit" class="sv-button sv-button-secondary sv-button-sm" data-loading-text="Menambahkan..."><span data-button-label>Tambah relay</span></button></div>
                                                </form>
                                            </details>
                                        @endif

                                        <div class="sv-tech-two-column sv-tech-inventory-grid">
                                            <section class="sv-tech-panel">
                                                <header><h4>Sensor Listrik</h4><span class="sv-badge sv-badge-neutral">{{ $roomMeters->count() }}</span></header>
                                                <div class="sv-tech-item-list">
                                                    @forelse($roomMeters as $meter)
                                                        <article class="sv-tech-item">
                                                            <div class="sv-tech-item-head">
                                                                <div class="sv-tech-item-copy"><strong>{{ $meter->name }}</strong><span>ESP {{ $meter->esp_unit_id }} · {{ $meter->meter_code }}</span></div>
                                                                <span class="sv-badge {{ $meter->is_active ? 'sv-badge-success' : 'sv-badge-neutral' }}">{{ $meter->is_active ? 'Aktif' : 'Nonaktif' }}</span>
                                                            </div>
                                                            <div class="sv-tech-actions-row">
                                                                <form action="{{ route('technician.sensors.toggle', $meter) }}" method="POST" data-loading-form>@csrf @method('PATCH')<button type="submit" class="sv-button sv-button-secondary sv-button-sm" data-loading-text="Memproses..."><span data-button-label>{{ $meter->is_active ? 'Nonaktifkan' : 'Aktifkan' }}</span></button></form>
                                                                <details class="sv-tech-edit"><summary>Ubah</summary><div class="sv-tech-edit-body"><form action="{{ route('technician.sensors.update', $meter) }}" method="POST" class="sv-form-grid-two" data-loading-form>@csrf @method('PUT')<div class="sv-form-field"><label class="sv-form-label">Nama sensor</label><input type="text" name="name" value="{{ $meter->name }}" class="sv-form-control" maxlength="100" required></div><div class="sv-form-field"><label class="sv-form-label">Meter code</label><input type="text" name="meter_code" value="{{ $meter->meter_code }}" class="sv-form-control" maxlength="50" required></div><input type="hidden" name="sensor_type" value="{{ $meter->sensor_type ?: 'PZEM004T' }}"><label class="sv-checkbox"><input type="checkbox" name="is_active" value="1" {{ $meter->is_active ? 'checked' : '' }}><span></span><em>Sensor aktif</em></label><div class="sv-form-actions"><button type="submit" class="sv-button sv-button-primary sv-button-sm" data-loading-text="Menyimpan..."><span data-button-label>Simpan</span></button></div></form></div></details>
                                                                <form action="{{ route('technician.sensors.destroy', $meter) }}" method="POST" data-confirm="Hapus sensor {{ $meter->name }}?">@csrf @method('DELETE')<button type="submit" class="sv-text-danger">Hapus</button></form>
                                                            </div>
                                                        </article>
                                                    @empty
                                                        <div class="sv-empty-inline">Belum ada sensor.</div>
                                                    @endforelse
                                                </div>
                                            </section>

                                            <section class="sv-tech-panel">
                                                <header><h4>Relay dan Perangkat</h4><span class="sv-badge sv-badge-neutral">{{ $roomDevices->count() }}</span></header>
                                                <div class="sv-tech-item-list">
                                                    @forelse($roomDevices as $device)
                                                        <article class="sv-tech-item">
                                                            <div class="sv-tech-item-head">
                                                                <div class="sv-tech-item-copy"><strong>{{ $device->name }}</strong><span>ESP {{ $device->esp_unit_id ?: $device->esp32_device_id ?: '-' }} · Relay {{ $device->relay_code ?: '-' }}</span></div>
                                                                <span class="sv-badge {{ $device->status ? 'sv-badge-success' : 'sv-badge-neutral' }}">{{ $device->status ? 'Nyala' : 'Mati' }}</span>
                                                            </div>
                                                            <div class="sv-tech-actions-row">
                                                                <details class="sv-tech-edit"><summary>Ubah</summary><div class="sv-tech-edit-body"><form action="{{ route('devices.update', $device) }}" method="POST" class="sv-form-grid-two" data-loading-form>@csrf @method('PUT')<input type="hidden" name="return_to" value="settings"><div class="sv-form-field"><label class="sv-form-label">Nama perangkat</label><input type="text" name="name" value="{{ $device->name }}" class="sv-form-control" maxlength="100" required></div><div class="sv-form-field"><label class="sv-form-label">ESP Unit ID</label><input type="text" name="esp_unit_id" value="{{ $device->esp_unit_id ?: $device->esp32_device_id }}" class="sv-form-control" maxlength="100" required></div><div class="sv-form-field"><label class="sv-form-label">Relay channel</label><input type="text" name="relay_code" value="{{ $device->relay_code }}" class="sv-form-control" maxlength="100" required></div><div class="sv-form-field"><label class="sv-form-label">Device key</label><input type="text" name="device_key" value="{{ $device->device_key }}" class="sv-form-control" maxlength="100"></div><div class="sv-form-actions sv-grid-full"><button type="submit" class="sv-button sv-button-primary sv-button-sm" data-loading-text="Menyimpan..."><span data-button-label>Simpan</span></button></div></form></div></details>
                                                                <form action="{{ route('devices.destroy', $device) }}" method="POST" data-confirm="Hapus relay {{ $device->name }}?">@csrf @method('DELETE')<input type="hidden" name="return_to" value="settings"><button type="submit" class="sv-text-danger">Hapus</button></form>
                                                            </div>
                                                        </article>
                                                    @empty
                                                        <div class="sv-empty-inline">Belum ada relay.</div>
                                                    @endforelse
                                                </div>
                                            </section>
                                        </div>
                                    </div>
                                </details>
                            @endforeach
                        </section>
                    @endif
                @endif
            </section>
        </div>
    </div>

    <dialog class="sv-dialog" id="technicianPinDialog" {{ $errors->has('advanced_mode') ? 'data-auto-open-dialog=technicianPinDialog' : '' }}>
        <header class="sv-dialog-header">
            <div><h3>Verifikasi PIN Teknisi</h3><p>Masukkan PIN untuk membuka konfigurasi.</p></div>
            <button type="button" class="sv-dialog-close" data-dialog-close aria-label="Tutup"><x-icon name="close" :size="18" /></button>
        </header>
        <div class="sv-dialog-body">
            <form action="{{ route('advanced-mode.enable') }}" method="POST" data-loading-form>
                @csrf
                <div class="sv-form-field">
                    <label for="technician_pin" class="sv-form-label">PIN Teknisi</label>
                    <div class="sv-password-wrap">
                        <input type="password" id="technician_pin" name="pin" class="sv-form-control @error('advanced_mode') is-invalid @enderror" inputmode="numeric" autocomplete="off" required autofocus>
                        <button type="button" class="sv-password-toggle" data-password-toggle="technician_pin" aria-label="Tampilkan PIN"><x-icon name="eye" :size="18" /></button>
                    </div>
                    @error('advanced_mode')<p class="sv-form-error">{{ $message }}</p>@enderror
                </div>
                <div class="sv-dialog-actions"><button type="button" class="sv-button sv-button-secondary" data-dialog-close>Batal</button><button type="submit" class="sv-button sv-button-warning" data-loading-text="Memverifikasi..."><span class="sv-button-spinner"></span><span data-button-label>Verifikasi</span></button></div>
            </form>
        </div>
    </dialog>

    <dialog class="sv-dialog" id="addRoomDialog">
        <header class="sv-dialog-header">
            <div><h3>Tambah Ruangan</h3><p>Masukkan nama ruangan.</p></div>
            <button type="button" class="sv-dialog-close" data-dialog-close aria-label="Tutup"><x-icon name="close" :size="18" /></button>
        </header>
        <div class="sv-dialog-body">
            <form action="{{ route('rooms.store') }}" method="POST" data-loading-form>
                @csrf
                <input type="hidden" name="return_to" value="settings">
                <div class="sv-form-field"><label for="new_room_name" class="sv-form-label">Nama ruangan</label><input type="text" id="new_room_name" name="name" class="sv-form-control" maxlength="100" placeholder="Contoh: Ruang Tamu" required></div>
                <div class="sv-dialog-actions"><button type="button" class="sv-button sv-button-secondary" data-dialog-close>Batal</button><button type="submit" class="sv-button sv-button-primary" data-loading-text="Menyimpan..."><span class="sv-button-spinner"></span><span data-button-label>Simpan ruangan</span></button></div>
            </form>
        </div>
    </dialog>
@endsection

@push('scripts')
    <script src="{{ asset('assets/js/smartvolt-settings.js') }}?v=20260731-v8" defer></script>
@endpush
