@extends('layouts.app')

@section('title', 'Pengaturan')
@section('page-title', request('tab') === 'technician' ? 'Mode Teknisi' : 'Pengaturan')
@section('page-subtitle', request('tab') === 'technician' ? 'Konfigurasi ruangan, ESP32, sensor PZEM, dan relay.' : 'Kelola akun, keamanan, tarif listrik, dan preferensi sistem.')
@section('body-class', 'sv-settings-page')

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
        <span aria-hidden="true">●</span>
        {{ $advancedMode ? 'Mode Teknisi aktif' : 'Mode pengguna' }}
    </span>
@endsection

@section('content')
    <div class="sv-settings-layout">
        <nav class="sv-settings-nav" data-tabs data-panels-root="settingsPanels" aria-label="Bagian pengaturan">
            <button type="button" class="sv-tab-button {{ $activeTab === 'profile' ? 'is-active' : '' }}" data-tab-target="tab-profile" data-tab-name="profile" aria-selected="{{ $activeTab === 'profile' ? 'true' : 'false' }}">
                <x-icon name="user" :size="17" /> Profil
            </button>
            <button type="button" class="sv-tab-button {{ $activeTab === 'security' ? 'is-active' : '' }}" data-tab-target="tab-security" data-tab-name="security" aria-selected="{{ $activeTab === 'security' ? 'true' : 'false' }}">
                <x-icon name="shield" :size="17" /> Keamanan
            </button>
            <button type="button" class="sv-tab-button {{ $activeTab === 'system' ? 'is-active' : '' }}" data-tab-target="tab-system" data-tab-name="system" aria-selected="{{ $activeTab === 'system' ? 'true' : 'false' }}">
                <x-icon name="energy" :size="17" /> Energi & Sistem
            </button>
            <button type="button" class="sv-tab-button {{ $activeTab === 'technician' ? 'is-active' : '' }}" data-tab-target="tab-technician" data-tab-name="technician" aria-selected="{{ $activeTab === 'technician' ? 'true' : 'false' }}">
                <x-icon name="tools" :size="17" /> Mode Teknisi
            </button>
        </nav>

        <div class="sv-settings-panels" id="settingsPanels">
            <section id="tab-profile" class="sv-tab-panel {{ $activeTab === 'profile' ? 'is-active' : '' }}" data-tab-panel>
                <article class="sv-card sv-settings-card">
                    <header class="sv-profile-summary">
                        <span class="sv-profile-avatar-large">{{ strtoupper(substr($user->name ?? 'U', 0, 1)) }}</span>
                        <div>
                            <h3>{{ $user->name ?? 'Pengguna SmartVolt' }}</h3>
                            <p>{{ $user->email ?? '-' }} · Akun pengguna SmartVolt</p>
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
                <article class="sv-card sv-settings-card">
                    <header class="sv-card-header">
                        <div>
                            <h2>Keamanan Akun</h2>
                            <p>Perbarui kata sandi akun SmartVolt.</p>
                        </div>
                    </header>
                    <div class="sv-card-body">
                        <div class="sv-security-note">
                            <span class="sv-security-note-icon"><x-icon name="shield" :size="19" /></span>
                            <div>
                                <strong>Masukkan kata sandi saat ini untuk melanjutkan</strong>
                                <p>Gunakan kata sandi baru minimal delapan karakter.</p>
                            </div>
                        </div>

                        <form action="{{ route('settings.password.update') }}" method="POST" class="sv-stack" data-loading-form style="margin-top: 16px;">
                            @csrf
                            @method('PUT')

                            <div class="sv-form-field">
                                <label for="current_password" class="sv-form-label">Kata sandi saat ini</label>
                                <div class="sv-password-wrap">
                                    <input type="password" id="current_password" name="current_password" class="sv-form-control @error('current_password') is-invalid @enderror" autocomplete="current-password" required>
                                    <button type="button" class="sv-password-toggle" data-password-toggle="current_password" aria-label="Tampilkan kata sandi"><x-icon name="eye" :size="18" /></button>
                                </div>
                                @error('current_password')<p class="sv-form-error">{{ $message }}</p>@enderror
                            </div>

                            <div class="sv-form-grid">
                                <div class="sv-form-field">
                                    <label for="new_password" class="sv-form-label">Kata sandi baru</label>
                                    <div class="sv-password-wrap">
                                        <input type="password" id="new_password" name="password" class="sv-form-control @error('password') is-invalid @enderror" minlength="8" autocomplete="new-password" required>
                                        <button type="button" class="sv-password-toggle" data-password-toggle="new_password" aria-label="Tampilkan kata sandi"><x-icon name="eye" :size="18" /></button>
                                    </div>
                                    @error('password')<p class="sv-form-error">{{ $message }}</p>@enderror
                                </div>
                                <div class="sv-form-field">
                                    <label for="new_password_confirmation" class="sv-form-label">Konfirmasi kata sandi</label>
                                    <div class="sv-password-wrap">
                                        <input type="password" id="new_password_confirmation" name="password_confirmation" class="sv-form-control" minlength="8" autocomplete="new-password" required>
                                        <button type="button" class="sv-password-toggle" data-password-toggle="new_password_confirmation" aria-label="Tampilkan kata sandi"><x-icon name="eye" :size="18" /></button>
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
                <article class="sv-card sv-settings-card">
                    <header class="sv-card-header">
                        <div>
                            <h2>Energi dan Sistem</h2>
                            <p>Atur tarif listrik, batas daya, dan interval pembaruan.</p>
                        </div>
                        @if(!$advancedMode)
                            <span class="sv-badge sv-badge-warning"><x-icon name="lock" :size="14" /> Terkunci</span>
                        @endif
                    </header>
                    <div class="sv-card-body">
                        @if(!$advancedMode)
                            <div class="sv-security-note">
                                <span class="sv-security-note-icon"><x-icon name="lock" :size="19" /></span>
                                <div>
                                    <strong>Aktifkan Mode Teknisi untuk mengubah konfigurasi sistem</strong>
                                    <p>Nilai tetap dapat dilihat, tetapi perubahan tarif, batas daya, dan interval refresh dibatasi dengan PIN teknisi.</p>
                                </div>
                            </div>
                        @endif

                        <form action="{{ route('settings.system.update') }}" method="POST" class="sv-stack" data-loading-form style="margin-top: 16px;">
                            @csrf
                            @method('PUT')

                            <div class="sv-form-grid-three">
                                <div class="sv-form-field">
                                    <label for="electricity_tariff" class="sv-form-label">Tarif listrik per kWh</label>
                                    <input type="number" step="0.01" min="0" id="electricity_tariff" name="electricity_tariff" class="sv-form-control @error('electricity_tariff') is-invalid @enderror" value="{{ old('electricity_tariff', $systemSetting->electricity_tariff) }}" {{ !$advancedMode ? 'disabled' : '' }} required>
                                    <p class="sv-form-help">Contoh: 1444 untuk Rp1.444/kWh.</p>
                                    @error('electricity_tariff')<p class="sv-form-error">{{ $message }}</p>@enderror
                                </div>
                                <div class="sv-form-field">
                                    <label for="power_limit" class="sv-form-label">Batas daya rumah</label>
                                    <input type="number" min="1" id="power_limit" name="power_limit" class="sv-form-control @error('power_limit') is-invalid @enderror" value="{{ old('power_limit', $systemSetting->power_limit) }}" {{ !$advancedMode ? 'disabled' : '' }} required>
                                    <p class="sv-form-help">Satuan Watt, misalnya 900 atau 1300.</p>
                                    @error('power_limit')<p class="sv-form-error">{{ $message }}</p>@enderror
                                </div>
                                <div class="sv-form-field">
                                    <label for="refresh_interval" class="sv-form-label">Interval pembaruan</label>
                                    <input type="number" min="1" max="60" id="refresh_interval" name="refresh_interval" class="sv-form-control @error('refresh_interval') is-invalid @enderror" value="{{ old('refresh_interval', $systemSetting->refresh_interval) }}" {{ !$advancedMode ? 'disabled' : '' }} required>
                                    <p class="sv-form-help">1–60 detik. Dashboard membatasi refresh minimum yang aman.</p>
                                    @error('refresh_interval')<p class="sv-form-error">{{ $message }}</p>@enderror
                                </div>
                            </div>

                            <div class="sv-form-actions">
                                @if($advancedMode)
                                    <button type="submit" class="sv-button sv-button-primary" data-loading-text="Menyimpan...">
                                        <span class="sv-button-spinner" aria-hidden="true"></span>
                                        <span data-button-label>Simpan pengaturan sistem</span>
                                    </button>
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
                    <div class="sv-technician-lock" id="technician">
                        <div>
                            <span class="sv-technician-lock-icon"><x-icon name="lock" :size="28" /></span>
                            <h3>Akses Mode Teknisi</h3>
                            <p>Masukkan PIN untuk mengelola perangkat dan parameter teknis.</p>
                            <button type="button" class="sv-button sv-button-warning" data-dialog-open="technicianPinDialog">
                                <x-icon name="lock" :size="17" /> Verifikasi PIN
                            </button>
                        </div>
                    </div>
                @else
                    <div class="sv-technician-toolbar" id="technician">
                        <div>
                            <strong>Mode Teknisi sedang aktif</strong>
                            <span>Perubahan diterapkan pada konfigurasi SmartVolt.</span>
                        </div>
                        <form action="{{ route('advanced-mode.disable') }}" method="POST" data-loading-form>
                            @csrf
                            <button type="submit" class="sv-button sv-button-secondary sv-button-sm" data-loading-text="Menonaktifkan...">
                                <span class="sv-button-spinner"></span><span data-button-label>Nonaktifkan</span>
                            </button>
                        </form>
                    </div>

                    <div class="sv-technician-summary">
                        <article class="sv-technician-summary-card"><span>Ruangan</span><strong>{{ $roomCollection->count() }}</strong></article>
                        <article class="sv-technician-summary-card"><span>Sensor PZEM</span><strong>{{ $meterCollection->count() }}</strong></article>
                        <article class="sv-technician-summary-card"><span>Relay</span><strong>{{ $deviceCollection->count() }}</strong></article>
                    </div>

                    <article class="sv-card sv-settings-card">
                        <header class="sv-card-header">
                            <div>
                                <h2>Konfigurasi Perangkat IoT</h2>
                                <p>Kelola ruangan, sensor, dan relay.</p>
                            </div>
                            <button type="button" class="sv-button sv-button-primary sv-button-sm" data-dialog-open="addRoomDialog">
                                <x-icon name="plus" :size="16" /> Tambah ruangan
                            </button>
                        </header>
                        <div class="sv-card-body">
                            @if($roomCollection->isEmpty())
                                <div class="sv-empty-state">
                                    <x-icon name="rooms" :size="32" />
                                    <h3>Belum ada ruangan</h3>
                                    <p>Tambahkan ruangan sebagai wadah untuk sensor listrik dan relay.</p>
                                    <button type="button" class="sv-button sv-button-primary" data-dialog-open="addRoomDialog">Tambah Ruangan</button>
                                </div>
                            @else
                                @foreach($roomCollection as $room)
                                    @php
                                        $roomMeters = collect($room->energyMeters ?? []);
                                        $roomDevices = collect($room->devices ?? []);
                                        $roomEspIds = $roomMeters->pluck('esp_unit_id')->filter()->unique()->values();
                                    @endphp
                                    <details class="sv-tech-room" id="room-{{ $room->id }}" {{ $selectedRoomId === $room->id || $loop->first ? 'open' : '' }}>
                                        <summary>
                                            <span class="sv-tech-room-heading">
                                                <span class="sv-room-icon"><x-icon name="rooms" :size="18" /></span>
                                                <span>
                                                    <strong>{{ $room->name }}</strong>
                                                    <span>{{ $roomMeters->count() }} sensor · {{ $roomDevices->count() }} relay</span>
                                                </span>
                                            </span>
                                            <span class="sv-room-chevron"><x-icon name="chevron-right" :size="17" /></span>
                                        </summary>

                                        <div class="sv-tech-room-content">
                                            <section class="sv-tech-subsection">
                                                <div class="sv-tech-subsection-head">
                                                    <h4>Pengaturan ruangan</h4>
                                                </div>
                                                <form action="{{ route('rooms.update', $room) }}" method="POST" class="sv-form-grid" data-loading-form>
                                                    @csrf
                                                    @method('PUT')
                                                    <input type="hidden" name="return_to" value="settings">
                                                    <div class="sv-form-field">
                                                        <label for="room_name_{{ $room->id }}" class="sv-form-label">Nama ruangan</label>
                                                        <input type="text" id="room_name_{{ $room->id }}" name="name" class="sv-form-control" value="{{ $room->name }}" maxlength="100" required>
                                                    </div>
                                                    <div class="sv-form-actions">
                                                        <button type="submit" class="sv-button sv-button-secondary sv-button-sm" data-loading-text="Menyimpan..."><span data-button-label>Simpan nama</span></button>
                                                    </div>
                                                </form>
                                                <form action="{{ route('rooms.destroy', $room) }}" method="POST" data-confirm="Hapus ruangan {{ $room->name }}? Relay akan dihapus dan sensor yang memiliki riwayat akan dinonaktifkan." style="margin-top: 10px;">
                                                    @csrf
                                                    @method('DELETE')
                                                    <input type="hidden" name="return_to" value="settings">
                                                    <button type="submit" class="sv-button sv-button-danger sv-button-sm"><x-icon name="trash" :size="15" /> Hapus ruangan</button>
                                                </form>
                                            </section>

                                            <section class="sv-tech-subsection">
                                                <div class="sv-tech-subsection-head">
                                                    <h4>Tambah ESP32, sensor PZEM, dan relay</h4>
                                                </div>
                                                <form action="{{ route('technician.rooms.sensor.store', $room) }}" method="POST" class="sv-stack" data-loading-form data-sensor-form>
                                                    @csrf
                                                    <input type="hidden" name="return_to" value="settings">
                                                    <div class="sv-form-grid-three">
                                                        <div class="sv-form-field">
                                                            <label class="sv-form-label" for="sensor_name_{{ $room->id }}">Nama sensor</label>
                                                            <input type="text" id="sensor_name_{{ $room->id }}" name="sensor_name" class="sv-form-control" placeholder="Meter {{ $room->name }}" maxlength="100" required>
                                                        </div>
                                                        <div class="sv-form-field">
                                                            <label class="sv-form-label" for="esp_unit_{{ $room->id }}">ESP Unit ID</label>
                                                            <input type="text" id="esp_unit_{{ $room->id }}" name="esp_unit_id" class="sv-form-control" placeholder="2" maxlength="100" required>
                                                        </div>
                                                        <div class="sv-form-field">
                                                            <label class="sv-form-label" for="meter_code_{{ $room->id }}">Meter code</label>
                                                            <input type="text" id="meter_code_{{ $room->id }}" name="meter_code" class="sv-form-control" value="main" maxlength="50" required>
                                                        </div>
                                                        <div class="sv-form-field">
                                                            <label class="sv-form-label" for="sensor_type_{{ $room->id }}">Jenis sensor</label>
                                                            <input type="text" id="sensor_type_{{ $room->id }}" name="sensor_type" class="sv-form-control" value="PZEM004T" maxlength="50">
                                                        </div>
                                                        <div class="sv-form-field">
                                                            <label class="sv-form-label" for="relay_count_{{ $room->id }}">Jumlah relay</label>
                                                            <select id="relay_count_{{ $room->id }}" name="relay_count" class="sv-form-control" data-relay-count required>
                                                                @for($relayCount = 1; $relayCount <= 8; $relayCount++)
                                                                    <option value="{{ $relayCount }}" {{ $relayCount === 2 ? 'selected' : '' }}>{{ $relayCount }} relay</option>
                                                                @endfor
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <div class="sv-relay-name-fields" data-relay-names>
                                                        @for($relayIndex = 1; $relayIndex <= 8; $relayIndex++)
                                                            <div class="sv-form-field" data-relay-name-item="{{ $relayIndex }}" {{ $relayIndex > 2 ? 'hidden' : '' }}>
                                                                <label class="sv-form-label">Nama Relay {{ $relayIndex }}</label>
                                                                <input type="text" name="relay_names[{{ $relayIndex }}]" class="sv-form-control" placeholder="Relay {{ $relayIndex }} {{ $room->name }}" maxlength="100" {{ $relayIndex <= 2 ? 'required' : '' }}>
                                                            </div>
                                                        @endfor
                                                    </div>
                                                    <div class="sv-form-actions">
                                                        <button type="submit" class="sv-button sv-button-primary" data-loading-text="Menyimpan konfigurasi..."><span class="sv-button-spinner"></span><span data-button-label>Tambah konfigurasi</span></button>
                                                    </div>
                                                </form>
                                            </section>

                                            @if($roomEspIds->isNotEmpty())
                                                <section class="sv-tech-subsection">
                                                    <div class="sv-tech-subsection-head"><h4>Tambah relay ke ESP32 yang ada</h4></div>
                                                    <form action="{{ route('technician.rooms.relay.store', $room) }}" method="POST" class="sv-form-grid-three" data-loading-form>
                                                        @csrf
                                                        <input type="hidden" name="return_to" value="settings">
                                                        <div class="sv-form-field">
                                                            <label class="sv-form-label">ESP Unit ID</label>
                                                            <select name="esp_unit_id" class="sv-form-control" required>
                                                                @foreach($roomEspIds as $espId)<option value="{{ $espId }}">{{ $espId }}</option>@endforeach
                                                            </select>
                                                        </div>
                                                        <div class="sv-form-field">
                                                            <label class="sv-form-label">Relay channel</label>
                                                            <input type="text" name="relay_code" class="sv-form-control" placeholder="3" maxlength="50" required>
                                                        </div>
                                                        <div class="sv-form-field">
                                                            <label class="sv-form-label">Nama perangkat</label>
                                                            <input type="text" name="name" class="sv-form-control" placeholder="Lampu meja" maxlength="100" required>
                                                        </div>
                                                        <div class="sv-form-actions">
                                                            <button type="submit" class="sv-button sv-button-secondary" data-loading-text="Menambahkan..."><span data-button-label>Tambah relay</span></button>
                                                        </div>
                                                    </form>
                                                </section>
                                            @endif

                                            <section class="sv-tech-subsection">
                                                <div class="sv-tech-subsection-head"><h4>Sensor listrik</h4></div>
                                                <div class="sv-tech-item-list">
                                                    @forelse($roomMeters as $meter)
                                                        <article class="sv-tech-item">
                                                            <div class="sv-tech-item-head">
                                                                <div class="sv-tech-item-copy">
                                                                    <strong>{{ $meter->name }}</strong>
                                                                    <span>ESP {{ $meter->esp_unit_id }} · {{ $meter->meter_code }} · {{ $meter->sensor_type ?: 'PZEM004T' }} · {{ $meter->readings_count ?? 0 }} pembacaan</span>
                                                                </div>
                                                                <span class="sv-badge {{ $meter->is_active ? 'sv-badge-success' : 'sv-badge-neutral' }}">{{ $meter->is_active ? 'Aktif' : 'Nonaktif' }}</span>
                                                            </div>

                                                            <div class="sv-tech-actions" style="margin-top: 10px;">
                                                                <form action="{{ route('technician.sensors.toggle', $meter) }}" method="POST" data-loading-form>
                                                                    @csrf
                                                                    @method('PATCH')
                                                                    <button type="submit" class="sv-button sv-button-secondary sv-button-sm" data-loading-text="Memproses..."><span data-button-label>{{ $meter->is_active ? 'Nonaktifkan' : 'Aktifkan' }}</span></button>
                                                                </form>
                                                                <form action="{{ route('technician.sensors.destroy', $meter) }}" method="POST" data-confirm="Hapus sensor {{ $meter->name }}? Sensor yang memiliki riwayat akan dinonaktifkan, bukan dihapus permanen.">
                                                                    @csrf
                                                                    @method('DELETE')
                                                                    <button type="submit" class="sv-button sv-button-danger sv-button-sm"><x-icon name="trash" :size="14" /> Hapus</button>
                                                                </form>
                                                            </div>

                                                            <details class="sv-tech-edit">
                                                                <summary><x-icon name="edit" :size="14" /> Ubah sensor</summary>
                                                                <div class="sv-tech-edit-body">
                                                                    <form action="{{ route('technician.sensors.update', $meter) }}" method="POST" class="sv-form-grid-three" data-loading-form>
                                                                        @csrf
                                                                        @method('PUT')
                                                                        <div class="sv-form-field"><label class="sv-form-label">Nama sensor</label><input type="text" name="name" value="{{ $meter->name }}" class="sv-form-control" maxlength="100" required></div>
                                                                        <div class="sv-form-field"><label class="sv-form-label">Meter code</label><input type="text" name="meter_code" value="{{ $meter->meter_code }}" class="sv-form-control" maxlength="50" required></div>
                                                                        <div class="sv-form-field"><label class="sv-form-label">Jenis sensor</label><input type="text" name="sensor_type" value="{{ $meter->sensor_type ?: 'PZEM004T' }}" class="sv-form-control" maxlength="50"></div>
                                                                        <label class="sv-checkbox"><input type="checkbox" name="is_active" value="1" {{ $meter->is_active ? 'checked' : '' }}><span></span><em>Sensor aktif</em></label>
                                                                        <div class="sv-form-actions"><button type="submit" class="sv-button sv-button-primary sv-button-sm" data-loading-text="Menyimpan..."><span data-button-label>Simpan sensor</span></button></div>
                                                                    </form>
                                                                </div>
                                                            </details>
                                                        </article>
                                                    @empty
                                                        <div class="sv-empty-state"><p>Belum ada sensor listrik pada ruangan ini.</p></div>
                                                    @endforelse
                                                </div>
                                            </section>

                                            <section class="sv-tech-subsection">
                                                <div class="sv-tech-subsection-head"><h4>Relay dan perangkat</h4></div>
                                                <div class="sv-tech-item-list">
                                                    @forelse($roomDevices as $device)
                                                        <article class="sv-tech-item">
                                                            <div class="sv-tech-item-head">
                                                                <div class="sv-tech-item-copy">
                                                                    <strong>{{ $device->name }}</strong>
                                                                    <span>ESP {{ $device->esp_unit_id ?: $device->esp32_device_id ?: '-' }} · Relay {{ $device->relay_code ?: '-' }} · {{ $device->status ? 'Nyala' : 'Mati' }}</span>
                                                                </div>
                                                                <span class="sv-badge {{ $device->status ? 'sv-badge-success' : 'sv-badge-neutral' }}">{{ $device->status ? 'Nyala' : 'Mati' }}</span>
                                                            </div>

                                                            <div class="sv-tech-actions" style="margin-top: 10px;">
                                                                <form action="{{ route('devices.destroy', $device) }}" method="POST" data-confirm="Hapus relay {{ $device->name }} dari konfigurasi?">
                                                                    @csrf
                                                                    @method('DELETE')
                                                                    <input type="hidden" name="return_to" value="settings">
                                                                    <button type="submit" class="sv-button sv-button-danger sv-button-sm"><x-icon name="trash" :size="14" /> Hapus</button>
                                                                </form>
                                                            </div>

                                                            <details class="sv-tech-edit">
                                                                <summary><x-icon name="edit" :size="14" /> Ubah relay</summary>
                                                                <div class="sv-tech-edit-body">
                                                                    <form action="{{ route('devices.update', $device) }}" method="POST" class="sv-form-grid-three" data-loading-form>
                                                                        @csrf
                                                                        @method('PUT')
                                                                        <input type="hidden" name="return_to" value="settings">
                                                                        <div class="sv-form-field"><label class="sv-form-label">Nama perangkat</label><input type="text" name="name" value="{{ $device->name }}" class="sv-form-control" maxlength="100" required></div>
                                                                        <div class="sv-form-field"><label class="sv-form-label">ESP Unit ID</label><input type="text" name="esp_unit_id" value="{{ $device->esp_unit_id ?: $device->esp32_device_id }}" class="sv-form-control" maxlength="100" required></div>
                                                                        <div class="sv-form-field"><label class="sv-form-label">Relay channel</label><input type="text" name="relay_code" value="{{ $device->relay_code }}" class="sv-form-control" maxlength="100" required></div>
                                                                        <div class="sv-form-field"><label class="sv-form-label">Device key (opsional)</label><input type="text" name="device_key" value="{{ $device->device_key }}" class="sv-form-control" maxlength="100"></div>
                                                                        <div class="sv-form-actions"><button type="submit" class="sv-button sv-button-primary sv-button-sm" data-loading-text="Menyimpan..."><span data-button-label>Simpan relay</span></button></div>
                                                                    </form>
                                                                </div>
                                                            </details>
                                                        </article>
                                                    @empty
                                                        <div class="sv-empty-state"><p>Belum ada relay pada ruangan ini.</p></div>
                                                    @endforelse
                                                </div>
                                            </section>
                                        </div>
                                    </details>
                                @endforeach
                            @endif
                        </div>
                    </article>
                @endif
            </section>
        </div>
    </div>

    <dialog class="sv-dialog" id="technicianPinDialog" {{ $errors->has('advanced_mode') ? 'data-auto-open-dialog=technicianPinDialog' : '' }}>
        <header class="sv-dialog-header">
            <div><h3>Verifikasi PIN Teknisi</h3><p>PIN diperlukan sebelum konfigurasi perangkat dapat diubah.</p></div>
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
                <div class="sv-dialog-actions">
                    <button type="button" class="sv-button sv-button-secondary" data-dialog-close>Batal</button>
                    <button type="submit" class="sv-button sv-button-warning" data-loading-text="Memverifikasi..."><span class="sv-button-spinner"></span><span data-button-label>Verifikasi</span></button>
                </div>
            </form>
        </div>
    </dialog>

    <dialog class="sv-dialog" id="addRoomDialog">
        <header class="sv-dialog-header">
            <div><h3>Tambah Ruangan</h3><p>Ruangan menjadi kelompok untuk sensor listrik dan perangkat relay.</p></div>
            <button type="button" class="sv-dialog-close" data-dialog-close aria-label="Tutup"><x-icon name="close" :size="18" /></button>
        </header>
        <div class="sv-dialog-body">
            <form action="{{ route('rooms.store') }}" method="POST" data-loading-form>
                @csrf
                <input type="hidden" name="return_to" value="settings">
                <div class="sv-form-field">
                    <label for="new_room_name" class="sv-form-label">Nama ruangan</label>
                    <input type="text" id="new_room_name" name="name" class="sv-form-control" maxlength="100" placeholder="Contoh: Ruang Tamu" required>
                </div>
                <div class="sv-dialog-actions">
                    <button type="button" class="sv-button sv-button-secondary" data-dialog-close>Batal</button>
                    <button type="submit" class="sv-button sv-button-primary" data-loading-text="Menyimpan..."><span class="sv-button-spinner"></span><span data-button-label>Simpan ruangan</span></button>
                </div>
            </form>
        </div>
    </dialog>
@endsection

@push('scripts')
    <script src="{{ asset('assets/js/smartvolt-settings.js') }}?v=20260727" defer></script>
@endpush
