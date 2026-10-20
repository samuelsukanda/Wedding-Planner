@extends('invites.layout')

@section('title', 'Undangan Pasangan')

@section('card')
    {{-- Logo Nupsera: ikon + nama brand sesuai desain sidebar & login --}}
    <div style="display:flex;flex-direction:column;align-items:center;gap:6px;margin-bottom:18px;">
        <img src="{{ asset('img/nupsera-ori.png') }}" alt="Logo Nupsera"
            style="height:56px;width:auto;object-fit:contain;display:block;">
        <div style="text-align:center;line-height:1;">
            <div
                style="font-family:'Playfair Display',serif;font-size:22px;font-weight:700;
                    background:linear-gradient(135deg,#8B4513 0%,#C07830 35%,#D4A96A 60%,#B8762A 100%);
                    -webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;">
                Nupsera</div>
            <div
                style="font-family:'Outfit',sans-serif;font-size:9px;font-weight:600;
                    letter-spacing:0.18em;text-transform:uppercase;color:#4A5240;margin-top:2px;">
                Wedding &amp; Moments</div>
        </div>
    </div>

    <h1 class="font-serif-title text-2xl text-[#5F6F5B] text-center">Bergabung ke Wedding Workspace</h1>
    <p class="text-center text-[13px] text-[#B6ADA3] mt-1 mb-6">
        Anda diundang untuk bergabung ke Wedding Workspace pasangan.
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
                <label for="password" class="block text-xs font-bold uppercase tracking-wider text-[#5F6F5B] mb-1.5">
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

        <button type="submit" class="btn-primary-rose w-full px-5 py-3 rounded-xl text-sm cursor-pointer">
            <i class="fa-solid fa-arrow-right-to-bracket" style="margin-right: 8px;"></i> Daftar & Bergabung
        </button>
    </form>

    {{-- Sama seperti halaman login: pendaftaran juga bisa lewat akun Google. --}}
    <div class="my-5 flex items-center gap-3 text-[11px] text-[#B6ADA3]">
        <span class="h-px flex-1 bg-[#B6ADA3]/40"></span>
        atau
        <span class="h-px flex-1 bg-[#B6ADA3]/40"></span>
    </div>

    <a href="{{ route('google.redirect', ['invite' => $invite->token]) }}"
        class="flex w-full items-center justify-center gap-2 rounded-xl border border-[#B6ADA3]/50 bg-white px-5 py-2.5 text-sm font-semibold text-[#5F6F5B] shadow-sm hover:bg-[#FAF7F2] transition-all no-underline">
        <svg width="18" height="18" viewBox="0 0 48 48" aria-hidden="true">
            <path fill="#EA4335"
                d="M24 9.5c3.54 0 6.71 1.23 9.21 3.6l6.85-6.85C35.9 2.38 30.47 0 24 0 14.62 0 6.51 5.38 2.56 13.22l7.98 6.19C12.43 13.72 17.74 9.5 24 9.5z" />
            <path fill="#4285F4"
                d="M46.98 24.55c0-1.57-.15-3.09-.38-4.55H24v9.02h12.94c-.58 2.96-2.26 5.48-4.78 7.18l7.73 6c4.51-4.18 7.09-10.36 7.09-17.65z" />
            <path fill="#FBBC05"
                d="M10.53 28.59A14.42 14.42 0 0 1 9.75 24c0-1.59.27-3.13.76-4.59l-7.98-6.19A23.87 23.87 0 0 0 0 24c0 3.87.93 7.54 2.56 10.78l7.97-6.19z" />
            <path fill="#34A853"
                d="M24 48c6.48 0 11.93-2.13 15.89-5.81l-7.73-6c-2.15 1.45-4.92 2.3-8.16 2.3-6.26 0-11.57-4.22-13.47-9.91l-7.98 6.19C6.51 42.62 14.62 48 24 48z" />
        </svg>
        Lanjutkan dengan Google
    </a>

    @unless (config('services.google.client_id'))
        <p class="mt-2 text-center text-[11px] text-[#B6ADA3]">
            Login Google belum aktif. Admin perlu mengisi kredensial OAuth.
        </p>
    @endunless

    <p class="mt-5 text-center text-[11px] text-[#B6ADA3]">
        Sudah punya akun? <a href="{{ route('login') }}" class="font-semibold text-[#C2757F] hover:underline">Masuk
            di sini</a>
    </p>
@endsection
