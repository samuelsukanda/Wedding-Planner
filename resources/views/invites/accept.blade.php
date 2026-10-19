@extends('invites.layout')

@section('title', 'Undangan Pasangan')

@section('card')
        <div class="w-16 h-16 rounded-2xl bg-[#D8A7B1]/20 flex items-center justify-center mx-auto mb-4">
            <i class="fa-solid fa-heart text-[#D8A7B1] text-2xl"></i>
        </div>

        <h1 class="font-serif-title text-2xl text-[#5F6F5B] text-center">Kamu Diundang</h1>
        <p class="text-center text-[13px] text-[#B6ADA3] mt-1 mb-6">
            Kamu diundang untuk bergabung ke Wedding Workspace pasanganmu dan mulai mengelola rencana pernikahan
            bersama.
        </p>

        <form method="POST" action="{{ route('invite.accept.store', $invite->token) }}" class="space-y-4">
            @csrf

            <div>
                <label for="name" class="block text-xs font-bold uppercase tracking-wider text-[#5F6F5B] mb-1.5">
                    Nama Lengkap *
                </label>
                <input type="text" name="name" id="name" value="{{ old('name') }}" required autofocus
                    placeholder="Nama lengkap"
                    class="block w-full bg-[#FAF7F2] border border-[#B6ADA3]/50 rounded-xl px-3.5 py-2.5 text-sm text-[#5F6F5B] focus:border-[#D8A7B1] focus:bg-white focus:outline-none transition-all">
                @error('name')
                    <p class="mt-1.5 text-xs text-[#C2757F]">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="email" class="block text-xs font-bold uppercase tracking-wider text-[#5F6F5B] mb-1.5">
                    Email *
                </label>
                <input type="email" name="email" id="email" value="{{ old('email') }}" required
                    placeholder="email@contoh.com"
                    class="block w-full bg-[#FAF7F2] border border-[#B6ADA3]/50 rounded-xl px-3.5 py-2.5 text-sm text-[#5F6F5B] focus:border-[#D8A7B1] focus:bg-white focus:outline-none transition-all">
                @error('email')
                    <p class="mt-1.5 text-xs text-[#C2757F]">{{ $message }}</p>
                @enderror
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label for="password"
                        class="block text-xs font-bold uppercase tracking-wider text-[#5F6F5B] mb-1.5">
                        Password *
                    </label>
                    <input type="password" name="password" id="password" required
                        class="block w-full bg-[#FAF7F2] border border-[#B6ADA3]/50 rounded-xl px-3.5 py-2.5 text-sm text-[#5F6F5B] focus:border-[#D8A7B1] focus:bg-white focus:outline-none transition-all">
                    @error('password')
                        <p class="mt-1.5 text-xs text-[#C2757F]">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="password_confirmation"
                        class="block text-xs font-bold uppercase tracking-wider text-[#5F6F5B] mb-1.5">
                        Ulangi Password *
                    </label>
                    <input type="password" name="password_confirmation" id="password_confirmation" required
                        class="block w-full bg-[#FAF7F2] border border-[#B6ADA3]/50 rounded-xl px-3.5 py-2.5 text-sm text-[#5F6F5B] focus:border-[#D8A7B1] focus:bg-white focus:outline-none transition-all">
                </div>
            </div>

            <p class="text-[11px] text-[#B6ADA3] leading-relaxed">
                Password minimal 8 karakter.
            </p>

            <button type="submit" class="btn-primary-rose w-full px-5 py-3 rounded-xl text-sm cursor-pointer">
                <i class="fa-solid fa-arrow-right-to-bracket" style="margin-right: 8px;"></i> Masuk
            </button>
        </form>

        <p class="mt-5 text-center text-[11px] text-[#B6ADA3]">
            Sudah punya akun? <a href="{{ route('login') }}" class="font-semibold text-[#C2757F] hover:underline">Masuk
                di sini</a>
        </p>
@endsection
