<?php

namespace Database\Seeders;

use App\Models\DropdownOption;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Seeder dasar aplikasi.
 *
 * Yang di-seed HANYA:
 *   1. Satu user superadmin (tanpa data pernikahan).
 *   2. Master data dropdown — 93 opsi / 13 group, urutan mengikuti nilai
 *      yang sudah dipakai aplikasi.
 *
 * Tidak ada data wedding, tamu, budget, vendor, dsb. Semua itu dibuat
 * lewat aplikasi (menu Admin Panel) atau oleh user yang sedang memakai app.
 *
 * Semua proses memakai firstOrCreate, jadi seeder aman dijalankan berulang.
 */
class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedSuperadmin();
        $this->seedDropdownMaster();
    }

    /**
     * Akun superadmin: hanya untuk mengelola user & master data,
     * tidak punya wedding sehingga tidak bisa melihat modul aplikasi.
     */
    private function seedSuperadmin(): void
    {
        User::firstOrCreate(
            ['email' => 'admin@weddingplanner.web.id'],
            [
                'name' => 'Superadmin',
                // cast 'password' => 'hashed' di model User otomatis meng-hash nilai ini
                'password' => 'lopyu',
                'is_superadmin' => true,
                'wedding_id' => null,
            ]
        );
    }

    /**
     * Master data dropdown.
     *
     * Format: groupKey => [ groupName, [ [optionValue, metaValue|null], ... ] ]
     * Indeks array menjadi sort_order.
     */
    private function seedDropdownMaster(): void
    {
        $groups = [
            'checklist_category' => ['Kategori Checklist', [
                ['General', null], ['Venue', null], ['Catering', null], ['Dekorasi', null],
                ['Fotografer', null], ['MUA', null], ['Undangan', null], ['Souvenir', null],
                ['Acara', null],
            ]],
            'checklist_priority' => ['Prioritas Checklist', [
                ['High', null], ['Medium', null], ['Low', null],
            ]],
            'checklist_status' => ['Status Checklist', [
                ['Todo', null], ['Progress', null], ['Done', null],
            ]],
            // Module 12 - Tabungan Pernikahan
            'savings_status' => ['Status Tabungan', [
                ['Aktif', null], ['Tercapai', null], ['Ditunda', null],
            ]],
            'savings_frequency' => ['Frekuensi Setoran', [
                ['Mingguan', null], ['Bulanan', null], ['Tahunan', null],
            ]],
            'savings_transaction_type' => ['Jenis Transaksi Tabungan', [
                ['setoran', 'Setoran'], ['penarikan', 'Penarikan'],
            ]],
            // Module 14 - Seserahan
            'souvenir_status' => ['Status Seserahan', [
                ['Belum Dipilih', null], ['Sedang Dipilih', null], ['Diterima', null],
            ]],
            'budget_category' => ['Kategori Budget', [
                ['Venue', null], ['Catering', null], ['Dekorasi', null], ['Fotografer', null],
                ['Videografer', null], ['MUA', null], ['Busana', null], ['Souvenir', null],
                ['Undangan', null], ['Transportasi', null], ['Seserahan', null], ['Mahar', null],
                ['Wedding Cake', null], ['Wedding Organizer', null], ['Hiburan', null], ['MC', null],
                ['Lighting', null], ['Sound System', null], ['Percetakan', null],
                ['Perhiasan', null], ['Lainnya', null],
            ]],
            'vendor_category' => ['Kategori Vendor', [
                ['Venue', null], ['Catering', null], ['Dekorasi', null], ['Fotografer', null],
                ['Videografer', null], ['MUA', null], ['Wedding Organizer', null], ['Hiburan', null],
                ['MC', null], ['Lighting', null], ['Sound System', null], ['Transportasi', null],
                ['Souvenir', null], ['Percetakan', null], ['Wedding Cake', null], ['Busana', null],
                ['Perhiasan', null], ['Lainnya', null],
            ]],
            'vendor_status' => ['Status Booking Vendor', [
                ['Not Contacted', null], ['Negotiating', null], ['Booked', null],
                ['Completed', null], ['Cancelled', null],
            ]],
            'guest_category' => ['Kategori Tamu', [
                ['Family', '2'], ['Friend', '2'], ['Office', '1'], ['VIP', '1'],
                ['Neighbor', '1'], ['Lainnya', null],
            ]],
            'guest_status' => ['Status Kehadiran (RSVP)', [
                ['Pending', null], ['Attend', null], ['Decline', null],
            ]],
            'guest_title' => ['Gelar Tamu', [
                ['Bpk.', null], ['Ibu.', null], ['Sdr.', null], ['Ny.', null], ['Tn.', null],
            ]],
            'moodboard_category' => ['Kategori Moodboard', [
                ['Dekorasi', null], ['Pelaminan', null], ['Gaun Pengantin', null], ['Suit', null],
                ['Makeup', null], ['Undangan', null], ['Souvenir', null], ['Table Setting', null],
                ['Lainnya', null],
            ]],
            'contract_status' => ['Status Kontrak Vendor', [
                ['Draft', null], ['Signed', null], ['Waiting', null], ['Completed', null],
                ['Cancelled', null],
            ]],
            'payment_status' => ['Status Pembayaran', [
                ['Belum Bayar', null], ['DP', null], ['Cicilan', null], ['Lunas', null],
            ]],
            'gift_type' => ['Jenis Hadiah', [
                ['Cash', null], ['Barang', null],
            ]],
        ];

        foreach ($groups as $groupKey => [$groupName, $options]) {
            foreach ($options as $sortOrder => [$value, $metaValue]) {
                DropdownOption::firstOrCreate(
                    ['group_key' => $groupKey, 'option_value' => $value],
                    [
                        'group_name' => $groupName,
                        'meta_value' => $metaValue,
                        'sort_order' => $sortOrder,
                    ]
                );
            }
        }
    }
}
