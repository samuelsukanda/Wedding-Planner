@extends('layouts.app')

@section('title', 'Template Persyaratan Nikah')

@section('content')
    <div class="space-y-6" x-data="{
        modalOpen: false,
        editMode: false,
        currentItem: { items: [] },
        rawItems: '',
    }">

        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold font-serif-title">Template Persyaratan Nikah</h1>
                <p class="text-xs text-[#B6ADA3] mt-1">Daftar dokumen master yang bisa dipakai semua pasangan.</p>
            </div>
            <button @click="modalOpen = true; editMode = false; currentItem = { items: [] }; rawItems = ''"
                class="btn-primary-rose px-4 py-2.5 rounded-xl text-sm flex items-center gap-2 cursor-pointer">
                <i class="fa-solid fa-plus"></i> Template Baru
            </button>
        </div>

        <div class="grid md:grid-cols-2 gap-4">
            @forelse ($templates as $template)
                <div class="bg-white rounded-2xl border border-[#B6ADA3]/35 shadow-xs p-5">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <h2 class="text-base font-bold text-[#5F6F5B]">{{ $template->name }}</h2>
                            @if ($template->description)
                                <p class="text-xs text-[#B6ADA3] mt-1">{{ $template->description }}</p>
                            @endif
                        </div>
                        <span @class([
                            'px-2 py-0.5 rounded-full text-[10px] font-semibold shrink-0',
                            'bg-[#5F6F5B]/12 text-[#5F6F5B]' => $template->is_active,
                            'bg-[#B6ADA3]/20 text-[#5F6F5B]' => ! $template->is_active,
                        ])>{{ $template->is_active ? 'Aktif' : 'Nonaktif' }}</span>
                    </div>

                    <div class="text-[10px] text-[#B6ADA3] mt-3">
                        {{ count($template->items ?? []) }} dokumen · dipakai {{ $template->requirements_count }} pasangan
                    </div>

                    <ul class="mt-3 space-y-1 max-h-40 overflow-y-auto">
                        @foreach ($template->items ?? [] as $item)
                            <li class="text-xs text-[#5F6F5B]/90 flex items-start gap-2">
                                <i class="fa-solid fa-circle text-[#B6ADA3] text-[4px] mt-1.5 shrink-0"></i>
                                {{ $item['name'] ?? '—' }}
                            </li>
                        @endforeach
                    </ul>

                    <div class="flex gap-2 mt-4 pt-4 border-t border-[#B6ADA3]/15">
                        <button @click="modalOpen = true; editMode = true; currentItem = {{ Js::from($template) }}; rawItems = {{ Js::from(collect($template->items ?? [])->pluck('name')->implode("\n")) }}"
                            class="text-xs text-[#5F6F5B] hover:text-[#C2757F] cursor-pointer">
                            <i class="fa-solid fa-pen mr-1"></i> Edit
                        </button>
                        <form method="POST" action="{{ route('admin.document-templates.destroy', $template) }}" class="inline"
                            onsubmit="return confirm('Hapus template ini?')">
                            @csrf @method('DELETE')
                            <button class="text-xs text-[#C2757F] hover:text-red-700 cursor-pointer">
                                <i class="fa-solid fa-trash mr-1"></i> Hapus
                            </button>
                        </form>
                    </div>
                </div>
            @empty
                <div class="col-span-full py-12 text-center">
                    <i class="fa-solid fa-file-circle-plus text-[#B6ADA3] text-2xl mb-3"></i>
                    <div class="text-xs text-[#B6ADA3]">Belum ada template.</div>
                </div>
            @endforelse
        </div>

        {{-- Modal --}}
        <div x-show="modalOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4"
            @click.self="modalOpen = false" @keydown.escape.window="modalOpen = false">
            <div class="bg-white rounded-2xl shadow-xl w-full max-w-lg max-h-[90vh] overflow-y-auto">
                <div class="p-5 border-b border-[#B6ADA3]/25 flex items-center justify-between">
                    <h3 class="font-bold text-[#5F6F5B]" x-text="editMode ? 'Edit Template' : 'Template Baru'"></h3>
                    <button @click="modalOpen = false" class="text-[#B6ADA3] hover:text-[#5F6F5B] cursor-pointer"><i class="fa-solid fa-xmark"></i></button>
                </div>

                <form :action="(editMode && currentItem.id) ? '{{ url('/admin/document-templates') }}/' + currentItem.id : '{{ route('admin.document-templates.store') }}'"
                    method="POST" class="p-5 space-y-3">
                    @csrf
                    <template x-if="editMode"><input type="hidden" name="_method" value="PUT"></template>

                    <div>
                        <label class="block text-xs font-semibold text-[#5F6F5B] mb-1.5">Nama Template <span class="text-[#C2757F]">*</span></label>
                        <input type="text" name="name" x-model="currentItem.name" required
                            class="form-input w-full rounded-xl border border-[#B6ADA3]/40 px-3 py-2 text-sm">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-[#5F6F5B] mb-1.5">Deskripsi</label>
                        <input type="text" name="description" x-model="currentItem.description"
                            class="form-input w-full rounded-xl border border-[#B6ADA3]/40 px-3 py-2 text-sm">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-[#5F6F5B] mb-1.5">Daftar Dokumen</label>
                        <p class="text-[10px] text-[#B6ADA3] mb-1.5">Satu dokumen per baris.</p>
                        <textarea name="items_raw" x-model="rawItems" rows="8"
                            class="form-input w-full rounded-xl border border-[#B6ADA3]/40 px-3 py-2 text-sm font-mono"></textarea>
                    </div>

                    <div class="flex items-center gap-2">
                        <input type="hidden" name="is_active" value="0">
                        <input type="checkbox" name="is_active" value="1" x-model="currentItem.is_active" id="is_active"
                            class="w-4 h-4 rounded border-[#B6ADA3]">
                        <label for="is_active" class="text-xs text-[#5F6F5B]">Template aktif (bisa dipakai pasangan)</label>
                    </div>

                    <div class="flex justify-end gap-2 pt-2">
                        <button type="button" @click="modalOpen = false"
                            class="px-4 py-2 rounded-xl text-sm bg-[#B6ADA3]/15 text-[#5F6F5B] cursor-pointer">Batal</button>
                        <button class="btn-primary-rose px-5 py-2 rounded-xl text-sm cursor-pointer">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection