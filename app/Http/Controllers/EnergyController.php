<?php

namespace App\Http\Controllers;

use App\Models\EnergyLog;
use App\Models\EnergyMeter;
use App\Models\SystemSetting;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnergyController extends Controller
{
    public function index(Request $request)
    {
        /*
         * Catatan:
         * Parameter URL tetap memakai device_id agar form/filter lama tidak rusak.
         * Tetapi isi device_id sekarang dimaknai sebagai energy_meter_id.
         *
         * Konsep baru:
         * - devices      = relay/perangkat ON-OFF
         * - energy_meter = PZEM ruangan/panel
         * - energy_logs  = riwayat pembacaan PZEM
         */

        $filters = [
            'meter_id' => $request->input('device_id'),
            'date_from' => $request->input('date_from'),
            'date_to' => $request->input('date_to'),
        ];

        $query = $this->energyLogQuery($filters);

        /*
         * Tabel utama hanya menampilkan 1 baris terbaru untuk setiap meter PZEM.
         * Jika telemetry baru masuk dari meter yang sama, barisnya tidak bertambah,
         * tetapi nilai waktu, tegangan, arus, daya, dan energi berubah di baris yang sama.
         */
        $logs = $this->latestLogsForTable($filters)
            ->orderByDesc('observed_at')
            ->paginate(20)
            ->appends($request->query());

        /*
         * Menambahkan nama ruangan dan nama meter agar Blade mudah menampilkan:
         * RUANGAN | METER RUANGAN | WAKTU | TEGANGAN | ARUS | DAYA TOTAL | ENERGI
         *
         * device_name tetap diisi untuk kompatibilitas Blade lama.
         */
        $logs->getCollection()->transform(function (EnergyLog $log) {
            $meter = $log->energyMeter;
            $room = $meter?->room;

            $log->room_name = $room?->name ?? '-';
            $log->meter_name = $meter?->name ?? '-';

            /*
             * Kompatibilitas dengan Blade lama yang mungkin masih memakai device_name.
             * Sekarang device_name berisi nama meter PZEM, bukan nama relay.
             */
            $log->device_name = $log->meter_name;

            return $log;
        });

        /*
         * Variabel tetap bernama $devices agar Blade lama tidak error.
         * Isinya sekarang adalah daftar PZEM / meter ruangan.
         */
        $devices = EnergyMeter::with('room')
            ->where('user_id', Auth::id())
            ->where('is_active', true)
            ->orderBy('name')
            ->get()
            ->map(function (EnergyMeter $meter) {
                $meter->device_name = $meter->name;
                $meter->meter_name = $meter->name;
                $meter->room_name = $meter->room?->name ?? '-';

                return $meter;
            });

        $meterIds = $this->getMeterIds($filters);

        $firstLog = (clone $query)
            ->orderBy('observed_at')
            ->first();

        $lastLog = (clone $query)
            ->orderByDesc('observed_at')
            ->first();

        $usageKwh = 0;

        if ($firstLog && $lastLog) {
            $usageKwh = $this->calculateEnergyUsage(
                $meterIds,
                $this->toCarbon($firstLog->observed_at),
                $this->toCarbon($lastLog->observed_at)
            );
        }

        $summary = [
            'total_logs' => (clone $this->latestLogsForTable($filters))->count(),
            'max_power' => round((float) ((clone $query)->max('power') ?? 0), 2),
            'avg_power' => round((float) ((clone $query)->avg('power') ?? 0), 2),
            'avg_voltage' => round((float) ((clone $query)->avg('voltage') ?? 0), 2),
            'usage_kwh' => round($usageKwh, 4),
            'latest_time' => $lastLog
                ? $this->formatDateTime($lastLog->observed_at)
                : null,
        ];

        $chartLogs = (clone $query)
            ->orderByDesc('observed_at')
            ->take(10)
            ->get()
            ->reverse()
            ->values();

        $chart = [
            'labels' => $chartLogs->map(function (EnergyLog $log) {
                return $this->formatDateTime($log->observed_at, 'H:i');
            }),
            'power' => $chartLogs->map(function (EnergyLog $log) {
                return round((float) ($log->power ?? 0), 2);
            }),
            /*
             * Nilai energy dari PZEM biasanya kumulatif.
             * Untuk pemakaian periode, sistem memakai calculateEnergyUsage().
             */
            'energy' => $chartLogs->map(function (EnergyLog $log) {
                return round((float) ($log->energy ?? 0), 4);
            }),
        ];

        $electricityTariff = $this->getElectricityTariff();

        $paymentEstimations = $this->buildPaymentEstimations(
            $meterIds,
            $electricityTariff
        );

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
            'meter_id' => $request->input('device_id'),
            'date_from' => $request->input('date_from'),
            'date_to' => $request->input('date_to'),
        ];

        $query = $this->energyLogQuery($filters);
        $meterIds = $this->getMeterIds($filters);
        $electricityTariff = $this->getElectricityTariff();

        /*
         * Ekspor juga dibuat sama dengan tampilan tabel:
         * hanya 1 data terbaru untuk setiap meter PZEM.
         */
        $exportLogs = $this->latestLogsForTable($filters)
            ->orderByDesc('observed_at')
            ->get();

        /*
         * Range perhitungan tetap memakai seluruh log sesuai filter,
         * bukan hanya data terbaru, agar estimasi kWh tetap benar.
         */
        $firstLogForRange = (clone $query)
            ->orderBy('observed_at')
            ->first();

        $lastLogForRange = (clone $query)
            ->orderByDesc('observed_at')
            ->first();

        $rangeStart = ! empty($filters['date_from'])
            ? Carbon::parse($filters['date_from'])->startOfDay()
            : ($firstLogForRange
                ? $this->toCarbon($firstLogForRange->observed_at)
                : null);

        $rangeEnd = ! empty($filters['date_to'])
            ? Carbon::parse($filters['date_to'])->endOfDay()
            : ($lastLogForRange
                ? $this->toCarbon($lastLogForRange->observed_at)
                : null);

        $usageKwh = ($rangeStart && $rangeEnd)
            ? $this->calculateEnergyUsage(
                $meterIds,
                $rangeStart,
                $rangeEnd
            )
            : 0;

        $estimatedCost = $usageKwh * $electricityTariff;

        $summaryRows = collect([[
            'Ruangan' => 'TOTAL',
            'Meter Ruangan' => 'Total Pemakaian Periode',
            'Waktu' => Carbon::now('Asia/Jakarta')->format('d/m/Y H:i:s'),
            'Tegangan (V)' => '-',
            'Arus (A)' => '-',
            'Daya Total (W)' => '-',
            'Energi (kWh)' => number_format($usageKwh, 4, ',', '.'),
            'Tarif per kWh' => 'Rp ' . number_format(
                $electricityTariff,
                0,
                ',',
                '.'
            ),
            'Estimasi Pembayaran' => 'Rp ' . number_format(
                $estimatedCost,
                0,
                ',',
                '.'
            ),
        ]]);

        $logRows = $exportLogs->map(function (EnergyLog $log) {
            return [
                'Ruangan' => $log->energyMeter?->room?->name ?? '-',
                'Meter Ruangan' => $log->energyMeter?->name ?? '-',
                'Waktu' => $this->formatDateTime($log->observed_at),
                'Tegangan (V)' => number_format(
                    (float) ($log->voltage ?? 0),
                    2,
                    ',',
                    '.'
                ),
                'Arus (A)' => number_format(
                    (float) ($log->current ?? 0),
                    2,
                    ',',
                    '.'
                ),
                'Daya Total (W)' => number_format(
                    (float) ($log->power ?? 0),
                    2,
                    ',',
                    '.'
                ),
                'Energi (kWh)' => number_format(
                    (float) ($log->energy ?? 0),
                    4,
                    ',',
                    '.'
                ),
                'Tarif per kWh' => '-',
                'Estimasi Pembayaran' => '-',
            ];
        });

        return response()->json(
            $summaryRows
                ->concat($logRows)
                ->values()
        );
    }

    private function latestLogsForTable(array $filters)
    {
        $latestLogIds = (clone $this->energyLogQuery($filters))
            ->whereNotNull('energy_meter_id')
            ->selectRaw('MAX(id) as id')
            ->groupBy('energy_meter_id')
            ->pluck('id')
            ->filter()
            ->values();

        return EnergyLog::with('energyMeter.room')
            ->whereIn('id', $latestLogIds);
    }

    private function energyLogQuery(array $filters)
    {
        $query = EnergyLog::with('energyMeter.room')
            ->whereHas('energyMeter', function ($meterQuery) {
                $meterQuery->where('user_id', Auth::id());
            });

        if (! empty($filters['meter_id'])) {
            $query->where('energy_meter_id', $filters['meter_id']);
        }

        if (! empty($filters['date_from'])) {
            $query->whereDate(
                'observed_at',
                '>=',
                $filters['date_from']
            );
        }

        if (! empty($filters['date_to'])) {
            $query->whereDate(
                'observed_at',
                '<=',
                $filters['date_to']
            );
        }

        return $query;
    }

    private function getMeterIds(array $filters)
    {
        $query = EnergyMeter::query()
            ->where('user_id', Auth::id())
            ->where('is_active', true);

        if (! empty($filters['meter_id'])) {
            $query->where('id', $filters['meter_id']);
        }

        return $query->pluck('id');
    }

    private function getElectricityTariff(): float
    {
        $systemSetting = SystemSetting::where(
            'user_id',
            Auth::id()
        )->first();

        return (float) ($systemSetting?->electricity_tariff ?? 1444);
    }

    private function buildPaymentEstimations(
        $meterIds,
        float $tariff
    ): array {
        $now = Carbon::now('Asia/Jakarta');

        return [
            'today' => $this->buildPaymentEstimation(
                label: 'Hari Ini',
                startDate: $now->copy()->startOfDay(),
                endDate: $now->copy(),
                meterIds: $meterIds,
                tariff: $tariff
            ),
            'week' => $this->buildPaymentEstimation(
                label: 'Minggu Ini',
                startDate: $now->copy()->startOfWeek(),
                endDate: $now->copy(),
                meterIds: $meterIds,
                tariff: $tariff
            ),
            'month' => $this->buildPaymentEstimation(
                label: 'Bulan Ini',
                startDate: $now->copy()->startOfMonth(),
                endDate: $now->copy(),
                meterIds: $meterIds,
                tariff: $tariff
            ),
        ];
    }

    private function buildPaymentEstimation(
        string $label,
        Carbon $startDate,
        Carbon $endDate,
        $meterIds,
        float $tariff
    ): array {
        $usageKwh = $this->calculateEnergyUsage(
            $meterIds,
            $startDate,
            $endDate
        );

        $estimatedCost = $usageKwh * $tariff;

        return [
            'label' => $label,
            'period' => $startDate->format('d/m/Y')
                . ' - '
                . $endDate->format('d/m/Y'),
            'usage_kwh' => round($usageKwh, 4),
            'tariff' => round($tariff, 2),
            'estimated_cost' => round($estimatedCost),
            'formula' => round($usageKwh, 4)
                . ' kWh x Rp '
                . number_format($tariff, 0, ',', '.'),
        ];
    }

    /*
     * PZEM mengirim nilai energy kumulatif.
     * Jadi pemakaian periode bukan SUM(energy), tetapi selisih antar pembacaan.
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
                ->first();

            $logs = EnergyLog::query()
                ->where('energy_meter_id', $meterId)
                ->whereBetween('observed_at', [$startDate, $endDate])
                ->whereNotNull('energy')
                ->orderBy('observed_at')
                ->get([
                    'id',
                    'energy_meter_id',
                    'energy',
                    'observed_at',
                ]);

            $previousEnergy = $previousLog
                ? (float) $previousLog->energy
                : null;

            foreach ($logs as $log) {
                $currentEnergy = (float) $log->energy;

                if ($previousEnergy === null) {
                    $previousEnergy = $currentEnergy;
                    continue;
                }

                /*
                 * Normal:
                 * currentEnergy >= previousEnergy
                 * usage = selisih.
                 *
                 * Kalau PZEM reset dan nilai energy turun:
                 * currentEnergy < previousEnergy
                 * usage dianggap mulai dari currentEnergy.
                 */
                $totalUsage += $currentEnergy >= $previousEnergy
                    ? $currentEnergy - $previousEnergy
                    : max(0, $currentEnergy);

                $previousEnergy = $currentEnergy;
            }
        }

        return round($totalUsage, 4);
    }

    private function toCarbon($value): Carbon
    {
        if ($value instanceof Carbon) {
            return $value->copy();
        }

        return Carbon::parse($value);
    }

    private function formatDateTime(
        $value,
        string $format = 'd/m/Y H:i:s'
    ): ?string {
        if (! $value) {
            return null;
        }

        return $this->toCarbon($value)
            ->timezone('Asia/Jakarta')
            ->format($format);
    }
}