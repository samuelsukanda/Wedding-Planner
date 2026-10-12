// Filter tabel tanpa reload, untuk semua halaman daftar.
//
// Pola pakai:
//
//   <div x-data="tableFilter({ fields: ['search','category'], rows: [...] })">
//     <input x-model="filters.search">
//     <tr x-show="visible(rows[0])">
//
// Server mengirim semua baris sekali, penyaringan terjadi di browser, jadi
// mengganti filter tidak memicu request sama sekali.
//
// File ini HARUS dimuat sebelum Alpine. Alpine dimuat dari CDN dengan `defer`
// dan langsung menjalankan start() begitu dievaluasi; kalau helper ini datang
// setelahnya, Alpine sudah selesai membaca atribut dan x-data tidak pernah
// ter-resolve.
window.tableFilter = function (config = {}) {
    return {
        filters: config.initial ?? {},

        // Properti milik halaman (modal, tab, dsb) digabung ke scope yang sama
        // supaya filter dan UI halaman berbagi satu x-data.
        ...(config.extra ?? {}),

        rows: config.rows ?? [],
        fields: config.fields ?? [],

        init() {
            // Halaman menitipkan init() sendiri lewat `extra`. Karena x-data
            // dipegang tableFilter, hook halaman harus dipanggil dari sini:
            // kalau hanya dibiarkan sebagai properti, Alpine tidak
            // memperlakukannya sebagai lifecycle method dan watcher di
            // dalamnya tidak pernah terdaftar.
            if (typeof config.extra?.init === 'function') {
                config.extra.init.call(this);
            }
        },

        /**
         * Satu baris tampil bila setiap filter yang terisi cocok dengan
         * nilainya. Pencocokan teks longgar: case-insensitive dan mengabaikan
         * spasi berlebih, jadi "Bpk. Budi Santoso" tetap ketemu saat user
         * mengetik "budisantoso".
         */
        rowMatches(row) {
            return this.fields.every((field) => {
                const wanted = (this.filters[field] ?? '').toString().trim().toLowerCase();
                if (!wanted) return true;

                const actual = (row[field] ?? '').toString().toLowerCase();

                return actual.replace(/\s+/g, '').includes(wanted.replace(/\s+/g, ''));
            });
        },

        visible(row) {
            return this.rowMatches(row);
        },

        visibleCount() {
            return this.rows.filter((row) => this.rowMatches(row)).length;
        },

        hasActiveFilter() {
            return this.fields.some((field) => (this.filters[field] ?? '').toString().trim() !== '');
        },

        reset() {
            this.fields.forEach((field) => {
                this.filters[field] = '';
            });
        },
    };
};

/**
 * x-outside: tutup modal hanya kalau klik benar-benar mendarat di luar.
 *
 * x-click.away bawaan Alpine terlalu sensitif untuk modal yang berisi
 * datepicker:
 *
 * - Di HP, kalender flatpickr menempel di <body>, jadi tap pertama untuk
 *   memilih tanggal dianggap "klik di luar" dan modal ikut tertutup.
 * - Di desktop, menyorot teks lalu menarik mouse ke luar juga memicu
 *   penutupannya, padahal itu bukan niat menutup.
 *
 * Directive ini menutup modal hanya bila pointerdown DAN click sama-sama
 * terjadi di luar, dan mengabaikan kalender. Tombol X dan Batal tetap bekerja
 * seperti biasa karena keduanya memanggil aksi secara langsung.
 *
 * Daftar directive ini harus ada di file ini, bukan app.js, karena Alpine
 * baru menjalankan start() setelah helper dimuat — lihat catatan di atas file.
 */
document.addEventListener('alpine:init', () => {
    window.__alpineInitFired = (window.__alpineInitFired || 0) + 1;
    Alpine.directive('outside', (el, { expression }, { evaluate, cleanup }) => {
        let downInside = false;

        const isInside = (target) =>
            !!target
            && (el.contains(target)
                || (target.closest && target.closest('.flatpickr-calendar')));

        // Listener duduk di document, bukan di el. Overlay modal adalah induk
        // dari el, jadi pointerdown di overlay tak akan melewati el dan
        // penanda downInside akan basi.
        const onDown = (e) => {
            downInside = isInside(e.target);
        };

        const onClose = (e) => {
            // Modal yang sedang tersembunyi (x-show) tidak boleh merespons
            // klik. Tanpa guard ini, directive belonging ke modal yang
            // tertutup ikut menutup modal lain yang kebetulan berbagi state.
            if (el.offsetWidth < 1 && el.offsetHeight < 1) return;

            // Seret dari dalam ke luar = seleksi teks, bukan menutup modal.
            if (downInside || isInside(e.target)) return;

            evaluate(expression);
        };

        document.addEventListener('pointerdown', onDown, true);
        document.addEventListener('click', onClose, true);
        document.addEventListener('touchend', onClose, true);

        cleanup(() => {
            document.removeEventListener('pointerdown', onDown, true);
            document.removeEventListener('click', onClose, true);
            document.removeEventListener('touchend', onClose, true);
        });
    });
});