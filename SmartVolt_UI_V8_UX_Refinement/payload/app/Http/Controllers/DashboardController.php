<?php

namespace App\Http\Controllers;

use App\Models\EnergyDailySummary;
use App\Models\EnergyLog;
use App\Models\EnergyMeter;
use App\Models\Room;
use App\Models\SystemSetting;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

class DashboardController extends Controller
{
    private const DEFAULT_TIMEZONE = 'Asia/Jakarta';
    private const DEFAULT_TARIFF = 1444;
    private const DEFAULT_POWER_LIMIT = 1300;
    private const DEFAULT_REFRESH_INTERVAL = 30;

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

        $espIds = $devices
            ->map(function ($device) {
                return trim(
                    (string) ($device->esp_unit_id ?: $device->esp32_device_id)
                );
            })
            ->filter()
            ->unique()
            ->values();

        $onlineEspUnitIds = $this->resolveOnlineEspUnitIds($espIds);
        $onlineTimeoutMinutes = $this->onlineTimeoutMinutes();
        $onlineThreshold = now()->subMinutes($onlineTimeoutMinutes);

        $freshReadings = $latestReadings->filter(function (EnergyLog $log) use ($onlineThreshold) {
            return $log->created_at && $log->created_at->greaterThanOrEqualTo($onlineThreshold);
        });

        $currentPower = (float) $freshReadings->sum(
            fn (EnergyLog $log) => max(0, (float) ($log->power ?? 0))
        );

        $totalEnergyToday = $this->calculateEnergyUsage(
            $meterIds,
            $todayStart,
            $now
        );

        $totalEnergyYesterday = $this->calculateEnergyUsage(
            $meterIds,
            $yesterdayStart,
            $yesterdayEnd
        );

        $energyComparison = null;

