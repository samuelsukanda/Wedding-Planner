<?php

/**
 * Pengaturan add-on Photobooth.
 *
 * Daftar font di sini satu-satunya sumber di sisi Wedding Planner: form
 * pengaturannya offering daftar ini. Backend Photobooth tidak menyalin
 * daftarnya, ia hanya menyaring pola nama font, jadi tidak ada dua daftar
 * yang bisa berbeda isi.
 */
return [
    'default_font' => 'Great Vibes',

    'fonts' => [
        'Gaya Tulisan Tangan (Handwriting)' => [
            'Caveat',
            'Permanent Marker',
            'Indie Flower',
            'Kalam',
        ],
        'Gaya Skrip Elegan (Calligraphy)' => [
            'Great Vibes',
            'Alex Brush',
            'Pinyon Script',
            'Dancing Script',
        ],
        'Gaya Mesin Tik (Typewriter / Vintage)' => [
            'Courier Prime',
            'Special Elite',
            'VT323',
        ],
        'Gaya Sans Serif Modern (Clean & Minimalist)' => [
            'Montserrat',
            'Poppins',
            'Lato',
            'Roboto',
        ],
    ],
];
