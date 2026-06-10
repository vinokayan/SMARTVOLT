<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DashboardController extends Controller
{
    public function index()
    {
        return view('dashboard', [
            'dashboardData' => $this->buildDashboardData(),
        ]);
    }

    public function data()
    {
        return response()->json($this->buildDashboardData());
    }

    public function toggle(Request $request, $id)
    {
        if (!$this->tableExists('devices')) {
            return response()->json([
                'success' => false,
                'message' => 'Tabel devices tidak ditemukan.',
            ], 422);
        }

        $statusColumn = $this->firstExistingColumn('devices', ['status', 'is_active', 'state']);

        if (!$statusColumn) {
            return response()->json([
                'success' => false,
                'message' => 'Kolom status device tidak ditemukan.',
            ], 422);
        }

        $device = DB::table('devices')->where('id', $id)->first();

        if (!$device) {
            return response()->json([
                'success' => false,
                'message' => 'Device tidak ditemukan.',
            ], 404);
        }

        $currentValue = $device->{$statusColumn};
        $newValue = $this->toggleStatusValue($currentValue);

        $updateData = [
            $statusColumn => $newValue,
        ];

        if ($this->columnExists('devices', 'updated_at')) {
            $updateData['updated_at'] = now();
        }

        DB::table('devices')
            ->where('id', $id)
            ->update($updateData);

        return response()->json([
            'success' => true,
            'message' => 'Status device berhasil diubah.',
            'status' => $this->normalizeStatus($newValue),
        ]);
    }

    private function buildDashboardData(): array
{
    $userId = Auth::id();

    $totalEnergyToday = 0;
    $currentPower = 0;
    $chartLabels = [];
    $chartPower = [];
    $chartEnergy = [];

    if ($this->tableExists('energy_logs')) {
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

        $deviceColumn = $this->columnExists('energy_logs', 'device_id') ? 'device_id' : null;

        $logsQuery = DB::table('energy_logs');

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
                    $chartLabels[] = \Carbon\Carbon::parse($log->{$dateColumn})->format('H:i');
                } else {
                    $chartLabels[] = '#' . ($log->id ?? count($chartLabels) + 1);
                }

                $chartPower[] = $powerColumn ? round((float) ($log->{$powerColumn} ?? 0), 2) : 0;
                $chartEnergy[] = $energyColumn ? round((float) ($log->{$energyColumn} ?? 0), 4) : 0;
            }
        }
    }

    $rooms = collect();

    if ($this->tableExists('rooms')) {
        $roomsQuery = DB::table('rooms as r');

        if ($userId && $this->columnExists('rooms', 'user_id')) {
            $roomsQuery->where('r.user_id', $userId);
        }

        if ($this->tableExists('devices') && $this->columnExists('devices', 'room_id')) {
            $roomsQuery
                ->leftJoin('devices as d', 'd.room_id', '=', 'r.id')
                ->select(
                    'r.id',
                    DB::raw("COALESCE(r.name, CONCAT('Room ', r.id)) as name"),
                    DB::raw('COUNT(d.id) as total_devices')
                )
                ->groupBy('r.id', 'r.name')
                ->orderBy('r.id');
        } else {
            $roomsQuery
                ->select(
                    'r.id',
                    DB::raw("COALESCE(r.name, CONCAT('Room ', r.id)) as name"),
                    DB::raw('0 as total_devices')
                )
                ->orderBy('r.id');
        }

        $rooms = $roomsQuery->get()->map(function ($room) {
            return [
                'id' => $room->id,
                'name' => $room->name ?? 'Unnamed Room',
                'total_devices' => (int) ($room->total_devices ?? 0),
            ];
        });
    }

    $devices = collect();

    if ($this->tableExists('devices')) {
        $devicesQuery = DB::table('devices as d');
        $selects = ['d.id'];

        if ($this->columnExists('devices', 'name')) {
            $selects[] = 'd.name';
        }

        if ($this->columnExists('devices', 'type')) {
            $selects[] = 'd.type';
        }

        $statusColumn = $this->firstExistingColumn('devices', ['status', 'is_active', 'state']);

        if ($statusColumn) {
            $selects[] = "d.$statusColumn as status_value";
        }

        if (
            $this->tableExists('rooms') &&
            $this->columnExists('devices', 'room_id') &&
            $this->columnExists('rooms', 'name')
        ) {
            $devicesQuery->leftJoin('rooms as r', 'r.id', '=', 'd.room_id');
            $selects[] = 'r.name as room_name';

            if ($userId && $this->columnExists('rooms', 'user_id')) {
                $devicesQuery->where('r.user_id', $userId);
            }
        }

        $devices = $devicesQuery
            ->selectRaw(implode(', ', $selects))
            ->orderBy('d.id')
            ->get()
            ->map(function ($device) {
                $rawStatus = $device->status_value ?? 'off';

                return [
                    'id' => $device->id,
                    'name' => $device->name ?? 'Unnamed Device',
                    'type' => $device->type ?? ($device->name ?? 'device'),
                    'room_name' => $device->room_name ?? 'Tanpa Room',
                    'status' => $this->normalizeStatus($rawStatus),
                ];
            });
    }

    return [
        'stats' => [
            'total_energy_today' => round($totalEnergyToday, 3),
            'current_power' => round($currentPower, 0),
            'total_rooms' => $rooms->count(),
            'total_devices' => $devices->count(),
            'active_devices' => $devices->where('status', 'on')->count(),
        ],
        'chart' => [
            'labels' => $chartLabels,
            'power' => $chartPower,
            'energy' => $chartEnergy,
        ],
        'rooms' => $rooms->values(),
        'devices' => $devices->values(),
        'user' => [
            'name' => Auth::user()?->name ?? 'User',
            'email' => Auth::user()?->email ?? '',
        ],
    ];
}

    private function normalizeStatus($value): string
    {
        $normalized = strtolower((string) $value);

        if (in_array($normalized, ['1', 'true', 'on', 'aktif'], true)) {
            return 'on';
        }

        return 'off';
    }

    private function toggleStatusValue($value)
    {
        if (is_numeric($value)) {
            return ((int) $value === 1) ? 0 : 1;
        }

        $normalized = strtolower((string) $value);

        return in_array($normalized, ['on', '1', 'true', 'aktif'], true) ? 'off' : 'on';
    }

    private function tableExists(string $table): bool
    {
        return Schema::hasTable($table);
    }

    private function columnExists(string $table, string $column): bool
    {
        return Schema::hasColumn($table, $column);
    }

    private function firstExistingColumn(string $table, array $columns): ?string
    {
        foreach ($columns as $column) {
            if ($this->columnExists($table, $column)) {
                return $column;
            }
        }

        return null;
    }
}