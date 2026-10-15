<?php

/**
 * Registry fitur berbayar.
 *
 * Setiap fitur dipakai dua kali: sebagai keyword route middleware
 * (->middleware('plan:moodboard')) dan sebagai kunci untuk ikon gembok di
 * sidebar. Menambah menu Pro berarti menambah satu baris di sini, satu keyword
 * di routes/web.php, dan satu atribut di nav — tidak ada kode gating lain yang
 * perlu disentuh.
 *
 * Fitur yang tidak terdaftar di sini dianggap gratis, jadi middleware tidak
 * menahannya.
 *
 * Harga, rekening, dan nomor WA admin sengaja TIDAK ada di sini karena
 * dikelola superadmin lewat tabel app_settings (lihat App\Models\Setting).
 */
return [
    'features' => [
        'moodboard' => 'pro',
    ],
];