<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Device;
use App\Models\EnergyDailySummary;
use App\Models\EnergyLog;
use App\Models\EnergyMeter;
use App\Models\Room;
use App\Models\SystemSetting;
use App\Services\NilmFeatureService;
use App\Services\NilmService;
use Carbon\CarbonImmutable;
use Illuminate\Database\QueryException;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class EnergyApiController extends Controller
{
    private const DEFAULT_TIMEZONE = 'Asia/Jakarta';
    private const DEFAULT_TARIFF = 1444;
    private const RAW_LOG_RETENTION_DAYS = 30;

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
            'meter_code' => [
                'nullable',
                'string',
                'max:50',
            ],
            'telemetry_id' => [
                'nullable',
                'string',
                'max:100',
            ],
            'observed_at' => [
                'nullable',
                'date',
            ],

            /*
             * Rentang berikut mencegah nilai negatif, korup, atau tidak masuk
             * akal masuk ke database. Sesuaikan batas maksimum apabila
             * perangkat SmartVolt dikembangkan untuk kapasitas yang lebih besar.
             */
            'voltage' => [
                'required',
                'numeric',
                'between:0,300',
            ],
            'current' => [
                'required',
                'numeric',
                'between:0,100',
            ],
            'power' => [
                'required',
                'numeric',
                'between:0,30000',
            ],
            'energy' => [
                'required',
                'numeric',
                'min:0',
            ],
            'frequency' => [
                'nullable',
                'numeric',
                'between:40,70',
            ],
            'power_factor' => [
                'nullable',
                'numeric',
                'between:0,1',
            ],
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
            ? trim((string) $validated['telemetry_id'])
            : null;

        /*
         * SmartVolt saat ini berjalan pada server lokal dengan timezone
         * Asia/Jakarta. observed_at disimpan menggunakan timezone aplikasi
         * agar konsisten dengan created_at dan updated_at.
         *
         * Apabila observed_at dikirim dengan offset, misalnya +07:00 atau Z,
         * Carbon akan membacanya lalu mengonversi ke timezone aplikasi.
         */
        $timezone = $this->applicationTimezone();

        $observedAt = isset($validated['observed_at'])
            ? CarbonImmutable::parse(
                (string) $validated['observed_at'],
                $timezone
            )->setTimezone($timezone)
            : CarbonImmutable::now($timezone);

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
                /*
                 * Rekap dibangun ulang dari seluruh log pada hari yang sama.
                 * Cara ini lebih aman untuk data terlambat, urutan request yang
                 * berubah, dan reset nilai energi PZEM.
                 */
                $result['daily_summary'] = $this->rebuildDailySummary(
                    energyMeter: $meter,
                    latestLog: $result['log'],
                );
            } else {
                $result['daily_summary'] = null;
            }

            return $result;
        }, 3);

        /*
         * Cleanup dibatasi satu kali per hari. Telemetry tidak lagi menjalankan
         * DELETE pada setiap pengiriman satu menit.
         */
        if ($result['created']) {
            $this->cleanupOldEnergyLogsOncePerDay();
        }
        /*
 * Jalankan analisis NILM setelah telemetry berhasil tersimpan.
 *
 * AI tidak boleh mengganggu proses utama IoT.
 * Jika prediksi gagal, EnergyLog tetap aman tersimpan.
 */
