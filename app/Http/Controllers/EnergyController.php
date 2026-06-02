<?php

namespace App\Http\Controllers;

use App\Models\EnergyLog;
use App\Models\Device;
use Illuminate\Http\Request;

class EnergyController extends Controller
{
    public function index(Request $request)
    {
        $logs = EnergyLog::latest()
            ->paginate(20);

        $devices = Device::with('room')->get();

        $summary = [
            'total_logs' => EnergyLog::count(),
            'max_power' => EnergyLog::max('power') ?? 0,
            'avg_power' => round(EnergyLog::avg('power') ?? 0, 2),
            'avg_voltage' => round(EnergyLog::avg('voltage') ?? 0, 2),
            'usage_kwh' => round(EnergyLog::max('energy') ?? 0, 4),
            'latest_time' => optional(EnergyLog::latest()->first())->created_at?->format('d/m/Y H:i:s'),
        ];

        $chartLogs = EnergyLog::latest()
            ->take(10)
            ->get()
            ->reverse()
            ->values();

        $chart = [
            'labels' => $chartLogs->map(fn ($log) => $log->created_at->format('H:i')),
            'power' => $chartLogs->map(fn ($log) => $log->power),
            'energy' => $chartLogs->map(fn ($log) => $log->energy),
        ];

        $filters = [
            'device_id' => null,
            'date_from' => null,
            'date_to' => null,
        ];

       return view('auth.energy-history', compact(
    'logs',
    'devices',
    'summary',
    'chart',
    'filters'
));
    }

    public function exportData(Request $request)
    {
        $logs = EnergyLog::latest()->get()->map(function ($log) {
            return [
                'Waktu' => optional($log->created_at)->format('d/m/Y H:i:s'),
                'Voltage (V)' => number_format($log->voltage ?? 0, 2),
                'Current (A)' => number_format($log->current ?? 0, 2),
                'Power (W)' => number_format($log->power ?? 0, 2),
                'Energy (kWh)' => number_format($log->energy_kwh ?? $log->energy ?? 0, 4),
            ];
        });

        return response()->json($logs);
    }
}