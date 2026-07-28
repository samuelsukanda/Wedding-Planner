@extends('layouts.app')

@section('title', 'Keuangan & Dokumen - Wedding Gifts')

@section('content')
    <div class="space-y-6" x-data="{ modalOpen: false, editMode: false, currentItem: {} }">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold font-serif-title">Wedding Gifts & Souvenir</h1>
            </div>
            <div class="grid grid-cols-2 gap-3 w-full md:w-auto md:flex md:items-center md:gap-3">
                <a href="{{ route('gifts.export') }}"
                    class="px-4 py-2.5 rounded-xl bg-white text-[#5F6F5B] hover:bg-[#FAF7F2] border border-[#B6ADA3]/40 text-sm flex items-center justify-center gap-2 shadow-xs font-semibold w-full md:w-auto">
                    <i class="fa-solid fa-file-excel text-[#D8A7B1]"></i> Export Excel
                </a>
                <button @click="modalOpen = true; editMode = false; currentItem = {}"
                    class="btn-primary-rose px-4 py-2.5 rounded-xl text-sm flex items-center justify-center gap-2 cursor-pointer w-full md:w-auto">
                    <i class="fa-solid fa-plus"></i> Catat Hadiah Baru
                </button>
            </div>
        </div>

        <!-- Summary Widgets -->
        <div class="grid grid-cols-3 gap-3">
            <div class="bg-white p-3 sm:p-4 rounded-xl border border-[#B6ADA3]/35 shadow-xs text-center">
                <div class="text-xs text-[#5F6F5B] mb-1">Total Hadiah Tercatat</div>
                <div class="text-xl sm:text-2xl font-bold">{{ $gifts->count() }}</div>
                <div class="text-xs text-[#5F6F5B] font-medium mt-1">Item Terdaftar</div>
            </div>
            <div class="bg-white p-3 sm:p-4 rounded-xl border border-[#B6ADA3]/35 shadow-xs text-center">
                <div class="text-xs text-[#5F6F5B] mb-1">Total Amplop (Cash)</div>
                <div class="text-xl sm:text-2xl font-bold text-[#5F6F5B]">Rp {{ number_format($totalCash, 0, ',', '.') }}
                </div>
                <div class="text-xs text-[#5F6F5B] font-medium mt-1">+ {{ $totalGoodsCount }} Hadiah Barang</div>
            </div>
            <div class="bg-white p-3 sm:p-4 rounded-xl border border-[#B6ADA3]/35 shadow-xs text-center">
                <div class="text-xs text-[#5F6F5B] mb-1">Thank You Terkirim</div>
                <div class="text-xl sm:text-2xl font-bold text-[#5F6F5B]">{{ $thankYouSentCount }}</div>
                <div class="text-xs text-[#5F6F5B] font-medium mt-1">dari {{ $gifts->count() }} Total Tamu</div>
            </div>
        </div>

        <!-- Filter -->
        <form method="GET" action="{{ route('gifts.index') }}"
            class="bg-white p-4 rounded-xl border border-[#B6ADA3]/35 shadow-xs flex flex-col md:flex-row gap-3 items-center justify-between">
            <div class="flex flex-wrap items-center gap-3 w-full md:w-auto">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama pemberi..."
                    class="bg-[#FAF7F2] border border-[#B6ADA3]/40 text-xs text-[#5F6F5B] px-3 py-2 rounded-xl focus:border-[#D8A7B1] focus:outline-none w-full sm:w-48">
                <select name="type" onchange="this.form.submit()"
                    class="bg-[#FAF7F2] border border-[#B6ADA3]/40 text-xs text-[#5F6F5B] px-3 py-2 rounded-xl focus:border-[#D8A7B1] focus:outline-none w-full sm:w-auto">
                    <option value="">Semua Jenis Hadiah</option>
                    @foreach ($giftTypes as $gt)
                        <option value="{{ $gt }}" {{ request('type') == $gt ? 'selected' : '' }}>
                            {{ $gt }}</option>
                    @endforeach
                </select>
            </div>
            @if (request()->anyFilled(['search', 'type']))
                <a href="{{ route('gifts.index') }}"
                    class="text-xs text-[#5F6F5B] hover:text-[#5F6F5B] font-semibold hover:underline">Reset Filter</a>
            @endif
        </form>

        <!-- Gifts Table -->
        <div class="bg-white rounded-2xl border border-[#B6ADA3]/35 shadow-xs overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr
                            class="bg-[#FAF7F2] text-xs text-[#5F6F5B] uppercase tracking-wider border-b border-[#B6ADA3]/30">
                            <th class="p-4">Nama Pemberi Hadiah</th>
                            <th class="p-4">Jenis Hadiah</th>
                            <th class="p-4">Deskripsi / Barang</th>
                            <th class="p-4">Nominal (Rp)</th>
                            <th class="p-4">Tanggal</th>
                            <th class="p-4">Thank You</th>
                            <th class="p-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#B6ADA3]/20 text-sm">
                        @forelse($gifts as $g)
                            <tr class="hover:bg-[#FAF7F2]/60 transition-colors">
                                <td class="p-4 font-semibold text-[#5F6F5B]">{{ $g->giver_name }}</td>
                                <td class="p-4">
                                    <span
                                        class="px-2.5 py-1 rounded-full text-xs font-semibold {{ $g->gift_type == 'Cash' ? 'bg-[#A3B7A6]/25 text-[#5F6F5B] border border-[#A3B7A6]/40' : 'bg-[#D8A7B1]/25 text-[#5F6F5B] border border-[#D8A7B1]/40' }}">
                                        {{ $g->gift_type }}
                                    </span>
                                </td>
                                <td class="p-4 text-xs text-[#5F6F5B]/80 max-w-xs truncate">
                                    {{ $g->description ?? '—' }}</td>
                                <td class="p-4 font-bold text-[#5F6F5B]">
                                    {{ $g->nominal ? 'Rp ' . number_format($g->nominal, 0, ',', '.') : '—' }}
                                </td>
                                <td class="p-4 text-xs text-[#5F6F5B]/80 font-mono">
                                    {{ $g->created_at ? $g->created_at->format('d M Y') : '—' }}
                                </td>
                                <td class="p-4">
                                    <form action="{{ route('gifts.toggle-thank-you', $g->id) }}" method="POST">
                                        @csrf
                                        <button type="submit"
                                            class="px-2.5 py-1 rounded-full text-xs font-semibold transition-all cursor-pointer {{ $g->is_thank_you_sent ? 'bg-[#5F6F5B] text-white' : 'bg-[#FAF7F2] text-[#5F6F5B] border border-[#B6ADA3]/40' }}">
                                            <i class="fa-solid fa-heart text-[10px] mr-1"></i>
                                            {{ $g->is_thank_you_sent ? 'Terkirim' : 'Belum' }}
                                        </button>
                                    </form>
                                </td>
                                <td class="p-4 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <button
                                            @click="modalOpen = true; editMode = true; currentItem = {{ json_encode($g) }}"
                                            title="Edit" class="p-2 text-[#B6ADA3] hover:text-[#5F6F5B] cursor-pointer"><i
                                                class="fa-solid fa-pen-to-square"></i></button>
                                        <form id="del-gift-{{ $g->id }}"
                                            action="{{ route('gifts.destroy', $g->id) }}" method="POST">
                                            @csrf
                                            @method('DELETE')
                                            <button type="button" title="Hapus"
                                                onclick="confirmDelete('del-gift-{{ $g->id }}', '{{ addslashes($g->giver_name) }}')"
                                                class="p-2 text-[#B6ADA3] hover:text-[#5F6F5B] cursor-pointer"><i
                                                    class="fa-solid fa-trash"></i></button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-10 text-[#B6ADA3] text-sm">Belum ada hadiah yang
                                    tercatat.</td>
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
                        x-text="editMode ? 'Edit Catatan Hadiah' : 'Catat Hadiah Baru'"></h3>
                    <button @click="modalOpen = false" class="text-[#B6ADA3] hover:text-[#5F6F5B] cursor-pointer"><i
                            class="fa-solid fa-xmark"></i></button>
                </div>
                <form :action="editMode ? '/gifts/' + currentItem.id : '{{ route('gifts.store') }}'" method="POST"
                    class="space-y-4">
                    @csrf
                    <template x-if="editMode"><input type="hidden" name="_method" value="PUT"></template>
                    <div>
                        <label class="block text-xs font-semibold text-[#5F6F5B] mb-1">Nama Pemberi Hadiah *</label>
                        <input type="text" name="giver_name" x-model="currentItem.giver_name" required
                            class="w-full bg-[#FAF7F2] border border-[#B6ADA3]/40 text-sm text-[#5F6F5B] px-3 py-2 rounded-xl focus:border-[#D8A7B1] focus:outline-none">
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-[#5F6F5B] mb-1">Jenis Hadiah *</label>
                            <select name="gift_type" x-model="currentItem.gift_type" required
                                class="w-full bg-[#FAF7F2] border border-[#B6ADA3]/40 text-sm text-[#5F6F5B] px-3 py-2 rounded-xl focus:border-[#D8A7B1] focus:outline-none">
                                @foreach ($giftTypes as $gt)
                                    <option value="{{ $gt }}">{{ $gt }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-[#5F6F5B] mb-1">Nominal / Estimasi Harga</label>
                            <input type="number" name="nominal" x-model="currentItem.nominal"
                                class="w-full bg-[#FAF7F2] border border-[#B6ADA3]/40 text-sm text-[#5F6F5B] px-3 py-2 rounded-xl focus:border-[#D8A7B1] focus:outline-none">
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-[#5F6F5B] mb-1">Deskripsi Hadiah</label>
                        <textarea name="description" x-model="currentItem.description" rows="2"
                            class="w-full bg-[#FAF7F2] border border-[#B6ADA3]/40 text-sm text-[#5F6F5B] px-3 py-2 rounded-xl focus:border-[#D8A7B1] focus:outline-none"></textarea>
                    </div>
                    <div class="flex items-center gap-3 p-3 rounded-xl bg-[#FAF7F2] border border-[#B6ADA3]/30">
                        <input type="hidden" name="is_thank_you_sent" value="0">
                        <input type="checkbox" id="thankYouSent" name="is_thank_you_sent" value="1"
                            :checked="currentItem.is_thank_you_sent == 1"
                            class="w-4 h-4 accent-[#D8A7B1] rounded cursor-pointer">
                        <label for="thankYouSent" class="text-sm text-[#5F6F5B] font-medium cursor-pointer">Ucapan Terima
                            Kasih sudah terkirim</label>
                    </div>
                    <div class="flex justify-end gap-3 pt-3 border-t border-[#B6ADA3]/30">
                        <button type="button" @click="modalOpen = false"
                            class="px-4 py-2 text-xs text-[#B6ADA3] hover:text-[#5F6F5B] cursor-pointer">Batal</button>
                        <button type="submit" class="btn-primary-rose px-5 py-2 rounded-xl text-xs cursor-pointer">Simpan
                            Catatan Hadiah</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
