<form action="{{ route('devices.store') }}" method="POST">
    @csrf

    <div>
        <label>Room</label>
        <select name="room_id" required>
            <option value="">Pilih Room</option>
            @foreach($rooms as $room)
                <option value="{{ $room->id }}" {{ old('room_id') == $room->id ? 'selected' : '' }}>
                    {{ $room->name }}
                </option>
            @endforeach
        </select>
    </div>

    <div>
        <label>Nama Device</label>
        <input type="text" name="name" value="{{ old('name') }}" required>
    </div>

    <div>
        <label>Tipe Device</label>
        <input type="text" name="type" value="{{ old('type') }}" placeholder="Contoh: lamp, fan, tv">
    </div>

    <button type="submit">Tambah Device</button>
</form>