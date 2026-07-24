<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Login — Wedding Planner</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&family=Playfair+Display:ital,wght@0,500;0,600;0,700;1,600&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="shortcut icon" href="{{ asset('img/icon.jpg') }}" type="image/x-icon">
    @vite(['resources/css/app.css'])
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Outfit', sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #FAF7F2;
            padding: 20px;
        }

        .login-card {
            background: #ffffff;
            border-radius: 24px;
            padding: 48px 40px;
            max-width: 420px;
            width: 100%;
            border: 1px solid #e5e0d8;
            box-shadow: 0 8px 32px rgba(95, 111, 91, 0.08);
        }

        .brand-icon {
            width: 64px;
            height: 64px;
            border-radius: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
        }

        h1 {
            font-family: 'Playfair Display', serif;
            font-size: 22px;
            color: #5F6F5B;
            text-align: center;
            margin-bottom: 4px;
        }

        .subtitle {
            text-align: center;
            font-size: 13px;
            color: #B6ADA3;
            margin-bottom: 32px;
        }

        .form-group {
            margin-bottom: 20px;
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
            color: #B6ADA3;
            font-size: 14px;
        }

        input[type="email"],
        input[type="password"] {
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

        .btn-login {
            width: 100%;
            padding: 13px;
            background: linear-gradient(135deg, #D8A7B1, #C48D9A);
            color: #fff;
            border: none;
            border-radius: 12px;
            font-size: 14px;
            font-weight: 700;
            font-family: 'Outfit', sans-serif;
            cursor: pointer;
            transition: all 0.2s;
            margin-top: 8px;
        }

        .btn-login:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 16px rgba(216, 167, 177, 0.35);
        }

        .btn-login:active {
            transform: translateY(0);
        }

        .error {
            background: #fef2f2;
            border: 1px solid #fecaca;
            border-radius: 10px;
            padding: 10px 14px;
            font-size: 13px;
            color: #dc2626;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .error i {
            font-size: 14px;
        }

        .footer-text {
            text-align: center;
            font-size: 12px;
            color: #B6ADA3;
            margin-top: 24px;
            padding-top: 20px;
            border-top: 1px solid #f0ece6;
        }

        .footer-text span {
            color: #D8A7B1;
            font-family: 'Playfair Display', serif;
        }
    </style>
</head>

<body>
    <div class="login-card">
        <div class="brand-icon">
            <i class="fa-solid fa-heart" style="font-size: 28px; color: #D8A7B1;"></i>
        </div>
        <h1>Wedding Planner</h1>
        <p class="subtitle">Masuk untuk mengelola acara pernikahan</p>

        @if ($errors->any())
            <div class="error">
                <i class="fa-solid fa-circle-exclamation"></i>
                {{ $errors->first('email') }}
            </div>
        @endif

        <form method="POST" action="{{ route('login') }}">
            @csrf
            <div class="form-group">
                <label for="email">Email</label>
                <div class="input-wrapper">
                    <i class="fa-solid fa-envelope"></i>
                    <input type="email" id="email" name="email" value="{{ old('email') }}"
                        placeholder="admin@gmail.com" required autofocus>
                </div>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <div class="input-wrapper">
                    <i class="fa-solid fa-lock"></i>
                    <input type="password" id="password" name="password" placeholder="••••••••" required>
                </div>
            </div>
            <div class="form-group" style="display:flex; align-items:center; gap:8px;">
                <input type="checkbox" name="remember" id="remember"
                    style="accent-color:#D8A7B1; width:16px; height:16px;">
                <label for="remember" style="margin:0; text-transform:none; letter-spacing:0; font-size:13px;">Ingat
                    saya</label>
            </div>
            <button type="submit" class="btn-login">
                <i class="fa-solid fa-arrow-right-to-bracket" style="margin-right: 8px;"></i> Masuk
            </button>
        </form>

        <div class="footer-text">
            &copy; {{ date('Y') }} <span>Samuel & Angela</span> Wedding Planner
        </div>
    </div>

    <script>
        sessionStorage.removeItem('sidebarScrollPos');
        sessionStorage.removeItem('adminRestore');
    </script>
</body>

</html>
