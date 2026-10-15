@extends('layouts.app')

@section('title', 'Cari Vendor')

@section('content')
    <div class="space-y-6">
        {{-- Header --}}
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-white p-5 rounded-2xl border border-[#B6ADA3]/35 shadow-xs">
            <div>
                <h1 class="text-xl md:text-2xl font-bold font-serif-title text-[#5F6F5B]">Cari Vendor</h1>
                <p class="text-xs text-[#5F6F5B]/70 mt-1">Katalog vendor dari Weddingku untuk riset dan bandingkan pilihan.</p>
            </div>
            <div class="flex items-center gap-2">
                <span class="text-xs text-[#B6ADA3]">
                    <strong class="text-[#5F6F5B]">{{ number_format($vendors->total()) }}</strong> vendor
                </span>
            </div>
        </div>

        {{-- Filter --}}
        <form method="GET" action="{{ route('vendor-directory.index') }}"
            class="bg-white p-4 rounded-2xl border border-[#B6ADA3]/35 shadow-xs">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
                <input type="text" name="q" value="{{ $q }}" placeholder="Cari nama vendor / kota..."
                    class="bg-[#FAF7F2] border border-[#B6ADA3]/40 text-xs text-[#5F6F5B] px-3 py-2.5 rounded-xl focus:border-[#D8A7B1] focus:outline-none lg:col-span-2">

                <select name="category" class="bg-[#FAF7F2] border border-[#B6ADA3]/40 text-xs text-[#5F6F5B] px-3 py-2.5 rounded-xl focus:border-[#D8A7B1] focus:outline-none">
                    <option value="">Semua Kategori</option>
                    @foreach ($categories as $cat)
                        <option value="{{ $cat }}" @selected($category === $cat)>{{ $cat }}</option>
                    @endforeach
                </select>

                <select name="city" class="bg-[#FAF7F2] border border-[#B6ADA3]/40 text-xs text-[#5F6F5B] px-3 py-2.5 rounded-xl focus:border-[#D8A7B1] focus:outline-none">
                    <option value="">Semua Kota</option>
                    @foreach ($cities as $kota)
                        <option value="{{ $kota }}" @selected($city === $kota)>{{ $kota }}</option>
                    @endforeach
                </select>

                <select name="sort" class="bg-[#FAF7F2] border border-[#B6ADA3]/40 text-xs text-[#5F6F5B] px-3 py-2.5 rounded-xl focus:border-[#D8A7B1] focus:outline-none">
                    <option value="">Urut: Nama</option>
                    <option value="rating" @selected($sort === 'rating')>Urut: Rating Tertinggi</option>
                </select>
            </div>

            <div class="flex items-center justify-between gap-3 mt-3">
                <p class="text-[11px] text-[#B6ADA3]">Sumber data: Weddingku.com</p>
                <div class="flex items-center gap-2">
                    @if ($q || $category || $city || $sort)
                        <a href="{{ route('vendor-directory.index') }}"
                            class="px-3 py-2 text-xs text-[#B6ADA3] hover:text-[#5F6F5B] font-semibold">Reset</a>
                    @endif
                    <button type="submit"
                        class="btn-primary-rose px-4 py-2.5 rounded-xl text-xs cursor-pointer">Terapkan</button>
                </div>
            </div>
        </form>

        {{-- Grid --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-4 gap-4">
        @forelse ($vendors as $vendor)
            <div class="bg-white rounded-2xl border border-[#B6ADA3]/35 shadow-xs overflow-hidden flex flex-col hover:border-[#D8A7B1] transition-colors">
                <a href="{{ route('vendor-directory.show', $vendor) }}" class="block">
                    @if ($vendor->photo)
                        <img src="{{ $vendor->photo }}" alt="{{ $vendor->name }}" loading="lazy"
                            class="w-full h-40 object-cover">
                    @else
                        <div class="w-full h-40 bg-[#FAF7F2] flex items-center justify-center">
                            <i class="fa-solid fa-store text-[#B6ADA3] text-3xl"></i>
                        </div>
                    @endif
                </a>

                <div class="p-4 flex flex-col flex-1 gap-2">
                    <div class="flex items-start justify-between gap-2">
                        <a href="{{ route('vendor-directory.show', $vendor) }}"
                            class="font-bold text-sm text-[#5F6F5B] hover:underline">{{ $vendor->name }}</a>
                        @if ($vendor->rating > 0)
                            <span class="shrink-0 text-[10px] font-bold px-2 py-1 rounded-full bg-[#D8A7B1]/20 text-[#C2757F] border border-[#D8A7B1]/40">
                                <i class="fa-solid fa-star text-[10px]"></i> {{ number_format($vendor->rating, 1) }}
                            </span>
                        @endif
                    </div>

                    <div class="flex flex-wrap items-center gap-2 text-[11px] text-[#5F6F5B]/80">
                        <span class="px-2 py-0.5 rounded border border-[#B6ADA3]/40 bg-[#FAF7F2] font-medium">
                            {{ $vendor->category }}
                        </span>
                        @if ($vendor->city)
                            <span><i class="fa-solid fa-location-dot text-[10px]"></i> {{ $vendor->city }}</span>
                        @endif
                        @if ($vendor->review_count > 0)
                            <span><i class="fa-regular fa-comment-dots text-[10px]"></i> {{ $vendor->review_count }}</span>
                        @endif
                    </div>

                    <div class="mt-auto pt-3">
                        <a href="{{ route('vendor-directory.show', $vendor) }}"
                            class="block text-center px-3 py-2 rounded-xl bg-[#FAF7F2] border border-[#B6ADA3]/40 text-xs font-semibold text-[#5F6F5B] hover:bg-white transition-colors">
                            Lihat Detail
                        </a>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-full bg-white rounded-2xl border border-[#B6ADA3]/35 shadow-xs py-16 text-center">
                <i class="fa-solid fa-store text-[#B6ADA3] text-3xl mb-3"></i>
                <p class="text-sm text-[#5F6F5B] font-semibold">Katalog masih kosong.</p>
                <p class="text-xs text-[#B6ADA3] mt-1">
                    Jalankan <code class="px-1.5 py-0.5 rounded bg-[#FAF7F2]">php artisan vendors:import</code> untuk mengisi data.
                </p>
            </div>
        @endforelse
        </div>

        {{-- Paginasi --}}
        @if ($vendors->hasPages())
            <div class="no-print">{{ $vendors->links() }}</div>
        @endif
    </div>
@endsection
