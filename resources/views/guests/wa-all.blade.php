@extends('layouts.app')

@section('title', 'Kirim WA Semua Tamu')

@section('content')
    <div class="space-y-6" x-data="{ templateModalOpen: false }">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-white p-5 rounded-2xl border border-[#B6ADA3]/35 shadow-xs text-[#5F6F5B]">
            <div>
                <h1 class="text-xl md:text-2xl font-bold font-serif-title text-[#5F6F5B]">Kirim Undangan WA</h1>
                <p class="text-xs text-[#5F6F5B] mt-1">{{ $links->count() }} tamu dengan nomor telepon
                    · Pesan: {{ $waIsDefault ? 'bawaan' : 'kustom' }}</p>
            </div>
            <div class="flex flex-col sm:flex-row gap-2 w-full md:w-auto">
                <button @click="templateModalOpen = true" type="button"
                    class="px-4 py-2.5 rounded-xl bg-white text-[#5F6F5B] hover:bg-[#FAF7F2] border border-[#B6ADA3]/40 text-xs flex items-center justify-center gap-1.5 shadow-xs font-semibold cursor-pointer">
                    <i class="fa-solid fa-pen-to-square text-[#8B3A47]" aria-hidden="true"></i> Edit Pesan
                </button>
                <a href="{{ route('guests.index') }}"
                    class="px-4 py-2.5 rounded-xl bg-white text-[#5F6F5B] hover:bg-[#FAF7F2] border border-[#B6ADA3]/40 text-xs flex items-center justify-center gap-1.5 shadow-xs font-semibold w-full md:w-auto">
                    <i class="fa-solid fa-arrow-left" aria-hidden="true"></i> Kembali
                </a>
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-[#B6ADA3]/35 shadow-xs overflow-hidden text-[#5F6F5B]">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr
                            class="bg-[#FAF7F2] text-xs text-[#5F6F5B] uppercase tracking-wider border-b border-[#B6ADA3]/30">
                            <th class="p-4 w-12">No</th>
                            <th class="p-4">Nama Tamu</th>
                            <th class="p-4">No. Telepon</th>
                            <th class="p-4">Status Kirim</th>
                            <th class="p-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#B6ADA3]/20 text-sm">
                        @forelse($links as $i => $item)
                            <tr class="hover:bg-[#FAF7F2]/60 transition-colors">
                                <td class="p-4 text-[#5F6F5B] font-semibold">{{ $i + 1 }}</td>
                                <td class="p-4 font-semibold text-[#5F6F5B]">{{ $item->nama }}</td>
                                <td class="p-4 text-xs text-[#5F6F5B]">{{ $item->phone_display }}</td>
                                <td class="p-4">
                                    <span
                                        class="px-2.5 py-1 rounded-full text-xs font-semibold
                                    @if ($item->wa_sent) bg-[#EBF2EC] text-[#2E4B33] border border-[#A3B7A6]/60
                                    @else bg-[#FAF7F2] text-[#5F6F5B] border border-[#B6ADA3]/40 @endif">
                                        {{ $item->wa_sent ? 'Terkirim' : 'Belum' }}
                                    </span>
                                </td>
                                <td class="p-4 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="{{ $item->url }}" target="_blank" title="Kirim WA"
                                            class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl bg-white text-[#5F6F5B] hover:bg-green-50 border border-[#B6ADA3]/40 text-xs shadow-xs font-semibold transition-colors">
                                            <i class="fa-brands fa-whatsapp text-green-600" aria-hidden="true"></i> Kirim WA
                                        </a>
                                        <form action="{{ route('guests.wa-sent', $item->id) }}" method="POST">
                                            @csrf
                                            <button type="submit"
                                                title="{{ $item->wa_sent ? 'Tandai belum terkirim' : 'Tandai terkirim' }}"
                                                class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl text-xs shadow-xs font-semibold transition-colors cursor-pointer
                                            @if ($item->wa_sent) bg-white text-[#5F6F5B] hover:bg-[#FAF7F2] border border-[#B6ADA3]/40
                                            @else bg-[#5F6F5B] text-white hover:bg-[#4A5B47] @endif">
                                                <i class="fa-solid {{ $item->wa_sent ? 'fa-circle-check' : 'fa-circle' }}" aria-hidden="true"></i>
                                                {{ $item->wa_sent ? 'Belum' : 'Terkirim' }}
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-10 text-[#5F6F5B] text-sm">
                                    Tidak ada tamu dengan nomor telepon.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Modal Edit Pesan Undangan WA -->
        <div x-show="templateModalOpen" x-transition.opacity x-cloak
            @keydown.escape.window="templateModalOpen = false"
            class="fixed inset-0 z-50 bg-[#5F6F5B]/40 backdrop-blur-md flex items-center justify-center p-4">
            <div x-outside="templateModalOpen = false"
                class="bg-white w-full max-w-lg p-6 rounded-2xl border border-[#B6ADA3]/40 shadow-2xl space-y-4 max-h-[90vh] overflow-y-auto text-[#5F6F5B]">
                <div class="flex items-center justify-between border-b border-[#B6ADA3]/30 pb-3">
                    <h2 class="text-lg font-bold font-serif-title text-[#5F6F5B]">Edit Pesan Undangan WA</h2>
                    <button @click="templateModalOpen = false"
                        class="text-[#5F6F5B] hover:text-[#8B3A47] cursor-pointer" aria-label="Tutup modal"><i
                            class="fa-solid fa-xmark" aria-hidden="true"></i></button>
                </div>

                <form action="{{ route('guests.wa-template.update') }}" method="POST" class="space-y-4">
                    @csrf
                    @method('PUT')

                    <div>
                        <label for="wa-template-input" class="text-xs font-semibold text-[#5F6F5B] mb-1 flex items-center gap-1.5">Isi Pesan *
                            <i class="fa-solid fa-circle-info text-[#5F6F5B] hover:text-[#8B3A47] cursor-help"
                                aria-hidden="true"
                                title="Tulis *teks* diapit bintang untuk huruf tebal (bold) di WhatsApp"></i>
                        </label>
                        <textarea name="wa_template" id="wa-template-input" rows="12" required
                            aria-label="Isi Pesan Undangan WhatsApp"
                            class="w-full bg-[#FAF7F2] border border-[#B6ADA3]/40 text-sm text-[#5F6F5B] px-3 py-2 rounded-xl focus:border-[#D8A7B1] focus:outline-none">{{ $waTemplate }}</textarea>
                        <p class="text-[11px] text-[#5F6F5B] mt-1">Placeholder:
                            <code>{nama tamu}</code> <code>{mempelai}</code> <code>{tanggal}</code>
                            <code>{waktu}</code> <code>{tempat}</code> <code>{link}</code>
                        </p>
                    </div>

                    <div class="flex justify-between items-center gap-3 pt-3 border-t border-[#B6ADA3]/30">
                        <button type="button" id="wa-template-reset"
                            class="min-h-[44px] px-5 py-2.5 rounded-xl bg-white text-[#5F6F5B] hover:bg-[#FAF7F2] border border-[#B6ADA3]/40 text-xs font-semibold shadow-xs cursor-pointer">Kembalikan
                            Default</button>
                        <div class="flex gap-3">
                            <button type="button" @click="templateModalOpen = false"
                                class="min-h-[44px] px-4 py-2.5 text-xs text-[#5F6F5B] hover:text-[#8B3A47] cursor-pointer font-semibold">Batal</button>
                            <button type="submit"
                                class="btn-primary-rose min-h-[44px] px-5 py-2.5 rounded-xl text-xs cursor-pointer font-semibold">Simpan
                                Pesan</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        <script>
            document.getElementById('wa-template-reset')?.addEventListener('click', function () {
                document.getElementById('wa-template-input').value = @js($waDefault);
            });
        </script>
    </div>
@endsection
