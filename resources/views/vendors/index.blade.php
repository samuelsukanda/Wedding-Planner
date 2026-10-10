@extends('layouts.app')

@section('title', 'Perencanaan - Vendor Management')

@section('content')
    <div class="space-y-6" x-data="{ modalOpen: false, editMode: false, currentItem: {}, detailModalOpen: false, detailVendor: null }">
        <!-- Top Bar -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold font-serif-title">Vendor Management</h1>
            </div>
            <button @click="modalOpen = true; editMode = false; currentItem = {}"
                class="btn-primary-rose px-4 py-2.5 rounded-xl text-sm flex items-center justify-center gap-2 cursor-pointer w-full md:w-auto">
                <i class="fa-solid fa-plus"></i> Tambah Vendor Baru
            </button>
        </div>

        <!-- Filter Bar -->
        <form method="GET" action="{{ route('vendors.index') }}"
            class="bg-white p-4 rounded-xl border border-[#B6ADA3]/35 shadow-xs flex flex-col md:flex-row gap-3 items-center justify-between">
            <div class="w-full md:w-auto md:flex md:flex-row md:items-center md:gap-3 space-y-3 md:space-y-0">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama vendor..."
                    class="bg-[#FAF7F2] border border-[#B6ADA3]/40 text-xs text-[#5F6F5B] px-3 py-2 rounded-xl focus:border-[#D8A7B1] focus:outline-none w-full md:w-56">
                <div class="grid grid-cols-2 gap-3 md:flex md:items-center md:gap-3">
                    <select name="category" onchange="this.form.submit()"
                        class="bg-[#FAF7F2] border border-[#B6ADA3]/40 text-xs text-[#5F6F5B] px-3 py-2 rounded-xl focus:border-[#D8A7B1] focus:outline-none w-full md:w-auto">
                        <option value="">Semua Kategori Vendor</option>
                        @foreach ($categories as $cat)
                            <option value="{{ $cat }}" {{ request('category') == $cat ? 'selected' : '' }}>
                                {{ $cat }}</option>
                        @endforeach
                    </select>
                    <select name="status" onchange="this.form.submit()"
                        class="bg-[#FAF7F2] border border-[#B6ADA3]/40 text-xs text-[#5F6F5B] px-3 py-2 rounded-xl focus:border-[#D8A7B1] focus:outline-none w-full md:w-auto">
                        <option value="">Semua Status Booking</option>
                        @foreach ($statuses as $st)
                            <option value="{{ $st }}" {{ request('status') == $st ? 'selected' : '' }}>
                                {{ $st }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            @if (request()->anyFilled(['search', 'category', 'status']))
                <a href="{{ route('vendors.index') }}"
                    class="text-xs text-[#5F6F5B] hover:text-[#5F6F5B] font-semibold hover:underline">Reset Filter</a>
            @endif
        </form>

        <!-- Vendor Cards Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            @forelse($vendors as $v)
                <div @click="detailVendor = {{ json_encode($v) }}; detailModalOpen = true"
                    class="bg-white rounded-2xl border border-[#B6ADA3]/35 shadow-xs p-5 flex flex-col justify-between hover:border-[#D8A7B1] hover:shadow-md cursor-pointer transition-all space-y-4 group">
                    <div>
                        <div class="flex items-start justify-between gap-3 mb-3">
                            <div>
                                <span
                                    class="text-[11px] font-semibold text-[#5F6F5B] bg-[#A3B7A6]/20 px-2.5 py-1 rounded-full border border-[#A3B7A6]/40">{{ $v->category }}</span>
                                <h3 class="font-bold text-[#5F6F5B] text-lg mt-2 leading-snug group-hover:text-[#D8A7B1] transition-colors flex items-center gap-1.5"
                                    title="Klik untuk lihat detail & isi paketan vendor">
                                    <span>{{ $v->name }}</span>
                                    <i
                                        class="fa-solid fa-chevron-right text-xs opacity-0 group-hover:opacity-100 transition-all transform group-hover:translate-x-0.5"></i>
                                </h3>
                            </div>
                            <span
                                class="px-2.5 py-1 rounded-full text-xs font-semibold shrink-0
                            @if ($v->booking_status == 'Completed' || $v->booking_status == 'Booked') bg-[#A3B7A6]/25 text-[#5F6F5B] border border-[#A3B7A6]/40
                            @elseif($v->booking_status == 'Negotiating') bg-[#D8A7B1]/25 text-[#5F6F5B] border border-[#D8A7B1]/40
                            @else bg-[#FAF7F2] text-[#5F6F5B] border border-[#B6ADA3]/40 @endif">
                                {{ $v->booking_status }}
                            </span>
                        </div>
                        <div class="space-y-2 text-xs text-[#5F6F5B]/80">
                            @if ($v->package)
                                <div><i class="fa-solid fa-box text-[#D8A7B1] mr-2"></i> {{ $v->package }}</div>
                            @endif
                            <div class="text-[#5F6F5B] font-bold text-sm">Rp {{ number_format($v->price, 0, ',', '.') }}
                            </div>

                            @if ($v->packageItems->count() > 0)
                                <div class="pt-1 flex flex-wrap gap-1">
                                    @foreach ($v->packageItems->take(3) as $item)
                                        <span
                                            class="inline-flex items-center gap-1 text-[10px] bg-[#FAF7F2] text-[#5F6F5B] px-2 py-0.5 rounded-md border border-[#B6ADA3]/30">
                                            <i class="fa-solid fa-check text-[9px] text-[#A3B7A6]"></i> {{ $item->name }}
                                        </span>
                                    @endforeach
                                    @if ($v->packageItems->count() > 3)
                                        <span
                                            class="text-[10px] text-[#B6ADA3] font-semibold self-center">+{{ $v->packageItems->count() - 3 }}
                                            item lainnya</span>
                                    @endif
                                </div>
                            @endif

                            @if ($v->contact)
                                <div class="flex items-center gap-2" @click.stop>
                                    <i class="fa-brands fa-whatsapp text-[#D8A7B1]"></i> {{ $v->contact }}
                                </div>
                            @endif
                            @if ($v->address)
                                <div class="flex items-center gap-2"><i class="fa-solid fa-location-dot text-[#D8A7B1]"></i>
                                    {{ $v->address }}</div>
                            @endif
                            @if ($v->google_maps_url)
                                <a href="{{ $v->google_maps_url }}" target="_blank" @click.stop
                                    class="inline-flex items-center gap-1 text-[11px] text-[#5F6F5B] hover:text-[#D8A7B1] hover:underline font-semibold">
                                    <i class="fa-solid fa-map-location-dot"></i> Buka Google Maps &rarr;
                                </a>
                            @endif
                        </div>
                        @if ($v->review)
                            <div
                                class="mt-3 p-3 rounded-xl bg-[#FAF7F2] border border-[#B6ADA3]/30 text-xs italic text-[#5F6F5B]/80">
                                "{{ $v->review }}"
                            </div>
                        @endif
                    </div>
                    <div class="pt-3 border-t border-[#B6ADA3]/25 flex items-center justify-between text-xs">
                        <div class="flex items-center gap-1 text-[#5F6F5B] font-bold">
                            <i class="fa-solid fa-star text-amber-400"></i> {{ number_format($v->rating, 1) }} / 5.0
                        </div>
                        <div class="flex items-center gap-2" @click.stop>
                            <button @click="modalOpen = true; editMode = true; currentItem = {{ json_encode($v) }}"
                                title="Edit" class="p-2 text-[#B6ADA3] hover:text-[#5F6F5B] cursor-pointer">
                                <i class="fa-solid fa-pen-to-square"></i>
                            </button>
                            <form id="del-vendor-{{ $v->id }}" action="{{ route('vendors.destroy', $v->id) }}"
                                method="POST">
                                @csrf
                                @method('DELETE')
                                <button type="button" title="Hapus"
                                    onclick="confirmDelete('del-vendor-{{ $v->id }}', '{{ addslashes($v->name) }}')"
                                    class="p-2 text-[#B6ADA3] hover:text-[#5F6F5B] cursor-pointer">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            @empty
                <div
                    class="col-span-full text-center py-12 bg-white rounded-2xl text-[#B6ADA3] text-sm border border-[#B6ADA3]/35">
                    Belum ada data vendor ditemukan.
                </div>
            @endforelse
        </div>

        <!-- Modal Form (Tambah / Edit Vendor) -->
        <div x-show="modalOpen" x-transition.opacity
            class="fixed inset-0 z-50 bg-[#5F6F5B]/40 backdrop-blur-md flex items-center justify-center p-4">
            <div @click.away="modalOpen = false"
                class="bg-white w-full max-w-lg p-6 rounded-2xl border border-[#B6ADA3]/40 shadow-2xl space-y-4 max-h-[90vh] overflow-y-auto">
                <div class="flex items-center justify-between border-b border-[#B6ADA3]/30 pb-3">
                    <h3 class="text-lg font-bold font-serif-title text-[#5F6F5B]"
                        x-text="editMode ? 'Edit Vendor' : 'Tambah Vendor Baru'"></h3>
                    <button @click="modalOpen = false" class="text-[#B6ADA3] hover:text-[#5F6F5B] cursor-pointer"><i
                            class="fa-solid fa-xmark"></i></button>
                </div>
                <form :action="editMode ? '/vendors/' + currentItem.id : '{{ route('vendors.store') }}'" method="POST"
                    class="space-y-4">
                    @csrf
                    <template x-if="editMode"><input type="hidden" name="_method" value="PUT"></template>

                    <div>
                        <label class="block text-xs font-semibold text-[#5F6F5B] mb-1">Nama Vendor *</label>
                        <input type="text" name="name" x-model="currentItem.name" required
                            class="w-full bg-[#FAF7F2] border border-[#B6ADA3]/40 text-sm text-[#5F6F5B] px-3 py-2 rounded-xl focus:border-[#D8A7B1] focus:outline-none">
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-[#5F6F5B] mb-1">Kategori *</label>
                            <select name="category" x-model="currentItem.category" required
                                class="w-full bg-[#FAF7F2] border border-[#B6ADA3]/40 text-sm text-[#5F6F5B] px-3 py-2 rounded-xl focus:border-[#D8A7B1] focus:outline-none">
                                <option value="">-- Pilih Kategori --</option>
                                @foreach ($categories as $cat)
                                    <option value="{{ $cat }}">{{ $cat }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-[#5F6F5B] mb-1">Status Booking *</label>
                            <select name="booking_status" x-model="currentItem.booking_status" required
                                class="w-full bg-[#FAF7F2] border border-[#B6ADA3]/40 text-sm text-[#5F6F5B] px-3 py-2 rounded-xl focus:border-[#D8A7B1] focus:outline-none">
                                @foreach ($statuses as $st)
                                    <option value="{{ $st }}">{{ $st }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-[#5F6F5B] mb-1">Nomor Kontak / WA</label>
                            <input type="text" name="contact" inputmode="numeric" :value="wpPhone(currentItem.contact)"
                                @input="currentItem.contact = wpDigits($event.target.value)"
                                class="w-full bg-[#FAF7F2] border border-[#B6ADA3]/40 text-sm text-[#5F6F5B] px-3 py-2 rounded-xl focus:border-[#D8A7B1] focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-[#5F6F5B] mb-1">Harga Paket (Rp) *</label>
                            <input type="text" name="price" inputmode="numeric" required :value="wpMoney(currentItem.price)"
                                @input="currentItem.price = wpDigits($event.target.value)"
                                class="w-full bg-[#FAF7F2] border border-[#B6ADA3]/40 text-sm text-[#5F6F5B] px-3 py-2 rounded-xl focus:border-[#D8A7B1] focus:outline-none">
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-[#5F6F5B] mb-1">Nama Paket</label>
                        <input type="text" name="package" x-model="currentItem.package"
                            class="w-full bg-[#FAF7F2] border border-[#B6ADA3]/40 text-sm text-[#5F6F5B] px-3 py-2 rounded-xl focus:border-[#D8A7B1] focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-[#5F6F5B] mb-1">Alamat</label>
                        <input type="text" name="address" x-model="currentItem.address"
                            class="w-full bg-[#FAF7F2] border border-[#B6ADA3]/40 text-sm text-[#5F6F5B] px-3 py-2 rounded-xl focus:border-[#D8A7B1] focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-[#5F6F5B] mb-1">Link Google Maps URL</label>
                        <input type="url" name="google_maps_url" x-model="currentItem.google_maps_url"
                            class="w-full bg-[#FAF7F2] border border-[#B6ADA3]/40 text-sm text-[#5F6F5B] px-3 py-2 rounded-xl focus:border-[#D8A7B1] focus:outline-none">
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-[#5F6F5B] mb-1">Rating (0 - 5)</label>
                            <input type="number" step="0.1" name="rating" x-model="currentItem.rating"
                                class="w-full bg-[#FAF7F2] border border-[#B6ADA3]/40 text-sm text-[#5F6F5B] px-3 py-2 rounded-xl focus:border-[#D8A7B1] focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-[#5F6F5B] mb-1">Review / Catatan</label>
                            <input type="text" name="review" x-model="currentItem.review"
                                class="w-full bg-[#FAF7F2] border border-[#B6ADA3]/40 text-sm text-[#5F6F5B] px-3 py-2 rounded-xl focus:border-[#D8A7B1] focus:outline-none">
                        </div>
                    </div>
                    <div class="flex justify-end gap-3 pt-3 border-t border-[#B6ADA3]/30">
                        <button type="button" @click="modalOpen = false"
                            class="px-4 py-2 text-xs text-[#B6ADA3] hover:text-[#5F6F5B] cursor-pointer">Batal</button>
                        <button type="submit" class="btn-primary-rose px-5 py-2 rounded-xl text-xs cursor-pointer">Simpan
                            Vendor</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Modal Detail / Show Vendor -->
        <div x-show="detailModalOpen" x-transition.opacity
            class="fixed inset-0 z-50 bg-[#5F6F5B]/40 backdrop-blur-md flex items-center justify-center p-4">
            <div @click.away="detailModalOpen = false"
                class="bg-white w-full max-w-2xl p-6 rounded-2xl border border-[#B6ADA3]/40 shadow-2xl space-y-6 max-h-[90vh] overflow-y-auto">
                <!-- Modal Header -->
                <div class="flex items-start justify-between border-b border-[#B6ADA3]/30 pb-4">
                    <div class="space-y-1">
                        <div class="flex items-center gap-2">
                            <span
                                class="text-xs font-semibold text-[#5F6F5B] bg-[#A3B7A6]/20 px-2.5 py-1 rounded-full border border-[#A3B7A6]/40"
                                x-text="detailVendor ? detailVendor.category : ''"></span>
                            <span class="px-2.5 py-1 rounded-full text-xs font-semibold"
                                :class="{
                                    'bg-[#A3B7A6]/25 text-[#5F6F5B] border border-[#A3B7A6]/40': detailVendor && (
                                        detailVendor.booking_status === 'Completed' || detailVendor
                                        .booking_status === 'Booked'),
                                    'bg-[#D8A7B1]/25 text-[#5F6F5B] border border-[#D8A7B1]/40': detailVendor &&
                                        detailVendor.booking_status === 'Negotiating',
                                    'bg-[#FAF7F2] text-[#5F6F5B] border border-[#B6ADA3]/40': detailVendor && (
                                        detailVendor.booking_status !== 'Completed' && detailVendor
                                        .booking_status !== 'Booked' && detailVendor
                                        .booking_status !== 'Negotiating')
                                }"
                                x-text="detailVendor ? detailVendor.booking_status : ''"></span>
                        </div>
                        <h2 class="text-2xl font-bold font-serif-title text-[#5F6F5B]"
                            x-text="detailVendor ? detailVendor.name : ''"></h2>
                    </div>
                    <button @click="detailModalOpen = false"
                        class="text-[#B6ADA3] hover:text-[#5F6F5B] cursor-pointer p-1"><i
                            class="fa-solid fa-xmark text-lg"></i></button>
                </div>

                <!-- Vendor Info Grid -->
                <div
                    class="grid grid-cols-1 md:grid-cols-2 gap-4 p-4 rounded-xl bg-[#FAF7F2]/60 border border-[#B6ADA3]/30 text-xs text-[#5F6F5B]">
                    <div class="space-y-2">
                        <div>
                            <span class="text-[#B6ADA3] font-medium block">Nama Paket:</span>
                            <span class="font-bold text-sm"
                                x-text="detailVendor && detailVendor.package ? detailVendor.package : 'Standard Package'"></span>
                        </div>
                        <div>
                            <span class="text-[#B6ADA3] font-medium block">Harga Paket:</span>
                            <span class="font-bold text-base text-[#5F6F5B]"
                                x-text="detailVendor ? 'Rp ' + Number(detailVendor.price).toLocaleString('id-ID') : 'Rp 0'"></span>
                        </div>
                        <div x-show="detailVendor && detailVendor.contact">
                            <span class="text-[#B6ADA3] font-medium block">Kontak / WhatsApp:</span>
                            <a :href="'https://wa.me/' + (detailVendor ? detailVendor.contact : '')" target="_blank"
                                class="font-semibold text-[#5F6F5B] hover:underline inline-flex items-center gap-1">
                                <i class="fa-brands fa-whatsapp text-[#D8A7B1]"></i> <span
                                    x-text="detailVendor ? detailVendor.contact : ''"></span>
                            </a>
                        </div>
                    </div>
                    <div class="space-y-2">
                        <div>
                            <span class="text-[#B6ADA3] font-medium block">Rating & Review:</span>
                            <span class="font-bold inline-flex items-center gap-1">
                                <i class="fa-solid fa-star text-amber-400"></i>
                                <span x-text="detailVendor ? detailVendor.rating : '5.0'"></span> / 5.0
                            </span>
                            <p class="italic text-[#5F6F5B]/80 mt-1"
                                x-text="detailVendor && detailVendor.review ? '&quot;' + detailVendor.review + '&quot;' : 'Belum ada catatan review.'">
                            </p>
                        </div>
                        <div x-show="detailVendor && detailVendor.address">
                            <span class="text-[#B6ADA3] font-medium block">Alamat:</span>
                            <span x-text="detailVendor ? detailVendor.address : ''"></span>
                        </div>
                        <div x-show="detailVendor && detailVendor.google_maps_url">
                            <a :href="detailVendor ? detailVendor.google_maps_url : '#'" target="_blank"
                                class="inline-flex items-center gap-1 text-[#5F6F5B] hover:underline font-semibold mt-1">
                                <i class="fa-solid fa-map-location-dot"></i> Lihat di Google Maps &rarr;
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Section: Detail Isi Paketan Vendor -->
                <div class="space-y-4 pt-2">
                    <div class="flex items-center justify-between border-b border-[#B6ADA3]/20 pb-2">
                        <div>
                            <h3 class="font-bold text-base font-serif-title text-[#5F6F5B] flex items-center gap-2">
                                <i class="fa-solid fa-boxes-packing text-[#D8A7B1]"></i> Detail & Isi Paketan Vendor
                            </h3>
                            <p class="text-xs text-[#5F6F5B]/70">Daftar item kelengkapan, fasilitas, dan bonus dalam
                                paketan ini.</p>
                        </div>
                    </div>

                    <!-- Items List -->
                    <div class="space-y-2 max-h-56 overflow-y-auto pr-1">
                        <template
                            x-if="detailVendor && detailVendor.package_items && detailVendor.package_items.length > 0">
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                <template x-for="item in detailVendor.package_items" :key="item.id">
                                    <div
                                        class="p-3 bg-white rounded-xl border border-[#B6ADA3]/35 flex items-center justify-between shadow-2xs hover:border-[#D8A7B1] transition-all group">
                                        <div class="flex items-center gap-2.5 overflow-hidden">
                                            <span
                                                class="w-6 h-6 rounded-full bg-[#A3B7A6]/20 text-[#5F6F5B] flex items-center justify-center shrink-0 text-xs font-bold">
                                                <i class="fa-solid fa-check text-[10px]"></i>
                                            </span>
                                            <div>
                                                <span class="text-xs font-bold text-[#5F6F5B] block truncate"
                                                    x-text="item.name"></span>
                                                <span class="text-[10px] text-[#B6ADA3] font-semibold"
                                                    x-text="item.type || 'Include'"></span>
                                            </div>
                                        </div>
                                        <form :id="'del-pkg-' + item.id" :action="'/vendors/package-items/' + item.id"
                                            method="POST" @click.stop>
                                            @csrf
                                            @method('DELETE')
                                            <button type="button" title="Hapus"
                                                onclick="confirmDelete('del-pkg-' + item.id, 'item paketan ini')"
                                                class="text-[#B6ADA3] hover:text-[#5F6F5B] p-1.5 cursor-pointer">
                                                <i class="fa-solid fa-trash text-xs"></i>
                                            </button>
                                        </form>
                                    </div>
                                </template>
                            </div>
                        </template>
                        <template
                            x-if="!detailVendor || !detailVendor.package_items || detailVendor.package_items.length === 0">
                            <div
                                class="text-center py-6 bg-[#FAF7F2] rounded-xl border border-dashed border-[#B6ADA3]/40 text-xs text-[#B6ADA3]">
                                <i class="fa-solid fa-box-open text-2xl mb-1 text-[#D8A7B1] block"></i>
                                Belum ada rincian item/bonus paketan untuk vendor ini. Silakan tambahkan di bawah.
                            </div>
                        </template>
                    </div>

                    <!-- Form Tambah Detail Item Paketan -->
                    <form :action="'/vendors/' + (detailVendor ? detailVendor.id : '') + '/package-items'" method="POST"
                        class="p-3 bg-[#FAF7F2] rounded-xl border border-[#B6ADA3]/40 space-y-3">
                        @csrf
                        <div class="text-xs font-bold text-[#5F6F5B] flex items-center gap-1.5">
                            <i class="fa-solid fa-plus-circle text-[#D8A7B1]"></i> Tambah Detail Isi Paketan / Bonus Baru
                        </div>
                        <div class="flex flex-col sm:flex-row gap-2">
                            <input type="text" name="name" required
                                placeholder="Contoh: Bonus Dekorasi Photobooth, Live Music Acoustic 3 Singer, dll."
                                class="bg-white border border-[#B6ADA3]/40 text-xs text-[#5F6F5B] px-3 py-2 rounded-xl focus:border-[#D8A7B1] focus:outline-none flex-1">
                            <select name="type"
                                class="bg-white border border-[#B6ADA3]/40 text-xs text-[#5F6F5B] px-3 py-2 rounded-xl focus:border-[#D8A7B1] focus:outline-none w-full sm:w-32">
                                <option value="Include">Include</option>
                                <option value="Bonus">Bonus</option>
                                <option value="Utama">Utama</option>
                                <option value="Fasilitas">Fasilitas</option>
                            </select>
                            <button type="submit"
                                class="btn-primary-rose px-4 py-2 rounded-xl text-xs font-semibold flex items-center justify-center gap-1 cursor-pointer shrink-0">
                                <i class="fa-solid fa-plus"></i> Tambah
                            </button>
                        </div>
                    </form>
                </div>

                <div class="flex justify-end pt-3 border-t border-[#B6ADA3]/30">
                    <button type="button" @click="detailModalOpen = false"
                        class="px-5 py-2 text-xs font-semibold bg-[#FAF7F2] text-[#5F6F5B] border border-[#B6ADA3]/40 rounded-xl hover:bg-[#efe9e1] cursor-pointer">
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection
