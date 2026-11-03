<?php

namespace Tests\Feature;

use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class TempPurchaseFlowTest extends TestCase
{
    use RefreshDatabase;

    private function couple(string $plan = 'free'): array
    {
        $wedding = \App\Models\Wedding::create([
            'bride_name' => 'Siti',
            'groom_name' => 'Budi',
            'wedding_date' => '2026-12-12',
            'total_budget' => 100000000,
            'plan' => $plan,
        ]);
        $user = \App\Models\User::factory()->create([
            'email' => "pasangan+{$wedding->id}@example.com",
            'wedding_id' => $wedding->id,
        ]);

        return [$wedding, $user];
    }

    public function test_pro_submit_lands_on_pending(): void
    {
        Storage::fake('local');
        Setting::put('pro_price', '249000');
        [, $user] = $this->couple();

        $response = $this->actingAs($user)
            ->post('/upgrade', ['proof' => UploadedFile::fake()->image('bukti.jpg')]);

        $response->assertRedirect(route('upgrade.index'));

        $html = $this->actingAs($user)->get('/upgrade')->getContent();
        $this->assertStringContainsString('Sedang Diverifikasi', $html, 'PRO: pending card missing');
    }

    public function test_photobooth_submit_lands_on_pending(): void
    {
        Storage::fake('local');
        Setting::put('photobooth_price', '250000');
        [, $user] = $this->couple();

        $response = $this->actingAs($user)
            ->post('/photobooth/purchase', ['proof' => UploadedFile::fake()->image('bukti.jpg')]);

        $response->assertRedirect(route('photobooth.index'));

        $html = $this->actingAs($user)->get('/photobooth')->getContent();
        $this->assertStringContainsString('Sedang Diverifikasi', $html, 'PHOTOBOOTH: pending card missing');
    }
}
