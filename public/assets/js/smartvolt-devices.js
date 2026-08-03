(() => {
    'use strict';

    const search = document.querySelector('[data-device-search]');
    if (!(search instanceof HTMLInputElement)) return;

    const items = Array.from(document.querySelectorAll('[data-device-filter-item]'));
    const visibleCounter = document.querySelector('[data-device-visible-count]');

    const normalize = (value) => String(value || '')
        .toLocaleLowerCase('id-ID')
        .normalize('NFD')
        .replace(/[\u0300-\u036f]/g, '')
        .trim();

    const filterItems = () => {
        const query = normalize(search.value);
        let visible = 0;

        items.forEach((item) => {
            const content = normalize(item.dataset.deviceSearchText || item.textContent);
            const matched = query === '' || content.includes(query);
            item.classList.toggle('sv-device-hidden', !matched);
            if (matched) visible += 1;
        });

        // The same device is rendered once as a desktop row and once as a mobile card.
        const displayCount = Math.ceil(visible / 2);
        if (visibleCounter) visibleCounter.textContent = String(displayCount);
    };

    search.addEventListener('input', filterItems);
})();
