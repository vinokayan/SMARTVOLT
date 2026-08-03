@extends('layouts.app')

@section('title', 'Pemakaian Listrik')
@section('page-title', 'Pemakaian Listrik')
@section('page-subtitle', 'Lihat riwayat, grafik, dan estimasi biaya listrik.')
@section('body-class', 'sv-energy-history-page')

@php
    $meterCollection = collect($devices ?? []);
    $paymentCollection = collect($paymentEstimations ?? []);
    $chartData = $chart ?? ['labels' => [], 'power' => [], 'energy' => []];
    $selectedMeterId = $filters['meter_id'] ?? null;
    $fromDate = $filters['date_from'] ?? now()->toDateString();
    $toDate = $filters['date_to'] ?? now()->toDateString();
@endphp

@section('system-status')
    <span class="sv-badge {{ ($summary['total_logs'] ?? 0) > 0 ? 'sv-badge-success' : 'sv-badge-neutral' }}">
        <span aria-hidden="true">●</span>
        {{ ($summary['total_logs'] ?? 0) > 0 ? number_format((int) $summary['total_logs'], 0, ',', '.') . ' telemetry' : 'Belum ada data' }}
    </span>
@endsection

@section('content')
    <div class="sv-page-heading">
        <div class="sv-page-heading-copy">
            <h2>Riwayat dan analisis</h2>
            <p>Menampilkan data periode {{ \Carbon\Carbon::parse($fromDate)->format('d/m/Y') }} sampai {{ \Carbon\Carbon::parse($toDate)->format('d/m/Y') }}.</p>
        </div>
        <div class="sv-page-heading-actions">
            <button type="button" class="sv-button sv-button-secondary" data-export-url="{{ route('energy.history.export', request()->query()) }}" data-energy-export>
                <x-icon name="download" :size="17" />
                <span data-export-label>Unduh CSV</span>
            </button>
        </div>
    </div>

    <section class="sv-card sv-filter-card">
        <div class="sv-card-body">
            <form action="{{ route('energy.history') }}" method="GET" class="sv-filter-grid">
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
                    <label for="date_from" class="sv-form-label">Tanggal mulai</label>
                    <input type="date" id="date_from" name="date_from" class="sv-form-control" value="{{ $fromDate }}" required>
                </div>
                <div class="sv-form-field">
                    <label for="date_to" class="sv-form-label">Tanggal selesai</label>
                    <input type="date" id="date_to" name="date_to" class="sv-form-control" value="{{ $toDate }}" required>
                </div>
                <div class="sv-filter-actions sv-inline">
                    <button type="submit" class="sv-button sv-button-primary"><x-icon name="filter" :size="16" /> Terapkan</button>
                    <a href="{{ route('energy.history') }}" class="sv-button sv-button-ghost">Atur ulang</a>
                </div>
            </form>

            <div class="sv-quick-filters" aria-label="Pilihan periode cepat">
                <button type="button" class="sv-quick-filter" data-date-range="today">Hari ini</button>
                <button type="button" class="sv-quick-filter" data-date-range="7days">7 hari</button>
                <button type="button" class="sv-quick-filter" data-date-range="month">Bulan ini</button>
                <button type="button" class="sv-quick-filter" data-date-range="last-month">Bulan lalu</button>
            </div>
        </div>
    </section>

    <section class="sv-history-metrics" aria-label="Ringkasan pemakaian listrik">
        <article class="sv-history-metric is-primary">
            <span>Total Pemakaian</span>
            <strong>{{ number_format((float) ($summary['usage_kwh'] ?? 0), 4, ',', '.') }} kWh</strong>
            <small>Pemakaian pada periode yang dipilih.</small>
        </article>
        <article class="sv-history-metric">
            <span>Estimasi Pembayaran</span>
            @php
                $selectedEstimation = $paymentCollection->get('selected') ?? $paymentCollection->get('today') ?? [];
            @endphp
            <strong>Rp{{ number_format((float) ($selectedEstimation['estimated_cost'] ?? (($summary['usage_kwh'] ?? 0) * ($electricityTariff ?? 0))), 0, ',', '.') }}</strong>
            <small>Tarif Rp{{ number_format((float) ($electricityTariff ?? 0), 0, ',', '.') }}/kWh.</small>
        </article>
        <article class="sv-history-metric">
            <span>Daya Tertinggi</span>
            <strong>{{ number_format((float) ($summary['max_power'] ?? 0), 1, ',', '.') }} W</strong>
            <small>Rata-rata {{ number_format((float) ($summary['avg_power'] ?? 0), 1, ',', '.') }} W.</small>
        </article>
        <article class="sv-history-metric">
            <span>Rata-rata Tegangan</span>
            <strong>{{ number_format((float) ($summary['avg_voltage'] ?? 0), 1, ',', '.') }} V</strong>
            <small>Data terakhir {{ $summary['latest_time'] ?? 'belum tersedia' }}.</small>
        </article>
    </section>

    <section class="sv-section-grid sv-section-grid-two">
        <article class="sv-card">
            <header class="sv-card-header">
                <div>
                    <h2>Analisis Pemakaian</h2>
                    <p>Data sesuai meter dan periode yang dipilih.</p>
                </div>
                <div class="sv-chart-tabs" role="tablist" aria-label="Jenis grafik">
                    <button type="button" class="sv-chart-tab is-active" data-history-chart-mode="power" aria-selected="true">Daya</button>
                    <button type="button" class="sv-chart-tab" data-history-chart-mode="energy" aria-selected="false">Energi</button>
                </div>
            </header>
            <div class="sv-card-body">
                @if(collect($chartData['labels'] ?? [])->isEmpty())
                    <div class="sv-empty-state" data-history-chart-empty>
                        <x-icon name="chart" :size="30" />
                        <h3>Belum ada data pada periode ini</h3>
                        <p>Pilih periode lain atau periksa koneksi perangkat.</p>
                    </div>
                @endif
                <div class="sv-chart-container {{ collect($chartData['labels'] ?? [])->isEmpty() ? 'is-hidden' : '' }}" data-history-chart-container>
                    <canvas id="energyHistoryChart" aria-label="Grafik riwayat pemakaian listrik"></canvas>
                </div>
            </div>
        </article>

        <article class="sv-card">
            <header class="sv-card-header">
                <div>
                    <h2>Estimasi Pembayaran</h2>
                    <p>Menggunakan tarif yang tersimpan di Pengaturan.</p>
                </div>
            </header>
            <div class="sv-card-body">
                <div class="sv-billing-list">
                    @forelse($paymentCollection as $key => $estimation)
                        <details class="sv-billing-item" {{ $loop->first ? 'open' : '' }}>
                            <summary>
                                <strong>{{ $estimation['label'] ?? ucfirst((string) $key) }}</strong>
                                <span>Rp{{ number_format((float) ($estimation['estimated_cost'] ?? 0), 0, ',', '.') }}</span>
                            </summary>
                            <div class="sv-billing-details">
                                <dl>
                                    <dt>Periode</dt><dd>{{ $estimation['period'] ?? '-' }}</dd>
                                    <dt>Pemakaian</dt><dd>{{ number_format((float) ($estimation['usage_kwh'] ?? 0), 4, ',', '.') }} kWh</dd>
                                    <dt>Tarif</dt><dd>Rp{{ number_format((float) ($estimation['tariff'] ?? $electricityTariff ?? 0), 0, ',', '.') }}/kWh</dd>
                                    <dt>Rumus</dt><dd>{{ $estimation['formula'] ?? '-' }}</dd>
                                </dl>
                            </div>
                        </details>
                    @empty
                        <div class="sv-empty-state"><p>Estimasi pembayaran belum tersedia.</p></div>
                    @endforelse
                </div>
            </div>
        </article>
    </section>

    <section class="sv-card">
        <header class="sv-card-header">
            <div>
                <h2>Riwayat Pemakaian Listrik</h2>
                <p>Satu pembacaan terbaru untuk setiap meter pada periode filter.</p>
            </div>
            <span class="sv-badge sv-badge-neutral">{{ $logs->total() }} meter</span>
        </header>
        <div class="sv-card-body">
            @if($logs->isEmpty())
                <div class="sv-empty-state">
                    <x-icon name="document" :size="30" />
                    <h3>Data riwayat tidak ditemukan</h3>
                    <p>Tidak ada pembacaan meter yang sesuai dengan filter saat ini.</p>
                </div>
            @else
                <div class="sv-table-wrap sv-table-history-desktop">
                    <table class="sv-table">
                        <thead>
                            <tr>
                                <th>Ruangan</th>
                                <th>Meter</th>
                                <th>Waktu Data</th>
                                <th class="is-numeric">Tegangan</th>
                                <th class="is-numeric">Arus</th>
                                <th class="is-numeric">Daya</th>
                                <th class="is-numeric">Energi</th>
                                <th>Status</th>
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
                                    <td><span class="sv-badge sv-badge-success">Terbaru</span></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="sv-history-mobile-list" aria-label="Riwayat pemakaian listrik versi mobile">
                    @foreach($logs as $log)
                        <article class="sv-history-mobile-card">
                            <div class="sv-history-mobile-head">
                                <div>
                                    <strong>{{ $log->room_name ?? 'Tanpa ruangan' }}</strong>
                                    <span>{{ $log->meter_name ?? $log->device_name ?? '-' }}</span>
                                </div>
                                <span>{{ $log->observed_at?->format('d/m/Y H:i') ?? '-' }}</span>
                            </div>
                            <div class="sv-history-mobile-grid">
                                <div><small>Tegangan</small><strong>{{ number_format((float) ($log->voltage ?? 0), 1, ',', '.') }} V</strong></div>
                                <div><small>Arus</small><strong>{{ number_format((float) ($log->current ?? 0), 3, ',', '.') }} A</strong></div>
                                <div><small>Daya</small><strong>{{ number_format((float) ($log->power ?? 0), 1, ',', '.') }} W</strong></div>
                                <div><small>Energi</small><strong>{{ number_format((float) ($log->energy ?? 0), 4, ',', '.') }} kWh</strong></div>
                            </div>
                        </article>
                    @endforeach
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
    <script src="{{ asset('assets/js/smartvolt-energy-history.js') }}?v=20260727" defer></script>
@endpush
