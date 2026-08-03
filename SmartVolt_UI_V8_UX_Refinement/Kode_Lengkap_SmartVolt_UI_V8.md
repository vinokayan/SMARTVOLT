# Kode Lengkap SmartVolt UI V8 — UX Refinement

Dokumen ini berisi kode utuh untuk seluruh file yang diubah pada V8. Gunakan paket ZIP untuk pemasangan otomatis atau salin file sesuai lokasi masing-masing.

## `app/Http/Controllers/DashboardController.php`

```php
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
```

## `resources/views/layouts/app.blade.php`

```php
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="color-scheme" content="light">
    <meta name="theme-color" content="#ffffff">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'SmartVolt') | SmartVolt</title>

    <link rel="icon" href="{{ asset('favicon.ico') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/smartvolt-app.css') }}?v=20260731-v8-base">
    <link rel="stylesheet" href="{{ asset('assets/css/smartvolt-pro.css') }}?v=20260731-v8">

    @stack('styles')
    @stack('head-scripts')
</head>
@php
    $isTechnicianPage = request()->routeIs('settings*') && request('tab') === 'technician';
@endphp
<body class="sv-app-body sv-pro-app @yield('body-class')">
    <a class="sv-skip-link" href="#main-content">Lewati ke konten</a>

    <div class="sv-app-layout">
        <button type="button" class="sv-sidebar-overlay" data-sidebar-close aria-label="Tutup menu"></button>

        <aside class="sv-app-sidebar" data-sidebar>
            <div class="sv-sidebar-header">
                <a href="{{ route('dashboard') }}" class="sv-brand" aria-label="Beranda SmartVolt">
                    <span class="sv-brand-mark" aria-hidden="true">
                        <x-icon name="bolt" :size="22" />
                    </span>
                    <span class="sv-brand-copy">
                        <strong>SmartVolt</strong>
                        <small>Monitoring Energi</small>
                    </span>
                </a>

                <button type="button" class="sv-icon-button sv-sidebar-close" data-sidebar-close aria-label="Tutup menu">
                    <x-icon name="close" :size="20" />
                </button>
            </div>

            <nav class="sv-sidebar-nav" aria-label="Navigasi utama">
                <p class="sv-nav-label">Menu utama</p>

                <a href="{{ route('dashboard') }}" class="sv-nav-link {{ request()->routeIs('dashboard*') ? 'is-active' : '' }}">
                    <span class="sv-pro-nav-icon sv-pro-nav-icon--blue"><x-icon name="home" :size="19" /></span>
                    <span>Beranda</span>
                </a>

                <a href="{{ route('energy.history') }}" class="sv-nav-link {{ request()->routeIs('energy.history*') ? 'is-active' : '' }}">
                    <span class="sv-pro-nav-icon sv-pro-nav-icon--green"><x-icon name="chart" :size="19" /></span>
                    <span>Pemakaian Listrik</span>
                </a>

                <a href="{{ route('rooms') }}" class="sv-nav-link {{ request()->routeIs('rooms*') || request()->routeIs('devices*') ? 'is-active' : '' }}">
                    <span class="sv-pro-nav-icon sv-pro-nav-icon--violet"><x-icon name="rooms" :size="19" /></span>
                    <span>Ruangan & Perangkat</span>
                </a>

                <a href="{{ route('settings') }}" class="sv-nav-link {{ request()->routeIs('settings*') && !$isTechnicianPage ? 'is-active' : '' }}">
                    <span class="sv-pro-nav-icon sv-pro-nav-icon--slate"><x-icon name="settings" :size="19" /></span>
                    <span>Pengaturan</span>
                </a>

                <p class="sv-nav-label sv-nav-label-spaced">Sistem</p>

                <a href="{{ route('settings', ['tab' => 'technician']) }}#technician" class="sv-nav-link sv-nav-link-secondary {{ $isTechnicianPage ? 'is-active' : '' }}">
                    <span class="sv-pro-nav-icon sv-pro-nav-icon--amber"><x-icon name="tools" :size="19" /></span>
                    <span>Mode Teknisi</span>
                </a>
            </nav>

            <div class="sv-sidebar-footer-simple">
                <span class="sv-sidebar-footer-dot" aria-hidden="true"></span>
                <span>SmartVolt siap digunakan</span>
            </div>
        </aside>

        <main class="sv-app-main" id="main-content" tabindex="-1">
            <header class="sv-app-topbar">
                <div class="sv-topbar-inner">
                    <div class="sv-topbar-left">
                        <button type="button" class="sv-icon-button sv-mobile-menu" data-sidebar-open aria-label="Buka menu">
                            <x-icon name="menu" :size="22" />
                        </button>

                        <div class="sv-topbar-heading">
                            <h1 class="sv-page-title">@yield('page-title', 'SmartVolt')</h1>
                            <p class="sv-page-subtitle">@yield('page-subtitle')</p>
                        </div>
                    </div>

                    <div class="sv-topbar-actions">
                        @hasSection('page-actions')
                            <div class="sv-page-actions">@yield('page-actions')</div>
                        @endif

                        @hasSection('system-status')
                            <div class="sv-topbar-status">@yield('system-status')</div>
                        @endif

                        @include('components.notification-bell')

                        <div class="sv-profile-menu" data-profile-menu>
                            <button type="button" class="sv-profile-trigger" data-profile-trigger aria-expanded="false">
                                <span class="sv-avatar">{{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 1)) }}</span>
                                <span class="sv-profile-copy">
                                    <strong>{{ auth()->user()->name ?? 'Pengguna' }}</strong>
                                    <small>Pemilik Rumah</small>
                                </span>
                                <x-icon name="chevron-down" :size="16" />
                            </button>

                            <div class="sv-profile-dropdown" data-profile-dropdown>
                                <div class="sv-profile-dropdown-head">
                                    <strong>{{ auth()->user()->name ?? 'Pengguna' }}</strong>
                                    <span>{{ auth()->user()->email ?? '' }}</span>
                                </div>

                                <a href="{{ route('settings') }}">
                                    <x-icon name="settings" :size="17" />
                                    <span>Pengaturan akun</span>
                                </a>

                                <form action="{{ route('logout') }}" method="POST">
                                    @csrf
                                    <button type="submit">
                                        <x-icon name="logout" :size="17" />
                                        <span>Keluar</span>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            <div class="sv-page-content">
                @if(session('status') || session('success'))
                    <div class="sv-alert sv-alert-success" role="status">
                        <x-icon name="check" :size="19" />
                        <div>{{ session('status') ?? session('success') }}</div>
                    </div>
                @endif

                @if($errors->any())
                    <div class="sv-alert sv-alert-danger" role="alert">
                        <x-icon name="warning" :size="19" />
                        <div>
                            <strong>Periksa kembali data yang dimasukkan.</strong>
                            <p>{{ $errors->first() }}</p>
                        </div>
                    </div>
                @endif

                @yield('content')
            </div>

            <nav class="sv-bottom-navigation" aria-label="Navigasi mobile">
                <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard*') ? 'is-active' : '' }}">
                    <x-icon name="home" :size="20" />
                    <span>Beranda</span>
                </a>
                <a href="{{ route('energy.history') }}" class="{{ request()->routeIs('energy.history*') ? 'is-active' : '' }}">
                    <x-icon name="chart" :size="20" />
                    <span>Pemakaian</span>
                </a>
                <a href="{{ route('rooms') }}" class="{{ request()->routeIs('rooms*') || request()->routeIs('devices*') ? 'is-active' : '' }}">
                    <x-icon name="rooms" :size="20" />
                    <span>Perangkat</span>
                </a>
                <a href="{{ route('settings') }}" class="{{ request()->routeIs('settings*') ? 'is-active' : '' }}">
                    <x-icon name="settings" :size="20" />
                    <span>Pengaturan</span>
                </a>
            </nav>
        </main>
    </div>

    <div class="sv-toast-region" data-toast-region aria-live="polite" aria-atomic="true"></div>

    <script src="{{ asset('assets/js/smartvolt-app.js') }}?v=20260731-v8" defer></script>
    @stack('scripts')
</body>
</html>
```

## `resources/views/dashboard.blade.php`

