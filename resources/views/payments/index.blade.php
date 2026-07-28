@extends('layouts.app')

@section('title', 'Keuangan & Dokumen - Payment Tracker')

@section('content')
    <div class="space-y-6" x-data="{ modalOpen: false, editMode: false, currentItem: {} }">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold font-serif-title">Payment Tracker</h1>
            </div>
            <button @click="modalOpen = true; editMode = false; currentItem = {}"
                class="btn-primary-rose px-4 py-2.5 rounded-xl text-sm flex items-center justify-center gap-2 cursor-pointer w-full md:w-auto">
                <i class="fa-solid fa-plus"></i> Catat Pembayaran
            </button>
        </div>

        <!-- Reminder Alert Banner -->
        <div
            class="p-4 rounded-2xl bg-gradient-to-r from-[#FAF7F2] to-[#D8A7B1]/20 border border-[#B6ADA3]/40 flex items-center gap-4">
            <div
                class="w-10 h-10 rounded-xl bg-[#D8A7B1]/30 flex items-center justify-center text-[#5F6F5B] text-lg shrink-0">
                <i class="fa-solid fa-bell"></i>
            </div>
            <div class="text-xs space-y-0.5">
                <div class="font-bold text-[#5F6F5B]">Pengingat Pembayaran Otomatis Aktif</div>
                <div class="text-[#5F6F5B]/80">Sistem mengirimkan pengingat notifikasi pada H-7, H-3, dan Hari H tanggal
                    jatuh tempo pembayaran vendor.</div>
            </div>
        </div>

        <!-- Payments Table -->
        <div class="bg-white rounded-2xl border border-[#B6ADA3]/35 shadow-xs overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr
                            class="bg-[#FAF7F2] text-xs text-[#5F6F5B] uppercase tracking-wider border-b border-[#B6ADA3]/30">
                            <th class="p-4">Nama Vendor</th>
                            <th class="p-4">Nominal Dibayar</th>
                            <th class="p-4">Tanggal Bayar</th>
                            <th class="p-4">Metode Pembayaran</th>
                            <th class="p-4">Tanggal Reminder</th>
                            <th class="p-4">Status</th>
                            <th class="p-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#B6ADA3]/20 text-sm">
                        @forelse($payments as $p)
                            <tr class="hover:bg-[#FAF7F2]/60 transition-colors">
                                <td class="p-4 font-semibold text-[#5F6F5B]">
                                    {{ $p->vendor->name ?? 'Vendor #' . $p->vendor_id }}</td>
                                <td class="p-4 font-bold text-[#5F6F5B]">Rp {{ number_format($p->nominal, 0, ',', '.') }}
                                </td>
                                <td class="p-4 text-xs text-[#5F6F5B]/80">
                                    <i
                                        class="fa-solid fa-calendar mr-1 text-[#D8A7B1]"></i>{{ $p->payment_date ? $p->payment_date->format('d M Y') : '—' }}
                                </td>
                                <td class="p-4 text-xs text-[#5F6F5B]/80">{{ $p->payment_method ?? '—' }}</td>
                                <td class="p-4 text-xs">
                                    @if ($p->reminder_date)
                                        <span
                                            class="inline-flex items-center gap-1 bg-[#D8A7B1]/20 text-[#5F6F5B] px-2.5 py-1 rounded-lg border border-[#D8A7B1]/40">
                                            <i class="fa-solid fa-clock text-[10px]"></i>
                                            {{ $p->reminder_date->format('d M Y') }}
                                        </span>
                                    @else
                                        —
                                    @endif
                                </td>
                                <td class="p-4">
                                    <span
                                        class="px-2.5 py-1 rounded-full text-xs font-semibold
                                    @if ($p->status == 'Lunas') bg-[#A3B7A6]/25 text-[#5F6F5B] border border-[#A3B7A6]/40
                                    @elseif($p->status == 'DP' || $p->status == 'Cicilan') bg-[#D8A7B1]/25 text-[#5F6F5B] border border-[#D8A7B1]/40
                                    @else bg-[#FAF7F2] text-[#5F6F5B] border border-[#B6ADA3]/40 @endif">
                                        {{ $p->status }}
                                    </span>
                                </td>
                                <td class="p-4 text-right">
                                     <div class="flex items-center justify-end gap-2">
                                         <button
                                             @click="modalOpen = true; editMode = true; currentItem = {{ json_encode($p) }}"
                                             title="Edit"
                                             class="p-2 text-[#B6ADA3] hover:text-[#5F6F5B] cursor-pointer"><i
                                                 class="fa-solid fa-pen-to-square"></i></button>
                                         <form id="del-payment-{{ $p->id }}" action="{{ route('payments.destroy', $p->id) }}" method="POST">
                                             @csrf
                                             @method('DELETE')
                                             <button type="button"
                                                 title="Hapus"
                                                 onclick="confirmDelete('del-payment-{{ $p->id }}', '{{ addslashes($p->vendor_name) }}')"
                                                 class="p-2 text-[#B6ADA3] hover:text-[#5F6F5B] cursor-pointer"><i
                                                     class="fa-solid fa-trash"></i></button>
                                         </form>
                                     </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-10 text-[#B6ADA3] text-sm">Belum ada riwayat
                                    pembayaran vendor.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Modal Form -->
        <div x-show="modalOpen" x-transition.opacity
            class="fixed inset-0 z-50 bg-[#5F6F5B]/40 backdrop-blur-md flex items-center justify-center p-4">
            <div @click.away="if (!$event.target.closest('.flatpickr-calendar')) modalOpen = false"
                class="bg-white w-full max-w-lg p-6 rounded-2xl border border-[#B6ADA3]/40 shadow-2xl space-y-4">
                <div class="flex items-center justify-between border-b border-[#B6ADA3]/30 pb-3">
                    <h3 class="text-lg font-bold font-serif-title text-[#5F6F5B]"
                        x-text="editMode ? 'Edit Catatan Pembayaran' : 'Catat Pembayaran Vendor'"></h3>
                    <button @click="modalOpen = false" class="text-[#B6ADA3] hover:text-[#5F6F5B] cursor-pointer"><i
                            class="fa-solid fa-xmark"></i></button>
                </div>
                <form :action="editMode ? '/payments/' + currentItem.id : '{{ route('payments.store') }}'" method="POST"
                    class="space-y-4">
                    @csrf
                    <template x-if="editMode"><input type="hidden" name="_method" value="PUT"></template>
                    <div>
                        <label class="block text-xs font-semibold text-[#5F6F5B] mb-1">Vendor *</label>
                        <select name="vendor_id" x-model="currentItem.vendor_id" required
                            class="w-full bg-[#FAF7F2] border border-[#B6ADA3]/40 text-sm text-[#5F6F5B] px-3 py-2 rounded-xl focus:border-[#D8A7B1] focus:outline-none">
                            <option value="">Pilih Vendor</option>
                            @foreach ($vendors as $v)
                                <option value="{{ $v->id }}">{{ $v->name }} ({{ $v->category }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-[#5F6F5B] mb-1">Nominal (Rp) *</label>
                            <input type="number" name="nominal" x-model="currentItem.nominal" required
                                class="w-full bg-[#FAF7F2] border border-[#B6ADA3]/40 text-sm text-[#5F6F5B] px-3 py-2 rounded-xl focus:border-[#D8A7B1] focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-[#5F6F5B] mb-1">Status Pembayaran *</label>
                            <select name="status" x-model="currentItem.status" required
                                class="w-full bg-[#FAF7F2] border border-[#B6ADA3]/40 text-sm text-[#5F6F5B] px-3 py-2 rounded-xl focus:border-[#D8A7B1] focus:outline-none">
                                @foreach ($statuses as $st)
                                    <option value="{{ $st }}">{{ $st }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-[#5F6F5B] mb-1">Tanggal Bayar</label>
                            <input type="text" name="payment_date" x-model="currentItem.payment_date"
                                class="datepicker w-full bg-[#FAF7F2] border border-[#B6ADA3]/40 text-sm text-[#5F6F5B] px-3 py-2 rounded-xl focus:border-[#D8A7B1] focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-[#5F6F5B] mb-1">Tanggal Reminder</label>
                            <input type="text" name="reminder_date" x-model="currentItem.reminder_date"
                                class="datepicker w-full bg-[#FAF7F2] border border-[#B6ADA3]/40 text-sm text-[#5F6F5B] px-3 py-2 rounded-xl focus:border-[#D8A7B1] focus:outline-none">
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-[#5F6F5B] mb-1">Metode Pembayaran</label>
                        <input type="text" name="payment_method" x-model="currentItem.payment_method"
                            class="w-full bg-[#FAF7F2] border border-[#B6ADA3]/40 text-sm text-[#5F6F5B] px-3 py-2 rounded-xl focus:border-[#D8A7B1] focus:outline-none"
                            placeholder="Bank Transfer BCA / Mandiri / Cash">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-[#5F6F5B] mb-1">Catatan</label>
                        <textarea name="notes" x-model="currentItem.notes" rows="2"
                            class="w-full bg-[#FAF7F2] border border-[#B6ADA3]/40 text-sm text-[#5F6F5B] px-3 py-2 rounded-xl focus:border-[#D8A7B1] focus:outline-none"></textarea>
                    </div>
                    <div class="flex justify-end gap-3 pt-3 border-t border-[#B6ADA3]/30">
                        <button type="button" @click="modalOpen = false"
                            class="px-4 py-2 text-xs text-[#B6ADA3] hover:text-[#5F6F5B] cursor-pointer">Batal</button>
                        <button type="submit" class="btn-primary-rose px-5 py-2 rounded-xl text-xs cursor-pointer">Simpan
                            Pembayaran</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
