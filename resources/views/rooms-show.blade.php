<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $room->name }} - SmartVolt</title>
    <link rel="stylesheet" href="{{ asset('assets/css/smartvolt-brand.css') }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body class="sv-dashboard-body">
    <div style="padding: 24px; color: white;">
        <h1>{{ $room->name }}</h1>
        <p><a href="{{ route('rooms') }}">← Kembali ke Rooms</a></p>

        @if(session('status'))
            <div style="margin:12px 0; color:#9ff0c2;">{{ session('status') }}</div>
        @endif

        @if($errors->any())
            <div style="margin:12px 0; color:#ffb4b4;">{{ $errors->first() }}</div>
        @endif

        <h2>Tambah Device</h2>
        <form action="{{ route('rooms.devices.store', $room->id) }}" method="POST">
            @csrf
            <input type="text" name="name" placeholder="Nama device" value="{{ old('name') }}" required>
            <input type="text" name="type" placeholder="Tipe device, contoh: lamp, fan, tv" value="{{ old('type') }}">
            <button type="submit">Tambah Device</button>
        </form>

        <hr>

        <h2>Daftar Device</h2>

        @forelse($devices as $device)
            <div style="margin-bottom:16px; padding:12px; border:1px solid #334; border-radius:12px;">
                <strong>{{ $device->name }}</strong>
                <div>Tipe: {{ $device->type ?? '-' }}</div>
                <div>Status: {{ $device->status ? 'ON' : 'OFF' }}</div>

                <form action="{{ route('rooms.devices.toggle', [$room->id, $device->id]) }}" method="POST" style="display:inline-block;">
                    @csrf
                    <button type="submit">{{ $device->status ? 'Matikan' : 'Nyalakan' }}</button>
                </form>

                <form action="{{ route('rooms.devices.destroy', [$room->id, $device->id]) }}" method="POST" style="display:inline-block;" onsubmit="return confirm('Hapus device ini?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit">Hapus</button>
                </form>

                <details style="margin-top:10px;">
                    <summary>Edit Device</summary>
                    <form action="{{ route('rooms.devices.update', [$room->id, $device->id]) }}" method="POST" style="margin-top:10px;">
                        @csrf
                        @method('PUT')
                        <input type="text" name="name" value="{{ $device->name }}" required>
                        <input type="text" name="type" value="{{ $device->type }}">
                        <button type="submit">Simpan Perubahan</button>
                    </form>
                </details>
            </div>
        @empty
            <p>Belum ada device di room ini.</p>
        @endforelse
    </div>
</body>
</html>