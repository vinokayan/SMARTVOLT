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

        $logs = (clone $query)
            ->latest()
            ->paginate(20)
            ->appends($request->query());

        $logs->getCollection()->transform(function ($log) {
            $log->room_name = $log->device?->room?->name ?? '-';
            $log->device_name = $log->device?->name ?? '-';

            return $log;
        });

        $devices = Device::with('room')
            ->whereHas('room', function ($roomQuery) {
                $roomQuery->where('user_id', Auth::id());
            })
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

        $exportLogs = (clone $query)
            ->latest()
            ->get();

        $exportUsageKwh = (float) $exportLogs->sum(function ($log) {
            return $this->getLogEnergyKwh($log);
        });

        $exportEstimatedCost = $exportUsageKwh * $electricityTariff;

        $summaryRows = collect($paymentEstimations)
            ->map(function ($estimation) {
                return [
                    'Room' => 'Ringkasan Pemakaian',
                    'Device' => $estimation['label'],
                    'Waktu' => $estimation['period'],
                    'Voltage (V)' => '-',
                    'Current (A)' => '-',
                    'Power (W)' => '-',
                    'Energy (kWh)' => number_format($estimation['usage_kwh'], 4, ',', '.'),
                    'Tarif per kWh' => 'Rp ' . number_format($estimation['tariff'], 0, ',', '.'),
                    'Estimasi Pembayaran' => 'Rp ' . number_format($estimation['estimated_cost'], 0, ',', '.'),
                ];
            })
            ->push([
                'Room' => 'TOTAL',
                'Device' => 'Total Data yang Diekspor',
                'Waktu' => Carbon::now()->format('d/m/Y H:i:s'),
                'Voltage (V)' => '-',
                'Current (A)' => '-',
                'Power (W)' => '-',
                'Energy (kWh)' => number_format($exportUsageKwh, 4, ',', '.'),
                'Tarif per kWh' => 'Rp ' . number_format($electricityTariff, 0, ',', '.'),
                'Estimasi Pembayaran' => 'Rp ' . number_format($exportEstimatedCost, 0, ',', '.'),
            ]);

        $separatorRow = collect([
            [
                'Room' => '-',
                'Device' => '-',
                'Waktu' => '-',
                'Voltage (V)' => '-',
                'Current (A)' => '-',
                'Power (W)' => '-',
                'Energy (kWh)' => '-',
                'Tarif per kWh' => '-',
                'Estimasi Pembayaran' => '-',
            ],
        ]);

        $logRows = $exportLogs->map(function ($log) use ($electricityTariff) {
            $energyKwh = $this->getLogEnergyKwh($log);
            $estimatedCost = $energyKwh * $electricityTariff;

            return [
                'Room' => $log->device?->room?->name ?? '-',
                'Device' => $log->device?->name ?? '-',
                'Waktu' => optional($log->created_at)->format('d/m/Y H:i:s'),
                'Voltage (V)' => number_format($log->voltage ?? 0, 2, ',', '.'),
                'Current (A)' => number_format($log->current ?? 0, 2, ',', '.'),
                'Power (W)' => number_format($log->power ?? 0, 2, ',', '.'),
                'Energy (kWh)' => number_format($energyKwh, 4, ',', '.'),
                'Tarif per kWh' => 'Rp ' . number_format($electricityTariff, 0, ',', '.'),
                'Estimasi Pembayaran' => 'Rp ' . number_format($estimatedCost, 0, ',', '.'),
            ];
        });

        return response()->json(
            $summaryRows
                ->concat($separatorRow)
                ->concat($logRows)
                ->values()
        );
    }

    private function energyLogQuery(array $filters)
    {
        $query = EnergyLog::with('device.room')
            ->whereHas('device.room', function ($roomQuery) {
                $roomQuery->where('user_id', Auth::id());
            });

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
        $devicesQuery = Device::whereHas('room', function ($roomQuery) {
            $roomQuery->where('user_id', Auth::id());
        });

        if (!empty($filters['device_id'])) {
            $devicesQuery->where('id', $filters['device_id']);
        }

        return $devicesQuery->pluck('id');
    }

    private function buildPaymentEstimations($deviceIds, float $tariff): array
    {
        $now = Carbon::now();

        return [
            'today' => $this->buildPaymentEstimation(
                label: 'Hari Ini',
                startDate: Carbon::today(),
                endDate: $now->copy(),
                deviceIds: $deviceIds,
                tariff: $tariff
            ),
            'week' => $this->buildPaymentEstimation(
                label: 'Minggu Ini',
                startDate: $now->copy()->startOfWeek(),
                endDate: $now->copy(),
                deviceIds: $deviceIds,
                tariff: $tariff
            ),
            'month' => $this->buildPaymentEstimation(
                label: 'Bulan Ini',
                startDate: $now->copy()->startOfMonth(),
                endDate: $now->copy(),
                deviceIds: $deviceIds,
                tariff: $tariff
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
            'formula' => round($usageKwh, 4) . ' kWh x Rp ' . number_format($tariff, 0, ',', '.'),
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

    private function getLogEnergyKwh(EnergyLog $log): float
    {
        return (float) ($log->energy_kwh ?? $log->energy ?? 0);
    }
}