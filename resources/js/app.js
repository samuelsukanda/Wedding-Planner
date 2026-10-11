import * as Turbo from '@hotwired/turbo';

const LAST_ROUTE_KEY = 'weddingPlanner.lastRoute';
const RESTORING_ROUTE_KEY = 'weddingPlanner.restoringRoute';
const INITIAL_LOAD_KEY = 'weddingPlanner.initialLoadHandled';
const LAST_ROUTE_COOKIE = 'weddingPlannerLastRoute';
const pageCache = new Map();

if (performance.getEntriesByType('navigation')[0]?.type !== 'back_forward') {
    sessionStorage.removeItem(INITIAL_LOAD_KEY);
}

function routeFor(url) {
    const next = new URL(url, window.location.origin);
    if (next.origin !== window.location.origin) return null;
    return `${next.pathname}${next.search}${next.hash}`;
}

function rememberRoute(route) {
    if (route && route !== '/') {
        sessionStorage.setItem(LAST_ROUTE_KEY, route);
        document.cookie = `${LAST_ROUTE_COOKIE}=${encodeURIComponent(route)}; Path=/; Max-Age=28800; SameSite=Lax`;
    }
    if (route === '/') clearLastRoute();
}

function clearLastRoute() {
    sessionStorage.removeItem(LAST_ROUTE_KEY);
    sessionStorage.removeItem(RESTORING_ROUTE_KEY);
    sessionStorage.removeItem(INITIAL_LOAD_KEY);
    document.cookie = `${LAST_ROUTE_COOKIE}=; Path=/; Max-Age=0; SameSite=Lax`;
}

function internalLinkRoute(link) {
    if (!link || link.dataset.turbo === 'false' || link.target === '_blank') return null;
    const isMenuLink = link.closest('nav, aside');
    const isMasterDataTab = link.href.includes('/admin/master-data?group=');
    if (!isMenuLink && !isMasterDataTab) return null;
    return routeFor(link.href);
}

function fetchPage(route) {
    if (!pageCache.has(route)) {
        const request = fetch(route, {
            headers: { Accept: 'text/html', 'X-Requested-With': 'XMLHttpRequest' },
            credentials: 'same-origin',
        }).then(async response => {
            if (!response.ok) throw new Error(`Navigation failed: ${response.status}`);
            return response.text();
        }).catch(error => {
            pageCache.delete(route);
            throw error;
        });
        pageCache.set(route, request);
    }
    return pageCache.get(route);
}

// Mulai fetch saat pointer menyentuh menu. Klik berikutnya tidak menunggu
// request baru jika hasilnya sudah tersedia.
document.addEventListener('pointerover', function(event) {
    const route = internalLinkRoute(event.target.closest('a'));
    if (route && route !== '/') fetchPage(route).catch(() => {});
});

// Dashboard memakai route root, sehingga sengaja tidak melewati
// visitWithoutUrl(). Bersihkan state menu lama SEBELUM Turbo menangani klik;
// tanpa ini refresh Dashboard bisa me-restore Checklist sebelumnya.
document.addEventListener('click', function(event) {
    const route = internalLinkRoute(event.target.closest('a'));
    if (route === '/') clearLastRoute();
});

// Submit form menghasilkan dua visit pada Turbo: visit POST-nya sendiri dan
// visit lanjutan ke tujuan redirect. Keduanya harus dibiarkan Turbo tangani,
// kalau tidak halaman ter-fetch dua kali dan flash sukses/error habis dipakai
// pada fetch pertama sehingga toast tidak pernah tampil.
let skipNextVisitInterception = false;

document.addEventListener('turbo:before-visit', function(event) {
    if (event.detail.formSubmission || event.detail.isFormSubmission) return;

    if (skipNextVisitInterception) {
        skipNextVisitInterception = false;
        return;
    }

    const route = routeFor(event.detail.url);
    if (!route || route === '/' || event.detail.url.startsWith('http') && !event.detail.url.startsWith(window.location.origin)) {
        return;
    }

    event.preventDefault();
    rememberRoute(route);
    visitWithoutUrl(route);
});

async function visitWithoutUrl(route) {
    try {
        const html = await fetchPage(route);
        const nextDocument = new DOMParser().parseFromString(html, 'text/html');
        const nextBody = nextDocument.body;

        // Terapkan lebar sidebar ciut sebelum body masuk DOM agar tidak ada
        // flash animasi menutup setiap kali ganti menu.
        if (localStorage.getItem('wp.sidebarCollapsed') === '1') {
            nextDocument.documentElement.classList.add('sidebar-collapsed');
        }

        document.title = nextDocument.title;
        document.body.replaceWith(nextBody);
        window.history.replaceState(window.history.state, '', '/');

        // Re-execute page-specific scripts pushed into the replaced body.
        document.body.querySelectorAll('script').forEach(function(oldScript) {
            const newScript = document.createElement('script');
            [...oldScript.attributes].forEach(attribute => {
                newScript.setAttribute(attribute.name, attribute.value);
            });
            newScript.textContent = oldScript.textContent;
            oldScript.replaceWith(newScript);
        });

        document.dispatchEvent(new CustomEvent('turbo:load', { detail: { url: route } }));
    } catch (error) {
        window.location.assign(route);
    }
}

document.addEventListener('submit', function(event) {
    if (event.target.method.toLowerCase() !== 'get') {
        pageCache.clear();
        skipNextVisitInterception = true;
    }

    if (event.target.matches('form[action*="/logout"]')) {
        clearLastRoute();
    }
});

