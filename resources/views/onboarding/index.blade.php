<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Mulai Perjalanan — Wedding Planner</title>
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
            flex-direction: column;
            align-items: center;
            justify-content: center;
            background: #FAF7F2;
            padding: 24px 20px;
        }

        .dots {
            display: flex;
            gap: 8px;
            margin-bottom: 26px;
        }

        .dot {
            width: 9px;
            height: 9px;
            border-radius: 999px;
            background: #E5E0D8;
            transition: all 0.25s;
        }

        .dot.is-done {
            background: #D8A7B1;
        }

        .dot.is-active {
            width: 34px;
            background: #D8A7B1;
        }

        .wizard-card {
            background: #ffffff;
            border-radius: 24px;
            padding: 40px 38px;
            max-width: 520px;
            width: 100%;
            border: 1px solid #e5e0d8;
            box-shadow: 0 8px 32px rgba(95, 111, 91, 0.08);
        }

        .step-emoji {
            font-size: 34px;
            line-height: 1;
            margin-bottom: 14px;
        }

        h1 {
            font-family: 'Outfit', sans-serif;
            font-size: 25px;
            font-weight: 700;
            color: #1F2937;
            margin-bottom: 8px;
            line-height: 1.25;
        }

        .subtitle {
            font-size: 14px;
            color: #6B7280;
            margin-bottom: 24px;
        }

        input[type="text"],
        input[type="date"],
        input[type="number"] {
            width: 100%;
            padding: 14px 16px;
            border: 1.5px solid #d7d2c9;
            border-radius: 12px;
            font-size: 15px;
            font-family: 'Outfit', sans-serif;
            color: #2D372E;
            background: #ffffff;
            transition: all 0.2s;
            outline: none;
        }

        input:focus {
            border-color: #D8A7B1;
            box-shadow: 0 0 0 3px rgba(216, 167, 177, 0.18);
        }

        input::placeholder {
            color: #B6ADA3;
        }

        .summary {
            background: #FAF3F5;
            border-radius: 14px;
            padding: 18px 20px;
            margin-bottom: 22px;
        }

        .summary-row {
            display: flex;
            gap: 12px;
            padding: 6px 0;
            font-size: 14px;
        }

        .summary-label {
            color: #6B7280;
            min-width: 92px;
        }

        .summary-value {
            color: #1F2937;
            font-weight: 600;
        }

        .actions {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
            margin-top: 22px;
        }

        .btn {
            padding: 13px 18px;
            border-radius: 12px;
            font-size: 14px;
            font-weight: 600;
            font-family: 'Outfit', sans-serif;
            cursor: pointer;
            transition: all 0.2s;
            border: none;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .btn-primary {
            background: linear-gradient(135deg, #D8A7B1, #C48D9A);
            color: #ffffff;
        }

        .btn-primary:hover {
            background: linear-gradient(135deg, #C48D9A, #B87C89);
        }

        .btn-primary:disabled {
            background: #E3C8CF;
            cursor: not-allowed;
        }

        .btn-secondary {
            background: #ffffff;
            color: #1F2937;
            border: 1.5px solid #d7d2c9;
        }

        .btn-secondary:hover {
            background: #FAF3F5;
            border-color: #D8A7B1;
        }

        .btn-block {
            grid-column: 1 / -1;
        }

        .error {
            background: #fef2f2;
            border: 1px solid #fecaca;
            border-radius: 10px;
            padding: 10px 14px;
            font-size: 13px;
            color: #dc2626;
            margin-bottom: 18px;
        }
    </style>
</head>

<body>
    <div class="dots" aria-label="Progres">
        @for ($i = 1; $i <= 4; $i++)
            @if ($step >= 5)
                <span class="dot is-done"></span>
            @elseif ($step === $i)
                <span class="dot is-active"></span>
            @elseif ($step > $i)
                <span class="dot is-done"></span>
            @else
                <span class="dot"></span>
            @endif
        @endfor
    </div>

    <div class="wizard-card">
        @if ($errors->any())
            <div class="error">{{ $errors->first() }}</div>
        @endif

        @if ($step === 1)
            <div class="step-emoji">👋</div>
            <h1>Siapa nama kamu?</h1>
            <p class="subtitle">Kami akan menyimpanmu dengan nama ini.</p>
            <form method="POST" action="{{ route('onboarding.store') }}">
                @csrf
                <input type="text" name="user_name" id="user_name" readonly required
                    value="{{ $data['user_name'] ?? auth()->user()->name }}"
                    style="background:#F5F3F0; color:#6B7280;">
                <div class="actions">
                    <button type="submit" class="btn btn-primary btn-block">Lanjut</button>
                </div>
            </form>

        @elseif ($step === 2)
            <div class="step-emoji">💑</div>
            <h1>Siapa nama pasanganmu?</h1>
            <p class="subtitle">Kalian akan merencanakan bersama.</p>
            <form method="POST" action="{{ route('onboarding.store') }}">
                @csrf
                <input type="text" name="partner_name" placeholder="Contoh: Aulia" required autofocus
                    value="{{ $data['partner_name'] ?? '' }}">
                <div class="actions">
                    <button type="submit" name="direction" value="back" class="btn btn-secondary"
                        formnovalidate>Kembali</button>
                    <button type="submit" class="btn btn-primary">Lanjut</button>
                </div>
            </form>

        @elseif ($step === 3)
            <div class="step-emoji">💍</div>
            <h1>Kapan kalian menikah?</h1>
            <p class="subtitle">Kami akan menghitung mundur untuk kalian.</p>
            <form method="POST" action="{{ route('onboarding.store') }}">
                @csrf
                <input type="date" name="wedding_date" required autofocus
                    value="{{ $data['wedding_date'] ?? '' }}">
                <div class="actions">
                    <button type="submit" name="direction" value="back" class="btn btn-secondary"
                        formnovalidate>Kembali</button>
                    <button type="submit" class="btn btn-primary">Lanjut</button>
                </div>
            </form>

        @elseif ($step === 4)
            <div class="step-emoji">💰</div>
            <h1>Berapa target anggaran pernikahan kalian?</h1>
            <p class="subtitle">Bisa diubah kapan saja nanti.</p>
            <form method="POST" action="{{ route('onboarding.store') }}">
                @csrf
                <input type="number" name="total_budget" min="0" step="100000" required autofocus
                    placeholder="Rp 100.000.000" value="{{ $data['total_budget'] ?? '' }}">
                <div class="actions">
                    <button type="submit" name="direction" value="back" class="btn btn-secondary"
                        formnovalidate>Kembali</button>
                    <button type="submit" class="btn btn-primary">Lanjut</button>
                </div>
            </form>

        @else
            <div class="step-emoji">🎉</div>
            <h1>Siap memulai perjalanan!</h1>
            <p class="subtitle">Workspace wedding kalian akan dibuat dengan template default. Semua bisa diedit
                nanti.</p>
            <div class="summary">
                <div class="summary-row">
                    <span class="summary-label">Pasangan</span>
                    <span class="summary-value">{{ $data['user_name'] ?? auth()->user()->name }} &amp;
                        {{ $data['partner_name'] ?? '' }}</span>
                </div>
                <div class="summary-row">
                    <span class="summary-label">Tanggal</span>
                    <span class="summary-value">
                        {{ \Carbon\Carbon::parse($data['wedding_date'])->format('j F Y') }}</span>
                </div>
                <div class="summary-row">
                    <span class="summary-label">Budget</span>
                    <span class="summary-value">Rp {{ number_format((float) ($data['total_budget'] ?? 0), 0, ',', '.') }}
                    </span>
                </div>
            </div>
            <form method="POST" action="{{ route('onboarding.finish') }}">
                @csrf
                <div class="actions">
                    <button type="submit" name="direction" value="back" class="btn btn-secondary"
                        formnovalidate>Kembali</button>
                    <button type="submit" class="btn btn-primary">
                        Mulai Perencanaan <i class="fa-solid fa-circle-notch fa-spin"></i>
                    </button>
                </div>
            </form>
        @endif
    </div>
</body>

</html>