```php
@extends('layouts.app')

@section('title', 'Beranda')
@section('page-title', 'Beranda')
@section('page-subtitle', 'Ringkasan listrik rumah saat ini.')
@section('body-class', 'sv-dashboard-page sv-pro-dashboard')

@php
    $stats = $dashboardData['stats'] ?? [];
    $system = $dashboardData['system'] ?? [];
    $chart = $dashboardData['chart'] ?? ['labels' => [], 'power' => [], 'energy' => []];
    $recentReadings = collect($dashboardData['recent_readings'] ?? [])->take(5);
    $dashboardRooms = collect($dashboardData['rooms'] ?? [])
        ->filter(fn ($room) => collect($room['devices'] ?? [])->isNotEmpty())
        ->values();

    $chartPowerValues = collect($chart['power'] ?? [])->map(fn ($value) => (float) $value);
    $chartLatestPower = (float) ($chartPowerValues->last() ?? 0);
    $chartLatestLabel = collect($chart['labels'] ?? [])->last();

    $loadStatus = $stats['load_status'] ?? 'normal';
    $loadPercentage = min(100, max(0, (float) ($stats['load_percentage'] ?? 0)));
    $energyComparison = $stats['energy_comparison_percent'] ?? null;

    $statusBadgeClass = ($system['has_fresh_data'] ?? false)
        ? 'sv-badge-success'
        : (($system['has_data'] ?? false) ? 'sv-badge-warning' : 'sv-badge-neutral');

    $systemStatusLabel = ($system['has_fresh_data'] ?? false)
        ? 'Sistem terhubung'
        : (($system['has_data'] ?? false) ? 'Data terlambat' : 'Belum terhubung');

    $systemStatusHelp = ($system['has_fresh_data'] ?? false)
        ? 'Data SmartVolt diperbarui secara normal.'
        : (($system['has_data'] ?? false)
            ? 'Data terakhir diterima ' . ($system['latest_received_human'] ?? 'beberapa waktu lalu') . '. Periksa koneksi ESP32.'
            : 'Belum ada data dari perangkat SmartVolt.');
@endphp

@section('system-status')
    <span
        class="sv-badge {{ $statusBadgeClass }}"
        data-dashboard-system-badge
        title="{{ $systemStatusHelp }}"
        aria-label="{{ $systemStatusLabel }}. {{ $systemStatusHelp }}"
    >
        <span class="sv-status-dot" aria-hidden="true"></span>
        <span data-dashboard-system-label>{{ $systemStatusLabel }}</span>
    </span>
@endsection

@section('content')
    <section class="sv-pro-metrics" aria-label="Ringkasan penggunaan listrik">
        <article class="sv-pro-metric sv-pro-metric--primary">
            <div class="sv-pro-metric-head">
                <span class="sv-pro-icon-tile sv-pro-icon-tile--blue"><x-icon name="bolt" :size="22" /></span>
                <span class="sv-pro-metric-label">Daya saat ini</span>
            </div>
            <div class="sv-pro-metric-value">
                <strong data-stat-current-power>{{ number_format((float) ($stats['current_power'] ?? 0), 1, ',', '.') }}</strong>
                <small>W</small>
            </div>
            <div class="sv-pro-metric-meta">
                <span class="sv-pro-state {{ $loadStatus === 'danger' ? 'is-danger' : ($loadStatus === 'warning' ? 'is-warning' : 'is-success') }}" data-load-status-badge>
                    <span data-load-status-label>{{ $loadStatus === 'danger' ? 'Beban tinggi' : ($loadStatus === 'warning' ? 'Mendekati batas' : 'Normal') }}</span>
                </span>
                <span><strong data-stat-load-percentage>{{ number_format($loadPercentage, 0, ',', '.') }}</strong>% batas</span>
            </div>
            <div class="sv-progress {{ $loadStatus === 'danger' ? 'is-danger' : ($loadStatus === 'warning' ? 'is-warning' : '') }}" data-load-progress style="--sv-progress: {{ $loadPercentage }}%"><span></span></div>
        </article>

        <article class="sv-pro-metric">
            <div class="sv-pro-metric-head">
                <span class="sv-pro-icon-tile sv-pro-icon-tile--green"><x-icon name="energy" :size="21" /></span>
                <span class="sv-pro-metric-label">Energi hari ini</span>
            </div>
            <div class="sv-pro-metric-value">
                <strong data-stat-energy-today>{{ number_format((float) ($stats['total_energy_today'] ?? 0), 3, ',', '.') }}</strong>
                <small>kWh</small>
            </div>
            <p class="sv-pro-metric-note" data-energy-comparison>
                @if(is_numeric($energyComparison))
                    {{ $energyComparison > 0 ? 'Naik' : ($energyComparison < 0 ? 'Lebih hemat' : 'Sama') }}
                    {{ number_format(abs((float) $energyComparison), 1, ',', '.') }}% dari kemarin
                @else
                    Belum ada perbandingan
                @endif
            </p>
        </article>

        <article class="sv-pro-metric">
            <div class="sv-pro-metric-head">
                <span class="sv-pro-icon-tile sv-pro-icon-tile--amber"><x-icon name="money" :size="21" /></span>
                <span class="sv-pro-metric-label">Estimasi bulan ini</span>
            </div>
            <div class="sv-pro-metric-value sv-pro-metric-value--money">
                <small>Rp</small>
                <strong data-stat-monthly-cost>{{ number_format((float) ($stats['monthly_estimated_cost'] ?? 0), 0, ',', '.') }}</strong>
            </div>
            <p class="sv-pro-metric-note"><span data-stat-monthly-energy>{{ number_format((float) ($stats['monthly_energy_usage'] ?? 0), 3, ',', '.') }}</span> kWh tercatat</p>
        </article>

        <article class="sv-pro-metric">
            <div class="sv-pro-metric-head">
                <span class="sv-pro-icon-tile sv-pro-icon-tile--cyan"><x-icon name="power" :size="21" /></span>
                <span class="sv-pro-metric-label">Perangkat menyala</span>
            </div>
            <div class="sv-pro-metric-value">
                <strong data-stat-powered-devices>{{ (int) ($stats['online_active_devices'] ?? 0) }}</strong>
                <small>perangkat</small>
            </div>
            <p class="sv-pro-metric-note">
                <span data-stat-online-devices>{{ (int) ($stats['online_devices'] ?? 0) }}</span> online dari
                <span data-stat-total-devices>{{ (int) ($stats['total_devices'] ?? 0) }}</span> terdaftar
            </p>
        </article>
    </section>

    <section class="sv-pro-system-strip" aria-label="Status sistem SmartVolt">
        <div class="sv-pro-system-item {{ ($system['esp_online'] ?? false) ? 'is-success' : 'is-warning' }}" data-system-esp>
            <span class="sv-pro-system-icon"><x-icon name="server" :size="18" /></span>
            <div><strong data-system-esp-label>{{ ($system['esp_online'] ?? false) ? (($system['online_esp_count'] ?? 0) . ' ESP32 terhubung') : 'ESP32 belum terhubung' }}</strong><span>Perangkat utama</span></div>
        </div>
        <div class="sv-pro-system-item {{ ($system['has_fresh_data'] ?? false) ? 'is-success' : 'is-warning' }}" data-system-pzem>
            <span class="sv-pro-system-icon"><x-icon name="sensor" :size="18" /></span>
            <div><strong>Sensor <span data-system-pzem-label>{{ $system['pzem_status'] ?? 'Belum tersedia' }}</span></strong><span>Pembacaan listrik</span></div>
        </div>
        <div class="sv-pro-system-item {{ ($system['esp_online'] ?? false) ? 'is-success' : 'is-warning' }}" data-system-control>
            <span class="sv-pro-system-icon"><x-icon name="mqtt" :size="18" /></span>
            <div><strong>Kontrol <span data-system-control-label>{{ $system['control_channel_status'] ?? 'Menunggu perangkat' }}</span></strong><span>Saluran MQTT</span></div>
        </div>
        <div class="sv-pro-system-item {{ ($system['has_data'] ?? false) ? 'is-success' : 'is-warning' }}" data-system-latest>
            <span class="sv-pro-system-icon"><x-icon name="clock" :size="18" /></span>
            <div><strong data-system-latest-label>{{ $system['latest_received_human'] ?? 'Belum ada data' }}</strong><span>Data terakhir</span></div>
        </div>
    </section>

    <section class="sv-pro-dashboard-grid">
        <article class="sv-card sv-pro-chart-card">
            <header class="sv-card-header">
                <div>
                    <h2>Daya terbaru</h2>
                    <p>Pembacaan terakhir yang tersedia dari meter utama.</p>
                </div>
                <a href="{{ route('energy.history') }}" class="sv-text-button">Lihat analisis</a>
            </header>
            <div class="sv-card-body">
                <div class="sv-chart-summary sv-chart-summary-compact">
                    <div>
                        <span>Terakhir tercatat</span>
                        <strong><span data-chart-current>{{ number_format($chartLatestPower, 1, ',', '.') }}</span> <small data-chart-unit>W</small></strong>
                        @if($chartLatestLabel)
                            <small class="sv-chart-summary-time" data-chart-latest-label>{{ $chartLatestLabel }}</small>
                        @endif
                    </div>
                    <div><span>Rata-rata</span><strong><span data-chart-average>0</span> <small data-chart-average-unit>W</small></strong></div>
                    <div><span>Tertinggi</span><strong><span data-chart-maximum>0</span> <small data-chart-maximum-unit>W</small></strong></div>
                </div>

                <div class="sv-empty-state {{ collect($chart['labels'] ?? [])->isNotEmpty() ? 'is-hidden' : '' }}" data-dashboard-chart-empty>
                    <span class="sv-pro-icon-tile sv-pro-icon-tile--blue"><x-icon name="chart" :size="22" /></span>
                    <h3>Belum ada data terbaru</h3>
                    <p>Grafik akan muncul setelah telemetry diterima.</p>
                </div>

                <div class="sv-chart-container {{ collect($chart['labels'] ?? [])->isEmpty() ? 'is-hidden' : '' }}" data-dashboard-chart-container>
                    <canvas id="dashboardEnergyChart" aria-label="Grafik daya SmartVolt"></canvas>
                </div>
            </div>
        </article>

        <article class="sv-card sv-pro-control-card">
            <header class="sv-card-header">
                <div>
                    <h2>Perangkat yang dapat dikontrol</h2>
                    <p>Hanya ruangan yang sudah memiliki perangkat.</p>
                </div>
                <a href="{{ route('rooms') }}" class="sv-text-button">Kelola semua</a>
            </header>

            <div class="sv-card-body">
                @if($dashboardRooms->isEmpty())
                    <div class="sv-empty-state sv-empty-state-compact">
                        <span class="sv-pro-icon-tile sv-pro-icon-tile--violet"><x-icon name="rooms" :size="22" /></span>
                        <h3>Belum ada perangkat</h3>
                        <p>Tambahkan perangkat melalui Mode Teknisi.</p>
                        <a href="{{ route('settings', ['tab' => 'technician']) }}#technician" class="sv-button sv-button-secondary sv-button-sm">Tambah perangkat</a>
                    </div>
                @else
                    <div class="sv-room-control-list">
                        @foreach($dashboardRooms->take(3) as $room)
                            @php
                                $dashboardRoomName = strtolower((string) ($room['name'] ?? ''));
                                $dashboardRoomIcon = str_contains($dashboardRoomName, 'dapur') ? 'kitchen'
                                    : (str_contains($dashboardRoomName, 'kamar') ? 'bed'
                                    : (str_contains($dashboardRoomName, 'tamu') ? 'sofa'
                                    : (str_contains($dashboardRoomName, 'garasi') ? 'garage' : 'rooms')));
                            @endphp
                            <details class="sv-room-control" data-room-id="{{ $room['id'] }}" {{ $loop->first ? 'open' : '' }}>
                                <summary>
                                    <span class="sv-room-summary-main">
                                        <span class="sv-pro-icon-tile sv-pro-icon-tile--violet sv-pro-icon-tile--sm"><x-icon :name="$dashboardRoomIcon" :size="17" /></span>
                                        <span class="sv-room-summary-copy">
                                            <strong>{{ ucwords(strtolower((string) $room['name'])) }}</strong>
                                            <span>
                                                <span data-room-active-count="{{ $room['id'] }}">{{ (int) ($room['online_active_devices'] ?? 0) }}</span> menyala ·
                                                {{ (int) ($room['online_devices'] ?? 0) }} online
                                            </span>
                                        </span>
                                    </span>
                                    <span class="sv-room-power">{{ number_format((float) ($room['current_power'] ?? 0), 1, ',', '.') }} W</span>
                                    <span class="sv-room-chevron"><x-icon name="chevron-right" :size="16" /></span>
                                </summary>

                                <div class="sv-room-devices">
                                    @forelse(collect($room['devices'] ?? [])->take(4) as $device)
                                        @php
                                            $deviceOn = (bool) ($device['is_on'] ?? false);
                                            $espOnline = (bool) ($device['esp_online'] ?? false);
                                            $dashboardDeviceLabel = strtolower((string) (($device['type'] ?? '') . ' ' . ($device['name'] ?? '')));
                                            $dashboardDeviceIcon = str_contains($dashboardDeviceLabel, 'kipas') || str_contains($dashboardDeviceLabel, 'fan')
                                                ? 'fan'
                                                : (str_contains($dashboardDeviceLabel, 'lampu') || str_contains($dashboardDeviceLabel, 'light') ? 'lightbulb' : 'plug');
                                        @endphp
                                        <div class="sv-device-row" data-device-row="{{ $device['id'] }}">
                                            <div class="sv-device-main">
                                                <span class="sv-pro-device-icon {{ $espOnline && $deviceOn ? 'is-on' : '' }} {{ ! $espOnline ? 'is-offline' : '' }}"><x-icon :name="$dashboardDeviceIcon" :size="17" /></span>
                                                <div class="sv-device-copy">
                                                    <strong>{{ $device['name'] }}</strong>
                                                    <span>{{ $espOnline ? ($deviceOn ? 'Sedang menyala' : 'Siap dikontrol') : 'Perangkat offline' }}</span>
                                                </div>
                                            </div>

                                            <button
                                                type="button"
                                                class="sv-device-switch {{ $deviceOn ? 'is-on' : '' }} {{ ! $espOnline ? 'is-offline' : '' }}"
                                                data-device-toggle
                                                data-device-id="{{ $device['id'] }}"
                                                data-room-id="{{ $room['id'] }}"
                                                data-url="{{ route('devices.toggle', $device['id']) }}"
                                                data-current-state="{{ $deviceOn ? 'on' : 'off' }}"
                                                aria-pressed="{{ $deviceOn ? 'true' : 'false' }}"
                                                {{ ! $espOnline ? 'disabled' : '' }}
                                            >
                                                <span class="sv-switch-track"><span></span></span>
                                                <span data-switch-label>{{ $espOnline ? ($deviceOn ? 'Nyala' : 'Mati') : 'Offline' }}</span>
                                            </button>
                                        </div>
                                    @empty
                                        <div class="sv-empty-state sv-empty-state-compact"><p>Belum ada perangkat.</p></div>
                                    @endforelse
                                </div>
                            </details>
                        @endforeach
                    </div>
                @endif
            </div>
        </article>
    </section>

    <section class="sv-card sv-pro-activity-card">
        <header class="sv-card-header">
            <div>
                <h2>Aktivitas Terbaru</h2>
                <p>Lima pembacaan terakhir.</p>
            </div>
            <a href="{{ route('energy.history') }}" class="sv-text-button">Lihat riwayat</a>
        </header>

        <div class="sv-card-body sv-card-body-table">
            @if($recentReadings->isEmpty())
                <div class="sv-empty-state sv-empty-state-compact">
                    <span class="sv-pro-icon-tile sv-pro-icon-tile--blue"><x-icon name="document" :size="22" /></span>
                    <h3>Belum ada pembacaan</h3>
                </div>
            @else
                <div class="sv-table-wrap">
                    <table class="sv-table sv-table-compact">
                        <thead>
                            <tr>
                                <th>Waktu</th>
                                <th>Ruangan</th>
                                <th class="is-numeric">Daya</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($recentReadings as $reading)
                                <tr>
                                    <td>{{ $reading['observed_at'] ?? '-' }}</td>
                                    <td>{{ $reading['room_name'] ?? '-' }}</td>
                                    <td class="is-numeric">{{ number_format((float) ($reading['power'] ?? 0), 1, ',', '.') }} W</td>
                                    <td><span class="sv-badge sv-badge-success">Tercatat</span></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </section>

    <script type="application/json" id="smartvolt-dashboard-data">{!! json_encode([
        'endpoint' => route('dashboard.data'),
        'refreshInterval' => (int) ($dashboardData['settings']['refresh_interval'] ?? 30),
        'chart' => $chart,
        'stats' => $stats,
        'system' => $system,
    ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) !!}</script>
@endsection

@push('scripts')
    <script src="{{ asset('assets/js/chart.umd.min.js') }}?v=4.5.1" defer></script>
    <script src="{{ asset('assets/js/smartvolt-dashboard.js') }}?v=20260731-v8" defer></script>
@endpush
```

