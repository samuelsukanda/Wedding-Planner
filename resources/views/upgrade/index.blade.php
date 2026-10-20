@extends('layouts.app')

@section('title', 'Upgrade ke Pro')

@section('content')
    <div class="space-y-6">
        @if ($pending)
            <!-- ================= STATUS: MENUNGGU KONFIRMASI ================= -->
            <a href="{{ route('admin.index') }}"
                class="inline-flex items-center gap-1.5 text-xs font-semibold text-[#5F6F5B]/70 hover:text-[#5F6F5B] transition">
                <i class="fa-solid fa-chevron-left text-[10px]"></i> Kembali
            </a>

            <div class="w-full bg-white p-6 md:p-7 rounded-2xl border border-[#D8A7B1]/50 shadow-xs space-y-5">
                <div class="flex items-center gap-4">
                    <div class="w-14 h-14 shrink-0 rounded-2xl bg-[#D8A7B1] flex items-center justify-center">
                        <i class="fa-solid fa-clock text-white text-xl"></i>
                    </div>
                    <div class="min-w-0">
                        <h2 class="text-lg font-bold font-serif-title text-[#5F6F5B]">Pembayaran Sedang Diverifikasi</h2>
                        <p class="text-xs text-[#5F6F5B]/70 mt-0.5">Admin sedang memeriksa pembayaran Pro kamu.</p>
                    </div>
                </div>

                {{-- Nominal direkam saat request dibuat, jadi tetap sesuai harga saat
                     pembayaran dibuat walau admin nanti mengubah harga Pro. --}}
                <dl
                    class="rounded-xl border border-[#D8A7B1]/35 bg-[#FBF2F4]/60 px-4 py-3.5 text-sm divide-y divide-[#D8A7B1]/20">
                    <div class="flex items-center justify-between gap-4 py-1.5">
                        <dt class="text-xs text-[#5F6F5B]/70">Nominal</dt>
                        <dd class="font-semibold text-[#5F6F5B]">Rp {{ number_format($pending->amount, 0, ',', '.') }}</dd>
                    </div>
                    <div class="flex items-center justify-between gap-4 py-1.5">
                        <dt class="text-xs text-[#5F6F5B]/70">Konfirmasi</dt>
                        <dd class="text-[#5F6F5B]/90">{{ $pending->created_at->format('d/m/Y, H.i.s') }}</dd>
                    </div>
                    <div class="flex items-center justify-between gap-4 py-1.5">
                        <dt class="text-xs text-[#5F6F5B]/70">Status</dt>
                        <dd class="font-semibold text-[#C2757F]">Menunggu Aktivasi</dd>
                    </div>
                </dl>

                {{-- Refresh Status sengaja link GET ke halaman ini: cukup klik
                     ulang setelah admin menyetujui, dan status terupdate. --}}
                <a href="{{ route('upgrade.index') }}"
                    class="btn-primary-rose inline-flex items-center justify-center gap-2 px-4 py-3 rounded-xl text-xs w-full sm:w-auto">
                    <i class="fa-solid fa-rotate-right"></i> Refresh Status
                </a>
            </div>
        @else
            {{-- checkout = false Initially: nomor rekening dan form upload sengaja
                 TIDAK ada di DOM sebelum tombol Pro diklik, supaya belum ada yang
                 bisa dikirim ke server tanpa lewat tombol itu. --}}
            <div class="w-full space-y-6" x-data="{ modalOpen: false }">

                <!-- ================= HERO PAKET ================= -->
                {{-- Warna hero pakai palet app (rose), bukan hijau tua: teks putih
                     hanya terbaca kalau latar cukup gelap, dan kartu hero satu
                     warna dengan tombol CTA di bawahnya. --}}
                <div class="w-full rounded-2xl bg-gradient-to-br from-[#C2757F] to-[#A855A0] p-6 md:p-8 shadow-xs">

                    <span
                        class="inline-flex items-center gap-1.5 rounded-full bg-white/20 px-3 py-1 text-[10px] font-bold uppercase tracking-wider text-white">
                        <i class="fa-solid fa-wand-magic-sparkles"></i> Recommended
                    </span>

                    <h2 class="mt-4 text-2xl md:text-3xl font-bold font-serif-title text-white">Nupsera Pro</h2>
                    <p class="mt-1.5 text-sm text-white/85">Upgrade sekali, nikmati Pro selamanya.</p>

                    <div class="mt-5 flex items-end flex-wrap gap-x-3 gap-y-1">
                        {{-- Harga coret hanya muncul kalau superadmin mengisinya dan
                             angkanya memang lebih besar dari harga jual. --}}
                        @if ($priceBefore > $price && $price > 0)
                            <span class="text-base text-white/60 line-through pb-1">
                                Rp {{ number_format($priceBefore, 0, ',', '.') }}
                            </span>
                        @endif
                        <span class="text-3xl md:text-4xl font-bold font-serif-title text-white leading-none">
                            Rp {{ number_format($price, 0, ',', '.') }}
                        </span>
                        <span class="text-sm text-white/80 pb-1">/ Lifetime</span>
                    </div>
                </div>

                <!-- ================= YANG KAMU DAPAT ================= -->
                <div class="w-full bg-white p-6 md:p-7 rounded-2xl border border-[#B6ADA3]/35 shadow-xs">
                    <h3 class="text-sm font-bold text-[#5F6F5B]">Yang kamu dapat</h3>
                    <ul class="mt-4 space-y-3">
                        @foreach (['Moodboard tanpa batas jumlah item', 'Cari Vendor — katalog vendor weddings lengkap', 'Berlaku selamanya, tanpa biaya bulanan', 'Berlaku juga untuk akun pasangan di Workspace yang sama', 'Bisa diakses dari HP maupun desktop'] as $benefit)
                            <li class="flex items-center gap-3 text-sm text-[#5F6F5B]/85">
                                <i class="fa-solid fa-circle-check text-[#C2757F]"></i>
                                <span>{{ $benefit }}</span>
                            </li>
                        @endforeach
                    </ul>
                </div>

                <!-- ================= CTA PEMBUKA CHECKOUT ================= -->
                <button type="button" @click="modalOpen = true"
                    class="btn-primary-rose w-full px-6 py-4 rounded-2xl text-sm cursor-pointer">
                    <i class="fa-solid fa-wand-magic-sparkles mr-2"></i> Upgrade ke Pro
                </button>

                <!-- ================= MODAL RINGKASAN PEMBELIAN =================
                                                         Struktur, warna, dan cara menutupnya mengikuti modal
                                                         "Tambah Data" di modul lain (x-show + x-outside + Escape),
                                                         supaya tidak ada gaya modal ketiga di aplikasi ini. -->
                <div x-show="modalOpen" x-transition.opacity x-cloak @keydown.escape.window="modalOpen = false"
                    class="fixed inset-0 z-50 bg-[#5F6F5B]/40 backdrop-blur-md flex items-center justify-center p-4">
                    <div x-outside="modalOpen = false"
                        class="bg-white w-full max-w-2xl max-h-[90vh] overflow-y-auto p-6 rounded-2xl border border-[#B6ADA3]/40 shadow-2xl space-y-5">

                        <div class="flex items-center justify-between border-b border-[#B6ADA3]/30 pb-3">
                            <h3 class="text-lg font-bold font-serif-title text-[#5F6F5B]">Ringkasan Pembelian</h3>
                            <button @click="modalOpen = false" aria-label="Tutup"
                                class="text-[#B6ADA3] hover:text-[#5F6F5B] cursor-pointer">
                                <i class="fa-solid fa-xmark"></i>
                            </button>
                        </div>

                        <!-- Ringkasan total harga -->
                        <dl class="text-sm divide-y divide-[#B6ADA3]/25">
                            <div class="flex items-center justify-between gap-4 py-2.5">
                                <dt class="text-[#5F6F5B]/70">Paket</dt>
                                <dd class="font-semibold text-[#5F6F5B]">Pro</dd>
                            </div>
                            @if ($priceBefore > $price && $price > 0)
                                <div class="flex items-center justify-between gap-4 py-2.5">
                                    <dt class="text-[#5F6F5B]/70">Harga normal</dt>
                                    <dd class="text-[#B6ADA3] line-through">
                                        Rp {{ number_format($priceBefore, 0, ',', '.') }}
                                    </dd>
                                </div>
                            @endif
                            <div class="flex items-center justify-between gap-4 pt-3 mt-1">
                                <dt class="font-bold text-[#5F6F5B]">Total</dt>
                                <dd class="text-xl font-bold font-serif-title text-[#C2757F]">
                                    Rp {{ number_format($price, 0, ',', '.') }}
                                </dd>
                            </div>
                        </dl>

                        <!-- Rekening tujuan -->
                        <div class="rounded-xl bg-[#FAF7F2] p-4">
                            <h4 class="text-xs font-bold uppercase tracking-wider text-[#5F6F5B] flex items-center gap-2">
                                <i class="fa-solid fa-building-columns text-[#D8A7B1]"></i> Rekening Tujuan
                            </h4>

                            @if (trim($paymentNote) !== '')
                                <!-- Deskripsi pembayaran dari superadmin, di bawah judul -->
                                <p class="mt-2 text-xs text-[#5F6F5B]/75 leading-relaxed">
                                    {{ trim($paymentNote) }}
                                </p>
                            @endif

                            <div class="space-y-2 mt-3">
                                @forelse ($banks as $account)
                                    <div
                                        class="flex items-center gap-3 rounded-xl bg-white border border-[#B6ADA3]/35 px-3.5 py-2.5">
                                        <div
                                            class="w-9 h-9 shrink-0 rounded-lg bg-[#D8A7B1]/20 flex items-center justify-center">
                                            <i
                                                class="fa-solid {{ $account['icon'] ?: 'fa-building-columns' }} text-[#C2757F] text-sm"></i>
                                        </div>
                                        <div class="min-w-0">
                                            @if ($account['name'] !== '')
                                                <div class="text-[10px] font-bold uppercase tracking-wider text-[#B6ADA3]">
                                                    {{ $account['name'] }}
                                                </div>
                                            @endif
                                            <div class="text-sm font-semibold text-[#5F6F5B] break-words">
                                                {{ $account['number'] }}
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <p class="text-sm text-[#5F6F5B]/90">Belum diatur.</p>
                                @endforelse
                            </div>
                        </div>

                        @if (trim($qrisPath) !== '')
                            <div class="rounded-xl bg-[#FAF7F2] p-4 flex justify-center" x-data="{ open: false }">
                                <img src="{{ \Illuminate\Support\Facades\Storage::url($qrisPath) }}"
                                    x-on:click="open = true" alt="Kode QRIS"
                                    class="h-40 w-40 rounded-xl border border-[#B6ADA3]/30 object-contain bg-white p-1 cursor-zoom-in"
                                    title="Klik untuk memperbesar">

                                <!-- Tampilan besar: ukuran terbatas, tutup lewat
                                     tombol X di pojok atau klik area gelap -->
                                <div x-show="open" x-on:click="open = false" x-cloak
                                    class="fixed inset-0 z-50 bg-black/70 flex items-center justify-center p-6 cursor-zoom-out">
                                    <div class="relative flex items-center justify-center max-w-full max-h-full">
                                        <button type="button" x-on:click.stop="open = false"
                                            class="absolute -top-3 -right-3 w-9 h-9 rounded-full bg-white text-[#5F6F5B] shadow-md flex items-center justify-center cursor-pointer hover:bg-[#FAF7F2] transition"
                                            aria-label="Tutup">
                                            <i class="fa-solid fa-xmark"></i>
                                        </button>
                                        <img src="{{ \Illuminate\Support\Facades\Storage::url($qrisPath) }}"
                                            alt="Kode QRIS"
                                            class="max-w-sm max-h-[80vh] w-auto h-auto rounded-xl bg-white p-3 shadow-xl">
                                    </div>
                                </div>
                            </div>
                        @endif

                        <!-- Kirim bukti -->
                        <form method="POST" action="{{ route('upgrade.store') }}" enctype="multipart/form-data"
                            class="space-y-4">
                            @csrf
                            <div>
                                <label for="proof"
                                    class="block text-xs font-bold uppercase tracking-wider text-[#5F6F5B] mb-1.5">
                                    Bukti Transfer *
                                </label>
                                <input type="file" name="proof" id="proof"
                                    accept="image/jpeg,image/png,application/pdf" required
                                    class="block w-full text-sm text-[#5F6F5B] file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border-0 file:bg-[#FAF7F2] file:text-[#5F6F5B] file:text-xs file:font-semibold file:cursor-pointer hover:file:bg-[#F2EDE4] transition">
                                <p class="mt-1.5 text-[11px] text-[#5F6F5B]/60">Format JPG, PNG, atau PDF. Maksimal 2 MB.
                                </p>
                            </div>

                            @error('proof')
                                <p class="text-sm text-[#C2757F]">{{ $message }}</p>
                            @enderror

                            <button type="submit"
                                class="btn-primary-rose w-full px-6 py-3.5 rounded-xl text-sm cursor-pointer">
                                Kirim Bukti
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        @endif
    </div>
@endsection
