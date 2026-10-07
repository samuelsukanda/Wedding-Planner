import * as Turbo from '@hotwired/turbo';

const LAST_ROUTE_KEY = 'weddingPlanner.lastRoute';
const RESTORING_ROUTE_KEY = 'weddingPlanner.restoringRoute';
const INITIAL_LOAD_KEY = 'weddingPlanner.initialLoadHandled';
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
        document.cookie = `${LAST_ROUTE_KEY}=${encodeURIComponent(route)}; Max-Age=86400; Path=/; SameSite=Lax`;
    }
    if (route === '/') clearLastRoute();
}

function clearLastRoute() {
    sessionStorage.removeItem(LAST_ROUTE_KEY);
    document.cookie = `${LAST_ROUTE_KEY}=; Max-Age=0; Path=/; SameSite=Lax`;
}

function setPageLoader(visible) {
    document.getElementById('page-loader')?.classList.toggle('is-visible', visible);
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

document.addEventListener('turbo:before-visit', function(event) {
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
    if (event.target.method.toLowerCase() !== 'get') pageCache.clear();
    if (event.target.matches('form[action*="/logout"]')) {
        clearLastRoute();
    }
});

// Restore menu terakhir hanya saat browser membuka/refresh dokumen root,
// bukan pada setiap turbo:load internal.
document.addEventListener('DOMContentLoaded', function() {
    const currentRoute = routeFor(window.location.href);
    const lastRoute = sessionStorage.getItem(LAST_ROUTE_KEY);

    if (currentRoute === '/' && lastRoute && !sessionStorage.getItem(INITIAL_LOAD_KEY)) {
        const alreadyRendered = lastRoute.startsWith('/admin/master-data')
            ? document.body.innerText.includes('Kelola Master Dropdown')
            : lastRoute === '/admin/users' && document.body.innerText.includes('Kelola seluruh user');

        sessionStorage.setItem(INITIAL_LOAD_KEY, '1');
        if (alreadyRendered) {
            setPageLoader(false);
            return;
        }
        setPageLoader(true);
        sessionStorage.setItem(RESTORING_ROUTE_KEY, lastRoute);
        Turbo.visit(lastRoute, { action: 'replace' });
        return;
    }

    setPageLoader(false);
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
        setPageLoader(false);
    } else if (!sessionStorage.getItem(LAST_ROUTE_KEY) || !sessionStorage.getItem(RESTORING_ROUTE_KEY)) {
        setPageLoader(false);
    }

    document.querySelectorAll('.datepicker').forEach(function(el) {
        if (el._flatpickr) return;
        flatpickr(el, {
            dateFormat: 'Y-m-d',
            allowInput: true,
            onChange: function() {
                el.dispatchEvent(new Event('input', { bubbles: true }));
            }
        });
    });
});

Turbo.start();
