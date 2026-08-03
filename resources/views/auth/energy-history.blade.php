@extends('layouts.app')

@section('title', 'Pemakaian Listrik')
@section('page-title', 'Pemakaian Listrik')
@section('page-subtitle', 'Lihat pemakaian, biaya, dan riwayat listrik berdasarkan periode.')
@section('body-class', 'sv-energy-history-page sv-energy-streamlined')

@php
    $timezone = config('app.timezone', 'Asia/Jakarta');
    $meterCollection = collect($devices ?? []);
    $paymentCollection = collect($paymentEstimations ?? []);
    $chartData = $chart ?? ['labels' => [], 'power' => [], 'energy' => []];
    $selectedMeterId = $filters['meter_id'] ?? null;
    $fromDate = $filters['date_from'] ?? now($timezone)->toDateString();
    $toDate = $filters['date_to'] ?? now($timezone)->toDateString();
    $selectedEstimation = $paymentCollection->get('selected') ?? [];
    $monthEstimation = $paymentCollection->get('month') ?? [];
    $hasPeriodData = (int) ($summary['total_logs'] ?? 0) > 0;
    $systemConnected = (bool) ($system['connected'] ?? false);

    $fromCarbon = \Carbon\Carbon::parse($fromDate, $timezone)->locale('id');
    $toCarbon = \Carbon\Carbon::parse($toDate, $timezone)->locale('id');
    $fromHuman = $fromCarbon->translatedFormat('j F Y');
    $toHuman = $toCarbon->translatedFormat('j F Y');

    $periodLabel = match (true) {
        $fromCarbon->isSameDay($toCarbon) => $fromHuman,
        $fromCarbon->year === $toCarbon->year && $fromCarbon->month === $toCarbon->month
            => $fromCarbon->translatedFormat('j') . '–' . $toCarbon->translatedFormat('j F Y'),
        $fromCarbon->year === $toCarbon->year
            => $fromCarbon->translatedFormat('j F') . '–' . $toCarbon->translatedFormat('j F Y'),
        default => $fromHuman . '–' . $toHuman,
    };

    $formatDateTime = static function ($value) use ($timezone): string {
        if (! $value) {
            return '—';
        }

        $date = $value instanceof \Carbon\CarbonInterface
            ? \Carbon\Carbon::parse($value->format('Y-m-d H:i:s'), $timezone)
            : \Carbon\Carbon::parse((string) $value, $timezone);

        return $date->locale('id')->translatedFormat('j M Y, H.i');
    };

    $exportQuery = array_filter([
        'device_id' => $selectedMeterId,
        'date_from' => $fromDate,
        'date_to' => $toDate,
    ], static fn ($value) => $value !== null && $value !== '');

    $exportUrl = route('energy.history.export', $exportQuery);
@endphp

@section('system-status')
    <span
        class="sv-badge {{ $systemConnected ? 'sv-badge-success' : 'sv-badge-neutral' }}"
        data-energy-system-badge
    >
        <span class="sv-status-dot" aria-hidden="true"></span>
        <span data-energy-system-label>
            {{ $systemConnected ? 'Terhubung' : 'Belum terhubung' }}
        </span>
    </span>
@endsection

