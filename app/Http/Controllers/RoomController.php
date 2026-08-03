<?php

namespace App\Http\Controllers;

use App\Models\EnergyLog;
use App\Models\Room;
use App\Models\SystemSetting;
use Carbon\CarbonInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class RoomController extends Controller
{
    private const DEFAULT_REFRESH_INTERVAL = 30;

    private function ensureAdvancedMode(): void
    {
        if (! session('advanced_mode')) {
            abort(403, 'Mode Teknisi belum aktif.');
        }
    }

    private function ensureRoomOwner(Room $room): void
    {
        if ((int) $room->user_id !== (int) Auth::id()) {
            abort(403, 'Anda tidak punya akses ke ruangan ini.');
        }
    }

    public function index(): View
    {
        $payload = $this->buildRoomPagePayload();

        return view('rooms', [
            'rooms' => $payload['room_models'],
            'roomPageData' => $payload['rooms'],
            'roomStats' => $payload['stats'],
            'roomSystem' => $payload['system'],
            'refreshInterval' => $payload['refresh_interval'],
        ]);
    }

    public function data(): JsonResponse
    {
        $payload = $this->buildRoomPagePayload();

        return response()->json([
            'stats' => $payload['stats'],
            'system' => $payload['system'],
            'rooms' => $payload['rooms'],
        ]);
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

    private function buildRoomPagePayload(): array
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

        $devices = $rooms
            ->pluck('devices')
            ->flatten()
            ->values();

        $statusThreshold = now()->subMinutes(
            $this->onlineTimeoutMinutes()
        );

        $onlineEspUnitIds = $this->resolveOnlineEspUnitIds(
            $devices,
            $statusThreshold
        );

        $roomPower = $this->resolveCurrentRoomPower(
            $rooms->pluck('energyMeters')->flatten()->pluck('id'),
            $statusThreshold
        );

        $roomPageData = $rooms
            ->map(function (Room $room) use (
                $onlineEspUnitIds,
                $roomPower,
                $statusThreshold
            ) {
                return $this->roomPayload(
                    $room,
                    $onlineEspUnitIds,
                    $roomPower,
                    $statusThreshold
                );
            })
            ->values();

        $devicePayloads = $roomPageData
            ->flatMap(fn (array $room) => collect($room['devices'] ?? []))
            ->values();

        $deviceStatusAvailable = $devicePayloads->isEmpty()
            || $devicePayloads->every(
                fn (array $device) => (bool) ($device['status_available'] ?? false)
            );

        $activeDevices = $deviceStatusAvailable
            ? $devicePayloads
                ->filter(fn (array $device) => ($device['is_on'] ?? null) === true)
                ->count()
            : null;

        $settings = SystemSetting::query()
            ->where('user_id', Auth::id())
            ->first();

        $refreshInterval = min(
            60,
            max(
                10,
                (int) ($settings?->refresh_interval ?? self::DEFAULT_REFRESH_INTERVAL)
            )
        );

        return [
            'room_models' => $rooms,
            'rooms' => $roomPageData,
            'stats' => [
                'total_rooms' => $rooms->count(),
                'total_devices' => $devices->count(),
                'active_devices' => $activeDevices,
                'device_status_available' => $deviceStatusAvailable,
            ],
            'system' => [
                'connected' => $onlineEspUnitIds->isNotEmpty(),
                'online_esp_count' => $onlineEspUnitIds->count(),
                'online_esp_unit_ids' => $onlineEspUnitIds->values(),
            ],
            'refresh_interval' => $refreshInterval,
        ];
    }

    private function roomPayload(
        Room $room,
        Collection $onlineEspUnitIds,
        Collection $roomPower,
        CarbonInterface $statusThreshold
    ): array {
        $devices = $room->devices
            ->map(function ($device) use (
                $onlineEspUnitIds,
                $statusThreshold
            ) {
                return $this->devicePayload(
                    $device,
                    $onlineEspUnitIds,
                    $statusThreshold
                );
            })
            ->values();

        $statusAvailable = $devices->isEmpty()
            || $devices->every(
                fn (array $device) => (bool) ($device['status_available'] ?? false)
            );

        $activeDevices = $statusAvailable
            ? $devices
                ->filter(fn (array $device) => ($device['is_on'] ?? null) === true)
                ->count()
            : null;

        $onlineDevices = $devices
            ->filter(fn (array $device) => (bool) ($device['esp_online'] ?? false))
            ->count();

        $totalDevices = $devices->count();

        $connectionLabel = match (true) {
            $totalDevices === 0 => 'Belum ada perangkat',
            $onlineDevices === $totalDevices => 'Terhubung',
            $onlineDevices > 0 => 'Sebagian terhubung',
            default => 'Belum terhubung',
        };

        return [
            'id' => $room->id,
            'name' => $room->name,
            'total_devices' => $totalDevices,
            'active_devices' => $activeDevices,
            'status_available' => $statusAvailable,
            'online_devices' => $onlineDevices,
            'connected' => $onlineDevices > 0,
            'connection_label' => $connectionLabel,
            'current_power' => $roomPower->has($room->id)
                ? (float) $roomPower[$room->id]
                : null,
            'devices' => $devices,
        ];
    }

    private function devicePayload(
        $device,
        Collection $onlineEspUnitIds,
        CarbonInterface $statusThreshold
    ): array {
        $espUnitId = trim(
            (string) ($device->esp_unit_id ?: $device->esp32_device_id)
        );

        $espOnline = $espUnitId !== ''
            && $onlineEspUnitIds->contains($espUnitId);

        $statusAvailable = $espOnline
            && $device->last_confirmed_at
            && $device->last_confirmed_at->greaterThanOrEqualTo(
                $statusThreshold
            );

        $lastKnownState = $this->isDeviceOn($device->status ?? null);
        $currentState = $statusAvailable ? $lastKnownState : null;

        $commandPending = $device->pending_state !== null
            && $device->last_command_at
            && $device->last_command_at->greaterThanOrEqualTo(
                now()->subSeconds(15)
            )
            && (string) $device->last_ack_command_id
                !== (string) $device->last_command_id;

        $statusMessage = match (true) {
            ! $espOnline => 'SmartVolt belum terhubung',
            ! $statusAvailable => 'Menunggu pembaruan status',
            $commandPending => 'Perintah sedang diproses',
            $currentState === true => 'Perangkat sedang menyala',
            default => 'Perangkat sedang mati',
        };

        return [
            'id' => $device->id,
            'room_id' => $device->room_id,
            'name' => $device->name,
            'type' => 'relay',
            'relay_code' => $device->relay_code ?? null,
            'esp_unit_id' => $espUnitId,
            'esp_online' => $espOnline,
            'status_available' => $statusAvailable,
            'command_pending' => $commandPending,
            'status' => $currentState,
            'is_on' => $currentState,
            'last_known_state' => $lastKnownState,
            'status_message' => $statusMessage,
        ];
    }

    private function resolveOnlineEspUnitIds(
        Collection $devices,
        CarbonInterface $threshold
    ): Collection {
        return $devices
            ->filter(function ($device) use ($threshold) {
                return (bool) ($device->is_online ?? false)
                    && $device->last_seen_at
                    && $device->last_seen_at->greaterThanOrEqualTo(
                        $threshold
                    );
            })
            ->map(function ($device) {
                return trim(
                    (string) ($device->esp_unit_id ?: $device->esp32_device_id)
                );
            })
            ->filter()
            ->unique()
            ->values();
    }

    private function resolveCurrentRoomPower(
        Collection $meterIds,
        CarbonInterface $threshold
    ): Collection {
        if (
            $meterIds->isEmpty()
            || ! Schema::hasTable('energy_logs')
        ) {
            return collect();
        }

        $latestPerMeter = EnergyLog::query()
            ->with('energyMeter:id,room_id')
            ->whereIn('energy_meter_id', $meterIds)
            ->where('created_at', '>=', $threshold)
            ->orderBy('observed_at')
            ->orderBy('id')
            ->get()
            ->groupBy('energy_meter_id')
            ->map(fn (Collection $logs) => $logs->last())
            ->filter();

        return $latestPerMeter
            ->filter(fn (EnergyLog $log) => $log->energyMeter?->room_id)
            ->groupBy(
                fn (EnergyLog $log) => (int) $log->energyMeter->room_id
            )
            ->map(fn (Collection $logs) => round(
                (float) $logs->sum(
                    fn (EnergyLog $log) => max(
                        0,
                        (float) ($log->power ?? 0)
                    )
                ),
                1
            ));
    }

    private function isDeviceOn($status): bool
    {
        if (is_bool($status)) {
            return $status;
        }

        if (is_numeric($status)) {
            return (int) $status === 1;
        }

        return in_array(
            strtolower(trim((string) $status)),
            ['on', 'nyala', 'active', 'aktif', 'true', '1'],
            true
        );
    }

    private function onlineTimeoutMinutes(): int
    {
        return max(
            1,
            (int) config('services.iot.online_timeout_minutes', 2)
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
                ->route('technician.index')
                ->with('status', $message);

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