## `resources/views/rooms.blade.php`

```php
@extends('layouts.app')

@section('title', 'Ruangan & Perangkat')
@section('page-title', 'Ruangan & Perangkat')
@section('page-subtitle', 'Kontrol perangkat berdasarkan ruangan.')
@section('body-class', 'sv-rooms-page sv-rooms-streamlined')

@php
    $roomCollection = collect($rooms ?? []);
    $onlineIds = collect($onlineEspUnitIds ?? [])->map(fn ($value) => (string) $value);
    $allDevices = $roomCollection->pluck('devices')->flatten();
    $isOn = function ($status): bool {
        if (is_bool($status)) return $status;
        if (is_numeric($status)) return (int) $status === 1;
        return in_array(strtolower((string) $status), ['on', 'nyala', 'active', 'aktif', 'true', '1'], true);
    };
    $totalDevices = $allDevices->count();
    $onlineDevices = $allDevices->filter(function ($device) use ($onlineIds) {
        $espId = trim((string) ($device->esp_unit_id ?: $device->esp32_device_id));
        return $espId !== '' && $onlineIds->contains($espId);
    })->count();
    $roomTones = ['violet', 'blue', 'green', 'amber', 'cyan', 'rose'];
@endphp

@section('system-status')
    <span
        class="sv-badge {{ $onlineIds->isNotEmpty() ? 'sv-badge-success' : 'sv-badge-warning' }}"
        title="{{ $onlineIds->isNotEmpty() ? 'Perangkat dapat dikontrol melalui ESP32 yang terhubung.' : 'Tidak ada ESP32 yang mengirim telemetry terbaru.' }}"
    >
        <span class="sv-status-dot" aria-hidden="true"></span>
        {{ $onlineIds->isNotEmpty() ? $onlineIds->count() . ' ESP32 online' : 'Perangkat offline' }}
    </span>
@endsection

@section('content')
    <section class="sv-room-summary-grid sv-room-summary-grid-three" aria-label="Ringkasan ruangan dan perangkat">
        <article class="sv-summary-card sv-summary-card--violet">
            <x-feature-icon name="rooms" tone="violet" :size="19" variant="soft" class="sv-summary-icon" />
            <div class="sv-summary-copy"><span>Ruangan</span><strong>{{ $roomCollection->count() }}</strong></div>
        </article>
        <article class="sv-summary-card sv-summary-card--cyan">
            <x-feature-icon name="plug" tone="cyan" :size="19" variant="soft" class="sv-summary-icon" />
            <div class="sv-summary-copy"><span>Total Perangkat</span><strong>{{ $totalDevices }}</strong></div>
        </article>
        <article class="sv-summary-card sv-summary-card--green">
            <x-feature-icon name="wifi" tone="green" :size="19" variant="soft" class="sv-summary-icon" />
            <div class="sv-summary-copy"><span>Perangkat Online</span><strong>{{ $onlineDevices }}</strong></div>
        </article>
    </section>

    @if($roomCollection->isEmpty())
        <section class="sv-card">
            <div class="sv-card-body">
                <div class="sv-empty-state sv-empty-state-compact">
                    <x-feature-icon name="rooms" tone="violet" :size="27" variant="soft" />
                    <h3>Belum ada ruangan</h3>
                    <a href="{{ route('settings', ['tab' => 'technician']) }}#technician" class="sv-button sv-button-primary">Tambah ruangan</a>
                </div>
            </div>
        </section>
    @else
        <section class="sv-room-grid sv-room-grid-three" aria-label="Daftar ruangan">
            @foreach($roomCollection as $room)
                @php
                    $devices = collect($room->devices ?? []);
                    $roomActiveCount = $devices->filter(fn ($device) => $isOn($device->status ?? null))->count();
                    $roomOnlineDevices = $devices->filter(function ($device) use ($onlineIds) {
                        $espId = trim((string) ($device->esp_unit_id ?: $device->esp32_device_id));
                        return $espId !== '' && $onlineIds->contains($espId);
                    });
                    $roomOnlineCount = $roomOnlineDevices->count();
                    $roomOnlineActiveCount = $roomOnlineDevices
                        ->filter(fn ($device) => $isOn($device->status ?? null))
                        ->count();
                    $power = (float) (($roomPower ?? collect())[$room->id] ?? 0);
                    $roomNameLower = strtolower((string) $room->name);
                    $roomIcon = str_contains($roomNameLower, 'dapur') ? 'kitchen'
                        : (str_contains($roomNameLower, 'kamar') ? 'bed'
                        : (str_contains($roomNameLower, 'tamu') ? 'sofa'
                        : (str_contains($roomNameLower, 'garasi') ? 'garage' : 'rooms')));
                    $roomTone = $roomTones[$loop->index % count($roomTones)];
                @endphp

                <article class="sv-room-card sv-room-card--{{ $roomTone }}" data-room-card="{{ $room->id }}">
                    <header class="sv-room-card-head">
                        <div class="sv-room-card-title">
                            <x-feature-icon :name="$roomIcon" :tone="$roomTone" :size="18" variant="soft" class="sv-room-icon" />
                            <div>
                                <h3>{{ ucwords(strtolower((string) $room->name)) }}</h3>
                                <p>{{ $devices->count() }} perangkat · {{ number_format($power, 1, ',', '.') }} W</p>
                            </div>
                        </div>
                        @if($devices->isEmpty())
                            <span class="sv-badge sv-badge-neutral">Belum ada perangkat</span>
                        @else
                            <span class="sv-badge {{ $roomOnlineCount > 0 ? 'sv-badge-success' : 'sv-badge-warning' }}">
                                <span class="sv-status-dot" aria-hidden="true"></span>
                                {{ $roomOnlineCount > 0 ? 'Online' : 'Offline' }}
                            </span>
                        @endif
                    </header>

                    <div class="sv-room-card-body">
                        @forelse($devices as $device)
                            @php
                                $deviceOn = $isOn($device->status ?? null);
                                $espId = trim((string) ($device->esp_unit_id ?: $device->esp32_device_id));
                                $espOnline = $espId !== '' && $onlineIds->contains($espId);
                                $deviceLabel = strtolower(($device->type ?? '') . ' ' . ($device->name ?? ''));
                                $deviceIcon = str_contains($deviceLabel, 'kipas') || str_contains($deviceLabel, 'fan') ? 'fan'
                                    : (str_contains($deviceLabel, 'lampu') || str_contains($deviceLabel, 'light') ? 'lightbulb' : 'plug');
                                $deviceTone = ! $espOnline
                                    ? 'amber'
                                    : ($deviceOn ? 'green' : ($deviceIcon === 'fan' ? 'cyan' : ($deviceIcon === 'lightbulb' ? 'amber' : 'blue')));
                            @endphp
                            <div class="sv-device-row" data-device-row="{{ $device->id }}">
                                <div class="sv-device-main">
                                    <x-feature-icon :name="$deviceIcon" :tone="$deviceTone" :size="16" variant="soft" class="sv-device-icon" />
                                    <div class="sv-device-copy">
                                        <strong>{{ $device->name }}</strong>
                                        <span>{{ $espOnline ? ($deviceOn ? 'Sedang menyala' : 'Siap digunakan') : 'Tidak dapat dikontrol' }}</span>
                                    </div>
                                </div>

                                <button
                                    type="button"
                                    class="sv-device-switch {{ $deviceOn ? 'is-on' : '' }} {{ ! $espOnline ? 'is-offline' : '' }}"
                                    data-device-toggle
                                    data-device-id="{{ $device->id }}"
                                    data-room-id="{{ $room->id }}"
                                    data-url="{{ route('devices.toggle', $device) }}"
                                    data-current-state="{{ $deviceOn ? 'on' : 'off' }}"
                                    aria-pressed="{{ $deviceOn ? 'true' : 'false' }}"
                                    {{ ! $espOnline ? 'disabled' : '' }}
                                    title="{{ $espOnline ? 'Ubah status perangkat' : 'Perangkat sedang offline' }}"
                                >
                                    <span data-switch-label>{{ $espOnline ? ($deviceOn ? 'Nyala' : 'Mati') : 'Offline' }}</span>
                                </button>
                            </div>
                        @empty
                            <div class="sv-empty-state sv-empty-state-compact">
                                <x-feature-icon name="plug" tone="cyan" :size="23" variant="soft" />
                                <h3>Belum ada perangkat</h3>
                                <a href="{{ route('settings', ['tab' => 'technician']) }}#room-{{ $room->id }}" class="sv-button sv-button-secondary sv-button-sm">Tambah perangkat</a>
                            </div>
                        @endforelse
                    </div>

                    <footer class="sv-room-card-foot">
                        @if($devices->isEmpty())
                            <span>Belum ada perangkat terdaftar</span>
                        @else
                            <span>
                                <strong data-room-active-count="{{ $room->id }}">{{ $roomOnlineActiveCount }}</strong> menyala ·
                                {{ $roomOnlineCount }} dari {{ $devices->count() }} online
                            </span>
                        @endif
                    </footer>
                </article>
            @endforeach
        </section>
    @endif
@endsection

@push('scripts')
    <script src="{{ asset('assets/js/smartvolt-rooms.js') }}?v=20260731-v8" defer></script>
@endpush
```

## `public/assets/css/smartvolt-pro.css`

