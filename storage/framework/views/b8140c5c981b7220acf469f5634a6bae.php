<?php $__env->startSection('title', 'Keuangan & Dokumen - Vendor Contracts'); ?>

<?php $__env->startSection('content'); ?>
    <div class="space-y-6" x-data="{ modalOpen: false, editMode: false, currentItem: {} }">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold font-serif-title">Vendor Contract Management</h1>
            </div>
            <button @click="modalOpen = true; editMode = false; currentItem = {}"
                class="btn-primary-rose px-4 py-2.5 rounded-xl text-sm flex items-center justify-center gap-2 cursor-pointer w-full md:w-auto">
                <i class="fa-solid fa-plus"></i> Tambah Kontrak Baru
            </button>
        </div>

        <!-- Contracts Table -->
        <div class="bg-white rounded-2xl border border-[#B6ADA3]/35 shadow-xs overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr
                            class="bg-[#FAF7F2] text-xs text-[#5F6F5B] uppercase tracking-wider border-b border-[#B6ADA3]/30">
                            <th class="p-4">No. Kontrak</th>
                            <th class="p-4">Nama Vendor</th>
                            <th class="p-4">Nominal Kontrak</th>
                            <th class="p-4">Uang Muka (DP)</th>
                            <th class="p-4">Pelunasan</th>
                            <th class="p-4">Jatuh Tempo</th>
                            <th class="p-4">Status Kontrak</th>
                            <th class="p-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#B6ADA3]/20 text-sm">
                        <?php $__empty_1 = true; $__currentLoopData = $contracts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $c): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr class="hover:bg-[#FAF7F2]/60 transition-colors">
                                <td class="p-4 font-mono text-xs font-bold text-[#5F6F5B]"><?php echo e($c->contract_number); ?></td>
                                <td class="p-4 font-semibold text-[#5F6F5B]">
                                    <?php echo e($c->vendor->name ?? 'Vendor #' . $c->vendor_id); ?></td>
                                <td class="p-4 font-bold text-[#5F6F5B]">Rp <?php echo e(number_format($c->nominal, 0, ',', '.')); ?>

                                </td>
                                <td class="p-4 text-xs text-[#5F6F5B]">Rp <?php echo e(number_format($c->dp_amount, 0, ',', '.')); ?>

                                </td>
                                <td class="p-4 text-xs text-[#5F6F5B]/80">Rp
                                    <?php echo e(number_format($c->final_amount, 0, ',', '.')); ?></td>
                                <td class="p-4 text-xs text-[#5F6F5B]/80">
                                    <i
                                        class="fa-solid fa-calendar mr-1"></i><?php echo e($c->due_date ? $c->due_date->format('d M Y') : '—'); ?>

                                </td>
                                <td class="p-4">
                                    <span
                                        class="px-2.5 py-1 rounded-full text-xs font-semibold
                                    <?php if($c->status == 'Completed' || $c->status == 'Signed'): ?> bg-[#A3B7A6]/25 text-[#5F6F5B] border border-[#A3B7A6]/40
                                    <?php elseif($c->status == 'Waiting'): ?> bg-[#D8A7B1]/25 text-[#5F6F5B] border border-[#D8A7B1]/40
                                    <?php else: ?> bg-[#FAF7F2] text-[#5F6F5B] border border-[#B6ADA3]/40 <?php endif; ?>">
                                        <?php echo e($c->status); ?>

                                    </span>
                                </td>
                                <td class="p-4 text-right">
                                     <div class="flex items-center justify-end gap-2">
                                         <button
                                             @click="modalOpen = true; editMode = true; currentItem = <?php echo e(json_encode($c)); ?>"
                                             title="Edit"
                                             class="p-2 text-[#B6ADA3] hover:text-[#5F6F5B] cursor-pointer"><i
                                                 class="fa-solid fa-pen-to-square"></i></button>
                                         <form id="del-contract-<?php echo e($c->id); ?>" action="<?php echo e(route('contracts.destroy', $c->id)); ?>" method="POST">
                                             <?php echo csrf_field(); ?>
                                             <?php echo method_field('DELETE'); ?>
                                             <button type="button"
                                                 title="Hapus"
                                                 onclick="confirmDelete('del-contract-<?php echo e($c->id); ?>', '<?php echo e(addslashes($c->contract_name ?? $c->vendor_name)); ?>')"
                                                 class="p-2 text-[#B6ADA3] hover:text-[#5F6F5B] cursor-pointer"><i
                                                     class="fa-solid fa-trash"></i></button>
                                         </form>
                                     </div>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="8" class="text-center py-10 text-[#B6ADA3] text-sm">Belum ada kontrak vendor
                                    tersimpan.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Modal Form -->
        <div x-show="modalOpen" x-transition.opacity
            class="fixed inset-0 z-50 bg-[#5F6F5B]/40 backdrop-blur-md flex items-center justify-center p-4">
            <div @click.away="modalOpen = false"
                class="bg-white w-full max-w-lg p-6 rounded-2xl border border-[#B6ADA3]/40 shadow-2xl space-y-4">
                <div class="flex items-center justify-between border-b border-[#B6ADA3]/30 pb-3">
                    <h3 class="text-lg font-bold font-serif-title text-[#5F6F5B]"
                        x-text="editMode ? 'Edit Kontrak Vendor' : 'Tambah Kontrak Vendor Baru'"></h3>
                    <button @click="modalOpen = false" class="text-[#B6ADA3] hover:text-[#5F6F5B] cursor-pointer"><i
                            class="fa-solid fa-xmark"></i></button>
                </div>
                <form :action="editMode ? '/contracts/' + currentItem.id : '<?php echo e(route('contracts.store')); ?>'" method="POST"
                    class="space-y-4">
                    <?php echo csrf_field(); ?>
                    <template x-if="editMode"><input type="hidden" name="_method" value="PUT"></template>
                    <div>
                        <label class="block text-xs font-semibold text-[#5F6F5B] mb-1">Vendor *</label>
                        <select name="vendor_id" x-model="currentItem.vendor_id" required
                            class="w-full bg-[#FAF7F2] border border-[#B6ADA3]/40 text-sm text-[#5F6F5B] px-3 py-2 rounded-xl focus:border-[#D8A7B1] focus:outline-none">
                            <option value="">Pilih Vendor</option>
                            <?php $__currentLoopData = $vendors; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $v): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($v->id); ?>"><?php echo e($v->name); ?> (<?php echo e($v->category); ?>)</option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-[#5F6F5B] mb-1">Nomor Kontrak *</label>
                            <input type="text" name="contract_number" x-model="currentItem.contract_number" required
                                class="w-full bg-[#FAF7F2] border border-[#B6ADA3]/40 text-sm text-[#5F6F5B] px-3 py-2 rounded-xl focus:border-[#D8A7B1] focus:outline-none"
                                placeholder="CTR/SA/2026/001">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-[#5F6F5B] mb-1">Status Kontrak *</label>
                            <select name="status" x-model="currentItem.status" required
                                class="w-full bg-[#FAF7F2] border border-[#B6ADA3]/40 text-sm text-[#5F6F5B] px-3 py-2 rounded-xl focus:border-[#D8A7B1] focus:outline-none">
                                <?php $__currentLoopData = $statuses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $st): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($st); ?>"><?php echo e($st); ?></option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </div>
                    </div>
                    <div class="grid grid-cols-3 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-[#5F6F5B] mb-1">Nominal Total (Rp) *</label>
                            <input type="number" name="nominal" x-model="currentItem.nominal" required
                                class="w-full bg-[#FAF7F2] border border-[#B6ADA3]/40 text-sm text-[#5F6F5B] px-3 py-2 rounded-xl focus:border-[#D8A7B1] focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-[#5F6F5B] mb-1">DP (Rp) *</label>
                            <input type="number" name="dp_amount" x-model="currentItem.dp_amount" required
                                class="w-full bg-[#FAF7F2] border border-[#B6ADA3]/40 text-sm text-[#5F6F5B] px-3 py-2 rounded-xl focus:border-[#D8A7B1] focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-[#5F6F5B] mb-1">Pelunasan (Rp) *</label>
                            <input type="number" name="final_amount" x-model="currentItem.final_amount" required
                                class="w-full bg-[#FAF7F2] border border-[#B6ADA3]/40 text-sm text-[#5F6F5B] px-3 py-2 rounded-xl focus:border-[#D8A7B1] focus:outline-none">
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-[#5F6F5B] mb-1">Tanggal Jatuh Tempo</label>
                        <input type="date" name="due_date" x-model="currentItem.due_date"
                            class="w-full bg-[#FAF7F2] border border-[#B6ADA3]/40 text-sm text-[#5F6F5B] px-3 py-2 rounded-xl focus:border-[#D8A7B1] focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-[#5F6F5B] mb-1">Catatan Perjanjian</label>
                        <textarea name="notes" x-model="currentItem.notes" rows="2"
                            class="w-full bg-[#FAF7F2] border border-[#B6ADA3]/40 text-sm text-[#5F6F5B] px-3 py-2 rounded-xl focus:border-[#D8A7B1] focus:outline-none"></textarea>
                    </div>
                    <div class="flex justify-end gap-3 pt-3 border-t border-[#B6ADA3]/30">
                        <button type="button" @click="modalOpen = false"
                            class="px-4 py-2 text-xs text-[#B6ADA3] hover:text-[#5F6F5B] cursor-pointer">Batal</button>
                        <button type="submit" class="btn-primary-rose px-5 py-2 rounded-xl text-xs cursor-pointer">Simpan
                            Kontrak</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Admin\Herd\wedding_planner\resources\views/contracts/index.blade.php ENDPATH**/ ?>