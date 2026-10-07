<!DOCTYPE html>
<html lang="id" class="h-full">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    {{-- Halaman dinamis: jangan pernah tampilkan snapshot basi saat kembali/back. --}}
    <meta name="turbo-cache-control" content="no-cache">
    <title>@yield('title', 'Wedding Planner') — {{ $brandName ?? 'Wedding Planner' }}</title>
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

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        /* ---- Flatpickr custom theme ---- */
        .flatpickr-calendar {
            background: #fff;
            border: 1px solid #e5e0d8;
            border-radius: 16px;
            box-shadow: 0 8px 32px rgba(95, 111, 91, 0.1);
            font-family: 'Outfit', sans-serif;
        }

        .flatpickr-months {
            background: #FAF7F2;
            border-radius: 16px 16px 0 0;
            padding: 8px 0;
        }

        .flatpickr-current-month .flatpickr-monthDropdown-months {
            font-weight: 600;
            color: #5F6F5B;
        }

        .flatpickr-current-month input.cur-year {
            font-weight: 600;
            color: #5F6F5B;
        }

        .flatpickr-months .flatpickr-prev-month,
        .flatpickr-months .flatpickr-next-month {
            color: #D8A7B1 !important;
            fill: #D8A7B1 !important;
        }

        .flatpickr-months .flatpickr-prev-month:hover,
        .flatpickr-months .flatpickr-next-month:hover {
            color: #5F6F5B !important;
            fill: #5F6F5B !important;
        }

        .flatpickr-weekday {
            color: #B6ADA3;
            font-weight: 600;
            font-size: 11px;
        }

        .flatpickr-day {
            color: #5F6F5B;
            border-radius: 10px;
            transition: all 0.15s;
        }

        .flatpickr-day:hover {
            background: #FAF7F2;
            border-color: #e5e0d8;
        }

        .flatpickr-day.today {
            border-color: #D8A7B1;
            background: #FDF6F7;
        }

        .flatpickr-day.selected {
            background: #D8A7B1;
            border-color: #D8A7B1;
            color: #fff;
            font-weight: 600;
        }

        .flatpickr-day.selected:hover {
            background: #C48D9A;
            border-color: #C48D9A;
        }

        .flatpickr-day.inRange {
            background: #FDF6F7;
            border-color: #FDF6F7;
            box-shadow: -5px 0 0 #FDF6F7, 5px 0 0 #FDF6F7;
        }

        .flatpickr-day.startRange,
        .flatpickr-day.endRange {
            background: #D8A7B1;
            border-color: #D8A7B1;
            color: #fff;
        }

        .flatpickr-day.flatpickr-disabled {
            color: #D8D2C8;
        }

        .flatpickr-time {
            border-top: 1px solid #e5e0d8;
        }

        .flatpickr-time input {
            color: #5F6F5B;
            font-weight: 600;
        }

        .flatpickr-time .flatpickr-am-pm {
            color: #5F6F5B;
            font-weight: 600;
        }

        .flatpickr-time .flatpickr-am-pm:hover {
            background: #FAF7F2;
        }

        .flatpickr-calendar.arrowTop:before {
            border-bottom-color: #e5e0d8;
        }

        .flatpickr-calendar.arrowTop:after {
            border-bottom-color: #FAF7F2;
        }

        .flatpickr-calendar.arrowBottom:before {
            border-top-color: #e5e0d8;
        }

        .flatpickr-calendar.arrowBottom:after {
            border-top-color: #FAF7F2;
        }

        .numInputWrapper span {
            border-color: #e5e0d8;
        }

        body {
            font-family: 'Outfit', sans-serif;
            background-color: #FAF7F2;
            color: #5F6F5B;
        }

        .font-serif-title {
            font-family: 'Playfair Display', serif;
        }

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

            /* ---- Reset body layout (flex → block for print) ---- */
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
    x-data="{ sidebarOpen: false }">

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
    <aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full md:translate-x-0'"
        class="fixed md:static inset-y-0 left-0 z-50 w-64 bg-white border-r border-[#B6ADA3]/35 flex flex-col transition-transform duration-300 ease-in-out shadow-lg md:shadow-none">

        <!-- Logo & Header -->
        <div class="p-5 border-b border-[#B6ADA3]/30 flex items-center justify-between bg-[#FAF7F2]/60">
            <a href="{{ route('dashboard') }}" class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-2xl flex items-center justify-center text-white shadow-md">
                    <img src="{{ asset('img/logo.png') }}" alt="Logo">
                </div>
                <div>
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
        <nav class="flex-1 overflow-y-auto px-3 py-4 space-y-1">
            @unless (auth()->user()?->is_superadmin)
            <div class="px-3 pb-2 text-[10px] font-bold uppercase tracking-wider text-[#B6ADA3]">Utama</div>

            <a href="{{ route('dashboard') }}"
                class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-all {{ request()->routeIs('dashboard') ? 'bg-[#D8A7B1]/20 text-[#5F6F5B] font-bold border border-[#D8A7B1]/40' : 'text-[#5F6F5B]/80 hover:bg-[#FAF7F2] hover:text-[#5F6F5B]' }}">
                <i class="fa-solid fa-chart-pie w-5 text-center text-[#D8A7B1]"></i>
                <span>Dashboard</span>
            </a>

            <div class="px-3 pt-4 pb-2 text-[10px] font-bold uppercase tracking-wider text-[#B6ADA3]">Perencanaan</div>

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

            <div class="px-3 pt-4 pb-2 text-[10px] font-bold uppercase tracking-wider text-[#B6ADA3]">Inspirasi & Jadwal
            </div>

            <a href="{{ route('moodboards.index') }}"
                class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-all {{ request()->routeIs('moodboards.*') ? 'bg-[#D8A7B1]/20 text-[#5F6F5B] font-bold border border-[#D8A7B1]/40' : 'text-[#5F6F5B]/80 hover:bg-[#FAF7F2] hover:text-[#5F6F5B]' }}">
                <i class="fa-solid fa-palette w-5 text-center text-[#D8A7B1]"></i>
                <span>Moodboard</span>
            </a>

            <a href="{{ route('rundowns.index') }}"
                class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-all {{ request()->routeIs('rundowns.*') ? 'bg-[#D8A7B1]/20 text-[#5F6F5B] font-bold border border-[#D8A7B1]/40' : 'text-[#5F6F5B]/80 hover:bg-[#FAF7F2] hover:text-[#5F6F5B]' }}">
                <i class="fa-solid fa-clock w-5 text-center text-[#D8A7B1]"></i>
                <span>Rundown Acara</span>
            </a>

            <div class="px-3 pt-4 pb-2 text-[10px] font-bold uppercase tracking-wider text-[#B6ADA3]">Keuangan & Dokumen
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

            <div class="px-3 pt-4 pb-2 text-[10px] font-bold uppercase tracking-wider text-[#B6ADA3]">Laporan</div>

            <a href="{{ route('reports.index') }}"
                class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-all {{ request()->routeIs('reports.*') ? 'bg-[#D8A7B1]/20 text-[#5F6F5B] font-bold border border-[#D8A7B1]/40' : 'text-[#5F6F5B]/80 hover:bg-[#FAF7F2] hover:text-[#5F6F5B]' }}">
                <i class="fa-solid fa-file-invoice-dollar w-5 text-center text-[#D8A7B1]"></i>
                <span>Laporan & Export</span>
            </a>

            <div class="px-3 pt-4 pb-2 text-[10px] font-bold uppercase tracking-wider text-[#B6ADA3]">Akun</div>

            <a href="{{ route('admin.index') }}"
                class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-all {{ request()->routeIs('admin.index', 'admin.wedding.*') ? 'bg-[#D8A7B1]/20 text-[#5F6F5B] font-bold border border-[#D8A7B1]/40' : 'text-[#5F6F5B]/80 hover:bg-[#FAF7F2] hover:text-[#5F6F5B]' }}">
                <i class="fa-solid fa-user-gear w-5 text-center text-[#D8A7B1]"></i>
                <span>Profile</span>
            </a>
            @endunless

            @if (auth()->user()?->is_superadmin)
            <div class="px-3 pt-4 pb-2 text-[10px] font-bold uppercase tracking-wider text-[#B6ADA3]">Admin</div>

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

            <div class="border-t border-[#B6ADA3]/30 my-3 mx-3"></div>

            <form method="POST" action="{{ route('logout') }}" class="px-3" data-turbo="false">
                @csrf
                <button type="submit"
                    class="flex items-center gap-3 w-full px-3.5 py-2.5 rounded-xl text-sm font-medium text-[#B6ADA3] hover:bg-[#FAF7F2] hover:text-[#D8A7B1] cursor-pointer transition-all">
                    <i class="fa-solid fa-right-from-bracket w-5 text-center"></i>
                    <span>Keluar</span>
                </button>
            </form>
        </nav>
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

        <!-- Flash Messages -->
        @if (session('success'))
            <div
                class="mx-4 sm:mx-6 mt-4 p-4 rounded-xl bg-[#A3B7A6]/20 border border-[#A3B7A6]/50 text-[#5F6F5B] text-sm flex items-center justify-between shadow-xs">
                <div class="flex items-center gap-3">
                    <i class="fa-solid fa-circle-check text-[#D8A7B1] text-lg"></i>
                    <span>{{ session('success') }}</span>
                </div>
                <button onclick="this.parentElement.remove()"
                    class="text-[#5F6F5B] hover:opacity-80 p-1 cursor-pointer">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
        @endif

        @if (session('error'))
            <div
                class="mx-4 sm:mx-6 mt-4 p-4 rounded-xl bg-[#D8A7B1]/25 border border-[#D8A7B1]/60 text-[#5F6F5B] text-sm flex items-center justify-between shadow-xs">
                <div class="flex items-center gap-3">
                    <i class="fa-solid fa-circle-exclamation text-[#D8A7B1] text-lg"></i>
                    <span>{{ session('error') }}</span>
                </div>
                <button onclick="this.parentElement.remove()"
                    class="text-[#5F6F5B] hover:opacity-80 p-1 cursor-pointer">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
        @endif

        @if ($errors->any())
            <div
                class="mx-4 sm:mx-6 mt-4 p-4 rounded-xl bg-[#D8A7B1]/25 border border-[#D8A7B1]/60 text-[#5F6F5B] text-sm shadow-xs">
                <div class="flex items-center gap-3 mb-2">
                    <i class="fa-solid fa-circle-exclamation text-[#D8A7B1] text-lg"></i>
                    <span class="font-semibold">Mohon lengkapi data berikut:</span>
                </div>
                <ul class="list-disc list-inside space-y-0.5 ml-1">
                    @foreach ($errors->all() as $err)
                        <li class="text-xs text-[#5F6F5B]/90">{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

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
