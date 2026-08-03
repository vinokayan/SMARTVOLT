<?php

namespace App\Http\Controllers;

use App\Models\Device;
use App\Models\EnergyMeter;
use App\Models\Room;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Throwable;

class TechnicianPageController extends Controller
{
    public function index(): View
    {
        $userId = (int) Auth::id();

        $rooms = Room::query()
            ->withCount([
                'devices',
                'energyMeters',
            ])
            ->with([
                'devices' => fn ($query) => $query
                    ->orderBy('esp_unit_id')
                    ->orderBy('relay_code')
                    ->orderBy('name'),
                'energyMeters' => fn ($query) => $query
                    ->orderBy('esp_unit_id')
                    ->orderBy('meter_code')
                    ->orderBy('name'),
            ])
            ->where('user_id', $userId)
            ->orderBy('name')
            ->get();

        $devices = Device::query()
            ->with('room')
            ->whereHas(
                'room',
                fn ($query) => $query->where('user_id', $userId)
            )
            ->orderBy('name')
            ->get();

        $energyMeters = EnergyMeter::query()
            ->with('room')
            ->withCount('readings')
            ->where('user_id', $userId)
            ->orderBy('room_id')
            ->orderBy('esp_unit_id')
            ->orderBy('meter_code')
            ->get();

        return view('technician.index', [
            'rooms' => $rooms,
            'devices' => $devices,
            'energyMeters' => $energyMeters,
            'systemConnected' => $this->systemConnected($devices),
        ]);
    }

    public function storeSensorWithRelays(
        Request $request,
        Room $room
    ): RedirectResponse {
        $this->ensureRoomOwner($room);

        $validated = $request->validate([
            'sensor_name' => ['required', 'string', 'max:100'],
            'esp_unit_id' => ['required', 'string', 'max:100'],
            'meter_code' => ['required', 'string', 'max:50'],
            'sensor_type' => ['nullable', 'string', 'max:50'],
            'relay_count' => ['required', 'integer', 'min:1', 'max:8'],
            'relay_names' => ['required', 'array', 'min:1', 'max:8'],
            'relay_names.*' => ['nullable', 'string', 'max:100'],
        ], [
            'sensor_name.required' => 'Nama meter listrik wajib diisi.',
            'esp_unit_id.required' => 'Kode unit wajib diisi.',
            'meter_code.required' => 'Kode meter wajib diisi.',
            'relay_count.required' => 'Jumlah perangkat wajib dipilih.',
            'relay_count.min' => 'Jumlah perangkat minimal 1.',
            'relay_count.max' => 'Jumlah perangkat maksimal 8.',
            'relay_names.required' => 'Nama perangkat wajib diisi.',
        ]);

        $espUnitId = trim($validated['esp_unit_id']);
        $meterCode = trim($validated['meter_code']);
        $relayCount = (int) $validated['relay_count'];

        $relayNames = collect($validated['relay_names'])
            ->map(fn ($name) => trim((string) $name));

        for ($relayIndex = 1; $relayIndex <= $relayCount; $relayIndex++) {
            if ($relayNames->get($relayIndex, '') === '') {
                throw ValidationException::withMessages([
                    "relay_names.$relayIndex" => "Nama perangkat $relayIndex wajib diisi.",
                ]);
            }
        }

        $selectedRelayNames = $relayNames
            ->only(range(1, $relayCount))
            ->filter()
            ->map(fn ($name) => mb_strtolower($name));

        if ($selectedRelayNames->duplicates()->isNotEmpty()) {
            throw ValidationException::withMessages([
                'relay_names' => 'Nama perangkat tidak boleh sama.',
            ]);
        }

        $meterAlreadyExists = EnergyMeter::query()
            ->where('esp_unit_id', $espUnitId)
            ->where('meter_code', $meterCode)
            ->exists();

        if ($meterAlreadyExists) {
            throw ValidationException::withMessages([
                'meter_code' => 'Kode meter sudah digunakan pada unit tersebut.',
            ]);
        }

        for ($relayIndex = 1; $relayIndex <= $relayCount; $relayIndex++) {
            if ($this->relayCodeExists($espUnitId, (string) $relayIndex)) {
                throw ValidationException::withMessages([
                    'relay_count' => "Saluran $relayIndex sudah digunakan pada unit tersebut.",
                ]);
            }
        }

        DB::transaction(function () use (
            $room,
            $validated,
            $espUnitId,
            $meterCode,
            $relayCount,
            $relayNames
        ): void {
            EnergyMeter::create([
                'user_id' => Auth::id(),
                'room_id' => $room->id,
                'esp_unit_id' => $espUnitId,
                'meter_code' => $meterCode,
                'name' => trim($validated['sensor_name']),
                'sensor_type' => trim((string) ($validated['sensor_type'] ?? 'PZEM004T')) ?: 'PZEM004T',
                'is_active' => true,
            ]);

            for ($relayIndex = 1; $relayIndex <= $relayCount; $relayIndex++) {
                $this->createDevice([
                    'user_id' => (int) Auth::id(),
                    'room_id' => (int) $room->id,
                    'name' => $relayNames->get($relayIndex),
                    'esp_unit_id' => $espUnitId,
                    'relay_code' => (string) $relayIndex,
                ]);
            }
        }, 3);

        return $this->redirectToTechnician(
            'Meter listrik dan perangkat berhasil ditambahkan.',
            $room->id
        );
    }

