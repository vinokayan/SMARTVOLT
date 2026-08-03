<?php

namespace App\Http\Controllers;

use App\Models\EnergyLog;
use App\Models\EnergyMeter;
use App\Models\Room;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
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
        $rooms = Room::query()
            ->withCount([
                'devices',
                'energyMeters',
            ])
            ->with([
                'devices' => function ($query) {
                    $query->orderBy('name');
                },
                'energyMeters' => function ($query) {
                    $query->where('is_active', true)
                        ->orderBy('name');
                },
            ])
            ->where('user_id', Auth::id())
            ->orderBy('name')
            ->get();

        $espIds = $rooms
            ->pluck('devices')
            ->flatten()
            ->map(function ($device) {
                return trim(
                    (string) ($device->esp_unit_id ?: $device->esp32_device_id)
                );
            })
            ->filter()
            ->unique()
            ->values();

        $onlineEspUnitIds = $this->resolveOnlineEspUnitIds($espIds);
        $roomPower = $this->resolveCurrentRoomPower(
            $rooms->pluck('energyMeters')->flatten()->pluck('id')
        );

        return view('rooms', compact(
            'rooms',
            'onlineEspUnitIds',
            'roomPower'
        ));
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

    private function resolveOnlineEspUnitIds(
        Collection $espIds
    ): Collection {
        if (
            $espIds->isEmpty()
            || ! Schema::hasTable('energy_meters')
            || ! Schema::hasTable('energy_logs')
            || ! Schema::hasColumn('energy_meters', 'esp_unit_id')
            || ! Schema::hasColumn('energy_meters', 'is_active')
            || ! Schema::hasColumn('energy_logs', 'energy_meter_id')
            || ! Schema::hasColumn('energy_logs', 'created_at')
        ) {
            return collect();
        }

        return EnergyLog::query()
            ->where(
                'created_at',
                '>=',
                now()->subMinutes(
                    max(
                        1,
                        (int) config(
                            'services.iot.online_timeout_minutes',
                            2
                        )
                    )
                )
            )
            ->whereHas('energyMeter', function ($query) use ($espIds) {
                $query->whereIn('esp_unit_id', $espIds)
                    ->where('is_active', true);
            })
            ->with('energyMeter:id,esp_unit_id')
            ->get()
            ->pluck('energyMeter.esp_unit_id')
            ->filter()
            ->map(fn ($value) => (string) $value)
            ->unique()
            ->values();
    }

    private function resolveCurrentRoomPower(
        Collection $meterIds
    ): Collection {
        if (
            $meterIds->isEmpty()
            || ! Schema::hasTable('energy_logs')
        ) {
            return collect();
        }

        $latestIds = EnergyLog::query()
            ->whereIn('energy_meter_id', $meterIds)
            ->whereNotNull('energy_meter_id')
            ->selectRaw('MAX(id) AS id')
            ->groupBy('energy_meter_id')
            ->pluck('id')
            ->filter()
            ->values();

        if ($latestIds->isEmpty()) {
            return collect();
        }

        $threshold = now()->subMinutes(
            max(
                1,
                (int) config(
                    'services.iot.online_timeout_minutes',
                    2
                )
            )
        );

        return EnergyLog::query()
            ->with('energyMeter:id,room_id')
            ->whereIn('id', $latestIds)
            ->get()
            ->filter(function (EnergyLog $log) use ($threshold) {
                return $log->created_at
                    && $log->created_at->greaterThanOrEqualTo($threshold)
                    && $log->energyMeter?->room_id;
            })
            ->groupBy(
                fn (EnergyLog $log) => (int) $log->energyMeter->room_id
            )
            ->map(
                fn ($logs) => round(
                    (float) $logs->sum('power'),
                    1
                )
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
                ->route('settings', ['tab' => 'technician'])
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
