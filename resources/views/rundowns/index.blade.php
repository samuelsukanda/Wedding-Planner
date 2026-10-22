@extends('layouts.app')

@section('title', 'Perencanaan - Event Rundown')

@section('content')
    <div class="space-y-6" x-data="{
        modalOpen: false,
        editMode: false,
        currentItem: {},
        openEdit(item) {
            const [startTime, endTime] = (item.time || '').split(' - ');
            this.currentItem = {
                ...item,
                start_time: (startTime || '').replace('.', ':'),
                end_time: (endTime || '').replace('.', ':')
            };
            this.modalOpen = true;
            this.editMode = true;
        }
    }">
        <div class="no-print flex flex-col md:flex-row md:items-center justify-between gap-4 bg-white p-5 rounded-2xl border border-[#B6ADA3]/35 shadow-xs text-[#5F6F5B]">
            <div>
                <h1 class="text-xl md:text-2xl font-bold font-serif-title text-[#5F6F5B]">Event Rundown</h1>
                <p class="text-xs text-[#5F6F5B] mt-1">Susun urutan acara dan jadwal kegiatan agar seluruh rangkaian
                    pernikahan berjalan lancar.</p>
            </div>
            <button @click="modalOpen = true; editMode = false; currentItem = {}"
                class="btn-primary-rose px-4 py-2.5 rounded-xl text-sm flex items-center justify-center gap-2 cursor-pointer w-full md:w-auto">
                <i class="fa-solid fa-plus" aria-hidden="true"></i> Tambah Susunan Acara
            </button>
        </div>

        {{-- Print Header: hidden on screen, shown only when printing --}}
        <div class="print-header" style="display:none;">
            <h1>Event Rundown &mdash; {{ $wedding?->couple_name }}</h1>
            <p>Dicetak pada: {{ now()->format('d F Y, H:i') }} WIB &nbsp;|&nbsp; Total {{ $rundowns->count() }} Susunan
                Acara</p>
        </div>

        <!-- Timeline View -->
        <div id="rundown-content" class="bg-white p-5 sm:p-6 rounded-2xl border border-[#B6ADA3]/35 shadow-xs space-y-6 text-[#5F6F5B]">
            <div class="flex items-center justify-between border-b border-[#B6ADA3]/30 pb-4">
                <h2 class="font-bold text-[#5F6F5B] text-base sm:text-lg flex items-center gap-2">
                    <i class="fa-solid fa-timeline text-[#8B3A47]" aria-hidden="true"></i> Rundown Acara
                </h2>
                <button onclick="window.print()"
                    class="no-print text-xs px-3 py-1.5 rounded-lg bg-[#FAF7F2] text-[#5F6F5B] hover:bg-[#D8A7B1]/20 border border-[#B6ADA3]/40 font-medium cursor-pointer">
                    <i class="fa-solid fa-print mr-1" aria-hidden="true"></i> Cetak Rundown
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
                                    class="px-3 py-1 rounded-lg bg-[#FCE8EC] text-[#8B3A47] border border-[#D8A7B1]/60 text-xs font-bold font-mono">
                                    <i class="fa-solid fa-clock text-[10px] mr-1" aria-hidden="true"></i> {{ $rd->time }}
                                </span>
                                <h3 class="font-bold text-[#5F6F5B] text-base leading-snug">{{ $rd->activity }}</h3>
                            </div>
                            <div class="flex flex-wrap items-center gap-4 text-xs text-[#5F6F5B] pt-2">
                                <span><i class="fa-solid fa-user-gear text-[#8B3A47] mr-1" aria-hidden="true"></i> PIC: <strong
                                        class="text-[#5F6F5B]">{{ $rd->pic }}</strong></span>
                                @if ($rd->location)
                                    <span><i class="fa-solid fa-location-dot text-[#8B3A47] mr-1" aria-hidden="true"></i>
                                        {{ $rd->location }}</span>
                                @endif
                            </div>
                            @if ($rd->notes)
                                <div
                                    class="text-xs text-[#5F6F5B] mt-2 bg-white p-2.5 rounded-xl border border-[#B6ADA3]/30 italic">
                                    Catatan: {{ $rd->notes }}
                                </div>
                            @endif
                        </div>

                        <div class="no-print flex items-center gap-2 self-end md:self-center">
                            <button @click="openEdit(JSON.parse($el.dataset.rundown))" data-rundown="{{ json_encode($rd) }}"
                                title="Edit" class="btn-icon"><i class="fa-solid fa-pen-to-square" aria-hidden="true"></i></button>
                            <form id="del-rundown-{{ $rd->id }}" action="{{ route('rundowns.destroy', $rd->id) }}"
                                method="POST">
                                @csrf
                                @method('DELETE')
                                <button type="button" title="Hapus"
                                    onclick="confirmDelete('del-rundown-{{ $rd->id }}', '{{ addslashes($rd->activity) }}')"
                                    class="btn-icon"><i class="fa-solid fa-trash" aria-hidden="true"></i></button>
                            </form>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-10 text-[#5F6F5B] text-sm">
                        <i class="fa-solid fa-timeline text-[#5F6F5B] text-2xl mb-3" aria-hidden="true"></i>
                        <div>Belum ada susunan acara yang dibuat.</div>
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Modal Form -->
        <div x-show="modalOpen" x-transition.opacity
            class="no-print fixed inset-0 z-50 bg-[#5F6F5B]/40 backdrop-blur-md flex items-center justify-center p-4">
            <div x-outside="modalOpen = false"
                class="bg-white w-full max-w-lg p-6 rounded-2xl border border-[#B6ADA3]/40 shadow-2xl space-y-4 text-[#5F6F5B]">
                <div class="flex items-center justify-between border-b border-[#B6ADA3]/30 pb-3">
                    <h2 class="text-lg font-bold font-serif-title text-[#5F6F5B]"
                        x-text="editMode ? 'Edit Susunan Acara' : 'Tambah Susunan Acara'"></h2>
                    <button @click="modalOpen = false" class="text-[#5F6F5B] hover:text-[#8B3A47] cursor-pointer" aria-label="Tutup modal"><i
                            class="fa-solid fa-xmark" aria-hidden="true"></i></button>
                </div>
                <form :action="editMode ? '/rundowns/' + currentItem.id : '{{ route('rundowns.store') }}'" method="POST"
                    class="space-y-4">
                    @csrf
                    <template x-if="editMode"><input type="hidden" name="_method" value="PUT"></template>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-[#5F6F5B] mb-1">Waktu Mulai *</label>
                            <input type="time" name="start_time" x-model="currentItem.start_time" required
                                aria-label="Waktu mulai"
                                class="w-full bg-[#FAF7F2] border border-[#B6ADA3]/40 text-sm text-[#5F6F5B] px-3 py-2 rounded-xl focus:border-[#D8A7B1] focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-[#5F6F5B] mb-1">Waktu Selesai *</label>
                            <input type="time" name="end_time" x-model="currentItem.end_time" required
                                aria-label="Waktu selesai"
                                class="w-full bg-[#FAF7F2] border border-[#B6ADA3]/40 text-sm text-[#5F6F5B] px-3 py-2 rounded-xl focus:border-[#D8A7B1] focus:outline-none">
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-[#5F6F5B] mb-1">Nama Kegiatan / Acara *</label>
                        <input type="text" name="activity" x-model="currentItem.activity" required
                            placeholder="Contoh: Akad Nikah"
                            aria-label="Nama kegiatan atau acara"
                            class="w-full bg-[#FAF7F2] border border-[#B6ADA3]/40 text-sm text-[#5F6F5B] px-3 py-2 rounded-xl focus:border-[#D8A7B1] focus:outline-none">
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-[#5F6F5B] mb-1">PIC (Penanggung Jawab) *</label>
                            <input type="text" name="pic" x-model="currentItem.pic" required
                                placeholder="Nama PIC"
                                aria-label="Nama penanggung jawab"
                                class="w-full bg-[#FAF7F2] border border-[#B6ADA3]/40 text-sm text-[#5F6F5B] px-3 py-2 rounded-xl focus:border-[#D8A7B1] focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-[#5F6F5B] mb-1">Lokasi</label>
                            <input type="text" name="location" x-model="currentItem.location"
                                placeholder="Lokasi kegiatan"
                                aria-label="Lokasi"
                                class="w-full bg-[#FAF7F2] border border-[#B6ADA3]/40 text-sm text-[#5F6F5B] px-3 py-2 rounded-xl focus:border-[#D8A7B1] focus:outline-none">
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-[#5F6F5B] mb-1">Catatan Tambahan</label>
                        <textarea name="notes" x-model="currentItem.notes" rows="2"
                            placeholder="Catatan tambahan (opsional)"
                            aria-label="Catatan tambahan"
                            class="w-full bg-[#FAF7F2] border border-[#B6ADA3]/40 text-sm text-[#5F6F5B] px-3 py-2 rounded-xl focus:border-[#D8A7B1] focus:outline-none"></textarea>
                    </div>
                    <div class="flex justify-end gap-3 pt-3 border-t border-[#B6ADA3]/30">
                        <button type="button" @click="modalOpen = false"
                            class="px-4 py-2 text-xs text-[#5F6F5B] hover:text-[#8B3A47] cursor-pointer">Batal</button>
                        <button type="submit" class="btn-primary-rose px-5 py-2 rounded-xl text-xs cursor-pointer">Simpan
                            Rundown</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
