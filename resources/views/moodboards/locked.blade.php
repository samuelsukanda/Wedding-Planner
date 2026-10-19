@extends('layouts.app')

@section('title', 'Moodboard - Paket Pro')

@section('content')
    {{-- Halaman yang sama persis substituir daftar moodboard untuk paket Free.
         Sengaja tanpa tautan ke filter kategori, form, atau tombol tambah:
         paket Free tidak punya akses ke apa pun di modul ini. --}}
    <div class="bg-white rounded-2xl border border-[#B6ADA3]/35 shadow-xs px-6 py-14 md:py-20">
        <div class="max-w-2xl mx-auto text-center">

            <div
                class="w-14 h-14 rounded-2xl border border-[#B6ADA3]/40 mx-auto flex items-center justify-center">
                <i class="fa-solid fa-palette text-xl text-[#5F6F5B]"></i>
            </div>

            <h1 class="mt-5 text-2xl md:text-3xl font-bold font-serif-title text-[#5F6F5B]">
                {{ $feature['title'] }}
            </h1>

            <p class="mt-2 text-sm text-[#5F6F5B]/75 leading-relaxed max-w-xl mx-auto">
                {{ $feature['description'] }}
            </p>

            <div class="mt-6 flex flex-wrap items-center justify-center gap-2.5">
                @foreach ($feature['highlights'] as $highlight)
                    <span
                        class="px-3.5 py-1.5 rounded-full bg-[#FAF7F2] border border-[#B6ADA3]/40 text-[11px] font-bold text-[#5F6F5B]">
                        {{ $highlight }}
                    </span>
                @endforeach
            </div>

            <a href="{{ route('upgrade.index') }}"
                class="mt-8 inline-flex items-center justify-center gap-2 px-6 py-3 rounded-xl bg-[#5F6F5B] hover:bg-[#4F5E4B] text-white text-sm font-semibold transition cursor-pointer">
                <i class="fa-solid fa-crown"></i> Upgrade ke Pro
            </a>

            <p class="mt-5 text-xs text-[#B6ADA3]">{{ $feature['note'] }}</p>
        </div>
    </div>
@endsection