```css
/* =========================================================
   SmartVolt UI V7 — Professional Minimal
   Loaded after smartvolt-app.css to preserve project behavior.
   ========================================================= */

:root {
    --svp-bg: #f6f8fb;
    --svp-surface: #ffffff;
    --svp-surface-soft: #f8fafc;
    --svp-text: #172033;
    --svp-heading: #101828;
    --svp-muted: #667085;
    --svp-muted-2: #98a2b3;
    --svp-border: #e3e8ef;
    --svp-border-strong: #d4dbe5;
    --svp-blue: #2563eb;
    --svp-blue-dark: #1d4ed8;
    --svp-blue-soft: #eef4ff;
    --svp-green: #16803c;
    --svp-green-soft: #edf8f1;
    --svp-amber: #b76e00;
    --svp-amber-soft: #fff7e5;
    --svp-red: #c83c3c;
    --svp-red-soft: #fff0f0;
    --svp-violet: #7457d7;
    --svp-violet-soft: #f4f0ff;
    --svp-cyan: #087f8c;
    --svp-cyan-soft: #eaf8fa;
    --svp-slate: #475467;
    --svp-radius-sm: 10px;
    --svp-radius: 14px;
    --svp-radius-lg: 18px;
    --svp-shadow: 0 1px 2px rgba(16, 24, 40, .03), 0 8px 28px rgba(16, 24, 40, .05);
    --svp-shadow-float: 0 16px 48px rgba(16, 24, 40, .12);
    --svp-content: 1480px;
    --svp-sidebar: 248px;
    --svp-topbar: 76px;
}

body.sv-pro-app {
    color: var(--svp-text);
    background: var(--svp-bg);
    font-size: 14px;
    line-height: 1.55;
}

body.sv-pro-app :focus-visible {
    outline: 3px solid rgba(37, 99, 235, .2);
    outline-offset: 2px;
}

.sv-skip-link {
    position: fixed;
    top: 10px;
    left: 10px;
    z-index: 999;
    padding: 10px 14px;
    border-radius: 9px;
    color: #fff;
    background: var(--svp-blue);
    transform: translateY(-150%);
    transition: transform .15s ease;
}

.sv-skip-link:focus {
    transform: translateY(0);
}

/* Shell */
body.sv-pro-app .sv-app-sidebar {
    width: var(--svp-sidebar);
    border-right: 1px solid var(--svp-border);
    background: rgba(255, 255, 255, .98);
    box-shadow: none;
}

body.sv-pro-app .sv-sidebar-header {
    min-height: var(--svp-topbar);
    padding: 0 20px;
    border-bottom: 1px solid var(--svp-border);
}

body.sv-pro-app .sv-brand {
    gap: 12px;
}

body.sv-pro-app .sv-brand-mark {
    width: 40px;
    height: 40px;
    flex-basis: 40px;
    border-radius: 12px;
    color: #fff;
    background: var(--svp-blue);
    box-shadow: 0 7px 18px rgba(37, 99, 235, .18);
}

body.sv-pro-app .sv-brand-copy strong {
    color: var(--svp-heading);
    font-size: 18px;
    font-weight: 780;
    letter-spacing: -.03em;
}

body.sv-pro-app .sv-brand-copy small {
    margin-top: 1px;
    color: var(--svp-muted);
    font-size: 11px;
    font-weight: 600;
}

body.sv-pro-app .sv-sidebar-nav {
    padding: 22px 14px 16px;
    gap: 5px;
}

body.sv-pro-app .sv-nav-label {
    margin: 0 10px 8px;
    color: var(--svp-muted-2);
    font-size: 10px;
    font-weight: 750;
    letter-spacing: .1em;
}

body.sv-pro-app .sv-nav-label-spaced {
    margin-top: 22px;
}

body.sv-pro-app .sv-nav-link {
    min-height: 46px;
    padding: 8px 11px;
    gap: 11px;
    border: 1px solid transparent;
    border-radius: 11px;
    color: #475467;
    font-size: 13.5px;
    font-weight: 650;
}

body.sv-pro-app .sv-nav-link::before {
    display: none;
}

body.sv-pro-app .sv-nav-link:hover {
    color: var(--svp-heading);
    background: #f7f9fc;
}

body.sv-pro-app .sv-nav-link.is-active {
    color: var(--svp-blue-dark);
    border-color: #dce8ff;
    background: var(--svp-blue-soft);
}

body.sv-pro-app .sv-nav-link-secondary:not(.is-active) {
    color: #596579;
}

.sv-pro-nav-icon {
    display: grid;
    width: 30px;
    height: 30px;
    flex: 0 0 30px;
    place-items: center;
    border-radius: 9px;
    color: #667085;
    background: #f4f6f9;
    transition: color .15s ease, background-color .15s ease;
}

.sv-pro-nav-icon--blue { color: #3974d7; background: #eef4ff; }
.sv-pro-nav-icon--green { color: #2f8b50; background: #eef8f1; }
.sv-pro-nav-icon--violet { color: #765ed0; background: #f4f0ff; }
.sv-pro-nav-icon--slate { color: #596579; background: #eef1f5; }
.sv-pro-nav-icon--amber { color: #b27516; background: #fff7e8; }

.sv-nav-link.is-active .sv-pro-nav-icon--blue,
.sv-nav-link:hover .sv-pro-nav-icon--blue { color: var(--svp-blue); background: #e3edff; }
.sv-nav-link.is-active .sv-pro-nav-icon--green,
.sv-nav-link:hover .sv-pro-nav-icon--green { color: var(--svp-green); background: #e4f5ea; }
.sv-nav-link.is-active .sv-pro-nav-icon--violet,
.sv-nav-link:hover .sv-pro-nav-icon--violet { color: var(--svp-violet); background: #ece6ff; }
.sv-nav-link.is-active .sv-pro-nav-icon--slate,
.sv-nav-link:hover .sv-pro-nav-icon--slate { color: var(--svp-slate); background: #e6ebf0; }
.sv-nav-link.is-active .sv-pro-nav-icon--amber,
.sv-nav-link:hover .sv-pro-nav-icon--amber { color: var(--svp-amber); background: #ffefc9; }

body.sv-pro-app .sv-sidebar-footer-simple {
    margin: 0 14px 16px;
    padding: 12px 13px;
    gap: 9px;
    border: 1px solid var(--svp-border);
    border-radius: 11px;
    color: var(--svp-muted);
    background: var(--svp-surface-soft);
    font-size: 11.5px;
    font-weight: 600;
}

body.sv-pro-app .sv-sidebar-footer-dot {
    width: 8px;
    height: 8px;
    background: #22a447;
    box-shadow: 0 0 0 4px rgba(34, 164, 71, .12);
}

body.sv-pro-app .sv-app-main {
    min-height: 100svh;
    margin-left: var(--svp-sidebar);
}

body.sv-pro-app .sv-app-topbar {
    min-height: var(--svp-topbar);
    border-bottom: 1px solid var(--svp-border);
    background: rgba(255, 255, 255, .94);
    backdrop-filter: blur(14px);
}

body.sv-pro-app .sv-topbar-inner {
    width: min(100%, calc(var(--svp-content) + 56px));
    min-height: var(--svp-topbar);
    margin: 0 auto;
    padding: 0 28px;
}

body.sv-pro-app .sv-topbar-left {
    min-width: 0;
    gap: 12px;
}

body.sv-pro-app .sv-page-title {
    margin: 0;
    color: var(--svp-heading);
    font-size: 24px;
    font-weight: 760;
    letter-spacing: -.035em;
    line-height: 1.2;
}

body.sv-pro-app .sv-page-subtitle {
    margin: 4px 0 0;
    color: var(--svp-muted);
    font-size: 12.5px;
    line-height: 1.35;
}

body.sv-pro-app .sv-topbar-actions {
    gap: 9px;
}

body.sv-pro-app .sv-icon-button {
    width: 40px;
    height: 40px;
    border: 1px solid var(--svp-border);
    border-radius: 11px;
    color: var(--svp-slate);
    background: #fff;
}

body.sv-pro-app .sv-icon-button:hover {
    color: var(--svp-blue);
    border-color: #cddcff;
    background: var(--svp-blue-soft);
}

body.sv-pro-app .sv-profile-trigger {
    min-height: 44px;
    padding: 5px 8px 5px 6px;
    border: 1px solid var(--svp-border);
    border-radius: 12px;
    background: #fff;
}

body.sv-pro-app .sv-avatar {
    width: 32px;
    height: 32px;
    flex-basis: 32px;
    border-radius: 9px;
    color: #fff;
    background: var(--svp-blue);
    font-size: 12px;
}

body.sv-pro-app .sv-profile-copy strong { font-size: 12.5px; }
body.sv-pro-app .sv-profile-copy small { font-size: 10.5px; color: var(--svp-muted); }

body.sv-pro-app .sv-profile-dropdown {
    width: 260px;
    padding: 8px;
    border: 1px solid var(--svp-border);
    border-radius: 14px;
    box-shadow: var(--svp-shadow-float);
}

body.sv-pro-app .sv-page-content {
    width: min(100%, calc(var(--svp-content) + 56px));
    margin: 0 auto;
    padding: 28px 28px 44px;
}

/* Shared surfaces */
body.sv-pro-app .sv-card,
body.sv-pro-app .sv-filter-card,
body.sv-pro-app .sv-settings-card,
body.sv-pro-app .sv-tech-room,
body.sv-pro-app .sv-tech-panel {
    border: 1px solid var(--svp-border);
    border-radius: var(--svp-radius);
    background: var(--svp-surface);
    box-shadow: var(--svp-shadow);
}

body.sv-pro-app .sv-card-header {
    min-height: 68px;
    padding: 18px 20px;
    align-items: center;
    border-bottom: 1px solid var(--svp-border);
}

body.sv-pro-app .sv-card-header h2,
body.sv-pro-app .sv-card-header h3 {
    margin: 0;
    color: var(--svp-heading);
    font-size: 16px;
    font-weight: 730;
    letter-spacing: -.015em;
}

body.sv-pro-app .sv-card-header p {
    margin: 3px 0 0;
    color: var(--svp-muted);
    font-size: 12.5px;
}

body.sv-pro-app .sv-card-body {
    padding: 20px;
}

body.sv-pro-app .sv-card-body-table {
    padding: 0;
}

body.sv-pro-app .sv-text-button {
    color: var(--svp-blue);
    font-size: 12.5px;
    font-weight: 700;
}

body.sv-pro-app .sv-text-button:hover {
    color: var(--svp-blue-dark);
    text-decoration: underline;
    text-underline-offset: 3px;
}

body.sv-pro-app .sv-button {
    min-height: 42px;
    padding: 0 15px;
    border-radius: 10px;
    font-size: 13px;
    font-weight: 700;
}

body.sv-pro-app .sv-button-sm {
    min-height: 36px;
    padding-inline: 12px;
    font-size: 12px;
}

body.sv-pro-app .sv-button-primary {
    border-color: var(--svp-blue);
    background: var(--svp-blue);
    box-shadow: 0 5px 14px rgba(37, 99, 235, .15);
}

body.sv-pro-app .sv-button-primary:hover {
    border-color: var(--svp-blue-dark);
    background: var(--svp-blue-dark);
}

body.sv-pro-app .sv-button-secondary,
body.sv-pro-app .sv-button-ghost {
    color: var(--svp-slate);
    border-color: var(--svp-border-strong);
    background: #fff;
}

body.sv-pro-app .sv-button-secondary:hover,
body.sv-pro-app .sv-button-ghost:hover {
    color: var(--svp-blue);
    border-color: #c8d8ff;
    background: var(--svp-blue-soft);
}

body.sv-pro-app .sv-badge {
    min-height: 28px;
    padding: 4px 9px;
    gap: 6px;
    border-radius: 999px;
    font-size: 11px;
    font-weight: 700;
}

body.sv-pro-app .sv-badge-success { color: #137136; background: #edf8f1; border-color: #cdebd7; }
body.sv-pro-app .sv-badge-warning { color: #986000; background: #fff7e5; border-color: #f3dfad; }
body.sv-pro-app .sv-badge-danger { color: #b23434; background: #fff0f0; border-color: #f4cccc; }
body.sv-pro-app .sv-badge-neutral { color: #596579; background: #f3f5f8; border-color: #e0e5ec; }

body.sv-pro-app .sv-status-dot {
    width: 7px;
    height: 7px;
}

body.sv-pro-app .sv-feature-icon {
    border-radius: 10px;
}

body.sv-pro-app .sv-feature-icon--soft {
    background: #f3f5f8;
}

body.sv-pro-app .sv-feature-icon--blue { color: var(--svp-blue); background: var(--svp-blue-soft); }
body.sv-pro-app .sv-feature-icon--green { color: var(--svp-green); background: var(--svp-green-soft); }
body.sv-pro-app .sv-feature-icon--amber { color: var(--svp-amber); background: var(--svp-amber-soft); }
body.sv-pro-app .sv-feature-icon--violet { color: var(--svp-violet); background: var(--svp-violet-soft); }
body.sv-pro-app .sv-feature-icon--cyan { color: var(--svp-cyan); background: var(--svp-cyan-soft); }
body.sv-pro-app .sv-feature-icon--slate { color: var(--svp-slate); background: #eef1f5; }
body.sv-pro-app .sv-feature-icon--rose { color: #bb4e70; background: #fff0f4; }

.sv-pro-icon-tile {
    display: grid;
    width: 42px;
    height: 42px;
    flex: 0 0 42px;
    place-items: center;
    border-radius: 12px;
}

.sv-pro-icon-tile--sm { width: 34px; height: 34px; flex-basis: 34px; border-radius: 9px; }
.sv-pro-icon-tile--blue { color: var(--svp-blue); background: var(--svp-blue-soft); }
.sv-pro-icon-tile--green { color: var(--svp-green); background: var(--svp-green-soft); }
.sv-pro-icon-tile--amber { color: var(--svp-amber); background: var(--svp-amber-soft); }
.sv-pro-icon-tile--violet { color: var(--svp-violet); background: var(--svp-violet-soft); }
.sv-pro-icon-tile--cyan { color: var(--svp-cyan); background: var(--svp-cyan-soft); }

/* Forms */
body.sv-pro-app .sv-form-label {
    margin-bottom: 7px;
    color: #344054;
    font-size: 12.5px;
    font-weight: 700;
}

body.sv-pro-app .sv-form-control {
    min-height: 44px;
    padding: 10px 12px;
    border: 1px solid var(--svp-border-strong);
    border-radius: 10px;
    color: var(--svp-text);
    background: #fff;
    font-size: 13.5px;
}

body.sv-pro-app .sv-form-control::placeholder { color: #a4adbb; }
body.sv-pro-app .sv-form-control:hover { border-color: #b9c5d5; }
body.sv-pro-app .sv-form-control:focus {
    border-color: #8bb0ff;
    box-shadow: 0 0 0 4px rgba(37, 99, 235, .10);
}

body.sv-pro-app .sv-form-grid-two {
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 16px;
}

body.sv-pro-app .sv-form-grid-three {
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 16px;
}

body.sv-pro-app .sv-checkbox {
    min-height: 42px;
    font-size: 13px;
}

/* Tables */
body.sv-pro-app .sv-table-wrap {
    border-radius: 0 0 var(--svp-radius) var(--svp-radius);
}

body.sv-pro-app .sv-table {
    font-size: 12.5px;
}

body.sv-pro-app .sv-table th {
    height: 46px;
    padding: 0 16px;
    color: #667085;
    background: #f8fafc;
    font-size: 10.5px;
    font-weight: 760;
    letter-spacing: .055em;
    text-transform: uppercase;
}

body.sv-pro-app .sv-table td {
    height: 52px;
    padding: 10px 16px;
    border-top-color: #edf0f4;
    color: #344054;
}

body.sv-pro-app .sv-table tbody tr:hover td {
    background: #fbfcfe;
}

/* Dashboard */
.sv-pro-metrics {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 16px;
    margin-bottom: 16px;
}

.sv-pro-metric {
    position: relative;
    min-width: 0;
    min-height: 158px;
    padding: 18px;
    overflow: hidden;
    border: 1px solid var(--svp-border);
    border-radius: var(--svp-radius);
    background: #fff;
    box-shadow: var(--svp-shadow);
}

.sv-pro-metric::after {
    position: absolute;
    inset: auto 0 0;
    height: 3px;
    content: "";
    background: #dfe5ed;
}

.sv-pro-metric:nth-child(1)::after { background: var(--svp-blue); }
.sv-pro-metric:nth-child(2)::after { background: #2aa75b; }
.sv-pro-metric:nth-child(3)::after { background: #d08a18; }
.sv-pro-metric:nth-child(4)::after { background: #1596a6; }

.sv-pro-metric-head {
    display: flex;
    align-items: center;
    gap: 11px;
}

.sv-pro-metric-label {
    color: #475467;
    font-size: 12.5px;
    font-weight: 700;
}

.sv-pro-metric-value {
    display: flex;
    margin-top: 17px;
    align-items: baseline;
    gap: 6px;
    color: var(--svp-heading);
}

.sv-pro-metric-value strong {
    font-size: clamp(27px, 2vw, 34px);
    font-weight: 760;
    letter-spacing: -.045em;
    line-height: 1;
}

.sv-pro-metric-value small {
    color: var(--svp-muted);
    font-size: 12px;
    font-weight: 650;
}

.sv-pro-metric-value--money small {
    color: var(--svp-heading);
    font-size: 15px;
    font-weight: 730;
}

.sv-pro-metric-note,
.sv-pro-metric-meta {
    margin: 14px 0 0;
    color: var(--svp-muted);
    font-size: 11.5px;
}

.sv-pro-metric-meta {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 8px;
}

.sv-pro-state {
    display: inline-flex;
    align-items: center;
    font-weight: 700;
}

.sv-pro-state.is-success { color: var(--svp-green); }
.sv-pro-state.is-warning { color: var(--svp-amber); }
.sv-pro-state.is-danger { color: var(--svp-red); }

.sv-pro-metric .sv-progress {
    height: 5px;
    margin-top: 10px;
    border-radius: 999px;
    background: #eef1f5;
}

.sv-pro-system-strip {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 0;
    margin-bottom: 18px;
    overflow: hidden;
    border: 1px solid var(--svp-border);
    border-radius: var(--svp-radius);
    background: #fff;
    box-shadow: 0 1px 2px rgba(16, 24, 40, .025);
}

.sv-pro-system-item {
    display: flex;
    min-width: 0;
    min-height: 72px;
    padding: 14px 16px;
    align-items: center;
    gap: 11px;
    border-right: 1px solid var(--svp-border);
}

.sv-pro-system-item:last-child { border-right: 0; }

.sv-pro-system-icon {
    display: grid;
    width: 34px;
    height: 34px;
    flex: 0 0 34px;
    place-items: center;
    border-radius: 10px;
    color: #667085;
    background: #f3f5f8;
}

.sv-pro-system-item.is-success .sv-pro-system-icon { color: var(--svp-green); background: var(--svp-green-soft); }
.sv-pro-system-item.is-warning .sv-pro-system-icon { color: var(--svp-amber); background: var(--svp-amber-soft); }

.sv-pro-system-item > div { min-width: 0; }
.sv-pro-system-item strong,
.sv-pro-system-item span { display: block; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.sv-pro-system-item strong { color: #344054; font-size: 12px; font-weight: 720; }
.sv-pro-system-item > div > span { margin-top: 2px; color: var(--svp-muted); font-size: 10.5px; }

.sv-pro-dashboard-grid {
    display: grid;
    grid-template-columns: minmax(0, 1.65fr) minmax(330px, .85fr);
    gap: 18px;
    margin-bottom: 18px;
}

.sv-pro-chart-card .sv-chart-container { min-height: 330px; }
.sv-pro-control-card .sv-card-body { padding: 12px 16px 16px; }

body.sv-pro-app .sv-chart-summary {
    margin-bottom: 14px;
    gap: 10px;
}

body.sv-pro-app .sv-chart-summary > div {
    padding: 11px 13px;
    border: 1px solid var(--svp-border);
    border-radius: 10px;
    background: var(--svp-surface-soft);
}

body.sv-pro-app .sv-chart-summary span { font-size: 10.5px; }
body.sv-pro-app .sv-chart-summary strong { font-size: 15px; }

body.sv-pro-app .sv-room-control {
    border: 1px solid var(--svp-border);
    border-radius: 11px;
    background: #fff;
}

body.sv-pro-app .sv-room-control + .sv-room-control { margin-top: 9px; }
body.sv-pro-app .sv-room-control > summary { min-height: 58px; padding: 9px 10px; }
body.sv-pro-app .sv-room-devices { padding: 2px 10px 10px; }

.sv-pro-device-icon {
    display: grid;
    width: 32px;
    height: 32px;
    flex: 0 0 32px;
    place-items: center;
    border-radius: 9px;
    color: #667085;
    background: #f2f4f7;
}
.sv-pro-device-icon.is-on { color: var(--svp-green); background: var(--svp-green-soft); }

body.sv-pro-app .sv-device-row {
    min-height: 52px;
    padding: 8px 2px;
    border-top: 1px solid #edf0f4;
}

body.sv-pro-app .sv-device-copy strong { font-size: 12.5px; }
body.sv-pro-app .sv-device-copy span { font-size: 10.5px; }

body.sv-pro-app .sv-device-switch {
    min-width: 76px;
    min-height: 34px;
    border-radius: 9px;
    font-size: 10.5px;
}

.sv-pro-activity-card { margin-top: 0; }

/* Energy history */
body.sv-pro-app .sv-filter-card {
    margin-bottom: 16px;
    box-shadow: 0 1px 2px rgba(16, 24, 40, .025);
}

body.sv-pro-app .sv-filter-card .sv-card-body { padding: 18px; }

body.sv-pro-app .sv-filter-grid-compact {
    grid-template-columns: minmax(240px, 1.4fr) minmax(150px, .75fr) minmax(150px, .75fr) auto;
    gap: 14px;
    align-items: end;
}

body.sv-pro-app .sv-filter-actions {
    display: flex;
    gap: 8px;
}

body.sv-pro-app .sv-filter-footer {
    margin-top: 14px;
    padding-top: 14px;
    border-top: 1px solid var(--svp-border);
}

body.sv-pro-app .sv-quick-filters { gap: 6px; }
body.sv-pro-app .sv-quick-filter {
    min-height: 34px;
    padding: 0 11px;
    border: 1px solid var(--svp-border);
    border-radius: 9px;
    color: #596579;
    background: #fff;
    font-size: 11.5px;
    font-weight: 650;
}
body.sv-pro-app .sv-quick-filter:hover,
body.sv-pro-app .sv-quick-filter.is-active { color: var(--svp-blue); border-color: #c8d8ff; background: var(--svp-blue-soft); }

body.sv-pro-app .sv-history-metrics-three {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 14px;
    margin-bottom: 16px;
}

body.sv-pro-app .sv-history-metric {
    min-height: 92px;
    padding: 16px;
    gap: 12px;
    border: 1px solid var(--svp-border);
    border-radius: var(--svp-radius);
    background: #fff;
    box-shadow: var(--svp-shadow);
}

body.sv-pro-app .sv-history-metric::before { display: none; }
body.sv-pro-app .sv-history-metric span { font-size: 11.5px; }
body.sv-pro-app .sv-history-metric strong { margin-top: 5px; color: var(--svp-heading); font-size: 20px; letter-spacing: -.025em; }

body.sv-pro-app .sv-section-grid-analysis {
    grid-template-columns: minmax(0, 1.65fr) minmax(300px, .75fr);
    gap: 16px;
    margin-bottom: 16px;
}

body.sv-pro-app .sv-history-chart-card .sv-chart-container { min-height: 350px; }
body.sv-pro-app .sv-chart-tabs { padding: 3px; border: 1px solid var(--svp-border); border-radius: 10px; background: #f7f9fc; }
body.sv-pro-app .sv-chart-tab { min-height: 32px; padding: 0 12px; border-radius: 7px; font-size: 11.5px; }
body.sv-pro-app .sv-chart-tab.is-active { color: var(--svp-blue); background: #fff; box-shadow: 0 1px 3px rgba(16,24,40,.08); }

body.sv-pro-app .sv-estimation-highlight {
    padding: 18px;
    border: 1px solid #dce8ff;
    border-radius: 12px;
    background: #f5f8ff;
}
body.sv-pro-app .sv-estimation-highlight strong { font-size: 27px; color: var(--svp-heading); }
body.sv-pro-app .sv-estimation-secondary { margin-top: 14px; }

/* Rooms and devices */
body.sv-pro-app .sv-room-summary-grid-three {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 14px;
    margin-bottom: 16px;
}

body.sv-pro-app .sv-summary-card {
    min-height: 86px;
    padding: 15px 17px;
    border: 1px solid var(--svp-border);
    border-radius: var(--svp-radius);
    background: #fff;
    box-shadow: var(--svp-shadow);
}
body.sv-pro-app .sv-summary-card::before { display: none; }
body.sv-pro-app .sv-summary-copy span { font-size: 11.5px; }
body.sv-pro-app .sv-summary-copy strong { font-size: 22px; }

body.sv-pro-app .sv-room-grid-three {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 16px;
}

body.sv-pro-app .sv-room-card {
    min-width: 0;
    overflow: hidden;
    border: 1px solid var(--svp-border);
    border-radius: var(--svp-radius);
    background: #fff;
    box-shadow: var(--svp-shadow);
}

body.sv-pro-app .sv-room-card::before { display: none; }

body.sv-pro-app .sv-room-card-head {
    min-height: 72px;
    padding: 15px 16px;
    border-bottom: 1px solid var(--svp-border);
}

body.sv-pro-app .sv-room-card-title h3 {
    color: var(--svp-heading);
    font-size: 15px;
    font-weight: 730;
}
body.sv-pro-app .sv-room-card-title p { margin-top: 3px; color: var(--svp-muted); font-size: 11px; }
body.sv-pro-app .sv-room-card-body { padding: 6px 14px 10px; }
body.sv-pro-app .sv-room-card-foot { min-height: 43px; padding: 10px 15px; border-top: 1px solid var(--svp-border); background: #fbfcfe; font-size: 11.5px; }

body.sv-pro-app .sv-device-directory {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 12px;
}

body.sv-pro-app .sv-device-directory-card {
    min-height: 70px;
    padding: 13px;
    border: 1px solid var(--svp-border);
    border-radius: 11px;
    background: #fff;
}

/* Settings */
body.sv-pro-app .sv-settings-layout {
    display: grid;
    grid-template-columns: 230px minmax(0, 1fr);
    gap: 18px;
    align-items: start;
}

body.sv-pro-app .sv-settings-nav {
    position: sticky;
    top: calc(var(--svp-topbar) + 20px);
    padding: 8px;
    border: 1px solid var(--svp-border);
    border-radius: var(--svp-radius);
    background: #fff;
    box-shadow: var(--svp-shadow);
}

body.sv-pro-app .sv-tab-button {
    min-height: 44px;
    padding: 9px 10px;
    border: 1px solid transparent;
    border-radius: 9px;
    color: #596579;
    font-size: 12.5px;
    font-weight: 650;
}
body.sv-pro-app .sv-tab-button:hover { color: var(--svp-heading); background: #f7f9fc; }
body.sv-pro-app .sv-tab-button.is-active { color: var(--svp-blue); border-color: #dce8ff; background: var(--svp-blue-soft); }
body.sv-pro-app .sv-tab-button .sv-feature-icon { width: 30px; height: 30px; }

body.sv-pro-app .sv-settings-panels { min-width: 0; }
body.sv-pro-app .sv-settings-card { overflow: hidden; }
body.sv-pro-app .sv-profile-summary { gap: 14px; }
body.sv-pro-app .sv-profile-avatar-large { width: 58px; height: 58px; border-radius: 16px; background: var(--svp-blue); }

body.sv-pro-app .sv-technician-summary-three {
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 12px;
}

body.sv-pro-app .sv-technician-summary-card {
    min-height: 84px;
    padding: 14px;
    border: 1px solid var(--svp-border);
    border-radius: 11px;
    background: #fff;
}
body.sv-pro-app .sv-technician-summary-card::before { display: none; }

body.sv-pro-app .sv-technician-toolbar {
    padding: 14px 16px;
    border: 1px solid var(--svp-border);
    border-radius: 12px;
    background: #fff;
}

body.sv-pro-app .sv-tech-room-list { display: grid; gap: 12px; }
body.sv-pro-app .sv-tech-room { overflow: hidden; box-shadow: none; }
body.sv-pro-app .sv-tech-room > summary {
    min-height: 62px;
    padding: 12px 15px;
    background: #fff;
}
body.sv-pro-app .sv-tech-room[open] > summary { background: #fbfcfe; border-bottom: 1px solid var(--svp-border); }
body.sv-pro-app .sv-tech-room-body { padding: 16px; }
body.sv-pro-app .sv-tech-two-column,
body.sv-pro-app .sv-tech-inventory-grid {
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 14px;
}

body.sv-pro-app .sv-tech-panel {
    padding: 0;
    box-shadow: none;
}
body.sv-pro-app .sv-tech-panel > header { min-height: 54px; padding: 12px 14px; border-bottom: 1px solid var(--svp-border); }
body.sv-pro-app .sv-tech-panel > header h4 { font-size: 13.5px; }
body.sv-pro-app .sv-tech-item-list { padding: 10px; }
body.sv-pro-app .sv-tech-item { padding: 12px; border: 1px solid var(--svp-border); border-radius: 10px; background: #fff; }
body.sv-pro-app .sv-tech-item + .sv-tech-item { margin-top: 8px; }
body.sv-pro-app .sv-tech-item-copy strong { font-size: 12.5px; }
body.sv-pro-app .sv-tech-item-copy span { font-size: 10.5px; }
body.sv-pro-app .sv-tech-actions-row { gap: 8px; }
body.sv-pro-app .sv-tech-edit > summary { min-height: 34px; padding: 0 10px; border: 1px solid var(--svp-border-strong); border-radius: 8px; font-size: 11.5px; }
body.sv-pro-app .sv-tech-edit-body { padding: 13px; border: 1px solid var(--svp-border); border-radius: 10px; background: #f8fafc; }

body.sv-pro-app .sv-dialog {
    width: min(560px, calc(100vw - 28px));
    border: 1px solid var(--svp-border);
    border-radius: 16px;
    box-shadow: var(--svp-shadow-float);
}

/* Empty states */
body.sv-pro-app .sv-empty-state {
    min-height: 220px;
    padding: 28px 20px;
}
body.sv-pro-app .sv-empty-state-compact { min-height: 128px; padding: 18px; }
body.sv-pro-app .sv-empty-state h3 { margin: 10px 0 0; font-size: 14px; }
body.sv-pro-app .sv-empty-state p { max-width: 360px; margin: 5px auto 0; color: var(--svp-muted); font-size: 12px; }

/* Alerts */
body.sv-pro-app .sv-alert {
    margin-bottom: 16px;
    padding: 12px 14px;
    border-radius: 11px;
    font-size: 12.5px;
}

/* Bottom navigation */
body.sv-pro-app .sv-bottom-navigation {
    border-top: 1px solid var(--svp-border);
    background: rgba(255,255,255,.96);
    backdrop-filter: blur(14px);
}
body.sv-pro-app .sv-bottom-navigation a {
    min-height: 58px;
    color: #7b8798;
    font-size: 9.5px;
    font-weight: 650;
}
body.sv-pro-app .sv-bottom-navigation a.is-active { color: var(--svp-blue); }
body.sv-pro-app .sv-bottom-navigation a::before { background: var(--svp-blue); }

/* Responsive */
@media (max-width: 1399px) {
    :root { --svp-content: 1320px; }
    .sv-pro-metrics { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    body.sv-pro-app .sv-room-grid-three { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    body.sv-pro-app .sv-device-directory { grid-template-columns: repeat(2, minmax(0, 1fr)); }
}

@media (max-width: 1180px) {
    .sv-pro-dashboard-grid,
    body.sv-pro-app .sv-section-grid-analysis { grid-template-columns: minmax(0, 1fr); }
    .sv-pro-system-strip { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    .sv-pro-system-item:nth-child(2) { border-right: 0; }
    .sv-pro-system-item:nth-child(-n+2) { border-bottom: 1px solid var(--svp-border); }
    body.sv-pro-app .sv-filter-grid-compact { grid-template-columns: 1fr 1fr 1fr; }
    body.sv-pro-app .sv-filter-actions { grid-column: 1 / -1; justify-content: flex-end; }
}

@media (max-width: 1023px) {
    body.sv-pro-app .sv-app-main { margin-left: 0; }
    body.sv-pro-app .sv-app-sidebar { width: min(310px, 88vw); box-shadow: var(--svp-shadow-float); }
    body.sv-pro-app .sv-topbar-inner,
    body.sv-pro-app .sv-page-content { width: 100%; }
    body.sv-pro-app .sv-topbar-inner { padding-inline: 20px; }
    body.sv-pro-app .sv-page-content { padding: 22px 20px 88px; }
    body.sv-pro-app .sv-page-title { font-size: 22px; }
    body.sv-pro-app .sv-profile-copy { display: none; }
    body.sv-pro-app .sv-profile-trigger { width: 44px; padding: 5px; }
    body.sv-pro-app .sv-profile-trigger > .sv-icon { display: none; }
    body.sv-pro-app .sv-settings-layout { grid-template-columns: 1fr; }
    body.sv-pro-app .sv-settings-nav { position: static; display: flex; overflow-x: auto; gap: 6px; padding: 6px; }
    body.sv-pro-app .sv-tab-button { flex: 0 0 auto; }
    body.sv-pro-app .sv-tech-two-column,
    body.sv-pro-app .sv-tech-inventory-grid { grid-template-columns: 1fr; }
}

@media (max-width: 767px) {
    body.sv-pro-app .sv-app-topbar { min-height: 66px; }
    body.sv-pro-app .sv-topbar-inner { min-height: 66px; padding-inline: 14px; }
    body.sv-pro-app .sv-page-content { padding: 16px 14px 86px; }
    body.sv-pro-app .sv-page-title { font-size: 20px; }
    body.sv-pro-app .sv-page-subtitle { display: none; }
    body.sv-pro-app .sv-topbar-status { display: none; }
    body.sv-pro-app .sv-card-header { min-height: 62px; padding: 15px 16px; gap: 10px; }
    body.sv-pro-app .sv-card-header h2 { font-size: 15px; }
    body.sv-pro-app .sv-card-body { padding: 16px; }

    .sv-pro-metrics,
    body.sv-pro-app .sv-history-metrics-three,
    body.sv-pro-app .sv-room-summary-grid-three { grid-template-columns: 1fr 1fr; gap: 11px; }
    .sv-pro-metric { min-height: 145px; padding: 15px; }
    .sv-pro-icon-tile { width: 38px; height: 38px; flex-basis: 38px; }
    .sv-pro-metric-value { margin-top: 14px; }
    .sv-pro-metric-value strong { font-size: 27px; }

    .sv-pro-system-strip { grid-template-columns: 1fr; }
    .sv-pro-system-item { min-height: 64px; border-right: 0; border-bottom: 1px solid var(--svp-border); }
    .sv-pro-system-item:last-child { border-bottom: 0; }

    .sv-pro-dashboard-grid { gap: 14px; }
    .sv-pro-chart-card .sv-chart-container,
    body.sv-pro-app .sv-history-chart-card .sv-chart-container { min-height: 260px; }

    body.sv-pro-app .sv-filter-grid-compact { grid-template-columns: 1fr; }
    body.sv-pro-app .sv-filter-actions { grid-column: auto; justify-content: stretch; }
    body.sv-pro-app .sv-filter-actions .sv-button { flex: 1; }
    body.sv-pro-app .sv-filter-footer { align-items: stretch; gap: 12px; }
    body.sv-pro-app .sv-quick-filters { overflow-x: auto; flex-wrap: nowrap; padding-bottom: 2px; }
    body.sv-pro-app .sv-quick-filter { flex: 0 0 auto; }

    body.sv-pro-app .sv-room-grid-three,
    body.sv-pro-app .sv-device-directory { grid-template-columns: 1fr; }

    body.sv-pro-app .sv-form-grid-two,
    body.sv-pro-app .sv-form-grid-three { grid-template-columns: 1fr; }
    body.sv-pro-app .sv-grid-full { grid-column: auto; }

    body.sv-pro-app .sv-table-wrap { overflow-x: auto; }
    body.sv-pro-app .sv-table { min-width: 680px; }

    body.sv-pro-app .sv-technician-summary-three { grid-template-columns: 1fr; }
    body.sv-pro-app .sv-settings-nav { margin-inline: -2px; }
}

@media (max-width: 520px) {
    .sv-pro-metrics,
    body.sv-pro-app .sv-history-metrics-three,
    body.sv-pro-app .sv-room-summary-grid-three { grid-template-columns: 1fr; }
    .sv-pro-metric { min-height: 136px; }
    .sv-pro-metric-head { justify-content: flex-start; }
    body.sv-pro-app .sv-chart-summary { grid-template-columns: repeat(3, minmax(0, 1fr)); }
    body.sv-pro-app .sv-chart-summary > div { padding: 9px; }
    body.sv-pro-app .sv-chart-summary strong { font-size: 13px; }
    body.sv-pro-app .sv-card-header { align-items: flex-start; }
    body.sv-pro-app .sv-card-header > :last-child { flex: 0 0 auto; }
}

@media (prefers-reduced-motion: reduce) {
    *, *::before, *::after {
        scroll-behavior: auto !important;
        animation-duration: .01ms !important;
        animation-iteration-count: 1 !important;
        transition-duration: .01ms !important;
    }
}

/* =========================================================
   SmartVolt UI V8 — UX clarity refinements
   ========================================================= */

/* Navigation: warna tetap konsisten, tetapi menu nonaktif lebih tenang. */
body.sv-pro-app .sv-nav-link:not(.is-active) .sv-pro-nav-icon {
    background: transparent;
    box-shadow: none;
}

body.sv-pro-app .sv-nav-link:not(.is-active):hover .sv-pro-nav-icon {
    background: #f1f4f8;
}

body.sv-pro-app .sv-nav-link.is-active .sv-pro-nav-icon {
    box-shadow: inset 0 0 0 1px rgba(255, 255, 255, .55);
}

/* Status sistem dibuat lebih padat agar tidak mengambil ruang berlebihan. */
.sv-pro-system-item {
    min-height: 62px;
    padding: 11px 14px;
}

.sv-pro-system-icon {
    width: 32px;
    height: 32px;
    flex-basis: 32px;
}

.sv-pro-system-item strong {
    font-size: 11.75px;
}

.sv-pro-system-item > div > span {
    font-size: 10px;
}

/* Grafik Dashboard menegaskan bahwa angka pertama adalah data historis terakhir. */
.sv-chart-summary-time {
    display: block;
    margin-top: 3px;
    color: var(--svp-muted-2);
    font-size: 9.5px;
    font-weight: 600;
}

.sv-pro-chart-card .sv-chart-container {
    min-height: 310px;
}

/* Panel kontrol hanya menampilkan ruangan yang memiliki perangkat. */
.sv-room-control-list {
    display: grid;
    gap: 9px;
}

body.sv-pro-app .sv-room-control + .sv-room-control {
    margin-top: 0;
}

.sv-pro-control-card .sv-card-body {
    padding-top: 12px;
}

.sv-pro-device-icon.is-offline {
    color: var(--svp-amber);
    background: var(--svp-amber-soft);
}

/* Empty state dipadatkan agar halaman lebih efisien. */
body.sv-pro-app .sv-empty-state-compact {
    min-height: 104px;
    padding: 14px 16px;
}

body.sv-pro-app .sv-room-card-body .sv-empty-state-compact {
    min-height: 112px;
}

body.sv-pro-app .sv-empty-state-compact h3 {
    margin-top: 8px;
}

body.sv-pro-app .sv-empty-state-compact .sv-button,
body.sv-pro-app .sv-empty-state-compact .sv-text-button {
    margin-top: 10px;
}

/* Ringkasan perangkat menggunakan istilah yang tidak ambigu. */
body.sv-pro-app .sv-summary-card--cyan {
    border-color: #d8eef1;
    background: linear-gradient(180deg, #ffffff 0%, #f7fcfd 100%);
}

body.sv-pro-app .sv-room-card-foot {
    color: var(--svp-muted);
}

body.sv-pro-app .sv-room-card-foot strong {
    color: var(--svp-heading);
}

@media (max-width: 767px) {
    .sv-pro-system-item {
        min-height: 58px;
    }

    .sv-pro-chart-card .sv-chart-container {
        min-height: 250px;
    }
}
```

