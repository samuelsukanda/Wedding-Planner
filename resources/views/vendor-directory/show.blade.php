@extends('layouts.app')

@section('title', 'Detail Vendor - Cari Vendor')

@section('content')
    <div class="space-y-6">
        {{-- Header --}}
        <div
            class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-white p-5 rounded-2xl border border-[#B6ADA3]/35 shadow-xs">
            <div class="min-w-0">
                <h1 class="text-xl md:text-2xl font-bold font-serif-title text-[#5F6F5B] truncate">{{ $vendor->name }}</h1>
                <div class="flex flex-wrap items-center gap-3 mt-1.5 text-xs text-[#5F6F5B]">
                    <span class="px-2 py-0.5 rounded border border-[#B6ADA3]/40 bg-[#FAF7F2] font-semibold text-[#5F6F5B]">
                        {{ $vendor->category }}
                    </span>
                    @if ($vendor->city)
                        <span class="font-medium"><i class="fa-solid fa-location-dot text-[10px] text-[#8B3A47]" aria-hidden="true"></i> {{ $vendor->city }}</span>
                    @endif
                    @if ($vendor->rating > 0)
                        <span class="font-semibold text-[#8B3A47]">
                            <i class="fa-solid fa-star text-[10px]" aria-hidden="true"></i>
                            {{ number_format($vendor->rating, 1) }}
                            ({{ $vendor->review_count }} review)
                        </span>
                    @endif
                </div>
            </div>

            {{-- Aksi di kanan: Simpan lalu Kembali, gaya & urutan sama dengan
                 halaman Kirim Undangan WA (tombol aksi dulu, Kembali terakhir). --}}
            <div class="flex flex-col sm:flex-row gap-2 w-full md:w-auto">
                @if ($alreadySaved)
                    <a href="{{ route('vendors.index') }}"
                        class="min-h-[44px] px-4 py-2.5 rounded-xl bg-white text-[#5F6F5B] hover:bg-[#FAF7F2] border border-[#B6ADA3]/40 text-xs flex items-center justify-center gap-1.5 shadow-xs font-semibold w-full md:w-auto">
                        <i class="fa-solid fa-circle-check text-emerald-700" aria-hidden="true"></i> Sudah ada di Vendor Management
                    </a>
                @else
                    <form method="POST" action="{{ route('vendor-directory.store', $vendor) }}">
                        @csrf
                        <button type="submit"
                            class="btn-primary-rose min-h-[44px] px-4 py-2.5 rounded-xl text-xs sm:text-sm flex items-center justify-center gap-2 cursor-pointer w-full md:w-auto font-semibold">
                            <i class="fa-solid fa-plus" aria-hidden="true"></i> Simpan ke Vendor Management
                        </button>
                    </form>
                @endif

                <a href="{{ route('vendor-directory.index') }}"
                    class="min-h-[44px] px-4 py-2.5 rounded-xl bg-white text-[#5F6F5B] hover:bg-[#FAF7F2] border border-[#B6ADA3]/40 text-xs flex items-center justify-center gap-1.5 shadow-xs font-semibold w-full md:w-auto">
                    <i class="fa-solid fa-arrow-left" aria-hidden="true"></i> Kembali
                </a>
            </div>
        </div>

        <div class="space-y-6">
            {{-- Hero + Kontak. Foto dibiarkan pada rasio aslinya (tidak dipotong,
                 hanya dibatasi tingginya), lalu panel Kontak mengisi sisa lebar
                 di sebelahnya. object-contain + max-height, bukan object-cover
                 dengan tinggi tetap, karena cover memotong bagian foto. --}}
            <div class="flex flex-col lg:flex-row lg:items-start gap-6">
                @if ($vendor->photo)
                    <div
                        class="w-full lg:w-auto lg:shrink-0 bg-white rounded-2xl border border-[#B6ADA3]/35 shadow-xs overflow-hidden">
                        <img src="{{ $vendor->photo }}" alt="{{ $vendor->name }}"
                            width="380" height="380"
                            class="w-full lg:w-auto object-contain mx-auto" style="max-height:380px">
                    </div>
                @endif

                {{-- Kontak, lokasi, sosmed --}}
                <div class="flex-1 min-w-0 w-full">
                    <div class="bg-white p-5 sm:p-6 rounded-2xl border border-[#B6ADA3]/35 shadow-xs space-y-4">
                        @include('vendor-directory.partials.contact')
                    </div>
                </div>
            </div>

            @if (!empty($album))
                <div class="bg-white p-5 sm:p-6 rounded-2xl border border-[#B6ADA3]/35 shadow-xs" x-data="{ modalOpen: false, active: 0 }">
                    <h2 class="font-bold text-[#5F6F5B] flex items-center gap-2 mb-4">
                        <i class="fa-solid fa-images text-[#D8A7B1]"></i> Album
                    </h2>

                    {{-- Grid hanya 6 foto; sisanya dibuka lewat tombol "Lihat Semua". --}}
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-2.5">
                        @foreach (array_slice($album, 0, $albumPreview) as $i => $photo)
                            <button type="button" @click="active = {{ $i }}; modalOpen = true"
                                class="group relative w-full overflow-hidden rounded-xl border border-[#B6ADA3]/30 hover:border-[#D8A7B1] transition-colors"
                                aria-label="Lihat foto {{ $i + 1 }} dari album {{ $vendor->name }}">
                                {{-- Rasio 1:1 pakai style inline, bukan aspect-square/pt-full: kedua
                                         kelas itu tidak ikut ter-generate di build
                                         Tailwind proyek ini sehingga thumbnail gepeng. --}}
                                <span class="block" style="padding-top:100%"></span>
                                <img src="{{ $photo }}" alt="{{ $vendor->name }} - foto {{ $i + 1 }}"
                                    loading="lazy" width="300" height="300" class="absolute inset-0 w-full h-full object-cover">
                            </button>
                        @endforeach
                    </div>

                    @if (count($album) > $albumPreview)
                        <button type="button" @click="active = {{ $albumPreview }}; modalOpen = true"
                            class="mt-3 w-full min-h-[44px] px-3 py-2.5 rounded-xl bg-[#FAF7F2] border border-[#B6ADA3]/40 text-xs font-semibold text-[#5F6F5B] hover:bg-white transition-colors flex items-center justify-center gap-2 cursor-pointer">
                            <i class="fa-solid fa-images" aria-hidden="true"></i> Lihat Semua ({{ count($album) }} foto)
                        </button>
                    @endif

                    {{-- Lightbox --}}
                    <div x-show="modalOpen" x-cloak @keydown.escape.window="modalOpen = false"
                        class="fixed inset-0 z-[60] bg-black/80 flex items-center justify-center p-4"
                        @click.self="modalOpen = false">
                        <button type="button" @click="modalOpen = false"
                            class="absolute top-4 right-4 w-12 h-12 rounded-full bg-white/20 hover:bg-white/30 text-white flex items-center justify-center cursor-pointer"
                            aria-label="Tutup galeri">
                            <i class="fa-solid fa-xmark text-lg" aria-hidden="true"></i>
                        </button>
                        <img :src='@json($album)[active]' alt="{{ $vendor->name }}"
                            class="max-w-full max-h-[85vh] object-contain rounded-xl">
                        <div class="absolute bottom-5 left-1/2 -translate-x-1/2 flex items-center gap-4">
                            <button type="button"
                                @click="active = (active - 1 + {{ count($album) }}) % {{ count($album) }}"
                                class="w-12 h-12 rounded-full bg-white/20 hover:bg-white/30 text-white flex items-center justify-center cursor-pointer"
                                aria-label="Foto sebelumnya">
                                <i class="fa-solid fa-chevron-left text-sm" aria-hidden="true"></i>
                            </button>
                            <span class="text-xs text-white/90 tabular-nums">
                                <span x-text="active + 1"></span>/{{ count($album) }}
                            </span>
                            <button type="button" @click="active = (active + 1) % {{ count($album) }}"
                                class="w-12 h-12 rounded-full bg-white/20 hover:bg-white/30 text-white flex items-center justify-center cursor-pointer"
                                aria-label="Foto berikutnya">
                                <i class="fa-solid fa-chevron-right text-sm" aria-hidden="true"></i>
                            </button>
                        </div>
                    </div>
                </div>
            @endif

            @if (!empty($reviews))
                <div class="bg-white p-5 sm:p-6 rounded-2xl border border-[#B6ADA3]/35 shadow-xs">
                    <h2 class="font-bold text-[#5F6F5B] flex items-center gap-2 mb-4">
                        <i class="fa-regular fa-comment-dots text-[#D8A7B1]" aria-hidden="true"></i> Ulasan Vendor
                        @if ($vendor->review_count)
                            <span class="text-xs font-normal text-[#5F6F5B]">({{ $vendor->review_count }} review)</span>
                        @endif
                    </h2>

                    <div class="space-y-3">
                        @foreach ($reviews as $review)
                            <div class="p-4 rounded-xl bg-[#FAF7F2] border border-[#B6ADA3]/30">
                                <div class="flex items-start justify-between gap-3 mb-2">
                                    <div class="text-sm font-semibold text-[#5F6F5B]">{{ $review['title'] }}</div>
                                    @if ($review['date'])
                                        <div class="text-xs text-[#5F6F5B] shrink-0">{{ $review['date'] }}</div>
                                    @endif
                                </div>
                                <p class="text-xs text-[#5F6F5B] leading-relaxed whitespace-pre-line">
                                    {{ $review['content'] }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

        {{-- Review & rating pengguna --}}
        <div class="bg-white p-5 sm:p-6 rounded-2xl border border-[#B6ADA3]/35 shadow-xs">
            <h2 class="font-bold text-[#5F6F5B] flex flex-wrap items-center gap-2 mb-4">
                <i class="fa-solid fa-star-half-stroke text-[#D8A7B1]" aria-hidden="true"></i> Review Pengguna
                @if ($userReviews->isNotEmpty())
                    <span class="text-[11px] font-normal text-[#5F6F5B]">
                        Rata-rata <strong class="text-[#8B3A47]">{{ number_format($userRatingAvg, 1) }}</strong>
                        dari {{ $userReviews->count() }} pasangan
                    </span>
                @endif
            </h2>

            {{-- Form review: rating bintang (Alpine) + ulasan opsional --}}
            <form method="POST" action="{{ route('vendor-directory.review', $vendor) }}"
                x-data="{ selected: {{ (int) old('rating', $myReview?->rating ?? 0) }} }"
                class="p-4 rounded-xl bg-[#FAF7F2] border border-[#B6ADA3]/30 space-y-3 mb-4">
                @csrf
                <div>
                    <div class="text-xs font-semibold text-[#5F6F5B] mb-1.5">Rating kamu{{ $myReview ? ' (update review)' : '' }}</div>
                    <input type="hidden" name="rating" :value="selected">
                    <div class="flex items-center gap-2" role="group" aria-label="Pilih rating bintang">
                        <template x-for="star in 5" :key="star">
                            <button type="button" @click="selected = star"
                                class="cursor-pointer w-12 h-12 flex items-center justify-center text-2xl transition-transform hover:scale-110 focus:outline-none focus:ring-2 focus:ring-[#8B3A47]/40 rounded-xl bg-white border border-[#B6ADA3]/40"
                                :aria-label="'Beri rating ' + star + ' bintang'"
                                :class="star <= selected ? 'text-[#8B3A47] border-[#8B3A47]/50 shadow-xs' : 'text-[#5F6F5B]/50 hover:text-[#8B3A47]'">
                                <i class="fa-solid fa-star" aria-hidden="true"></i>
                            </button>
                        </template>
                        @if ($errors->has('rating'))
                            <span class="ml-2 text-xs font-semibold text-red-600">Pilih rating dulu</span>
                        @endif
                    </div>
                </div>

                <div>
                    <label class="text-xs font-semibold text-[#5F6F5B] mb-1 block" for="catalog-review">Ulasan (opsional)</label>
                    <textarea name="review" id="catalog-review" rows="3" maxlength="1000"
                        placeholder="Bagikan pengalamanmu dengan vendor ini..."
                        class="w-full bg-white border border-[#B6ADA3]/40 text-xs text-[#5F6F5B] px-3 py-2.5 rounded-xl focus:border-[#D8A7B1] focus:outline-none resize-none">{{ old('review', $myReview?->review) }}</textarea>
                </div>

                <button type="submit"
                    class="btn-primary-rose min-h-[44px] px-4 py-2.5 rounded-xl text-xs flex items-center justify-center gap-2 cursor-pointer font-semibold">
                    <i class="fa-regular fa-paper-plane" aria-hidden="true"></i> {{ $myReview ? 'Perbarui Review' : 'Kirim Review' }}
                </button>
            </form>

            <div class="space-y-3">
                @forelse ($userReviews as $userReview)
                    <div class="p-4 rounded-xl bg-[#FAF7F2] border border-[#B6ADA3]/30">
                        <div class="flex items-start justify-between gap-3 mb-2">
                            <div>
                                <div class="text-sm font-semibold text-[#5F6F5B]">
                                    {{ $userReview->wedding?->couple_name ?? 'Pasangan' }}
                                    @if ($myReview && $userReview->wedding_id === $myReview->wedding_id)
                                        <span class="text-[10px] font-semibold text-[#8B3A47]">(review kamu)</span>
                                    @endif
                                </div>
                                <div class="flex items-center gap-1 mt-0.5">
                                    @for ($i = 1; $i <= 5; $i++)
                                        <i class="fa-solid fa-star text-[10px] {{ $i <= $userReview->rating ? 'text-[#8B3A47]' : 'text-[#B6ADA3]/50' }}" aria-hidden="true"></i>
                                    @endfor
                                </div>
                            </div>
                            <div class="text-xs text-[#5F6F5B] shrink-0">
                                {{ $userReview->created_at?->translatedFormat('d M Y') }}
                            </div>
                        </div>
                        @if ($userReview->review)
                            <p class="text-xs text-[#5F6F5B] leading-relaxed whitespace-pre-line">
                                {{ $userReview->review }}</p>
                        @endif
                    </div>
                @empty
                    <p class="text-xs text-[#5F6F5B]">Belum ada review dari pengguna. Jadilah yang pertama!</p>
                @endforelse
            </div>
        </div>

            @if (!empty($rooms))
                <div class="bg-white p-5 sm:p-6 rounded-2xl border border-[#B6ADA3]/35 shadow-xs">
                    <h2 class="font-bold text-[#5F6F5B] flex items-center gap-2 mb-4">
                        <i class="fa-solid fa-layer-group text-[#D8A7B1]" aria-hidden="true"></i> Paket &amp; Harga
                    </h2>
                    <div class="space-y-2.5">
                        @foreach ($rooms as $room)
                            <div
                                class="flex items-center justify-between gap-4 p-3.5 rounded-xl bg-[#FAF7F2] border border-[#B6ADA3]/30">
                                <div class="min-w-0">
                                    <div class="text-sm font-semibold text-[#5F6F5B] truncate">{{ $room['name'] }}</div>
                                    @if (!empty($room['capacity']))
                                        <div class="text-xs text-[#5F6F5B] font-medium mt-0.5">{{ $room['capacity'] }} pax</div>
                                    @endif
                                </div>
                                @if (!empty($room['price']))
                                    <div class="text-sm font-bold text-[#5F6F5B] whitespace-nowrap shrink-0">
                                        Rp {{ number_format($room['price'], 0, ',', '.') }}
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            @if ($vendor->about)
                <div class="bg-white p-5 sm:p-6 rounded-2xl border border-[#B6ADA3]/35 shadow-xs">
                    <h2 class="font-bold text-[#5F6F5B] flex items-center gap-2 mb-3">
                        <i class="fa-solid fa-circle-info text-[#D8A7B1]" aria-hidden="true"></i> Tentang Vendor
                    </h2>
                    <p class="text-sm text-[#5F6F5B] leading-relaxed">{{ $vendor->about }}</p>
                </div>
            @endif
        </div>
    </div>
@endsection
