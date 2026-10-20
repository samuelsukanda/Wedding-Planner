<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Undangan') — Wedding Planner</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&family=Playfair+Display:ital,wght@0,500;0,600;0,700;1,600&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="shortcut icon" href="{{ asset('img/nupsera-ori.jpg') }}" type="image/x-icon">
    @vite(['resources/css/app.css'])
    <style>
        /* Meniru halaman Login persis, termasuk perilaku scrollnya:
           body cuma min-height, TIDAK di-mount tinggi penuh dengan
           overflow:hidden - halaman menggulir normal saat form lebih tinggi
           dari layar HP, sama seperti /login. Gaya tanpa @layer sengaja
           supaya aturan ini tetap menang atas utility Tailwind, sama alasan
           dengan catatan di login.blade.php. */
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
            padding: 40px 36px;
            max-width: 440px;
            width: 100%;
            border: 1px solid #e5e0d8;
            box-shadow: 0 8px 32px rgba(95, 111, 91, 0.08);
            margin: auto;
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
            color: #B6ADA3;
            margin-bottom: 26px;
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
            color: #B6ADA3;
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
            box-sizing: border-box;
        }

        input:focus {
            border-color: #D8A7B1;
            background: #ffffff;
            box-shadow: 0 0 0 3px rgba(216, 167, 177, 0.15);
        }

        input::placeholder {
            color: #C4BCB1;
        }

        .error-text {
            display: block;
            margin-top: 6px;
            font-size: 12px;
            color: #dc2626;
        }

        .divider {
            display: flex;
            align-items: center;
            gap: 12px;
            margin: 22px 0 16px;
            color: #B6ADA3;
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
            box-sizing: border-box;
        }

        .btn-google:hover {
            background: #FAF7F2;
            border-color: #D8A7B1;
        }

        .google-note {
            margin-top: 10px;
            font-size: 11px;
            color: #B6ADA3;
            text-align: center;
        }

        .footer-text {
            text-align: center;
            font-size: 12px;
            color: #B6ADA3;
            margin-top: 24px;
            padding-top: 18px;
            border-top: 1px solid #f0ece6;
        }
    </style>
</head>

<body>
    <div class="invite-card">
        @yield('card')
    </div>
</body>

</html>
