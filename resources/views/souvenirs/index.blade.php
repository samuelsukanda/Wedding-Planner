@extends('layouts.app')

@section('title', 'Perencanaan - Seserahan')

@section('content')
    <div class="space-y-6" x-data="{
        modalOpen: false,
        init() {
            // Nilai tanggal baru ditulis x-model setelah currentItem terisi,
            // jadi tampilan flatpickr disegarkan lagi di sini.
            this.$watch('currentItem', () => window.syncDatepickersSoon());
        },
        editMode: false,
        currentItem: { status: 'Belum Dipilih', planned_price: '', actual_cost: '' },
    }">

        {{-- Header --}}
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-white p-5 rounded-2xl border border-[#B6ADA3]/35 shadow-xs text-[#5F6F5B]">
            <div>
                <h1 class="text-xl md:text-2xl font-bold font-serif-title text-[#5F6F5B]">Seserahan</h1>
                <p class="text-xs text-[#5F6F5B] mt-1">Catat dan pantau kebutuhan seserahan agar setiap item siap menjelang
                    hari pernikahan.</p>
            </div>
            <button
                @click="modalOpen = true; editMode = false; currentItem = { status: 'Belum Dipilih', planned_price: '', actual_cost: '' }"
                class="btn-primary-rose px-4 py-2.5 rounded-xl text-sm flex items-center justify-center gap-2 cursor-pointer">
                <i class="fa-solid fa-plus" aria-hidden="true"></i> Tambah Seserahan
            </button>
        </div>

        {{-- Ringkasan --}}
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 text-[#5F6F5B]">
            <div class="bg-white p-4 rounded-2xl border border-[#B6ADA3]/35 shadow-xs">
                <div class="text-xs text-[#5F6F5B] mb-1 font-medium">Total Item</div>
                <div class="text-lg font-bold text-[#5F6F5B]">{{ $summary['total'] }}</div>
            </div>
            <div class="bg-white p-4 rounded-2xl border border-[#B6ADA3]/35 shadow-xs">
                <div class="text-xs text-[#5F6F5B] mb-1 font-medium">Sudah Diterima</div>
                <div class="text-lg font-bold text-[#5F6F5B]">{{ $summary['received'] }} / {{ $summary['total'] }}</div>
            </div>
            <div class="bg-white p-4 rounded-2xl border border-[#B6ADA3]/35 shadow-xs">
                <div class="text-xs text-[#5F6F5B] mb-1 font-medium">Rencana</div>
                <div class="text-lg font-bold text-[#5F6F5B]">Rp {{ number_format($summary['planned'], 0, ',', '.') }}</div>
            </div>
            <div class="bg-white p-4 rounded-2xl border border-[#D8A7B1]/50 shadow-xs">
                <div class="text-xs text-[#5F6F5B] mb-1 font-medium">Realisasi</div>
                <div class="text-lg font-bold text-[#8B3A47]">Rp {{ number_format($summary['actual'], 0, ',', '.') }}</div>
                <div class="text-[10px] text-[#5F6F5B] mt-0.5">Dari item diterima</div>
            </div>
        </div>

        {{-- Tabel --}}
        <div class="bg-white rounded-2xl border border-[#B6ADA3]/35 shadow-xs overflow-hidden text-[#5F6F5B]">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr
                            class="bg-[#FAF7F2] text-xs text-[#5F6F5B] uppercase tracking-wider border-b border-[#B6ADA3]/30">
                            <th class="p-4">Item</th>
                            <th class="p-4">Vendor</th>
                            <th class="p-4 text-right">Rencana</th>
                            <th class="p-4 text-right">Aktual</th>
                            <th class="p-4 text-right">Selisih</th>
                            <th class="p-4">Status</th>
                            <th class="p-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#B6ADA3]/20 text-sm">
                        @forelse ($souvenirs as $item)
                            <tr class="hover:bg-[#FAF7F2]/60 transition-colors">
                                <td class="p-4">
                                    <div class="flex items-center gap-3">
                                        @if ($item->photo)
                                            <img src="{{ $item->photo }}" alt="{{ $item->name }}"
                                                class="w-10 h-10 rounded-lg object-cover border border-[#B6ADA3]/30">
                                        @else
                                            <div
                                                class="w-10 h-10 rounded-lg bg-[#5F6F5B]/8 flex items-center justify-center">
                                                <i class="fa-solid fa-gift text-[#5F6F5B] text-xs" aria-hidden="true"></i>
                                            </div>
                                        @endif
                                        <div>
                                            <div class="font-medium text-[#5F6F5B]">{{ $item->name }}</div>
                                            @if ($item->link)
                                                <a href="{{ $item->link }}" target="_blank" rel="noopener noreferrer"
                                                    class="text-[10px] text-[#8B3A47] hover:underline">
                                                    <i class="fa-solid fa-link" aria-hidden="true"></i> Lihat link
                                                </a>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td class="p-4 text-xs text-[#5F6F5B]">{{ $item->vendor?->name ?: '-' }}</td>
                                <td class="p-4 text-xs text-right text-[#5F6F5B]">Rp
                                    {{ number_format($item->planned_price, 0, ',', '.') }}</td>
                                <td class="p-4 text-xs text-right text-[#5F6F5B]">Rp {{ number_format($item->actual_cost, 0, ',', '.') }}
                                </td>
                                <td @class([
                                    'p-4 text-xs text-right font-semibold',
                                    'text-[#8B3A47]' => $item->variance() < 0,
                                    'text-[#5F6F5B]' => $item->variance() >= 0,
                                ])>
                                    {{ $item->variance() >= 0 ? '+' : '-' }} Rp
                                    {{ number_format(abs($item->variance()), 0, ',', '.') }}
                                </td>
                                <td class="p-4">
                                    @if ($item->isReceived())
                                        <span
                                            class="px-2.5 py-1 rounded-full text-xs font-semibold bg-[#EBF2EC] text-[#2E4B33] border border-[#A3B7A6]/60">
                                            Diterima
                                        </span>
                                        @if ($item->received_date)
                                            <div class="text-[10px] text-[#5F6F5B] mt-0.5">
                                                {{ $item->received_date->format('d M Y') }}</div>
                                        @endif
                                    @else
                                        <span
                                            class="px-2.5 py-1 rounded-full text-xs font-semibold bg-[#FAF7F2] text-[#5F6F5B] border border-[#B6ADA3]/40">
                                            {{ $item->status }}
                                        </span>
                                    @endif
                                </td>
                                <td class="p-4 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <button
                                            @click="modalOpen = true; editMode = true; currentItem = {{ Js::from($item) }}"
                                            title="Edit" class="btn-icon">
                                            <i class="fa-solid fa-pen-to-square" aria-hidden="true"></i>
                                        </button>
                                        <form id="del-souvenir-{{ $item->id }}" method="POST"
                                            action="{{ route('souvenirs.destroy', $item) }}">
                                            @csrf @method('DELETE')
                                            <button type="button" title="Hapus" class="btn-icon"
                                                onclick="confirmDelete('del-souvenir-{{ $item->id }}', '{{ addslashes($item->name) }}')">
                                                <i class="fa-solid fa-trash" aria-hidden="true"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-12 text-center text-[#5F6F5B]">
                                    <i class="fa-solid fa-gift text-[#5F6F5B] text-2xl mb-3" aria-hidden="true"></i>
                                    <div class="text-xs text-[#5F6F5B]">Belum ada item seserahan.</div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Modal --}}
        <div x-show="modalOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-[#5F6F5B]/40 backdrop-blur-md p-4"
            @keydown.escape.window="modalOpen = false">
            <div x-outside="modalOpen = false"
                class="bg-white rounded-2xl border border-[#B6ADA3]/40 shadow-2xl w-full max-w-lg max-h-[90vh] overflow-y-auto text-[#5F6F5B]">
                <div class="p-5 border-b border-[#B6ADA3]/25 flex items-center justify-between">
                    <h2 class="font-bold text-[#5F6F5B]"
                        x-text="editMode ? 'Edit Item Seserahan' : 'Tambah Item Seserahan'"></h2>
                    <button @click="modalOpen = false" class="text-[#5F6F5B] hover:text-[#8B3A47] cursor-pointer" aria-label="Tutup modal">
                        <i class="fa-solid fa-xmark" aria-hidden="true"></i>
                    </button>
                </div>

                {{-- action diturunkan dari currentItem.id, bukan flag editMode: kalau
                     editMode di-set di handler yang sama, Alpine bisa
                     mengevaluasi :action sebelum currentItem ter-assign. --}}
                <form
                    :action="(currentItem && currentItem.id) ? '{{ url('/souvenirs') }}/' + currentItem.id:
                        '{{ route('souvenirs.store') }}'"
                    method="POST" class="p-5 space-y-3">
                    @csrf
                    <template x-if="currentItem && currentItem.id"><input type="hidden" name="_method"
                            value="PUT"></template>

                    <div>
                        <label for="souv_name" class="form-label">Nama Item <span class="text-[#8B3A47]">*</span></label>
                        <input id="souv_name" type="text" name="name" x-model="currentItem.name" required
                            placeholder="mis. Mug Keramik Custom" class="form-input" aria-label="Nama Item">
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label for="souvenir_vendor_id" class="form-label">Vendor</label>
                            <select id="souvenir_vendor_id" name="vendor_id" x-model="currentItem.vendor_id" class="form-input" aria-label="Vendor">
                                <option value="">-- Pilih Vendor --</option>
                                @foreach ($wedding->vendors()->orderBy('name')->get() as $v)
                                    <option value="{{ $v->id }}">{{ $v->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label for="souvenir_status" class="form-label">Status</label>
                            <select id="souvenir_status" name="status" x-model="currentItem.status" class="form-input" aria-label="Status">
                                @foreach ($statuses as $opt)
                                    <option value="{{ $opt }}">{{ $opt }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label for="souv_planned_price" class="form-label">Harga Rencana <span class="text-[#8B3A47]">*</span></label>
                            <input id="souv_planned_price" type="text" name="planned_price" inputmode="numeric" placeholder="Rp" required
                                aria-label="Harga Rencana"
                                :value="wpMoney(currentItem.planned_price)"
                                @input="currentItem.planned_price = wpDigits($event.target.value); wpClamp($event.target, wpMoney)"
                                class="form-input form-input-number">
                        </div>
                        <div>
                            <label for="souv_actual_cost" class="form-label">Harga Beli</label>
                            <input id="souv_actual_cost" type="text" name="actual_cost" inputmode="numeric" placeholder="Rp"
                                aria-label="Harga Beli"
                                :value="wpMoney(currentItem.actual_cost)"
                                @input="currentItem.actual_cost = wpDigits($event.target.value); wpClamp($event.target, wpMoney)"
                                class="form-input form-input-number">
                        </div>
                    </div>

                    <p class="text-[11px] text-[#5F6F5B] bg-[#FAF7F2] rounded-lg px-3 py-2 border border-[#B6ADA3]/30">
                        <i class="fa-solid fa-circle-info mr-1 text-[#8B3A47]" aria-hidden="true"></i>
                        Saat status <strong>Diterima</strong>, harga beli otomatis tercatat di Budget Planner
                        kategori Seserahan.
                    </p>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label for="souv_link" class="form-label">Link Pembelian</label>
                            <input id="souv_link" type="url" name="link" x-model="currentItem.link" placeholder="https://..."
                                class="form-input" aria-label="Link Pembelian">
                        </div>
                        <div>
                            <label for="souv_photo" class="form-label">URL Foto</label>
                            <input id="souv_photo" type="url" name="photo" x-model="currentItem.photo"
                                placeholder="https://...jpg" class="form-input" aria-label="URL Foto">
                        </div>
                    </div>

                    <div x-show="currentItem.status === 'Diterima'" x-cloak>
                        <label for="souv_received_date" class="form-label">Tanggal Diterima</label>
                        <input id="souv_received_date" type="text" name="received_date" x-model="currentItem.received_date"
                            class="form-input datepicker" aria-label="Tanggal Diterima">
                    </div>

                    <div>
                        <label for="souv_notes" class="form-label">Catatan</label>
                        <textarea id="souv_notes" name="notes" x-model="currentItem.notes" rows="2" class="form-input" aria-label="Catatan"></textarea>
                    </div>

                    <div class="flex justify-end gap-2 pt-2 border-t border-[#B6ADA3]/25">
                        <button type="button" @click="modalOpen = false"
                            class="px-4 py-2 rounded-xl text-xs text-[#5F6F5B] hover:text-[#8B3A47] cursor-pointer">Batal</button>
                        <button class="btn-primary-rose px-5 py-2 rounded-xl text-xs cursor-pointer">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
