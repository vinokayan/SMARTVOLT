(() => {
    'use strict';

    const cleanLegacyHash = () => {
        if (window.location.hash !== '#technician') {
            return;
        }

        const url = new URL(window.location.href);
        url.hash = '';
        window.history.replaceState({}, '', url);
    };

    const syncDeviceNameFields = (select) => {
        const form = select.closest('[data-sensor-form]');
        const container = form?.querySelector('[data-relay-names]');

        if (!container) {
            return;
        }

        const count = Math.min(
            8,
            Math.max(1, Number(select.value || 1))
        );

        container
            .querySelectorAll('[data-relay-name-item]')
            .forEach((item) => {
                const index = Number(
                    item.dataset.relayNameItem || 0
                );
                const visible = index <= count;
                const input = item.querySelector('input');

                item.hidden = !visible;

                if (input) {
                    input.required = visible;
                    input.disabled = !visible;
                }
            });
    };

    cleanLegacyHash();

    document
        .querySelectorAll('[data-relay-count]')
        .forEach((select) => {
            syncDeviceNameFields(select);
            select.addEventListener(
                'change',
                () => syncDeviceNameFields(select)
            );
        });

    const hash = window.location.hash;

    if (hash && hash.startsWith('#room-')) {
        const room = document.querySelector(hash);

        if (room instanceof HTMLDetailsElement) {
            room.open = true;
            window.setTimeout(
                () => room.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start',
                }),
                150
            );
        }
    }

    const tracker = document.querySelector(
        '[data-advanced-mode-tracker]'
    );

    if (!(tracker instanceof HTMLElement)) {
        return;
    }

    const leaveUrl = tracker.dataset.leaveUrl || '';
    const visitToken = tracker.dataset.visitToken || '';
    const csrfToken = tracker.dataset.csrfToken || '';
    let leaveRecorded = false;

    const recordLeave = () => {
        if (
            leaveRecorded
            || !leaveUrl
            || !visitToken
            || !csrfToken
            || typeof navigator.sendBeacon !== 'function'
        ) {
            return;
        }

        const body = new FormData();
        body.append('_token', csrfToken);
        body.append('visit_token', visitToken);

        if (navigator.sendBeacon(leaveUrl, body)) {
            leaveRecorded = true;
        }
    };

    window.addEventListener('pagehide', recordLeave);
})();
