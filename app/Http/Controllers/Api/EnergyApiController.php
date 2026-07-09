<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Device;
use App\Models\EnergyLog;
use App\Models\EnergyMeter;
use App\Models\Room;
use App\Models\SystemSetting;
use Carbon\CarbonImmutable;
use Illuminate\Database\QueryException;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class EnergyApiController extends Controller
{
    private function checkApiKey(Request $request): void
    {
        $configuredApiKey = (string) config('services.iot.api_key');
        $requestApiKey = (string) $request->header('X-API-KEY');

        if ($configuredApiKey === '') {
            throw new HttpResponseException(response()->json([
                'success' => false,
                'message' => 'IoT API key belum dikonfigurasi di server.',
            ], 500));
        }

        if (! hash_equals($configuredApiKey, $requestApiKey)) {
            throw new HttpResponseException(response()->json([
                'success' => false,
                'message' => 'Invalid API Key.',
            ], 401));
        }
    }

    /*
     * PZEM disimpan ke satu energy meter.
     * Tidak lagi disalin ke semua relay/device dalam ESP yang sama.
     */
    public function store(Request $request)
    {
        $this->checkApiKey($request);

        $validated = $request->validate([
            'esp_unit_id' => [
                'nullable',
                'string',
                'max:100',
                'required_without:esp32_device_id',
            ],
            'esp32_device_id' => [
                'nullable',
                'string',
                'max:100',
                'required_without:esp_unit_id',
            ],
            'meter_code' => ['nullable', 'string', 'max:50'],
            'telemetry_id' => ['nullable', 'string', 'max:100'],
            'observed_at' => ['nullable', 'date'],

            'voltage' => ['required', 'numeric'],
            'current' => ['required', 'numeric'],
            'power' => ['required', 'numeric'],
            'energy' => ['required', 'numeric'],
            'frequency' => ['nullable', 'numeric'],
            'power_factor' => ['nullable', 'numeric'],
        ]);

        $espUnitId = trim((string) (
            $validated['esp_unit_id']
            ?? $validated['esp32_device_id']
        ));

        $meterCode = trim((string) ($validated['meter_code'] ?? 'main'));

        if ($meterCode === '') {
            $meterCode = 'main';
        }

        $meter = EnergyMeter::query()
            ->where('esp_unit_id', $espUnitId)
            ->where('meter_code', $meterCode)
            ->where('is_active', true)
            ->first();

        if (! $meter) {
            return response()->json([
                'success' => false,
                'message' => 'Energy meter tidak ditemukan atau tidak aktif.',
                'esp_unit_id' => $espUnitId,
                'meter_code' => $meterCode,
            ], 404);
        }

        $telemetryId = filled($validated['telemetry_id'] ?? null)
            ? (string) $validated['telemetry_id']
            : null;

        $observedAt = isset($validated['observed_at'])
            ? CarbonImmutable::parse($validated['observed_at'])->utc()
            : CarbonImmutable::now('UTC');

        $energyData = [
            'voltage' => (float) $validated['voltage'],
            'current' => (float) $validated['current'],
            'power' => (float) $validated['power'],
            'energy' => (float) $validated['energy'],
            'frequency' => isset($validated['frequency'])
                ? (float) $validated['frequency']
                : null,
            'power_factor' => isset($validated['power_factor'])
                ? (float) $validated['power_factor']
                : null,
        ];

        $result = DB::transaction(function () use (
            $meter,
            $energyData,
            $observedAt,
            $telemetryId
        ) {
            return $this->storeEnergyLog(
                meter: $meter,
                energyData: $energyData,
                observedAt: $observedAt,
                telemetryId: $telemetryId,
            );
        }, 3);

        return response()->json([
            'success' => true,
            'message' => $result['created']
                ? 'Data PZEM berhasil dicatat ke riwayat telemetry.'
                : 'Telemetry duplikat diterima; riwayat tidak dibuat ulang.',

            'esp_unit_id' => $meter->esp_unit_id,
            'meter_code' => $meter->meter_code,
            'energy_meter_id' => $meter->id,
            'telemetry_id' => $telemetryId,
            'observed_at' => $observedAt->toIso8601String(),

            'created_logs' => $result['created'] ? 1 : 0,
            'duplicate_logs' => $result['created'] ? 0 : 1,
            'data' => $result['log'],
        ], $result['created'] ? 201 : 200);
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
                'relay_code' => (string) (
                    $device->relay_code
                    ?? $device->id
                ),
                'relay' => $isOn,
                'status' => $isOn ? 'ON' : 'OFF',
            ];
        })->values();

        return response()->json([
            'success' => true,
            'esp_unit_id' => $firstDevice->esp_unit_id
                ?? $firstDevice->esp32_device_id,
            'esp32_device_id' => $firstDevice->esp32_device_id,
            'refresh_interval' => $systemSetting?->refresh_interval ?? 5,
            'total_relays' => $relays->count(),
            'relays' => $relays,
        ]);
    }

    private function storeEnergyLog(
        EnergyMeter $meter,
        array $energyData,
        CarbonImmutable $observedAt,
        ?string $telemetryId,
    ): array {
        if ($telemetryId !== null) {
            $existing = EnergyLog::query()
                ->where('energy_meter_id', $meter->id)
                ->where('telemetry_id', $telemetryId)
                ->first();

            if ($existing) {
                return [
                    'log' => $existing,
                    'created' => false,
                ];
            }
        }

        $attributes = array_merge([
            'device_id' => null,
            'energy_meter_id' => $meter->id,
            'telemetry_id' => $telemetryId,
            'observed_at' => $observedAt,
        ], $energyData);

        try {
            return [
                'log' => EnergyLog::create($attributes),
                'created' => true,
            ];
        } catch (QueryException $exception) {
            if ($telemetryId !== null) {
                $existing = EnergyLog::query()
                    ->where('energy_meter_id', $meter->id)
                    ->where('telemetry_id', $telemetryId)
                    ->first();

                if ($existing) {
                    return [
                        'log' => $existing,
                        'created' => false,
                    ];
                }
            }

            throw $exception;
        }
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
            $systemSetting = SystemSetting::where(
                'device_id',
                $device->id
            )->first();

            if ($systemSetting) {
                return $systemSetting;
            }
        }

        if (! empty($device->room_id)) {
            $roomUserId = Room::where(
                'id',
                $device->room_id
            )->value('user_id');

            if ($roomUserId) {
                return SystemSetting::where(
                    'user_id',
                    $roomUserId
                )->first();
            }
        }

        return null;
    }
}