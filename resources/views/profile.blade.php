@extends('layouts.app')

@section('title', 'Profil Saya')

@section('content')
    <div class="space-y-6 md:space-y-8">

        {{-- Kartu identitas: foto, nama, email, dan aksi ganti/hapus foto --}}
        <div class="rounded-2xl bg-white border border-[#B6ADA3]/35 shadow-sm p-6 sm:p-7">
            <div class="flex flex-col sm:flex-row sm:items-center gap-6">

                <div class="relative shrink-0">
                    @if ($user->avatarUrl())
                        <img src="{{ $user->avatarUrl() }}" alt="Foto {{ $user->name }}"
                            class="w-20 h-20 sm:w-24 sm:h-24 rounded-full object-cover border-2 border-[#D8A7B1]/50">
                    @else
                        <div
                            class="w-20 h-20 sm:w-24 sm:h-24 rounded-full bg-[#A855A0] text-white flex items-center justify-center text-3xl font-bold">
                            {{ $user->initial() }}
                        </div>
                    @endif

                    {{--Tombol pena: memicu input file foto --}}
                    <button type="button" data-photo-trigger aria-label="Ganti foto"
                        class="absolute -bottom-0.5 -right-0.5 w-7 h-7 rounded-full bg-[#D8A7B1] text-white shadow-md flex items-center justify-center hover:bg-[#C2757F] transition cursor-pointer">
                        <i class="fa-solid fa-pen text-[10px]"></i>
                    </button>
                </div>

                <div class="min-w-0 flex-1">
                    <h3 class="text-lg font-bold text-[#5F6F5B]">{{ $user->name }}</h3>
                    <p class="text-sm text-[#B6ADA3] break-all">{{ $user->email }}</p>

                    <div class="mt-4 flex flex-wrap items-center gap-3">
                        <form method="POST" action="{{ route('profile.photo.update') }}" enctype="multipart/form-data"
                            data-turbo="false">
                            @csrf
                            <input type="file" name="photo" id="photo-input" accept="image/*" class="hidden">
                            <button type="button" data-photo-trigger
                                class="px-4 py-2 rounded-xl border border-[#D8A7B1]/60 text-[#C2757F] text-sm font-medium hover:bg-[#D8A7B1]/10 transition cursor-pointer">
                                Ganti Foto
                            </button>
                        </form>

                        @if ($user->profile_photo)
                            <form method="POST" action="{{ route('profile.photo.destroy') }}" data-turbo="false"
                                onsubmit="return confirm('Hapus foto profil?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                    class="px-4 py-2 rounded-xl border border-[#C2757F]/60 text-[#C2757F] text-sm font-medium hover:bg-[#C2757F]/10 transition cursor-pointer">
                                    Hapus Foto
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>

            @error('photo')
                <p class="mt-4 text-sm text-[#C2757F]">{{ $message }}</p>
            @enderror
        </div>

        {{-- Detail akun. Nama dan email belum bisa diubah dari sini. --}}
        <div class="rounded-2xl bg-white border border-[#B6ADA3]/35 shadow-sm p-6 sm:p-7">
            <h3 class="text-base font-bold text-[#5F6F5B] mb-4">Informasi Akun</h3>
            <dl class="grid gap-4 sm:grid-cols-2 text-sm">
                <div>
                    <dt class="text-xs uppercase tracking-wider text-[#B6ADA3] font-bold mb-1">Nama Lengkap</dt>
                    <dd class="text-[#5F6F5B]">{{ $user->name }}</dd>
                </div>
                <div>
                    <dt class="text-xs uppercase tracking-wider text-[#B6ADA3] font-bold mb-1">Email</dt>
                    <dd class="text-[#5F6F5B] break-all">{{ $user->email }}</dd>
                </div>
                <div>
                    <dt class="text-xs uppercase tracking-wider text-[#B6ADA3] font-bold mb-1">Bergabung Sejak</dt>
                    <dd class="text-[#5F6F5B]">{{ $user->created_at->format('d F Y') }}</dd>
                </div>
                <div>
                    <dt class="text-xs uppercase tracking-wider text-[#B6ADA3] font-bold mb-1">Peran</dt>
                    <dd class="text-[#5F6F5B]">{{ $user->is_superadmin ? 'Superadmin' : 'Bride & Groom' }}</dd>
                </div>
            </dl>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        // Satu input file dipakai bersama oleh tombol pena dan tombol "Ganti Foto",
        // jadi tidak perlu dua input terpisah.
        (function() {
            const input = document.getElementById('photo-input');
            if (!input) return;

            document.querySelectorAll('[data-photo-trigger]').forEach(function(button) {
                button.addEventListener('click', function() { input.click(); });
            });

            input.addEventListener('change', function() {
                if (input.files && input.files.length) {
                    input.form.submit();
                }
            });
        })();
    </script>
@endpush