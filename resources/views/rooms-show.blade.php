<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $room->name }} - SmartVolt</title>
</head>
<body style="background:#081426; color:white; font-family:Arial; padding:24px;">

    <h1>Room: {{ $room->name }}</h1>

    <p>
        <a href="{{ route('rooms') }}" style="color:#7dd3fc;">
            Kembali ke Rooms
        </a>
    </p>

    @if(session('status'))
        <div style="margin:12px 0; color:#86efac;">
            {{ session('status') }}
        </div>
    @endif

    @if($errors->any())
        <div style="margin:12px 0; color:#fca5a5;">
            {{ $errors->first() }}
        </div>
    @endif

    <h2>Tambah Device</h2>

    <form action="{{ route('rooms.devices.store', $room->id) }}" method="POST" style="margin-bottom:20px;">
        @csrf

        <div style="margin-bottom:10px;">
            <label>Nama Device</label><br>
            <input
                type="text"
                name="name"
                placeholder="Contoh: Lampu"
                required
                style="padding:8px; width:260px;"
            >
        </div>

        <div style="margin-bottom:10px;">
            <label>Tipe Device</label><br>
            <input
                type="text"
                name="type"
                placeholder="Contoh: lampu / fan"
                style="padding:8px; width:260px;"
            >
        </div>

        <div style="margin-bottom:10px;">
            <label>Device Key</label><br>
            <input
                type="text"
                name="esp32_device_id"
                placeholder="Contoh: 2"
                required
                style="padding:8px; width:260px;"
            >
            <small style="display:block; color:#94a3b8;">
                Device Key digunakan untuk kontrol relay dari ESP32.
            </small>
        </div>

        <div style="margin-bottom:10px;">
            <label>Sensor ID</label><br>
            <input
                type="text"
                name="esp_unit_id"
                placeholder="Contoh: SV-001"
                style="padding:8px; width:260px;"
            >
            <small style="display:block; color:#94a3b8;">
                Sensor ID harus sama dengan unitId di kode Arduino.
            </small>
        </div>

        <button type="submit" style="padding:8px 14px;">
            Tambah Device
        </button>
    </form>

    <h2>Daftar Device</h2>

    @forelse($devices as $device)
        <div style="margin-bottom:12px; padding:12px; border:1px solid #334155;">
            <strong>{{ $device->name }}</strong><br>

            <span>
                Tipe:
                {{ $device->type ?? 'device' }}
            </span><br>

            <span>
                Status:
                {{ $device->status ? 'ON' : 'OFF' }}
            </span><br>

            <span>
                Device Key:
                {{ $device->esp32_device_id ?? '-' }}
            </span><br>

            <span>
                Sensor ID:
                {{ $device->esp_unit_id ?? '-' }}
            </span><br>

            <form action="{{ route('rooms.devices.toggle', [$room->id, $device->id]) }}" method="POST" style="display:inline-block; margin-top:10px;">
                @csrf
                <button type="submit">
                    {{ $device->status ? 'Matikan' : 'Nyalakan' }}
                </button>
            </form>

            <form action="{{ route('rooms.devices.destroy', [$room->id, $device->id]) }}" method="POST" style="display:inline-block; margin-left:10px;">
                @csrf
                @method('DELETE')
                <button type="submit">
                    Hapus
                </button>
            </form>
        </div>
    @empty
        <p>Belum ada device di room ini.</p>
    @endforelse

</body>
</html>