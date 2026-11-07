const menu = document.querySelector('[data-menu]');

if (menu) {
    const searchToggle = menu.querySelector('[data-search-toggle]');
    const searchContainer = menu.querySelector('[data-search-container]');
    const searchInput = menu.querySelector('[data-search-input]');
    const productSections = [...menu.querySelectorAll('.menu-section')];
    const categoryChips = [...menu.querySelectorAll('[data-category-chip]')];
    const emptyResults = menu.querySelector('[data-empty-results]');
    const numberFormatter = new Intl.NumberFormat('fa-IR');

    const icons = {
        search: '<circle cx="10.8" cy="10.8" r="6.8"></circle><path d="m16 16 4.5 4.5"></path>',
        close: '<path d="m18 6-12 12M6 6l12 12"></path>',
    };

    const setSearchOpen = (open) => {
        searchContainer.classList.toggle('search-field-open', open);
        searchContainer.setAttribute('aria-hidden', String(!open));
        searchInput.tabIndex = open ? 0 : -1;
        searchToggle.setAttribute('aria-expanded', String(open));
        searchToggle.setAttribute('aria-label', open ? 'بستن جستجو' : 'جستجو در منو');
        searchToggle.innerHTML = `<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">${icons[open ? 'close' : 'search']}</svg>`;
    };

    const filterProducts = () => {
        const query = searchInput.value.trim().toLocaleLowerCase('fa');
        let visibleCount = 0;

        productSections.forEach((section) => {
            const products = [...section.querySelectorAll('[data-product]')];
            let sectionCount = 0;

            products.forEach((product) => {
                const visible = !query || product.dataset.name.toLocaleLowerCase('fa').includes(query);
                product.hidden = !visible;
                sectionCount += Number(visible);
            });

            section.hidden = sectionCount === 0;
            section.querySelector('[data-section-count]').textContent = `${numberFormatter.format(sectionCount)} مورد`;
            visibleCount += sectionCount;
        });

        emptyResults.hidden = visibleCount > 0;
    };

    searchToggle.addEventListener('click', () => {
        const open = searchToggle.getAttribute('aria-expanded') !== 'true';
        searchInput.value = '';
        filterProducts();
        setSearchOpen(open);

        if (open) {
            searchInput.focus();
        }
    });

    searchInput.addEventListener('input', filterProducts);

    categoryChips.forEach((chip) => {
        chip.addEventListener('click', () => {
            const categoryId = chip.dataset.categoryChip;
            categoryChips.forEach((item) => {
                const active = item === chip;
                item.classList.toggle('active', active);
                item.setAttribute('aria-pressed', String(active));
            });

            if (categoryId === 'all') {
                window.scrollTo({ top: 0, behavior: 'smooth' });
                return;
            }

            document.getElementById(`section-${categoryId}`)?.scrollIntoView({
                behavior: 'smooth',
                block: 'start',
            });
        });
    });

    const sections = menu.querySelectorAll('[data-category]');
    const observer = new IntersectionObserver((entries) => {
        const current = entries
            .filter((entry) => entry.isIntersecting)
            .sort((left, right) => right.intersectionRatio - left.intersectionRatio)[0];

        if (!current) {
            return;
        }

        const categoryId = current.target.getAttribute('data-category') || 'all';
        categoryChips.forEach((chip) => {
            const active = chip.dataset.categoryChip === categoryId;
            chip.classList.toggle('active', active);
            chip.setAttribute('aria-pressed', String(active));
        });
    }, { rootMargin: '-25% 0px -65% 0px', threshold: [0, 0.2, 0.5] });

    sections.forEach((section) => observer.observe(section));
}

if ('serviceWorker' in navigator) {
    window.addEventListener('load', () => {
        if (import.meta.env.DEV) {
            Promise.all([
                navigator.serviceWorker.getRegistrations()
                    .then((registrations) => Promise.all(registrations.map((registration) => registration.unregister()))),
                caches.keys()
                    .then((names) => Promise.all(names
                        .filter((name) => name.startsWith('restaurant-menu-'))
                        .map((name) => caches.delete(name)))),
            ]).catch((error) => {
                console.error('Development service worker cleanup failed.', error);
            });

            return;
        }

        navigator.serviceWorker.register('/sw.js', { scope: '/' }).catch((error) => {
            console.error('Service worker registration failed.', error);
        });
    });
}
