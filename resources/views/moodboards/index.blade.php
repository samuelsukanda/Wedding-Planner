@extends('layouts.app')

@section('title', 'Inspirasi & Jadwal - Moodboard')

@section('content')
    <div class="space-y-6" x-data="{ modalOpen: false, editMode: false, currentItem: {} }">
        <!-- Top Bar -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold font-serif-title">Moodboard & Inspirasi</h1>
            </div>
            <button @click="modalOpen = true; editMode = false; currentItem = {}"
                class="btn-primary-rose px-4 py-2.5 rounded-xl text-sm flex items-center justify-center gap-2 cursor-pointer w-full md:w-auto">
                <i class="fa-solid fa-plus"></i> Tambah Moodboard
            </button>
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
                    class="bg-white rounded-2xl border border-[#B6ADA3]/35 shadow-xs overflow-hidden flex flex-col hover:border-[#D8A7B1] transition-all group">
                    <!-- Visual Card Header -->
                    <div
                        class="relative h-44 sm:h-48 bg-gradient-to-tr from-[#D8A7B1]/20 via-[#FAF7F2] to-[#A3B7A6]/20 flex items-center justify-center overflow-hidden">
                        <div class="text-center p-6 space-y-2">
                            <i class="fa-solid fa-wand-magic-sparkles text-[#D8A7B1] text-3xl"></i>
                            <div class="text-xs text-[#5F6F5B] font-semibold tracking-wide uppercase">{{ $mb->category }}
                            </div>
                        </div>
                        <span
                            class="absolute top-3 right-3 bg-white/90 backdrop-blur text-[#5F6F5B] text-[10px] font-bold px-2.5 py-1 rounded-full border border-[#B6ADA3]/30 shadow-xs">
                            {{ $mb->category }}
                        </span>
                    </div>
                    <!-- Card Content -->
                    <div class="p-5 space-y-3 flex-1 flex flex-col justify-between">
                        <div>
                            <h3
                                class="font-bold text-[#5F6F5B] text-base mb-1 group-hover:text-[#5F6F5B] transition-colors leading-snug">
                                {{ $mb->title }}</h3>
                            <p class="text-xs text-[#5F6F5B]/80 leading-relaxed">
                                {{ $mb->description ?? 'Tidak ada deskripsi.' }}</p>
                        </div>
                        @if ($mb->link_reference)
                            <a href="{{ $mb->link_reference }}" target="_blank"
                                class="inline-flex items-center gap-1 text-xs text-[#5F6F5B] hover:text-[#5F6F5B] hover:underline font-semibold">
                                <i class="fa-solid fa-arrow-up-right-from-square text-[10px]"></i> Link Referensi Inspirasi
                            </a>
                        @endif
                        <div
                            class="pt-3 border-t border-[#B6ADA3]/25 flex items-center justify-between text-xs text-[#B6ADA3]">
                            <span>{{ $mb->created_at->format('d M Y') }}</span>
                            <div class="flex items-center gap-2">
                                <button @click="modalOpen = true; editMode = true; currentItem = {{ json_encode($mb) }}"
                                    title="Edit"
                                    class="p-2 text-[#B6ADA3] hover:text-[#5F6F5B] cursor-pointer">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                </button>
                                <form id="del-mb-{{ $mb->id }}" action="{{ route('moodboards.destroy', $mb->id) }}" method="POST">
                                    @csrf
                                    @method('DELETE')
                                    <button type="button"
                                        title="Hapus"
                                        onclick="confirmDelete('del-mb-{{ $mb->id }}', '{{ addslashes($mb->title) }}')"
                                        class="p-2 text-[#B6ADA3] hover:text-[#5F6F5B] cursor-pointer">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div
                    class="col-span-full text-center py-12 bg-white rounded-2xl text-[#B6ADA3] text-sm border border-[#B6ADA3]/35">
                    Belum ada inspirasi moodboard dalam kategori ini.
                </div>
            @endforelse
        </div>

        <!-- Modal Form -->
        <div x-show="modalOpen" x-transition.opacity
            class="fixed inset-0 z-50 bg-[#5F6F5B]/40 backdrop-blur-md flex items-center justify-center p-4">
            <div @click.away="modalOpen = false"
                class="bg-white w-full max-w-lg p-6 rounded-2xl border border-[#B6ADA3]/40 shadow-2xl space-y-4">
                <div class="flex items-center justify-between border-b border-[#B6ADA3]/30 pb-3">
                    <h3 class="text-lg font-bold font-serif-title text-[#5F6F5B]"
                        x-text="editMode ? 'Edit Inspirasi Moodboard' : 'Tambah Inspirasi Moodboard'"></h3>
                    <button @click="modalOpen = false" class="text-[#B6ADA3] hover:text-[#5F6F5B] cursor-pointer"><i
                            class="fa-solid fa-xmark"></i></button>
                </div>
                <form :action="editMode ? '/moodboards/' + currentItem.id : '{{ route('moodboards.store') }}'" method="POST" class="space-y-4">
                    @csrf
                    <template x-if="editMode">
                        <input type="hidden" name="_method" value="PUT">
                    </template>
                    <div>
                        <label class="block text-xs font-semibold text-[#5F6F5B] mb-1">Judul Inspirasi *</label>
                        <input type="text" name="title" x-model="currentItem.title" required
                            class="w-full bg-[#FAF7F2] border border-[#B6ADA3]/40 text-sm text-[#5F6F5B] px-3 py-2 rounded-xl focus:border-[#D8A7B1] focus:outline-none"
                            placeholder="Pelaminan Dusty Rose & Sage Garden">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-[#5F6F5B] mb-1">Kategori *</label>
                        <select name="category" x-model="currentItem.category" required
                            class="w-full bg-[#FAF7F2] border border-[#B6ADA3]/40 text-sm text-[#5F6F5B] px-3 py-2 rounded-xl focus:border-[#D8A7B1] focus:outline-none">
                            @foreach ($categories as $cat)
                                <option value="{{ $cat }}">{{ $cat }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-[#5F6F5B] mb-1">Link Referensi (Pinterest /
                            Instagram)</label>
                        <input type="url" name="link_reference" x-model="currentItem.link_reference"
                            class="w-full bg-[#FAF7F2] border border-[#B6ADA3]/40 text-sm text-[#5F6F5B] px-3 py-2 rounded-xl focus:border-[#D8A7B1] focus:outline-none"
                            placeholder="https://pinterest.com/pin/...">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-[#5F6F5B] mb-1">Deskripsi / Konsep</label>
                        <textarea name="description" x-model="currentItem.description" rows="3"
                            class="w-full bg-[#FAF7F2] border border-[#B6ADA3]/40 text-sm text-[#5F6F5B] px-3 py-2 rounded-xl focus:border-[#D8A7B1] focus:outline-none"
                            placeholder="Detail gaya visual, warna, bunga..."></textarea>
                    </div>
                    <div class="flex justify-end gap-3 pt-3 border-t border-[#B6ADA3]/30">
                        <button type="button" @click="modalOpen = false"
                            class="px-4 py-2 text-xs text-[#B6ADA3] hover:text-[#5F6F5B] cursor-pointer">Batal</button>
                        <button type="submit" class="btn-primary-rose px-5 py-2 rounded-xl text-xs cursor-pointer">Simpan
                            Inspirasi</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
