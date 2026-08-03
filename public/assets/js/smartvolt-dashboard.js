(() => {
    'use strict';

    const dataNode = document.getElementById('smartvolt-dashboard-data');

    if (!dataNode || !window.SmartVolt || window.__smartVoltDashboardInitialized) {
        return;
    }

    window.__smartVoltDashboardInitialized = true;

    let config = {};

    try {
        config = JSON.parse(dataNode.textContent || '{}');
    } catch {
        window.SmartVolt.toast('Beranda tidak dapat dimuat.', {type: 'error'});
        return;
    }

    const pendingStates = new Map();
    const activeRequests = new Set();
    const syncTimers = new Map();

    const formatNumber = (value, digits = 0) => Number(value ?? 0).toLocaleString('id-ID', {
        minimumFractionDigits: digits,
        maximumFractionDigits: digits,
    });

    const setText = (selector, value) => {
        document.querySelectorAll(selector).forEach((element) => {
            element.textContent = String(value);
        });
    };

    const setHidden = (selector, hidden) => {
        document.querySelectorAll(selector).forEach((element) => {
            element.hidden = Boolean(hidden);
        });
    };

    const activeCountLabel = (available, count) => {
        const total = Number(count ?? 0);
        return available && total > 0 ? total : '—';
    };

    const clearTimers = (deviceId) => {
        const key = String(deviceId);
        const timers = syncTimers.get(key) || [];
        timers.forEach((timer) => window.clearTimeout(timer));
        syncTimers.delete(key);
    };

    const chartCanvas = document.getElementById('dashboardEnergyChart');
    const chartContainer = document.querySelector('[data-dashboard-chart-container]');
    const chartEmpty = document.querySelector('[data-dashboard-chart-empty]');
    let chartMode = 'power';
    let chartInstance = null;
    let dashboardChart = config.chart || {labels: [], power: [], energy: []};

    const currentChartValues = () => {
        const values = dashboardChart[chartMode];
        return Array.isArray(values) ? values.map((value) => Number(value ?? 0)) : [];
    };

    const updateChartSummary = () => {
        const values = currentChartValues();
        const unit = chartMode === 'power' ? 'W' : 'kWh';
        const digits = chartMode === 'power' ? 1 : 4;
        const latest = values.length ? values.at(-1) : 0;
        const average = values.length
            ? values.reduce((sum, value) => sum + value, 0) / values.length
            : 0;
        const maximum = values.length ? Math.max(...values) : 0;

        setText('[data-chart-current]', formatNumber(latest, digits));
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

        setText('[data-chart-title]', dashboardChart.title || 'Riwayat Daya Terakhir');
        setText(
            '[data-chart-subtitle]',
            dashboardChart.subtitle || 'Menampilkan perubahan daya dalam 24 jam terakhir.'
        );

        if (!hasData || !(chartCanvas instanceof HTMLCanvasElement) || typeof window.Chart !== 'function') {
            chartInstance?.destroy();
            chartInstance = null;
            return;
        }

        const datasetLabel = chartMode === 'power' ? 'Daya' : 'Energi';
        const unit = chartMode === 'power' ? 'W' : 'kWh';
        const digits = chartMode === 'power' ? 1 : 4;
        const borderColor = chartMode === 'power' ? '#2563eb' : '#16a34a';
        const backgroundColor = chartMode === 'power'
            ? 'rgba(37, 99, 235, 0.10)'
            : 'rgba(22, 163, 74, 0.10)';

        chartInstance?.destroy();
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
                    pointRadius: labels.length > 36 ? 0 : 2.5,
                    pointHoverRadius: 4,
                    fill: true,
                    tension: 0.3,
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
                        ticks: {color: '#7a8699', maxTicksLimit: 9, font: {size: 10}},
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

    const loadLabel = (status, available) => {
        if (!available) {
            return 'Data terbaru belum tersedia';
        }
        if (status === 'danger') {
            return 'Pemakaian sangat tinggi';
        }
        if (status === 'warning') {
            return 'Mendekati batas';
        }
        return 'Pemakaian normal';
    };

    const setSwitchState = (
        button,
        isOn,
        statusAvailable = true,
        online = true,
        pending = false
    ) => {
        const controlAvailable = Boolean(online && statusAvailable);
        const confirmedOn = Boolean(controlAvailable && isOn);

        button.dataset.currentState = confirmedOn ? 'on' : 'off';
        button.dataset.statusAvailable = statusAvailable ? 'true' : 'false';
        button.dataset.espOnline = online ? 'true' : 'false';
        button.dataset.confirming = pending ? 'true' : 'false';

        button.classList.toggle('is-on', confirmedOn);
        button.classList.toggle('is-offline', !controlAvailable);
        button.classList.toggle('is-loading', pending && controlAvailable);
        button.setAttribute('aria-pressed', confirmedOn ? 'true' : 'false');

        const label = button.querySelector('[data-switch-label]');
        if (label) {
            label.textContent = pending && controlAvailable
                ? 'Memproses'
                : (confirmedOn ? 'Nyala' : 'Mati');
        }

        const deviceName = button
            .closest('[data-device-row]')
            ?.querySelector('.sv-device-copy strong')
            ?.textContent
            ?.trim() || 'Perangkat';

        const description = !controlAvailable
            ? `${deviceName} belum dapat digunakan`
            : pending
                ? `${deviceName} sedang diproses`
                : `${confirmedOn ? 'Matikan' : 'Nyalakan'} ${deviceName}`;

        button.setAttribute('aria-label', description);
        button.setAttribute('title', description);
        button.disabled = pending || !controlAvailable;
    };

    const deviceStatusText = (device, pending = false) => {
        if (!device.esp_online) {
            return 'SmartVolt belum terhubung';
        }
        if (!device.status_available) {
            return 'Menyiapkan perangkat';
        }
        if (pending || device.command_pending) {
            return 'Sedang memproses';
        }
        return device.is_on === true ? 'Sedang menyala' : 'Siap digunakan';
    };

    const updateRoomInformation = (room, roomId) => {
        setText(`[data-room-summary-total="${CSS.escape(roomId)}"]`, Number(room.total_devices ?? 0));
        setText(`[data-room-summary-condition="${CSS.escape(roomId)}"]`, room.condition_label || 'Belum terhubung');
        setText(
            `[data-room-summary-last-power="${CSS.escape(roomId)}"]`,
            room.last_power === null || room.last_power === undefined
                ? '—'
                : `${formatNumber(room.last_power, 1)} W`
        );
        setText(
            `[data-room-summary-updated="${CSS.escape(roomId)}"]`,
            room.updated_human || 'Belum ada data'
        );

        const badge = document.querySelector(`[data-room-summary-condition="${CSS.escape(roomId)}"]`);
        if (badge) {
            const active = Number(room.active_devices ?? 0);
            const available = Boolean(room.status_available);
            badge.classList.remove('sv-badge-success', 'sv-badge-warning', 'sv-badge-neutral');
            badge.classList.add(available && active > 0 ? 'sv-badge-success' : 'sv-badge-neutral');
        }
    };

    const updateRooms = (rooms = []) => {
        const uniqueRooms = new Map();
        (Array.isArray(rooms) ? rooms : []).forEach((room) => {
            const roomId = String(room.id ?? '');
            if (roomId) {
                uniqueRooms.set(roomId, room);
            }
        });

        uniqueRooms.forEach((room, roomId) => {
            setText(
                `[data-room-active-count="${CSS.escape(roomId)}"]`,
                activeCountLabel(Boolean(room.status_available), room.active_devices)
            );
            setText(
                `[data-room-power="${CSS.escape(roomId)}"]`,
                `Daya saat ini: ${room.current_power === null || room.current_power === undefined
                    ? '—'
                    : `${formatNumber(room.current_power, 1)} W`}`
            );

            updateRoomInformation(room, roomId);

            const devices = Array.isArray(room.devices) ? room.devices : [];
            devices.forEach((device) => {
                const deviceId = String(device.id ?? '');
                if (!deviceId) {
                    return;
                }

                const button = document.querySelector(
                    `[data-device-toggle][data-device-id="${CSS.escape(deviceId)}"]`
                );
                const statusElement = document.querySelector(
                    `[data-device-status="${CSS.escape(deviceId)}"]`
                );

                const online = Boolean(device.esp_online);
                const available = Boolean(device.status_available);
                const actualOn = available && device.is_on === true;
                const localPending = pendingStates.get(deviceId);
                const serverPending = Boolean(device.command_pending);

                if (localPending && available && actualOn === localPending.requestedState) {
                    pendingStates.delete(deviceId);
                    clearTimers(deviceId);
                }

                if (!online || !available) {
                    pendingStates.delete(deviceId);
                    clearTimers(deviceId);
                }

                const pending = Boolean(pendingStates.get(deviceId)) || serverPending;

                if (button instanceof HTMLButtonElement) {
                    setSwitchState(button, actualOn, available, online, pending);
                }

                if (statusElement) {
                    statusElement.textContent = deviceStatusText(device, pending);
                }
            });
        });
    };

    const updateDashboard = (data = {}) => {
        const stats = data.stats || {};
        const system = data.system || {};
        const currentPowerAvailable = Boolean(stats.current_power_available);
        const energyTodayAvailable = Boolean(stats.energy_today_available);
        const deviceStatusAvailable = Boolean(stats.device_status_available);
        const connected = Boolean(system.connected ?? system.esp_online);

        setText('[data-stat-current-power]', currentPowerAvailable ? formatNumber(stats.current_power, 1) : '—');
        setText('[data-stat-energy-today]', energyTodayAvailable ? formatNumber(stats.total_energy_today, 3) : '—');
        setText('[data-stat-monthly-cost]', formatNumber(stats.monthly_estimated_cost, 0));
        setText('[data-stat-monthly-energy]', formatNumber(stats.monthly_energy_usage, 3));
        setText('[data-stat-total-devices]', Number(stats.total_devices ?? 0));
        setText('[data-stat-active-devices]', activeCountLabel(deviceStatusAvailable, stats.active_devices));
        setText(
            '[data-active-devices-message]',
            !connected
                ? 'SmartVolt belum terhubung'
                : deviceStatusAvailable
                    ? 'Sesuai kondisi perangkat saat ini'
                    : 'Menyiapkan informasi perangkat'
        );

        setText('[data-stat-load-percentage]', currentPowerAvailable ? formatNumber(stats.load_percentage, 0) : 0);
        setHidden('[data-load-percentage-wrap]', !currentPowerAvailable);

        const progress = document.querySelector('[data-load-progress]');
        if (progress) {
            const percentage = currentPowerAvailable
                ? Math.min(100, Math.max(0, Number(stats.load_percentage ?? 0)))
                : 0;
            progress.style.setProperty('--sv-progress', `${percentage}%`);
            progress.classList.toggle('is-warning', stats.load_status === 'warning');
            progress.classList.toggle('is-danger', stats.load_status === 'danger');
            progress.hidden = !currentPowerAvailable;
        }

        const loadBadge = document.querySelector('[data-load-status-badge]');
        if (loadBadge) {
            loadBadge.classList.remove('is-success', 'is-warning', 'is-danger');
            if (currentPowerAvailable) {
                loadBadge.classList.add(
                    stats.load_status === 'danger'
                        ? 'is-danger'
                        : stats.load_status === 'warning'
                            ? 'is-warning'
                            : 'is-success'
                );
            }
        }

        setText('[data-load-status-label]', loadLabel(stats.load_status, currentPowerAvailable));

        const comparison = Number(stats.energy_comparison_percent);
        setText(
            '[data-energy-comparison]',
            !energyTodayAvailable
                ? 'Data hari ini belum tersedia'
                : Number.isFinite(comparison)
                    ? `${comparison > 0 ? 'Lebih tinggi' : comparison < 0 ? 'Lebih hemat' : 'Sama'} ${formatNumber(Math.abs(comparison), 1)}% dari kemarin`
                    : 'Perbandingan belum tersedia'
        );

        const headerBadge = document.querySelector('[data-dashboard-system-badge]');
        if (headerBadge) {
            headerBadge.classList.remove('sv-badge-success', 'sv-badge-warning', 'sv-badge-neutral');
            headerBadge.classList.add(connected ? 'sv-badge-success' : 'sv-badge-neutral');
        }

        setText('[data-dashboard-system-label]', connected ? 'Terhubung' : 'Belum terhubung');
        setText('[data-system-status-title]', system.status_title || (connected ? 'SmartVolt terhubung' : 'SmartVolt belum terhubung'));
        setText('[data-system-status-message]', system.status_message || 'Hubungkan SmartVolt untuk melihat kondisi terbaru.');

        updateRooms(data.rooms || []);
        dashboardChart = data.chart || dashboardChart;
        renderChart();
    };

    let refreshInProgress = false;

    const refreshDashboard = async (force = false) => {
        if (refreshInProgress || (!force && document.hidden) || !config.endpoint) {
            return null;
        }

        refreshInProgress = true;

        try {
            const data = await window.SmartVolt.fetchJson(
                config.endpoint,
                {method: 'GET'},
                10000
            );
            updateDashboard(data);
            return data;
        } catch (error) {
            console.warn('Pembaruan Beranda gagal:', error.message);
            return null;
        } finally {
            refreshInProgress = false;
        }
    };

    const scheduleSync = (deviceId) => {
        const key = String(deviceId);
        clearTimers(key);
        const delays = [350, 800, 1500, 3000, 6000, 10000, 15000, 22000, 30000];
        const timers = delays.map((delay, index) => window.setTimeout(async () => {
            if (!pendingStates.has(key)) {
                clearTimers(key);
                return;
            }

            await refreshDashboard(true);

            if (index === delays.length - 1 && pendingStates.has(key)) {
                pendingStates.delete(key);
                clearTimers(key);
                await refreshDashboard(true);
                window.SmartVolt.toast(
                    'Perubahan belum dikonfirmasi. Periksa koneksi SmartVolt.',
                    {type: 'error'}
                );
            }
        }, delay));
        syncTimers.set(key, timers);
    };

    document.addEventListener('click', async (event) => {
        const button = event.target.closest('[data-device-toggle]');

        if (!(button instanceof HTMLButtonElement) || button.disabled) {
            return;
        }

        const deviceId = String(button.dataset.deviceId || '');
        if (!deviceId || activeRequests.has(deviceId)) {
            return;
        }

        const online = button.dataset.espOnline === 'true';
        const available = button.dataset.statusAvailable === 'true';
        const currentOn = button.dataset.currentState === 'on';
        const requestedState = !currentOn;
        const statusElement = document.querySelector(
            `[data-device-status="${CSS.escape(deviceId)}"]`
        );

        if (!online || !available) {
            setSwitchState(button, false, available, online, false);
            return;
        }

        activeRequests.add(deviceId);
        pendingStates.set(deviceId, {requestedState, startedAt: Date.now()});
        setSwitchState(button, currentOn, available, online, true);

        if (statusElement) {
            statusElement.textContent = 'Sedang memproses';
        }

        try {
            const response = await window.SmartVolt.fetchJson(
                button.dataset.url,
                {method: 'POST'},
                5000
            );

            if (response.success === false) {
                throw new Error(response.message || 'Perintah tidak dapat diproses.');
            }

            window.SmartVolt.toast(
                response.message || 'Perubahan sedang diproses.',
                {type: 'success'}
            );
            scheduleSync(deviceId);
        } catch (error) {
            pendingStates.delete(deviceId);
            clearTimers(deviceId);
            await refreshDashboard(true);
            window.SmartVolt.toast(
                error.message || 'Perubahan tidak berhasil. Periksa koneksi SmartVolt.',
                {type: 'error'}
            );
        } finally {
            activeRequests.delete(deviceId);
        }
    });

    renderChart();
    updateDashboard({
        stats: config.stats || {},
        system: config.system || {},
        rooms: config.rooms || [],
        chart: config.chart || {},
    });

    const intervalSeconds = Math.min(
        60,
        Math.max(10, Number(config.refreshInterval || 30))
    );

    window.setInterval(() => refreshDashboard(false), intervalSeconds * 1000);
})();
