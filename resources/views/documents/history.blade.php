@extends('layouts.app')

@section('title', 'Riwayat - ' . $requirement->name)

@section('content')
    <div class="space-y-6">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <a href="{{ route('documents.index') }}" class="text-xs text-[#B6ADA3] hover:text-[#5F6F5B]">
                    <i class="fa-solid fa-arrow-left mr-1"></i> Kembali ke Persyaratan Nikah
                </a>
                <h1 class="text-2xl font-bold font-serif-title mt-2">Riwayat: {{ $requirement->name }}</h1>
                <p class="text-xs text-[#B6ADA3] mt-1">
                    Status saat ini <strong>{{ $requirement->statusLabel() }}</strong>
                    @if ($requirement->pic_name) · PIC {{ $requirement->pic_name }} @endif
                </p>
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-[#B6ADA3]/35 shadow-xs p-5">
            @forelse ($histories as $history)
                <div class="flex items-start gap-3 pb-5 last:pb-0 border-b border-[#B6ADA3]/15 last:border-0">
                    <div class="flex flex-col items-center shrink-0">
                        <div class="w-2.5 h-2.5 rounded-full bg-[#D8A7B1]"></div>
                        <div class="flex-1 w-px bg-[#B6ADA3]/25 mt-1"></div>
                    </div>
                    <div class="flex-1">
                        <div class="text-sm text-[#5F6F5B]">{{ $history->note ?: 'Status diubah menjadi ' . $history->status }}</div>
                        <div class="text-[10px] text-[#B6ADA3] mt-0.5">
                            {{ $history->created_at->format('d M Y H:i') }}
                            @if ($history->pic_name) · PIC {{ $history->pic_name }} @endif
                            @if ($history->deadline) · Deadline {{ $history->deadline->format('d M Y') }} @endif
                        </div>
                    </div>
                </div>
            @empty
                <div class="py-8 text-center text-xs text-[#B6ADA3]">Belum ada riwayat untuk dokumen ini.</div>
            @endforelse
        </div>
    </div>
@endsection