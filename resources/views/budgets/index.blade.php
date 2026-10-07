@extends('layouts.app')

@section('title', 'Perencanaan - Budget Planner')

@section('content')
    <style>
        [x-cloak] {
            display: none !important;
        }
    </style>
    <div class="space-y-6" x-data="{
        modalOpen: false,
        editMode: false,
        currentItem: {},
        vendors: {{ $vendorsJson }},
        selectedVendorId: '',
        get availableVendors() {
            const cat = this.currentItem.category;
            return cat && this.vendors[cat] ? this.vendors[cat] : [];
        },
        fillFromVendor() {
            const vendor = this.availableVendors.find(v => v.id == this.selectedVendorId);
            if (vendor) {
                this.currentItem.vendor_id = vendor.id;
                this.currentItem.item_name = vendor.package;
                this.currentItem.actual_cost = vendor.price;
            }
        }
    }">
        <!-- Top Bar -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold font-serif-title">Budget Planner</h1>
            </div>
            <button @click="modalOpen = true; editMode = false; currentItem = {}"
                class="btn-primary-rose px-4 py-2.5 rounded-xl text-sm flex items-center justify-center gap-2 cursor-pointer w-full md:w-auto">
                <i class="fa-solid fa-plus"></i> Tambah Item Anggaran
            </button>
        </div>

        <!-- Summary Widgets -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div class="bg-white p-5 rounded-2xl border border-[#B6ADA3]/35 shadow-xs flex items-center justify-between">
                <div>
                    <div class="text-xs text-[#5F6F5B ]">Total Direncanakan</div>
                    <div class="text-xl sm:text-2xl font-bold text-[#5F6F5B]">Rp
                        {{ number_format($totalPlanned, 0, ',', '.') }}</div>
                </div>
                <div
                    class="w-11 h-11 rounded-xl bg-[#A3B7A6]/20 border border-[#A3B7A6]/40 flex items-center justify-center text-[#5F6F5B] text-lg">
                    <i class="fa-solid fa-calculator"></i>
                </div>
            </div>

            <div class="bg-white p-5 rounded-2xl border border-[#B6ADA3]/35 shadow-xs flex items-center justify-between">
                <div>
                    <div class="text-xs text-[#5F6F5B ]">Total Biaya Terpakai</div>
                    <div class="text-xl sm:text-2xl font-bold text-[#5F6F5B]">Rp
                        {{ number_format($totalActual, 0, ',', '.') }}</div>
                </div>
                <div
                    class="w-11 h-11 rounded-xl bg-[#A3B7A6]/20 border border-[#A3B7A6]/40 flex items-center justify-center text-[#5F6F5B] text-lg">
                    <i class="fa-solid fa-receipt"></i>
                </div>
            </div>

            <div class="bg-white p-5 rounded-2xl border border-[#B6ADA3]/35 shadow-xs flex items-center justify-between">
                <div>
                    <div class="text-xs text-[#5F6F5B]">Sisa Anggaran Pernikahan</div>
                    <div class="text-xl sm:text-2xl font-bold text-[#5F6F5B]">Rp
                        {{ number_format($totalRemaining, 0, ',', '.') }}</div>
                </div>
                <div
                    class="w-11 h-11 rounded-xl bg-[#A3B7A6]/25 border border-[#A3B7A6]/40 flex items-center justify-center text-[#5F6F5B] text-lg">
                    <i class="fa-solid fa-piggy-bank"></i>
                </div>
            </div>
        </div>

        <!-- Charts Section (Pie & Bar Chart) -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Pie Chart -->
            <div class="bg-white p-5 sm:p-6 rounded-2xl border border-[#B6ADA3]/35 shadow-xs space-y-4">
                <h3 class="font-bold text-[#5F6F5B] text-base flex items-center gap-2">
                    <i class="fa-solid fa-chart-pie text-[#D8A7B1]"></i> Distribusi Budget per Kategori
                </h3>
                <div class="h-64 relative flex items-center justify-center">
                    <canvas id="budgetPieChart"></canvas>
                </div>
            </div>

            <!-- Bar Chart -->
            <div class="bg-white p-5 sm:p-6 rounded-2xl border border-[#B6ADA3]/35 shadow-xs space-y-4">
                <h3 class="font-bold text-[#5F6F5B] text-base flex items-center gap-2">
                    <i class="fa-solid fa-chart-column text-[#D8A7B1]"></i> Perbandingan Planned vs Actual
                </h3>
                <div class="h-64 relative flex items-center justify-center">
                    <canvas id="budgetBarChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Budget Items Table -->
        <div class="bg-white rounded-2xl border border-[#B6ADA3]/35 shadow-xs overflow-hidden space-y-4 p-4">
            <div class="px-2 pt-2">
                <h3 class="font-bold text-[#5F6F5B] text-base sm:text-lg">Rincian Anggaran Kategori</h3>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr
                            class="bg-[#FAF7F2] text-xs text-[#5F6F5B] uppercase tracking-wider border-b border-[#B6ADA3]/30">
                            <th class="p-4">Kategori</th>
                            <th class="p-4">Nama Item</th>
                            <th class="p-4">Vendor</th>
                            <th class="p-4">Planned Budget</th>
                            <th class="p-4">Actual Cost</th>
                            <th class="p-4">Selisih / Remaining</th>
                            <th class="p-4">Catatan</th>
                            <th class="p-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#B6ADA3]/20 text-sm">
                        @forelse($budgets as $b)
                            @php
                                $diff = $b->planned_budget - $b->actual_cost;
                            @endphp
                            <tr class="hover:bg-[#FAF7F2]/60 transition-colors">
                                <td class="p-4 font-semibold text-[#5F6F5B] text-xs">
                                    <span class="bg-[#FAF7F2] px-2.5 py-1 rounded-lg border border-[#B6ADA3]/40">
                                        {{ $b->category }}
                                    </span>
                                </td>
                                <td class="p-4 font-medium text-[#5F6F5B]">{{ $b->item_name }}</td>
                                <td class="p-4 text-xs text-[#5F6F5B]/80">
                                    @if($b->vendor)
                                        <span class="font-medium">{{ $b->vendor->name }}</span>
                                    @else
                                        <span class="text-[#B6ADA3]">—</span>
                                    @endif
                                </td>
                                <td class="p-4 text-[#5F6F5B]/80">Rp {{ number_format($b->planned_budget, 0, ',', '.') }}
                                </td>
                                <td class="p-4 font-semibold text-[#5F6F5B]">Rp
                                    {{ number_format($b->actual_cost, 0, ',', '.') }}</td>
                                <td class="p-4 font-bold {{ $diff >= 0 ? 'text-[#5F6F5B]' : 'text-[#5F6F5B]' }}">
                                    Rp {{ number_format($diff, 0, ',', '.') }}
                                </td>
                                <td class="p-4 text-xs text-[#B6ADA3] max-w-xs truncate">{{ $b->notes ?? '—' }}</td>
                                <td class="p-4 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <button
                                            @click="modalOpen = true; editMode = true; currentItem = {{ json_encode($b) }}"
                                            title="Edit" class="p-2 text-[#B6ADA3] hover:text-[#5F6F5B] cursor-pointer">
                                            <i class="fa-solid fa-pen-to-square"></i>
                                        </button>
                                        <form id="del-budget-{{ $b->id }}"
                                            action="{{ route('budgets.destroy', $b->id) }}" method="POST">
                                            @csrf
                                            @method('DELETE')
                                            <button type="button" title="Hapus"
                                                onclick="confirmDelete('del-budget-{{ $b->id }}', '{{ addslashes($b->item_name) }}')"
                                                class="p-2 text-[#B6ADA3] hover:text-[#5F6F5B] cursor-pointer">
                                                <i class="fa-solid fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-10 text-[#B6ADA3] text-sm">Belum ada rincian
                                    anggaran.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Modal Form (Tambah / Edit Budget) -->
        <div x-show="modalOpen" x-transition.opacity
            class="fixed inset-0 z-50 bg-[#5F6F5B]/40 backdrop-blur-md flex items-center justify-center p-4">
            <div @click.away="modalOpen = false"
                class="bg-white w-full max-w-lg p-6 rounded-2xl border border-[#B6ADA3]/40 shadow-2xl space-y-4">
                <div class="flex items-center justify-between border-b border-[#B6ADA3]/30 pb-3">
                    <h3 class="text-lg font-bold font-serif-title text-[#5F6F5B]"
                        x-text="editMode ? 'Edit Item Anggaran' : 'Tambah Item Anggaran'"></h3>
                    <button @click="modalOpen = false" class="text-[#B6ADA3] hover:text-[#5F6F5B] cursor-pointer"><i
                            class="fa-solid fa-xmark"></i></button>
                </div>

                <form :action="editMode ? '/budgets/' + currentItem.id : '{{ route('budgets.store') }}'" method="POST"
                    class="space-y-4">
                    @csrf
                    <template x-if="editMode">
                        <input type="hidden" name="_method" value="PUT">
                    </template>
                    <input type="hidden" name="vendor_id" x-model="currentItem.vendor_id">

                    <div>
                        <label class="block text-xs font-semibold text-[#5F6F5B] mb-1">Kategori Budget *</label>
                        <select name="category" x-model="currentItem.category" @change="selectedVendorId = ''" required
                            class="w-full bg-[#FAF7F2] border border-[#B6ADA3]/40 text-sm text-[#5F6F5B] px-3 py-2 rounded-xl focus:border-[#D8A7B1] focus:outline-none">
                            <option value="">-- Pilih Kategori --</option>
                            @foreach ($categories as $cat)
                                <option value="{{ $cat }}">{{ $cat }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div x-show="availableVendors.length > 0" x-cloak>
                        <label class="block text-xs font-semibold text-[#5F6F5B] mb-1">Pilih Vendor Terkait</label>
                        <select x-model="selectedVendorId" @change="fillFromVendor()"
                            class="w-full bg-[#FAF7F2] border border-[#B6ADA3]/40 text-sm text-[#5F6F5B] px-3 py-2 rounded-xl focus:border-[#D8A7B1] focus:outline-none">
                            <option value="">-- Pilih Vendor --</option>
                            <template x-for="v in availableVendors" :key="v.id">
                                <option :value="v.id" x-text="v.name"></option>
                            </template>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-[#5F6F5B] mb-1">Nama Item / Pengeluaran *</label>
                        <input type="text" name="item_name" x-model="currentItem.item_name" required
                            class="w-full bg-[#FAF7F2] border border-[#B6ADA3]/40 text-sm text-[#5F6F5B] px-3 py-2 rounded-xl focus:border-[#D8A7B1] focus:outline-none">
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-[#5F6F5B] mb-1">Planned Budget (Rp) *</label>
                            <input type="number" name="planned_budget" x-model="currentItem.planned_budget" required
                                class="w-full bg-[#FAF7F2] border border-[#B6ADA3]/40 text-sm text-[#5F6F5B] px-3 py-2 rounded-xl focus:border-[#D8A7B1] focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-[#5F6F5B] mb-1">Actual Cost (Rp) *</label>
                            <input type="number" name="actual_cost" x-model="currentItem.actual_cost" required
                                class="w-full bg-[#FAF7F2] border border-[#B6ADA3]/40 text-sm text-[#5F6F5B] px-3 py-2 rounded-xl focus:border-[#D8A7B1] focus:outline-none">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-[#5F6F5B] mb-1">Catatan / Invoice Link</label>
                        <textarea name="notes" x-model="currentItem.notes" rows="2"
                            class="w-full bg-[#FAF7F2] border border-[#B6ADA3]/40 text-sm text-[#5F6F5B] px-3 py-2 rounded-xl focus:border-[#D8A7B1] focus:outline-none"></textarea>
                    </div>

                    <div class="flex justify-end gap-3 pt-3 border-t border-[#B6ADA3]/30">
                        <button type="button" @click="modalOpen = false"
                            class="px-4 py-2 text-xs text-[#B6ADA3] hover:text-[#5F6F5B] cursor-pointer">Batal</button>
                        <button type="submit" class="btn-primary-rose px-5 py-2 rounded-xl text-xs cursor-pointer">Simpan
                            Anggaran</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const chartData = @json($chartData);
            const labels = Object.keys(chartData);
            const plannedValues = labels.map(k => chartData[k].planned);
            const actualValues = labels.map(k => chartData[k].actual);

            // Pie Chart — Soft Romantic Palette (#D8A7B1, #A3B7A6, #5F6F5B, #B6ADA3)
            const pieCtx = document.getElementById('budgetPieChart').getContext('2d');
            new Chart(pieCtx, {
                type: 'doughnut',
                data: {
                    labels: labels,
                    datasets: [{
                        data: actualValues,
                        backgroundColor: [
                            '#D8A7B1', '#A3B7A6', '#5F6F5B', '#B6ADA3', '#C4919B',
                            '#8EA391', '#4D5C4A', '#D0C6BC', '#E2BDC4', '#788C75'
                        ],
                        borderWidth: 0
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'right',
                            labels: {
                                font: {
                                    size: 11
                                }
                            }
                        }
                    }
                }
            });

            // Bar Chart — Soft Romantic Palette
            const barCtx = document.getElementById('budgetBarChart').getContext('2d');
            new Chart(barCtx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                            label: 'Planned',
                            data: plannedValues,
                            backgroundColor: '#A3B7A6'
                        },
                        {
                            label: 'Actual',
                            data: actualValues,
                            backgroundColor: '#D8A7B1'
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        x: {
                            ticks: {
                                font: {
                                    size: 10
                                }
                            },
                            grid: {
                                display: false
                            }
                        },
                        y: {
                            ticks: {
                                font: {
                                    size: 10
                                }
                            },
                            grid: {
                                color: 'rgba(182, 173, 163, 0.2)'
                            }
                        }
                    }
                }
            });
        });
    </script>
@endpush
