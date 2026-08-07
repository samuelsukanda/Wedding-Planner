<?php $__env->startSection('title', 'Kirim WA Semua Tamu'); ?>

<?php $__env->startSection('content'); ?>
    <div class="space-y-6">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold font-serif-title">Kirim Undangan WA</h1>
                <p class="text-xs text-[#5F6F5B]/70 mt-1"><?php echo e($links->count()); ?> tamu dengan nomor telepon</p>
            </div>
            <a href="<?php echo e(route('guests.index')); ?>"
                class="px-4 py-2.5 rounded-xl bg-white text-[#5F6F5B] hover:bg-[#FAF7F2] border border-[#B6ADA3]/40 text-xs flex items-center justify-center gap-1.5 shadow-xs font-semibold w-full md:w-auto">
                <i class="fa-solid fa-arrow-left"></i> Kembali
            </a>
        </div>

        <div class="bg-white rounded-2xl border border-[#B6ADA3]/35 shadow-xs overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr
                            class="bg-[#FAF7F2] text-xs text-[#5F6F5B] uppercase tracking-wider border-b border-[#B6ADA3]/30">
                            <th class="p-4 w-12">No</th>
                            <th class="p-4">Nama Tamu</th>
                            <th class="p-4">No. Telepon</th>
                            <th class="p-4">Status Kirim</th>
                            <th class="p-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#B6ADA3]/20 text-sm">
                        <?php $__empty_1 = true; $__currentLoopData = $links; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr class="hover:bg-[#FAF7F2]/60 transition-colors">
                                <td class="p-4 text-[#B6ADA3]"><?php echo e($i + 1); ?></td>
                                <td class="p-4 font-semibold text-[#5F6F5B]"><?php echo e($item->nama); ?></td>
                                <td class="p-4 text-xs text-[#5F6F5B]"><?php echo e($item->phone_display); ?></td>
                                <td class="p-4">
                                    <span
                                        class="px-2.5 py-1 rounded-full text-xs font-semibold
                                    <?php if($item->wa_sent): ?> bg-[#A3B7A6]/25 text-[#5F6F5B] border border-[#A3B7A6]/40
                                    <?php else: ?> bg-[#FAF7F2] text-[#5F6F5B] border border-[#B6ADA3]/40 <?php endif; ?>">
                                        <?php echo e($item->wa_sent ? 'Terkirim' : 'Belum'); ?>

                                    </span>
                                </td>
                                <td class="p-4 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="<?php echo e($item->url); ?>" target="_blank" title="Kirim WA"
                                            class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl bg-white text-[#5F6F5B] hover:bg-green-50 border border-[#B6ADA3]/40 text-xs shadow-xs font-semibold transition-colors">
                                            <i class="fa-brands fa-whatsapp text-green-600"></i> Kirim WA
                                        </a>
                                        <form action="<?php echo e(route('guests.wa-sent', $item->id)); ?>" method="POST">
                                            <?php echo csrf_field(); ?>
                                            <button type="submit"
                                                title="<?php echo e($item->wa_sent ? 'Tandai belum terkirim' : 'Tandai terkirim'); ?>"
                                                class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl text-xs shadow-xs font-semibold transition-colors cursor-pointer
                                            <?php if($item->wa_sent): ?> bg-white text-[#5F6F5B] hover:bg-[#FAF7F2] border border-[#B6ADA3]/40
                                            <?php else: ?> bg-[#5F6F5B] text-white hover:bg-[#4A5B47] <?php endif; ?>">
                                                <i class="fa-solid <?php echo e($item->wa_sent ? 'fa-circle-check' : 'fa-circle'); ?>"></i>
                                                <?php echo e($item->wa_sent ? 'Belum' : 'Terkirim'); ?>

                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="5" class="text-center py-10 text-[#B6ADA3] text-sm">
                                    Tidak ada tamu dengan nomor telepon.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Admin\Herd\wedding_planner\resources\views/guests/wa-all.blade.php ENDPATH**/ ?>