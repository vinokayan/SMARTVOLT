(() => {
    'use strict';

    const body = document.body;
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';

    const closeAllMenus = (except = null) => {
        document.querySelectorAll('[data-profile-menu].is-open, [data-notification-menu].is-open')
            .forEach((menu) => {
                if (menu === except) {
                    return;
                }

                menu.classList.remove('is-open');
                menu.querySelector('[aria-expanded="true"]')?.setAttribute('aria-expanded', 'false');
            });
    };

    const openSidebar = () => {
        body.classList.add('sv-sidebar-open', 'sv-is-locked');
    };

    const closeSidebar = () => {
        body.classList.remove('sv-sidebar-open', 'sv-is-locked');
    };

    document.addEventListener('click', (event) => {
        if (event.target.closest('[data-sidebar-open]')) {
            openSidebar();
            return;
        }

        if (event.target.closest('[data-sidebar-close]')) {
            closeSidebar();
            return;
        }

        const profileTrigger = event.target.closest('[data-profile-trigger]');
        if (profileTrigger) {
            const menu = profileTrigger.closest('[data-profile-menu]');
            const nextState = !menu.classList.contains('is-open');

            closeAllMenus(menu);
            menu.classList.toggle('is-open', nextState);
            profileTrigger.setAttribute('aria-expanded', nextState ? 'true' : 'false');
            return;
        }

        const notificationTrigger = event.target.closest('[data-notification-trigger]');
        if (notificationTrigger) {
            const menu = notificationTrigger.closest('[data-notification-menu]');
            const nextState = !menu.classList.contains('is-open');

            closeAllMenus(menu);
            menu.classList.toggle('is-open', nextState);
            notificationTrigger.setAttribute('aria-expanded', nextState ? 'true' : 'false');
            return;
        }

        if (!event.target.closest('[data-profile-menu], [data-notification-menu]')) {
            closeAllMenus();
        }

        const openDialogButton = event.target.closest('[data-dialog-open]');
        if (openDialogButton) {
            const dialog = document.getElementById(openDialogButton.dataset.dialogOpen || '');
            if (dialog instanceof HTMLDialogElement) {
                dialog.showModal();
                body.classList.add('sv-is-locked');
            }
            return;
        }

        const closeDialogButton = event.target.closest('[data-dialog-close]');
        if (closeDialogButton) {
            const dialog = closeDialogButton.closest('dialog');
            if (dialog instanceof HTMLDialogElement) {
                dialog.close();
            }
            return;
        }

        const passwordToggle = event.target.closest('[data-password-toggle]');
        if (passwordToggle) {
            const targetId = passwordToggle.dataset.passwordToggle;
            const input = document.getElementById(targetId);
            if (!(input instanceof HTMLInputElement)) {
                return;
            }

            const shouldShow = input.type === 'password';
            input.type = shouldShow ? 'text' : 'password';
            passwordToggle.setAttribute(
                'aria-label',
                shouldShow ? 'Sembunyikan kata sandi' : 'Tampilkan kata sandi'
            );
            passwordToggle.setAttribute('aria-pressed', shouldShow ? 'true' : 'false');
            passwordToggle.classList.toggle('is-visible', shouldShow);
            input.focus();
            return;
        }

        const tabButton = event.target.closest('[data-tab-target]');
        if (tabButton) {
            const group = tabButton.closest('[data-tabs]');
            const targetId = tabButton.dataset.tabTarget;

            if (!group || !targetId) {
                return;
            }

            group.querySelectorAll('[data-tab-target]').forEach((button) => {
                const active = button === tabButton;
                button.classList.toggle('is-active', active);
                button.setAttribute('aria-selected', active ? 'true' : 'false');
            });

            const panelsRootId = group.dataset.panelsRoot;
            const panelsRoot = panelsRootId
                ? document.getElementById(panelsRootId)
                : group.parentElement;

            panelsRoot?.querySelectorAll('[data-tab-panel]').forEach((panel) => {
                panel.classList.toggle('is-active', panel.id === targetId);
            });

            const url = new URL(window.location.href);
            url.searchParams.set('tab', tabButton.dataset.tabName || targetId.replace(/^tab-/, ''));
            history.replaceState({}, '', url);
        }
    });

    document.addEventListener('submit', (event) => {
        const form = event.target;
        if (!(form instanceof HTMLFormElement)) {
            return;
        }

        const confirmMessage = form.dataset.confirm;
        if (confirmMessage && !window.confirm(confirmMessage)) {
            event.preventDefault();
            return;
        }

        if (!form.hasAttribute('data-loading-form')) {
            return;
        }

        if (!form.checkValidity()) {
            return;
        }

        const submitButton = form.querySelector('button[type="submit"]');
        if (!(submitButton instanceof HTMLButtonElement)) {
            return;
        }

        submitButton.disabled = true;
        submitButton.classList.add('is-loading');
        submitButton.setAttribute('aria-busy', 'true');

        const label = submitButton.querySelector('[data-button-label]');
        if (label && submitButton.dataset.loadingText) {
            label.textContent = submitButton.dataset.loadingText;
        }
    });

    document.querySelectorAll('dialog').forEach((dialog) => {
        dialog.addEventListener('close', () => {
            if (!document.querySelector('dialog[open]')) {
                body.classList.remove('sv-is-locked');
            }
        });

        dialog.addEventListener('click', (event) => {
            if (event.target === dialog) {
                dialog.close();
            }
        });
    });

    document.querySelectorAll('[data-auto-open-dialog]').forEach((element) => {
        const dialog = document.getElementById(element.dataset.autoOpenDialog || '');
        if (dialog instanceof HTMLDialogElement) {
            dialog.showModal();
            body.classList.add('sv-is-locked');
        }
    });

    document.addEventListener('keydown', (event) => {
        if (event.key !== 'Escape') {
            return;
        }

        closeSidebar();
        closeAllMenus();
    });

    const toastIcon = (type) => {
        if (type === 'success') {
            return '<svg class="sv-icon" width="19" height="19" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="m5 12 4 4L19 6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>';
        }

        if (type === 'warning' || type === 'error') {
            return '<svg class="sv-icon" width="19" height="19" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M12 3 2.5 20h19L12 3Z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/><path d="M12 9v5M12 17h.01" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>';
        }

        return '<svg class="sv-icon" width="19" height="19" viewBox="0 0 24 24" fill="none" aria-hidden="true"><circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="1.8"/><path d="M12 11v5M12 8h.01" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>';
    };

    const toast = (message, options = {}) => {
        const region = document.querySelector('[data-toast-region]');
        if (!region) {
            return;
        }

        const type = ['success', 'error', 'warning', 'info'].includes(options.type)
            ? options.type
            : 'info';

        const title = options.title
            || (type === 'success'
                ? 'Berhasil'
                : type === 'error'
                    ? 'Tidak berhasil'
                    : type === 'warning'
                        ? 'Perlu perhatian'
                        : 'Informasi');

        const item = document.createElement('div');
        item.className = `sv-toast is-${type}`;
        item.setAttribute('role', type === 'error' ? 'alert' : 'status');
        item.innerHTML = `
            <span class="sv-text-${type === 'error' ? 'danger' : type}">${toastIcon(type)}</span>
            <span class="sv-toast-copy">
                <strong></strong>
                <span></span>
            </span>
            <button type="button" class="sv-toast-close" aria-label="Tutup notifikasi">
                <svg class="sv-icon" width="15" height="15" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                    <path d="m6 6 12 12M18 6 6 18" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                </svg>
            </button>
        `;

        item.querySelector('strong').textContent = title;
        item.querySelector('.sv-toast-copy span').textContent = String(message || '');
        item.querySelector('.sv-toast-close').addEventListener('click', () => item.remove());

        region.appendChild(item);
        requestAnimationFrame(() => item.classList.add('is-visible'));

        window.setTimeout(() => {
            item.classList.remove('is-visible');
            window.setTimeout(() => item.remove(), 180);
        }, Math.max(2500, Number(options.duration) || 4500));
    };

    const safeJson = async (response) => {
        const text = await response.text();

        if (!text) {
            return {};
        }

        try {
            return JSON.parse(text);
        } catch {
            throw new Error('Respons server tidak dapat dibaca.');
        }
    };

    const fetchJson = async (url, options = {}, timeoutMs = 12000) => {
        const controller = new AbortController();
        const timeout = window.setTimeout(() => controller.abort(), timeoutMs);

        try {
            const response = await fetch(url, {
                credentials: 'same-origin',
                ...options,
                signal: controller.signal,
                headers: {
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    ...(csrfToken ? {'X-CSRF-TOKEN': csrfToken} : {}),
                    ...(options.headers || {}),
                },
            });

            const data = await safeJson(response);

            if (!response.ok) {
                const error = new Error(
                    data.message
                    || (response.status === 419
                        ? 'Sesi keamanan telah berakhir. Muat ulang halaman lalu coba kembali.'
                        : `Permintaan gagal dengan status ${response.status}.`)
                );
                error.status = response.status;
                error.data = data;
                throw error;
            }

            return data;
        } catch (error) {
            if (error.name === 'AbortError') {
                throw new Error('Server terlalu lama merespons. Periksa koneksi lalu coba kembali.');
            }

            throw error;
        } finally {
            window.clearTimeout(timeout);
        }
    };

    window.SmartVolt = Object.freeze({
        csrfToken,
        toast,
        fetchJson,
        closeSidebar,
    });
})();
