@extends('layouts.app')

@section('title', 'Perencanaan - Checklist')

@section('content')
    <div class="space-y-6" x-data="tableFilter({
        fields: ['search', 'status', 'priority'],
        // Filter tidak disimpan di query string supaya URL tetap bersih.
        initial: { search: '', status: '', priority: '' },
        rows: {{ Js::from(
            $checklists->map(
                fn($c) => [
                    'search' => $c->title,
                    'status' => $c->status,
                    'priority' => $c->priority,
                ],
            ),
        ) }},
        extra: {
            modalOpen: false,
            editMode: false,
            currentItem: {},
            init() {
                // Nilai tanggal baru ditulis x-model setelah currentItem
                // terisi, jadi tampilan flatpickr disegarkan lagi di sini.
                this.$watch('currentItem', () => window.syncDatepickersSoon());
            }
        }
    })">
        <!-- Top Bar -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-white p-5 rounded-2xl border border-[#B6ADA3]/35 shadow-xs">
            <div>
                <h1 class="text-xl md:text-2xl font-bold font-serif-title text-[#5F6F5B]">Checklist</h1>
                <p class="text-xs text-[#5F6F5B] mt-1">Pantau dan kelola seluruh tugas persiapan pernikahan agar tidak ada
                    yang terlewat.</p>
            </div>
            <button @click="modalOpen = true; editMode = false; currentItem = {}"
                class="btn-primary-rose px-4 py-2.5 rounded-xl text-sm flex items-center justify-center gap-2 cursor-pointer w-full md:w-auto">
                <i class="fa-solid fa-plus" aria-hidden="true"></i> Tambah Checklist
            </button>
        </div>

        <!-- Counters Grid -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-3 sm:gap-4">
            <div class="bg-white p-4 rounded-xl border border-[#B6ADA3]/35 shadow-xs flex items-center justify-between">
                <div>
                    <div class="text-xs text-[#5F6F5B]">Total Tugas</div>
                    <div class="text-xl font-bold text-[#5F6F5B]" id="cnt-total">{{ $total }}</div>
                </div>
                <i class="fa-solid fa-list-check text-[#5F6F5B] text-xl" aria-hidden="true"></i>
            </div>
            <div class="bg-white p-4 rounded-xl border border-[#B6ADA3]/35 shadow-xs flex items-center justify-between">
                <div>
                    <div class="text-xs text-[#5F6F5B]">Selesai (Done)</div>
                    <div class="text-xl font-bold text-[#5F6F5B]" id="cnt-done">{{ $doneCount }}</div>
                </div>
                <i class="fa-solid fa-circle-check text-[#5F6F5B] text-xl" aria-hidden="true"></i>
            </div>
            <div class="bg-white p-4 rounded-xl border border-[#B6ADA3]/35 shadow-xs flex items-center justify-between">
                <div>
                    <div class="text-xs text-[#5F6F5B]">Dalam Proses</div>
                    <div class="text-xl font-bold text-[#5F6F5B]" id="cnt-progress">{{ $progressCount }}</div>
                </div>
                <i class="fa-solid fa-spinner text-[#D8A7B1] text-xl" aria-hidden="true"></i>
            </div>
            <div class="bg-white p-4 rounded-xl border border-[#B6ADA3]/35 shadow-xs flex items-center justify-between">
                <div>
                    <div class="text-xs text-[#5F6F5B]">Belum Dimulai</div>
                    <div class="text-xl font-bold text-[#5F6F5B]" id="cnt-todo">{{ $todoCount }}</div>
                </div>
                <i class="fa-solid fa-hourglass-start text-[#D8A7B1] text-xl" aria-hidden="true"></i>
            </div>
        </div>

        <!-- Filter & Search Bar -->
        <div
            class="bg-white p-4 rounded-xl border border-[#B6ADA3]/35 shadow-xs flex flex-col md:flex-row gap-3 items-center justify-between">
            <div class="w-full md:w-auto md:flex md:flex-row md:items-center md:gap-3 space-y-3 md:space-y-0">
                <input type="text" x-model="filters.search" placeholder="Cari nama checklist..."
                    aria-label="Cari nama checklist"
                    class="bg-[#FAF7F2] border border-[#B6ADA3]/40 text-xs text-[#5F6F5B] px-3 py-2.5 rounded-xl focus:border-[#D8A7B1] focus:outline-none w-full md:w-56">

                <div class="grid grid-cols-2 gap-3 md:flex md:items-center md:gap-3">
                    <select x-model="filters.status"
                        id="filter-checklist-status"
                        aria-label="Filter status checklist"
                        class="bg-[#FAF7F2] border border-[#B6ADA3]/40 text-xs text-[#5F6F5B] px-3 py-2.5 rounded-xl focus:border-[#D8A7B1] focus:outline-none w-full md:w-auto">
                        <option value="">Semua Status</option>
                        @foreach ($statuses as $st)
                            <option value="{{ $st }}">{{ $st }}</option>
                        @endforeach
                    </select>

                    <select x-model="filters.priority"
                        id="filter-checklist-priority"
                        aria-label="Filter prioritas checklist"
                        class="bg-[#FAF7F2] border border-[#B6ADA3]/40 text-xs text-[#5F6F5B] px-3 py-2.5 rounded-xl focus:border-[#D8A7B1] focus:outline-none w-full md:w-auto">
                        <option value="">Semua Prioritas</option>
                        @foreach ($priorities as $pr)
                            <option value="{{ $pr }}">{{ $pr }} Priority</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="flex items-center gap-3 shrink-0">
                <span x-show="hasActiveFilter()" x-cloak class="text-xs text-[#B6ADA3] whitespace-nowrap">
                    <span x-text="visibleCount()"></span>/<span x-text="rows.length"></span> item
                </span>
                <button x-show="hasActiveFilter()" x-cloak @click="reset()" type="button"
                    class="text-xs text-[#5F6F5B] hover:text-[#5F6F5B] font-semibold hover:underline cursor-pointer whitespace-nowrap">Reset</button>
            </div>
        </div>

        <!-- Checklist Table -->
        <div class="bg-white rounded-2xl border border-[#B6ADA3]/35 shadow-xs overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr
                            class="bg-[#FAF7F2] text-xs text-[#5F6F5B] uppercase tracking-wider border-b border-[#B6ADA3]/30">
                            <th class="p-4 w-12 text-center">Status</th>
                            <th class="p-4">Judul Checklist</th>
                            <th class="p-4">Kategori</th>
                            <th class="p-4">Tenggat (Deadline)</th>
                            <th class="p-4">Prioritas</th>
                            <th class="p-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#B6ADA3]/20 text-sm">
                        @forelse($checklists as $i => $chk)
                            <tr x-show="visible(rows[{{ $i }}])" x-cloak
                                class="hover:bg-[#FAF7F2]/60 transition-colors">
                                <td class="p-4 text-center">
                                    <button type="button" class="cursor-pointer disabled:opacity-50"
                                        data-url="{{ route('checklists.toggle', $chk->id) }}"
                                        @click="window.wpToggleChecklist($el, {{ $i }})"
                                        :aria-label="'Ubah status checklist'">
                                        <span data-icon>
                                            @if ($chk->status === 'Done')
                                                <i class="fa-solid fa-circle-check text-[#5F6F5B] text-lg"></i>
                                            @elseif($chk->status === 'Progress')
                                                <i class="fa-solid fa-spinner text-[#D8A7B1] text-lg"></i>
                                            @else
                                                <i
                                                    class="fa-regular fa-circle text-[#B6ADA3] text-lg hover:text-[#5F6F5B]"></i>
                                            @endif
                                        </span>
                                    </button>
                                </td>
                                <td data-title
                                    class="p-4 font-semibold text-[#5F6F5B] {{ $chk->status === 'Done' ? 'line-through text-[#B6ADA3]' : '' }}">
                                    {{ $chk->title }}
                                </td>
                                <td class="p-4">
                                    <span
                                        class="text-xs bg-[#FAF7F2] text-[#5F6F5B] font-medium px-2.5 py-1 rounded-lg border border-[#B6ADA3]/40">
                                        {{ $chk->category }}
                                    </span>
                                </td>
                                <td class="p-4 text-xs text-[#5F6F5B]">
                                    {{ $chk->deadline ? \Carbon\Carbon::parse($chk->deadline)->format('d M Y') : '—' }}
                                </td>
                                <td class="p-4">
                                    <span
                                        class="text-xs font-semibold px-2.5 py-1 rounded-full
                                    @if ($chk->priority === 'High') bg-[#FCE8EC] text-[#8B3A47] border border-[#D8A7B1]/60
                                    @elseif($chk->priority === 'Medium') bg-[#EBF2EC] text-[#2E4B33] border border-[#A3B7A6]/60
                                    @else bg-[#F4F0E9] text-[#4E473F] border border-[#B6ADA3]/60 @endif">
                                        {{ $chk->priority }}
                                    </span>
                                </td>
                                <td class="p-4 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <button
                                            @click="modalOpen = true; editMode = true; currentItem = {{ Js::from([
                                                'id' => $chk->id,
                                                'title' => $chk->title,
                                                'category' => $chk->category,
                                                'deadline' => $chk->deadline?->format('Y-m-d'),
                                                'priority' => $chk->priority,
                                                'status' => $chk->status,
                                                'description' => $chk->description,
                                            ]) }}"
                                            title="Edit" class="btn-icon">
                                            <i class="fa-solid fa-pen-to-square"></i>
                                        </button>
                                        <form action="{{ route('checklists.duplicate', $chk->id) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="btn-icon" title="Duplikasi">
                                                <i class="fa-solid fa-copy"></i>
                                            </button>
                                        </form>
                                        <form id="del-chk-{{ $chk->id }}"
                                            action="{{ route('checklists.destroy', $chk->id) }}" method="POST">
                                            @csrf
                                            @method('DELETE')
                                            <button type="button" title="Hapus"
                                                onclick="confirmDelete('del-chk-{{ $chk->id }}', '{{ addslashes($chk->title) }}')"
                                                class="btn-icon">
                                                <i class="fa-solid fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-10 text-[#B6ADA3] text-sm">
                                    <i class="fa-solid fa-list-check text-[#B6ADA3] text-2xl mb-3"></i>
                                    <div>Belum ada item checklist.</div>
                                </td>
                            </tr>
                        @endforelse
                        <tr x-show="hasActiveFilter() && visibleCount() === 0" x-cloak>
                            <td colspan="6" class="text-center py-10 text-[#B6ADA3] text-sm">
                                <i class="fa-solid fa-list-check text-[#B6ADA3] text-2xl mb-3"></i>
                                <div>Tidak ada item yang cocok dengan filter.</div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Modal Form (Tambah / Edit Checklist) -->
        <div x-show="modalOpen" x-transition.opacity x-cloak @keydown.escape.window="modalOpen = false"
            class="fixed inset-0 z-50 bg-[#5F6F5B]/40 backdrop-blur-md flex items-center justify-center p-4">
            <div x-outside="modalOpen = false"
                class="bg-white w-full max-w-lg p-6 rounded-2xl border border-[#B6ADA3]/40 shadow-2xl space-y-4">
                <div class="flex items-center justify-between border-b border-[#B6ADA3]/30 pb-3">
                    <h3 class="text-lg font-bold font-serif-title text-[#5F6F5B]"
                        x-text="editMode ? 'Edit Item Checklist' : 'Tambah Item Checklist'"></h3>
                    <button @click="modalOpen = false" aria-label="Tutup modal" class="text-[#5F6F5B] hover:text-[#2D372E] cursor-pointer"><i
                            class="fa-solid fa-xmark" aria-hidden="true"></i></button>
                </div>

                <form :action="editMode ? '/checklists/' + currentItem.id : '{{ route('checklists.store') }}'"
                    method="POST" class="space-y-4">
                    @csrf
                    <template x-if="editMode">
                        <input type="hidden" name="_method" value="PUT">
                    </template>

                    <div>
                        <label for="modal_title" class="block text-xs font-semibold text-[#5F6F5B] mb-1">Judul Checklist *</label>
                        <input type="text" id="modal_title" name="title" x-model="currentItem.title" required
                            class="w-full bg-[#FAF7F2] border border-[#B6ADA3]/40 text-sm text-[#5F6F5B] px-3 py-2 rounded-xl focus:border-[#D8A7B1] focus:outline-none"
                            placeholder="Misal: Booking Catering">
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label for="modal_category" class="block text-xs font-semibold text-[#5F6F5B] mb-1">Kategori *</label>
                            <select id="modal_category" name="category" x-model="currentItem.category" required
                                class="w-full bg-[#FAF7F2] border border-[#B6ADA3]/40 text-sm text-[#5F6F5B] px-3 py-2 rounded-xl focus:border-[#D8A7B1] focus:outline-none">
                                <option value="">-- Pilih Kategori --</option>
                                @foreach ($categories as $cat)
                                    <option value="{{ $cat }}">{{ $cat }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="modal_priority" class="block text-xs font-semibold text-[#5F6F5B] mb-1">Prioritas *</label>
                            <select id="modal_priority" name="priority" x-model="currentItem.priority" required
                                class="w-full bg-[#FAF7F2] border border-[#B6ADA3]/40 text-sm text-[#5F6F5B] px-3 py-2 rounded-xl focus:border-[#D8A7B1] focus:outline-none">
                                @foreach ($priorities as $pr)
                                    <option value="{{ $pr }}">{{ $pr }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label for="modal_deadline" class="block text-xs font-semibold text-[#5F6F5B] mb-1">Tenggat Waktu</label>
                            <input type="text" id="modal_deadline" name="deadline" x-model="currentItem.deadline"
                                placeholder="DD-MM-YYYY"
                                class="w-full bg-[#FAF7F2] border border-[#B6ADA3]/40 text-sm text-[#5F6F5B] px-3 py-2 rounded-xl focus:border-[#D8A7B1] focus:outline-none datepicker">
                        </div>

                        <div>
                            <label for="modal_status" class="block text-xs font-semibold text-[#5F6F5B] mb-1">Status *</label>
                            <select id="modal_status" name="status" x-model="currentItem.status" required
                                class="w-full bg-[#FAF7F2] border border-[#B6ADA3]/40 text-sm text-[#5F6F5B] px-3 py-2 rounded-xl focus:border-[#D8A7B1] focus:outline-none">
                                @foreach ($statuses as $st)
                                    <option value="{{ $st }}">{{ $st }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div>
                        <label for="modal_description" class="block text-xs font-semibold text-[#5F6F5B] mb-1">Deskripsi / Catatan</label>
                        <textarea id="modal_description" name="description" x-model="currentItem.description" rows="3"
                            class="w-full bg-[#FAF7F2] border border-[#B6ADA3]/40 text-sm text-[#5F6F5B] px-3 py-2 rounded-xl focus:border-[#D8A7B1] focus:outline-none"></textarea>
                    </div>

                    <div class="flex justify-end gap-3 pt-3 border-t border-[#B6ADA3]/30">
                        <button type="button" @click="modalOpen = false"
                            class="px-4 py-2 text-xs text-[#5F6F5B] hover:text-[#2D372E] font-medium cursor-pointer">Batal</button>
                        <button type="submit" class="btn-primary-rose px-5 py-2 rounded-xl text-xs cursor-pointer">Simpan
                            Checklist</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        // Toggle status checklist tanpa reload: POST via fetch (jawab JSON),
        // lalu update ikon, coretan judul, counter, dan baris filter Alpine.
        window.wpToggleChecklist = async function(btn, rowIndex) {
            const url = btn.dataset.url;
            const token = document.querySelector('meta[name="csrf-token"]')?.content;
            btn.disabled = true;
            try {
                const res = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': token,
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    credentials: 'same-origin',
                });
                if (!res.ok) throw new Error('Toggle gagal: ' + res.status);
                const d = await res.json();

                btn.querySelector('[data-icon]').innerHTML = d.status === 'Done' ?
                    '<i class="fa-solid fa-circle-check text-[#5F6F5B] text-lg"></i>' :
                    '<i class="fa-regular fa-circle text-[#B6ADA3] text-lg hover:text-[#5F6F5B]"></i>';

                const row = btn.closest('tr');
                const title = row ? row.querySelector('[data-title]') : null;
                if (title) {
                    title.classList.toggle('line-through', d.status === 'Done');
                    title.classList.toggle('text-[#B6ADA3]', d.status === 'Done');
                }

                const set = (id, v) => {
                    const el = document.getElementById(id);
                    if (el) el.textContent = v;
                };
                set('cnt-total', d.total);
                set('cnt-done', d.doneCount);
                set('cnt-progress', d.progressCount);
                set('cnt-todo', d.todoCount);

                // Sinkron status ke baris filter Alpine supaya filter tetap benar.
                const root = btn.closest('[x-data]');
                if (root && window.Alpine) {
                    const scope = window.Alpine.$data(root);
                    if (scope && scope.rows && scope.rows[rowIndex]) scope.rows[rowIndex].status = d.status;
                }
            } catch (e) {
                console.error(e);
            } finally {
                btn.disabled = false;
            }
        };
    </script>
@endpush
