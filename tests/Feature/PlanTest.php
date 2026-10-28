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

    public function test_free_couple_sees_photobooth_purchase_page(): void
    {
        [, $user] = $this->couple();

        // Photobooth adalah add-on berdiri sendiri, bukan bagian paket Pro,
        // jadi paket Free tidak dialihkan ke /upgrade tapi diberi halaman
        // pembelian.
        $this->actingAs($user)
            ->get('/photobooth')
            ->assertOk()
            ->assertSee('Buka Akses Photobooth Sekarang');
    }

    public function test_pro_couple_can_open_photobooth_menu(): void
    {
        [, $user] = $this->couple('pro');

        // Menu Photobooth terbuka untuk semua paket: photobooth adalah add-on
        // berdiri sendiri, jadi belum aktif pun halamannya harus tampil.
        $this->actingAs($user)
            ->get('/photobooth')
            ->assertOk()
            ->assertSee('Buka Akses Photobooth Sekarang')
            ->assertSee('TERKUNCI');
    }

    public function test_photobooth_sits_under_moodboard_in_creative_sidebar(): void
    {
        [, $user] = $this->couple('pro');
        $html = $this->actingAs($user)->get('/photobooth')->assertOk()->getContent();

        $this->assertLessThan(
            strpos($html, '>Photobooth</span>'),
            strpos($html, '>Keuangan &amp; Dokumen')
        );
        $this->assertLessThan(
            strpos($html, '>Photobooth</span>'),
            strpos($html, '>Moodboard</span>') + strlen('>Moodboard</span>')
        );
    }

    public function test_pro_couple_can_save_photobooth_connection(): void
    {
        [$wedding, $user] = $this->couple('pro');
        $wedding->update(['photobooth_active' => true]);

        $this->actingAs($user)
            ->put('/photobooth', [
                'event_slug' => 'sa2027',
                'api_key' => 'test-api-key',
                'active' => '1',
            ])
            ->assertRedirect(route('photobooth.index'));

        $this->assertSame('sa2027', $wedding->fresh()->photobooth_event_slug);
        $this->assertSame('test-api-key', $wedding->fresh()->photoboothApiKey());
        $this->assertTrue((bool) $wedding->fresh()->photobooth_active);
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

        $this->actingAs($user)->get('/moodboards')->assertOk();
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
        Setting::put('pro_banks', json_encode([
            ['icon' => 'fa-landmark', 'name' => 'BCA', 'number' => '1234567890'],
        ]));

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

public function test_pending_state_shows_verification_card_with_refresh_only()
    {
        Storage::fake('local');
        Setting::put('pro_price', '55000');

        [, $user] = $this->couple();
        $this->actingAs($user)->post('/upgrade', ['proof' => UploadedFile::fake()->image('bukti.jpg')]);

        $this->actingAs($user)->get('/upgrade')
            ->assertOk()
            ->assertSee('Pembayaran Pro Sedang Diverifikasi')
            ->assertSee('Admin sedang memeriksa pembayaran kamu.')
            ->assertSee('Rp 55.000')
            ->assertSee('Menunggu Aktivasi')
            ->assertSee('Refresh Status')
            // Kontak WA admin sengaja dihapus dari semua keadaan halaman.
            ->assertDontSee('Kontak Admin')
            ->assertDontSee('wa.me', false)
            // Teks "kamu akan menerima email" dihapus: app ini tidak mengirim email.
            ->assertDontSee('kamu akan menerima email');
    }

    public function test_upgrade_page_shows_price_and_bank_with_icon_from_settings()
    {
        Setting::put('pro_price', '249000');
        Setting::put('pro_banks', json_encode([
            ['icon' => 'fa-landmark', 'name' => 'BCA', 'number' => '1234567890'],
        ]));

        [, $user] = $this->couple();

        $this->actingAs($user)->get('/upgrade')
            ->assertOk()
            ->assertSee('249.000')
            ->assertSee('BCA')
            ->assertSee('1234567890')
            // Ikon dipilih superadmin per rekening, bukan class tetap di Blade.
            ->assertSee('fa-landmark', false)
            ->assertDontSee('wa.me', false);
    }

    /** Data lama berupa teks tetap tampil, supaya halaman bayar tidak kosong. */
    public function test_legacy_plain_text_bank_setting_still_renders()
    {
        Setting::put('pro_price', '55000');
        Setting::put('pro_bank', "JAGO - 104273096017\nBCA - 5410928045");

        [, $user] = $this->couple();

        $this->actingAs($user)->get('/upgrade')
            ->assertOk()
            ->assertSee('104273096017')
            ->assertSee('5410928045');
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
        Storage::disk('local')->assertExists($request->proof_path);
    }

    public function test_second_request_is_blocked_while_one_is_pending()
    {
        Storage::fake('local');
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

    public function test_admin_cannot_reject_an_already_approved_request(): void
    {
        Storage::fake('local');
        [$wedding, $user] = $this->couple();
        $this->actingAs($user)->post('/upgrade', ['proof' => UploadedFile::fake()->image('bukti.jpg')]);
        $request = UpgradeRequest::sole();
        $request->update(['status' => 'approved']);

        $this->actingAs($this->superadmin())
            ->post("/admin/upgrade-requests/{$request->id}/reject", ['admin_note' => 'Tidak valid'])
            ->assertStatus(409);
        $this->assertSame('approved', $request->fresh()->status);
    }

    public function test_superadmin_can_read_proof_but_regular_user_cannot(): void
    {
        Storage::fake('local');
        [, $user] = $this->couple();
        $this->actingAs($user)->post('/upgrade', ['proof' => UploadedFile::fake()->image('bukti.jpg')]);
        $request = UpgradeRequest::sole();

        $this->actingAs($user)->get(route('admin.upgrade-requests.proof', $request))->assertForbidden();
        $this->actingAs($this->superadmin())->get(route('admin.upgrade-requests.proof', $request))->assertOk();
    }

    public function test_settings_accept_formatted_money_input()
    {
        $this->actingAs($this->superadmin())
            ->put('/admin/pro-settings', [
                // Persis seperti yang terkirim browser: field uang sudah
                // diformat, jadi controller wajib membersihkan sendiri.
                'pro_price' => 'Rp 249.000',
                'pro_price_before' => 'Rp 1.500.000',
                'banks' => [
                    ['icon' => 'fa-landmark', 'name' => 'BCA', 'number' => '1234567890'],
                ],
                'moodboard_free_limit' => 5,
            ])
            ->assertSessionHasNoErrors();

        $this->assertSame('249000', Setting::get('pro_price'));
        $this->assertSame('1500000', Setting::get('pro_price_before'));
    }

    public function test_superadmin_can_upload_qris_and_payment_note()
    {
        Storage::fake('public');

        $banks = [
            ['icon' => 'fa-landmark', 'name' => 'BCA', 'number' => '1234567890'],
        ];

        $this->actingAs($this->superadmin())
            ->put('/admin/pro-settings', [
                'pro_price' => 249000,
                'banks' => $banks,
                'pro_payment_note' => 'Kirim sesuai nominal lalu unggah bukti di sini.',
                'pro_qris' => UploadedFile::fake()->image('qris.png'),
            ])
            ->assertRedirect(route('admin.pro-settings.index'));

        $path = Setting::get('pro_qris_path');
        $this->assertStringStartsWith('qris/', $path);
        Storage::disk('public')->assertExists($path);
        $this->assertSame('Kirim sesuai nominal lalu unggah bukti di sini.', Setting::get('pro_payment_note'));

        // Unggah kedua menimpa file pertama supaya disk tidak menumpuk.
        $this->actingAs($this->superadmin())
            ->put('/admin/pro-settings', [
                'pro_price' => 249000,
                'banks' => $banks,
                'pro_qris' => UploadedFile::fake()->image('qris-lagi.png'),
            ])
            ->assertRedirect(route('admin.pro-settings.index'));

        Storage::disk('public')->assertMissing($path);
        $this->assertStringStartsWith('qris/', Setting::get('pro_qris_path'));
    }

    public function test_upgrade_page_shows_payment_note_and_qris_below_the_banks_heading()
    {
        Storage::fake('public');
        Setting::put('pro_price', '55000');
        Setting::put('pro_banks', json_encode([
            ['icon' => 'fa-landmark', 'name' => 'BCA', 'number' => '5410928045'],
        ]));
        Setting::put('pro_payment_note', 'Kirim sesuai nominal lalu unggah bukti di sini.');
        Setting::put('pro_qris_path', 'qris/demo.png');

        [, $user] = $this->couple();

        $html = $this->actingAs($user)->get('/upgrade')->assertOk()->getContent();

        $this->assertStringContainsString('Kirim sesuai nominal lalu unggah bukti di sini.', $html);
        $this->assertStringContainsString('storage/qris/demo.png', $html);

        // Urutan: judul Rekening Tujuan -> deskripsi -> QRIS, ditampilkan
        // tanpa judul "Bayar lewat QRIS" (gambar QRIS saja).
        $this->assertLessThan(
            mb_strpos($html, 'Kirim sesuai nominal'),
            mb_strpos($html, 'Rekening Tujuan'),
        );
        $this->assertLessThan(
            mb_strpos($html, 'qris/demo.png'),
            mb_strpos($html, 'Kirim sesuai nominal'),
        );
        $this->assertStringNotContainsString('Bayar lewat QRIS', $html);
    }

    /** Baris rekening yang dikosongkan tidak boleh memblokir simpan. */
    public function test_empty_bank_rows_are_dropped_before_validation()
    {
        $this->actingAs($this->superadmin())
            ->put('/admin/pro-settings', [
                'pro_price' => 55000,
                'banks' => [
                    ['icon' => 'fa-landmark', 'name' => 'BCA', 'number' => '5410928045'],
                    ['icon' => 'fa-landmark', 'name' => '', 'number' => ''],
                ],
            ])
            ->assertSessionHasNoErrors();

        $this->assertCount(1, json_decode(Setting::get('pro_banks'), true));
    }

    /** Ikon di luar daftar ditolak, jangan sampai class sembarang masuk ke halaman. */
    public function test_unknown_bank_icon_is_rejected()
    {
        $this->actingAs($this->superadmin())
            ->put('/admin/pro-settings', [
                'pro_price' => 55000,
                'banks' => [['icon' => 'fa-skull', 'name' => 'BCA', 'number' => '123']],
            ])
            ->assertSessionHasErrors('banks.0.icon');
    }

    public function test_pro_settings_page_renders_formatted_values()
    {
        Setting::put('pro_price', '55000');
        Setting::put('pro_price_before', '150000');
        Setting::put('pro_banks', json_encode([
            ['icon' => 'fa-wallet', 'name' => 'Jago', 'number' => '104273096017'],
        ]));

        $this->actingAs($this->superadmin())
            ->get('/admin/pro-settings')
            ->assertOk()
            ->assertSee('Rp 55.000')
            ->assertSee('Rp 150.000')
            ->assertSee('data-format="money"', false)
            ->assertSee('104273096017')
            // Nomor WA admin sudah tidak dikelola dari sini.
            ->assertDontSee('Nomor WhatsApp Admin');
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

    public function test_admin_sidebar_shows_add_on_settings_as_flat_menus(): void
    {
        $html = $this->actingAs($this->superadmin())
            ->get('/admin/pro-settings')
            ->assertOk()
            ->getContent();

        // Add-on tidak lagi dropdown: kedua pengaturan jadi menu sendiri.
        $this->assertStringNotContainsString('>Add-on</span>', $html);
        $this->assertStringContainsString('href="https://nupsera.my.id/admin/pro-settings"', $html);
        $this->assertStringContainsString('href="https://nupsera.my.id/admin/photobooth-settings"', $html);
        $this->assertStringContainsString('Pengaturan Pro', $html);
        $this->assertStringContainsString('Pengaturan Photobooth', $html);
        $this->assertStringNotContainsString('x-collapse', $html);
    }

    public function test_superadmin_can_delete_a_request_and_its_proof_file()
    {
        Storage::fake('public');
        [$wedding, $user] = $this->couple();

        $this->actingAs($user)->post('/upgrade', ['proof' => UploadedFile::fake()->image('bukti.jpg')]);
        $request = UpgradeRequest::sole();
        $proofPath = $request->proof_path;

        Storage::disk('local')->assertExists($proofPath);

        $this->actingAs($this->superadmin())
            ->delete("/admin/upgrade-requests/{$request->id}")
            ->assertSessionHas('success');

        $this->assertSame(0, UpgradeRequest::count());
        // Bukti ikut hilang, kalau tidak file yatim menumpuk di storage.
        Storage::disk('local')->assertMissing($proofPath);
    }

public function test_superadmin_can_save_pro_settings()
    {
        $this->actingAs($this->superadmin())
            ->put('/admin/pro-settings', [
                'pro_price' => 249000,
                'banks' => [
                    ['icon' => 'fa-wallet', 'name' => 'Jago', 'number' => '104273096017'],
                    ['icon' => 'fa-landmark', 'name' => 'BCA', 'number' => '5410928045'],
                ],
            ])
            ->assertRedirect(route('admin.pro-settings.index'));

        $this->assertSame('249000', Setting::get('pro_price'));
        $this->assertSame([
            ['icon' => 'fa-wallet', 'name' => 'Jago', 'number' => '104273096017'],
            ['icon' => 'fa-landmark', 'name' => 'BCA', 'number' => '5410928045'],
        ], json_decode(Setting::get('pro_banks'), true));
    }

    public function test_sidebar_brand_shows_the_product_name_not_the_couple_name()
    {
        [$wedding, $user] = $this->couple();

        $html = $this->actingAs($user)->get('/')->assertOk()->getContent();

        // Baris di bawah logo adalah merek: "Nupsera" lalu "Wedding & Moments".
        // "Nupsera" sengaja <div> bukan <h1> agar urutan heading halaman tetap
        // benar (h1 dipakai judul konten). Blade memecah baris di dalam <p>,
        // jadi teksnya jadi "Wedding\n& Moments".
        $this->assertMatchesRegularExpression('/<div[^>]*>\s*Nupsera\s*<\/div>/', $html);
        $this->assertMatchesRegularExpression('/Wedding\s+&amp;\s+Moments/', $html);

        // "Wedding Planner" tidak boleh lagi jadi baris brand di sidebar.
        // Nama pasangan ($brandName) sudah tampil di header utama, jadi
        // mengulangnya di sidebar cuma memakan ruang.
        $this->assertDoesNotMatchRegularExpression('/Wedding\s+Planner<\/p>/', $html);
    }

    public function test_mobile_bottom_navigation_groups_app_menus(): void
    {
        [, $user] = $this->couple();

        $html = $this->actingAs($user)->get('/')->assertOk()->getContent();

        $this->assertStringContainsString('data-mobile-bottom-nav', $html);
        $this->assertStringContainsString('Home', $html);
        $this->assertStringContainsString('Planning', $html);
        $this->assertStringContainsString('Inspirasi', $html);
        $this->assertStringContainsString('Keuangan', $html);
        $this->assertStringContainsString('Saya', $html);
        $this->assertStringContainsString('Laporan &amp; Export', $html);
        $this->assertStringContainsString('Profile', $html);
        $mobileNav = substr($html, strpos($html, 'data-mobile-bottom-nav'));
        $this->assertStringNotContainsString('Keluar', $mobileNav);
        $this->assertStringNotContainsString('Buka Menu Navigation', $mobileNav);
    }

    public function test_dashboard_has_sized_logo_and_no_low_contrast_labels(): void
    {
        [$wedding, $user] = $this->couple();

        $wedding->checklists()->create(['title' => 'Cek hias', 'category' => 'Lainnya', 'is_completed' => false]);

        $html = $this->actingAs($user)->get('/')->getContent();

        // Logo sidebar harus punya dimensi eksplisit (Lighthouse "Images lack width and height").
        $this->assertMatchesRegularExpression(
            '/nupsera-ori\.png[^>]*width="\d+" height="\d+"/',
            $html
        );

        // Label 80%-opacity dan sage sangat terang gagal kontras 4.5:1 di atas putih.
        $this->assertStringNotContainsString('text-[#5F6F5B]/80', $html);
        $this->assertStringNotContainsString('text-[#A3B7A6]', $html);
    }
}
