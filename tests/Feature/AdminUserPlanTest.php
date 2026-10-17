<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Wedding;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Status akun (free/pro) disimpan di weddings, bukan di users, karena satu
 * Wedding Kit dipakai kedua pasangan. Panel admin mengelolanya lewat form
 * edit user.
 */
class AdminUserPlanTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_sees_account_status_in_user_list(): void
    {
        $wedding = $this->makeWedding(plan: 'pro');

        $response = $this->actingAs($this->makeSuperadmin())
            ->get(route('admin.users.index'));

        $response->assertOk();
        $response->assertSee('Status Akun');
        $response->assertSee('Pro');
    }

    public function test_admin_can_change_free_to_pro_from_edit_user(): void
    {
        $wedding = $this->makeWedding(plan: 'free');
        $user = User::factory()->create(['wedding_id' => $wedding->id, 'is_superadmin' => false]);

        $this->actingAs($this->makeSuperadmin())
            ->put(route('admin.users.update', $user), [
                'name' => $user->name,
                'email' => $user->email,
                'wedding_id' => $wedding->id,
                'plan' => 'pro',
            ])
            ->assertRedirect(route('admin.users.index'));

        $this->assertSame('pro', $wedding->fresh()->plan);
    }

    public function test_admin_can_change_pro_back_to_free(): void
    {
        $wedding = $this->makeWedding(plan: 'pro');
        $user = User::factory()->create(['wedding_id' => $wedding->id]);

        $this->actingAs($this->makeSuperadmin())
            ->put(route('admin.users.update', $user), [
                'name' => $user->name,
                'email' => $user->email,
                'wedding_id' => $wedding->id,
                'plan' => 'free',
            ]);

        $this->assertSame('free', $wedding->fresh()->plan);
    }

    /**
     * Kalau sekalian dipindah ke Wedding Kit lain, status harus mengikuti
     * wedding yang baru, bukan wedding lama.
     */
    public function test_plan_follows_the_newly_assigned_wedding(): void
    {
        $old = $this->makeWedding(plan: 'free');
        $new = $this->makeWedding(plan: 'free');
        $user = User::factory()->create(['wedding_id' => $old->id]);

        $this->actingAs($this->makeSuperadmin())
            ->put(route('admin.users.update', $user), [
                'name' => $user->name,
                'email' => $user->email,
                'wedding_id' => $new->id,
                'plan' => 'pro',
            ]);

        $this->assertSame('pro', $new->fresh()->plan);
        $this->assertSame('free', $old->fresh()->plan);
    }

    public function test_admin_can_add_partner_from_quick_modal(): void
    {
        $wedding = $this->makeWedding();
        $user = User::factory()->create(['wedding_id' => $wedding->id]);

        $this->actingAs($this->makeSuperadmin())
            ->put(route('admin.users.partner', $user), [
                'partner_name' => 'Nabila',
                'partner_email' => 'nabila@email.com',
            ])
            ->assertRedirect(route('admin.users.index'));

        $partner = User::where('email', 'nabila@email.com')->first();

        $this->assertNotNull($partner, 'Akun pasangan harus dibuat.');
        $this->assertSame('Nabila', $partner->name);
        $this->assertSame(
            $wedding->id,
            $partner->wedding_id,
            'Pasangan harus masuk Wedding Kit yang sama.'
        );
        $this->assertSame('nabila@email.com', $user->fresh()->partner_email);
    }

    /**
     * Kalau email-nya sudah terdaftar, akun itu yang dipakai -- tidak boleh
     * dibuat akun duplikat.
     */
    public function test_existing_email_is_linked_instead_of_duplicated(): void
    {
        $wedding = $this->makeWedding();
        $user = User::factory()->create(['wedding_id' => $wedding->id]);
        $existing = User::factory()->create(['wedding_id' => null, 'name' => 'Nama Lama']);

        $this->actingAs($this->makeSuperadmin())
            ->put(route('admin.users.partner', $user), [
                'partner_name' => 'Nabila',
                'partner_email' => $existing->email,
            ]);

        $this->assertSame(1, User::where('email', $existing->email)->count());
        $this->assertSame($wedding->id, $existing->fresh()->wedding_id);
        $this->assertSame('Nabila', $existing->fresh()->name);
    }

    public function test_partner_is_not_created_for_user_without_wedding(): void
    {
        $user = User::factory()->create(['wedding_id' => null]);

        $this->actingAs($this->makeSuperadmin())
            ->put(route('admin.users.partner', $user), [
                'partner_name' => 'Nabila',
                'partner_email' => 'nabila@email.com',
            ]);

        $this->assertNull(User::where('email', 'nabila@email.com')->first());
    }

    public function test_user_cannot_be_own_partner(): void
    {
        $wedding = $this->makeWedding();
        $user = User::factory()->create(['wedding_id' => $wedding->id]);

        $this->actingAs($this->makeSuperadmin())
            ->put(route('admin.users.partner', $user), [
                'partner_name' => 'Dirinya Sendiri',
                'partner_email' => $user->email,
            ])
            ->assertSessionHasErrors('partner_email');

        $this->assertSame(1, User::where('email', $user->email)->count());
    }

    public function test_partner_is_shown_in_user_list(): void
    {
        $wedding = $this->makeWedding();
        $user = User::factory()->create(['wedding_id' => $wedding->id]);
        User::factory()->create([
            'wedding_id' => $wedding->id,
            'name' => 'Nabila',
            'email' => 'nabila@email.com',
        ]);

        $this->actingAs($this->makeSuperadmin())
            ->get(route('admin.users.index'))
            ->assertSee('Pasangan')
            ->assertSee('Nabila')
            ->assertSee('nabila@email.com');
    }

    public function test_role_column_is_removed_from_user_list(): void
    {
        $response = $this->actingAs($this->makeSuperadmin())
            ->get(route('admin.users.index'));

        $response->assertOk();
        $response->assertDontSee('>Role<');
    }

    public function test_plan_is_rejected_when_not_free_or_pro(): void
    {
        $wedding = $this->makeWedding(plan: 'free');
        $user = User::factory()->create(['wedding_id' => $wedding->id]);

        $this->actingAs($this->makeSuperadmin())
            ->put(route('admin.users.update', $user), [
                'name' => $user->name,
                'email' => $user->email,
                'wedding_id' => $wedding->id,
                'plan' => 'enterprise',
            ])
            ->assertSessionHasErrors('plan');

        $this->assertSame('free', $wedding->fresh()->plan);
    }

    private function makeSuperadmin(): User
    {
        return User::factory()->create(['is_superadmin' => true]);
    }

    private function makeWedding(string $plan = 'free'): Wedding
    {
        return Wedding::create([
            'groom_name' => 'Groom',
            'bride_name' => 'Bride',
            'wedding_date' => '2027-10-07',
            'total_budget' => 100000000,
            'plan' => $plan,
        ]);
    }
}