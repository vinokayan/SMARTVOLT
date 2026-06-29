<?php

namespace App\Http\Controllers;

use App\Models\Room;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RoomController extends Controller
{
    private function ensureAdvancedMode()
    {
        if (! session('advanced_mode')) {
            abort(403, 'Mode Lanjutan belum aktif.');
        }
    }

    private function ensureRoomOwner(Room $room)
    {
        if ((int) $room->user_id !== (int) Auth::id()) {
            abort(403, 'Anda tidak punya akses ke ruangan ini.');
        }
    }

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
        $this->ensureAdvancedMode();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
        ], [
            'name.required' => 'Nama ruangan wajib diisi.',
            'name.max' => 'Nama ruangan maksimal 100 karakter.',
        ]);

        $room = Room::create([
            'user_id' => Auth::id(),
            'name' => $validated['name'],
            'status' => true,
        ]);

        if ($request->input('return_to') === 'settings') {
            return redirect()
                ->route('settings')
                ->with('status', 'Ruangan berhasil ditambahkan.')
                ->with('open_advanced_panel', true)
                ->with('selected_room_id', $room->id);
        }

        return redirect()
            ->route('rooms')
            ->with('status', 'Ruangan berhasil ditambahkan.');
    }

    public function update(Request $request, Room $room)
    {
        $this->ensureAdvancedMode();
        $this->ensureRoomOwner($room);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
        ], [
            'name.required' => 'Nama ruangan wajib diisi.',
            'name.max' => 'Nama ruangan maksimal 100 karakter.',
        ]);

        $room->update([
            'name' => $validated['name'],
        ]);

        if ($request->input('return_to') === 'settings') {
            return redirect()
                ->route('settings')
                ->with('status', 'Ruangan berhasil diperbarui.')
                ->with('open_advanced_panel', true)
                ->with('selected_room_id', $room->id);
        }

        return redirect()
            ->route('rooms')
            ->with('status', 'Ruangan berhasil diperbarui.');
    }

    public function destroy(Request $request, Room $room)
    {
        $this->ensureAdvancedMode();
        $this->ensureRoomOwner($room);

        if ($room->devices()->count() > 0) {
            if ($request->input('return_to') === 'settings') {
                return redirect()
                    ->route('settings')
                    ->withErrors([
                        'room' => 'Ruangan tidak bisa dihapus karena masih memiliki perangkat.',
                    ])
                    ->with('open_advanced_panel', true)
                    ->with('selected_room_id', $room->id);
            }

            return redirect()
                ->route('rooms')
                ->withErrors([
                    'room' => 'Ruangan tidak bisa dihapus karena masih memiliki perangkat.',
                ]);
        }

        $room->delete();

        if ($request->input('return_to') === 'settings') {
            return redirect()
                ->route('settings')
                ->with('status', 'Ruangan berhasil dihapus.')
                ->with('open_advanced_panel', true);
        }

        return redirect()
            ->route('rooms')
            ->with('status', 'Ruangan berhasil dihapus.');
    }
}