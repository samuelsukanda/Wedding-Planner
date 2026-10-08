@props([
    'success' => null,
    'error' => null,
    // Sengaja bukan bernama "errors": nama itu akan menimpa $errors milik
    // Laravel di dalam komponen sehingga nilainya selalu null.
    'messages' => null,
])

@php
    // Sumber bisa berupa ViewErrorBag, array ['field' => ['pesan', ...]],
    // atau array datar. Semuanya dinormalkan jadi daftar pesan datar supaya
    // komponen ini aman dipakai dari view mana pun.
    $source = $messages instanceof \Illuminate\Support\ViewErrorBag
        ? $messages->all()
        : (array) ($messages ?? []);

    $errorList = [];

    foreach ($source as $bag) {
        foreach ((array) $bag as $entry) {
            foreach ((array) $entry as $message) {
                $errorList[] = is_array($message) ? implode(' ', $message) : (string) $message;
            }
        }
    }

    $errorList = array_values(array_filter($errorList, fn ($message) => $message !== ''));
@endphp

@if ($success || $error || count($errorList))
    <style>
        @keyframes flash-toast-in {
            from { opacity: 0; transform: translateY(-16px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @media (prefers-reduced-motion: reduce) {
            [data-flash] { animation: none !important; }
        }
    </style>

    <div class="fixed top-4 left-1/2 -translate-x-1/2 z-[100] w-[calc(100%-2rem)] max-w-xl space-y-2">
        {{-- Semua jenis pesan hilang sendiri setelah 4 detik, dan tetap bisa
             ditutup manual lewat tombol ×. --}}
        @if ($success)
            <div data-flash data-flash-auto style="animation: flash-toast-in 300ms ease-out both;"
                class="flex items-start gap-3 rounded-2xl border border-[#A3B7A6]/60 bg-[#F1F5F0] px-4 py-3.5 text-sm text-[#5F6F5B] shadow-lg shadow-[#5F6F5B]/10">
                <i class="fa-solid fa-circle-check mt-0.5 text-[#A3B7A6]"></i>
                <span class="flex-1">{{ $success }}</span>
                <button type="button" data-flash-dismiss aria-label="Tutup"
                    class="cursor-pointer text-[#5F6F5B]/70 transition hover:text-[#5F6F5B]">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
        @endif

        @if ($error)
            <div data-flash data-flash-auto style="animation: flash-toast-in 300ms ease-out both;"
                class="flex items-start gap-3 rounded-2xl border border-[#D8A7B1]/70 bg-[#FBF2F4] px-4 py-3.5 text-sm text-[#5F6F5B] shadow-lg shadow-[#D8A7B1]/15">
                <i class="fa-solid fa-circle-exclamation mt-0.5 text-[#D8A7B1]"></i>
                <span class="flex-1">{{ $error }}</span>
                <button type="button" data-flash-dismiss aria-label="Tutup"
                    class="cursor-pointer text-[#5F6F5B]/70 transition hover:text-[#5F6F5B]">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
        @endif

        @if (count($errorList))
            <div data-flash data-flash-auto style="animation: flash-toast-in 300ms ease-out both;"
                class="rounded-2xl border border-[#D8A7B1]/70 bg-[#FBF2F4] px-4 py-3.5 text-sm text-[#5F6F5B] shadow-lg shadow-[#D8A7B1]/15">
                <div class="flex items-start gap-3">
                    <i class="fa-solid fa-circle-exclamation mt-0.5 text-[#D8A7B1]"></i>
                    <div class="flex-1">
                        <p class="font-semibold">Mohon lengkapi data berikut:</p>
                        <ul class="mt-1 list-disc space-y-0.5 pl-4 text-[#5F6F5B]/90">
                            @foreach ($errorList as $message)
                                <li class="text-xs">{{ $message }}</li>
                            @endforeach
                        </ul>
                    </div>
                    <button type="button" data-flash-dismiss aria-label="Tutup"
                        class="cursor-pointer text-[#5F6F5B]/70 transition hover:text-[#5F6F5B]">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>
            </div>
        @endif
    </div>

    <script>
        (function() {
            document.querySelectorAll('[data-flash-dismiss]').forEach(function(button) {
                button.addEventListener('click', function() {
                    const node = button.closest('[data-flash]');
                    if (node) node.remove();
                });
            });

            // Semua pesan (sukses, error, validasi) hilang sendiri, dan tombol
            // × tetap bisa menutup lebih cepat.
            document.querySelectorAll('[data-flash-auto]').forEach(function(node) {
                window.setTimeout(function() {
                    node.style.transition = 'opacity 250ms ease, transform 250ms ease';
                    node.style.opacity = '0';
                    node.style.transform = 'translateY(-16px)';
                    window.setTimeout(function() { node.remove(); }, 260);
                }, 4000);
            });
        })();
    </script>
@endif