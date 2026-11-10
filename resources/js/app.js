const menu = document.querySelector('[data-menu]');

if (menu) {
    const searchToggle = menu.querySelector('[data-search-toggle]');
    const searchContainer = menu.querySelector('[data-search-container]');
    const searchInput = menu.querySelector('[data-search-input]');
    const productSections = [...menu.querySelectorAll('.menu-section')];
    const categoryChips = [...menu.querySelectorAll('[data-category-chip]')];
    const emptyResults = menu.querySelector('[data-empty-results]');
    const numberFormatter = new Intl.NumberFormat('fa-IR');

    const setActiveCategory = (categoryId) => {
        let activeChip = null;
        const wasActive = categoryChips.some((chip) =>
            chip.dataset.categoryChip === categoryId && chip.classList.contains('active')
        );

        categoryChips.forEach((chip) => {
            const active = chip.dataset.categoryChip === categoryId;
            chip.classList.toggle('active', active);
            chip.setAttribute('aria-pressed', String(active));

            if (active) {
                activeChip = chip;
            }
        });

        if (activeChip && !wasActive) {
            activeChip.scrollIntoView({
                behavior: window.matchMedia('(prefers-reduced-motion: reduce)').matches ? 'auto' : 'smooth',
                block: 'nearest',
                inline: 'center',
            });
        }
    };

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
            setActiveCategory(categoryId);

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
        setActiveCategory(categoryId);
    }, { rootMargin: '-25% 0px -65% 0px', threshold: [0, 0.2, 0.5] });

    sections.forEach((section) => observer.observe(section));

    const lightbox = document.querySelector('[data-image-lightbox]');
    const lightboxImage = lightbox?.querySelector('[data-lightbox-image]');
    const lightboxClose = lightbox?.querySelector('[data-lightbox-close]');

    if (lightbox && lightboxImage && lightboxClose) {
        let scrollPosition = null;
        let previousBodyStyles = null;
        let previousFocus = null;
        let closeTimer = null;

        const restorePage = () => {
            if (!previousBodyStyles || !scrollPosition) {
                return;
            }

            const body = document.body;
            body.style.position = previousBodyStyles.position;
            body.style.top = previousBodyStyles.top;
            body.style.left = previousBodyStyles.left;
            body.style.width = previousBodyStyles.width;
            body.style.overflow = previousBodyStyles.overflow;

            window.scrollTo(scrollPosition.x, scrollPosition.y);
            previousBodyStyles = null;
            scrollPosition = null;

            if (previousFocus?.isConnected) {
                previousFocus.focus({ preventScroll: true });
            }
            previousFocus = null;
        };

        const finishClose = () => {
            if (!lightbox.hidden && !lightbox.classList.contains('is-open')) {
                lightbox.hidden = true;
                lightboxImage.removeAttribute('src');
                restorePage();
            }
        };

        const closeLightbox = () => {
            if (lightbox.hidden || !lightbox.classList.contains('is-open')) {
                return;
            }

            lightbox.classList.remove('is-open');
            lightbox.setAttribute('aria-hidden', 'true');
            window.clearTimeout(closeTimer);
            closeTimer = window.setTimeout(finishClose, 300);
        };

        const openLightbox = (image) => {
            if (!lightbox.hidden) {
                return;
            }

            window.clearTimeout(closeTimer);
            previousFocus = document.activeElement;
            scrollPosition = { x: window.scrollX, y: window.scrollY };
            const body = document.body;
            previousBodyStyles = {
                position: body.style.position,
                top: body.style.top,
                left: body.style.left,
                width: body.style.width,
                overflow: body.style.overflow,
            };

            lightboxImage.src = image.currentSrc || image.src;
            lightboxImage.alt = image.alt;
            body.style.position = 'fixed';
            body.style.top = `-${scrollPosition.y}px`;
            body.style.left = `-${scrollPosition.x}px`;
            body.style.width = '100%';
            body.style.overflow = 'hidden';
            lightbox.hidden = false;
            lightbox.setAttribute('aria-hidden', 'false');
            window.requestAnimationFrame(() => lightbox.classList.add('is-open'));
            lightboxClose.focus({ preventScroll: true });
        };

        menu.addEventListener('click', (event) => {
            const image = event.target.closest('[data-product-image]');

            if (image && menu.contains(image)) {
                openLightbox(image);
            }
        });

        menu.addEventListener('keydown', (event) => {
            if ((event.key === 'Enter' || event.key === ' ') &&
                event.target.matches('[data-product-image]')) {
                event.preventDefault();
                openLightbox(event.target);
            }
        });

        lightbox.addEventListener('click', (event) => {
            if (!event.target.closest('[data-lightbox-image]')) {
                closeLightbox();
            }
        });

        lightbox.addEventListener('transitionend', (event) => {
            if (event.target === lightbox && event.propertyName === 'opacity') {
                finishClose();
            }
        });

        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') {
                closeLightbox();
            }
        });
    }
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
