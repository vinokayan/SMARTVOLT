@extends('layouts.app')

@section('title', 'Pemakaian Listrik')
@section('page-title', 'Pemakaian Listrik')
@section('page-subtitle', 'Analisis pemakaian berdasarkan meter dan periode.')
@section('body-class', 'sv-energy-history-page sv-energy-streamlined')

@php
    $meterCollection = collect($devices ?? []);
    $paymentCollection = collect($paymentEstimations ?? []);
    $chartData = $chart ?? ['labels' => [], 'power' => [], 'energy' => []];
    $selectedMeterId = $filters['meter_id'] ?? null;
    $fromDate = $filters['date_from'] ?? now()->toDateString();
    $toDate = $filters['date_to'] ?? now()->toDateString();
    $selectedEstimation = $paymentCollection->get('selected') ?? $paymentCollection->get('today') ?? [];
    $monthEstimation = $paymentCollection->get('month') ?? [];
@endphp

@section('system-status')
    <span class="sv-badge {{ ($summary['total_logs'] ?? 0) > 0 ? 'sv-badge-success' : 'sv-badge-neutral' }}">
        <span class="sv-status-dot" aria-hidden="true"></span>
        {{ ($summary['total_logs'] ?? 0) > 0 ? number_format((int) $summary['total_logs'], 0, ',', '.') . ' data' : 'Belum ada data' }}
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
                </div>
                <div class="sv-form-field">
                    <label for="date_to" class="sv-form-label">Selesai</label>
                    <input type="date" id="date_to" name="date_to" class="sv-form-control" value="{{ $toDate }}" required>
                </div>
                <div class="sv-filter-actions">
                    <button type="submit" class="sv-button sv-button-primary">Terapkan</button>
                    <a href="{{ route('energy.history') }}" class="sv-button sv-button-ghost">Reset</a>
                </div>
            </form>

            <div class="sv-filter-footer">
                <div class="sv-quick-filters" aria-label="Pilihan periode cepat">
                    <button type="button" class="sv-quick-filter" data-date-range="today">Hari ini</button>
                    <button type="button" class="sv-quick-filter" data-date-range="7days">7 hari</button>
                    <button type="button" class="sv-quick-filter" data-date-range="month">Bulan ini</button>
                    <button type="button" class="sv-quick-filter" data-date-range="last-month">Bulan lalu</button>
                </div>
                <button type="button" class="sv-button sv-button-secondary sv-button-sm" data-export-url="{{ route('energy.history.export', request()->query()) }}" data-energy-export>
                    <x-icon name="download" :size="16" />
                    <span data-export-label>Unduh CSV</span>
                </button>
            </div>
        </div>
    </section>

    <section class="sv-history-metrics sv-history-metrics-three" aria-label="Ringkasan pemakaian listrik">
        <article class="sv-history-metric sv-history-metric--green">
            <x-feature-icon name="energy" tone="green" :size="20" variant="soft" />
            <div><span>Total Pemakaian</span><strong>{{ number_format((float) ($summary['usage_kwh'] ?? 0), 4, ',', '.') }} kWh</strong></div>
        </article>
        <article class="sv-history-metric sv-history-metric--amber">
            <x-feature-icon name="money" tone="amber" :size="20" variant="soft" />
            <div><span>Biaya Periode</span><strong>Rp{{ number_format((float) ($selectedEstimation['estimated_cost'] ?? (($summary['usage_kwh'] ?? 0) * ($electricityTariff ?? 0))), 0, ',', '.') }}</strong></div>
        </article>
        <article class="sv-history-metric sv-history-metric--blue">
            <x-feature-icon name="bolt" tone="blue" :size="20" variant="soft" />
            <div><span>Daya Tertinggi</span><strong>{{ number_format((float) ($summary['max_power'] ?? 0), 1, ',', '.') }} W</strong></div>
        </article>
    </section>

    <section class="sv-section-grid sv-section-grid-analysis">
        <article class="sv-card sv-history-chart-card">
            <header class="sv-card-header">
                <div>
                    <h2>Grafik Pemakaian</h2>
                    <p>{{ \Carbon\Carbon::parse($fromDate)->format('d/m/Y') }} – {{ \Carbon\Carbon::parse($toDate)->format('d/m/Y') }}</p>
                </div>
                <div class="sv-chart-tabs" role="tablist" aria-label="Jenis grafik">
                    <button type="button" class="sv-chart-tab is-active" data-history-chart-mode="power" aria-selected="true">Daya</button>
                    <button type="button" class="sv-chart-tab" data-history-chart-mode="energy" aria-selected="false">Energi</button>
                </div>
            </header>
            <div class="sv-card-body">
                <div class="sv-chart-meta-line">
                    <span>Rata-rata daya <strong>{{ number_format((float) ($summary['avg_power'] ?? 0), 1, ',', '.') }} W</strong></span>
                    <span>Rata-rata tegangan <strong>{{ number_format((float) ($summary['avg_voltage'] ?? 0), 1, ',', '.') }} V</strong></span>
                </div>

                <div class="sv-empty-state {{ collect($chartData['labels'] ?? [])->isNotEmpty() ? 'is-hidden' : '' }}" data-history-chart-empty>
                    <x-feature-icon name="chart" tone="green" :size="24" variant="soft" />
                    <h3>Tidak ada data pada periode ini</h3>
                    <p>Pilih periode lain atau periksa perangkat.</p>
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
                    <strong>Rp{{ number_format((float) ($selectedEstimation['estimated_cost'] ?? 0), 0, ',', '.') }}</strong>
                    <small>{{ number_format((float) ($selectedEstimation['usage_kwh'] ?? 0), 4, ',', '.') }} kWh</small>
                </div>

                @if(!empty($monthEstimation))
                    <dl class="sv-estimation-secondary">
                        <div><dt>{{ $monthEstimation['label'] ?? 'Bulan Ini' }}</dt><dd>Rp{{ number_format((float) ($monthEstimation['estimated_cost'] ?? 0), 0, ',', '.') }}</dd></div>
                        <div><dt>Periode</dt><dd>{{ $monthEstimation['period'] ?? '-' }}</dd></div>
                    </dl>
                @endif
            </div>
        </article>
    </section>

    <section class="sv-card">
        <header class="sv-card-header">
            <div>
                <h2>Riwayat Meter</h2>
                <p>Data terbaru setiap meter pada periode terpilih.</p>
            </div>
            <span class="sv-badge sv-badge-neutral">{{ $logs->total() }} meter</span>
        </header>
        <div class="sv-card-body">
            @if($logs->isEmpty())
                <div class="sv-empty-state sv-empty-state-compact">
                    <x-feature-icon name="document" tone="blue" :size="24" variant="soft" />
                    <h3>Data tidak ditemukan</h3>
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
                                    <td>{{ $log->observed_at?->format('d/m/Y H:i:s') ?? '-' }}</td>
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
        'exportUrl' => route('energy.history.export', request()->query()),
    ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) !!}</script>
@endsection

@push('scripts')
    <script src="{{ asset('assets/js/chart.umd.min.js') }}?v=4.5.1" defer></script>
    <script src="{{ asset('assets/js/smartvolt-energy-history.js') }}?v=20260731-v6" defer></script>
@endpush
