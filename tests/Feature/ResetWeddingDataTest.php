<?php

namespace Tests\Feature;

use App\Models\Budget;
use App\Models\Checklist;
use App\Models\DocumentRequirement;
use App\Models\Guest;
use App\Models\Moodboard;
use App\Models\RundownEvent;
use App\Models\SavingsGoal;
use App\Models\SavingsTransaction;
use App\Models\Souvenir;
use App\Models\UpgradeRequest;
use App\Models\User;
use App\Models\Vendor;
use App\Models\VendorCatalog;
use App\Models\Wedding;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Reset Semua Data menghapus worksheet secara permanen, jadi yang dijaga di
 * sini bukan hanya "datanya hilang" tapi juga "yang tidak boleh ikut hilang
 * benar-benar tetap ada".
 */
class ResetWeddingDataTest extends TestCase
{
    use RefreshDatabase;

    /** @return array{0: Wedding, 1: User} */
    private function couple(): array
    {
        $wedding = Wedding::create([
            'bride_name' => 'Siti',
            'groom_name' => 'Budi',
            'wedding_date' => '2026-12-12',
            'total_budget' => 100000000,
            'location' => 'Hotel Indonesia',
            'notes' => 'Tema dusty rose',
            'wa_template' => 'Halo {nama}',
            'plan' => 'pro',
        ]);

        $user = User::factory()->create([
            'email' => 'reset@example.com',
            'wedding_id' => $wedding->id,
        ]);

        return [$wedding, $user];
    }

    private function seedWorksheets(Wedding $wedding): void
    {
        $wedding->checklists()->create(['title' => 'Booking catering', 'category' => 'Catering', 'status' => 'Todo']);
        $wedding->budgets()->create(['category' => 'Venue', 'item_name' => 'Hotel Indonesia', 'planned_budget' => 1000000]);
        $wedding->guests()->create(['name' => 'Tamu 1', 'guest_count' => 2]);
        $wedding->moodboards()->create(['category' => 'Dekorasi', 'title' => 'Referensi']);
        $wedding->rundownEvents()->create(['time' => '09:00', 'activity' => 'Akad', 'pic' => 'Budi']);
        $wedding->souvenirs()->create(['name' => 'Kipas']);
        $wedding->documentRequirements()->create(['name' => 'Akta nikah']);
        $wedding->savingsGoals()->create(['name' => 'Dekorasi', 'target_amount' => 5000000]);
        $wedding->vendors()->create(['name' => 'Catering A', 'category' => 'Catering']);

        $goal = $wedding->savingsGoals()->first();
        SavingsTransaction::create([
            'wedding_id' => $wedding->id,
            'savings_goal_id' => $goal->id,
            'type' => 'setor',
            'amount' => 100000,
            'transaction_date' => '2026-10-01',
        ]);
    }

    public function test_reset_requires_the_confirmation_keyword()
    {
        [$wedding, $user] = $this->couple();
        $this->seedWorksheets($wedding);

        $this->actingAs($user)
            ->delete('/profile/reset-data')
            ->assertSessionHasErrors('confirm');

        // Tidak ada yang boleh terhapus sebelum konfirmasi benar.
        $this->assertSame(1, Checklist::count());
        $this->assertSame(1, Budget::count());
        $this->assertNotNull($wedding->fresh()->groom_name);
    }

    public function test_reset_wrong_keyword_is_still_refused()
    {
        [$wedding, $user] = $this->couple();
        $this->seedWorksheets($wedding);

        $this->actingAs($user)
            ->delete('/profile/reset-data', ['confirm' => 'hapus'])
            ->assertSessionHasErrors('confirm');

        $this->assertSame(1, Checklist::count());
    }