## `public/assets/js/smartvolt-dashboard.js`

```javascript
(() => {
    'use strict';

    const dataNode = document.getElementById('smartvolt-dashboard-data');
    if (!dataNode || !window.SmartVolt) {
        return;
    }

    let config = {};
    try {
        config = JSON.parse(dataNode.textContent || '{}');
    } catch {
        window.SmartVolt.toast('Dashboard tidak dapat dimuat.', {type: 'error'});
        return;
    }

    const formatNumber = (value, digits = 0) => Number(value || 0).toLocaleString('id-ID', {
        minimumFractionDigits: digits,
        maximumFractionDigits: digits,
    });

    const setText = (selector, value) => {
        document.querySelectorAll(selector).forEach((element) => {
            element.textContent = String(value);
        });
    };

    const toNumbers = (values) => Array.isArray(values)
        ? values.map((value) => Number(value || 0))
        : [];

    const chartCanvas = document.getElementById('dashboardEnergyChart');
    const chartContainer = document.querySelector('[data-dashboard-chart-container]');
    const chartEmpty = document.querySelector('[data-dashboard-chart-empty]');

    let chartMode = 'power';
    let chartInstance = null;
    let dashboardChart = config.chart || {labels: [], power: [], energy: []};

    const currentChartValues = () => toNumbers(dashboardChart[chartMode]);

    const updateChartSummary = () => {
        const values = currentChartValues();
        const unit = chartMode === 'power' ? 'W' : 'kWh';
        const digits = chartMode === 'power' ? 1 : 4;
        const average = values.length
            ? values.reduce((total, value) => total + value, 0) / values.length
            : 0;
        const maximum = values.length ? Math.max(...values) : 0;
        const current = values.length ? values[values.length - 1] : 0;

        setText('[data-chart-current]', formatNumber(current, digits));
        setText('[data-chart-average]', formatNumber(average, digits));
        setText('[data-chart-maximum]', formatNumber(maximum, digits));
        setText('[data-chart-unit], [data-chart-average-unit], [data-chart-maximum-unit]', unit);

        const labels = Array.isArray(dashboardChart.labels) ? dashboardChart.labels : [];
        setText('[data-chart-latest-label]', labels.length ? labels[labels.length - 1] : '-');
    };

    const renderChart = () => {
        const labels = Array.isArray(dashboardChart.labels) ? dashboardChart.labels : [];
        const values = currentChartValues();
        const hasData = labels.length > 0 && values.length > 0;

        chartContainer?.classList.toggle('is-hidden', !hasData);
        chartEmpty?.classList.toggle('is-hidden', hasData);
        updateChartSummary();

        if (!hasData || !(chartCanvas instanceof HTMLCanvasElement) || typeof window.Chart !== 'function') {
            chartInstance?.destroy();
            chartInstance = null;
            return;
        }

        const datasetLabel = chartMode === 'power' ? 'Daya' : 'Energi';
        const unit = chartMode === 'power' ? 'W' : 'kWh';
        const borderColor = chartMode === 'power' ? '#1267e8' : '#0aa5b8';
        const backgroundColor = chartMode === 'power'
            ? 'rgba(18, 103, 232, 0.10)'
            : 'rgba(10, 165, 184, 0.10)';

        if (chartInstance) {
            chartInstance.data.labels = labels;
            chartInstance.data.datasets[0].label = datasetLabel;
            chartInstance.data.datasets[0].data = values;
            chartInstance.data.datasets[0].borderColor = borderColor;
            chartInstance.data.datasets[0].backgroundColor = backgroundColor;
            chartInstance.options.scales.y.title.text = unit;
            chartInstance.update('none');
            return;
        }

        chartInstance = new window.Chart(chartCanvas, {
            type: 'line',
            data: {
                labels,
                datasets: [{
                    label: datasetLabel,
                    data: values,
                    borderColor,
                    backgroundColor,
                    borderWidth: 2.2,
                    pointRadius: labels.length > 18 ? 0 : 2.5,
                    pointHoverRadius: 4,
                    pointBackgroundColor: '#ffffff',
                    pointBorderColor: borderColor,
                    pointBorderWidth: 2,
                    fill: true,
                    tension: 0.34,
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: {mode: 'index', intersect: false},
                plugins: {
                    legend: {display: false},
                    tooltip: {
                        displayColors: false,
                        padding: 10,
                        callbacks: {
                            label: (context) => `${datasetLabel}: ${formatNumber(context.parsed.y, chartMode === 'power' ? 1 : 4)} ${unit}`,
                        },
                    },
                },
                scales: {
                    x: {
                        grid: {display: false},
                        border: {display: false},
                        ticks: {color: '#8390a4', maxTicksLimit: 8, font: {size: 10}},
                    },
                    y: {
                        beginAtZero: true,
                        border: {display: false},
                        grid: {color: '#edf1f5'},
                        ticks: {color: '#8390a4', font: {size: 10}},
                        title: {display: true, text: unit, color: '#68758a', font: {size: 10}},
                    },
                },
            },
        });
    };

    document.querySelectorAll('[data-dashboard-chart-mode]').forEach((button) => {
        button.addEventListener('click', () => {
            chartMode = button.dataset.dashboardChartMode === 'energy' ? 'energy' : 'power';

            document.querySelectorAll('[data-dashboard-chart-mode]').forEach((item) => {
                const active = item === button;
                item.classList.toggle('is-active', active);
                item.setAttribute('aria-selected', active ? 'true' : 'false');
            });

            renderChart();
        });
    });

    const loadLabel = (status) => status === 'danger'
        ? 'Beban tinggi'
        : status === 'warning'
            ? 'Mendekati batas'
            : 'Penggunaan normal';

    const updateStatusClass = (element, state) => {
        if (!element) {
            return;
        }
        element.classList.remove('is-success', 'is-warning', 'is-danger');
        element.classList.add(state);
    };

    const updateDashboard = (data) => {
        const stats = data.stats || {};
        const system = data.system || {};

        setText('[data-stat-current-power]', formatNumber(stats.current_power, 1));
        setText('[data-stat-energy-today]', formatNumber(stats.total_energy_today, 3));
        setText('[data-stat-monthly-cost]', formatNumber(stats.monthly_estimated_cost, 0));
        setText('[data-stat-monthly-energy]', formatNumber(stats.monthly_energy_usage, 3));
        setText('[data-stat-powered-devices]', Number(stats.online_active_devices || 0));
        setText('[data-stat-online-devices]', Number(stats.online_devices || 0));
        setText('[data-stat-total-devices]', Number(stats.total_devices || 0));
        setText('[data-stat-load-percentage]', formatNumber(stats.load_percentage, 0));

        const progress = document.querySelector('[data-load-progress]');
        if (progress) {
            progress.style.setProperty('--sv-progress', `${Math.min(100, Math.max(0, Number(stats.load_percentage || 0)))}%`);
            progress.classList.toggle('is-warning', stats.load_status === 'warning');
            progress.classList.toggle('is-danger', stats.load_status === 'danger');
        }

        const loadBadge = document.querySelector('[data-load-status-badge]');
        if (loadBadge) {
            loadBadge.classList.remove('is-success', 'is-warning', 'is-danger');
            loadBadge.classList.add(
                stats.load_status === 'danger'
                    ? 'is-danger'
                    : stats.load_status === 'warning'
                        ? 'is-warning'
                        : 'is-success'
            );
        }
        setText('[data-load-status-label]', loadLabel(stats.load_status));

        const comparison = Number(stats.energy_comparison_percent);
        setText(
            '[data-energy-comparison]',
            Number.isFinite(comparison)
                ? `${comparison > 0 ? 'Lebih tinggi' : comparison < 0 ? 'Lebih hemat' : 'Sama'} ${formatNumber(Math.abs(comparison), 1)}% dari kemarin`
                : 'Perbandingan belum tersedia'
        );

        setText('[data-system-esp-label]', system.esp_online ? `${Number(system.online_esp_count || 0)} ESP32 Terhubung` : 'ESP32 Belum Terhubung');
        setText('[data-system-pzem-label]', system.pzem_status || 'Belum tersedia');
        setText('[data-system-control-label]', system.control_channel_status || 'Menunggu perangkat');
        setText('[data-system-latest-label]', system.latest_received_human || 'Belum ada data');

        updateStatusClass(document.querySelector('[data-system-esp]'), system.esp_online ? 'is-success' : 'is-warning');
        updateStatusClass(document.querySelector('[data-system-pzem]'), system.has_fresh_data ? 'is-success' : 'is-warning');
        updateStatusClass(document.querySelector('[data-system-control]'), system.esp_online ? 'is-success' : 'is-warning');
        updateStatusClass(document.querySelector('[data-system-latest]'), system.has_data ? 'is-success' : 'is-warning');

        const headerBadge = document.querySelector('[data-dashboard-system-badge]');
        const headerStatusLabel = system.has_fresh_data
            ? 'Sistem terhubung'
            : system.has_data
                ? 'Data terlambat'
                : 'Belum terhubung';
        const headerStatusHelp = system.has_fresh_data
            ? 'Data SmartVolt diperbarui secara normal.'
            : system.has_data
                ? `Data terakhir diterima ${system.latest_received_human || 'beberapa waktu lalu'}. Periksa koneksi ESP32.`
                : 'Belum ada data dari perangkat SmartVolt.';

        if (headerBadge) {
            headerBadge.classList.remove('sv-badge-success', 'sv-badge-warning', 'sv-badge-neutral');
            headerBadge.classList.add(system.has_fresh_data ? 'sv-badge-success' : system.has_data ? 'sv-badge-warning' : 'sv-badge-neutral');
            headerBadge.title = headerStatusHelp;
            headerBadge.setAttribute('aria-label', `${headerStatusLabel}. ${headerStatusHelp}`);
        }
        setText('[data-dashboard-system-label]', headerStatusLabel);

        const intro = stats.load_status === 'danger'
            ? 'Beban listrik tinggi. Matikan perangkat yang tidak diperlukan.'
            : stats.load_status === 'warning'
                ? 'Beban listrik mulai mendekati batas rumah.'
                : system.has_fresh_data
                    ? 'Sistem kelistrikan rumah Anda dalam kondisi normal.'
                    : 'Menunggu data terbaru dari perangkat SmartVolt.';
        setText('[data-dashboard-intro-message]', intro);

        dashboardChart = data.chart || dashboardChart;
        renderChart();
    };

    const setSwitchState = (button, isOn, online = true) => {
        button.dataset.currentState = isOn ? 'on' : 'off';
        button.classList.toggle('is-on', isOn);
        button.classList.toggle('is-offline', !online);
        button.setAttribute('aria-pressed', isOn ? 'true' : 'false');

        const label = button.querySelector('[data-switch-label]');
        if (label) {
            label.textContent = online ? (isOn ? 'Nyala' : 'Mati') : 'Offline';
        }
        button.disabled = !online;
    };

    document.addEventListener('click', async (event) => {
        const button = event.target.closest('[data-device-toggle]');
        if (!(button instanceof HTMLButtonElement) || button.disabled || button.classList.contains('is-loading')) {
            return;
        }

        const originalState = button.dataset.currentState === 'on';
        const label = button.querySelector('[data-switch-label]');
        button.classList.add('is-loading');
        button.disabled = true;
        if (label) {
            label.textContent = 'Mengirim';
        }

        try {
            const response = await window.SmartVolt.fetchJson(button.dataset.url, {method: 'POST'});
            const nextState = response.status === 'on' || response.status === true;
            setSwitchState(button, nextState, response.esp_online !== false);
            window.SmartVolt.toast(response.message || 'Status perangkat diperbarui.', {type: 'success'});

            const roomId = button.dataset.roomId;
            if (roomId) {
                const activeInRoom = document.querySelectorAll(`[data-room-id="${roomId}"] [data-device-toggle].is-on:not(.is-offline)`).length;
                setText(`[data-room-active-count="${roomId}"]`, activeInRoom);
            }

            const poweredDevices = document.querySelectorAll('[data-device-toggle].is-on:not(.is-offline)').length;
            setText('[data-stat-powered-devices]', poweredDevices);
        } catch (error) {
            setSwitchState(button, originalState, error.status !== 409);
            window.SmartVolt.toast(error.message || 'Perintah perangkat tidak berhasil.', {type: 'error'});
        } finally {
            button.classList.remove('is-loading');
            if (!button.classList.contains('is-offline')) {
                button.disabled = false;
            }
        }
    });

    let refreshInProgress = false;
    const refreshDashboard = async () => {
        if (refreshInProgress || document.hidden || !config.endpoint) {
            return;
        }

        refreshInProgress = true;
        try {
            const data = await window.SmartVolt.fetchJson(config.endpoint, {method: 'GET'}, 10000);
            updateDashboard(data);
        } catch (error) {
            console.warn('Pembaruan Dashboard gagal:', error.message);
        } finally {
            refreshInProgress = false;
        }
    };

    renderChart();

    const intervalSeconds = Math.min(60, Math.max(10, Number(config.refreshInterval || 30)));
    window.setInterval(refreshDashboard, intervalSeconds * 1000);
})();
```

