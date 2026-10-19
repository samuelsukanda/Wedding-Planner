@extends('layouts.app')

@section('title', 'Inspirasi - Moodboard')

@section('content')
    {{-- Halaman terkunci untuk paket Free.
         Foto dan warna diambil dari pin yang dipilih pemilik produk dan
         disimpan sendiri di repo, bukan hotlink ke i.pinimg.com: link
         Pinterest bisa berubah atau diblokir, dan halaman ini tidak boleh
         ikut rusak kalau CDN-nya berganti. Warna palette dibaca langsung dari
         pixel pin aslinya, jadi bukan tebakan. --}}
    <div class="relative overflow-hidden rounded-3xl px-5 py-14 md:py-20 text-center">
        {{-- Cahaya hangat di belakang, biar tengah halaman tidak terasa kosong --}}
        <div aria-hidden="true"
            class="pointer-events-none absolute inset-x-0 top-10 mx-auto h-72 w-[46rem] max-w-full rounded-full bg-[#F3E4E1]/50 blur-3xl"></div>

        <div class="relative">
            {{-- ================= PIL FITUR ================= --}}
            <span
                class="inline-flex items-center gap-1.5 rounded-full border border-[#D8A7B1]/50 bg-[#D8A7B1]/10 px-3.5 py-1.5 text-[11px] font-bold tracking-wide text-[#C2757F]">
                <i class="fa-solid fa-wand-magic-sparkles"></i> FITUR PRO
            </span>

            <h1
                class="mx-auto mt-6 max-w-2xl font-serif-title text-[1.75rem] font-bold leading-tight text-[#3F4A3B] md:text-[2.5rem]">
                {{ $feature['title'] }}
            </h1>

            <p class="mx-auto mt-3 max-w-xl text-sm leading-relaxed text-[#5F6F5B]/70">
                {{ $feature['description'] }}
            </p>

            {{-- ================= KLASTER BOARD ================= --}}
            <div class="mt-12 flex items-center justify-center gap-4 md:gap-7">
                {{-- Kartu kiri: satu foto melebar penuh di baris atas, dua foto
                     setengah lebar di bawahnya. Miring ke kiri. --}}
                <div
                    class="hidden w-32 rounded-2xl border border-[#B6ADA3]/30 bg-white p-2.5 shadow-lg shadow-[#5F6F5B]/10 -rotate-6 sm:block md:w-40">
                    <div class="grid grid-cols-2 gap-1.5">
                        <img src="{{ asset('img/pexels-cake-floral.jpg') }}" alt=""
                            class="col-span-2 aspect-[2/1] w-full rounded-lg object-cover" loading="lazy">
                        <img src="{{ asset('img/pexels-cake-elegant.jpg') }}" alt=""
                            class="aspect-square w-full rounded-lg object-cover" loading="lazy">
                        <img src="{{ asset('img/pexels-flatlay-seal.jpg') }}" alt=""
                            class="aspect-square w-full rounded-lg object-cover" loading="lazy">
                    </div>
                </div>

                {{-- Kartu tengah: board terkunci. Isinya palet putih-krem
                     muda, jadi isinya kelihatan tapi tidak terbaca
                     detailnya. Gembok dan badge PRO memakai coklat supaya
                     terpisah dari aksen pink halaman. --}}
                <div
                    class="relative z-10 w-32 rounded-2xl border border-[#B6ADA3]/40 bg-white p-2.5 shadow-xl shadow-[#5F6F5B]/15 md:w-40">
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

                {{-- Kartu kanan: kebalikan dari kartu kiri. Dua foto setengah lebar
                     ditumpuk di kolom kiri, satu foto melebar penuh berdiri
                     di kolom kanan. Miring ke kanan. --}}
                <div
                    class="hidden w-32 rounded-2xl border border-[#B6ADA3]/30 bg-white p-2.5 shadow-lg shadow-[#5F6F5B]/10 rotate-6 sm:block md:w-40">
                    <div class="grid grid-cols-2 gap-1.5">
                        <div class="flex flex-col gap-1.5">
                            <img src="{{ asset('img/pexels-ceremony-flowers.jpg') }}" alt=""
                                class="aspect-square w-full rounded-lg object-cover" loading="lazy">
                            <img src="{{ asset('img/pexels-bridal-flatlay.jpg') }}" alt=""
                                class="aspect-square w-full rounded-lg object-cover" loading="lazy">
                        </div>

                        {{-- h-full, bukan aspect-ratio: tinggi kolom kiri sudah
                             ditentukan dua kotak persegi, jadi foto kanan tinggal
                             ikut meregang setinggi itu. --}}
                        <img src="{{ asset('img/pexels-photo-layout.jpg') }}" alt=""
                            class="h-full w-full rounded-lg object-cover" loading="lazy">
                    </div>
                </div>
            </div>

            {{-- ================= MANFAAT ================= --}}
            <ul class="mt-12 flex flex-wrap items-center justify-center gap-x-9 gap-y-4">
                @foreach ($feature['benefits'] as $benefit)
                    <li class="flex items-center gap-2 text-sm text-[#5F6F5B]">
                        <i class="fa-solid {{ $benefit['icon'] }} text-[#C2757F]"></i>
                        <span>{{ $benefit['label'] }}</span>
                    </li>
                @endforeach
            </ul>

            {{-- ================= CTA ================= --}}
            <a href="{{ route('upgrade.index') }}"
                class="btn-primary-rose mt-9 inline-flex items-center justify-center gap-2 rounded-full px-7 py-3.5 text-sm font-semibold cursor-pointer">
                <i class="fa-solid fa-wand-magic-sparkles"></i> Upgrade ke Pro Sekarang
            </a>

            {{-- Paket Pro di aplikasi ini dibeli sekali, bukan berlangganan, jadi
                 tidak boleh ada "/bulan" atau "batalkan kapan saja" di sini:
                 itu akan jadi klaim palsu di halaman yang paling dipercaya
                 saat pengguna memutuskan membayar. --}}
            <p class="mt-6 text-xs text-[#B6ADA3]">
                @if ($price > 0)
                    Sekali bayar Rp {{ number_format($price, 0, ',', '.') }} &middot; Akses semua fitur &amp; device
                    &middot; Berlaku seumur akun
                @else
                    Sekali bayar &middot; Akses semua fitur &amp; device &middot; Berlaku seumur akun
                @endif
            </p>
        </div>
    </div>
@endsection