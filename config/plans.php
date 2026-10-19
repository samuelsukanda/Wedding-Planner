<?php

/**
 * Registry fitur yang dikunci penuh per paket.
 *
 * Bentuknya: 'nama_menu' => 'paket_yang_dibutuhkan'. Satu-satunya tempat yang
 * memutuskan sebuah fitur berbayar boleh dibuka; middleware EnsurePlan, halaman
 * terkunci, dan ikon gembok di sidebar semuanya membaca dari sini supaya tidak
 * bisa berbeda pendapat.
 *
 * Dua cara mengunci, pilihannya ada di routes/web.php:
 * - ->middleware('plan:nama') untuk fitur yang cukup dialihkan ke /upgrade
 *   (Cari Vendor).
 * - Halaman terkunci sendiri untuk fitur yang lebih baik menampilkan penjelasan
 *   apa yang terkunci di halaman tersebut (Moodboard).
 *
 * Kalau nanti ada modul Premium lain, tambahkan satu baris di sini lalu pilih
 * salah satu dari dua cara di atas.
 */
return [
    'features' => [
        'vendor_directory' => 'pro',
        'moodboard' => 'pro',
    ],
];