<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Vendor;
use App\Models\VendorCatalog;
use App\Models\Wedding;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VendorDirectoryTest extends TestCase
{
    use RefreshDatabase;

    private function makeUser(string $email = 'a@example.com'): User
    {
        $wedding = Wedding::create([
            'groom_name' => 'Groom',
            'bride_name' => 'Bride',
            'wedding_date' => '2027-10-07',
            'total_budget' => 100000000,
        ]);

        return User::factory()->create([
            'name' => 'Samuel',
            'email' => $email,
            'wedding_id' => $wedding->id,
        ]);
    }

    private function makeVendor(array $overrides = []): VendorCatalog
    {
        return VendorCatalog::create([
            'source' => 'weddingku',
            'source_url' => 'https://www.weddingku.com/wedding-vendors/hotel-ballroom/apurva',
            'slug' => 'apurva',
            'name' => 'The Apurva Kempinski Bali',
            'category' => 'Hotel Wedding Venue',
            'city' => 'Bali',
            'address' => 'Kawasan Nusa Dua, Bali',
            'phone' => '(0361) 2092288',
            'instagram' => 'https://www.instagram.com/kempinskibali',
            'rating' => 4.9,
            'review_count' => 4,
            'about' => 'Bohemian beachfront resort.',
            'photo' => 'https://images.weddingku.com/images/upload/partners/pp/x.jpg',
            'scraped_at' => now(),
            ...$overrides,
        ]);
    }

    public function test_index_lists_catalog(): void
    {
        $user = $this->makeUser();
        $this->makeVendor();

        $this->actingAs($user)->get('/cari-vendor')
            ->assertOk()
            ->assertSee('Cari Vendor')
            ->assertSee('The Apurva Kempinski Bali');
    }

    public function test_catalog_is_shared_across_weddings(): void
    {
        // Katalog tidak memakai ScopedToWedding: satu baris terlihat oleh
        // semua pasangan, bukan milik satu wedding.
        $a = $this->makeUser('a@example.com');
        $b = $this->makeUser('b@example.com');
        $this->makeVendor();

        $this->actingAs($a)->get('/cari-vendor')->assertSee('The Apurva Kempinski Bali');
        $this->actingAs($b)->get('/cari-vendor')->assertSee('The Apurva Kempinski Bali');
    }

    public function test_index_filters_by_category_city_and_search(): void
    {
        $user = $this->makeUser();
        $this->makeVendor();
        $this->makeVendor([
            'source_url' => 'https://www.weddingku.com/wedding-vendors/catering/ayana',
            'slug' => 'ayana',
            'name' => 'Catering Ayana',
            'category' => 'Catering',
            'city' => 'Bandung',
            'rating' => 4.1,
        ]);

        $this->actingAs($user)->get('/cari-vendor?category=Catering')
            ->assertSee('Catering Ayana')
            ->assertDontSee('The Apurva Kempinski Bali');

        $this->actingAs($user)->get('/cari-vendor?city=Bandung')
            ->assertSee('Catering Ayana')
            ->assertDontSee('The Apurva Kempinski Bali');

        $this->actingAs($user)->get('/cari-vendor?q=ayana')->assertSee('Catering Ayana');
    }

    public function test_show_page_displays_contact_and_description(): void
    {
        $user = $this->makeUser();
        $vendor = $this->makeVendor();

        $this->actingAs($user)->get('/cari-vendor/' . $vendor->slug)
            ->assertOk()
            ->assertSee('The Apurva Kempinski Bali')
            ->assertSee('Nusa Dua')
            ->assertSee('kempinskibali')
            ->assertSee('beachfront resort');
    }

    public function test_vendor_can_be_saved_to_vendor_management(): void
    {
        $user = $this->makeUser();
        $vendor = $this->makeVendor();

        $this->actingAs($user)->post('/cari-vendor/' . $vendor->slug)
            ->assertRedirect(route('vendors.index'));

        $this->assertDatabaseHas('vendors', [
            'wedding_id' => $user->wedding_id,
            'name' => 'The Apurva Kempinski Bali',
            'category' => 'Hotel Wedding Venue',
            'instagram' => 'https://www.instagram.com/kempinskibali',
            'address' => 'Kawasan Nusa Dua, Bali',
        ]);
    }

    public function test_saving_twice_does_not_duplicate(): void
    {
        $user = $this->makeUser();
        $vendor = $this->makeVendor();

        $this->actingAs($user)->post('/cari-vendor/' . $vendor->slug);
        $this->actingAs($user)->post('/cari-vendor/' . $vendor->slug)
            ->assertSessionHas('error');

        $this->assertSame(1, Vendor::count());
    }

    public function test_saved_vendor_belongs_only_to_saving_wedding(): void
    {
        $a = $this->makeUser('a@example.com');
        $b = $this->makeUser('b@example.com');
        $vendor = $this->makeVendor();

        $this->actingAs($a)->post('/cari-vendor/' . $vendor->slug);

        $this->assertSame(1, Vendor::where('wedding_id', $a->wedding_id)->count());
        $this->assertSame(0, Vendor::where('wedding_id', $b->wedding_id)->count());
    }

    /**
     * Menu wajib punya route .index agar whitelist refresh di DashboardController
     * mengenali /cari-vendor; kalau tidak, refresh jatuh ke menu sebelumnya.
     */
    public function test_route_is_registered_as_index_for_refresh_whitelist(): void
    {
        $this->assertTrue(
            collect(\Illuminate\Support\Facades\Route::getRoutes())
                ->contains(fn ($route) => $route->getName() === 'vendor-directory.index'
                    && $route->uri() === 'cari-vendor'
                    && in_array('GET', $route->methods(), true))
        );
    }
}