if ($result['created']) {

    try {

        app(NilmFeatureService::class)
            ->generate($result['log']);


        app(NilmService::class)
            ->predict($result['log']);


    } catch (\Throwable $exception) {

        Log::error('NILM pipeline gagal.', [

            'message' => $exception->getMessage(),

            'energy_log_id' => $result['log']->id ?? null,

        ]);

    }
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

            'observed_at' => $observedAt->format('Y-m-d H:i:s'),
            'observed_at_iso' => $observedAt->toIso8601String(),
            'timezone' => $timezone,

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
            'esp_unit_id' => $device->esp_unit_id
                ?? $device->esp32_device_id,
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

    public function acknowledge(Request $request, string $esp_unit_id)
    {
        $this->checkApiKey($request);

        $validated = $request->validate([
            'command_id' => ['nullable', 'string', 'max:100'],
            'relay_code' => ['required', 'string', 'max:20'],
            'requested_state' => ['nullable', 'boolean'],
            'actual_state' => ['required', 'boolean'],
            'success' => ['required', 'boolean'],
            'message' => ['nullable', 'string', 'max:255'],
        ]);

        $relayCode = trim((string) $validated['relay_code']);
        $commandId = trim((string) ($validated['command_id'] ?? ''));

        $device = $this->deviceQueryForEsp($esp_unit_id)
            ->where('relay_code', $relayCode)
            ->first();

        if (! $device) {
            return response()->json([
                'success' => false,
                'message' => 'Perangkat tidak ditemukan.',
            ], 404);
        }

        $hasNewerPendingCommand = filled($device->last_command_id)
            && $commandId !== ''
            && (string) $device->last_command_id !== $commandId
            && $device->pending_state !== null;

        if ($hasNewerPendingCommand) {
            $device->update([
                'is_online' => true,
                'last_seen_at' => now(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Konfirmasi lama diabaikan karena ada perintah yang lebih baru.',
            ], 409);
        }

        $actualState = (bool) $validated['actual_state'];
        $commandSuccess = (bool) $validated['success'];

        $device->update([
            'status' => $actualState,
            'is_online' => true,
            'last_seen_at' => now(),
            'last_confirmed_at' => now(),
            'last_ack_command_id' => $commandId !== '' ? $commandId : null,
            'pending_state' => null,
            'last_command_success' => $commandSuccess,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Status perangkat berhasil dikonfirmasi.',
            'device_id' => $device->id,
            'esp_unit_id' => $esp_unit_id,
            'relay_code' => $relayCode,
            'actual_state' => $actualState,
            'command_success' => $commandSuccess,
            'confirmed_at' => $device->fresh()->last_confirmed_at?->toIso8601String(),
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
            /*
             * Jika database mempunyai unique index untuk
             * energy_meter_id + telemetry_id, request bersamaan dapat
             * menyebabkan duplicate-key. Ambil record yang sudah dibuat.
             */
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

    private function rebuildDailySummary(
        EnergyMeter $energyMeter,
        EnergyLog $latestLog
    ): ?EnergyDailySummary {
        if (! Schema::hasTable('energy_daily_summaries')) {
            return null;
        }

        $timezone = $this->applicationTimezone();

        $rawObservedAt = $latestLog->getRawOriginal('observed_at')
            ?: $latestLog->observed_at;

        $observedAtLocal = CarbonImmutable::parse(
            (string) $rawObservedAt,
            $timezone
        )->setTimezone($timezone);

        $summaryDate = $observedAtLocal->toDateString();

        $dayStart = $observedAtLocal->startOfDay();
        $dayEnd = $observedAtLocal->endOfDay();

        $logs = EnergyLog::query()
            ->where('energy_meter_id', $energyMeter->id)
            ->whereBetween('observed_at', [$dayStart, $dayEnd])
            ->whereNotNull('energy')
            ->orderBy('observed_at')
            ->orderBy('id')
            ->get([
                'id',
                'observed_at',
                'voltage',
                'current',
                'power',
                'energy',
            ]);

        if ($logs->isEmpty()) {
            return null;
        }

        $usageKwh = 0.0;
        $previousEnergy = null;
        $voltageTotal = 0.0;
        $maxPower = 0.0;

        foreach ($logs as $log) {
            $currentEnergy = max(0, (float) $log->energy);
            $voltage = max(0, (float) $log->voltage);
            $power = max(0, (float) $log->power);

            $voltageTotal += $voltage;
            $maxPower = max($maxPower, $power);

            if ($previousEnergy !== null) {
                if ($currentEnergy >= $previousEnergy) {
                    /*
                     * Nilai energy dari PZEM bersifat kumulatif.
                     * Pemakaian adalah selisih positif antarpembacaan.
                     */
                    $usageKwh += $currentEnergy - $previousEnergy;
                } else {
                    /*
                     * Penurunan energy dapat berarti reset PZEM, pergantian
                     * meter, data lama yang datang terlambat, atau data uji.
                     * Nilai setelah turun tidak langsung dihitung sebagai
                     * pemakaian baru agar biaya tidak melonjak.
                     */
                    if ((int) $log->id === (int) $latestLog->id) {
                        Log::notice('Penurunan energi PZEM terdeteksi.', [
                            'energy_meter_id' => $energyMeter->id,
                            'previous_energy' => $previousEnergy,
                            'current_energy' => $currentEnergy,
                            'observed_at' => $log->observed_at,
                        ]);
                    }
                }
            }

            $previousEnergy = $currentEnergy;
        }

        $firstLog = $logs->first();
        $lastLog = $logs->last();
        $sampleCount = $logs->count();

        $tariff = $this->getElectricityTariff(
            (int) $energyMeter->user_id
        );

        /*
         * Row yang sudah ada dikunci selama transaksi agar dua request
         * tidak saling menimpa rekap yang sama.
         */
        $summary = EnergyDailySummary::query()
            ->where('energy_meter_id', $energyMeter->id)
            ->where('summary_date', $summaryDate)
            ->lockForUpdate()
            ->first();

        if (! $summary) {
            $summary = new EnergyDailySummary();
            $summary->energy_meter_id = $energyMeter->id;
            $summary->summary_date = $summaryDate;
        }

        $summary->user_id = $energyMeter->user_id;
        $summary->energy_start = max(0, (float) $firstLog->energy);
        $summary->energy_end = max(0, (float) $lastLog->energy);
        $summary->usage_kwh = round($usageKwh, 6);
        $summary->avg_voltage = round(
            $voltageTotal / max(1, $sampleCount),
            2
        );
        $summary->max_power = round($maxPower, 3);
        $summary->last_voltage = max(0, (float) $lastLog->voltage);
        $summary->last_current = max(0, (float) $lastLog->current);
        $summary->last_power = max(0, (float) $lastLog->power);
        $summary->tariff_per_kwh = $tariff;
        $summary->estimated_cost = round($usageKwh * $tariff, 2);
        $summary->sample_count = $sampleCount;
        $summary->last_observed_at = $lastLog->observed_at;
        $summary->save();

        return $summary->fresh();
    }

    private function getElectricityTariff(int $userId): float
    {
        if (! Schema::hasTable('system_settings')) {
            return self::DEFAULT_TARIFF;
        }

        $setting = SystemSetting::query()
            ->where('user_id', $userId)
            ->first();

        return max(
            0,
            (float) ($setting?->electricity_tariff
                ?? self::DEFAULT_TARIFF)
        );
    }

    private function cleanupOldEnergyLogsOncePerDay(): void
    {
        try {
            $cacheKey = 'smartvolt:cleanup-energy-logs';

            /*
             * Hanya request pertama dalam 24 jam yang menjalankan cleanup.
             */
            if (! Cache::add(
                $cacheKey,
                true,
                now()->addDay()
            )) {
                return;
            }

            $timezone = $this->applicationTimezone();
            $cutoff = CarbonImmutable::now($timezone)
                ->subDays(self::RAW_LOG_RETENTION_DAYS);

            EnergyLog::query()
                ->where('observed_at', '<', $cutoff)
                ->delete();
        } catch (\Throwable $exception) {
            /*
             * Cleanup tidak boleh membuat telemetry gagal, tetapi kesalahan
             * tetap dicatat agar teknisi dapat memeriksanya.
             */
            Log::warning('Cleanup energy_logs gagal.', [
                'message' => $exception->getMessage(),
            ]);
        }
    }

    private function deviceQueryForEsp(string $espIdentifier)
    {
        $hasEspUnitId = Schema::hasColumn('devices', 'esp_unit_id');
        $hasEsp32DeviceId = Schema::hasColumn(
            'devices',
            'esp32_device_id'
        );

        return Device::query()
            ->with('room')
            ->where(function ($query) use (
                $espIdentifier,
                $hasEspUnitId,
                $hasEsp32DeviceId
            ) {
                if ($hasEspUnitId) {
                    $query->where('esp_unit_id', $espIdentifier);
                }

                if ($hasEsp32DeviceId) {
                    $method = $hasEspUnitId ? 'orWhere' : 'where';
                    $query->{$method}(
                        'esp32_device_id',
                        $espIdentifier
                    );
                }

                /*
                 * Mencegah seluruh device terbaca apabila kedua kolom tidak ada.
                 */
                if (! $hasEspUnitId && ! $hasEsp32DeviceId) {
                    $query->whereRaw('1 = 0');
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

        return in_array(strtolower(trim((string) $status)), [
            'on',
            'nyala',
            'active',
            'aktif',
            'true',
            '1',
        ], true);
    }

    private function resolveSystemSetting(
        Device $device
    ): ?SystemSetting {
        if (! Schema::hasTable('system_settings')) {
            return null;
        }

        if (Schema::hasColumn('system_settings', 'device_id')) {
            $systemSetting = SystemSetting::query()
                ->where('device_id', $device->id)
                ->first();

            if ($systemSetting) {
                return $systemSetting;
            }
        }

        if (! empty($device->room_id)) {
            $roomUserId = Room::query()
                ->where('id', $device->room_id)
                ->value('user_id');

            if ($roomUserId) {
                return SystemSetting::query()
                    ->where('user_id', $roomUserId)
                    ->first();
            }
        }

        return null;
    }

    private function applicationTimezone(): string
    {
        $timezone = trim((string) config(
            'app.timezone',
            self::DEFAULT_TIMEZONE
        ));

        return $timezone !== ''
            ? $timezone
            : self::DEFAULT_TIMEZONE;
    }

    /**
 * History Energy + NILM Prediction
 */
public function historyWithNilm(Request $request)
{
    $logs = EnergyLog::with([
        'predictions.deviceClass'
    ])
    ->latest()
    ->limit(50)
    ->get();


    return response()->json([
        'success' => true,
        'data' => $logs->map(function ($log) {

            $prediction = $log->predictions->first();

            return [
                'id' => $log->id,

                'observed_at' => $log->observed_at,

                'voltage' => $log->voltage,

                'current' => $log->current,

                'power' => $log->power,

                'energy' => $log->energy,

                'power_factor' => $log->power_factor,


                'nilm' => $prediction ? [

                    'device' => $prediction->deviceClass->name ?? 'Unknown',

                    'estimated_power' =>
                        $prediction->estimated_power,

                    'confidence' =>
                        $prediction->confidence,

                ] : null,

            ];

        })
    ]);
}
}