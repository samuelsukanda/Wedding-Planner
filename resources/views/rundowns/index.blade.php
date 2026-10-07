@extends('layouts.app')

@section('title', 'Inspirasi & Jadwal - Event Rundown')

@section('content')
    <div class="space-y-6" x-data="{ modalOpen: false, editMode: false, currentItem: {} }">
        <div class="no-print flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold font-serif-title">Event Rundown</h1>
            </div>
            <button @click="modalOpen = true; editMode = false; currentItem = {}"
                class="btn-primary-rose px-4 py-2.5 rounded-xl text-sm flex items-center justify-center gap-2 cursor-pointer w-full md:w-auto">
                <i class="fa-solid fa-plus"></i> Tambah Susunan Acara
            </button>
        </div>

        {{-- Print Header: hidden on screen, shown only when printing --}}
        <div class="print-header" style="display:none;">
            <h1>Event Rundown &mdash; {{ $wedding?->couple_name }}</h1>
            <p>Dicetak pada: {{ now()->format('d F Y, H:i') }} WIB &nbsp;|&nbsp; Total {{ $rundowns->count() }} Susunan
                Acara</p>
        </div>

        <!-- Timeline View -->
        <div id="rundown-content" class="bg-white p-5 sm:p-6 rounded-2xl border border-[#B6ADA3]/35 shadow-xs space-y-6">
            <div class="flex items-center justify-between border-b border-[#B6ADA3]/30 pb-4">
                <h3 class="font-bold text-[#5F6F5B] text-base sm:text-lg flex items-center gap-2">
                    <i class="fa-solid fa-timeline text-[#D8A7B1]"></i> Rundown Acara
                </h3>
                <button onclick="window.print()"
                    class="no-print text-xs px-3 py-1.5 rounded-lg bg-[#FAF7F2] text-[#5F6F5B] hover:bg-[#D8A7B1]/20 border border-[#B6ADA3]/40 font-medium cursor-pointer">
                    <i class="fa-solid fa-print mr-1"></i> Cetak Rundown
                </button>
            </div>

            <div
                class="relative pl-6 md:pl-10 print:pl-10 space-y-6 sm:space-y-8 before:absolute before:left-3 md:before:left-5 print:before:left-5 before:top-3 before:bottom-3 before:w-0.5 before:bg-gradient-to-b before:from-[#D8A7B1] before:via-[#A3B7A6] before:to-[#5F6F5B]">
                @forelse($rundowns as $index => $rd)
                    <div
                        class="relative flex flex-col md:flex-row md:items-center print:flex-row print:items-center justify-between gap-4 p-4 sm:p-5 rounded-2xl bg-[#FAF7F2] border border-[#B6ADA3]/30 hover:border-[#D8A7B1] transition-all">
                        <!-- Timeline Node -->
                        <div
                            class="absolute -left-6 md:-left-10 print:-left-10 top-5 w-6 h-6 rounded-full bg-white border-2 border-[#D8A7B1] flex items-center justify-center text-[10px] font-bold text-[#5F6F5B] shadow-xs">
                            {{ $index + 1 }}
                        </div>

                        <div class="space-y-1">
                            <div class="flex items-center gap-3">
                                <span
                                    class="px-3 py-1 rounded-lg bg-[#D8A7B1]/20 text-[#5F6F5B] border border-[#D8A7B1]/40 text-xs font-bold font-mono">
                                    <i class="fa-solid fa-clock text-[10px] mr-1"></i> {{ $rd->time }}
                                </span>
                                <h4 class="font-bold text-[#5F6F5B] text-base leading-snug">{{ $rd->activity }}</h4>
                            </div>
                            <div class="flex flex-wrap items-center gap-4 text-xs text-[#5F6F5B]/80 pt-2">
                                <span><i class="fa-solid fa-user-gear text-[#D8A7B1] mr-1"></i> PIC: <strong
                                        class="text-[#5F6F5B]">{{ $rd->pic }}</strong></span>
                                @if ($rd->location)
                                    <span><i class="fa-solid fa-location-dot text-[#D8A7B1] mr-1"></i>
                                        {{ $rd->location }}</span>
                                @endif
                            </div>
                            @if ($rd->notes)
                                <div
                                    class="text-xs text-[#5F6F5B]/80 mt-2 bg-white p-2.5 rounded-xl border border-[#B6ADA3]/30 italic">
                                    Catatan: {{ $rd->notes }}
                                </div>
                            @endif
                        </div>

                        <div class="no-print flex items-center gap-2 self-end md:self-center">
                            <button @click="modalOpen = true; editMode = true; currentItem = {{ json_encode($rd) }}"
                                title="Edit" class="p-2 text-[#B6ADA3] hover:text-[#5F6F5B] cursor-pointer"><i
                                    class="fa-solid fa-pen-to-square"></i></button>
                            <form id="del-rundown-{{ $rd->id }}" action="{{ route('rundowns.destroy', $rd->id) }}"
                                method="POST">
                                @csrf
                                @method('DELETE')
                                <button type="button" title="Hapus"
                                    onclick="confirmDelete('del-rundown-{{ $rd->id }}', '{{ addslashes($rd->activity) }}')"
                                    class="p-2 text-[#B6ADA3] hover:text-[#5F6F5B] cursor-pointer"><i
                                        class="fa-solid fa-trash"></i></button>
                            </form>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-10 text-[#B6ADA3] text-sm">Belum ada susunan acara yang dibuat.</div>
                @endforelse
            </div>
        </div>

        <!-- Modal Form -->
        <div x-show="modalOpen" x-transition.opacity
            class="no-print fixed inset-0 z-50 bg-[#5F6F5B]/40 backdrop-blur-md flex items-center justify-center p-4">
            <div @click.away="modalOpen = false"
                class="bg-white w-full max-w-lg p-6 rounded-2xl border border-[#B6ADA3]/40 shadow-2xl space-y-4">
                <div class="flex items-center justify-between border-b border-[#B6ADA3]/30 pb-3">
                    <h3 class="text-lg font-bold font-serif-title text-[#5F6F5B]"
                        x-text="editMode ? 'Edit Susunan Acara' : 'Tambah Susunan Acara'"></h3>
                    <button @click="modalOpen = false" class="text-[#B6ADA3] hover:text-[#5F6F5B] cursor-pointer"><i
                            class="fa-solid fa-xmark"></i></button>
                </div>
                <form :action="editMode ? '/rundowns/' + currentItem.id : '{{ route('rundowns.store') }}'" method="POST"
                    class="space-y-4">
                    @csrf
                    <template x-if="editMode"><input type="hidden" name="_method" value="PUT"></template>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-[#5F6F5B] mb-1">Waktu (Jam) *</label>
                            <input type="text" name="time" x-model="currentItem.time" required
                                class="w-full bg-[#FAF7F2] border border-[#B6ADA3]/40 text-sm text-[#5F6F5B] px-3 py-2 rounded-xl focus:border-[#D8A7B1] focus:outline-none"
                                placeholder="09.00 - 10.30">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-[#5F6F5B] mb-1">Urutan (Sort Order)</label>
                            <input type="number" name="sort_order" x-model="currentItem.sort_order"
                                class="w-full bg-[#FAF7F2] border border-[#B6ADA3]/40 text-sm text-[#5F6F5B] px-3 py-2 rounded-xl focus:border-[#D8A7B1] focus:outline-none">
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-[#5F6F5B] mb-1">Nama Kegiatan / Acara *</label>
                        <input type="text" name="activity" x-model="currentItem.activity" required
                            class="w-full bg-[#FAF7F2] border border-[#B6ADA3]/40 text-sm text-[#5F6F5B] px-3 py-2 rounded-xl focus:border-[#D8A7B1] focus:outline-none">
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-[#5F6F5B] mb-1">PIC (Penanggung Jawab) *</label>
                            <input type="text" name="pic" x-model="currentItem.pic" required
                                class="w-full bg-[#FAF7F2] border border-[#B6ADA3]/40 text-sm text-[#5F6F5B] px-3 py-2 rounded-xl focus:border-[#D8A7B1] focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-[#5F6F5B] mb-1">Lokasi</label>
                            <input type="text" name="location" x-model="currentItem.location"
                                class="w-full bg-[#FAF7F2] border border-[#B6ADA3]/40 text-sm text-[#5F6F5B] px-3 py-2 rounded-xl focus:border-[#D8A7B1] focus:outline-none">
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-[#5F6F5B] mb-1">Catatan Tambahan</label>
                        <textarea name="notes" x-model="currentItem.notes" rows="2"
                            class="w-full bg-[#FAF7F2] border border-[#B6ADA3]/40 text-sm text-[#5F6F5B] px-3 py-2 rounded-xl focus:border-[#D8A7B1] focus:outline-none"></textarea>
                    </div>
                    <div class="flex justify-end gap-3 pt-3 border-t border-[#B6ADA3]/30">
                        <button type="button" @click="modalOpen = false"
                            class="px-4 py-2 text-xs text-[#B6ADA3] hover:text-[#5F6F5B] cursor-pointer">Batal</button>
                        <button type="submit" class="btn-primary-rose px-5 py-2 rounded-xl text-xs cursor-pointer">Simpan
                            Rundown</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
