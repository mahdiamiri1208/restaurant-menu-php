const CACHE_NAME = 'restaurant-menu-v2';
const MENU_URL = '/';

self.addEventListener('install', (event) => {
    event.waitUntil((async () => {
        const cache = await caches.open(CACHE_NAME);
        const menuResponse = await fetch(MENU_URL, { cache: 'no-store' });

        if (!menuResponse.ok) {
            throw new Error(`Unable to precache the menu: ${menuResponse.status}`);
        }

        const html = await menuResponse.clone().text();
        await cache.put(MENU_URL, menuResponse);

        const urls = new Set(['/manifest.json', '/fonts/vazirmatn.woff2']);
        for (const match of html.matchAll(/(?:src|href)="([^"]+)"/g)) {
            const url = new URL(match[1], self.location.origin);
            if (url.origin === self.location.origin) {
                urls.add(url.href);
            }
        }

        const resources = [...urls].filter((value) => {
            const pathname = new URL(value, self.location.origin).pathname;
            return /\.(?:css|js|json|woff2)$/i.test(pathname);
        });

        await Promise.all(resources.map(async (url) => {
            const response = await fetch(url, { cache: 'no-store' });
            if (!response.ok) {
                throw new Error(`Unable to precache ${url}: ${response.status}`);
            }
            await cache.put(url, response);
        }));

        const images = [...html.matchAll(/<img\b[^>]*\bsrc="([^"]+)"/gi)]
            .map((match) => new URL(match[1], self.location.origin))
            .filter((url) => url.origin === self.location.origin);

        await Promise.all(images.map(async (url) => {
            try {
                const response = await fetch(url.href, { cache: 'no-store' });
                if (response.ok) {
                    await cache.put(url.href, response);
                }
            } catch (error) {
                console.warn('Unable to precache a menu image.', error);
            }
        }));

        await self.skipWaiting();
    })());
});

self.addEventListener('activate', (event) => {
    event.waitUntil((async () => {
        const names = await caches.keys();
        await Promise.all(names
            .filter((name) => name.startsWith('restaurant-menu-') && name !== CACHE_NAME)
            .map((name) => caches.delete(name)));
        await self.clients.claim();
    })());
});

self.addEventListener('fetch', (event) => {
    const request = event.request;
    const url = new URL(request.url);

    if (request.method !== 'GET' || url.origin !== self.location.origin) {
        return;
    }

    if (request.mode === 'navigate' && url.pathname === MENU_URL) {
        event.respondWith((async () => {
            try {
                const response = await fetch(request);
                if (response.ok) {
                    event.waitUntil(caches.open(CACHE_NAME).then((cache) => cache.put(MENU_URL, response.clone())));
                }
                return response;
            } catch {
                const cached = await caches.match(MENU_URL);
                return cached || Response.error();
            }
        })());
        return;
    }

    if (request.destination === 'image' || request.destination === 'font' || request.destination === 'style' || request.destination === 'script') {
        event.respondWith((async () => {
            const cached = await caches.match(request);
            if (cached) {
                return cached;
            }

            const response = await fetch(request);
            if (response.ok) {
                event.waitUntil(caches.open(CACHE_NAME).then((cache) => cache.put(request, response.clone())));
            }
            return response;
        })());
    }
});