## `public/assets/js/smartvolt-rooms.js`

```javascript
(() => {
    'use strict';

    if (!window.SmartVolt) {
        return;
    }

    const updateTotals = () => {
        const powered = document.querySelectorAll('[data-device-toggle].is-on:not(.is-offline)').length;
        document.querySelectorAll('[data-powered-devices]').forEach((element) => {
            element.textContent = String(powered);
        });

        document.querySelectorAll('[data-room-card]').forEach((roomCard) => {
            const roomId = roomCard.dataset.roomCard;
            const activeInRoom = roomCard.querySelectorAll('[data-device-toggle].is-on:not(.is-offline)').length;
            document.querySelectorAll(`[data-room-active-count="${CSS.escape(roomId)}"]`).forEach((element) => {
                element.textContent = String(activeInRoom);
            });
        });
    };

    const setSwitchState = (button, state, online = true) => {
        button.dataset.currentState = state ? 'on' : 'off';
        button.classList.toggle('is-on', state);
        button.classList.toggle('is-offline', !online);
        button.setAttribute('aria-pressed', state ? 'true' : 'false');
        const label = button.querySelector('[data-switch-label]');
        if (label) {
            label.textContent = online ? (state ? 'Nyala' : 'Mati') : 'Offline';
        }
        button.disabled = !online;
    };

    document.addEventListener('click', async (event) => {
        const button = event.target.closest('[data-device-toggle]');
        if (!(button instanceof HTMLButtonElement) || button.disabled || button.classList.contains('is-loading')) {
            return;
        }

        const previousState = button.dataset.currentState === 'on';
        const label = button.querySelector('[data-switch-label]');
        button.disabled = true;
        button.classList.add('is-loading');
        if (label) {
            label.textContent = 'Mengirim';
        }

        try {
            const data = await window.SmartVolt.fetchJson(button.dataset.url, {method: 'POST'});
            const nextState = data.status === 'on' || data.status === true;
            setSwitchState(button, nextState, data.esp_online !== false);
            updateTotals();
            window.SmartVolt.toast(data.message || 'Perintah berhasil dikirim.', {type: 'success'});
        } catch (error) {
            setSwitchState(button, previousState, error.status !== 409);
            window.SmartVolt.toast(error.message || 'Perintah perangkat tidak berhasil.', {type: 'error'});
        } finally {
            button.classList.remove('is-loading');
            if (!button.classList.contains('is-offline')) {
                button.disabled = false;
            }
        }
    });
})();
```

