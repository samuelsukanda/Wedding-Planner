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
self.addEventListener('install', () => self.skipWaiting());

self.addEventListener('activate', (event) => {
    event.waitUntil(self.clients.claim());
});

// Hanya meneruskan ke jaringan, tanpa cache. respondWith dipanggil
// eksplisit: sebagian versi Chrome menghitung kelayakan instal dari
// adanya handler yang benar-benar merespons, bukan listener kosong.
self.addEventListener('fetch', (event) => {
    event.respondWith(fetch(event.request));
});
