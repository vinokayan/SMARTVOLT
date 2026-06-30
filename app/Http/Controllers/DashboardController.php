<?php

namespace App\Http\Controllers;

use App\Models\Room;
use App\Models\Device;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;

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

        $deviceIds = $devices
            ->pluck('id')
            ->filter()
            ->values();

        $totalEnergyToday = 0;
        $currentPower = 0;
        $chartLabels = [];
        $chartPower = [];
        $chartEnergy = [];

        if (Schema::hasTable('energy_logs')) {
            $energyColumn = $this->firstExistingColumn('energy_logs', [
                'energy_kwh',
                'kwh',
                'energy',
                'total_kwh',
            ]);

            $powerColumn = $this->firstExistingColumn('energy_logs', [
                'power_watt',
                'current_power',
                'power',
                'watt',
            ]);

            $dateColumn = $this->firstExistingColumn('energy_logs', [
                'logged_at',
                'created_at',
            ]);

            $deviceColumn = Schema::hasColumn('energy_logs', 'device_id')
                ? 'device_id'
                : null;

            $logsQuery = DB::table('energy_logs');

            if ($deviceColumn && $deviceIds->isNotEmpty()) {
                $logsQuery->whereIn($deviceColumn, $deviceIds);
            }

            if ($dateColumn) {
                $logsQuery->whereDate($dateColumn, now()->toDateString());
            }

            $todayLogs = $logsQuery
                ->orderBy($dateColumn ?? 'id')
                ->get();

            if ($todayLogs->isNotEmpty()) {
                if ($deviceColumn) {
                    $latestLogsPerDevice = $todayLogs
                        ->groupBy($deviceColumn)
                        ->map(function ($logs) {
                            return $logs->last();
                        })
                        ->values();
                } else {
                    $latestLogsPerDevice = collect([$todayLogs->last()]);
                }

                if ($powerColumn) {
                    $currentPower = (float) $latestLogsPerDevice->sum(function ($log) use ($powerColumn) {
                        return (float) ($log->{$powerColumn} ?? 0);
                    });
                }

                if ($energyColumn) {
                    $totalEnergyToday = (float) $latestLogsPerDevice->sum(function ($log) use ($energyColumn) {
                        return (float) ($log->{$energyColumn} ?? 0);
                    });
                }

                $chartLogs = $todayLogs->take(-12)->values();

                foreach ($chartLogs as $log) {
                    if ($dateColumn && isset($log->{$dateColumn})) {
                        $chartLabels[] = Carbon::parse($log->{$dateColumn})->format('H:i');
                    } else {
                        $chartLabels[] = '#' . ($log->id ?? count($chartLabels) + 1);
                    }

                    $chartPower[] = $powerColumn
                        ? round((float) ($log->{$powerColumn} ?? 0), 2)
                        : 0;

                    $chartEnergy[] = $energyColumn
                        ? round((float) ($log->{$energyColumn} ?? 0), 4)
                        : 0;
                }
            }
        }

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

            'chart' => [
                'labels' => $chartLabels,
                'power' => $chartPower,
                'energy' => $chartEnergy,
            ],

            'rooms' => $rooms->map(function ($room) {
                return [
                    'id' => $room->id,
                    'name' => $room->name,
                    'total_devices' => $room->devices->count(),
                    'active_devices' => $room->devices->filter(function ($device) {
                        return $this->isDeviceOn($device->status ?? null);
                    })->count(),

                    'devices' => $room->devices->map(function ($device) {
                        $status = $this->isDeviceOn($device->status ?? null);

                        return [
                            'id' => $device->id,
                            'room_id' => $device->room_id,
                            'name' => $device->name,

                            'type' => $device->type ?? null,

                            'device_key' => $device->device_key ?? null,
                            'relay_code' => $device->relay_code ?? null,
                            'esp32_device_id' => $device->esp32_device_id ?? null,
                            'esp_unit_id' => $device->esp_unit_id ?? null,

                            'status' => $status,
                            'status_text' => $status ? 'on' : 'off',
                            'status_label' => $status ? 'Nyala' : 'Mati',
                        ];
                    })->values(),
                ];
            })->values(),

            'devices' => $devices->map(function ($device) {
                $status = $this->isDeviceOn($device->status ?? null);

                return [
                    'id' => $device->id,
                    'room_id' => $device->room_id,
                    'name' => $device->name,

                    'type' => $device->type ?? null,

                    'device_key' => $device->device_key ?? null,
                    'relay_code' => $device->relay_code ?? null,
                    'esp32_device_id' => $device->esp32_device_id ?? null,
                    'esp_unit_id' => $device->esp_unit_id ?? null,

                    'status' => $status,
                    'status_text' => $status ? 'on' : 'off',
                    'status_label' => $status ? 'Nyala' : 'Mati',
                ];
            })->values(),

            'user' => [
                'name' => Auth::user()?->name ?? 'User',
                'email' => Auth::user()?->email ?? '',
            ],
        ];
    }

    private function firstExistingColumn(string $table, array $columns): ?string
    {
        foreach ($columns as $column) {
            if (Schema::hasColumn($table, $column)) {
                return $column;
            }
        }

        return null;
    }

    private function isDeviceOn($status): bool
    {
        if (is_bool($status)) {
            return $status;
        }

        if (is_numeric($status)) {
            return (int) $status === 1;
        }

        $status = strtolower((string) $status);

        return in_array($status, [
            'on',
            'nyala',
            'active',
            'aktif',
            'true',
            '1',
        ], true);
    }
}