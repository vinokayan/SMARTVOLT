<?php

namespace App\Http\Controllers;

use App\Models\Device;
use App\Models\EnergyLog;
use Illuminate\Http\Request;

class EnergyController extends Controller
{
    public function index(Request $request)
    {
        $filters = [
            'device_id' => $request->input('device_id'),
            'date_from' => $request->input('date_from'),
            'date_to' => $request->input('date_to'),
        ];

        $query = EnergyLog::with('device.room');

        if (!empty($filters['device_id'])) {
            $query->where('device_id', $filters['device_id']);
        }

        if (!empty($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }

        $logs = (clone $query)
            ->latest()
            ->paginate(20)
            ->appends($request->query());

        $devices = Device::with('room')
            ->orderBy('name')
            ->get();

        $summary = [
            'total_logs' => (clone $query)->count(),
            'max_power' => (clone $query)->max('power') ?? 0,
            'avg_power' => round((clone $query)->avg('power') ?? 0, 2),
            'avg_voltage' => round((clone $query)->avg('voltage') ?? 0, 2),
            'usage_kwh' => round((clone $query)->max('energy') ?? 0, 4),
            'latest_time' => optional((clone $query)->latest()->first())->created_at?->format('d/m/Y H:i:s'),
        ];

        $chartLogs = (clone $query)
            ->latest()
            ->take(10)
            ->get()
            ->reverse()
            ->values();

        $chart = [
            'labels' => $chartLogs->map(fn ($log) => $log->created_at->format('H:i')),
            'power' => $chartLogs->map(fn ($log) => round($log->power, 2)),
            'energy' => $chartLogs->map(fn ($log) => round($log->energy, 4)),
        ];

        return view('auth.energy-history', compact(
            'logs',
            'devices',
            'summary',
            'chart',
            'filters'
        ));
    }

    public function export(Request $request)
    {
        $filters = [
            'device_id' => $request->input('device_id'),
            'date_from' => $request->input('date_from'),
            'date_to' => $request->input('date_to'),
        ];

        $query = EnergyLog::with('device.room');

        if (!empty($filters['device_id'])) {
            $query->where('device_id', $filters['device_id']);
        }

        if (!empty($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }

        $logs = $query
            ->latest()
            ->get()
            ->map(function ($log) {
                return [
                    'id' => $log->id,
                    'device_id' => $log->device_id,
                    'device_name' => $log->device?->name ?? '-',
                    'room_name' => $log->device?->room?->name ?? '-',
                    'voltage' => $log->voltage ?? 0,
                    'current' => $log->current ?? 0,
                    'power' => $log->power ?? 0,
                    'energy' => $log->energy ?? 0,
                    'created_at' => $log->created_at?->format('d/m/Y H:i:s'),
                ];
            });

        return response()->json($logs);
    }
}