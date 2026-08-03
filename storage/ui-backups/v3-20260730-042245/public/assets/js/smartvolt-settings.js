(() => {
    'use strict';

    const syncRelayNameFields = (select) => {
        const form = select.closest('[data-sensor-form]');
        const container = form?.querySelector('[data-relay-names]');
        if (!container) {
            return;
        }

        const count = Math.min(8, Math.max(1, Number(select.value || 1)));
        container.querySelectorAll('[data-relay-name-item]').forEach((item) => {
            const index = Number(item.dataset.relayNameItem || 0);
            const visible = index <= count;
            item.hidden = !visible;
            const input = item.querySelector('input');
            if (input) {
                input.required = visible;
                input.disabled = !visible;
            }
        });
    };

    document.querySelectorAll('[data-relay-count]').forEach((select) => {
        syncRelayNameFields(select);
        select.addEventListener('change', () => syncRelayNameFields(select));
    });

    const requestedTab = new URL(window.location.href).searchParams.get('tab');
    if (requestedTab) {
        const button = document.querySelector(`[data-tab-name="${CSS.escape(requestedTab)}"]`);
        if (button && !button.classList.contains('is-active')) {
            button.click();
        }
    }

    const hash = window.location.hash;
    if (hash && hash.startsWith('#room-')) {
        const room = document.querySelector(hash);
        if (room instanceof HTMLDetailsElement) {
            room.open = true;
            window.setTimeout(() => room.scrollIntoView({behavior: 'smooth', block: 'start'}), 150);
        }
    }
})();
