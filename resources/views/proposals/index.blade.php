@extends('layouts.app')

@section('title', 'Perencanaan - Lamaran')

@section('content')
    <div class="space-y-6" x-data="{
        tab: {{ Js::from(in_array(request('tab'), ['info', 'checklist', 'budget', 'guests', 'vendors'], true) ? request('tab') : 'info') }},
        modal: null,
        currentItem: {},
        vendors: {{ $pbudVendorsJson }},
        selectedVendorId: '',
        get availableVendors() {
            const cat = this.currentItem.category;
            return cat && this.vendors[cat] ? this.vendors[cat] : [];
        },
        fillFromVendor() {
            const vendor = this.availableVendors.find(v => v.id == this.selectedVendorId);
            if (vendor) {
                this.currentItem.vendor_id = vendor.id;
                this.currentItem.item_name = vendor.package;
                this.currentItem.actual_cost = vendor.price;
            }
        },
        init() {
            // Nilai tanggal baru ditulis x-model setelah currentItem terisi,
            // jadi tampilan flatpickr disegarkan lagi di sini.
            this.$watch('currentItem', () => window.syncDatepickersSoon());
        }
    }">

        {{-- Header --}}
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-white p-5 rounded-2xl border border-[#B6ADA3]/35 shadow-xs">
            <div>
                <h1 class="text-xl md:text-2xl font-bold font-serif-title text-[#5F6F5B]">Lamaran</h1>
                <p class="text-xs text-[#5F6F5B]/70 mt-1">Kelola seluruh persiapan acara lamaran sebelum hari pernikahan.</p>
            </div>
            @if ($event->event_date)
                <div class="text-right">
                    <div class="text-xs text-[#B6ADA3]">Tanggal Lamaran</div>
                    <div class="text-base font-bold text-[#5F6F5B]">{{ $event->event_date->format('d M Y') }}</div>
                </div>
            @endif
        </div>

        {{-- Ringkasan --}}
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
            <div class="bg-white p-4 rounded-2xl border border-[#B6ADA3]/35 shadow-xs">
                <div class="text-xs text-[#5F6F5B]/80 mb-1">Checklist Selesai</div>
                <div class="text-lg font-bold text-[#5F6F5B]"><span id="pcnt-done">{{ $checklistSummary['done'] }}</span> /
                    <span id="pcnt-total">{{ $checklistSummary['total'] }}</span>
                </div>
            </div>
            <div class="bg-white p-4 rounded-2xl border border-[#B6ADA3]/35 shadow-xs">
                <div class="text-xs text-[#5F6F5B]/80 mb-1">Estimasi Tamu</div>
                <div class="text-lg font-bold text-[#5F6F5B]">{{ $guestSummary['pax'] }} orang</div>
            </div>
            <div class="bg-white p-4 rounded-2xl border border-[#B6ADA3]/35 shadow-xs">
                <div class="text-xs text-[#5F6F5B]/80 mb-1">Rencana Budget Lamaran</div>
                <div class="text-lg font-bold text-[#5F6F5B]">Rp {{ number_format($budgetSummary['planned'], 0, ',', '.') }}
                </div>
            </div>
            <div class="bg-white p-4 rounded-2xl border border-[#D8A7B1]/50 shadow-xs">
                <div class="text-xs text-[#5F6F5B]/80 mb-1">Total Biaya Persiapan</div>
                <div class="text-lg font-bold text-[#C2757F]">Rp {{ number_format($weddingBudgetTotal, 0, ',', '.') }}</div>
                <div class="text-[10px] text-[#B6ADA3] mt-0.5">Resepsi + lamaran</div>
            </div>
        </div>

        {{-- Tab --}}
        <div class="bg-white rounded-2xl border border-[#B6ADA3]/35 shadow-xs overflow-hidden">
            <div class="flex flex-wrap gap-1 border-b border-[#B6ADA3]/25 px-3 pt-3">
                @foreach ([
            'info' => 'Informasi Acara',
            'checklist' => 'Checklist',
            'budget' => 'Budget',
            'guests' => 'Keluarga & Tamu',
            'vendors' => 'Vendor',
        ] as $key => $label)
                    <button @click="tab = '{{ $key }}'"
                        :class="tab === '{{ $key }}' ? 'bg-[#5F6F5B] text-white' :
                            'text-[#5F6F5B] hover:bg-[#5F6F5B]/8'"
                        class="px-3.5 py-2 rounded-t-lg text-xs font-semibold transition cursor-pointer mb-[-1px]">
                        {{ $label }}
                    </button>
                @endforeach
            </div>

            <div class="p-5">
                {{-- ============ INFORMASI ACARA ============ --}}
                <div x-show="tab === 'info'" x-cloak>
                    <form method="POST" action="{{ route('proposals.event.update') }}">
                        @csrf
                        @method('PUT')
                        <div class="grid md:grid-cols-2 gap-4">
                            <div>
                                <label class="form-label">Tanggal</label>
                                <input type="text" name="event_date"
                                    value="{{ old('event_date', $event->event_date?->format('Y-m-d')) }}"
                                    class="form-input datepicker" placeholder="DD-MM-YYYY">
                            </div>
                            <div>
                                <label class="form-label">Waktu</label>
                                <input type="time" name="event_time" value="{{ old('event_time', $event->event_time) }}"
                                    class="form-input">
                            </div>
                            <div>
                                <label class="form-label">Lokasi</label>
                                <input type="text" name="location" value="{{ old('location', $event->location) }}"
                                    placeholder="Alamat acara lamaran" class="form-input">
                            </div>
                            <div>
                                <label class="form-label">Tema</label>
                                <input type="text" name="theme" value="{{ old('theme', $event->theme) }}"
                                    placeholder="Tema acara" class="form-input">
                            </div>
                            <div class="md:col-span-2">
                                <label class="form-label">Catatan Kebutuhan
                                    Acara</label>
                                <textarea name="notes" rows="4" placeholder="Kebutuhan khusus acara lamaran..." class="form-input">{{ old('notes', $event->notes) }}</textarea>
                            </div>
                        </div>
                        <div class="mt-4 flex justify-end">
                            <button class="btn-primary-rose px-5 py-2.5 rounded-xl text-sm cursor-pointer">Simpan
                                Informasi</button>
                        </div>
                    </form>
                </div>

                {{-- ============ CHECKLIST ============ --}}
                <div x-show="tab === 'checklist'" x-cloak>
                    <div class="flex items-center justify-between mb-4">
                        <div class="text-xs text-[#B6ADA3]" id="pcnt-overdue-wrap">
                            @if ($checklistSummary['overdue'] > 0)
                                <span class="text-[#C2757F] font-semibold"><span
                                        id="pcnt-overdue">{{ $checklistSummary['overdue'] }}</span> item melewati
                                    deadline</span>
                            @else
                                Semua item dalam jadwal.
                            @endif
                        </div>
                        <button @click="modal = 'checklist'; currentItem = { status: 'Todo', priority: 'Medium' }"
                            class="btn-primary-rose px-4 py-2 rounded-xl text-xs flex items-center gap-2 cursor-pointer">
                            <i class="fa-solid fa-plus"></i> Tambah Item
                        </button>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr
                                    class="bg-[#FAF7F2] text-xs text-[#5F6F5B] uppercase tracking-wider border-b border-[#B6ADA3]/30">
                                    <th class="p-4 w-12 text-center">Status</th>
                                    <th class="p-4">Judul Checklist</th>
                                    <th class="p-4">Kategori</th>
                                    <th class="p-4">Tenggat (Deadline)</th>
                                    <th class="p-4">Prioritas</th>
                                    <th class="p-4 text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-[#B6ADA3]/20 text-sm">
                                @forelse ($checklists as $item)
                                    <tr class="hover:bg-[#FAF7F2]/60 transition-colors">
                                        <td class="p-4 text-center">
                                            <button type="button" class="cursor-pointer disabled:opacity-50"
                                                data-url="{{ route('proposals.checklists.toggle', $item->id) }}"
                                                @click="window.wpToggleProposalChecklist($el)"
                                                :aria-label="'Ubah status checklist'">
                                                <span data-icon>
                                                    @if ($item->status === 'Done')
                                                        <i class="fa-solid fa-circle-check text-[#5F6F5B] text-lg"></i>
                                                    @elseif($item->status === 'Progress')
                                                        <i class="fa-solid fa-spinner text-[#D8A7B1] text-lg"></i>
                                                    @else
                                                        <i
                                                            class="fa-regular fa-circle text-[#B6ADA3] text-lg hover:text-[#5F6F5B]"></i>
                                                    @endif
                                                </span>
                                            </button>
                                        </td>
                                        <td data-title
                                            class="p-4 font-semibold text-[#5F6F5B] {{ $item->status === 'Done' ? 'line-through text-[#B6ADA3]' : '' }}">
                                            {{ $item->title }}
                                        </td>
                                        <td class="p-4">
                                            <span
                                                class="text-xs bg-[#FAF7F2] text-[#5F6F5B] font-medium px-2.5 py-1 rounded-lg border border-[#B6ADA3]/40">
                                                {{ $item->category }}
                                            </span>
                                        </td>
                                        <td class="p-4 text-xs text-[#5F6F5B]">
                                            {{ $item->deadline?->format('d M Y') ?: '—' }}
                                        </td>
                                        <td class="p-4">
                                            <span
                                                class="text-xs font-semibold px-2.5 py-1 rounded-full
                                            @if ($item->priority === 'High') bg-[#D8A7B1]/30 text-[#5F6F5B] border border-[#A3B7A6]/40
                                            @elseif($item->priority === 'Medium') bg-[#FAF7F2] text-[#5F6F5B] border border-[#A3B7A6]/40
                                            @else bg-[#A3B7A6]/20 text-[#5F6F5B] border border-[#A3B7A6]/40 @endif">
                                                {{ $item->priority }}
                                            </span>
                                        </td>
                                        <td class="p-4 text-right">
                                            <div class="flex items-center justify-end gap-2">
                                                <button @click="modal = 'checklist'; currentItem = {{ Js::from($item) }}"
                                                    title="Edit" class="btn-icon"><i
                                                        class="fa-solid fa-pen-to-square"></i></button>
                                                <form id="del-pchk-{{ $item->id }}" method="POST"
                                                    action="{{ route('proposals.checklists.destroy', $item) }}">
                                                    @csrf @method('DELETE')
                                                    <button type="button" title="Hapus" class="btn-icon"
                                                        onclick="confirmDelete('del-pchk-{{ $item->id }}', '{{ addslashes($item->title) }}')"><i
                                                            class="fa-solid fa-trash"></i></button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center py-10 text-[#B6ADA3] text-sm">Belum ada item
                                            checklist.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- ============ BUDGET ============ --}}
                <div x-show="tab === 'budget'" x-cloak>
                    <div class="flex items-center justify-between mb-4">
                        <div class="text-xs text-[#B6ADA3]">
                            Rencana Rp {{ number_format($budgetSummary['planned'], 0, ',', '.') }} ·
                            Realisasi Rp {{ number_format($budgetSummary['actual'], 0, ',', '.') }} ·
                            <span
                                class="{{ $budgetSummary['variance'] >= 0 ? 'text-[#5F6F5B] font-semibold' : 'text-[#C2757F] font-semibold' }}">
                                {{ $budgetSummary['variance'] >= 0 ? 'Hemat' : 'Over' }} Rp
                                {{ number_format(abs($budgetSummary['variance']), 0, ',', '.') }}
                            </span>
                        </div>
                        <button @click="modal = 'budget'; currentItem = {}"
                            class="btn-primary-rose px-4 py-2 rounded-xl text-xs flex items-center gap-2 cursor-pointer">
                            <i class="fa-solid fa-plus"></i> Tambah Budget
                        </button>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr
                                    class="bg-[#FAF7F2] text-xs text-[#5F6F5B] uppercase tracking-wider border-b border-[#B6ADA3]/30">
                                    <th class="p-4">Kategori</th>
                                    <th class="p-4">Nama Item</th>
                                    <th class="p-4">Vendor</th>
                                    <th class="p-4">Planned Budget</th>
                                    <th class="p-4">Actual Cost</th>
                                    <th class="p-4">Selisih / Remaining</th>
                                    <th class="p-4">Catatan</th>
                                    <th class="p-4 text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-[#B6ADA3]/20 text-sm">
                                @forelse ($budgets as $item)
                                    @php
                                        $diff = $item->planned_budget - $item->actual_cost;
                                    @endphp
                                    <tr class="hover:bg-[#FAF7F2]/60 transition-colors">
                                        <td class="p-4 font-semibold text-[#5F6F5B] text-xs">
                                            <span class="bg-[#FAF7F2] px-2.5 py-1 rounded-lg border border-[#B6ADA3]/40">
                                                {{ $item->category }}
                                            </span>
                                        </td>
                                        <td class="p-4 font-medium text-[#5F6F5B]">{{ $item->item_name }}</td>
                                        <td class="p-4 text-xs text-[#5F6F5B]/80">
                                            @if ($item->vendor)
                                                <span class="font-medium">{{ $item->vendor->name }}</span>
                                            @else
                                                <span class="text-[#B6ADA3]">-</span>
                                            @endif
                                        </td>
                                        <td class="p-4 text-[#5F6F5B]/80">Rp
                                            {{ number_format($item->planned_budget, 0, ',', '.') }}
                                        </td>
                                        <td class="p-4 font-semibold text-[#5F6F5B]">Rp
                                            {{ number_format($item->actual_cost, 0, ',', '.') }}</td>
                                        <td class="p-4 font-bold {{ $diff >= 0 ? 'text-[#5F6F5B]' : 'text-[#5F6F5B]' }}">
                                            Rp {{ number_format($diff, 0, ',', '.') }}
                                        </td>
                                        <td class="p-4 text-xs text-[#B6ADA3] max-w-xs truncate">{{ $item->notes ?? '-' }}
                                        </td>
                                        <td class="p-4 text-right">
                                            <div class="flex items-center justify-end gap-2">
                                                <button @click="modal = 'budget'; currentItem = {{ Js::from($item) }}"
                                                    title="Edit" class="btn-icon"><i
                                                        class="fa-solid fa-pen-to-square"></i></button>
                                                <form id="del-pbud-{{ $item->id }}" method="POST"
                                                    action="{{ route('proposals.budgets.destroy', $item) }}">
                                                    @csrf @method('DELETE')
                                                    <button type="button" title="Hapus" class="btn-icon"
                                                        onclick="confirmDelete('del-pbud-{{ $item->id }}', '{{ addslashes($item->item_name) }}')"><i
                                                            class="fa-solid fa-trash"></i></button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center py-10 text-[#B6ADA3] text-sm">Belum ada
                                            budget lamaran.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- ============ KELUARGA & TAMU ============ --}}
                <div x-show="tab === 'guests'" x-cloak>
                    <div class="flex items-center justify-between mb-4">
                        <div class="text-xs text-[#B6ADA3]">
                            {{ $guestSummary['rows'] }} nama · {{ $guestSummary['pax'] }} orang ·
                            {{ $guestSummary['attend'] }} hadir
                        </div>
                        <button @click="modal = 'guest'; currentItem = { attendance_status: 'Pending' }"
                            class="btn-primary-rose px-4 py-2 rounded-xl text-xs flex items-center gap-2 cursor-pointer">
                            <i class="fa-solid fa-plus"></i> Tambah Tamu
                        </button>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr
                                    class="bg-[#FAF7F2] text-xs text-[#5F6F5B] uppercase tracking-wider border-b border-[#B6ADA3]/30">
                                    <th class="p-4">Nama</th>
                                    <th class="p-4">Hubungan</th>
                                    <th class="p-4">Kategori</th>
                                    <th class="p-4">Kehadiran</th>
                                    <th class="p-4 text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-[#B6ADA3]/20 text-sm">
                                @forelse ($guests as $item)
                                    <tr class="hover:bg-[#FAF7F2]/60 transition-colors">
                                        <td class="p-4 font-medium text-[#5F6F5B]">
                                            {{ $item->title ? $item->title . ' ' : '' }}{{ $item->name }}
                                        </td>
                                        <td class="p-4 text-xs text-[#B6ADA3]">{{ $item->relation ?: '-' }}</td>
                                        <td class="p-4 text-xs text-[#B6ADA3]">{{ $item->category ?: '-' }}</td>
                                        <td class="p-4">
                                            <span @class([
                                                'px-2.5 py-1 rounded-full text-xs font-semibold',
                                                'bg-[#5F6F5B]/12 text-[#5F6F5B]' => $item->attendance_status === 'Attend',
                                                'bg-[#C2757F]/15 text-[#C2757F]' => $item->attendance_status === 'Decline',
                                                'bg-[#B6ADA3]/20 text-[#5F6F5B]' =>
                                                    $item->attendance_status !== 'Attend' &&
                                                    $item->attendance_status !== 'Decline',
                                            ])>{{ $item->attendance_status }}</span>
                                        </td>
                                        <td class="p-4 text-right">
                                            <div class="flex items-center justify-end gap-2">
                                                <button @click="modal = 'guest'; currentItem = {{ Js::from($item) }}"
                                                    title="Edit" class="btn-icon"><i
                                                        class="fa-solid fa-pen-to-square"></i></button>
                                                <form id="del-pgst-{{ $item->id }}" method="POST"
                                                    action="{{ route('proposals.guests.destroy', $item) }}">
                                                    @csrf @method('DELETE')
                                                    <button type="button" title="Hapus" class="btn-icon"
                                                        onclick="confirmDelete('del-pgst-{{ $item->id }}', '{{ addslashes($item->name) }}')"><i
                                                            class="fa-solid fa-trash"></i></button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center py-10 text-[#B6ADA3] text-sm">Belum ada tamu
                                            lamaran.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- ============ VENDOR ============ --}}
                <div x-show="tab === 'vendors'" x-cloak>
                    <p class="text-xs text-[#B6ADA3] mb-4">Vendor yang ditandai dipakai untuk acara lamaran. Sisanya tetap
                        tersedia di modul Vendor umum.</p>

                    <div class="grid md:grid-cols-2 gap-5">
                        <div>
                            <h3 class="text-xs font-bold text-[#5F6F5B] mb-2 uppercase tracking-wider">Vendor Lamaran
                                ({{ $lamaranVendors->count() }})</h3>
                            <div class="space-y-2">
                                @forelse ($lamaranVendors as $vendor)
                                    <div class="flex items-center justify-between bg-[#5F6F5B]/5 rounded-xl px-3 py-2.5">
                                        <div>
                                            <div class="text-sm font-medium text-[#5F6F5B]">{{ $vendor->name }}</div>
                                            <div class="text-[10px] text-[#B6ADA3]">{{ $vendor->category }} ·
                                                {{ $vendor->booking_status }}</div>
                                        </div>
                                        <form method="POST" action="{{ route('proposals.vendors.toggle', $vendor) }}">
                                            @csrf
                                            <button
                                                class="text-[10px] text-[#C2757F] hover:underline cursor-pointer">Lepas</button>
                                        </form>
                                    </div>
                                @empty
                                    <div class="text-xs text-[#B6ADA3] py-4">Belum ada vendor lamaran.</div>
                                @endforelse
                            </div>
                        </div>

                        <div>
                            <h3 class="text-xs font-bold text-[#5F6F5B] mb-2 uppercase tracking-wider">Kandidat Vendor
                                ({{ $otherVendors->count() }})</h3>
                            <div class="space-y-2">
                                @forelse ($otherVendors as $vendor)
                                    <div
                                        class="flex items-center justify-between bg-white border border-[#B6ADA3]/25 rounded-xl px-3 py-2.5">
                                        <div>
                                            <div class="text-sm font-medium text-[#5F6F5B]">{{ $vendor->name }}</div>
                                            <div class="text-[10px] text-[#B6ADA3]">{{ $vendor->category }} ·
                                                {{ $vendor->booking_status }}</div>
                                        </div>
                                        <form method="POST" action="{{ route('proposals.vendors.toggle', $vendor) }}">
                                            @csrf
                                            <button
                                                class="text-[10px] text-[#5F6F5B] hover:underline cursor-pointer">Pakai</button>
                                        </form>
                                    </div>
                                @empty
                                    <div class="text-xs text-[#B6ADA3] py-4">Semua vendor sudah dipakai untuk lamaran.
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        {{-- ================= MODAL: CHECKLIST ================= --}}
        <div x-show="modal === 'checklist'" x-cloak
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4"
            @keydown.escape.window="modal = null">
            <div x-outside="modal = null"
                class="bg-white w-full max-w-lg p-6 rounded-2xl border border-[#B6ADA3]/40 shadow-2xl space-y-4">
                <div class="flex items-center justify-between border-b border-[#B6ADA3]/30 pb-3">
                    <h3 class="text-lg font-bold font-serif-title text-[#5F6F5B]"
                        x-text="currentItem?.id ? 'Edit Item Checklist' : 'Tambah Item Checklist'"></h3>
                    <button @click="modal = null" class="text-[#B6ADA3] hover:text-[#5F6F5B] cursor-pointer"><i
                            class="fa-solid fa-xmark"></i></button>
                </div>
                <form
                    :action="currentItem?.id ? '{{ route('proposals.checklists.update', 0) }}'.replace('/0/', '/' + currentItem
                        .id + '/') : '{{ route('proposals.checklists.store') }}'"
                    method="POST" class="space-y-4">
                    @csrf
                    <template x-if="currentItem?.id"><input type="hidden" name="_method" value="PUT"></template>
                    <div>
                        <label class="block text-xs font-semibold text-[#5F6F5B] mb-1">Judul Checklist *</label>
                        <input type="text" name="title" x-model="currentItem.title" required
                            class="w-full bg-[#FAF7F2] border border-[#B6ADA3]/40 text-sm text-[#5F6F5B] px-3 py-2 rounded-xl focus:border-[#D8A7B1] focus:outline-none"
                            placeholder="Misal: Booking Catering">
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-[#5F6F5B] mb-1">Kategori *</label>
                            <select name="category" x-model="currentItem.category" required
                                class="w-full bg-[#FAF7F2] border border-[#B6ADA3]/40 text-sm text-[#5F6F5B] px-3 py-2 rounded-xl focus:border-[#D8A7B1] focus:outline-none">
                                <option value="">-- Pilih Kategori --</option>
                                @foreach (\App\Models\DropdownOption::getOptions('checklist_category') as $opt)
                                    <option value="{{ $opt }}">{{ $opt }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-[#5F6F5B] mb-1">Prioritas *</label>
                            <select name="priority" x-model="currentItem.priority" required
                                class="w-full bg-[#FAF7F2] border border-[#B6ADA3]/40 text-sm text-[#5F6F5B] px-3 py-2 rounded-xl focus:border-[#D8A7B1] focus:outline-none">
                                @foreach (\App\Models\DropdownOption::getOptions('checklist_priority') as $opt)
                                    <option value="{{ $opt }}">{{ $opt }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-[#5F6F5B] mb-1">Tenggat Waktu</label>
                            <input type="text" name="deadline" x-model="currentItem.deadline"
                                placeholder="DD-MM-YYYY"
                                class="w-full bg-[#FAF7F2] border border-[#B6ADA3]/40 text-sm text-[#5F6F5B] px-3 py-2 rounded-xl focus:border-[#D8A7B1] focus:outline-none datepicker">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-[#5F6F5B] mb-1">Status *</label>
                            <select name="status" x-model="currentItem.status" required
                                class="w-full bg-[#FAF7F2] border border-[#B6ADA3]/40 text-sm text-[#5F6F5B] px-3 py-2 rounded-xl focus:border-[#D8A7B1] focus:outline-none">
                                @foreach (\App\Models\DropdownOption::getOptions('checklist_status') as $opt)
                                    <option value="{{ $opt }}">{{ $opt }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-[#5F6F5B] mb-1">Deskripsi / Catatan</label>
                        <textarea name="notes" x-model="currentItem.notes" rows="3"
                            class="w-full bg-[#FAF7F2] border border-[#B6ADA3]/40 text-sm text-[#5F6F5B] px-3 py-2 rounded-xl focus:border-[#D8A7B1] focus:outline-none"></textarea>
                    </div>
                    <div class="flex justify-end gap-3 pt-3 border-t border-[#B6ADA3]/30">
                        <button type="button" @click="modal = null"
                            class="px-4 py-2 text-xs text-[#B6ADA3] hover:text-[#5F6F5B] cursor-pointer">Batal</button>
                        <button type="submit" class="btn-primary-rose px-5 py-2 rounded-xl text-xs cursor-pointer">Simpan
                            Checklist</button>
                    </div>
                </form>
            </div>
        </div>

        {{-- ================= MODAL: BUDGET ================= --}}
        <div x-show="modal === 'budget'" x-cloak
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4"
            @keydown.escape.window="modal = null">
            <div x-outside="modal = null"
                class="bg-white w-full max-w-lg p-6 rounded-2xl border border-[#B6ADA3]/40 shadow-2xl space-y-4">
                <div class="flex items-center justify-between border-b border-[#B6ADA3]/30 pb-3">
                    <h3 class="text-lg font-bold font-serif-title text-[#5F6F5B]"
                        x-text="currentItem?.id ? 'Edit Item Anggaran' : 'Tambah Item Anggaran'"></h3>
                    <button @click="modal = null" class="text-[#B6ADA3] hover:text-[#5F6F5B] cursor-pointer"><i
                            class="fa-solid fa-xmark"></i></button>
                </div>
                <form
                    :action="currentItem?.id ? '{{ route('proposals.budgets.update', 0) }}'.replace('/0/', '/' + currentItem
                        .id + '/') : '{{ route('proposals.budgets.store') }}'"
                    method="POST" class="space-y-4">
                    @csrf
                    <template x-if="currentItem?.id"><input type="hidden" name="_method" value="PUT"></template>
                    <input type="hidden" name="vendor_id" x-model="currentItem.vendor_id">

                    <div>
                        <label class="block text-xs font-semibold text-[#5F6F5B] mb-1">Kategori Budget *</label>
                        <select name="category" x-model="currentItem.category" @change="selectedVendorId = ''" required
                            class="w-full bg-[#FAF7F2] border border-[#B6ADA3]/40 text-sm text-[#5F6F5B] px-3 py-2 rounded-xl focus:border-[#D8A7B1] focus:outline-none">
                            <option value="">-- Pilih Kategori --</option>
                            @foreach (\App\Models\DropdownOption::getOptions('checklist_category') as $opt)
                                <option value="{{ $opt }}">{{ $opt }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div x-show="availableVendors.length > 0" x-cloak>
                        <label class="block text-xs font-semibold text-[#5F6F5B] mb-1">Pilih Vendor Terkait</label>
                        <select x-model="selectedVendorId" @change="fillFromVendor()"
                            class="w-full bg-[#FAF7F2] border border-[#B6ADA3]/40 text-sm text-[#5F6F5B] px-3 py-2 rounded-xl focus:border-[#D8A7B1] focus:outline-none">
                            <option value="">-- Pilih Vendor --</option>
                            <template x-for="v in availableVendors" :key="v.id">
                                <option :value="v.id" x-text="v.name"></option>
                            </template>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-[#5F6F5B] mb-1">Nama Item / Pengeluaran *</label>
                        <input type="text" name="item_name" x-model="currentItem.item_name" required
                            class="w-full bg-[#FAF7F2] border border-[#B6ADA3]/40 text-sm text-[#5F6F5B] px-3 py-2 rounded-xl focus:border-[#D8A7B1] focus:outline-none">
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-[#5F6F5B] mb-1">Planned Budget (Rp) *</label>
                            <input type="text" name="planned_budget" inputmode="numeric" required
                                :value="wpMoney(currentItem.planned_budget)"
                                @input="currentItem.planned_budget = wpDigits($event.target.value); wpClamp($event.target, wpMoney)"
                                class="w-full bg-[#FAF7F2] border border-[#B6ADA3]/40 text-sm text-[#5F6F5B] px-3 py-2 rounded-xl focus:border-[#D8A7B1] focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-[#5F6F5B] mb-1">Actual Cost (Rp) *</label>
                            <input type="text" name="actual_cost" inputmode="numeric" required
                                :value="wpMoney(currentItem.actual_cost)"
                                @input="currentItem.actual_cost = wpDigits($event.target.value); wpClamp($event.target, wpMoney)"
                                class="w-full bg-[#FAF7F2] border border-[#B6ADA3]/40 text-sm text-[#5F6F5B] px-3 py-2 rounded-xl focus:border-[#D8A7B1] focus:outline-none">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-[#5F6F5B] mb-1">Catatan / Invoice Link</label>
                        <textarea name="notes" x-model="currentItem.notes" rows="2"
                            class="w-full bg-[#FAF7F2] border border-[#B6ADA3]/40 text-sm text-[#5F6F5B] px-3 py-2 rounded-xl focus:border-[#D8A7B1] focus:outline-none"></textarea>
                    </div>

                    <div class="flex justify-end gap-3 pt-3 border-t border-[#B6ADA3]/30">
                        <button type="button" @click="modal = null"
                            class="px-4 py-2 text-xs text-[#B6ADA3] hover:text-[#5F6F5B] cursor-pointer">Batal</button>
                        <button type="submit" class="btn-primary-rose px-5 py-2 rounded-xl text-xs cursor-pointer">Simpan
                            Anggaran</button>
                    </div>
                </form>
            </div>
        </div>

        {{-- ================= MODAL: TAMU ================= --}}
        <div x-show="modal === 'guest'" x-cloak
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4"
            @keydown.escape.window="modal = null">
            <div x-outside="modal = null"
                class="bg-white rounded-2xl shadow-xl w-full max-w-lg max-h-[90vh] overflow-y-auto">
                <div class="p-5 border-b border-[#B6ADA3]/25 flex items-center justify-between">
                    <h3 class="font-bold text-[#5F6F5B]" x-text="currentItem?.id ? 'Edit Tamu' : 'Tambah Tamu'"></h3>
                    <button @click="modal = null" class="text-[#B6ADA3] hover:text-[#5F6F5B] cursor-pointer"><i
                            class="fa-solid fa-xmark"></i></button>
                </div>
                <form
                    :action="currentItem?.id ? '{{ route('proposals.guests.update', 0) }}'.replace('/0/', '/' + currentItem.id +
                        '/') : '{{ route('proposals.guests.store') }}'"
                    method="POST" class="p-5 space-y-3">
                    @csrf
                    <template x-if="currentItem?.id"><input type="hidden" name="_method" value="PUT"></template>
                    <div class="grid grid-cols-[80px_1fr] gap-3">
                        <div>
                            <label class="form-label">Gelar</label>
                            <select name="title" x-model="currentItem.title" class="form-input">
                                <option value="">-- Gelar --</option>
                                @foreach (\App\Models\DropdownOption::getOptions('guest_title') as $opt)
                                    <option value="{{ $opt }}">{{ $opt }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="form-label">Nama <span class="text-[#C2757F]">*</span></label>
                            <input type="text" name="name" x-model="currentItem.name" required class="form-input">
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="form-label">Hubungan</label>
                            <input type="text" name="relation" x-model="currentItem.relation"
                                placeholder="mis. Keluarga pasangan dekat" class="form-input">
                        </div>
                        <div>
                            <label class="form-label">Kategori</label>
                            <select name="category" x-model="currentItem.category" class="form-input">
                                <option value="">-- Pilih Kategori --</option>
                                @foreach (\App\Models\DropdownOption::getOptions('guest_category') as $opt)
                                    <option value="{{ $opt }}">{{ $opt }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-span-2">
                            <label class="form-label">Kehadiran</label>
                            <select name="attendance_status" x-model="currentItem.attendance_status" class="form-input">
                                @foreach (\App\Models\DropdownOption::getOptions('guest_status') as $opt)
                                    <option value="{{ $opt }}">{{ $opt }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="flex justify-end gap-2 pt-2">
                        <button type="button" @click="modal = null"
                            class="px-4 py-2 rounded-xl text-sm bg-[#B6ADA3]/15 text-[#5F6F5B] cursor-pointer">Batal</button>
                        <button class="btn-primary-rose px-5 py-2 rounded-xl text-sm cursor-pointer">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Toggle status checklist lamaran tanpa reload: POST via fetch (jawab JSON),
        // lalu update ikon, coretan judul, dan ringkasan. Mirror
        // window.wpToggleChecklist di halaman checklist resepsi.
        window.wpToggleProposalChecklist = async function(btn) {
            const url = btn.dataset.url;
            const token = document.querySelector('meta[name="csrf-token"]')?.content;
            btn.disabled = true;
            try {
                const res = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': token,
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    credentials: 'same-origin',
                });
                if (!res.ok) throw new Error('Toggle gagal: ' + res.status);
                const d = await res.json();

                btn.querySelector('[data-icon]').innerHTML = d.status === 'Done' ?
                    '<i class="fa-solid fa-circle-check text-[#5F6F5B] text-lg"></i>' :
                    '<i class="fa-regular fa-circle text-[#B6ADA3] text-lg hover:text-[#5F6F5B]"></i>';

                const row = btn.closest('tr');
                const title = row ? row.querySelector('[data-title]') : null;
                if (title) {
                    title.classList.toggle('line-through', d.status === 'Done');
                    title.classList.toggle('text-[#B6ADA3]', d.status === 'Done');
                }

                const set = (id, v) => {
                    const el = document.getElementById(id);
                    if (el) el.textContent = v;
                };
                set('pcnt-done', d.doneCount);
                set('pcnt-total', d.total);

                const wrap = document.getElementById('pcnt-overdue-wrap');
                if (wrap) {
                    wrap.innerHTML = d.overdueCount > 0 ?
                        `<span class="text-[#C2757F] font-semibold">${d.overdueCount} item melewati deadline</span>` :
                        'Semua item dalam jadwal.';
                }
            } catch (e) {
                console.error(e);
            } finally {
                btn.disabled = false;
            }
        };
    </script>
@endsection
