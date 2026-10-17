<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * Superadmin harus selalu bisa dikembalikan ke kredensial default dengan
 * `db:seed`, termasuk setelah password-nya berubah. Kalau tidak, akun
 * yang "di-seed ulang" tidak bisa dipakai dan harus di-reset manual.
 */
class SuperadminSeedTest extends TestCase
{
    use RefreshDatabase;

    public function test_seeder_creates_superadmin_without_wedding(): void
    {
        $this->seed(DatabaseSeeder::class);

        $admin = User::where('email', 'admin@weddingplanner.web.id')->first();

        $this->assertNotNull($admin);
        $this->assertTrue($admin->is_superadmin);
        $this->assertNull($admin->wedding_id, 'Superadmin tidak punya Wedding Kit.');
        $this->assertTrue(Hash::check('lopyu', $admin->password));
    }

    public function test_reseeding_restores_the_default_password(): void
    {
        $this->seed(DatabaseSeeder::class);

        $admin = User::where('email', 'admin@weddingplanner.web.id')->first();
        $admin->update(['password' => 'PasswordYangBerubah']);
        $this->assertFalse(Hash::check('lopyu', $admin->fresh()->password));

        // Jalankan seeder kedua kalinya.
        $this->seed(DatabaseSeeder::class);

        $this->assertTrue(
            Hash::check('lopyu', $admin->fresh()->password),
            'db:seed harus mengembalikan password superadmin ke default.'
        );
    }

    public function test_reseeding_promotes_a_plain_user_back_to_superadmin(): void
    {
        $this->seed(DatabaseSeeder::class);

        User::where('email', 'admin@weddingplanner.web.id')
            ->update(['is_superadmin' => false]);

        $this->seed(DatabaseSeeder::class);

        $this->assertTrue(
            User::where('email', 'admin@weddingplanner.web.id')->first()->is_superadmin
        );
    }
}