/**
 * ServicEngine Service Worker
 */

const CACHE_NAME = 'amc-servicengine-v1';
const ASSETS_TO_CACHE = [
    './',
    'assets/css/custom.css',
    'assets/css/mobile-engineer.css',
    'assets/js/app.js',
    'assets/js/fast-search.js',
    'assets/js/signature-pad.js',
    'assets/js/offline-sync.js',
    'https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css',
    'https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css',
    'https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js'
];

self.addEventListener('install', (e) => {
    e.waitUntil(
        caches.open(CACHE_NAME).then((cache) => {
            return cache.addAll(ASSETS_TO_CACHE);
        })
    );
    self.skipWaiting();
});

self.addEventListener('activate', (e) => {
    e.waitUntil(
        caches.keys().then((keys) => {
            return Promise.all(
                keys.map((k) => {
                    if (k !== CACHE_NAME) return caches.delete(k);
                })
            );
        })
    );
    self.clients.claim();
});

self.addEventListener('fetch', (e) => {
    // Only cache GET requests for static assets
    if (e.request.method === 'GET' && (e.request.url.includes('/assets/') || e.request.url.includes('cdn.jsdelivr.net'))) {
        e.respondWith(
            caches.match(e.request).then((cachedResponse) => {
                return cachedResponse || fetch(e.request);
            })
        );
    }
});
