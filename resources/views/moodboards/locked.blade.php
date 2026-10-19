@extends('layouts.app')

@section('title', 'Inspirasi - Moodboard')

@section('content')
    {{-- Halaman terkunci untuk paket Free. Isinya murni CSS dan SVG inline,
         bukan file gambar: blok foto pada desain ini kecil, miring, dan
         sengaja dibuat buram, jadi gradien memberi hasil yang sama
         tanpa menambah aset binary ke repo dan tanpa masalah hak cipta. --}}
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
                {{-- Kartu kiri: kolase foto, miring ke kiri --}}
                <div
                    class="hidden w-36 rounded-2xl border border-[#B6ADA3]/30 bg-white p-2.5 shadow-lg shadow-[#5F6F5B]/10 -rotate-6 sm:block md:w-44">
                    <div class="grid grid-cols-2 gap-1.5">
                        @foreach ([
                            'linear-gradient(140deg,#A8B5A0,#6E7F6B)',
                            'linear-gradient(140deg,#F0D9D5,#D8A7B1)',
                            'linear-gradient(140deg,#F6EFE4,#D9CBB6)',
                            'linear-gradient(140deg,#C08570,#8E5A44)',
                        ] as $tile)
                            <span class="aspect-square rounded-lg" style="background:{{ $tile }}"></span>
                        @endforeach
                    </div>
                </div>

                {{-- Kartu tengah: board terkunci --}}
                <div
                    class="relative z-10 w-32 rounded-2xl border border-[#B6ADA3]/40 bg-white p-2.5 shadow-xl shadow-[#5F6F5B]/15 md:w-40">
                    <div class="relative aspect-[3/4] overflow-hidden rounded-xl bg-[#FAF7F2]">
                        {{-- Isi board sengaja diburam: yang dilihat pengguna bukan
                             detailnya, tapi bahwa isinya ada di sana. --}}
                        <div aria-hidden="true" class="absolute inset-0 grid grid-cols-2 gap-1 p-1.5 blur-[7px]">
                            <span class="rounded-md bg-[#A8B5A0]"></span>
                            <span class="rounded-md bg-[#D8A7B1]"></span>
                            <span class="rounded-md bg-[#C08570]"></span>
                            <span class="rounded-md bg-[#EFE6D4]"></span>
                        </div>

                        <div class="absolute inset-0 flex flex-col items-center justify-center gap-2.5">
                            <span
                                class="flex h-11 w-11 items-center justify-center rounded-full border border-[#C9A227]/40 bg-[#C9A227]/15">
                                <i class="fa-solid fa-lock text-[#C9A227]"></i>
                            </span>
                            <span
                                class="rounded-full bg-[#C2757F]/15 px-2.5 py-1 text-[10px] font-bold tracking-wide text-[#C2757F]">
                                PRO
                            </span>
                        </div>
                    </div>
                </div>

                {{-- Kartu kanan: palette dan template, miring ke kanan --}}
                <div
                    class="hidden w-36 rounded-2xl border border-[#B6ADA3]/30 bg-white p-2.5 shadow-lg shadow-[#5F6F5B]/10 rotate-6 sm:block md:w-44">
                    <div class="grid grid-cols-4 gap-1">
                        <span class="aspect-square rounded-md bg-[#7D8B74]"></span>
                        <span class="aspect-square rounded-md bg-[#D8CFC0]"></span>
                        <span class="aspect-square rounded-md bg-[#EFE7D6]"></span>
                        <span class="aspect-square rounded-md bg-[#C0724A]"></span>
                    </div>

                    {{-- Dua template garis: SVG inline supaya tidak butuh aset --}}
                    <div class="mt-1.5 grid grid-cols-2 gap-1.5">
                        @foreach ([
                            'M10 34c0-9 5-16 12-16s12 7 12 16c0 6-5 9-12 9s-12-3-12-9z M22 18v25 M15 26h14',
                            'M8 40h28 M22 40V16 M22 16c-6 0-9 4-9 8 M22 16c6 0 9 4 9 8',
                        ] as $path)
                            <span class="flex aspect-square items-center justify-center rounded-lg bg-[#F6F1E8]">
                                <svg viewBox="0 0 44 48" fill="none" stroke="#A8987C" stroke-width="1.6"
                                    stroke-linecap="round" class="h-3/5 w-3/5" aria-hidden="true">
                                    <path d="{{ $path }}" />
                                </svg>
                            </span>
                        @endforeach
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