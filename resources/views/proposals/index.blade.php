@extends('layouts.app')

@section('title', 'Lamaran - Informasi, Checklist, Budget & Tamu')

@section('content')
    <div class="space-y-6" x-data="{
        tab: '{{ request('tab', 'info') }}',
        modal: null,
        currentItem: {},
    }">

        {{-- Header --}}
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold font-serif-title">Lamaran</h1>
                <p class="text-xs text-[#B6ADA3] mt-1">Kelola seluruh persiapan acara lamaran sebelum hari pernikahan.</p>
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
                <div class="text-lg font-bold text-[#5F6F5B]">{{ $checklistSummary['done'] }} / {{ $checklistSummary['total'] }}</div>
            </div>
            <div class="bg-white p-4 rounded-2xl border border-[#B6ADA3]/35 shadow-xs">
                <div class="text-xs text-[#5F6F5B]/80 mb-1">Estimasi Tamu</div>
                <div class="text-lg font-bold text-[#5F6F5B]">{{ $guestSummary['pax'] }} orang</div>
            </div>
            <div class="bg-white p-4 rounded-2xl border border-[#B6ADA3]/35 shadow-xs">
                <div class="text-xs text-[#5F6F5B]/80 mb-1">Rencana Budget Lamaran</div>
                <div class="text-lg font-bold text-[#5F6F5B]">Rp {{ number_format($budgetSummary['planned'], 0, ',', '.') }}</div>
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
                    'rundown' => 'Rundown',
                    'docs' => 'Dokumentasi',
                ] as $key => $label)
                    <button @click="tab = '{{ $key }}'"
                        :class="tab === '{{ $key }}' ? 'bg-[#5F6F5B] text-white' : 'text-[#5F6F5B] hover:bg-[#5F6F5B]/8'"
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
                                <label class="block text-xs font-semibold text-[#5F6F5B] mb-1.5">Tanggal</label>
                                <input type="date" name="event_date" value="{{ old('event_date', $event->event_date?->format('Y-m-d')) }}"
                                    class="form-input w-full rounded-xl border border-[#B6ADA3]/40 px-3 py-2 text-sm">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-[#5F6F5B] mb-1.5">Waktu</label>
                                <input type="time" name="event_time" value="{{ old('event_time', $event->event_time) }}"
                                    class="form-input w-full rounded-xl border border-[#B6ADA3]/40 px-3 py-2 text-sm">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-[#5F6F5B] mb-1.5">Lokasi</label>
                                <input type="text" name="location" value="{{ old('location', $event->location) }}"
                                    placeholder="Alamat acara lamaran" class="form-input w-full rounded-xl border border-[#B6ADA3]/40 px-3 py-2 text-sm">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-[#5F6F5B] mb-1.5">Tema</label>
                                <input type="text" name="theme" value="{{ old('theme', $event->theme) }}"
                                    placeholder="Tema acara" class="form-input w-full rounded-xl border border-[#B6ADA3]/40 px-3 py-2 text-sm">
                            </div>
                            <div class="md:col-span-2">
                                <label class="block text-xs font-semibold text-[#5F6F5B] mb-1.5">Catatan Kebutuhan Acara</label>
                                <textarea name="notes" rows="4" placeholder="Kebutuhan khusus acara lamaran..."
                                    class="form-input w-full rounded-xl border border-[#B6ADA3]/40 px-3 py-2 text-sm">{{ old('notes', $event->notes) }}</textarea>
                            </div>
                        </div>
                        <div class="mt-4 flex justify-end">
                            <button class="btn-primary-rose px-5 py-2.5 rounded-xl text-sm cursor-pointer">Simpan Informasi</button>
                        </div>
                    </form>
                </div>

                {{-- ============ CHECKLIST ============ --}}
                <div x-show="tab === 'checklist'" x-cloak>
                    <div class="flex items-center justify-between mb-4">
                        <div class="text-xs text-[#B6ADA3]">
                            @if ($checklistSummary['overdue'] > 0)
                                <span class="text-[#C2757F] font-semibold">{{ $checklistSummary['overdue'] }} item melewati deadline</span>
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
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="text-left text-[10px] uppercase tracking-wider text-[#B6ADA3] border-b border-[#B6ADA3]/25">
                                    <th class="py-2 font-semibold">Item</th>
                                    <th class="py-2 font-semibold">Kategori</th>
                                    <th class="py-2 font-semibold">Deadline</th>
                                    <th class="py-2 font-semibold">Prioritas</th>
                                    <th class="py-2 font-semibold">Status</th>
                                    <th class="py-2 font-semibold text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($checklists as $item)
                                    <tr class="border-b border-[#B6ADA3]/15 last:border-0">
                                        <td class="py-2.5 font-medium text-[#5F6F5B]">{{ $item->title }}</td>
                                        <td class="py-2.5 text-xs text-[#B6ADA3]">{{ $item->category ?: '-' }}</td>
                                        <td class="py-2.5 text-xs">
                                            <span class="{{ $item->isOverdue() ? 'text-[#C2757F] font-semibold' : 'text-[#B6ADA3]' }}">
                                                {{ $item->deadline?->format('d M Y') ?: '-' }}
                                            </span>
                                        </td>
                                        <td class="py-2.5 text-xs text-[#B6ADA3]">{{ $item->priority ?: '-' }}</td>
                                        <td class="py-2.5">
                                            @php $label = $item->statusLabel(); @endphp
                                            <span @class([
                                                'px-2 py-0.5 rounded-full text-[10px] font-semibold',
                                                'bg-[#5F6F5B]/12 text-[#5F6F5B]' => $label === 'Done',
                                                'bg-[#C2757F]/15 text-[#C2757F]' => $label === 'Terlambat',
                                                'bg-[#B6ADA3]/20 text-[#5F6F5B]' => ! in_array($label, ['Done', 'Terlambat']),
                                            ])>{{ $label }}</span>
                                        </td>
                                        <td class="py-2.5 text-right space-x-2 whitespace-nowrap">
                                            <button @click="modal = 'checklist'; currentItem = {{ Js::from($item) }}"
                                                class="text-[#5F6F5B] hover:text-[#C2757F] text-xs cursor-pointer"><i class="fa-solid fa-pen"></i></button>
                                            <form method="POST" action="{{ route('proposals.checklists.destroy', $item) }}" class="inline"
                                                onsubmit="return confirm('Hapus item checklist ini?')">
                                                @csrf @method('DELETE')
                                                <button class="text-[#C2757F] hover:text-red-700 text-xs cursor-pointer"><i class="fa-solid fa-trash"></i></button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="6" class="py-8 text-center text-xs text-[#B6ADA3]">Belum ada item checklist.</td></tr>
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
                            <span class="{{ $budgetSummary['variance'] >= 0 ? 'text-[#5F6F5B] font-semibold' : 'text-[#C2757F] font-semibold' }}">
                                {{ $budgetSummary['variance'] >= 0 ? 'Hemat' : 'Over' }} Rp {{ number_format(abs($budgetSummary['variance']), 0, ',', '.') }}
                            </span>
                        </div>
                        <button @click="modal = 'budget'; currentItem = {}"
                            class="btn-primary-rose px-4 py-2 rounded-xl text-xs flex items-center gap-2 cursor-pointer">
                            <i class="fa-solid fa-plus"></i> Tambah Budget
                        </button>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="text-left text-[10px] uppercase tracking-wider text-[#B6ADA3] border-b border-[#B6ADA3]/25">
                                    <th class="py-2 font-semibold">Item</th>
                                    <th class="py-2 font-semibold">Kategori</th>
                                    <th class="py-2 font-semibold">Vendor</th>
                                    <th class="py-2 font-semibold text-right">Rencana</th>
                                    <th class="py-2 font-semibold text-right">Aktual</th>
                                    <th class="py-2 font-semibold text-right">Selisih</th>
                                    <th class="py-2 font-semibold text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($budgets as $item)
                                    <tr class="border-b border-[#B6ADA3]/15 last:border-0">
                                        <td class="py-2.5 font-medium text-[#5F6F5B]">{{ $item->item_name }}</td>
                                        <td class="py-2.5 text-xs text-[#B6ADA3]">{{ $item->category }}</td>
                                        <td class="py-2.5 text-xs text-[#B6ADA3]">{{ $item->vendor?->name ?: '-' }}</td>
                                        <td class="py-2.5 text-xs text-right">Rp {{ number_format($item->planned_budget, 0, ',', '.') }}</td>
                                        <td class="py-2.5 text-xs text-right">Rp {{ number_format($item->actual_cost, 0, ',', '.') }}</td>
                                        <td @class([
                                            'py-2.5 text-xs text-right font-semibold',
                                            'text-[#C2757F]' => $item->isOverBudget(),
                                            'text-[#5F6F5B]' => ! $item->isOverBudget(),
                                        ])>
                                            {{ $item->variance() >= 0 ? '+' : '-' }} Rp {{ number_format(abs($item->variance()), 0, ',', '.') }}
                                        </td>
                                        <td class="py-2.5 text-right space-x-2 whitespace-nowrap">
                                            <button @click="modal = 'budget'; currentItem = {{ Js::from($item) }}"
                                                class="text-[#5F6F5B] hover:text-[#C2757F] text-xs cursor-pointer"><i class="fa-solid fa-pen"></i></button>
                                            <form method="POST" action="{{ route('proposals.budgets.destroy', $item) }}" class="inline"
                                                onsubmit="return confirm('Hapus budget lamaran ini?')">
                                                @csrf @method('DELETE')
                                                <button class="text-[#C2757F] hover:text-red-700 text-xs cursor-pointer"><i class="fa-solid fa-trash"></i></button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="7" class="py-8 text-center text-xs text-[#B6ADA3]">Belum ada budget lamaran.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- ============ KELUARGA & TAMU ============ --}}
                <div x-show="tab === 'guests'" x-cloak>
                    <div class="flex items-center justify-between mb-4">
                        <div class="text-xs text-[#B6ADA3]">
                            {{ $guestSummary['rows'] }} nama · {{ $guestSummary['pax'] }} orang · {{ $guestSummary['attend'] }} hadir
                        </div>
                        <button @click="modal = 'guest'; currentItem = { guest_count: 1, attendance_status: 'Pending' }"
                            class="btn-primary-rose px-4 py-2 rounded-xl text-xs flex items-center gap-2 cursor-pointer">
                            <i class="fa-solid fa-plus"></i> Tambah Tamu
                        </button>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="text-left text-[10px] uppercase tracking-wider text-[#B6ADA3] border-b border-[#B6ADA3]/25">
                                    <th class="py-2 font-semibold">Nama</th>
                                    <th class="py-2 font-semibold">Hubungan</th>
                                    <th class="py-2 font-semibold">Kategori</th>
                                    <th class="py-2 font-semibold text-center">Jumlah</th>
                                    <th class="py-2 font-semibold">Kehadiran</th>
                                    <th class="py-2 font-semibold text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($guests as $item)
                                    <tr class="border-b border-[#B6ADA3]/15 last:border-0">
                                        <td class="py-2.5 font-medium text-[#5F6F5B]">
                                            {{ $item->title ? $item->title . ' ' : '' }}{{ $item->name }}
                                        </td>
                                        <td class="py-2.5 text-xs text-[#B6ADA3]">{{ $item->relation ?: '-' }}</td>
                                        <td class="py-2.5 text-xs text-[#B6ADA3]">{{ $item->category ?: '-' }}</td>
                                        <td class="py-2.5 text-xs text-center">{{ $item->guest_count }}</td>
                                        <td class="py-2.5">
                                            <span @class([
                                                'px-2 py-0.5 rounded-full text-[10px] font-semibold',
                                                'bg-[#5F6F5B]/12 text-[#5F6F5B]' => $item->attendance_status === 'Attend',
                                                'bg-[#C2757F]/15 text-[#C2757F]' => $item->attendance_status === 'Decline',
                                                'bg-[#B6ADA3]/20 text-[#5F6F5B]' => $item->attendance_status !== 'Attend' && $item->attendance_status !== 'Decline',
                                            ])>{{ $item->attendance_status }}</span>
                                        </td>
                                        <td class="py-2.5 text-right space-x-2 whitespace-nowrap">
                                            <button @click="modal = 'guest'; currentItem = {{ Js::from($item) }}"
                                                class="text-[#5F6F5B] hover:text-[#C2757F] text-xs cursor-pointer"><i class="fa-solid fa-pen"></i></button>
                                            <form method="POST" action="{{ route('proposals.guests.destroy', $item) }}" class="inline"
                                                onsubmit="return confirm('Hapus tamu ini?')">
                                                @csrf @method('DELETE')
                                                <button class="text-[#C2757F] hover:text-red-700 text-xs cursor-pointer"><i class="fa-solid fa-trash"></i></button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="6" class="py-8 text-center text-xs text-[#B6ADA3]">Belum ada tamu lamaran.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- ============ VENDOR ============ --}}
                <div x-show="tab === 'vendors'" x-cloak>
                    <p class="text-xs text-[#B6ADA3] mb-4">Vendor yang ditandai dipakai untuk acara lamaran. Sisanya tetap tersedia di modul Vendor umum.</p>

                    <div class="grid md:grid-cols-2 gap-5">
                        <div>
                            <h3 class="text-xs font-bold text-[#5F6F5B] mb-2 uppercase tracking-wider">Vendor Lamaran ({{ $lamaranVendors->count() }})</h3>
                            <div class="space-y-2">
                                @forelse ($lamaranVendors as $vendor)
                                    <div class="flex items-center justify-between bg-[#5F6F5B]/5 rounded-xl px-3 py-2.5">
                                        <div>
                                            <div class="text-sm font-medium text-[#5F6F5B]">{{ $vendor->name }}</div>
                                            <div class="text-[10px] text-[#B6ADA3]">{{ $vendor->category }} · {{ $vendor->booking_status }}</div>
                                        </div>
                                        <form method="POST" action="{{ route('proposals.vendors.toggle', $vendor) }}">
                                            @csrf
                                            <button class="text-[10px] text-[#C2757F] hover:underline cursor-pointer">Lepas</button>
                                        </form>
                                    </div>
                                @empty
                                    <div class="text-xs text-[#B6ADA3] py-4">Belum ada vendor lamaran.</div>
                                @endforelse
                            </div>
                        </div>

                        <div>
                            <h3 class="text-xs font-bold text-[#5F6F5B] mb-2 uppercase tracking-wider">Kandidat Vendor ({{ $otherVendors->count() }})</h3>
                            <div class="space-y-2">
                                @forelse ($otherVendors as $vendor)
                                    <div class="flex items-center justify-between bg-white border border-[#B6ADA3]/25 rounded-xl px-3 py-2.5">
                                        <div>
                                            <div class="text-sm font-medium text-[#5F6F5B]">{{ $vendor->name }}</div>
                                            <div class="text-[10px] text-[#B6ADA3]">{{ $vendor->category }} · {{ $vendor->booking_status }}</div>
                                        </div>
                                        <form method="POST" action="{{ route('proposals.vendors.toggle', $vendor) }}">
                                            @csrf
                                            <button class="text-[10px] text-[#5F6F5B] hover:underline cursor-pointer">Pakai</button>
                                        </form>
                                    </div>
                                @empty
                                    <div class="text-xs text-[#B6ADA3] py-4">Semua vendor sudah dipakai untuk lamaran.</div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ============ RUNDOWN ============ --}}
                <div x-show="tab === 'rundown'" x-cloak>
                    <p class="text-xs text-[#B6ADA3] mb-4">Rundown lamaran dikelola di modul Rundown. Yang tampil di bawah hanya agenda acara lamaran.</p>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="text-left text-[10px] uppercase tracking-wider text-[#B6ADA3] border-b border-[#B6ADA3]/25">
                                    <th class="py-2 font-semibold">Waktu</th>
                                    <th class="py-2 font-semibold">Kegiatan</th>
                                    <th class="py-2 font-semibold">PIC</th>
                                    <th class="py-2 font-semibold">Lokasi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($rundown as $item)
                                    <tr class="border-b border-[#B6ADA3]/15 last:border-0">
                                        <td class="py-2.5 text-xs text-[#5F6F5B] font-semibold">{{ $item->time }}</td>
                                        <td class="py-2.5 font-medium text-[#5F6F5B]">{{ $item->activity }}</td>
                                        <td class="py-2.5 text-xs text-[#B6ADA3]">{{ $item->pic ?: '-' }}</td>
                                        <td class="py-2.5 text-xs text-[#B6ADA3]">{{ $item->location ?: '-' }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="4" class="py-8 text-center text-xs text-[#B6ADA3]">Belum ada rundown lamaran.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- ============ DOKUMENTASI ============ --}}
                <div x-show="tab === 'docs'" x-cloak>
                    <p class="text-xs text-[#B6ADA3] mb-4">Dokumentasi dan moodboard lamaran dikelola di modul Moodboard.</p>
                    <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-3">
                        @forelse ($moodboards as $doc)
                            <div class="border border-[#B6ADA3]/25 rounded-xl p-3">
                                <div class="text-sm font-medium text-[#5F6F5B]">{{ $doc->title }}</div>
                                <div class="text-[10px] text-[#B6ADA3] mt-0.5">{{ $doc->category }}</div>
                                @if ($doc->description)
                                    <div class="text-xs text-[#B6ADA3] mt-2 line-clamp-3">{{ $doc->description }}</div>
                                @endif
                            </div>
                        @empty
                            <div class="text-xs text-[#B6ADA3] py-4 col-span-full">Belum ada dokumentasi lamaran.</div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        {{-- ================= MODAL: CHECKLIST ================= --}}
        <div x-show="modal === 'checklist'" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4"
            @click.self="modal = null" @keydown.escape.window="modal = null">
            <div class="bg-white rounded-2xl shadow-xl w-full max-w-lg max-h-[90vh] overflow-y-auto">
                <div class="p-5 border-b border-[#B6ADA3]/25 flex items-center justify-between">
                    <h3 class="font-bold text-[#5F6F5B]" x-text="currentItem?.id ? 'Edit Item Checklist' : 'Tambah Item Checklist'"></h3>
                    <button @click="modal = null" class="text-[#B6ADA3] hover:text-[#5F6F5B] cursor-pointer"><i class="fa-solid fa-xmark"></i></button>
                </div>
                <form :action="currentItem?.id ? '{{ route('proposals.checklists.update', 0) }}'.replace('/0/', '/' + currentItem.id + '/') : '{{ route('proposals.checklists.store') }}'"
                    method="POST" class="p-5 space-y-3">
                    @csrf
                    <template x-if="currentItem?.id"><input type="hidden" name="_method" value="PUT"></template>
                    <div>
                        <label class="block text-xs font-semibold text-[#5F6F5B] mb-1.5">Item <span class="text-[#C2757F]">*</span></label>
                        <input type="text" name="title" x-model="currentItem.title" required
                            placeholder="mis. Booking dekorasi lamaran"
                            class="form-input w-full rounded-xl border border-[#B6ADA3]/40 px-3 py-2 text-sm">
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-[#5F6F5B] mb-1.5">Kategori</label>
                            <select name="category" x-model="currentItem.category" class="form-input w-full rounded-xl border border-[#B6ADA3]/40 px-3 py-2 text-sm">
                                <option value="">-</option>
                                @foreach (\App\Models\DropdownOption::getOptions('checklist_category') as $opt)
                                    <option value="{{ $opt }}">{{ $opt }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-[#5F6F5B] mb-1.5">Deadline</label>
                            <input type="date" name="deadline" x-model="currentItem.deadline" class="form-input w-full rounded-xl border border-[#B6ADA3]/40 px-3 py-2 text-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-[#5F6F5B] mb-1.5">Prioritas</label>
                            <select name="priority" x-model="currentItem.priority" class="form-input w-full rounded-xl border border-[#B6ADA3]/40 px-3 py-2 text-sm">
                                @foreach (\App\Models\DropdownOption::getOptions('checklist_priority') as $opt)
                                    <option value="{{ $opt }}">{{ $opt }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-[#5F6F5B] mb-1.5">Status</label>
                            <select name="status" x-model="currentItem.status" class="form-input w-full rounded-xl border border-[#B6ADA3]/40 px-3 py-2 text-sm">
                                @foreach (\App\Models\DropdownOption::getOptions('checklist_status') as $opt)
                                    <option value="{{ $opt }}">{{ $opt }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-[#5F6F5B] mb-1.5">Catatan</label>
                        <textarea name="notes" x-model="currentItem.notes" rows="2" class="form-input w-full rounded-xl border border-[#B6ADA3]/40 px-3 py-2 text-sm"></textarea>
                    </div>
                    <div class="flex justify-end gap-2 pt-2">
                        <button type="button" @click="modal = null" class="px-4 py-2 rounded-xl text-sm bg-[#B6ADA3]/15 text-[#5F6F5B] cursor-pointer">Batal</button>
                        <button class="btn-primary-rose px-5 py-2 rounded-xl text-sm cursor-pointer">Simpan</button>
                    </div>
                </form>
            </div>
        </div>

        {{-- ================= MODAL: BUDGET ================= --}}
        <div x-show="modal === 'budget'" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4"
            @click.self="modal = null" @keydown.escape.window="modal = null">
            <div class="bg-white rounded-2xl shadow-xl w-full max-w-lg max-h-[90vh] overflow-y-auto">
                <div class="p-5 border-b border-[#B6ADA3]/25 flex items-center justify-between">
                    <h3 class="font-bold text-[#5F6F5B]" x-text="currentItem?.id ? 'Edit Budget Lamaran' : 'Tambah Budget Lamaran'"></h3>
                    <button @click="modal = null" class="text-[#B6ADA3] hover:text-[#5F6F5B] cursor-pointer"><i class="fa-solid fa-xmark"></i></button>
                </div>
                <form :action="currentItem?.id ? '{{ route('proposals.budgets.update', 0) }}'.replace('/0/', '/' + currentItem.id + '/') : '{{ route('proposals.budgets.store') }}'"
                    method="POST" class="p-5 space-y-3">
                    @csrf
                    <template x-if="currentItem?.id"><input type="hidden" name="_method" value="PUT"></template>
                    <div>
                        <label class="block text-xs font-semibold text-[#5F6F5B] mb-1.5">Nama Item <span class="text-[#C2757F]">*</span></label>
                        <input type="text" name="item_name" x-model="currentItem.item_name" required
                            placeholder="mis. Sewa venue lamaran" class="form-input w-full rounded-xl border border-[#B6ADA3]/40 px-3 py-2 text-sm">
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-[#5F6F5B] mb-1.5">Kategori <span class="text-[#C2757F]">*</span></label>
                            <select name="category" x-model="currentItem.category" required class="form-input w-full rounded-xl border border-[#B6ADA3]/40 px-3 py-2 text-sm">
                                @foreach (\App\Models\DropdownOption::getOptions('budget_category') as $opt)
                                    <option value="{{ $opt }}">{{ $opt }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-[#5F6F5B] mb-1.5">Vendor</label>
                            <select name="vendor_id" x-model="currentItem.vendor_id" class="form-input w-full rounded-xl border border-[#B6ADA3]/40 px-3 py-2 text-sm">
                                <option value="">-</option>
                                @foreach ($wedding->vendors()->orderBy('name')->get() as $v)
                                    <option value="{{ $v->id }}">{{ $v->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-[#5F6F5B] mb-1.5">Rencana <span class="text-[#C2757F]">*</span></label>
                            <input type="number" name="planned_budget" x-model="currentItem.planned_budget" required min="0" step="0.01"
                                class="form-input w-full rounded-xl border border-[#B6ADA3]/40 px-3 py-2 text-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-[#5F6F5B] mb-1.5">Aktual</label>
                            <input type="number" name="actual_cost" x-model="currentItem.actual_cost" min="0" step="0.01"
                                class="form-input w-full rounded-xl border border-[#B6ADA3]/40 px-3 py-2 text-sm">
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-[#5F6F5B] mb-1.5">Catatan</label>
                        <textarea name="notes" x-model="currentItem.notes" rows="2" class="form-input w-full rounded-xl border border-[#B6ADA3]/40 px-3 py-2 text-sm"></textarea>
                    </div>
                    <div class="flex justify-end gap-2 pt-2">
                        <button type="button" @click="modal = null" class="px-4 py-2 rounded-xl text-sm bg-[#B6ADA3]/15 text-[#5F6F5B] cursor-pointer">Batal</button>
                        <button class="btn-primary-rose px-5 py-2 rounded-xl text-sm cursor-pointer">Simpan</button>
                    </div>
                </form>
            </div>
        </div>

        {{-- ================= MODAL: TAMU ================= --}}
        <div x-show="modal === 'guest'" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4"
            @click.self="modal = null" @keydown.escape.window="modal = null">
            <div class="bg-white rounded-2xl shadow-xl w-full max-w-lg max-h-[90vh] overflow-y-auto">
                <div class="p-5 border-b border-[#B6ADA3]/25 flex items-center justify-between">
                    <h3 class="font-bold text-[#5F6F5B]" x-text="currentItem?.id ? 'Edit Tamu' : 'Tambah Tamu'"></h3>
                    <button @click="modal = null" class="text-[#B6ADA3] hover:text-[#5F6F5B] cursor-pointer"><i class="fa-solid fa-xmark"></i></button>
                </div>
                <form :action="currentItem?.id ? '{{ route('proposals.guests.update', 0) }}'.replace('/0/', '/' + currentItem.id + '/') : '{{ route('proposals.guests.store') }}'"
                    method="POST" class="p-5 space-y-3">
                    @csrf
                    <template x-if="currentItem?.id"><input type="hidden" name="_method" value="PUT"></template>
                    <div class="grid grid-cols-[80px_1fr] gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-[#5F6F5B] mb-1.5">Sapaan</label>
                            <select name="title" x-model="currentItem.title" class="form-input w-full rounded-xl border border-[#B6ADA3]/40 px-3 py-2 text-sm">
                                <option value="">-</option>
                                @foreach (\App\Models\DropdownOption::getOptions('guest_title') as $opt)
                                    <option value="{{ $opt }}">{{ $opt }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-[#5F6F5B] mb-1.5">Nama <span class="text-[#C2757F]">*</span></label>
                            <input type="text" name="name" x-model="currentItem.name" required
                                class="form-input w-full rounded-xl border border-[#B6ADA3]/40 px-3 py-2 text-sm">
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-[#5F6F5B] mb-1.5">Hubungan</label>
                            <input type="text" name="relation" x-model="currentItem.relation" placeholder="mis. Keluarga pasangan dekat"
                                class="form-input w-full rounded-xl border border-[#B6ADA3]/40 px-3 py-2 text-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-[#5F6F5B] mb-1.5">Kategori</label>
                            <select name="category" x-model="currentItem.category" class="form-input w-full rounded-xl border border-[#B6ADA3]/40 px-3 py-2 text-sm">
                                <option value="">-</option>
                                @foreach (\App\Models\DropdownOption::getOptions('guest_category') as $opt)
                                    <option value="{{ $opt }}">{{ $opt }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-[#5F6F5B] mb-1.5">Jumlah</label>
                            <input type="number" name="guest_count" x-model="currentItem.guest_count" min="1"
                                class="form-input w-full rounded-xl border border-[#B6ADA3]/40 px-3 py-2 text-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-[#5F6F5B] mb-1.5">Kehadiran</label>
                            <select name="attendance_status" x-model="currentItem.attendance_status" class="form-input w-full rounded-xl border border-[#B6ADA3]/40 px-3 py-2 text-sm">
                                @foreach (\App\Models\DropdownOption::getOptions('guest_status') as $opt)
                                    <option value="{{ $opt }}">{{ $opt }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-[#5F6F5B] mb-1.5">Telepon</label>
                        <input type="text" name="phone" x-model="currentItem.phone" class="form-input w-full rounded-xl border border-[#B6ADA3]/40 px-3 py-2 text-sm">
                    </div>
                    <div class="flex justify-end gap-2 pt-2">
                        <button type="button" @click="modal = null" class="px-4 py-2 rounded-xl text-sm bg-[#B6ADA3]/15 text-[#5F6F5B] cursor-pointer">Batal</button>
                        <button class="btn-primary-rose px-5 py-2 rounded-xl text-sm cursor-pointer">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection