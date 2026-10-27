@extends('layouts.app')

@section('title', 'Kreatif - Photobooth')

@section('content')
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
                            'text' => old('frame_text', $event['frame_text'] ?? $event['frame']['text'] ?? trim($wedding->groom_name . ' & ' . $wedding->bride_name)),
                            'font' => $event['frame_font'] ?? $event['frame']['font'] ?? config('photobooth.default_font'),
                            'date' => $wedding?->wedding_date?->format('d - m - Y') ?? 'Belum ada tanggal',
                        ];
                    @endphp
                    {{-- State awal preview di variable global, bukan di dalam atribut HTML,
                        supaya nama yang memuat tanda kutip tetap aman. --}}
                    <script>
                        window.__framePreviewInit = @js($framePreview);
                        window.framePreview = function (init) {
                            return {
                                text: init.text,
                                font: init.font,
                                // Muat stylesheet Google Fonts untuk font frame aktif,
                                // idempoten supaya hydrate ulang Turbo tidak menumpuk tag.
                                loadFont: function (f) {
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
                        {{-- shortcut: backdrop gradient mewakili foto tamu; bingkai asli digambar sisi Photobooth,
                            upgrade jika API sudah menyediakan asset frame overlay. --}}
                        <div class="mt-5">
                            <h3 class="text-xs font-semibold text-[#5F6F5B]">Preview Bingkai</h3>
                            <div id="frame-preview" class="mx-auto mt-2 max-w-[240px]"
                                aria-label="Preview bingkai photobooth">
                                <div
                                    class="relative aspect-[3/4] overflow-hidden rounded-xl border border-[#B6ADA3]/40 bg-gradient-to-br from-[#EDE4DA] via-[#D8A7B1] to-[#B6ADA3]">
                                    <p x-text="text" :style="`font-family:'${font}', cursive`"
                                        style="font-family:'{{ $framePreview['font'] }}', cursive"
                                        class="absolute inset-x-3 bottom-11 break-words text-center text-2xl leading-tight text-white [text-shadow:0_1px_4px_rgba(0,0,0,0.35)]">
                                        {{ $framePreview['text'] }}</p>
                                    <p
                                        class="absolute inset-x-0 bottom-3.5 text-center text-[11px] font-semibold uppercase tracking-widest text-white/90 [text-shadow:0_1px_3px_rgba(0,0,0,0.35)]">
                                        {{ $framePreview['date'] }}</p>
                                </div>
                                <p class="mt-2 text-center text-[11px] text-[#5F6F5B]">Nama &amp; tanggal mengikuti data
                                    pernikahan; teks dan font di bawah langsung memperbarui preview ini.</p>
                            </div>
                        </div>
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
                                    value="{{ old('frame_text', $event['frame_text'] ?? '') }}"
                                    x-model="text"
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
                        <button type="submit" class="btn-primary-rose rounded-xl px-4 py-2 text-sm font-semibold">Simpan
                            pengaturan</button>
                    </form>
                    </div>
                    <form method="POST" action="{{ route('photobooth.banner') }}" enctype="multipart/form-data"
                        class="mt-5 border-t border-[#B6ADA3]/25 pt-4">
                        @csrf
                        <div>
                            <label for="photobooth_banner_file"
                                class="block text-xs font-semibold text-[#5F6F5B] mb-1">Banner homepage</label>
                            <input id="photobooth_banner_file" type="file" name="banner"
                                aria-label="Banner homepage" accept="image/jpeg,image/png,image/webp" required
                                class="mt-1 block w-full text-sm text-[#5F6F5B]">
                        </div>
                        <button type="submit"
                            class="mt-3 rounded-full border border-[#8B3A47] px-4 py-2 text-sm font-semibold text-[#8B3A47] hover:bg-[#8B3A47] hover:text-white transition">Upload
                            banner</button>
                    </form>
                </section>

                <section class="rounded-2xl border border-[#B6ADA3]/35 bg-white p-5 shadow-xs">
                    <div class="flex items-center justify-between gap-3">
                        <div>
                            <h2 class="font-semibold text-[#2D372E]">QR universal</h2>
                            <p class="mt-1 text-xs text-[#5F6F5B]">Cetak atau bagikan link ini ke area acara.</p>
                        </div>
                        <i class="fa-solid fa-qrcode text-2xl text-[#8B3A47]" aria-hidden="true"></i>
                    </div>
                    <div class="mt-5 flex justify-center rounded-xl bg-[#FAF7F2] p-6 border border-[#B6ADA3]/25">
                        <div id="photobooth-qr"
                            data-url="{{ rtrim(config('services.photobooth.url'), '/') . '/e/' . $wedding->photobooth_event_slug }}"
                            class="min-h-40"></div>
                    </div>
                    <p class="mt-3 break-all text-center text-xs text-[#5F6F5B] font-mono">
                        {{ rtrim(config('services.photobooth.url'), '/') . '/e/' . $wedding->photobooth_event_slug }}</p>
                    <button type="button" onclick="window.print()"
                        class="mt-4 w-full rounded-full border border-[#B6ADA3]/40 hover:bg-[#FAF7F2] px-4 py-2 text-sm font-semibold text-[#3F4A3B] transition shadow-xs">Cetak
                        QR</button>
                </section>
            </div>

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
                    <a href="{{ route('photobooth.photos.export') }}"
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
                                <form id="del-photo-{{ $photo['id'] }}"
                                    method="POST" action="{{ route('photobooth.photos.destroy', $photo['id']) }}"
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
                new QRCode(document.getElementById('photobooth-qr'), {
                    text: document.getElementById('photobooth-qr').dataset.url,
                    width: 180,
                    height: 180,
                    colorDark: '#3F4A3B',
                    colorLight: '#ffffff'
                });
            </script>
        @endif
    </div>
@endsection
