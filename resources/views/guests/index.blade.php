@extends('layouts.app')

@section('title', 'Perencanaan - Guest Management')

@section('content')
    <div class="space-y-6" x-data="{
        modalOpen: false,
        editMode: false,
        currentItem: {},
        titles: {{ json_encode($guestTitles) }},
        paxMap: {{ json_encode($categoryPax) }},
        autoFillPax() {
            if (!this.editMode && this.currentItem.category && this.paxMap[this.currentItem.category]) {
                this.currentItem.guest_count = this.paxMap[this.currentItem.category];
            }
        }
    }">
        <!-- Top Bar -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold font-serif-title">Guest Management</h1>
            </div>
            <div class="w-full md:w-auto md:flex md:items-center md:gap-2">
                <div class="grid grid-cols-3 gap-2">
                    <a href="{{ route('guests.wa-all') }}"
                        class="px-3 py-2.5 rounded-xl bg-white text-[#5F6F5B] hover:bg-green-50 border border-[#B6ADA3]/40 text-xs flex items-center justify-center gap-1.5 shadow-xs font-semibold">
                        <i class="fa-brands fa-whatsapp text-green-600"></i> Kirim WA
                    </a>
                    <a href="{{ route('guests.labels') }}" data-turbo="false"
                        class="px-3 py-2.5 rounded-xl bg-white text-[#5F6F5B] hover:bg-[#FAF7F2] border border-[#B6ADA3]/40 text-xs flex items-center justify-center gap-1.5 shadow-xs font-semibold">
                        <i class="fa-solid fa-tag text-[#D8A7B1]"></i> Label
                    </a>
                    <a href="{{ route('guests.export') }}" data-turbo="false"
                        class="px-3 py-2.5 rounded-xl bg-white text-[#5F6F5B] hover:bg-[#FAF7F2] border border-[#B6ADA3]/40 text-xs flex items-center justify-center gap-1.5 shadow-xs font-semibold">
                        <i class="fa-solid fa-file-excel text-[#D8A7B1]"></i> Excel
                    </a>
                </div>
                <button @click="modalOpen = true; editMode = false; currentItem = {}"
                    class="btn-primary-rose px-4 py-2.5 rounded-xl text-sm flex items-center justify-center gap-2 cursor-pointer w-full md:w-auto mt-2 md:mt-0">
                    <i class="fa-solid fa-plus"></i> Tambah Tamu
                </button>
            </div>
        </div>

        <!-- Summary Widgets -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-3 sm:gap-4">
            <div class="bg-white p-4 rounded-xl border border-[#B6ADA3]/35 shadow-xs">
                <div class="text-xs text-[#5F6F5B]">Total Undangan</div>
                <div class="text-xl sm:text-2xl font-bold text-[#5F6F5B]">{{ $totalGuests }}</div>
                <div class="text-xs text-[#5F6F5B] mt-1 font-semibold">{{ $totalPax }} Pax Total</div>
            </div>
            <div class="bg-white p-4 rounded-xl border border-[#B6ADA3]/35 shadow-xs">
                <div class="text-xs text-[#5F6F5B]">Konfirmasi Hadir</div>
                <div class="text-xl sm:text-2xl font-bold text-[#5F6F5B]">{{ $attendCount }}</div>
                <div class="text-xs text-[#A3B7A6] mt-1 font-semibold">RSVP Hadir</div>
            </div>
            <div class="bg-white p-4 rounded-xl border border-[#B6ADA3]/35 shadow-xs">
                <div class="text-xs text-[#5F6F5B]">Menunggu Konfirmasi</div>
                <div class="text-xl sm:text-2xl font-bold text-[#5F6F5B]">{{ $pendingCount }}</div>
                <div class="text-xs text-[#D8A7B1] mt-1 font-semibold">Pending</div>
            </div>
            <div class="bg-white p-4 rounded-xl border border-[#B6ADA3]/35 shadow-xs">
                <div class="text-xs text-[#5F6F5B]">Berhalangan</div>
                <div class="text-xl sm:text-2xl font-bold text-[#5F6F5B]">{{ $declineCount }}</div>
                <div class="text-xs text-[#5F6F5B]/80 mt-1 font-semibold">Decline</div>
            </div>
        </div>

        <!-- Search & Filter Bar -->
        <form method="GET" action="{{ route('guests.index') }}"
            class="bg-white p-4 rounded-xl border border-[#B6ADA3]/35 shadow-xs flex flex-col md:flex-row gap-3 items-center justify-between">
            <div class="w-full md:w-auto md:flex md:flex-row md:items-center md:gap-3 space-y-3 md:space-y-0">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama tamu..."
                    class="bg-[#FAF7F2] border border-[#B6ADA3]/40 text-xs text-[#5F6F5B] px-3 py-2 rounded-xl focus:border-[#D8A7B1] focus:outline-none w-full md:w-56">
                <div class="grid grid-cols-2 gap-3 md:flex md:items-center md:gap-3">
                    <select name="category" onchange="this.form.submit()"
                        class="bg-[#FAF7F2] border border-[#B6ADA3]/40 text-xs text-[#5F6F5B] px-3 py-2 rounded-xl focus:border-[#D8A7B1] focus:outline-none w-full md:w-auto">
                        <option value="">Semua Kategori</option>
                        @foreach ($categories as $cat)
                            <option value="{{ $cat }}" {{ request('category') == $cat ? 'selected' : '' }}>
                                {{ $cat }}
                            </option>
                        @endforeach
                    </select>
                    <select name="status" onchange="this.form.submit()"
                        class="bg-[#FAF7F2] border border-[#B6ADA3]/40 text-xs text-[#5F6F5B] px-3 py-2 rounded-xl focus:border-[#D8A7B1] focus:outline-none w-full md:w-auto">
                        <option value="">Semua Kehadiran</option>
                        @foreach ($statuses as $st)
                            <option value="{{ $st }}" {{ request('status') == $st ? 'selected' : '' }}>
                                {{ $st }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
            @if (request()->anyFilled(['search', 'category', 'status']))
                <a href="{{ route('guests.index') }}"
                    class="text-xs text-[#5F6F5B] hover:text-[#5F6F5B] font-semibold hover:underline">Reset Filter</a>
            @endif
        </form>

        <!-- Guest List Table -->
        <div class="bg-white rounded-2xl border border-[#B6ADA3]/35 shadow-xs overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr
                            class="bg-[#FAF7F2] text-xs text-[#5F6F5B] uppercase tracking-wider border-b border-[#B6ADA3]/30">
                            <th class="p-4">Nama Tamu</th>
                            <th class="p-4">Kategori</th>
                            <th class="p-4">Status Kehadiran</th>
                            <th class="p-4">Jumlah Pax</th>
                            <th class="p-4">Kontak / Telepon</th>
                            <th class="p-4">Alamat</th>
                            <th class="p-4 text-right">Aksi</th>                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#B6ADA3]/20 text-sm">
                        @forelse($guests as $g)
                            <tr class="hover:bg-[#FAF7F2]/60 transition-colors">
                                <td class="p-4 font-semibold text-[#5F6F5B]">
                                    {{ $g->title ? $g->title . ' ' : '' }}{{ $g->name }}</td>
                                <td class="p-4">
                                    <span
                                        class="bg-[#FAF7F2] text-[#5F6F5B] text-xs px-2.5 py-1 rounded-lg border border-[#B6ADA3]/40 font-medium">
                                        {{ $g->category }}
                                    </span>
                                </td>
                                <td class="p-4">
                                    <span
                                        class="px-2.5 py-1 rounded-full text-xs font-semibold 
                                    @if ($g->attendance_status == 'Attend') bg-[#A3B7A6]/25 text-[#5F6F5B] border border-[#A3B7A6]/40
                                    @elseif($g->attendance_status == 'Pending') bg-[#FAF7F2] text-[#5F6F5B] border border-[#B6ADA3]/40
                                    @else bg-[#D8A7B1]/25 text-[#5F6F5B] border border-[#D8A7B1]/40 @endif">
                                        {{ $g->attendance_status }}
                                    </span>
                                </td>
                                <td class="p-4 font-bold text-[#5F6F5B]">{{ $g->guest_count }} Pax</td>
                                <td class="p-4 text-xs text-[#5F6F5B]">
                                    @if ($g->phone)
                                        <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $g->phone) }}"
                                            target="_blank"
                                            class="inline-flex items-center gap-1 text-[#5F6F5B] hover:text-[#5F6F5B] font-semibold hover:underline">
                                            <i class="fa-brands fa-whatsapp text-[#D8A7B1]"></i> {{ $g->phone }}
                                        </a>
                                        <span
                                            class="ml-1.5 inline-block px-2 py-0.5 rounded-full text-[10px] font-semibold
                                        @if ($g->wa_sent) bg-[#A3B7A6]/25 text-[#5F6F5B] border border-[#A3B7A6]/40
                                        @else bg-[#FAF7F2] text-[#B6ADA3] border border-[#B6ADA3]/40 @endif">
                                            {{ $g->wa_sent ? 'WA Terkirim' : 'Belum' }}
                                        </span>
                                    @else
                                        —
                                    @endif
                                </td>
                                <td class="p-4 text-xs text-[#5F6F5B]/80 max-w-xs truncate">{{ $g->address ?? '—' }}</td>
                                <td class="p-4 text-right">
                                    <div class="flex items-center justify-end gap-1">
                                        @if($g->phone)
                                            <a href="{{ route('guests.wa', $g->id) }}" target="_blank" title="Kirim WA"
                                                class="p-2 text-[#B6ADA3] hover:text-green-600 cursor-pointer">
                                                <i class="fa-brands fa-whatsapp"></i>
                                            </a>
                                        @endif
                                        <button
                                            @click="modalOpen = true; editMode = true; currentItem = {{ json_encode($g) }}"
                                            title="Edit" class="p-2 text-[#B6ADA3] hover:text-[#5F6F5B] cursor-pointer"><i
                                                class="fa-solid fa-pen-to-square"></i></button>
                                        <form id="del-guest-{{ $g->id }}"
                                            action="{{ route('guests.destroy', $g->id) }}" method="POST">
                                            @csrf
                                            @method('DELETE')
                                            <button type="button" title="Hapus"
                                                onclick="confirmDelete('del-guest-{{ $g->id }}', '{{ addslashes($g->name) }}')"
                                                class="p-2 text-[#B6ADA3] hover:text-[#5F6F5B] cursor-pointer"><i
                                                    class="fa-solid fa-trash"></i></button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-10 text-[#B6ADA3] text-sm">Belum ada data tamu.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Modal Form -->
        <div x-show="modalOpen" x-transition.opacity
            class="fixed inset-0 z-50 bg-[#5F6F5B]/40 backdrop-blur-md flex items-center justify-center p-4">
            <div @click.away="modalOpen = false"
                class="bg-white w-full max-w-lg p-6 rounded-2xl border border-[#B6ADA3]/40 shadow-2xl space-y-4">
                <div class="flex items-center justify-between border-b border-[#B6ADA3]/30 pb-3">
                    <h3 class="text-lg font-bold font-serif-title text-[#5F6F5B]"
                        x-text="editMode ? 'Edit Data Tamu' : 'Tambah Tamu Undangan'"></h3>
                    <button @click="modalOpen = false" class="text-[#B6ADA3] hover:text-[#5F6F5B] cursor-pointer"><i
                            class="fa-solid fa-xmark"></i></button>
                </div>
                <form :action="editMode ? '/guests/' + currentItem.id : '{{ route('guests.store') }}'" method="POST"
                    class="space-y-4">
                    @csrf
                    <template x-if="editMode"><input type="hidden" name="_method" value="PUT"></template>
                    <div>
                        <label class="block text-xs font-semibold text-[#5F6F5B] mb-1">Nama Tamu / Penanggung Jawab
                            *</label>
                        <div class="flex gap-2">
                            <select name="title" x-model="currentItem.title"
                                class="w-28 bg-[#FAF7F2] border border-[#B6ADA3]/40 text-sm text-[#5F6F5B] px-3 py-2 rounded-xl focus:border-[#D8A7B1] focus:outline-none">
                                <option value="">-- Gelar --</option>
                                <template x-for="t in titles" :key="t">
                                    <option :value="t" x-text="t"></option>
                                </template>
                            </select>
                            <input type="text" name="name" x-model="currentItem.name" required
                                class="flex-1 bg-[#FAF7F2] border border-[#B6ADA3]/40 text-sm text-[#5F6F5B] px-3 py-2 rounded-xl focus:border-[#D8A7B1] focus:outline-none">
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-[#5F6F5B] mb-1">Kategori *</label>
                            <select name="category" x-model="currentItem.category" @change="autoFillPax()" required
                                class="w-full bg-[#FAF7F2] border border-[#B6ADA3]/40 text-sm text-[#5F6F5B] px-3 py-2 rounded-xl focus:border-[#D8A7B1] focus:outline-none">
                                <option value="">-- Pilih Kategori --</option>
                                @foreach ($categories as $cat)
                                    <option value="{{ $cat }}">{{ $cat }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-[#5F6F5B] mb-1">Status Kehadiran *</label>
                            <select name="attendance_status" x-model="currentItem.attendance_status" required
                                class="w-full bg-[#FAF7F2] border border-[#B6ADA3]/40 text-sm text-[#5F6F5B] px-3 py-2 rounded-xl focus:border-[#D8A7B1] focus:outline-none">
                                @foreach ($statuses as $st)
                                    <option value="{{ $st }}">{{ $st }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-[#5F6F5B] mb-1">Telepon / WA</label>
                            <input type="text" name="phone" x-model="currentItem.phone"
                                class="w-full bg-[#FAF7F2] border border-[#B6ADA3]/40 text-sm text-[#5F6F5B] px-3 py-2 rounded-xl focus:border-[#D8A7B1] focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-[#5F6F5B] mb-1">Jumlah Pax *</label>
                            <input type="number" name="guest_count" x-model="currentItem.guest_count" required
                                min="1"
                                class="w-full bg-[#FAF7F2] border border-[#B6ADA3]/40 text-sm text-[#5F6F5B] px-3 py-2 rounded-xl focus:border-[#D8A7B1] focus:outline-none">
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-[#5F6F5B] mb-1">Alamat / Kota</label>
                        <textarea name="address" x-model="currentItem.address" rows="2"
                            class="w-full bg-[#FAF7F2] border border-[#B6ADA3]/40 text-sm text-[#5F6F5B] px-3 py-2 rounded-xl focus:border-[#D8A7B1] focus:outline-none"></textarea>
                    </div>
                    <div class="flex justify-end gap-3 pt-3 border-t border-[#B6ADA3]/30">
                        <button type="button" @click="modalOpen = false"
                            class="px-4 py-2 text-xs text-[#B6ADA3] hover:text-[#5F6F5B] cursor-pointer">Batal</button>
                        <button type="submit" class="btn-primary-rose px-5 py-2 rounded-xl text-xs cursor-pointer">Simpan
                            Tamu</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
