<?php

namespace App\Http\Controllers;

use App\Models\EnergyLog;
use App\Models\EnergyMeter;
use App\Models\Room;
use App\Models\SystemSetting;
use App\Services\EnergyUsageCalculator;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

class DashboardController extends Controller
{
    private const DEFAULT_TIMEZONE = 'Asia/Jakarta';
    private const DEFAULT_TARIFF = 1444;
    private const DEFAULT_POWER_LIMIT = 1300;
    private const DEFAULT_REFRESH_INTERVAL = 30;

    public function __construct(
        private readonly EnergyUsageCalculator $energyUsageCalculator
    ) {
    }

    public function index()
    {
        $rooms = $this->getUserRooms();
        $dashboardData = $this->buildDashboardData($rooms);

        return view('dashboard', [
            'dashboardData' => $dashboardData,
            'rooms' => $rooms,
        ]);
    }

    public function data()
    {
        $rooms = $this->getUserRooms();

        return response()->json(
            $this->buildDashboardData($rooms)
        );
    }

    private function getUserRooms(): Collection
    {
        return Room::query()
            ->with([
                'devices' => function ($query) {
                    $query->orderBy('name');
                },
            ])
            ->where('user_id', Auth::id())
            ->orderBy('name')
            ->get();
    }

    private function buildDashboardData(?Collection $rooms = null): array
    {
        $rooms = $rooms ?? $this->getUserRooms();

        $devices = $rooms
            ->pluck('devices')
            ->flatten()
            ->values();

        $settings = SystemSetting::query()
            ->where('user_id', Auth::id())
            ->first();

        $electricityTariff = max(
            0,
            (float) ($settings?->electricity_tariff ?? self::DEFAULT_TARIFF)
        );

        $powerLimit = max(
            1,
            (int) ($settings?->power_limit ?? self::DEFAULT_POWER_LIMIT)
        );

        $refreshInterval = min(
            60,
            max(
                10,
                (int) ($settings?->refresh_interval ?? self::DEFAULT_REFRESH_INTERVAL)
            )
        );

        $meters = Schema::hasTable('energy_meters')
            ? EnergyMeter::query()
                ->with('room')
                ->where('user_id', Auth::id())
                ->where('is_active', true)
                ->orderBy('id')
                ->get()
            : collect();

        $meterIds = $meters->pluck('id');

        $timezone = $this->applicationTimezone();
        $now = Carbon::now($timezone);
        $todayStart = $now->copy()->startOfDay();
        $yesterdayStart = $todayStart->copy()->subDay();
        $yesterdayEnd = $todayStart->copy()->subSecond();
        $monthStart = $now->copy()->startOfMonth();

        $todayLogs = $this->logsForPeriod(
            $meterIds,
            $todayStart,
            $now
        );

        $latestReadings = $this->latestReadings($meterIds);
        $onlineTimeoutMinutes = $this->onlineTimeoutMinutes();
        $sensorThreshold = $now->copy()->subMinutes($onlineTimeoutMinutes);
        $deviceStatusThreshold = $now->copy()->subMinutes($onlineTimeoutMinutes);

        $freshReadings = $latestReadings->filter(function (EnergyLog $log) use ($sensorThreshold) {
            return $log->created_at
                && $log->created_at->greaterThanOrEqualTo($sensorThreshold);
        });

        $hasFreshSensorData = $freshReadings->isNotEmpty();

        $currentPower = $hasFreshSensorData
            ? (float) $freshReadings->sum(
                fn (EnergyLog $log) => max(0, (float) ($log->power ?? 0))
            )
            : null;

        $totalEnergyToday = $this->energyUsageCalculator->calculate(
            (int) Auth::id(),
            $meterIds,
            $todayStart,
            $now
        );

        $totalEnergyYesterday = $this->energyUsageCalculator->calculate(
            (int) Auth::id(),
            $meterIds,
            $yesterdayStart,
            $yesterdayEnd
        );

        $energyComparison = null;

        if ($totalEnergyYesterday > 0 && $todayLogs->isNotEmpty()) {
            $energyComparison = round(
                (($totalEnergyToday - $totalEnergyYesterday) / $totalEnergyYesterday) * 100,
                1
            );
        }

        $monthlyEstimation = $this->buildMonthlyEstimation(
            $meters,
            $monthStart,
            $now,
            $electricityTariff
        );

        $loadPercentage = $currentPower !== null
            ? min(100, round(($currentPower / $powerLimit) * 100, 1))
            : null;

        $loadStatus = $loadPercentage === null
            ? 'unknown'
            : match (true) {
                $loadPercentage >= 90 => 'danger',
                $loadPercentage >= 70 => 'warning',
                default => 'normal',
            };

        $latestReceivedAt = $latestReadings
            ->sortByDesc('created_at')
            ->first()?->created_at;

        $latestObservedAt = $latestReadings
            ->sortByDesc('observed_at')
            ->first()?->observed_at;

        $chartEnd = $hasFreshSensorData
            ? $now->copy()
            : ($latestObservedAt?->copy() ?? $now->copy());

        $chartStart = $chartEnd->copy()->subHours(24);
        $chartLogs = $this->logsForPeriod($meterIds, $chartStart, $chartEnd);
        $chart = $this->buildPowerChart(
            $chartLogs,
            $chartStart,
            $chartEnd,
            ! $hasFreshSensorData && $chartLogs->isNotEmpty()
        );

        $espIds = $devices
            ->map(function ($device) {
                return trim(
                    (string) ($device->esp_unit_id ?: $device->esp32_device_id)
                );
            })
            ->filter()
            ->unique()
            ->values();

        $onlineEspUnitIds = $this->resolveOnlineEspUnitIds(
            $devices,
            $deviceStatusThreshold
        );

        $hasAnyEspOnline = $onlineEspUnitIds->isNotEmpty();
        $hasAnyMeter = $meters->isNotEmpty();

        $onlineDevices = $devices
            ->filter(function ($device) use ($onlineEspUnitIds) {
                $espUid = trim(
                    (string) ($device->esp_unit_id ?: $device->esp32_device_id)
                );

                return $espUid !== ''
                    && $onlineEspUnitIds->contains($espUid);
            })
            ->values();

        $confirmedDevices = $devices
            ->filter(fn ($device) => $this->deviceStatusIsCurrent(
                $device,
                $deviceStatusThreshold
            ))
            ->values();

        $deviceStatusAvailable = $devices->isEmpty()
            || $confirmedDevices->count() === $devices->count();

        $activeDeviceCount = $deviceStatusAvailable
            ? $confirmedDevices
                ->filter(fn ($device) => $this->isDeviceOn($device->status ?? null))
                ->count()
            : null;

        $latestDeviceConfirmationAt = $confirmedDevices
            ->sortByDesc('last_confirmed_at')
            ->first()?->last_confirmed_at;

        $roomPower = $freshReadings
            ->filter(fn (EnergyLog $log) => $log->energyMeter?->room_id)
            ->groupBy(fn (EnergyLog $log) => (int) $log->energyMeter->room_id)
            ->map(fn ($logs) => round(
                (float) $logs->sum(
                    fn (EnergyLog $log) => max(0, (float) ($log->power ?? 0))
                ),
                1
            ));

        /*
         * Ringkasan data listrik terakhir untuk setiap ruangan.
         * Berbeda dengan current_power, data ini tetap tersedia meskipun
         * pembacaannya sudah melewati batas data real-time.
         */
        $roomLatestData = $latestReadings
            ->filter(fn (EnergyLog $log) => $log->energyMeter?->room_id)
            ->groupBy(fn (EnergyLog $log) => (int) $log->energyMeter->room_id)
            ->map(function (Collection $logs) {
                $latestAt = $logs
                    ->map(fn (EnergyLog $log) => $log->observed_at ?? $log->created_at)
                    ->filter()
                    ->sortByDesc(fn (CarbonInterface $date) => $date->getTimestamp())
                    ->first();

                return [
                    'power' => round(
                        (float) $logs->sum(
                            fn (EnergyLog $log) => max(0, (float) ($log->power ?? 0))
                        ),
                        1
                    ),
                    'updated_at' => $latestAt,
                ];
            });

        $latestReceivedHuman = $latestReceivedAt
            ? $latestReceivedAt->copy()->locale('id')->diffForHumans()
            : 'Belum ada data';

        $systemTitle = $hasAnyEspOnline
            ? 'SmartVolt terhubung'
            : 'SmartVolt belum terhubung';

        $systemMessage = match (true) {
            $hasAnyEspOnline && $hasFreshSensorData => 'SmartVolt terhubung dan data terbaru sudah tersedia.',
            $hasAnyEspOnline => 'SmartVolt terhubung. Data listrik terbaru belum masuk.',
            $latestReceivedAt !== null => 'Data terakhir diperbarui '
                . $latestReceivedHuman
                . '. Hubungkan kembali SmartVolt untuk melihat kondisi terbaru.',
            default => 'Hubungkan SmartVolt untuk mulai melihat data listrik.',
        };

        return [
            'stats' => [
                'total_energy_today' => round($totalEnergyToday, 3),
                'energy_today_available' => $todayLogs->isNotEmpty(),
                'total_energy_yesterday' => round($totalEnergyYesterday, 3),
                'energy_comparison_percent' => $energyComparison,
                'current_power' => $currentPower !== null
                    ? round($currentPower, 1)
                    : null,
                'current_power_available' => $hasFreshSensorData,
                'power_limit' => $powerLimit,
                'load_percentage' => $loadPercentage,
                'load_status' => $loadStatus,
                'monthly_energy_usage' => round(
                    $monthlyEstimation['usage_kwh'],
                    4
                ),
                'monthly_estimated_cost' => round(
                    $monthlyEstimation['estimated_cost']
                ),
                'electricity_tariff' => round($electricityTariff, 2),
                'active_meters' => $meters->count(),
                'total_rooms' => $rooms->count(),
                'total_devices' => $devices->count(),
                'active_devices' => $activeDeviceCount,
                'device_status_available' => $deviceStatusAvailable,
                'online_devices' => $onlineDevices->count(),
            ],

            'settings' => [
                'refresh_interval' => $refreshInterval,
                'power_limit' => $powerLimit,
            ],

            'system' => [
                'connected' => $hasAnyEspOnline,
                'has_meter' => $hasAnyMeter,
                'has_data' => $latestReadings->isNotEmpty(),
                'has_fresh_data' => $hasFreshSensorData,
                'esp_online' => $hasAnyEspOnline,
                'online_esp_unit_ids' => $onlineEspUnitIds->values(),
                'online_esp_count' => $onlineEspUnitIds->count(),
                'registered_esp_count' => $espIds->count(),
                'device_status_available' => $deviceStatusAvailable,
                'status_title' => $systemTitle,
                'status_message' => $systemMessage,
                'latest_received_at' => $latestReceivedAt?->toIso8601String(),
                'latest_received_human' => $latestReceivedHuman,
                'latest_device_confirmation_at' => $latestDeviceConfirmationAt?->toIso8601String(),
                'latest_device_confirmation_human' => $latestDeviceConfirmationAt
                    ? $latestDeviceConfirmationAt->copy()->locale('id')->diffForHumans()
                    : 'Belum ada konfirmasi',
                'online_timeout_minutes' => $onlineTimeoutMinutes,
            ],

            'monthly_estimation' => $monthlyEstimation,
            'chart' => $chart,

            'recent_readings' => $this->recentReadings($meterIds, 5)
                ->map(function (EnergyLog $log) {
                    return [
                        'id' => $log->id,
                        'room_name' => $log->energyMeter?->room?->name ?? '-',
                        'meter_name' => $log->energyMeter?->name ?? '-',
                        'observed_at' => $this->formatDateTimeIndonesia($log->observed_at),
                        'voltage' => round((float) ($log->voltage ?? 0), 1),
                        'current' => round((float) ($log->current ?? 0), 3),
                        'power' => round((float) ($log->power ?? 0), 1),
                        'energy' => round((float) ($log->energy ?? 0), 4),
                    ];
                })
                ->values(),

            'rooms' => $rooms
                ->filter(fn ($room) => $room->devices->isNotEmpty())
                ->map(function ($room) use (
                    $onlineEspUnitIds,
                    $roomPower,
                    $roomLatestData,
                    $deviceStatusThreshold
                ) {
                    $confirmedDevices = $room->devices
                        ->filter(fn ($device) => $this->deviceStatusIsCurrent(
                            $device,
                            $deviceStatusThreshold
                        ));

                    $statusAvailable = $room->devices->isEmpty()
                        || $confirmedDevices->count() === $room->devices->count();

                    $activeDevices = $statusAvailable
                        ? $confirmedDevices
                            ->filter(fn ($device) => $this->isDeviceOn($device->status ?? null))
                            ->count()
                        : null;

                    $totalDevices = $room->devices->count();
                    $inactiveDevices = $statusAvailable
                        ? max(0, $totalDevices - (int) $activeDevices)
                        : null;

                    $onlineDevices = $room->devices
                        ->filter(function ($device) use ($onlineEspUnitIds) {
                            $espUnitId = trim(
                                (string) ($device->esp_unit_id ?: $device->esp32_device_id)
                            );

                            return $espUnitId !== ''
                                && $onlineEspUnitIds->contains($espUnitId);
                        })
                        ->count();

                    $connectionLabel = match (true) {
                        $totalDevices === 0 => 'Belum ada perangkat',
                        $onlineDevices === $totalDevices => 'Terhubung',
                        $onlineDevices > 0 => 'Sebagian terhubung',
                        default => 'Belum terhubung',
                    };

                    $conditionLabel = match (true) {
                        ! $statusAvailable => 'Belum terhubung',
                        $totalDevices === 0 => 'Belum ada perangkat',
                        $activeDevices === 0 => 'Semua perangkat mati',
                        $activeDevices === $totalDevices => $totalDevices
                            . ' perangkat menyala',
                        default => $activeDevices
                            . ' menyala, '
                            . $inactiveDevices
                            . ' mati',
                    };

                    $latestRoomData = $roomLatestData->get((int) $room->id);
                    $latestRoomAt = $latestRoomData['updated_at'] ?? null;

                    return [
                        'id' => $room->id,
                        'name' => $room->name,
                        'total_devices' => $totalDevices,
                        'active_devices' => $activeDevices,
                        'inactive_devices' => $inactiveDevices,
                        'last_known_active_devices' => $activeDevices,
                        'status_available' => $statusAvailable,
                        'online_devices' => $onlineDevices,
                        'connected' => $onlineDevices > 0,
                        'connection_label' => $connectionLabel,
                        'condition_available' => $statusAvailable,
                        'condition_current' => $statusAvailable,
                        'condition_label' => $conditionLabel,
                        'current_power' => $roomPower->has($room->id)
                            ? (float) $roomPower[$room->id]
                            : null,
                        'last_power' => is_array($latestRoomData)
                            ? (float) ($latestRoomData['power'] ?? 0)
                            : null,
                        'updated_at' => $latestRoomAt?->toIso8601String(),
                        'updated_human' => $latestRoomAt
                            ? $latestRoomAt->copy()->locale('id')->diffForHumans()
                            : 'Belum ada data',
                        'devices' => $room->devices
                            ->map(function ($device) use (
                                $onlineEspUnitIds,
                                $deviceStatusThreshold
                            ) {
                                return $this->devicePayload(
                                    $device,
                                    $onlineEspUnitIds,
                                    $deviceStatusThreshold
                                );
                            })
                            ->values(),
                    ];
                })
                ->values(),

            'devices' => $devices
                ->map(function ($device) use (
                    $onlineEspUnitIds,
                    $deviceStatusThreshold
                ) {
                    return $this->devicePayload(
                        $device,
                        $onlineEspUnitIds,
                        $deviceStatusThreshold
                    );
                })
                ->values(),

            'user' => [
                'name' => Auth::user()?->name ?? 'Pengguna',
                'email' => Auth::user()?->email ?? '',
            ],
        ];
    }

    private function logsForPeriod(
        Collection $meterIds,
        CarbonInterface $startDate,
        CarbonInterface $endDate
    ): Collection {
        if (
            $meterIds->isEmpty()
            || ! Schema::hasTable('energy_logs')
        ) {
            return collect();
        }

        return EnergyLog::query()
            ->whereIn('energy_meter_id', $meterIds)
            ->whereBetween('observed_at', [$startDate, $endDate])
            ->orderBy('observed_at')
            ->orderBy('id')
            ->get();
    }

    private function latestReadings(Collection $meterIds): Collection
    {
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

        return EnergyLog::query()
            ->with('energyMeter.room')
            ->whereIn('id', $latestIds)
            ->get();
    }

    private function recentReadings(
        Collection $meterIds,
        int $limit = 5
    ): Collection {
        if (
            $meterIds->isEmpty()
            || ! Schema::hasTable('energy_logs')
        ) {
            return collect();
        }

        return EnergyLog::query()
            ->with('energyMeter.room')
            ->whereIn('energy_meter_id', $meterIds)
            ->orderByDesc('observed_at')
            ->orderByDesc('id')
            ->limit(max(1, $limit))
            ->get();
    }

    private function buildMonthlyEstimation(
        Collection $meters,
        CarbonInterface $startDate,
        CarbonInterface $endDate,
        float $tariff
    ): array {
        $period = $this->formatDateRangeIndonesia($startDate, $endDate);

        if ($meters->isEmpty()) {
            return [
                'label' => 'Estimasi Tagihan Bulan Ini',
                'period' => $period,
                'usage_kwh' => 0.0,
                'tariff' => round($tariff, 2),
                'estimated_cost' => 0.0,
                'meter_count' => 0,
                'meters' => collect(),
            ];
        }

        /*
         * Dashboard dan Pemakaian Listrik memakai EnergyUsageCalculator yang
         * sama. Hari lampau dapat memakai ringkasan harian, sedangkan hari
         * berjalan tetap dihitung dari telemetry terbaru.
         */
        $meterBreakdown = $meters
            ->map(function (EnergyMeter $meter) use (
                $tariff,
                $startDate,
                $endDate
            ) {
                $usageKwh = $this->energyUsageCalculator->calculate(
                    (int) Auth::id(),
                    collect([$meter->id]),
                    $startDate,
                    $endDate
                );

                return [
                    'energy_meter_id' => $meter->id,
                    'meter_name' => $meter->name,
                    'room_name' => $meter->room?->name ?? '-',
                    'usage_kwh' => round($usageKwh, 4),
                    'estimated_cost' => round(
                        $usageKwh * $tariff,
                        2
                    ),
                ];
            })
            ->values();

        $totalUsageKwh = (float) $meterBreakdown->sum('usage_kwh');

        return [
            'label' => 'Estimasi Tagihan Bulan Ini',
            'period' => $period,
            'usage_kwh' => round(max(0, $totalUsageKwh), 4),
            'tariff' => round($tariff, 2),
            'estimated_cost' => round(
                max(0, $totalUsageKwh * $tariff),
                2
            ),
            'meter_count' => $meters->count(),
            'meters' => $meterBreakdown,
        ];
    }

