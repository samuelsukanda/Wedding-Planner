<?php

namespace App\Support;

use App\Models\Checklist;
use App\Models\Wedding;
use Carbon\Carbon;

/**
 * Checklist awal untuk setiap pasangan yang baru dibuat.
 *
 * Dipanggil otomatis dari AdminPanelController::storeUser() saat
 * "Buat data pernikahan baru" dipilih, sehingga setiap pasangan baru
 * langsung punya daftar tugas yang bisa langsung dikerjakan.
 *
 * Deadline dihitung relatif terhadap hari pembuatan. Kalau ingin mengikuti
 * tanggal pernikahan, ganti base dengan:
 *     $base = Carbon::parse($wedding->wedding_date);
 */
class ChecklistTemplate
{
    /**
     * @return list<array{title:string, category:string, deadline:string, priority:string, status:string, description:string}>
     */
    public static function items(): array
    {
        return [
            ['title' => 'Menentukan tanggal pernikahan', 'category' => 'General', 'deadline' => '-60', 'priority' => 'High', 'status' => 'Todo', 'description' => 'Tanggal sudah disepakati oleh keluarga pengantin'],
            ['title' => 'Menentukan venue acara', 'category' => 'Venue', 'deadline' => '-45', 'priority' => 'High', 'status' => 'Todo', 'description' => 'Venue sudah dipilih dan ditawar'],
            ['title' => 'Booking gedung / venue', 'category' => 'Venue', 'deadline' => '-30', 'priority' => 'High', 'status' => 'Todo', 'description' => 'DP gedung sudah dibayarkan'],
            ['title' => 'Booking catering', 'category' => 'Catering', 'deadline' => '-20', 'priority' => 'High', 'status' => 'Todo', 'description' => 'Jumlah pax & menu sudah disepakati'],
            ['title' => 'Booking dekorasi', 'category' => 'Dekorasi', 'deadline' => '-10', 'priority' => 'High', 'status' => 'Todo', 'description' => 'Konsep dekorasi sudah disetujui'],
            ['title' => 'Booking fotografer & videografer', 'category' => 'Fotografer', 'deadline' => '+5', 'priority' => 'Medium', 'status' => 'Todo', 'description' => 'Paket liputan pre-wedding sudah dipilih'],
            ['title' => 'Booking MUA & rias pengantin', 'category' => 'MUA', 'deadline' => '+10', 'priority' => 'High', 'status' => 'Todo', 'description' => 'Konsep makeup & rias sudah fix'],
            ['title' => 'Membuat & cetak undangan', 'category' => 'Undangan', 'deadline' => '+20', 'priority' => 'Medium', 'status' => 'Todo', 'description' => 'Jumlah undangan sudah dihitung dari daftar tamu'],
            ['title' => 'Memesan souvenir pernikahan', 'category' => 'Souvenir', 'deadline' => '+30', 'priority' => 'Low', 'status' => 'Todo', 'description' => 'Pilih souvenir & vendor produksi'],
            ['title' => 'Gladi resik susunan acara', 'category' => 'Acara', 'deadline' => '+115', 'priority' => 'High', 'status' => 'Todo', 'description' => 'Gladi resik bersama WO & keluarga'],
            ['title' => 'Pelaksanaan Akad / Holy Matrimony', 'category' => 'Acara', 'deadline' => '+120', 'priority' => 'High', 'status' => 'Todo', 'description' => 'Penghulu & saksi sudah dikonfirmasi'],
            ['title' => 'Pelaksanaan Resepsi Pernikahan', 'category' => 'Acara', 'deadline' => '+120', 'priority' => 'High', 'status' => 'Todo', 'description' => 'Jam acara & jalur tamu sudah diatur'],
        ];
    }

    /**
     * Salin template ke satu weddings tertentu.
     */
    public static function seedFor(Wedding $wedding): void
    {
        $base = Carbon::now();

        foreach (self::items() as $item) {
            Checklist::create([
                'wedding_id' => $wedding->id,
                'title' => $item['title'],
                'category' => $item['category'],
                'deadline' => $base->copy()->addDays((int) $item['deadline']),
                'priority' => $item['priority'],
                'status' => $item['status'],
                'description' => $item['description'],
            ]);
        }
    }
}
