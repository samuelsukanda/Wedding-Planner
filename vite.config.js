import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import { bunny } from 'laravel-vite-plugin/fonts';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            // table-filter.js jadi entry terpisah supaya tag @vite-nya bisa diletakkan
// sebelum Alpine di layout. Alpine start() langsung setelah dievaluasi,
// sehingga helper x-data harus sudah ada di window sebelum itu.
input: ['resources/css/app.css', 'resources/js/table-filter.js', 'resources/js/app.js'],
            refresh: true,
            fonts: [
                bunny('Instrument Sans', {
                    weights: [400, 500, 600],
                }),
            ],
        }),
        tailwindcss(),
    ],
    server: {
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});
