@extends('layouts.app')

@section('title', 'Kreatif - Photobooth Digital')

@section('content')
    <div>
        <section class="rounded-[32px] border border-[#E7D7C6] bg-[#F8F4EE] px-4 py-8 sm:px-6 md:px-10 md:py-12">
            <div class="mx-auto grid items-center gap-10 lg:grid-cols-[0.95fr_1.05fr] lg:gap-14">
                <div class="order-2 min-w-0 lg:order-1">
                    <span
                        class="inline-flex items-center gap-2 rounded-full border border-[#D8A7B1]/50 bg-[#FDF4F3] px-3 py-1 text-[11px] font-bold tracking-widest text-[#B36F78]">
                        <span aria-hidden="true">✦</span> FITUR ADD-ON <span aria-hidden="true">✦</span>
                    </span>

                    <h1 class="mt-4 font-serif-title text-3xl font-bold leading-tight text-[#17221C] md:text-4xl">
                        Buka Akses Add-On Spesial Photobooth!
                    </h1>
                    <p class="mt-3 max-w-xl text-sm leading-relaxed text-[#535A53] md:text-base">
                        Bikin acaramu makin seru, biarkan para tamu berekspresi dengan template dan stiker kustom.
                    </p>

                    <ul class="mt-7 space-y-4">
                        <li class="flex items-center gap-3">
                            <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl border border-[#D0C4B4] bg-[#EEE3D3] text-[#A45F63] shadow-sm">
                                <i class="fa-solid fa-camera text-base" aria-hidden="true"></i>
                            </span>
                            <span class="text-sm font-medium text-[#252B27] md:text-base">Foto &amp; GIF Tanpa Batas</span>
                        </li>
                        <li class="flex items-center gap-3">
                            <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl border border-[#D0C4B4] bg-[#EEE3D3] text-[#A58A5D] shadow-sm">
                                <i class="fa-solid fa-lock text-base" aria-hidden="true"></i>
                            </span>
                            <span class="text-sm font-medium text-[#252B27] md:text-base">Frame &amp; Stiker Kustom (PRO)</span>
                        </li>
                        <li class="flex items-center gap-3">
                            <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl border border-[#D0C4B4] bg-[#EEE3D3] text-[#A45F63] shadow-sm">
                                <i class="fa-solid fa-mobile-screen-button text-base" aria-hidden="true"></i>
                            </span>
                            <span class="text-sm font-medium text-[#252B27] md:text-base">Unduh Instan &amp; Live Gallery</span>
                        </li>
                    </ul>

                    <div class="mt-8">
                        <a href="#photobooth-purchase"
                            class="inline-flex items-center gap-2 rounded-full bg-[#B56F78] px-6 py-3 text-sm font-bold text-white shadow-md transition hover:bg-[#A45F68] focus:outline-none focus:ring-2 focus:ring-[#B56F78]/45 focus:ring-offset-2 focus:ring-offset-[#F8F4EE]">
                            <i class="fa-solid fa-lock text-xs" aria-hidden="true"></i> Buka Akses Add-On Sekarang
                        </a>
                        <p class="mt-3 text-[11px] leading-relaxed text-[#535A53] md:text-xs">
                            Sekali bayar untuk seluruh tamu &bull; Bebas unduh &amp; pakai sepuasnya &bull; Desain sesuai tema
                        </p>
                    </div>
                </div>

                <div data-photobooth-collage class="relative order-1 mx-auto h-[360px] w-full max-w-[520px] lg:order-2 lg:h-[520px]" aria-hidden="true">
                    <div class="absolute inset-0 rounded-[28px] bg-[radial-gradient(circle_at_30%_30%,rgba(216,167,177,0.3),transparent_50%),radial-gradient(circle_at_70%_70%,rgba(182,173,163,0.25),transparent_50%)]"></div>

                    <div class="absolute left-[4%] top-[9%] w-[56%] -rotate-[7deg] overflow-hidden rounded-2xl bg-white p-2 shadow-xl">
                        <img src="{{ asset('img/pexels-photo-layout.jpg') }}" alt="" class="h-28 w-full rounded-xl object-cover sm:h-36 lg:h-44">
                        <div class="px-2 pb-2 pt-2 text-[9px] text-[#7B7167]">
                            <p>Galent Kitka</p>
                            <div class="mt-2 flex gap-1">
                                <span class="h-5 w-5 rounded-full bg-[#E0D4C5]"></span>
                                <span class="h-5 w-5 rounded-full bg-[#E6B4A9]"></span>
                                <span class="h-5 w-5 rounded-full bg-[#B3B9A9]"></span>
                            </div>
                        </div>
                    </div>

                    <div class="absolute right-[0%] top-[10%] w-[36%] rotate-[7deg] rounded-2xl bg-white p-2 shadow-lg">
                        <div class="rounded-xl bg-[#FCFAF7] p-3 text-center">
                            <i class="fa-solid fa-qrcode mx-auto block py-2 text-4xl text-[#676157]"></i>
                            <p class="text-[9px] font-medium text-[#8B8378]">Momenmu bisa difoto langsung</p>
                        </div>
                    </div>

                    <div class="absolute left-[6%] top-[46%] w-[46%] rotate-[-3deg] overflow-hidden rounded-xl bg-white p-2 shadow-md">
                        <div class="grid grid-cols-2 gap-1">
                            <img src="{{ asset('img/pexels-bridal-flatlay.jpg') }}" alt="" class="h-20 w-full rounded-lg object-cover sm:h-24 lg:h-28">
                            <img src="{{ asset('img/pexels-flatlay-seal.jpg') }}" alt="" class="h-20 w-full rounded-lg object-cover sm:h-24 lg:h-28">
                        </div>
                        <p class="px-2 pb-1 pt-2 text-[9px] text-[#9B9286]">Frame &amp; stickers</p>
                        <div class="flex gap-1 pb-1">
                            <span class="h-6 w-6 rounded-md border border-[#D8CFC2] bg-[#F8F4EE]"></span>
                            <span class="h-6 w-6 rounded-md border border-[#D8CFC2] bg-[#EDE0D5]"></span>
                            <span class="h-6 w-6 rounded-md border border-[#D8CFC2] bg-[#F6ECE9]"></span>
                        </div>
                    </div>

                    <div class="absolute right-[7%] top-[42%] w-[38%] rotate-[4deg] overflow-hidden rounded-2xl bg-white p-2 shadow-lg">
                        <img src="{{ asset('img/pexels-cake-floral.jpg') }}" alt="" class="h-24 w-full rounded-xl object-cover sm:h-28 lg:h-36">
                        <div class="mt-2 flex items-center justify-between rounded-lg bg-[#274329] px-2 py-1 text-white">
                            <span class="text-[8px] font-semibold">Live Gallery</span>
                            <i class="fa-solid fa-qrcode text-xs"></i>
                        </div>
                    </div>

                    <div class="absolute bottom-[2%] left-[23%] w-[42%] rotate-[5deg] rounded-lg bg-[#22201D] p-1.5 shadow-2xl">
                        <img src="{{ asset('img/pexels-ceremony-flowers.jpg') }}" alt="" class="h-28 w-full rounded-sm object-cover sm:h-32 lg:h-40">
                        <p class="mt-1 text-center text-[7px] tracking-wide text-white/90">Budi &amp; Siti · 12.12.2026</p>
                    </div>

                    <span class="absolute left-[2%] top-[38%] flex h-9 w-9 items-center justify-center rounded-full border-[3px] border-white bg-[#F1D3D1] text-sm text-[#9A5252] shadow-md">
                        <i class="fa-solid fa-heart" aria-hidden="true"></i>
                    </span>
                    <span class="absolute bottom-[24%] right-[6%] flex h-9 w-9 items-center justify-center rounded-full border-[3px] border-white bg-white text-xs text-[#B56F78] shadow-md">
                        <i class="fa-solid fa-gift" aria-hidden="true"></i>
                    </span>
                    <span class="absolute bottom-[12%] left-[10%] flex h-10 w-10 items-center justify-center rounded-full border-[3px] border-white bg-[#E7D4C0] text-sm text-[#9A7B57] shadow-md">
                        <i class="fa-solid fa-face-smile"></i>
                    </span>

                    <div class="absolute left-1/2 top-1/2 w-[39%] -translate-x-1/2 -translate-y-1/2 rounded-2xl border border-white/80 bg-white/80 p-4 text-center shadow-2xl backdrop-blur-md">
                        <span class="mx-auto flex h-12 w-12 items-center justify-center rounded-full border border-[#E6D4BF] bg-[#F9F1E6] text-[#AA8B61]">
                            <i class="fa-solid fa-lock"></i>
                        </span>
                        <span class="mt-2 inline-block rounded-full bg-[#F1E0DA] px-2.5 py-0.5 text-[9px] font-bold tracking-widest text-[#8F6463]">ADD-ON</span>
                        <p class="mt-1.5 text-[10px] font-semibold leading-tight text-[#292D29]">Frame &amp; Stiker Kustom (PRO)</p>
                        <p class="mt-1 text-[9px] tracking-widest text-[#A97176]">TERKUNCI</p>
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection
