<!DOCTYPE html>
<html lang="id" class="h-full">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    {{-- Halaman dinamis: jangan pernah tampilkan snapshot basi saat kembali/back. --}}
    <meta name="turbo-cache-control" content="no-cache">
    <title>Nupsera - Wedding &amp; Moments</title>
    <link rel="canonical" href="{{ url()->current() }}">
    <meta name="description"
        content="Nupsera: Aplikasi wedding planner dan photobooth virtual pernikahan. Kelola anggaran, jadwal vendor, dan undangan secara serba otomatis.">
    <link rel="shortcut icon" href="{{ asset('img/nupsera-ori.jpg') }}" type="image/x-icon">
    {{-- PWA: manifest + ikon iOS (Safari tidak membaca manifest ikon) --}}
    <link rel="manifest" href="{{ asset('manifest.json') }}">
    <meta name="theme-color" content="#D8A7B1">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="Nupsera">
    <link rel="apple-touch-icon" href="{{ asset('img/icon-192.png') }}">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&family=Playfair+Display:ital,wght@0,500;0,600;0,700;1,600&display=swap"
        rel="stylesheet">

    <!-- FontAwesome Icons & Chart.js -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <!-- Helper filter tabel. Entry Vite sendiri (bukan digabung dengan app.js)
         supaya tagnya bisa diletakkan sebelum Alpine: Alpine start() begitu
         dievaluasi, jadi x-data="tableFilter(...)" hanya resolve kalau
         window.tableFilter sudah ada. -->
    @vite(['resources/js/table-filter.js'])
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <!-- SweetAlert2 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- Flatpickr Datepicker -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

    <script>
        // Dipasang sebelum <body> dirender agar sidebar yang sebelumnya ciut
        // tidak sempat melebar lalu menciut lagi saat Alpine mulai.
        if (localStorage.getItem('wp.sidebarCollapsed') === '1') {
            document.documentElement.classList.add('sidebar-collapsed');
        }
    </script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        /* Override SweetAlert2 button colors */
        .swal2-confirm {
            background-color: #5F6F5B !important;
            font-weight: 700 !important;
            border-radius: 12px !important;
        }

        .swal2-cancel {
            background-color: #FAF7F2 !important;
            color: #5F6F5B !important;
            border: 1px solid #B6ADA3 !important;
            font-weight: 700 !important;
            border-radius: 12px !important;
        }

        .swal2-cancel:hover {
            background-color: #efe9e1 !important;
        }

        .swal2-popup {
            border-radius: 20px !important;
            font-family: 'Outfit', sans-serif !important;
        }

        .swal2-title {
            font-family: 'Playfair Display', serif !important;
            color: #5F6F5B !important;
        }

        .swal2-html-container {
            color: #5F6F5B !important;
        }

        /* ============================================================
           PRINT STYLES — targets precise IDs, hides UI chrome
        ============================================================ */
        @media print {

            @page {
                margin: 1.2cm;
            }

            /* ---- Hide sidebar, mobile top bar, desktop header, footer, modal, action buttons ---- */
            aside,
            header,
            footer,
            .no-print,
            .mobile-bottom-nav {
                display: none !important;
                visibility: hidden !important;
            }

            /* ---- Color and font settings ---- */
            * {
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
                box-shadow: none !important;
                text-shadow: none !important;
            }

            /* ---- Reset body layout (flex â†’ block for print) ---- */
            html {
                height: auto !important;
            }

            body {
                display: block !important;
                width: 100% !important;
                height: auto !important;
                background: #ffffff !important;
                color: inherit !important;
                margin: 0 !important;
                padding: 0 !important;
            }

            /* ---- Main wrapper: full width, block layout ---- */
            #main-content {
                display: block !important;
                width: 100% !important;
                max-width: 100% !important;
                margin: 0 !important;
                padding: 0 !important;
                background: #ffffff !important;
                flex: none !important;
                height: auto !important;
                overflow: visible !important;
            }

            /* ---- Content yield wrapper: full width (page margin handled by @page) ---- */
            #page-content {
                display: block !important;
                width: 100% !important;
                max-width: 100% !important;
                margin: 0 !important;
                padding: 0 !important;
                background: #ffffff !important;
                flex: none !important;
                height: auto !important;
                overflow: visible !important;
            }

            /* ---- Show print-only header (hidden by default on screen) ---- */
            .print-header {
                display: block !important;
                visibility: visible !important;
                text-align: center;
                border-bottom: 2px solid #5F6F5B;
                margin-bottom: 16pt;
                padding-bottom: 8pt;
            }

            .print-header h1 {
                font-size: 16pt;
                font-weight: bold;
                color: #5F6F5B !important;
                margin: 0 0 4pt;
            }

            .print-header p {
                font-size: 10pt;
                color: #555 !important;
                margin: 0;
            }

            /* ---- Prevent page breaks inside cards/rows ---- */
            #page-content>div:not(#report-content),
            #report-content>.grid>div,
            #report-content>.report-print-card,
            tr {
                break-inside: avoid;
                page-break-inside: avoid;
            }

            a {
                color: inherit !important;
                text-decoration: none !important;
            }
        }

        /* ---- Sidebar dalam keadaan ter-ciut ----
           Hanya ikon menu yang terlihat. Selector ini sengaja menyasar
           .sidebar-label, isi <nav> (span + header section), dan avatar,
           supaya tiap menu tidak perlu diubah satu per satu. */
        @media (min-width: 768px) {
            html.sidebar-collapsed .sidebar {
                width: 4rem !important;
            }

            /* Dipakai sebelum Alpine selesai hydrate, termasuk setelah Turbo
               form submit. Ini mencegah lebar w-64 terlihat sesaat. */
            html.sidebar-collapsed:not(.sidebar-toggling) .sidebar {
                transition-property: transform;
            }

            .sidebar.is-collapsed .sidebar-label,
            .sidebar.is-collapsed nav span,
            .sidebar.is-collapsed .nav-section,
            html.sidebar-collapsed .sidebar .sidebar-label,
            html.sidebar-collapsed .sidebar nav span,
            html.sidebar-collapsed .sidebar .nav-section {
                display: none;
            }

            .sidebar.is-collapsed nav a,
            html.sidebar-collapsed .sidebar nav a {
                justify-content: center;
                padding-left: 0.5rem;
                padding-right: 0.5rem;
            }

            .sidebar.is-collapsed .shrink-0.border-t,
            html.sidebar-collapsed .sidebar .shrink-0.border-t {
                padding-left: 0.5rem;
                padding-right: 0.5rem;
            }

            .sidebar.is-collapsed>div:first-of-type,
            html.sidebar-collapsed .sidebar>div:first-of-type {
                justify-content: center;
            }

            .sidebar.is-collapsed>div:first-of-type a,
            html.sidebar-collapsed .sidebar>div:first-of-type a {
                justify-content: center;
                width: 100%;
            }

            .sidebar.is-collapsed .shrink-0.border-t>div,
            html.sidebar-collapsed .sidebar .shrink-0.border-t>div {
                flex-direction: column;
                justify-content: center;
                padding-top: 0.75rem;
                padding-bottom: 0.75rem;
                /* Kolom ciut: padding horizontal px-4 bawaan menyisakan
                   ~16px untuk konten sehingga max-width preflight ikut
                   mengecilkan <img>. Nolkan supaya avatar muat penuh. */
                padding-left: 0;
                padding-right: 0;
            }

            /* Foto profil harus tetap w-9 h-9 saat sidebar ciut. Tanpa ini,
               max-width: 100% preflight menjepit <img> ke lebar konten yang
               sempit (tidak terjadi pada <div> inisial, makanya yang tanpa
               foto terlihat normal). */
            .sidebar.is-collapsed .shrink-0.border-t img,
            html.sidebar-collapsed .sidebar .shrink-0.border-t img {
                max-width: none;
            }

            .sidebar.is-collapsed .shrink-0.border-t form,
            html.sidebar-collapsed .sidebar .shrink-0.border-t form {
                display: flex;
            }
        }
    </style>

    <script>
        // Global SweetAlert2 delete confirmation helper
        function confirmDelete(formId, itemName) {
            Swal.fire({
                title: 'Hapus Data?',
                html: itemName ?
                    `Apakah Anda yakin ingin menghapus <strong>${itemName}</strong>? Tindakan ini tidak dapat dibatalkan.` :
                    'Apakah Anda yakin ingin menghapus data ini? Tindakan ini tidak dapat dibatalkan.',
                icon: 'warning',
                iconColor: '#D8A7B1',
                showCancelButton: true,
                confirmButtonText: '<i class="fa-solid fa-trash mr-1"></i> Ya, Hapus',
                cancelButtonText: 'Batal',
                reverseButtons: true,
            }).then((result) => {
                if (result.isConfirmed) {
                    // requestSubmit(), bukan submit(): form.submit() bypass event
                    // submit sehingga Turbo tidak bisa mengintercept navigasi.
                    document.getElementById(formId).requestSubmit();
                }
            });
        }

        // Helper format input uang & telepon untuk form modal semua modul.
        // Model Alpine menyimpan angka polos; tampilan input diformat ulang
        // lewat binding :value sehingga kursor tidak lompat saat mengetik.
        //
        // Batas digit dipotong di sini, satu-satunya tempat yang dipakai semua
        // input uang dan telepon di seluruh aplikasi:
        //   - uang    : kolom decimal(15,2) hanya muat 13 digit bulat, sisa 2
        //                digit dipakai untuk pecahan. Digit lebih dari itu akan
        //                ditolak database (SQLSTATE 22003).
        //   - telepon : 13 digit sudah lebih dari cukup untuk nomor Indonesia
        //                (kode negara + nomor).
        window.wpDigitLimit = 13;

        window.wpDigits = function(value, max) {
            const digits = String(value ?? '').replace(/\D/g, '');
            const limit = max ?? window.wpDigitLimit;

            return limit ? digits.slice(0, limit) : digits;
        };

        window.wpMoney = function(value) {
            const digits = window.wpDigits(value);
            return digits ? 'Rp ' + Number(digits).toLocaleString('id-ID') : '';
        };

        window.wpPhone = function(value) {
            return window.wpDigits(value).replace(/(\d{4})(?=\d)/g, '$1-');
        };

        // Panggil dari @input setiap kali digit dipotong batas, supaya kotak
        // input tetap menampilkan formatnya ("Rp 1.212.122.121") alih-alih
        // angka polos mentah.
        //
        // Format sengaja hanya ditulis ulang saat ada digit yang benar-benar
        // dipotong. Kalau diformat tiap ketikan, kursor meloncat ke akhir
        // field dan mengetik menjadi ribet — itu sebabnya binding :value dipakai
        // sebagai pemformat, bukan penulisan langsung.
        window.wpClamp = function(el, format) {
            const raw = String(el.value ?? '').replace(/\D/g, '');

            if (raw.length <= window.wpDigitLimit) {
                return;
            }

            el.value = format(raw.slice(0, window.wpDigitLimit));
        };

        // Blokir huruf di input digit (uang & no HP) tepat di sumbernya.
        //
        // Latar: :value="wpMoney(...)" hanya me-render ulang saat model
        // berubah, jadi huruf yang diketik saat digit tidak berubah tetap
        // terlihat di kotak. Listener ini mencegatnya sebelum masuk (ketik,
        // paste, drop) sehingga @input/wpDigits yang sudah ada tidak perlu
        // diubah. Delegated di document: berlaku untuk semua modal/halaman.
        document.addEventListener('beforeinput', function(e) {
            const el = e.target && e.target.closest ?
                e.target.closest('.form-input-number, input[inputmode="numeric"]') :
                null;
            if (!el || el.disabled || el.readOnly) return;

            // Hanya jenis penyisipan teks; penghapusan & navigasi tidak dicegat.
            // Composition (IME/swipe keyboard HP) dibiarkan lewat dan tetap
            // dibersihkan oleh @input/wpDigits seperti sekarang.
            if (e.inputType !== 'insertText' &&
                e.inputType !== 'insertFromPaste' &&
                e.inputType !== 'insertFromDrop') return;

            if (/\D/.test(e.data || '')) e.preventDefault();
        });

        // Batasi input angka (jumlah pax, dan sejenisnya) agar tidak bisa
        // diketik melewati batas. Dipakai lewat @input karena maxlength tidak
        // berlaku untuk <input type="number">.
        window.wpCap = function(el, max) {
            const value = Number(el.value);

            if (Number.isFinite(value) && value > max) {
                el.value = max;
                return max;
            }

            return el.value;
        };

        // Simpan & restore posisi scroll sidebar (agar tidak kembali ke atas)
        // Pakai turbo:load, bukan DOMContentLoaded: DOMContentLoaded hanya
        // terjadi sekali, sedangkan Turbo mengganti <body> tiap perpindahan menu.
        document.addEventListener('turbo:load', function() {
            var sidebarNav = document.querySelector('nav.flex-1.overflow-y-auto');
            if (sidebarNav) {
                var saved = sessionStorage.getItem('sidebarScrollPos');
                if (saved) sidebarNav.scrollTop = parseInt(saved, 10);
                sidebarNav.addEventListener('click', function(e) {
                    var link = e.target.closest('a');
                    if (link && link.getAttribute('href')) {
                        sessionStorage.setItem('sidebarScrollPos', sidebarNav.scrollTop);
                    }
                });
            }
        });

        // Simpan & restore posisi scroll halaman Master Data (admin panel).
        // Turbo mengganti <body> tiap perpindahan menu, jadi pemeriksaan
        // pathname harus di dalam event, bukan di awal IIFE — kalau placed
        // di awal, script ini (di <head>) hanya jalan sekali dan listener
        // scroll tidak pernah terdaftar saat masuk lewat klik menu.
        (function() {
            var isMasterData = function(url) {
                return new URL(url || window.location.href, window.location.origin)
                    .pathname.indexOf('/admin/master-data') !== -1;
            };

            if (window.history && window.history.scrollRestoration) {
                window.history.scrollRestoration = 'manual';
            }

            // Elemen scroll utama adalah #main-content (overflow-y-auto), BUKAN window
            var scrollContainer = function() {
                return document.querySelector('#main-content');
            };

            // Simpan scroll & flag hanya saat klik group dropdown di admin
            document.addEventListener('click', function(e) {
                var link = e.target.closest('a[href*="/admin/master-data"][href*="group="]');
                if (!link || !isMasterData(link.href)) return;
                var el = scrollContainer();
                if (el) {
                    sessionStorage.setItem('adminScrollY', el.scrollTop);
                    sessionStorage.setItem('adminRestore', '1');
                }
            });

            // Restore scroll hanya jika ada flag (berarti navigasi dari klik group dropdown)
            document.addEventListener('turbo:load', function(event) {
                if (!isMasterData(event.detail && event.detail.url)) return;
                if (sessionStorage.getItem('adminRestore') !== '1') return;
                sessionStorage.removeItem('adminRestore');

                var saved = sessionStorage.getItem('adminScrollY');
                if (!saved) return;
                var el = scrollContainer();
                if (el) el.scrollTop = parseInt(saved, 10);
            });
        })();
    </script>
