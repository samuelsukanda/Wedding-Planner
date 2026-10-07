@extends('layouts.app')

@section('title', 'Admin Panel - Admin')

@section('content')
    <div class="space-y-6" x-data="{ modalOpen: false, editMode: false, currentItem: {}, weddingMode: 'existing' }">
        <!-- Page Title & Header -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-white p-5 rounded-2xl border border-[#B6ADA3]/35 shadow-xs">
            <div>
                <h1 class="text-xl md:text-2xl font-bold font-serif-title text-[#5F6F5B]">Admin Panel</h1>
                <p class="text-xs text-[#5F6F5B]/70 mt-1">Kelola seluruh user terdaftar dan pasangannya</p>
            </div>
            <button @click="modalOpen = true; editMode = false; currentItem = {}"
                class="btn-primary-rose px-4 py-2.5 rounded-xl text-sm flex items-center justify-center gap-2 cursor-pointer w-full md:w-auto">
                <i class="fa-solid fa-user-plus"></i> Tambah User
            </button>
        </div>

        <!-- Info: pasangan berbagi data -->
        <div class="bg-white p-4 rounded-2xl border border-[#B6ADA3]/35 shadow-xs flex items-start gap-3">
            <i class="fa-solid fa-circle-info text-[#D8A7B1] mt-0.5"></i>
            <p class="text-xs text-[#5F6F5B]/85 leading-relaxed">
                Dua user yang berpasangan bisa diarahkan ke data pernikahan yang <strong>sama</strong>,
                sehingga keduanya melihat seluruh data yang identik. Arahkan akun pasangan ke weddings
                yang sudah ada, atau buat data pernikahan baru saat menambah user.
            </p>
        </div>

        <!-- Summary Widgets -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-3 sm:gap-4">
            <div class="bg-white p-4 rounded-xl border border-[#B6ADA3]/35 shadow-xs">
                <div class="text-xs text-[#5F6F5B]">Total User</div>
                <div class="text-xl sm:text-2xl font-bold text-[#5F6F5B]">{{ $users->count() }}</div>
            </div>
            <div class="bg-white p-4 rounded-xl border border-[#B6ADA3]/35 shadow-xs">
                <div class="text-xs text-[#5F6F5B]">Superadmin</div>
                <div class="text-xl sm:text-2xl font-bold text-[#5F6F5B]">{{ $users->where('is_superadmin', true)->count() }}</div>
            </div>
            <div class="bg-white p-4 rounded-xl border border-[#B6ADA3]/35 shadow-xs">
                <div class="text-xs text-[#5F6F5B]">Sudah Dipasangkan</div>
                <div class="text-xl sm:text-2xl font-bold text-[#5F6F5B]">{{ $users->whereNotNull('wedding_id')->count() }}</div>
            </div>
            <div class="bg-white p-4 rounded-xl border border-[#B6ADA3]/35 shadow-xs">
                <div class="text-xs text-[#5F6F5B]">Belum Dipasangkan</div>
                <div class="text-xl sm:text-2xl font-bold text-[#5F6F5B]">{{ $users->whereNull('wedding_id')->count() }}</div>
            </div>
        </div>

        <!-- User Table -->
        <div class="bg-white rounded-2xl border border-[#B6ADA3]/35 shadow-xs overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-[#FAF7F2] text-xs text-[#5F6F5B] uppercase tracking-wider border-b border-[#B6ADA3]/30">
                            <th class="p-4 w-12">No</th>
                            <th class="p-4">Nama</th>
                            <th class="p-4">Email</th>
                            <th class="p-4">Data Pernikahan</th>
                            <th class="p-4">Role</th>
                            <th class="p-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#B6ADA3]/20 text-sm">
                        @forelse($users as $i => $u)
                            <tr class="hover:bg-[#FAF7F2]/60 transition-colors">
                                <td class="p-4 text-[#B6ADA3]">{{ $i + 1 }}</td>
                                <td class="p-4 font-semibold text-[#5F6F5B]">{{ $u->name }}</td>
                                <td class="p-4 text-xs text-[#5F6F5B]">{{ $u->email }}</td>
                                <td class="p-4 text-xs text-[#5F6F5B]">
                                    @if ($u->wedding)
                                        <span class="font-semibold">{{ $u->wedding->couple_name }}</span>
                                        <span class="block text-[10px] text-[#B6ADA3] mt-0.5">
                                            #{{ $u->wedding_id }}
                                            @if ($u->wedding->wedding_date)
                                                &middot; {{ $u->wedding->wedding_date->format('d M Y') }}
                                            @endif
                                        </span>
                                    @else
                                        <span class="px-2.5 py-1 rounded-full text-[10px] font-semibold bg-[#FAF7F2] text-[#B6ADA3] border border-[#B6ADA3]/40">
                                            Belum dipasangkan
                                        </span>
                                    @endif
                                </td>
                                <td class="p-4">
                                    @if ($u->is_superadmin)
                                        <span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-[#5F6F5B] text-white border border-[#5F6F5B]/40">
                                            Superadmin
                                        </span>
                                    @else
                                        <span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-[#A3B7A6]/25 text-[#5F6F5B] border border-[#A3B7A6]/40">
                                            User
                                        </span>
                                    @endif
                                </td>
                                <td class="p-4 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <button
                                            @click="modalOpen = true; editMode = true; currentItem = {{ json_encode($u) }}"
                                            title="Edit"
                                            class="p-2 text-[#B6ADA3] hover:text-[#5F6F5B] cursor-pointer">
                                            <i class="fa-solid fa-pen-to-square"></i>
                                        </button>
                                        <form id="del-user-{{ $u->id }}" action="{{ route('admin.users.destroy', $u->id) }}"
                                            method="POST">
                                            @csrf
                                            @method('DELETE')
                                            <button type="button" title="Hapus"
                                                onclick="confirmDelete('del-user-{{ $u->id }}', '{{ addslashes($u->name) }}')"
                                                class="p-2 text-[#B6ADA3] hover:text-[#5F6F5B] cursor-pointer">
                                                <i class="fa-solid fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-10 text-[#B6ADA3] text-sm">Belum ada user terdaftar.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Modal Form -->
        <div x-show="modalOpen" x-transition.opacity x-cloak
            class="fixed inset-0 z-50 bg-[#5F6F5B]/40 backdrop-blur-md flex items-center justify-center p-4">
            <div @click.away="modalOpen = false"
                class="bg-white w-full max-w-lg p-6 rounded-2xl border border-[#B6ADA3]/40 shadow-2xl space-y-4 max-h-[90vh] overflow-y-auto">
                <div class="flex items-center justify-between border-b border-[#B6ADA3]/30 pb-3">
                    <h3 class="text-lg font-bold font-serif-title text-[#5F6F5B]"
                        x-text="editMode ? 'Edit User' : 'Tambah User Baru'"></h3>
                    <button @click="modalOpen = false" class="text-[#B6ADA3] hover:text-[#5F6F5B] cursor-pointer">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>

                <!-- FORM TAMBAH (server-side) -->
                <form action="{{ route('admin.users.store') }}" method="POST" class="space-y-4" x-show="!editMode">
                    @csrf
                    <div>
                        <label class="block text-xs font-semibold text-[#5F6F5B] mb-1">Nama Lengkap *</label>
                        <input type="text" name="name" required
                            class="w-full bg-[#FAF7F2] border border-[#B6ADA3]/40 text-sm text-[#5F6F5B] px-3 py-2 rounded-xl focus:border-[#D8A7B1] focus:outline-none"
                            placeholder="Contoh: Joko Pratama">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-[#5F6F5B] mb-1">Email *</label>
                        <input type="email" name="email" required
                            class="w-full bg-[#FAF7F2] border border-[#B6ADA3]/40 text-sm text-[#5F6F5B] px-3 py-2 rounded-xl focus:border-[#D8A7B1] focus:outline-none"
                            placeholder="joko@email.com">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-[#5F6F5B] mb-1">Password *</label>
                        <input type="text" name="password" required minlength="8"
                            class="w-full bg-[#FAF7F2] border border-[#B6ADA3]/40 text-sm text-[#5F6F5B] px-3 py-2 rounded-xl focus:border-[#D8A7B1] focus:outline-none"
                            placeholder="Minimal 8 karakter">
                    </div>

                    <div class="pt-2 border-t border-[#B6ADA3]/30">
                        <label class="block text-xs font-semibold text-[#5F6F5B] mb-2">Data Pernikahan *</label>
                        <div class="space-y-2">
                            <label class="flex items-start gap-2 text-sm text-[#5F6F5B] cursor-pointer">
                                <input type="radio" name="wedding_mode" value="existing" x-model="weddingMode"
                                    class="mt-1" checked>
                                <span>Pakai data pernikahan yang sudah ada</span>
                            </label>
                            <label class="flex items-start gap-2 text-sm text-[#5F6F5B] cursor-pointer">
                                <input type="radio" name="wedding_mode" value="new" x-model="weddingMode"
                                    class="mt-1">
                                <span>Buat data pernikahan baru</span>
                            </label>
                        </div>
                    </div>

                    <div x-show="weddingMode === 'existing'">
                        <label class="block text-xs font-semibold text-[#5F6F5B] mb-1">Pilih Data Pernikahan *</label>
                        <select name="wedding_id"
                            class="w-full bg-[#FAF7F2] border border-[#B6ADA3]/40 text-sm text-[#5F6F5B] px-3 py-2 rounded-xl focus:border-[#D8A7B1] focus:outline-none">
                            <option value="">-- Pilih --</option>
                            @foreach($weddings as $w)
                                <option value="{{ $w->id }}">{{ $w->couple_name }} (#{{ $w->id }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div x-show="weddingMode === 'new'" class="space-y-3">
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-semibold text-[#5F6F5B] mb-1">Nama Pengantin Pria *</label>
                                <input type="text" name="groom_name"
                                    class="w-full bg-[#FAF7F2] border border-[#B6ADA3]/40 text-sm text-[#5F6F5B] px-3 py-2 rounded-xl focus:border-[#D8A7B1] focus:outline-none">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-[#5F6F5B] mb-1">Nama Pengantin Wanita *</label>
                                <input type="text" name="bride_name"
                                    class="w-full bg-[#FAF7F2] border border-[#B6ADA3]/40 text-sm text-[#5F6F5B] px-3 py-2 rounded-xl focus:border-[#D8A7B1] focus:outline-none">
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-[#5F6F5B] mb-1">Tanggal Pernikahan *</label>
                            <input type="text" name="wedding_date" class="datepicker w-full bg-[#FAF7F2] border border-[#B6ADA3]/40 text-sm text-[#5F6F5B] px-3 py-2 rounded-xl focus:border-[#D8A7B1] focus:outline-none">
                        </div>
                    </div>

                    <div class="flex justify-end gap-3 pt-3 border-t border-[#B6ADA3]/30">
                        <button type="button" @click="modalOpen = false"
                            class="px-4 py-2 text-xs text-[#B6ADA3] hover:text-[#5F6F5B] cursor-pointer">Batal</button>
                        <button type="submit" class="btn-primary-rose px-5 py-2 rounded-xl text-xs cursor-pointer">Simpan
                            User</button>
                    </div>
                </form>

                <!-- FORM EDIT (server-side, pre-filled) -->
                <form :action="'{{ url('admin/users') }}/' + currentItem.id" method="POST"
                    class="space-y-4" x-show="editMode" x-cloak>
                    @csrf
                    @method('PUT')
                    <div>
                        <label class="block text-xs font-semibold text-[#5F6F5B] mb-1">Nama Lengkap *</label>
                        <input type="text" name="name" x-model="currentItem.name" required
                            class="w-full bg-[#FAF7F2] border border-[#B6ADA3]/40 text-sm text-[#5F6F5B] px-3 py-2 rounded-xl focus:border-[#D8A7B1] focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-[#5F6F5B] mb-1">Email *</label>
                        <input type="email" name="email" x-model="currentItem.email" required
                            class="w-full bg-[#FAF7F2] border border-[#B6ADA3]/40 text-sm text-[#5F6F5B] px-3 py-2 rounded-xl focus:border-[#D8A7B1] focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-[#5F6F5B] mb-1">Password Baru</label>
                        <input type="text" name="password" minlength="8"
                            class="w-full bg-[#FAF7F2] border border-[#B6ADA3]/40 text-sm text-[#5F6F5B] px-3 py-2 rounded-xl focus:border-[#D8A7B1] focus:outline-none"
                            placeholder="Kosongkan jika tidak diubah">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-[#5F6F5B] mb-1">Data Pernikahan</label>
                        <select name="wedding_id" x-model="currentItem.wedding_id"
                            class="w-full bg-[#FAF7F2] border border-[#B6ADA3]/40 text-sm text-[#5F6F5B] px-3 py-2 rounded-xl focus:border-[#D8A7B1] focus:outline-none">
                            <option value="">-- Belum dipasangkan --</option>
                            @foreach($weddings as $w)
                                <option value="{{ $w->id }}">{{ $w->couple_name }} (#{{ $w->id }})</option>
                            @endforeach
                        </select>
                    </div>
                    <label class="flex items-center gap-2 text-sm text-[#5F6F5B] cursor-pointer">
                        <input type="checkbox" name="is_superadmin" value="1" x-model="currentItem.is_superadmin"
                            class="rounded">
                        <span>Jadikan superadmin (bisa akses menu Admin Panel)</span>
                    </label>
                    <div class="flex justify-end gap-3 pt-3 border-t border-[#B6ADA3]/30">
                        <button type="button" @click="modalOpen = false"
                            class="px-4 py-2 text-xs text-[#B6ADA3] hover:text-[#5F6F5B] cursor-pointer">Batal</button>
                        <button type="submit" class="btn-primary-rose px-5 py-2 rounded-xl text-xs cursor-pointer">Simpan
                            User</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection