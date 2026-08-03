(() => {
    'use strict';

    const dataNode = document.getElementById('smartvolt-dashboard-data');
    if (!dataNode || !window.SmartVolt) {
        return;
    }

    let config = {};
    try {
        config = JSON.parse(dataNode.textContent || '{}');
    } catch {
        window.SmartVolt.toast('Dashboard tidak dapat dimuat.', {type: 'error'});
        return;
    }

    const formatNumber = (value, digits = 0) => Number(value || 0).toLocaleString('id-ID', {
        minimumFractionDigits: digits,
        maximumFractionDigits: digits,
    });

    const setText = (selector, value) => {
        document.querySelectorAll(selector).forEach((element) => {
            element.textContent = String(value);
        });
    };

    const toNumbers = (values) => Array.isArray(values)
        ? values.map((value) => Number(value || 0))
        : [];

    const chartCanvas = document.getElementById('dashboardEnergyChart');
    const chartContainer = document.querySelector('[data-dashboard-chart-container]');
    const chartEmpty = document.querySelector('[data-dashboard-chart-empty]');

    let chartMode = 'power';
    let chartInstance = null;
    let dashboardChart = config.chart || {labels: [], power: [], energy: []};

    const currentChartValues = () => toNumbers(dashboardChart[chartMode]);

    const updateChartSummary = () => {
        const values = currentChartValues();
        const unit = chartMode === 'power' ? 'W' : 'kWh';
        const digits = chartMode === 'power' ? 1 : 4;
        const average = values.length
            ? values.reduce((total, value) => total + value, 0) / values.length
            : 0;
        const maximum = values.length ? Math.max(...values) : 0;
        const current = values.length ? values[values.length - 1] : 0;

        setText('[data-chart-current]', formatNumber(current, digits));
        setText('[data-chart-average]', formatNumber(average, digits));
        setText('[data-chart-maximum]', formatNumber(maximum, digits));
        setText('[data-chart-unit], [data-chart-average-unit], [data-chart-maximum-unit]', unit);
    };

    const renderChart = () => {
        const labels = Array.isArray(dashboardChart.labels) ? dashboardChart.labels : [];
        const values = currentChartValues();
        const hasData = labels.length > 0 && values.length > 0;

        chartContainer?.classList.toggle('is-hidden', !hasData);
        chartEmpty?.classList.toggle('is-hidden', hasData);
        updateChartSummary();

        if (!hasData || !(chartCanvas instanceof HTMLCanvasElement) || typeof window.Chart !== 'function') {
            chartInstance?.destroy();
            chartInstance = null;
            return;
        }

        const datasetLabel = chartMode === 'power' ? 'Daya' : 'Energi';
        const unit = chartMode === 'power' ? 'W' : 'kWh';
        const borderColor = chartMode === 'power' ? '#1267e8' : '#0aa5b8';
        const backgroundColor = chartMode === 'power'
            ? 'rgba(18, 103, 232, 0.10)'
            : 'rgba(10, 165, 184, 0.10)';

        if (chartInstance) {
            chartInstance.data.labels = labels;
            chartInstance.data.datasets[0].label = datasetLabel;
            chartInstance.data.datasets[0].data = values;
            chartInstance.data.datasets[0].borderColor = borderColor;
            chartInstance.data.datasets[0].backgroundColor = backgroundColor;
            chartInstance.options.scales.y.title.text = unit;
            chartInstance.update('none');
            return;
        }

        chartInstance = new window.Chart(chartCanvas, {
            type: 'line',
            data: {
                labels,
                datasets: [{
                    label: datasetLabel,
                    data: values,
                    borderColor,
                    backgroundColor,
                    borderWidth: 2.2,
                    pointRadius: labels.length > 18 ? 0 : 2.5,
                    pointHoverRadius: 4,
                    pointBackgroundColor: '#ffffff',
                    pointBorderColor: borderColor,
                    pointBorderWidth: 2,
                    fill: true,
                    tension: 0.34,
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
                        padding: 10,
                        callbacks: {
                            label: (context) => `${datasetLabel}: ${formatNumber(context.parsed.y, chartMode === 'power' ? 1 : 4)} ${unit}`,
                        },
                    },
                },
                scales: {
                    x: {
                        grid: {display: false},
                        border: {display: false},
                        ticks: {color: '#8390a4', maxTicksLimit: 8, font: {size: 10}},
                    },
                    y: {
                        beginAtZero: true,
                        border: {display: false},
                        grid: {color: '#edf1f5'},
                        ticks: {color: '#8390a4', font: {size: 10}},
                        title: {display: true, text: unit, color: '#68758a', font: {size: 10}},
                    },
                },
            },
        });
    };

    document.querySelectorAll('[data-dashboard-chart-mode]').forEach((button) => {
        button.addEventListener('click', () => {
            chartMode = button.dataset.dashboardChartMode === 'energy' ? 'energy' : 'power';

            document.querySelectorAll('[data-dashboard-chart-mode]').forEach((item) => {
                const active = item === button;
                item.classList.toggle('is-active', active);
                item.setAttribute('aria-selected', active ? 'true' : 'false');
            });

            renderChart();
        });
    });

    const loadLabel = (status) => status === 'danger'
        ? 'Beban tinggi'
        : status === 'warning'
            ? 'Mendekati batas'
            : 'Penggunaan normal';

    const updateStatusClass = (element, state) => {
        if (!element) {
            return;
        }
        element.classList.remove('is-success', 'is-warning', 'is-danger');
        element.classList.add(state);
    };

    const updateDashboard = (data) => {
        const stats = data.stats || {};
        const system = data.system || {};

        setText('[data-stat-current-power]', formatNumber(stats.current_power, 1));
        setText('[data-stat-energy-today]', formatNumber(stats.total_energy_today, 3));
        setText('[data-stat-monthly-cost]', formatNumber(stats.monthly_estimated_cost, 0));
        setText('[data-stat-monthly-energy]', formatNumber(stats.monthly_energy_usage, 3));
        setText('[data-stat-active-devices]', Number(stats.active_devices || 0));
        setText('[data-stat-total-devices]', Number(stats.total_devices || 0));
        setText('[data-stat-online-active-devices]', Number(stats.online_active_devices || 0));
        setText('[data-stat-load-percentage]', formatNumber(stats.load_percentage, 0));

        const progress = document.querySelector('[data-load-progress]');
        if (progress) {
            progress.style.setProperty('--sv-progress', `${Math.min(100, Math.max(0, Number(stats.load_percentage || 0)))}%`);
            progress.classList.toggle('is-warning', stats.load_status === 'warning');
            progress.classList.toggle('is-danger', stats.load_status === 'danger');
        }

        const loadBadge = document.querySelector('[data-load-status-badge]');
        if (loadBadge) {
            loadBadge.classList.remove('is-success', 'is-warning', 'is-danger');
            loadBadge.classList.add(
                stats.load_status === 'danger'
                    ? 'is-danger'
                    : stats.load_status === 'warning'
                        ? 'is-warning'
                        : 'is-success'
            );
        }
        setText('[data-load-status-label]', loadLabel(stats.load_status));

        const comparison = Number(stats.energy_comparison_percent);
        setText(
            '[data-energy-comparison]',
            Number.isFinite(comparison)
                ? `${comparison > 0 ? 'Lebih tinggi' : comparison < 0 ? 'Lebih hemat' : 'Sama'} ${formatNumber(Math.abs(comparison), 1)}% dari kemarin`
                : 'Perbandingan belum tersedia'
        );

        setText('[data-system-esp-label]', system.esp_online ? `${Number(system.online_esp_count || 0)} ESP32 Terhubung` : 'ESP32 Belum Terhubung');
        setText('[data-system-pzem-label]', system.pzem_status || 'Belum tersedia');
        setText('[data-system-control-label]', system.control_channel_status || 'Menunggu perangkat');
        setText('[data-system-latest-label]', system.latest_received_human || 'Belum ada data');

        updateStatusClass(document.querySelector('[data-system-esp]'), system.esp_online ? 'is-success' : 'is-warning');
        updateStatusClass(document.querySelector('[data-system-pzem]'), system.has_fresh_data ? 'is-success' : 'is-warning');
        updateStatusClass(document.querySelector('[data-system-control]'), system.esp_online ? 'is-success' : 'is-warning');
        updateStatusClass(document.querySelector('[data-system-latest]'), system.has_data ? 'is-success' : 'is-warning');

        const headerBadge = document.querySelector('[data-dashboard-system-badge]');
        if (headerBadge) {
            headerBadge.classList.remove('sv-badge-success', 'sv-badge-warning', 'sv-badge-neutral');
            headerBadge.classList.add(system.has_fresh_data ? 'sv-badge-success' : system.has_data ? 'sv-badge-warning' : 'sv-badge-neutral');
        }
        setText('[data-dashboard-system-label]', system.has_fresh_data ? 'Sistem Terhubung' : system.has_data ? 'Data Terlambat' : 'Belum Terhubung');

        const intro = stats.load_status === 'danger'
            ? 'Beban listrik tinggi. Matikan perangkat yang tidak diperlukan.'
            : stats.load_status === 'warning'
                ? 'Beban listrik mulai mendekati batas rumah.'
                : system.has_fresh_data
                    ? 'Sistem kelistrikan rumah Anda dalam kondisi normal.'
                    : 'Menunggu data terbaru dari perangkat SmartVolt.';
        setText('[data-dashboard-intro-message]', intro);

        dashboardChart = data.chart || dashboardChart;
        renderChart();
    };

    const setSwitchState = (button, isOn, online = true) => {
        button.dataset.currentState = isOn ? 'on' : 'off';
        button.classList.toggle('is-on', isOn);
        button.classList.toggle('is-offline', !online);
        button.setAttribute('aria-pressed', isOn ? 'true' : 'false');

        const label = button.querySelector('[data-switch-label]');
        if (label) {
            label.textContent = online ? (isOn ? 'Nyala' : 'Mati') : 'Offline';
        }
        button.disabled = !online;
    };

    document.addEventListener('click', async (event) => {
        const button = event.target.closest('[data-device-toggle]');
        if (!(button instanceof HTMLButtonElement) || button.disabled || button.classList.contains('is-loading')) {
            return;
        }

        const originalState = button.dataset.currentState === 'on';
        const label = button.querySelector('[data-switch-label]');
        button.classList.add('is-loading');
        button.disabled = true;
        if (label) {
            label.textContent = 'Mengirim';
        }

        try {
            const response = await window.SmartVolt.fetchJson(button.dataset.url, {method: 'POST'});
            const nextState = response.status === 'on' || response.status === true;
            setSwitchState(button, nextState, response.esp_online !== false);
            window.SmartVolt.toast(response.message || 'Status perangkat diperbarui.', {type: 'success'});

            const roomId = button.dataset.roomId;
            if (roomId) {
                const activeInRoom = document.querySelectorAll(`[data-room-id="${roomId}"] [data-device-toggle].is-on`).length;
                setText(`[data-room-active-count="${roomId}"]`, activeInRoom);
            }
        } catch (error) {
            setSwitchState(button, originalState, error.status !== 409);
            window.SmartVolt.toast(error.message || 'Perintah perangkat tidak berhasil.', {type: 'error'});
        } finally {
            button.classList.remove('is-loading');
            if (!button.classList.contains('is-offline')) {
                button.disabled = false;
            }
        }
    });

    let refreshInProgress = false;
    const refreshDashboard = async () => {
        if (refreshInProgress || document.hidden || !config.endpoint) {
            return;
        }

        refreshInProgress = true;
        try {
            const data = await window.SmartVolt.fetchJson(config.endpoint, {method: 'GET'}, 10000);
            updateDashboard(data);
        } catch (error) {
            console.warn('Pembaruan Dashboard gagal:', error.message);
        } finally {
            refreshInProgress = false;
        }
    };

    renderChart();

    const intervalSeconds = Math.min(60, Math.max(10, Number(config.refreshInterval || 30)));
    window.setInterval(refreshDashboard, intervalSeconds * 1000);
})();
