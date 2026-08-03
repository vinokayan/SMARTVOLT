<?php

namespace App\Services;

use App\Models\EnergyDailySummary;
use App\Models\EnergyLog;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;

class EnergyUsageCalculator
{
    /**
     * Menghitung pemakaian energi dengan aturan yang sama untuk Beranda
     * dan Pemakaian Listrik.
     *
     * Hari yang sudah selesai memakai energy_daily_summaries ketika tersedia.
     * Hari berjalan atau tanggal yang belum memiliki ringkasan dihitung dari
     * energy_logs agar data terbaru tidak terlewat.
     */
    public function calculate(
        int $userId,
        Collection $meterIds,
        CarbonInterface $startDate,
        CarbonInterface $endDate
    ): float {
        $meterIds = $meterIds
            ->map(fn ($value) => (int) $value)
            ->filter(fn (int $value) => $value > 0)
            ->unique()
            ->values();

        if ($meterIds->isEmpty()) {
            return 0.0;
        }

        $start = $this->copyCarbon($startDate);
        $end = $this->copyCarbon($endDate);

        if ($start->greaterThan($end)) {
            return 0.0;
        }

        $summaryMap = $this->loadSummaryMap(
            $userId,
            $meterIds,
            $start,
            $end
        );

        $todayStart = Carbon::now($start->getTimezone())
            ->startOfDay();

        $totalUsage = 0.0;

        foreach ($meterIds as $meterId) {
            $cursor = $start->copy()->startOfDay();
            $lastDay = $end->copy()->startOfDay();

            while ($cursor->lessThanOrEqualTo($lastDay)) {
                $dayStart = $cursor->copy()->startOfDay();
                $dayEnd = $cursor->copy()->endOfDay();

                $effectiveStart = $start->greaterThan($dayStart)
                    ? $start->copy()
                    : $dayStart->copy();

                $effectiveEnd = $end->lessThan($dayEnd)
                    ? $end->copy()
                    : $dayEnd->copy();

                $summaryKey = $this->summaryKey(
                    $meterId,
                    $cursor->toDateString()
                );

                $isCompletedDay = $cursor->lessThan($todayStart);
                $coversWholeDay = $effectiveStart->isStartOfDay()
                    && $effectiveEnd->format('H:i:s') === '23:59:59';

                if (
                    $isCompletedDay
                    && $coversWholeDay
                    && $summaryMap->has($summaryKey)
                ) {
                    $totalUsage += max(
                        0,
                        (float) $summaryMap->get($summaryKey)
                    );
                } else {
                    $totalUsage += $this->calculateFromLogsForMeter(
                        $meterId,
                        $effectiveStart,
                        $effectiveEnd
                    );
                }

                $cursor->addDay();
            }
        }

        return round(max(0, $totalUsage), 6);
    }

    private function loadSummaryMap(
        int $userId,
        Collection $meterIds,
        CarbonInterface $startDate,
        CarbonInterface $endDate
    ): Collection {
        if (! Schema::hasTable('energy_daily_summaries')) {
            return collect();
        }

        return EnergyDailySummary::query()
            ->where('user_id', $userId)
            ->whereIn('energy_meter_id', $meterIds)
            ->whereBetween('summary_date', [
                $startDate->toDateString(),
                $endDate->toDateString(),
            ])
            ->get([
                'energy_meter_id',
                'summary_date',
                'usage_kwh',
            ])
            ->mapWithKeys(function (EnergyDailySummary $summary) {
                $date = $summary->summary_date instanceof CarbonInterface
                    ? $summary->summary_date->toDateString()
                    : Carbon::parse((string) $summary->summary_date)
                        ->toDateString();

                return [
                    $this->summaryKey(
                        (int) $summary->energy_meter_id,
                        $date
                    ) => max(0, (float) $summary->usage_kwh),
                ];
            });
    }

    private function calculateFromLogsForMeter(
        int $meterId,
        CarbonInterface $startDate,
        CarbonInterface $endDate
    ): float {
        if (! Schema::hasTable('energy_logs')) {
            return 0.0;
        }

        $previousLog = EnergyLog::query()
            ->where('energy_meter_id', $meterId)
            ->where('observed_at', '<', $startDate)
            ->whereNotNull('energy')
            ->orderByDesc('observed_at')
            ->orderByDesc('id')
            ->first();

        $logs = EnergyLog::query()
            ->where('energy_meter_id', $meterId)
            ->whereBetween('observed_at', [$startDate, $endDate])
            ->whereNotNull('energy')
            ->orderBy('observed_at')
            ->orderBy('id')
            ->get([
                'id',
                'energy',
                'observed_at',
            ]);

        $previousEnergy = $previousLog
            ? max(0, (float) $previousLog->energy)
            : null;

        $usage = 0.0;

        foreach ($logs as $log) {
            $currentEnergy = max(0, (float) $log->energy);

            if ($previousEnergy === null) {
                $previousEnergy = $currentEnergy;
                continue;
            }

            if ($currentEnergy >= $previousEnergy) {
                $usage += $currentEnergy - $previousEnergy;
            } else {
                /*
                 * Nilai energi PZEM dapat kembali kecil setelah reset.
                 * Pemakaian sesudah reset dihitung kembali mulai dari nol.
                 */
                $usage += $currentEnergy;
            }

            $previousEnergy = $currentEnergy;
        }

        return round(max(0, $usage), 6);
    }

    private function summaryKey(int $meterId, string $date): string
    {
        return $meterId . '|' . $date;
    }

    private function copyCarbon(CarbonInterface $value): Carbon
    {
        return Carbon::parse(
            $value->format('Y-m-d H:i:s.u'),
            $value->getTimezone()
        );
    }
}
