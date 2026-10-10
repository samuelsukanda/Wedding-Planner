@extends('layouts.app')

@section('title', 'Daftar Seserahan')

@section('content')
    <div class="space-y-6" x-data="{
        modalOpen: false,
        editMode: false,
        currentItem: { status: 'Belum Dipilih', planned_price: '', actual_cost: '' },
    }">

        {{-- Header --}}
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold font-serif-title">Daftar Seserahan</h1>
                <p class="text-xs text-[#B6ADA3] mt-1">Katalog souvenir untuk tamu. Item yang sudah diterima otomatis masuk ke Budget Planner.</p>
            </div>
            <button @click="modalOpen = true; editMode = false; currentItem = { status: 'Belum Dipilih', planned_price: '', actual_cost: '' }"
                class="btn-primary-rose px-4 py-2.5 rounded-xl text-sm flex items-center justify-center gap-2 cursor-pointer">
                <i class="fa-solid fa-plus"></i> Tambah Seserahan
            </button>
        </div>

        {{-- Ringkasan --}}
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
            <div class="bg-white p-4 rounded-2xl border border-[#B6ADA3]/35 shadow-xs">
                <div class="text-xs text-[#5F6F5B]/80 mb-1">Total Item</div>
                <div class="text-lg font-bold text-[#5F6F5B]">{{ $summary['total'] }}</div>
            </div>
            <div class="bg-white p-4 rounded-2xl border border-[#B6ADA3]/35 shadow-xs">
                <div class="text-xs text-[#5F6F5B]/80 mb-1">Sudah Diterima</div>
                <div class="text-lg font-bold text-[#5F6F5B]">{{ $summary['received'] }} / {{ $summary['total'] }}</div>
            </div>
            <div class="bg-white p-4 rounded-2xl border border-[#B6ADA3]/35 shadow-xs">
                <div class="text-xs text-[#5F6F5B]/80 mb-1">Rencana</div>
                <div class="text-lg font-bold text-[#5F6F5B]">Rp {{ number_format($summary['planned'], 0, ',', '.') }}</div>
            </div>
            <div class="bg-white p-4 rounded-2xl border border-[#D8A7B1]/50 shadow-xs">
                <div class="text-xs text-[#5F6F5B]/80 mb-1">Realisasi</div>
                <div class="text-lg font-bold text-[#C2757F]">Rp {{ number_format($summary['actual'], 0, ',', '.') }}</div>
                <div class="text-[10px] text-[#B6ADA3] mt-0.5">Dari item diterima</div>
            </div>
        </div>

        {{-- Tabel --}}
        <div class="bg-white rounded-2xl border border-[#B6ADA3]/35 shadow-xs overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-left text-[10px] uppercase tracking-wider text-[#B6ADA3] border-b border-[#B6ADA3]/25 bg-[#FAF7F2]">
                            <th class="py-3 px-4 font-semibold">Item</th>
                            <th class="py-3 px-4 font-semibold">Vendor</th>
                            <th class="py-3 px-4 font-semibold text-right">Rencana</th>
                            <th class="py-3 px-4 font-semibold text-right">Aktual</th>
                            <th class="py-3 px-4 font-semibold text-right">Selisih</th>
                            <th class="py-3 px-4 font-semibold">Status</th>
                            <th class="py-3 px-4 font-semibold text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($souvenirs as $item)
                            <tr class="border-b border-[#B6ADA3]/15 last:border-0 hover:bg-[#FAF7F2]/60">
                                <td class="py-3 px-4">
                                    <div class="flex items-center gap-3">
                                        @if ($item->photo)
                                            <img src="{{ $item->photo }}" alt="{{ $item->name }}"
                                                class="w-10 h-10 rounded-lg object-cover border border-[#B6ADA3]/30">
                                        @else
                                            <div class="w-10 h-10 rounded-lg bg-[#5F6F5B]/8 flex items-center justify-center">
                                                <i class="fa-solid fa-gift text-[#B6ADA3] text-xs"></i>
                                            </div>
                                        @endif
                                        <div>
                                            <div class="font-medium text-[#5F6F5B]">{{ $item->name }}</div>
                                            @if ($item->link)
                                                <a href="{{ $item->link }}" target="_blank" rel="noopener noreferrer"
                                                    class="text-[10px] text-[#C2757F] hover:underline">
                                                    <i class="fa-solid fa-link"></i> Lihat link
                                                </a>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td class="py-3 px-4 text-xs text-[#B6ADA3]">{{ $item->vendor?->name ?: '-' }}</td>
                                <td class="py-3 px-4 text-xs text-right">Rp {{ number_format($item->planned_price, 0, ',', '.') }}</td>
                                <td class="py-3 px-4 text-xs text-right">Rp {{ number_format($item->actual_cost, 0, ',', '.') }}</td>
                                <td @class([
                                    'py-3 px-4 text-xs text-right font-semibold',
                                    'text-[#C2757F]' => $item->variance() < 0,
                                    'text-[#5F6F5B]' => $item->variance() >= 0,
                                ])>
                                    {{ $item->variance() >= 0 ? '+' : '-' }} Rp {{ number_format(abs($item->variance()), 0, ',', '.') }}
                                </td>
                                <td class="py-3 px-4">
                                    @if ($item->isReceived())
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-[#5F6F5B]/12 text-[#5F6F5B]">
                                            Diterima
                                        </span>
                                        @if ($item->received_date)
                                            <div class="text-[10px] text-[#B6ADA3] mt-0.5">{{ $item->received_date->format('d M Y') }}</div>
                                        @endif
                                    @else
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-[#B6ADA3]/20 text-[#5F6F5B]">
                                            {{ $item->status }}
                                        </span>
                                    @endif
                                </td>
                                <td class="py-3 px-4 text-right space-x-2 whitespace-nowrap">
                                    <button @click="modalOpen = true; editMode = true; currentItem = {{ Js::from($item) }}"
                                        class="text-[#5F6F5B] hover:text-[#C2757F] text-xs cursor-pointer">
                                        <i class="fa-solid fa-pen"></i>
                                    </button>
                                    <form method="POST" action="{{ route('souvenirs.destroy', $item) }}" class="inline"
                                        onsubmit="return confirm('Hapus item seserahan ini beserta pengeluarannya?')">
                                        @csrf @method('DELETE')
                                        <button class="text-[#C2757F] hover:text-red-700 text-xs cursor-pointer">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-12 text-center">
                                    <i class="fa-solid fa-gift text-[#B6ADA3] text-2xl mb-3"></i>
                                    <div class="text-xs text-[#B6ADA3]">Belum ada item seserahan.</div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Modal --}}
        <div x-show="modalOpen" x-cloak
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4"
            @click.self="modalOpen = false" @keydown.escape.window="modalOpen = false">
            <div class="bg-white rounded-2xl shadow-xl w-full max-w-lg max-h-[90vh] overflow-y-auto">
                <div class="p-5 border-b border-[#B6ADA3]/25 flex items-center justify-between">
                    <h3 class="font-bold text-[#5F6F5B]" x-text="editMode ? 'Edit Item Seserahan' : 'Tambah Item Seserahan'"></h3>
                    <button @click="modalOpen = false" class="text-[#B6ADA3] hover:text-[#5F6F5B] cursor-pointer">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>

                {{-- action diturunkan dari currentItem.id, bukan flag editMode: kalau
                     editMode di-set di handler yang sama, Alpine bisa
                     mengevaluasi :action sebelum currentItem ter-assign. --}}
                <form :action="(currentItem && currentItem.id) ? '{{ url('/souvenirs') }}/' + currentItem.id : '{{ route('souvenirs.store') }}'"
                    method="POST" class="p-5 space-y-3">
                    @csrf
                    <template x-if="currentItem && currentItem.id"><input type="hidden" name="_method" value="PUT"></template>

                    <div>
                        <label class="block text-xs font-semibold text-[#5F6F5B] mb-1.5">Nama Item <span class="text-[#C2757F]">*</span></label>
                        <input type="text" name="name" x-model="currentItem.name" required
                            placeholder="mis. Mug Keramik Custom"
                            class="form-input w-full rounded-xl border border-[#B6ADA3]/40 px-3 py-2 text-sm">
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-[#5F6F5B] mb-1.5">Vendor</label>
                            <select name="vendor_id" x-model="currentItem.vendor_id"
                                class="form-input w-full rounded-xl border border-[#B6ADA3]/40 px-3 py-2 text-sm">
                                <option value="">-</option>
                                @foreach ($wedding->vendors()->orderBy('name')->get() as $v)
                                    <option value="{{ $v->id }}">{{ $v->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-[#5F6F5B] mb-1.5">Status</label>
                            <select name="status" x-model="currentItem.status"
                                class="form-input w-full rounded-xl border border-[#B6ADA3]/40 px-3 py-2 text-sm">
                                @foreach ($statuses as $opt)
                                    <option value="{{ $opt }}">{{ $opt }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-[#5F6F5B] mb-1.5">Harga Rencana <span class="text-[#C2757F]">*</span></label>
                            <input type="number" name="planned_price" x-model="currentItem.planned_price" required min="0" step="0.01"
                                class="form-input w-full rounded-xl border border-[#B6ADA3]/40 px-3 py-2 text-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-[#5F6F5B] mb-1.5">Harga Beli</label>
                            <input type="number" name="actual_cost" x-model="currentItem.actual_cost" min="0" step="0.01"
                                class="form-input w-full rounded-xl border border-[#B6ADA3]/40 px-3 py-2 text-sm">
                        </div>
                    </div>

                    <p class="text-[10px] text-[#B6ADA3] bg-[#FAF7F2] rounded-lg px-3 py-2">
                        <i class="fa-solid fa-circle-info mr-1"></i>
                        Saat status <strong>Diterima</strong>, harga beli otomatis tercatat di Budget Planner
                        kategori Seserahan.
                    </p>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-[#5F6F5B] mb-1.5">Link Pembelian</label>
                            <input type="url" name="link" x-model="currentItem.link" placeholder="https://..."
                                class="form-input w-full rounded-xl border border-[#B6ADA3]/40 px-3 py-2 text-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-[#5F6F5B] mb-1.5">URL Foto</label>
                            <input type="url" name="photo" x-model="currentItem.photo" placeholder="https://...jpg"
                                class="form-input w-full rounded-xl border border-[#B6ADA3]/40 px-3 py-2 text-sm">
                        </div>
                    </div>

                    <div x-show="currentItem.status === 'Diterima'" x-cloak>
                        <label class="block text-xs font-semibold text-[#5F6F5B] mb-1.5">Tanggal Diterima</label>
                        <input type="date" name="received_date" x-model="currentItem.received_date"
                            class="form-input w-full rounded-xl border border-[#B6ADA3]/40 px-3 py-2 text-sm">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-[#5F6F5B] mb-1.5">Catatan</label>
                        <textarea name="notes" x-model="currentItem.notes" rows="2"
                            class="form-input w-full rounded-xl border border-[#B6ADA3]/40 px-3 py-2 text-sm"></textarea>
                    </div>

                    <div class="flex justify-end gap-2 pt-2">
                        <button type="button" @click="modalOpen = false"
                            class="px-4 py-2 rounded-xl text-sm bg-[#B6ADA3]/15 text-[#5F6F5B] cursor-pointer">Batal</button>
                        <button class="btn-primary-rose px-5 py-2 rounded-xl text-sm cursor-pointer">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection