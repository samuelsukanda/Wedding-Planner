@extends('layouts.app')

@section('title', 'Tambah Pasangan')

@section('content')
    <div class="space-y-6">
        <!-- Header -->
        <div class="bg-white p-5 rounded-2xl border border-[#B6ADA3]/35 shadow-xs">
            <h1 class="text-xl md:text-2xl font-bold font-serif-title text-[#5F6F5B]">Tambah Pasangan</h1>
            <p class="text-xs text-[#5F6F5B]/70 mt-1">
                Buat dan bagikan link undangan agar pasanganmu dapat bergabung dan mengelola persiapan pernikahan bersama.
            </p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 items-start">

            <!-- ================= PASANGAN YANG SUDAH TERHUBUNG ================= -->
            <div class="bg-white p-6 rounded-2xl border border-[#B6ADA3]/35 shadow-xs space-y-4">
                <h2 class="text-sm font-bold text-[#5F6F5B] flex items-center gap-2">
                    <i class="fa-solid fa-users text-[#D8A7B1]"></i> Pasangan
                </h2>

                @php $partner = $wedding->users->where('id', '!=', auth()->id()); @endphp

                @if ($partner->isNotEmpty())
                    <div class="space-y-2">
                        @foreach ($partner as $member)
                            <div class="flex items-center gap-3 rounded-xl bg-[#FAF7F2] px-3.5 py-3">
                                <div
                                    class="w-9 h-9 shrink-0 rounded-full bg-[#A855A0] text-white flex items-center justify-center text-sm font-bold">
                                    {{ $member->initial() }}
                                </div>
                                <div class="min-w-0">
                                    <div class="text-sm font-semibold text-[#5F6F5B] truncate">{{ $member->name }}</div>
                                    <div class="text-xs text-[#B6ADA3] truncate">{{ $member->email }}</div>
                                </div>
                                <i class="fa-solid fa-circle-check text-[#A3B7A6] ml-auto text-xs"></i>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-sm text-[#5F6F5B]/75 leading-relaxed">
                        Belum ada pasangan yang terhubung. Bagikan link undangan agar pasanganmu dapat bergabung dan
                        mengelola persiapan pernikahan bersama.
                    </p>
                @endif
            </div>

            <!-- ================= LINK UNDANGAN ================= -->
            <div class="bg-white p-6 rounded-2xl border border-[#B6ADA3]/35 shadow-xs space-y-4">
                <h2 class="text-sm font-bold text-[#5F6F5B] flex items-center gap-2">
                    <i class="fa-solid fa-link text-[#D8A7B1]"></i> Link Undangan
                </h2>

                @php $pending = $invites->firstWhere('accepted_at', null); @endphp

                @if ($pending && $pending->expires_at->isFuture())
                    <div class="space-y-3">
                        <p class="text-sm text-[#5F6F5B]/75 leading-relaxed">
                            Berlaku sampai
                            <strong>{{ $pending->expires_at->translatedFormat('d M Y H:i') }}</strong> dan hanya bisa
                            dipakai satu kali.
                        </p>

                        <div class="rounded-xl bg-[#FAF7F2] border border-[#B6ADA3]/35 px-3.5 py-3 break-all">
                            <code class="text-xs text-[#5F6F5B]" id="invite-link">{{ $inviteUrl($pending) }}</code>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                            <button type="button" data-copy="{{ $inviteUrl($pending) }}"
                                class="px-4 py-2.5 rounded-xl bg-white border border-[#B6ADA3]/50 text-[#5F6F5B] text-xs font-semibold hover:bg-[#FAF7F2] transition cursor-pointer">
                                <i class="fa-solid fa-copy mr-1"></i> Salin Link
                            </button>

                            <a href="https://wa.me/?text={{ rawurlencode('Halo, kamu Capitan undangan untuk Wedding Kit kami. Buka link ini untuk membuat akun dan bergabung: ' . $inviteUrl($pending)) }}&api=wa"
                                target="_blank" rel="noopener"
                                class="px-4 py-2.5 rounded-xl btn-primary-rose text-xs flex items-center justify-center">
                                <i class="fa-brands fa-whatsapp mr-1"></i> Bagikan ke WhatsApp
                            </a>
                        </div>

                        <form method="POST" action="{{ route('profile.partner-invites.destroy', $pending->id) }}">
                            @csrf
                            @method('DELETE')
                            <button type="submit"
                                class="w-full px-4 py-2.5 rounded-xl bg-white border border-[#B6ADA3]/40 text-[#5F6F5B] text-xs font-semibold hover:bg-[#FAF7F2] transition cursor-pointer">
                                Batalkan Link
                            </button>
                        </form>
                    </div>
                @else
                    <div class="space-y-3">
                        <p class="text-sm text-[#5F6F5B]/75 leading-relaxed">
                            Belum ada link undangan aktif. Link berlaku selama
                            <strong>{{ \App\Models\WeddingInvite::VALID_DAYS }} hari</strong> dan hanya dapat digunakan satu
                            kali.
                        </p>

                        <form method="POST" action="{{ route('profile.partner-invites.store') }}">
                            @csrf
                            <button type="submit"
                                class="btn-primary-rose w-full px-4 py-3 rounded-xl text-sm cursor-pointer">
                                <i class="fa-solid fa-link mr-1"></i> Buat Link Undangan
                            </button>
                        </form>
                    </div>
                @endif
            </div>
        </div>

        <!-- ================= RIWAYAT ================= -->
        @if ($invites->isNotEmpty())
            <div class="bg-white p-6 rounded-2xl border border-[#B6ADA3]/35 shadow-xs space-y-3">
                <h2 class="text-sm font-bold text-[#5F6F5B]">Riwayat Undangan</h2>

                <ul class="divide-y divide-[#B6ADA3]/25 text-sm">
                    @foreach ($invites as $invite)
                        <li class="flex flex-wrap items-center justify-between gap-2 py-2.5">
                            <div class="min-w-0">
                                <div class="text-[#5F6F5B]">
                                    @if ($invite->acceptedUser)
                                        Digunakan oleh {{ $invite->acceptedUser->name }}
                                    @elseif ($invite->accepted_at)
                                        Sudah pernah dipakai
                                    @elseif ($invite->expires_at->isPast())
                                        Kedaluwarsa
                                    @else
                                        Menunggu dipakai
                                    @endif
                                </div>
                                <div class="text-[11px] text-[#B6ADA3]">
                                    Dibuat {{ $invite->created_at->translatedFormat('d M Y H:i') }} ·
                                    kedaluwarsa {{ $invite->expires_at->translatedFormat('d M Y') }}
                                </div>
                            </div>

                            <span
                                class="shrink-0 rounded-full border px-2.5 py-0.5 text-[11px] font-semibold {{ $invite->isPending() ? 'border-[#A3B7A6]/60 bg-[#F1F5F0] text-[#5F6F5B]' : 'border-[#B6ADA3]/40 bg-[#FAF7F2] text-[#5F6F5B]/60' }}">
                                {{ $invite->isPending() ? 'Aktif' : 'Tidak aktif' }}
                            </span>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif
    </div>
@endsection

@push('scripts')
    <script>
        // Salin link tanpa library: navigator.clipboard butuh HTTPS, sedangkan
        // banyak yang masih membuka lewat http://localhost atau IP LAN.
        document.querySelectorAll('[data-copy]').forEach(function(button) {
            button.addEventListener('click', function() {
                const text = button.dataset.copy;
                const done = function(ok) {
                    const label = button.querySelector('i');
                    if (!label) return;
                    const original = 'fa-copy';
                    label.classList.remove(original);
                    label.classList.add(ok ? 'fa-check' : 'fa-xmark');
                    window.setTimeout(function() {
                        label.classList.remove('fa-check', 'fa-xmark');
                        label.classList.add(original);
                    }, 1800);
                };

                if (navigator.clipboard && window.isSecureContext) {
                    navigator.clipboard.writeText(text).then(function() {
                        done(true);
                    }, function() {
                        done(false);
                    });
                    return;
                }

                // Fallback untuk konteks non-secure.
                const helper = document.createElement('textarea');
                helper.value = text;
                helper.setAttribute('readonly', '');
                helper.style.position = 'fixed';
                helper.style.opacity = '0';
                document.body.appendChild(helper);
                helper.select();

                let ok = false;
                try {
                    ok = document.execCommand('copy');
                } catch (e) {
                    ok = false;
                }
                document.body.removeChild(helper);
                done(ok);
            });
        });
    </script>
@endpush
