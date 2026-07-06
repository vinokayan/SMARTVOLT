<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Device;
use App\Models\EnergyLog;
use App\Models\Room;
use App\Models\SmartvoltNotification;
use App\Models\SystemSetting;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class EnergyApiController extends Controller
{
    private function checkApiKey(Request $request): void
    {
        $configuredApiKey = config('services.iot.api_key');
        $requestApiKey = (string) $request->header('X-API-KEY');

        if (blank($configuredApiKey)) {
            throw new HttpResponseException(response()->json([
                'success' => false,
                'message' => 'IoT API key belum dikonfigurasi di server.',
            ], 500));
        }

        if (! hash_equals((string) $configuredApiKey, $requestApiKey)) {
            throw new HttpResponseException(response()->json([
                'success' => false,
                'message' => 'Invalid API Key.',
            ], 401));
        }
    }

    public function store(Request $request)
    {
        $this->checkApiKey($request);

        $validated = $request->validate([
            'esp_unit_id' => ['nullable', 'string', 'max:100', 'required_without:esp32_device_id'],
            'esp32_device_id' => ['nullable', 'string', 'max:100', 'required_without:esp_unit_id'],
            'relay_code' => ['nullable', 'string', 'max:100'],
            'voltage' => ['required', 'numeric'],
            'current' => ['required', 'numeric'],
            'power' => ['required', 'numeric'],
            'energy' => ['required', 'numeric'],
            'frequency' => ['nullable', 'numeric'],
            'power_factor' => ['nullable', 'numeric'],
        ]);

        $espIdentifier = $validated['esp_unit_id'] ?? $validated['esp32_device_id'];

        $devicesQuery = $this->deviceQueryForEsp($espIdentifier);

        if (! empty($validated['relay_code']) && Schema::hasColumn('devices', 'relay_code')) {
            $devicesQuery->where('relay_code', $validated['relay_code']);
        }

        $devices = $this->applyRelayOrder($devicesQuery)->get();

        if ($devices->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Device tidak ditemukan. Pastikan ESP Unit ID dan relay sudah terdaftar.',
                'esp_unit_id' => $espIdentifier,
                'relay_code' => $validated['relay_code'] ?? null,
            ], 404);
        }

        $energyData = [
            'voltage' => $validated['voltage'],
            'current' => $validated['current'],
            'power' => $validated['power'],
            'energy' => $validated['energy'],
            'frequency' => $validated['frequency'] ?? null,
            'power_factor' => $validated['power_factor'] ?? null,
        ];

        $logs = $devices->map(function (Device $device) use ($energyData) {
            return $this->saveLatestEnergyLog($device, $energyData);
        });

        $this->createPowerLimitNotification($devices->first(), $energyData, $espIdentifier);

        return response()->json([
            'success' => true,
            'message' => 'Data sensor berhasil disimpan.',
            'esp_unit_id' => $espIdentifier,
            'relay_code' => $validated['relay_code'] ?? null,
            'updated_devices' => $devices->count(),
            'data' => $logs->values(),
        ]);
    }

    public function command(Request $request, string $esp32_device_id)
    {
        $this->checkApiKey($request);

        $device = $this->applyRelayOrder(
            $this->deviceQueryForEsp($esp32_device_id)
        )->first();

        if (! $device) {
            return response()->json([
                'success' => false,
                'message' => 'Device tidak ditemukan.',
            ], 404);
        }

        $systemSetting = $this->resolveSystemSetting($device);
        $isOn = $this->isDeviceOn($device->status);

        return response()->json([
            'success' => true,
            'esp_unit_id' => $device->esp_unit_id ?? $device->esp32_device_id,
            'esp32_device_id' => $device->esp32_device_id,
            'device_id' => $device->id,
            'device_name' => $device->name,
            'relay_code' => (string) ($device->relay_code ?? '1'),
            'relay' => $isOn,
            'status' => $isOn ? 'ON' : 'OFF',
            'refresh_interval' => $systemSetting?->refresh_interval ?? 5,
        ]);
    }

    public function commands(Request $request, string $esp32_device_id)
    {
        $this->checkApiKey($request);

        $devices = $this->applyRelayOrder(
            $this->deviceQueryForEsp($esp32_device_id)
        )->get();

        if ($devices->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak ada device untuk ESP32 ini.',
                'esp_unit_id' => $esp32_device_id,
                'esp32_device_id' => $esp32_device_id,
                'refresh_interval' => 5,
                'relays' => [],
            ], 404);
        }

        $firstDevice = $devices->first();
        $systemSetting = $this->resolveSystemSetting($firstDevice);

        $relays = $devices->map(function (Device $device) {
            $isOn = $this->isDeviceOn($device->status);

            return [
                'device_id' => $device->id,
                'device_name' => $device->name,
                'room_id' => $device->room_id,
                'room_name' => $device->room?->name,
                'relay_code' => (string) ($device->relay_code ?? $device->id),
                'relay' => $isOn,
                'status' => $isOn ? 'ON' : 'OFF',
            ];
        })->values();

        return response()->json([
            'success' => true,
            'esp_unit_id' => $firstDevice->esp_unit_id ?? $firstDevice->esp32_device_id,
            'esp32_device_id' => $firstDevice->esp32_device_id,
            'refresh_interval' => $systemSetting?->refresh_interval ?? 5,
            'total_relays' => $relays->count(),
            'relays' => $relays,
        ]);
    }

    private function deviceQueryForEsp(string $espIdentifier)
    {
        return Device::query()
            ->with('room')
            ->where(function ($query) use ($espIdentifier) {
                if (Schema::hasColumn('devices', 'esp_unit_id')) {
                    $query->where('esp_unit_id', $espIdentifier);
                }

                if (Schema::hasColumn('devices', 'esp32_device_id')) {
                    $query->orWhere('esp32_device_id', $espIdentifier);
                }
            });
    }

    private function applyRelayOrder($query)
    {
        if (Schema::hasColumn('devices', 'relay_code')) {
            return $query->orderBy('relay_code');
        }

        return $query->orderBy('id');
    }

    private function saveLatestEnergyLog(Device $device, array $energyData): EnergyLog
    {
        $log = EnergyLog::where('device_id', $device->id)
            ->latest('id')
            ->first();

        if ($log) {
            $log->update($energyData);
        } else {
            $log = EnergyLog::create(array_merge([
                'device_id' => $device->id,
            ], $energyData));
        }

        EnergyLog::where('device_id', $device->id)
            ->where('id', '!=', $log->id)
            ->delete();

        return $log->refresh();
    }

    private function createPowerLimitNotification(Device $device, array $energyData, string $espIdentifier): void
    {
        if (! Schema::hasTable('smartvolt_notifications')) {
            return;
        }

        $power = (float) ($energyData['power'] ?? 0);
        $systemSetting = $this->resolveSystemSetting($device);
        $powerLimit = (float) ($systemSetting?->power_limit ?? 0);

        if ($powerLimit <= 0 || $power < $powerLimit) {
            return;
        }

        $device->loadMissing('room');

        $userId = $device->room?->user_id ?? $systemSetting?->user_id;

        if (! $userId) {
            return;
        }

        $recentNotificationExists = SmartvoltNotification::where('user_id', $userId)
            ->where('device_id', $device->id)
            ->where('type', 'power_limit_exceeded')
            ->where('created_at', '>=', now()->subMinutes(15))
            ->exists();

        if ($recentNotificationExists) {
            return;
        }

        SmartvoltNotification::create([
            'user_id' => $userId,
            'device_id' => $device->id,
            'type' => 'power_limit_exceeded',
            'severity' => 'danger',
            'title' => 'Daya melebihi batas',
            'message' => sprintf(
                'ESP %s membaca daya %.0f W, melewati batas %.0f W. Matikan beberapa perangkat agar MCB tidak turun.',
                $espIdentifier,
                $power,
                $powerLimit
            ),
            'data' => [
                'power' => $power,
                'power_limit' => $powerLimit,
                'device_id' => $device->id,
                'device_name' => $device->name,
                'esp_identifier' => $espIdentifier,
                'room_id' => $device->room_id,
                'room_name' => $device->room?->name,
            ],
        ]);
    }

    private function isDeviceOn($status): bool
    {
        if (is_bool($status)) {
            return $status;
        }

        if (is_numeric($status)) {
            return (int) $status === 1;
        }

        return in_array(strtolower((string) $status), [
            'on',
            'nyala',
            'active',
            'aktif',
            'true',
            '1',
        ], true);
    }

    private function resolveSystemSetting(Device $device): ?SystemSetting
    {
        if (Schema::hasColumn('system_settings', 'device_id')) {
            $systemSetting = SystemSetting::where('device_id', $device->id)->first();

            if ($systemSetting) {
                return $systemSetting;
            }
        }

        if (! empty($device->room_id)) {
            $roomUserId = Room::where('id', $device->room_id)->value('user_id');

            if ($roomUserId) {
                return SystemSetting::where('user_id', $roomUserId)->first();
            }
        }

        return null;
    }
}