    public function test_reset_clears_every_worksheet_and_the_wedding_profile()
    {
        [$wedding, $user] = $this->couple();
        $this->seedWorksheets($wedding);

        $this->actingAs($user)
            ->delete('/profile/reset-data', ['confirm' => 'RESET'])
            ->assertRedirect(route('admin.index'));

        foreach ([Checklist::class, Budget::class, Guest::class, Moodboard::class, RundownEvent::class,
            Souvenir::class, DocumentRequirement::class, SavingsGoal::class, SavingsTransaction::class,
            Vendor::class] as $model) {
            $this->assertSame(0, $model::count(), $model.' masih punya data setelah reset.');
        }

        $fresh = $wedding->fresh();
        $this->assertNull($fresh->bride_name);
        $this->assertNull($fresh->groom_name);
        $this->assertNull($fresh->wedding_date);
        $this->assertNull($fresh->location);
        $this->assertNull($fresh->notes);
        $this->assertNull($fresh->wa_template);
        $this->assertEquals(0, $fresh->total_budget);
    }

    public function test_reset_keeps_accounts_partner_plan_and_purchase_history()
    {
        [$wedding, $user] = $this->couple();
        $this->seedWorksheets($wedding);

        // Akun pasangan ikut memakai wedding_id yang sama.
        $partner = User::factory()->create([
            'email' => 'pasangan2@example.com',
            'wedding_id' => $wedding->id,
        ]);

        $wedding->upgradeRequests()->create([
            'user_id' => $user->id,
            'plan' => 'pro',
            'amount' => 55000,
            'proof_path' => 'upgrade-proofs/bukti.jpg',
            'status' => 'approved',
        ]);

        $this->actingAs($user)->delete('/profile/reset-data', ['confirm' => 'RESET']);

        $this->assertNotNull(User::find($user->id));
        $this->assertNotNull(User::find($partner->id));
        $this->assertSame(1, UpgradeRequest::count());

        // Paket yang sudah dibeli tidak hilang karena reset.
        $this->assertTrue($wedding->fresh()->isPro());
    }

    public function test_reset_does_not_touch_the_global_vendor_catalog()
    {
        [$wedding, $user] = $this->couple();
        $this->seedWorksheets($wedding);

        // Katalog Weddingku milik semua pasangan, bukan milik satu wedding.
        VendorCatalog::create([
            'name' => 'The Apurva Kempinski Bali',
            'slug' => 'apurva',
            'category' => 'Hotel Wedding Venue',
            'source_url' => 'https://weddingku.com/vendor/apurva',
        ]);

        $this->actingAs($user)->delete('/profile/reset-data', ['confirm' => 'RESET']);

        $this->assertSame(1, VendorCatalog::count());
    }

    public function test_reset_only_affects_the_current_wedding()
    {
        [$wedding, $user] = $this->couple();
        $this->seedWorksheets($wedding);

        $other = Wedding::create([
            'bride_name' => 'Ani',
            'groom_name' => 'Doni',
            'wedding_date' => '2027-01-01',
            'total_budget' => 50000000,
        ]);
        $other->checklists()->create(['title' => 'Checklist pasangan lain', 'category' => 'Umum']);

        $this->actingAs($user)->delete('/profile/reset-data', ['confirm' => 'RESET']);

        // Lewati global scope: model punya ScopedToWedding, jadi count() biasa
        // hanya melihat wedding yang sedang login dan tidak bisa membuktikan
        // data pasangan lain selamat.
        $rows = DB::table('checklists')->orderBy('id')->get();

        $this->assertCount(1, $rows, 'Data pasangan lain ikut terhapus.');
        $this->assertSame('Checklist pasangan lain', $rows->first()->title);
    }

    public function test_profile_page_shows_the_reset_card_and_confirmation_modal()
    {
        [$wedding, $user] = $this->couple();

        $this->actingAs($user)->get('/admin')
            ->assertOk()
            ->assertSee('Reset Semua Data')
            ->assertSee('tidak bisa dibatalkan')
            ->assertSee('Tetap aman:')
            ->assertSee(route('profile.reset-data'))
            ->assertSee('Ketik', false);
    }
}