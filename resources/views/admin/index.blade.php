@extends('layouts.app')

@section('title', 'Akun - Profile')

@section('content')
    <div class="space-y-6">
        <!-- Page Title & Header -->
        <div
            class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-white p-5 rounded-2xl border border-[#B6ADA3]/35 shadow-xs">
            <div>
                <h1 class="text-xl md:text-2xl font-bold font-serif-title text-[#5F6F5B]">Profile</h1>
                <p class="text-xs text-[#5F6F5B]/70 mt-1">Kelola informasi pribadi, detail akun, dan pengaturan profil untuk
                    memastikan data akun tetap akurat dan terkini.</p>
            </div>
        </div>

        <!-- ================= FOTO PROFIL ================= -->
        <div class="bg-white p-6 rounded-2xl border border-[#B6ADA3]/35 shadow-xs">
            <div class="flex flex-col sm:flex-row sm:items-center gap-6">
                <div class="relative shrink-0">
                    @if ($me->avatarUrl())
                        <img src="{{ $me->avatarUrl() }}" alt="Foto {{ $me->name }}"
                            class="w-20 h-20 sm:w-24 sm:h-24 rounded-full object-cover border-2 border-[#D8A7B1]/50">
                    @else
                        <div
                            class="w-20 h-20 sm:w-24 sm:h-24 rounded-full bg-[#A855A0] text-white flex items-center justify-center text-3xl font-bold">
                            {{ $me->initial() }}
                        </div>
                    @endif

                    <button type="button" data-photo-trigger aria-label="Ganti foto"
                        class="absolute -bottom-0.5 -right-0.5 w-7 h-7 rounded-full bg-[#D8A7B1] text-white shadow-md flex items-center justify-center hover:bg-[#C2757F] transition cursor-pointer">
                        <i class="fa-solid fa-pen text-[10px]"></i>
                    </button>
                </div>

                <div class="min-w-0 flex-1">
                    <div class="flex items-center gap-2 flex-wrap">
                        <h3 class="text-lg font-bold text-[#5F6F5B]">{{ $me->name }}</h3>

                        {{-- Badge hanya untuk akun yang masuk lewat Google.
                             Akun daftar email tidak menampilkan apa pun. --}}
                        @if ($me->isGoogle())
                            <span
                                class="inline-flex items-center gap-1.5 rounded-full border border-[#D8A7B1]/60 bg-[#D8A7B1]/10 px-2.5 py-0.5 text-[11px] font-semibold text-[#C2757F]">
                                <i class="fa-brands fa-google"></i>
                                Login dengan Google
                            </span>
                        @endif
                    </div>
                    <p class="text-sm text-[#B6ADA3] break-all">{{ $me->email }}</p>

                    <div class="mt-4 flex flex-wrap items-center gap-3">
                        {{-- Satu form dipakai oleh tombol pena dan tombol "Ganti Foto". --}}
                        <form method="POST" action="{{ route('profile.photo.update') }}" enctype="multipart/form-data">
                            @csrf
                            <input type="file" name="photo" id="photo-input" accept="image/*" class="hidden">
                            <button type="button" data-photo-trigger
                                class="px-4 py-2 rounded-xl border border-[#D8A7B1]/60 text-[#C2757F] text-sm font-medium hover:bg-[#D8A7B1]/10 transition cursor-pointer">
                                Ganti Foto
                            </button>
                        </form>

                        @if ($me->profile_photo)
                            <form method="POST" action="{{ route('profile.photo.destroy') }}" data-confirm-photo>
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                    class="px-4 py-2 rounded-xl border border-[#C2757F]/60 text-[#C2757F] text-sm font-medium hover:bg-[#C2757F]/10 transition cursor-pointer">
                                    Hapus Foto
                                </button>
                            </form>
                        @endif
                    </div>

                    @error('photo')
                        <p class="mt-4 text-sm text-[#C2757F]">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        <!-- ================= STATUS PAKET ================= -->
        @if ($wedding)
            <div class="bg-white p-6 rounded-2xl border border-[#B6ADA3]/35 shadow-xs space-y-4">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <h2 class="text-base font-bold font-serif-title text-[#5F6F5B] flex items-center gap-2">
                        <i class="fa-solid fa-crown text-[#D8A7B1]"></i> Status Akun
                    </h2>

                    @if ($wedding->isPro())
                        <span
                            class="inline-flex items-center gap-1.5 rounded-full border border-[#A3B7A6]/60 bg-[#F1F5F0] px-2.5 py-1 text-[11px] font-bold text-[#5F6F5B]">
                            <i class="fa-solid fa-circle-check text-[#A3B7A6]"></i>
                            PRO
                        </span>
                    @else
                        <span
                            class="inline-flex items-center gap-1.5 rounded-full border border-[#B6ADA3]/50 bg-[#FAF7F2] px-2.5 py-1 text-[11px] font-bold text-[#5F6F5B]/70">
                            FREE
                        </span>
                    @endif
                </div>

                @if ($wedding->isPro())
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                        <p class="text-sm text-[#5F6F5B]/80 leading-relaxed">
                            Semua fitur Pro aktif untuk akun ini dan akun pasangan, berlaku seumur akun tanpa biaya
                            berlangganan tambahan.
                        </p>
                        <a href="{{ route('profile.partner-invites.index') }}"
                            class="btn-primary-rose px-5 py-2.5 rounded-xl text-sm cursor-pointer whitespace-nowrap text-center inline-flex items-center justify-center">
                            <i class="fa-solid fa-link mr-2"></i> Tambah Pasangan
                        </a>
                    </div>
                @else
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                        <p class="text-sm text-[#5F6F5B]/80 leading-relaxed">
                            Semua fitur Pro aktif untuk akun ini dan akun pasangan, berlaku seumur akun tanpa biaya
                            berlangganan tambahan.
                        </p>
                        <a href="{{ route('upgrade.index') }}"
                            class="btn-primary-rose px-5 py-2.5 rounded-xl text-sm cursor-pointer whitespace-nowrap text-center inline-flex items-center justify-center">
                            <i class="fa-solid fa-star mr-2"></i> Upgrade ke Pro
                        </a>
                    </div>
                @endif

                {{-- Riwayat pembelian paket Pro. Pending dan rejected ikut
                     ditampilkan: pending supaya pengguna tahu permintaannya
                     masih diproses, rejected supaya jelas kenapa tidak jadi Pro. --}}
                @if ($upgradeRequests->isNotEmpty())
                    <div class="border-t border-[#B6ADA3]/30 pt-4">
                        <h3 class="text-[11px] font-bold uppercase tracking-wider text-[#B6ADA3] mb-2.5">
                            Riwayat Pembelian
                        </h3>

                        <ul class="space-y-2">
                            @foreach ($upgradeRequests as $purchase)
                                @php
                                    $badge = [
                                        'pending' => ['bg-[#FAF7F2] border-[#B6ADA3]/50 text-[#5F6F5B]', 'Menunggu'],
                                        'approved' => ['bg-[#F1F5F0] border-[#A3B7A6]/60 text-[#5F6F5B]', 'Disetujui'],
                                        'rejected' => ['bg-[#FBF2F4] border-[#D8A7B1]/60 text-[#C2757F]', 'Ditolak'],
                                    ][$purchase->status] ?? [
                                        'bg-[#FAF7F2] border-[#B6ADA3]/50 text-[#5F6F5B]',
                                        $purchase->status,
                                    ];

                                    // Yang ditampilkan adalah tanggal persetujuan, karena
                                    // itulah saat paketnya benar-benar aktif. Kalau masih
                                    // pending, tanggal pengajuannya yang relevan.
                                    $date = $purchase->reviewed_at ?? $purchase->created_at;
                                @endphp

                                <li
                                    class="flex flex-wrap items-center justify-between gap-2 rounded-xl bg-[#FAF7F2] border border-[#B6ADA3]/30 px-3.5 py-2.5">
                                    <div class="min-w-0">
                                        <div class="text-sm font-semibold text-[#5F6F5B]">
                                            Paket {{ strtoupper($purchase->plan) }} &middot;
                                            Rp {{ number_format($purchase->amount, 0, ',', '.') }}
                                        </div>
                                        <div class="text-[11px] text-[#B6ADA3]">
                                            {{ $date->translatedFormat('d M Y') }}
                                            @if ($purchase->status === 'rejected' && $purchase->admin_note)
                                                &middot; {{ $purchase->admin_note }}
                                            @endif
                                        </div>
                                    </div>

                                    <span
                                        class="shrink-0 rounded-full border px-2.5 py-0.5 text-[11px] font-semibold {{ $badge[0] }}">
                                        {{ $badge[1] }}
                                    </span>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            </div>
        @endif

        <!-- ================= INFORMASI ACARA PERNIKAHAN ================= -->
        @if ($wedding)
            <div class="space-y-6">
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">

                    <!-- Left: Form Input Data Pernikahan (7 Cols on Desktop) -->
                    <div class="lg:col-span-7 bg-white p-6 rounded-2xl border border-[#B6ADA3]/35 shadow-xs space-y-5">
                        <div class="flex items-center justify-between border-b border-[#B6ADA3]/30 pb-3">
                            <h2 class="text-base font-bold font-serif-title text-[#5F6F5B] flex items-center gap-2">
                                <i class="fa-solid fa-pen-to-square text-[#D8A7B1]"></i> Edit Detail Acara Pernikahan
                            </h2>
                        </div>

                        <form action="{{ route('admin.wedding.update', $wedding->id) }}" method="POST" class="space-y-4">
                            @csrf
                            @method('PUT')

                            <!-- Bride & Groom Name Grid -->
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-bold text-[#5F6F5B] mb-1">
                                        <i class="fa-solid fa-venus text-[#D8A7B1] mr-1"></i> Nama Pengantin Wanita (Bride)
                                        *
                                    </label>
                                    <input type="text" name="bride_name"
                                        value="{{ old('bride_name', $wedding->bride_name) }}" required
                                        placeholder="Juliet Capulet"
                                        class="w-full bg-[#FAF7F2] border border-[#B6ADA3]/50 text-sm font-semibold text-[#2D372E] px-3.5 py-2.5 rounded-xl focus:border-[#D8A7B1] focus:bg-white focus:outline-none transition-all">
                                </div>

                                <div>
                                    <label class="block text-xs font-bold text-[#5F6F5B] mb-1">
                                        <i class="fa-solid fa-mars text-[#5F6F5B] mr-1"></i> Nama Pengantin Pria (Groom) *
                                    </label>
                                    <input type="text" name="groom_name"
                                        value="{{ old('groom_name', $wedding->groom_name) }}" required
                                        placeholder="Romeo Montague"
                                        class="w-full bg-[#FAF7F2] border border-[#B6ADA3]/50 text-sm font-semibold text-[#2D372E] px-3.5 py-2.5 rounded-xl focus:border-[#D8A7B1] focus:bg-white focus:outline-none transition-all">
                                </div>
                            </div>

                            <!-- Date & Total Budget Grid -->
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-bold text-[#5F6F5B] mb-1">
                                        <i class="fa-solid fa-calendar-day text-[#D8A7B1] mr-1"></i> Tanggal Pernikahan
                                        (Hari H)
                                        *
                                    </label>
                                    <input type="text" name="wedding_date" placeholder="DD-MM-YYYY"
                                        value="{{ old('wedding_date', $wedding->wedding_date ? $wedding->wedding_date->format('Y-m-d') : '') }}"
                                        required
                                        class="w-full bg-[#FAF7F2] border border-[#B6ADA3]/50 text-sm font-semibold text-[#2D372E] px-3.5 py-2.5 rounded-xl focus:border-[#D8A7B1] focus:bg-white focus:outline-none transition-all datepicker">
                                </div>

                                <div>
                                    <label class="block text-xs font-bold text-[#5F6F5B] mb-1">
                                        <i class="fa-solid fa-money-bill-wave text-[#5F6F5B] mr-1"></i> Target Total Budget
                                        (Rp)
                                        *
                                    </label>
                                    <input type="text" name="total_budget" inputmode="numeric" required
                                        value="Rp {{ number_format((int) preg_replace('/\\D/', '', old('total_budget', $wedding->total_budget)), 0, ',', '.') }}"
                                        placeholder="Rp 1.000.000.000" data-budget-input
                                        class="w-full bg-[#FAF7F2] border border-[#B6ADA3]/50 text-sm font-bold text-[#2D372E] px-3.5 py-2.5 rounded-xl focus:border-[#D8A7B1] focus:bg-white focus:outline-none transition-all">
                                </div>
                            </div>

                            <!-- Location Venue -->
                            <div>
                                <label class="block text-xs font-bold text-[#5F6F5B] mb-1">
                                    <i class="fa-solid fa-location-dot text-[#D8A7B1] mr-1"></i> Lokasi Venue Pernikahan
                                </label>
                                <input type="text" name="location" value="{{ old('location', $wedding->location) }}"
                                    placeholder="Grand Ballroom Hotel Indonesia, Jakarta"
                                    class="w-full bg-[#FAF7F2] border border-[#B6ADA3]/50 text-sm font-semibold text-[#2D372E] px-3.5 py-2.5 rounded-xl focus:border-[#D8A7B1] focus:bg-white focus:outline-none transition-all">
                            </div>

                            <!-- Notes & Theme -->
                            <div>
                                <label class="block text-xs font-bold text-[#5F6F5B] mb-1">Catatan & Tema
                                    Pernikahan</label>
                                <textarea name="notes" rows="3" placeholder="Misal: Tema warna Dusty Rose & Sage Green..."
                                    class="w-full bg-[#FAF7F2] border border-[#B6ADA3]/50 text-sm font-medium text-[#2D372E] px-3.5 py-2.5 rounded-xl focus:border-[#D8A7B1] focus:bg-white focus:outline-none transition-all resize-none">{{ old('notes', $wedding->notes) }}</textarea>
                            </div>

                            <div class="pt-3 border-t border-[#B6ADA3]/30 flex justify-end">
                                <button type="submit"
                                    class="btn-primary-rose px-6 py-3 rounded-xl text-xs sm:text-sm font-bold flex items-center justify-center gap-2 cursor-pointer shadow-md">
                                    <i class="fa-solid fa-floppy-disk"></i> Simpan Informasi Pernikahan
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- Right: Preview Ringkasan Card (5 Cols on Desktop) -->
                    <div class="lg:col-span-5 space-y-4">
                        <!-- High Contrast Preview Card -->
                        <div class="bg-[#5F6F5B] text-white p-6 rounded-2xl shadow-md space-y-5 border border-[#4F5E4B]">
                            <div class="flex items-center justify-between border-b border-white/20 pb-3">
                                <span
                                    class="text-[10px] uppercase font-bold tracking-widest bg-white/20 text-white px-2.5 py-1 rounded-full">Pratinjau
                                    Dashboard</span>
                                <i class="fa-solid fa-heart text-base text-[#D8A7B1]"></i>
                            </div>

                            <div class="space-y-1">
                                <h3 class="text-xl font-bold font-serif-title text-[#FAF7F2]">Wedding of</h3>
                                <div class="text-lg font-bold text-[#FAF7F2] pt-0.5">
                                    {{ $wedding->couple_name }}
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-3 pt-3 border-t border-white/20 text-xs">
                                <div class="bg-black/20 p-3 rounded-xl border border-white/15">
                                    <span class="text-[#D8A7B1] block text-[10px] uppercase font-bold mb-0.5">TANGGAL HARI
                                        H</span>
                                    <span class="font-bold text-white text-xs sm:text-sm">
                                        {{ $wedding->wedding_date ? $wedding->wedding_date->format('d M Y') : '—' }}
                                    </span>
                                </div>
                                <div class="bg-black/20 p-3 rounded-xl border border-white/15">
                                    <span class="text-[#D8A7B1] block text-[10px] uppercase font-bold mb-0.5">TARGET
                                        BUDGET</span>
                                    <span class="font-bold text-white text-xs sm:text-sm">
                                        Rp {{ number_format($wedding->total_budget, 0, ',', '.') }}
                                    </span>
                                </div>
                            </div>

                            @if ($wedding->location)
                                <div
                                    class="text-xs text-white flex items-center gap-2 pt-1 bg-black/20 px-3.5 py-2.5 rounded-xl border border-white/15">
                                    <i class="fa-solid fa-location-dot text-[#D8A7B1]"></i>
                                    <span class="truncate font-semibold">{{ $wedding->location }}</span>
                                </div>
                            @endif
                        </div>

                        <!-- High Contrast Info Box -->
                        <div class="bg-white p-5 rounded-2xl border border-[#B6ADA3]/35 shadow-xs space-y-2 text-xs">
                            <div class="flex items-center gap-2 font-bold text-[#5F6F5B] text-sm">
                                <i class="fa-solid fa-circle-info text-[#D8A7B1]"></i>
                                <span>Catatan Otomatisasi</span>
                            </div>
                            <p class="text-[#5F6F5B] leading-relaxed">
                                Data yang Anda simpan di sini secara otomatis memperbarui <strong>Countdown Hari H</strong>,
                                header aplikasi, serta angka acuan di modul <strong>Budget Planner</strong> dan
                                <strong>Laporan
                                    Keuangan</strong>.
                            </p>
                        </div>

                        <!-- ================= RESET SEMUA DATA ================= -->
                        <div x-data="{
                            open: false,
                            typed: '',
                            // Input-nya styled uppercase, jadi yang tampil di layar
                            // bukan isi aslinya. Tanpa normalisasi di sini, mengetik
                            // 'reset' lowercase terlihat seperti 'RESET' tapi tetap
                            // tidak cocok dan tombolnya tidak pernah hidup.
                            get ready() { return this.typed.trim().toUpperCase() === 'RESET' }
                        }"
                            class="bg-white p-5 rounded-2xl border border-[#D8A7B1]/50 bg-[#FBF2F4] shadow-xs space-y-3 text-xs">
                            <div class="flex items-center gap-2 font-bold text-[#5F6F5B] text-sm">
                                <i class="fa-solid fa-rotate-left text-[#C2757F]"></i>
                                <span>Reset Semua Data</span>
                            </div>
                            <p class="text-[#5F6F5B] leading-relaxed">
                                Hapus semua worksheet <strong>Lamaran &amp; Pernikahan</strong> beserta profil acara,
                                lalu mulai dari awal. Status Pro, akun, dan pasangan yang
                                terhubung tidak ikut terhapus.
                            </p>

                            <button type="button" @click="open = true; typed = ''"
                                class="w-full px-4 py-2.5 rounded-xl bg-white border border-[#C2757F]/60 text-[#C2757F] font-semibold hover:bg-[#FBF2F4] transition cursor-pointer">
                                <i class="fa-solid fa-rotate-left mr-1"></i> Reset Semua Data
                            </button>

                            {{-- Modal mengikuti pola modal Tambah Data: x-show + x-outside + Escape.
                                 Mematikan tombol hanya kenyamanan; kata kunci RESET
                                 tetap divalidasi ulang di server. --}}
                            <div x-show="open" x-transition.opacity x-cloak @keydown.escape.window="open = false"
                                class="fixed inset-0 z-50 bg-[#5F6F5B]/40 backdrop-blur-md flex items-center justify-center p-4">
                                <div x-outside="open = false"
                                    class="bg-white w-full max-w-lg max-h-[90vh] overflow-y-auto p-6 rounded-2xl border border-[#B6ADA3]/40 shadow-2xl space-y-4">

                                    <div class="flex items-start justify-between gap-3">
                                        <div class="flex items-center gap-2.5">
                                            <i class="fa-solid fa-triangle-exclamation text-[#C2757F] text-lg mt-0.5"></i>
                                            <h3 class="text-base font-bold font-serif-title text-[#C2757F]">
                                                Yakin reset semua data?
                                            </h3>
                                        </div>
                                        <button type="button" @click="open = false" aria-label="Tutup"
                                            class="text-[#B6ADA3] hover:text-[#5F6F5B] cursor-pointer">
                                            <i class="fa-solid fa-xmark"></i>
                                        </button>
                                    </div>

                                    <p class="text-sm text-[#5F6F5B] leading-relaxed">
                                        Tindakan ini <strong class="text-[#C2757F]">tidak bisa dibatalkan</strong>.
                                        Data berikut akan dihapus permanen:
                                    </p>

                                    <ul class="text-xs text-[#5F6F5B]/85 space-y-1.5 list-disc pl-4">
                                        <li><strong>Lamaran:</strong> acara, tugas, anggaran, dan tamu lamaran</li>
                                        <li><strong>Pernikahan:</strong> Checklist, Budget Planner, Tamu, Vendor
                                            (beserta kontrak &amp; pembayarannya), Rundown Acara, Seserahan,
                                            Persyaratan Nikah, Moodboard, Gift, Tabungan, dan review vendor</li>
                                        <li><strong>Profil acara:</strong> nama pasangan, tanggal, budget, lokasi, dan
                                            catatan</li>
                                    </ul>

                                    <div class="rounded-xl bg-[#FAF7F2] p-3.5">
                                        <p class="text-xs font-bold text-[#5F6F5B]">Tetap aman:</p>
                                        <p class="text-xs text-[#5F6F5B]/80 leading-relaxed mt-1">
                                            Akun &amp; login, pasangan yang terhubung, status Pro/Free tidak ikut terhapus.
                                        </p>
                                    </div>

                                    <form method="POST" action="{{ route('profile.reset-data') }}">
                                        @csrf
                                        @method('DELETE')
                                        <label for="reset-confirm" class="block text-xs font-bold text-[#5F6F5B] mb-1.5">
                                            Ketik <span class="uppercase">RESET</span> untuk konfirmasi
                                        </label>
                                        <input type="text" id="reset-confirm" x-model="typed" autocomplete="off"
                                            placeholder="RESET"
                                            class="block w-full px-3.5 py-2.5 rounded-xl border border-[#B6ADA3]/40 bg-[#FAF7F2] text-sm uppercase tracking-widest text-[#5F6F5B] placeholder-[#B6ADA3] focus:border-[#D8A7B1] focus:outline-none transition">
                                        {{-- Yang terkirim ke server adalah hasil normalisasi,
                                             bukan apa yang diketik mentah, supaya validasi
                                             'in:RESET' di server tidak gagal hanya karena
                                             huruf besar-kecil atau spasi kebetulan. --}}
                                        <input type="hidden" name="confirm" :value="typed.trim().toUpperCase()">

                                        @error('confirm')
                                            <p class="mt-1.5 text-xs text-[#C2757F]">{{ $message }}</p>
                                        @enderror

                                        <div class="flex flex-col-reverse sm:flex-row sm:justify-end gap-2 pt-2">
                                            <button type="button" @click="open = false"
                                                class="px-4 py-2.5 rounded-xl bg-white border border-[#B6ADA3]/50 text-[#5F6F5B] text-xs font-semibold hover:bg-[#FAF7F2] transition cursor-pointer">
                                                Batal
                                            </button>
                                            <button type="submit" :disabled="!ready"
                                                :class="ready ? 'btn-primary-rose' : 'opacity-40 cursor-not-allowed'"
                                                class="px-4 py-2.5 rounded-xl text-xs font-semibold">
                                                <i class="fa-solid fa-rotate-left mr-1"></i> Ya, Reset Semua
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        @else
            <div class="bg-white p-10 rounded-2xl border border-[#B6ADA3]/35 shadow-xs text-center space-y-3">
                <i class="fa-solid fa-user-clock text-3xl text-[#D8A7B1]"></i>
                <h2 class="text-lg font-bold font-serif-title text-[#5F6F5B]">Akun belum dipasangkan</h2>
                <p class="text-sm text-[#5F6F5B]/80 max-w-md mx-auto">
                    Data pernikahan belum terhubung ke akun ini. Hubungi superadmin agar akun
                    ini dihubungkan dengan acara pernikahan yang benar.
                </p>
            </div>
        @endif
    </div>
@endsection

@push('scripts')
    <script>
        (function() {
            // Satu input file dipakai bersama oleh tombol pena dan "Ganti Foto".
            const input = document.getElementById('photo-input');
            if (input) {
                document.querySelectorAll('[data-photo-trigger]').forEach(function(button) {
                    button.addEventListener('click', function() {
                        input.click();
                    });
                });
                input.addEventListener('change', function() {
                    if (input.files && input.files.length) input.form.requestSubmit();
                });
            }

            // Konfirmasi hapus foto memakai SweetAlert2, bukan confirm() bawaan browser.
            document.querySelectorAll('[data-confirm-photo]').forEach(function(form) {
                form.addEventListener('submit', function(event) {
                    // requestSubmit() setelah user menekan konfirmasi akan
                    // melewati prompt kedua ini, lalu Turbo yang mengirim form.
                    if (form.dataset.photoConfirmed === '1') return;

                    event.preventDefault();
                    Swal.fire({
                        title: 'Hapus Foto Profil?',
                        text: 'Foto akan dihapus dan avatar kembali ke huruf awal.',
                        icon: 'warning',
                        iconColor: '#D8A7B1',
                        showCancelButton: true,
                        confirmButtonText: '<i class="fa-solid fa-trash mr-1"></i> Ya, Hapus',
                        cancelButtonText: 'Batal',
                        reverseButtons: true,
                    }).then(function(result) {
                        if (result.isConfirmed) {
                            form.dataset.photoConfirmed = '1';
                            form.requestSubmit();
                        }
                    });
                });
            });

            const budgetInput = document.querySelector('[data-budget-input]');
            if (budgetInput) {
                const formatBudget = function() {
                    const digits = budgetInput.value.replace(/\D/g, '');
                    budgetInput.value = digits ? `Rp ${Number(digits).toLocaleString('id-ID')}` : '';
                };

                budgetInput.addEventListener('input', formatBudget);
                formatBudget();
            }
        })();
    </script>
@endpush
