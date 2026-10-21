/**
 * Service worker minimal: hanya memenuhi syarat instalasi PWA.
 *
 * Sengaja TIDAK melakukan caching. Halaman aplikasi ini dinamis
 * (data akun, profil, panel admin per wedding kit), jadi menyajikan
 * salinan cache berpotensi menampilkan data basi atau milik akun lain.
 * Tanpa fetch cache, perilaku jaringan tetap sama persis dengan
 * halaman biasa; sw.js punya syarat sama (https, di-root) demi
 * beforeinstallprompt Chrome/Edge.
 */

const CACHE_NAME = 'nupsera-v2';

self.addEventListener('install', (event) => {
    // Pre-cache halaman offline sederhana agar ada respons offline
    // (syarat Chrome agar PWA dianggap installable).
    event.waitUntil(
        caches.open(CACHE_NAME).then((cache) => {
            return cache.addAll([
                '/offline.html'
            ]);
        })
    );
    self.skipWaiting();
});

self.addEventListener('activate', (event) => {
    // Hapus cache lama jika ada versi baru
    event.waitUntil(
        caches.keys().then((names) => {
            return Promise.all(
                names
                    .filter((name) => name !== CACHE_NAME)
                    .map((name) => caches.delete(name))
            );
        }).then(() => self.clients.claim())
    );
});

self.addEventListener('fetch', (event) => {
    // Hanya intercept request http/https — abaikan chrome-extension://,
    // data:, blob:, dll. yang menyebabkan fetch() error.
    if (!event.request.url.startsWith('http')) {
        return;
    }

    // Strategi network-first: coba jaringan dulu, kalau gagal tampilkan
    // halaman offline untuk navigasi (bukan aset).
    if (event.request.mode === 'navigate') {
        event.respondWith(
            fetch(event.request).catch(() => {
                return caches.match('/offline.html');
            })
        );
        return;
    }

    // Request non-navigasi: langsung teruskan ke jaringan tanpa cache.
    event.respondWith(
        fetch(event.request).catch(() => {
            return new Response('', { status: 408, statusText: 'Offline' });
        })
    );
});
