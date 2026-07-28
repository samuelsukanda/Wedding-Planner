<?php $__env->startSection('title', 'Pengaturan - Admin Panel'); ?>

<?php $__env->startSection('content'); ?>
    <div class="space-y-6" x-data="{
        activeTab: '<?php echo e(request('tab', $activeTab ?? 'wedding')); ?>',
        modalOpen: false,
        editMode: false,
        currentItem: {}
    }">
        <!-- Page Title & Header -->
        <div
            class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-white p-5 rounded-2xl border border-[#B6ADA3]/35 shadow-xs">
            <div>
                <h1 class="text-xl md:text-2xl font-bold font-serif-title text-[#5F6F5B]">Admin Panel & Pengaturan Master
                </h1>
            </div>
        </div>

        <!-- Main Tab Bar (Full Width & High Contrast) -->
        <div class="bg-white p-1.5 rounded-2xl border border-[#B6ADA3]/35 shadow-xs flex items-center gap-2 overflow-x-auto">
            <button @click="activeTab = 'wedding'"
                :class="activeTab === 'wedding' ? 'bg-[#5F6F5B] text-white font-bold shadow-xs' :
                    'text-[#5F6F5B] hover:bg-[#FAF7F2] font-semibold'"
                class="flex-1 min-w-[180px] py-3 px-4 rounded-xl text-xs sm:text-sm transition-all flex items-center justify-center gap-2 cursor-pointer">
                <i class="fa-solid fa-heart text-[#D8A7B1]"></i>
                <span>Informasi Acara Pernikahan</span>
            </button>
            <button @click="activeTab = 'dropdowns'"
                :class="activeTab === 'dropdowns' ? 'bg-[#5F6F5B] text-white font-bold shadow-xs' :
                    'text-[#5F6F5B] hover:bg-[#FAF7F2] font-semibold'"
                class="flex-1 min-w-[180px] py-3 px-4 rounded-xl text-xs sm:text-sm transition-all flex items-center justify-center gap-2 cursor-pointer">
                <i class="fa-solid fa-sliders text-[#D8A7B1]"></i>
                <span>Kelola Master Dropdown</span>
            </button>
        </div>

        <!-- ================= TAB 1: INFORMASI ACARA PERNIKAHAN ================= -->
        <div x-show="activeTab === 'wedding'" x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0"
            class="space-y-6">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">

                <!-- Left: Form Input Data Pernikahan (7 Cols on Desktop) -->
                <div class="lg:col-span-7 bg-white p-6 rounded-2xl border border-[#B6ADA3]/35 shadow-xs space-y-5">
                    <div class="flex items-center justify-between border-b border-[#B6ADA3]/30 pb-3">
                        <h2 class="text-base font-bold font-serif-title text-[#5F6F5B] flex items-center gap-2">
                            <i class="fa-solid fa-pen-to-square text-[#D8A7B1]"></i> Edit Detail Acara Pernikahan
                        </h2>
                        <span
                            class="text-xs text-[#5F6F5B] font-bold bg-[#FAF7F2] px-3 py-1 rounded-lg border border-[#B6ADA3]/30">
                            ID Acara #<?php echo e($wedding->id); ?>

                        </span>
                    </div>

                    <form action="<?php echo e(route('admin.wedding.update', $wedding->id)); ?>" method="POST" class="space-y-4">
                        <?php echo csrf_field(); ?>
                        <?php echo method_field('PUT'); ?>

                        <!-- Judul Acara -->
                        <div>
                            <label class="block text-xs font-bold text-[#5F6F5B] mb-1">Judul Acara Pernikahan *</label>
                            <input type="text" name="title" value="<?php echo e(old('title', $wedding->title)); ?>" required
                                placeholder="Contoh: Pernikahan Romeo & Juliet"
                                class="w-full bg-[#FAF7F2] border border-[#B6ADA3]/50 text-sm font-bold text-[#2D372E] px-3.5 py-2.5 rounded-xl focus:border-[#D8A7B1] focus:bg-white focus:outline-none transition-all">
                        </div>

                        <!-- Bride & Groom Name Grid -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-[#5F6F5B] mb-1">
                                    <i class="fa-solid fa-venus text-[#D8A7B1] mr-1"></i> Nama Pengantin Wanita (Bride) *
                                </label>
                                <input type="text" name="bride_name"
                                    value="<?php echo e(old('bride_name', $wedding->bride_name)); ?>" required
                                    placeholder="Juliet Capulet"
                                    class="w-full bg-[#FAF7F2] border border-[#B6ADA3]/50 text-sm font-semibold text-[#2D372E] px-3.5 py-2.5 rounded-xl focus:border-[#D8A7B1] focus:bg-white focus:outline-none transition-all">
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-[#5F6F5B] mb-1">
                                    <i class="fa-solid fa-mars text-[#5F6F5B] mr-1"></i> Nama Pengantin Pria (Groom) *
                                </label>
                                <input type="text" name="groom_name"
                                    value="<?php echo e(old('groom_name', $wedding->groom_name)); ?>" required
                                    placeholder="Romeo Montague"
                                    class="w-full bg-[#FAF7F2] border border-[#B6ADA3]/50 text-sm font-semibold text-[#2D372E] px-3.5 py-2.5 rounded-xl focus:border-[#D8A7B1] focus:bg-white focus:outline-none transition-all">
                            </div>
                        </div>

                        <!-- Date & Total Budget Grid -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-[#5F6F5B] mb-1">
                                    <i class="fa-solid fa-calendar-day text-[#D8A7B1] mr-1"></i> Tanggal Pernikahan (Hari H)
                                    *
                                </label>
                                <input type="text" name="wedding_date"
                                    value="<?php echo e(old('wedding_date', $wedding->wedding_date ? $wedding->wedding_date->format('Y-m-d') : '')); ?>"
                                    required
                                    class="datepicker w-full bg-[#FAF7F2] border border-[#B6ADA3]/50 text-sm font-semibold text-[#2D372E] px-3.5 py-2.5 rounded-xl focus:border-[#D8A7B1] focus:bg-white focus:outline-none transition-all">
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-[#5F6F5B] mb-1">
                                    <i class="fa-solid fa-money-bill-wave text-[#5F6F5B] mr-1"></i> Target Total Budget (Rp)
                                    *
                                </label>
                                <input type="number" name="total_budget"
                                    value="<?php echo e(old('total_budget', (int) $wedding->total_budget)); ?>" required min="0"
                                    placeholder="150000000"
                                    class="w-full bg-[#FAF7F2] border border-[#B6ADA3]/50 text-sm font-bold text-[#2D372E] px-3.5 py-2.5 rounded-xl focus:border-[#D8A7B1] focus:bg-white focus:outline-none transition-all">
                            </div>
                        </div>

                        <!-- Location Venue -->
                        <div>
                            <label class="block text-xs font-bold text-[#5F6F5B] mb-1">
                                <i class="fa-solid fa-location-dot text-[#D8A7B1] mr-1"></i> Lokasi Venue Pernikahan
                            </label>
                            <input type="text" name="location" value="<?php echo e(old('location', $wedding->location)); ?>"
                                placeholder="Grand Ballroom Hotel Indonesia, Jakarta"
                                class="w-full bg-[#FAF7F2] border border-[#B6ADA3]/50 text-sm font-semibold text-[#2D372E] px-3.5 py-2.5 rounded-xl focus:border-[#D8A7B1] focus:bg-white focus:outline-none transition-all">
                        </div>

                        <!-- Notes & Theme -->
                        <div>
                            <label class="block text-xs font-bold text-[#5F6F5B] mb-1">Catatan & Tema Pernikahan</label>
                            <textarea name="notes" rows="3" placeholder="Misal: Tema warna Dusty Rose & Sage Green..."
                                class="w-full bg-[#FAF7F2] border border-[#B6ADA3]/50 text-sm font-medium text-[#2D372E] px-3.5 py-2.5 rounded-xl focus:border-[#D8A7B1] focus:bg-white focus:outline-none transition-all resize-none"><?php echo e(old('notes', $wedding->notes)); ?></textarea>
                        </div>

                        <div class="pt-3 border-t border-[#B6ADA3]/30 flex justify-end">
                            <button type="submit"
                                class="btn-primary-rose px-6 py-3 rounded-xl text-xs sm:text-sm font-bold flex items-center justify-center gap-2 cursor-pointer shadow-md">
                                <i class="fa-solid fa-floppy-disk"></i> Simpan Informasi Pernikahan
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Right: Preview Ringkasan Card (5 Cols on Desktop) -->
                <div class="lg:col-span-5 space-y-4">
                    <!-- High Contrast Preview Card -->
                    <div class="bg-[#5F6F5B] text-white p-6 rounded-2xl shadow-md space-y-5 border border-[#4F5E4B]">
                        <div class="flex items-center justify-between border-b border-white/20 pb-3">
                            <span
                                class="text-[10px] uppercase font-bold tracking-widest bg-white/20 text-white px-2.5 py-1 rounded-full">Pratinjau
                                Dashboard</span>
                            <i class="fa-solid fa-heart text-base text-[#D8A7B1]"></i>
                        </div>

                        <div class="space-y-1">
                            <h3 class="text-xl font-bold font-serif-title text-[#FAF7F2]"><?php echo e($wedding->title); ?></h3>
                            <p class="text-xs text-white/80">Pasangan Pengantin:</p>
                            <div class="text-lg font-bold text-[#FAF7F2] flex items-center gap-2 pt-0.5">
                                <span><?php echo e($wedding->bride_name); ?></span>
                                <span class="text-[#D8A7B1] font-serif">&amp;</span>
                                <span><?php echo e($wedding->groom_name); ?></span>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-3 pt-3 border-t border-white/20 text-xs">
                            <div class="bg-black/20 p-3 rounded-xl border border-white/15">
                                <span class="text-[#D8A7B1] block text-[10px] uppercase font-bold mb-0.5">TANGGAL HARI
                                    H</span>
                                <span class="font-bold text-white text-xs sm:text-sm">
                                    <?php echo e($wedding->wedding_date ? $wedding->wedding_date->format('d M Y') : '—'); ?>

                                </span>
                            </div>
                            <div class="bg-black/20 p-3 rounded-xl border border-white/15">
                                <span class="text-[#D8A7B1] block text-[10px] uppercase font-bold mb-0.5">TARGET
                                    BUDGET</span>
                                <span class="font-bold text-white text-xs sm:text-sm">
                                    Rp <?php echo e(number_format($wedding->total_budget, 0, ',', '.')); ?>

                                </span>
                            </div>
                        </div>

                        <?php if($wedding->location): ?>
                            <div
                                class="text-xs text-white flex items-center gap-2 pt-1 bg-black/20 px-3.5 py-2.5 rounded-xl border border-white/15">
                                <i class="fa-solid fa-location-dot text-[#D8A7B1]"></i>
                                <span class="truncate font-semibold"><?php echo e($wedding->location); ?></span>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- High Contrast Info Box -->
                    <div class="bg-white p-5 rounded-2xl border border-[#B6ADA3]/35 shadow-xs space-y-2 text-xs">
                        <div class="flex items-center gap-2 font-bold text-[#5F6F5B] text-sm">
                            <i class="fa-solid fa-circle-info text-[#D8A7B1]"></i>
                            <span>Catatan Otomatisasi</span>
                        </div>
                        <p class="text-[#5F6F5B] leading-relaxed">
                            Data yang Anda simpan di sini secara otomatis memperbarui <strong>Countdown Hari H</strong>,
                            header aplikasi, serta angka acuan di modul <strong>Budget Planner</strong> dan <strong>Laporan
                                Keuangan</strong>.
                        </p>
                    </div>
                </div>

            </div>
        </div>

        <!-- ================= TAB 2: MASTER DROPDOWN SYSTEM ================= -->
        <div x-show="activeTab === 'dropdowns'" x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0"
            class="space-y-6">

            <!-- Mobile Selector Header (Visible on Small Screens) -->
            <div class="block lg:hidden bg-white p-4 rounded-2xl border border-[#B6ADA3]/35 shadow-xs space-y-2">
                <label class="block text-xs font-bold uppercase tracking-wider text-[#5F6F5B]">Pilih Group Dropdown
                    Menu</label>
                <select onchange="window.location.href = this.value"
                    class="w-full bg-[#FAF7F2] border border-[#B6ADA3]/50 text-sm font-bold text-[#5F6F5B] px-3.5 py-2.5 rounded-xl focus:border-[#D8A7B1] focus:outline-none">
                    <?php $__currentLoopData = $groups; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $name): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php $count = $groupCounts[$key] ?? 0; ?>
                        <option value="<?php echo e(route('admin.index', ['tab' => 'dropdowns', 'group' => $key])); ?>"
                            <?php echo e($selectedGroup === $key ? 'selected' : ''); ?>>
                            <?php echo e($name); ?> (<?php echo e($count); ?> item)
                        </option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>

            <!-- Main Master Dropdown Grid (Sidebar + Table) -->
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">

                <!-- Left Panel: PC Group Navigation (4 Cols on Desktop) -->
                <div
                    class="hidden lg:block lg:col-span-4 bg-white rounded-2xl border border-[#B6ADA3]/35 shadow-xs p-4 space-y-2">
                    <div class="px-2 pb-2.5 border-b border-[#B6ADA3]/30 flex items-center justify-between">
                        <span class="text-xs font-bold uppercase tracking-wider text-[#5F6F5B]">Daftar Master
                            Dropdown</span>
                        <span
                            class="text-[10px] bg-[#FAF7F2] text-[#5F6F5B] px-2 py-0.5 rounded-full border border-[#B6ADA3]/30 font-bold"><?php echo e(count($groups)); ?>

                            Group</span>
                    </div>

                    <div class="space-y-1.5 pt-1">
                        <?php $__currentLoopData = $groups; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $name): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php
                                $count = $groupCounts[$key] ?? 0;
                                $isActive = $selectedGroup === $key;
                            ?>
                            <a href="<?php echo e(route('admin.index', ['tab' => 'dropdowns', 'group' => $key])); ?>"
                                class="flex items-center justify-between px-3.5 py-2.5 rounded-xl text-xs font-semibold transition-all <?php echo e($isActive ? 'bg-[#5F6F5B] text-white shadow-xs font-bold' : 'text-[#5F6F5B] hover:bg-[#FAF7F2]'); ?>">
                                <div class="flex items-center gap-2.5 truncate">
                                    <i class="fa-solid fa-list-ul text-[#D8A7B1]"></i>
                                    <span class="truncate"><?php echo e($name); ?></span>
                                </div>
                                <span
                                    class="px-2 py-0.5 rounded-full text-[10px] font-bold shrink-0 <?php echo e($isActive ? 'bg-white/20 text-white' : 'bg-[#FAF7F2] text-[#5F6F5B] border border-[#B6ADA3]/30'); ?>">
                                    <?php echo e($count); ?>

                                </span>
                            </a>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                </div>

                <!-- Right Panel: Options List Table (8 Cols on Desktop) -->
                <div class="lg:col-span-8 space-y-4">

                    <!-- Group Banner & Add Button -->
                    <div
                        class="bg-white p-5 rounded-2xl border border-[#B6ADA3]/35 shadow-xs flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                        <div>
                            <span class="text-[10px] text-[#5F6F5B] uppercase tracking-wider font-bold">Group
                                Terpilih</span>
                            <h2 class="text-lg sm:text-xl font-bold font-serif-title text-[#5F6F5B] mt-0.5">
                                <?php echo e($groups[$selectedGroup] ?? $selectedGroup); ?>

                            </h2>
                        </div>

                        <button
                            @click="modalOpen = true; editMode = false; currentItem = { group_key: '<?php echo e($selectedGroup); ?>', sort_order: <?php echo e($options->count()); ?> }"
                            class="btn-primary-rose px-4 py-2.5 rounded-xl text-xs font-bold flex items-center justify-center gap-2 cursor-pointer shadow-xs w-full sm:w-auto">
                            <i class="fa-solid fa-plus"></i> Tambah Pilihan Dropdown
                        </button>
                    </div>

                    <!-- Options Table View -->
                    <div class="bg-white rounded-2xl border border-[#B6ADA3]/35 shadow-xs overflow-hidden">
                        <div class="overflow-x-auto">
                            <table class="w-full text-left border-collapse">
                                <thead>
                                    <tr
                                        class="bg-[#FAF7F2] text-xs text-[#5F6F5B] uppercase tracking-wider border-b border-[#B6ADA3]/30">
                                        <th class="p-4 w-20 text-center">Urutan</th>
                                        <th class="p-4">Nilai Pilihan (Option Value)</th>
                                        <?php if($selectedGroup === 'guest_category'): ?>
                                            <th class="p-4 w-24 text-center">Default Pax</th>
                                        <?php endif; ?>
                                        <th class="p-4">Tanggal Dibuat</th>
                                        <th class="p-4 text-right w-28">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-[#B6ADA3]/20 text-sm">
                                    <?php $__empty_1 = true; $__currentLoopData = $options; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $opt): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                        <tr class="hover:bg-[#FAF7F2]/70 transition-colors">
                                            <td class="p-4 text-center font-bold text-[#5F6F5B]">
                                                <span
                                                    class="w-7 h-7 inline-flex items-center justify-center rounded-full text-xs">
                                                    <?php echo e($opt->sort_order); ?>

                                                </span>
                                            </td>
                                            <td class="p-4 font-bold text-[#2D372E]">
                                                <span
                                                    class="px-3 py-1 rounded-xl bg-[#FAF7F2] border border-[#B6ADA3]/40 inline-block text-xs sm:text-sm text-[#5F6F5B]">
                                                    <?php echo e($opt->option_value); ?>

                                                </span>
                                            </td>
                                            <?php if($selectedGroup === 'guest_category'): ?>
                                                <td class="p-4 text-center font-bold text-[#5F6F5B]">
                                                    <?php echo e($opt->meta_value ?? '—'); ?>

                                                </td>
                                            <?php endif; ?>
                                            <td class="p-4 text-xs font-medium text-[#5F6F5B]">
                                                <?php echo e($opt->created_at ? $opt->created_at->format('d M Y, H:i') : '—'); ?>

                                            </td>
                                            <td class="p-4 text-right">
                                                <div class="flex items-center justify-end gap-2">
                                                    <button
                                                        @click="modalOpen = true; editMode = true; currentItem = <?php echo e(json_encode($opt)); ?>"
                                                        class="p-2 text-[#B6ADA3] hover:text-[#5F6F5B] cursor-pointer"
                                                        title="Edit">
                                                        <i class="fa-solid fa-pen-to-square"></i>
                                                    </button>
                                                    <form id="del-dropdown-<?php echo e($opt->id); ?>" action="<?php echo e(route('admin.dropdowns.destroy', $opt->id)); ?>"
                                                         method="POST">
                                                         <?php echo csrf_field(); ?>
                                                         <?php echo method_field('DELETE'); ?>
                                                         <button type="button"
                                                             title="Hapus"
                                                             onclick="confirmDelete('del-dropdown-<?php echo e($opt->id); ?>', '<?php echo e(addslashes($opt->option_value)); ?>')"
                                                             class="p-2 text-[#B6ADA3] hover:text-[#5F6F5B] cursor-pointer">
                                                             <i class="fa-solid fa-trash"></i>
                                                         </button>
                                                     </form>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                        <tr>
                                            <td colspan="4" class="text-center py-12 text-[#5F6F5B] text-sm">
                                                <i class="fa-solid fa-folder-open text-2xl text-[#D8A7B1] mb-2 block"></i>
                                                Belum ada pilihan dropdown untuk group ini. Klik tombol <strong>Tambah
                                                    Pilihan Dropdown</strong> untuk membuat.
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                </div>
            </div>

        </div>

        <!-- ================= MODAL FORM (TAMBAH / EDIT DROPDOWN OPTION) ================= -->
        <div x-show="modalOpen" x-transition.opacity
            class="fixed inset-0 z-50 bg-[#5F6F5B]/40 backdrop-blur-md flex items-center justify-center p-4" x-cloak>
            <div @click.away="modalOpen = false"
                class="bg-white w-full max-w-md p-6 rounded-2xl border border-[#B6ADA3]/40 shadow-2xl space-y-4">
                <div class="flex items-center justify-between border-b border-[#B6ADA3]/30 pb-3">
                    <h3 class="text-base font-bold font-serif-title text-[#5F6F5B]"
                        x-text="editMode ? 'Edit Pilihan Dropdown' : 'Tambah Pilihan Dropdown Baru'"></h3>
                    <button @click="modalOpen = false" class="text-[#B6ADA3] hover:text-[#5F6F5B] cursor-pointer">
                        <i class="fa-solid fa-xmark text-lg"></i>
                    </button>
                </div>

                <form :action="editMode ? '/admin/dropdowns/' + currentItem.id : '<?php echo e(route('admin.dropdowns.store')); ?>'"
                    method="POST" class="space-y-4">
                    <?php echo csrf_field(); ?>
                    <template x-if="editMode">
                        <input type="hidden" name="_method" value="PUT">
                    </template>

                    <input type="hidden" name="group_key" :value="currentItem.group_key || '<?php echo e($selectedGroup); ?>'">

                    <div>
                        <label class="block text-xs font-bold text-[#5F6F5B] mb-1">Group Target</label>
                        <input type="text" readonly :value="'<?php echo e($groups[$selectedGroup] ?? $selectedGroup); ?>'"
                            class="w-full bg-[#FAF7F2] border border-[#B6ADA3]/40 text-sm font-bold text-[#5F6F5B] px-3.5 py-2.5 rounded-xl focus:outline-none">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-[#5F6F5B] mb-1">Nama Pilihan (Option Value) *</label>
                        <input type="text" name="option_value" x-model="currentItem.option_value" required
                            placeholder="Contoh: Catering International, High Priority, dll"
                            class="w-full bg-[#FAF7F2] border border-[#B6ADA3]/50 text-sm font-semibold text-[#2D372E] px-3.5 py-2.5 rounded-xl focus:border-[#D8A7B1] focus:bg-white focus:outline-none">
                    </div>

                    <?php if($selectedGroup === 'guest_category'): ?>
                    <div>
                        <label class="block text-xs font-bold text-[#5F6F5B] mb-1">Default Pax</label>
                        <input type="number" name="meta_value" x-model="currentItem.meta_value" min="1"
                            placeholder="2"
                            class="w-full bg-[#FAF7F2] border border-[#B6ADA3]/50 text-sm font-semibold text-[#2D372E] px-3.5 py-2.5 rounded-xl focus:border-[#D8A7B1] focus:bg-white focus:outline-none">
                        <p class="text-[11px] text-[#5F6F5B]/70 mt-1">Jumlah pax default saat pilih kategori ini.</p>
                    </div>
                    <?php endif; ?>

                    <div>
                        <label class="block text-xs font-bold text-[#5F6F5B] mb-1">Urutan Tampil (Sort Order)</label>
                        <input type="number" name="sort_order" x-model="currentItem.sort_order" min="0"
                            placeholder="0"
                            class="w-full bg-[#FAF7F2] border border-[#B6ADA3]/50 text-sm font-semibold text-[#2D372E] px-3.5 py-2.5 rounded-xl focus:border-[#D8A7B1] focus:bg-white focus:outline-none">
                        <p class="text-[11px] text-[#5F6F5B]/70 mt-1">Angka lebih kecil tampil lebih di atas.</p>
                    </div>

                    <div class="flex justify-end gap-3 pt-3 border-t border-[#B6ADA3]/30">
                        <button type="button" @click="modalOpen = false"
                            class="px-4 py-2 text-xs text-[#5F6F5B] hover:text-[#5F6F5B] font-bold cursor-pointer">Batal</button>
                        <button type="submit"
                            class="btn-primary-rose px-5 py-2.5 rounded-xl text-xs cursor-pointer font-bold shadow-xs">
                            <i class="fa-solid fa-check mr-1"></i> Simpan Pilihan
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Admin\Herd\wedding_planner\resources\views/admin/index.blade.php ENDPATH**/ ?>