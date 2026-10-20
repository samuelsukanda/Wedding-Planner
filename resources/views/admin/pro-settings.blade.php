@extends('layouts.app')

@section('title', 'Pengaturan Pro')

@section('content')
    @php
        // Harga disimpan sebagai angka mentah ("55000") tapi ditampilkan
        // berformat mata uang supaya superadmin tidak salah baca nolnya.
        // Controller membersihkan ulang format ini sebelum menyimpan.
        $money = function ($value) {
            $digits = preg_replace('/\D/', '', (string) $value);

            return $digits === '' ? '' : 'Rp ' . number_format((int) $digits, 0, ',', '.');
        };

        // old() dipakai supaya isian tidak hilang saat validasi gagal.
        $rows = [];
        foreach (array_values((array) old('banks', $banks)) as $i => $row) {
            $row = is_array($row) ? $row : [];
            $rows[] = [
                'id' => $i,
                'icon' => $row['icon'] ?? 'fa-building-columns',
                'name' => $row['name'] ?? '',
                'number' => $row['number'] ?? '',
            ];
        }

        if ($rows === []) {
            $rows = [['id' => 0, 'icon' => 'fa-building-columns', 'name' => '', 'number' => '']];
        }
    @endphp

    <div class="space-y-6">
        <!-- Header -->
        <div class="bg-white p-5 rounded-2xl border border-[#B6ADA3]/35 shadow-xs">
            <h1 class="text-xl md:text-2xl font-bold font-serif-title text-[#5F6F5B]">Pengaturan Pro</h1>
            <p class="text-xs text-[#5F6F5B]/70 mt-1">Atur harga dan rekening tujuan transfer Pro yang ditampilkan pada
                halaman upgrade pasangan tanpa perlu mengubah kode aplikasi atau melakukan deployment ulang.</p>
        </div>

        <form method="POST" action="{{ route('admin.pro-settings.update') }}" enctype="multipart/form-data"
            class="bg-white p-6 rounded-2xl border border-[#B6ADA3]/35 shadow-xs space-y-5 max-w-2xl">
            @csrf
            @method('PUT')

            <div>
                <label for="pro_price" class="block text-xs font-bold uppercase tracking-wider text-[#5F6F5B] mb-1.5">
                    Harga Pro *
                </label>
                <input type="text" name="pro_price" id="pro_price" inputmode="numeric" required data-format="money"
                    value="{{ old('pro_price', $money($values['pro_price'])) }}" placeholder="Rp 0"
                    class="block w-full px-4 py-2.5 rounded-xl border border-[#B6ADA3]/40 bg-[#FAF7F2] text-sm font-bold text-[#5F6F5B] placeholder-[#B6ADA3] placeholder:font-normal focus:border-[#D8A7B1] focus:outline-none transition">
                @error('pro_price')
                    <p class="mt-1.5 text-sm text-[#C2757F]">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="pro_price_before"
                    class="block text-xs font-bold uppercase tracking-wider text-[#5F6F5B] mb-1.5">
                    Harga Coret / Sebelum Diskon
                </label>
                <input type="text" name="pro_price_before" id="pro_price_before" inputmode="numeric" data-format="money"
                    value="{{ old('pro_price_before', $money($values['pro_price_before'])) }}"
                    class="block w-full px-4 py-2.5 rounded-xl border border-[#B6ADA3]/40 bg-[#FAF7F2] text-sm font-bold text-[#5F6F5B] placeholder-[#B6ADA3] placeholder:font-normal focus:border-[#D8A7B1] focus:outline-none transition"
                    placeholder="Kosongkan kalau tidak ada diskon">
                @error('pro_price_before')
                    <p class="mt-1.5 text-sm text-[#C2757F]">{{ $message }}</p>
                @enderror
            </div>

            <!-- Narasi singkat pembayaran, tampil di atas QRIS & rekening
                 pada halaman upgrade pasangan. -->
            <div>
                <label for="pro_payment_note"
                    class="block text-xs font-bold uppercase tracking-wider text-[#5F6F5B] mb-1.5">
                    Deskripsi Pembayaran (Opsional)
                </label>
                <textarea name="pro_payment_note" id="pro_payment_note" rows="2" maxlength="300"
                    placeholder="Misal: Transfer sesuai nominal lalu unggah bukti di sini..."
                    class="block w-full px-4 py-2.5 rounded-xl border border-[#B6ADA3]/40 bg-[#FAF7F2] text-sm text-[#5F6F5B] placeholder-[#B6ADA3] placeholder:font-normal focus:border-[#D8A7B1] focus:outline-none transition">{{ old('pro_payment_note', $values['pro_payment_note']) }}</textarea>
                <p class="mt-1.5 text-[11px] text-[#5F6F5B]/60">Tampil di atas rekening &amp; kode QRIS pada halaman
                    upgrade. Maksimal 300 karakter.</p>
                @error('pro_payment_note')
                    <p class="mt-1.5 text-sm text-[#C2757F]">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <span class="block text-xs font-bold uppercase tracking-wider text-[#5F6F5B] mb-1.5">
                    Rekening Tujuan *
                </span>
                @error('banks')
                    <p class="mb-2 text-sm text-[#C2757F]">{{ $message }}</p>
                @enderror

                <div x-data="{
                    rows: @js($rows),
                    icons: @js($icons),
                    nextId: {{ count($rows) }},
                    add() {
                        this.rows.push({ id: this.nextId++, icon: 'fa-building-columns', name: '', number: '' });
                    },
                    removeRow(i) {
                        this.rows.splice(i, 1);
                        if (!this.rows.length) this.add();
                    },
                }" x-cloak class="space-y-3">
                    <template x-for="(row, i) in rows" :key="row.id">
                        <div class="rounded-xl border border-[#B6ADA3]/35 p-3 space-y-2">
                            <div class="flex items-center gap-2">
                                <div class="w-9 h-9 shrink-0 rounded-lg bg-[#D8A7B1]/20 flex items-center justify-center">
                                    <i :class="'fa-solid ' + row.icon" class="text-[#C2757F] text-sm"></i>
                                </div>

                                <select :name="`banks[${i}][icon]`" x-model="row.icon"
                                    class="flex-1 min-w-0 px-3 py-2.5 rounded-xl border border-[#B6ADA3]/40 bg-[#FAF7F2] text-sm text-[#5F6F5B] focus:border-[#D8A7B1] focus:outline-none transition">
                                    <template x-for="opt in icons" :key="opt.value">
                                        <option :value="opt.value" x-text="opt.label"></option>
                                    </template>
                                </select>

                                <button type="button" @click="removeRow(i)" aria-label="Hapus rekening"
                                    class="w-9 h-9 shrink-0 rounded-lg border border-[#B6ADA3]/40 text-[#B6ADA3] hover:text-[#C2757F] hover:border-[#D8A7B1] transition cursor-pointer">
                                    <i class="fa-solid fa-xmark"></i>
                                </button>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                <input type="text" :name="`banks[${i}][name]`" x-model="row.name" required maxlength="40"
                                    placeholder="Nama bank (BCA)"
                                    class="block w-full px-3 py-2.5 rounded-xl border border-[#B6ADA3]/40 bg-[#FAF7F2] text-sm text-[#5F6F5B] placeholder-[#B6ADA3] focus:border-[#D8A7B1] focus:outline-none transition">
                                <input type="text" :name="`banks[${i}][number]`" x-model="row.number" required
                                    maxlength="60" placeholder="Nomor rekening (5410928045)"
                                    class="block w-full px-3 py-2.5 rounded-xl border border-[#B6ADA3]/40 bg-[#FAF7F2] text-sm font-bold text-[#5F6F5B] placeholder-[#B6ADA3] placeholder:font-normal focus:border-[#D8A7B1] focus:outline-none transition">
                            </div>
                        </div>
                    </template>

                    <button type="button" @click="add()"
                        class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl border border-[#D8A7B1]/60 bg-white text-[#C2757F] text-xs font-semibold hover:bg-[#FAF7F2] transition cursor-pointer">
                        <i class="fa-solid fa-plus"></i> Tambah Rekening
                    </button>
                </div>

                <!-- Foto QRIS opsional: mengunggah baru menimpa gambar lama -->
                <div class="rounded-xl border border-[#B6ADA3]/35 p-3 space-y-2">
                    <label for="pro_qris" class="block text-xs font-bold uppercase tracking-wider text-[#5F6F5B]">
                        Foto QRIS (Opsional)
                    </label>
                    @if (trim($qrisPath) !== '')
                        <img src="{{ \Illuminate\Support\Facades\Storage::url($qrisPath) }}" alt="QRIS saat ini"
                            class="h-32 w-32 rounded-xl border border-[#B6ADA3]/30 object-contain bg-white p-1">
                    @endif
                    <input type="file" name="pro_qris" id="pro_qris" accept="image/jpeg,image/png,image/webp"
                        class="block w-full text-sm text-[#5F6F5B] file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border-0 file:bg-[#FAF7F2] file:text-[#5F6F5B] file:text-xs file:font-semibold file:cursor-pointer hover:file:bg-[#F2EDE4] transition">
                    <p class="text-[11px] text-[#5F6F5B]/60">Tampil di halaman upgrade sebagai cara bayar selain transfer
                        rekening. Format JPG/PNG/WEBP, maksimal 2 MB.</p>
                    @error('pro_qris')
                        <p class="text-sm text-[#C2757F]">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <button type="submit" class="btn-primary-rose px-5 py-2.5 rounded-xl text-sm cursor-pointer">
                Simpan Pengaturan
            </button>
        </form>
    </div>
@endsection

@push('scripts')
    <script>
        (function() {
            // Format uang memakai helper global yang sama dengan input nominal
            // di modal Tambah Data (wpMoney), jadi tidak ada aturan format
            // kedua di aplikasi ini. Diformat tiap ketik, supaya sama seperti
            // input telepon di modal Tambah Data.
            document.querySelectorAll('input[data-format="money"]').forEach(function(input) {
                input.addEventListener('input', function() {
                    input.value = window.wpMoney(input.value);
                });

                input.value = window.wpMoney(input.value);
            });
        })();
    </script>
@endpush
