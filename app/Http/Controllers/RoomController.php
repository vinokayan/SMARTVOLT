<?php

namespace App\Http\Controllers;

use App\Models\Room;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RoomController extends Controller
{
    public function index()
    {
        $rooms = Room::withCount('devices')
            ->with(['devices' => function ($query) {
                $query->latest();
            }])
            ->where('user_id', Auth::id())
            ->latest()
            ->get();

        return view('rooms', compact('rooms'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
        ], [
            'name.required' => 'Nama room wajib diisi.',
            'name.max' => 'Nama room maksimal 100 karakter.',
        ]);

        Room::create([
            'user_id' => Auth::id(),
            'name' => $validated['name'],
            'status' => true,
        ]);

        return redirect()->route('rooms')->with('status', 'Room berhasil ditambahkan.');
    }

    public function update(Request $request, Room $room)
    {
        if ($room->user_id !== Auth::id()) {
            abort(403, 'Anda tidak punya akses ke room ini.');
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
        ]);

        $room->update([
            'name' => $validated['name'],
        ]);

        return redirect()->route('rooms')->with('status', 'Room berhasil diperbarui.');
    }

    public function destroy(Room $room)
    {
        if ($room->user_id !== Auth::id()) {
            abort(403, 'Anda tidak punya akses ke room ini.');
        }

        if ($room->devices()->count() > 0) {
            return redirect()->route('rooms')->withErrors([
                'room' => 'Room tidak bisa dihapus karena masih memiliki device.',
            ]);
        }

        $room->delete();

        return redirect()->route('rooms')->with('status', 'Room berhasil dihapus.');
    }
}