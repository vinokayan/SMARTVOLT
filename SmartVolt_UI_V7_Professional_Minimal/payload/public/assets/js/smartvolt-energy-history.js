(() => {
    'use strict';

    const dataNode = document.getElementById('smartvolt-history-data');
    if (!dataNode || !window.SmartVolt) {
        return;
    }

    let config = {};
    try {
        config = JSON.parse(dataNode.textContent || '{}');
    } catch {
        window.SmartVolt.toast('Konfigurasi riwayat tidak dapat dibaca.', {type: 'error'});
        return;
    }

    const pad = (value) => String(value).padStart(2, '0');
    const dateString = (date) => `${date.getFullYear()}-${pad(date.getMonth() + 1)}-${pad(date.getDate())}`;

    document.querySelectorAll('[data-date-range]').forEach((button) => {
        button.addEventListener('click', () => {
            const from = document.getElementById('date_from');
            const to = document.getElementById('date_to');
            if (!(from instanceof HTMLInputElement) || !(to instanceof HTMLInputElement)) {
                return;
            }

            const now = new Date();
            const start = new Date(now.getFullYear(), now.getMonth(), now.getDate());
            const end = new Date(start);

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
            from.form?.requestSubmit();
        });
    });

    const chartData = config.chart || {labels: [], power: [], energy: []};
    const chartCanvas = document.getElementById('energyHistoryChart');
    const chartContainer = document.querySelector('[data-history-chart-container]');
    const chartEmpty = document.querySelector('[data-history-chart-empty]');
    let mode = 'power';
    let chart = null;

    const number = (value, digits = 0) => Number(value || 0).toLocaleString('id-ID', {
        minimumFractionDigits: digits,
        maximumFractionDigits: digits,
    });

    const render = () => {
        const labels = Array.isArray(chartData.labels) ? chartData.labels : [];
        const values = Array.isArray(chartData[mode]) ? chartData[mode].map((value) => Number(value || 0)) : [];
        const hasData = labels.length > 0 && values.length > 0;
        chartContainer?.classList.toggle('is-hidden', !hasData);
        chartEmpty?.classList.toggle('is-hidden', hasData);

        if (!hasData || !(chartCanvas instanceof HTMLCanvasElement) || typeof window.Chart !== 'function') {
            chart?.destroy();
            chart = null;
            return;
        }

        const label = mode === 'power' ? 'Daya' : 'Energi kumulatif';
        const unit = mode === 'power' ? 'W' : 'kWh';

        chart?.destroy();
        chart = new window.Chart(chartCanvas, {
            type: 'line',
            data: {
                labels,
                datasets: [{
                    label,
                    data: values,
                    borderColor: '#2563eb',
                    backgroundColor: 'rgba(37, 99, 235, 0.08)',
                    borderWidth: 2,
                    tension: 0.28,
                    pointRadius: 3,
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
                    tooltip: {callbacks: {label: (context) => `${label}: ${number(context.parsed.y, mode === 'power' ? 1 : 4)} ${unit}`}},
                },
                scales: {
                    x: {grid: {display: false}, ticks: {color: '#7a8699', font: {size: 10}}},
                    y: {beginAtZero: true, grid: {color: '#edf1f5'}, ticks: {color: '#7a8699', font: {size: 10}}},
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
            render();
        });
    });

    const csvEscape = (value) => {
        const text = String(value ?? '');
        return `"${text.replaceAll('"', '""')}"`;
    };

    const downloadCsv = (rows) => {
        if (!Array.isArray(rows) || rows.length === 0) {
            throw new Error('Tidak ada data yang dapat diekspor.');
        }
        const headers = Object.keys(rows[0]);
        const lines = [
            headers.map(csvEscape).join(','),
            ...rows.map((row) => headers.map((header) => csvEscape(row[header])).join(',')),
        ];
        const blob = new Blob([`\uFEFF${lines.join('\r\n')}`], {type: 'text/csv;charset=utf-8'});
        const url = URL.createObjectURL(blob);
        const link = document.createElement('a');
        link.href = url;
        link.download = `smartvolt-pemakaian-${dateString(new Date())}.csv`;
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
                const rows = await window.SmartVolt.fetchJson(button.dataset.exportUrl || config.exportUrl, {method: 'GET'}, 15000);
                downloadCsv(rows);
                window.SmartVolt.toast('Data pemakaian berhasil diunduh dalam format CSV.', {type: 'success'});
            } catch (error) {
                window.SmartVolt.toast(error.message || 'Data tidak dapat diekspor.', {type: 'error'});
            } finally {
                button.disabled = false;
                if (label) {
                    label.textContent = 'Unduh CSV';
                }
            }
        });
    });

    render();
})();
