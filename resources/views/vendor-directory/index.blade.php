@extends('layouts.app')

@section('title', 'Cari Vendor')

@section('content')
    <div class="space-y-6">
        {{-- Header --}}
        <div
            class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-white p-5 rounded-2xl border border-[#B6ADA3]/35 shadow-xs">
            <div>
                <h1 class="text-xl md:text-2xl font-bold font-serif-title text-[#5F6F5B]">Cari Vendor</h1>
                <p class="text-xs text-[#5F6F5B]/70 mt-1">Temukan dan bandingkan vendor pernikahan sesuai kebutuhan,
                    anggaran, dan konsep pernikahan impianmu.</p>
            </div>
            <div class="flex items-center gap-3">
                <span class="text-xs text-[#B6ADA3]">
                    <strong class="text-[#5F6F5B]">{{ number_format($vendors->total()) }}</strong> vendor
                </span>
                <a href="{{ route('vendors.index') }}"
                    class="px-4 py-2.5 rounded-xl bg-white text-[#5F6F5B] hover:bg-[#FAF7F2] border border-[#B6ADA3]/40 text-xs flex items-center justify-center gap-1.5 shadow-xs font-semibold">
                    <i class="fa-solid fa-arrow-left"></i> Kembali
                </a>
            </div>
        </div>

        {{-- Filter --}}
        <form method="GET" action="{{ route('vendor-directory.index') }}"
            class="bg-white p-4 rounded-2xl border border-[#B6ADA3]/35 shadow-xs">
            <div class="grid grid-cols-1 sm:grid-cols-6 gap-3 items-center">
                <input type="text" name="q" value="{{ $q }}" placeholder="Cari nama vendor / kota..."
                    class="bg-[#FAF7F2] border border-[#B6ADA3]/40 text-xs text-[#5F6F5B] px-3 py-2.5 rounded-xl focus:border-[#D8A7B1] focus:outline-none sm:col-span-2">

                <select name="category"
                    class="bg-[#FAF7F2] border border-[#B6ADA3]/40 text-xs text-[#5F6F5B] px-3 py-2.5 rounded-xl focus:border-[#D8A7B1] focus:outline-none">
                    <option value="">Semua Kategori</option>
                    @foreach ($categories as $cat)
                        <option value="{{ $cat }}" @selected($category === $cat)>{{ $cat }}</option>
                    @endforeach
                </select>

                <select name="city"
                    class="bg-[#FAF7F2] border border-[#B6ADA3]/40 text-xs text-[#5F6F5B] px-3 py-2.5 rounded-xl focus:border-[#D8A7B1] focus:outline-none">
                    <option value="">Semua Kota</option>
                    @foreach ($cities as $kota)
                        <option value="{{ $kota }}" @selected($city === $kota)>{{ $kota }}</option>
                    @endforeach
                </select>

                <select name="sort"
                    class="bg-[#FAF7F2] border border-[#B6ADA3]/40 text-xs text-[#5F6F5B] px-3 py-2.5 rounded-xl focus:border-[#D8A7B1] focus:outline-none">
                    {{-- Dua option ini sebelumnya sama-sama value="", jadi memilih
                         "Nama (A–Z)" tidak mengubah apa pun dan browser
                         selalu menampilkan yang pertama. --}}
                    <option value="">Nama (A–Z)</option>
                    <option value="rating" @selected($sort === 'rating')>Rating Tertinggi</option>
                </select>

                <div class="flex items-center justify-end gap-2">
                    <button type="submit"
                        class="btn-primary-rose px-4 py-2.5 rounded-xl text-xs cursor-pointer">Terapkan</button>
                </div>
            </div>
        </form>

        {{-- Grid --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            @forelse ($vendors as $vendor)
                <div
                    class="bg-white rounded-2xl border border-[#B6ADA3]/35 shadow-xs p-5 flex flex-col justify-between hover:border-[#D8A7B1] hover:shadow-md transition-all space-y-4 group">
                    <div>
                        @if ($vendor->photo)
                            <a href="{{ route('vendor-directory.show', $vendor) }}" class="block mb-4">
                                <img src="{{ $vendor->photo }}" alt="{{ $vendor->name }}" loading="lazy"
                                    class="w-full h-40 object-cover rounded-xl">
                            </a>
                        @endif

                        <div class="flex items-start justify-between gap-3 mb-3">
                            <span
                                class="px-2.5 py-1 rounded-full bg-[#D8A7B1]/20 text-[10px] font-semibold text-[#C2757F] border border-[#D8A7B1]/40 shrink-0">
                                {{ $vendor->category }}
                            </span>
                            @if ($vendor->rating > 0)
                                <span
                                    class="shrink-0 text-[10px] font-bold px-2 py-1 rounded-full bg-[#FAF7F2] border border-[#B6ADA3]/40 text-[#5F6F5B]">
                                    <i class="fa-solid fa-star text-[9px] text-[#C2757F]"></i>
                                    {{ number_format($vendor->rating, 1) }}
                                </span>
                            @endif
                        </div>

                        <a href="{{ route('vendor-directory.show', $vendor) }}"
                            class="block font-bold text-sm text-[#5F6F5B] hover:underline leading-snug">{{ $vendor->name }}</a>

                        <div class="mt-3 space-y-1.5 text-[11px] text-[#5F6F5B]/80">
                            @if ($vendor->price_label)
                                <p class="font-bold text-[#5F6F5B]">{{ $vendor->price_label }}</p>
                            @endif
                            @if ($vendor->phone)
                                <p class="flex items-center gap-1.5">
                                    <i class="fa-solid fa-phone text-[9px] text-[#B6ADA3] w-3"></i>
                                    <span class="truncate">{{ $vendor->phone }}</span>
                                </p>
                            @endif
                            @if ($vendor->city)
                                <p class="flex items-center gap-1.5">
                                    <i class="fa-solid fa-location-dot text-[9px] text-[#B6ADA3] w-3"></i>
                                    <span class="truncate">{{ $vendor->city }}</span>
                                </p>
                            @endif
                        </div>
                    </div>

                    <a href="{{ route('vendor-directory.show', $vendor) }}"
                        class="block text-center px-3 py-2 rounded-xl bg-[#FAF7F2] border border-[#B6ADA3]/40 text-xs font-semibold text-[#5F6F5B] hover:bg-white transition-colors">
                        Lihat Detail
                    </a>
                </div>
            @empty
                <div class="col-span-full bg-white rounded-2xl border border-[#B6ADA3]/35 shadow-xs py-16 text-center">
                    <i class="fa-solid fa-store text-[#B6ADA3] text-3xl mb-3"></i>
                    <p class="text-sm text-[#5F6F5B] font-semibold">Katalog masih kosong.</p>
                </div>
            @endforelse
        </div>

        {{-- Paginasi --}}
        @if ($vendors->hasPages())
            <div class="no-print">{{ $vendors->links() }}</div>
        @endif
    </div>
@endsection
