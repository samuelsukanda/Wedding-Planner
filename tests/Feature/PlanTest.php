<?php

namespace Tests\Feature;

use App\Models\Notification;
use App\Models\Setting;
use App\Models\UpgradeRequest;
use App\Models\User;
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

    public function test_free_couple_is_redirected_from_pro_feature_to_upgrade_page()
    {
        [, $user] = $this->couple();

        $this->actingAs($user)
            ->get('/moodboards')
            ->assertRedirect(route('upgrade.index', ['from' => 'moodboard']));
    }

    public function test_pro_couple_can_open_moodboard()
    {
        [, $user] = $this->couple('pro');

        $this->actingAs($user)->get('/moodboards')->assertOk();
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

    public function test_superadmin_can_save_pro_settings()
    {
        $this->actingAs($this->superadmin())
            ->put('/admin/pro-settings', [
                'pro_price' => 249000,
                'pro_bank' => 'BCA 1234567890 a/n Wedding Planner',
                'admin_whatsapp' => '6281234567890',
            ])
            ->assertRedirect(route('admin.pro-settings.edit'));

        $this->assertSame('249000', Setting::get('pro_price'));
        $this->assertSame('6281234567890', Setting::get('admin_whatsapp'));
    }
}