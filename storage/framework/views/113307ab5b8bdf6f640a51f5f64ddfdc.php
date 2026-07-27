<?php $__env->startSection('title', 'Dashboard Overview'); ?>

<?php $__env->startSection('content'); ?>
    <div class="space-y-6 md:space-y-8">
        <!-- Header Banner -->
        <div
            class="relative overflow-hidden rounded-2xl bg-[#5F6F5B] text-white p-6 sm:p-8 shadow-md border border-[#A3B7A6]/30">
            <div class="absolute -right-10 -bottom-10 w-64 h-64 bg-white/10 rounded-full blur-3xl pointer-events-none"></div>
            <div class="relative z-10 flex flex-col md:flex-row items-start md:items-center justify-between gap-6">
                <div>
                    <div
                        class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/20 backdrop-blur text-white text-xs font-semibold mb-3 border border-white/30">
                        <i class="fa-solid fa-heart text-white" style="color: #D8A7B1 !important;"></i> Wedding Planner
                        Dashboard
                    </div>
                    <h1 class="text-2xl sm:text-3xl md:text-4xl font-bold font-serif-title text-white mb-2 leading-tight">
                        Weeding of <br> <span><?php echo e($wedding->groom_name); ?> & <?php echo e($wedding->bride_name); ?></span>!
                    </h1>
                </div>
                <!-- D-Day Countdown Card -->
                <div
                    class="bg-white/20 backdrop-blur-md px-5 py-4 rounded-2xl flex items-center gap-4 border border-white/30 shadow-sm shrink-0 w-full sm:w-auto">
                    <div
                        class="w-12 sm:w-14 h-12 sm:h-14 rounded-xl bg-white flex items-center justify-center font-bold text-xl sm:text-2xl shadow-xs">
                        <i class="fa-solid fa-calendar-days text-[#D8A7B1]" style="color: #D8A7B1 !important;"></i>
                    </div>
                    <div>
                        <div class="text-2xl sm:text-3xl font-extrabold text-white"><?php echo e($daysLeft); ?> Hari</div>
                        <div class="text-xs text-white/90 font-medium">Menuju Hari H Pernikahan</div>
                        <div class="text-[11px] text-white/80 mt-0.5"><i class="fa-solid fa-location-dot"></i>
                            <?php echo e($wedding->location); ?></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Widgets Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4 sm:gap-6">
            <!-- Progress Persiapan Widget -->
            <div class="bg-white p-5 sm:p-6 rounded-2xl flex flex-col justify-between border border-[#B6ADA3]/35 shadow-xs">
                <div class="flex items-center justify-between mb-4">
                    <div class="text-sm font-semibold text-[#5F6F5B] flex items-center gap-2">
                        <i class="fa-solid fa-tasks text-[#D8A7B1]"></i> Progress Persiapan
                    </div>
                    <span
                        class="text-xs font-bold px-2.5 py-1 rounded-full bg-[#A3B7A6]/20 text-[#5F6F5B] border border-[#A3B7A6]/40">
                        <?php echo e($progressPercent); ?>% Selesai
                    </span>
                </div>
                <div>
                    <div class="flex justify-between text-xs text-[#5F6F5B]/80 mb-2">
                        <span><?php echo e($completedChecklists); ?> checklist selesai</span>
                        <span>Total <?php echo e($totalChecklists); ?> checklist</span>
                    </div>
                    <div class="w-full h-3 bg-[#FAF7F2] rounded-full overflow-hidden p-0.5 border border-[#B6ADA3]/30">
                        <div class="h-full bg-gradient-to-r from-[#D8A7B1] to-[#A3B7A6] rounded-full transition-all duration-500"
                            style="width: <?php echo e($progressPercent); ?>%"></div>
                    </div>
                </div>
                <div
                    class="mt-4 pt-3 border-t border-[#B6ADA3]/25 flex items-center justify-between text-xs text-[#5F6F5B]">
                    <span>Belum selesai: <strong class="text-[#5F6F5B]"><?php echo e($uncompletedChecklists); ?></strong></span>
                    <a href="<?php echo e(route('checklists.index')); ?>"
                        class="text-[#5F6F5B] hover:text-[#5F6F5B] hover:underline flex items-center gap-1 font-semibold transition-colors">
                        Lihat Semua <i class="fa-solid fa-arrow-right text-[10px]"></i>
                    </a>
                </div>
            </div>

            <!-- Budget Summary Widget -->
            <div class="bg-white p-5 sm:p-6 rounded-2xl flex flex-col justify-between border border-[#B6ADA3]/35 shadow-xs">
                <div class="flex items-center justify-between mb-2">
                    <div class="text-sm font-semibold text-[#5F6F5B] flex items-center gap-2">
                        <i class="fa-solid fa-wallet text-[#D8A7B1]"></i> Ringkasan Budget
                    </div>
                    <span class="text-xs text-[#B6ADA3]">Target Rp <?php echo e(number_format($totalBudget, 0, ',', '.')); ?></span>
                </div>
                <div class="space-y-2 my-2">
                    <div class="flex justify-between items-baseline">
                        <span class="text-xs text-[#5F6F5B]/80">Terpakai:</span>
                        <span class="text-base font-bold text-[#5F6F5B]">Rp
                            <?php echo e(number_format($budgetTerpakai, 0, ',', '.')); ?></span>
                    </div>
                    <div class="flex justify-between items-baseline">
                        <span class="text-xs text-[#5F6F5B]/80">Sisa Anggaran:</span>
                        <span class="text-base font-bold text-[#5F6F5B]">Rp
                            <?php echo e(number_format($budgetSisa, 0, ',', '.')); ?></span>
                    </div>
                </div>
                <div class="pt-3 border-t border-[#B6ADA3]/25 flex items-center justify-between text-xs">
                    <span class="text-[#5F6F5B]/80">Pengeluaran:
                        <?php echo e($totalBudget > 0 ? round(($budgetTerpakai / $totalBudget) * 100) : 0); ?>%</span>
                    <a href="<?php echo e(route('budgets.index')); ?>"
                        class="text-[#5F6F5B] hover:text-[#5F6F5B] hover:underline flex items-center gap-1 font-semibold transition-colors">
                        Kelola Anggaran <i class="fa-solid fa-arrow-right text-[10px]"></i>
                    </a>
                </div>
            </div>

            <!-- Quick Statistics Widget -->
            <div
                class="bg-white p-5 sm:p-6 rounded-2xl flex flex-col justify-between border border-[#B6ADA3]/35 shadow-xs sm:col-span-2 md:col-span-1">
                <div class="text-sm font-semibold text-[#5F6F5B] flex items-center gap-2 mb-3">
                    <i class="fa-solid fa-chart-line text-[#D8A7B1]"></i> Statistik
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div class="bg-[#FAF7F2] p-3 rounded-xl border border-[#B6ADA3]/30">
                        <div class="text-xs text-[#B6ADA3]">Total Vendor</div>
                        <div class="text-lg font-bold text-[#5F6F5B]"><?php echo e($totalVendors); ?></div>
                        <div class="text-[10px] text-[#5F6F5B]/80 mt-1"><?php echo e($vendorsPaid); ?> Paid &bull;
                            <?php echo e($vendorsUnpaid); ?> Unpaid</div>
                    </div>
                    <div class="bg-[#FAF7F2] p-3 rounded-xl border border-[#B6ADA3]/30">
                        <div class="text-xs text-[#B6ADA3]">Total Tamu</div>
                        <div class="text-lg font-bold text-[#5F6F5B]"><?php echo e($totalGuests); ?> Pax</div>
                        <div class="text-[10px] text-[#5F6F5B]/80 mt-1">Undangan Dikonfirmasi</div>
                    </div>
                </div>
                <div class="mt-3 pt-3 border-t border-[#B6ADA3]/25 flex items-center justify-between text-xs">
                    <a href="<?php echo e(route('vendors.index')); ?>"
                        class="text-[#5F6F5B] hover:text-[#5F6F5B] hover:underline font-semibold transition-colors">Vendor
                        Manager &rarr;</a>
                    <a href="<?php echo e(route('guests.index')); ?>"
                        class="text-[#5F6F5B] hover:text-[#5F6F5B] hover:underline font-semibold transition-colors">Daftar
                        Tamu &rarr;</a>
                </div>
            </div>
        </div>

        <!-- Middle Content Grid -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Upcoming Checklists -->
            <div class="md:col-span-2 bg-white p-5 sm:p-6 rounded-2xl border border-[#B6ADA3]/35 shadow-xs space-y-4">
                <div class="flex items-center justify-between">
                    <h3 class="font-bold text-[#5F6F5B] text-lg flex items-center gap-2">
                        <i class="fa-solid fa-clock-rotate-left text-[#D8A7B1]"></i> Checklist Mendatang
                    </h3>
                    <a href="<?php echo e(route('checklists.index')); ?>"
                        class="text-xs text-[#5F6F5B] hover:text-[#5F6F5B] hover:underline font-semibold">Lihat Semua
                        Checklist</a>
                </div>

                <div class="space-y-3">
                    <?php $__empty_1 = true; $__currentLoopData = $upcomingChecklists; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $chk): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <div
                            class="flex items-center justify-between p-4 rounded-xl bg-[#FAF7F2] border border-[#B6ADA3]/30 hover:border-[#D8A7B1] transition-all">
                            <div class="flex items-center gap-3 sm:gap-4">
                                <form action="<?php echo e(route('checklists.toggle', $chk->id)); ?>" method="POST">
                                    <?php echo csrf_field(); ?>
                                    <button type="submit"
                                        class="w-6 h-6 rounded-md border border-[#B6ADA3] hover:border-[#5F6F5B] flex items-center justify-center text-transparent hover:text-[#5F6F5B] transition-colors cursor-pointer">
                                        <i class="fa-solid fa-check text-xs"></i>
                                    </button>
                                </form>
                                <div>
                                    <div class="font-semibold text-[#5F6F5B] text-sm"><?php echo e($chk->title); ?></div>
                                    <div class="flex flex-wrap items-center gap-2 sm:gap-3 text-xs text-[#5F6F5B]/80 mt-1">
                                        <span
                                            class="bg-white text-[#5F6F5B] px-2 py-0.5 rounded border border-[#B6ADA3]/40 font-medium text-[11px]"><?php echo e($chk->category); ?></span>
                                        <span><i class="fa-solid fa-calendar text-[10px]"></i>
                                            <?php echo e($chk->deadline ? $chk->deadline->format('d M Y') : 'Tanpa Tenggat'); ?></span>
                                    </div>
                                </div>
                            </div>
                            <span
                                class="px-2.5 py-1 rounded-full text-xs font-semibold shrink-0
                            <?php if($chk->priority == 'High'): ?> bg-[#D8A7B1]/25 text-[#5F6F5B] border border-[#D8A7B1]/40
                            <?php elseif($chk->priority == 'Medium'): ?> bg-[#A3B7A6]/25 text-[#5F6F5B] border border-[#A3B7A6]/40
                            <?php else: ?> bg-[#FAF7F2] text-[#5F6F5B] border border-[#B6ADA3]/40 <?php endif; ?>">
                                <?php echo e($chk->priority); ?>

                            </span>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <div class="text-center py-8 text-[#B6ADA3] text-sm">Semua checklist tugas penting sudah selesai! 🎉
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Quick Access Navigation Panel -->
            <div class="bg-white p-5 sm:p-6 rounded-2xl border border-[#B6ADA3]/35 shadow-xs space-y-4">
                <h3 class="font-bold text-[#5F6F5B] text-lg flex items-center gap-2">
                    <i class="fa-solid fa-compass text-[#D8A7B1]"></i> Pintasan
                </h3>
                <div class="grid grid-cols-1 gap-2.5 text-sm">
                    <a href="<?php echo e(route('moodboards.index')); ?>"
                        class="flex items-center justify-between p-3 rounded-xl bg-[#FAF7F2] border border-[#B6ADA3]/30 hover:border-[#D8A7B1] hover:bg-white transition-colors text-[#5F6F5B]">
                        <span class="flex items-center gap-3 font-medium"><i class="fa-solid fa-palette text-[#D8A7B1]"></i>
                            Moodboard Inspirasi</span>
                        <i class="fa-solid fa-chevron-right text-xs text-[#D8A7B1]"></i>
                    </a>
                    <a href="<?php echo e(route('rundowns.index')); ?>"
                        class="flex items-center justify-between p-3 rounded-xl bg-[#FAF7F2] border border-[#B6ADA3]/30 hover:border-[#D8A7B1] hover:bg-white transition-colors text-[#5F6F5B]">
                        <span class="flex items-center gap-3 font-medium"><i class="fa-solid fa-clock text-[#D8A7B1]"></i>
                            Rundown Acara</span>
                        <i class="fa-solid fa-chevron-right text-xs text-[#D8A7B1]"></i>
                    </a>
                    <a href="<?php echo e(route('contracts.index')); ?>"
                        class="flex items-center justify-between p-3 rounded-xl bg-[#FAF6F0] border border-[#B6ADA3]/30 hover:border-[#D8A7B1] hover:bg-white transition-colors text-[#5F6F5B]">
                        <span class="flex items-center gap-3 font-medium"><i
                                class="fa-solid fa-file-contract text-[#D8A7B1]"></i> Kontrak Vendor</span>
                        <i class="fa-solid fa-chevron-right text-xs text-[#D8A7B1]"></i>
                    </a>
                    <a href="<?php echo e(route('payments.index')); ?>"
                        class="flex items-center justify-between p-3 rounded-xl bg-[#FAF7F2] border border-[#B6ADA3]/30 hover:border-[#D8A7B1] hover:bg-white transition-colors text-[#5F6F5B]">
                        <span class="flex items-center gap-3 font-medium"><i
                                class="fa-solid fa-credit-card text-[#D8A7B1]"></i> Payment Tracker</span>
                        <i class="fa-solid fa-chevron-right text-xs text-[#D8A7B1]"></i>
                    </a>
                    <a href="<?php echo e(route('gifts.index')); ?>"
                        class="flex items-center justify-between p-3 rounded-xl bg-[#FAF7F2] border border-[#B6ADA3]/30 hover:border-[#D8A7B1] hover:bg-white transition-colors text-[#5F6F5B]">
                        <span class="flex items-center gap-3 font-medium"><i class="fa-solid fa-gift text-[#D8A7B1]"></i>
                            Gift & Angpao</span>
                        <i class="fa-solid fa-chevron-right text-xs text-[#D8A7B1]"></i>
                    </a>
                    <a href="<?php echo e(route('reports.index')); ?>"
                        class="flex items-center justify-between p-3 rounded-xl bg-[#FAF7F2] border border-[#B6ADA3]/30 hover:border-[#D8A7B1] hover:bg-white transition-colors text-[#5F6F5B]">
                        <span class="flex items-center gap-3 font-medium"><i
                                class="fa-solid fa-file-invoice-dollar text-[#D8A7B1]"></i> Laporan Akhir & Export</span>
                        <i class="fa-solid fa-chevron-right text-xs text-[#D8A7B1]"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Admin\Herd\wedding_planner\resources\views/dashboard.blade.php ENDPATH**/ ?>