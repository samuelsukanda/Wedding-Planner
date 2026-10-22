@extends('layouts.app')

@section('title', 'Kreatif - Photobooth')

@section('content')
    <div class="mx-auto max-w-3xl space-y-6">
        <div class="relative overflow-hidden rounded-3xl bg-[#3F4A3B] px-6 py-8 text-white shadow-lg md:px-10 md:py-10">
            <div class="pointer-events-none absolute -right-20 -top-24 h-64 w-64 rounded-full bg-[#D8A7B1]/30 blur-3xl"></div>
            <div class="relative">
                <span class="inline-flex items-center gap-2 rounded-full border border-white/20 bg-white/10 px-3 py-1 text-[11px] font-bold uppercase tracking-wider text-[#F8E4E8]">
                    <i class="fa-solid fa-camera-retro" aria-hidden="true"></i> Add-on Photobooth
                </span>
                <h1 class="mt-4 font-serif-title text-2xl font-bold md:text-3xl">Photobooth untuk acaraeddinganmu</h1>
                <p class="mt-3 max-w-xl text-sm leading-relaxed text-white/90">
                    Satu QR untuk semua tamu, galeri foto bersama, dan frame yang memakai nama serta tanggal acara kamu.
                </p>
            </div>
        </div>

        @if (session('success'))
            <p class="rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">{{ session('success') }}</p>
        @endif
        @if (session('error'))
            <p class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">{{ session('error') }}</p>
        @endif

        @if ($pending)
            <div class="rounded-2xl border border-[#D8A7B1]/50 bg-[#FDF7F5] p-6">
                <h2 class="font-semibold text-[#2D372E]">Pembayaran Sedang Diverifikasi</h2>
                <p class="mt-1 text-sm text-[#5F6F5B]">Admin sedang memeriksa bukti transfer kamu. Halaman ini akan otomatis berubah setelah disetujui.</p>
                <p class="mt-3 rounded-xl bg-white px-4 py-3 text-sm text-[#5F6F5B]">
                    Nominal transfer: <span class="font-semibold text-[#2D372E]">Rp {{ number_format((int) $pending->amount, 0, ',', '.') }}</span>
                </p>
            </div>
        @else
            <div class="rounded-2xl border border-[#B6ADA3]/40 bg-white p-6 shadow-xs space-y-5">
                <div>
                    <h2 class="font-semibold text-[#2D372E]">Harga Add-on</h2>
                    @if ($price > 0)
                        <p class="mt-1 text-2xl font-bold font-serif-title text-[#C2757F]">Rp {{ number_format($price, 0, ',', '.') }}</p>
                        <p class="text-xs text-[#5F6F5B]/70">Sekali bayar, berlaku untuk acara ini.</p>
                    @else
                        <p class="mt-1 text-sm text-[#5F6F5B]">Harga sedang disiapkan admin. Silakan hubungi admin untuk info terbaru.</p>
                    @endif
                </div>

                @if ($banks)
                    <div>
                        <h3 class="text-xs font-bold uppercase tracking-wider text-[#5F6F5B]">Rekening Tujuan</h3>
                        <ul class="mt-2 space-y-1.5 text-sm text-[#2D372E]">
                            @foreach ($banks as $bank)
                                <li class="flex items-center gap-2">
                                    <i class="fa-solid {{ $bank['icon'] }} text-[#C2757F]" aria-hidden="true"></i>
                                    <span class="font-semibold">{{ $bank['name'] }}</span>
                                    <span class="font-mono">{{ $bank['number'] }}</span>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @if ($paymentNote)
                    <p class="text-sm text-[#5F6F5B]">{{ $paymentNote }}</p>
                @endif

                <form method="POST" action="{{ route('photobooth.purchase') }}" enctype="multipart/form-data" class="space-y-3">
                    @csrf
                    <div>
                        <label for="photobooth_proof" class="block text-xs font-semibold text-[#5F6F5B] mb-1">Bukti Transfer</label>
                        <input type="file" name="proof" id="photobooth_proof" accept="image/jpeg,image/png,application/pdf" required
                            class="block w-full text-sm text-[#5F6F5B]">
                        @error('proof')
                            <p class="mt-1.5 text-sm text-[#C2757F]">{{ $message }}</p>
                        @enderror
                    </div>
                    <button type="submit" class="inline-flex items-center gap-2 rounded-full bg-[#8B3A47] hover:bg-[#722F3A] px-5 py-2.5 text-sm font-semibold text-white transition shadow-xs">
                        <i class="fa-solid fa-paper-plane" aria-hidden="true"></i> Kirim Bukti
                    </button>
                </form>
            </div>
        @endif
    </div>
@endsection
