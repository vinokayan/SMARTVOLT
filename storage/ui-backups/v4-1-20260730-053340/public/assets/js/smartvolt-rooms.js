(() => {
    'use strict';

    if (!window.SmartVolt) {
        return;
    }

    const updateTotals = () => {
        const active = document.querySelectorAll('[data-device-toggle].is-on').length;
        document.querySelectorAll('[data-active-devices]').forEach((element) => {
            element.textContent = String(active);
        });

        document.querySelectorAll('[data-room-card]').forEach((roomCard) => {
            const roomId = roomCard.dataset.roomCard;
            const activeInRoom = roomCard.querySelectorAll('[data-device-toggle].is-on').length;
            document.querySelectorAll(`[data-room-active-count="${CSS.escape(roomId)}"]`).forEach((element) => {
                element.textContent = String(activeInRoom);
            });
        });
    };

    const setSwitchState = (button, state, online = true) => {
        button.dataset.currentState = state ? 'on' : 'off';
        button.classList.toggle('is-on', state);
        button.classList.toggle('is-offline', !online);
        button.setAttribute('aria-pressed', state ? 'true' : 'false');
        const label = button.querySelector('[data-switch-label]');
        if (label) {
            label.textContent = online ? (state ? 'Nyala' : 'Mati') : 'Offline';
        }
        button.disabled = !online;
    };

    document.addEventListener('click', async (event) => {
        const button = event.target.closest('[data-device-toggle]');
        if (!(button instanceof HTMLButtonElement) || button.disabled || button.classList.contains('is-loading')) {
            return;
        }

        const previousState = button.dataset.currentState === 'on';
        const label = button.querySelector('[data-switch-label]');
        button.disabled = true;
        button.classList.add('is-loading');
        if (label) {
            label.textContent = 'Mengirim';
        }

        try {
            const data = await window.SmartVolt.fetchJson(button.dataset.url, {method: 'POST'});
            const nextState = data.status === 'on' || data.status === true;
            setSwitchState(button, nextState, data.esp_online !== false);
            updateTotals();
            window.SmartVolt.toast(data.message || 'Perintah berhasil dikirim.', {type: 'success'});
        } catch (error) {
            setSwitchState(button, previousState, error.status !== 409);
            window.SmartVolt.toast(error.message || 'Perintah perangkat tidak berhasil.', {type: 'error'});
        } finally {
            button.classList.remove('is-loading');
            if (!button.classList.contains('is-offline')) {
                button.disabled = false;
            }
        }
    });
})();
