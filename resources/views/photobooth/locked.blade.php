@extends('layouts.app')

@section('title', 'Kreatif - Photobooth Digital')

@section('content')
    <div
        class="relative flex min-h-[calc(100vh_-_13rem)] flex-col justify-center overflow-hidden rounded-3xl px-5 py-4 text-center md:py-5">
        <div aria-hidden="true"
            class="pointer-events-none absolute inset-x-0 top-1/2 h-72 w-[46rem] max-w-full -translate-y-1/2 rounded-full bg-[#F3E4E1]/50 blur-3xl">
        </div>

        <div class="relative">
            <span
                class="inline-flex items-center gap-1.5 rounded-full border border-[#D8A7B1]/50 bg-[#D8A7B1]/10 px-3 py-1 text-[11px] font-bold tracking-wide text-[#C2757F]">
                <i class="fa-solid fa-camera-retro" aria-hidden="true"></i> FITUR ADD-ON
            </span>

            <h1
                class="mx-auto mt-2.5 max-w-2xl font-serif-title text-[1.25rem] font-bold leading-tight text-[#3F4A3B] md:text-[1.5rem]">
                Buka Akses Photobooth Digital untuk Acara Kamu
            </h1>
            <p class="mx-auto mt-1.5 max-w-lg text-[13px] leading-relaxed text-[#5F6F5B]/70">
                Bikin acaramu makin seru dengan foto, GIF, frame, dan live gallery yang bisa dinikmati seluruh tamu.
            </p>

            <div class="relative z-10 mx-auto mt-5 h-44 w-full md:mt-6 md:h-48" style="max-width:34rem;min-height:11rem">
                @foreach ([['img-3.jpg', 'left:5%;top:2.5rem;transform:rotate(-18deg)', 1], ['img-2.jpg', 'left:21%;top:0;transform:rotate(-8deg)', 2], ['img-5.jpg', 'right:21%;top:0;transform:rotate(8deg)', 4], ['img-4.jpg', 'right:5%;top:2.5rem;transform:rotate(18deg)', 3]] as [$image, $position, $layer])
                    <div class="absolute rounded-2xl border border-[#B6ADA3]/30 bg-white p-2 shadow-lg shadow-[#5F6F5B]/10 transition-transform duration-300 ease-out hover:z-20 hover:-translate-y-2 hover:rotate-0 hover:shadow-xl motion-reduce:transform-none sm:p-2.5"
                        style="{{ $position }};z-index:{{ $layer }};width:clamp(4.5rem,13vw,6.5rem)">
                        <img src="{{ asset('img/' . $image) }}" alt=""
                            class="aspect-[3/4] w-full rounded-xl object-cover" loading="lazy">
                    </div>
                @endforeach

                <div class="absolute left-1/2 top-0 z-10 -translate-x-1/2 rounded-2xl border border-[#B6ADA3]/40 bg-white p-2.5 shadow-xl shadow-[#5F6F5B]/15 transition-transform duration-300 ease-out hover:-translate-y-2 hover:shadow-2xl motion-reduce:transform-none"
                    style="width:clamp(7rem,18vw,8.5rem)">
                    <div class="relative aspect-[3/4] overflow-hidden rounded-xl bg-[#FBF7F0]">
                        <div aria-hidden="true" class="absolute inset-0 grid grid-cols-2 gap-1 p-1.5 blur-[7px]">
                            @foreach (['FDF7F0', 'F5E8E0', 'EADFCB', 'F9F3EA'] as $cream)
                                <span class="rounded-md" style="background:#{{ $cream }}"></span>
                            @endforeach
                        </div>
                        <div class="absolute inset-0 flex flex-col items-center justify-center gap-2.5">
                            <span
                                class="flex h-11 w-11 items-center justify-center rounded-full border border-[#B08D57]/40 bg-[#B08D57]/15">
                                <i class="fa-solid fa-lock text-[#B08D57]" aria-hidden="true"></i>
                            </span>
                            <span
                                class="rounded-full bg-[#8A6A45]/12 px-2.5 py-1 text-[10px] font-bold tracking-wide text-[#8A6A45]">ADD-ON</span>
                            <span class="text-[10px] font-medium text-[#A97176]">TERKUNCI</span>
                        </div>
                    </div>
                </div>
            </div>

            <ul class="relative z-20 mt-4 flex flex-wrap items-center justify-center gap-x-8 gap-y-2 text-xs md:mt-5">
                <li class="flex items-center gap-2 text-sm text-[#5F6F5B]"><i class="fa-solid fa-camera text-[#C2757F]"
                        aria-hidden="true"></i><span>Foto &amp; GIF Tanpa Batas</span></li>
                <li class="flex items-center gap-2 text-sm text-[#5F6F5B]"><i
                        class="fa-solid fa-wand-magic-sparkles text-[#C2757F]" aria-hidden="true"></i><span>Frame &amp;
                        Stiker Kustom</span></li>
                <li class="flex items-center gap-2 text-sm text-[#5F6F5B]"><i class="fa-solid fa-images text-[#C2757F]"
                        aria-hidden="true"></i><span>Live Gallery untuk Tamu</span></li>
            </ul>

            <a href="{{ route('upgrade.index', ['feature' => 'photobooth']) }}"
                class="btn-primary-rose mt-4 inline-flex cursor-pointer items-center justify-center gap-2 rounded-full px-6 py-2.5 text-sm font-semibold">
                <i class="fa-solid fa-gem" aria-hidden="true"></i> Buka Akses Photobooth Sekarang
            </a>
            <p class="mt-2.5 text-[11px] text-[#B6ADA3]">Sekali bayar untuk seluruh tamu + QR universal + Desain sesuai tema
            </p>
        </div>
    </div>
@endsection
