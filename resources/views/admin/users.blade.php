@extends('layouts.app')

@section('title', 'Admin Panel - Admin')

@section('content')
    <div class="space-y-6" x-data="{ modalOpen: false, editMode: false, currentItem: {}, weddingMode: 'existing', partnerModalOpen: false, partnerItem: {}, openPartnerModal(u) { this.partnerItem = u;
            this.partnerModalOpen = true }, init() {} }">
        <!-- Page Title & Header -->
        <div
            class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-white p-5 rounded-2xl border border-[#B6ADA3]/35 shadow-xs">
            <div>
                <h1 class="text-xl md:text-2xl font-bold font-serif-title text-[#5F6F5B]">Admin Panel</h1>
                <p class="text-xs text-[#5F6F5B] mt-1">Kelola seluruh user terdaftar dan pasangannya</p>
            </div>
            <button @click="modalOpen = true; editMode = false; currentItem = {}"
                class="btn-primary-rose px-4 py-2.5 rounded-xl text-sm flex items-center justify-center gap-2 cursor-pointer w-full md:w-auto">
                <i class="fa-solid fa-user-plus"></i> Tambah User
            </button>
        </div>

        <!-- Summary Widgets -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-3 sm:gap-4">
            <div class="bg-white p-4 rounded-xl border border-[#B6ADA3]/35 shadow-xs">
                <div class="text-xs text-[#5F6F5B]">Total User</div>
                <div class="text-xl sm:text-2xl font-bold text-[#5F6F5B]">{{ $users->count() }}</div>
            </div>
            <div class="bg-white p-4 rounded-xl border border-[#B6ADA3]/35 shadow-xs">
                <div class="text-xs text-[#5F6F5B]">Superadmin</div>
                <div class="text-xl sm:text-2xl font-bold text-[#5F6F5B]">
                    {{ $users->where('is_superadmin', true)->count() }}</div>
            </div>
            <div class="bg-white p-4 rounded-xl border border-[#B6ADA3]/35 shadow-xs">
                <div class="text-xs text-[#5F6F5B]">Sudah Dipasangkan</div>
                <div class="text-xl sm:text-2xl font-bold text-[#5F6F5B]">{{ $users->whereNotNull('wedding_id')->count() }}
                </div>
            </div>
            <div class="bg-white p-4 rounded-xl border border-[#B6ADA3]/35 shadow-xs">
                <div class="text-xs text-[#5F6F5B]">Belum Dipasangkan</div>
                <div class="text-xl sm:text-2xl font-bold text-[#5F6F5B]">{{ $users->whereNull('wedding_id')->count() }}
                </div>
            </div>
        </div>

        <!-- User Table -->
        <div class="bg-white rounded-2xl border border-[#B6ADA3]/35 shadow-xs overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr
                            class="bg-[#FAF7F2] text-xs text-[#5F6F5B] uppercase tracking-wider border-b border-[#B6ADA3]/30">
                            <th class="p-4 w-12">No</th>
                            <th class="p-4">Nama</th>
                            <th class="p-4">Email</th>
                            <th class="p-4">Data Pernikahan</th>
                            <th class="p-4">Pasangan</th>
                            <th class="p-4">Status Akun</th>
                            <th class="p-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#B6ADA3]/20 text-sm">
                        @forelse($users as $i => $u)
                            <tr class="hover:bg-[#FAF7F2]/60 transition-colors">
                                <td class="p-4 text-[#5F6F5B]">{{ $i + 1 }}</td>
                                <td class="p-4 font-semibold text-[#5F6F5B]">{{ $u->name }}</td>
                                <td class="p-4 text-xs text-[#5F6F5B]">{{ $u->email }}</td>
                                <td class="p-4 text-xs text-[#5F6F5B]">
                                    @if ($u->wedding)
                                        <span class="font-semibold">{{ $u->wedding->couple_name }}</span>
                                        <span class="block text-[10px] text-[#5F6F5B] mt-0.5">
                                            #{{ $u->wedding_id }}
                                            @if ($u->wedding->wedding_date)
                                                &middot; {{ $u->wedding->wedding_date->format('d M Y') }}
                                            @endif
                                        </span>
                                    @else
                                        <span
                                            class="px-2.5 py-1 rounded-full text-[10px] font-semibold bg-[#FAF7F2] text-[#5F6F5B] border border-[#B6ADA3]/60">
                                            Belum dipasangkan
                                        </span>
                                    @endif
                                </td>
                                {{-- Pasangan = user lain yang memakai Wedding Kit
                                     yang sama, jadi keduanya saling melihat
                                     data tanpa perlu kolom terpisah. --}}
                                <td class="p-4 text-xs text-[#5F6F5B]">
                                    @if ($u->partner_name)
                                        <span class="font-semibold">{{ $u->partner_name }}</span>
                                        <span class="block text-[10px] text-[#5F6F5B] mt-0.5">
                                            {{ $u->partner_email }}
                                        </span>
                                    @else
                                        <span class="text-[#5F6F5B]">&mdash;</span>
                                    @endif
                                </td>
                                {{-- Status akun menempel pada Wedding Kit, jadi
                                     dua akun pasangan selalu sama. --}}
                                <td class="p-4">
                                    @if ($u->wedding)
                                        @if ($u->wedding->isPro())
                                            <span
                                                class="px-2.5 py-1 rounded-full text-xs font-semibold bg-[#D8A7B1]/25 text-[#8B3A47] border border-[#D8A7B1]/40 inline-flex items-center gap-1">
                                                <i class="fa-solid fa-crown text-[9px]"></i> Pro
                                            </span>
                                        @else
                                            <span
                                                class="px-2.5 py-1 rounded-full text-xs font-semibold bg-[#FAF7F2] text-[#5F6F5B] border border-[#B6ADA3]/40">
                                                Free
                                            </span>
                                        @endif
                                    @else
                                        <span class="text-xs text-[#5F6F5B]">&mdash;</span>
                                    @endif
                                </td>
                                <td class="p-4 text-right">
                                    <div class="flex items-center justify-end gap-2" x-data="{ open: false }">
                                        <button
                                            @click="modalOpen = true; editMode = true; currentItem = {{ json_encode($u) }}"
                                            title="Edit" aria-label="Edit user {{ $u->name }}" class="btn-icon">
                                            <i class="fa-solid fa-pen-to-square"></i>
                                        </button>

                                        {{-- Dropdown aksi cepat: tambah / ubah
                                             pasangan tanpa membuka form edit. --}}
                                        <div class="relative">
                                            <button type="button" @click="open = !open" title="Aksi lainnya" aria-label="Aksi lainnya untuk {{ $u->name }}"
                                                class="btn-icon">
                                                <i class="fa-solid fa-ellipsis-vertical"></i>
                                            </button>
                                            <div x-show="open" @click.outside="open = false" x-transition x-cloak
                                                class="absolute right-0 mt-1 w-52 bg-white border border-[#B6ADA3]/40 rounded-xl shadow-lg py-1 z-20">
                                                <button type="button"
                                                    @click="open = false; openPartnerModal({{ json_encode($u) }})"
                                                    class="w-full text-left px-4 py-2 text-xs text-[#5F6F5B] hover:bg-[#FAF7F2]">
                                                    <i class="fa-solid fa-user-plus text-[#8B3A47] w-4"></i>
                                                    {{ $u->partner_name ? 'Ubah Pasangan' : 'Tambah Pasangan' }}
                                                </button>
                                            </div>
                                        </div>

                                        <form id="del-user-{{ $u->id }}"
                                            action="{{ route('admin.users.destroy', $u->id) }}" method="POST">
                                            @csrf
                                            @method('DELETE')
                                            <button type="button" title="Hapus" aria-label="Hapus user {{ $u->name }}"
                                                onclick="confirmDelete('del-user-{{ $u->id }}', '{{ addslashes($u->name) }}')"
                                                class="btn-icon">
                                                <i class="fa-solid fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-10 text-[#5F6F5B] text-sm">Belum ada user
                                    terdaftar.
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
            <div x-outside="modalOpen = false"
                class="bg-white w-full max-w-lg p-6 rounded-2xl border border-[#B6ADA3]/40 shadow-2xl space-y-4 max-h-[90vh] overflow-y-auto">
                <div class="flex items-center justify-between border-b border-[#B6ADA3]/30 pb-3">
                    <h2 class="text-lg font-bold font-serif-title text-[#5F6F5B]"
                        x-text="editMode ? 'Edit User' : 'Tambah User Baru'"></h2>
                    <button @click="modalOpen = false" aria-label="Tutup modal" class="text-[#5F6F5B] hover:text-[#2D372E] cursor-pointer">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>

                <!-- FORM TAMBAH (server-side) -->
                <form action="{{ route('admin.users.store') }}" method="POST" class="space-y-4" x-show="!editMode">
                    @csrf
                    <div>
                        <label for="store_user_name" class="block text-xs font-semibold text-[#5F6F5B] mb-1">Nama Lengkap *</label>
                        <input type="text" id="store_user_name" name="name" required
                            aria-label="Nama Lengkap"
                            class="w-full bg-[#FAF7F2] border border-[#B6ADA3]/40 text-sm text-[#5F6F5B] px-3 py-2 rounded-xl focus:border-[#D8A7B1] focus:outline-none"
                            placeholder="Contoh: Joko Pratama">
                    </div>
                    <div>
                        <label for="store_user_email" class="block text-xs font-semibold text-[#5F6F5B] mb-1">Email *</label>
                        <input type="email" id="store_user_email" name="email" required
                            aria-label="Email"
                            class="w-full bg-[#FAF7F2] border border-[#B6ADA3]/40 text-sm text-[#5F6F5B] px-3 py-2 rounded-xl focus:border-[#D8A7B1] focus:outline-none"
                            placeholder="joko@email.com">
                    </div>
                    <div>
                        <label for="store_user_password" class="block text-xs font-semibold text-[#5F6F5B] mb-1">Password *</label>
                        <input type="text" id="store_user_password" name="password" required minlength="8"
                            aria-label="Password"
                            class="w-full bg-[#FAF7F2] border border-[#B6ADA3]/40 text-sm text-[#5F6F5B] px-3 py-2 rounded-xl focus:border-[#D8A7B1] focus:outline-none"
                            placeholder="Minimal 8 karakter">
                    </div>

                    <div class="pt-2 border-t border-[#B6ADA3]/30">
                        <span class="block text-xs font-semibold text-[#5F6F5B] mb-2">Data Pernikahan *</span>
                        <div class="space-y-2">
                            <div class="flex items-start gap-2">
                                <input type="radio" id="store_wedding_mode_existing" name="wedding_mode" value="existing" x-model="weddingMode"
                                    aria-label="Pakai data pernikahan yang sudah ada"
                                    class="mt-1" checked>
                                <label for="store_wedding_mode_existing" class="text-sm text-[#5F6F5B] cursor-pointer">
                                    Pakai data pernikahan yang sudah ada
                                </label>
                            </div>
                            <div class="flex items-start gap-2">
                                <input type="radio" id="store_wedding_mode_new" name="wedding_mode" value="new" x-model="weddingMode"
                                    aria-label="Buat data pernikahan baru"
                                    class="mt-1">
                                <label for="store_wedding_mode_new" class="text-sm text-[#5F6F5B] cursor-pointer">
                                    Buat data pernikahan baru
                                </label>
                            </div>
                        </div>
                    </div>

                    <div x-show="weddingMode === 'existing'">
                        <label for="store_user_wedding_id" class="block text-xs font-semibold text-[#5F6F5B] mb-1">Pilih Data Pernikahan *</label>
                        <select id="store_user_wedding_id" name="wedding_id"
                            aria-label="Pilih Data Pernikahan"
                            class="w-full bg-[#FAF7F2] border border-[#B6ADA3]/40 text-sm text-[#5F6F5B] px-3 py-2 rounded-xl focus:border-[#D8A7B1] focus:outline-none">
                            <option value="">-- Pilih --</option>
                            @foreach ($weddings as $w)
                                <option value="{{ $w->id }}">{{ $w->couple_name }} (#{{ $w->id }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div x-show="weddingMode === 'new'" class="space-y-3">
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label for="store_user_groom_name" class="block text-xs font-semibold text-[#5F6F5B] mb-1">Nama Pengantin Pria
                                    *</label>
                                <input type="text" id="store_user_groom_name" name="groom_name"
                                    aria-label="Nama Pengantin Pria"
                                    class="w-full bg-[#FAF7F2] border border-[#B6ADA3]/40 text-sm text-[#5F6F5B] px-3 py-2 rounded-xl focus:border-[#D8A7B1] focus:outline-none">
                            </div>
                            <div>
                                <label for="store_user_bride_name" class="block text-xs font-semibold text-[#5F6F5B] mb-1">Nama Pengantin Wanita
                                    *</label>
                                <input type="text" id="store_user_bride_name" name="bride_name"
                                    aria-label="Nama Pengantin Wanita"
                                    class="w-full bg-[#FAF7F2] border border-[#B6ADA3]/40 text-sm text-[#5F6F5B] px-3 py-2 rounded-xl focus:border-[#D8A7B1] focus:outline-none">
                            </div>
                        </div>
                        <div>
                            <label for="store_user_wedding_date" class="block text-xs font-semibold text-[#5F6F5B] mb-1">Tanggal Pernikahan *</label>
                            <input type="text" id="store_user_wedding_date" name="wedding_date" placeholder="DD-MM-YYYY"
                                aria-label="Tanggal Pernikahan"
                                class="w-full bg-[#FAF7F2] border border-[#B6ADA3]/40 text-sm text-[#5F6F5B] px-3 py-2 rounded-xl focus:border-[#D8A7B1] focus:outline-none datepicker">
                        </div>
                    </div>

                    <div class="flex justify-end gap-3 pt-3 border-t border-[#B6ADA3]/30">
                        <button type="button" @click="modalOpen = false"
                            class="px-4 py-2 text-xs font-semibold text-[#5F6F5B] hover:text-[#2D372E] cursor-pointer">Batal</button>
                        <button type="submit" class="btn-primary-rose px-5 py-2 rounded-xl text-xs cursor-pointer">Simpan
                            User</button>
                    </div>
                </form>

                <!-- FORM EDIT (server-side, pre-filled) -->
                <form :action="'{{ url('admin/users') }}/' + currentItem.id" method="POST" class="space-y-4"
                    x-show="editMode" x-cloak>
                    @csrf
                    @method('PUT')
                    <div>
                        <label for="edit_user_name" class="block text-xs font-semibold text-[#5F6F5B] mb-1">Nama Lengkap *</label>
                        <input type="text" id="edit_user_name" name="name" x-model="currentItem.name" required
                            aria-label="Nama Lengkap"
                            class="w-full bg-[#FAF7F2] border border-[#B6ADA3]/40 text-sm text-[#5F6F5B] px-3 py-2 rounded-xl focus:border-[#D8A7B1] focus:outline-none">
                    </div>
                    <div>
                        <label for="edit_user_email" class="block text-xs font-semibold text-[#5F6F5B] mb-1">Email *</label>
                        <input type="email" id="edit_user_email" name="email" x-model="currentItem.email" required
                            aria-label="Email"
                            class="w-full bg-[#FAF7F2] border border-[#B6ADA3]/40 text-sm text-[#5F6F5B] px-3 py-2 rounded-xl focus:border-[#D8A7B1] focus:outline-none">
                    </div>
                    <div>
                        <label for="edit_user_password" class="block text-xs font-semibold text-[#5F6F5B] mb-1">Password Baru</label>
                        <input type="text" id="edit_user_password" name="password" minlength="8"
                            aria-label="Password Baru"
                            class="w-full bg-[#FAF7F2] border border-[#B6ADA3]/40 text-sm text-[#5F6F5B] px-3 py-2 rounded-xl focus:border-[#D8A7B1] focus:outline-none"
                            placeholder="Kosongkan jika tidak diubah">
                    </div>
                    <div>
                        <label for="edit_user_wedding_id" class="block text-xs font-semibold text-[#5F6F5B] mb-1">Data Pernikahan</label>
                        <select id="edit_user_wedding_id" name="wedding_id" x-model="currentItem.wedding_id"
                            aria-label="Data Pernikahan"
                            class="w-full bg-[#FAF7F2] border border-[#B6ADA3]/40 text-sm text-[#5F6F5B] px-3 py-2 rounded-xl focus:border-[#D8A7B1] focus:outline-none">
                            <option value="">-- Belum dipasangkan --</option>
                            @foreach ($weddings as $w)
                                <option value="{{ $w->id }}">{{ $w->couple_name }} (#{{ $w->id }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    {{-- Status akun: menempel pada Wedding Kit, jadi hanya ada kalau user
                         sudah dipasangkan ke sebuah wedding. --}}
                    <div>
                        <label for="edit_user_plan" class="block text-xs font-semibold text-[#5F6F5B] mb-1">Status Akun</label>
                        <select id="edit_user_plan" name="plan" x-model="currentItem.plan" :disabled="!currentItem.wedding_id"
                            aria-label="Status Akun"
                            class="w-full bg-[#FAF7F2] border border-[#B6ADA3]/40 text-sm text-[#5F6F5B] px-3 py-2 rounded-xl focus:border-[#D8A7B1] focus:outline-none disabled:opacity-60 disabled:cursor-not-allowed">
                            <option value="free">Free</option>
                            <option value="pro">Pro</option>
                        </select>
                    </div>
                    <div>
                        <label for="edit_user_add_on" class="block text-xs font-semibold text-[#5F6F5B] mb-1">Add-on</label>
                        <select id="edit_user_add_on" name="add_on" x-model="currentItem.add_on" :disabled="!currentItem.wedding_id"
                            aria-label="Add-on"
                            class="w-full bg-[#FAF7F2] border border-[#B6ADA3]/40 text-sm text-[#5F6F5B] px-3 py-2 rounded-xl focus:border-[#D8A7B1] focus:outline-none disabled:opacity-60 disabled:cursor-not-allowed">
                            <option value="none">Tidak ada add-on</option>
                            <option value="photobooth">Photobooth</option>
                        </select>
                    </div>
                    <div class="rounded-xl border border-[#B6ADA3]/30 bg-[#FAF7F2]/50 p-3 space-y-3">
                        <span class="block text-xs font-semibold text-[#5F6F5B]">Pasangan</span>
                        <div>
                            <label for="edit_user_partner_name" class="block text-xs font-semibold text-[#5F6F5B] mb-1">Nama Pasangan</label>
                            <input type="text" id="edit_user_partner_name" name="partner_name" x-model="currentItem.partner_name"
                                :disabled="!currentItem.wedding_id"
                                aria-label="Nama Pasangan"
                                class="w-full bg-white border border-[#B6ADA3]/40 text-sm text-[#5F6F5B] px-3 py-2 rounded-xl focus:border-[#D8A7B1] focus:outline-none disabled:opacity-60 disabled:cursor-not-allowed"
                                placeholder="Contoh: Nabila">
                        </div>
                        <div>
                            <label for="edit_user_partner_email" class="block text-xs font-semibold text-[#5F6F5B] mb-1">Email Pasangan</label>
                            <input type="email" id="edit_user_partner_email" name="partner_email" x-model="currentItem.partner_email"
                                :disabled="!currentItem.wedding_id"
                                aria-label="Email Pasangan"
                                class="w-full bg-white border border-[#B6ADA3]/40 text-sm text-[#5F6F5B] px-3 py-2 rounded-xl focus:border-[#D8A7B1] focus:outline-none disabled:opacity-60 disabled:cursor-not-allowed"
                                placeholder="nabila@email.com">
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <input type="checkbox" id="edit_user_is_superadmin" name="is_superadmin" value="1" x-model="currentItem.is_superadmin"
                            aria-label="Jadikan superadmin (bisa akses menu Admin Panel)"
                            class="rounded">
                        <label for="edit_user_is_superadmin" class="text-sm text-[#5F6F5B] cursor-pointer">
                            Jadikan superadmin (bisa akses menu Admin Panel)
                        </label>
                    </div>
                    <div class="flex justify-end gap-3 pt-3 border-t border-[#B6ADA3]/30">
                        <button type="button" @click="modalOpen = false"
                            class="px-4 py-2 text-xs font-semibold text-[#5F6F5B] hover:text-[#2D372E] cursor-pointer">Batal</button>
                        <button type="submit" class="btn-primary-rose px-5 py-2 rounded-xl text-xs cursor-pointer">Simpan
                            User</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Modal cepat: tambah/ubah pasangan dari dropdown di list -->
        <div x-show="partnerModalOpen" x-transition.opacity x-cloak
            class="fixed inset-0 z-50 bg-[#5F6F5B]/40 backdrop-blur-md flex items-center justify-center p-4">
            <div x-outside="partnerModalOpen = false"
                class="bg-white w-full max-w-md p-6 rounded-2xl border border-[#B6ADA3]/40 shadow-2xl space-y-4">
                <div class="flex items-center justify-between border-b border-[#B6ADA3]/30 pb-3">
                    <h2 class="text-lg font-bold font-serif-title text-[#5F6F5B]">
                        Pasangan untuk <span x-text="partnerItem.name"></span>
                    </h2>
                    <button @click="partnerModalOpen = false" aria-label="Tutup modal" class="text-[#5F6F5B] hover:text-[#2D372E] cursor-pointer">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>

                <div x-show="!partnerItem.wedding_id" class="text-sm text-[#8B3A47] bg-[#D8A7B1]/15 rounded-xl p-3">
                    User ini belum dipasangkan ke Wedding Kit. Pasang dulu di form Edit User, baru bisa menambahkan
                    pasangan.
                </div>

                <form :action="'{{ url('admin/users') }}/' + partnerItem.id + '/partner'" method="POST"
                    class="space-y-4" x-show="partnerItem.wedding_id">
                    @csrf
                    @method('PUT')
                    <div>
                        <label for="partner_modal_name" class="block text-xs font-semibold text-[#5F6F5B] mb-1">Nama Pasangan</label>
                        <input type="text" id="partner_modal_name" name="partner_name" x-model="partnerItem.partner_name"
                            aria-label="Nama Pasangan"
                            class="w-full bg-[#FAF7F2] border border-[#B6ADA3]/40 text-sm text-[#5F6F5B] px-3 py-2 rounded-xl focus:border-[#D8A7B1] focus:outline-none"
                            placeholder="Contoh: Nabila">
                    </div>
                    <div>
                        <label for="partner_modal_email" class="block text-xs font-semibold text-[#5F6F5B] mb-1">Email Pasangan *</label>
                        <input type="email" id="partner_modal_email" name="partner_email" x-model="partnerItem.partner_email" required
                            aria-label="Email Pasangan"
                            class="w-full bg-[#FAF7F2] border border-[#B6ADA3]/40 text-sm text-[#5F6F5B] px-3 py-2 rounded-xl focus:border-[#D8A7B1] focus:outline-none"
                            placeholder="nabila@email.com">
                    </div>
                    <p class="text-[10px] text-[#5F6F5B]">Kalau email-nya sudah terdaftar, akun tersebut bergabung ke
                        Wedding Kit ini. Kalau belum, dibuatkan akun baru dan pasangan memakai &quot;Lupa
                        Password&quot; untuk masuk.</p>
                    <div class="flex justify-end gap-3 pt-3 border-t border-[#B6ADA3]/30">
                        <button type="button" @click="partnerModalOpen = false"
                            class="px-4 py-2 text-xs font-semibold text-[#5F6F5B] hover:text-[#2D372E] cursor-pointer">Batal</button>
                        <button type="submit" class="btn-primary-rose px-5 py-2 rounded-xl text-xs cursor-pointer">Simpan
                            Pasangan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
