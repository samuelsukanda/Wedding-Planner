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
        fieldNames: config.fieldNames ?? {},

        init() {
            this.$watch('filters', () => this.syncQueryString(), { deep: true });
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

        /**
         * Filter ditulis ke query string supaya status bisa dibagikan dan
         * tetap ada saat tombol back Turbo ditekan. replaceState dipakai supaya
         * tiap ketikan tidak menambah history entry.
         */
        syncQueryString() {
            const params = new URLSearchParams();

            this.fields.forEach((field) => {
                const value = (this.filters[field] ?? '').toString().trim();
                if (value) params.set(this.fieldNames[field] ?? field, value);
            });

            const query = params.toString();
            const url = query ? `${window.location.pathname}?${query}` : window.location.pathname;

            window.history.replaceState(window.history.state, '', url);
        },
    };
};