    public function storeRelay(
        Request $request,
        Room $room
    ): RedirectResponse {
        $this->ensureRoomOwner($room);

        $validated = $request->validate([
            'esp_unit_id' => ['required', 'string', 'max:100'],
            'relay_code' => ['required', 'string', 'max:50'],
            'name' => ['required', 'string', 'max:100'],
        ], [
            'esp_unit_id.required' => 'Kode unit wajib dipilih.',
            'relay_code.required' => 'Kode saluran wajib diisi.',
            'name.required' => 'Nama perangkat wajib diisi.',
        ]);

        $espUnitId = trim($validated['esp_unit_id']);
        $relayCode = trim($validated['relay_code']);
        $deviceName = trim($validated['name']);

        $unitBelongsToRoom = EnergyMeter::query()
            ->where('user_id', Auth::id())
            ->where('room_id', $room->id)
            ->where('esp_unit_id', $espUnitId)
            ->exists();

        if (! $unitBelongsToRoom) {
            throw ValidationException::withMessages([
                'esp_unit_id' => 'Kode unit tidak terdaftar pada ruangan ini.',
            ]);
        }

        if ($this->relayCodeExists($espUnitId, $relayCode)) {
            throw ValidationException::withMessages([
                'relay_code' => 'Kode saluran sudah digunakan pada unit tersebut.',
            ]);
        }

        $nameAlreadyExists = Device::query()
            ->where('room_id', $room->id)
            ->whereRaw('LOWER(name) = ?', [mb_strtolower($deviceName)])
            ->exists();

        if ($nameAlreadyExists) {
            throw ValidationException::withMessages([
                'name' => 'Nama perangkat sudah digunakan di ruangan ini.',
            ]);
        }

        $this->createDevice([
            'user_id' => (int) Auth::id(),
            'room_id' => (int) $room->id,
            'name' => $deviceName,
            'esp_unit_id' => $espUnitId,
            'relay_code' => $relayCode,
        ]);

        return $this->redirectToTechnician(
            'Perangkat berhasil ditambahkan.',
            $room->id
        );
    }

