<?php

namespace App\Http\Controllers;

use App\Models\Device;
use App\Models\EnergyLog;
use App\Models\SystemSetting;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnergyController extends Controller
{
    public function index(Request $request)
    {
        $filters = [
            'device_id' => $request->input('device_id'),
            'date_from' => $request->input('date_from'),
            'date_to' => $request->input('date_to'),
        ];

        $query = $this->energyLogQuery($filters);

        $logs = (clone $query)->latest()->paginate(20)->appends($request->query());

        $logs->getCollection()->transform(function ($log) {
            $log->room_name = $log->device?->room?->name ?? '-';
            $log->device_name = $log->device?->name ?? '-';
            return $log;
        });

        $devices = Device::with('room')
            ->whereHas('room', fn ($roomQuery) => $roomQuery->where('user_id', Auth::id()))
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

        $chartLogs = (clone $query)->latest()->take(10)->get()->reverse()->values();

        $chart = [
            'labels' => $chartLogs->map(fn ($log) => optional($log->created_at)->format('H:i')),
            'power' => $chartLogs->map(fn ($log) => round($log->power ?? 0, 2)),
            'energy' => $chartLogs->map(fn ($log) => round($log->energy ?? 0, 4)),
        ];

        $electricityTariff = $this->getElectricityTariff();
        $paymentEstimations = $this->buildPaymentEstimations($devices->pluck('id'), $electricityTariff);

        return view('auth.energy-history', compact(
            'logs',
            'devices',
            'summary',
            'chart',
            'filters',
            'paymentEstimations',
            'electricityTariff'
        ));
    }

   public function export(Request $request)
{
    $filters = [
        'device_id' => $request->input('device_id'),
        'date_from' => $request->input('date_from'),
        'date_to' => $request->input('date_to'),
    ];

    $query = $this->energyLogQuery($filters);
    $electricityTariff = $this->getElectricityTariff();
    $deviceIds = $this->getExportDeviceIds($filters);
    $paymentEstimations = $this->buildPaymentEstimations($deviceIds, $electricityTariff);

    $devices = (clone $query)
        ->latest()
        ->get()
        ->map(function ($log) {
            return [
                'ruangan' => $log->device?->room?->name ?? '-',
                'perangkat' => $log->device?->name ?? '-',
                'waktu' => optional($log->updated_at)->timezone('Asia/Jakarta')->format('d/m/Y H:i:s'),
                'tegangan' => number_format($log->voltage ?? 0, 2, '.', ''),
                'arus' => number_format($log->current ?? 0, 2, '.', ''),
                'daya' => number_format($log->power ?? 0, 2, '.', ''),
                'energi' => number_format($log->energy_kwh ?? $log->energy ?? 0, 4, '.', ''),
            ];
        })
        ->values();

    return response()->json([
        'generated_at' => Carbon::now('Asia/Jakarta')->format('d/m/Y H:i:s'),
        'devices' => $devices,
        'summary' => [
            [
                'label' => 'Energi (kWh)',
                'hari_ini' => number_format($paymentEstimations['today']['usage_kwh'], 4, '.', ''),
                'minggu_ini' => number_format($paymentEstimations['week']['usage_kwh'], 4, '.', ''),
                'bulan_ini' => number_format($paymentEstimations['month']['usage_kwh'], 4, '.', ''),
            ],
            [
                'label' => 'Tarif / kWh',
                'hari_ini' => 'Rp' . number_format($electricityTariff, 0, ',', '.'),
                'minggu_ini' => 'Rp' . number_format($electricityTariff, 0, ',', '.'),
                'bulan_ini' => 'Rp' . number_format($electricityTariff, 0, ',', '.'),
            ],
            [
                'label' => 'Estimasi Pembayaran',
                'hari_ini' => 'Rp' . number_format($paymentEstimations['today']['estimated_cost'], 0, ',', '.'),
                'minggu_ini' => 'Rp' . number_format($paymentEstimations['week']['estimated_cost'], 0, ',', '.'),
                'bulan_ini' => 'Rp' . number_format($paymentEstimations['month']['estimated_cost'], 0, ',', '.'),
            ],
        ],
    ]);
}

    private function energyLogQuery(array $filters)
    {
        $query = EnergyLog::with('device.room')
            ->whereHas('device.room', fn ($roomQuery) => $roomQuery->where('user_id', Auth::id()));

        if (!empty($filters['device_id'])) {
            $query->where('device_id', $filters['device_id']);
        }

        if (!empty($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }

        return $query;
    }

    private function getElectricityTariff(): float
    {
        $systemSetting = SystemSetting::where('user_id', Auth::id())->first();
        return (float) ($systemSetting?->electricity_tariff ?? 1444);
    }

    private function getExportDeviceIds(array $filters)
    {
        $devicesQuery = Device::whereHas('room', fn ($roomQuery) => $roomQuery->where('user_id', Auth::id()));

        if (!empty($filters['device_id'])) {
            $devicesQuery->where('id', $filters['device_id']);
        }

        return $devicesQuery->pluck('id');
    }

   private function buildPaymentEstimations($deviceIds, float $tariff): array
{
    $now = Carbon::now('Asia/Jakarta');

    return [
        'today' => $this->buildPaymentEstimation(
            'Hari Ini',
            Carbon::today('Asia/Jakarta'),
            $now->copy(),
            $deviceIds,
            $tariff
        ),
        'week' => $this->buildPaymentEstimation(
            'Minggu Ini',
            $now->copy()->startOfWeek(),
            $now->copy(),
            $deviceIds,
            $tariff
        ),
        'month' => $this->buildPaymentEstimation(
            'Bulan Ini',
            $now->copy()->startOfMonth(),
            $now->copy(),
            $deviceIds,
            $tariff
        ),
    ];
}

private function buildPaymentEstimation(
    string $label,
    Carbon $startDate,
    Carbon $endDate,
    $deviceIds,
    float $tariff
): array {
    $usageKwh = $this->calculateEnergyUsage($deviceIds, $startDate, $endDate);
    $estimatedCost = $usageKwh * $tariff;

    return [
        'label' => $label,
        'period' => $startDate->format('d/m/Y') . ' - ' . $endDate->format('d/m/Y'),
        'usage_kwh' => round($usageKwh, 4),
        'tariff' => round($tariff, 2),
        'estimated_cost' => round($estimatedCost),
    ];
}

    private function calculateEnergyUsage($deviceIds, Carbon $startDate, Carbon $endDate): float
    {
        if ($deviceIds->isEmpty()) {
            return 0;
        }

        return (float) EnergyLog::whereIn('device_id', $deviceIds)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->sum('energy');
    }
}