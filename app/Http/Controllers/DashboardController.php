<?php

namespace App\Http\Controllers;

use App\Models\Device;
use App\Models\EnergyLog;
use App\Models\EnergyMeter;
use App\Models\Room;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
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

        return response()->json($this->buildDashboardData($rooms));
    }

    public function toggle(Request $request, $id)
    {
        $device = Device::where('id', $id)
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
                'label' => $newStatus === 'on' ? 'Nyala' : 'Mati',
            ]);
        }

        return back()->with('success', $newStatus === 'on'
            ? 'Perangkat berhasil dinyalakan.'
            : 'Perangkat berhasil dimatikan.'
        );
    }

    private function getUserRooms()
    {
        return Room::with(['devices' => function ($query) {
            $query->orderBy('name');
        }])
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

        $meters = EnergyMeter::query()
            ->where('user_id', Auth::id())
            ->where('is_active', true)
            ->orderBy('id')
            ->get();

        $meterIds = $meters->pluck('id');

        $now = Carbon::now();
        $todayStart = $now->copy()->startOfDay();

        $todayLogs = $meterIds->isEmpty()
            ? collect()
            : EnergyLog::query()
                ->whereIn('energy_meter_id', $meterIds)
                ->whereBetween('observed_at', [$todayStart, $now])
                ->orderBy('observed_at')
                ->get();

        $latestLogsPerMeter = $todayLogs
            ->groupBy('energy_meter_id')
            ->map(fn ($logs) => $logs->last())
            ->values();

        $currentPower = (float) $latestLogsPerMeter->sum(
            fn (EnergyLog $log) => (float) ($log->power ?? 0)
        );

        $totalEnergyToday = $this->calculateEnergyUsage(
            $meterIds,
            $todayStart,
            $now
        );

        $chart = $this->buildPowerChart($todayLogs);

        return [
            'stats' => [
                'total_energy_today' => round($totalEnergyToday, 3),
                'current_power' => round($currentPower, 0),
                'total_rooms' => $rooms->count(),
                'total_devices' => $devices->count(),
                'active_devices' => $devices->filter(function ($device) {
                    return $this->isDeviceOn($device->status ?? null);
                })->count(),
            ],

            'chart' => $chart,

            'rooms' => $rooms->map(function ($room) {
                return [
                    'id' => $room->id,
                    'name' => $room->name,
                    'total_devices' => $room->devices->count(),
                    'active_devices' => $room->devices
                        ->filter(function ($device) {
                            return $this->isDeviceOn($device->status ?? null);
                        })
                        ->count(),
                    'devices' => $room->devices
                        ->map(function ($device) {
                            return $this->devicePayload($device);
                        })
                        ->values(),
                ];
            })->values(),

            'devices' => $devices->map(function ($device) {
                return $this->devicePayload($device);
            })->values(),

            'user' => [
                'name' => Auth::user()?->name ?? 'User',
                'email' => Auth::user()?->email ?? '',
            ],
        ];
    }

    private function buildPowerChart($todayLogs): array
    {
        $buckets = $todayLogs
            ->groupBy(function (EnergyLog $log) {
                return $log->observed_at
                    ?->timezone('Asia/Jakarta')
                    ->format('H:i') ?? '-';
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
            ->take(-12)
            ->values();

        return [
            'labels' => $buckets->pluck('label')->values(),
            'power' => $buckets->pluck('power')->values(),
            'energy' => $buckets->pluck('energy')->values(),
        ];
    }

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
                ->first();

            $logs = EnergyLog::query()
                ->where('energy_meter_id', $meterId)
                ->whereBetween('observed_at', [$startDate, $endDate])
                ->whereNotNull('energy')
                ->orderBy('observed_at')
                ->get(['energy', 'observed_at']);

            $previousEnergy = $previousLog
                ? (float) $previousLog->energy
                : null;

            foreach ($logs as $log) {
                $currentEnergy = (float) $log->energy;

                if ($previousEnergy === null) {
                    $previousEnergy = $currentEnergy;
                    continue;
                }

                $totalUsage += $currentEnergy >= $previousEnergy
                    ? $currentEnergy - $previousEnergy
                    : max(0, $currentEnergy);

                $previousEnergy = $currentEnergy;
            }
        }

        return round($totalUsage, 4);
    }

    private function devicePayload($device): array
    {
        $status = $this->isDeviceOn($device->status ?? null);

        return [
            'id' => $device->id,
            'room_id' => $device->room_id,
            'name' => $device->name,
            'type' => 'relay',
            'device_key' => $device->device_key ?? null,
            'relay_code' => $device->relay_code ?? null,
            'esp32_device_id' => $device->esp32_device_id ?? null,
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

        return in_array(strtolower((string) $status), [
            'on',
            'nyala',
            'active',
            'aktif',
            'true',
            '1',
        ], true);
    }
}