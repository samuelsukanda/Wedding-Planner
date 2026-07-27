@extends('layouts.app')

@section('title', 'Kirim WA Semua Tamu')

@section('content')
    <div class="space-y-6">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold font-serif-title">Kirim Undangan WA</h1>
                <p class="text-xs text-[#5F6F5B]/70 mt-1">{{ $links->count() }} tamu dengan nomor telepon</p>
            </div>
            <a href="{{ route('guests.index') }}"
                class="px-4 py-2.5 rounded-xl bg-white text-[#5F6F5B] hover:bg-[#FAF7F2] border border-[#B6ADA3]/40 text-xs flex items-center justify-center gap-1.5 shadow-xs font-semibold w-full md:w-auto">
                <i class="fa-solid fa-arrow-left"></i> Kembali
            </a>
        </div>

        <div class="bg-white rounded-2xl border border-[#B6ADA3]/35 shadow-xs overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-[#FAF7F2] text-xs text-[#5F6F5B] uppercase tracking-wider border-b border-[#B6ADA3]/30">
                            <th class="p-4 w-12">No</th>
                            <th class="p-4">Nama Tamu</th>
                            <th class="p-4">No. Telepon</th>
                            <th class="p-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#B6ADA3]/20 text-sm">
                        @forelse($links as $i => $item)
                            <tr class="hover:bg-[#FAF7F2]/60 transition-colors">
                                <td class="p-4 text-[#B6ADA3]">{{ $i + 1 }}</td>
                                <td class="p-4 font-semibold text-[#5F6F5B]">{{ $item->nama }}</td>
                                <td class="p-4 text-xs text-[#5F6F5B]">62{{ $item->phone }}</td>
                                <td class="p-4 text-right">
                                    <a href="{{ $item->url }}" target="_blank" title="Kirim WA"
                                        class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl bg-white text-[#5F6F5B] hover:bg-green-50 border border-[#B6ADA3]/40 text-xs shadow-xs font-semibold transition-colors">
                                        <i class="fa-brands fa-whatsapp text-green-600"></i> Kirim WA
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center py-10 text-[#B6ADA3] text-sm">
                                    Tidak ada tamu dengan nomor telepon.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