// Flatpickr milik layout app (halaman form tanggal). Head tidak di-re-execute
// oleh Turbo, jadi didaftarkan sekali di sini, bukan per-load.
document.addEventListener('turbo:load', function(event) {
    const currentRoute = routeFor(event.detail?.url || window.location.href);
    const restoringRoute = sessionStorage.getItem(RESTORING_ROUTE_KEY);

    if (restoringRoute) {
        sessionStorage.removeItem(RESTORING_ROUTE_KEY);
        rememberRoute(restoringRoute);
    }

    if (currentRoute && currentRoute !== '/') {
        rememberRoute(currentRoute);
        sessionStorage.removeItem(RESTORING_ROUTE_KEY);
        window.history.replaceState(window.history.state, '', '/');
    }

    initDatepickers();
});

// Input tanggal di dalam modal muncul setelah turbo:load, saat Alpine
// merender x-show / x-if. Observer MutationObserver sempat dicoba tapi
// flatpickr mengganti input asli dengan altInput, jadi observer melihat
// node baru lalu menginisialisasi lagi: itu loop dan bikin halaman macet.
//
// Catatan penting: kalau altInput aktif, flatpickr menaruh _flatpickr di
// altInput, bukan di elemen asli. Karena itu instance disimpan sendiri di
// el.datepicker, dan els._flatpickr tidak boleh dipakai.
/**
 * Daftarkan flatpickr pada sebuah elemen tanggal.
 */
function attachDatepicker(el) {
    if (!el) return;

    // altInput dibuat dengan cloneNode dari elemen asli, jadi mewarisi class
    // datepicker. Penanda di bawah hanya ada pada elemen yang kita pasang.
    if (el.dataset.datepickerAttached || el.classList.contains('flatpickr-input')) {
        return;
    }

    el.dataset.datepickerAttached = '1';

    const instance = flatpickr(el, {
        // Nilai ke server tetap ISO (Y-m-d) supaya validasi Laravel dan kolom
        // tanggal di database tidak berubah.
        dateFormat: 'Y-m-d',
        // Yang dilihat user: DD-MM-YYYY.
        altInput: true,
        altFormat: 'd-m-Y',
        allowInput: true,
        // altInput ikut disembunyikan kalau elemen aslinya sedang tersembunyi
        // saat dipasang, jadi type dipaksa setelah pemasangan.
        onReady: function(selectedDates, dateStr, fp) {
            if (!fp.altInput) return;

            fp.altInput.type = 'text';

            if (!fp.altInput.placeholder) {
                fp.altInput.placeholder = 'DD-MM-YYYY';
            }

            // Nilai bisa sudah terisi oleh x-model sebelum flatpickr dipasang.
            if (!dateStr && el.value) {
                fp.setDate(el.value, false);
            }
        },
        onChange: function() {
            el.dispatchEvent(new Event('input', { bubbles: true }));
        }
    });

    el.datepicker = instance;
}

function initDatepickers(root = document) {
    root.querySelectorAll('.datepicker').forEach(attachDatepicker);
}

/**
 * Segarkan nilai flatpickr yang sudah terpasang.
 *
 * Flatpickr dipasang saat modal dibuka, sebelum Alpine mengisi currentItem.
 * Akibatnya altInput masih kosong walau input aslinya sudah berisi tanggal.
 *
 * setDate() menerima string dan memparsenya mengikuti dateFormat di atas, jadi
 * tidak ada lagi risiko tanggal salah zona waktu maupun Invalid Date. Nilai
 * terakhir yang disinkronkan dicatat di dataset supaya pemanggilan ulang tidak
 * memutar onChange.
 */
function syncDatepickers(root = document) {
    root.querySelectorAll('[data-datepicker-attached]').forEach(function(el) {
        const fp = el.datepicker;
        if (!fp || !fp.altInput) return;

        // Flatpickr menyalin type dari elemen asli ke altInput. Kalau dipasang
        // selagi modal masih tersembunyi, altInput ikut hidden dan tanggalnya
        // tak pernah terlihat.
        fp.altInput.type = 'text';

        const wanted = el.value;

        if (el.dataset.syncedValue === wanted) return;
        el.dataset.syncedValue = wanted;

        if (wanted) {
            fp.setDate(wanted, true);
        } else {
            fp.clear();
        }
    });
}

// Dipakai dari watcher currentItem di view: memasang flatpickr saja tidak
// cukup karena nilainya baru diisi setelah modal terbuka.
window.syncDatepickersSoon = function () {
    setTimeout(function() {
        syncDatepickers(document);
    }, 0);
};

// Turbo menyimpan snapshot DOM sebelum pindah halaman. Kalau dibiarkan, markup
// hasil flatpickr ikut ter-cache: input asli sudah jadi type=hidden dan
// altInput sudah ada, tapi instance JS-nya hilang saat snapshot dipulihkan.
// Akibatnya field tanggal kosong dan tidak bisa diklik. Bongkar dulu sebelum
// di-cache supaya snapshot menyimpan input yang bersih.
document.addEventListener('turbo:before-cache', function () {
    document.querySelectorAll('[data-datepicker-attached]').forEach(function (el) {
        if (el.datepicker) {
            el.datepicker.destroy();
            el.datepicker = null;
        }

        delete el.dataset.datepickerAttached;
    });
});

document.addEventListener('alpine:initialized', function () {
    initDatepickers();
    syncDatepickers();
});

Turbo.start();
