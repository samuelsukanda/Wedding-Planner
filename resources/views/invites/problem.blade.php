@extends('invites.layout')

@section('title', $title)

@section('card')
    <div class="w-16 h-16 rounded-2xl bg-[#D8A7B1]/20 flex items-center justify-center mx-auto mb-4">
        <i class="fa-solid {{ $icon }} text-[#D8A7B1] text-2xl"></i>
    </div>

    <h1 class="font-serif-title text-2xl text-[#5F6F5B] text-center">{{ $title }}</h1>
    <p class="text-center text-[13px] text-[#5F6F5B]/80 mt-2 mb-6 leading-relaxed">{{ $message }}</p>

    {{-- 'block' wajib: <a> itu inline, jadi w-full tidak berlaku --
         tombolnya akan selebar teksnya saja. --}}
    <a href="{{ route('login') }}"
        class="btn-primary-rose block w-full px-5 py-3 rounded-xl text-sm cursor-pointer text-center">
        <i class="fa-solid fa-arrow-right-to-bracket" style="margin-right: 8px;"></i> Masuk ke Akun
    </a>

    @if ($hintLink)
        <p class="mt-5 text-center text-[11px] text-[#B6ADA3] leading-relaxed">
            {{ $hint }}
            <a href="{{ $hintLink }}" class="font-semibold text-[#C2757F] hover:underline">Buat akun baru</a>
        </p>
    @endif
@endsection