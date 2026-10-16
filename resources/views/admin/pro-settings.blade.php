@extends('layouts.app')

@section('title', 'Pengaturan Pro')

@section('content')
    @php
        // Setelan disimpan sebagai angka mentah ("55000", "6281234567890"), tapi
        // ditampilkan format mata uang / telepon supaya superadmin tidak salah
        // baca nolnya. Controller membersihkan ulang format ini sebelum menyimpan.
        $money = function ($value) {
            $digits = preg_replace('/\D/', '', (string) $value);

            return $digits === '' ? '' : 'Rp ' . number_format((int) $digits, 0, ',', '.');
        };
        $phone = function ($value) {
            return preg_replace('/(\d{4})(?=\d)/', '$1-', preg_replace('/\D/', '', (string) $value));
        };
    @endphp

    <div class="space-y-6">
        <!-- Header -->
        <div class="bg-white p-5 rounded-2xl border border-[#B6ADA3]/35 shadow-xs">
            <h1 class="text-xl md:text-2xl font-bold font-serif-title text-[#5F6F5B]">Pengaturan Pro</h1>
            <p class="text-xs text-[#5F6F5B]/70 mt-1">
                Atur harga, informasi pembayaran, dan detail paket Pro yang ditampilkan pada halaman upgrade pasangan tanpa
                perlu mengubah kode aplikasi atau melakukan deployment ulang.</p>
        </div>

        <form method="POST" action="{{ route('admin.pro-settings.update') }}"
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
                <p class="mt-1.5 text-[11px] text-[#5F6F5B]/60">Dipakai untuk transfer manual, dibayar sekali seumur akun.</p>
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
                <p class="mt-1.5 text-[11px] text-[#5F6F5B]/60">
                    Angka yang dicoret di halaman upgrade untuk memberi kesan diskon. Hanya tampil kalau lebih besar
                    dari Harga Pro.
                </p>
                @error('pro_price_before')
                    <p class="mt-1.5 text-sm text-[#C2757F]">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="pro_bank" class="block text-xs font-bold uppercase tracking-wider text-[#5F6F5B] mb-1.5">
                    Rekening Tujuan *
                </label>
                <textarea name="pro_bank" id="pro_bank" rows="4" required
                    class="block w-full px-4 py-2.5 rounded-xl border border-[#B6ADA3]/40 bg-[#FAF7F2] text-sm text-[#5F6F5B] focus:border-[#D8A7B1] focus:outline-none transition">{{ old('pro_bank', $values['pro_bank']) }}</textarea>
                <p class="mt-1.5 text-[11px] text-[#5F6F5B]/60">Boleh isi nama bank, nomor, dan nama pemilik. Boleh beberapa baris.</p>
                @error('pro_bank')
                    <p class="mt-1.5 text-sm text-[#C2757F]">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="admin_whatsapp" class="block text-xs font-bold uppercase tracking-wider text-[#5F6F5B] mb-1.5">
                    Nomor WhatsApp Admin *
                </label>
                <input type="text" name="admin_whatsapp" id="admin_whatsapp" inputmode="numeric" required data-format="phone"
                    value="{{ old('admin_whatsapp', $phone($values['admin_whatsapp'])) }}" placeholder="6281-2345-6789"
                    class="block w-full px-4 py-2.5 rounded-xl border border-[#B6ADA3]/40 bg-[#FAF7F2] text-sm font-bold text-[#5F6F5B] placeholder-[#B6ADA3] placeholder:font-normal focus:border-[#D8A7B1] focus:outline-none transition">
                <p class="mt-1.5 text-[11px] text-[#5F6F5B]/60">Format internasional tanpa tanda +. Dipakai untuk tombol "Tanya admin".</p>
                @error('admin_whatsapp')
                    <p class="mt-1.5 text-sm text-[#C2757F]">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="moodboard_free_limit"
                    class="block text-xs font-bold uppercase tracking-wider text-[#5F6F5B] mb-1.5">
                    Batas Moodboard Paket Free *
                </label>
                <input type="number" name="moodboard_free_limit" id="moodboard_free_limit" min="0" max="1000"
                    required value="{{ old('moodboard_free_limit', $values['moodboard_free_limit'] ?: 3) }}"
                    class="block w-full px-4 py-2.5 rounded-xl border border-[#B6ADA3]/40 bg-[#FAF7F2] text-sm text-[#5F6F5B] focus:border-[#D8A7B1] focus:outline-none transition">
                <p class="mt-1.5 text-[11px] text-[#5F6F5B]/60">
                    Berapa item moodboard yang boleh disimpan pasangan Free. Paket Pro tidak terbatas.
                    Pasangan yang sudah punya lebih dari batas ini tetap bisa membuka semua itemnya — hanya tidak bisa menambah.
                </p>
                @error('moodboard_free_limit')
                    <p class="mt-1.5 text-sm text-[#C2757F]">{{ $message }}</p>
                @enderror
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
            // Format uang dan telepon memakai helper global yang sama dengan
            // input telepon di modal Tambah Data (wpPhone), jadi tidak ada
            // aturan format kedua di aplikasi ini.
            //
            // Diformat tiap ketik, supaya sama seperti input telepon di modal
            // Tambah Data. wpPhone/wpMoney sudah memanggil wpDigits di dalam
            // dirinya, jadi batas 13 digit ikut terpotong.
            document.querySelectorAll('input[data-format]').forEach(function(input) {
                const format = input.dataset.format === 'phone' ? window.wpPhone : window.wpMoney;

                input.addEventListener('input', function() {
                    input.value = format(input.value);
                });

                input.value = format(input.value);
            });
        })();
    </script>
@endpush