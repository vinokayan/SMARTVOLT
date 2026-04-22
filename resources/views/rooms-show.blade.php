<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $room->name }} - SmartVolt</title>
</head>
<body style="background:#081426; color:white; font-family:Arial; padding:24px;">
    <h1>Room: {{ $room->name }}</h1>
    <p><a href="{{ route('rooms') }}" style="color:#7dd3fc;">Kembali ke Rooms</a></p>

    @if(session('status'))
        <div style="margin:12px 0; color:#86efac;">{{ session('status') }}</div>
    @endif

    @if($errors->any())
        <div style="margin:12px 0; color:#fca5a5;">{{ $errors->first() }}</div>
    @endif

    <h2>Tambah Device</h2>
    <form action="{{ route('rooms.devices.store', $room->id) }}" method="POST" style="margin-bottom:20px;">
        @csrf
        <input type="text" name="name" placeholder="Nama device" required>
        <input type="text" name="type" placeholder="Tipe device">
        <button type="submit">Tambah Device</button>
    </form>

    <h2>Daftar Device</h2>
    @forelse($devices as $device)
        <div style="margin-bottom:12px; padding:12px; border:1px solid #334155;">
            <strong>{{ $device->name }}</strong> - {{ $device->type ?? 'device' }} - {{ $device->status ? 'ON' : 'OFF' }}

            <form action="{{ route('rooms.devices.toggle', [$room->id, $device->id]) }}" method="POST" style="display:inline-block; margin-left:10px;">
                @csrf
                <button type="submit">{{ $device->status ? 'Matikan' : 'Nyalakan' }}</button>
            </form>

            <form action="{{ route('rooms.devices.destroy', [$room->id, $device->id]) }}" method="POST" style="display:inline-block; margin-left:10px;">
                @csrf
                @method('DELETE')
                <button type="submit">Hapus</button>
            </form>
        </div>
    @empty
        <p>Belum ada device di room ini.</p>
    @endforelse
</body>
</html>