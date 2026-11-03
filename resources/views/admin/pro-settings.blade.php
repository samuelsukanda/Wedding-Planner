@extends('layouts.app')

@section('title', 'Pengaturan Pro')

@section('content')
    <div class="space-y-6">
        <!-- Header -->
        <div class="bg-white p-5 rounded-2xl border border-[#B6ADA3]/35 shadow-xs">
            <h1 class="text-xl md:text-2xl font-bold font-serif-title text-[#5F6F5B]">Pengaturan Pro</h1>
            <p class="text-xs text-[#5F6F5B] mt-1">Atur harga paket Pro.</p>
        </div>

        <form method="POST" action="{{ route('admin.pro-settings.update') }}"
            class="bg-white p-6 rounded-2xl border border-[#B6ADA3]/35 shadow-xs space-y-5 w-full">
            @csrf
            @method('PUT')

            @php
                $money = fn($v) => $v === '' || $v === null ? '' : 'Rp ' . number_format((int) $v, 0, ',', '.');
            @endphp

            <div>
                <label for="pro_price" class="block text-xs font-bold uppercase tracking-wider text-[#5F6F5B] mb-1.5">
                    Harga Pro (Rp)
                </label>
                <input type="text" name="pro_price" id="pro_price" inputmode="numeric" data-format="money" required
                    value="{{ old('pro_price', $money($values['pro_price'])) }}" aria-label="Harga Pro (Rp)"
                    class="block w-full px-4 py-2.5 rounded-xl border border-[#B6ADA3]/40 bg-[#FAF7F2] text-sm font-bold text-[#5F6F5B] placeholder-[#B6ADA3] placeholder:font-normal focus:border-[#D8A7B1] focus:outline-none transition">
                @error('pro_price')
                    <p class="mt-1.5 text-sm text-[#8B3A47]">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="pro_price_before"
                    class="block text-xs font-bold uppercase tracking-wider text-[#5F6F5B] mb-1.5">
                    Harga Coret / Sebelum Diskon
                </label>
                <input type="text" name="pro_price_before" id="pro_price_before" inputmode="numeric" data-format="money"
                    value="{{ old('pro_price_before', $money($values['pro_price_before'])) }}"
                    aria-label="Harga Coret / Sebelum Diskon"
                    class="block w-full px-4 py-2.5 rounded-xl border border-[#B6ADA3]/40 bg-[#FAF7F2] text-sm font-bold text-[#5F6F5B] placeholder-[#B6ADA3] placeholder:font-normal focus:border-[#D8A7B1] focus:outline-none transition"
                    placeholder="Kosongkan kalau tidak ada diskon">
                @error('pro_price_before')
                    <p class="mt-1.5 text-sm text-[#8B3A47]">{{ $message }}</p>
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
            document.querySelectorAll('input[data-format="money"]').forEach(function(input) {
                input.addEventListener('input', function() {
                    input.value = window.wpMoney(input.value);
                });
                input.value = window.wpMoney(input.value);
            });
        })();
    </script>
@endpush
