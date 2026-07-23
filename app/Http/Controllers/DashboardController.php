<?php

namespace App\Http\Controllers;

use App\Models\Device;
use App\Models\EnergyDailySummary;
use App\Models\EnergyLog;
use App\Models\EnergyMeter;
use App\Models\Room;
use App\Models\SystemSetting;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    private const DEFAULT_TIMEZONE = 'Asia/Jakarta';
    private const DEFAULT_TARIFF = 1444;

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

    public function toggle(Request $request, $id)
    {
        $device = Device::query()
            ->where('id', $id)
            ->whereHas('room', function ($query) {
                $query->where('user_id', Auth::id());
            })
            ->first();

        if (! $device) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Perangkat tidak ditemukan.',
                ], 404);
            }

            return back()->withErrors([
                'device' => 'Perangkat tidak ditemukan.',
            ]);
        }

        $isCurrentlyOn = $this->isDeviceOn($device->status);
        $newStatus = $isCurrentlyOn ? 'off' : 'on';

        $device->update([
            'status' => $newStatus,
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => $newStatus === 'on'
                    ? 'Perangkat berhasil dinyalakan.'
                    : 'Perangkat berhasil dimatikan.',
                'status' => $newStatus,
                'label' => $newStatus === 'on'
                    ? 'Nyala'
                    : 'Mati',
            ]);
        }

        return back()->with(
            'success',
            $newStatus === 'on'
                ? 'Perangkat berhasil dinyalakan.'
                : 'Perangkat berhasil dimatikan.'
        );
    }

    private function getUserRooms()
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

    private function buildDashboardData($rooms = null): array
    {
        $rooms = $rooms ?? $this->getUserRooms();

        $devices = $rooms
            ->pluck('devices')
            ->flatten()
            ->values();

        /*
         * Setiap meter ruangan dihitung secara terpisah.
         * Ketika meter baru ditambahkan oleh teknisi, meter aktif tersebut
         * otomatis ikut masuk ke total estimasi bulanan di Beranda.
         */
        $meters = EnergyMeter::query()
            ->with('room')
            ->where('user_id', Auth::id())
            ->where('is_active', true)
            ->orderBy('id')
            ->get();

        $meterIds = $meters->pluck('id');

        $timezone = $this->applicationTimezone();
        $now = Carbon::now($timezone);
        $todayStart = $now->copy()->startOfDay();
        $monthStart = $now->copy()->startOfMonth();

        $todayLogs = $meterIds->isEmpty()
            ? collect()
            : EnergyLog::query()
                ->whereIn('energy_meter_id', $meterIds)
                ->whereBetween(
                    'observed_at',
                    [$todayStart, $now]
                )
                ->orderBy('observed_at')
                ->orderBy('id')
                ->get();

        /*
         * Daya saat ini adalah jumlah pembacaan terbaru dari seluruh
         * meter ruangan aktif.
         */
        $latestLogsPerMeter = $todayLogs
            ->groupBy('energy_meter_id')
            ->map(fn ($logs) => $logs->last())
            ->values();

        $currentPower = (float) $latestLogsPerMeter->sum(
            fn (EnergyLog $log) => (float) ($log->power ?? 0)
        );

        /*
         * Energi hari ini tetap dihitung dari raw log agar statistik
         * Beranda mengikuti data terbaru.
         */
        $totalEnergyToday = $this->calculateEnergyUsage(
            $meterIds,
            $todayStart,
            $now
        );

        /*
         * Total bulanan dihitung dari energy_daily_summaries.
         * Nilainya merupakan jumlah pemakaian semua meter ruangan aktif
         * pada bulan berjalan, lalu dikalikan tarif listrik user.
         */
        $electricityTariff = $this->getElectricityTariff();

        $monthlyEstimation = $this->buildMonthlyEstimation(
            $meters,
            $monthStart,
            $now,
            $electricityTariff
        );

        $chart = $this->buildPowerChart($todayLogs);

        return [
            'stats' => [
                'total_energy_today' => round(
                    $totalEnergyToday,
                    3
                ),
                'current_power' => round(
                    $currentPower,
                    1
                ),
                'monthly_energy_usage' => round(
                    $monthlyEstimation['usage_kwh'],
                    4
                ),
                'monthly_estimated_cost' => round(
                    $monthlyEstimation['estimated_cost']
                ),
                'electricity_tariff' => round(
                    $electricityTariff,
                    2
                ),
                'active_meters' => $meters->count(),
                'total_rooms' => $rooms->count(),
                'total_devices' => $devices->count(),
                'active_devices' => $devices
                    ->filter(function ($device) {
                        return $this->isDeviceOn(
                            $device->status ?? null
                        );
                    })
                    ->count(),
            ],

            /*
             * Data ini disediakan untuk kartu total di Beranda dan
             * dapat digunakan nanti untuk menampilkan rincian per meter.
             */
            'monthly_estimation' => $monthlyEstimation,

            'chart' => $chart,

            'rooms' => $rooms
                ->map(function ($room) {
                    return [
                        'id' => $room->id,
                        'name' => $room->name,
                        'total_devices' => $room
                            ->devices
                            ->count(),
                        'active_devices' => $room
                            ->devices
                            ->filter(function ($device) {
                                return $this->isDeviceOn(
                                    $device->status ?? null
                                );
                            })
                            ->count(),
                        'devices' => $room
                            ->devices
                            ->map(function ($device) {
                                return $this->devicePayload(
                                    $device
                                );
                            })
                            ->values(),
                    ];
                })
                ->values(),

            'devices' => $devices
                ->map(function ($device) {
                    return $this->devicePayload($device);
                })
                ->values(),

            'user' => [
                'name' => Auth::user()?->name ?? 'User',
                'email' => Auth::user()?->email ?? '',
            ],
        ];
    }

    /**
     * Menghitung estimasi tagihan bulan berjalan untuk seluruh meter.
     *
     * Contoh:
     * Meter Kamar  = 10 kWh
     * Meter Dapur  = 20 kWh
     * Total rumah  = 30 kWh x tarif listrik
     */
    private function buildMonthlyEstimation(
        $meters,
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

        /*
         * Satu query digunakan untuk menjumlahkan pemakaian per meter.
         * Ini lebih ringan daripada menghitung setiap meter dengan query
         * terpisah.
         */
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

        $meterBreakdown = $meters
            ->map(function (EnergyMeter $meter) use (
                $usageByMeter,
                $tariff
            ) {
                $usageKwh = max(
                    0,
                    (float) ($usageByMeter[$meter->id] ?? 0)
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

        $totalUsageKwh = (float) $meterBreakdown->sum(
            'usage_kwh'
        );

        return [
            'label' => 'Estimasi Tagihan Bulan Ini',
            'period' => $startDate->format('d/m/Y')
                . ' - '
                . $endDate->format('d/m/Y'),
            'usage_kwh' => round(
                max(0, $totalUsageKwh),
                4
            ),
            'tariff' => round($tariff, 2),
            'estimated_cost' => round(
                max(0, $totalUsageKwh * $tariff),
                2
            ),
            'meter_count' => $meters->count(),
            'meters' => $meterBreakdown,
        ];
    }

    private function buildPowerChart($todayLogs): array
    {
        $buckets = $todayLogs
            ->groupBy(function (EnergyLog $log) {
                /*
                 * observed_at sudah disimpan dalam waktu Asia/Jakarta.
                 * Tidak dikonversi lagi agar jam tidak bertambah 7 jam.
                 */
                return $log->observed_at
                    ?->format('H:i') ?? '-';
            })
            ->map(function ($logs, $label) {
                $latestPerMeter = $logs
                    ->groupBy('energy_meter_id')
                    ->map(
                        fn ($meterLogs) => $meterLogs->last()
                    )
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
            ->take(-12)
            ->values();

        return [
            'labels' => $buckets
                ->pluck('label')
                ->values(),
            'power' => $buckets
                ->pluck('power')
                ->values(),
            'energy' => $buckets
                ->pluck('energy')
                ->values(),
        ];
    }

    /**
     * Menghitung pemakaian energi dari raw log.
     *
     * Ketika nilai energy PZEM menurun, nilai baru tidak ditambahkan
     * sebagai pemakaian. Penurunan dapat terjadi karena reset PZEM,
     * data uji, atau pembacaan yang tidak valid.
     */
    private function calculateEnergyUsage(
        $meterIds,
        Carbon $startDate,
        Carbon $endDate
    ): float {
        if ($meterIds->isEmpty()) {
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
                ->whereBetween(
                    'observed_at',
                    [$startDate, $endDate]
                )
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

                /*
                 * Jika nilai menurun, pindahkan baseline tanpa
                 * menambahkan nilai baru sebagai pemakaian.
                 */
                $previousEnergy = $currentEnergy;
            }
        }

        return round(
            max(0, $totalUsage),
            6
        );
    }

    private function getElectricityTariff(): float
    {
        $systemSetting = SystemSetting::query()
            ->where('user_id', Auth::id())
            ->first();

        return max(
            0,
            (float) (
                $systemSetting?->electricity_tariff
                ?? self::DEFAULT_TARIFF
            )
        );
    }

    private function devicePayload($device): array
    {
        $status = $this->isDeviceOn(
            $device->status ?? null
        );

        return [
            'id' => $device->id,
            'room_id' => $device->room_id,
            'name' => $device->name,
            'type' => 'relay',
            'device_key' => $device->device_key ?? null,
            'relay_code' => $device->relay_code ?? null,
            'esp32_device_id' => $device
                ->esp32_device_id ?? null,
            'esp_unit_id' => $device->esp_unit_id ?? null,
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
