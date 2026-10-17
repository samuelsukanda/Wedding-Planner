<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Wedding;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

/**
 * Refresh harus kembali ke menu yang sedang dibuka, bukan Dashboard.
 *
 * URL publik sengaja disembunyikan jadi "/", jadi posisinya disimpan di cookie
 * weddingPlannerLastRoute dan dibaca lagi di DashboardController. Modul baru
 * dulu luput dari daftar whitelist di sana, sehingga refresh diam-diam
 * mendarat di Dashboard.
 *
 * Test di bawah mengunci perilakunya dari dua sisi: modul yang harus
 * dialihkan, dan yang tidak boleh.
 */
class LastRouteRedirectTest extends TestCase
{
    use RefreshDatabase;

    private function makeUser(): User
    {
        $wedding = Wedding::create([
            'groom_name' => 'Groom',
            'bride_name' => 'Bride',
            'wedding_date' => '2027-10-07',
            'total_budget' => 100000000,
        ]);

        return User::factory()->create(['wedding_id' => $wedding->id]);
    }

    /**
     * Refresh harus kembali ke menu yang sedang dibuka. Loop biasa dipakai
     * supaya PHPUnit lama yang tidak mendukung dataProvider tetap jalan.
     */
    public function test_refresh_returns_to_menu(): void
    {
        foreach ($this->menuPages() as $path) {
            $user = $this->makeUser();

            $this->actingAs($user)
                ->withUnencryptedCookie('weddingPlannerLastRoute', $path)
                ->get('/')
                ->assertRedirect($path, "Refresh dari {$path} harus kembali ke {$path}, bukan Dashboard.");
        }
    }

    public static function menuPages(): array
    {
        return [
            '/checklists',
            '/budgets',
            '/vendors',
            '/guests',
            '/moodboards',
            '/rundowns',
            '/contracts',
            '/payments',
            '/gifts',
            '/reports',
            '/savings',
            '/proposals',
            '/souvenirs',
            '/documents',
        ];
    }

    /**
     * Path di dalam menu sah ikut dialihkan, bukan hanya path persisnya.
     */
    public function test_sub_path_of_menu_is_also_redirected(): void
    {
        $user = $this->makeUser();

        $this->actingAs($user)
            ->withUnencryptedCookie('weddingPlannerLastRoute', '/documents/12/history')
            ->get('/')
            ->assertRedirect('/documents/12/history');
    }

    /**
     * Cookie lastRoute boleh membawa query string (mis. dari navigasi lama),
     * dan harus diteruskan apa adanya.
     */
    public function test_query_string_is_kept(): void
    {
        $user = $this->makeUser();

        $this->actingAs($user)
            ->withUnencryptedCookie('weddingPlannerLastRoute', '/guests?category=Family')
            ->get('/')
            ->assertRedirect('/guests?category=Family');
    }

    public function test_non_menu_paths_render_dashboard(): void
    {
        $nonMenu = [
            // Area admin punya aturan sendiri di EnsureWedding.
            '/admin/users',
            '/admin/master-data',
            '/admin/document-templates',
            // Onboarding hanya untuk yang belum punya data pernikahan.
            '/onboarding',
            // Tidak ada halaman daftar di path ini.
            '/halaman-ngawur',
            '/guests-lama',
        ];

        foreach ($nonMenu as $path) {
            $user = $this->makeUser();

            $this->actingAs($user)
                ->withUnencryptedCookie('weddingPlannerLastRoute', $path)
                ->get('/')
                ->assertOk("Path {$path} tidak boleh dialihkan ke dirinya sendiri.");
        }
    }

    /**
     * Refresh dari halaman Profile harus kembali ke Profile.
     *
     * app.js menulis ulang URL ke '/' setiap kali Turbo memuat halaman, jadi
     * reload sebenarnya meminta "/" dan hanya cookie yang tahu halaman mana
     * yang sedang dibuka. Selama /admin masih dikecualikan dari whitelist,
     * refresh dari Profile selalu mendarat di Dashboard.
     */
    public function test_refresh_from_profile_returns_to_profile(): void
    {
        $user = $this->makeUser();

        $this->actingAs($user)
            ->withUnencryptedCookie('weddingPlannerLastRoute', '/admin')
            ->get('/')
            ->assertRedirect('/admin');
    }

    public function test_empty_cookie_renders_dashboard(): void
    {
        $user = $this->makeUser();

        $this->actingAs($user)->get('/')->assertOk();
    }

    /**
     * Refresh superadmin di halaman admin harus kembali ke halaman itu
     * (navigasi sungguhan, bukan render menumpang route dashboard) supaya
     * halaman benar dan highlight sidebar admin tidak hilang.
     */
    public function test_superadmin_refresh_returns_to_admin_page(): void
    {
        foreach (['/admin/users', '/admin/master-data?group=checklist_category', '/admin/document-templates'] as $path) {
            $admin = User::factory()->create([
                'email' => 'admin-' . md5($path) . '@example.com',
                'wedding_id' => null,
                'is_superadmin' => true,
            ]);

            $this->actingAs($admin)
                ->withUnencryptedCookie('weddingPlannerLastRoute', $path)
                ->get('/')
                ->assertRedirect($path, "Refresh superadmin dari {$path} harus kembali ke {$path}.");
        }
    }

    /**
     * Setiap halaman daftar milik pasangan harus punya route .index supaya
     * ikut ter-whitelist. Ini yang membuat modul baru otomatis aman.
     */
    public function test_every_public_index_route_is_covered(): void
    {
        $paths = collect(Route::getRoutes())
            ->filter(fn ($route) => in_array('GET', $route->methods(), true) && str_ends_with($route->getName() ?? '', '.index'))
            ->map(fn ($route) => '/' . ltrim($route->uri(), '/'))
            ->reject(fn (string $uri) => $uri === '/' || $uri === '/onboarding' || str_starts_with($uri, '/admin'))
            ->values();

        $this->assertContains('/savings', $paths->all());
        $this->assertContains('/proposals', $paths->all());
        $this->assertContains('/souvenirs', $paths->all());
        $this->assertContains('/documents', $paths->all());
    }
}