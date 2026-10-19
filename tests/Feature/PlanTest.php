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

    public function test_free_couple_sees_the_locked_moodboard_page()
    {
        [$wedding, $user] = $this->couple();

        // Item sisa dari pasangan yang sebelumnya masih Free tidak boleh bocor
        // ke halaman terkunci.
        $wedding->moodboards()->create(['category' => 'Dekorasi', 'title' => 'Referensi lama']);

        $this->actingAs($user)->get('/moodboards')
            ->assertOk()
            ->assertSee('Upgrade ke Pro')
            ->assertSee(route('upgrade.index'))
            ->assertDontSee('Referensi lama')
            // Tidak ada form maupun tombol tambah: paket Free tidak punya
            // akses ke apa pun di modul ini.
            ->assertDontSee('Tambah Moodboard');
    }

    public function test_locked_moodboard_page_sells_the_feature_without_a_lying_price()
    {
        Setting::put('pro_price', '55000');

        [, $user] = $this->couple();

        $html = $this->actingAs($user)->get('/moodboards')->assertOk()->getContent();

        $this->assertStringContainsString('FITUR PRO', $html);
        $this->assertStringContainsString('Buka Akses Moodboard Tanpa Batas', $html);
        $this->assertStringContainsString('Unlimited Boards', $html);
        $this->assertStringContainsString('Kolaborasi Partner &amp; Vendor', $html);
        $this->assertStringContainsString('Palette &amp; Template Eksklusif', $html);

        // Aset gambar disimpan di repo, bukan hotlink ke Pinterest: link CDN
        // bisa berubah atau diblokir. Test ini menahan aset yang hilang supaya
        // kotak kosong tidak baru ketahuan setelah deploy.
        $this->assertStringNotContainsString('moodboard-rustic-table', $html);

        // Enam foto Pexels (Pexels License: bebas komersial, tanpa wajib
        // atribusi), disimpan di repo supaya tidak bergantung pada CDN pihak
        // ketiga yang bisa diblokir atau berubah.
        foreach ([
            'pexels-cake-floral', 'pexels-cake-elegant', 'pexels-flatlay-seal',
            'pexels-ceremony-flowers', 'pexels-bridal-flatlay', 'pexels-photo-layout',
        ] as $asset) {
            $this->assertStringContainsString("img/$asset.jpg", $html);
            $this->assertFileExists(public_path("img/$asset.jpg"));
        }

        // Foto Pinterest sebelumnya sudah tidak dipakai; halaman tidak boleh
        // masih merujuk ke sana.
        $this->assertStringNotContainsString('img/moodboard-rustic-', $html);

        // Kartu kiri dan kanan sama-sama memuat satu foto melebar penuh (h-full)
        // dan dua foto setengah lebar (aspect-square). Sisi penuhnya dibalik:
        // di kartu kiri foto penuh mendahului kolom setengah lebarnya, di
        // kartu kanan sebaliknya.
        $leftFull = strpos($html, 'pexels-cake-floral.jpg');
        $leftHalves = strpos($html, 'pexels-cake-elegant.jpg');
        $rightFull = strpos($html, 'pexels-photo-layout.jpg');
        $rightHalves = strpos($html, 'pexels-bridal-flatlay.jpg');

        $this->assertLessThan($leftHalves, $leftFull);
        $this->assertGreaterThan($rightHalves, $rightFull);

        // Foto penuh membentang setinggi kolomnya, bukan kotak persegi.
        $this->assertMatchesRegularExpression('/pexels-cake-floral\.jpg[^>]*h-full/', $html);
        $this->assertMatchesRegularExpression('/pexels-photo-layout\.jpg[^>]*h-full/', $html);

        // Ada gerak halus saat hover, dan dimatikan kalau pengguna meminta
        // pengurangan animasi di sistemnya.
        $this->assertStringContainsString('hover:-translate-y-2', $html);
        $this->assertStringContainsString('hover:rotate-0', $html);
        $this->assertStringContainsString('motion-reduce:transform-none', $html);

        // Muat satu layar tanpa harus scroll ke bawah.
        $this->assertStringContainsString('min-h-[calc(100vh_-_13rem)]', $html);

        // Ukuran kartu mengikuti tinggi layar lewat clamp. Angkanya sengaja
        // dikunci di sini: versi sebelumnya clamp(6rem, 15vh, 10rem) membuat
        // kartunya mengecil jadi sekitar 93px dan halaman terlihat kosong,
        // padahal ruangnya masih plenty.
        $this->assertSame(3, substr_count($html, 'w-[clamp(7rem,20vh,13rem)]'));
        $this->assertStringNotContainsString('clamp(6rem,15vh', $html);

        // Kartu tengah: palet putih-krem muda, gembok coklat, badge PRO coklat.
        foreach (['FFFFFF', 'FDFAF5', 'F6EEE3', 'EADFCB'] as $cream) {
            $this->assertStringContainsString($cream, $html);
        }
        $this->assertStringContainsString('text-[#8A6A45]', $html);
        $this->assertStringContainsString('fa-lock', $html);

        // Paket Pro di aplikasi ini dibeli sekali dan berlaku seumur akun.
        // "/bulan" dan "batalkan kapan saja" akan jadi klaim iklan palsu tepat
        // di halaman yang paling dipercaya saat pengguna memutuskan bayar.
        // Halaman ini tidak menampilkan harga sama sekali; harga hanya muncul
        // di /upgrade yang menampilkannya dari app_settings.
        $this->assertStringNotContainsString('/bulan', $html);
        $this->assertStringNotContainsString('Batalkan kapan saja', $html);
        $this->assertStringNotContainsString('Berlangganan', $html);
        $this->assertStringNotContainsString('Sekali bayar', $html);
    }

    public function test_free_couple_cannot_create_items()
    {
        [$wedding, $user] = $this->couple();

        $this->actingAs($user)
            ->post('/moodboards', ['category' => 'Dekorasi', 'title' => 'Referensi'])
            ->assertRedirect(route('moodboards.index'));

        $this->assertSame(0, $wedding->moodboards()->count());
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

    public function test_free_couple_cannot_edit_or_delete_leftover_items()
    {
        // Pasangan yang sebelumnya masih Free bisa saja sudah punya item.
        // Membuka kunci mati tidak boleh menyisakan jalur tulis di belakang
        // halaman terkunci, jadi edit dan hapus ikut tertutup.
        [$wedding, $user] = $this->couple();
        $item = $wedding->moodboards()->create(['category' => 'Dekorasi', 'title' => 'Referensi lama']);

        $this->actingAs($user)
            ->put('/moodboards/' . $item->id, ['category' => 'Dekorasi', 'title' => 'Diubah'])
            ->assertRedirect(route('moodboards.index'));
        $this->assertSame('Referensi lama', $item->fresh()->title);

        $this->actingAs($user)
            ->delete('/moodboards/' . $item->id)
            ->assertRedirect(route('moodboards.index'));
        $this->assertSame(1, $wedding->moodboards()->count());
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
            // Langkah 1: hero + manfaat + tombol CTA. Nama brand di hero
            // sengaja tidak di-assert di sini supaya pergantian nama tidak
            // membuat test ini gagal; yang dijaga strukturnya.
            ->assertSee('Recommended')
            ->assertSee('Yang kamu dapat')
            ->assertSee('Moodboard tanpa batas jumlah item')
            ->assertSee('Cari Vendor')
            // fa-sparkles adalah icon FontAwesome Pro yang tidak tampil di
            // versi gratis, jadi CTA memakai fa-wand-magic-sparkles.
            ->assertSee('fa-wand-magic-sparkles', false)
            ->assertSee('Upgrade ke Pro')
            // Harga coret tidak ada karena setting kosong.
            ->assertDontSee('line-through')
            // Ringkasan + rekening + form ada di dalam modal yang baru tampil
            // setelah CTA diklik, jadi tetap harus bisa dirender tanpa error.
            ->assertSee('Ringkasan Pembelian')
            ->assertSee('Rekening Tujuan')
            ->assertSee('Bukti Transfer')
            ->assertSee('Kirim Bukti')
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
            ])
            ->assertRedirect(route('admin.pro-settings.index'));

        $this->assertSame('249000', Setting::get('pro_price'));
        $this->assertSame('6281234567890', Setting::get('admin_whatsapp'));
    }

    public function test_sidebar_brand_shows_the_product_name_not_the_couple_name()
    {
        [$wedding, $user] = $this->couple();

        $html = $this->actingAs($user)->get('/')->assertOk()->getContent();

        // Baris di bawah logo adalah merek: "Nupsera" lalu "Wedding & Moments".
        // Blade memecah baris di dalam <p>, jadi teksnya jadi "Wedding\n& Moments".
        $this->assertMatchesRegularExpression('/<h1[^>]*>\s*Nupsera\s*<\/h1>/', $html);
        $this->assertMatchesRegularExpression('/Wedding\s+&amp;\s+Moments/', $html);

        // "Wedding Planner" tidak boleh lagi jadi baris brand di sidebar.
        // Nama pasangan ($brandName) sudah tampil di header utama, jadi
        // mengulangnya di sidebar cuma memakan ruang.
        $this->assertDoesNotMatchRegularExpression('/Wedding\s+Planner<\/p>/', $html);
    }
}