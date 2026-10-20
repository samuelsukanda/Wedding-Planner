<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Wedding;
use App\Models\WeddingInvite;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Socialite\Facades\Socialite;
use Tests\TestCase;

class PartnerInviteTest extends TestCase
{
    use RefreshDatabase;

    /** Mock Socialite untuk satu login Google (email tanpa foto profil). */
    private function fakeGoogleCallback(string $email, string $name = 'Gugel User'): void
    {
        $googleUser = new \Laravel\Socialite\Two\User();
        $googleUser->map(['id' => 'g-1', 'name' => $name, 'email' => $email]);

        $provider = \Mockery::mock(\Laravel\Socialite\Two\GoogleProvider::class);
        $provider->shouldReceive('user')->andReturn($googleUser);
        $provider->shouldReceive('redirect')->andReturn('https://accounts.google.com/fake');

        Socialite::shouldReceive('driver')->with('google')->andReturn($provider);
    }

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

    private function inviteFor(Wedding $wedding, ?User $creator = null): WeddingInvite
    {
        return WeddingInvite::create([
            'wedding_id' => $wedding->id,
            'token' => 'tok-' . bin2hex(random_bytes(8)),
            'expires_at' => now()->addDays(7),
            'created_by' => ($creator ?? User::factory()->create())->id,
        ]);
    }

    public function test_profile_shows_the_partner_menu_only_for_pro()
    {
        [$wedding, $user] = $this->couple('pro');

        $this->actingAs($user)
            ->get('/admin')
            ->assertOk()
            ->assertSee('Tambah Pasangan')
            ->assertSee(route('profile.partner-invites.index'));

        [$freeWedding, $freeUser] = $this->couple('free');

        $this->actingAs($freeUser)
            ->get('/admin')
            ->assertOk()
            ->assertDontSee('Tambah Pasangan')
            ->assertDontSee(route('profile.partner-invites.index'));
    }

    public function test_free_couple_cannot_open_the_invite_page()
    {
        [, $user] = $this->couple('free');

        $this->actingAs($user)->get('/profile/pasangan')->assertForbidden();
    }

    public function test_pro_couple_can_create_an_invite()
    {
        [$wedding, $user] = $this->couple();

        $this->actingAs($user)
            ->post('/profile/pasangan')
            ->assertRedirect(route('profile.partner-invites.index'));

        $invite = WeddingInvite::where('wedding_id', $wedding->id)->firstOrFail();

        // Token harus panjang dan acak, bukan tebakan berurutan.
        $this->assertSame(48, strlen($invite->token));
        $this->assertTrue($invite->isPending());
        $this->assertEqualsWithDelta(
            now()->addDays(7)->timestamp,
            $invite->expires_at->timestamp,
            5,
        );
    }

    public function test_creating_a_second_invite_replaces_the_pending_one()
    {
        [$wedding, $user] = $this->couple();

        $this->actingAs($user)->post('/profile/pasangan');
        $first = WeddingInvite::where('wedding_id', $wedding->id)->firstOrFail()->token;

        $this->actingAs($user)->post('/profile/pasangan');

        // Hanya satu undangan aktif, supaya tidak ada beberapa tautan yang
        // bisa salah kirim ke pasangan.
        $this->assertSame(1, WeddingInvite::where('wedding_id', $wedding->id)->count());
        $this->assertNull(WeddingInvite::where('token', $first)->first());
    }

    public function test_free_couple_cannot_post_an_invite_even_by_calling_the_endpoint()
    {
        [$wedding, $user] = $this->couple('free');

        $this->actingAs($user)->post('/profile/pasangan')->assertForbidden();

        $this->assertSame(0, WeddingInvite::where('wedding_id', $wedding->id)->count());
    }

    public function test_cannot_revoke_someone_elses_invite()
    {
        [$wedding, $owner] = $this->couple();
        [$otherWedding] = $this->couple();

        $invite = $this->inviteFor($otherWedding);

        $this->actingAs($owner)
            ->delete(route('profile.partner-invites.destroy', $invite))
            ->assertForbidden();

        $this->assertNotNull(WeddingInvite::find($invite->id));
    }

    public function test_guest_can_open_an_invite_without_leaking_the_couple_names()
    {
        $wedding = Wedding::create([
            'bride_name' => 'Siti Rahma',
            'groom_name' => 'Budi Santoso',
            'wedding_date' => '2026-12-12',
            'plan' => 'pro',
        ]);

        $invite = WeddingInvite::create([
            'wedding_id' => $wedding->id,
            'token' => 'tok-' . bin2hex(random_bytes(8)),
            'expires_at' => now()->addDays(7),
            'created_by' => User::factory()->create()->id,
        ]);

        $this->get(route('invite.accept', $invite->token))
            ->assertOk()
            ->assertSee('Anda diundang')
            // Nama lengkap tidak ikut tampil, karena link bisa saja terbawa
            // ke orang lain tanpa sengaja.
            ->assertDontSee('Siti')
            ->assertDontSee('Budi');
    }

