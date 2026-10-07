@extends('layouts.app')

@section('title', 'Admin Panel - Admin')

@section('content')
    <div class="space-y-6" x-data="{ modalOpen: false, editMode: false, currentItem: {} }">

        <!-- Page Title & Header -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-white p-5 rounded-2xl border border-[#B6ADA3]/35 shadow-xs">
            <div>
                <h1 class="text-xl md:text-2xl font-bold font-serif-title text-[#5F6F5B]">Kelola Master Dropdown</h1>
                <p class="text-xs text-[#5F6F5B]/70 mt-1">Opsi pilihan yang dipakai bersama seluruh modul aplikasi</p>
            </div>
        </div>

            <!-- Mobile Selector Header (Visible on Small Screens) -->
            <div class="block lg:hidden bg-white p-4 rounded-2xl border border-[#B6ADA3]/35 shadow-xs space-y-2">
                <label class="block text-xs font-bold uppercase tracking-wider text-[#5F6F5B]">Pilih Group Dropdown
                    Menu</label>
                <select onchange="window.location.href = this.value"
                    class="w-full bg-[#FAF7F2] border border-[#B6ADA3]/50 text-sm font-bold text-[#5F6F5B] px-3.5 py-2.5 rounded-xl focus:border-[#D8A7B1] focus:outline-none">
                    @foreach ($groups as $key => $name)
                        @php $count = $groupCounts[$key] ?? 0; @endphp
                        <option value="{{ route('admin.dropdowns.index', ['group' => $key]) }}"
                            {{ $selectedGroup === $key ? 'selected' : '' }}>
                            {{ $name }} ({{ $count }} item)
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Main Master Dropdown Grid (Sidebar + Table) -->
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">

                <!-- Left Panel: PC Group Navigation (4 Cols on Desktop) -->
                <div
                    class="hidden lg:block lg:col-span-4 bg-white rounded-2xl border border-[#B6ADA3]/35 shadow-xs p-4 space-y-2">
                    <div class="px-2 pb-2.5 border-b border-[#B6ADA3]/30 flex items-center justify-between">
                        <span class="text-xs font-bold uppercase tracking-wider text-[#5F6F5B]">Daftar Master
                            Dropdown</span>
                        <span
                            class="text-[10px] bg-[#FAF7F2] text-[#5F6F5B] px-2 py-0.5 rounded-full border border-[#B6ADA3]/30 font-bold">{{ count($groups) }}
                            Group</span>
                    </div>

                    <div class="space-y-1.5 pt-1">
                        @foreach ($groups as $key => $name)
                            @php
                                $count = $groupCounts[$key] ?? 0;
                                $isActive = $selectedGroup === $key;
                            @endphp
                            <a href="{{ route('admin.dropdowns.index', ['group' => $key]) }}"
                                class="flex items-center justify-between px-3.5 py-2.5 rounded-xl text-xs font-semibold transition-all {{ $isActive ? 'bg-[#5F6F5B] text-white shadow-xs font-bold' : 'text-[#5F6F5B] hover:bg-[#FAF7F2]' }}">
                                <div class="flex items-center gap-2.5 truncate">
                                    <i class="fa-solid fa-list-ul text-[#D8A7B1]"></i>
                                    <span class="truncate">{{ $name }}</span>
                                </div>
                                <span
                                    class="px-2 py-0.5 rounded-full text-[10px] font-bold shrink-0 {{ $isActive ? 'bg-white/20 text-white' : 'bg-[#FAF7F2] text-[#5F6F5B] border border-[#B6ADA3]/30' }}">
                                    {{ $count }}
                                </span>
                            </a>
                        @endforeach
                    </div>
                </div>

                <!-- Right Panel: Options List Table (8 Cols on Desktop) -->
                <div class="lg:col-span-8 space-y-4">

                    <!-- Group Banner & Add Button -->
                    <div
                        class="bg-white p-5 rounded-2xl border border-[#B6ADA3]/35 shadow-xs flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                        <div>
                            <span class="text-[10px] text-[#5F6F5B] uppercase tracking-wider font-bold">Group
                                Terpilih</span>
                            <h2 class="text-lg sm:text-xl font-bold font-serif-title text-[#5F6F5B] mt-0.5">
                                {{ $groups[$selectedGroup] ?? $selectedGroup }}
                            </h2>
                        </div>

                        <button
                            @click="modalOpen = true; editMode = false; currentItem = { group_key: '{{ $selectedGroup }}', sort_order: {{ $options->count() }} }"
                            class="btn-primary-rose px-4 py-2.5 rounded-xl text-xs font-bold flex items-center justify-center gap-2 cursor-pointer shadow-xs w-full sm:w-auto">
                            <i class="fa-solid fa-plus"></i> Tambah Pilihan Dropdown
                        </button>
                    </div>

                    <!-- Options Table View -->
                    <div class="bg-white rounded-2xl border border-[#B6ADA3]/35 shadow-xs overflow-hidden">
                        <div class="overflow-x-auto">
                            <table class="w-full text-left border-collapse">
                                <thead>
                                    <tr
                                        class="bg-[#FAF7F2] text-xs text-[#5F6F5B] uppercase tracking-wider border-b border-[#B6ADA3]/30">
                                        <th class="p-4 w-20 text-center">Urutan</th>
                                        <th class="p-4">Nilai Pilihan (Option Value)</th>
                                        @if ($selectedGroup === 'guest_category')
                                            <th class="p-4 w-24 text-center">Default Pax</th>
                                        @endif
                                        <th class="p-4">Tanggal Dibuat</th>
                                        <th class="p-4 text-right w-28">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-[#B6ADA3]/20 text-sm">
                                    @forelse($options as $opt)
                                        <tr class="hover:bg-[#FAF7F2]/70 transition-colors">
                                            <td class="p-4 text-center font-bold text-[#5F6F5B]">
                                                <span
                                                    class="w-7 h-7 inline-flex items-center justify-center rounded-full text-xs">
                                                    {{ $opt->sort_order }}
                                                </span>
                                            </td>
                                            <td class="p-4 font-bold text-[#2D372E]">
                                                <span
                                                    class="px-3 py-1 rounded-xl bg-[#FAF7F2] border border-[#B6ADA3]/40 inline-block text-xs sm:text-sm text-[#5F6F5B]">
                                                    {{ $opt->option_value }}
                                                </span>
                                            </td>
                                            @if ($selectedGroup === 'guest_category')
                                                <td class="p-4 text-center font-bold text-[#5F6F5B]">
                                                    {{ $opt->meta_value ?? '—' }}
                                                </td>
                                            @endif
                                            <td class="p-4 text-xs font-medium text-[#5F6F5B]">
                                                {{ $opt->created_at ? $opt->created_at->format('d M Y, H:i') : '—' }}
                                            </td>
                                            <td class="p-4 text-right">
                                                <div class="flex items-center justify-end gap-2">
                                                    <button
                                                        @click="modalOpen = true; editMode = true; currentItem = {{ json_encode($opt) }}"
                                                        class="p-2 text-[#B6ADA3] hover:text-[#5F6F5B] cursor-pointer"
                                                        title="Edit">
                                                        <i class="fa-solid fa-pen-to-square"></i>
                                                    </button>
                                                    <form id="del-dropdown-{{ $opt->id }}"
                                                        action="{{ route('admin.dropdowns.destroy', $opt->id) }}"
                                                        method="POST">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="button" title="Hapus"
                                                            onclick="confirmDelete('del-dropdown-{{ $opt->id }}', '{{ addslashes($opt->option_value) }}')"
                                                            class="p-2 text-[#B6ADA3] hover:text-[#5F6F5B] cursor-pointer">
                                                            <i class="fa-solid fa-trash"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="text-center py-12 text-[#5F6F5B] text-sm">
                                                <i class="fa-solid fa-folder-open text-2xl text-[#D8A7B1] mb-2 block"></i>
                                                Belum ada pilihan dropdown untuk group ini. Klik tombol <strong>Tambah
                                                    Pilihan Dropdown</strong> untuk membuat.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                </div>
            </div>

        </div>

        <!-- ================= MODAL FORM (TAMBAH / EDIT DROPDOWN OPTION) ================= -->
        <div x-show="modalOpen" x-transition.opacity
            class="fixed inset-0 z-50 bg-[#5F6F5B]/40 backdrop-blur-md flex items-center justify-center p-4" x-cloak>
            <div @click.away="modalOpen = false"
                class="bg-white w-full max-w-md p-6 rounded-2xl border border-[#B6ADA3]/40 shadow-2xl space-y-4">
                <div class="flex items-center justify-between border-b border-[#B6ADA3]/30 pb-3">
                    <h3 class="text-base font-bold font-serif-title text-[#5F6F5B]"
                        x-text="editMode ? 'Edit Pilihan Dropdown' : 'Tambah Pilihan Dropdown Baru'"></h3>
                    <button @click="modalOpen = false" class="text-[#B6ADA3] hover:text-[#5F6F5B] cursor-pointer">
                        <i class="fa-solid fa-xmark text-lg"></i>
                    </button>
                </div>

                <form :action="editMode ? '/admin/dropdowns/' + currentItem.id : '{{ route('admin.dropdowns.store') }}'"
                    method="POST" class="space-y-4">
                    @csrf
                    <template x-if="editMode">
                        <input type="hidden" name="_method" value="PUT">
                    </template>

                    <input type="hidden" name="group_key" :value="currentItem.group_key || '{{ $selectedGroup }}'">

                    <div>
                        <label class="block text-xs font-bold text-[#5F6F5B] mb-1">Group Target</label>
                        <input type="text" readonly :value="'{{ $groups[$selectedGroup] ?? $selectedGroup }}'"
                            class="w-full bg-[#FAF7F2] border border-[#B6ADA3]/40 text-sm font-bold text-[#5F6F5B] px-3.5 py-2.5 rounded-xl focus:outline-none">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-[#5F6F5B] mb-1">Nama Pilihan (Option Value) *</label>
                        <input type="text" name="option_value" x-model="currentItem.option_value" required
                            placeholder="Contoh: Catering International, High Priority, dll"
                            class="w-full bg-[#FAF7F2] border border-[#B6ADA3]/50 text-sm font-semibold text-[#2D372E] px-3.5 py-2.5 rounded-xl focus:border-[#D8A7B1] focus:bg-white focus:outline-none">
                    </div>

                    @if ($selectedGroup === 'guest_category')
                        <div>
                            <label class="block text-xs font-bold text-[#5F6F5B] mb-1">Default Pax</label>
                            <input type="number" name="meta_value" x-model="currentItem.meta_value" min="1"
                                placeholder="2"
                                class="w-full bg-[#FAF7F2] border border-[#B6ADA3]/50 text-sm font-semibold text-[#2D372E] px-3.5 py-2.5 rounded-xl focus:border-[#D8A7B1] focus:bg-white focus:outline-none">
                            <p class="text-[11px] text-[#5F6F5B]/70 mt-1">Jumlah pax default saat pilih kategori ini.</p>
                        </div>
                    @endif

                    <div>
                        <label class="block text-xs font-bold text-[#5F6F5B] mb-1">Urutan Tampil (Sort Order)</label>
                        <input type="number" name="sort_order" x-model="currentItem.sort_order" min="0"
                            placeholder="0"
                            class="w-full bg-[#FAF7F2] border border-[#B6ADA3]/50 text-sm font-semibold text-[#2D372E] px-3.5 py-2.5 rounded-xl focus:border-[#D8A7B1] focus:bg-white focus:outline-none">
                        <p class="text-[11px] text-[#5F6F5B]/70 mt-1">Angka lebih kecil tampil lebih di atas.</p>
                    </div>

                    <div class="flex justify-end gap-3 pt-3 border-t border-[#B6ADA3]/30">
                        <button type="button" @click="modalOpen = false"
                            class="px-4 py-2 text-xs text-[#5F6F5B] hover:text-[#5F6F5B] font-bold cursor-pointer">Batal</button>
                        <button type="submit"
                            class="btn-primary-rose px-5 py-2.5 rounded-xl text-xs cursor-pointer font-bold shadow-xs">
                            <i class="fa-solid fa-check mr-1"></i> Simpan Pilihan
                        </button>
                    </div>
                </form>
            </div>
    </div>
@endsection
