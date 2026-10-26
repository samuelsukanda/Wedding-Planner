<?php

namespace Tests\Feature;

use App\Models\Setting;
use App\Models\UpgradeRequest;
use App\Models\User;
use App\Models\Wedding;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
            ->put('/admin/wedding/' . $wedding->id, [
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

        Http::fake(fn () => throw new \Illuminate\Http\Client\ConnectionException('timeout'));

        $response = $this->actingAs($user)->get('/photobooth');

        $response->assertOk();
        $this->assertStringContainsString('tidak dapat dihubungi', $response->getContent());
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

        Http::fake(fn () => throw new \Illuminate\Http\Client\ConnectionException('timeout'));

        $html = $this->actingAs($user)->get('/photobooth')->assertOk()->getContent();

        $this->assertStringContainsString('Samuel &amp; Angela Wedding', $html);
    }

    public function test_saving_settings_when_api_is_down_reports_error(): void
    {
        config(['services.photobooth.url' => 'https://photobooth.test']);
        [, $user] = $this->connected();

        Http::fake(fn () => throw new \Illuminate\Http\Client\ConnectionException('timeout'));

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

        Http::fake(fn () => throw new \Illuminate\Http\Client\ConnectionException('timeout'));

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

        $this->assertStringContainsString('Buka Akses Add-On Spesial Photobooth!', $html);
        $this->assertStringNotContainsString('Buka Akses dengan Pembayaran', $html);
        $this->assertStringNotContainsString('name="proof"', $html);
    }

    public function test_locked_page_shows_add_on_hero_framework(): void
    {
        Setting::put('photobooth_price', '250000');
        [, $user] = $this->couple('free');

        $html = $this->actingAs($user)->get('/photobooth')->assertOk()->getContent();

        // Kerangka hero sesuai mockup: badge, judul, tiga baris fitur, CTA.
        $this->assertStringContainsString('FITUR ADD-ON', $html);
        $this->assertStringContainsString('Buka Akses Add-On Spesial Photobooth!', $html);
        $this->assertStringContainsString('Foto &amp; GIF Tanpa Batas', $html);
        $this->assertStringContainsString('Frame &amp; Stiker Kustom (PRO)', $html);
        $this->assertStringContainsString('Unduh Instan &amp; Live Gallery', $html);
        $this->assertStringContainsString('Buka Akses Add-On Sekarang', $html);

        // Kolase memakai foto lokal dan kartu add-on terkunci.
        $this->assertStringContainsString('data-photobooth-collage', $html);
        $this->assertStringContainsString('img/pexels-photo-layout.jpg', $html);
        $this->assertStringContainsString('img/pexels-bridal-flatlay.jpg', $html);
        $this->assertStringContainsString('Momenmu bisa difoto langsung', $html);
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
