<?php

namespace App\Http\Controllers;

use App\Models\EnergyMeter;
use App\Models\Room;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class RoomController extends Controller
{
    private function ensureAdvancedMode(): void
    {
        if (! session('advanced_mode')) {
            abort(403, 'Mode Lanjutan belum aktif.');
        }
    }

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
        $this->ensureAdvancedMode();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
        ], [
            'name.required' => 'Nama ruangan wajib diisi.',
            'name.max' => 'Nama ruangan maksimal 100 karakter.',
        ]);

        $data = [
            'user_id' => Auth::id(),
            'name' => trim($validated['name']),
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
        $this->ensureAdvancedMode();
        $this->ensureRoomOwner($room);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
        ], [
            'name.required' => 'Nama ruangan wajib diisi.',
            'name.max' => 'Nama ruangan maksimal 100 karakter.',
        ]);

        $room->update([
            'name' => trim($validated['name']),
        ]);

        return $this->redirectAfterAction(
            $request,
            'Ruangan berhasil diperbarui.',
            $room->id
        );
    }

    public function destroy(Request $request, Room $room)
    {
        $this->ensureAdvancedMode();
        $this->ensureRoomOwner($room);

        DB::transaction(function () use ($room) {
            $meters = $room->energyMeters()
                ->withCount('readings')
                ->get();

            /*
             * Relay selalu dihapus bersama room. Meter yang sudah memiliki
             * riwayat hanya dinonaktifkan agar energy_logs tidak rusak.
             */
            $room->devices()->delete();

            foreach ($meters as $meter) {
                if ($meter->readings_count > 0) {
                    $meter->update([
                        'room_id' => null,
                        'is_active' => false,
                    ]);
                } else {
                    $meter->delete();
                }
            }

            $room->delete();
        }, 3);

        return $this->redirectAfterAction(
            $request,
            'Ruangan berhasil dihapus. Meter dengan riwayat energi dinonaktifkan agar data lama tetap aman.'
        );
    }

    private function redirectAfterAction(
        Request $request,
        string $message,
        ?int $selectedRoomId = null
    ) {
        $returnTo = $request->input('return_to');

        if ($returnTo === 'technician' || $returnTo === 'settings') {
            $redirect = redirect()
                ->route('settings')
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
                ->with('status', $message);
        }

        return redirect()
            ->route('dashboard')
            ->with('status', $message);
    }
}
