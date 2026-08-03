<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Device;
use App\Models\EnergyDailySummary;
use App\Models\EnergyLog;
use App\Models\EnergyMeter;
use App\Models\Room;
use App\Models\SystemSetting;
use Carbon\Carbon;
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

        /*
         * observed_at disimpan sebagai UTC.
         * Untuk tampilan WIB dan rekap harian, data dikonversi ke Asia/Jakarta.
         */
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
            $result = $this->storeEnergyLog(
                meter: $meter,
                energyData: $energyData,
                observedAt: $observedAt,
                telemetryId: $telemetryId,
            );

            if ($result['created']) {
                $result['daily_summary'] = $this->updateDailySummary(
                    energyMeter: $meter,
                    latestLog: $result['log']
                );
            } else {
                $result['daily_summary'] = null;
            }

            return $result;
        }, 3);

        if ($result['created']) {
            $this->cleanupOldEnergyLogs();
        }

        return response()->json([
            'success' => true,
            'message' => $result['created']
                ? 'Data PZEM berhasil dicatat ke riwayat telemetry dan rekap harian.'
                : 'Telemetry duplikat diterima; riwayat tidak dibuat ulang.',

            'esp_unit_id' => $meter->esp_unit_id,
            'meter_code' => $meter->meter_code,
            'energy_meter_id' => $meter->id,
            'telemetry_id' => $telemetryId,

            'observed_at' => $observedAt->toIso8601String(),
            'observed_at_wib' => $observedAt
                ->timezone('Asia/Jakarta')
                ->format('Y-m-d H:i:s'),

            'created_logs' => $result['created'] ? 1 : 0,
            'duplicate_logs' => $result['created'] ? 0 : 1,

            'data' => $result['log'],
            'daily_summary' => $result['daily_summary'],
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

    private function updateDailySummary(
        EnergyMeter $energyMeter,
        EnergyLog $latestLog
    ): ?EnergyDailySummary {
        if (! Schema::hasTable('energy_daily_summaries')) {
            return null;
        }

        $observedAtWib = Carbon::parse($latestLog->observed_at)
            ->timezone('Asia/Jakarta');

        $summaryDate = $observedAtWib->toDateString();

        $dayStartUtc = Carbon::parse($summaryDate, 'Asia/Jakarta')
            ->startOfDay()
            ->utc();

        $dayEndUtc = Carbon::parse($summaryDate, 'Asia/Jakarta')
            ->endOfDay()
            ->utc();

        $energyValue = (float) ($latestLog->energy ?? 0);
        $voltageValue = (float) ($latestLog->voltage ?? 0);
        $currentValue = (float) ($latestLog->current ?? 0);
        $powerValue = (float) ($latestLog->power ?? 0);

        $tariff = $this->getElectricityTariff((int) $energyMeter->user_id);

        $summary = EnergyDailySummary::firstOrNew([
            'energy_meter_id' => $energyMeter->id,
            'summary_date' => $summaryDate,
        ]);

        if (! $summary->exists) {
            $summary->user_id = $energyMeter->user_id;
            $summary->energy_start = $energyValue;
            $summary->energy_end = $energyValue;
            $summary->usage_kwh = 0;
            $summary->avg_voltage = $voltageValue;
            $summary->max_power = $powerValue;
            $summary->last_voltage = $voltageValue;
            $summary->last_current = $currentValue;
            $summary->last_power = $powerValue;
            $summary->tariff_per_kwh = $tariff;
            $summary->estimated_cost = 0;
            $summary->sample_count = 1;
            $summary->last_observed_at = $latestLog->observed_at;
            $summary->save();

            return $summary;
        }

        $previousLog = EnergyLog::query()
            ->where('energy_meter_id', $energyMeter->id)
            ->where('id', '<', $latestLog->id)
            ->whereBetween('observed_at', [$dayStartUtc, $dayEndUtc])
            ->whereNotNull('energy')
            ->orderByDesc('id')
            ->first();

        $deltaKwh = 0;

        if ($previousLog) {
            $previousEnergy = (float) ($previousLog->energy ?? 0);

            if ($energyValue >= $previousEnergy) {
                $deltaKwh = $energyValue - $previousEnergy;
            } else {
                $deltaKwh = max(0, $energyValue);
            }
        }

        $oldSampleCount = max(1, (int) $summary->sample_count);
        $newSampleCount = $oldSampleCount + 1;

        $summary->energy_end = $energyValue;
        $summary->usage_kwh = round(((float) $summary->usage_kwh) + $deltaKwh, 4);

        $summary->avg_voltage = round(
            ((((float) $summary->avg_voltage) * $oldSampleCount) + $voltageValue) / $newSampleCount,
            2
        );

        $summary->max_power = max((float) $summary->max_power, $powerValue);

        $summary->last_voltage = $voltageValue;
        $summary->last_current = $currentValue;
        $summary->last_power = $powerValue;

        $summary->tariff_per_kwh = $tariff;
        $summary->estimated_cost = round(((float) $summary->usage_kwh) * $tariff, 2);

        $summary->sample_count = $newSampleCount;
        $summary->last_observed_at = $latestLog->observed_at;

        $summary->save();

        return $summary;
    }

    private function getElectricityTariff(int $userId): float
    {
        if (! Schema::hasTable('system_settings')) {
            return 1444;
        }

        $setting = SystemSetting::where('user_id', $userId)->first();

        return (float) ($setting?->electricity_tariff ?? 1444);
    }

    private function cleanupOldEnergyLogs(): void
    {
        try {
            /*
             * Default: simpan data detail 7 hari terakhir.
             * Kalau mau lebih ringan, ganti subDays(7) menjadi subDays(1).
             */
            EnergyLog::query()
                ->where('observed_at', '<', now('Asia/Jakarta')->subDays(7)->utc())
                ->delete();
        } catch (\Throwable $exception) {
            /*
             * Cleanup tidak boleh membuat telemetry gagal.
             */
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