## `install-smartvolt-ui-v8.ps1`

```powershell
param(
    [string]$ProjectRoot = "C:\SMARTVOLT"
)

$ErrorActionPreference = "Stop"
$ScriptRoot = Split-Path -Parent $MyInvocation.MyCommand.Path
$PayloadRoot = Join-Path $ScriptRoot "payload"

Write-Host ""
Write-Host "SmartVolt UI V8 - UX Refinement" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan

if (-not (Test-Path $PayloadRoot)) {
    throw "Folder payload tidak ditemukan: $PayloadRoot"
}

if (-not (Test-Path $ProjectRoot)) {
    throw "Folder proyek tidak ditemukan: $ProjectRoot"
}

$ArtisanPath = Join-Path $ProjectRoot "artisan"
if (-not (Test-Path $ArtisanPath)) {
    throw "Folder tujuan bukan proyek Laravel SmartVolt karena file artisan tidak ditemukan."
}

$Timestamp = Get-Date -Format "yyyyMMdd-HHmmss"
$BackupRoot = Join-Path $ProjectRoot "storage\ui-backups\v8-ux-refinement-$Timestamp"
New-Item -ItemType Directory -Path $BackupRoot -Force | Out-Null

$Files = Get-ChildItem -Path $PayloadRoot -File -Recurse

foreach ($File in $Files) {
    $RelativePath = $File.FullName.Substring($PayloadRoot.Length).TrimStart('\', '/')
    $TargetPath = Join-Path $ProjectRoot $RelativePath

    if (Test-Path $TargetPath) {
        $BackupPath = Join-Path $BackupRoot $RelativePath
        $BackupDirectory = Split-Path -Parent $BackupPath
        New-Item -ItemType Directory -Path $BackupDirectory -Force | Out-Null
        Copy-Item -Path $TargetPath -Destination $BackupPath -Force
    }

    $TargetDirectory = Split-Path -Parent $TargetPath
    New-Item -ItemType Directory -Path $TargetDirectory -Force | Out-Null
    Copy-Item -Path $File.FullName -Destination $TargetPath -Force

    Write-Host "[OK] $RelativePath" -ForegroundColor Green
}

Write-Host ""
Write-Host "File lama dicadangkan ke:" -ForegroundColor Yellow
Write-Host $BackupRoot

$PhpCommand = Get-Command php -ErrorAction SilentlyContinue
if ($PhpCommand) {
    Push-Location $ProjectRoot
    try {
        php artisan optimize:clear
    }
    finally {
        Pop-Location
    }
} else {
    Write-Host "PHP tidak ditemukan di PATH. Jalankan 'php artisan optimize:clear' secara manual." -ForegroundColor Yellow
}

Write-Host ""
Write-Host "Pemasangan SmartVolt UI V8 berhasil." -ForegroundColor Green
Write-Host "Buka website lalu tekan Ctrl + F5." -ForegroundColor Cyan
```

## `install-smartvolt-ui-v8.cmd`

```bat
@echo off
setlocal
cd /d "%~dp0"
powershell.exe -NoProfile -ExecutionPolicy Bypass -File "%~dp0install-smartvolt-ui-v8.ps1" -ProjectRoot "C:\SMARTVOLT"
echo.
pause
```
