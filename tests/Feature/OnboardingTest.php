<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Wedding;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OnboardingTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_creates_user_and_redirects_to_onboarding(): void
    {
        $response = $this->post('/register', [
            'name' => 'Samuel Sukanda',
            'email' => 'samuel@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertRedirect(route('onboarding.index'));
        $this->assertDatabaseHas('users', [
            'email' => 'samuel@example.com',
            'name' => 'Samuel Sukanda',
            'is_superadmin' => false,
            'wedding_id' => null,
        ]);
    }

    public function test_registration_rejects_duplicate_email(): void
    {
        User::factory()->create(['email' => 'samuel@example.com']);

        $this->post('/register', [
            'name' => 'Other',
            'email' => 'samuel@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])->assertSessionHasErrors('email');

        $this->assertSame(1, User::where('email', 'samuel@example.com')->count());
    }

    public function test_wizard_advances_through_steps_and_creates_workspace(): void
    {
        $user = User::factory()->create(['name' => 'Samuel Sukanda']);

        $this->actingAs($user)->get('/onboarding')->assertOk();

        $this->actingAs($user)->post('/onboarding', ['user_name' => 'Samuel Sukanda'])
            ->assertRedirect(route('onboarding.index'));

        $this->actingAs($user)->post('/onboarding', ['partner_name' => 'Aulia'])
            ->assertRedirect(route('onboarding.index'));

        $this->actingAs($user)->post('/onboarding', ['wedding_date' => '2027-10-07'])
            ->assertRedirect(route('onboarding.index'));

        $this->actingAs($user)->post('/onboarding', ['total_budget' => 100000000])
            ->assertRedirect(route('onboarding.index'));

        $this->actingAs($user)->get('/onboarding')
            ->assertOk()
            ->assertSee('Siap memulai perjalanan!')
            ->assertSee('Aulia');

        $this->actingAs($user)->post('/onboarding/finish')
            ->assertRedirect(route('dashboard'));

        $wedding = Wedding::first();
        $this->assertNotNull($wedding);
        $this->assertSame('Samuel Sukanda', $wedding->groom_name);
        $this->assertSame('Aulia', $wedding->bride_name);
        $this->assertSame('2027-10-07', $wedding->wedding_date->toDateString());
        $this->assertSame('100000000.00', $wedding->total_budget);

        // Template checklist ikut dibuat supaya workspace tidak kosong.
        $this->assertSame(12, $wedding->checklists()->count());
        $this->assertSame($wedding->id, $user->fresh()->wedding_id);
    }

    public function test_wizard_progress_survives_refresh(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post('/onboarding', ['user_name' => 'Samuel']);
        $this->actingAs($user)->post('/onboarding', ['partner_name' => 'Aulia']);

        $this->actingAs($user)->get('/onboarding')
            ->assertOk()
            ->assertSee('Kapan kalian menikah?');
    }

    public function test_back_button_returns_to_previous_step(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post('/onboarding', ['user_name' => 'Samuel']);
        $this->actingAs($user)->post('/onboarding', ['partner_name' => 'Aulia']);

        $this->actingAs($user)->post('/onboarding', ['direction' => 'back'])
            ->assertRedirect(route('onboarding.index'));

        $this->actingAs($user)->get('/onboarding')
            ->assertOk()
            ->assertSee('Siapa nama pasanganmu?');
    }

    public function test_user_without_wedding_is_redirected_to_onboarding(): void
    {
        $user = User::factory()->create(['wedding_id' => null]);

        $this->actingAs($user)->get('/guests')->assertRedirect(route('onboarding.index'));
    }

    public function test_user_with_wedding_is_not_blocked(): void
    {
        $wedding = Wedding::create([
            'groom_name' => 'Samuel',
            'bride_name' => 'Aulia',
            'wedding_date' => '2027-10-07',
            'total_budget' => 100000000,
        ]);
        $user = User::factory()->create(['wedding_id' => $wedding->id]);

        $this->actingAs($user)->get('/guests')->assertOk();
    }

    public function test_finished_onboarding_cannot_be_reopened(): void
    {
        $wedding = Wedding::create([
            'groom_name' => 'Samuel',
            'bride_name' => 'Aulia',
            'wedding_date' => '2027-10-07',
            'total_budget' => 100000000,
        ]);
        $user = User::factory()->create(['wedding_id' => $wedding->id]);

        $this->actingAs($user)->get('/onboarding')->assertRedirect(route('dashboard'));
    }

    public function test_finish_requires_partner_name(): void
    {
        $user = User::factory()->create(['wedding_id' => null]);

        $this->actingAs($user)->withSession(['onboarding' => [
            'user_name' => 'Samuel',
            'wedding_date' => '2027-10-07',
            'total_budget' => 100000000,
        ]])->post('/onboarding/finish')->assertSessionHasErrors('partner_name');

        $this->assertSame(0, Wedding::count());
    }

    public function test_google_login_shows_message_when_not_configured(): void
    {
        config(['services.google.client_id' => null]);

        $this->get('/auth/google/redirect')
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors('email');
    }
}