    public function test_accepting_creates_a_partner_account_in_the_same_wedding()
    {
        [$wedding, $owner] = $this->couple();
        $invite = $this->inviteFor($wedding);

        $response = $this->post(route('invite.accept.store', $invite->token), [
            'name' => 'Siti Rahma',
            'email' => 'siti@example.com',
            'password' => 'rahasia123',
            'password_confirmation' => 'rahasia123',
        ]);

        $response->assertRedirect(route('dashboard'));

        $partner = User::where('email', 'siti@example.com')->firstOrFail();

        $this->assertSame($wedding->id, $partner->wedding_id);
        $this->assertAuthenticatedAs($partner);

        $invite->refresh();
        $this->assertNotNull($invite->accepted_at);
        $this->assertSame($partner->id, $invite->accepted_user_id);
        $this->assertFalse($invite->isPending());

        // Pasangan harus langsung bisa masuk Wedding Kit yang sama.
        $this->actingAs($partner)->get('/')->assertOk();

        // Plan menempel di wedding, jadi pasangan otomatis dapat Pro.
        $this->assertTrue($partner->fresh()->wedding->isPro());
    }

    public function test_invite_cannot_be_used_twice()
    {
        [$wedding] = $this->couple();
        $invite = $this->inviteFor($wedding);

        $this->post(route('invite.accept.store', $invite->token), [
            'name' => 'Siti',
            'email' => 'siti@example.com',
            'password' => 'rahasia123',
            'password_confirmation' => 'rahasia123',
        ])->assertRedirect(route('dashboard'));

        $this->post(route('invite.accept.store', $invite->token), [
            'name' => 'Orang Lain',
            'email' => 'lain@example.com',
            'password' => 'rahasia123',
            'password_confirmation' => 'rahasia123',
        ])->assertStatus(410);

        $this->assertNull(User::where('email', 'lain@example.com')->first());
    }

    public function test_expired_invite_is_rejected()
    {
        [$wedding] = $this->couple();
        $invite = WeddingInvite::create([
            'wedding_id' => $wedding->id,
            'token' => 'tok-' . bin2hex(random_bytes(8)),
            'expires_at' => now()->subMinute(),
            'created_by' => User::factory()->create()->id,
        ]);

        $this->get(route('invite.accept', $invite->token))->assertStatus(410);

        $this->post(route('invite.accept.store', $invite->token), [
            'name' => 'Siti',
            'email' => 'siti@example.com',
            'password' => 'rahasia123',
            'password_confirmation' => 'rahasia123',
        ])->assertStatus(410);

        $this->assertNull(User::where('email', 'siti@example.com')->first());
    }

    public function test_unknown_token_is_not_found()
    {
        $this->get(route('invite.accept', 'tidak-ada-token-ini'))->assertNotFound();
    }

    /**
     * Yang membuka tautan belum punya akun dan sering salah klik, jadi error
     * harus berupa halaman aplikasi -- bukan halaman error Laravel mentah yang
     * menampilkan trace exception.
     */
    public function test_used_invite_shows_a_friendly_page_not_a_laravel_error_page()
    {
        [$wedding] = $this->couple();
        $invite = $this->inviteFor($wedding);

        $this->post(route('invite.accept.store', $invite->token), [
            'name' => 'Siti',
            'email' => 'siti@example.com',
            'password' => 'rahasia123',
            'password_confirmation' => 'rahasia123',
        ]);

        $response = $this->get(route('invite.accept', $invite->token));

        $response->assertStatus(410);
        $response->assertSee('Link Undangan Sudah Dipakai');
        $response->assertDontSee('Exception trace');
        $response->assertDontSee('vendor/laravel');
        // Tidak boleh membocorkan siapa saja yang sudah memakainya.
        $response->assertDontSee('siti@example.com');
    }

    public function test_expired_invite_shows_a_friendly_page_and_tells_how_long_it_lasted()
    {
        [$wedding] = $this->couple();
        $invite = WeddingInvite::create([
            'wedding_id' => $wedding->id,
            'token' => 'tok-' . bin2hex(random_bytes(8)),
            'expires_at' => now()->subMinute(),
            'created_by' => User::factory()->create()->id,
        ]);

        $response = $this->get(route('invite.accept', $invite->token));

        $response->assertStatus(410);
        $response->assertSee('Link Undangan Kedaluwarsa');
        $response->assertSee(WeddingInvite::VALID_DAYS.' hari');
        $response->assertDontSee('Exception trace');
    }

    public function test_unknown_token_shows_a_friendly_page()
    {
        $response = $this->get(route('invite.accept', 'tidak-ada-token-ini'));

        $response->assertNotFound();
        $response->assertSee('Link Undangan Tidak Ditemukan');
        $response->assertDontSee('Exception trace');
    }

