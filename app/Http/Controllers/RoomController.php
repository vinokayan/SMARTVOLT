<?php

namespace App\Http\Controllers;

use App\Models\Room;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

class RoomController extends Controller
{
    private function ensureRoomOwner(Room $room): void
    {
        if ((int) $room->user_id !== (int) Auth::id()) {
            abort(403, 'Anda tidak punya akses ke ruangan ini.');
        }
    }

    public function index()
    {
        $rooms = Room::withCount('devices')
            ->with(['devices' => function ($query) {
                $query->orderBy('name');
            }])
            ->where('user_id', Auth::id())
            ->orderBy('name')
            ->get();

        return view('rooms', compact('rooms'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
        ], [
            'name.required' => 'Nama ruangan wajib diisi.',
            'name.max' => 'Nama ruangan maksimal 100 karakter.',
        ]);

        $data = [
            'user_id' => Auth::id(),
            'name' => $validated['name'],
        ];

        if (Schema::hasColumn('rooms', 'status')) {
            $data['status'] = true;
        }

        $room = Room::create($data);

        return $this->redirectAfterAction(
            $request,
            'Ruangan berhasil ditambahkan.',
            $room->id
        );
    }

    public function update(Request $request, Room $room)
    {
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

        return $this->redirectAfterAction(
            $request,
            'Ruangan berhasil diperbarui.',
            $room->id
        );
    }

    public function destroy(Request $request, Room $room)
    {
        $this->ensureRoomOwner($room);

        $room->devices()->delete();
        $room->delete();

        return $this->redirectAfterAction(
            $request,
            'Ruangan berhasil dihapus.'
        );
    }

    private function redirectAfterAction(Request $request, string $message, ?int $selectedRoomId = null)
    {
        $returnTo = $request->input('return_to');

        if ($returnTo === 'settings') {
            $redirect = redirect()
                ->route('settings')
                ->with('success', $message)
                ->with('status', $message)
                ->with('open_advanced_panel', true);

            if ($selectedRoomId) {
                $redirect->with('selected_room_id', $selectedRoomId);
            }

            return $redirect;
        }

        if ($returnTo === 'rooms') {
            return redirect()
                ->route('rooms')
                ->with('success', $message)
                ->with('status', $message);
        }

        return redirect()
            ->route('dashboard')
            ->with('success', $message)
            ->with('status', $message);
    }
}