    private function buildPowerChart(
        Collection $logs,
        CarbonInterface $startDate,
        CarbonInterface $endDate,
        bool $isHistorical = false
    ): array {
        $sameDay = $startDate->isSameDay($endDate);

        $buckets = $logs
            ->groupBy(function (EnergyLog $log) {
                if (! $log->observed_at) {
                    return '-';
                }

                $bucketMinute = intdiv(
                    (int) $log->observed_at->format('i'),
                    15
                ) * 15;

                return $log->observed_at
                    ->copy()
                    ->minute($bucketMinute)
                    ->second(0)
                    ->format('Y-m-d H:i');
            })
            ->map(function ($bucketLogs, $bucketKey) use ($sameDay) {
                $latestPerMeter = $bucketLogs
                    ->groupBy('energy_meter_id')
                    ->map(fn ($meterLogs) => $meterLogs->last())
                    ->values();

                $bucketTime = $bucketKey !== '-'
                    ? Carbon::createFromFormat('Y-m-d H:i', $bucketKey)
                    : null;

                return [
                    'label' => $bucketTime
                        ? $bucketTime
                            ->locale('id')
                            ->translatedFormat($sameDay ? 'H.i' : 'j M, H.i')
                        : '-',
                    'power' => round(
                        (float) $latestPerMeter->sum('power'),
                        2
                    ),
                    'energy' => round(
                        (float) $latestPerMeter->sum('energy'),
                        4
                    ),
                ];
            })
            ->values();

        return [
            'title' => 'Riwayat Daya Terakhir',
            'subtitle' => $isHistorical
                ? 'Menampilkan 24 jam data terakhir yang tersimpan.'
                : 'Menampilkan perubahan daya dalam 24 jam terakhir.',
            'is_historical' => $isHistorical,
            'period_start' => $startDate->toIso8601String(),
            'period_end' => $endDate->toIso8601String(),
            'labels' => $buckets->pluck('label')->values(),
            'power' => $buckets->pluck('power')->values(),
            'energy' => $buckets->pluck('energy')->values(),
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
                    && $device->last_seen_at->greaterThanOrEqualTo($threshold);
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

    private function deviceStatusIsCurrent(
        $device,
        CarbonInterface $threshold
    ): bool {
        return (bool) ($device->is_online ?? false)
            && $device->last_seen_at
            && $device->last_seen_at->greaterThanOrEqualTo($threshold)
            && $device->last_confirmed_at
            && $device->last_confirmed_at->greaterThanOrEqualTo($threshold);
    }

    private function devicePayload(
        $device,
        Collection $onlineEspUnitIds,
        CarbonInterface $statusThreshold
    ): array {
        $lastKnownState = $this->isDeviceOn($device->status ?? null);

        $espUid = trim(
            (string) ($device->esp_unit_id ?: $device->esp32_device_id)
        );

        $espOnline = $espUid !== ''
            && $onlineEspUnitIds->contains($espUid);

        $statusAvailable = $this->deviceStatusIsCurrent(
            $device,
            $statusThreshold
        );

        /*
         * Status lama tetap disimpan untuk audit internal, tetapi tidak boleh
         * dianggap sebagai status aktual ketika ESP32 offline atau konfirmasi
         * relay sudah kedaluwarsa.
         */
        $currentState = $statusAvailable
            ? $lastKnownState
            : null;

        $commandPending = $device->pending_state !== null
            && $device->last_command_at
            && $device->last_command_at->greaterThanOrEqualTo(
                now()->subSeconds(15)
            )
            && (string) $device->last_ack_command_id
                !== (string) $device->last_command_id;

        $statusLabel = match (true) {
            $commandPending && $statusAvailable => 'Memproses',
            $currentState === true => 'Nyala',
            default => 'Mati',
        };

        $statusMessage = match (true) {
            ! $espOnline => 'SmartVolt belum terhubung',
            ! $statusAvailable => 'Menyiapkan perangkat',
            $commandPending => 'Sedang memproses',
            $currentState === true => 'Sedang menyala',
            default => 'Siap digunakan',
        };

        return [
            'id' => $device->id,
            'room_id' => $device->room_id,
            'name' => $device->name,
            'type' => 'relay',
            'device_key' => $device->device_key ?? null,
            'relay_code' => $device->relay_code ?? null,
            'esp32_device_id' => $device->esp32_device_id ?? null,
            'esp_unit_id' => $device->esp_unit_id ?? null,
            'esp_online' => $espOnline,
            'status_available' => $statusAvailable,
            'command_pending' => $commandPending,
            'pending_state' => $device->pending_state,

            /*
             * status dan is_on hanya berisi boolean saat status masih aktual.
             * Saat offline nilainya null agar UI tidak menampilkan Nyala/Mati.
             */
            'status' => $currentState,
            'is_on' => $currentState,
            'status_text' => $currentState === null
                ? 'off'
                : ($currentState ? 'on' : 'off'),
            'status_label' => $statusLabel,
            'status_message' => $statusMessage,

            /*
             * Nilai terakhir tetap tersedia secara terpisah dan tidak dipakai
             * sebagai status langsung pada switch.
             */
            'last_known_state' => $lastKnownState,
            'last_seen_at' => $device->last_seen_at?->toIso8601String(),
            'last_confirmed_at' => $device->last_confirmed_at?->toIso8601String(),
            'last_confirmed_human' => $device->last_confirmed_at
                ? $device->last_confirmed_at->copy()->locale('id')->diffForHumans()
                : 'Belum pernah dikonfirmasi',
        ];
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
            strtolower((string) $status),
            [
                'on',
                'nyala',
                'active',
                'aktif',
                'true',
                '1',
            ],
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

    private function formatDateTimeIndonesia(
        ?CarbonInterface $date
    ): ?string {
        if ($date === null) {
            return null;
        }

        return $date
            ->copy()
            ->timezone($this->applicationTimezone())
            ->locale('id')
            ->translatedFormat('j M Y, H.i');
    }

    private function formatDateRangeIndonesia(
        CarbonInterface $startDate,
        CarbonInterface $endDate
    ): string {
        $start = $startDate
            ->copy()
            ->timezone($this->applicationTimezone())
            ->locale('id');

        $end = $endDate
            ->copy()
            ->timezone($this->applicationTimezone())
            ->locale('id');

        if ($start->isSameDay($end)) {
            return $start->translatedFormat('j F Y');
        }

        if (
            $start->year === $end->year
            && $start->month === $end->month
        ) {
            return $start->translatedFormat('j')
                . '–'
                . $end->translatedFormat('j F Y');
        }

        if ($start->year === $end->year) {
            return $start->translatedFormat('j F')
                . '–'
                . $end->translatedFormat('j F Y');
        }

        return $start->translatedFormat('j F Y')
            . '–'
            . $end->translatedFormat('j F Y');
    }

    private function applicationTimezone(): string
    {
        $timezone = trim(
            (string) config(
                'app.timezone',
                self::DEFAULT_TIMEZONE
            )
        );

        return $timezone !== ''
            ? $timezone
            : self::DEFAULT_TIMEZONE;
    }
}
