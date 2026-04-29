<?php

namespace App\Http\Controllers;

use App\Models\Device;
use App\Models\Room;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class DeviceController extends Controller
{
    public function index()
    {
        $devices = Device::with('room')
            ->whereHas('room', function ($query) {
                $query->where('user_id', Auth::id());
            })
            ->latest()
            ->get();

        return view('devices', compact('devices'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'room_id' => ['required', 'exists:rooms,id'],
            'name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('devices')->where(function ($query) use ($request) {
                    return $query->where('room_id', $request->room_id);
                }),
            ],
            'type' => ['nullable', 'string', 'max:100'],
        ], [
            'room_id.required' => 'Room wajib dipilih.',
            'room_id.exists' => 'Room tidak valid.',
            'name.required' => 'Nama device wajib diisi.',
            'name.unique' => 'Nama device sudah ada di room ini.',
        ]);

        $room = Room::findOrFail($validated['room_id']);

        if ($room->user_id !== Auth::id()) {
            abort(403, 'Anda tidak punya akses ke room ini.');
        }

        Device::create([
            'room_id' => $room->id,
            'name' => $validated['name'],
            'type' => $validated['type'] ?? null,
            'status' => false,
        ]);

        return back()->with('status', 'Device berhasil ditambahkan.');
    }

    public function update(Request $request, Device $device)
    {
        $device->load('room');

        if (!$device->room || $device->room->user_id !== Auth::id()) {
            abort(403, 'Anda tidak punya akses ke device ini.');
        }

        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('devices')
                    ->where(function ($query) use ($device) {
                        return $query->where('room_id', $device->room_id);
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

        return back()->with('status', 'Device berhasil diperbarui.');
    }

    public function destroy(Device $device)
    {
        $device->load('room');

        if (!$device->room || $device->room->user_id !== Auth::id()) {
            abort(403, 'Anda tidak punya akses ke device ini.');
        }

        $device->delete();

        return back()->with('status', 'Device berhasil dihapus.');
    }

   public function toggle(Request $request, Device $device)
{
    $device->load('room');

    if (!$device->room || $device->room->user_id !== Auth::id()) {
        abort(403, 'Anda tidak punya akses ke device ini.');
    }

    $device->update([
        'status' => !$device->status,
    ]);

    if ($request->expectsJson()) {
        return response()->json([
            'success' => true,
            'status' => $device->status ? 'on' : 'off',
        ]);
    }

    return back()
        ->with('status', 'Status device berhasil diubah.')
        ->with('open_room_id', $request->open_room_id);
}
}