    public function updateSensor(
        Request $request,
        EnergyMeter $energyMeter
    ): RedirectResponse {
        $this->ensureMeterOwner($energyMeter);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'meter_code' => ['required', 'string', 'max:50'],
            'sensor_type' => ['nullable', 'string', 'max:50'],
            'is_active' => ['nullable', 'boolean'],
        ], [
            'name.required' => 'Nama meter listrik wajib diisi.',
            'meter_code.required' => 'Kode meter wajib diisi.',
        ]);

        $meterCode = trim($validated['meter_code']);

        $meterCodeExists = EnergyMeter::query()
            ->where('esp_unit_id', $energyMeter->esp_unit_id)
            ->where('meter_code', $meterCode)
            ->whereKeyNot($energyMeter->id)
            ->exists();

        if ($meterCodeExists) {
            throw ValidationException::withMessages([
                'meter_code' => 'Kode meter sudah digunakan pada unit tersebut.',
            ]);
        }

        $energyMeter->update([
            'name' => trim($validated['name']),
            'meter_code' => $meterCode,
            'sensor_type' => trim((string) ($validated['sensor_type'] ?? 'PZEM004T')) ?: 'PZEM004T',
            'is_active' => $request->boolean('is_active'),
        ]);

        return $this->redirectToTechnician(
            'Meter listrik berhasil diperbarui.',
            $energyMeter->room_id
        );
    }

    public function toggleSensor(
        EnergyMeter $energyMeter
    ): RedirectResponse {
        $this->ensureMeterOwner($energyMeter);

        $energyMeter->update([
            'is_active' => ! (bool) $energyMeter->is_active,
        ]);

        return $this->redirectToTechnician(
            $energyMeter->is_active
                ? 'Meter listrik berhasil diaktifkan.'
                : 'Meter listrik berhasil dinonaktifkan.',
            $energyMeter->room_id
        );
    }

    public function destroySensor(
        EnergyMeter $energyMeter
    ): RedirectResponse {
        $this->ensureMeterOwner($energyMeter);

        $roomId = $energyMeter->room_id;
        $hasReadings = $energyMeter->readings()->exists();

        if ($hasReadings) {
            $energyMeter->update([
                'is_active' => false,
            ]);

            return $this->redirectToTechnician(
                'Meter memiliki riwayat pemakaian sehingga dinonaktifkan, bukan dihapus.',
                $roomId
            );
        }

        $energyMeter->delete();

        return $this->redirectToTechnician(
            'Meter listrik berhasil dihapus.',
            $roomId
        );
    }

    private function systemConnected(Collection $devices): bool
    {
        $threshold = now()->subMinutes(
            max(
                1,
                (int) config(
                    'services.iot.online_timeout_minutes',
                    2
                )
            )
        );

        return $devices->contains(function (Device $device) use ($threshold) {
            if (! (bool) ($device->is_online ?? false)) {
                return false;
            }

            $lastSeenAt = $this->toCarbon($device->last_seen_at ?? null);

            return $lastSeenAt !== null
                && $lastSeenAt->greaterThanOrEqualTo($threshold);
        });
    }

    private function ensureRoomOwner(Room $room): void
    {
        if ((int) $room->user_id !== (int) Auth::id()) {
            abort(403, 'Anda tidak memiliki akses ke ruangan ini.');
        }
    }

    private function ensureMeterOwner(EnergyMeter $energyMeter): void
    {
        if ((int) $energyMeter->user_id !== (int) Auth::id()) {
            abort(403, 'Anda tidak memiliki akses ke meter listrik ini.');
        }
    }

    private function relayCodeExists(
        string $espUnitId,
        string $relayCode
    ): bool {
        return Device::query()
            ->where('relay_code', $relayCode)
            ->where(function ($query) use ($espUnitId) {
                if (Schema::hasColumn('devices', 'esp_unit_id')) {
                    $query->where('esp_unit_id', $espUnitId);
                }

                if (Schema::hasColumn('devices', 'esp32_device_id')) {
                    if (Schema::hasColumn('devices', 'esp_unit_id')) {
                        $query->orWhere('esp32_device_id', $espUnitId);
                    } else {
                        $query->where('esp32_device_id', $espUnitId);
                    }
                }
            })
            ->exists();
    }

    private function createDevice(array $values): Device
    {
        $data = [
            'room_id' => $values['room_id'],
            'name' => $values['name'],
            'status' => false,
        ];

        if (Schema::hasColumn('devices', 'user_id')) {
            $data['user_id'] = $values['user_id'];
        }

        if (Schema::hasColumn('devices', 'type')) {
            $data['type'] = 'relay';
        }

        if (Schema::hasColumn('devices', 'relay_code')) {
            $data['relay_code'] = $values['relay_code'];
        }

        if (Schema::hasColumn('devices', 'esp_unit_id')) {
            $data['esp_unit_id'] = $values['esp_unit_id'];
        }

        if (Schema::hasColumn('devices', 'esp32_device_id')) {
            $data['esp32_device_id'] = $values['esp_unit_id'];
        }

        if (Schema::hasColumn('devices', 'device_key')) {
            $data['device_key'] = null;
        }

        return Device::create($data);
    }

    private function redirectToTechnician(
        string $message,
        ?int $roomId = null
    ): RedirectResponse {
        $redirect = redirect()
            ->route('technician.index')
            ->with('status', $message)
            ->with('success', $message);

        if ($roomId !== null) {
            $redirect->with('selected_room_id', $roomId);
        }

        return $redirect;
    }

    private function toCarbon(mixed $value): ?Carbon
    {
        if ($value === null || $value === '') {
            return null;
        }

        if ($value instanceof Carbon) {
            return $value;
        }

        if ($value instanceof \DateTimeInterface) {
            return Carbon::instance($value);
        }

        try {
            return Carbon::parse((string) $value);
        } catch (Throwable) {
            return null;
        }
    }
}