@section('content')
    <section class="sv-card sv-filter-card">
        <div class="sv-card-body">
            <form action="{{ route('energy.history') }}" method="GET" class="sv-filter-grid sv-filter-grid-compact">
                <div class="sv-form-field">
                    <label for="device_id" class="sv-form-label">Meter listrik</label>
                    <select id="device_id" name="device_id" class="sv-form-control">
                        <option value="">Semua meter</option>
                        @foreach($meterCollection as $meter)
                            <option value="{{ $meter->id }}" {{ (string) $selectedMeterId === (string) $meter->id ? 'selected' : '' }}>
                                {{ $meter->room_name ?? $meter->room?->name ?? '-' }} · {{ $meter->meter_name ?? $meter->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="sv-form-field">
                    <label for="date_from" class="sv-form-label">Mulai</label>
                    <input type="date" id="date_from" name="date_from" class="sv-form-control" value="{{ $fromDate }}" required>
                    <small class="sv-date-readable" data-readable-date="date_from">{{ $fromHuman }}</small>
                </div>

                <div class="sv-form-field">
                    <label for="date_to" class="sv-form-label">Selesai</label>
                    <input type="date" id="date_to" name="date_to" class="sv-form-control" value="{{ $toDate }}" required>
                    <small class="sv-date-readable" data-readable-date="date_to">{{ $toHuman }}</small>
                </div>

                <div class="sv-filter-actions">
                    <button type="submit" class="sv-button sv-button-primary">Terapkan</button>
                    <a href="{{ route('energy.history') }}" class="sv-button sv-button-ghost">Atur ulang</a>
                </div>
            </form>

            <div class="sv-filter-footer">
                <div class="sv-quick-filters" aria-label="Pilihan periode cepat">
                    <button type="button" class="sv-quick-filter" data-date-range="today">Hari ini</button>
                    <button type="button" class="sv-quick-filter" data-date-range="7days">7 hari</button>
                    <button type="button" class="sv-quick-filter" data-date-range="month">Bulan ini</button>
                    <button type="button" class="sv-quick-filter" data-date-range="last-month">Bulan lalu</button>
                </div>

                <div class="sv-filter-footer-actions">
                    <span class="sv-data-count">
                        {{ number_format((int) ($summary['total_logs'] ?? 0), 0, ',', '.') }} pembacaan pada periode ini
                    </span>
                    <button type="button" class="sv-button sv-button-secondary sv-button-sm" data-export-url="{{ $exportUrl }}" data-energy-export>
                        <x-icon name="download" :size="16" />
                        <span data-export-label>Unduh CSV</span>
                    </button>
                </div>
            </div>
        </div>
    </section>

    <section class="sv-history-metrics sv-history-metrics-three" aria-label="Ringkasan pemakaian listrik">
        <article class="sv-history-metric sv-history-metric--green">
            <x-feature-icon name="energy" tone="green" :size="20" variant="soft" />
            <div>
                <span>Total Pemakaian</span>
                <strong>{{ $hasPeriodData ? number_format((float) ($summary['usage_kwh'] ?? 0), 3, ',', '.') . ' kWh' : '—' }}</strong>
            </div>
        </article>

        <article class="sv-history-metric sv-history-metric--amber">
            <x-feature-icon name="money" tone="amber" :size="20" variant="soft" />
            <div>
                <span>Biaya Periode</span>
                <strong>{{ $hasPeriodData ? 'Rp' . number_format((float) ($selectedEstimation['estimated_cost'] ?? 0), 0, ',', '.') : '—' }}</strong>
            </div>
        </article>

        <article class="sv-history-metric sv-history-metric--blue">
            <x-feature-icon name="bolt" tone="blue" :size="20" variant="soft" />
            <div>
                <span>Daya Tertinggi</span>
                <strong>{{ $hasPeriodData ? number_format((float) ($summary['max_power'] ?? 0), 1, ',', '.') . ' W' : '—' }}</strong>
            </div>
        </article>
    </section>

    <section class="sv-section-grid sv-section-grid-analysis">
        <article class="sv-card sv-history-chart-card">
            <header class="sv-card-header">
                <div>
                    <h2>Grafik Pemakaian</h2>
                    <p>{{ $periodLabel }}</p>
                </div>

                <div class="sv-chart-tabs" role="tablist" aria-label="Jenis grafik">
                    <button type="button" class="sv-chart-tab is-active" data-history-chart-mode="power" aria-selected="true">Daya</button>
                    <button type="button" class="sv-chart-tab" data-history-chart-mode="energy" aria-selected="false">Energi</button>
                </div>
            </header>

            <div class="sv-card-body">
                <div class="sv-chart-meta-line">
                    <span>Rata-rata daya <strong>{{ $hasPeriodData ? number_format((float) ($summary['avg_power'] ?? 0), 1, ',', '.') . ' W' : '—' }}</strong></span>
                    <span>Rata-rata tegangan <strong>{{ $hasPeriodData ? number_format((float) ($summary['avg_voltage'] ?? 0), 1, ',', '.') . ' V' : '—' }}</strong></span>
                    <span>Data terakhir <strong>{{ $summary['latest_time'] ?? 'Belum tersedia' }}</strong></span>
                </div>

                <div class="sv-empty-state {{ collect($chartData['labels'] ?? [])->isNotEmpty() ? 'is-hidden' : '' }}" data-history-chart-empty>
                    <x-feature-icon name="chart" tone="green" :size="24" variant="soft" />
                    <h3>Belum ada data pada periode ini</h3>
                    <p>Pilih periode lain atau hubungkan SmartVolt untuk menerima data terbaru.</p>
                </div>

                <div class="sv-chart-container {{ collect($chartData['labels'] ?? [])->isEmpty() ? 'is-hidden' : '' }}" data-history-chart-container>
                    <canvas id="energyHistoryChart" aria-label="Grafik riwayat pemakaian listrik"></canvas>
                </div>
            </div>
        </article>

        <article class="sv-card sv-estimation-card">
            <header class="sv-card-header">
                <div>
                    <h2>Ringkasan Biaya</h2>
                    <p>Tarif Rp{{ number_format((float) ($electricityTariff ?? 0), 0, ',', '.') }}/kWh.</p>
                </div>
            </header>

            <div class="sv-card-body">
                <div class="sv-estimation-highlight">
                    <span>{{ $selectedEstimation['label'] ?? 'Periode Terpilih' }}</span>
                    <strong>{{ $hasPeriodData ? 'Rp' . number_format((float) ($selectedEstimation['estimated_cost'] ?? 0), 0, ',', '.') : '—' }}</strong>
                    <small>{{ $hasPeriodData ? number_format((float) ($selectedEstimation['usage_kwh'] ?? 0), 3, ',', '.') . ' kWh' : 'Data belum tersedia' }}</small>
                </div>

                @if(!empty($monthEstimation))
                    <dl class="sv-estimation-secondary">
                        <div>
                            <dt>{{ $monthEstimation['label'] ?? 'Bulan Ini' }}</dt>
                            <dd>Rp{{ number_format((float) ($monthEstimation['estimated_cost'] ?? 0), 0, ',', '.') }}</dd>
                        </div>
                        <div>
                            <dt>Periode</dt>
                            <dd>{{ $monthEstimation['period'] ?? '—' }}</dd>
                        </div>
                    </dl>
                @endif
            </div>
        </article>
    </section>

    <section class="sv-card">
        <header class="sv-card-header">
            <div>
                <h2>Riwayat Meter</h2>
                <p>Data terbaru dari setiap meter pada periode yang dipilih.</p>
            </div>
            <span class="sv-badge sv-badge-neutral">{{ $logs->total() }} meter ditampilkan</span>
        </header>

        <div class="sv-card-body">
            @if($logs->isEmpty())
                <div class="sv-empty-state sv-empty-state-compact">
                    <x-feature-icon name="document" tone="blue" :size="24" variant="soft" />
                    <h3>Data tidak ditemukan</h3>
                    <p>Belum ada data pemakaian untuk {{ $periodLabel }}.</p>
                </div>
            @else
                <div class="sv-table-wrap">
                    <table class="sv-table">
                        <thead>
                            <tr>
                                <th>Ruangan</th>
                                <th>Meter</th>
                                <th>Waktu</th>
                                <th class="is-numeric">Tegangan</th>
                                <th class="is-numeric">Arus</th>
                                <th class="is-numeric">Daya</th>
                                <th class="is-numeric">Energi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($logs as $log)
                                <tr>
                                    <td>{{ $log->room_name ?? '-' }}</td>
                                    <td>{{ $log->meter_name ?? $log->device_name ?? '-' }}</td>
                                    <td>{{ $formatDateTime($log->observed_at) }}</td>
                                    <td class="is-numeric">{{ number_format((float) ($log->voltage ?? 0), 1, ',', '.') }} V</td>
                                    <td class="is-numeric">{{ number_format((float) ($log->current ?? 0), 3, ',', '.') }} A</td>
                                    <td class="is-numeric">{{ number_format((float) ($log->power ?? 0), 1, ',', '.') }} W</td>
                                    <td class="is-numeric">{{ number_format((float) ($log->energy ?? 0), 4, ',', '.') }} kWh</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="sv-pagination">{{ $logs->links() }}</div>
            @endif
        </div>
    </section>

    <script type="application/json" id="smartvolt-history-data">{!! json_encode([
        'chart' => $chartData,
        'exportUrl' => $exportUrl,
        'today' => now($timezone)->toDateString(),
        'dateFrom' => $fromDate,
        'dateTo' => $toDate,
        'statusEndpoint' => route('dashboard.data'),
        'refreshInterval' => (int) ($refreshInterval ?? 30),
        'system' => $system ?? [],
    ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) !!}</script>
@endsection

@push('scripts')
    <script src="{{ asset('assets/js/chart.umd.min.js') }}?v=4.5.1" defer></script>
    <script src="{{ asset('assets/js/smartvolt-energy-history.js') }}?v=20260803-final" defer></script>
@endpush
