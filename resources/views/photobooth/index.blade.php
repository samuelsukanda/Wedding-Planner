@extends('layouts.app')

@section('title', 'Kreatif - Photobooth')

@section('content')
    <div class="mx-auto max-w-5xl space-y-6">
        <div class="relative overflow-hidden rounded-3xl bg-[#3F4A3B] px-6 py-8 text-white shadow-lg md:px-10 md:py-10">
            <div class="pointer-events-none absolute -right-20 -top-24 h-64 w-64 rounded-full bg-[#D8A7B1]/30 blur-3xl"></div>
            <div class="relative max-w-2xl">
                <span class="inline-flex items-center gap-2 rounded-full border border-white/20 bg-white/10 px-3 py-1 text-[11px] font-bold uppercase tracking-wider text-[#F8E4E8]">
                    <i class="fa-solid fa-camera-retro" aria-hidden="true"></i> Add-on Photobooth
                </span>
                <h1 class="mt-4 font-serif-title text-3xl font-bold md:text-4xl text-white">Buat kenangan yang bisa dibawa pulang.</h1>
                <p class="mt-3 max-w-xl text-sm leading-relaxed text-white/95 md:text-base">
                    Kelola photobooth pernikahanmu dari satu tempat. Tamu cukup scan QR universal, ambil foto, lalu semua kenangan terkumpul di galeri event.
                </p>
            </div>
        </div>

        <div class="grid gap-4 md:grid-cols-3">
            <div class="rounded-2xl border border-[#B6ADA3]/40 bg-white p-5 shadow-xs">
                <i class="fa-solid fa-qrcode text-xl text-[#8B3A47]" aria-hidden="true"></i>
                <h2 class="mt-4 font-semibold text-[#2D372E]">QR universal</h2>
                <p class="mt-1 text-sm leading-relaxed text-[#5F6F5B]">Satu QR untuk semua tamu, tanpa login dan tanpa broadcast satu per satu.</p>
            </div>
            <div class="rounded-2xl border border-[#B6ADA3]/40 bg-white p-5 shadow-xs">
                <i class="fa-solid fa-images text-xl text-[#8B3A47]" aria-hidden="true"></i>
                <h2 class="mt-4 font-semibold text-[#2D372E]">Galeri event</h2>
                <p class="mt-1 text-sm leading-relaxed text-[#5F6F5B]">Lihat, unduh, dan hapus semua foto photobooth dari menu Wedding Planner.</p>
            </div>
            <div class="rounded-2xl border border-[#B6ADA3]/40 bg-white p-5 shadow-xs">
                <i class="fa-solid fa-wand-magic-sparkles text-xl text-[#8B3A47]" aria-hidden="true"></i>
                <h2 class="mt-4 font-semibold text-[#2D372E]">Frame personal</h2>
                <p class="mt-1 text-sm leading-relaxed text-[#5F6F5B]">Nama pasangan, tanggal, dan banner event mengikuti pengaturan pernikahanmu.</p>
            </div>
        </div>

        <div class="rounded-2xl border border-[#D8A7B1]/50 bg-[#FDF7F5] p-5 md:px-6 shadow-xs">
            <div>
                <h2 class="font-semibold text-[#2D372E] text-base md:text-lg">Aktifkan Photobooth</h2>
                <p class="mt-1 text-sm text-[#5F6F5B]">Event: {{ $wedding?->bride_name }} &amp; {{ $wedding?->groom_name }}</p>
            </div>
            @if (session('success'))
                <p class="mt-3 rounded-xl bg-green-50 border border-green-200 px-4 py-3 text-sm text-green-800">{{ session('success') }}</p>
            @endif
            @if (session('error'))
                <p class="mt-3 rounded-xl bg-red-50 border border-red-200 px-4 py-3 text-sm text-red-800">{{ session('error') }}</p>
            @endif
            <form method="POST" action="{{ route('photobooth.activate') }}" class="mt-4">
                @csrf
                <button class="inline-flex w-fit items-center gap-2 rounded-full bg-[#3F4A3B] hover:bg-[#2D372E] px-5 py-2.5 text-sm font-semibold text-white transition shadow-xs">
                    <i class="fa-solid fa-bolt" aria-hidden="true"></i> Aktifkan Photobooth
                </button>
                <p class="mt-2 text-[11px] text-[#5F6F5B]">Event dibuat otomatis di Photobooth, lalu QR &amp; galerinya langsung tersambung ke halaman ini.</p>
            </form>

            <details class="mt-5 border-t border-[#D8A7B1]/40 pt-4">
                <summary class="cursor-pointer text-xs font-semibold text-[#5F6F5B]">Sudah punya event photobooth? Hubungkan manual</summary>
                <form method="POST" action="{{ route('photobooth.update') }}" class="mt-4 grid gap-4 md:grid-cols-2">
                @csrf
                @method('PUT')
                <div>
                    <label for="photobooth_event_slug" class="block text-xs font-semibold text-[#5F6F5B] mb-1">Event slug</label>
                    <input id="photobooth_event_slug" name="event_slug" aria-label="Event slug" value="{{ old('event_slug', $wedding?->photobooth_event_slug) }}" placeholder="contoh: sa2027" required class="w-full rounded-xl border border-[#B6ADA3]/50 bg-white px-3 py-2.5 text-sm text-[#2D372E] focus:outline-none focus:ring-2 focus:ring-[#8B3A47]/40 focus:border-[#8B3A47]">
                </div>
                <div>
                    <label for="photobooth_api_key" class="block text-xs font-semibold text-[#5F6F5B] mb-1">API key</label>
                    <input id="photobooth_api_key" name="api_key" aria-label="API key" value="{{ old('api_key') }}" placeholder="X-Api-Key dari Photobooth" required class="w-full rounded-xl border border-[#B6ADA3]/50 bg-white px-3 py-2.5 text-sm text-[#2D372E] focus:outline-none focus:ring-2 focus:ring-[#8B3A47]/40 focus:border-[#8B3A47]">
                </div>
                <div class="md:col-span-2 flex items-center gap-2">
                    <input type="checkbox" id="photobooth_active" name="active" value="1" @checked($wedding?->photobooth_active) class="rounded border-[#B6ADA3]/50 text-[#8B3A47] focus:ring-[#8B3A47]">
                    <label for="photobooth_active" class="text-sm font-medium text-[#2D372E] cursor-pointer">
                        Aktifkan Photobooth untuk event ini
                    </label>
                </div>
                <div>
                    <button type="submit" class="inline-flex w-fit items-center gap-2 rounded-full bg-[#8B3A47] hover:bg-[#722F3A] px-5 py-2.5 text-sm font-semibold text-white transition shadow-xs">
                        <i class="fa-solid fa-link" aria-hidden="true"></i> Kelola Photobooth
                    </button>
                </div>
                </form>
            </details>
        </div>

        @if ($offline)
            <p class="rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
                <i class="fa-solid fa-triangle-exclamation" aria-hidden="true"></i>
                Photobooth sedang tidak dapat dihubungi. Data di bawah memakai data tersimpan terakhir.
            </p>
        @endif

        @if ($wedding?->photobooth_active && $event)
            <div class="grid gap-6 lg:grid-cols-2">
                <section class="rounded-2xl border border-[#B6ADA3]/40 bg-white p-5 shadow-xs">
                    <div class="flex items-center justify-between gap-3">
                        <div>
                            <h2 class="font-semibold text-[#2D372E]">Pengaturan event</h2>
                            <p class="mt-1 text-xs text-[#5F6F5B]">Konten yang tampil di photobooth tamu.</p>
                        </div>
                        <span class="rounded-full bg-[#EBF2EC] text-[#2E4B33] border border-[#A3B7A6]/60 px-3 py-1 text-xs font-semibold">Aktif</span>
                    </div>
                    <form method="POST" action="{{ route('photobooth.settings') }}" class="mt-5 space-y-3">
                        @csrf @method('PUT')
                        <div>
                            <label for="photobooth_title" class="block text-xs font-semibold text-[#5F6F5B] mb-1">Judul Event</label>
                            <input id="photobooth_title" name="title" aria-label="Judul Event" value="{{ $event['title'] ?? '' }}" placeholder="Judul event" required class="w-full rounded-xl border border-[#B6ADA3]/50 bg-white px-3 py-2.5 text-sm text-[#2D372E] focus:outline-none focus:ring-2 focus:ring-[#8B3A47]/40">
                        </div>
                        <div class="grid gap-3 sm:grid-cols-2">
                            <div>
                                <label for="photobooth_groom" class="block text-xs font-semibold text-[#5F6F5B] mb-1">Mempelai Pria</label>
                                <input id="photobooth_groom" name="groom_name" aria-label="Nama Mempelai Pria" value="{{ $event['groom_name'] ?? '' }}" placeholder="Nama mempelai pria" required class="w-full rounded-xl border border-[#B6ADA3]/50 bg-white px-3 py-2.5 text-sm text-[#2D372E] focus:outline-none focus:ring-2 focus:ring-[#8B3A47]/40">
                            </div>
                            <div>
                                <label for="photobooth_bride" class="block text-xs font-semibold text-[#5F6F5B] mb-1">Mempelai Wanita</label>
                                <input id="photobooth_bride" name="bride_name" aria-label="Nama Mempelai Wanita" value="{{ $event['bride_name'] ?? '' }}" placeholder="Nama mempelai wanita" required class="w-full rounded-xl border border-[#B6ADA3]/50 bg-white px-3 py-2.5 text-sm text-[#2D372E] focus:outline-none focus:ring-2 focus:ring-[#8B3A47]/40">
                            </div>
                        </div>
                        <div class="grid gap-3 sm:grid-cols-2">
                            <div>
                                <label for="photobooth_wedding_date" class="block text-xs font-semibold text-[#5F6F5B] mb-1">Tanggal Pernikahan</label>
                                <p id="photobooth_wedding_date" class="rounded-xl border border-[#B6ADA3]/40 bg-[#FAF7F2] px-3 py-2.5 text-sm text-[#2D372E]">
                                    {{ $wedding?->wedding_date?->format('d - m - Y') ?? 'Belum ada tanggal' }}
                                </p>
                                <p class="mt-1 text-[11px] text-[#5F6F5B]">Diambil otomatis dari data pernikahan, tidak diketik manual.</p>
                            </div>
                            <div>
                                <label for="photobooth_gallery_mode" class="block text-xs font-semibold text-[#5F6F5B] mb-1">Mode Galeri</label>
                                <select id="photobooth_gallery_mode" name="gallery_mode" aria-label="Mode Galeri" class="w-full rounded-xl border border-[#B6ADA3]/50 bg-white px-3 py-2.5 text-sm text-[#2D372E] focus:outline-none focus:ring-2 focus:ring-[#8B3A47]/40">
                                    <option value="public" @selected(($event['gallery_mode'] ?? 'public') === 'public')>Galeri publik</option>
                                    <option value="private" @selected(($event['gallery_mode'] ?? '') === 'private')>Galeri privat (reserved)</option>
                                </select>
                            </div>
                        </div>
                        <div class="grid gap-3 sm:grid-cols-2">
                            <div>
                                <label for="photobooth_frame_text" class="block text-xs font-semibold text-[#5F6F5B] mb-1">Teks di Frame</label>
                                <input id="photobooth_frame_text" name="frame_text" aria-label="Teks di Frame" value="{{ old('frame_text', $event['frame_text'] ?? '') }}" placeholder="{{ trim(($wedding?->groom_name ?? '') . ' & ' . ($wedding?->bride_name ?? '')) }}" maxlength="40" class="w-full rounded-xl border border-[#B6ADA3]/50 bg-white px-3 py-2.5 text-sm text-[#2D372E] focus:outline-none focus:ring-2 focus:ring-[#8B3A47]/40">
                                <p class="mt-1 text-[11px] text-[#5F6F5B]">Teks yang dicetak di bawah foto. Kosongkan untuk memakai inisial nama.</p>
                            </div>
                            <div>
                                <label for="photobooth_frame_font" class="block text-xs font-semibold text-[#5F6F5B] mb-1">Font Frame</label>
                                <select id="photobooth_frame_font" name="frame_font" aria-label="Font Frame" class="w-full rounded-xl border border-[#B6ADA3]/50 bg-white px-3 py-2.5 text-sm text-[#2D372E] focus:outline-none focus:ring-2 focus:ring-[#8B3A47]/40">
                                    @php $selectedFont = $event['frame_font'] ?? $event['frame']['font'] ?? config('photobooth.default_font'); @endphp
                                    @foreach (config('photobooth.fonts') as $group => $fonts)
                                        <optgroup label="{{ $group }}">
                                            @foreach ($fonts as $font)
                                                <option value="{{ $font }}" @selected($selectedFont === $font)>{{ $font }}</option>
                                            @endforeach
                                        </optgroup>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <button type="submit" class="rounded-full bg-[#3F4A3B] hover:bg-[#2D372E] px-4 py-2 text-sm font-semibold text-white transition shadow-xs">Simpan pengaturan</button>
                    </form>
                    <form method="POST" action="{{ route('photobooth.banner') }}" enctype="multipart/form-data" class="mt-5 border-t border-[#B6ADA3]/25 pt-4">
                        @csrf
                        <div>
                            <label for="photobooth_banner_file" class="block text-xs font-semibold text-[#5F6F5B] mb-1">Banner homepage</label>
                            <input id="photobooth_banner_file" type="file" name="banner" accept="image/jpeg,image/png,image/webp" required class="mt-1 block w-full text-sm text-[#5F6F5B]">
                        </div>
                        <button type="submit" class="mt-3 rounded-full border border-[#8B3A47] px-4 py-2 text-sm font-semibold text-[#8B3A47] hover:bg-[#8B3A47] hover:text-white transition">Upload banner</button>
                    </form>
                </section>

                <section class="rounded-2xl border border-[#B6ADA3]/40 bg-white p-5 shadow-xs">
                    <div class="flex items-center justify-between gap-3">
                        <div>
                            <h2 class="font-semibold text-[#2D372E]">QR universal</h2>
                            <p class="mt-1 text-xs text-[#5F6F5B]">Cetak atau bagikan link ini ke area acara.</p>
                        </div>
                        <i class="fa-solid fa-qrcode text-2xl text-[#8B3A47]" aria-hidden="true"></i>
                    </div>
                    <div class="mt-5 flex justify-center rounded-xl bg-[#FAF7F2] p-6 border border-[#B6ADA3]/25">
                        <div id="photobooth-qr" data-url="{{ rtrim(config('services.photobooth.url'), '/') . '/e/' . $wedding->photobooth_event_slug }}" class="min-h-40"></div>
                    </div>
                    <p class="mt-3 break-all text-center text-xs text-[#5F6F5B] font-mono">{{ rtrim(config('services.photobooth.url'), '/') . '/e/' . $wedding->photobooth_event_slug }}</p>
                    <button type="button" onclick="window.print()" class="mt-4 w-full rounded-full border border-[#B6ADA3]/40 hover:bg-[#FAF7F2] px-4 py-2 text-sm font-semibold text-[#3F4A3B] transition shadow-xs">Cetak QR</button>
                </section>
            </div>

            <section class="rounded-2xl border border-[#B6ADA3]/40 bg-white p-5 shadow-xs">
                <div class="flex flex-col justify-between gap-3 sm:flex-row sm:items-center">
                    <div>
                        <h2 class="font-semibold text-[#2D372E]">Semua foto</h2>
                        <p class="mt-1 text-xs text-[#5F6F5B]">{{ count($photos) }} foto tersimpan di event ini.</p>
                    </div>
                    <a href="{{ route('photobooth.photos.export') }}" class="inline-flex w-fit items-center gap-2 rounded-full bg-[#3F4A3B] hover:bg-[#2D372E] px-4 py-2 text-sm font-semibold text-white transition shadow-xs">
                        <i class="fa-solid fa-file-zipper" aria-hidden="true"></i> Unduh semua ZIP
                    </a>
                </div>
                @if (count($photos))
                    <div class="mt-5 grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-5">
                        @foreach ($photos as $photo)
                            <div class="group relative overflow-hidden rounded-xl bg-[#FAF7F2] border border-[#B6ADA3]/30">
                                <img src="{{ $photo['thumbnail_url'] ?: $photo['image_url'] }}" alt="Foto photobooth" loading="lazy" class="aspect-square w-full object-cover">
                                <form method="POST" action="{{ route('photobooth.photos.destroy', $photo['id']) }}" onsubmit="return confirm('Hapus foto ini?')" class="absolute right-2 top-2 opacity-0 transition group-hover:opacity-100">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="flex h-8 w-8 items-center justify-center rounded-full bg-white/90 text-red-600 shadow hover:bg-white" aria-label="Hapus foto"><i class="fa-solid fa-trash" aria-hidden="true"></i></button>
                                </form>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="mt-5 rounded-xl bg-[#FAF7F2] border border-[#B6ADA3]/25 p-6 text-center text-sm text-[#5F6F5B]">Belum ada foto dari tamu.</p>
                @endif
            </section>
            <script src="https://cdn.jsdelivr.net/npm/qrcodejs@1.0.0/qrcode.min.js"></script>
            <script>new QRCode(document.getElementById('photobooth-qr'), { text: document.getElementById('photobooth-qr').dataset.url, width: 180, height: 180, colorDark: '#3F4A3B', colorLight: '#ffffff' });</script>
        @endif
    </div>
@endsection