        if ($totalEnergyYesterday > 0) {
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

        $chart = $this->buildPowerChart($todayLogs);
        $loadPercentage = min(
            100,
            round(($currentPower / $powerLimit) * 100, 1)
        );

        $loadStatus = match (true) {
            $loadPercentage >= 90 => 'danger',
            $loadPercentage >= 70 => 'warning',
            default => 'normal',
        };

        $latestReceivedAt = $latestReadings
            ->sortByDesc('created_at')
            ->first()?->created_at;

        $hasAnyEspOnline = $onlineEspUnitIds->isNotEmpty();
        $hasAnyMeter = $meters->isNotEmpty();
        $hasFreshSensorData = $freshReadings->isNotEmpty();

        $onlineDevices = $devices
            ->filter(function ($device) use ($onlineEspUnitIds) {
                $espUid = trim(
                    (string) ($device->esp_unit_id ?: $device->esp32_device_id)
                );

                return $espUid !== ''
                    && $onlineEspUnitIds->contains($espUid);
            });

        $onlineActiveDevices = $onlineDevices
            ->filter(fn ($device) => $this->isDeviceOn($device->status ?? null))
            ->count();

        $roomPower = $freshReadings
            ->filter(fn (EnergyLog $log) => $log->energyMeter?->room_id)
            ->groupBy(fn (EnergyLog $log) => (int) $log->energyMeter->room_id)
            ->map(fn ($logs) => round((float) $logs->sum('power'), 1));

        return [
            'stats' => [
                'total_energy_today' => round($totalEnergyToday, 3),
                'total_energy_yesterday' => round($totalEnergyYesterday, 3),
                'energy_comparison_percent' => $energyComparison,
                'current_power' => round($currentPower, 1),
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
                'active_devices' => $devices
                    ->filter(fn ($device) => $this->isDeviceOn($device->status ?? null))
                    ->count(),
                'online_devices' => $onlineDevices->count(),
                'online_active_devices' => $onlineActiveDevices,
            ],

            'settings' => [
                'refresh_interval' => $refreshInterval,
                'power_limit' => $powerLimit,
            ],

            'system' => [
                'has_meter' => $hasAnyMeter,
                'has_data' => $latestReadings->isNotEmpty(),
                'has_fresh_data' => $hasFreshSensorData,
                'esp_online' => $hasAnyEspOnline,
                'online_esp_unit_ids' => $onlineEspUnitIds->values(),
                'online_esp_count' => $onlineEspUnitIds->count(),
                'registered_esp_count' => $espIds->count(),
                'pzem_status' => $hasFreshSensorData
                    ? 'Normal'
                    : ($hasAnyMeter ? 'Menunggu data' : 'Belum terdaftar'),
                'control_channel_status' => $hasAnyEspOnline
                    ? 'Siap digunakan'
                    : 'Menunggu perangkat',
                'latest_received_at' => $latestReceivedAt?->toIso8601String(),
                'latest_received_human' => $latestReceivedAt
                    ? $latestReceivedAt->diffForHumans()
                    : 'Belum ada data',
                'online_timeout_minutes' => $onlineTimeoutMinutes,
            ],

            'monthly_estimation' => $monthlyEstimation,
            'chart' => $chart,

            'recent_readings' => $latestReadings
                ->sortByDesc('observed_at')
                ->take(6)
                ->map(function (EnergyLog $log) {
                    return [
                        'id' => $log->id,
                        'room_name' => $log->energyMeter?->room?->name ?? '-',
                        'meter_name' => $log->energyMeter?->name ?? '-',
                        'observed_at' => $log->observed_at?->format('d/m/Y H:i:s'),
                        'voltage' => round((float) ($log->voltage ?? 0), 1),
                        'current' => round((float) ($log->current ?? 0), 3),
                        'power' => round((float) ($log->power ?? 0), 1),
                        'energy' => round((float) ($log->energy ?? 0), 4),
                    ];
                })
                ->values(),

            'rooms' => $rooms
                ->filter(fn ($room) => $room->devices->isNotEmpty())
                ->map(function ($room) use ($onlineEspUnitIds, $roomPower) {
                    $activeDevices = $room->devices
                        ->filter(fn ($device) => $this->isDeviceOn($device->status ?? null))
                        ->count();

                    $onlineDevices = $room->devices
                        ->filter(function ($device) use ($onlineEspUnitIds) {
                            $espUid = trim(
                                (string) ($device->esp_unit_id ?: $device->esp32_device_id)
                            );

                            return $espUid !== ''
                                && $onlineEspUnitIds->contains($espUid);
                        });

                    $onlineActiveDevices = $onlineDevices
                        ->filter(fn ($device) => $this->isDeviceOn($device->status ?? null))
                        ->count();

                    return [
                        'id' => $room->id,
                        'name' => $room->name,
                        'total_devices' => $room->devices->count(),
                        'active_devices' => $activeDevices,
                        'online_devices' => $onlineDevices->count(),
                        'online_active_devices' => $onlineActiveDevices,
                        'current_power' => (float) ($roomPower[$room->id] ?? 0),
                        'devices' => $room->devices
                            ->map(function ($device) use ($onlineEspUnitIds) {
                                return $this->devicePayload(
                                    $device,
                                    $onlineEspUnitIds
                                );
                            })
                            ->values(),
                    ];
                })
                ->values(),

            'devices' => $devices
                ->map(function ($device) use ($onlineEspUnitIds) {
                    return $this->devicePayload(
                        $device,
                        $onlineEspUnitIds
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
        Carbon $startDate,
        Carbon $endDate
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

    private function buildMonthlyEstimation(
        Collection $meters,
        Carbon $startDate,
        Carbon $endDate,
        float $tariff
    ): array {
        if ($meters->isEmpty()) {
            return [
                'label' => 'Estimasi Tagihan Bulan Ini',
                'period' => $startDate->format('d/m/Y')
                    . ' - '
                    . $endDate->format('d/m/Y'),
                'usage_kwh' => 0.0,
                'tariff' => round($tariff, 2),
                'estimated_cost' => 0.0,
                'meter_count' => 0,
                'meters' => collect(),
            ];
        }

        $meterIds = $meters->pluck('id');
        $usageByMeter = collect();

        if (Schema::hasTable('energy_daily_summaries')) {
            $usageByMeter = EnergyDailySummary::query()
                ->where('user_id', Auth::id())
                ->whereIn('energy_meter_id', $meterIds)
                ->whereBetween('summary_date', [
                    $startDate->toDateString(),
                    $endDate->toDateString(),
                ])
                ->selectRaw(
                    'energy_meter_id, SUM(usage_kwh) AS usage_kwh'
                )
                ->groupBy('energy_meter_id')
                ->pluck('usage_kwh', 'energy_meter_id');
        }

        $meterBreakdown = $meters
            ->map(function (EnergyMeter $meter) use (
                $usageByMeter,
                $tariff,
                $startDate,
                $endDate
            ) {
                $usageKwh = array_key_exists($meter->id, $usageByMeter->all())
                    ? max(0, (float) $usageByMeter[$meter->id])
                    : $this->calculateEnergyUsage(
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
            'period' => $startDate->format('d/m/Y')
                . ' - '
                . $endDate->format('d/m/Y'),
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

    private function buildPowerChart(Collection $todayLogs): array
    {
        $buckets = $todayLogs
            ->groupBy(function (EnergyLog $log) {
                return $log->observed_at?->format('H:i') ?? '-';
            })
            ->map(function ($logs, $label) {
                $latestPerMeter = $logs
                    ->groupBy('energy_meter_id')
                    ->map(fn ($meterLogs) => $meterLogs->last())
                    ->values();

                return [
                    'label' => $label,
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
            ->take(-24)
            ->values();

        return [
            'labels' => $buckets->pluck('label')->values(),
            'power' => $buckets->pluck('power')->values(),
            'energy' => $buckets->pluck('energy')->values(),
        ];
    }

    private function calculateEnergyUsage(
        Collection $meterIds,
        Carbon $startDate,
        Carbon $endDate
    ): float {
        if (
            $meterIds->isEmpty()
            || ! Schema::hasTable('energy_logs')
        ) {
            return 0;
        }

        $totalUsage = 0.0;

        foreach ($meterIds as $meterId) {
            $previousLog = EnergyLog::query()
                ->where('energy_meter_id', $meterId)
                ->where('observed_at', '<', $startDate)
                ->whereNotNull('energy')
                ->orderByDesc('observed_at')
                ->orderByDesc('id')
                ->first();

            $logs = EnergyLog::query()
                ->where('energy_meter_id', $meterId)
                ->whereBetween('observed_at', [$startDate, $endDate])
                ->whereNotNull('energy')
                ->orderBy('observed_at')
                ->orderBy('id')
                ->get([
                    'id',
                    'energy',
                    'observed_at',
                ]);

            $previousEnergy = $previousLog
                ? max(0, (float) $previousLog->energy)
                : null;

            foreach ($logs as $log) {
                $currentEnergy = max(
                    0,
                    (float) $log->energy
                );

                if ($previousEnergy === null) {
                    $previousEnergy = $currentEnergy;
                    continue;
                }

                if ($currentEnergy >= $previousEnergy) {
                    $totalUsage += (
                        $currentEnergy - $previousEnergy
                    );
                }

                $previousEnergy = $currentEnergy;
            }
        }

        return round(max(0, $totalUsage), 6);
    }

    private function resolveOnlineEspUnitIds(Collection $espIds): Collection
    {
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
            ->where('created_at', '>=', now()->subMinutes($this->onlineTimeoutMinutes()))
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

    private function devicePayload(
        $device,
        Collection $onlineEspUnitIds
    ): array {
        $status = $this->isDeviceOn(
            $device->status ?? null
        );

        $espUid = trim(
            (string) ($device->esp_unit_id ?: $device->esp32_device_id)
        );

        return [
            'id' => $device->id,
            'room_id' => $device->room_id,
            'name' => $device->name,
            'type' => 'relay',
            'device_key' => $device->device_key ?? null,
            'relay_code' => $device->relay_code ?? null,
            'esp32_device_id' => $device->esp32_device_id ?? null,
            'esp_unit_id' => $device->esp_unit_id ?? null,
            'esp_online' => $espUid !== ''
                && $onlineEspUnitIds->contains($espUid),
            'status' => $status,
            'status_text' => $status ? 'on' : 'off',
            'status_label' => $status ? 'Nyala' : 'Mati',
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
