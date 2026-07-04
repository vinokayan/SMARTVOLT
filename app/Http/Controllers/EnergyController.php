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
        // Tambahan: supaya Blade bisa langsung membaca room_name dan device_name
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
        /*
        |--------------------------------------------------------------------------
        | Estimasi Pembayaran Listrik
        |--------------------------------------------------------------------------
        | Perhitungan menggunakan rumus:
        | estimasi biaya = total kWh x tarif listrik per kWh
        |
        | Tarif listrik diambil dari system_settings.electricity_tariff.
        | Jika belum ada, default menggunakan 1444.
        |--------------------------------------------------------------------------
        */
        $systemSetting = SystemSetting::where('user_id', Auth::id())->first();
        $electricityTariff = (float) ($systemSetting?->electricity_tariff ?? 1444);
        $deviceIds = $devices->pluck('id');
        $paymentEstimations = [
            'today' => $this->buildPaymentEstimation(
                label: 'Hari Ini',
                startDate: Carbon::today(),
                endDate: Carbon::now(),
                deviceIds: $deviceIds,
                tariff: $electricityTariff
            ),
            'week' => $this->buildPaymentEstimation(
                label: 'Minggu Ini',
                startDate: Carbon::now()->startOfWeek(),
                endDate: Carbon::now(),
                deviceIds: $deviceIds,
                tariff: $electricityTariff
            ),
            'month' => $this->buildPaymentEstimation(
                label: 'Bulan Ini',
                startDate: Carbon::now()->startOfMonth(),
                endDate: Carbon::now(),
                deviceIds: $deviceIds,
                tariff: $electricityTariff
            ),
        ];
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
        $logs = $query
            ->latest()
            ->get()
            ->map(function ($log) {
                return [
                    'Room' => $log->device?->room?->name ?? '-',
                    'Device' => $log->device?->name ?? '-',
                    'Waktu' => optional($log->created_at)->format('d/m/Y H:i:s'),
                    'Voltage (V)' => number_format($log->voltage ?? 0, 2),
                    'Current (A)' => number_format($log->current ?? 0, 2),
                    'Power (W)' => number_format($log->power ?? 0, 2),
                    'Energy (kWh)' => number_format($log->energy_kwh ?? $log->energy ?? 0, 4),
                ];
            });
        return response()->json($logs);
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
}