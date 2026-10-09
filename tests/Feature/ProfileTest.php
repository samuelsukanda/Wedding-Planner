<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Wedding;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Laravel\Socialite\Facades\Socialite;
use Mockery;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    private function user(string $email, string $provider = 'app'): User
    {
        $wedding = Wedding::create([
            'groom_name' => 'Groom',
            'bride_name' => 'Bride',
            'wedding_date' => '2027-10-07',
            'total_budget' => 100000000,
        ]);

        return User::factory()->create([
            'name' => 'Samuel Test',
            'email' => $email,
            'wedding_id' => $wedding->id,
            'auth_provider' => $provider,
        ]);
    }

    public function test_google_badge_hidden_for_app_account(): void
    {
        $user = $this->user('app@example.com', 'app');

        $this->actingAs($user)->get('/admin')
            ->assertOk()
            ->assertDontSee('Login dengan Google');
    }

    public function test_google_badge_shown_for_google_account(): void
    {
        $user = $this->user('google@example.com', 'google');

        $this->actingAs($user)->get('/admin')
            ->assertOk()
            ->assertSee('Login dengan Google');
    }

    public function test_google_callback_imports_avatar_and_marks_provider(): void
    {
        Storage::fake('public');

        $png = base64_decode(
            'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg=='
        );

        Http::fake(['*' => Http::response($png, 200, ['Content-Type' => 'image/png'])]);

        $user = $this->user('oauth@example.com', 'app');

        $googleUser = new \Laravel\Socialite\Two\User();
        $googleUser->map([
            'id' => '123',
            'nickname' => 'oauth',
            'name' => 'OAuth User',
            'email' => 'oauth@example.com',
            'avatar' => 'https://lh3.googleusercontent.com/a/abc123',
        ]);

        $provider = Mockery::mock(\Laravel\Socialite\Two\GoogleProvider::class);
        $provider->shouldReceive('user')->andReturn($googleUser);

        Socialite::shouldReceive('driver')->with('google')->andReturn($provider);

        $this->get('/auth/google/callback')->assertRedirect(route('dashboard'));

        $user->refresh();

        $this->assertSame('google', $user->auth_provider);
        $this->assertTrue($user->isGoogle());
        $this->assertNotNull($user->profile_photo);
        $this->assertStringStartsWith('avatars/', $user->profile_photo);
        Storage::disk('public')->assertExists($user->profile_photo);
    }

    public function test_google_callback_does_not_overwrite_existing_photo(): void
    {
        Storage::fake('public');

        $png = base64_decode(
            'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg=='
        );

        Http::fake(['*' => Http::response($png, 200, ['Content-Type' => 'image/png'])]);

        $user = $this->user('manual@example.com', 'app');
        Storage::disk('public')->put('avatars/manual.png', $png);
        $user->forceFill(['profile_photo' => 'avatars/manual.png'])->save();

        $googleUser = new \Laravel\Socialite\Two\User();
        $googleUser->map([
            'id' => '456',
            'nickname' => 'manual',
            'name' => 'Manual Photo',
            'email' => 'manual@example.com',
            'avatar' => 'https://lh3.googleusercontent.com/a/xyz789',
        ]);

        $provider = Mockery::mock(\Laravel\Socialite\Two\GoogleProvider::class);
        $provider->shouldReceive('user')->andReturn($googleUser);

        Socialite::shouldReceive('driver')->with('google')->andReturn($provider);

        $this->get('/auth/google/callback')->assertRedirect(route('dashboard'));

        $user->refresh();

        $this->assertSame('avatars/manual.png', $user->profile_photo);
        $this->assertSame('google', $user->auth_provider);
    }

    public function test_google_callback_skips_avatar_when_response_is_not_image(): void
    {
        Storage::fake('public');

        Http::fake(['*' => Http::response('<html>error</html>', 200, ['Content-Type' => 'text/html'])]);

        $user = $this->user('noimage@example.com', 'app');

        $googleUser = new \Laravel\Socialite\Two\User();
        $googleUser->map([
            'id' => '789',
            'nickname' => 'noimage',
            'name' => 'No Image',
            'email' => 'noimage@example.com',
            'avatar' => 'https://lh3.googleusercontent.com/a/broken',
        ]);

        $provider = Mockery::mock(\Laravel\Socialite\Two\GoogleProvider::class);
        $provider->shouldReceive('user')->andReturn($googleUser);

        Socialite::shouldReceive('driver')->with('google')->andReturn($provider);

        $this->get('/auth/google/callback')->assertRedirect(route('dashboard'));

        $user->refresh();

        $this->assertNull($user->profile_photo);
        $this->assertSame('google', $user->auth_provider);
    }
}