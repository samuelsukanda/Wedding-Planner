@extends('layouts.app')

@section('title', 'Detail Vendor - Cari Vendor')

@section('content')
    <div class="space-y-6">
        {{-- Header --}}
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-white p-5 rounded-2xl border border-[#B6ADA3]/35 shadow-xs">
            <div class="min-w-0">
                <a href="{{ route('vendor-directory.index') }}"
                    class="text-xs text-[#B6ADA3] hover:text-[#5F6F5B] font-semibold inline-flex items-center gap-1.5">
                    <i class="fa-solid fa-arrow-left text-[10px]"></i> Kembali ke Cari Vendor
                </a>
                <h1 class="text-xl md:text-2xl font-bold font-serif-title text-[#5F6F5B] mt-2 truncate">{{ $vendor->name }}</h1>
                <div class="flex flex-wrap items-center gap-3 mt-1.5 text-xs text-[#5F6F5B]/80">
                    <span class="px-2 py-0.5 rounded border border-[#B6ADA3]/40 bg-[#FAF7F2] font-medium">
                        {{ $vendor->category }}
                    </span>
                    @if ($vendor->city)
                        <span><i class="fa-solid fa-location-dot text-[10px]"></i> {{ $vendor->city }}</span>
                    @endif
                    @if ($vendor->rating > 0)
                        <span class="font-semibold text-[#C2757F]">
                            <i class="fa-solid fa-star text-[10px]"></i>
                            {{ number_format($vendor->rating, 1) }}
                            ({{ $vendor->review_count }} review)
                        </span>
                    @endif
                </div>
            </div>

            @if ($alreadySaved)
                <a href="{{ route('vendors.index') }}"
                    class="px-4 py-2.5 rounded-xl bg-[#FAF7F2] border border-[#B6ADA3]/40 text-[#5F6F5B] text-xs font-semibold flex items-center justify-center gap-2 w-full md:w-auto">
                    <i class="fa-solid fa-circle-check text-[#A3B7A6]"></i> Sudah di Vendor Management
                </a>
            @else
                <form method="POST" action="{{ route('vendor-directory.store', $vendor) }}">
                    @csrf
                    <button type="submit"
                        class="btn-primary-rose px-4 py-2.5 rounded-xl text-sm flex items-center justify-center gap-2 cursor-pointer w-full md:w-auto">
                        <i class="fa-solid fa-plus"></i> Simpan ke Vendor Management
                    </button>
                </form>
            @endif
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            {{-- Kiri: paket, harga, about, kontak --}}
            <div class="lg:col-span-2 space-y-6">
                <div class="bg-white rounded-2xl border border-[#B6ADA3]/35 shadow-xs overflow-hidden">
                    @if ($vendor->photo)
                        <img src="{{ $vendor->photo }}" alt="{{ $vendor->name }}" class="w-full h-56 object-cover">
                    @endif
                </div>

                @if ($vendor->about)
                    <div class="bg-white p-5 sm:p-6 rounded-2xl border border-[#B6ADA3]/35 shadow-xs">
                        <h2 class="font-bold text-[#5F6F5B] flex items-center gap-2 mb-3">
                            <i class="fa-solid fa-circle-info text-[#D8A7B1]"></i> Tentang Vendor
                        </h2>
                        <p class="text-sm text-[#5F6F5B]/90 leading-relaxed">{{ $vendor->about }}</p>
                    </div>
                @endif
            </div>

            {{-- Kanan: kontak, lokasi, sosmed, sumber --}}
            <div class="space-y-6">
                <div class="bg-white p-5 sm:p-6 rounded-2xl border border-[#B6ADA3]/35 shadow-xs space-y-4">
                    <h2 class="font-bold text-[#5F6F5B] flex items-center gap-2">
                        <i class="fa-solid fa-address-book text-[#D8A7B1]"></i> Kontak & Lokasi
                    </h2>

                    @if ($vendor->address)
                        <div>
                            <div class="text-xs text-[#B6ADA3] mb-1">Alamat</div>
                            <p class="text-sm text-[#5F6F5B] leading-relaxed">{{ $vendor->address }}</p>
                        </div>
                    @endif

                    @if ($vendor->phone)
                        <div>
                            <div class="text-xs text-[#B6ADA3] mb-1">Telepon</div>
                            <p class="text-sm text-[#5F6F5B]">{{ $vendor->phone }}</p>
                        </div>
                    @endif

                    @if ($vendor->instagram)
                        <div>
                            <div class="text-xs text-[#B6ADA3] mb-1">Instagram</div>
                            <a href="{{ $vendor->instagram }}" target="_blank" rel="noopener"
                                class="text-sm text-[#5F6F5B] hover:underline break-all">
                                {{ $vendor->instagram }}
                            </a>
                        </div>
                    @endif

                    @if ($vendor->whatsapp)
                        <div>
                            <div class="text-xs text-[#B6ADA3] mb-1">WhatsApp</div>
                            <a href="{{ $vendor->whatsapp }}" target="_blank" rel="noopener"
                                class="text-sm text-[#5F6F5B] hover:underline break-all">
                                {{ $vendor->whatsapp }}
                            </a>
                        </div>
                    @endif

                    <p class="text-[11px] text-[#B6ADA3] pt-3 border-t border-[#B6ADA3]/20">
                        Data katalog dari Weddingku.com
                        @if ($vendor->scraped_at)
                            · diperbarui {{ $vendor->scraped_at->format('d M Y') }}
                        @endif
                    </p>

                    <a href="{{ $vendor->source_url }}" target="_blank" rel="noopener"
                        class="block text-center px-3 py-2 rounded-xl bg-[#FAF7F2] border border-[#B6ADA3]/40 text-xs font-semibold text-[#5F6F5B] hover:bg-white transition-colors">
                        Lihat di Weddingku <i class="fa-solid fa-arrow-up-right-from-square text-[10px]"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>
@endsection
