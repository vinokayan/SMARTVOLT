(() => {
    'use strict';

    const allowedTabs = new Set([
        'profile',
        'security',
        'system',
    ]);

    const currentUrl = new URL(window.location.href);

    if (currentUrl.hash === '#technician') {
        currentUrl.hash = '';
        window.history.replaceState({}, '', currentUrl);
    }

    const activateTab = (tabName) => {
        if (!allowedTabs.has(tabName)) {
            return;
        }

        const button = document.querySelector(
            `[data-tab-name="${CSS.escape(tabName)}"]`
        );

        if (button && !button.classList.contains('is-active')) {
            button.click();
        }
    };

    const requestedTab = currentUrl.searchParams.get('tab');
    activateTab(allowedTabs.has(requestedTab) ? requestedTab : 'profile');

    document
        .querySelectorAll('[data-tab-name]')
        .forEach((button) => {
            button.addEventListener('click', () => {
                const tabName = button.dataset.tabName || '';

                if (!allowedTabs.has(tabName)) {
                    return;
                }

                const nextUrl = new URL(window.location.href);
                nextUrl.searchParams.set('tab', tabName);
                nextUrl.hash = '';

                window.history.replaceState({}, '', nextUrl);
            });
        });
})();
