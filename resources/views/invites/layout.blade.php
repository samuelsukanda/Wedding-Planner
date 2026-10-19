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
        @yield('card')
    </div>
</body>

</html>