@extends('layouts.app')

@section('title', 'Pengaturan Photobooth')

@section('content')
    @php
        $money = function ($value) {
            $digits = preg_replace('/\D/', '', (string) $value);

            return $digits === '' ? '' : 'Rp ' . number_format((int) $digits, 0, ',', '.');
        };
    @endphp

    <div class="space-y-6">
        <div class="bg-white p-5 rounded-2xl border border-[#B6ADA3]/35 shadow-xs">
            <h1 class="text-xl md:text-2xl font-bold font-serif-title text-[#5F6F5B]">Pengaturan Photobooth</h1>
            <p class="text-xs text-[#5F6F5B]/70 mt-1">Harga add-on Photobooth dan batas jumlah foto per event.
                Batas foto langsung disiarkan ke event yang sudah aktif.</p>
        </div>

        <form method="POST" action="{{ route('admin.photobooth-settings.update') }}"
            class="bg-white p-6 rounded-2xl border border-[#B6ADA3]/35 shadow-xs space-y-5 max-w-2xl">
            @csrf
            @method('PUT')

            <div>
                <label for="photobooth_price" class="block text-xs font-bold uppercase tracking-wider text-[#5F6F5B] mb-1.5">
                    Harga Add-on Photobooth *
                </label>
                <input type="text" name="photobooth_price" id="photobooth_price" inputmode="numeric" required data-format="money"
                    value="{{ old('photobooth_price', $money($price)) }}" placeholder="Rp 0"
                    class="block w-full px-4 py-2.5 rounded-xl border border-[#B6ADA3]/40 bg-[#FAF7F2] text-sm font-bold text-[#5F6F5B] placeholder-[#B6ADA3] placeholder:font-normal focus:border-[#D8A7B1] focus:outline-none transition">
                @error('photobooth_price')
                    <p class="mt-1.5 text-sm text-[#C2757F]">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="photobooth_photo_quota" class="block text-xs font-bold uppercase tracking-wider text-[#5F6F5B] mb-1.5">
                    Batas Foto per Event *
                </label>
                <input type="number" name="photobooth_photo_quota" id="photobooth_photo_quota" min="0" required
                    value="{{ old('photobooth_photo_quota', $quota) }}"
                    class="block w-full px-4 py-2.5 rounded-xl border border-[#B6ADA3]/40 bg-[#FAF7F2] text-sm font-bold text-[#5F6F5B] focus:border-[#D8A7B1] focus:outline-none transition">
                <p class="mt-1.5 text-xs text-[#5F6F5B]/70">0 berarti tanpa batas. Setelah batas tercapai, upload foto baru dari tamu ditolak.</p>
                @error('photobooth_photo_quota')
                    <p class="mt-1.5 text-sm text-[#C2757F]">{{ $message }}</p>
                @enderror
            </div>

            <button type="submit" class="px-5 py-2.5 rounded-full bg-[#C2757F] hover:bg-[#B56874] text-white text-sm font-bold transition shadow-xs">
                Simpan Pengaturan
            </button>
        </form>

        <div class="bg-white p-5 rounded-2xl border border-[#B6ADA3]/35 shadow-xs">
            <h2 class="text-base font-bold font-serif-title text-[#5F6F5B]">Event Photobooth Aktif</h2>
            @if ($events->isEmpty())
                <p class="mt-2 text-sm text-[#5F6F5B]/70">Belum ada pasangan yang mengaktifkan Photobooth.</p>
            @else
                <ul class="mt-3 divide-y divide-[#B6ADA3]/20 text-sm">
                    @foreach ($events as $event)
                        <li class="flex flex-wrap items-center justify-between gap-2 py-2">
                            <span class="font-medium text-[#2D372E]">{{ $event->couple_name }}</span>
                            <span class="text-xs text-[#5F6F5B]/70">{{ $event->photobooth_event_slug ?? 'belum terhubung' }}</span>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>
    </div>
@endsection
