@extends('layouts.app')

@section('title', 'Kreatif - Photobooth')

@section('content')
    @php
        // Cache-busting: versi berubah otomatis tiap file qr-card.jpg diedit,
        // jadi browser tidak menyajikan gambar lama.
        $qrCardUrl = asset('img/qr-card.jpg') . '?v=' . filemtime(public_path('img/qr-card.jpg'));
    @endphp
    <style>
        /* ── Screen preview kartu QR: satu gambar template, QR ditimpa ──
                                                                                           Palet Nupsera: bg #FBF9F5, border luar #383431, bingkai #C6A478,
                                                                                           sudut #C88D68, aksen gelap #8C4635, teks/QR #2B2523. */
        .qr-card-preview {
            position: relative;
            width: 100%;
            max-width: 540px;
            aspect-ratio: 90 / 55;
            margin: 0 auto;
            border: 1.5px solid #383431;
            outline: 1px solid #C6A478;
            outline-offset: 3px;
            background: #FBF9F5 url('{{ $qrCardUrl }}') center / 100% 100% no-repeat;
            border-radius: 4px;
            overflow: hidden;
            box-shadow: 0 2px 16px rgba(43, 37, 35, .12);
        }

        /* Panel off-white hanya menutup QR bawaan template. */
        .qr-card-preview-overlay {
            position: absolute;
            left: 65.9%;
            top: 28.5%;
            width: 25%;
            aspect-ratio: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 0;
            padding: 0;
            background: #FBF9F5;
            border: 0;
            border-radius: 0;
            box-sizing: border-box;
            box-shadow: 0 1px 6px rgba(43, 37, 35, .25);
        }

        .qr-card-preview-frame {
            position: relative;
            width: 100%;
            aspect-ratio: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #FBF9F5;
        }

        #photobooth-qr canvas,
        #photobooth-qr img {
            width: 100% !important;
            height: 100% !important;
            display: block;
        }

        /* ── Print: 90×55mm, 1 halaman ── */
        @media print {
            @page {
                size: 90mm 55mm;
                margin: 0;
            }

            html,
            body {
                width: 90mm !important;
                height: 55mm !important;
                max-height: 55mm !important;
                margin: 0 !important;
                padding: 0 !important;
                overflow: hidden !important;
            }

            body * {
                visibility: hidden !important;
            }

            #photobooth-qr-print,
            #photobooth-qr-print * {
                visibility: visible !important;
            }

            .photobooth-qr-print-button,
            .photobooth-qr-download-button,
            .photobooth-qr-copy-button,
            .photobooth-qr-refresh-button,
            .photobooth-qr-card-download-button,
            .photobooth-qr-copy-status,
            .qr-card-actions,
            .qr-print-url {
                display: none !important;
            }

            #photobooth-qr-print {
                position: fixed;
                inset: 0;
                width: 90mm;
                height: 55mm;
                padding: 0;
                margin: 0;
                background: #FBF9F5 !important;
                border-radius: 0 !important;
                box-shadow: none !important;
                overflow: hidden !important;
                page-break-inside: avoid !important;
                break-inside: avoid !important;
            }

            /* Kartu 90×55mm: satu gambar template + QR ditimpa */
            #photobooth-qr-print .qr-card-preview {
                position: relative;
                width: 90mm;
                height: 55mm;
                max-width: none;
                aspect-ratio: auto;
                border: 0;
                outline: 0;
                background: #FBF9F5 url('{{ $qrCardUrl }}') center / 100% 100% no-repeat;
                border-radius: 0;
                box-shadow: none;
                overflow: hidden;
                box-sizing: border-box;
                margin: 0;
            }

            /* Bingkai luar dicetak di dalam area kartu; outline luar akan terpotong. */
            #photobooth-qr-print .qr-card-preview::before {
                content: '';
                position: absolute;
                inset: 1mm;
                z-index: 3;
                border: 0.7pt solid #383431;
                outline: 0.5pt solid #C6A478;
                outline-offset: 1pt;
                pointer-events: none;
            }

            /* Panel off-white menutup QR bawaan template */
            #photobooth-qr-print .qr-card-preview-overlay {
                position: absolute;
                left: 65.9%;
                top: 28.8%;
                width: 22.5mm;
                aspect-ratio: 1;
                display: flex;
                flex-direction: column;
                align-items: center;
                justify-content: center;
                gap: 0;
                padding: 0;
                background: #FBF9F5;
                border: 0;
                border-radius: 0;
                box-sizing: border-box;
                box-shadow: none;
            }

            /* Frame QR isi penuh panel */
            #photobooth-qr-print .qr-card-preview-frame {
                position: relative;
                width: 100%;
                aspect-ratio: 1;
                display: flex;
                align-items: center;
                justify-content: center;
                background: #FBF9F5;
            }

            /* QR canvas: warna #2B2523, isi area */
            #photobooth-qr canvas,
            #photobooth-qr img {
                width: 100% !important;
                height: 100% !important;
            }
        }
    </style>

    <div class="space-y-6">
        <div
            class="flex flex-col gap-4 rounded-2xl border border-[#B6ADA3]/35 bg-white p-5 text-[#5F6F5B] shadow-xs md:flex-row md:items-center md:justify-between">
            <div>
                <h1 class="font-serif-title text-xl font-bold text-[#5F6F5B] md:text-2xl">Photobooth Digital</h1>
                <p class="mt-1 text-xs text-[#5F6F5B]">Kelola QR, frame, dan galeri foto tamu dari satu tempat.</p>
            </div>
            <span
                class="inline-flex w-fit items-center gap-1.5 rounded-full border border-[#D8A7B1]/50 bg-[#D8A7B1]/10 px-3 py-1.5 text-[11px] font-bold text-[#8B3A47]">
                <i class="fa-solid fa-camera-retro" aria-hidden="true"></i> ADD-ON
            </span>
        </div>

        <div class="grid gap-4 md:grid-cols-3">
            <div class="rounded-2xl border border-[#B6ADA3]/35 bg-white p-5 text-[#5F6F5B] shadow-xs">
                <div
                    class="flex h-10 w-10 items-center justify-center rounded-xl border border-[#D8A7B1]/40 bg-[#D8A7B1]/15 text-[#8B3A47]">
                    <i class="fa-solid fa-qrcode text-lg" aria-hidden="true"></i>
                </div>
                <h2 class="mt-4 font-semibold text-[#2D372E]">QR Code</h2>
                <p class="mt-1 text-sm leading-relaxed text-[#5F6F5B]">Satu QR untuk semua tamu, tanpa login dan tanpa
                    broadcast satu per satu.</p>
            </div>
            <div class="rounded-2xl border border-[#B6ADA3]/35 bg-white p-5 text-[#5F6F5B] shadow-xs">
                <div
                    class="flex h-10 w-10 items-center justify-center rounded-xl border border-[#D8A7B1]/40 bg-[#D8A7B1]/15 text-[#8B3A47]">
                    <i class="fa-solid fa-images text-lg" aria-hidden="true"></i>
                </div>
                <h2 class="mt-4 font-semibold text-[#2D372E]">Galeri event</h2>
                <p class="mt-1 text-sm leading-relaxed text-[#5F6F5B]">Lihat, unduh, dan hapus semua foto photobooth dari
                    menu ini.</p>
            </div>
            <div class="rounded-2xl border border-[#B6ADA3]/35 bg-white p-5 text-[#5F6F5B] shadow-xs">
                <div
                    class="flex h-10 w-10 items-center justify-center rounded-xl border border-[#D8A7B1]/40 bg-[#D8A7B1]/15 text-[#8B3A47]">
                    <i class="fa-solid fa-wand-magic-sparkles text-lg" aria-hidden="true"></i>
                </div>
                <h2 class="mt-4 font-semibold text-[#2D372E]">Frame personal</h2>
                <p class="mt-1 text-sm leading-relaxed text-[#5F6F5B]">Nama pasangan, tanggal, dan banner event mengikuti
                    pengaturan pernikahanmu.</p>
            </div>
        </div>

        <div class="rounded-2xl border border-[#B6ADA3]/35 bg-white p-5 shadow-xs md:p-6">
            <div>
                <h2 class="font-semibold text-[#2D372E] text-base md:text-lg">Aktifkan Photobooth</h2>
                <p class="mt-1 text-sm text-[#5F6F5B]">Event: {{ $wedding?->groom_name }} &amp; {{ $wedding?->bride_name }}
                </p>
            </div>
            @if (session('error'))
                <p class="mt-3 rounded-xl bg-red-50 border border-red-200 px-4 py-3 text-sm text-red-800">
                    {{ session('error') }}</p>
            @endif
            <form method="POST" action="{{ route('photobooth.activate') }}" class="mt-4">
                @csrf
                <button
                    class="btn-primary-rose inline-flex w-fit items-center gap-2 rounded-xl px-5 py-2.5 text-sm font-semibold">
                    <i class="fa-solid fa-bolt" aria-hidden="true"></i> Aktifkan Photobooth
                </button>
                <p class="mt-2 text-[11px] text-[#5F6F5B]">Event dibuat otomatis di Photobooth, lalu QR &amp; galerinya
                    langsung tersambung ke halaman ini.</p>
            </form>
        </div>

        @if ($offline)
            <p class="rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
                <i class="fa-solid fa-triangle-exclamation" aria-hidden="true"></i>
                Koneksi ke Photobooth tidak dapat dihubungi. Menampilkan data tersimpan terakhir.
            </p>
        @endif

        @if ($wedding?->photobooth_active && $event)
            <div class="grid gap-6 lg:grid-cols-2">
                {{-- Pengaturan Acara --}}
                <section class="rounded-2xl border border-[#B6ADA3]/35 bg-white p-5 shadow-xs">
                    <div class="flex items-center justify-between gap-3">
                        <div>
                            <h2 class="font-semibold text-[#2D372E]">Pengaturan Acara</h2>
                            <p class="mt-1 text-xs text-[#5F6F5B]">Kelola konten yang akan ditampilkan pada layar photobooth
                                pengunjung.</p>
                        </div>
                        <span
                            class="rounded-full bg-[#EBF2EC] text-[#2E4B33] border border-[#A3B7A6]/60 px-3 py-1 text-xs font-semibold">Aktif</span>
                    </div>
                    @php
                        $framePreview = [
                            'text' => old(
                                'frame_text',
                                $event['frame_text'] ??
                                    ($event['frame']['text'] ??
                                        trim($wedding->groom_name . ' & ' . $wedding->bride_name)),
                            ),
                            'font' =>
                                $event['frame_font'] ?? ($event['frame']['font'] ?? config('photobooth.default_font')),
                            'date' => $wedding?->wedding_date?->format('d - m - Y') ?? 'Belum ada tanggal',
                        ];
                    @endphp
                    <script>
                        window.__framePreviewInit = @js($framePreview);
                        window.framePreview = function(init) {
                            return {
                                text: init.text,
                                font: init.font,
                                showPreview: false,
                                loadFont: function(f) {
                                    if (!f) return;
                                    var id = 'frame-font-' + f.replace(/[^a-zA-Z0-9_-]/g, '_');
                                    if (document.getElementById(id)) return;
                                    var link = document.createElement('link');
                                    link.id = id;
                                    link.rel = 'stylesheet';
                                    link.href = 'https://fonts.googleapis.com/css2?family=' +
                                        encodeURIComponent(f).replace(/%20/g, '+') + '&display=swap';
                                    document.head.appendChild(link);
                                },
                            };
                        };
                    </script>
                    <div x-data="framePreview(window.__framePreviewInit)" x-effect="loadFont(font)">
                        <form method="POST" action="{{ route('photobooth.settings') }}" class="mt-5 space-y-3">
                            @csrf @method('PUT')
                            <div>
                                <label for="photobooth_title" class="block text-xs font-semibold text-[#5F6F5B] mb-1">Judul
                                    Event</label>
                                <input id="photobooth_title" name="title" aria-label="Judul Event"
                                    value="{{ $event['title'] ?? '' }}" placeholder="Judul event" required
                                    class="w-full rounded-xl border border-[#B6ADA3]/50 bg-white px-3 py-2.5 text-sm text-[#2D372E] focus:outline-none focus:ring-2 focus:ring-[#8B3A47]/40">
                            </div>
                            <div class="grid gap-3 sm:grid-cols-2">
                                <div>
                                    <label for="photobooth_groom"
                                        class="block text-xs font-semibold text-[#5F6F5B] mb-1">Mempelai Pria</label>
                                    <input id="photobooth_groom" name="groom_name" aria-label="Nama Mempelai Pria"
                                        value="{{ $event['groom_name'] ?? '' }}" placeholder="Nama mempelai pria" required
                                        class="w-full rounded-xl border border-[#B6ADA3]/50 bg-white px-3 py-2.5 text-sm text-[#2D372E] focus:outline-none focus:ring-2 focus:ring-[#8B3A47]/40">
                                </div>
                                <div>
                                    <label for="photobooth_bride"
                                        class="block text-xs font-semibold text-[#5F6F5B] mb-1">Mempelai Wanita</label>
                                    <input id="photobooth_bride" name="bride_name" aria-label="Nama Mempelai Wanita"
                                        value="{{ $event['bride_name'] ?? '' }}" placeholder="Nama mempelai wanita" required
                                        class="w-full rounded-xl border border-[#B6ADA3]/50 bg-white px-3 py-2.5 text-sm text-[#2D372E] focus:outline-none focus:ring-2 focus:ring-[#8B3A47]/40">
                                </div>
                            </div>
                            <div class="grid gap-3 sm:grid-cols-2">
                                <div>
                                    <label for="photobooth_wedding_date"
                                        class="block text-xs font-semibold text-[#5F6F5B] mb-1">Tanggal Pernikahan</label>
                                    <p id="photobooth_wedding_date"
                                        class="rounded-xl border border-[#B6ADA3]/40 bg-[#FAF7F2] px-3 py-2.5 text-sm text-[#2D372E]">
                                        {{ $wedding?->wedding_date?->format('d - m - Y') ?? 'Belum ada tanggal' }}
                                    </p>
                                </div>
                            </div>
                            <div class="grid gap-3 sm:grid-cols-2">
                                <div>
                                    <label for="photobooth_frame_text"
                                        class="block text-xs font-semibold text-[#5F6F5B] mb-1">Teks di Frame</label>
                                    <input id="photobooth_frame_text" name="frame_text" aria-label="Teks di Frame"
                                        value="{{ old('frame_text', $event['frame_text'] ?? '') }}" x-model="text"
                                        placeholder="{{ trim(($wedding?->groom_name ?? '') . ' & ' . ($wedding?->bride_name ?? '')) }}"
                                        maxlength="40"
                                        class="w-full rounded-xl border border-[#B6ADA3]/50 bg-white px-3 py-2.5 text-sm text-[#2D372E] focus:outline-none focus:ring-2 focus:ring-[#8B3A47]/40">
                                </div>
                                <div>
                                    <label for="photobooth_frame_font"
                                        class="block text-xs font-semibold text-[#5F6F5B] mb-1">Font Frame</label>
                                    <select id="photobooth_frame_font" name="frame_font" aria-label="Font Frame"
                                        x-model="font"
                                        class="w-full rounded-xl border border-[#B6ADA3]/50 bg-white px-3 py-2.5 text-sm text-[#2D372E] focus:outline-none focus:ring-2 focus:ring-[#8B3A47]/40">
                                        @foreach (config('photobooth.fonts') as $group => $fonts)
                                            <optgroup label="{{ $group }}">
                                                @foreach ($fonts as $font)
                                                    <option value="{{ $font }}" @selected($framePreview['font'] === $font)>
                                                        {{ $font }}</option>
                                                @endforeach
                                            </optgroup>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="flex flex-wrap items-center gap-2">
                                <button type="submit"
                                    class="btn-primary-rose rounded-xl px-4 py-2 text-sm font-semibold">Simpan
                                    pengaturan</button>
                                <button type="button" @click="showPreview = !showPreview"
                                    class="inline-flex items-center gap-2 rounded-xl border border-[#8B3A47] px-4 py-2 text-sm font-semibold text-[#8B3A47] transition hover:bg-[#8B3A47] hover:text-white">
                                    <i class="fa-solid fa-eye" aria-hidden="true"></i> Lihat Preview
                                </button>
                            </div>
                        </form>

                        <div x-show="showPreview" x-collapse x-cloak class="mt-5">
                            <h3 class="text-xs font-semibold text-[#5F6F5B]">Preview Bingkai</h3>
                            <div id="frame-preview" class="mx-auto mt-2 max-w-[240px]"
                                aria-label="Preview bingkai photobooth">
                                <div
                                    class="relative aspect-[3/4] rounded-lg bg-white p-3 shadow-md ring-1 ring-[#B6ADA3]/30">
                                    <div class="flex h-full flex-col gap-2.5">
                                        <div
                                            class="flex flex-1 items-center justify-center rounded-sm bg-gradient-to-br from-[#EDE4DA] via-[#D8A7B1]/50 to-[#B6ADA3]/50">
                                            <i class="fa-solid fa-image text-2xl text-white/90" aria-hidden="true"></i>
                                        </div>
                                        <div class="shrink-0 pb-1 text-center">
                                            <p x-text="text" :style="`font-family:'${font}', cursive`"
                                                style="font-family:'{{ $framePreview['font'] }}', cursive"
                                                class="break-words text-xl leading-tight text-[#2D372E]">
                                                {{ $framePreview['text'] }}</p>
                                            <p
                                                class="mt-0.5 text-[10px] font-semibold uppercase tracking-widest text-[#8B8378]">
                                                {{ $framePreview['date'] }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <form method="POST" action="{{ route('photobooth.banner') }}" enctype="multipart/form-data"
                        class="mt-5 border-t border-[#B6ADA3]/25 pt-4" x-data="{
                            previewUrl: '',
                            fileName: '',
                            updatePreview(event) {
                                const file = event.target.files[0];
                                if (this.previewUrl) URL.revokeObjectURL(this.previewUrl);
                                this.previewUrl = file ? URL.createObjectURL(file) : '';
                                this.fileName = file ? file.name : '';
                            },
                            destroy() {
                                if (this.previewUrl) URL.revokeObjectURL(this.previewUrl);
                            },
                        }">
                        @csrf
                        <label for="photobooth_banner_file" class="mb-2 block text-xs font-semibold text-[#5F6F5B]">Banner
                            Homepage</label>
                        <div
                            class="relative overflow-hidden rounded-xl border border-dashed border-[#B6ADA3]/60 bg-[#FAF7F2]/70 transition hover:border-[#D8A7B1] hover:bg-[#FAF7F2] focus-within:border-[#8B3A47] focus-within:ring-2 focus-within:ring-[#8B3A47]/25">
                            <input id="photobooth_banner_file" type="file" name="banner"
                                accept="image/jpeg,image/png,image/webp" required
                                aria-describedby="photobooth_banner_help" x-on:change="updatePreview($event)"
                                class="absolute inset-0 z-10 h-full w-full cursor-pointer opacity-0">
                            <div class="flex min-h-24 items-center gap-4 p-4 sm:p-5" aria-hidden="true">
                                <div x-show="!previewUrl"
                                    class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl border border-[#D8A7B1]/40 bg-white text-[#8B3A47]">
                                    <i class="fa-solid fa-cloud-arrow-up text-lg"></i>
                                </div>
                                <img x-cloak x-show="previewUrl" :src="previewUrl" alt=""
                                    class="h-16 w-20 shrink-0 rounded-lg border border-[#B6ADA3]/30 bg-white object-cover">
                                <div class="min-w-0 flex-1">
                                    <p x-show="!fileName" class="text-sm font-semibold text-[#2D372E]">
                                        Pilih gambar banner</p>
                                    <p x-show="!fileName" class="mt-1 text-xs text-[#5F6F5B]">
                                        Klik area ini untuk memilih file dari perangkat Anda.</p>
                                    <p x-cloak x-show="fileName" class="truncate text-sm font-semibold text-[#2D372E]"
                                        x-text="fileName"></p>
                                    <p x-show="fileName" class="mt-1 text-xs text-[#5F6F5B]">Gambar siap diunggah</p>
                                </div>
                                <span
                                    class="hidden shrink-0 rounded-lg border border-[#B6ADA3]/40 bg-white px-3 py-2 text-xs font-semibold text-[#8B3A47] sm:inline-flex">
                                    Pilih file
                                </span>
                            </div>
                        </div>
                        <p id="photobooth_banner_help" class="mt-2 text-[11px] text-[#5F6F5B]">
                            JPG, PNG, atau WEBP · Maksimal 5 MB
                        </p>
                        <button type="submit"
                            class="btn-primary-rose mt-4 inline-flex items-center gap-2 rounded-xl px-4 py-2.5 text-sm font-semibold">
                            <i class="fa-solid fa-cloud-arrow-up" aria-hidden="true"></i> Upload banner
                        </button>
                    </form>
                </section>

                {{-- Kartu QR cetak --}}
                <section id="photobooth-qr-print" class="rounded-2xl bg-white p-5 shadow-xs">
                    <div class="qr-card-preview">
                        {{-- QR dinamis ditimpa langsung di area QR template qr-card-final.jpg --}}
                        <div class="qr-card-preview-overlay">
                            <div class="qr-card-preview-frame">
                                <div id="photobooth-qr"
                                    data-url="{{ rtrim(config('services.photobooth.url'), '/') . '/e/' . $wedding->photobooth_event_slug }}">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="qr-card-actions mt-5 flex flex-col items-center gap-2">
                        <p class="qr-print-url break-all text-center text-xs text-[#5F6F5B] font-mono">
                            {{ rtrim(config('services.photobooth.url'), '/') . '/e/' . $wedding->photobooth_event_slug }}
                        </p>
                        <div class="mt-1 flex items-center justify-center gap-2">
                            <button type="button"
                                data-copy="{{ rtrim(config('services.photobooth.url'), '/') . '/e/' . $wedding->photobooth_event_slug }}"
                                class="photobooth-qr-copy-button inline-flex items-center gap-2 rounded-full border border-[#B6ADA3]/40 hover:bg-[#FAF7F2] px-4 py-2 text-sm font-semibold text-[#3F4A3B] transition shadow-xs">
                                <i class="fa-solid fa-copy" aria-hidden="true"></i> Salin Link</button>
                            <button type="button" onclick="renderPhotoboothQr()"
                                class="photobooth-qr-refresh-button inline-flex items-center gap-2 rounded-full border border-[#B6ADA3]/40 hover:bg-[#FAF7F2] px-4 py-2 text-sm font-semibold text-[#3F4A3B] transition shadow-xs">
                                <i class="fa-solid fa-rotate" aria-hidden="true"></i> Refresh QR</button>
                            <span class="photobooth-qr-copy-status text-xs text-[#5F6F5B]" aria-live="polite"></span>
                        </div>
                        <div class="mt-2 flex flex-col items-center gap-2 sm:flex-row sm:justify-center">
                            <button type="button" onclick="requestAnimationFrame(() => window.print())"
                                class="photobooth-qr-print-button w-full rounded-full border border-[#B6ADA3]/40 hover:bg-[#FAF7F2] px-4 py-2 text-sm font-semibold text-[#3F4A3B] transition shadow-xs sm:w-auto">Cetak
                                QR</button>
                            <button type="button" onclick="downloadPhotoboothQr()"
                                class="photobooth-qr-download-button w-full rounded-full border border-[#B6ADA3]/40 hover:bg-[#FAF7F2] px-4 py-2 text-sm font-semibold text-[#3F4A3B] transition shadow-xs sm:w-auto">Unduh
                                QR PNG</button>
                            <a href="{{ $qrCardUrl }}" download="qr-card.jpg" role="button"
                                class="photobooth-qr-card-download-button w-full rounded-full border border-[#B6ADA3]/40 hover:bg-[#FAF7F2] px-4 py-2 text-center text-sm font-semibold text-[#3F4A3B] transition shadow-xs sm:w-auto">Unduh
                                QR Card</a>
                        </div>
                    </div>
                </section>
            </div>

            {{-- Galeri foto --}}
            <section class="rounded-2xl border border-[#B6ADA3]/40 bg-white p-5 shadow-xs">
                <div class="flex flex-col justify-between gap-3 sm:flex-row sm:items-center">
                    <div>
                        <h2 class="font-semibold text-[#2D372E]">Semua foto</h2>
                        <p class="mt-1 text-xs text-[#5F6F5B]">{{ count($photos) }} foto tersimpan di event ini.
                            @if (!empty($event['photo_quota']))
                                Sisa kuota: {{ max(0, (int) $event['photo_quota'] - (int) ($event['photos_used'] ?? 0)) }}
                                dari {{ (int) $event['photo_quota'] }} foto.
                            @endif
                        </p>
                    </div>
                    <a href="{{ route('photobooth.photos.export') }}" target="_blank" rel="noopener"
                        download="photobooth.zip"
                        class="btn-primary-rose inline-flex w-fit items-center gap-2 rounded-xl px-4 py-2 text-sm font-semibold">
                        <i class="fa-solid fa-file-zipper" aria-hidden="true"></i> Unduh semua ZIP
                    </a>
                </div>
                @if (count($photos))
                    <div class="mt-5 grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-5">
                        @foreach ($photos as $photo)
                            <div class="group relative overflow-hidden rounded-xl bg-[#FAF7F2] border border-[#B6ADA3]/30">
                                <img src="{{ $photo['thumbnail_url'] ?: $photo['image_url'] }}" alt="Foto photobooth"
                                    loading="lazy" class="aspect-square w-full object-cover">
                                <form id="del-photo-{{ $photo['id'] }}" method="POST"
                                    action="{{ route('photobooth.photos.destroy', $photo['id']) }}"
                                    class="absolute right-2 top-2 opacity-0 transition group-hover:opacity-100">
                                    @csrf @method('DELETE')
                                    <button type="button"
                                        onclick="confirmDelete('del-photo-{{ $photo['id'] }}', 'foto photobooth ini')"
                                        class="flex h-8 w-8 items-center justify-center rounded-full bg-white/90 text-red-600 shadow hover:bg-white"
                                        aria-label="Hapus foto"><i class="fa-solid fa-trash"
                                            aria-hidden="true"></i></button>
                                </form>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p
                        class="mt-5 rounded-xl bg-[#FAF7F2] border border-[#B6ADA3]/25 p-6 text-center text-sm text-[#5F6F5B]">
                        Belum ada foto dari tamu.</p>
                @endif
            </section>
            <script src="https://cdn.jsdelivr.net/npm/qrcodejs@1.0.0/qrcode.min.js"></script>
            <script>
                function renderPhotoboothQr() {
                    var el = document.getElementById('photobooth-qr');
                    if (!el) return;
                    el.innerHTML = '';
                    new QRCode(el, {
                        text: el.dataset.url,
                        width: 180,
                        height: 180,
                        colorDark: '#2B2523',
                        colorLight: '#FBF9F5'
                    });
                }
                renderPhotoboothQr();

                async function downloadPhotoboothQr() {
                    const qrElement = document.getElementById('photobooth-qr');
                    const qrImage = qrElement.querySelector('canvas, img');
                    if (!qrImage) return;
                    if (qrImage instanceof HTMLImageElement && !qrImage.complete) {
                        await new Promise(resolve => {
                            qrImage.addEventListener('load', resolve, {
                                once: true
                            });
                            qrImage.addEventListener('error', resolve, {
                                once: true
                            });
                        });
                    }
                    const canvas = document.createElement('canvas');
                    canvas.width = qrImage instanceof HTMLCanvasElement ? qrImage.width : qrImage.naturalWidth;
                    canvas.height = qrImage instanceof HTMLCanvasElement ? qrImage.height : qrImage.naturalHeight;
                    const context = canvas.getContext('2d');
                    context.drawImage(qrImage, 0, 0);
                    const image = context.getImageData(0, 0, canvas.width, canvas.height);
                    for (let i = 0; i < image.data.length; i += 4) {
                        if (image.data[i] > 245 && image.data[i + 1] > 245 && image.data[i + 2] > 245) {
                            image.data[i + 3] = 0;
                        }
                    }
                    context.putImageData(image, 0, 0);
                    const link = document.createElement('a');
                    link.download = 'qr-code-{{ $wedding->photobooth_event_slug }}.png';
                    link.href = canvas.toDataURL('image/png');
                    link.click();
                }
            </script>
        @endif
    </div>
    @push('scripts')
        <style>
            .copy-check {
                display: inline-block;
                animation: copy-pop 0.4s ease-out;
            }

            @keyframes copy-pop {
                0% {
                    transform: scale(0.4);
                    opacity: 0;
                }

                60% {
                    transform: scale(1.25);
                    opacity: 1;
                }

                100% {
                    transform: scale(1);
                    opacity: 1;
                }
            }

            .copy-success {
                color: #2E4B33;
                border-color: #A3B7A6;
                background-color: #EBF2EC;
            }
        </style>
        <script>
            (function() {
                document.querySelectorAll('[data-copy]').forEach(function(el) {
                    el.addEventListener('click', function() {
                        const text = el.dataset.copy;
                        const done = function(ok) {
                            if (!el.dataset.originalHtml) el.dataset.originalHtml = el.innerHTML;
                            if (ok) {
                                el.innerHTML =
                                    '<i class="fa-solid fa-check copy-check" aria-hidden="true"></i> Tersalin';
                                el.classList.add('copy-success');
                            }
                            const status = document.querySelector('.photobooth-qr-copy-status');
                            if (status) status.textContent = '';
                            window.clearTimeout(el._t);
                            el._t = window.setTimeout(function() {
                                el.innerHTML = el.dataset.originalHtml;
                                el.classList.remove('copy-success');
                            }, 1800);
                        };
                        if (navigator.clipboard && window.isSecureContext) {
                            navigator.clipboard.writeText(text).then(function() {
                                done(true);
                            }, function() {
                                done(false);
                            });
                            return;
                        }
                        const helper = document.createElement('textarea');
                        helper.value = text;
                        helper.setAttribute('readonly', '');
                        helper.style.position = 'fixed';
                        helper.style.opacity = '0';
                        document.body.appendChild(helper);
                        helper.select();
                        let ok = false;
                        try {
                            ok = document.execCommand('copy');
                        } catch (e) {
                            ok = false;
                        }
                        document.body.removeChild(helper);
                        done(ok);
                    });
                });
            })();
        </script>
    @endpush
@endsection
