<?php

namespace Tests\Feature;

use App\Models\Setting;
use App\Models\UpgradeRequest;
use App\Models\User;
use App\Models\Wedding;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PhotoboothTest extends TestCase
{
    use RefreshDatabase;

    /** @return array{0: Wedding, 1: User} */
    private function couple(string $plan = 'pro'): array
    {
        $wedding = Wedding::create([
            'bride_name' => 'Siti',
            'groom_name' => 'Budi',
            'wedding_date' => '2026-12-12',
            'total_budget' => 100000000,
            'plan' => $plan,
        ]);

        $user = User::factory()->create([
            'email' => "pasangan+{$wedding->id}@example.com",
            'wedding_id' => $wedding->id,
        ]);

        return [$wedding, $user];
    }

    /** @return array{0: Wedding, 1: User} */
    private function connected(): array
    {
        [$wedding, $user] = $this->couple();
        $wedding->update(['photobooth_active' => true]);

        $this->actingAs($user)
            ->put('/photobooth', [
                'event_slug' => 'sa2027',
                'api_key' => 'test-key',
                'active' => '1',
            ])
            ->assertRedirect(route('photobooth.index'));

        return [$wedding->fresh(), $user];
    }

    public function test_photobooth_page_offers_frame_text_and_font_picker(): void
    {
        config(['services.photobooth.url' => 'https://photobooth.test']);
        [, $user] = $this->connected();

        Http::fake([
            'https://photobooth.test/api/admin/event' => Http::response([
                'title' => 'Samuel & Angela Wedding',
                'gallery_mode' => 'public',
                'frame' => ['text' => 'Samuel & Angela', 'date' => '27 - 07 - 2027', 'font' => 'Great Vibes'],
            ]),
            'https://photobooth.test/api/admin/photos' => Http::response([]),
        ]);

        $html = $this->actingAs($user)->get('/photobooth')->assertOk()->getContent();

        $this->assertStringContainsString('name="frame_text"', $html);
        $this->assertStringContainsString('name="frame_font"', $html);

        // Empat kelompok gaya tulisan, masing-masing harus punya font contoh.
        foreach (['Caveat', 'Great Vibes', 'Courier Prime', 'Montserrat'] as $font) {
            $this->assertStringContainsString($font, $html);
        }

        // Teks frame default-nya nama pasangan dari data pernikahan.
        $this->assertStringContainsString('Budi &amp; Siti', $html);

        // Tanggal diambil dari data pernikahan, bukan diketik manual.
        $this->assertStringContainsString('12 - 12 - 2026', $html);
    }

    public function test_photobooth_qr_print_shows_only_universal_qr_content(): void
    {
        config(['services.photobooth.url' => 'https://photobooth.test']);
        [, $user] = $this->connected();

        Http::fake([
            'https://photobooth.test/api/admin/event' => Http::response(['title' => 'Samuel & Angela Wedding']),
            'https://photobooth.test/api/admin/photos' => Http::response([]),
        ]);

        $html = $this->actingAs($user)->get('/photobooth')->assertOk()->getContent();

        $this->assertStringContainsString('id="photobooth-qr-print"', $html);
        $this->assertStringContainsString('data-url="https://photobooth.test/e/sa2027"', $html);
        $this->assertMatchesRegularExpression('#https://photobooth\.test/e/sa2027\s*</p>#', $html);
        $this->assertStringContainsString('body * {', $html);
        $this->assertStringContainsString('visibility: hidden !important;', $html);
        $this->assertStringContainsString('requestAnimationFrame(() => window.print())', $html);
        $this->assertStringContainsString('.photobooth-qr-print-button', $html);
        $this->assertMatchesRegularExpression('#Unduh\s+QR PNG#', $html);
        $this->assertStringContainsString('downloadPhotoboothQr()', $html);
        $this->assertStringContainsString("link.download = 'qr-code-sa2027.png'", $html);
        $this->assertStringContainsString('image.data[i + 3] = 0;', $html);
    }

    public function test_qr_link_can_be_copied(): void
    {
        config(['services.photobooth.url' => 'https://photobooth.test']);
        [, $user] = $this->connected();

        Http::fake([
            'https://photobooth.test/api/admin/event' => Http::response(['title' => 'Samuel & Angela Wedding']),
            'https://photobooth.test/api/admin/photos' => Http::response([]),
        ]);

        $html = $this->actingAs($user)->get('/photobooth')->assertOk()->getContent();

        // Link QR bisa diklik untuk menyalin, plus tombol "Salin Link"
        // di sebelahnya dengan status umpan balik.
        $this->assertStringContainsString('data-copy="https://photobooth.test/e/sa2027"', $html);
        $this->assertStringContainsString('photobooth-qr-copy-button', $html);
        $this->assertStringContainsString('photobooth-qr-copy-status', $html);
        $this->assertStringContainsString('Salin Link', $html);
    }

    public function test_photobooth_page_without_event_does_not_render_universal_qr(): void
    {
        [$wedding, $user] = $this->couple();
        $wedding->update(['photobooth_active' => true]);

        $html = $this->actingAs($user)->get('/photobooth')->assertOk()->getContent();

        $this->assertStringNotContainsString('id="photobooth-qr-print"', $html);
        $this->assertStringNotContainsString('id="photobooth-qr"', $html);
    }

    public function test_photo_delete_uses_sweetalert_confirm(): void
    {
        config(['services.photobooth.url' => 'https://photobooth.test']);
        [, $user] = $this->connected();

        Http::fake([
            'https://photobooth.test/api/admin/event' => Http::response(['title' => 'Samuel & Angela Wedding']),
            'https://photobooth.test/api/admin/photos' => Http::response([
                ['id' => 11, 'thumbnail_url' => '', 'image_url' => 'https://photobooth.test/p/11.jpg'],
                ['id' => 12, 'thumbnail_url' => '', 'image_url' => 'https://photobooth.test/p/12.jpg'],
            ]),
        ]);

        $html = $this->actingAs($user)->get('/photobooth')->assertOk()->getContent();

        // Dialog hapus foto memakai helper SweetAlert global (confirmDelete),
        // sama seperti modul lain; confirm() bawaan browser tidak boleh lagi.
        $this->assertStringNotContainsString('onsubmit="return confirm(', $html);
        $this->assertStringContainsString('id="del-photo-11"', $html);
        $this->assertStringContainsString("confirmDelete('del-photo-11'", $html);
    }

    public function test_frame_preview_shows_couple_name_and_date(): void
    {
        config(['services.photobooth.url' => 'https://photobooth.test']);
        [, $user] = $this->connected();

        Http::fake([
            'https://photobooth.test/api/admin/event' => Http::response(['title' => 'Samuel & Angela Wedding']),
            'https://photobooth.test/api/admin/photos' => Http::response([]),
        ]);

        $html = $this->actingAs($user)->get('/photobooth')->assertOk()->getContent();

        // Preview bingkai tidak tampil langsung: tombol "Lihat Preview"
        // men-toggle panel inline (bukan modal) di bawah tombol simpan.
        // Panel polos hanya frame putih berisi nama & tanggal — tanpa teks
        // caption penjelasan — memakai font frame aktif dan ikut berubah
        // saat form diisi ulang.
        $this->assertStringContainsString('Lihat Preview', $html);
        $this->assertStringContainsString('@click="showPreview = !showPreview"', $html);
        $this->assertStringContainsString('x-show="showPreview"', $html);
        $this->assertStringNotContainsString('role="dialog"', $html);
        $this->assertStringNotContainsString('mengikuti data pernikahan', $html);
        $this->assertStringContainsString('Preview Bingkai', $html);
        $this->assertStringContainsString('id="frame-preview"', $html);
        $this->assertStringContainsString("font-family:'Great Vibes', cursive", $html);
        $this->assertStringContainsString('x-model="text"', $html);
        $this->assertStringContainsString('x-model="font"', $html);
    }

    public function test_saving_settings_sends_frame_text_font_and_wedding_date(): void
    {
        config(['services.photobooth.url' => 'https://photobooth.test']);
        [, $user] = $this->connected();

        Http::fake(['https://photobooth.test/*' => Http::response(['ok' => true])]);

        $this->actingAs($user)
            ->put('/photobooth/settings', [
                'title' => 'Samuel & Angela Wedding',
                'groom_name' => 'Samuel',
                'bride_name' => 'Angela',
                'gallery_mode' => 'public',
                'frame_text' => 'Samuel & Angela',
                'frame_font' => 'Great Vibes',
                // Tanggal sengaja diisi lain: sumbernya tetap data pernikahan.
                'wedding_date' => '2000-01-01',
            ])
            ->assertRedirect();

        Http::assertSent(function ($request) {
            return $request->url() === 'https://photobooth.test/api/admin/event'
                && $request->method() === 'PUT'
                && $request['frame_text'] === 'Samuel & Angela'
                && $request['frame_font'] === 'Great Vibes'
                && $request['wedding_date'] === '2026-12-12';
        });
    }

    public function test_wedding_date_change_reaches_photobooth_event(): void
    {
        config(['services.photobooth.url' => 'https://photobooth.test']);
        [$wedding, $user] = $this->connected();

        Http::fake(['https://photobooth.test/*' => Http::response(['ok' => true])]);

        $this->actingAs($user)
            ->put('/admin/wedding/'.$wedding->id, [
                'bride_name' => 'Siti',
                'groom_name' => 'Budi',
                'wedding_date' => '2028-02-03',
                'total_budget' => '100000000',
            ])
            ->assertRedirect(route('admin.index'));

        Http::assertSent(fn ($request) => str_contains($request->url(), '/api/admin/event')
            && ($request['wedding_date'] ?? null) === '2028-02-03');
    }

    public function test_mobile_creative_sheet_lists_moodboard_and_photobooth(): void
    {
        [, $user] = $this->couple();

        $html = $this->actingAs($user)->get('/')->assertOk()->getContent();
        $mobile = substr($html, strpos($html, 'data-mobile-bottom-nav'));

        // Tab Kreatif jadi sheet berisi dua menu, sama seperti section
        // Kreatif di sidebar desktop.
        $this->assertStringContainsString("sheet === 'creative'", $mobile);
        $this->assertStringContainsString(route('moodboards.index'), $mobile);
        $this->assertStringContainsString(route('photobooth.index'), $mobile);
    }

    public function test_photobooth_page_survives_photobooth_api_failure(): void
    {
        config(['services.photobooth.url' => 'https://photobooth.test']);
        [, $user] = $this->connected();

        Http::fake(fn () => throw new ConnectionException('timeout'));

        $response = $this->actingAs($user)->get('/photobooth');

        $response->assertOk();
        $this->assertStringContainsString('tidak dapat dihubungi', $response->getContent());
    }

    public function test_photobooth_page_uses_fresh_snapshot_without_calling_api_again(): void
    {
        config(['services.photobooth.url' => 'https://photobooth.test']);
        [, $user] = $this->connected();

        Http::fake([
            'https://photobooth.test/api/admin/event' => Http::response(['title' => 'Samuel & Angela Wedding']),
            'https://photobooth.test/api/admin/photos' => Http::response([]),
        ]);
        $this->actingAs($user)->get('/photobooth')->assertOk();

        Http::fake(fn () => throw new ConnectionException('unexpected request'));

        $html = $this->actingAs($user)->get('/photobooth')->assertOk()->getContent();

        $this->assertStringContainsString('Samuel &amp; Angela Wedding', $html);
        Http::assertNothingSent();
    }

    public function test_photobooth_page_shows_cached_data_when_api_is_down(): void
    {
        config(['services.photobooth.url' => 'https://photobooth.test']);
        [, $user] = $this->connected();

        Http::fake([
            'https://photobooth.test/api/admin/event' => Http::response(['title' => 'Samuel & Angela Wedding']),
            'https://photobooth.test/api/admin/photos' => Http::response([]),
        ]);
        $this->actingAs($user)->get('/photobooth')->assertOk();
        $this->travel(61)->seconds();

        Http::fake(fn () => throw new ConnectionException('timeout'));

        $html = $this->actingAs($user)->get('/photobooth')->assertOk()->getContent();

        $this->assertStringContainsString('Samuel &amp; Angela Wedding', $html);
        $this->assertStringContainsString('tidak dapat dihubungi', $html);
    }

    public function test_saving_settings_when_api_is_down_reports_error(): void
    {
        config(['services.photobooth.url' => 'https://photobooth.test']);
        [, $user] = $this->connected();

        Http::fake(fn () => throw new ConnectionException('timeout'));

        $this->actingAs($user)
            ->put('/photobooth/settings', [
                'title' => 'Samuel & Angela Wedding',
                'groom_name' => 'Samuel',
                'bride_name' => 'Angela',
                'gallery_mode' => 'public',
                'frame_text' => 'Samuel & Angela',
                'frame_font' => 'Great Vibes',
            ])
            ->assertRedirect();

        $this->assertNotNull(session('error'));
    }

    public function test_deleting_a_photo_calls_photobooth_api(): void
    {
        config(['services.photobooth.url' => 'https://photobooth.test']);
        [, $user] = $this->connected();

        Http::fake(['https://photobooth.test/*' => Http::response(['ok' => true])]);

        $this->actingAs($user)->delete('/photobooth/photos/7')->assertRedirect();

        Http::assertSent(fn ($request) => $request->method() === 'DELETE'
            && $request->url() === 'https://photobooth.test/api/admin/photos/7');
    }

    public function test_export_returns_zip_file(): void
    {
        config(['services.photobooth.url' => 'https://photobooth.test']);
        [, $user] = $this->connected();

        Http::fake(['https://photobooth.test/*' => Http::response('PK-zip-content')]);

        $response = $this->actingAs($user)->get('/photobooth/photos/export');

        $response->assertOk();
        $this->assertSame('application/zip', $response->headers->get('Content-Type'));
    }

    public function test_activate_provisions_event_and_stores_connection(): void
    {
        config([
            'services.photobooth.url' => 'https://photobooth.test',
            'services.photobooth.server_key' => 'server-key',
        ]);
        [$wedding, $user] = $this->couple();
        $wedding->update(['photobooth_active' => true]);

        Http::fake(['https://photobooth.test/*' => Http::response([
            'slug' => 'sa2027',
            'api_key' => 'generated-key',
        ])]);

        $this->actingAs($user)
            ->post('/photobooth/activate')
            ->assertRedirect(route('photobooth.index'));

        $wedding = $wedding->fresh();
        $this->assertSame('sa2027', $wedding->photobooth_event_slug);
        $this->assertSame('generated-key', $wedding->photoboothApiKey());
        $this->assertTrue($wedding->photobooth_active);

        Http::assertSent(function ($request) {
            return $request->url() === 'https://photobooth.test/api/provision'
                && $request->method() === 'POST'
                && ($request->header('X-Server-Key')[0] ?? null) === 'server-key'
                && ($request['title'] ?? null) === 'Budi & Siti Wedding';
        });
    }

    public function test_activate_reports_error_when_provisioning_fails(): void
    {
        config([
            'services.photobooth.url' => 'https://photobooth.test',
            'services.photobooth.server_key' => 'server-key',
        ]);
        [$wedding, $user] = $this->couple();

        Http::fake(fn () => throw new ConnectionException('timeout'));

        $this->actingAs($user)
            ->post('/photobooth/activate')
            ->assertRedirect();

        $this->assertNotNull(session('error'));
        $this->assertFalse((bool) $wedding->fresh()->photobooth_active);
    }

    public function test_couple_without_photobooth_sees_locked_page(): void
    {
        Setting::put('photobooth_price', '250000');
        [, $user] = $this->couple('free');

        $html = $this->actingAs($user)->get('/photobooth')->assertOk()->getContent();

        $this->assertStringContainsString('Photobooth', $html);
        $this->assertStringNotContainsString('Buka Akses dengan Pembayaran', $html);
        $this->assertStringNotContainsString('name="proof"', $html);
    }

    public function test_unpurchased_photobooth_cannot_activate_add_on_directly(): void
    {
        [$wedding, $user] = $this->couple('free');

        $this->actingAs($user)
            ->post('/photobooth/activate')
            ->assertRedirect(route('photobooth.index'));

        $this->assertFalse((bool) $wedding->fresh()->photobooth_active);
        $this->assertStringContainsString('beli add-on', strtolower((string) session('error')));
    }

    public function test_activation_card_hides_manual_connection_form(): void
    {
        // Pemilik add-on yang event-nya belum ter-provisioning melihat kartu
        // aktivasi, tapi tanpa form hubungkan manual (slug/API key): pembeli
        // biasa cukup klik "Aktifkan Photobooth". PUT /photobooth tetap hidup
        // untuk jalur admin/API.
        [$wedding, $user] = $this->couple('pro');
        $wedding->update(['photobooth_active' => true]);

        $html = $this->actingAs($user)->get('/photobooth')->assertOk()->getContent();

        $this->assertStringNotContainsString('Hubungkan manual', $html);
        $this->assertStringNotContainsString('name="event_slug"', $html);
        $this->assertStringNotContainsString('name="api_key"', $html);
        $this->assertStringNotContainsString('Kelola Photobooth', $html);
        $this->assertStringContainsString('Aktifkan Photobooth', $html);
    }

    public function test_activation_card_does_not_repeat_success_flash(): void
    {
        // Interior flash sukses sudah tampil sebagai toast global; jangan
        // diduplikasi di dalam kartu di atas tombol aktivasi.
        config([
            'services.photobooth.url' => 'https://photobooth.test',
            'services.photobooth.server_key' => 'server-key',
        ]);
        [$wedding, $user] = $this->couple();
        $wedding->update(['photobooth_active' => true]);

        // Handler kondisional per-URL: pola wildcard yang didefinisikan
        // belakangan bisa mengoverride stub spesifik di versi ini.
        Http::fake(fn ($request) => str_contains($request->url(), '/api/provision')
            ? Http::response(['slug' => 'sa2027', 'api_key' => 'generated-key'])
            : throw new ConnectionException('timeout'));

        $html = $this->followingRedirects()->actingAs($user)
            ->post('/photobooth/activate')
            ->assertOk()
            ->getContent();

        // Satu-satunya tampilan pesan sukses adalah toast global di layout;
        // teks yang sama tidak boleh diulang di dalam kartu aktivasi.
        $this->assertSame(1, substr_count($html, 'QR dan pengaturan sudah siap'));
        $this->assertStringContainsString('Aktifkan Photobooth', $html);
    }

    public function test_locked_page_shows_add_on_hero_framework(): void
    {
        Setting::put('photobooth_price', '250000');
        [, $user] = $this->couple('free');

        $html = $this->actingAs($user)->get('/photobooth')->assertOk()->getContent();

        // Struktur mengikuti locked Moodboard: centered hero, visual cards, benefits, CTA.
        $this->assertStringContainsString('Photobooth', $html);
        $this->assertStringContainsString('Foto &amp; GIF Tanpa Batas', $html);
        $this->assertStringContainsString('Frame &amp; Stiker Kustom', $html);
        $this->assertStringContainsString('Live Gallery untuk Tamu', $html);
        $this->assertStringContainsString('Buka Akses Photobooth Sekarang', $html);

        // Empat foto portrait mengitari lock card dalam busur setengah lingkaran.
        $this->assertStringContainsString('Buka Akses Photobooth Sekarang', $html);
        $this->assertStringContainsString('FITUR ADD-ON', $html);
        foreach (['img-2.jpg', 'img-3.jpg', 'img-4.jpg', 'img-5.jpg'] as $image) {
            $this->assertStringContainsString('img/'.$image, $html);
        }
    }

    public function test_photobooth_purchase_creates_pending_request(): void
    {
        Storage::fake('local');
        Setting::put('photobooth_price', '250000');
        [, $user] = $this->couple('free');

        $this->actingAs($user)
            ->post('/photobooth/purchase', ['proof' => UploadedFile::fake()->image('bukti.jpg')])
            ->assertRedirect(route('photobooth.index'));

        $request = UpgradeRequest::sole();
        $this->assertSame('photobooth', $request->feature);
        $this->assertSame('pending', $request->status);
        $this->assertSame(250000, $request->amount);
    }

    public function test_approving_photobooth_request_provisions_event_and_activates(): void
    {
        config([
            'services.photobooth.url' => 'https://photobooth.test',
            'services.photobooth.server_key' => 'server-key',
        ]);
        Storage::fake('local');
        Setting::put('photobooth_price', '250000');
        [$wedding, $user] = $this->couple('free');

        Http::fake(['https://photobooth.test/*' => Http::response([
            'slug' => 'sa2027',
            'api_key' => 'generated-key',
        ])]);

        $this->actingAs($user)
            ->post('/photobooth/purchase', ['proof' => UploadedFile::fake()->image('bukti.jpg')]);
        $request = UpgradeRequest::sole();

        $admin = User::factory()->create(['is_superadmin' => true, 'email' => 'admin+pb@example.com']);
        $this->actingAs($admin)->post("/admin/upgrade-requests/{$request->id}/approve")->assertRedirect();

        $wedding = $wedding->fresh();
        $this->assertTrue($wedding->photobooth_active);
        $this->assertSame('sa2027', $wedding->photobooth_event_slug);
        $this->assertSame('generated-key', $wedding->photoboothApiKey());
    }

    public function test_admin_updates_photobooth_price_and_quota(): void
    {
        $admin = User::factory()->create(['is_superadmin' => true, 'email' => 'admin+pb2@example.com']);

        $this->actingAs($admin)
            ->put('/admin/photobooth-settings', [
                'photobooth_price' => '350000',
                'photobooth_photo_quota' => '500',
            ])
            ->assertRedirect(route('admin.photobooth-settings.index'));

        $this->assertSame(350000, (int) Setting::get('photobooth_price'));
        $this->assertSame(500, (int) Setting::get('photobooth_photo_quota'));
    }

    public function test_quota_change_is_sent_to_active_photobooth_events(): void
    {
        config(['services.photobooth.url' => 'https://photobooth.test']);
        Http::fake(['https://photobooth.test/*' => Http::response(['ok' => true])]);

        [, $user] = $this->couple('pro');
        $user->wedding->update(['photobooth_active' => true]);
        $this->actingAs($user)->put('/photobooth', [
            'event_slug' => 'sa2027',
            'api_key' => 'test-key',
            'active' => '1',
        ]);

        $admin = User::factory()->create(['is_superadmin' => true, 'email' => 'admin+pb3@example.com']);
        $this->actingAs($admin)->put('/admin/photobooth-settings', [
            'photobooth_price' => '350000',
            'photobooth_photo_quota' => '500',
        ]);

        Http::assertSent(fn ($request) => str_contains($request->url(), '/api/admin/event')
            && ($request['photo_quota'] ?? null) === 500);
    }
}
