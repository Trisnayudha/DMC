const CACHE_NAME = 'dmc-lucky-draw-v1';
const STATIC_ASSETS = [
    '/lucky-draw',
    '/manifest.json',
    '/image/dmc.png',
    'https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/css/bootstrap.min.css',
    'https://cdn.tutorialjinni.com/intl-tel-input/17.0.8/css/intlTelInput.css',
    'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css',
    'https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;800;900&family=Inter:wght@400;500;600;700&display=swap',
    'https://cdn.jsdelivr.net/npm/canvas-confetti@1.9.4/dist/confetti.browser.min.js',
    'https://cdn.tutorialjinni.com/intl-tel-input/17.0.8/js/intlTelInput.min.js',
    'https://unpkg.com/sweetalert/dist/sweetalert.min.js',
    'https://cdn.tutorialjinni.com/intl-tel-input/17.0.8/js/utils.js'
];

self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(CACHE_NAME).then((cache) => {
            return Promise.allSettled(
                STATIC_ASSETS.map((url) => {
                    return cache.add(new Request(url, { mode: 'no-cors' })).catch((err) => {
                        console.warn('[SW] Could not pre-cache asset:', url, err);
                    });
                })
            );
        }).then(() => self.skipWaiting())
    );
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys().then((keys) => {
            return Promise.all(
                keys.filter((key) => key !== CACHE_NAME).map((key) => caches.delete(key))
            );
        }).then(() => self.clients.claim())
    );
});

self.addEventListener('fetch', (event) => {
    const request = event.request;
    const url = new URL(request.url);

    // Bypass POST requests (business card upload, sync, draw store)
    if (request.method !== 'GET') {
        return;
    }

    // Bypass ping check so ping accurately tests network
    if (url.pathname.includes('/lucky-draw/ping')) {
        return;
    }

    // HTML navigation requests: Network first with Cache fallback
    if (request.mode === 'navigate' || request.headers.get('accept')?.includes('text/html')) {
        event.respondWith(
            fetch(request)
                .then((response) => {
                    if (response.ok) {
                        const copy = response.clone();
                        caches.open(CACHE_NAME).then((cache) => cache.put(request, copy));
                    }
                    return response;
                })
                .catch(() => {
                    return caches.match('/lucky-draw').then((cached) => {
                        return cached || caches.match(request);
                    });
                })
        );
        return;
    }

    // Static assets (CDNs, styles, scripts, images): Cache first with network fallback
    event.respondWith(
        caches.match(request).then((cachedResponse) => {
            if (cachedResponse) {
                // Fetch in background to update cache (Stale-While-Revalidate)
                fetch(request).then((networkResponse) => {
                    if (networkResponse && networkResponse.status === 200) {
                        caches.open(CACHE_NAME).then((cache) => cache.put(request, networkResponse));
                    }
                }).catch(() => {/* ignore network errors */});
                return cachedResponse;
            }

            return fetch(request).then((networkResponse) => {
                if (networkResponse && (networkResponse.status === 200 || networkResponse.type === 'opaque')) {
                    const copy = networkResponse.clone();
                    caches.open(CACHE_NAME).then((cache) => cache.put(request, copy));
                }
                return networkResponse;
            }).catch(() => {
                // Offline fallback if needed
                return new Response('Asset not available offline', { status: 503, statusText: 'Offline' });
            });
        })
    );
});
