@extends('layouts.app')

@section('title', 'Upgrade ke Pro')

@section('content')
    <div class="space-y-6">
        <!-- Header -->
        <div class="bg-white p-5 rounded-2xl border border-[#B6ADA3]/35 shadow-xs">
            <div class="flex items-center gap-3">
                <i class="fa-solid fa-crown text-[#D8A7B1] text-xl"></i>
                <div>
                    <h1 class="text-xl md:text-2xl font-bold font-serif-title text-[#5F6F5B]">Upgrade ke Pro</h1>
                    <p class="text-xs text-[#5F6F5B]/70 mt-0.5">Nikmati semua fitur premium dengan satu kali pembayaran.
                        Aktif selamanya, tanpa biaya berlangganan bulanan.</p>
                </div>
            </div>
        </div>

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
                <dl class="rounded-xl border border-[#D8A7B1]/35 bg-[#FBF2F4]/60 px-4 py-3.5 text-sm divide-y divide-[#D8A7B1]/20">
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
                     ulang setelah admin menyetujui, dan status terupdate.
                     Kontak Admin disembunyikan kalau nomor WA belum diisi admin. --}}
                <div class="flex flex-col sm:flex-row gap-3">
                    <a href="{{ route('upgrade.index') }}"
                        class="flex-1 inline-flex items-center justify-center gap-2 px-4 py-3 rounded-xl bg-white border border-[#B6ADA3]/40 text-[#5F6F5B] text-xs font-semibold hover:bg-[#FAF7F2] transition">
                        <i class="fa-solid fa-rotate-right"></i> Refresh Status
                    </a>
                    @if ($adminWa)
                        <a href="https://wa.me/{{ preg_replace('/\D/', '', $adminWa) }}" target="_blank" rel="noopener"
                            class="flex-1 inline-flex items-center justify-center gap-2 px-4 py-3 rounded-xl bg-white border border-[#D8A7B1]/60 text-[#C2757F] text-xs font-semibold hover:bg-[#FAF7F2] transition">
                            <i class="fa-brands fa-whatsapp"></i> Kontak Admin
                        </a>
                    @endif
                </div>
            </div>
        @else
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 items-start">

                <!-- ================= KIRI: MANFAAT + HARGA ================= -->
                <div class="space-y-6">
                    <div class="bg-white p-6 rounded-2xl border border-[#B6ADA3]/35 shadow-xs space-y-4">
                        <h2 class="text-base font-bold font-serif-title text-[#5F6F5B] flex items-center gap-2">
                            <i class="fa-solid fa-sparkles text-[#D8A7B1]"></i> Yang Didapat
                        </h2>
                        <ul class="space-y-2.5 text-sm text-[#5F6F5B]/85">
                            <li class="flex items-start gap-2.5">
                                <i class="fa-solid fa-circle-check text-[#A3B7A6] mt-0.5"></i>
                                <span><strong>Moodboard</strong> — kumpulkan referensi dekorasi, gaun, dan vendor dalam satu
                                    papan, <strong>tanpa batas jumlah item</strong>.</span>
                            </li>
                            <li class="flex items-start gap-2.5">
                                <i class="fa-solid fa-circle-check text-[#A3B7A6] mt-0.5"></i>
                                <span><strong>Cari Vendor</strong> — telusuri katalog vendor weddings sesuai lokasi, jenis,
                                    dan budget kamu.</span>
                            </li>
                            <li class="flex items-start gap-2.5">
                                <i class="fa-solid fa-circle-check text-[#A3B7A6] mt-0.5"></i>
                                <span>Aktif <strong>seumur akun</strong>, tidak ada biaya bulanan.</span>
                            </li>
                            <li class="flex items-start gap-2.5">
                                <i class="fa-solid fa-circle-check text-[#A3B7A6] mt-0.5"></i>
                                <span>Berlaku untuk <strong>semua akun</strong> di Wedding Kit yang sama.</span>
                            </li>
                        </ul>
                    </div>

                    <div class="bg-white p-6 rounded-2xl border border-[#B6ADA3]/35 shadow-xs space-y-4">
                        <h2 class="text-base font-bold font-serif-title text-[#5F6F5B] flex items-center gap-2">
                            <i class="fa-solid fa-tag text-[#D8A7B1]"></i> Harga
                        </h2>
                        <p class="text-3xl font-bold font-serif-title text-[#5F6F5B]">
                            Rp {{ number_format($price, 0, ',', '.') }}
                        </p>
                        <p class="text-xs text-[#5F6F5B]/70">Sekali transfer, langsung aktif setelah dikonfirmasi admin.</p>
                    </div>
                </div>

                <!-- ================= KANAN: TRANSFER + UPLOAD BUKTI ================= -->
                <div class="space-y-6">
                    @if ($rejected)
                        <div class="bg-white p-5 rounded-2xl border border-[#D8A7B1]/60 bg-[#FBF2F4] shadow-xs space-y-2">
                            <div class="flex items-center gap-2 font-bold text-[#5F6F5B]">
                                <i class="fa-solid fa-circle-exclamation text-[#D8A7B1]"></i>
                                <span>Bukti sebelumnya ditolak</span>
                            </div>
                            <p class="text-sm text-[#5F6F5B]/85 leading-relaxed">{{ $rejected->admin_note }}</p>
                            <p class="text-xs text-[#5F6F5B]/70">Silakan transfer ulang lalu kirim bukti yang baru.</p>
                        </div>
                    @endif

                    <div class="bg-white p-6 rounded-2xl border border-[#B6ADA3]/35 shadow-xs space-y-4">
                        <h2 class="text-base font-bold font-serif-title text-[#5F6F5B] flex items-center gap-2">
                            <i class="fa-solid fa-building-columns text-[#D8A7B1]"></i> Rekening Tujuan
                        </h2>
                        <p class="text-sm text-[#5F6F5B]/90 whitespace-pre-line leading-relaxed">
                            {{ $bank ?: 'Belum diatur. Hubungi admin.' }}</p>

                        @if ($adminWa)
                            <a href="https://wa.me/{{ preg_replace('/\D/', '', $adminWa) }}" target="_blank" rel="noopener"
                                class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-white border border-[#D8A7B1]/60 text-[#C2757F] text-sm font-medium hover:bg-[#FAF7F2] transition">
                                <i class="fa-brands fa-whatsapp"></i> {{ $adminWa }}
                            </a>
                        @endif
                    </div>

                    <div class="bg-white p-6 rounded-2xl border border-[#B6ADA3]/35 shadow-xs space-y-4">
                        <h2 class="text-base font-bold font-serif-title text-[#5F6F5B] flex items-center gap-2">
                            <i class="fa-solid fa-receipt text-[#D8A7B1]"></i> Kirim Bukti Transfer
                        </h2>

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
                                class="btn-primary-rose w-full px-5 py-2.5 rounded-xl text-sm cursor-pointer">
                                Kirim Bukti
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        @endif
    </div>
@endsection
