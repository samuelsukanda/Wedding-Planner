@extends('layouts.app')

@section('title', 'Laporan - Wedding Reports')

@section('content')
    {{-- Print Header: hidden on screen, shown only when printing --}}
    <div class="print-header" style="display:none;">
        <h1>Laporan Rekapitulasi Pernikahan &mdash; {{ $wedding?->couple_name }}</h1>
        <p>Dicetak pada: {{ now()->format('d F Y, H:i') }} WIB</p>
    </div>

    <div id="report-content" class="space-y-8" x-data="{}">
        <div class="no-print flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold font-serif-title">Laporan & Rekap Pernikahan</h1>
            </div>
            <div class="no-print grid grid-cols-2 gap-3 w-full md:w-auto sm:flex sm:items-center sm:gap-3">
                <button onclick="window.print()"
                    class="px-4 py-2.5 rounded-xl bg-white text-[#5F6F5B] hover:bg-[#FAF7F2] border border-[#B6ADA3]/40 text-sm flex items-center justify-center gap-2 shadow-xs font-semibold cursor-pointer w-full sm:w-auto">
                    <i class="fa-solid fa-print text-[#D8A7B1]"></i> Cetak Laporan
                </button>
                <a href="{{ route('reports.export') }}" data-turbo="false"
                    class="btn-primary-rose px-4 py-2.5 rounded-xl text-sm flex items-center justify-center gap-2 w-full sm:w-auto">
                    <i class="fa-solid fa-file-excel"></i> Export Excel
                </a>
            </div>
        </div>

        <!-- KPI Summary Row -->
        <div class="grid grid-cols-2 md:grid-cols-4 print:grid-cols-4 gap-3 sm:gap-4">
            <div class="bg-gradient-to-br from-[#A3B7A6] to-[#5F6F5B] p-5 rounded-2xl text-white shadow-md text-center">
                <div class="text-xs font-medium opacity-90 mb-1">Total Anggaran</div>
                <div class="text-lg sm:text-xl font-bold leading-tight">Rp {{ number_format($totalBudget, 0, ',', '.') }}
                </div>
                <div class="text-[11px] opacity-80 mt-1">Budget Direncanakan</div>
            </div>
            <div class="bg-gradient-to-br from-[#A3B7A6] to-[#5F6F5B] p-5 rounded-2xl text-white shadow-md text-center">
                <div class="text-xs font-medium opacity-90 mb-1">Total Terealisasi</div>
                <div class="text-lg sm:text-xl font-bold leading-tight">Rp {{ number_format($totalActual, 0, ',', '.') }}
                </div>
                <div class="text-[11px] opacity-80 mt-1">Budget Terpakai</div>
            </div>
            <div class="bg-white p-5 rounded-2xl border border-[#B6ADA3]/35 shadow-xs text-center">
                <div class="text-xs text-[#5F6F5B] mb-1">Sisa Anggaran</div>
                <div class="text-lg sm:text-xl font-bold text-[#5F6F5B]">
                    Rp {{ number_format($totalBudget - $totalActual, 0, ',', '.') }}
                </div>
                <div class="text-xs text-[#5F6F5B] mt-1">
                    {{ $totalBudget > 0 ? number_format((($totalBudget - $totalActual) / $totalBudget) * 100, 1) : 0 }}%
                    Efisiensi</div>
            </div>
            <div class="bg-white p-5 rounded-2xl border border-[#B6ADA3]/35 shadow-xs text-center">
                <div class="text-xs text-[#5F6F5B] mb-1">Jumlah Tamu Hadir</div>
                <div class="text-2xl sm:text-3xl font-bold">{{ $guestAttend }}</div>
                <div class="text-xs text-[#5F6F5B] mt-1 font-medium">dari {{ $totalGuests }} Undangan</div>
            </div>
        </div>

        <!-- 2-column report blocks -->
        <div class="grid grid-cols-1 md:grid-cols-2 print:grid-cols-2 gap-6">
            <!-- Checklist Progress -->
            <div class="bg-white p-5 sm:p-6 rounded-2xl border border-[#B6ADA3]/35 shadow-xs space-y-4">
                <h3 class="font-bold text-[#5F6F5B] flex items-center gap-2">
                    <i class="fa-solid fa-list-check text-[#D8A7B1]"></i> Progress Checklist
                </h3>
                <div>
                    <div class="flex justify-between text-xs text-[#5F6F5B] mb-2">
                        <span>{{ $checklistDone }} dari {{ $totalChecklists }} task selesai</span>
                        <span
                            class="font-bold text-[#5F6F5B]">{{ $totalChecklists > 0 ? round(($checklistDone / $totalChecklists) * 100) : 0 }}%</span>
                    </div>
                    <div class="h-3 w-full bg-[#FAF7F2] rounded-full overflow-hidden p-0.5 border border-[#B6ADA3]/30">
                        <div class="h-full rounded-full bg-gradient-to-r from-[#D8A7B1] to-[#A3B7A6] transition-all duration-700"
                            style="width: {{ $totalChecklists > 0 ? ($checklistDone / $totalChecklists) * 100 : 0 }}%">
                        </div>
                    </div>
                </div>
                <div class="grid grid-cols-3 gap-3 text-center">
                    <div class="p-3 rounded-xl bg-[#FAF7F2] border border-[#B6ADA3]/30">
                        <div class="text-lg font-bold text-[#5F6F5B]">{{ $totalChecklists }}</div>
                        <div class="text-xs text-[#5F6F5B]">Total Task</div>
                    </div>
                    <div class="p-3 rounded-xl bg-[#FAF7F2] border border-[#B6ADA3]/30">
                        <div class="text-lg font-bold text-[#5F6F5B]">{{ $checklistDone }}</div>
                        <div class="text-xs text-[#5F6F5B]">Selesai</div>
                    </div>
                    <div class="p-3 rounded-xl bg-[#FAF7F2] border border-[#B6ADA3]/30">
                        <div class="text-lg font-bold text-[#5F6F5B]">{{ $totalChecklists - $checklistDone }}</div>
                        <div class="text-xs text-[#5F6F5B]">Pending</div>
                    </div>
                </div>
            </div>

            <!-- Budget Breakdown by Category -->
            <div class="bg-white p-5 sm:p-6 rounded-2xl border border-[#B6ADA3]/35 shadow-xs space-y-4">
                <h3 class="font-bold text-[#5F6F5B] flex items-center gap-2">
                    <i class="fa-solid fa-chart-pie text-[#D8A7B1]"></i> Rekap Anggaran Per Kategori
                </h3>
                <div class="space-y-3">
                    @forelse($budgetByCategory as $cat)
                        <div>
                            <div class="flex justify-between text-xs font-medium text-[#5F6F5B] mb-1.5">
                                <span>{{ $cat->category }}</span>
                                <span class="text-[#5F6F5B] font-bold">Rp
                                    {{ number_format($cat->total_actual, 0, ',', '.') }}</span>
                            </div>
                            <div class="h-2 w-full bg-[#FAF7F2] rounded-full overflow-hidden">
                                <div class="h-full rounded-full bg-gradient-to-r from-[#D8A7B1] to-[#A3B7A6] transition-all duration-700"
                                    style="width: {{ $totalActual > 0 ? ($cat->total_actual / $totalActual) * 100 : 0 }}%">
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-xs text-[#B6ADA3] text-center py-4">Belum ada data anggaran.</div>
                    @endforelse
                </div>
            </div>

            <!-- Vendor Summary -->
            <div class="bg-white p-5 sm:p-6 rounded-2xl border border-[#B6ADA3]/35 shadow-xs space-y-4">
                <h3 class="font-bold text-[#5F6F5B] flex items-center gap-2">
                    <i class="fa-solid fa-handshake text-[#D8A7B1]"></i> Rekap Status Vendor
                </h3>
                <div class="grid grid-cols-3 gap-3">
                    @foreach ($vendorStatusSummary as $status => $count)
                        <div class="p-3 sm:p-4 rounded-xl border text-center bg-[#FAF7F2] border-[#B6ADA3]/30">
                            <div class="text-xl sm:text-2xl font-bold text-[#5F6F5B]">
                                {{ $count }}
                            </div>
                            <div class="text-xs text-[#5F6F5B] font-medium">{{ $status }}</div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Guest Summary -->
            <div class="bg-white p-5 sm:p-6 rounded-2xl border border-[#B6ADA3]/35 shadow-xs space-y-4">
                <h3 class="font-bold text-[#5F6F5B] flex items-center gap-2">
                    <i class="fa-solid fa-users text-[#D8A7B1]"></i> Rekap Status Tamu
                </h3>
                <div class="grid grid-cols-3 gap-3">
                    <div class="p-3 sm:p-4 rounded-xl bg-[#FAF7F2] border border-[#B6ADA3]/30 text-center">
                        <div class="text-xl sm:text-2xl font-bold text-[#5F6F5B]">{{ $guestAttend }}</div>
                        <div class="text-xs text-[#5F6F5B] mt-1">Hadir (Attend)</div>
                    </div>
                    <div class="p-3 sm:p-4 rounded-xl bg-[#FAF7F2] border border-[#B6ADA3]/30 text-center">
                        <div class="text-xl sm:text-2xl font-bold text-[#5F6F5B]">{{ $guestPending }}</div>
                        <div class="text-xs text-[#5F6F5B] mt-1">Pending</div>
                    </div>
                    <div class="p-3 sm:p-4 rounded-xl bg-[#FAF7F2] border border-[#B6ADA3]/30 text-center">
                        <div class="text-xl sm:text-2xl font-bold text-[#5F6F5B]">{{ $guestDecline }}</div>
                        <div class="text-xs text-[#5F6F5B] mt-1">Decline</div>
                    </div>
                </div>
                <div class="text-xs text-center text-[#5F6F5B]/80 pt-2">
                    Total Pax tamu hadir: <strong class="text-[#5F6F5B]">{{ $totalPax }} orang</strong>
                </div>
            </div>
        </div>

        <!-- Payment Summary -->
        <div class="bg-white p-5 sm:p-6 rounded-2xl border border-[#B6ADA3]/35 shadow-xs">
            <h3 class="font-bold text-[#5F6F5B] flex items-center gap-2 mb-4">
                <i class="fa-solid fa-money-bill-transfer text-[#D8A7B1]"></i> Rekap Total Pembayaran Vendor Tercatat
            </h3>
            <div class="grid grid-cols-2 md:grid-cols-4 print:grid-cols-4 gap-3 sm:gap-4">
                @foreach ($paymentStatusSummary as $status => $total)
                    <div class="p-4 rounded-xl bg-[#FAF7F2] border border-[#B6ADA3]/30 text-center">
                        <div class="text-sm sm:text-base font-bold text-[#5F6F5B]">Rp
                            {{ number_format($total, 0, ',', '.') }}</div>
                        <div class="text-xs text-[#5F6F5B] mt-1">{{ $status }}</div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Tambahan modul: Tabungan, Lamaran, Seserahan, Persyaratan -->
        <div class="grid grid-cols-1 md:grid-cols-2 print:grid-cols-2 gap-6">
            <!-- Tabungan -->
            <div class="bg-white p-5 sm:p-6 rounded-2xl border border-[#B6ADA3]/35 shadow-xs space-y-4">
                <h3 class="font-bold text-[#5F6F5B] flex items-center gap-2">
                    <i class="fa-solid fa-piggy-bank text-[#D8A7B1]"></i> Rekap Tabungan
                </h3>
                <div>
                    <div class="flex justify-between text-xs text-[#5F6F5B] mb-2">
                        <span>Terkumpul Rp {{ number_format($savingsBalance, 0, ',', '.') }}</span>
                        <span
                            class="font-bold text-[#5F6F5B]">{{ $savingsTarget > 0 ? round(($savingsBalance / $savingsTarget) * 100) : 0 }}%</span>
                    </div>
                    <div class="h-3 w-full bg-[#FAF7F2] rounded-full overflow-hidden p-0.5 border border-[#B6ADA3]/30">
                        <div class="h-full rounded-full bg-gradient-to-r from-[#D8A7B1] to-[#A3B7A6] transition-all duration-700"
                            style="width: {{ $savingsTarget > 0 ? min(100, ($savingsBalance / $savingsTarget) * 100) : 0 }}%">
                        </div>
                    </div>
                </div>
                <div class="grid grid-cols-3 gap-3 text-center">
                    <div class="p-3 rounded-xl bg-[#FAF7F2] border border-[#B6ADA3]/30">
                        <div class="text-sm font-bold text-[#5F6F5B]">Rp
                            {{ number_format($savingsTarget, 0, ',', '.') }}</div>
                        <div class="text-xs text-[#5F6F5B]">Target</div>
                    </div>
                    <div class="p-3 rounded-xl bg-[#FAF7F2] border border-[#B6ADA3]/30">
                        <div class="text-sm font-bold text-[#5F6F5B]">Rp
                            {{ number_format($savingsBalance, 0, ',', '.') }}</div>
                        <div class="text-xs text-[#5F6F5B]">Terkumpul</div>
                    </div>
                    <div class="p-3 rounded-xl bg-[#FAF7F2] border border-[#B6ADA3]/30">
                        <div class="text-sm font-bold text-[#5F6F5B]">Rp
                            {{ number_format($savingsShortfall, 0, ',', '.') }}</div>
                        <div class="text-xs text-[#5F6F5B]">Kekurangan</div>
                    </div>
                </div>
            </div>

            <!-- Lamaran -->
            <div class="bg-white p-5 sm:p-6 rounded-2xl border border-[#B6ADA3]/35 shadow-xs space-y-4">
                <h3 class="font-bold text-[#5F6F5B] flex items-center gap-2">
                    <i class="fa-solid fa-ring text-[#D8A7B1]"></i> Rekap Lamaran
                </h3>
                @if ($proposalEvent)
                    <div class="text-xs text-[#5F6F5B]">
                        {{ $proposalEvent->event_date ? $proposalEvent->event_date->format('d M Y') : 'Tanggal belum ditentukan' }}
                        @if ($proposalEvent->location)
                            &bull; {{ $proposalEvent->location }}
                        @endif
                        @if ($proposalEvent->theme)
                            &bull; {{ $proposalEvent->theme }}
                        @endif
                    </div>
                @else
                    <div class="text-xs text-[#B6ADA3]">Belum ada data acara lamaran.</div>
                @endif
                <div class="grid grid-cols-3 gap-3 text-center">
                    <div class="p-3 rounded-xl bg-[#FAF7F2] border border-[#B6ADA3]/30">
                        <div class="text-lg font-bold text-[#5F6F5B]">{{ $proposalChecklistDone }}/{{ $proposalChecklistTotal }}</div>
                        <div class="text-xs text-[#5F6F5B]">Checklist</div>
                    </div>
                    <div class="p-3 rounded-xl bg-[#FAF7F2] border border-[#B6ADA3]/30">
                        <div class="text-lg font-bold text-[#5F6F5B]">{{ $proposalGuestPax }}</div>
                        <div class="text-xs text-[#5F6F5B]">Pax ({{ $proposalGuestRows }} undangan)</div>
                    </div>
                    <div class="p-3 rounded-xl bg-[#FAF7F2] border border-[#B6ADA3]/30">
                        <div class="text-sm font-bold text-[#5F6F5B]">Rp
                            {{ number_format($proposalBudgetActual, 0, ',', '.') }}</div>
                        <div class="text-xs text-[#5F6F5B]">Terpakai / Rp
                            {{ number_format($proposalBudgetPlanned, 0, ',', '.') }}</div>
                    </div>
                </div>
            </div>

            <!-- Seserahan Progress -->
            <div class="bg-white p-5 sm:p-6 rounded-2xl border border-[#B6ADA3]/35 shadow-xs space-y-4">
                <h3 class="font-bold text-[#5F6F5B] flex items-center gap-2">
                    <i class="fa-solid fa-gift text-[#D8A7B1]"></i> Progress Seserahan
                </h3>
                <div>
                    <div class="flex justify-between text-xs text-[#5F6F5B] mb-2">
                        <span>{{ $souvenirReceived }} dari {{ $souvenirTotal }} item diterima</span>
                        <span
                            class="font-bold text-[#5F6F5B]">{{ $souvenirTotal > 0 ? round(($souvenirReceived / $souvenirTotal) * 100) : 0 }}%</span>
                    </div>
                    <div class="h-3 w-full bg-[#FAF7F2] rounded-full overflow-hidden p-0.5 border border-[#B6ADA3]/30">
                        <div class="h-full rounded-full bg-gradient-to-r from-[#D8A7B1] to-[#A3B7A6] transition-all duration-700"
                            style="width: {{ $souvenirTotal > 0 ? ($souvenirReceived / $souvenirTotal) * 100 : 0 }}%">
                        </div>
                    </div>
                </div>
                <div class="grid grid-cols-3 gap-3 text-center">
                    <div class="p-3 rounded-xl bg-[#FAF7F2] border border-[#B6ADA3]/30">
                        <div class="text-lg font-bold text-[#5F6F5B]">{{ $souvenirTotal }}</div>
                        <div class="text-xs text-[#5F6F5B]">Total Item</div>
                    </div>
                    <div class="p-3 rounded-xl bg-[#FAF7F2] border border-[#B6ADA3]/30">
                        <div class="text-lg font-bold text-[#5F6F5B]">{{ $souvenirReceived }}</div>
                        <div class="text-xs text-[#5F6F5B]">Diterima</div>
                    </div>
                    <div class="p-3 rounded-xl bg-[#FAF7F2] border border-[#B6ADA3]/30">
                        <div class="text-sm font-bold text-[#5F6F5B]">Rp
                            {{ number_format($souvenirActual, 0, ',', '.') }}</div>
                        <div class="text-xs text-[#5F6F5B]">Nilai Diterima</div>
                    </div>
                </div>
            </div>

            <!-- Persyaratan Nikah Progress -->
            <div class="bg-white p-5 sm:p-6 rounded-2xl border border-[#B6ADA3]/35 shadow-xs space-y-4">
                <h3 class="font-bold text-[#5F6F5B] flex items-center gap-2">
                    <i class="fa-solid fa-file-circle-check text-[#D8A7B1]"></i> Progress Persyaratan Nikah
                </h3>
                <div>
                    <div class="flex justify-between text-xs text-[#5F6F5B] mb-2">
                        <span>{{ $documentComplete }} dari {{ $documentTotal }} dokumen lengkap</span>
                        <span
                            class="font-bold text-[#5F6F5B]">{{ $documentTotal > 0 ? round(($documentComplete / $documentTotal) * 100) : 0 }}%</span>
                    </div>
                    <div class="h-3 w-full bg-[#FAF7F2] rounded-full overflow-hidden p-0.5 border border-[#B6ADA3]/30">
                        <div class="h-full rounded-full bg-gradient-to-r from-[#D8A7B1] to-[#A3B7A6] transition-all duration-700"
                            style="width: {{ $documentTotal > 0 ? ($documentComplete / $documentTotal) * 100 : 0 }}%">
                        </div>
                    </div>
                </div>
                <div class="grid grid-cols-3 gap-3 text-center">
                    <div class="p-3 rounded-xl bg-[#FAF7F2] border border-[#B6ADA3]/30">
                        <div class="text-lg font-bold text-[#5F6F5B]">{{ $documentTotal }}</div>
                        <div class="text-xs text-[#5F6F5B]">Total Dokumen</div>
                    </div>
                    <div class="p-3 rounded-xl bg-[#FAF7F2] border border-[#B6ADA3]/30">
                        <div class="text-lg font-bold text-[#5F6F5B]">{{ $documentComplete }}</div>
                        <div class="text-xs text-[#5F6F5B]">Lengkap</div>
                    </div>
                    <div class="p-3 rounded-xl bg-[#FAF7F2] border border-[#B6ADA3]/30">
                        <div class="text-lg font-bold text-[#5F6F5B]">{{ $documentTotal - $documentComplete }}</div>
                        <div class="text-xs text-[#5F6F5B]">Belum Lengkap</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
