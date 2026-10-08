<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Wedding;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Blade;
use Tests\TestCase;

class OnboardingTest extends TestCase
{
    use RefreshDatabase;

    private const FINISH_PAYLOAD = [
        'user_name' => 'Nabila',
        'partner_name' => 'Nabila',
        'wedding_date' => '2027-10-07',
        'total_budget' => 100000000,
    ];

    public function test_registration_creates_user_and_redirects_to_onboarding(): void
    {
        $response = $this->post('/register', [
            'name' => 'Nabila',
            'email' => 'nabila@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertRedirect(route('onboarding.index'));
        $this->assertDatabaseHas('users', [
            'email' => 'nabila@example.com',
            'name' => 'Nabila',
            'is_superadmin' => false,
            'wedding_id' => null,
        ]);
    }

    public function test_registration_rejects_duplicate_email(): void
    {
        User::factory()->create(['email' => 'nabila@example.com']);

        $this->post('/register', [
            'name' => 'Other',
            'email' => 'nabila@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])->assertSessionHasErrors('email');

        $this->assertSame(1, User::where('email', 'nabila@example.com')->count());
    }

    public function test_onboarding_page_renders_all_five_steps_in_one_document(): void
    {
        $user = User::factory()->create(['name' => 'Nabila']);

        // Kelima langkah harus ada sekaligus supaya perpindahan hanya client-side.
        $this->actingAs($user)->get('/')
            ->assertOk()
            ->assertSee('Siapa nama kamu?')
            ->assertSee('Siapa nama pasanganmu?')
            ->assertSee('Kapan kalian menikah?')
            ->assertSee('target anggaran pernikahan kalian?')
            ->assertSee('Siap memulai perjalanan!')
            ->assertSee('DD-MM-YYYY');
    }

    public function test_finish_creates_wedding_and_seeds_checklist_template(): void
    {
        $user = User::factory()->create(['name' => 'Nabila']);

        $this->actingAs($user)->post('/onboarding/finish', self::FINISH_PAYLOAD)
            ->assertRedirect(route('dashboard'));

        $wedding = Wedding::first();
        $this->assertNotNull($wedding);
        $this->assertSame('Nabila', $wedding->groom_name);
        $this->assertSame('Nabila', $wedding->bride_name);
        $this->assertSame('2027-10-07', $wedding->wedding_date->toDateString());
        $this->assertSame('100000000.00', $wedding->total_budget);
        $this->assertSame(12, $wedding->checklists()->count());
        $this->assertSame($wedding->id, $user->fresh()->wedding_id);
    }

    public function test_finish_flashes_success_toast(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post('/onboarding/finish', self::FINISH_PAYLOAD)
            ->assertSessionHas('success');
    }

    public function test_finish_requires_every_field(): void
    {
        $user = User::factory()->create(['wedding_id' => null]);

        $this->actingAs($user)->post('/onboarding/finish', [])
            ->assertSessionHasErrors(['user_name', 'partner_name', 'wedding_date', 'total_budget']);

        $this->assertSame(0, Wedding::count());
    }

    public function test_finish_rejects_zero_budget(): void
    {
        $user = User::factory()->create(['wedding_id' => null]);

        $this->actingAs($user)->post('/onboarding/finish', array_merge(self::FINISH_PAYLOAD, ['total_budget' => 0]))
            ->assertSessionHasErrors('total_budget');

        $this->assertSame(0, Wedding::count());
    }

    public function test_step_progression_routes_are_gone(): void
    {
        $user = User::factory()->create(['wedding_id' => null]);

        // Pergantian langkah sekarang client-side, jadi route POST-nya dihapus.
        // /onboarding masih ada sebagai GET, jadi POST ke sana dijawab 405,
        // sedangkan /onboarding/back tidak ada sama sekali.
        $this->actingAs($user)->post('/onboarding', ['partner_name' => 'Nabila'])
            ->assertStatus(405);
        $this->actingAs($user)->post('/onboarding/back')
            ->assertNotFound();
    }

    public function test_user_without_wedding_is_redirected_to_onboarding(): void
    {
        $user = User::factory()->create(['wedding_id' => null]);

        $this->actingAs($user)->get('/guests')->assertRedirect(route('onboarding.index'));
    }

    public function test_user_with_wedding_is_not_blocked(): void
    {
        $wedding = Wedding::create([
            'groom_name' => 'Nabila',
            'bride_name' => 'Nabila',
            'wedding_date' => '2027-10-07',
            'total_budget' => 100000000,
        ]);
        $user = User::factory()->create(['wedding_id' => $wedding->id]);

        $this->actingAs($user)->get('/guests')->assertOk();
    }

    public function test_finished_onboarding_cannot_be_reopened(): void
    {
        $wedding = Wedding::create([
            'groom_name' => 'Nabila',
            'bride_name' => 'Nabila',
            'wedding_date' => '2027-10-07',
            'total_budget' => 100000000,
        ]);
        $user = User::factory()->create(['wedding_id' => $wedding->id]);

        $this->actingAs($user)->get('/onboarding')->assertRedirect(route('dashboard'));
        $this->actingAs($user)->post('/onboarding/finish', self::FINISH_PAYLOAD)
            ->assertRedirect(route('dashboard'));

        $this->assertSame(1, Wedding::count());
    }

    public function test_google_login_shows_message_when_not_configured(): void
    {
        config(['services.google.client_id' => null]);

        $this->get('/auth/google/redirect')
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors('email');
    }

    public function test_flash_toast_renders_success_toast_on_dashboard(): void
    {
        $wedding = Wedding::create([
            'groom_name' => 'Nabila',
            'bride_name' => 'Nabila',
            'wedding_date' => '2027-10-07',
            'total_budget' => 100000000,
        ]);
        $user = User::factory()->create(['wedding_id' => $wedding->id]);

        $this->actingAs($user)
            ->withSession(['success' => 'Data berhasil disimpan'])
            ->get('/')
            ->assertOk()
            ->assertSee('Data berhasil disimpan')
            ->assertSee('data-flash-auto', false);
    }

public function test_flash_toast_auto_dismisses_success_error_and_validation(): void
    {
        // Komponen diuji langsung supaya tidak bergantung pada plumbing session.
        // Nilai dikirim lewat array data karena parser atribut Blade tidak
        // menangani spasi di dalam binding atribut.
        // Pencocokan memakai regex pada tag pembuka, bukan substring mentah,
        // karena selector di dalam <script> juga memuat nama atribut itu.
        $autoToastOnElement = '/<div[^>]*\bdata-flash-auto\b/';

        $error = Blade::render('<x-flash-toast :error="$text" />', ['text' => 'Gagal menyimpan']);
        $this->assertStringContainsString('Gagal menyimpan', $error);
        $this->assertSame(1, preg_match($autoToastOnElement, $error));

        $validation = Blade::render('<x-flash-toast :messages="$list" />', [
            // Bentuk sama dengan output ViewErrorBag::all() yang dikirim layout.
            // Objek ViewErrorBag sendiri tidak bisa dioper lewat Blade::render(),
            // jalur aslinya sudah diverifikasi lewat browser.
            'list' => ['partner_name' => ['Nama pasangan wajib diisi.']],
        ]);
        $this->assertStringContainsString('Mohon lengkapi data berikut', $validation);
        $this->assertStringContainsString('Nama pasangan wajib diisi.', $validation);
        $this->assertSame(1, preg_match($autoToastOnElement, $validation));

        $success = Blade::render('<x-flash-toast :success="$text" />', ['text' => 'Berhasil disimpan']);
        $this->assertStringContainsString('Berhasil disimpan', $success);
        $this->assertSame(1, preg_match($autoToastOnElement, $success));

        // Tanpa pesan tidak ada yang dirender sama sekali.
        $empty = Blade::render('<x-flash-toast />');
        $this->assertStringNotContainsString('data-flash', $empty);
    }
}
