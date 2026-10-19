<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Undangan Pasangan — Wedding Planner</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&family=Playfair+Display:ital,wght@0,500;0,600;0,700;1,600&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="shortcut icon" href="{{ asset('img/nupsera-ori.jpg') }}" type="image/x-icon">
    @vite(['resources/css/app.css'])
    <style>
        /* Layout mandiri, meniru halaman Login: halaman ini berdiri sendiri
           karena yang membukanya belum punya akun, jadi tidak bisa memakai
           layout aplikasi yang sidebar-nya butuh user yang login. */

        body {
            font-family: 'Outfit', sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #FAF7F2;
            padding: 20px;
        }

        .invite-card {
            background: #ffffff;
            border-radius: 24px;
            padding: 36px 32px;
            max-width: 460px;
            width: 100%;
            border: 1px solid #e5e0d8;
            box-shadow: 0 8px 32px rgba(95, 111, 91, 0.08);
        }
    </style>
</head>

<body>
    <div class="invite-card">
        <div class="w-16 h-16 rounded-2xl bg-[#D8A7B1]/20 flex items-center justify-center mx-auto mb-4">
            <i class="fa-solid fa-heart text-[#D8A7B1] text-2xl"></i>
        </div>

        <h1 class="font-serif-title text-2xl text-[#5F6F5B] text-center">Kamu Diundang</h1>
        <p class="text-center text-[13px] text-[#B6ADA3] mt-1 mb-6">
            Kamu diundang untuk bergabung ke Wedding Kit pasanganmu dan mulai mengelola rencana pernikahan
            bersama.
        </p>

        {{-- couple_name sudah nama depan saja, jadi nama lengkap pasangan tidak ikut
             terbawa kalau link ini tanpa sengaja dibagikan ke orang lain. --}}
        <div class="rounded-xl bg-[#FAF7F2] border border-[#B6ADA3]/35 px-4 py-3 mb-5 text-center">
            <div class="text-[11px] uppercase tracking-wider font-bold text-[#B6ADA3]">Wedding Kit</div>
            <div class="text-sm font-bold text-[#5F6F5B] mt-0.5">
                {{ $invite->wedding->couple_name ?: 'Wedding Planner' }}
            </div>
        </div>

        <form method="POST" action="{{ route('invite.accept.store', $invite->token) }}" class="space-y-4">
            @csrf

            <div>
                <label for="name" class="block text-xs font-bold uppercase tracking-wider text-[#5F6F5B] mb-1.5">
                    Nama Lengkap *
                </label>
                <input type="text" name="name" id="name" value="{{ old('name') }}" required autofocus
                    placeholder="Nama lengkapmu"
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
                Password minimal 8 karakter. Buat password yang kamu ingat sendiri — aplikasi ini belum punya fitur
                lupa password, jadi kalau lupa kamu perlu dihubungi pemilik Wedding Kit.
            </p>

            <button type="submit" class="btn-primary-rose w-full px-5 py-3 rounded-xl text-sm cursor-pointer">
                <i class="fa-solid fa-heart mr-1"></i> Terima Undangan &amp; Masuk
            </button>
        </form>

        <p class="mt-5 text-center text-[11px] text-[#B6ADA3]">
            Sudah punya akun? <a href="{{ route('login') }}" class="font-semibold text-[#C2757F] hover:underline">Masuk
                di sini</a>
        </p>
    </div>
</body>

</html>