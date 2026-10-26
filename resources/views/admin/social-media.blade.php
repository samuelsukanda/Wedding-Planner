@extends('layouts.app')

@section('title', 'Social Media')

@section('content')
    <div class="space-y-6">
        <!-- Header -->
        <div class="bg-white p-5 rounded-2xl border border-[#B6ADA3]/35 shadow-xs">
            <h1 class="text-xl md:text-2xl font-bold font-serif-title text-[#5F6F5B]">Social Media</h1>
            <p class="text-xs text-[#5F6F5B] mt-1">Atur tautan media sosial yang akan muncul di kartu "Hubungi Kami" pada
                profil pasangan. Semua kolom bersifat opsional; tombol hanya tampil jika kolom diisi.</p>
        </div>

        <form method="POST" action="{{ route('admin.social-media.update') }}"
            class="bg-white p-6 rounded-2xl border border-[#B6ADA3]/35 shadow-xs space-y-5 max-w-2xl">
            @csrf
            @method('PUT')

            <div>
                <label for="social_whatsapp"
                    class="block text-xs font-bold uppercase tracking-wider text-[#5F6F5B] mb-1.5">WhatsApp</label>
                <input type="text" name="social_whatsapp" id="social_whatsapp" inputmode="numeric"
                    value="{{ old('social_whatsapp', $values['social_whatsapp']) }}" placeholder="6281234567890"
                    aria-label="WhatsApp"
                    class="block w-full px-4 py-2.5 rounded-xl border border-[#B6ADA3]/40 bg-[#FAF7F2] text-sm text-[#5F6F5B] placeholder-[#B6ADA3] placeholder:font-normal focus:border-[#D8A7B1] focus:outline-none transition">
                @error('social_whatsapp')
                    <p class="mt-1.5 text-sm text-[#8B3A47]">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="social_instagram"
                    class="block text-xs font-bold uppercase tracking-wider text-[#5F6F5B] mb-1.5">Instagram</label>
                <input type="text" name="social_instagram" id="social_instagram"
                    value="{{ old('social_instagram', $values['social_instagram']) }}"
                    placeholder="https://instagram.com/nupsera"
                    aria-label="Instagram"
                    class="block w-full px-4 py-2.5 rounded-xl border border-[#B6ADA3]/40 bg-[#FAF7F2] text-sm text-[#5F6F5B] placeholder-[#B6ADA3] placeholder:font-normal focus:border-[#D8A7B1] focus:outline-none transition">
                @error('social_instagram')
                    <p class="mt-1.5 text-sm text-[#8B3A47]">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="social_tiktok"
                    class="block text-xs font-bold uppercase tracking-wider text-[#5F6F5B] mb-1.5">TikTok</label>
                <input type="text" name="social_tiktok" id="social_tiktok"
                    value="{{ old('social_tiktok', $values['social_tiktok']) }}" placeholder="https://tiktok.com/@nupsera"
                    aria-label="TikTok"
                    class="block w-full px-4 py-2.5 rounded-xl border border-[#B6ADA3]/40 bg-[#FAF7F2] text-sm text-[#5F6F5B] placeholder-[#B6ADA3] placeholder:font-normal focus:border-[#D8A7B1] focus:outline-none transition">
                @error('social_tiktok')
                    <p class="mt-1.5 text-sm text-[#8B3A47]">{{ $message }}</p>
                @enderror
            </div>

            <button type="submit" class="btn-primary-rose px-5 py-2.5 rounded-xl text-sm cursor-pointer">
                Simpan Pengaturan
            </button>
        </form>
    </div>
@endsection