</head>

<body class="h-full flex flex-col md:flex-row antialiased selection:bg-[#D8A7B1] selection:text-white"
    x-data="{ sidebarOpen: false, sidebarCollapsed: localStorage.getItem('wp.sidebarCollapsed') === '1' }"
    @sidebar-collapsed.window="sidebarCollapsed = $event.detail; localStorage.setItem('wp.sidebarCollapsed', $event.detail ? '1' : '0'); document.documentElement.classList.toggle('sidebar-collapsed', $event.detail)">

    <!-- Mobile Header -->
    <header
        class="md:hidden flex items-center justify-between px-4 py-3 bg-[#FAF7F2]/95 border-b border-[#B6ADA3]/40 backdrop-blur sticky top-0 z-40 shadow-sm">
        <div class="flex items-center gap-3">
            @if ($isSuper ?? false)
                <button @click="sidebarOpen = true"
                    class="text-[#5F6F5B] hover:text-[#2D372E] p-2 rounded-xl border border-[#B6ADA3]/30 bg-white cursor-pointer"
                    aria-label="Buka Menu Navigation">
                    <i class="fa-solid fa-bars text-lg" aria-hidden="true"></i>
                </button>
            @endif
            <a href="{{ route('dashboard') }}" class="flex items-center gap-2">
                <i class="fa-solid fa-heart text-[#8B3A47] text-lg" aria-hidden="true"></i>
                <span class="font-serif-title font-bold text-lg text-[#5F6F5B]">{{ $brandName ?? 'Nupsera' }}</span>
            </a>
        </div>

        <!-- Mobile Days Badge -->
        @unless ($isSuper ?? false)
            <div
                class="flex items-center gap-1.5 px-3 py-1 rounded-full bg-[#FCE8EC] text-[#8B3A47] text-xs font-semibold border border-[#D8A7B1]/60">
                <i class="fa-solid fa-calendar-days text-[#8B3A47]" aria-hidden="true"></i>
                <span>{{ $daysLeft ?? 0 }} Hari</span>
            </div>
        @endunless
    </header>

    <!-- Sidebar Backdrop for Mobile -->
    <div x-show="sidebarOpen" x-transition.opacity @click="sidebarOpen = false"
        class="hidden fixed inset-0 bg-[#5F6F5B]/50 backdrop-blur-sm z-50 md:hidden" x-cloak></div>

    <!-- Sidebar Navigation -->
    <aside
        :class="[sidebarOpen ? 'translate-x-0' : '-translate-x-full md:translate-x-0', sidebarCollapsed ?
            'is-collapsed md:w-16' : 'md:w-64'
        ]"
        class="sidebar {{ $isSuper ?? false ? 'flex' : 'hidden' }} md:flex fixed md:relative inset-y-0 left-0 z-50 w-64 md:transition-[width,transform] duration-300 ease-in-out bg-white border-r border-[#B6ADA3]/35 flex-col shadow-lg md:shadow-none">

        <!-- Logo & Header. Baris di bawah logo adalah merek, bukan nama pasangan:
                 nama pasangan sudah tampil di header utama, jadi mengulangnya
                 di sini hanya memakan ruang dan membuat dua baris brand
                 saling tumpang tindih. -->
        <div class="px-4 py-3 border-b border-[#B6ADA3]/30 flex items-center justify-between bg-[#FAF7F2]/60">
            <a href="{{ route('dashboard') }}" class="flex items-center gap-2 min-w-0">
                {{-- Logo ikon: nupsera-ori.png ditampilkan seukuran tinggi teks
                     (h-8 / 32px) agar proporsional. object-contain memastikan
                     seluruh logo tampil tanpa terpotong. --}}
                <img src="{{ asset('img/nupsera-ori.png') }}" alt="Logo Nupsera" width="47" height="32"
                    class="shrink-0 object-contain" style="height:2rem;width:auto;max-width:2.5rem;">
                <div class="sidebar-label min-w-0 leading-none">
                    {{-- "Nupsera" — gold/copper gradient sesuai logo-brand.jpg --}}
                    <div class="font-serif-title font-bold text-[1.1rem] leading-tight truncate"
                        style="background:linear-gradient(135deg,#8B4513 0%,#C07830 35%,#D4A96A 60%,#B8762A 100%);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;">
                        Nupsera</div>
                    {{-- "WEDDING & MOMENTS" — sans-serif, uppercase kecil, warna gelap sesuai referensi --}}
                    <p class="whitespace-nowrap font-semibold uppercase tracking-widest"
                        style="font-size:0.55rem;color:#4A5240;letter-spacing:0.18em;">Wedding &amp; Moments</p>
                </div>
            </a>
            <button @click="sidebarOpen = false"
                class="md:hidden text-[#5F6F5B] hover:text-[#2D372E] p-1.5 cursor-pointer"
                aria-label="Tutup sidebar">
                <i class="fa-solid fa-xmark text-xl" aria-hidden="true"></i>
            </button>
        </div>

        <!-- Navigation Links (All 11 PRD Modules) -->
        <nav class="flex-1 min-h-0 overflow-y-auto px-3 py-4 space-y-1">
            @unless (auth()->user()?->is_superadmin)
                <div class="nav-section px-3 pb-2 text-[10px] font-bold uppercase tracking-wider text-[#5F6F5B]">Utama
                </div>

                <a href="{{ route('dashboard') }}"
                    class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-all {{ request()->routeIs('dashboard') ? 'bg-[#D8A7B1]/20 text-[#5F6F5B] font-bold border border-[#D8A7B1]/40' : 'text-[#4E473F] hover:bg-[#FAF7F2] hover:text-[#2D372E]' }}">
                    <i class="fa-solid fa-chart-pie w-5 text-center text-[#D8A7B1]" aria-hidden="true"></i>
                    <span>Dashboard</span>
                </a>

                <div class="nav-section px-3 pt-4 pb-2 text-[10px] font-bold uppercase tracking-wider text-[#5F6F5B]">
                    Perencanaan</div>

                <a href="{{ route('checklists.index') }}"
                    class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-all {{ request()->routeIs('checklists.*') ? 'bg-[#D8A7B1]/20 text-[#5F6F5B] font-bold border border-[#D8A7B1]/40' : 'text-[#4E473F] hover:bg-[#FAF7F2] hover:text-[#2D372E]' }}">
                    <i class="fa-solid fa-list-check w-5 text-center text-[#D8A7B1]" aria-hidden="true"></i>
                    <span>Checklist</span>
                </a>

                <a href="{{ route('vendors.index') }}"
                    class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-all {{ request()->routeIs('vendors.*', 'vendor-directory.*') ? 'bg-[#D8A7B1]/20 text-[#5F6F5B] font-bold border border-[#D8A7B1]/40' : 'text-[#4E473F] hover:bg-[#FAF7F2] hover:text-[#2D372E]' }}">
                    <i class="fa-solid fa-store w-5 text-center text-[#D8A7B1]" aria-hidden="true"></i>
                    <span>Vendor Management</span>
                </a>

                <a href="{{ route('guests.index') }}"
                    class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-all {{ request()->routeIs('guests.*') ? 'bg-[#D8A7B1]/20 text-[#5F6F5B] font-bold border border-[#D8A7B1]/40' : 'text-[#4E473F] hover:bg-[#FAF7F2] hover:text-[#2D372E]' }}">
                    <i class="fa-solid fa-users w-5 text-center text-[#D8A7B1]" aria-hidden="true"></i>
                    <span>Guest Management</span>
                </a>

                <a href="{{ route('rundowns.index') }}"
                    class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-all {{ request()->routeIs('rundowns.*') ? 'bg-[#D8A7B1]/20 text-[#5F6F5B] font-bold border border-[#D8A7B1]/40' : 'text-[#4E473F] hover:bg-[#FAF7F2] hover:text-[#2D372E]' }}">
                    <i class="fa-solid fa-clock w-5 text-center text-[#D8A7B1]" aria-hidden="true"></i>
                    <span>Rundown Acara</span>
                </a>

                <a href="{{ route('proposals.index') }}"
                    class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-all {{ request()->routeIs('proposals.*') ? 'bg-[#D8A7B1]/20 text-[#5F6F5B] font-bold border border-[#D8A7B1]/40' : 'text-[#4E473F] hover:bg-[#FAF7F2] hover:text-[#2D372E]' }}">
                    <i class="fa-solid fa-ring w-5 text-center text-[#D8A7B1]" aria-hidden="true"></i>
                    <span>Lamaran</span>
                </a>

                <a href="{{ route('souvenirs.index') }}"
                    class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-all {{ request()->routeIs('souvenirs.*') ? 'bg-[#D8A7B1]/20 text-[#5F6F5B] font-bold border border-[#D8A7B1]/40' : 'text-[#4E473F] hover:bg-[#FAF7F2] hover:text-[#2D372E]' }}">
                    <i class="fa-solid fa-gift w-5 text-center text-[#D8A7B1]" aria-hidden="true"></i>
                    <span>Seserahan</span>
                </a>

                <a href="{{ route('documents.index') }}"
                    class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-all {{ request()->routeIs('documents.*') ? 'bg-[#D8A7B1]/20 text-[#5F6F5B] font-bold border border-[#D8A7B1]/40' : 'text-[#4E473F] hover:bg-[#FAF7F2] hover:text-[#2D372E]' }}">
                    <i class="fa-solid fa-file-circle-check w-5 text-center text-[#D8A7B1]" aria-hidden="true"></i>
                    <span>Persyaratan Nikah</span>
                </a>

                <div class="nav-section px-3 pt-4 pb-2 text-[10px] font-bold uppercase tracking-wider text-[#5F6F5B]">
                    Kreatif
                </div>

                <a href="{{ route('moodboards.index') }}"
                    class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-all {{ request()->routeIs('moodboards.*') ? 'bg-[#D8A7B1]/20 text-[#5F6F5B] font-bold border border-[#D8A7B1]/40' : 'text-[#4E473F] hover:bg-[#FAF7F2] hover:text-[#2D372E]' }}">
                    <i class="fa-solid fa-palette w-5 text-center text-[#D8A7B1]" aria-hidden="true"></i>
                    <span>Moodboard</span>
                </a>

                <a href="{{ route('photobooth.index') }}"
                    class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-all {{ request()->routeIs('photobooth.*') ? 'bg-[#D8A7B1]/20 text-[#5F6F5B] font-bold border border-[#D8A7B1]/40' : 'text-[#4E473F] hover:bg-[#FAF7F2] hover:text-[#2D372E]' }}">
                    <i class="fa-solid fa-camera-retro w-5 text-center text-[#D8A7B1]" aria-hidden="true"></i>
                    <span>Photobooth</span>
                </a>

                <div class="nav-section px-3 pt-4 pb-2 text-[10px] font-bold uppercase tracking-wider text-[#5F6F5B]">
                    Keuangan & Dokumen
                </div>

                <a href="{{ route('budgets.index') }}"
                    class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-all {{ request()->routeIs('budgets.*') ? 'bg-[#D8A7B1]/20 text-[#5F6F5B] font-bold border border-[#D8A7B1]/40' : 'text-[#4E473F] hover:bg-[#FAF7F2] hover:text-[#2D372E]' }}">
                    <i class="fa-solid fa-wallet w-5 text-center text-[#D8A7B1]" aria-hidden="true"></i>
                    <span>Budget Planner</span>
                </a>

                <a href="{{ route('contracts.index') }}"
                    class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-all {{ request()->routeIs('contracts.*') ? 'bg-[#D8A7B1]/20 text-[#5F6F5B] font-bold border border-[#D8A7B1]/40' : 'text-[#4E473F] hover:bg-[#FAF7F2] hover:text-[#2D372E]' }}">
                    <i class="fa-solid fa-file-contract w-5 text-center text-[#D8A7B1]" aria-hidden="true"></i>
                    <span>Vendor Contract</span>
                </a>

                <a href="{{ route('payments.index') }}"
                    class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-all {{ request()->routeIs('payments.*') ? 'bg-[#D8A7B1]/20 text-[#5F6F5B] font-bold border border-[#D8A7B1]/40' : 'text-[#4E473F] hover:bg-[#FAF7F2] hover:text-[#2D372E]' }}">
                    <i class="fa-solid fa-credit-card w-5 text-center text-[#D8A7B1]" aria-hidden="true"></i>
                    <span>Payment Tracker</span>
                </a>

                <a href="{{ route('savings.index') }}"
                    class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-all {{ request()->routeIs('savings.*') ? 'bg-[#D8A7B1]/20 text-[#5F6F5B] font-bold border border-[#D8A7B1]/40' : 'text-[#4E473F] hover:bg-[#FAF7F2] hover:text-[#2D372E]' }}">
                    <i class="fa-solid fa-piggy-bank w-5 text-center text-[#D8A7B1]" aria-hidden="true"></i>
                    <span>Tabungan</span>
                </a>

                <a href="{{ route('gifts.index') }}"
                    class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-all {{ request()->routeIs('gifts.*') ? 'bg-[#D8A7B1]/20 text-[#5F6F5B] font-bold border border-[#D8A7B1]/40' : 'text-[#4E473F] hover:bg-[#FAF7F2] hover:text-[#2D372E]' }}">
                    <i class="fa-solid fa-gift w-5 text-center text-[#D8A7B1]" aria-hidden="true"></i>
                    <span>Gift Management</span>
                </a>

                <div class="nav-section px-3 pt-4 pb-2 text-[10px] font-bold uppercase tracking-wider text-[#5F6F5B]">
                    Laporan</div>

                <a href="{{ route('reports.index') }}"
                    class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-all {{ request()->routeIs('reports.*') ? 'bg-[#D8A7B1]/20 text-[#5F6F5B] font-bold border border-[#D8A7B1]/40' : 'text-[#4E473F] hover:bg-[#FAF7F2] hover:text-[#2D372E]' }}">
                    <i class="fa-solid fa-file-invoice-dollar w-5 text-center text-[#D8A7B1]" aria-hidden="true"></i>
                    <span>Laporan & Export</span>
                </a>

                <div class="nav-section px-3 pt-4 pb-2 text-[10px] font-bold uppercase tracking-wider text-[#5F6F5B]">Akun
                </div>

                <a href="{{ route('admin.index') }}"
                    class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-all {{ request()->routeIs('admin.index', 'admin.wedding.*') ? 'bg-[#D8A7B1]/20 text-[#5F6F5B] font-bold border border-[#D8A7B1]/40' : 'text-[#4E473F] hover:bg-[#FAF7F2] hover:text-[#2D372E]' }}">
                    <i class="fa-solid fa-user-gear w-5 text-center text-[#D8A7B1]" aria-hidden="true"></i>
                    <span>Profile</span>
                </a>
            @endunless

            @if (auth()->user()?->is_superadmin)
                <div class="nav-section px-3 pt-4 pb-2 text-[10px] font-bold uppercase tracking-wider text-[#5F6F5B]">
                    Admin</div>

                <a href="{{ route('admin.users.index') }}"
                    class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-all {{ request()->routeIs('admin.users.*') ? 'bg-[#D8A7B1]/20 text-[#5F6F5B] font-bold border border-[#D8A7B1]/40' : 'text-[#4E473F] hover:bg-[#FAF7F2] hover:text-[#2D372E]' }}">
                    <i class="fa-solid fa-users-gear w-5 text-center text-[#D8A7B1]" aria-hidden="true"></i>
                    <span>Admin Panel</span>
                </a>

                <a href="{{ route('admin.master-data.index') }}"
                    class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-all {{ request()->routeIs('admin.master-data.*') ? 'bg-[#D8A7B1]/20 text-[#5F6F5B] font-bold border border-[#D8A7B1]/40' : 'text-[#4E473F] hover:bg-[#FAF7F2] hover:text-[#2D372E]' }}">
                    <i class="fa-solid fa-layer-group w-5 text-center text-[#D8A7B1]" aria-hidden="true"></i>
                    <span>Master Data</span>
                </a>

                <a href="{{ route('admin.document-templates.index') }}"
                    class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-all {{ request()->routeIs('admin.document-templates.*') ? 'bg-[#D8A7B1]/20 text-[#5F6F5B] font-bold border border-[#D8A7B1]/40' : 'text-[#4E473F] hover:bg-[#FAF7F2] hover:text-[#2D372E]' }}">
                    <i class="fa-solid fa-file-lines w-5 text-center text-[#D8A7B1]" aria-hidden="true"></i>
                    <span>Template Dokumen</span>
                </a>

                <a href="{{ route('admin.upgrade-requests.index') }}"
                    class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-all {{ request()->routeIs('admin.upgrade-requests.*') ? 'bg-[#D8A7B1]/20 text-[#5F6F5B] font-bold border border-[#D8A7B1]/40' : 'text-[#4E473F] hover:bg-[#FAF7F2] hover:text-[#2D372E]' }}">
                    <i class="fa-solid fa-crown w-5 text-center text-[#D8A7B1]" aria-hidden="true"></i>
                    <span>Request Upgrade Pro</span>
                </a>

                <a href="{{ route('admin.social-media.index') }}"
                    class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-all {{ request()->routeIs('admin.social-media.*') ? 'bg-[#D8A7B1]/20 text-[#5F6F5B] font-bold border border-[#D8A7B1]/40' : 'text-[#4E473F] hover:bg-[#FAF7F2] hover:text-[#2D372E]' }}">
                    <i class="fa-solid fa-share-nodes w-5 text-center text-[#D8A7B1]" aria-hidden="true"></i>
                    <span>Social Media</span>
                </a>

                <a href="{{ route('admin.pro-settings.index') }}"
                    class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-all {{ request()->routeIs('admin.pro-settings.*') ? 'bg-[#D8A7B1]/20 text-[#5F6F5B] font-bold border border-[#D8A7B1]/40' : 'text-[#4E473F] hover:bg-[#FAF7F2] hover:text-[#2D372E]' }}">
                    <i class="fa-solid fa-sliders w-5 text-center text-[#D8A7B1]" aria-hidden="true"></i>
                    <span>Pengaturan Pro</span>
                </a>

                <a href="{{ route('admin.photobooth-settings.index') }}"
                    class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-all {{ request()->routeIs('admin.photobooth-settings.*') ? 'bg-[#D8A7B1]/20 text-[#5F6F5B] font-bold border border-[#D8A7B1]/40' : 'text-[#4E473F] hover:bg-[#FAF7F2] hover:text-[#2D372E]' }}">
                    <i class="fa-solid fa-camera-retro w-5 text-center text-[#D8A7B1]" aria-hidden="true"></i>
                    <span>Pengaturan Photobooth</span>
                </a>
            @endif
        </nav>

        {{-- Footer sidebar: DI LUAR <nav> scrollable, jadi kartu user dan
             tombol Keluar selalu menempel di bawah dan tidak ikut bergeser.
             Menu Profile tetap memakai link yang sudah ada di <nav>. --}}
        <div class="shrink-0 border-t border-[#B6ADA3]/30 bg-white">

            <div class="flex items-center gap-3 px-4 py-3 border-t border-[#B6ADA3]/25">
                @if (auth()->user()?->avatarUrl())
                    <img src="{{ auth()->user()->avatarUrl() }}" alt="Foto {{ auth()->user()->name }}"
                        width="36" height="36"
                        class="w-9 h-9 rounded-full object-cover shrink-0">
                @else
                    <div
                        class="w-9 h-9 rounded-full bg-[#A855A0] text-white flex items-center justify-center text-sm font-bold shrink-0">
                        {{ auth()->user()?->initial() }}
                    </div>
                @endif

                <div class="sidebar-label min-w-0 flex-1">
                    <div class="text-sm font-bold text-[#5F6F5B] truncate">{{ auth()->user()?->name }}</div>
                    <div class="text-[11px] text-[#5F6F5B] truncate">{{ auth()->user()?->email }}</div>
                </div>

                <form method="POST" action="{{ route('logout') }}" data-turbo="false" class="shrink-0">
                    @csrf
                    <button type="submit" aria-label="Keluar" title="Keluar"
                        class="w-8 h-8 rounded-lg text-[#5F6F5B] hover:bg-[#FAF7F2] hover:text-[#C2757F] cursor-pointer transition-all flex items-center justify-center">
                        <i class="fa-solid fa-right-from-bracket" aria-hidden="true"></i>
                    </button>
                </form>
            </div>
        </div>

        {{-- Tombol ciut/buka sidebar, menempel di tepi kanan tengah sidebar. --}}
        <button type="button"
            @click="$dispatch('sidebar-collapsed', ! sidebarCollapsed); document.documentElement.classList.add('sidebar-toggling'); window.setTimeout(() => document.documentElement.classList.remove('sidebar-toggling'), 320)"
            class="hidden md:flex absolute right-0 top-1/2 -translate-y-1/2 translate-x-1/2 z-10 w-6 h-12 items-center justify-center rounded-full bg-white border border-[#B6ADA3]/40 text-[#5F6F5B] hover:text-[#C2757F] shadow-sm transition-colors cursor-pointer"
            :aria-label="sidebarCollapsed ? 'Perlebar sidebar' : 'Ciutkan sidebar'">
            <i class="fa-solid text-[10px]" :class="sidebarCollapsed ? 'fa-chevron-right' : 'fa-chevron-left'" aria-hidden="true"></i>
        </button>
    </aside>

    @unless (auth()->user()?->is_superadmin)
        <div data-mobile-bottom-nav x-data="{ sheet: null }" class="mobile-bottom-nav md:hidden">
            <div x-show="sheet" x-cloak x-transition.opacity @click="sheet = null" class="mobile-nav-scrim"></div>

            <div x-show="sheet" x-cloak x-transition:enter="mobile-sheet-enter" x-transition:leave="mobile-sheet-leave"
                class="mobile-nav-sheet">
                <div class="mobile-sheet-grabber"></div>
                <div class="flex items-center justify-between mb-3">
                    <h2 class="font-serif-title text-lg font-bold text-[#5F6F5B]"
                        x-text="sheet === 'planning' ? 'Planning' : (sheet === 'finance' ? 'Keuangan' : (sheet === 'creative' ? 'Kreatif' : 'Saya'))"></h2>
                    <button type="button" @click="sheet = null" aria-label="Tutup menu"
                        class="w-9 h-9 rounded-full text-[#5F6F5B] hover:bg-[#FAF7F2] cursor-pointer flex items-center justify-center">
                        <i class="fa-solid fa-xmark" aria-hidden="true"></i>
                    </button>
                </div>

                <div class="space-y-1.5 max-h-[60vh] overflow-y-auto">
                    @php
                        $mobilePlanning = [
                            [
                                'route' => 'checklists.index',
                                'match' => 'checklists.*',
                                'icon' => 'fa-list-check',
                                'title' => 'Checklist',
                                'subtitle' => 'Daftar tugas persiapan',
                            ],
                            [
                                'route' => 'vendors.index',
                                'match' => 'vendors.*',
                                'icon' => 'fa-store',
                                'title' => 'Vendor Management',
                                'subtitle' => 'Data vendor & katalog',
                            ],
                            [
                                'route' => 'guests.index',
                                'match' => 'guests.*',
                                'icon' => 'fa-users',
                                'title' => 'Guest Management',
                                'subtitle' => 'Daftar tamu & RSVP',
                            ],
                            [
                                'route' => 'rundowns.index',
                                'match' => 'rundowns.*',
                                'icon' => 'fa-clock',
                                'title' => 'Rundown Acara',
                                'subtitle' => 'Jadwal hari H',
                            ],
                            [
                                'route' => 'proposals.index',
                                'match' => 'proposals.*',
                                'icon' => 'fa-ring',
                                'title' => 'Lamaran',
                                'subtitle' => 'Rencana acara lamaran',
                            ],
                            [
                                'route' => 'souvenirs.index',
                                'match' => 'souvenirs.*',
                                'icon' => 'fa-gift',
                                'title' => 'Seserahan',
                                'subtitle' => 'Daftar seserahan',
                            ],
                            [
                                'route' => 'documents.index',
                                'match' => 'documents.*',
                                'icon' => 'fa-file-circle-check',
                                'title' => 'Persyaratan Nikah',
                                'subtitle' => 'Berkas & kelengkapan',
                            ],
                        ];
                        $mobileFinance = [
                            [
                                'route' => 'budgets.index',
                                'match' => 'budgets.*',
                                'icon' => 'fa-wallet',
                                'title' => 'Budget Planner',
                                'subtitle' => 'Rencana anggaran',
                            ],
                            [
                                'route' => 'contracts.index',
                                'match' => 'contracts.*',
                                'icon' => 'fa-file-contract',
                                'title' => 'Vendor Contract',
                                'subtitle' => 'Kontrak vendor',
                            ],
                            [
                                'route' => 'payments.index',
                                'match' => 'payments.*',
                                'icon' => 'fa-credit-card',
                                'title' => 'Payment Tracker',
                                'subtitle' => 'Angsuran & pembayaran',
                            ],
                            [
                                'route' => 'savings.index',
                                'match' => 'savings.*',
                                'icon' => 'fa-piggy-bank',
                                'title' => 'Tabungan',
                                'subtitle' => 'Rencana menabung',
                            ],
                            [
                                'route' => 'gifts.index',
                                'match' => 'gifts.*',
                                'icon' => 'fa-gift',
                                'title' => 'Gift Management',
                                'subtitle' => 'Daftar hadiah',
                            ],
                        ];
                        $mobileCreative = [
                            [
                                'route' => 'moodboards.index',
                                'match' => 'moodboards.*',
                                'icon' => 'fa-palette',
                                'title' => 'Moodboard',
                                'subtitle' => 'Inspirasi visual pernikahan',
                            ],
                            [
                                'route' => 'photobooth.index',
                                'match' => 'photobooth.*',
                                'icon' => 'fa-camera-retro',
                                'title' => 'Photobooth',
                                'subtitle' => 'QR & galeri foto tamu',
                            ],
                        ];
                        $mobileMe = [
                            [
                                'route' => 'reports.index',
                                'match' => 'reports.*',
                                'icon' => 'fa-file-invoice-dollar',
                                'title' => 'Laporan & Export',
                                'subtitle' => 'Ringkasan dan export',
                            ],
                            [
                                'route' => 'admin.index',
                                'match' => 'admin.index,admin.wedding.*',
                                'icon' => 'fa-user-gear',
                                'title' => 'Profile',
                                'subtitle' => 'Profil akun & pasangan',
                            ],
                        ];
                    @endphp

                    @foreach (['planning' => $mobilePlanning, 'creative' => $mobileCreative, 'finance' => $mobileFinance, 'me' => $mobileMe] as $group => $items)
                        <template x-if="sheet === '{{ $group }}'">
                            <div class="space-y-1.5">
                                @foreach ($items as $item)
                                    <a href="{{ route($item['route']) }}"
                                        class="mobile-sheet-item {{ request()->routeIs($item['match']) ? 'mobile-sheet-item-active' : '' }}">
                                        <span class="mobile-sheet-icon"><i
                                                class="fa-solid {{ $item['icon'] }}" aria-hidden="true"></i></span>
                                        <span class="min-w-0 flex-1">
                                            <strong>{{ $item['title'] }}</strong>
                                            <small>{{ $item['subtitle'] }}</small>
                                        </span>
                                        <i class="fa-solid fa-chevron-right text-[10px] text-[#5F6F5B]" aria-hidden="true"></i>
                                    </a>
                                @endforeach
                            </div>
                        </template>
                    @endforeach
                </div>
            </div>

            <nav class="mobile-bottom-pill" aria-label="Navigasi utama mobile">
                <a href="{{ route('dashboard') }}"
                    class="mobile-tab {{ request()->routeIs('dashboard') ? 'mobile-tab-active' : '' }}">
                    <i class="fa-solid fa-house" aria-hidden="true"></i><span>Home</span><b></b>
                </a>
                <button type="button" @click="sheet = sheet === 'planning' ? null : 'planning'"
                    class="mobile-tab {{ request()->routeIs('checklists.*', 'vendors.*', 'vendor-directory.*', 'guests.*', 'rundowns.*', 'proposals.*', 'souvenirs.*', 'documents.*') ? 'mobile-tab-active' : '' }}">
                    <i class="fa-solid fa-list-check" aria-hidden="true"></i><span>Planning</span><b></b>
                </button>
                <button type="button" @click="sheet = sheet === 'creative' ? null : 'creative'"
                    class="mobile-tab">
                    <i class="fa-solid fa-palette" aria-hidden="true"></i><span>Kreatif</span><b></b>
                </button>
                <button type="button" @click="sheet = sheet === 'finance' ? null : 'finance'"
                    class="mobile-tab {{ request()->routeIs('budgets.*', 'contracts.*', 'payments.*', 'savings.*', 'gifts.*') ? 'mobile-tab-active' : '' }}">
                    <i class="fa-solid fa-wallet" aria-hidden="true"></i><span>Keuangan</span><b></b>
                </button>
                <button type="button" @click="sheet = sheet === 'me' ? null : 'me'"
                    class="mobile-tab {{ request()->routeIs('reports.*', 'admin.index', 'admin.wedding.*') ? 'mobile-tab-active' : '' }}">
                    <i class="fa-solid fa-user" aria-hidden="true"></i><span>Saya</span><b></b>
                </button>
            </nav>
        </div>
    @endunless

    <!-- Main Content Body -->
    <main id="main-content" class="flex-1 flex flex-col min-w-0 overflow-y-auto">
        <!-- Desktop / Tablet Navbar -->
        <header
            class="hidden md:flex items-center justify-between px-8 py-4 bg-white/80 border-b border-[#B6ADA3]/30 backdrop-blur sticky top-0 z-30">
            <div class="flex items-center gap-3">
                <div class="text-xl font-bold font-serif-title text-[#5F6F5B]">@yield('title', 'Nupsera')</div>
            </div>

            <div class="flex items-center gap-5">
                <!-- D-Day Badge Header -->
                @unless ($isSuper ?? false)
                    <div
                        class="flex items-center gap-2 px-3.5 py-1.5 rounded-xl bg-[#FAF7F2] text-[#5F6F5B] border border-[#B6ADA3]/40 text-xs font-semibold shadow-xs">
                        <i class="fa-solid fa-hourglass-half text-[#D8A7B1]" aria-hidden="true"></i>
                        <span>Hari H: {{ $daysLeft ?? 0 }} Hari Lagi</span>
                    </div>
                @endunless

                <!-- Profile Avatar -->
                <div class="flex items-center gap-3 pl-4 border-l border-[#B6ADA3]/30">
                    <div class="text-xs">
                        <div class="font-semibold text-[#5F6F5B]">{{ $brandName ?? 'Nupsera' }}</div>
                        @unless ($isSuper ?? false)
                            <div class="text-[#5F6F5B]">Bride &amp; Groom</div>
                        @endunless
                    </div>
                </div>
            </div>
        </header>

        <!-- Flash Messages (sukses = toast, error & validasi = blok sticky) -->
        <x-flash-toast :success="session('success')" :error="session('error')" :messages="$errors" />

        <!-- Main Yield View -->
        <div id="page-content" class="p-4 sm:p-6 md:p-8 space-y-6 flex-1">
            @yield('content')
        </div>

        <!-- Footer -->
        <footer
            class="px-6 md:px-8 py-4 border-t border-[#B6ADA3]/30 bg-white/60 text-xs text-[#5F6F5B] flex flex-col md:flex-row items-center justify-between gap-2">
            <div>&copy; {{ date('Y') }} Nupsera. All rights reserved.</div>
            <div class="flex items-center gap-4 text-[#5F6F5B]">
            </div>
        </footer>
    </main>

    @stack('scripts')

    {{-- PWA: daftarkan service worker dan simpan event instal di window
         supaya tombol di halaman berapa pun bisa membaca kondisi saat
         itu juga diklik, tanpa tergantung urutan event. --}}
    <script>
        (function() {
            window.__pwaPrompt = null;
            window.__pwaError = null;
            window.__pwaInstalled = false;

            // Tangkap event beforeinstallprompt SEAWAL mungkin (sebelum SW
            // selesai register), karena browser bisa mengirimnya kapan saja
            // begitu manifest valid.
            window.addEventListener('beforeinstallprompt', function(e) {
                e.preventDefault();
                window.__pwaPrompt = e;
                window.__pwaError = null;
                // Dispatch custom event agar komponen UI bisa bereaksi
                window.dispatchEvent(new Event('pwa-ready'));
            });

            // Deteksi jika PWA sudah terinstall
            window.addEventListener('appinstalled', function() {
                window.__pwaInstalled = true;
                window.__pwaPrompt = null;
            });

            // Cek display-mode standalone (sudah dibuka sebagai PWA)
            if (window.matchMedia('(display-mode: standalone)').matches ||
                window.navigator.standalone === true) {
                window.__pwaInstalled = true;
            }

            if ('serviceWorker' in navigator) {
                // Gunakan load event: memastikan halaman sudah selesai load
                // sehingga SW register tidak bersaing dengan resource utama.
                window.addEventListener('load', function() {
                    navigator.serviceWorker.register('/sw.js', {
                        scope: '/',
                        updateViaCache: 'none' // selalu ambil sw.js fresh
                    }).then(function(reg) {
                        // Force check update setiap kali halaman dibuka
                        reg.update();

                        // Jika SW baru sedang waiting (update tersedia),
                        // aktifkan langsung agar tidak perlu close/reopen tab.
                        if (reg.waiting) {
                            reg.waiting.postMessage({
                                type: 'SKIP_WAITING'
                            });
                        }

                        reg.addEventListener('updatefound', function() {
                            var newSW = reg.installing;
                            if (newSW) {
                                newSW.addEventListener('statechange', function() {
                                    if (newSW.state === 'activated') {
                                        // SW baru aktif → reload halaman agar
                                        // prompt install muncul di-load berikutnya
                                        // (hanya jika bukan first-install)
                                    }
                                });
                            }
                        });
                    }).catch(function(err) {
                        window.__pwaError = (err && err.message) ? err.message : String(err);
                    });
                });
            } else {
                window.__pwaError = 'Browser tidak mendukung service worker.';
            }
        })();
    </script>
</body>

</html>
