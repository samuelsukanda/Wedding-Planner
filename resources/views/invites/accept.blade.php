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

    <h1>Bergabung ke Wedding Workspace</h1>
    <p class="subtitle">Anda diundang untuk bergabung ke Wedding Workspace pasangan.</p>

    <form method="POST" action="{{ route('invite.accept.store', $invite->token) }}">
        @csrf

        <div class="form-group">
            <label for="name">Nama Lengkap *</label>
            <div class="input-wrapper">
                <i class="fa-solid fa-user"></i>
                <input type="text" id="name" name="name" value="{{ old('name') }}" required autofocus
                    placeholder="Nama lengkap">
            </div>
            @error('name')
                <span class="error-text">{{ $message }}</span>
            @enderror
        </div>

        <div class="form-group">
            <label for="email">Email *</label>
            <div class="input-wrapper">
                <i class="fa-solid fa-envelope"></i>
                <input type="email" id="email" name="email" value="{{ old('email') }}" required
                    placeholder="email@contoh.com">
            </div>
            @error('email')
                <span class="error-text">{{ $message }}</span>
            @enderror
        </div>

        <div class="form-group">
            <label for="password">Password *</label>
            <div class="input-wrapper">
                <i class="fa-solid fa-lock"></i>
                <input type="password" id="password" name="password" required placeholder="Min. 8 karakter">
            </div>
            @error('password')
                <span class="error-text">{{ $message }}</span>
            @enderror
        </div>

        <div class="form-group">
            <label for="password_confirmation">Ulangi Password *</label>
            <div class="input-wrapper">
                <i class="fa-solid fa-lock"></i>
                <input type="password" id="password_confirmation" name="password_confirmation" required
                    placeholder="Ulangi password">
            </div>
        </div>

        <button type="submit" class="btn-primary-rose w-full px-5 py-3 rounded-xl text-sm cursor-pointer">
            <i class="fa-solid fa-arrow-right-to-bracket" style="margin-right: 8px;"></i> Daftar & Bergabung
        </button>
    </form>

    {{-- Sama seperti halaman login: pendaftaran juga bisa lewat akun Google. --}}
    <div class="divider">atau</div>

    <a href="{{ route('google.redirect', ['invite' => $invite->token]) }}" class="btn-google"
        style="text-decoration:none;" data-turbo="false">
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
        <p class="google-note">Login Google belum aktif. Admin perlu mengisi kredensial OAuth.</p>
    @endunless

    <p class="footer-text">
        Sudah punya akun? <a href="{{ route('login') }}"
            class="font-semibold text-[#C2757F] hover:underline">Masuk di sini</a>
    </p>
@endsection
