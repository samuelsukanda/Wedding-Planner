{{-- Kontak, lokasi, dan sosial media vendor. Dipisah jadi partial supaya bisa
     diletakkan di samping foto hero tanpa ikut terpotong layout. --}}
<h2 class="font-bold text-[#5F6F5B] flex items-center gap-2">
    <i class="fa-solid fa-address-book text-[#D8A7B1]" aria-hidden="true"></i> Informasi Vendor
</h2>

@if ($vendor->address)
    <div>
        <div class="text-xs font-semibold text-[#5F6F5B] mb-1">Alamat</div>
        <p class="text-sm text-[#5F6F5B] leading-relaxed">{{ $vendor->address }}</p>
    </div>
@endif

@if ($vendor->phone)
    <div>
        <div class="text-xs font-semibold text-[#5F6F5B] mb-1">Telepon</div>
        <p class="text-sm text-[#5F6F5B]">{{ $vendor->phone }}</p>
    </div>
@endif

@if ($vendor->instagram)
    <div>
        <div class="text-xs font-semibold text-[#5F6F5B] mb-1">Instagram</div>
        <a href="{{ $vendor->instagram }}" target="_blank" rel="noopener"
            class="text-sm text-[#5F6F5B] hover:text-[#8B3A47] hover:underline break-all inline-block py-1">
            {{ $vendor->instagram }}
        </a>
    </div>
@endif

@if ($vendor->whatsapp)
    <div>
        <div class="text-xs font-semibold text-[#5F6F5B] mb-1">WhatsApp</div>
        <a href="{{ $vendor->whatsapp }}" target="_blank" rel="noopener"
            class="text-sm text-[#5F6F5B] hover:text-[#8B3A47] hover:underline break-all inline-block py-1">
            {{ $vendor->whatsapp }}
        </a>
    </div>
@endif