<?php

namespace App\Http\Controllers;

use App\Models\EnergyDailySummary;
use App\Models\EnergyLog;
use App\Models\EnergyMeter;
use App\Models\SystemSetting;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

class EnergyController extends Controller
{
    private const DEFAULT_TIMEZONE = 'Asia/Jakarta';
    private const DEFAULT_TARIFF = 1444;

    public function index(Request $request)
    {
        /*
         * Parameter URL tetap memakai device_id agar form/filter lama
         * tidak rusak. Nilainya sekarang dimaknai sebagai energy_meter_id.
         *
         * Jika tanggal tidak dipilih, halaman secara default menampilkan
         * data hari ini. Dengan demikian kartu ringkasan tidak lagi
         * mencampur data uji atau data lama dari seluruh riwayat.
         */
        $filters = $this->resolveFilters($request);
        $query = $this->energyLogQuery($filters);

        /*
         * Tabel utama menampilkan satu pembacaan terbaru untuk setiap
         * meter PZEM dalam periode filter.
         */
        $logs = $this->latestLogsForTable($filters)
            ->orderByDesc('observed_at')
            ->orderByDesc('id')
            ->paginate(20)
            ->appends($request->query());

        $logs->getCollection()->transform(function (EnergyLog $log) {
            $meter = $log->energyMeter;
            $room = $meter?->room;

            $log->room_name = $room?->name ?? '-';
            $log->meter_name = $meter?->name ?? '-';

            /*
             * Kompatibilitas dengan Blade lama yang masih memakai
             * properti device_name.
             */
            $log->device_name = $log->meter_name;

            return $log;
        });

        /*
         * Nama variabel tetap $devices agar Blade lama tidak error.
         * Isinya merupakan daftar meter PZEM, bukan relay.
         */
        $devices = EnergyMeter::query()
            ->with('room')
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
        $lastLog = (clone $query)
            ->orderByDesc('observed_at')
            ->orderByDesc('id')
            ->first();

        $periodStart = Carbon::parse(
            $filters['date_from'],
            $this->applicationTimezone()
        )->startOfDay();

        $periodEnd = Carbon::parse(
            $filters['date_to'],
            $this->applicationTimezone()
        )->endOfDay();

        $usageKwh = $this->calculateUsageForPeriod(
            $meterIds,
            $periodStart,
            $periodEnd
        );

        /*
         * total_logs harus menghitung seluruh telemetry sesuai filter,
         * bukan jumlah meter pada tabel terbaru.
         */
        $summary = [
            'total_logs' => (clone $query)->count(),
            'max_power' => round(
                (float) ((clone $query)->max('power') ?? 0),
                2
            ),
            'avg_power' => round(
                (float) ((clone $query)->avg('power') ?? 0),
                2
            ),
            'avg_voltage' => round(
                (float) ((clone $query)->avg('voltage') ?? 0),
                2
            ),
            'usage_kwh' => round($usageKwh, 4),
            'latest_time' => $lastLog
                ? $this->formatDateTime($lastLog->observed_at)
                : null,
        ];

        $chartLogs = (clone $query)
            ->orderByDesc('observed_at')
            ->orderByDesc('id')
            ->take(10)
            ->get()
            ->reverse()
            ->values();

        $chart = [
            'labels' => $chartLogs->map(function (EnergyLog $log) {
                return $this->formatDateTime(
                    $log->observed_at,
                    'H:i'
                );
            }),
            'power' => $chartLogs->map(function (EnergyLog $log) {
                return round((float) ($log->power ?? 0), 2);
            }),
            /*
             * Nilai energy pada grafik tetap menampilkan nilai kumulatif
             * PZEM. Pemakaian periode dihitung dari rekap harian.
             */
            'energy' => $chartLogs->map(function (EnergyLog $log) {
                return round((float) ($log->energy ?? 0), 4);
            }),
        ];

        $electricityTariff = $this->getElectricityTariff();

        /*
         * Estimasi hari, minggu, dan bulan mengambil usage_kwh dari
         * energy_daily_summaries, bukan menjumlahkan energy kumulatif.
         */
        $paymentEstimations = $this->buildPaymentEstimations(
            $meterIds,
            $electricityTariff,
            $filters
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
        $filters = $this->resolveFilters($request);
        $meterIds = $this->getMeterIds($filters);
        $electricityTariff = $this->getElectricityTariff();

        /*
         * Ekspor mengikuti tabel utama: satu data terbaru untuk setiap
         * meter dalam periode filter.
         */
        $exportLogs = $this->latestLogsForTable($filters)
            ->orderByDesc('observed_at')
            ->orderByDesc('id')
            ->get();

        $rangeStart = Carbon::parse(
            $filters['date_from'],
            $this->applicationTimezone()
        )->startOfDay();

        $rangeEnd = Carbon::parse(
            $filters['date_to'],
            $this->applicationTimezone()
        )->endOfDay();

        $usageKwh = $this->calculateUsageForPeriod(
            $meterIds,
            $rangeStart,
            $rangeEnd
        );

        $estimatedCost = $usageKwh * $electricityTariff;

        $summaryRows = collect([[
            'Ruangan' => 'TOTAL',
            'Meter Ruangan' => 'Total Pemakaian Periode',
            'Waktu' => Carbon::now(
                $this->applicationTimezone()
            )->format('d/m/Y H:i:s'),
            'Tegangan (V)' => '-',
            'Arus (A)' => '-',
            'Daya Total (W)' => '-',
            'Energi (kWh)' => number_format(
                $usageKwh,
                4,
                ',',
                '.'
            ),
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
                'Waktu' => $this->formatDateTime(
                    $log->observed_at
                ),
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

    /**
     * Membaca dan menormalkan filter halaman.
     *
     * Ketika tanggal kosong, gunakan hari ini agar kartu ringkasan,
     * grafik, dan tabel memakai periode yang sama.
     */
    private function resolveFilters(Request $request): array
    {
        $validated = $request->validate([
            'device_id' => [
                'nullable',
                'integer',
                'min:1',
            ],
            'date_from' => [
                'nullable',
                'date_format:Y-m-d',
            ],
            'date_to' => [
                'nullable',
                'date_format:Y-m-d',
            ],
        ]);

        $timezone = $this->applicationTimezone();
        $today = Carbon::now($timezone)->toDateString();

        $dateFrom = $validated['date_from'] ?? null;
        $dateTo = $validated['date_to'] ?? null;

        if (empty($dateFrom) && empty($dateTo)) {
            $dateFrom = $today;
            $dateTo = $today;
        } elseif (! empty($dateFrom) && empty($dateTo)) {
            $dateTo = $dateFrom;
        } elseif (empty($dateFrom) && ! empty($dateTo)) {
            $dateFrom = $dateTo;
        }

        $from = Carbon::parse($dateFrom, $timezone)->startOfDay();
        $to = Carbon::parse($dateTo, $timezone)->endOfDay();

        if ($from->greaterThan($to)) {
            [$dateFrom, $dateTo] = [$dateTo, $dateFrom];
        }

        return [
            'meter_id' => $validated['device_id'] ?? null,
            'date_from' => $dateFrom,
            'date_to' => $dateTo,
        ];
    }

    /**
     * Mengambil satu log terbaru untuk setiap meter dalam periode filter.
     */
    private function latestLogsForTable(array $filters)
    {
        $latestLogIds = (clone $this->energyLogQuery($filters))
            ->whereNotNull('energy_meter_id')
            ->selectRaw('MAX(id) AS id')
            ->groupBy('energy_meter_id')
            ->pluck('id')
            ->filter()
            ->values();

        return EnergyLog::query()
            ->with('energyMeter.room')
            ->whereIn('id', $latestLogIds);
    }

    private function energyLogQuery(array $filters)
    {
        $query = EnergyLog::query()
            ->with('energyMeter.room')
            ->whereHas('energyMeter', function ($meterQuery) {
                $meterQuery->where('user_id', Auth::id());
            });

        if (! empty($filters['meter_id'])) {
            $query->where(
                'energy_meter_id',
                $filters['meter_id']
            );
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
        $systemSetting = SystemSetting::query()
            ->where('user_id', Auth::id())
            ->first();

        return max(
            0,
            (float) ($systemSetting?->electricity_tariff
                ?? self::DEFAULT_TARIFF)
        );
    }

    /**
     * Membuat estimasi pembayaran berdasarkan filter yang diterapkan.
     *
     * - Periode Terpilih memakai date_from sampai date_to.
     * - Hari Terpilih memakai tanggal selesai filter.
     * - Minggu Terpilih memakai awal minggu sampai tanggal selesai filter.
     * - Bulan Terpilih memakai awal bulan sampai tanggal selesai filter.
     *
     * Dengan struktur ini, estimasi rentang filter tetap tersedia tanpa
     * menghilangkan estimasi hari, minggu, dan bulan.
     */
    private function buildPaymentEstimations(
        $meterIds,
        float $tariff,
        array $filters
    ): array {
        $timezone = $this->applicationTimezone();
        $today = Carbon::now($timezone);

        $selectedStart = Carbon::parse(
            $filters['date_from'] ?? $today->toDateString(),
            $timezone
        )->startOfDay();

        $referenceDate = Carbon::parse(
            $filters['date_to'] ?? $today->toDateString(),
            $timezone
        );

        /*
         * Untuk tanggal hari ini, batas akhir memakai waktu sekarang agar
         * sistem tidak menghitung waktu yang belum terjadi. Untuk tanggal
         * lampau, batas akhirnya adalah akhir hari.
         */
        $referenceEnd = $referenceDate->isSameDay($today)
            ? $today->copy()
            : $referenceDate->copy()->endOfDay();

        $usingToday = $referenceDate->isSameDay($today);
        $singleSelectedDay = $selectedStart->isSameDay($referenceDate);

        $estimations = [
            'today' => $this->buildPaymentEstimation(
                label: $usingToday ? 'Hari Ini' : 'Hari Terpilih',
                startDate: $referenceDate->copy()->startOfDay(),
                endDate: $referenceEnd->copy(),
                meterIds: $meterIds,
                tariff: $tariff
            ),
            'week' => $this->buildPaymentEstimation(
                label: $usingToday ? 'Minggu Ini' : 'Minggu Terpilih',
                startDate: $referenceDate->copy()->startOfWeek(),
                endDate: $referenceEnd->copy(),
                meterIds: $meterIds,
                tariff: $tariff
            ),
            'month' => $this->buildPaymentEstimation(
                label: $usingToday ? 'Bulan Ini' : 'Bulan Terpilih',
                startDate: $referenceDate->copy()->startOfMonth(),
                endDate: $referenceEnd->copy(),
                meterIds: $meterIds,
                tariff: $tariff
            ),
        ];

        /*
         * Untuk rentang lebih dari satu hari, tambahkan estimasi yang sama
         * persis dengan date_from dan date_to. Untuk satu hari, kartu ini
         * tidak ditambahkan agar tidak menduplikasi kartu Hari Terpilih.
         */
        if (! $singleSelectedDay) {
            return [
                'selected' => $this->buildPaymentEstimation(
                    label: 'Periode Terpilih',
                    startDate: $selectedStart->copy(),
                    endDate: $referenceEnd->copy(),
                    meterIds: $meterIds,
                    tariff: $tariff
                ),
            ] + $estimations;
        }

        return $estimations;
    }

    private function buildPaymentEstimation(
        string $label,
        Carbon $startDate,
        Carbon $endDate,
        $meterIds,
        float $tariff
    ): array {
        $usageKwh = $this->calculateUsageForPeriod(
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
            'usage_kwh' => round($usageKwh, 6),
            'tariff' => round($tariff, 2),
            /*
             * Dua angka desimal dipertahankan supaya pemakaian kecil tidak
             * selalu terlihat sebagai Rp 0, Rp 1, atau angka bulat yang sama.
             */
            'estimated_cost' => round($estimatedCost, 2),
            'formula' => round($usageKwh, 6)
                . ' kWh x Rp '
                . number_format($tariff, 0, ',', '.'),
        ];
    }

    /**
     * Menghitung pemakaian periode.
     *
     * Prioritas pertama adalah energy_daily_summaries karena nilai energy
     * PZEM bersifat kumulatif dan raw log hanya disimpan beberapa hari.
     * Jika tabel rekap belum tersedia atau belum memiliki data untuk
     * periode tersebut, sistem menggunakan perhitungan raw log yang aman.
     */
    private function calculateUsageForPeriod(
        $meterIds,
        Carbon $startDate,
        Carbon $endDate
    ): float {
        if ($meterIds->isEmpty()) {
            return 0;
        }

        if (Schema::hasTable('energy_daily_summaries')) {
            $summaryQuery = EnergyDailySummary::query()
                ->where('user_id', Auth::id())
                ->whereIn('energy_meter_id', $meterIds)
                ->whereBetween('summary_date', [
                    $startDate->toDateString(),
                    $endDate->toDateString(),
                ]);

            if ((clone $summaryQuery)->exists()) {
                return round(
                    max(
                        0,
                        (float) $summaryQuery->sum('usage_kwh')
                    ),
                    6
                );
            }
        }

        return $this->calculateEnergyUsageFromLogs(
            $meterIds,
            $startDate,
            $endDate
        );
    }

    /**
     * Fallback perhitungan dari raw log.
     *
     * Ketika nilai energy menurun, sistem tidak menambahkan nilai baru
     * sebagai pemakaian. Penurunan dapat berarti reset PZEM, data uji,
     * pergantian meter, atau data yang datang tidak berurutan.
     */
    private function calculateEnergyUsageFromLogs(
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
                ->orderByDesc('id')
                ->first();

            $logs = EnergyLog::query()
                ->where('energy_meter_id', $meterId)
                ->whereBetween(
                    'observed_at',
                    [$startDate, $endDate]
                )
                ->whereNotNull('energy')
                ->orderBy('observed_at')
                ->orderBy('id')
                ->get([
                    'id',
                    'energy_meter_id',
                    'energy',
                    'observed_at',
                ]);

            $previousEnergy = $previousLog
                ? max(0, (float) $previousLog->energy)
                : null;

            foreach ($logs as $log) {
                $currentEnergy = max(
                    0,
                    (float) $log->energy
                );

                if ($previousEnergy === null) {
                    $previousEnergy = $currentEnergy;
                    continue;
                }

                if ($currentEnergy >= $previousEnergy) {
                    $totalUsage += (
                        $currentEnergy - $previousEnergy
                    );
                }

                /*
                 * Saat nilai turun, baseline dipindahkan ke nilai terbaru
                 * tanpa menambahkannya sebagai pemakaian baru.
                 */
                $previousEnergy = $currentEnergy;
            }
        }

        return round(max(0, $totalUsage), 6);
    }

    private function toCarbon($value): Carbon
    {
        $timezone = $this->applicationTimezone();

        if ($value instanceof CarbonInterface) {
            /*
             * observed_at sekarang disimpan langsung dalam Asia/Jakarta.
             * Jangan konversi lagi karena akan menambah tujuh jam.
             */
            return Carbon::parse(
                $value->format('Y-m-d H:i:s'),
                $timezone
            );
        }

        return Carbon::parse((string) $value, $timezone);
    }

    private function formatDateTime(
        $value,
        string $format = 'd/m/Y H:i:s'
    ): ?string {
        if (! $value) {
            return null;
        }

        return $this->toCarbon($value)->format($format);
    }

    private function applicationTimezone(): string
    {
        $timezone = trim((string) config(
            'app.timezone',
            self::DEFAULT_TIMEZONE
        ));

        return $timezone !== ''
            ? $timezone
            : self::DEFAULT_TIMEZONE;
    }
}
