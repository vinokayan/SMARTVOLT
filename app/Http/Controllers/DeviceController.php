<?php

namespace App\Http\Controllers;

use App\Models\Device;
use App\Models\Room;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class DeviceController extends Controller
{
    public function storeByRoom(Request $request, Room $room)
    {
        $this->authorizeRoom($room);

        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('devices')->where(function ($query) use ($room) {
                    return $query->where('room_id', $room->id);
                }),
            ],
            'type' => ['nullable', 'string', 'max:100'],
        ], [
            'name.required' => 'Nama device wajib diisi.',
            'name.unique' => 'Nama device sudah ada di room ini.',
        ]);

        Device::create([
            'room_id' => $room->id,
            'name' => $validated['name'],
            'type' => $validated['type'] ?? null,
            'status' => false,
        ]);

        return redirect()->route('rooms.show', $room->id)
            ->with('status', 'Device berhasil ditambahkan ke room.');
    }

    public function updateByRoom(Request $request, Room $room, Device $device)
    {
        $this->authorizeRoom($room);
        $this->authorizeDeviceInRoom($room, $device);

        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('devices')
                    ->where(function ($query) use ($room) {
                        return $query->where('room_id', $room->id);
                    })
                    ->ignore($device->id),
            ],
            'type' => ['nullable', 'string', 'max:100'],
        ], [
            'name.required' => 'Nama device wajib diisi.',
            'name.unique' => 'Nama device sudah ada di room ini.',
        ]);

        $device->update([
            'name' => $validated['name'],
            'type' => $validated['type'] ?? null,
        ]);

        return redirect()->route('rooms.show', $room->id)
            ->with('status', 'Device berhasil diperbarui.');
    }

    public function destroyByRoom(Room $room, Device $device)
    {
        $this->authorizeRoom($room);
        $this->authorizeDeviceInRoom($room, $device);

        $device->delete();

        return redirect()->route('rooms.show', $room->id)
            ->with('status', 'Device berhasil dihapus.');
    }

    public function toggleByRoom(Room $room, Device $device)
    {
        $this->authorizeRoom($room);
        $this->authorizeDeviceInRoom($room, $device);

        $device->update([
            'status' => !$device->status,
        ]);

        return redirect()->route('rooms.show', $room->id)
            ->with('status', 'Status device berhasil diubah.');
    }

    private function authorizeRoom(Room $room): void
    {
        if ($room->user_id !== auth()->id()) {
            abort(403, 'Anda tidak punya akses ke room ini.');
        }
    }

    private function authorizeDeviceInRoom(Room $room, Device $device): void
    {
        if ($device->room_id !== $room->id) {
            abort(404, 'Device tidak ditemukan di room ini.');
        }
    }
}