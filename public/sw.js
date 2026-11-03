/*
 * TixSwap service worker.
 * - Ticket pages (/my-tickets...) are saved when opened, so they can be
 *   shown at the gate without signal. Cleared on logout.
 * - Built assets, fonts and event images are cached for speed.
 * - Everything else goes to the network, with an offline page fallback.
 */
const VERSION = 'v2';
const STATIC = 'tixswap-static-' + VERSION;
const TICKETS = 'tixswap-tickets';
const OFFLINE_URL = '/offline';

self.addEventListener('install', (event) => {
    event.waitUntil(caches.open(STATIC).then((cache) => cache.addAll([OFFLINE_URL, '/favicon.svg', '/icon-192.png'])));
    self.skipWaiting();
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys().then((keys) => Promise.all(
            keys.filter((k) => k.startsWith('tixswap-static-') && k !== STATIC).map((k) => caches.delete(k))
        )).then(() => self.clients.claim())
    );
});

self.addEventListener('message', (event) => {
    if (event.data === 'clear-tickets') {
        event.waitUntil(caches.delete(TICKETS));
    }
});

const isTicketPage = (url) => url.pathname === '/my-tickets' || url.pathname.startsWith('/my-tickets/');
const isStatic = (url) => url.pathname.startsWith('/build/') || url.pathname.startsWith('/storage/event_images/')
    || url.hostname === 'fonts.bunny.net' || /\.(png|svg|ico|webmanifest)$/.test(url.pathname);

self.addEventListener('fetch', (event) => {
    const { request } = event;
    if (request.method !== 'GET') return;
    const url = new URL(request.url);

    // Static files: cache first, refresh in the background.
    if (isStatic(url)) {
        event.respondWith(caches.open(STATIC).then(async (cache) => {
            const cached = await cache.match(request);
            const network = fetch(request).then((res) => {
                if (res.ok) cache.put(request, res.clone());
                return res;
            }).catch(() => cached);
            return cached || network;
        }));
        return;
    }

    if (request.mode !== 'navigate' || url.origin !== self.location.origin) return;

    // Pages: network first. Ticket pages are saved for offline use.
    event.respondWith((async () => {
        try {
            const res = await fetch(request);
            if (res.ok && isTicketPage(url) && !res.redirected) {
                (await caches.open(TICKETS)).put(request, res.clone());
            }
            return res;
        } catch (e) {
            const saved = isTicketPage(url) ? await caches.match(request, { cacheName: TICKETS }) : null;
            return saved || (await caches.match(OFFLINE_URL));
        }
    })());
});
