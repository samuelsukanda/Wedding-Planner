@extends('layouts.app')

@section('title', 'Perencanaan - Persyaratan Nikah')

@section('content')
    <div class="space-y-6" x-data="{
        modal: null,
        currentItem: {},
        init() {
            // Nilai tanggal baru ditulis x-model setelah currentItem terisi,
            // jadi tampilan flatpickr disegarkan lagi di sini.
            this.$watch('currentItem', () => window.syncDatepickersSoon());
        }
    }">

        {{-- Header --}}
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-white p-5 rounded-2xl border border-[#B6ADA3]/35 shadow-xs">
            <div>
                <h1 class="text-xl md:text-2xl font-bold font-serif-title text-[#5F6F5B]">Persyaratan Nikah</h1>
                <p class="text-xs text-[#5F6F5B]/70 mt-1">Kelola dokumen dan persyaratan administrasi pernikahan agar seluruh
                    kebutuhan terpenuhi tepat waktu.</p>
            </div>
            <div class="flex gap-2">
                @if (count($templates))
                    <button @click="modal = 'template'"
                        class="px-4 py-2.5 rounded-xl text-sm border border-[#B6ADA3]/50 text-[#5F6F5B] hover:bg-[#FAF7F2] flex items-center gap-2 cursor-pointer">
                        <i class="fa-solid fa-file-import"></i> Tambah dari Template
                    </button>
                @endif
                <button @click="modal = 'doc'; currentItem = { status: 'Belum Lengkap' }"
                    class="btn-primary-rose px-4 py-2.5 rounded-xl text-sm flex items-center justify-center gap-2 cursor-pointer">
                    <i class="fa-solid fa-plus"></i> Tambah Dokumen
                </button>
            </div>
        </div>

        {{-- Ringkasan --}}
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
            <div class="bg-white p-4 rounded-2xl border border-[#B6ADA3]/35 shadow-xs">
                <div class="text-xs text-[#5F6F5B]/80 mb-1">Kelengkapan</div>
                <div class="text-lg font-bold text-[#5F6F5B]">{{ $summary['complete'] }} / {{ $summary['total'] }}</div>
                <div class="h-1.5 bg-[#5F6F5B]/10 rounded-full mt-2 overflow-hidden">
                    <div class="h-full bg-[#5F6F5B] rounded-full" style="width: {{ $percent }}%"></div>
                </div>
            </div>
            <div class="bg-white p-4 rounded-2xl border border-[#B6ADA3]/35 shadow-xs">
                <div class="text-xs text-[#5F6F5B]/80 mb-1">Sedang Diproses</div>
                <div class="text-lg font-bold text-[#5F6F5B]">{{ $requirements->where('status', 'Diproses')->count() }}
                </div>
            </div>
            <div class="bg-white p-4 rounded-2xl border border-[#D8A7B1]/50 shadow-xs">
                <div class="text-xs text-[#5F6F5B]/80 mb-1">Terlambat</div>
                <div class="text-lg font-bold text-[#C2757F]">{{ $summary['overdue'] }}</div>
            </div>
            <div class="bg-white p-4 rounded-2xl border border-[#D8A7B1]/50 shadow-xs">
                <div class="text-xs text-[#5F6F5B]/80 mb-1">Jatuh Tempo 7 Hari</div>
                <div class="text-lg font-bold text-[#C2757F]">{{ $summary['dueSoon'] }}</div>
            </div>
        </div>

        {{-- Tabel dokumen --}}
        <div class="bg-white rounded-2xl border border-[#B6ADA3]/35 shadow-xs overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr
                            class="bg-[#FAF7F2] text-xs text-[#5F6F5B] uppercase tracking-wider border-b border-[#B6ADA3]/30">
                            <th class="p-4">Dokumen</th>
                            <th class="p-4">PIC</th>
                            <th class="p-4">Kontak</th>
                            <th class="p-4">Deadline</th>
                            <th class="p-4">Status</th>
                            <th class="p-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#B6ADA3]/20 text-sm">
                        @forelse ($requirements as $item)
                            @php $label = $item->statusLabel(); @endphp
                            <tr class="hover:bg-[#FAF7F2]/60 transition-colors">
                                <td class="p-4 font-medium text-[#5F6F5B]">
                                    {{ $item->name }}
                                    @if ($item->document_file)
                                        <a href="{{ $item->document_file }}" target="_blank" rel="noopener noreferrer"
                                            class="block text-[10px] text-[#C2757F] hover:underline">
                                            <i class="fa-solid fa-paperclip"></i> Lihat file
                                        </a>
                                    @endif
                                </td>
                                <td class="p-4 text-xs text-[#B6ADA3]">{{ $item->pic_name ?: '-' }}</td>
                                <td class="p-4 text-xs text-[#B6ADA3]">{{ $item->phone ?: '-' }}</td>
                                <td @class([
                                    'p-4 text-xs',
                                    'text-[#C2757F] font-semibold' => $item->isOverdue(),
                                    'text-[#B6ADA3]' => !$item->isOverdue(),
                                ])>
                                    {{ $item->deadline?->format('d M Y') ?: '-' }}
                                </td>
                                <td class="p-4">
                                    <span @class([
                                        'px-2.5 py-1 rounded-full text-xs font-semibold',
                                        'bg-[#5F6F5B]/12 text-[#5F6F5B]' => $label === 'Lengkap',
                                        'bg-[#C2757F]/15 text-[#C2757F]' => $label === 'Terlambat',
                                        'bg-[#B6ADA3]/20 text-[#5F6F5B]' => !in_array($label, [
                                            'Lengkap',
                                            'Terlambat',
                                        ]),
                                    ])>{{ $label }}</span>
                                </td>
                                <td class="p-4 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="{{ route('documents.history', $item) }}" class="btn-icon" title="Riwayat">
                                            <i class="fa-solid fa-clock-rotate-left"></i>
                                        </a>
                                        <button @click="modal = 'doc'; currentItem = {{ Js::from($item) }}" title="Edit"
                                            class="btn-icon">
                                            <i class="fa-solid fa-pen-to-square"></i>
                                        </button>
                                        <form id="del-doc-{{ $item->id }}" method="POST"
                                            action="{{ route('documents.destroy', $item) }}">
                                            @csrf @method('DELETE')
                                            <button type="button" title="Hapus" class="btn-icon"
                                                onclick="confirmDelete('del-doc-{{ $item->id }}', '{{ addslashes($item->name) }}')">
                                                <i class="fa-solid fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-12 text-center">
                                    <i class="fa-solid fa-file-circle-check text-[#B6ADA3] text-2xl mb-3"></i>
                                    <div class="text-xs text-[#B6ADA3]">Belum ada dokumen. Tambahkan manual atau pakai
                                        template.</div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Riwayat terbaru (PRD Fitur: Riwayat) --}}
        @if ($recentHistories->isNotEmpty())
            <div class="bg-white rounded-2xl border border-[#B6ADA3]/35 shadow-xs p-5">
                <h2 class="text-base font-bold text-[#5F6F5B] mb-4 flex items-center gap-2">
                    <i class="fa-solid fa-clock-rotate-left text-[#D8A7B1]"></i> Riwayat Perubahan
                </h2>
                <div class="space-y-2.5">
                    @foreach ($recentHistories as $history)
                        <div class="flex items-start gap-3 text-xs">
                            <div class="w-1.5 h-1.5 rounded-full bg-[#D8A7B1] mt-1.5 shrink-0"></div>
                            <div>
                                <span class="font-medium text-[#5F6F5B]">{{ $history->requirement?->name }}</span>
                                <span class="text-[#B6ADA3]"> —
                                    {{ $history->note ?: 'Status: ' . $history->status }}</span>
                                <div class="text-[10px] text-[#B6ADA3] mt-0.5">
                                    {{ $history->created_at->format('d M Y H:i') }}</div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- Modal: Tambah / Edit dokumen --}}
        <div x-show="modal === 'doc'" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4"
            @keydown.escape.window="modal = null">
            <div x-outside="modal = null"
                class="bg-white rounded-2xl shadow-xl w-full max-w-lg max-h-[90vh] overflow-y-auto">
                <div class="p-5 border-b border-[#B6ADA3]/25 flex items-center justify-between">
                    <h3 class="font-bold text-[#5F6F5B]"
                        x-text="currentItem && currentItem.id ? 'Edit Dokumen' : 'Tambah Dokumen'"></h3>
                    <button @click="modal = null" class="text-[#B6ADA3] hover:text-[#5F6F5B] cursor-pointer"><i
                            class="fa-solid fa-xmark"></i></button>
                </div>

                <form
                    :action="(currentItem && currentItem.id) ? '{{ url('/documents') }}/' + currentItem.id:
                        '{{ route('documents.store') }}'"
                    method="POST" class="p-5 space-y-3">
                    @csrf
                    <template x-if="currentItem && currentItem.id"><input type="hidden" name="_method"
                            value="PUT"></template>

                    <div>
                        <label class="form-label">Nama Dokumen <span class="text-[#C2757F]">*</span></label>
                        <input type="text" name="name" x-model="currentItem.name" required
                            placeholder="mis. Akta Lahir Suami" class="form-input">
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="form-label">PIC</label>
                            <input type="text" name="pic_name" x-model="currentItem.pic_name" class="form-input">
                        </div>
                        <div>
                            <label class="form-label">Kontak</label>
                            <input type="text" name="phone" x-model="currentItem.phone" class="form-input">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="form-label">Deadline</label>
                            <input type="text" name="deadline" x-model="currentItem.deadline"
                                class="form-input datepicker">
                        </div>
                        <div>
                            <label class="form-label">Status</label>
                            <select name="status" x-model="currentItem.status" class="form-input">
                                @foreach ($statuses as $opt)
                                    <option value="{{ $opt }}">{{ $opt }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="form-label">URL File Dokumen</label>
                        <input type="url" name="document_file" x-model="currentItem.document_file"
                            placeholder="https://..." class="form-input">
                    </div>

                    <div>
                        <label class="form-label">Catatan</label>
                        <textarea name="notes" x-model="currentItem.notes" rows="2" class="form-input"></textarea>
                    </div>

                    <div class="flex justify-end gap-2 pt-2">
                        <button type="button" @click="modal = null"
                            class="px-4 py-2 rounded-xl text-sm bg-[#B6ADA3]/15 text-[#5F6F5B] cursor-pointer">Batal</button>
                        <button class="btn-primary-rose px-5 py-2 rounded-xl text-sm cursor-pointer">Simpan</button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Modal: pilih template --}}
        <div x-show="modal === 'template'" x-cloak
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4"
            @keydown.escape.window="modal = null">
            <div x-outside="modal = null"
                class="bg-white rounded-2xl shadow-xl w-full max-w-md max-h-[90vh] overflow-y-auto">
                <div class="p-5 border-b border-[#B6ADA3]/25 flex items-center justify-between">
                    <h3 class="font-bold text-[#5F6F5B]">Tambah dari Template</h3>
                    <button @click="modal = null" class="text-[#B6ADA3] hover:text-[#5F6F5B] cursor-pointer"><i
                            class="fa-solid fa-xmark"></i></button>
                </div>
                <div class="p-5 space-y-2">
                    @foreach ($templates as $template)
                        <form method="POST" action="{{ route('documents.templates.apply', $template) }}">
                            @csrf
                            <div class="flex items-center justify-between bg-[#5F6F5B]/5 rounded-xl px-4 py-3">
                                <div>
                                    <div class="text-sm font-medium text-[#5F6F5B]">{{ $template->name }}</div>
                                    <div class="text-[10px] text-[#B6ADA3]">
                                        {{ count($template->items ?? []) }} dokumen
                                        @if ($template->description)
                                            · {{ $template->description }}
                                        @endif
                                    </div>
                                </div>
                                <button class="text-xs text-[#5F6F5B] hover:underline cursor-pointer">Terapkan</button>
                            </div>
                        </form>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
@endsection
