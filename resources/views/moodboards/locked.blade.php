@extends('layouts.app')

@section('title', 'Inspirasi - Moodboard')

@section('content')
    {{-- Halaman terkunci untuk paket Free.
         Enam foto berlisensi Pexels (bebas komersial, tanpa wajib atribusi)
         disimpan sendiri di repo, bukan hotlink ke CDN pihak ketiga. --}}
    {{-- min-h pakai 13rem, bukan angka tebakan: ruang yang benar-benar
         tersisa di dalam #main-content dikurangi header (~89px), footer
         (~49px), dan padding halaman 2x32px, total sekitar 202px. Kalau
         minsinya lebih besar dari sisa itu, halaman justru jadi lebih tinggi
         dari layar dan memaksa scroll. --}}
    <div
        class="relative flex min-h-[calc(100vh_-_13rem)] flex-col justify-center overflow-hidden rounded-3xl px-5 py-4 text-center md:py-5">
        {{-- Cahaya hangat di belakang, biar tengah halaman tidak terasa kosong --}}
        <div aria-hidden="true"
            class="pointer-events-none absolute inset-x-0 top-1/2 h-72 w-[46rem] max-w-full -translate-y-1/2 rounded-full bg-[#F3E4E1]/50 blur-3xl">
        </div>

        <div class="relative">
            {{-- ================= PIL FITUR ================= --}}
            <span
                class="inline-flex items-center gap-1.5 rounded-full border border-[#D8A7B1]/50 bg-[#D8A7B1]/10 px-3 py-1 text-[11px] font-bold tracking-wide text-[#C2757F]">
                <i class="fa-solid fa-wand-magic-sparkles"></i> FITUR PRO
            </span>

            <h1
                class="mx-auto mt-2.5 max-w-2xl font-serif-title text-[1.25rem] font-bold leading-tight text-[#3F4A3B] md:text-[1.5rem]">
                {{ $feature['title'] }}
            </h1>

            <p class="mx-auto mt-1.5 max-w-lg text-[13px] leading-relaxed text-[#5F6F5B]/70">
                {{ $feature['description'] }}
            </p>

            {{-- ================= KLASTER BOARD =================
                 Semua kartu:sama lebarnya, satu foto melebar penuh berdiri di
                 kolom luar dan dua foto setengah lebar di kolom dalam, dengan
                 kolom penuhnya dibalik antar kartu kiri dan kanan. --}}
            <div class="mt-5 flex items-center justify-center gap-3 md:mt-6 md:gap-6">
                {{-- Kartu kiri: foto penuh di kolom kiri, miring ke kiri --}}
                <div
                    class="hidden w-[clamp(7rem,20vh,13rem)] rounded-2xl border border-[#B6ADA3]/30 bg-white p-2.5 shadow-lg shadow-[#5F6F5B]/10 transition-transform duration-300 ease-out hover:-translate-y-2 hover:rotate-0 hover:shadow-xl motion-reduce:transform-none sm:block -rotate-6">
                    <div class="grid grid-cols-2 gap-1.5">
                        {{-- h-full, bukan aspect-ratio: tinggi kolom dalam sudah
                             ditentukan dua kotak persegi, jadi foto penuh tinggal
                             ikut meregang setinggi itu. --}}
                        <img src="{{ asset('img/pexels-cake-floral.jpg') }}" alt=""
                            class="h-full w-full rounded-lg object-cover" loading="lazy">
                        <div class="flex flex-col gap-1.5">
                            <img src="{{ asset('img/pexels-cake-elegant.jpg') }}" alt=""
                                class="aspect-square w-full rounded-lg object-cover" loading="lazy">
                            <img src="{{ asset('img/pexels-flatlay-seal.jpg') }}" alt=""
                                class="aspect-square w-full rounded-lg object-cover" loading="lazy">
                        </div>
                    </div>
                </div>

                {{-- Kartu tengah: board terkunci. Isinya palet putih-krem
                     muda, jadi kelihatan tapi tidak terbaca detailnya. Gembok
                     dan badge PRO memakai coklat supaya terpisah dari aksen
                     pink halaman. --}}
                <div
                    class="relative z-10 w-[clamp(7rem,20vh,13rem)] rounded-2xl border border-[#B6ADA3]/40 bg-white p-2.5 shadow-xl shadow-[#5F6F5B]/15 transition-transform duration-300 ease-out hover:-translate-y-2 hover:shadow-2xl motion-reduce:transform-none">
                    <div class="relative aspect-[3/4] overflow-hidden rounded-xl bg-[#FBF7F0]">
                        <div aria-hidden="true" class="absolute inset-0 grid grid-cols-2 gap-1 p-1.5 blur-[7px]">
                            @foreach (['FFFFFF', 'FDFAF5', 'F6EEE3', 'EADFCB'] as $cream)
                                <span class="rounded-md" style="background:#{{ $cream }}"></span>
                            @endforeach
                        </div>

                        <div class="absolute inset-0 flex flex-col items-center justify-center gap-2.5">
                            <span
                                class="flex h-11 w-11 items-center justify-center rounded-full border border-[#B08D57]/40 bg-[#B08D57]/15">
                                <i class="fa-solid fa-lock text-[#B08D57]"></i>
                            </span>
                            <span
                                class="rounded-full bg-[#8A6A45]/12 px-2.5 py-1 text-[10px] font-bold tracking-wide text-[#8A6A45]">
                                PRO
                            </span>
                        </div>
                    </div>
                </div>

                {{-- Kartu kanan: foto penuh di kolom kanan, miring ke kanan --}}
                <div
                    class="hidden w-[clamp(7rem,20vh,13rem)] rounded-2xl border border-[#B6ADA3]/30 bg-white p-2.5 shadow-lg shadow-[#5F6F5B]/10 transition-transform duration-300 ease-out hover:-translate-y-2 hover:rotate-0 hover:shadow-xl motion-reduce:transform-none sm:block rotate-6">
                    <div class="grid grid-cols-2 gap-1.5">
                        <div class="flex flex-col gap-1.5">
                            <img src="{{ asset('img/pexels-ceremony-flowers.jpg') }}" alt=""
                                class="aspect-square w-full rounded-lg object-cover" loading="lazy">
                            <img src="{{ asset('img/pexels-bridal-flatlay.jpg') }}" alt=""
                                class="aspect-square w-full rounded-lg object-cover" loading="lazy">
                        </div>

                        <img src="{{ asset('img/pexels-photo-layout.jpg') }}" alt=""
                            class="h-full w-full rounded-lg object-cover" loading="lazy">
                    </div>
                </div>
            </div>

            {{-- ================= MANFAAT ================= --}}
            <ul class="mt-4 flex flex-wrap items-center justify-center gap-x-8 gap-y-2 md:mt-5 text-xs">
                @foreach ($feature['benefits'] as $benefit)
                    <li class="flex items-center gap-2 text-sm text-[#5F6F5B]">
                        <i class="fa-solid {{ $benefit['icon'] }} text-[#C2757F]"></i>
                        <span>{{ $benefit['label'] }}</span>
                    </li>
                @endforeach
            </ul>

            {{-- ================= CTA ================= --}}
            <a href="{{ route('upgrade.index') }}"
                class="btn-primary-rose mt-4 inline-flex items-center justify-center gap-2 rounded-full px-6 py-2.5 text-sm font-semibold cursor-pointer">
                <i class="fa-solid fa-wand-magic-sparkles"></i> Upgrade ke Pro Sekarang
            </a>

            {{-- Paket Pro di aplikasi ini dibeli sekali, bukan berlangganan, jadi
                 tidak boleh ada "/bulan" atau "batalkan kapan saja" di sini:
                 itu akan jadi klaim palsu di halaman yang paling dipercaya
                 saat pengguna memutuskan membayar. --}}
            <p class="mt-2.5 text-[11px] text-[#B6ADA3]">
                @if ($price > 0)
                    Lifetime + Akses ke semua fitur + Multi device + Invite Pasangan
                @else
                    Lifetime + Akses ke semua fitur + Multi device + Invite Pasangan
                @endif
            </p>
        </div>
    </div>
@endsection
