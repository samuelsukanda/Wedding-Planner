<?php

namespace Tests\Feature;

use App\Models\Notification;
use App\Models\DropdownOption;
use App\Models\Setting;
use App\Models\UpgradeRequest;
use App\Models\User;
use App\Models\Vendor;
use App\Models\VendorCatalog;
use App\Models\Wedding;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PlanTest extends TestCase
{
    use RefreshDatabase;

    /** @return array{0: Wedding, 1: User} */
    private function couple(string $plan = 'free'): array
    {
        $wedding = Wedding::create([
            'bride_name' => 'Siti',
            'groom_name' => 'Budi',
            'wedding_date' => '2026-12-12',
            'total_budget' => 100000000,
            'plan' => $plan,
        ]);

        $user = User::factory()->create([
            // Pakai id wedding supaya unik — satu test memanggil helper ini
            // dua kali untuk membandingkan status Free dan Pro.
            'email' => "pasangan+{$wedding->id}@example.com",
            'wedding_id' => $wedding->id,
        ]);

        return [$wedding, $user];
    }

    private static int $superadminCount = 0;

    private function superadmin(): User
    {
        return User::factory()->create([
            // Satu test bisa butuh dua superadmin sekaligus (misal saat
            // mencoba tolak tanpa catatan, lalu mengulangi dengan catatan),
            // jadi emailnya harus unik per panggilan.
            'email' => 'admin+' . ++self::$superadminCount . '@example.com',
            'is_superadmin' => true,
        ]);
    }

    public function test_free_couple_can_open_moodboard_up_to_the_quota()
    {
        [$wedding, $user] = $this->couple();

        $this->actingAs($user)->get('/moodboards')->assertOk()->assertSee('0 / 3 item terpakai');

        // Form moodboard minimal: kategori + judul.
        $payload = ['category' => 'Dekorasi', 'title' => 'Referensi'];

        foreach (range(1, 3) as $i) {
            $this->actingAs($user)
                ->post('/moodboards', $payload + ['title' => "Referensi {$i}"])
                ->assertRedirect(route('moodboards.index'));
        }

        $this->assertSame(3, $wedding->moodboards()->count());
    }

    public function test_free_couple_is_blocked_at_the_quota_and_frees_a_slot_on_delete()
    {
        [$wedding, $user] = $this->couple();
        $payload = ['category' => 'Dekorasi', 'title' => 'Referensi'];

        foreach (range(1, 3) as $i) {
            $this->actingAs($user)->post('/moodboards', $payload + ['title' => "Referensi {$i}"]);
        }

        // Keempatnya ditolak, jumlah item tidak bertambah.
        $this->actingAs($user)
            ->from('/moodboards')
            ->post('/moodboards', $payload + ['title' => 'Keempat'])
            ->assertRedirect('/moodboards')
            ->assertSessionHas('error');

        $this->assertSame(3, $wedding->moodboards()->count());

        // Hapus satu, slot langsung tersedia lagi.
        $this->actingAs($user)->delete('/moodboards/' . $wedding->moodboards()->first()->id);

        $this->actingAs($user)
            ->post('/moodboards', $payload + ['title' => 'Setelah hapus'])
            ->assertRedirect(route('moodboards.index'));

        $this->assertSame(3, $wedding->moodboards()->count());
    }

    public function test_quota_comes_from_admin_settings()
    {
        Setting::put('moodboard_free_limit', '5');

        [$wedding, $user] = $this->couple();
        $payload = ['category' => 'Dekorasi', 'title' => 'Referensi'];

        foreach (range(1, 5) as $i) {
            $this->actingAs($user)->post('/moodboards', $payload + ['title' => "Referensi {$i}"]);
        }

        $this->assertSame(5, $wedding->moodboards()->count());

        $this->actingAs($user)
            ->post('/moodboards', $payload + ['title' => 'Keenam'])
            ->assertSessionHas('error');

        $this->assertSame(5, $wedding->moodboards()->count());
    }

    public function test_pro_couple_is_not_limited()
    {
        [$wedding, $user] = $this->couple('pro');
        $payload = ['category' => 'Dekorasi', 'title' => 'Referensi'];

        foreach (range(1, 5) as $i) {
            $this->actingAs($user)->post('/moodboards', $payload + ['title' => "Referensi {$i}"]);
        }

        $this->actingAs($user)->get('/moodboards')->assertOk()->assertSee('Tanpa batas');
        $this->assertSame(5, $wedding->moodboards()->count());
    }

    public function test_couple_over_quota_keeps_access_to_existing_items()
    {
        // Batas diturunkan lewat admin di tengah jalan: item yang sudah ada
        // tidak boleh hilang atau terkunci, pasangan hanya tidak bisa menambah.
        Setting::put('moodboard_free_limit', '1');

        [$wedding, $user] = $this->couple();

        foreach (range(1, 3) as $i) {
            $wedding->moodboards()->create(['category' => 'Dekorasi', 'title' => "Referensi {$i}"]);
        }

        $this->actingAs($user)
            ->get('/moodboards')
            ->assertOk()
            ->assertSee('Referensi 1')
            ->assertSee('3 / 1 item terpakai');

        $this->actingAs($user)
            ->post('/moodboards', ['category' => 'Dekorasi', 'title' => 'Baru'])
            ->assertSessionHas('error');

        $this->assertSame(3, $wedding->moodboards()->count());
    }

    public function test_free_couple_is_redirected_from_vendor_directory()
    {
        [$wedding] = $this->couple();

        $vendor = VendorCatalog::create([
            'name' => 'The Apurva Kempinski Bali',
            'slug' => 'apurva',
            'category' => 'Hotel Wedding Venue',
            'source_url' => 'https://weddingku.com/vendor/apurva',
        ]);

        // Semua gerbang ikut terkunci: daftar, detail, simpan ke daftar, review.
        foreach ([
            '/cari-vendor',
            '/cari-vendor/' . $vendor->slug,
        ] as $path) {
            $this->actingAs($wedding->users->first())
                ->get($path)
                ->assertRedirect(route('upgrade.index'));
        }

        $this->actingAs($wedding->users->first())
            ->post('/cari-vendor/' . $vendor->slug)
            ->assertRedirect(route('upgrade.index'));

        $this->assertSame(0, Vendor::count());
    }

    public function test_pro_couple_can_open_vendor_directory()
    {
        [$wedding] = $this->couple('pro');

        $vendor = VendorCatalog::create([
            'name' => 'The Apurva Kempinski Bali',
            'slug' => 'apurva',
            'category' => 'Hotel Wedding Venue',
            'source_url' => 'https://weddingku.com/vendor/apurva',
        ]);

        $user = $wedding->users->first();

        $this->actingAs($user)->get('/cari-vendor')->assertOk();
        $this->actingAs($user)->get('/cari-vendor/' . $vendor->slug)->assertOk();
    }

    public function test_vendor_card_prompts_free_couple_to_upgrade()
    {
        [, $freeUser] = $this->couple();

        // Kartu tetap tampil (pintu upsell), tapi tujuannya ke halaman upgrade.
        $this->actingAs($freeUser)->get('/vendors')
            ->assertOk()
            ->assertSee('Cari Vendor')
            ->assertSee('Upgrade ke Pro');

        [, $proUser] = $this->couple('pro');
        $this->flushSession();
        $this->actingAs($proUser)->get('/vendors')
            ->assertOk()
            ->assertSee('Cari Vendor')
            ->assertDontSee('Upgrade ke Pro');
    }

    public function test_upgrade_page_is_a_two_step_flow()
    {
        Setting::put('pro_price', '55000');
        Setting::put('pro_bank', 'BCA 1234567890');
        Setting::put('admin_whatsapp', '6281234567890');

        [, $user] = $this->couple();

        $this->actingAs($user)->get('/upgrade')
            ->assertOk()
            // Langkah 1: hero + manfaat + tombol CTA.
            ->assertSee('Meet to Marry Pro')
            ->assertSee('Recommended')
            ->assertSee('Yang kamu dapat')
            ->assertSee('Moodboard tanpa batas jumlah item')
            ->assertSee('Cari Vendor')
            ->assertSee('Upgrade ke Pro · Rp 55.000')
            // Harga coret tidak ada karena setting kosong.
            ->assertDontSee('line-through')
            // Ringkasan + rekening + form ada di dalam modal yang baru tampil
            // setelah CTA diklik, jadi tetap harus bisa dirender tanpa error.
            ->assertSee('Ringkasan Pembelian')
            ->assertSee('Rekening Tujuan')
            ->assertSee('Bukti Transfer')
            ->assertSee('Kirim Bukti · Rp 55.000')
            // Pola modal harus sama dengan modal Tambah Data modul lain.
            ->assertSee('x-outside="modalOpen = false"', false)
            ->assertSee('keydown.escape.window="modalOpen = false"', false);
    }

    public function test_struck_through_price_only_shows_when_higher_than_selling_price()
    {
        [, $user] = $this->couple();

        // Kosong atau lebih kecil dari harga jual = tidak ada coretan yang menyesatkan.
        Setting::put('pro_price', '55000');
        Setting::put('pro_price_before', '40000');
        $this->actingAs($user)->get('/upgrade')->assertDontSee('line-through');

        $this->flushSession();
        Setting::put('pro_price_before', '149000');
        $this->actingAs($user)->get('/upgrade')
            ->assertOk()
            ->assertSee('line-through')
            ->assertSee('Rp 149.000');
    }

public function test_pending_state_shows_verification_card_with_refresh_and_contact()
    {
        Storage::fake('public');
        Setting::put('pro_price', '55000');
        Setting::put('admin_whatsapp', '6281234567890');

        [, $user] = $this->couple();
        $this->actingAs($user)->post('/upgrade', ['proof' => UploadedFile::fake()->image('bukti.jpg')]);

        $this->actingAs($user)->get('/upgrade')
            ->assertOk()
            ->assertSee('Pembayaran Sedang Diverifikasi')
            ->assertSee('Admin sedang memeriksa pembayaran Pro kamu.')
            ->assertSee('Rp 55.000')
            ->assertSee('Menunggu Aktivasi')
            ->assertSee('Refresh Status')
            ->assertSee('Kontak Admin')
            ->assertSee('wa.me/6281234567890', false)
            // Teks "kamu akan menerima email" dihapus: app ini tidak mengirim email.
            ->assertDontSee('kamu akan menerima email');
    }

    public function test_pending_state_hides_contact_button_when_admin_wa_missing()
    {
        Storage::fake('public');
        Setting::put('pro_price', '55000');

        [, $user] = $this->couple();
        $this->actingAs($user)->post('/upgrade', ['proof' => UploadedFile::fake()->image('bukti.jpg')]);

        $this->actingAs($user)->get('/upgrade')
            ->assertOk()
            ->assertSee('Refresh Status')
            ->assertDontSee('Kontak Admin');
    }

    public function test_upgrade_page_shows_price_and_bank_from_settings()
    {
        Setting::put('pro_price', '249000');
        Setting::put('pro_bank', 'BCA 1234567890 a/n Wedding Planner');
        Setting::put('admin_whatsapp', '6281234567890');

        [, $user] = $this->couple();

        $this->actingAs($user)->get('/upgrade')
            ->assertOk()
            ->assertSee('249.000')
            ->assertSee('BCA 1234567890')
            ->assertSee('wa.me/6281234567890', false);
    }

    public function test_pro_couple_is_redirected_away_from_upgrade_page()
    {
        [, $user] = $this->couple('pro');

        $this->actingAs($user)->get('/upgrade')->assertRedirect(route('admin.index'));
    }

    public function test_profile_shows_plan_badge()
    {
        [, $freeUser] = $this->couple();
        $this->actingAs($freeUser)->get('/admin')
            ->assertOk()
            ->assertSee('Status Akun')
            ->assertSee('FREE')
            ->assertSee('Upgrade ke Pro');

        $this->flushSession();
        [, $proUser] = $this->couple('pro');
        $this->actingAs($proUser)->get('/admin')
            ->assertOk()
            ->assertSee('Status Akun')
            ->assertSee('PRO')
            ->assertDontSee('Upgrade ke Pro');
    }

    public function test_upload_proof_creates_pending_request()
    {
        Storage::fake('public');

        Setting::put('pro_price', '249000');
        [$wedding, $user] = $this->couple();

        $this->actingAs($user)
            ->post('/upgrade', ['proof' => UploadedFile::fake()->image('bukti.jpg')])
            ->assertRedirect(route('upgrade.index'));

        $request = UpgradeRequest::sole();

        $this->assertSame('pending', $request->status);
        $this->assertSame('pro', $request->plan);
        // Harga direkam saat request dibuat, bukan diambil ulang saat disimpan.
        $this->assertSame(249000, $request->amount);
        $this->assertSame($wedding->id, $request->wedding_id);
        Storage::disk('public')->assertExists($request->proof_path);
    }

    public function test_second_request_is_blocked_while_one_is_pending()
    {
        Storage::fake('public');
        Setting::put('pro_price', '249000');

        [, $user] = $this->couple();

        $this->actingAs($user)->post('/upgrade', ['proof' => UploadedFile::fake()->image('a.jpg')]);

        $this->actingAs($user)
            ->from('/upgrade')
            ->post('/upgrade', ['proof' => UploadedFile::fake()->image('b.jpg')])
            ->assertRedirect('/upgrade')
            ->assertSessionHas('error');

        $this->assertSame(1, UpgradeRequest::count());
    }

    public function test_approve_activates_pro_and_notifies_couple()
    {
        Storage::fake('public');
        [$wedding, $user] = $this->couple();

        $this->actingAs($user)->post('/upgrade', ['proof' => UploadedFile::fake()->image('bukti.jpg')]);
        $request = UpgradeRequest::sole();

        $admin = $this->superadmin();
        $this->actingAs($admin)->post("/admin/upgrade-requests/{$request->id}/approve");

        $this->assertSame('pro', $wedding->fresh()->plan);
        $this->assertSame('approved', $request->fresh()->status);
        $this->assertTrue(Notification::where('wedding_id', $wedding->id)->exists());
    }

    public function test_reject_requires_admin_note()
    {
        Storage::fake('public');
        [$wedding, $user] = $this->couple();

        $this->actingAs($user)->post('/upgrade', ['proof' => UploadedFile::fake()->image('bukti.jpg')]);
        $request = UpgradeRequest::sole();

        $this->actingAs($this->superadmin())
            ->post("/admin/upgrade-requests/{$request->id}/reject")
            ->assertSessionHasErrors('admin_note');

        $this->assertSame('pending', $request->fresh()->status);

        $this->actingAs($this->superadmin())
            ->post("/admin/upgrade-requests/{$request->id}/reject", ['admin_note' => 'Bukti tidak terbaca'])
            ->assertSessionHasNoErrors();

        $this->assertSame('rejected', $request->fresh()->status);
        $this->assertSame('free', $wedding->fresh()->plan);
    }

    public function test_regular_user_cannot_reach_admin_pages()
    {
        [, $user] = $this->couple();

        $this->actingAs($user)->get('/admin/upgrade-requests')->assertForbidden();
        $this->actingAs($user)->get('/admin/pro-settings')->assertForbidden();
    }

    public function test_settings_accept_formatted_money_and_phone_input()
    {
        $this->actingAs($this->superadmin())
            ->put('/admin/pro-settings', [
                // Persis seperti yang terkirim browser: field uang & telepon
                // sudah diformat, jadi controller wajib membersihkan sendiri.
                'pro_price' => 'Rp 249.000',
                'pro_price_before' => 'Rp 1.500.000',
                'pro_bank' => 'BCA 1234567890',
                'admin_whatsapp' => '6281-2345-6789',
                'moodboard_free_limit' => 5,
            ])
            ->assertSessionHasNoErrors();

        $this->assertSame('249000', Setting::get('pro_price'));
        $this->assertSame('1500000', Setting::get('pro_price_before'));
        $this->assertSame('628123456789', Setting::get('admin_whatsapp'));
    }

    public function test_pro_settings_page_renders_formatted_values()
    {
        Setting::put('pro_price', '55000');
        Setting::put('pro_price_before', '150000');
        Setting::put('admin_whatsapp', '6281234567890');

        $this->actingAs($this->superadmin())
            ->get('/admin/pro-settings')
            ->assertOk()
            ->assertSee('Rp 55.000')
            ->assertSee('Rp 150.000')
            ->assertSee('6281-2345-6789')
            ->assertSee('data-format="money"', false);
    }

public function test_reject_button_lives_inside_the_form_holding_the_reason()
    {
        Storage::fake('public');
        [, $user] = $this->couple();
        $this->actingAs($user)->post('/upgrade', ['proof' => UploadedFile::fake()->image('bukti.jpg')]);

        $html = $this->actingAs($this->superadmin())
            ->get('/admin/upgrade-requests')
            ->assertOk()
            ->getContent();

        $dom = new \DOMDocument();
        @$dom->loadHTML('<?xml encoding="utf-8" ?>' . $html);
        $xpath = new \DOMXPath($dom);

        $notes = $xpath->query('//input[@name="admin_note"]');
        $this->assertGreaterThan(0, $notes->length, 'Kolom alasan tidak ada di halaman.');

        foreach ($notes as $note) {
            $form = $note->parentNode;
            while ($form && $form->nodeName !== 'form') {
                $form = $form->parentNode;
            }

            $this->assertNotNull($form, 'Kolom alasan tidak berada di dalam form.');

            // Tombol Tolak di form terpisah berarti admin_note tidak ikut
            // terkirim dan penolakan selalu gagal divalidasi.
            $this->assertGreaterThan(
                0,
                $xpath->query('.//button[@type="submit"]', $form)->length,
                'Tombol Tolak tidak ada di form yang sama dengan kolom alasannya.'
            );
        }
    }

public function test_reject_field_offers_default_reason_templates()
    {
        Storage::fake('public');
        [, $user] = $this->couple();

        $this->actingAs($user)->post('/upgrade', ['proof' => UploadedFile::fake()->image('bukti.jpg')]);

        // Belum ada isinya di Master Data, jadi harus jatuh ke bawaan.
        $this->actingAs($this->superadmin())
            ->get('/admin/upgrade-requests')
            ->assertOk()
            ->assertSee('data-fill-target="reject-note-', false)
            ->assertSee('Bukti transfer tidak terbaca, silakan kirim ulang yang lebih jelas')
            ->assertSee('Nominal transfer tidak sesuai dengan harga paket Pro')
            // Datalist membuka panel selebar inputnya sehingga menimpa card
            // di bawahnya, jadi tidak boleh dipakai lagi di sini.
            ->assertDontSee('<datalist', false);
    }

    public function test_reject_reason_templates_come_from_master_data()
    {
        Storage::fake('public');
        DropdownOption::create([
            'group_key' => 'upgrade_reject_reason',
            'group_name' => 'Alasan Tolak Upgrade Pro',
            'option_value' => 'Rekening tujuan salah Transfer',
        ]);

        [, $user] = $this->couple();
        $this->actingAs($user)->post('/upgrade', ['proof' => UploadedFile::fake()->image('bukti.jpg')]);

        $this->actingAs($this->superadmin())
            ->get('/admin/upgrade-requests')
            ->assertOk()
            ->assertSee('Rekening tujuan salah Transfer')
            // Defaults tergantikan begitu superadmin mengisi sendiri.
            ->assertDontSee('Nominal transfer tidak sesuai dengan harga paket Pro');
    }

public function test_admin_pages_render_for_superadmin()
    {
        $this->actingAs($this->superadmin())
            ->get('/admin/upgrade-requests')
            ->assertOk()
            ->assertSee('Request Upgrade Pro');

        $this->actingAs($this->superadmin())
            ->get('/admin/pro-settings')
            ->assertOk()
            ->assertSee('Pengaturan Pro');
    }

    public function test_superadmin_can_delete_a_request_and_its_proof_file()
    {
        Storage::fake('public');
        [$wedding, $user] = $this->couple();

        $this->actingAs($user)->post('/upgrade', ['proof' => UploadedFile::fake()->image('bukti.jpg')]);
        $request = UpgradeRequest::sole();
        $proofPath = $request->proof_path;

        Storage::disk('public')->assertExists($proofPath);

        $this->actingAs($this->superadmin())
            ->delete("/admin/upgrade-requests/{$request->id}")
            ->assertSessionHas('success');

        $this->assertSame(0, UpgradeRequest::count());
        // Bukti ikut hilang, kalau tidak file yatim menumpuk di storage.
        Storage::disk('public')->assertMissing($proofPath);
    }

public function test_superadmin_can_save_pro_settings()
    {
        $this->actingAs($this->superadmin())
            ->put('/admin/pro-settings', [
                'pro_price' => 249000,
                'pro_bank' => 'BCA 1234567890 a/n Wedding Planner',
                'admin_whatsapp' => '6281234567890',
                'moodboard_free_limit' => 3,
            ])
            ->assertRedirect(route('admin.pro-settings.edit'));

        $this->assertSame('249000', Setting::get('pro_price'));
        $this->assertSame('6281234567890', Setting::get('admin_whatsapp'));
        $this->assertSame('3', Setting::get('moodboard_free_limit'));
    }
}