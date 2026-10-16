{{-- Kontak, lokasi, dan sosial media vendor. Dipisah jadi partial supaya bisa
     diletakkan di samping foto hero tanpa ikut terpotong layout. --}}
<h2 class="font-bold text-[#5F6F5B] flex items-center gap-2">
    <i class="fa-solid fa-address-book text-[#D8A7B1]"></i> Informasi Vendor
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