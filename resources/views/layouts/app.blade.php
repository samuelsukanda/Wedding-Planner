<!DOCTYPE html>
<html lang="id" class="h-full">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    {{-- Halaman dinamis: jangan pernah tampilkan snapshot basi saat kembali/back. --}}
    <meta name="turbo-cache-control" content="no-cache">
    <title>@yield('title', 'Wedding Planner') â€” {{ $brandName ?? 'Wedding Planner' }}</title>
    <meta name="description"
        content="Wedding Planner - Rencanakan pernikahan tanpa ribet. Semua kebutuhan Anda tersusun rapi dalam satu dashboard.">

    <link rel="shortcut icon" href="{{ asset('img/icon.jpg') }}" type="image/x-icon">
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&family=Playfair+Display:ital,wght@0,500;0,600;0,700;1,600&display=swap"
        rel="stylesheet">

    <!-- FontAwesome Icons & Chart.js -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <!-- Alpine.js -->
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
           PRINT STYLES â€” targets precise IDs, hides UI chrome
        ============================================================ */
        @media print {

            @page {
                margin: 1.2cm;
            }

            /* ---- Hide sidebar, mobile top bar, desktop header, footer, modal, action buttons ---- */
            aside,
            header,
            footer,
            .no-print {
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
            #page-content > div,
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
/* Dipakai sebelum Alpine selesai hydrate, termasuk setelah Turbo form
               submit. Ini mencegah lebar w-64 terlihat sesaat. */
            html.sidebar-collapsed .sidebar {
                width: 4rem !important;
            }

            /* Saat user menekan tombol chevron, biarkan lebar ikut
               beranimasi; saat navigasi biasa tetap snap untuk mengindari
               efek "sidebar menutup" di setiap ganti menu. */
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

            .sidebar.is-collapsed > div:first-of-type,
            html.sidebar-collapsed .sidebar > div:first-of-type {
                justify-content: center;
            }

            .sidebar.is-collapsed > div:first-of-type a,
            html.sidebar-collapsed .sidebar > div:first-of-type a {
                justify-content: center;
                width: 100%;
            }

            .sidebar.is-collapsed .shrink-0.border-t > div,
            html.sidebar-collapsed .sidebar .shrink-0.border-t > div {
                flex-direction: column;
                justify-content: center;
                padding-top: 0.75rem;
                padding-bottom: 0.75rem;
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
        // pathname harus di dalam event, bukan di awal IIFE â€” kalau placed
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
            <button @click="sidebarOpen = true"
                class="text-[#5F6F5B] hover:text-[#5F6F5B] p-2 rounded-xl border border-[#B6ADA3]/30 bg-white cursor-pointer"
                aria-label="Buka Menu Navigation">
                <i class="fa-solid fa-bars text-lg"></i>
            </button>
            <a href="{{ route('dashboard') }}" class="flex items-center gap-2">
                <i class="fa-solid fa-heart text-[#D8A7B1] text-lg"></i>
                <span class="font-serif-title font-bold text-lg text-[#5F6F5B]">{{ $brandName ?? 'Wedding Planner' }}</span>
            </a>
        </div>

        <!-- Mobile Days Badge -->
        @unless ($isSuper ?? false)
        <div
            class="flex items-center gap-1.5 px-3 py-1 rounded-full bg-[#D8A7B1]/20 text-[#5F6F5B] text-xs font-semibold border border-[#D8A7B1]/40">
            <i class="fa-solid fa-calendar-days text-[#D8A7B1]" style="color: #D8A7B1 !important;"></i>
            <span>{{ $daysLeft ?? 0 }} Hari</span>
        </div>
        @endunless
    </header>

    <!-- Sidebar Backdrop for Mobile -->
    <div x-show="sidebarOpen" x-transition.opacity @click="sidebarOpen = false"
        class="fixed inset-0 bg-[#5F6F5B]/50 backdrop-blur-sm z-50 md:hidden" x-cloak></div>

    <!-- Sidebar Navigation -->
    <aside :class="[sidebarOpen ? 'translate-x-0' : '-translate-x-full md:translate-x-0', sidebarCollapsed ? 'is-collapsed md:w-16' : 'md:w-64']"
        class="sidebar fixed md:relative inset-y-0 left-0 z-50 w-64 md:transition-[width,transform] duration-300 ease-in-out bg-white border-r border-[#B6ADA3]/35 flex flex-col shadow-lg md:shadow-none">

        <!-- Logo & Header -->
        <div class="p-5 border-b border-[#B6ADA3]/30 flex items-center justify-between bg-[#FAF7F2]/60">
            <a href="{{ route('dashboard') }}" class="flex items-center gap-3 min-w-0">
                <div class="w-10 h-10 rounded-2xl flex items-center justify-center text-white shadow-md shrink-0">
                    <img src="{{ asset('img/logo.png') }}" alt="Logo">
                </div>
                <div class="sidebar-label min-w-0">
                    <h1 class="font-serif-title font-bold text-lg text-[#5F6F5B] leading-tight">{{ $brandName ?? 'Wedding Planner' }}</h1>
                    <p class="text-[11px] text-[#5F6F5B] font-bold tracking-wide uppercase">Wedding Planner</p>
                </div>
            </a>
            <button @click="sidebarOpen = false"
                class="md:hidden text-[#B6ADA3] hover:text-[#5F6F5B] p-1.5 cursor-pointer">
                <i class="fa-solid fa-xmark text-xl"></i>
            </button>
        </div>

        <!-- Navigation Links (All 11 PRD Modules) -->
        <nav class="flex-1 min-h-0 overflow-y-auto px-3 py-4 space-y-1">
            @unless (auth()->user()?->is_superadmin)
            <div class="nav-section px-3 pb-2 text-[10px] font-bold uppercase tracking-wider text-[#B6ADA3]">Utama</div>

            <a href="{{ route('dashboard') }}"
                class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-all {{ request()->routeIs('dashboard') ? 'bg-[#D8A7B1]/20 text-[#5F6F5B] font-bold border border-[#D8A7B1]/40' : 'text-[#5F6F5B]/80 hover:bg-[#FAF7F2] hover:text-[#5F6F5B]' }}">
                <i class="fa-solid fa-chart-pie w-5 text-center text-[#D8A7B1]"></i>
                <span>Dashboard</span>
            </a>

            <div class="nav-section px-3 pt-4 pb-2 text-[10px] font-bold uppercase tracking-wider text-[#B6ADA3]">Perencanaan</div>

            <a href="{{ route('checklists.index') }}"
                class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-all {{ request()->routeIs('checklists.*') ? 'bg-[#D8A7B1]/20 text-[#5F6F5B] font-bold border border-[#D8A7B1]/40' : 'text-[#5F6F5B]/80 hover:bg-[#FAF7F2] hover:text-[#5F6F5B]' }}">
                <i class="fa-solid fa-list-check w-5 text-center text-[#D8A7B1]"></i>
                <span>Checklist</span>
            </a>

            <a href="{{ route('budgets.index') }}"
                class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-all {{ request()->routeIs('budgets.*') ? 'bg-[#D8A7B1]/20 text-[#5F6F5B] font-bold border border-[#D8A7B1]/40' : 'text-[#5F6F5B]/80 hover:bg-[#FAF7F2] hover:text-[#5F6F5B]' }}">
                <i class="fa-solid fa-wallet w-5 text-center text-[#D8A7B1]"></i>
                <span>Budget Planner</span>
            </a>

            <a href="{{ route('vendors.index') }}"
                class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-all {{ request()->routeIs('vendors.*') ? 'bg-[#D8A7B1]/20 text-[#5F6F5B] font-bold border border-[#D8A7B1]/40' : 'text-[#5F6F5B]/80 hover:bg-[#FAF7F2] hover:text-[#5F6F5B]' }}">
                <i class="fa-solid fa-store w-5 text-center text-[#D8A7B1]"></i>
                <span>Vendor Management</span>
            </a>

            <a href="{{ route('guests.index') }}"
                class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-all {{ request()->routeIs('guests.*') ? 'bg-[#D8A7B1]/20 text-[#5F6F5B] font-bold border border-[#D8A7B1]/40' : 'text-[#5F6F5B]/80 hover:bg-[#FAF7F2] hover:text-[#5F6F5B]' }}">
                <i class="fa-solid fa-users w-5 text-center text-[#D8A7B1]"></i>
                <span>Guest Management</span>
            </a>

            <a href="{{ route('rundowns.index') }}"
                class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-all {{ request()->routeIs('rundowns.*') ? 'bg-[#D8A7B1]/20 text-[#5F6F5B] font-bold border border-[#D8A7B1]/40' : 'text-[#5F6F5B]/80 hover:bg-[#FAF7F2] hover:text-[#5F6F5B]' }}">
                <i class="fa-solid fa-clock w-5 text-center text-[#D8A7B1]"></i>
                <span>Rundown Acara</span>
            </a>

            <div class="nav-section px-3 pt-4 pb-2 text-[10px] font-bold uppercase tracking-wider text-[#B6ADA3]">Inspirasi
            </div>

            <a href="{{ route('moodboards.index') }}"
                class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-all {{ request()->routeIs('moodboards.*') ? 'bg-[#D8A7B1]/20 text-[#5F6F5B] font-bold border border-[#D8A7B1]/40' : 'text-[#5F6F5B]/80 hover:bg-[#FAF7F2] hover:text-[#5F6F5B]' }}">
                <i class="fa-solid fa-palette w-5 text-center text-[#D8A7B1]"></i>
                <span>Moodboard</span>
            </a>

            <div class="nav-section px-3 pt-4 pb-2 text-[10px] font-bold uppercase tracking-wider text-[#B6ADA3]">Keuangan & Dokumen
            </div>

            <a href="{{ route('contracts.index') }}"
                class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-all {{ request()->routeIs('contracts.*') ? 'bg-[#D8A7B1]/20 text-[#5F6F5B] font-bold border border-[#D8A7B1]/40' : 'text-[#5F6F5B]/80 hover:bg-[#FAF7F2] hover:text-[#5F6F5B]' }}">
                <i class="fa-solid fa-file-contract w-5 text-center text-[#D8A7B1]"></i>
                <span>Vendor Contract</span>
            </a>

            <a href="{{ route('payments.index') }}"
                class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-all {{ request()->routeIs('payments.*') ? 'bg-[#D8A7B1]/20 text-[#5F6F5B] font-bold border border-[#D8A7B1]/40' : 'text-[#5F6F5B]/80 hover:bg-[#FAF7F2] hover:text-[#5F6F5B]' }}">
                <i class="fa-solid fa-credit-card w-5 text-center text-[#D8A7B1]"></i>
                <span>Payment Tracker</span>
            </a>

            <a href="{{ route('gifts.index') }}"
                class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-all {{ request()->routeIs('gifts.*') ? 'bg-[#D8A7B1]/20 text-[#5F6F5B] font-bold border border-[#D8A7B1]/40' : 'text-[#5F6F5B]/80 hover:bg-[#FAF7F2] hover:text-[#5F6F5B]' }}">
                <i class="fa-solid fa-gift w-5 text-center text-[#D8A7B1]"></i>
                <span>Gift Management</span>
            </a>

            <div class="nav-section px-3 pt-4 pb-2 text-[10px] font-bold uppercase tracking-wider text-[#B6ADA3]">Laporan</div>

            <a href="{{ route('reports.index') }}"
                class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-all {{ request()->routeIs('reports.*') ? 'bg-[#D8A7B1]/20 text-[#5F6F5B] font-bold border border-[#D8A7B1]/40' : 'text-[#5F6F5B]/80 hover:bg-[#FAF7F2] hover:text-[#5F6F5B]' }}">
                <i class="fa-solid fa-file-invoice-dollar w-5 text-center text-[#D8A7B1]"></i>
                <span>Laporan & Export</span>
            </a>

            <div class="nav-section px-3 pt-4 pb-2 text-[10px] font-bold uppercase tracking-wider text-[#B6ADA3]">Akun</div>

            <a href="{{ route('admin.index') }}"
                class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-all {{ request()->routeIs('admin.index', 'admin.wedding.*') ? 'bg-[#D8A7B1]/20 text-[#5F6F5B] font-bold border border-[#D8A7B1]/40' : 'text-[#5F6F5B]/80 hover:bg-[#FAF7F2] hover:text-[#5F6F5B]' }}">
                <i class="fa-solid fa-user-gear w-5 text-center text-[#D8A7B1]"></i>
                <span>Profile</span>
            </a>
            @endunless

            @if (auth()->user()?->is_superadmin)
            <div class="nav-section px-3 pt-4 pb-2 text-[10px] font-bold uppercase tracking-wider text-[#B6ADA3]">Admin</div>

            <a href="{{ route('admin.master-data.index') }}"
                class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-all {{ request()->routeIs('admin.master-data.*') ? 'bg-[#D8A7B1]/20 text-[#5F6F5B] font-bold border border-[#D8A7B1]/40' : 'text-[#5F6F5B]/80 hover:bg-[#FAF7F2] hover:text-[#5F6F5B]' }}">
                <i class="fa-solid fa-layer-group w-5 text-center text-[#D8A7B1]"></i>
                <span>Master Data</span>
            </a>

            <a href="{{ route('admin.users.index') }}"
                class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-all {{ request()->routeIs('admin.users.*') ? 'bg-[#D8A7B1]/20 text-[#5F6F5B] font-bold border border-[#D8A7B1]/40' : 'text-[#5F6F5B]/80 hover:bg-[#FAF7F2] hover:text-[#5F6F5B]' }}">
                <i class="fa-solid fa-users-gear w-5 text-center text-[#D8A7B1]"></i>
                <span>Admin Panel</span>
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
                        class="w-9 h-9 rounded-full object-cover shrink-0">
                @else
                    <div
                        class="w-9 h-9 rounded-full bg-[#A855A0] text-white flex items-center justify-center text-sm font-bold shrink-0">
                        {{ auth()->user()?->initial() }}
                    </div>
                @endif

                <div class="sidebar-label min-w-0 flex-1">
                    <div class="text-sm font-bold text-[#5F6F5B] truncate">{{ auth()->user()?->name }}</div>
                    <div class="text-[11px] text-[#B6ADA3] truncate">{{ auth()->user()?->email }}</div>
                </div>

                <form method="POST" action="{{ route('logout') }}" data-turbo="false" class="shrink-0">
                    @csrf
                    <button type="submit" aria-label="Keluar" title="Keluar"
                        class="w-8 h-8 rounded-lg text-[#B6ADA3] hover:bg-[#FAF7F2] hover:text-[#D8A7B1] cursor-pointer transition-all flex items-center justify-center">
                        <i class="fa-solid fa-right-from-bracket"></i>
                    </button>
                </form>
            </div>
        </div>

        {{-- Tombol ciut/buka sidebar, menempel di tepi kanan tengah sidebar. --}}
        <button type="button" @click="$dispatch('sidebar-collapsed', ! sidebarCollapsed); document.documentElement.classList.add('sidebar-toggling'); window.setTimeout(() => document.documentElement.classList.remove('sidebar-toggling'), 320)"
            class="hidden md:flex absolute right-0 top-1/2 -translate-y-1/2 translate-x-1/2 z-10 w-6 h-12 items-center justify-center rounded-full bg-white border border-[#B6ADA3]/40 text-[#B6ADA3] hover:text-[#D8A7B1] shadow-sm transition-colors cursor-pointer"
            :aria-label="sidebarCollapsed ? 'Perlebar sidebar' : 'Ciutkan sidebar'">
            <i class="fa-solid text-[10px]" :class="sidebarCollapsed ? 'fa-chevron-right' : 'fa-chevron-left'"></i>
        </button>
    </aside>

    <!-- Main Content Body -->
    <main id="main-content" class="flex-1 flex flex-col min-w-0 overflow-y-auto">
        <!-- Desktop / Tablet Navbar -->
        <header
            class="hidden md:flex items-center justify-between px-8 py-4 bg-white/80 border-b border-[#B6ADA3]/30 backdrop-blur sticky top-0 z-30">
            <div class="flex items-center gap-3">
                <h2 class="text-xl font-bold font-serif-title text-[#5F6F5B]">@yield('title', 'Wedding Planner')</h2>
            </div>

            <div class="flex items-center gap-5">
                <!-- D-Day Badge Header -->
                @unless ($isSuper ?? false)
                <div
                    class="flex items-center gap-2 px-3.5 py-1.5 rounded-xl bg-[#FAF7F2] text-[#5F6F5B] border border-[#B6ADA3]/40 text-xs font-semibold shadow-xs">
                    <i class="fa-solid fa-hourglass-half text-[#D8A7B1]"></i>
                    <span>Hari H: {{ $daysLeft ?? 0 }} Hari Lagi</span>
                </div>
                @endunless

                <!-- Profile Avatar -->
                <div class="flex items-center gap-3 pl-4 border-l border-[#B6ADA3]/30">
                    <div class="text-xs">
                        <div class="font-semibold text-[#5F6F5B]">{{ $brandName ?? 'Wedding Planner' }}</div>
                        @unless ($isSuper ?? false)
                        <div class="text-[#B6ADA3]">Bride &amp; Groom</div>
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
            class="px-6 md:px-8 py-4 border-t border-[#B6ADA3]/30 bg-white/60 text-xs text-[#B6ADA3] flex flex-col md:flex-row items-center justify-between gap-2">
            <div>&copy; {{ date('Y') }} Wedding Planner. All rights reserved.</div>
            <div class="flex items-center gap-4 text-[#5F6F5B]">
            </div>
        </footer>
    </main>

    @stack('scripts')
</body>

</html>
