<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description"
        content="Nupsera: Aplikasi wedding planner dan photobooth virtual pernikahan. Kelola anggaran, jadwal vendor, dan undangan secara serba otomatis.">
    <link rel="canonical" href="https://nupsera.my.id/">
    <link rel="author" href="https://nupsera.my.id/llms.txt">
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="Nupsera">
    <meta property="og:title" content="Nupsera — Aplikasi Wedding Planner dan Photobooth Virtual">
    <meta property="og:description"
        content="Nupsera: Aplikasi wedding planner dan photobooth virtual pernikahan. Kelola anggaran, jadwal vendor, dan undangan secara serba otomatis.">
    <meta property="og:url" content="https://nupsera.my.id/">
    <meta property="og:image" content="https://nupsera.my.id/img/logo-brand.jpg">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="Nupsera — Aplikasi Wedding Planner dan Photobooth Virtual">
    <meta name="twitter:description"
        content="Nupsera: Aplikasi wedding planner dan photobooth virtual pernikahan. Kelola anggaran, jadwal vendor, dan undangan secara serba otomatis.">
    <meta name="twitter:image" content="https://nupsera.my.id/img/logo-brand.jpg">
    @php(
    $seoSchema = [
        '@context' => 'https://schema.org',
        '@type' => 'WebApplication',
        'name' => 'Nupsera',
        'alternateName' => 'Nupsera Wedding & Moments',
        'url' => 'https://nupsera.my.id/',
        'applicationCategory' => 'LifestyleApplication',
        'operatingSystem' => 'Web',
        'inLanguage' => 'id-ID',
        'description' => 'Aplikasi wedding planner Indonesia untuk mengatur checklist, budget, vendor, tamu, dan rundown pernikahan.',
        'offers' => [['@type' => 'Offer', 'name' => 'Free', 'price' => '0', 'priceCurrency' => 'IDR'], ['@type' => 'Offer', 'name' => 'Pro', 'priceCurrency' => 'IDR']]
    ]
)
    <script type="application/ld+json">{!! json_encode($seoSchema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
    <title>Nupsera - Wedding &amp; Moments</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="preload"
        href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&family=Playfair+Display:ital,wght@0,500;0,600;0,700;1,600&display=swap"
        as="style" onload="this.onload=null;this.rel='stylesheet'">
    <noscript>
        <link rel="stylesheet"
            href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&family=Playfair+Display:ital,wght@0,500;0,600;0,700;1,600&display=swap">
    </noscript>
    <link rel="preload" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" as="style"
        onload="this.onload=null;this.rel='stylesheet'">
    <noscript>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    </noscript>
    <link rel="shortcut icon" href="{{ asset('img/nupsera-ori.jpg') }}" type="image/x-icon">
    @vite(['resources/css/app.css'])
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        /* Reset global (margin/padding/box-sizing) sengaja TIDAK ditulis di
           sini. Preflight Tailwind sudah melakukannya, dan CSS tanpa @layer
           di halaman ini mengalahkan utility Tailwind karena aturan cascade:
           gaya tanpa layer selalu menang atas gaya berlapis. Akibatnya padding
           pada komponen toast hilang dan alert jadi tidak proporsi. */

        body {
            font-family: 'Outfit', sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #FAF7F2;
            padding: 20px;
        }

        #main-content {
            width: 100%;
            display: flex;
            justify-content: center;
        }

        .login-card {
            background: #ffffff;
            border-radius: 24px;
            padding: 40px 36px;
            max-width: 440px;
            width: 100%;
            border: 1px solid #e5e0d8;
            box-shadow: 0 8px 32px rgba(95, 111, 91, 0.08);
        }

        .brand-icon {
            width: 60px;
            height: 60px;
            border-radius: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 18px;
        }

        h1 {
            font-family: 'Playfair Display', serif;
            font-size: 24px;
            color: #5F6F5B;
            text-align: center;
            margin-bottom: 4px;
        }

        .subtitle {
            text-align: center;
            font-size: 13px;
            color: #6B6258;
            margin-bottom: 26px;
        }

        .tabs {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 4px;
            padding: 5px;
            background: #F4F1EC;
            border-radius: 14px;
            margin-bottom: 24px;
        }

        .tab-btn {
            padding: 11px 8px;
            border: none;
            background: transparent;
            border-radius: 10px;
            font-size: 13px;
            font-weight: 600;
            font-family: 'Outfit', sans-serif;
            color: #5F6F5B;
            cursor: pointer;
            transition: all 0.2s;
        }

        .tab-btn[aria-selected="true"] {
            background: #ffffff;
            color: #5F6F5B;
            box-shadow: 0 2px 8px rgba(95, 111, 91, 0.1);
        }

        .form-group {
            margin-bottom: 18px;
        }

        label {
            display: block;
            font-size: 12px;
            font-weight: 600;
            color: #5F6F5B;
            margin-bottom: 6px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .input-wrapper {
            position: relative;
        }

        .input-wrapper i {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: #6B6258;
            font-size: 14px;
        }

        input[type="email"],
        input[type="password"],
        input[type="text"] {
            width: 100%;
            padding: 12px 14px 12px 42px;
            border: 1.5px solid #e5e0d8;
            border-radius: 12px;
            font-size: 14px;
            font-family: 'Outfit', sans-serif;
            color: #2D372E;
            background: #FAF7F2;
            transition: all 0.2s;
            outline: none;
        }

        input:focus {
            border-color: #D8A7B1;
            background: #ffffff;
            box-shadow: 0 0 0 3px rgba(216, 167, 177, 0.15);
        }

        input::placeholder {
            color: #81786E;
        }

        .btn-primary {
            width: 100%;
            padding: 13px;
            background: linear-gradient(135deg, #B87584, #A55F70);
            color: #fff;
            border: none;
            border-radius: 12px;
            font-size: 14px;
            font-weight: 700;
            font-family: 'Outfit', sans-serif;
            cursor: pointer;
            transition: all 0.2s;
            margin-top: 4px;
        }

        .btn-primary:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 16px rgba(165, 95, 112, 0.35);
        }

        .btn-primary:active {
            transform: translateY(0);
        }

        .error {
            background: #fef2f2;
            border: 1px solid #fecaca;
            border-radius: 10px;
            padding: 10px 14px;
            font-size: 13px;
            color: #dc2626;
            margin-bottom: 18px;
            display: flex;
            align-items: flex-start;
            gap: 8px;
        }

        .divider {
            display: flex;
            align-items: center;
            gap: 12px;
            margin: 22px 0 16px;
            color: #6B6258;
            font-size: 12px;
        }

        .divider::before,
        .divider::after {
            content: "";
            flex: 1;
            height: 1px;
            background: #EDE8E1;
        }

        .btn-google {
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            padding: 12px;
            background: #ffffff;
            border: 1.5px solid #e5e0d8;
            border-radius: 12px;
            font-size: 14px;
            font-weight: 600;
            font-family: 'Outfit', sans-serif;
            color: #2D372E;
            cursor: pointer;
            transition: all 0.2s;
        }

        .btn-google:hover {
            background: #FAF7F2;
            border-color: #D8A7B1;
        }

        .btn-google img {
            width: 18px;
            height: 18px;
        }

        .google-note {
            margin-top: 10px;
            font-size: 11px;
            color: #6B6258;
            text-align: center;
        }

        .footer-text {
            text-align: center;
            font-size: 12px;
            color: #6B6258;
            margin-top: 24px;
            padding-top: 18px;
            border-top: 1px solid #f0ece6;
        }

        [x-cloak] {
            display: none !important;
        }
    </style>
</head>

<body>
    <main id="main-content">
        <div class="login-card" x-data="{ tab: '{{ old('_form') === 'register' || ($errors->any() && old('password_confirmation')) ? 'register' : 'login' }}' }">
            {{-- Logo Nupsera: ikon + nama brand sesuai desain sidebar --}}
            <div style="display:flex;flex-direction:column;align-items:center;gap:6px;margin-bottom:18px;">
                <img src="{{ asset('img/nupsera-ori.png') }}" alt="Logo Nupsera" width="56" height="56"
                    fetchpriority="high" decoding="async"
                    style="height:56px;width:56px;object-fit:contain;display:block;">
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
            <p class="subtitle">Masuk untuk mengelola acara pernikahan</p>

            <x-flash-toast :error="$errors->first()" />

            <div class="tabs" role="tablist">
                <button type="button" class="tab-btn" role="tab" @click="tab = 'login'"
                    :aria-selected="tab === 'login'">Masuk</button>
                <button type="button" class="tab-btn" role="tab" @click="tab = 'register'"
                    :aria-selected="tab === 'register'">Buat Akun</button>
            </div>

            {{-- ========== TAB MASUK ========== --}}
            <div x-show="tab === 'login'" x-cloak>
                <form method="POST" action="{{ route('login') }}">
                    @csrf
                    <input type="hidden" name="_form" value="login">
                    <div class="form-group">
                        <label for="email">Email</label>
                        <div class="input-wrapper">
                            <i class="fa-solid fa-envelope"></i>
                            <input type="email" id="email" name="email" value="{{ old('email') }}"
                                placeholder="you@email.com" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="password">Password</label>
                        <div class="input-wrapper">
                            <i class="fa-solid fa-lock"></i>
                            <input type="password" id="password" name="password" placeholder="Min. 8 karakter"
                                required>
                        </div>
                    </div>
                    <div class="form-group" style="display:flex; align-items:center; gap:8px;">
                        <input type="checkbox" name="remember" id="remember"
                            style="accent-color:#D8A7B1; width:16px; height:16px;">
                        <label for="remember"
                            style="margin:0; text-transform:none; letter-spacing:0; font-size:13px;">
                            Ingat saya</label>
                    </div>
                    <button type="submit" class="btn-primary">
                        <i class="fa-solid fa-arrow-right-to-bracket" style="margin-right: 8px;"></i> Masuk
                    </button>
                </form>
            </div>

            {{-- ========== TAB BUAT AKUN ========== --}}
            <div x-show="tab === 'register'" x-cloak>
                <form method="POST" action="{{ route('register') }}">
                    @csrf
                    <input type="hidden" name="_form" value="register">
                    <div class="form-group">
                        <label for="reg_name">Nama</label>
                        <div class="input-wrapper">
                            <i class="fa-solid fa-user"></i>
                            <input type="text" id="reg_name" name="name" value="{{ old('name') }}"
                                placeholder="Nama kamu" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="reg_email">Email</label>
                        <div class="input-wrapper">
                            <i class="fa-solid fa-envelope"></i>
                            <input type="email" id="reg_email" name="email" value="{{ old('email') }}"
                                placeholder="you@email.com" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="reg_password">Password</label>
                        <div class="input-wrapper">
                            <i class="fa-solid fa-lock"></i>
                            <input type="password" id="reg_password" name="password" placeholder="Min. 8 karakter"
                                required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="reg_password_confirmation">Konfirmasi Password</label>
                        <div class="input-wrapper">
                            <i class="fa-solid fa-lock"></i>
                            <input type="password" id="reg_password_confirmation" name="password_confirmation"
                                placeholder="Ulangi password" required>
                        </div>
                    </div>
                    <button type="submit" class="btn-primary">
                        <i class="fa-solid fa-user-plus" style="margin-right: 8px;"></i> Buat Akun
                    </button>
                </form>
            </div>

            <div class="divider">atau</div>

            <a href="{{ route('google.redirect') }}" class="btn-google" style="text-decoration:none;"
                data-turbo="false">
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
        </div>
    </main>

    <script>
        sessionStorage.removeItem('sidebarScrollPos');
        sessionStorage.removeItem('adminRestore');
    </script>
</body>

</html>
