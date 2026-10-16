<?php

/**
 * Registry fitur yang dikunci penuh per paket.
 *
 * Bentuknya: 'nama_menu' => 'paket_yang_dibutuhkan'. Dipakai middleware
 * EnsurePlan (alias 'plan' di bootstrap/app.php).
 *
 * Kosong sekarang: semua fitur Pro yang ada berbasis KUOTA, bukan kunci mati,
 * sehingga tidak ada route yang memakai EnsurePlan. Moodboard free boleh
 * dibuka, hanya jumlah item-nya yang dibatasi (lihat Wedding::limitFor()).
 * Jangan isi 'moodboard' di sini — hasFeature() akan menyatakan free tidak
 * boleh akses padahal nyatanya boleh.
 *
 * Kalau nanti ada modul Premium yang mau dikunci mati, tambahkan satu baris di
 * sini lalu pasang ->middleware('plan:nama_modul') di routes/web.php.
 */
return [
    'features' => [
        'vendor_directory' => 'pro',
    ],
];