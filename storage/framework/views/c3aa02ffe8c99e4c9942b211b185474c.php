<?php $__env->startSection('title', 'Perencanaan - Checklist'); ?>

<?php $__env->startSection('content'); ?>
    <div class="space-y-6" x-data="{ modalOpen: false, editMode: false, currentItem: {} }">
        <!-- Top Bar -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold font-serif-title">Checklist</h1>
            </div>
            <button @click="modalOpen = true; editMode = false; currentItem = {}"
                class="btn-primary-rose px-4 py-2.5 rounded-xl text-sm flex items-center justify-center gap-2 cursor-pointer w-full md:w-auto">
                <i class="fa-solid fa-plus"></i> Tambah Checklist
            </button>
        </div>

        <!-- Counters Grid -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-3 sm:gap-4">
            <div class="bg-white p-4 rounded-xl border border-[#B6ADA3]/35 shadow-xs flex items-center justify-between">
                <div>
                    <div class="text-xs text-[#5F6F5B]">Total Tugas</div>
                    <div class="text-xl font-bold text-[#5F6F5B]"><?php echo e($total); ?></div>
                </div>
                <i class="fa-solid fa-list-check text-[#5F6F5B] text-xl"></i>
            </div>
            <div class="bg-white p-4 rounded-xl border border-[#B6ADA3]/35 shadow-xs flex items-center justify-between">
                <div>
                    <div class="text-xs text-[#5F6F5B]">Selesai (Done)</div>
                    <div class="text-xl font-bold text-[#5F6F5B]"><?php echo e($doneCount); ?></div>
                </div>
                <i class="fa-solid fa-circle-check text-[#5F6F5B] text-xl"></i>
            </div>
            <div class="bg-white p-4 rounded-xl border border-[#B6ADA3]/35 shadow-xs flex items-center justify-between">
                <div>
                    <div class="text-xs text-[#5F6F5B]">Dalam Proses</div>
                    <div class="text-xl font-bold text-[#5F6F5B]"><?php echo e($progressCount); ?></div>
                </div>
                <i class="fa-solid fa-spinner text-[#D8A7B1] text-xl"></i>
            </div>
            <div class="bg-white p-4 rounded-xl border border-[#B6ADA3]/35 shadow-xs flex items-center justify-between">
                <div>
                    <div class="text-xs text-[#5F6F5B]">Belum Dimulai</div>
                    <div class="text-xl font-bold text-[#5F6F5B]"><?php echo e($todoCount); ?></div>
                </div>
                <i class="fa-solid fa-hourglass-start text-[#D8A7B1] text-xl"></i>
            </div>
        </div>

        <!-- Filter & Search Bar -->
        <form method="GET" action="<?php echo e(route('checklists.index')); ?>"
            class="bg-white p-4 rounded-xl border border-[#B6ADA3]/35 shadow-xs flex flex-col md:flex-row gap-3 items-center justify-between">
            <div class="w-full md:w-auto md:flex md:flex-row md:items-center md:gap-3 space-y-3 md:space-y-0">
                <input type="text" name="search" value="<?php echo e(request('search')); ?>" placeholder="Cari nama checklist..."
                    class="bg-[#FAF7F2] border border-[#B6ADA3]/40 text-xs text-[#5F6F5B] px-3 py-2.5 rounded-xl focus:border-[#D8A7B1] focus:outline-none w-full md:w-56">

                <div class="grid grid-cols-2 gap-3 md:flex md:items-center md:gap-3">
                    <select name="status" onchange="this.form.submit()"
                        class="bg-[#FAF7F2] border border-[#B6ADA3]/40 text-xs text-[#5F6F5B] px-3 py-2.5 rounded-xl focus:border-[#D8A7B1] focus:outline-none w-full md:w-auto">
                        <option value="">Semua Status</option>
                        <?php $__currentLoopData = $statuses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $st): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($st); ?>" <?php echo e(request('status') == $st ? 'selected' : ''); ?>>
                                <?php echo e($st); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>

                    <select name="priority" onchange="this.form.submit()"
                        class="bg-[#FAF7F2] border border-[#B6ADA3]/40 text-xs text-[#5F6F5B] px-3 py-2.5 rounded-xl focus:border-[#D8A7B1] focus:outline-none w-full md:w-auto">
                        <option value="">Semua Prioritas</option>
                        <?php $__currentLoopData = $priorities; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $pr): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($pr); ?>" <?php echo e(request('priority') == $pr ? 'selected' : ''); ?>>
                                <?php echo e($pr); ?> Priority</option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
            </div>

            <?php if(request()->anyFilled(['search', 'status', 'priority'])): ?>
                <a href="<?php echo e(route('checklists.index')); ?>"
                    class="text-xs text-[#5F6F5B] hover:text-[#5F6F5B] font-semibold hover:underline">Reset Filter</a>
            <?php endif; ?>
        </form>

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
                        <?php $__empty_1 = true; $__currentLoopData = $checklists; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $chk): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr class="hover:bg-[#FAF7F2]/60 transition-colors">
                                <td class="p-4 text-center">
                                    <form action="<?php echo e(route('checklists.toggle', $chk->id)); ?>" method="POST">
                                        <?php echo csrf_field(); ?>
                                        <button type="submit" class="cursor-pointer">
                                            <?php if($chk->status === 'Done'): ?>
                                                <i class="fa-solid fa-circle-check text-[#5F6F5B] text-lg"></i>
                                            <?php elseif($chk->status === 'Progress'): ?>
                                                <i class="fa-solid fa-spinner text-[#D8A7B1] text-lg"></i>
                                            <?php else: ?>
                                                <i
                                                    class="fa-regular fa-circle text-[#B6ADA3] text-lg hover:text-[#5F6F5B]"></i>
                                            <?php endif; ?>
                                        </button>
                                    </form>
                                </td>
                                <td
                                    class="p-4 font-semibold text-[#5F6F5B] <?php echo e($chk->status === 'Done' ? 'line-through text-[#B6ADA3]' : ''); ?>">
                                    <?php echo e($chk->title); ?>

                                </td>
                                <td class="p-4">
                                    <span
                                        class="text-xs bg-[#FAF7F2] text-[#5F6F5B] font-medium px-2.5 py-1 rounded-lg border border-[#B6ADA3]/40">
                                        <?php echo e($chk->category); ?>

                                    </span>
                                </td>
                                <td class="p-4 text-xs text-[#5F6F5B]">
                                    <?php echo e($chk->deadline ? \Carbon\Carbon::parse($chk->deadline)->format('d M Y') : '—'); ?>

                                </td>
                                <td class="p-4">
                                    <span
                                        class="text-xs font-semibold px-2.5 py-1 rounded-full
                                    <?php if($chk->priority === 'High'): ?> bg-[#D8A7B1]/30 text-[#5F6F5B] border border-[#A3B7A6]/40
                                    <?php elseif($chk->priority === 'Medium'): ?> bg-[#FAF7F2] text-[#5F6F5B] border border-[#A3B7A6]/40
                                    <?php else: ?> bg-[#A3B7A6]/20 text-[#5F6F5B] border border-[#A3B7A6]/40 <?php endif; ?>">
                                        <?php echo e($chk->priority); ?>

                                    </span>
                                </td>
                                <td class="p-4 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <button
                                            @click="modalOpen = true; editMode = true; currentItem = <?php echo e(json_encode($chk)); ?>"
                                            title="Edit"
                                            class="p-2 text-[#B6ADA3] hover:text-[#5F6F5B] cursor-pointer">
                                            <i class="fa-solid fa-pen-to-square"></i>
                                        </button>
                                        <form action="<?php echo e(route('checklists.duplicate', $chk->id)); ?>" method="POST">
                                            <?php echo csrf_field(); ?>
                                            <button type="submit"
                                                class="p-2 text-[#B6ADA3] hover:text-[#5F6F5B] cursor-pointer"
                                                title="Duplikasi">
                                                <i class="fa-solid fa-copy"></i>
                                            </button>
                                        </form>
                                        <form id="del-chk-<?php echo e($chk->id); ?>" action="<?php echo e(route('checklists.destroy', $chk->id)); ?>" method="POST">
                                            <?php echo csrf_field(); ?>
                                            <?php echo method_field('DELETE'); ?>
                                            <button type="button"
                                                title="Hapus"
                                                onclick="confirmDelete('del-chk-<?php echo e($chk->id); ?>', '<?php echo e(addslashes($chk->title)); ?>')"
                                                class="p-2 text-[#B6ADA3] hover:text-[#5F6F5B] cursor-pointer">
                                                <i class="fa-solid fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="6" class="text-center py-10 text-[#B6ADA3] text-sm">Belum ada item
                                    checklist.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Modal Form (Tambah / Edit Checklist) -->
        <div x-show="modalOpen" x-transition.opacity
            class="fixed inset-0 z-50 bg-[#5F6F5B]/40 backdrop-blur-md flex items-center justify-center p-4">
            <div @click.away="if (!$event.target.closest('.flatpickr-calendar')) modalOpen = false"
                class="bg-white w-full max-w-lg p-6 rounded-2xl border border-[#B6ADA3]/40 shadow-2xl space-y-4">
                <div class="flex items-center justify-between border-b border-[#B6ADA3]/30 pb-3">
                    <h3 class="text-lg font-bold font-serif-title text-[#5F6F5B]"
                        x-text="editMode ? 'Edit Item Checklist' : 'Tambah Item Checklist'"></h3>
                    <button @click="modalOpen = false" class="text-[#B6ADA3] hover:text-[#5F6F5B] cursor-pointer"><i
                            class="fa-solid fa-xmark"></i></button>
                </div>

                <form :action="editMode ? '/checklists/' + currentItem.id : '<?php echo e(route('checklists.store')); ?>'"
                    method="POST" class="space-y-4">
                    <?php echo csrf_field(); ?>
                    <template x-if="editMode">
                        <input type="hidden" name="_method" value="PUT">
                    </template>

                    <div>
                        <label class="block text-xs font-semibold text-[#5F6F5B] mb-1">Judul Checklist *</label>
                        <input type="text" name="title" x-model="currentItem.title" required
                            class="w-full bg-[#FAF7F2] border border-[#B6ADA3]/40 text-sm text-[#5F6F5B] px-3 py-2 rounded-xl focus:border-[#D8A7B1] focus:outline-none"
                            placeholder="Misal: Booking Catering">
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-[#5F6F5B] mb-1">Kategori *</label>
                            <select name="category" x-model="currentItem.category" required
                                class="w-full bg-[#FAF7F2] border border-[#B6ADA3]/40 text-sm text-[#5F6F5B] px-3 py-2 rounded-xl focus:border-[#D8A7B1] focus:outline-none">
                                <?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($cat); ?>"><?php echo e($cat); ?></option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-[#5F6F5B] mb-1">Prioritas *</label>
                            <select name="priority" x-model="currentItem.priority" required
                                class="w-full bg-[#FAF7F2] border border-[#B6ADA3]/40 text-sm text-[#5F6F5B] px-3 py-2 rounded-xl focus:border-[#D8A7B1] focus:outline-none">
                                <?php $__currentLoopData = $priorities; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $pr): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($pr); ?>"><?php echo e($pr); ?></option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-[#5F6F5B] mb-1">Tenggat Waktu</label>
                            <input type="text" name="deadline" x-model="currentItem.deadline"
                                class="datepicker w-full bg-[#FAF7F2] border border-[#B6ADA3]/40 text-sm text-[#5F6F5B] px-3 py-2 rounded-xl focus:border-[#D8A7B1] focus:outline-none">
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-[#5F6F5B] mb-1">Status *</label>
                            <select name="status" x-model="currentItem.status" required
                                class="w-full bg-[#FAF7F2] border border-[#B6ADA3]/40 text-sm text-[#5F6F5B] px-3 py-2 rounded-xl focus:border-[#D8A7B1] focus:outline-none">
                                <?php $__currentLoopData = $statuses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $st): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($st); ?>"><?php echo e($st); ?></option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-[#5F6F5B] mb-1">Deskripsi / Catatan</label>
                        <textarea name="description" x-model="currentItem.description" rows="3"
                            class="w-full bg-[#FAF7F2] border border-[#B6ADA3]/40 text-sm text-[#5F6F5B] px-3 py-2 rounded-xl focus:border-[#D8A7B1] focus:outline-none"></textarea>
                    </div>

                    <div class="flex justify-end gap-3 pt-3 border-t border-[#B6ADA3]/30">
                        <button type="button" @click="modalOpen = false"
                            class="px-4 py-2 text-xs text-[#B6ADA3] hover:text-[#5F6F5B] cursor-pointer">Batal</button>
                        <button type="submit" class="btn-primary-rose px-5 py-2 rounded-xl text-xs cursor-pointer">Simpan
                            Checklist</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Admin\Herd\wedding_planner\resources\views/checklists/index.blade.php ENDPATH**/ ?>