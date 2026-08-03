(() => {
    'use strict';

    const dataNode = document.getElementById('smartvolt-rooms-data');

    if (!dataNode || !window.SmartVolt || window.__smartVoltRoomsInitialized) {
        return;
    }

    window.__smartVoltRoomsInitialized = true;

    let config = {};

    try {
        config = JSON.parse(dataNode.textContent || '{}');
    } catch {
        window.SmartVolt.toast('Halaman ruangan tidak dapat dimuat.', {type: 'error'});
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

    const updateSystem = (system = {}) => {
        const connected = Boolean(system.connected);
        const badge = document.querySelector('[data-rooms-system-badge]');

        if (badge) {
            badge.classList.remove('sv-badge-success', 'sv-badge-warning', 'sv-badge-neutral');
            badge.classList.add(connected ? 'sv-badge-success' : 'sv-badge-neutral');
        }

        setText('[data-rooms-system-label]', connected ? 'Terhubung' : 'Belum terhubung');
    };

    const updateSummary = (stats = {}) => {
        const available = Boolean(stats.device_status_available);
        setText('[data-total-rooms]', Number(stats.total_rooms ?? 0));
        setText('[data-total-devices]', Number(stats.total_devices ?? 0));
        setText('[data-active-devices]', activeCountLabel(available, stats.active_devices));
    };

    const updateRoomBadge = (room, roomId) => {
        const badge = document.querySelector(
            `[data-room-connection-badge="${CSS.escape(roomId)}"]`
        );

        if (!badge) {
            return;
        }

        const totalDevices = Number(room.total_devices ?? 0);
        const onlineDevices = Number(room.online_devices ?? 0);

        badge.classList.remove('sv-badge-success', 'sv-badge-warning', 'sv-badge-neutral');
        badge.classList.add(
            totalDevices === 0
                ? 'sv-badge-neutral'
                : onlineDevices === totalDevices
                    ? 'sv-badge-success'
                    : onlineDevices > 0
                        ? 'sv-badge-warning'
                        : 'sv-badge-neutral'
        );

        setText(
            `[data-room-connection-label="${CSS.escape(roomId)}"]`,
            room.connection_label || (onlineDevices > 0 ? 'Terhubung' : 'Belum terhubung')
        );
    };

    const roomPowerText = (room) => {
        if (room.current_power !== null && room.current_power !== undefined) {
            return `Daya saat ini: ${formatNumber(room.current_power, 1)} W`;
        }

        if (room.last_power !== null && room.last_power !== undefined) {
            const updated = room.updated_human ? ` · ${room.updated_human}` : '';
            return `Data terakhir: ${formatNumber(room.last_power, 1)} W${updated}`;
        }

        return 'Belum ada data pemakaian';
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

    const updateRoom = (room = {}) => {
        const roomId = String(room.id ?? '');

        if (!roomId) {
            return;
        }

        const totalDevices = Number(room.total_devices ?? 0);
        const statusAvailable = Boolean(room.status_available);

        setText(
            `[data-room-total-devices="${CSS.escape(roomId)}"]`,
            totalDevices
        );

        if (totalDevices > 0) {
            setText(
                `[data-room-active-count="${CSS.escape(roomId)}"]`,
                activeCountLabel(statusAvailable, room.active_devices)
            );
        }

        setText(
            `[data-room-power-summary="${CSS.escape(roomId)}"]`,
            roomPowerText(room)
        );

        updateRoomBadge(room, roomId);

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
    };

    const updatePage = (data = {}) => {
        updateSystem(data.system || {});
        updateSummary(data.stats || {});

        const rooms = Array.isArray(data.rooms) ? data.rooms : [];
        rooms.forEach(updateRoom);
    };

    let refreshInProgress = false;

    const refreshPage = async (force = false) => {
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
            updatePage(data);
            return data;
        } catch (error) {
            console.warn('Pembaruan halaman ruangan gagal:', error.message);
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

            await refreshPage(true);

            if (index === delays.length - 1 && pendingStates.has(key)) {
                pendingStates.delete(key);
                clearTimers(key);
                await refreshPage(true);
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
        pendingStates.set(deviceId, {
            requestedState,
            startedAt: Date.now(),
        });

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
            await refreshPage(true);
            window.SmartVolt.toast(
                error.message || 'Perubahan tidak berhasil. Periksa koneksi SmartVolt.',
                {type: 'error'}
            );
        } finally {
            activeRequests.delete(deviceId);
        }
    });

    updatePage({
        stats: config.stats || {},
        system: config.system || {},
        rooms: config.rooms || [],
    });

    const intervalSeconds = Math.min(
        60,
        Math.max(10, Number(config.refreshInterval || 30))
    );

    window.setInterval(() => refreshPage(false), intervalSeconds * 1000);
})();
