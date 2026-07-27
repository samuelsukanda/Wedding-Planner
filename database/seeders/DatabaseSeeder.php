<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Wedding;
use App\Models\Checklist;
use App\Models\Budget;
use App\Models\Vendor;
use App\Models\VendorContract;
use App\Models\VendorPayment;
use App\Models\Guest;
use App\Models\Moodboard;
use App\Models\RundownEvent;
use App\Models\Gift;
use App\Models\Notification;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Default User
        $user = User::firstOrCreate(
            ['email' => 'admin@gmail.com'],
            [
                'name' => 'Samuel & Angela',
                'password' => bcrypt('lopyu'),
            ]
        );

        // 1. Wedding (Samuel & Angela)
        $weddingDate = Carbon::now()->addDays(120)->format('Y-m-d');
        $wedding = Wedding::create([
            'title' => 'Wedding Samuel & Angela',
            'bride_name' => 'Angela Bunga Ria',
            'groom_name' => 'Samuel',
            'wedding_date' => $weddingDate,
            'total_budget' => 80000000,
            'location' => 'The Glass House Ritz-Carlton, Jakarta',
            'notes' => 'Tema pernikahan: Romantic Pink Blossom & Rose Gold Elegance',
        ]);

        // 2. Checklists
        $checklists = [
            ['title' => 'Menentukan tanggal pernikahan', 'category' => 'General', 'deadline' => Carbon::now()->subDays(60), 'priority' => 'High', 'status' => 'Done', 'description' => 'Tanggal disepakati oleh keluarga Samuel & Angela'],
            ['title' => 'Menentukan venue acara', 'category' => 'Venue', 'deadline' => Carbon::now()->subDays(45), 'priority' => 'High', 'status' => 'Done', 'description' => 'Glass House Ballroom Ritz Carlton'],
            ['title' => 'Booking gedung / venue', 'category' => 'Venue', 'deadline' => Carbon::now()->subDays(30), 'priority' => 'High', 'status' => 'Done', 'description' => 'DP Gedung sudah dibayarkan'],
            ['title' => 'Booking catering', 'category' => 'Catering', 'deadline' => Carbon::now()->subDays(20), 'priority' => 'High', 'status' => 'Done', 'description' => 'Menu International Buffet 600 pax'],
            ['title' => 'Booking dekorasi pelaminan Pink Rose', 'category' => 'Dekorasi', 'deadline' => Carbon::now()->subDays(10), 'priority' => 'High', 'status' => 'Progress', 'description' => 'Konsep Pastel Pink Floral Garden'],
            ['title' => 'Booking fotografer & videografer', 'category' => 'Fotografer', 'deadline' => Carbon::now()->addDays(5), 'priority' => 'Medium', 'status' => 'Progress', 'description' => 'Paket liputan pre-wedding Samuel & Angela'],
            ['title' => 'Booking MUA & Rias Pengantin', 'category' => 'MUA', 'deadline' => Carbon::now()->addDays(10), 'priority' => 'High', 'status' => 'Done', 'description' => 'Soft Glam Pink MUA Angela & Rias Ibu'],
            ['title' => 'Membuat & cetak undangan', 'category' => 'Undangan', 'deadline' => Carbon::now()->addDays(20), 'priority' => 'Medium', 'status' => 'Progress', 'description' => 'Undangan Foil Rose Gold 350 lembar'],
            ['title' => 'Memesan souvenir pernikahan', 'category' => 'Souvenir', 'deadline' => Carbon::now()->addDays(30), 'priority' => 'Low', 'status' => 'Todo', 'description' => 'Scented Candle Custom Pink Velvet'],
            ['title' => 'Gladi resik susunan acara', 'category' => 'Acara', 'deadline' => Carbon::now()->addDays(115), 'priority' => 'High', 'status' => 'Todo', 'description' => 'Gladi resik bersama WO & keluarga'],
            ['title' => 'Pelaksanaan Akad / Holy Matrimony', 'category' => 'Acara', 'deadline' => Carbon::now()->addDays(120), 'priority' => 'High', 'status' => 'Todo', 'description' => 'Jam 09.00 WIB di Chapel Ritz Carlton'],
            ['title' => 'Pelaksanaan Resepsi Pernikahan', 'category' => 'Acara', 'deadline' => Carbon::now()->addDays(120), 'priority' => 'High', 'status' => 'Todo', 'description' => 'Jam 12.00 WIB - 15.00 WIB'],
        ];

        foreach ($checklists as $item) {
            Checklist::create(array_merge($item, ['wedding_id' => $wedding->id]));
        }

        // 3. Budgets (Total Rp 175.000.000)
        $budgets = [
            ['category' => 'Gedung', 'item_name' => 'Sewa Glass House Ritz-Carlton', 'planned_budget' => 55000000, 'actual_cost' => 55000000, 'notes' => 'Lunas termasuk fasilitas ballroom'],
            ['category' => 'Catering', 'item_name' => 'Buffet 600 Pax + 5 Food Stalls', 'planned_budget' => 50000000, 'actual_cost' => 30000000, 'notes' => 'DP 60% sudah dibayar'],
            ['category' => 'Dekorasi', 'item_name' => 'Dekor Pelaminan Pink Blossom & Lighting', 'planned_budget' => 30000000, 'actual_cost' => 18000000, 'notes' => 'DP 60% Pastel Pink Concept'],
            ['category' => 'Fotografer', 'item_name' => 'Foto & Video Prewed + H-Day Samuel & Angela', 'planned_budget' => 15000000, 'actual_cost' => 7000000, 'notes' => 'DP Liputan Cinematic'],
            ['category' => 'MUA', 'item_name' => 'Make Up Bride Angela & Family', 'planned_budget' => 12000000, 'actual_cost' => 4000000, 'notes' => 'DP Rias Pengantin'],
            ['category' => 'Busana', 'item_name' => 'Gaun Bride Angela & Tuxedo Samuel', 'planned_budget' => 10000000, 'actual_cost' => 3000000, 'notes' => 'Fitting gaun selesai'],
            ['category' => 'Souvenir', 'item_name' => 'Scented Candle Custom 450 Pcs', 'planned_budget' => 6000000, 'actual_cost' => 0, 'notes' => 'Proses produksi'],
            ['category' => 'Undangan', 'item_name' => 'Undangan Rose Gold Foil & Digital RSVP', 'planned_budget' => 4000000, 'actual_cost' => 0, 'notes' => 'Proses cetak'],
            ['category' => 'Lainnya', 'item_name' => 'Logistik WO & Cadangan', 'planned_budget' => 3000000, 'actual_cost' => 0, 'notes' => 'Dana operasional'],
        ];

        foreach ($budgets as $b) {
            Budget::create(array_merge($b, ['wedding_id' => $wedding->id]));
        }

        // 4. Vendors
        $v1 = Vendor::create([
            'wedding_id' => $wedding->id,
            'name' => 'Ritz-Carlton Glass House Venue',
            'category' => 'Venue',
            'contact' => '+62 811-3344-5566',
            'address' => 'Jl. Jend. Sudirman Kav. 52-53, Jakarta',
            'google_maps_url' => 'https://maps.google.com/?q=Ritz+Carlton+Jakarta',
            'package' => 'Luxury Glass House Wedding Package 600 Pax',
            'price' => 55000000,
            'rating' => 5.0,
            'review' => 'Lokasi mewah, pencahayaan alami kaca indah, sangat direkomendasikan.',
            'booking_status' => 'Completed',
        ]);

        $v2 = Vendor::create([
            'wedding_id' => $wedding->id,
            'name' => 'Pink Rose Culinary & Catering',
            'category' => 'Catering',
            'contact' => '+62 812-7766-5544',
            'address' => 'Jl. Senopati No. 88, Jakarta Selatan',
            'google_maps_url' => 'https://maps.google.com',
            'package' => 'Deluxe Pink Gourmet Buffet 600 Pax',
            'price' => 50000000,
            'rating' => 4.9,
            'review' => 'Menu lezat, penyajian estetik dengan tema warna pastel pink.',
            'booking_status' => 'Booked',
        ]);

        $v3 = Vendor::create([
            'wedding_id' => $wedding->id,
            'name' => 'Blossom Floral & Decor Studio',
            'category' => 'Dekorasi',
            'contact' => '+62 813-9900-1122',
            'address' => 'Jl. Gunawarman No. 22, Jakarta Selatan',
            'google_maps_url' => 'https://maps.google.com',
            'package' => 'Pastel Pink Garden & Rose Gold Arch Stage',
            'price' => 30000000,
            'rating' => 4.8,
            'review' => 'Desain dekorasi bunga pink sangat anggun dan sesuai impian Angela.',
            'booking_status' => 'Booked',
        ]);

        $v4 = Vendor::create([
            'wedding_id' => $wedding->id,
            'name' => 'Sweet Memories Cinema & Photo',
            'category' => 'Fotografer',
            'contact' => '+62 856-4433-2211',
            'address' => 'Jl. Wijaya II No. 15, Jakarta Selatan',
            'google_maps_url' => 'https://maps.google.com',
            'package' => 'Full Day Cinematic Video & Album Custom',
            'price' => 15000000,
            'rating' => 4.9,
            'review' => 'Dokumentasi momen Samuel & Angela sangat emosional dan estetik.',
            'booking_status' => 'Negotiating',
        ]);

        // 5. Vendor Contracts
        VendorContract::create([
            'wedding_id' => $wedding->id,
            'vendor_id' => $v1->id,
            'contract_number' => 'CTR/SA-VENUE/2026/01',
            'nominal' => 55000000,
            'dp_amount' => 20000000,
            'final_amount' => 35000000,
            'due_date' => Carbon::now()->subDays(10),
            'status' => 'Completed',
            'notes' => 'Sewa Glass House Samuel & Angela lunas.',
        ]);

        VendorContract::create([
            'wedding_id' => $wedding->id,
            'vendor_id' => $v2->id,
            'contract_number' => 'CTR/SA-CAT/2026/05',
            'nominal' => 50000000,
            'dp_amount' => 30000000,
            'final_amount' => 20000000,
            'due_date' => Carbon::now()->addDays(30),
            'status' => 'Signed',
            'notes' => 'Pelunasan catering H-14 sebelum acara.',
        ]);

        // 6. Vendor Payments
        VendorPayment::create([
            'wedding_id' => $wedding->id,
            'vendor_id' => $v1->id,
            'nominal' => 55000000,
            'payment_date' => Carbon::now()->subDays(15),
            'payment_method' => 'Bank Transfer BCA',
            'status' => 'Lunas',
            'reminder_date' => null,
            'notes' => 'Pembayaran lunas sewa venue Ritz-Carlton.',
        ]);

        VendorPayment::create([
            'wedding_id' => $wedding->id,
            'vendor_id' => $v2->id,
            'nominal' => 30000000,
            'payment_date' => Carbon::now()->subDays(5),
            'payment_method' => 'Bank Transfer Mandiri',
            'status' => 'DP',
            'reminder_date' => Carbon::now()->addDays(25),
            'notes' => 'DP Catering 60%, pelunasan sisa Rp 20.000.000',
        ]);

        VendorPayment::create([
            'wedding_id' => $wedding->id,
            'vendor_id' => $v3->id,
            'nominal' => 18000000,
            'payment_date' => Carbon::now()->subDays(2),
            'payment_method' => 'Bank Transfer BCA',
            'status' => 'DP',
            'reminder_date' => Carbon::now()->addDays(20),
            'notes' => 'DP Dekorasi Pelaminan Pink Blossom',
        ]);

        // 7. Guests
        $guests = [
            ['name' => 'Bpk. Wijaya & Ibu (Orang Tua Samuel)', 'address' => 'Jakarta Selatan', 'phone' => '08123456789', 'category' => 'Family', 'attendance_status' => 'Attend', 'guest_count' => 2],
            ['name' => 'Bpk. Agustina & Ibu (Orang Tua Angela)', 'address' => 'Bandung', 'phone' => '08139876543', 'category' => 'Family', 'attendance_status' => 'Attend', 'guest_count' => 2],
            ['name' => 'Christian & Stephanie', 'address' => 'Tangerang', 'phone' => '08571122334', 'category' => 'Friend', 'attendance_status' => 'Attend', 'guest_count' => 2],
            ['name' => 'Michelle Gunawan', 'address' => 'Jakarta Barat', 'phone' => '08784455667', 'category' => 'Friend', 'attendance_status' => 'Pending', 'guest_count' => 1],
            ['name' => 'Bpk. David Hartono (CEO Group)', 'address' => 'Jakarta Pusat', 'phone' => '08119988776', 'category' => 'Office', 'attendance_status' => 'Attend', 'guest_count' => 2],
            ['name' => 'Tim Executive & Product Dept.', 'address' => 'Jakarta', 'phone' => '08128877665', 'category' => 'Office', 'attendance_status' => 'Pending', 'guest_count' => 6],
            ['name' => 'Kevin Sanjaya', 'address' => 'Surabaya', 'phone' => '08133344556', 'category' => 'Friend', 'attendance_status' => 'Decline', 'guest_count' => 0],
        ];

        foreach ($guests as $g) {
            Guest::create(array_merge($g, ['wedding_id' => $wedding->id]));
        }

        // 8. Moodboards
        $moodboards = [
            ['category' => 'Dekorasi', 'title' => 'Pelaminan Pink Rose Blossom Garden', 'description' => 'Konsep panggung floral merah muda dan rose gold arch lighting.', 'link_reference' => 'https://pinterest.com/pin/pink_wedding_decor'],
            ['category' => 'Makeup', 'title' => 'Soft Pink Glam Look Bride Angela', 'description' => 'Makeup manis nuansa pink blush natural untuk Angela.', 'link_reference' => 'https://instagram.com/p/angela_makeup'],
            ['category' => 'Gaun', 'title' => 'Gaun Ballgown Blush Pink Lace Angela', 'description' => 'Gaun pengantin Angela nuansa soft pink dengan payet kristal.', 'link_reference' => 'https://pinterest.com/pin/angela_dress'],
            ['category' => 'Bouquet', 'title' => 'Hand Bouquet Pink Peony & Baby Breath', 'description' => 'Rangkaian bunga tangan warna baby pink lembut.', 'link_reference' => 'https://pinterest.com/pin/pink_bouquet'],
        ];

        foreach ($moodboards as $m) {
            Moodboard::create(array_merge($m, ['wedding_id' => $wedding->id]));
        }

        // 9. Rundown Events
        $rundowns = [
            ['time' => '07.00 - 08.30', 'activity' => 'Persiapan Rias Samuel & Angela', 'pic' => 'MUA & Tim WO (Mbak Pinkan)', 'location' => 'Presidential Suite Room', 'sort_order' => 1],
            ['time' => '09.00 - 10.30', 'activity' => 'Pemberkatan Nikah / Holy Matrimony', 'pic' => 'Pendeta & Saksi Keluarga', 'location' => 'Glass House Chapel', 'sort_order' => 2],
            ['time' => '10.30 - 11.30', 'activity' => 'Foto Keluarga Samuel & Angela', 'pic' => 'Tim Fotografer (Sweet Memories)', 'location' => 'Pelaminan Utama Pink Garden', 'sort_order' => 3],
            ['time' => '12.00 - 14.30', 'activity' => 'Resepsi Pernikahan & Grand Entrance', 'pic' => 'MC Acara (Mas Andrew)', 'location' => 'Ritz Carlton Ballroom', 'sort_order' => 4],
            ['time' => '14.30 - 15.00', 'activity' => 'Wedding Toast & Closing Ceremony', 'pic' => 'Tim WO', 'location' => 'Karpet Merah Center', 'sort_order' => 5],
        ];

        foreach ($rundowns as $r) {
            RundownEvent::create(array_merge($r, ['wedding_id' => $wedding->id]));
        }

        // 10. Gifts
        $gifts = [
            ['giver_name' => 'Bpk. Wijaya & Keluarga', 'gift_type' => 'Cash', 'nominal' => 5000000, 'description' => 'Angpao Pernikahan Samuel & Angela', 'is_thank_you_sent' => true],
            ['giver_name' => 'Christian & Stephanie', 'gift_type' => 'Barang', 'nominal' => 2500000, 'description' => 'Coffee Maker DeLonghi Pink Edition', 'is_thank_you_sent' => true],
            ['giver_name' => 'Bpk. David Hartono', 'gift_type' => 'Cash', 'nominal' => 3000000, 'description' => 'Hadiah dari Kantor Samuel', 'is_thank_you_sent' => false],
            ['giver_name' => 'Michelle Gunawan & Friends', 'gift_type' => 'Cash', 'nominal' => 1500000, 'description' => 'Angpao Sahabat Angela', 'is_thank_you_sent' => false],
        ];

        foreach ($gifts as $gf) {
            Gift::create(array_merge($gf, ['wedding_id' => $wedding->id]));
        }

        // 11. Notifications
        Notification::create([
            'wedding_id' => $wedding->id,
            'title' => 'Pengingat Pembayaran Catering H-30',
            'message' => 'Pelunasan sisa catering Pink Rose Culinary senilai Rp 20.000.000 akan jatuh tempo.',
            'type' => 'warning',
            'is_read' => false,
            'reminder_date' => Carbon::now()->addDays(25),
        ]);

        // 12. Dropdown Master Options
        $defaultDropdowns = [
            'checklist_category' => ['Kategori Checklist', ['General', 'Venue', 'Catering', 'Dekorasi', 'Fotografer', 'MUA', 'Undangan', 'Souvenir', 'Acara']],
            'checklist_priority' => ['Prioritas Checklist', ['High', 'Medium', 'Low']],
            'checklist_status' => ['Status Checklist', ['Todo', 'Progress', 'Done']],
            'budget_category' => ['Kategori Budget', ['Venue', 'Catering', 'Dekorasi', 'Fotografer', 'Videografer', 'MUA', 'Busana', 'Souvenir', 'Undangan', 'Transportasi', 'Seserahan', 'Mahar', 'Wedding Cake', 'Wedding Organizer', 'Hiburan', 'MC', 'Lighting', 'Sound System', 'Percetakan', 'Perhiasan', 'Lainnya']],
            'vendor_category' => ['Kategori Vendor', ['Venue', 'Catering', 'Dekorasi', 'Wedding Organizer', 'MUA', 'Fotografer', 'Videografer', 'Hiburan', 'MC', 'Lighting', 'Sound System', 'Transportasi', 'Souvenir', 'Percetakan', 'Wedding Cake', 'Busana', 'Perhiasan']],
            'vendor_status' => ['Status Booking Vendor', ['Not Contacted', 'Negotiating', 'Booked', 'Completed', 'Cancelled']],
            'guest_category' => ['Kategori Tamu', ['Family', 'Friend', 'Office', 'VIP', 'Neighbor']],
            'guest_status' => ['Status Kehadiran (RSVP)', ['Pending', 'Attend', 'Decline']],
            'moodboard_category' => ['Kategori Moodboard', ['Dekorasi', 'Pelaminan', 'Gaun Pengantin', 'Suit', 'Makeup', 'Undangan', 'Souvenir', 'Table Setting']],
            'contract_status' => ['Status Kontrak Vendor', ['Draft', 'Signed', 'Waiting', 'Completed', 'Cancelled']],
            'payment_status' => ['Status Pembayaran', ['Belum Bayar', 'DP', 'Cicilan', 'Lunas']],
            'gift_type' => ['Jenis Hadiah', ['Cash', 'Barang']],
        ];

        foreach ($defaultDropdowns as $groupKey => [$groupName, $options]) {
            foreach ($options as $order => $val) {
                \App\Models\DropdownOption::firstOrCreate([
                    'group_key' => $groupKey,
                    'option_value' => $val,
                ], [
                    'group_name' => $groupName,
                    'sort_order' => $order,
                ]);
            }
        }
    }
}
