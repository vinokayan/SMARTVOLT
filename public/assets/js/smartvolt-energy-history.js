(() => {
    'use strict';

    const dataNode = document.getElementById('smartvolt-history-data');

    if (!dataNode || !window.SmartVolt || window.__smartVoltEnergyHistoryInitialized) {
        return;
    }

    window.__smartVoltEnergyHistoryInitialized = true;

    let config = {};

    try {
        config = JSON.parse(dataNode.textContent || '{}');
    } catch {
        window.SmartVolt.toast('Halaman pemakaian listrik tidak dapat dimuat.', {type: 'error'});
        return;
    }

    const monthNames = [
        'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
        'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember',
    ];

    const pad = (value) => String(value).padStart(2, '0');
    const dateString = (date) => `${date.getFullYear()}-${pad(date.getMonth() + 1)}-${pad(date.getDate())}`;

    const parseDateString = (value) => {
        const match = String(value || '').match(/^(\d{4})-(\d{2})-(\d{2})$/);

        if (!match) {
            const now = new Date();
            return new Date(now.getFullYear(), now.getMonth(), now.getDate());
        }

        return new Date(Number(match[1]), Number(match[2]) - 1, Number(match[3]));
    };

    const readableDate = (value) => {
        const date = parseDateString(value);
        return `${date.getDate()} ${monthNames[date.getMonth()]} ${date.getFullYear()}`;
    };

    const updateReadableDate = (input) => {
        if (!(input instanceof HTMLInputElement)) {
            return;
        }

        document.querySelectorAll(`[data-readable-date="${CSS.escape(input.id)}"]`).forEach((element) => {
            element.textContent = readableDate(input.value);
        });
    };

    ['date_from', 'date_to'].forEach((id) => {
        const input = document.getElementById(id);
        if (input instanceof HTMLInputElement) {
            updateReadableDate(input);
            input.addEventListener('change', () => updateReadableDate(input));
        }
    });

    document.querySelectorAll('[data-date-range]').forEach((button) => {
        button.addEventListener('click', () => {
            const from = document.getElementById('date_from');
            const to = document.getElementById('date_to');

            if (!(from instanceof HTMLInputElement) || !(to instanceof HTMLInputElement)) {
                return;
            }

            const today = parseDateString(config.today);
            const start = new Date(today);
            const end = new Date(today);

            switch (button.dataset.dateRange) {
                case '7days':
                    start.setDate(start.getDate() - 6);
                    break;
                case 'month':
                    start.setDate(1);
                    break;
                case 'last-month':
                    start.setMonth(start.getMonth() - 1, 1);
                    end.setDate(0);
                    break;
                default:
                    break;
            }

            from.value = dateString(start);
            to.value = dateString(end);
            updateReadableDate(from);
            updateReadableDate(to);
            from.form?.requestSubmit();
        });
    });

    const chartData = config.chart || {labels: [], power: [], energy: []};
    const chartCanvas = document.getElementById('energyHistoryChart');
    const chartContainer = document.querySelector('[data-history-chart-container]');
    const chartEmpty = document.querySelector('[data-history-chart-empty]');
    let mode = 'power';
    let chart = null;

    const formatNumber = (value, digits = 0) => Number(value ?? 0).toLocaleString('id-ID', {
        minimumFractionDigits: digits,
        maximumFractionDigits: digits,
    });

    const renderChart = () => {
        const labels = Array.isArray(chartData.labels) ? chartData.labels : [];
        const values = Array.isArray(chartData[mode])
            ? chartData[mode].map((value) => Number(value ?? 0))
            : [];
        const hasData = labels.length > 0 && values.length > 0;

        chartContainer?.classList.toggle('is-hidden', !hasData);
        chartEmpty?.classList.toggle('is-hidden', hasData);

        if (!hasData || !(chartCanvas instanceof HTMLCanvasElement) || typeof window.Chart !== 'function') {
            chart?.destroy();
            chart = null;
            return;
        }

        const datasetLabel = mode === 'power' ? 'Daya' : 'Energi';
        const unit = mode === 'power' ? 'W' : 'kWh';
        const digits = mode === 'power' ? 1 : 4;
        const borderColor = mode === 'power' ? '#2563eb' : '#16a34a';
        const backgroundColor = mode === 'power'
            ? 'rgba(37, 99, 235, 0.08)'
            : 'rgba(22, 163, 74, 0.08)';

        chart?.destroy();
        chart = new window.Chart(chartCanvas, {
            type: 'line',
            data: {
                labels,
                datasets: [{
                    label: datasetLabel,
                    data: values,
                    borderColor,
                    backgroundColor,
                    borderWidth: 2,
                    tension: 0.28,
                    pointRadius: labels.length > 48 ? 0 : 3,
                    pointHoverRadius: 5,
                    fill: true,
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: {mode: 'index', intersect: false},
                plugins: {
                    legend: {display: false},
                    tooltip: {
                        displayColors: false,
                        callbacks: {
                            label: (context) => `${datasetLabel}: ${formatNumber(context.parsed.y, digits)} ${unit}`,
                        },
                    },
                },
                scales: {
                    x: {
                        grid: {display: false},
                        ticks: {color: '#7a8699', font: {size: 10}, maxTicksLimit: 10},
                    },
                    y: {
                        beginAtZero: true,
                        grid: {color: '#edf1f5'},
                        ticks: {color: '#7a8699', font: {size: 10}},
                    },
                },
            },
        });
    };

    document.querySelectorAll('[data-history-chart-mode]').forEach((button) => {
        button.addEventListener('click', () => {
            mode = button.dataset.historyChartMode === 'energy' ? 'energy' : 'power';

            document.querySelectorAll('[data-history-chart-mode]').forEach((item) => {
                const active = item === button;
                item.classList.toggle('is-active', active);
                item.setAttribute('aria-selected', active ? 'true' : 'false');
            });

            renderChart();
        });
    });

    const updateSystemStatus = (system = {}) => {
        const connected = Boolean(system.connected ?? system.esp_online);
        const badge = document.querySelector('[data-energy-system-badge]');

        if (badge) {
            badge.classList.remove('sv-badge-success', 'sv-badge-warning', 'sv-badge-neutral');
            badge.classList.add(connected ? 'sv-badge-success' : 'sv-badge-neutral');
        }

        document.querySelectorAll('[data-energy-system-label]').forEach((element) => {
            element.textContent = connected ? 'Terhubung' : 'Belum terhubung';
        });
    };

    let statusRefreshInProgress = false;

    const refreshSystemStatus = async () => {
        if (statusRefreshInProgress || document.hidden || !config.statusEndpoint) {
            return;
        }

        statusRefreshInProgress = true;

        try {
            const data = await window.SmartVolt.fetchJson(
                config.statusEndpoint,
                {method: 'GET'},
                10000
            );
            updateSystemStatus(data.system || {});
        } catch (error) {
            console.warn('Status SmartVolt tidak dapat diperbarui:', error.message);
        } finally {
            statusRefreshInProgress = false;
        }
    };

    const csvEscape = (value) => {
        const text = String(value ?? '');
        return `"${text.replaceAll('"', '""')}"`;
    };

    const downloadCsv = (rows) => {
        if (!Array.isArray(rows) || rows.length === 0) {
            throw new Error('Tidak ada data yang dapat diunduh.');
        }

        const headers = Object.keys(rows[0]);
        const lines = [
            headers.map(csvEscape).join(','),
            ...rows.map((row) => headers.map((header) => csvEscape(row[header])).join(',')),
        ];

        const blob = new Blob([`\uFEFF${lines.join('\r\n')}`], {type: 'text/csv;charset=utf-8'});
        const url = URL.createObjectURL(blob);
        const link = document.createElement('a');
        const fromDate = String(config.dateFrom || config.today || 'periode');
        const toDate = String(config.dateTo || fromDate);
        const period = fromDate === toDate ? fromDate : `${fromDate}_sampai_${toDate}`;

        link.href = url;
        link.download = `smartvolt-pemakaian-${period}.csv`;
        document.body.appendChild(link);
        link.click();
        link.remove();
        URL.revokeObjectURL(url);
    };

    document.querySelectorAll('[data-energy-export]').forEach((button) => {
        button.addEventListener('click', async () => {
            if (button.disabled) {
                return;
            }

            button.disabled = true;
            const label = button.querySelector('[data-export-label]');
            if (label) {
                label.textContent = 'Menyiapkan...';
            }

            try {
                const rows = await window.SmartVolt.fetchJson(
                    button.dataset.exportUrl || config.exportUrl,
                    {method: 'GET'},
                    15000
                );
                downloadCsv(rows);
                window.SmartVolt.toast('Data pemakaian berhasil diunduh.', {type: 'success'});
            } catch (error) {
                window.SmartVolt.toast(
                    error.message || 'Data tidak dapat diunduh.',
                    {type: 'error'}
                );
            } finally {
                button.disabled = false;
                if (label) {
                    label.textContent = 'Unduh CSV';
                }
            }
        });
    });

    updateSystemStatus(config.system || {});
    renderChart();

    const intervalSeconds = Math.min(
        60,
        Math.max(10, Number(config.refreshInterval || 30))
    );

    window.setInterval(refreshSystemStatus, intervalSeconds * 1000);
})();
