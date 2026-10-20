{{-- Badge rekening berwarna brand bank: kotak warna brand + akronim.
     FontAwesome tidak punya logo bank Indonesia, jadi identitasnya dibawa
     lewat warna brand. Dicocokkan longgar dari nama yang diketik superadmin,
     jadi "BCA", "Bank BCA", dan "bca" sama-sama kena. Bank di luar daftar
     kembali ke ikon generik pilihan superadmin (fa-...). --}}
@props(['name' => '', 'icon' => 'fa-building-columns'])

@php
    $bankBrands = [
        'BCA' => '#00579C',
        'BNI' => '#F37021',
        'JAGO' => '#16215B',
    ];

    $label = strtoupper(trim((string) $name));
    $brandLabel = collect($bankBrands)
        ->keys()
        ->first(fn (string $key) => str_contains($label, $key));
@endphp

@if ($brandLabel)
    <span style="background:{{ $bankBrands[$brandLabel] }};color:#fff;"
        class="w-9 h-9 shrink-0 rounded-lg flex items-center justify-center text-[11px] font-bold tracking-wide select-none">
        {{ $brandLabel }}
    </span>
@else
    <span class="w-9 h-9 shrink-0 rounded-lg bg-[#D8A7B1]/20 flex items-center justify-center">
        <i class="fa-solid {{ $icon }} text-[#C2757F] text-sm"></i>
    </span>
@endif
