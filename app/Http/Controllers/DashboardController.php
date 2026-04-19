<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $dashboardData = $this->buildDashboardData();

        return view('dashboard', [
            'dashboardData' => $dashboardData,
        ]);
    }

    public function data()
    {
        return response()->json($this->buildDashboardData());
    }

    public function toggle(Request $request, $id)
    {
        $statusColumn = collect(['status', 'is_active', 'state'])
            ->first(fn ($column) => Schema::hasColumn('devices', $column));

        if (!$statusColumn) {
            return response()->json([
                'success' => false,
                'message' => 'Kolom status device tidak ditemukan.'
            ], 422);
        }

        $device = DB::table('devices')->where('id', $id)->first();

        if (!$device) {
            return response()->json([
                'success' => false,
                'message' => 'Device tidak ditemukan.'
            ], 404);
        }

        $currentValue = $device->{$statusColumn};

        if (is_numeric($currentValue)) {
            $newValue = ((int) $currentValue === 1) ? 0 : 1;
        } else {
            $normalized = strtolower((string) $currentValue);
            $newValue = in_array($normalized, ['on', '1', 'true', 'aktif']) ? 'off' : 'on';
        }

        $updateData = [$statusColumn => $newValue];

        if (Schema::hasColumn('devices', 'updated_at')) {
            $updateData['updated_at'] = now();
        }

        DB::table('devices')
            ->where('id', $id)
            ->update($updateData);

        return response()->json([
            'success' => true,
            'message' => 'Status device berhasil diubah.'
        ]);
    }

    private function buildDashboardData(): array
    {
        $energyColumn = collect(['energy_kwh', 'kwh', 'energy', 'total_kwh'])
            ->first(fn ($column) => Schema::hasColumn('energy_logs', $column));

        $powerColumn = collect(['power', 'watt', 'current_power'])
            ->first(fn ($column) => Schema::hasColumn('energy_logs', $column));

        $createdAtExists = Schema::hasColumn('energy_logs', 'created_at');
        $energyLogsIdExists = Schema::hasColumn('energy_logs', 'id');

        $totalEnergyToday = 0;
        $currentPower = 0;

        if ($energyColumn) {
            $energyQuery = DB::table('energy_logs');

            if ($createdAtExists) {
                $energyQuery->whereDate('created_at', now()->toDateString());
            }

            $totalEnergyToday = (float) $energyQuery->sum($energyColumn);
        }

        if ($powerColumn) {
            $powerQuery = DB::table('energy_logs');

            if ($createdAtExists) {
                $powerQuery->orderByDesc('created_at');
            } elseif ($energyLogsIdExists) {
                $powerQuery->orderByDesc('id');
            }

            $currentPower = (float) ($powerQuery->value($powerColumn) ?? 0);
        }

        $rooms = collect();
        if (Schema::hasTable('rooms')) {
            $roomsQuery = DB::table('rooms as r');

            if (Schema::hasTable('devices') && Schema::hasColumn('devices', 'room_id')) {
                $roomsQuery->leftJoin('devices as d', 'd.room_id', '=', 'r.id')
                    ->select(
                        'r.id',
                        DB::raw("COALESCE(r.name, CONCAT('Room ', r.id)) as name"),
                        DB::raw('COUNT(d.id) as total_devices')
                    )
                    ->groupBy('r.id', 'r.name')
                    ->orderBy('r.id');
            } else {
                $roomsQuery->select(
                    'r.id',
                    DB::raw("COALESCE(r.name, CONCAT('Room ', r.id)) as name"),
                    DB::raw('0 as total_devices')
                )->orderBy('r.id');
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
        if (Schema::hasTable('devices')) {
            $devicesQuery = DB::table('devices as d');

            $selects = [
                'd.id',
            ];

            if (Schema::hasColumn('devices', 'name')) {
                $selects[] = 'd.name';
            }

            if (Schema::hasColumn('devices', 'type')) {
                $selects[] = 'd.type';
            }

            $statusColumn = collect(['status', 'is_active', 'state'])
                ->first(fn ($column) => Schema::hasColumn('devices', $column));

            if ($statusColumn) {
                $selects[] = "d.$statusColumn as status_value";
            }

            if (
                Schema::hasTable('rooms') &&
                Schema::hasColumn('devices', 'room_id') &&
                Schema::hasColumn('rooms', 'name')
            ) {
                $devicesQuery->leftJoin('rooms as r', 'r.id', '=', 'd.room_id');
                $selects[] = 'r.name as room_name';
            }

            $devices = $devicesQuery
                ->selectRaw(implode(', ', $selects))
                ->orderBy('d.id')
                ->get()
                ->map(function ($device) {
                    $rawStatus = $device->status_value ?? 'off';
                    $normalizedStatus = $this->normalizeStatus($rawStatus);

                    return [
                        'id' => $device->id,
                        'name' => $device->name ?? 'Unnamed Device',
                        'type' => $device->type ?? ($device->name ?? 'device'),
                        'room_name' => $device->room_name ?? 'Tanpa Room',
                        'status' => $normalizedStatus,
                    ];
                });
        }

        return [
            'stats' => [
                'total_energy_today' => round($totalEnergyToday, 2),
                'current_power' => round($currentPower, 0),
                'total_rooms' => $rooms->count(),
                'total_devices' => $devices->count(),
                'active_devices' => $devices->where('status', 'on')->count(),
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

        if (in_array($normalized, ['1', 'true', 'on', 'aktif'])) {
            return 'on';
        }

        return 'off';
    }
}