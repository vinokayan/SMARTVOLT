<?php

namespace App\Http\Controllers;

use App\Models\Device;
use App\Models\EnergyLog;
use App\Models\EnergyMeter;
use App\Models\SystemSetting;
use App\Services\EnergyUsageCalculator;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnergyController extends Controller
{
    private const DEFAULT_TIMEZONE = 'Asia/Jakarta';
    private const DEFAULT_TARIFF = 1444;
    private const DEFAULT_REFRESH_INTERVAL = 30;

    public function __construct(
        private readonly EnergyUsageCalculator $energyUsageCalculator
    ) {
    }

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

        $periodEnd = $this->periodEnd(
            $filters['date_to']
        );

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
                ? $this->formatDateTimeIndonesia($lastLog->observed_at)
                : null,
        ];

        /*
         * Grafik memakai agregasi waktu yang sama seperti Beranda.
         * Ketika semua meter dipilih, nilai daya tidak lagi ditampilkan
         * sebagai baris terpisah yang saling bercampur antarmeter.
         */
        $chart = $this->buildHistoryChart(
            clone $query,
            $periodStart,
            $periodEnd
        );

        $electricityTariff = $this->getElectricityTariff();
        $system = $this->buildSystemStatus();
        $refreshInterval = $this->getRefreshInterval();

        /*
         * Estimasi biaya memakai perhitungan energi yang sama dengan Beranda,
         * sehingga nilai pada kedua halaman tidak berbeda sumber.
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
            'electricityTariff',
            'system',
            'refreshInterval'
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

        $rangeEnd = $this->periodEnd(
            $filters['date_to']
        );

        $usageKwh = $this->calculateUsageForPeriod(
            $meterIds,
            $rangeStart,
            $rangeEnd
        );

        $estimatedCost = $usageKwh * $electricityTariff;

        $summaryRows = collect([[
            'Ruangan' => 'TOTAL',
            'Meter Ruangan' => 'Total Pemakaian Periode',
            'Waktu' => $this->formatDateTimeIndonesia(
                Carbon::now($this->applicationTimezone())
            ),
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
                'Waktu' => $this->formatDateTimeIndonesia(
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
        /*
         * Pembacaan terbaru ditentukan dari observed_at, bukan hanya ID.
         * ID dipakai sebagai pembanding kedua ketika waktu pembacaan sama.
         */
        $latestLogIds = (clone $this->energyLogQuery($filters))
            ->whereNotNull('energy_meter_id')
            ->orderBy('observed_at')
            ->orderBy('id')
            ->get([
                'id',
                'energy_meter_id',
                'observed_at',
            ])
            ->groupBy('energy_meter_id')
            ->map(fn ($meterLogs) => $meterLogs->last()?->id)
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

    private function buildSystemStatus(): array
    {
        $threshold = Carbon::now($this->applicationTimezone())
            ->subMinutes($this->onlineTimeoutMinutes());

        $devices = Device::query()
            ->whereHas('room', function ($query) {
                $query->where('user_id', Auth::id());
            })
            ->get();

        $connected = $devices->contains(function (Device $device) use ($threshold) {
            return (bool) $device->is_online
                && $device->last_seen_at
                && $device->last_seen_at->greaterThanOrEqualTo($threshold);
        });

        return [
            'connected' => $connected,
            'status_label' => $connected ? 'Terhubung' : 'Belum terhubung',
        ];
    }

    private function getRefreshInterval(): int
    {
        $setting = SystemSetting::query()
            ->where('user_id', Auth::id())
            ->first();

        return min(
            60,
            max(
                10,
                (int) ($setting?->refresh_interval ?? self::DEFAULT_REFRESH_INTERVAL)
            )
        );
    }

    private function onlineTimeoutMinutes(): int
    {
        return max(
            1,
            (int) config('services.iot.online_timeout_minutes', 2)
        );
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

        $selectedDateTo = Carbon::parse(
            $filters['date_to'] ?? $today->toDateString(),
            $timezone
        );

        $selectedEnd = $this->periodEnd(
            $selectedDateTo->toDateString()
        );

        $singleSelectedDay = $selectedStart->isSameDay($selectedDateTo);
        $selectedLabel = match (true) {
            $singleSelectedDay && $selectedDateTo->isSameDay($today) => 'Hari Ini',
            $singleSelectedDay => 'Hari Terpilih',
            default => 'Periode Terpilih',
        };

        $usingToday = $selectedDateTo->isSameDay($today);

        return [
            'selected' => $this->buildPaymentEstimation(
                label: $selectedLabel,
                startDate: $selectedStart->copy(),
                endDate: $selectedEnd->copy(),
                meterIds: $meterIds,
                tariff: $tariff
            ),
            'today' => $this->buildPaymentEstimation(
                label: $usingToday ? 'Hari Ini' : 'Hari Terpilih',
                startDate: $selectedDateTo->copy()->startOfDay(),
                endDate: $selectedEnd->copy(),
                meterIds: $meterIds,
                tariff: $tariff
            ),
            'week' => $this->buildPaymentEstimation(
                label: $usingToday ? 'Minggu Ini' : 'Minggu Terpilih',
                startDate: $selectedDateTo->copy()->startOfWeek(),
                endDate: $selectedEnd->copy(),
                meterIds: $meterIds,
                tariff: $tariff
            ),
            'month' => $this->buildPaymentEstimation(
                label: $usingToday ? 'Bulan Ini' : 'Bulan Terpilih',
                startDate: $selectedDateTo->copy()->startOfMonth(),
                endDate: $selectedEnd->copy(),
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
        $usageKwh = $this->calculateUsageForPeriod(
            $meterIds,
            $startDate,
            $endDate
        );

        $estimatedCost = $usageKwh * $tariff;

        return [
            'label' => $label,
            'period' => $this->formatDateRangeIndonesia(
                $startDate,
                $endDate
            ),
            'usage_kwh' => round($usageKwh, 6),
            'tariff' => round($tariff, 2),
            /*
             * Dua angka desimal dipertahankan supaya pemakaian kecil tidak
             * selalu terlihat sebagai Rp 0, Rp 1, atau angka bulat yang sama.
             */
            'estimated_cost' => round($estimatedCost, 2),
            'formula' => round($usageKwh, 6)
                . ' kWh × Rp '
                . number_format($tariff, 0, ',', '.'),
        ];
    }

    /**
     * Menghitung pemakaian periode dari data energi kumulatif PZEM.
     * Metode ini disamakan dengan perhitungan pada DashboardController.
     */
    private function calculateUsageForPeriod(
        $meterIds,
        Carbon $startDate,
        Carbon $endDate
    ): float {
        return $this->energyUsageCalculator->calculate(
            (int) Auth::id(),
            collect($meterIds),
            $startDate,
            $endDate
        );
    }

    private function buildHistoryChart(
        $query,
        Carbon $startDate,
        Carbon $endDate
    ): array {
        $logs = $query
            ->orderBy('observed_at')
            ->orderBy('id')
            ->get();

        if ($logs->isEmpty()) {
            return [
                'labels' => collect(),
                'power' => collect(),
                'energy' => collect(),
            ];
        }

        $periodDays = max(
            1,
            $startDate->copy()->startOfDay()->diffInDays(
                $endDate->copy()->startOfDay()
            ) + 1
        );

        $bucketType = match (true) {
            $periodDays <= 1 => 'quarter-hour',
            $periodDays <= 7 => 'hour',
            default => 'day',
        };

        $buckets = $logs
            ->groupBy(function (EnergyLog $log) use ($bucketType) {
                if (! $log->observed_at) {
                    return '-';
                }

                $time = $this->toCarbon($log->observed_at);

                if ($bucketType === 'quarter-hour') {
                    $minute = intdiv((int) $time->format('i'), 15) * 15;

                    return $time
                        ->copy()
                        ->minute($minute)
                        ->second(0)
                        ->format('Y-m-d H:i');
                }

                if ($bucketType === 'hour') {
                    return $time
                        ->copy()
                        ->minute(0)
                        ->second(0)
                        ->format('Y-m-d H:i');
                }

                return $time->copy()->startOfDay()->format('Y-m-d H:i');
            })
            ->map(function ($bucketLogs, $bucketKey) use ($bucketType) {
                $latestPerMeter = $bucketLogs
                    ->groupBy('energy_meter_id')
                    ->map(fn ($meterLogs) => $meterLogs->last())
                    ->values();

                $bucketTime = $bucketKey !== '-'
                    ? Carbon::createFromFormat(
                        'Y-m-d H:i',
                        $bucketKey,
                        $this->applicationTimezone()
                    )->locale('id')
                    : null;

                $labelFormat = match ($bucketType) {
                    'quarter-hour' => 'H.i',
                    'hour' => 'j M, H.i',
                    default => 'j M',
                };

                return [
                    'label' => $bucketTime
                        ? $bucketTime->translatedFormat($labelFormat)
                        : '-',
                    'power' => round(
                        (float) $latestPerMeter->sum(
                            fn (EnergyLog $log) => max(
                                0,
                                (float) ($log->power ?? 0)
                            )
                        ),
                        2
                    ),
                    'energy' => round(
                        (float) $latestPerMeter->sum(
                            fn (EnergyLog $log) => max(
                                0,
                                (float) ($log->energy ?? 0)
                            )
                        ),
                        4
                    ),
                ];
            })
            ->values();

        return [
            'labels' => $buckets->pluck('label')->values(),
            'power' => $buckets->pluck('power')->values(),
            'energy' => $buckets->pluck('energy')->values(),
        ];
    }

    private function periodEnd(string $date): Carbon
    {
        $timezone = $this->applicationTimezone();
        $selectedDate = Carbon::parse($date, $timezone);
        $now = Carbon::now($timezone);

        return $selectedDate->isSameDay($now)
            ? $now
            : $selectedDate->endOfDay();
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

    private function formatDateTimeIndonesia($value): ?string
    {
        if (! $value) {
            return null;
        }

        return $this->toCarbon($value)
            ->locale('id')
            ->translatedFormat('j M Y, H.i');
    }

    private function formatDateRangeIndonesia(
        CarbonInterface $startDate,
        CarbonInterface $endDate
    ): string {
        $start = $this->toCarbon($startDate)->locale('id');
        $end = $this->toCarbon($endDate)->locale('id');

        if ($start->isSameDay($end)) {
            return $start->translatedFormat('j F Y');
        }

        if (
            $start->year === $end->year
            && $start->month === $end->month
        ) {
            return $start->translatedFormat('j')
                . '–'
                . $end->translatedFormat('j F Y');
        }

        if ($start->year === $end->year) {
            return $start->translatedFormat('j F')
                . '–'
                . $end->translatedFormat('j F Y');
        }

        return $start->translatedFormat('j F Y')
            . '–'
            . $end->translatedFormat('j F Y');
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