    /** POST ke tautan mati juga harus sopan, bukan melempar exception. */
    public function test_posting_to_a_dead_invite_shows_the_same_friendly_page()
    {
        [$wedding] = $this->couple();
        $invite = $this->inviteFor($wedding);

        $response = $this->post(route('invite.accept.store', $invite->token), [
            'name' => 'Siti',
            'email' => 'siti@example.com',
            'password' => 'rahasia123',
            'password_confirmation' => 'rahasia123',
        ]);

        // Dipakai ulang setelah accept pertama.
        $this->post(route('invite.accept.store', $invite->token), [
            'name' => 'Lain',
            'email' => 'lain@example.com',
            'password' => 'rahasia123',
            'password_confirmation' => 'rahasia123',
        ])->assertStatus(410)->assertDontSee('Exception trace');
    }

    /** Halaman undangan yang masih hidup tidak boleh ikut jadi halaman error. */
    public function test_valid_invite_still_renders_the_signup_form()
    {
        [$wedding] = $this->couple();
        $invite = $this->inviteFor($wedding);

        $this->get(route('invite.accept', $invite->token))
            ->assertOk()
            ->assertSee('Anda diundang')
            ->assertSee('name="password_confirmation"', false);
    }

    public function test_existing_email_is_rejected_and_keeps_its_own_wedding()
    {
        [$wedding] = $this->couple();
        $invite = $this->inviteFor($wedding);

        // Orang ini sudah punya akun di Wedding Kit lain. Menempelkan dia secara
        // diam-diam akan merebut aksesnya dari Wedding Kit lamanya.
        [$otherWedding, $existing] = $this->couple();

        $response = $this->from(route('invite.accept', $invite->token))
            ->post(route('invite.accept.store', $invite->token), [
                'name' => 'Nama Lama',
                'email' => $existing->email,
                'password' => 'rahasia123',
                'password_confirmation' => 'rahasia123',
            ]);

        $response->assertRedirect(route('invite.accept', $invite->token));
        $response->assertSessionHasErrors('email');

        $this->assertSame($otherWedding->id, $existing->fresh()->wedding_id);
        $this->assertGuest();
        $this->assertNull($invite->fresh()->accepted_at);
    }

    public function test_owner_can_revoke_the_invite_and_the_link_stops_working()
    {
        [$wedding, $owner] = $this->couple();
        $invite = $this->inviteFor($wedding);

        $this->actingAs($owner)
            ->delete(route('profile.partner-invites.destroy', $invite))
            ->assertRedirect(route('profile.partner-invites.index'));

        $this->get(route('invite.accept', $invite->token))->assertNotFound();
    }

    public function test_google_signup_from_the_invite_joins_the_same_wedding()
    {
        [$wedding, $owner] = $this->couple();
        $invite = $this->inviteFor($wedding, $owner);
        $this->fakeGoogleCallback('gadis@example.com', 'Gadis Pasangan');
        config(['services.google.client_id' => 'test-client']);

        $this->get("/auth/google/redirect?invite={$invite->token}");
        $this->get('/auth/google/callback')->assertRedirect(route('dashboard'));

        $partner = User::where('email', 'gadis@example.com')->firstOrFail();
        $this->assertSame($wedding->id, $partner->wedding_id);
        $this->assertSame('google', $partner->auth_provider);
        $this->assertAuthenticatedAs($partner);

        $invite->refresh();
        $this->assertNotNull($invite->accepted_at);
        $this->assertSame($partner->id, $invite->accepted_user_id);
    }

    public function test_google_login_from_the_invite_does_not_move_an_existing_account()
    {
        [$wedding, $owner] = $this->couple();
        $invite = $this->inviteFor($wedding, $owner);

        // Akun Google ini sudah punya Wedding Kit lain: undangan tidak boleh
        // memindahkannya, sama seperti alur email yang menolak email lama.
        [$otherWedding, $oldUser] = $this->couple();
        $this->fakeGoogleCallback($oldUser->email);
        config(['services.google.client_id' => 'test-client']);

        $this->get("/auth/google/redirect?invite={$invite->token}");
        $this->get('/auth/google/callback')->assertRedirect(route('dashboard'));

        $this->assertSame($otherWedding->id, $oldUser->fresh()->wedding_id);

        $invite->refresh();
        $this->assertNull($invite->accepted_at);
        $this->assertNull($invite->accepted_user_id);
    }

    public function test_google_callback_with_a_dead_invite_shows_the_friendly_page()
    {
        $this->fakeGoogleCallback('baru@example.com');
        config(['services.google.client_id' => 'test-client']);

        $this->get('/auth/google/redirect?invite=tok-salah');
        $this->get('/auth/google/callback')->assertNotFound();

        $this->assertGuest();
        $this->assertDatabaseMissing('users', ['email' => 'baru@example.com']);
    }

    public function test_reset_wedding_data_also_clears_invites()
    {
        [$wedding, $owner] = $this->couple();
        $invite = $this->inviteFor($wedding);

        $this->actingAs($owner)
            ->delete('/profile/reset-data', ['confirm' => 'RESET'])
            ->assertRedirect(route('admin.index'));

        $this->assertNull(WeddingInvite::find($invite->id));
    }
}