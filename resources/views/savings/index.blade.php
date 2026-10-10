@extends('layouts.app')

@section('title', 'Keuangan & Dokumen - Tabungan Pernikahan')

@section('content')
    <div class="space-y-6" x-data="{
        modalOpen: false,
        editMode: false,
        txOpen: false,
        currentItem: { frequency: null, status: 'Aktif' },
        selectedGoal: null,
        selectedGoalId: null,
        txType: 'setoran',
        txAmount: '',
        txDate: '{{ now()->format('Y-m-d') }}',
        txNotes: '',
    }">

        {{-- Header --}}
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold font-serif-title">Tabungan Pernikahan</h1>
                <p class="text-xs text-[#B6ADA3] mt-1">Rencana dan pantau dana khusus kebutuhan pernikahan.</p>
            </div>
            <button @click="modalOpen = true; editMode = false; currentItem = { frequency: null, status: 'Aktif' }"
                class="btn-primary-rose px-4 py-2.5 rounded-xl text-sm flex items-center justify-center gap-2 cursor-pointer">
                <i class="fa-solid fa-plus"></i> Target Tabungan Baru
            </button>
        </div>

        {{-- Ringkasan (PRD: Saldo saat ini, Target, Estimasi kekurangan dana) --}}
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
            <div class="bg-white p-4 rounded-2xl border border-[#B6ADA3]/35 shadow-xs">
                <div class="text-xs text-[#5F6F5B]/80 mb-1">Total Saldo Tabungan</div>
                <div class="text-lg font-bold text-[#5F6F5B]">Rp {{ number_format($summary['totalBalance'], 0, ',', '.') }}
                </div>
            </div>
            <div class="bg-white p-4 rounded-2xl border border-[#B6ADA3]/35 shadow-xs">
                <div class="text-xs text-[#5F6F5B]/80 mb-1">Total Target</div>
                <div class="text-lg font-bold text-[#5F6F5B]">Rp {{ number_format($summary['totalTarget'], 0, ',', '.') }}
                </div>
            </div>
            <div class="bg-white p-4 rounded-2xl border border-[#D8A7B1]/50 shadow-xs">
                <div class="text-xs text-[#5F6F5B]/80 mb-1">Estimasi Kekurangan Dana</div>
                <div class="text-lg font-bold text-[#C2757F]">Rp
                    {{ number_format($summary['totalShortfall'], 0, ',', '.') }}</div>
            </div>
            <div class="bg-white p-4 rounded-2xl border border-[#B6ADA3]/35 shadow-xs">
                <div class="text-xs text-[#5F6F5B]/80 mb-1">Target Aktif</div>
                <div class="text-lg font-bold text-[#5F6F5B]">{{ $summary['activeGoals'] }} Target</div>
            </div>
        </div>

        {{-- Grafik perkembangan tabungan --}}
        <div class="bg-white p-5 rounded-2xl border border-[#B6ADA3]/35 shadow-xs">
            <h2 class="text-base font-bold font-serif-title text-[#5F6F5B] mb-4 flex items-center gap-2">
                <i class="fa-solid fa-chart-line text-[#D8A7B1]"></i> Grafik Perkembangan Tabungan
            </h2>
            <div class="h-64">
                <canvas id="savingsChart"></canvas>
            </div>
        </div>

        {{-- Daftar target --}}
        <div class="bg-white rounded-2xl border border-[#B6ADA3]/35 shadow-xs overflow-hidden">
            <div class="p-4 border-b border-[#B6ADA3]/30">
                <h2 class="text-base font-bold font-serif-title text-[#5F6F5B]">Target Tabungan</h2>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-[#FAF7F2] text-[10px] uppercase tracking-wider text-[#B6ADA3]">
                        <tr>
                            <th class="text-left px-4 py-3 font-bold">Target</th>
                            <th class="text-left px-4 py-3 font-bold">Saldo Saat Ini</th>
                            <th class="text-left px-4 py-3 font-bold">Progress</th>
                            <th class="text-left px-4 py-3 font-bold">Kekurangan</th>
                            <th class="text-left px-4 py-3 font-bold">Setoran / Jatuh Tempo</th>
                            <th class="text-right px-4 py-3 font-bold">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($goals as $goal)
                            <tr class="border-t border-[#B6ADA3]/20">
                                <td class="px-4 py-3">
                                    <div class="font-semibold text-[#5F6F5B]">{{ $goal->name }}</div>
                                    <div class="text-xs text-[#B6ADA3]">
                                        Target Rp {{ number_format((float) $goal->target_amount, 0, ',', '.') }}
                                        @if ($goal->target_date)
                                            · {{ $goal->target_date->format('d M Y') }}
                                        @endif
                                    </div>
                                    <span
                                        class="inline-block mt-1 rounded-full px-2 py-0.5 text-[10px] font-semibold
                                        {{ $goal->status === 'Tercapai'
                                            ? 'bg-[#A3B7A6]/25 text-[#5F6F5B] border border-[#A3B7A6]/40'
                                            : ($goal->status === 'Ditunda'
                                                ? 'bg-[#B6ADA3]/25 text-[#6B7280] border border-[#B6ADA3]/40'
                                                : 'bg-[#D8A7B1]/20 text-[#C2757F] border border-[#D8A7B1]/40') }}">
                                        {{ $goal->status }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 font-bold text-[#5F6F5B]">
                                    Rp {{ number_format((float) $goal->current_balance, 0, ',', '.') }}
                                </td>
                                <td class="px-4 py-3">
                                    <div class="w-32">
                                        <div class="flex justify-between text-[10px] text-[#B6ADA3] mb-1">
                                            <span>{{ $goal->progressPercent() }}%</span>
                                            <span>{{ $goal->transactions_count }} txn</span>
                                        </div>
                                        <div
                                            class="h-1.5 rounded-full bg-[#FAF7F2] border border-[#B6ADA3]/30 overflow-hidden">
                                            <div class="h-full bg-[#D8A7B1] rounded-full"
                                                style="width: {{ $goal->progressPercent() }}%"></div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-3">
                                    @if ($goal->shortfall() > 0)
                                        <span class="text-[#C2757F] font-semibold">
                                            Rp {{ number_format($goal->shortfall(), 0, ',', '.') }}
                                        </span>
                                    @else
                                        <span class="text-[#A3B7A6] font-semibold">Tercukup</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-xs">
                                    @if ((float) $goal->periodic_amount > 0)
                                        <div class="font-semibold text-[#5F6F5B]">
                                            Rp {{ number_format((float) $goal->periodic_amount, 0, ',', '.') }}
                                            / {{ $goal->frequency }}
                                        </div>
                                        @if ($goal->nextDueDate())
                                            <div class="text-[#B6ADA3] mt-0.5">
                                                Jatuh tempo
                                                {{ \Carbon\Carbon::parse($goal->nextDueDate())->format('d M Y') }}
                                            </div>
                                        @endif
                                    @else
                                        <span class="text-[#B6ADA3]">Belum diatur</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center justify-end gap-2">
                                        <button
                                            @click="txOpen = true; selectedGoalId = {{ $goal->id }}; selectedGoal = @js($goal->only(['id', 'name', 'current_balance', 'target_amount']))"
                                            title="Catat setoran / penarikan"
                                            class="px-2.5 py-1 rounded-lg text-xs font-semibold bg-[#D8A7B1]/15 text-[#C2757F] border border-[#D8A7B1]/40 hover:bg-[#D8A7B1]/25 cursor-pointer whitespace-nowrap">
                                            <i class="fa-solid fa-arrow-right-arrow-left text-[10px]"></i> Transaksi
                                        </button>
                                        <button title="Edit"
                                            @click="modalOpen = true; editMode = true; currentItem = {{ json_encode($goal) }}"
                                            class="p-2 text-[#B6ADA3] hover:text-[#5F6F5B] cursor-pointer">
                                            <i class="fa-solid fa-pen-to-square"></i>
                                        </button>
                                        <form id="del-savings-{{ $goal->id }}"
                                            action="{{ route('savings.destroy', $goal->id) }}" method="POST">
                                            @csrf
                                            @method('DELETE')
                                            <button type="button" title="Hapus"
                                                onclick="confirmDelete('del-savings-{{ $goal->id }}', '{{ addslashes($goal->name) }}')"
                                                class="p-2 text-[#B6ADA3] hover:text-[#D8A7B1] cursor-pointer">
                                                <i class="fa-solid fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-10 text-[#B6ADA3] text-sm">
                                    Belum ada target tabungan.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Riwayat transaksi seluruh target (PRD: Riwayat transaksi) --}}
        <div class="bg-white rounded-2xl border border-[#B6ADA3]/35 shadow-xs overflow-hidden">
            <div class="p-4 border-b border-[#B6ADA3]/30">
                <h2 class="text-base font-bold font-serif-title text-[#5F6F5B]">Riwayat Transaksi</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-[#FAF7F2] text-[10px] uppercase tracking-wider text-[#B6ADA3]">
                        <tr>
                            <th class="text-left px-4 py-3 font-bold">Tanggal</th>
                            <th class="text-left px-4 py-3 font-bold">Target</th>
                            <th class="text-left px-4 py-3 font-bold">Jenis</th>
                            <th class="text-left px-4 py-3 font-bold">Nominal</th>
                            <th class="text-left px-4 py-3 font-bold">Catatan</th>
                            <th class="text-right px-4 py-3 font-bold">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($allTransactions->sortByDesc('transaction_date') as $tx)
                            <tr class="border-t border-[#B6ADA3]/20">
                                <td class="px-4 py-3 text-xs">{{ $tx->transaction_date->format('d M Y') }}</td>
                                <td class="px-4 py-3 font-semibold text-[#5F6F5B]">{{ $tx->goal?->name }}</td>
                                <td class="px-4 py-3">
                                    <span
                                        class="rounded-full px-2 py-0.5 text-[10px] font-semibold
                                        {{ $tx->isPenarikan()
                                            ? 'bg-[#C2757F]/15 text-[#C2757F] border border-[#C2757F]/40'
                                            : 'bg-[#A3B7A6]/25 text-[#5F6F5B] border border-[#A3B7A6]/40' }}">
                                        {{ $tx->isPenarikan() ? 'Penarikan' : 'Setoran' }}
                                    </span>
                                </td>
                                <td
                                    class="px-4 py-3 font-bold {{ $tx->isPenarikan() ? 'text-[#C2757F]' : 'text-[#5F6F5B]' }}">
                                    {{ $tx->isPenarikan() ? '-' : '+' }}
                                    Rp {{ number_format((float) $tx->amount, 0, ',', '.') }}
                                </td>
                                <td class="px-4 py-3 text-xs text-[#B6ADA3]">{{ $tx->notes ?: '—' }}</td>
                                <td class="px-4 py-3 text-right">
                                    <form id="del-txn-{{ $tx->id }}"
                                        action="{{ route('savings.transactions.destroy', [$tx->savings_goal_id, $tx->id]) }}"
                                        method="POST">
                                        @csrf
                                        @method('DELETE')
                                        <button type="button" title="Hapus"
                                            onclick="confirmDelete('del-txn-{{ $tx->id }}', 'transaksi {{ $tx->isPenarikan() ? 'penarikan' : 'setoran' }} Rp {{ number_format((float) $tx->amount, 0, ',', '.') }}')"
                                            class="p-2 text-[#B6ADA3] hover:text-[#D8A7B1] cursor-pointer">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-10 text-[#B6ADA3] text-sm">
                                    Belum ada transaksi.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Modal Target --}}
        <div x-show="modalOpen" x-transition.opacity
            class="fixed inset-0 z-50 bg-[#5F6F5B]/40 backdrop-blur-md flex items-center justify-center p-4">
            <div @click.away="modalOpen = false"
                class="bg-white w-full max-w-lg p-6 rounded-2xl border border-[#B6ADA3]/40 shadow-2xl space-y-4 max-h-[90vh] overflow-y-auto">
                <div class="flex items-center justify-between border-b border-[#B6ADA3]/30 pb-3">
                    <h3 class="text-lg font-bold font-serif-title text-[#5F6F5B]"
                        x-text="editMode ? 'Edit Target Tabungan' : 'Target Tabungan Baru'"></h3>
                    <button @click="modalOpen = false" class="text-[#B6ADA3] hover:text-[#5F6F5B] cursor-pointer">
                        <i class="fa-solid fa-xmark text-lg"></i>
                    </button>
                </div>

                <form :action="editMode ? '{{ url('savings') }}/' + currentItem.id : '{{ route('savings.store') }}'"
                    method="POST" class="space-y-4">
                    @csrf
                    <template x-if="editMode"><input type="hidden" name="_method" value="PUT"></template>

                    <div>
                        <label class="block text-xs font-semibold text-[#5F6F5B] mb-1">Nama Target *</label>
                        <input type="text" name="name" x-model="currentItem.name" required
                            placeholder="Contoh: Dana Venue & Catering"
                            class="w-full bg-[#FAF7F2] border border-[#B6ADA3]/40 text-sm text-[#5F6F5B] px-3 py-2 rounded-xl focus:border-[#D8A7B1] focus:outline-none">
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-[#5F6F5B] mb-1">Target Nominal (Rp) *</label>
                            <input type="text" name="target_amount" inputmode="numeric" required
                                :value="wpMoney(currentItem.target_amount)"
                                @input="currentItem.target_amount = wpDigits($event.target.value)"
                                class="w-full bg-[#FAF7F2] border border-[#B6ADA3]/40 text-sm font-semibold text-[#5F6F5B] px-3 py-2 rounded-xl focus:border-[#D8A7B1] focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-[#5F6F5B] mb-1">Saldo Awal (Rp)</label>
                            <input type="text" name="initial_balance" inputmode="numeric"
                                :value="wpMoney(currentItem.initial_balance)"
                                @input="currentItem.initial_balance = wpDigits($event.target.value)"
                                :disabled="editMode && currentItem.transactions_count > 0"
                                :title="editMode && currentItem.transactions_count > 0 ? 'Terkunci karena sudah ada transaksi' :
                                    ''"
                                class="w-full bg-[#FAF7F2] border border-[#B6ADA3]/40 text-sm font-semibold text-[#5F6F5B] px-3 py-2 rounded-xl focus:border-[#D8A7B1] focus:outline-none disabled:opacity-60">
                            <p x-show="editMode && currentItem.transactions_count > 0"
                                class="text-[10px] text-[#C2757F] mt-1">
                                Terkunci karena sudah ada transaksi.
                            </p>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-[#5F6F5B] mb-1">Nominal Setoran / Periode
                                (Rp)</label>
                            <input type="text" name="periodic_amount" inputmode="numeric"
                                :value="wpMoney(currentItem.periodic_amount)"
                                @input="currentItem.periodic_amount = wpDigits($event.target.value)"
                                class="w-full bg-[#FAF7F2] border border-[#B6ADA3]/40 text-sm text-[#5F6F5B] px-3 py-2 rounded-xl focus:border-[#D8A7B1] focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-[#5F6F5B] mb-1">Frekuensi</label>
                            <select name="frequency" x-model="currentItem.frequency"
                                class="w-full bg-[#FAF7F2] border border-[#B6ADA3]/40 text-sm text-[#5F6F5B] px-3 py-2 rounded-xl focus:border-[#D8A7B1] focus:outline-none">
                                <option value="">—</option>
                                @foreach ($frequencies as $freq)
                                    <option value="{{ $freq }}">{{ $freq }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-[#5F6F5B] mb-1">Target Tanggal</label>
                            <input type="text" name="target_date" x-model="currentItem.target_date"
                                class="datepicker w-full bg-[#FAF7F2] border border-[#B6ADA3]/40 text-sm text-[#5F6F5B] px-3 py-2 rounded-xl focus:border-[#D8A7B1] focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-[#5F6F5B] mb-1">Status</label>
                            <select name="status" x-model="currentItem.status"
                                class="w-full bg-[#FAF7F2] border border-[#B6ADA3]/40 text-sm text-[#5F6F5B] px-3 py-2 rounded-xl focus:border-[#D8A7B1] focus:outline-none">
                                @foreach ($statuses as $st)
                                    <option value="{{ $st }}">{{ $st }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-[#5F6F5B] mb-1">Catatan</label>
                        <textarea name="notes" x-model="currentItem.notes" rows="2" placeholder="Contoh: Tabungan khusus DP gedung"
                            class="w-full bg-[#FAF7F2] border border-[#B6ADA3]/40 text-sm text-[#5F6F5B] px-3 py-2 rounded-xl focus:border-[#D8A7B1] focus:outline-none resize-none"></textarea>
                    </div>

                    @error('target_amount')
                        <p class="text-xs text-[#C2757F]">{{ $message }}</p>
                    @enderror
                    @error('name')
                        <p class="text-xs text-[#C2757F]">{{ $message }}</p>
                    @enderror
                    @error('initial_balance')
                        <p class="text-xs text-[#C2757F]">{{ $message }}</p>
                    @enderror

                    <div class="flex justify-end gap-3 pt-3 border-t border-[#B6ADA3]/30">
                        <button type="button" @click="modalOpen = false"
                            class="px-4 py-2 text-xs text-[#B6ADA3] hover:text-[#5F6F5B] cursor-pointer">Batal</button>
                        <button type="submit" class="btn-primary-rose px-5 py-2 rounded-xl text-xs cursor-pointer">
                            Simpan Target
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Modal Transaksi --}}
        <div x-show="txOpen" x-transition.opacity
            class="fixed inset-0 z-50 bg-[#5F6F5B]/40 backdrop-blur-md flex items-center justify-center p-4">
            <div @click.away="txOpen = false"
                class="bg-white w-full max-w-md p-6 rounded-2xl border border-[#B6ADA3]/40 shadow-2xl space-y-4">
                <div class="flex items-center justify-between border-b border-[#B6ADA3]/30 pb-3">
                    <h3 class="text-lg font-bold font-serif-title text-[#5F6F5B]">Catat Transaksi</h3>
                    <button @click="txOpen = false" class="text-[#B6ADA3] hover:text-[#5F6F5B] cursor-pointer">
                        <i class="fa-solid fa-xmark text-lg"></i>
                    </button>
                </div>

                <div class="text-xs text-[#B6ADA3]">
                    <span x-text="selectedGoal?.name"></span> ·
                    Saldo saat ini
                    <span class="font-bold text-[#5F6F5B]"
                        x-text="'Rp ' + Number(selectedGoal?.current_balance || 0).toLocaleString('id-ID')"></span>
                </div>

                <form :action="selectedGoal ? '{{ url('savings') }}/' + selectedGoal.id + '/transactions' : ''"
                    method="POST" class="space-y-4">
                    @csrf

                    <div>
                        <label class="block text-xs font-semibold text-[#5F6F5B] mb-1">Jenis Transaksi *</label>
                        <select name="type" x-model="txType" required
                            class="w-full bg-[#FAF7F2] border border-[#B6ADA3]/40 text-sm text-[#5F6F5B] px-3 py-2 rounded-xl focus:border-[#D8A7B1] focus:outline-none">
                            <option value="setoran">Setoran</option>
                            <option value="penarikan">Penarikan</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-[#5F6F5B] mb-1">Nominal (Rp) *</label>
                        <input type="number" name="amount" x-model="txAmount" required min="1" step="1"
                            class="w-full bg-[#FAF7F2] border border-[#B6ADA3]/40 text-sm font-bold text-[#5F6F5B] px-3 py-2 rounded-xl focus:border-[#D8A7B1] focus:outline-none">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-[#5F6F5B] mb-1">Tanggal Setoran *</label>
                        <input type="date" name="transaction_date" x-model="txDate" required
                            class="w-full bg-[#FAF7F2] border border-[#B6ADA3]/40 text-sm text-[#5F6F5B] px-3 py-2 rounded-xl focus:border-[#D8A7B1] focus:outline-none">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-[#5F6F5B] mb-1">Catatan</label>
                        <input type="text" name="notes" x-model="txNotes"
                            class="w-full bg-[#FAF7F2] border border-[#B6ADA3]/40 text-sm text-[#5F6F5B] px-3 py-2 rounded-xl focus:border-[#D8A7B1] focus:outline-none">
                    </div>

                    @error('amount')
                        <p class="text-xs text-[#C2757F]">{{ $message }}</p>
                    @enderror

                    <div class="flex justify-end gap-3 pt-3 border-t border-[#B6ADA3]/30">
                        <button type="button" @click="txOpen = false"
                            class="px-4 py-2 text-xs text-[#B6ADA3] hover:text-[#5F6F5B] cursor-pointer">Batal</button>
                        <button type="submit" class="btn-primary-rose px-5 py-2 rounded-xl text-xs cursor-pointer">
                            Simpan Transaksi
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        // Listener dipasang satu kali saja (guard), karena script ini berada di
        // dalam <body> yang di-evaluasi ulang tiap navigasi Turbo.
        if (!window.__savingsChartBound) {
            window.__savingsChartBound = true;

            document.addEventListener('turbo:load', function() {
                const canvas = document.getElementById('savingsChart');
                if (!canvas || !window.Chart) return;

                // Instance lama memegang context canvas yang sudah di-replace Turbo.
                if (window.__savingsChart) {
                    window.__savingsChart.destroy();
                    window.__savingsChart = null;
                }

                const data = @json($chart);

                window.__savingsChart = new Chart(canvas.getContext('2d'), {
                    type: 'line',
                    data: {
                        labels: data.labels,
                        datasets: [{
                            label: 'Saldo Tabungan',
                            data: data.values,
                            borderColor: '#D8A7B1',
                            backgroundColor: 'rgba(216, 167, 177, 0.15)',
                            fill: true,
                            tension: 0.3,
                            pointBackgroundColor: '#5F6F5B',
                            pointRadius: 3,
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: false
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    callback: function(value) {
                                        return 'Rp ' + Number(value).toLocaleString('id-ID');
                                    }
                                }
                            }
                        }
                    }
                });
            });
        }
    </script>
@endpush
