<?php

namespace App\Http\Controllers;

use App\Models\Device;
use App\Models\EnergyMeter;
use App\Models\Room;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

class TechnicianPanelController extends Controller
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

    private function ensureSensorOwner(EnergyMeter $energyMeter): void
    {
        if ((int) $energyMeter->user_id !== (int) Auth::id()) {
            abort(403, 'Anda tidak punya akses ke sensor listrik ini.');
        }
    }

    public function storeSensorWithRelays(Request $request, Room $room)
    {
        $this->ensureAdvancedMode();
        $this->ensureRoomOwner($room);

        $validated = $request->validate([
            'sensor_name' => ['required', 'string', 'max:100'],
            'esp_unit_id' => ['required', 'string', 'max:100'],
            'meter_code' => ['required', 'string', 'max:50'],
            'sensor_type' => ['nullable', 'string', 'max:50'],
            'relay_count' => ['required', 'integer', 'min:1', 'max:8'],
            'relay_names' => ['nullable', 'array'],
            'relay_names.*' => ['nullable', 'string', 'max:100'],
        ], [
            'sensor_name.required' => 'Nama sensor listrik wajib diisi.',
            'esp_unit_id.required' => 'ESP Unit ID wajib diisi.',
            'meter_code.required' => 'Meter code wajib diisi.',
            'relay_count.required' => 'Jumlah relay wajib dipilih.',
            'relay_count.min' => 'Minimal 1 relay.',
            'relay_count.max' => 'Maksimal 8 relay.',
        ]);

        $espUnitId = trim($validated['esp_unit_id']);
        $meterCode = trim($validated['meter_code']);
        $relayCount = (int) $validated['relay_count'];
        $relayNames = $validated['relay_names'] ?? [];

        $existingMeter = EnergyMeter::where('esp_unit_id', $espUnitId)
            ->where('meter_code', $meterCode)
            ->first();

        if ($existingMeter) {
            throw ValidationException::withMessages([
                'meter_code' => 'Sensor listrik dengan ESP Unit ID dan meter code ini sudah terdaftar.',
            ]);
        }

        for ($i = 1; $i <= $relayCount; $i++) {
            $relayCode = (string) $i;

            $existingRelay = Device::where('esp_unit_id', $espUnitId)
                ->where('relay_code', $relayCode)
                ->first();

            if ($existingRelay) {
                throw ValidationException::withMessages([
                    'relay_count' => "Relay channel {$relayCode} pada ESP Unit ID {$espUnitId} sudah digunakan.",
                ]);
            }
        }

        DB::transaction(function () use ($room, $validated, $espUnitId, $meterCode, $relayCount, $relayNames) {
            EnergyMeter::create([
                'user_id' => Auth::id(),
                'room_id' => $room->id,
                'esp_unit_id' => $espUnitId,
                'meter_code' => $meterCode,
                'name' => $validated['sensor_name'],
                'sensor_type' => $validated['sensor_type'] ?? 'PZEM004T',
                'is_active' => true,
            ]);

            for ($i = 1; $i <= $relayCount; $i++) {
                $relayCode = (string) $i;
                $relayName = trim($relayNames[$i] ?? '');

                if ($relayName === '') {
                    $relayName = 'Relay ' . $relayCode . ' ' . $room->name;
                }

                $this->createRelayDevice(
                    room: $room,
                    espUnitId: $espUnitId,
                    relayCode: $relayCode,
                    name: $relayName
                );
            }
        });

        return $this->backToSettings(
            'ESP, sensor listrik, dan relay berhasil ditambahkan.',
            $room->id
        );
    }

    public function storeRelay(Request $request, Room $room)
    {
        $this->ensureAdvancedMode();
        $this->ensureRoomOwner($room);

        $validated = $request->validate([
            'esp_unit_id' => ['required', 'string', 'max:100'],
            'relay_code' => ['required', 'string', 'max:50'],
            'name' => ['required', 'string', 'max:100'],
        ], [
            'esp_unit_id.required' => 'Pilih ESP Unit ID terlebih dahulu.',
            'relay_code.required' => 'Relay channel wajib diisi.',
            'name.required' => 'Nama perangkat wajib diisi.',
        ]);

        $espUnitId = trim($validated['esp_unit_id']);
        $relayCode = trim($validated['relay_code']);

        $sensorExists = EnergyMeter::where('user_id', Auth::id())
            ->where('room_id', $room->id)
            ->where('esp_unit_id', $espUnitId)
            ->exists();

        if (! $sensorExists) {
            throw ValidationException::withMessages([
                'esp_unit_id' => 'ESP Unit ID ini belum memiliki sensor listrik di ruangan ini.',
            ]);
        }

        $existingRelay = Device::where('esp_unit_id', $espUnitId)
            ->where('relay_code', $relayCode)
            ->first();

        if ($existingRelay) {
            throw ValidationException::withMessages([
                'relay_code' => 'Relay channel ini sudah digunakan pada ESP Unit ID tersebut.',
            ]);
        }

        $this->createRelayDevice(
            room: $room,
            espUnitId: $espUnitId,
            relayCode: $relayCode,
            name: $validated['name']
        );

        return $this->backToSettings(
            'Relay baru berhasil ditambahkan.',
            $room->id
        );
    }

    public function updateSensor(Request $request, EnergyMeter $energyMeter)
    {
        $this->ensureAdvancedMode();
        $this->ensureSensorOwner($energyMeter);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'meter_code' => ['required', 'string', 'max:50'],
            'sensor_type' => ['nullable', 'string', 'max:50'],
            'is_active' => ['nullable'],
        ], [
            'name.required' => 'Nama sensor listrik wajib diisi.',
            'meter_code.required' => 'Meter code wajib diisi.',
        ]);

        $meterCode = trim($validated['meter_code']);

        $duplicateMeter = EnergyMeter::where('esp_unit_id', $energyMeter->esp_unit_id)
            ->where('meter_code', $meterCode)
            ->where('id', '!=', $energyMeter->id)
            ->exists();

        if ($duplicateMeter) {
            throw ValidationException::withMessages([
                'meter_code' => 'Meter code ini sudah digunakan pada ESP Unit ID yang sama.',
            ]);
        }

        $energyMeter->update([
            'name' => $validated['name'],
            'meter_code' => $meterCode,
            'sensor_type' => $validated['sensor_type'] ?? 'PZEM004T',
            'is_active' => $request->boolean('is_active'),
        ]);

        return $this->backToSettings(
            'Sensor listrik berhasil diperbarui.',
            $energyMeter->room_id
        );
    }

    public function toggleSensor(EnergyMeter $energyMeter)
    {
        $this->ensureAdvancedMode();
        $this->ensureSensorOwner($energyMeter);

        $energyMeter->update([
            'is_active' => ! $energyMeter->is_active,
        ]);

        return $this->backToSettings(
            $energyMeter->is_active
                ? 'Sensor listrik berhasil diaktifkan.'
                : 'Sensor listrik berhasil dinonaktifkan.',
            $energyMeter->room_id
        );
    }

    public function destroySensor(EnergyMeter $energyMeter)
    {
        $this->ensureAdvancedMode();
        $this->ensureSensorOwner($energyMeter);

        $roomId = $energyMeter->room_id;

        if ($energyMeter->readings()->exists()) {
            $energyMeter->update([
                'is_active' => false,
            ]);

            return $this->backToSettings(
                'Sensor listrik sudah memiliki riwayat data, jadi tidak dihapus. Sensor dinonaktifkan agar riwayat tetap aman.',
                $roomId
            );
        }

        $energyMeter->delete();

        return $this->backToSettings(
            'Sensor listrik berhasil dihapus.',
            $roomId
        );
    }

    private function createRelayDevice(
        Room $room,
        string $espUnitId,
        string $relayCode,
        string $name
    ): Device {
        $data = [
            'room_id' => $room->id,
            'name' => $name,
            'status' => false,
        ];

        if (Schema::hasColumn('devices', 'user_id')) {
            $data['user_id'] = Auth::id();
        }

        if (Schema::hasColumn('devices', 'type')) {
            $data['type'] = 'relay';
        }

        if (Schema::hasColumn('devices', 'relay_code')) {
            $data['relay_code'] = $relayCode;
        }

        if (Schema::hasColumn('devices', 'esp_unit_id')) {
            $data['esp_unit_id'] = $espUnitId;
        }

        if (Schema::hasColumn('devices', 'esp32_device_id')) {
            $data['esp32_device_id'] = $espUnitId;
        }

        return Device::create($data);
    }

    private function backToSettings(string $message, ?int $roomId = null)
    {
        return redirect()
            ->route('settings')
            ->with('status', $message)
            ->with('success', $message)
            ->with('open_advanced_panel', true)
            ->with('selected_room_id', $roomId);
    }
}