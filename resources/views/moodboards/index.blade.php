@extends('layouts.app')

@section('title', 'Inspirasi - Moodboard')

@section('content')
    <div class="space-y-6" x-data="{ modalOpen: false, editMode: false, currentItem: {} }">
        <!-- Top Bar -->
        <div
            class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-white p-5 rounded-2xl border border-[#B6ADA3]/35 shadow-xs text-[#5F6F5B]">
            <div>
                <h1 class="text-xl md:text-2xl font-bold font-serif-title text-[#5F6F5B]">Moodboard</h1>
                <p class="text-xs text-[#5F6F5B] mt-1">Kumpulkan inspirasi tema, dekorasi, warna, dan konsep visual untuk
                    mewujudkan pernikahan impian.</p>
            </div>
            <div class="flex flex-col sm:flex-row sm:items-center gap-3 w-full md:w-auto">
                <span
                    class="inline-flex items-center gap-1.5 rounded-full border border-[#A3B7A6]/60 bg-[#EBF2EC] px-3 py-1.5 text-[11px] font-bold text-[#2E4B33]">
                    <i class="fa-solid fa-crown text-[#2E4B33]" aria-hidden="true"></i>
                    PRO
                </span>

                <button @click="modalOpen = true; editMode = false; currentItem = {}"
                    class="btn-primary-rose px-4 py-2.5 rounded-xl text-sm flex items-center justify-center gap-2 cursor-pointer w-full md:w-auto">
                    <i class="fa-solid fa-plus" aria-hidden="true"></i> Tambah Moodboard
                </button>
            </div>
        </div>

        <!-- Category Filter Tabs -->
        <div class="flex flex-wrap items-center gap-2 pb-2">
            <a href="{{ route('moodboards.index') }}"
                class="px-4 py-2 rounded-xl text-xs font-semibold transition-all {{ !request('category') ? 'bg-[#5F6F5B] text-white shadow-xs' : 'bg-white text-[#5F6F5B] hover:bg-[#FAF7F2] border border-[#B6ADA3]/40' }}">
                Semua Kategori
            </a>
            @foreach ($categories as $cat)
                <a href="{{ route('moodboards.index', ['category' => $cat]) }}"
                    class="px-4 py-2 rounded-xl text-xs font-semibold transition-all {{ request('category') == $cat ? 'bg-[#5F6F5B] text-white shadow-xs' : 'bg-white text-[#5F6F5B] hover:bg-[#FAF7F2] border border-[#B6ADA3]/40' }}">
                    {{ $cat }}
                </a>
            @endforeach
        </div>

        <!-- Moodboard Gallery Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            @forelse($moodboards as $mb)
                <div
                    class="bg-white rounded-2xl border border-[#B6ADA3]/35 shadow-xs overflow-hidden flex flex-col hover:border-[#D8A7B1] transition-all group text-[#5F6F5B]">
                    <!-- Visual Card Header -->
                    <div
                        class="relative h-44 sm:h-48 flex items-center justify-center overflow-hidden @if (!$mb->link_preview) bg-gradient-to-tr from-[#D8A7B1]/20 via-[#FAF7F2] to-[#A3B7A6]/20 @endif">
                        @if ($mb->link_preview)
                            <img src="{{ $mb->link_preview }}" alt="{{ $mb->title }}" class="w-full h-full object-cover"
                                onerror="this.closest('.relative').classList.add('bg-gradient-to-tr', 'from-[#D8A7B1]/20', 'via-[#FAF7F2]', 'to-[#A3B7A6]/20'); this.remove();">
                        @else
                            <div class="text-center p-6 space-y-2">
                                <i class="fa-solid fa-wand-magic-sparkles text-[#8B3A47] text-3xl" aria-hidden="true"></i>
                                <div class="text-xs text-[#5F6F5B] font-semibold tracking-wide uppercase">
                                    {{ $mb->category }}
                                </div>
                            </div>
                        @endif
                        <span
                            class="absolute top-3 right-3 bg-white/90 backdrop-blur text-[#5F6F5B] text-[10px] font-bold px-2.5 py-1 rounded-full border border-[#B6ADA3]/30 shadow-xs">
                            {{ $mb->category }}
                        </span>
                    </div>
                    <!-- Card Content -->
                    <div class="p-5 space-y-3 flex-1 flex flex-col justify-between">
                        <div>
                            <h3
                                class="font-bold text-[#5F6F5B] text-base mb-1 group-hover:text-[#8B3A47] transition-colors leading-snug">
                                {{ $mb->title }}</h3>
                            <p class="text-xs text-[#5F6F5B] leading-relaxed">
                                {{ $mb->description ?? 'Tidak ada deskripsi.' }}</p>
                        </div>
                        @if ($mb->link_reference)
                            <a href="{{ $mb->link_reference }}" target="_blank" rel="noopener noreferrer"
                                class="inline-flex items-center gap-1 text-xs text-[#5F6F5B] hover:text-[#8B3A47] hover:underline font-semibold">
                                <i class="fa-solid fa-arrow-up-right-from-square text-[10px]" aria-hidden="true"></i> Link Referensi Inspirasi
                            </a>
                        @endif
                        <div
                            class="pt-3 border-t border-[#B6ADA3]/25 flex items-center justify-between text-xs text-[#5F6F5B]">
                            <span>{{ $mb->created_at->format('d M Y') }}</span>
                            <div class="flex items-center gap-2">
                                <button @click="modalOpen = true; editMode = true; currentItem = {{ json_encode($mb) }}"
                                    title="Edit" class="btn-icon">
                                    <i class="fa-solid fa-pen-to-square" aria-hidden="true"></i>
                                </button>
                                <form id="del-mb-{{ $mb->id }}" action="{{ route('moodboards.destroy', $mb->id) }}"
                                    method="POST">
                                    @csrf
                                    @method('DELETE')
                                    <button type="button" title="Hapus"
                                        onclick="confirmDelete('del-mb-{{ $mb->id }}', '{{ addslashes($mb->title) }}')"
                                        class="btn-icon">
                                        <i class="fa-solid fa-trash" aria-hidden="true"></i>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div
                    class="col-span-full text-center py-12 bg-white rounded-2xl text-[#5F6F5B] text-sm border border-[#B6ADA3]/35">
                    <i class="fa-solid fa-palette text-[#5F6F5B] text-2xl mb-3" aria-hidden="true"></i>
                    <div>Belum ada inspirasi moodboard dalam kategori ini.</div>
                </div>
            @endforelse
        </div>

        <!-- Modal Form -->
        <div x-show="modalOpen" x-transition.opacity
            class="fixed inset-0 z-50 bg-[#5F6F5B]/40 backdrop-blur-md flex items-center justify-center p-4">
            <div x-outside="modalOpen = false"
                class="bg-white w-full max-w-lg p-6 rounded-2xl border border-[#B6ADA3]/40 shadow-2xl space-y-4 text-[#5F6F5B]">
                <div class="flex items-center justify-between border-b border-[#B6ADA3]/30 pb-3">
                    <h2 class="text-lg font-bold font-serif-title text-[#5F6F5B]"
                        x-text="editMode ? 'Edit Inspirasi Moodboard' : 'Tambah Inspirasi Moodboard'"></h2>
                    <button @click="modalOpen = false" class="text-[#5F6F5B] hover:text-[#8B3A47] cursor-pointer" aria-label="Tutup modal"><i
                            class="fa-solid fa-xmark" aria-hidden="true"></i></button>
                </div>
                <form :action="editMode ? '/moodboards/' + currentItem.id : '{{ route('moodboards.store') }}'"
                    method="POST" class="space-y-4">
                    @csrf
                    <template x-if="editMode">
                        <input type="hidden" name="_method" value="PUT">
                    </template>
                    <div>
                        <label for="mb_title" class="block text-xs font-semibold text-[#5F6F5B] mb-1">Judul Inspirasi *</label>
                        <input id="mb_title" type="text" name="title" x-model="currentItem.title" required
                            class="w-full bg-[#FAF7F2] border border-[#B6ADA3]/40 text-sm text-[#5F6F5B] px-3 py-2 rounded-xl focus:border-[#D8A7B1] focus:outline-none"
                            placeholder="Pelaminan Dusty Rose & Sage Garden" aria-label="Judul Inspirasi">
                    </div>
                    <div>
                        <label for="mb_category" class="block text-xs font-semibold text-[#5F6F5B] mb-1">Kategori *</label>
                        <select id="mb_category" name="category" x-model="currentItem.category" required aria-label="Kategori"
                            class="w-full bg-[#FAF7F2] border border-[#B6ADA3]/40 text-sm text-[#5F6F5B] px-3 py-2 rounded-xl focus:border-[#D8A7B1] focus:outline-none">
                            <option value="">-- Pilih Kategori --</option>
                            @foreach ($categories as $cat)
                                <option value="{{ $cat }}">{{ $cat }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="mb_link_reference" class="block text-xs font-semibold text-[#5F6F5B] mb-1">Link Referensi (Pinterest /
                            Instagram)</label>
                        <input id="mb_link_reference" type="url" name="link_reference" x-model="currentItem.link_reference"
                            class="w-full bg-[#FAF7F2] border border-[#B6ADA3]/40 text-sm text-[#5F6F5B] px-3 py-2 rounded-xl focus:border-[#D8A7B1] focus:outline-none"
                            placeholder="https://pinterest.com/pin/..." aria-label="Link Referensi">
                    </div>
                    <div>
                        <label for="mb_description" class="block text-xs font-semibold text-[#5F6F5B] mb-1">Deskripsi / Konsep</label>
                        <textarea id="mb_description" name="description" x-model="currentItem.description" rows="3" aria-label="Deskripsi atau Konsep"
                            class="w-full bg-[#FAF7F2] border border-[#B6ADA3]/40 text-sm text-[#5F6F5B] px-3 py-2 rounded-xl focus:border-[#D8A7B1] focus:outline-none"
                            placeholder="Detail gaya visual, warna, bunga..."></textarea>
                    </div>
                    <div class="flex justify-end gap-3 pt-3 border-t border-[#B6ADA3]/30">
                        <button type="button" @click="modalOpen = false"
                            class="px-4 py-2 text-xs text-[#5F6F5B] hover:text-[#8B3A47] cursor-pointer">Batal</button>
                        <button type="submit" class="btn-primary-rose px-5 py-2 rounded-xl text-xs cursor-pointer">Simpan
                            Inspirasi</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
