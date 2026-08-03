@extends('layouts.app')

@section('title', 'Mode Teknisi')
@section('page-title', 'Mode Teknisi')
@section('page-subtitle', 'Kelola ruangan, meter listrik, dan perangkat.')
@section('body-class', 'sv-settings-page sv-technician-page')

@php
    $advancedMode = (bool) session('advanced_mode');
    $technicianErrors = $errors->getBag('technician');
    $roomCollection = collect($rooms ?? []);
    $meterCollection = collect($energyMeters ?? []);
    $deviceCollection = collect($devices ?? []);
    $selectedRoomId = (int) (session('selected_room_id') ?? 0);
@endphp

@section('system-status')
    <span class="sv-badge {{ ($systemConnected ?? false) ? 'sv-badge-success' : 'sv-badge-neutral' }}">
        <span aria-hidden="true">●</span>
        {{ ($systemConnected ?? false) ? 'Terhubung' : 'Belum terhubung' }}
    </span>
@endsection

@section('content')
    <div class="sv-technician-shell">
                @if(!$advancedMode)
                    <div class="sv-technician-lock" id="technician">
                        <div>
                            <span class="sv-technician-lock-icon"><x-icon name="lock" :size="28" /></span>
                            <h3>Mode Teknisi terkunci</h3>
                            <p>Masukkan PIN untuk membuka pengaturan ruangan, meter listrik, dan perangkat.</p>
                            <button type="button" class="sv-button sv-button-warning" data-dialog-open="technicianPinDialog">
                                <x-icon name="lock" :size="17" /> Verifikasi PIN
                            </button>
                        </div>
                    </div>
                @else
                    <div class="sv-technician-toolbar" id="technician">
                        <div>
                            <strong>Mode Teknisi aktif</strong>
                            <span>Anda dapat mengatur ruangan, meter listrik, dan perangkat. Setelah keluar, akses tetap tersedia selama 1 menit.</span>
                        </div>
                        <form action="{{ route('advanced-mode.disable') }}" method="POST" data-loading-form>
                            @csrf
                            <button type="submit" class="sv-button sv-button-secondary sv-button-sm" data-loading-text="Menonaktifkan...">
                                <span class="sv-button-spinner"></span><span data-button-label>Akhiri Mode Teknisi</span>
                            </button>
                        </form>
                    </div>

                    <div class="sv-technician-summary">
                        <article class="sv-technician-summary-card sv-technician-summary-card--violet"><x-feature-icon name="rooms" tone="violet" :size="18" variant="flat" /><span>Ruangan</span><strong>{{ $roomCollection->count() }}</strong></article>
                        <article class="sv-technician-summary-card sv-technician-summary-card--green"><x-feature-icon name="energy" tone="green" :size="18" variant="flat" /><span>Meter Listrik</span><strong>{{ $meterCollection->count() }}</strong></article>
                        <article class="sv-technician-summary-card sv-technician-summary-card--cyan"><x-feature-icon name="plug" tone="cyan" :size="18" variant="flat" /><span>Perangkat</span><strong>{{ $deviceCollection->count() }}</strong></article>
                    </div>

                    <article class="sv-card sv-settings-card">
                        <header class="sv-card-header">
                            <div>
                                <h2>Pengaturan Ruangan dan Perangkat</h2>
                                <p>Kelola ruangan, meter listrik, dan perangkat yang sudah terdaftar.</p>
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
                                    <p>Tambahkan ruangan untuk mengelompokkan meter listrik dan perangkat.</p>
                                    <button type="button" class="sv-button sv-button-primary" data-dialog-open="addRoomDialog">Tambah ruangan</button>
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
                                                    <strong>{{ \Illuminate\Support\Str::title($room->name) }}</strong>
                                                    <span>
                                                        @if($roomMeters->isEmpty() && $roomDevices->isEmpty())
                                                            Belum ada meter listrik atau perangkat
                                                        @else
                                                            {{ $roomMeters->count() }} meter listrik · {{ $roomDevices->count() }} perangkat
                                                        @endif
                                                    </span>
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
                                                    <input type="hidden" name="return_to" value="technician">
                                                    <div class="sv-form-field">
                                                        <label for="room_name_{{ $room->id }}" class="sv-form-label">Nama ruangan</label>
                                                        <input type="text" id="room_name_{{ $room->id }}" name="name" class="sv-form-control" value="{{ $room->name }}" maxlength="100" required>
                                                    </div>
                                                    <div class="sv-form-actions">
                                                        <button type="submit" class="sv-button sv-button-secondary sv-button-sm" data-loading-text="Menyimpan..."><span data-button-label>Simpan nama</span></button>
                                                    </div>
                                                </form>
                                                <form action="{{ route('rooms.destroy', $room) }}" method="POST" data-confirm="Hapus ruangan {{ $room->name }}? Perangkat akan dihapus dan meter yang memiliki riwayat akan dinonaktifkan." style="margin-top: 10px;">
                                                    @csrf
                                                    @method('DELETE')
                                                    <input type="hidden" name="return_to" value="technician">
                                                    <button type="submit" class="sv-button sv-button-danger sv-button-sm"><x-icon name="trash" :size="15" /> Hapus ruangan</button>
                                                </form>
                                            </section>

                                            <section class="sv-tech-subsection">
                                                <div class="sv-tech-subsection-head">
                                                    <h4>Tambah meter listrik dan perangkat</h4>
                                                </div>
                                                <form action="{{ route('technician.rooms.sensor.store', $room) }}" method="POST" class="sv-stack" data-loading-form data-sensor-form>
                                                    @csrf
                                                    <input type="hidden" name="return_to" value="technician">
                                                    <div class="sv-form-grid-three">
                                                        <div class="sv-form-field">
                                                            <label class="sv-form-label" for="sensor_name_{{ $room->id }}">Nama meter listrik</label>
                                                            <input type="text" id="sensor_name_{{ $room->id }}" name="sensor_name" class="sv-form-control" placeholder="Meter {{ $room->name }}" maxlength="100" required>
                                                        </div>
                                                        <div class="sv-form-field">
                                                            <label class="sv-form-label" for="esp_unit_{{ $room->id }}">Kode unit</label>
                                                            <input type="text" id="esp_unit_{{ $room->id }}" name="esp_unit_id" class="sv-form-control" placeholder="2" maxlength="100" required>
                                                        </div>
                                                        <div class="sv-form-field">
                                                            <label class="sv-form-label" for="meter_code_{{ $room->id }}">Kode meter</label>
                                                            <input type="text" id="meter_code_{{ $room->id }}" name="meter_code" class="sv-form-control" value="main" maxlength="50" required>
                                                        </div>
                                                        <div class="sv-form-field">
                                                            <label class="sv-form-label" for="sensor_type_{{ $room->id }}">Jenis meter</label>
                                                            <input type="text" id="sensor_type_{{ $room->id }}" name="sensor_type" class="sv-form-control" value="PZEM004T" maxlength="50">
                                                        </div>
                                                        <div class="sv-form-field">
                                                            <label class="sv-form-label" for="relay_count_{{ $room->id }}">Jumlah perangkat</label>
                                                            <select id="relay_count_{{ $room->id }}" name="relay_count" class="sv-form-control" data-relay-count required>
                                                                @for($relayCount = 1; $relayCount <= 8; $relayCount++)
                                                                    <option value="{{ $relayCount }}" {{ $relayCount === 2 ? 'selected' : '' }}>{{ $relayCount }} perangkat</option>
                                                                @endfor
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <div class="sv-relay-name-fields" data-relay-names>
                                                        @for($relayIndex = 1; $relayIndex <= 8; $relayIndex++)
                                                            <div class="sv-form-field" data-relay-name-item="{{ $relayIndex }}" {{ $relayIndex > 2 ? 'hidden' : '' }}>
                                                                <label class="sv-form-label">Nama perangkat {{ $relayIndex }}</label>
                                                                <input type="text" name="relay_names[{{ $relayIndex }}]" class="sv-form-control" placeholder="Perangkat {{ $relayIndex }} {{ $room->name }}" maxlength="100" {{ $relayIndex <= 2 ? 'required' : '' }}>
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
                                                    <div class="sv-tech-subsection-head"><h4>Tambah perangkat pada unit yang sudah ada</h4></div>
                                                    <form action="{{ route('technician.rooms.relay.store', $room) }}" method="POST" class="sv-form-grid-three" data-loading-form>
                                                        @csrf
                                                        <input type="hidden" name="return_to" value="technician">
                                                        <div class="sv-form-field">
                                                            <label class="sv-form-label">Kode unit</label>
                                                            <select name="esp_unit_id" class="sv-form-control" required>
                                                                @foreach($roomEspIds as $espId)<option value="{{ $espId }}">{{ $espId }}</option>@endforeach
                                                            </select>
                                                        </div>
                                                        <div class="sv-form-field">
                                                            <label class="sv-form-label">Kode saluran</label>
                                                            <input type="text" name="relay_code" class="sv-form-control" placeholder="3" maxlength="50" required>
                                                        </div>
                                                        <div class="sv-form-field">
                                                            <label class="sv-form-label">Nama perangkat</label>
                                                            <input type="text" name="name" class="sv-form-control" placeholder="Lampu meja" maxlength="100" required>
                                                        </div>
                                                        <div class="sv-form-actions">
                                                            <button type="submit" class="sv-button sv-button-secondary" data-loading-text="Menambahkan..."><span data-button-label>Tambah perangkat</span></button>
                                                        </div>
                                                    </form>
                                                </section>
                                            @endif

                                            <section class="sv-tech-subsection">
                                                <div class="sv-tech-subsection-head"><h4>Meter listrik</h4></div>
                                                <div class="sv-tech-item-list">
                                                    @forelse($roomMeters as $meter)
                                                        <article class="sv-tech-item">
                                                            <div class="sv-tech-item-head">
                                                                <div class="sv-tech-item-copy">
                                                                    <strong>{{ $meter->name }}</strong>
                                                                    <span>Unit {{ $meter->esp_unit_id }} · Kode {{ $meter->meter_code }} · {{ $meter->readings_count ?? 0 }} pembacaan</span>
                                                                </div>
                                                                <span class="sv-badge {{ $meter->is_active ? 'sv-badge-success' : 'sv-badge-neutral' }}">{{ $meter->is_active ? 'Aktif' : 'Nonaktif' }}</span>
                                                            </div>

                                                            <div class="sv-tech-actions" style="margin-top: 10px;">
                                                                <form action="{{ route('technician.sensors.toggle', $meter) }}" method="POST" data-loading-form>
                                                                    @csrf
                                                                    @method('PATCH')
                                                                    <button type="submit" class="sv-button sv-button-secondary sv-button-sm" data-loading-text="Memproses..."><span data-button-label>{{ $meter->is_active ? 'Nonaktifkan' : 'Aktifkan' }}</span></button>
                                                                </form>
                                                                <form action="{{ route('technician.sensors.destroy', $meter) }}" method="POST" data-confirm="Hapus meter {{ $meter->name }}? Meter yang memiliki riwayat akan dinonaktifkan agar data lama tetap tersimpan.">
                                                                    @csrf
                                                                    @method('DELETE')
                                                                    <button type="submit" class="sv-button sv-button-danger sv-button-sm"><x-icon name="trash" :size="14" /> Hapus</button>
                                                                </form>
                                                            </div>

                                                            <details class="sv-tech-edit">
                                                                <summary><x-icon name="edit" :size="14" /> Ubah meter listrik</summary>
                                                                <div class="sv-tech-edit-body">
                                                                    <form action="{{ route('technician.sensors.update', $meter) }}" method="POST" class="sv-form-grid-three" data-loading-form>
                                                                        @csrf
                                                                        @method('PUT')
                                                                        <div class="sv-form-field"><label class="sv-form-label">Nama meter listrik</label><input type="text" name="name" value="{{ $meter->name }}" class="sv-form-control" maxlength="100" required></div>
                                                                        <div class="sv-form-field"><label class="sv-form-label">Kode meter</label><input type="text" name="meter_code" value="{{ $meter->meter_code }}" class="sv-form-control" maxlength="50" required></div>
                                                                        <div class="sv-form-field"><label class="sv-form-label">Jenis meter</label><input type="text" name="sensor_type" value="{{ $meter->sensor_type ?: 'PZEM004T' }}" class="sv-form-control" maxlength="50"></div>
                                                                        <label class="sv-checkbox"><input type="checkbox" name="is_active" value="1" {{ $meter->is_active ? 'checked' : '' }}><span></span><em>Meter aktif</em></label>
                                                                        <div class="sv-form-actions"><button type="submit" class="sv-button sv-button-primary sv-button-sm" data-loading-text="Menyimpan..."><span data-button-label>Simpan meter</span></button></div>
                                                                    </form>
                                                                </div>
                                                            </details>
                                                        </article>
                                                    @empty
                                                        <div class="sv-empty-state"><p>Belum ada meter listrik pada ruangan ini.</p></div>
                                                    @endforelse
                                                </div>
                                            </section>

                                            <section class="sv-tech-subsection">
                                                <div class="sv-tech-subsection-head"><h4>Perangkat terdaftar</h4></div>
                                                <div class="sv-tech-item-list">
                                                    @forelse($roomDevices as $device)
                                                        <article class="sv-tech-item">
                                                            <div class="sv-tech-item-head">
                                                                <div class="sv-tech-item-copy">
                                                                    <strong>{{ $device->name }}</strong>
                                                                    <span>Kode unit {{ $device->esp_unit_id ?: $device->esp32_device_id ?: '-' }} · Saluran {{ $device->relay_code ?: '-' }}</span>
                                                                </div>
                                                                <span class="sv-badge {{ $device->status ? 'sv-badge-success' : 'sv-badge-neutral' }}">{{ $device->status ? 'Nyala' : 'Mati' }}</span>
                                                            </div>

                                                            <div class="sv-tech-actions" style="margin-top: 10px;">
                                                                <form action="{{ route('devices.destroy', $device) }}" method="POST" data-confirm="Hapus perangkat {{ $device->name }} dari ruangan ini?">
                                                                    @csrf
                                                                    @method('DELETE')
                                                                    <input type="hidden" name="return_to" value="technician">
                                                                    <button type="submit" class="sv-button sv-button-danger sv-button-sm"><x-icon name="trash" :size="14" /> Hapus</button>
                                                                </form>
                                                            </div>

                                                            <details class="sv-tech-edit">
                                                                <summary><x-icon name="edit" :size="14" /> Ubah perangkat</summary>
                                                                <div class="sv-tech-edit-body">
                                                                    <form action="{{ route('devices.update', $device) }}" method="POST" class="sv-form-grid-three" data-loading-form>
                                                                        @csrf
                                                                        @method('PUT')
                                                                        <input type="hidden" name="return_to" value="technician">
                                                                        <div class="sv-form-field"><label class="sv-form-label">Nama perangkat</label><input type="text" name="name" value="{{ $device->name }}" class="sv-form-control" maxlength="100" required></div>
                                                                        <div class="sv-form-field"><label class="sv-form-label">Kode unit</label><input type="text" name="esp_unit_id" value="{{ $device->esp_unit_id ?: $device->esp32_device_id }}" class="sv-form-control" maxlength="100" required></div>
                                                                        <div class="sv-form-field"><label class="sv-form-label">Kode saluran</label><input type="text" name="relay_code" value="{{ $device->relay_code }}" class="sv-form-control" maxlength="100" required></div>
                                                                        <div class="sv-form-field"><label class="sv-form-label">Device key (opsional)</label><input type="text" name="device_key" value="{{ $device->device_key }}" class="sv-form-control" maxlength="100"></div>
                                                                        <div class="sv-form-actions"><button type="submit" class="sv-button sv-button-primary sv-button-sm" data-loading-text="Menyimpan..."><span data-button-label>Simpan perangkat</span></button></div>
                                                                    </form>
                                                                </div>
                                                            </details>
                                                        </article>
                                                    @empty
                                                        <div class="sv-empty-state"><p>Belum ada perangkat pada ruangan ini.</p></div>
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

    </div>

    @if($advancedMode)
        <span
            hidden
            data-advanced-mode-tracker
            data-leave-url="{{ route('advanced-mode.leave') }}"
            data-visit-token="{{ (string) session('advanced_mode_visit_token') }}"
            data-csrf-token="{{ csrf_token() }}"
        ></span>
    @endif

    <dialog class="sv-dialog" id="technicianPinDialog" {{ $technicianErrors->has('advanced_mode') ? 'data-auto-open-dialog=technicianPinDialog' : '' }}>
        <header class="sv-dialog-header">
            <div><h3>Verifikasi PIN Teknisi</h3><p>Masukkan PIN untuk membuka pengaturan teknis.</p></div>
            <button type="button" class="sv-dialog-close" data-dialog-close aria-label="Tutup"><x-icon name="close" :size="18" /></button>
        </header>
        <div class="sv-dialog-body">
            <form action="{{ route('advanced-mode.enable') }}" method="POST" data-loading-form>
                @csrf
                <div class="sv-form-field">
                    <label for="technician_pin" class="sv-form-label">PIN Teknisi</label>
                    <div class="sv-password-wrap">
                        <input type="password" id="technician_pin" name="pin" class="sv-form-control @error('advanced_mode', 'technician') is-invalid @enderror" inputmode="numeric" autocomplete="off" required autofocus>
                        <button type="button" class="sv-password-toggle" data-password-toggle="technician_pin" aria-label="Tampilkan PIN"><x-icon name="eye" :size="18" /></button>
                    </div>
                    @error('advanced_mode', 'technician')<p class="sv-form-error">{{ $message }}</p>@enderror
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
            <div><h3>Tambah ruangan</h3><p>Ruangan digunakan untuk mengelompokkan meter listrik dan perangkat.</p></div>
            <button type="button" class="sv-dialog-close" data-dialog-close aria-label="Tutup"><x-icon name="close" :size="18" /></button>
        </header>
        <div class="sv-dialog-body">
            <form action="{{ route('rooms.store') }}" method="POST" data-loading-form>
                @csrf
                <input type="hidden" name="return_to" value="technician">
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

@push('styles')
    <link
        rel="stylesheet"
        href="{{ asset('assets/css/smartvolt-technician.css') }}?v=20260803-1"
    >
@endpush

@push('scripts')
    <script
        src="{{ asset('assets/js/smartvolt-technician.js') }}?v=20260803-2"
        defer
    ></script>
@endpush
