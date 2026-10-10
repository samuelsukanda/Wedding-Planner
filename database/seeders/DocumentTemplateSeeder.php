<?php

namespace Database\Seeders;

use App\Models\DocumentTemplate;
use Illuminate\Database\Seeder;

/**
 * Template dokumen Persyaratan Nikah standar KUA.
 *
 * Dipanggil dari DatabaseSeeder. Superadmin masih bisa menambah, menghapus,
 * atau mengganti isi template ini lewat Admin Panel tanpa sentuh kode.
 *
 * firstOrCreate berdasarkan name, jadi aman dijalankan berulang.
 */
class DocumentTemplateSeeder extends Seeder
{
    public function run(): void
    {
        $template = DocumentTemplate::firstOrCreate(
            ['name' => 'Persyaratan Nikah (KUA)'],
            [
                'description' => 'Dokumen standar untuk pendaftaran dan percakapan nikah di KUA.',
                'is_active' => true,
                'sort_order' => 1,
            ]
        );

        $items = [
            ['name' => 'Surat Pengantar RT / RW', 'status' => 'Belum Lengkap'],
            ['name' => 'Fotokopi KTP Suami', 'status' => 'Belum Lengkap'],
            ['name' => 'Fotokopi KTP Istri', 'status' => 'Belum Lengkap'],
            ['name' => 'Fotokopi Kartu Keluarga', 'status' => 'Belum Lengkap'],
            ['name' => 'Akta Lahir Suami', 'status' => 'Belum Lengkap'],
            ['name' => 'Akta Lahir Istri', 'status' => 'Belum Lengkap'],
            ['name' => 'Buku Nikah', 'status' => 'Belum Lengkap'],
            ['name' => 'Surat Izin Orang Tua', 'status' => 'Belum Lengkap'],
            ['name' => 'Pas Foto Suami & Istri', 'status' => 'Belum Lengkap'],
            ['name' => 'Surat Pernikahan dari Kantor Urusan Agama', 'status' => 'Belum Lengkap'],
            ['name' => 'Surat Dispensasi (jika ada)', 'status' => 'Belum Lengkap'],
        ];

        // Hanya isi kalau template belum punya daftar, supaya perubahan yang
        // dilakukan superadmin tidak tertimpa setiap kali seeder dijalankan.
        if (empty($template->items)) {
            $template->update(['items' => $items]);
        }
    }
}