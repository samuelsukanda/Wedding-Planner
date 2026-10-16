@extends('layouts.app')

@section('title', 'Request Upgrade Pro')

@section('content')
    <div class="space-y-6">
        <!-- Header -->
        <div class="bg-white p-5 rounded-2xl border border-[#B6ADA3]/35 shadow-xs">
            <h1 class="text-xl md:text-2xl font-bold font-serif-title text-[#5F6F5B]">Request Upgrade Pro</h1>
            <p class="text-xs text-[#5F6F5B]/70 mt-1">
                Tinjau bukti pembayaran dan setujui atau tolak permintaan upgrade. Persetujuan akan mengaktifkan paket
                <strong>Pro</strong>
            </p>
        </div>

        @if ($requests->isEmpty())
            <div class="bg-white p-10 rounded-2xl border border-[#B6ADA3]/35 shadow-xs text-center space-y-3">
                <i class="fa-solid fa-inbox text-3xl text-[#D8A7B1]"></i>
                <h2 class="text-lg font-bold font-serif-title text-[#5F6F5B]">Belum ada request</h2>
                <p class="text-sm text-[#5F6F5B]/80 max-w-md mx-auto">
                    Request dari pasangan yang sudah mengunggah bukti transfer akan muncul di sini.
                </p>
            </div>
        @else
            <div class="space-y-4">
                @foreach ($requests as $request)
                    @php
                        $badge = [
                            'pending' => ['bg-[#FAF7F2] border-[#B6ADA3]/50 text-[#5F6F5B]', 'Menunggu'],
                            'approved' => ['bg-[#F1F5F0] border-[#A3B7A6]/60 text-[#5F6F5B]', 'Disetujui'],
                            'rejected' => ['bg-[#FBF2F4] border-[#D8A7B1]/60 text-[#C2757F]', 'Ditolak'],
                        ][$request->status] ?? ['bg-[#FAF7F2] border-[#B6ADA3]/50 text-[#5F6F5B]', $request->status];
                    @endphp

                    <div class="bg-white p-5 rounded-2xl border border-[#B6ADA3]/35 shadow-xs space-y-4">
                        <div class="flex flex-wrap items-start justify-between gap-3">
                            <div class="min-w-0">
                                <h2 class="text-base font-bold font-serif-title text-[#5F6F5B] truncate">
                                    {{ $request->wedding?->couple_name ?: 'Wedding #' . $request->wedding_id }}
                                </h2>
                                <p class="text-xs text-[#5F6F5B]/70 mt-0.5">
                                    {{ $request->user?->name ?? 'Akun dihapus' }} ·
                                    {{ $request->user?->email ?? '-' }} ·
                                    {{ $request->created_at->translatedFormat('d M Y H:i') }}
                                </p>
                            </div>
                            <!-- Hapus tersedia untuk semua status, bukan cuma yang pending: record lama
                                 yang sudah disetujui atau ditolak juga perlu bisa dibersihkan. -->
                            <div class="flex items-center gap-1 shrink-0">
                                <span
                                    class="rounded-full border px-2.5 py-0.5 text-[11px] font-semibold {{ $badge[0] }}">
                                    {{ $badge[1] }}
                                </span>
                                <form id="del-up-{{ $request->id }}" method="POST"
                                    action="{{ route('admin.upgrade-requests.destroy', $request->id) }}">
                                    @csrf
                                    @method('DELETE')
                                    <button type="button" title="Hapus request" aria-label="Hapus request"
                                        onclick="confirmDelete('del-up-{{ $request->id }}', '{{ $request->wedding?->couple_name ?: 'Request #' . $request->id }}')"
                                        class="btn-icon">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </div>

                        <div class="flex flex-wrap items-center gap-4 text-sm">
                            <span class="font-bold text-[#5F6F5B]">
                                Rp {{ number_format($request->amount, 0, ',', '.') }}
                            </span>
                            <span
                                class="rounded-full bg-[#FAF7F2] border border-[#B6ADA3]/35 px-2 py-0.5 text-[11px] font-semibold text-[#5F6F5B]/80 uppercase">
                                {{ $request->plan }}
                            </span>
                            @if ($request->wedding?->isPro())
                                <span class="text-[11px] font-semibold text-[#5F6F5B]/70">
                                    <i class="fa-solid fa-crown text-[#D8A7B1]"></i> Sudah Pro
                                </span>
                            @endif
                        </div>

                        <div>
                            <p class="text-[10px] font-bold uppercase tracking-wider text-[#5F6F5B]/60 mb-1.5">Bukti
                                Transfer</p>
                            @if (str_ends_with(strtolower($request->proof_path), '.pdf'))
                                <a href="{{ \Illuminate\Support\Facades\Storage::url($request->proof_path) }}"
                                    target="_blank" rel="noopener"
                                    class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-white border border-[#B6ADA3]/40 text-[#5F6F5B] text-sm font-medium hover:bg-[#FAF7F2] transition">
                                    <i class="fa-solid fa-file-pdf text-[#D8A7B1]"></i> Lihat Bukti (PDF)
                                </a>
                            @else
                                <a href="{{ \Illuminate\Support\Facades\Storage::url($request->proof_path) }}"
                                    target="_blank" rel="noopener" class="inline-block">
                                    <img src="{{ \Illuminate\Support\Facades\Storage::url($request->proof_path) }}"
                                        alt="Bukti transfer {{ $request->wedding?->couple_name }}" loading="lazy"
                                        class="max-h-56 rounded-xl border border-[#B6ADA3]/35 hover:opacity-90 transition">
                                </a>
                            @endif
                        </div>

                        @if ($request->status === 'pending')
                            <div class="flex flex-col sm:flex-row sm:items-end gap-3 pt-1 border-t border-[#B6ADA3]/25">
                                <form method="POST" action="{{ route('admin.upgrade-requests.approve', $request->id) }}"
                                    data-confirm="Aktifkan paket Pro untuk {{ $request->wedding?->couple_name ?: 'Wedding Kit ini' }}?">
                                    @csrf
                                    <button type="submit"
                                        class="btn-sage px-5 py-2 rounded-xl text-xs cursor-pointer w-full sm:w-auto">
                                        <i class="fa-solid fa-check mr-1"></i> Setujui
                                    </button>
                                </form>

                                <form method="POST" action="{{ route('admin.upgrade-requests.reject', $request->id) }}"
                                    class="flex flex-col sm:flex-row sm:items-center gap-2 flex-1">
                                    @csrf
                                    <button type="submit"
                                        class="px-5 py-2 rounded-xl bg-white border border-[#D8A7B1]/60 text-[#C2757F] text-xs font-semibold hover:bg-[#FAF7F2] transition cursor-pointer whitespace-nowrap">
                                        <i class="fa-solid fa-xmark mr-1"></i> Tolak
                                    </button>
                                    <input type="text" name="admin_note" required maxlength="500"
                                        placeholder="Alasan ditolak (wajib)"
                                        class="flex-1 px-3.5 py-2 rounded-xl border border-[#B6ADA3]/40 bg-[#FAF7F2] text-xs text-[#5F6F5B] placeholder-[#B6ADA3] focus:border-[#D8A7B1] focus:outline-none transition">
                                </form>
                            </div>

                            @error('admin_note')
                                <p class="text-sm text-[#C2757F]">{{ $message }}</p>
                            @enderror
                        @else
                            <div class="pt-1 border-t border-[#B6ADA3]/25 text-xs text-[#5F6F5B]/70 space-y-1">
                                @if ($request->admin_note)
                                    <p><span class="font-semibold">Catatan admin:</span> {{ $request->admin_note }}</p>
                                @endif
                                @if ($request->reviewer)
                                    <p>Diproses oleh {{ $request->reviewer->name }}
                                        {{ $request->reviewed_at?->translatedFormat('d M Y H:i') }}</p>
                                @endif
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        @endif
    </div>
@endsection

@push('scripts')
    <script>
        // Setuju/tolak mengubah paket pasangan, jadi selalu minta konfirmasi dulu.
        document.querySelectorAll('form[data-confirm]').forEach(function(form) {
            form.addEventListener('submit', function(event) {
                if (form.dataset.confirmed === '1') return;

                event.preventDefault();
                Swal.fire({
                    title: 'Konfirmasi',
                    text: form.dataset.confirm,
                    icon: 'question',
                    iconColor: '#D8A7B1',
                    showCancelButton: true,
                    confirmButtonText: 'Ya, Lanjutkan',
                    cancelButtonText: 'Batal',
                    reverseButtons: true,
                }).then(function(result) {
                    if (result.isConfirmed) {
                        form.dataset.confirmed = '1';
                        form.requestSubmit();
                    }
                });
            });
        });
    </script>
@endpush
