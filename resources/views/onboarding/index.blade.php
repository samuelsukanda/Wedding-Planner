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
    {{-- Flatpickr dipakai di langkah tanggal agar konsisten dengan form lain. --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    @vite(['resources/css/app.css'])
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/id.js"></script>
    <style>
        /* Reset global sengaja tidak diulang: preflight Tailwind sudah
           melakukannya, dan CSS tanpa @layer di sini mengalahkan utility
           Tailwind sehingga padding komponen toast hilang. */

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

        /* Lebar tetap di semua langkah. Tanpa ini kartu ikut menyusut mengikuti
           isi tiap langkah, jadi ukurannya terlihat berubah-ubah. */
        .wizard-shell {
            width: 100%;
            max-width: 520px;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .wizard-card {
            background: #ffffff;
            border-radius: 24px;
            padding: 40px 38px;
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

        .field {
            width: 100%;
            padding: 14px 16px;
            border: 1.5px solid #d7d2c9;
            border-radius: 12px;
            font-size: 15px;
            font-family: 'Outfit', sans-serif;
            color: #2D372E;
            background: #ffffff;
            transition: border-color 0.2s, box-shadow 0.2s;
            outline: none;
        }

        .field:focus {
            border-color: #D8A7B1;
            box-shadow: 0 0 0 3px rgba(216, 167, 177, 0.18);
        }

        .field.is-invalid {
            border-color: #D8A7B1;
            background: #FBF2F4;
        }

        .field::placeholder {
            color: #B6ADA3;
        }

        .field[readonly] {
            background: #FAF3F5;
            color: #6B7280;
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

        .btn-primary:hover:not(:disabled) {
            background: linear-gradient(135deg, #C48D9A, #B87C89);
        }

        .btn-primary:disabled {
            background: #E3C8CF;
            color: #ffffff;
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

        [x-cloak] {
            display: none !important;
        }

        @media (prefers-reduced-motion: reduce) {
            .dot { transition: none; }
        }
    </style>
</head>

<body>
    {{-- Semua langkah dirender sekali di DOM. Perpindahan Lanjut/Kembali hanya
         mengubah state Alpine, tidak ada request ke server sama sekali. --}}
    @php
        $saved = json_decode(session('onboarding_draft', ''), true) ?: [];
    @endphp

    <div class="wizard-shell" x-data="wizard()" x-init="restore()" x-cloak>
        <div class="dots" aria-label="Progres">
            <template x-for="i in 4" :key="i">
                <span class="dot" :class="{ 'is-active': step === i, 'is-done': step > i || step >= 5 }"></span>
            </template>
        </div>

        <div class="wizard-card">
            <x-flash-toast :error="$errors->first()" />

            {{-- ========== LANGKAH 1 ========== --}}
            <section x-show="step === 1">
                <div class="step-emoji">👋</div>
                <h1>Siapa nama kamu?</h1>
                <p class="subtitle">Kami akan menyimpanmu dengan nama ini.</p>
                <input type="text" class="field" :class="{ 'is-invalid': errors.user_name }" x-model="form.user_name"
                    readonly aria-label="Nama kamu">
                <div class="actions">
                    <button type="button" class="btn btn-primary btn-block" @click="next(1)">Lanjut</button>
                </div>
            </section>

            {{-- ========== LANGKAH 2 ========== --}}
            <section x-show="step === 2">
                <div class="step-emoji">💑</div>
                <h1>Siapa nama pasanganmu?</h1>
                <p class="subtitle">Kalian akan merencanakan bersama.</p>
                <input type="text" class="field" :class="{ 'is-invalid': errors.partner_name }" x-model="form.partner_name"
                    placeholder="Contoh: Nabila" x-ref="partner" @keydown.enter.prevent="next(2)">
                <div class="actions">
                    <button type="button" class="btn btn-secondary" @click="step = 1">Kembali</button>
                    <button type="button" class="btn btn-primary" @click="next(2)">Lanjut</button>
                </div>
            </section>

            {{-- ========== LANGKAH 3 ========== --}}
            <section x-show="step === 3">
                <div class="step-emoji">💍</div>
                <h1>Kapan kalian menikah?</h1>
                <p class="subtitle">Kami akan menghitung mundur untuk kalian.</p>
                <input type="text" class="field datepicker-input" :class="{ 'is-invalid': errors.wedding_date }"
                    x-model="form.wedding_date" placeholder="DD-MM-YYYY" data-date>
                <div class="actions">
                    <button type="button" class="btn btn-secondary" @click="step = 2">Kembali</button>
                    <button type="button" class="btn btn-primary" @click="next(3)">Lanjut</button>
                </div>
            </section>

            {{-- ========== LANGKAH 4 ========== --}}
            <section x-show="step === 4">
                <div class="step-emoji">💰</div>
                <h1>Berapa target anggaran pernikahan kalian?</h1>
                <p class="subtitle">Bisa diubah kapan saja nanti.</p>
                <input type="text" inputmode="numeric" class="field" :class="{ 'is-invalid': errors.total_budget }"
                    x-model="form.total_budget" placeholder="Rp 100.000.000" @input="formatBudget"
                    @keydown.enter.prevent="next(4)">
                <div class="actions">
                    <button type="button" class="btn btn-secondary" @click="step = 3">Kembali</button>
                    <button type="button" class="btn btn-primary" @click="next(4)">Lanjut</button>
                </div>
            </section>

            {{-- ========== LANGKAH 5 (konfirmasi) ========== --}}
            <section x-show="step === 5">
                <div class="step-emoji">🎉</div>
                <h1>Siap memulai perjalanan!</h1>
                <p class="subtitle">Workspace wedding kalian akan dibuat dengan template default. Semua bisa diedit
                    nanti.</p>
                <div class="summary">
                    <div class="summary-row">
                        <span class="summary-label">Pasangan</span>
                        <span class="summary-value" x-text="`${form.user_name} & ${form.partner_name}`"></span>
                    </div>
                    <div class="summary-row">
                        <span class="summary-label">Tanggal</span>
                        <span class="summary-value" x-text="formattedDate"></span>
                    </div>
                    <div class="summary-row">
                        <span class="summary-label">Budget</span>
                        <span class="summary-value" x-text="form.total_budget"></span>
                    </div>
                </div>

                <form method="POST" action="{{ route('onboarding.finish') }}"
                    onsubmit="sessionStorage.removeItem(STORAGE_KEY)">
                    @csrf
                    <input type="hidden" name="user_name" :value="form.user_name">
                    <input type="hidden" name="partner_name" :value="form.partner_name">
                    <input type="hidden" name="wedding_date" :value="form.wedding_date">
                    <input type="hidden" name="total_budget" :value="numericBudget">
                    <div class="actions">
                        <button type="button" class="btn btn-secondary" @click="step = 4">Kembali</button>
                        <button type="submit" class="btn btn-primary">
                            Mulai Perencanaan <i class="fa-solid fa-circle-notch fa-spin"></i>
                        </button>
                    </div>
                </form>
            </section>
        </div>
    </div>

    <script>
        // Kuncinya per-user: kalau global, draft user sebelumnya akan muncul
        // lagi saat akun lain daftar.
        const STORAGE_KEY = 'weddingPlanner.onboardingDraft.@js(auth()->id())';

        // Toast validasi client-side. Gaya dan timer auto-hilangnya sama
        // dengan komponen <x-flash-toast /> supaya konsisten di semua halaman.
        function showWizardToast(message) {
            let host = document.getElementById('wizard-toast-host');
            if (!host) {
                host = document.createElement('div');
                host.id = 'wizard-toast-host';
                host.className = 'fixed top-4 left-1/2 -translate-x-1/2 z-[100] w-[calc(100%-2rem)] max-w-xl';
                document.body.appendChild(host);
            }

            const toast = document.createElement('div');
            toast.setAttribute('data-toast', '');
            toast.className = 'flex items-start gap-3 rounded-2xl border border-[#D8A7B1]/70 bg-[#FBF2F4] px-4 py-3.5 text-sm text-[#5F6F5B] shadow-lg shadow-[#D8A7B1]/15';
            toast.innerHTML = '<i class="fa-solid fa-circle-exclamation mt-0.5 text-[#D8A7B1]"></i>'
                + '<span class="flex-1"></span>'
                + '<button type="button" class="cursor-pointer text-[#5F6F5B]/70 transition hover:text-[#5F6F5B]"><i class="fa-solid fa-xmark"></i></button>';
            toast.querySelector('span').textContent = message;
            host.appendChild(toast);

            // Fade out dulu, baru dilepas. Keyframes ada di app.css.
            const dismiss = () => {
                if (!toast.isConnected) return;
                toast.style.animation = 'flash-toast-out 260ms ease-in forwards';
                window.setTimeout(() => toast.remove(), 280);
            };

            toast.querySelector('button').addEventListener('click', dismiss);
            window.setTimeout(dismiss, 4000);
        }

        document.addEventListener('alpine:init', () => {
            Alpine.data('wizard', () => ({
                step: 1,
                errors: {},
                form: {
                    user_name: @js(auth()->user()->name),
                    partner_name: '',
                    wedding_date: '',
                    total_budget: '',
                },

                restore() {
                    try {
                        const saved = JSON.parse(sessionStorage.getItem(STORAGE_KEY) || '{}');
                        if (saved.form) Object.assign(this.form, saved.form);
                        if (saved.step) this.step = saved.step;
                    } catch (error) {
                        /* draft rusak, abaikan saja */
                    }
                    this.initDatepicker();
                },

                persist() {
                    sessionStorage.setItem(STORAGE_KEY, JSON.stringify({ step: this.step, form: this.form }));
                },

                initDatepicker() {
                    const el = this.$root.querySelector('[data-date]');
                    if (!el || el._flatpickr || !window.flatpickr) return;
                    el._flatpickr = window.flatpickr(el, {
                        // Nilai tetap Y-m-d agar aman dikirim ke server,
                        // altFormat yang menentukan yang dilihat user.
                        dateFormat: 'Y-m-d',
                        altFormat: 'j F Y',
                        altInput: true,
                        // Tanpa ini flatpickr tetap menulis nama bulan
                        // dalam bahasa Inggris ("October").
                        locale: 'id',
                        allowInput: true,
                        onChange: () => this.persist(),
                    });

                    // altInput adalah input terpisah dari aslinya (aslinya jadi
                    // type=hidden). Penanda ini dipakai agar mudah ditarget.
                    if (el._flatpickr.altInput) {
                        el._flatpickr.altInput.setAttribute('data-date-display', '');
                    }
                },

                // Validasi per langkah di browser supaya tidak ada request sia-sia.
                next(current) {
                    this.errors = {};

                    if (current === 2 && !this.form.partner_name.trim()) {
                        this.errors.partner_name = 'Nama pasangan wajib diisi.';
                    }

                    if (current === 3 && !this.form.wedding_date) {
                        this.errors.wedding_date = 'Tanggal pernikahan wajib diisi.';
                    }

                    if (current === 4) {
                        if (!this.numericBudget) {
                            this.errors.total_budget = 'Target anggaran wajib diisi.';
                        } else if (this.numericBudget < 1) {
                            this.errors.total_budget = 'Target anggaran harus lebih dari 0.';
                        }
                    }

                    if (Object.keys(this.errors).length) {
                        showWizardToast(Object.values(this.errors)[0]);
                        return;
                    }

                    this.step = current + 1;
                    this.persist();
                    this.$nextTick(() => this.initDatepicker());
                },

                get numericBudget() {
                    return Number(String(this.form.total_budget).replace(/\D/g, '')) || 0;
                },

                // Angka diketik jadi beruang; prefiks "Rp" sengaja tidak ikut
                // dihapus supaya tetap terlihat saat user mengetik.
                formatBudget(event) {
                    const digits = String(event.target.value).replace(/\D/g, '');
                    this.form.total_budget = digits ? `Rp ${Number(digits).toLocaleString('id-ID')}` : '';
                },

                get formattedDate() {
                    if (!this.form.wedding_date) return '';
                    const date = new Date(this.form.wedding_date);
                    return Number.isNaN(date.getTime())
                        ? this.form.wedding_date
                        : date.toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' });
                },
            }));
        });
    </script>
</body>

</html>