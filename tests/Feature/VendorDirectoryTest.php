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
            // Cari Vendor kini fitur Pro yang dikunci mati. Test di file ini
            // menguji isi katalog, bukan kontrol akses, jadi akunnya dibuat
            // Pro supaya halamannya tetap bisa dirender.
            'plan' => 'pro',
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
            'vendor_id' => 84631,
            'starting_price' => 1500000,
            'price_label' => 'Mulai Rp 1.500.000',
            'venue_rooms' => [
                ['name' => 'Amara or Kimaya Cliff Chapel', 'capacity' => 'Seated 2-30', 'price' => 2299000],
                ['name' => 'Ocean Front Lawn', 'capacity' => 'Seated 50-300 / Standing 200-400', 'price' => 1500000],
            ],
            'about' => 'Bohemian beachfront resort.',
            'photo' => 'https://images.weddingku.com/images/upload/partners/pp/x.jpg',
            'scraped_at' => now(),
            ...$overrides,
        ]);
    }

    private function makeVendorWithMedia(array $overrides = []): VendorCatalog
    {
        return $this->makeVendor([
            'album' => [
                'https://images.weddingku.com/images/upload/partners/84631/a.jpg',
                'https://images.weddingku.com/images/upload/partners/84631/b.jpg',
            ],
            'album_count' => 35,
            'reviews' => [
                ['title' => 'Aulika & Kiki', 'content' => 'Makasih banget timnya.', 'date' => '2020-12-02'],
            ],
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

    public function test_show_page_displays_contact_description_and_pricelist(): void
    {
        $user = $this->makeUser();
        $vendor = $this->makeVendor();

        $this->actingAs($user)->get('/cari-vendor/' . $vendor->slug)
            ->assertOk()
            ->assertSee('The Apurva Kempinski Bali')
            ->assertSee('Nusa Dua')
            ->assertSee('kempinskibali')
            ->assertSee('beachfront resort')
            // Pricelist dari endpoint venue_new.asp.
            ->assertSee('Paket &amp; Harga', false)
            ->assertSee('Amara or Kimaya Cliff Chapel')
            ->assertSee('Seated 2-30')
            ->assertSee('2.299.000');
    }

    /** Album & review dari endpoint collections.asp / review.asp. */
    public function test_show_page_renders_album_and_reviews(): void
    {
        $user = $this->makeUser();
        $vendor = $this->makeVendorWithMedia();

        $this->actingAs($user)->get('/cari-vendor/' . $vendor->slug)
            ->assertOk()
            ->assertSee('Album')
            ->assertSee('images/upload/partners/84631/a.jpg')
            ->assertSee('Aulika &amp; Kiki', false)
            ->assertSee('Makasih banget timnya.')
            ->assertSee('2020-12-02');
    }

    /**
     * Grid album hanya 6 foto; sisanya harus ada tombol "Lihat Semua" yang
     * membuka lightbox berisi seluruh foto.
     */
    public function test_album_grid_shows_six_photos_with_see_all_button(): void
    {
        $user = $this->makeUser();
        $photos = [];
        for ($i = 1; $i <= 10; $i++) {
            $photos[] = "https://images.weddingku.com/album/photo-{$i}.jpg";
        }
        $vendor = $this->makeVendorWithMedia(['album' => $photos, 'album_count' => 10]);

        $html = $this->actingAs($user)->get('/cari-vendor/' . $vendor->slug)->assertOk()->getContent();

        // Hanya <img src> di grid. Lightbox memakai binding Alpine
        // (:src='[...]') yang memuat seluruh URL, jadi tidak ikut dihitung.
        preg_match_all('/<img[^>]*src="[^"]*photo-(\d+)\.jpg"/', $html, $matches);
        $shown = array_values(array_unique(array_map('intval', $matches[1])));

        $this->assertCount(6, $shown, 'Grid album harus menampilkan tepat 6 foto.');
        $this->assertSame([1, 2, 3, 4, 5, 6], $shown, 'Grid harus 6 foto pertama.');

        $this->assertStringContainsString('Lihat Semua', $html);
        $this->assertStringContainsString('10 foto', $html);
    }

    /** Album yang sudah <= 6 foto tidak perlu tombol "Lihat Semua". */
    public function test_see_all_button_hidden_when_album_has_six_or_fewer(): void
    {
        $user = $this->makeUser();
        $photos = [];
        for ($i = 1; $i <= 4; $i++) {
            $photos[] = "https://images.weddingku.com/album/small-{$i}.jpg";
        }
        $vendor = $this->makeVendorWithMedia(['album' => $photos, 'album_count' => 4]);

        $this->actingAs($user)->get('/cari-vendor/' . $vendor->slug)
            ->assertOk()
            ->assertDontSee('Lihat Semua', false);
    }

/**
     * Rincian ruangan dari katalog harus ikut tersimpan sebagai detail paket.
     * Tanpa ini panel "Detail & Isi Paketan" selalu kosong padahal katalog
     * punya pricelist.
     */
    public function test_saving_catalog_vendor_carries_over_package_details(): void
    {
        $user = $this->makeUser();
        $vendor = $this->makeVendor();

        $this->actingAs($user)->post('/cari-vendor/' . $vendor->slug)
            ->assertRedirect(route('vendors.index'));

        $saved = Vendor::firstWhere('name', 'The Apurva Kempinski Bali');

        $this->assertNotNull($saved);
        $this->assertCount(2, $saved->packageItems, 'Semua ruangan harus ikut tersimpan.');

        $item = $saved->packageItems->firstWhere('name', 'Amara or Kimaya Cliff Chapel');

        $this->assertNotNull($item, 'Nama ruangan harus jadi nama item paket.');
        $this->assertStringContainsString('Seated 2-30', $item->type);
        $this->assertStringContainsString('2.299.000', $item->type);

        // Harga terendah ikut tersimpan di kolom price vendor.
        $this->assertEquals(1500000, (float) $saved->price);
    }

    /** Vendor tanpa pricelist tidak boleh dibuatkan item paket kosong. */
    public function test_saving_vendor_without_rooms_creates_no_package_items(): void
    {
        $user = $this->makeUser();
        $vendor = $this->makeVendor([
            'venue_rooms' => null,
            'starting_price' => null,
        ]);

        $this->actingAs($user)->post('/cari-vendor/' . $vendor->slug);

        $saved = Vendor::firstWhere('name', 'The Apurva Kempinski Bali');

        $this->assertCount(0, $saved->packageItems);
    }

/**
     * Harga di item paket harus di baris sendiri di bawah kapasitas, bukan
     * disambung tanda bullet di baris yang sama.
     */
    public function test_package_item_puts_price_on_its_own_line(): void
    {
        $user = $this->makeUser();
        $vendor = $this->makeVendor();

        $this->actingAs($user)->post('/cari-vendor/' . $vendor->slug);

        $item = Vendor::firstWhere('name', 'The Apurva Kempinski Bali')
            ->packageItems->firstWhere('name', 'Amara or Kimaya Cliff Chapel');

        $this->assertStringContainsString("\n", $item->type, 'Harga harus di baris berikutnya.');
        $this->assertSame('Seated 2-30 pax', explode("\n", $item->type)[0]);
        $this->assertSame('Rp 2.299.000', explode("\n", $item->type)[1]);
    }

    /**
     * Tombol hapus item paket harus benar-benar terikat ke id item.
     * onclick= biasa merender "item.id" sebagai teks sehingga tidak pernah
     * menemukan form-nya, dan tombolnya diam-diam mati.
     */
    public function test_package_item_delete_button_uses_alpine_binding(): void
    {
        $user = $this->makeUser();
        $this->makeUser('b@example.com');

        $vendor = $user->wedding->vendors()->create([
            'name' => 'Vendor Paket Uji',
            'category' => 'Catering',
            'booking_status' => 'Not Contacted',
        ]);

        $html = $this->actingAs($user)->get('/vendors')->assertOk()->getContent();

        // @click dievaluasi Alpine; onclick= tidak pernah dipakai untuk ini.
        $this->assertStringContainsString(
            "@click=\"confirmDelete('del-pkg-' + item.id, 'item paketan ini')\"",
            $html
        );
        $this->assertStringNotContainsString('onclick="confirmDelete(\'del-pkg-\' + item.id', $html);
    }

    /** Vendor asal katalog punya slug, jadi judul di modal bisa ditautkan. */
    public function test_catalog_vendor_exposes_slug_for_detail_link(): void
    {
        $user = $this->makeUser();
        $catalog = $this->makeVendor(['name' => 'The Apurva Kempinski Bali']);

        $user->wedding->vendors()->create([
            'name' => $catalog->name,
            'category' => 'Catering',
            'booking_status' => 'Not Contacted',
        ]);

        $html = $this->actingAs($user)->get('/vendors')->assertOk()->getContent();

        // JSON-nya berada di dalam atribut @click, jadi kutipnya ter-escape.
        $this->assertStringContainsString('catalog_slug&quot;:&quot;apurva', $html);
    }

    /** Vendor manual (bukan dari katalog) tidak boleh dapat tautan palsu. */
    public function test_manual_vendor_has_no_catalog_slug(): void
    {
        $user = $this->makeUser();

        $user->wedding->vendors()->create([
            'name' => 'Vendor Manual',
            'category' => 'Catering',
            'booking_status' => 'Not Contacted',
        ]);

        $html = $this->actingAs($user)->get('/vendors')->assertOk()->getContent();

        $this->assertStringContainsString('catalog_slug&quot;:null', $html);
    }

/**
     * Opsi urut harus punya value berbeda semua. Semula "Urutkan" dan
     * "Nama (A-Z)" sama-sama value="", sehingga memilih yang kedua tidak
     * mengubah apa pun dan yang pertama selalu ditampilkan.
     */
    public function test_sort_options_have_distinct_values(): void
    {
        $user = $this->makeUser();
        $this->makeVendor();

        $html = $this->actingAs($user)->get('/cari-vendor')->assertOk()->getContent();

        $this->assertMatchesRegularExpression('~<select name="sort".*?</select>~s', $html, 'Select urut tidak ditemukan.');

        preg_match('~<select name="sort".*?</select>~s', $html, $block);
        preg_match_all('~<option value="([^"]*)"~', $block[0], $values);

        $values = array_values(array_filter($values[1], fn ($v) => $v !== ''));

        $this->assertSame(
            array_values(array_unique($values)),
            $values,
            'Opsi urut tidak boleh punya value yang sama.'
        );

        $this->assertStringContainsString('Nama (A–Z)', $block[0]);
        $this->assertStringNotContainsString('>Urutkan<', $block[0], 'Placeholder "Urutkan" yang duplikat harus dihapus.');
    }

    /** sort kosong berarti urut nama; sort=rating berarti rating tertinggi. */
    public function test_sort_by_rating_orders_by_rating(): void
    {
        $user = $this->makeUser();
        $this->makeVendor([
            'source_url' => 'https://www.weddingku.com/wedding-vendors/catering/a',
            'slug' => 'a',
            'name' => 'AAA Vendor',
            'category' => 'Catering',
            'city' => 'Bandung',
            'rating' => 1.0,
        ]);
        $this->makeVendor([
            'source_url' => 'https://www.weddingku.com/wedding-vendors/catering/z',
            'slug' => 'z',
            'name' => 'ZZZ Vendor',
            'category' => 'Catering',
            'city' => 'Bandung',
            'rating' => 5.0,
        ]);

        // Rating tertinggi dulu: ZZZ (5.0) sebelum AAA (1.0).
        $byRating = $this->actingAs($user)->get('/cari-vendor?sort=rating')->assertOk()->getContent();
        $this->assertLessThan(strpos($byRating, 'AAA Vendor'), strpos($byRating, 'ZZZ Vendor'));

        // Tanpa sort: urut nama, AAA dulu.
        $byName = $this->actingAs($user)->get('/cari-vendor')->assertOk()->getContent();
        $this->assertLessThan(strpos($byName, 'ZZZ Vendor'), strpos($byName, 'AAA Vendor'));
    }

/**
     * Halaman katalog tidak boleh menyebut Weddingku di teks yang terlihat
     * maupun lewat link outbound ke situsnya.
     *
     * src gambar (images.weddingku.com) TIDAK ikut diperiksa: itu file fotonya
     * sendiri, dan album justru diminta untuk ditampilkan.
     */
    public function test_no_weddingku_mention_or_link_anywhere_on_catalog_pages(): void
    {
        $user = $this->makeUser();
        $vendor = $this->makeVendorWithMedia();

        foreach (['/cari-vendor', '/cari-vendor/' . $vendor->slug] as $path) {
            $html = $this->actingAs($user)->get($path)->assertOk()->getContent();

            // Teks yang terlihat saja: buang seluruh tag HTML.
            $visible = trim(preg_replace('/\s+/', ' ', strip_tags($html)));

            $this->assertStringNotContainsStringIgnoringCase(
                'weddingku',
                $visible,
                "Halaman {$path} masih menampilkan nama sumber ke user."
            );

            // Tidak boleh ada link yang mengarah ke situs sumber.
            preg_match_all('/<a\b[^>]*href="([^"]+)"/i', $html, $matches);
            foreach ($matches[1] as $href) {
                $this->assertStringNotContainsStringIgnoringCase(
                    'weddingku',
                    $href,
                    "Halaman {$path} masih punya link ke situs sumber."
                );
            }
        }
    }

    /** Vendor tanpa album/review tidak boleh menampilkan blok kosong. */
    public function test_show_page_hides_album_and_reviews_when_absent(): void
    {
        $user = $this->makeUser();
        $vendor = $this->makeVendor(['album' => null, 'album_count' => null, 'reviews' => null]);

        $html = $this->actingAs($user)->get('/cari-vendor/' . $vendor->slug)->assertOk()->getContent();

        $this->assertStringNotContainsString('fa-images', $html);
        $this->assertStringNotContainsString('Review</h2>', $html);
    }

    /**
     * Rating tidak boleh muncul sebagai angka rekaan. Sumbernya memang 0
     * karena 483 dari 484 vendor belum punya review di Weddingku. Form rating
     * pengguna punya ikon bintangnya sendiri, jadi yang dicek: header rating
     * vendor (bintang + angka rating + jumlah review) tidak boleh dirender.
     */
    public function test_unreviewed_vendor_shows_no_fake_rating(): void
    {
        $user = $this->makeUser();
        $vendor = $this->makeVendor(['rating' => 0, 'review_count' => 0]);

        $html = $this->actingAs($user)->get('/cari-vendor/' . $vendor->slug)->assertOk()->getContent();

        $this->assertStringNotContainsString('(0 review)', $html);
    }

    /** Vendor non-venue tidak punya pricelist: blok paket jangan dirender. */
    public function test_show_page_hides_pricelist_when_absent(): void
    {
        $user = $this->makeUser();
        $vendor = $this->makeVendor([
            'starting_price' => null,
            'price_label' => null,
            'venue_rooms' => null,
        ]);

        $this->actingAs($user)->get('/cari-vendor/' . $vendor->slug)
            ->assertOk()
            ->assertDontSee('Paket &amp; Harga', false);
    }

    /** Grid katalog harus 3 kolom, sama dengan Vendor Management. */
    public function test_index_uses_three_column_grid_like_vendor_management(): void
    {
        $user = $this->makeUser();
        $this->makeVendor();

        $this->actingAs($user)->get('/cari-vendor')
            ->assertOk()
            ->assertSee('lg:grid-cols-3', false);
    }

    /**
     * Deskripsi katalog disimpan utuh tanpa dipotong; pembatasan karakter
     * hanya boleh terjadi di tampilan kartu index (UI), bukan saat menyimpan.
     */
    public function test_saving_long_description_stores_it_intact(): void
    {
        $user = $this->makeUser();
        $long = str_repeat('a', 900);
        $vendor = $this->makeVendor(['about' => $long]);

        $this->actingAs($user)->post('/cari-vendor/' . $vendor->slug);

        $saved = Vendor::firstWhere('name', 'The Apurva Kempinski Bali');

        $this->assertNotNull($saved);
        $this->assertSame($long, $saved->review);
    }

    /** Catatan review di Vendor Management tidak lagi dibatasi 250 karakter. */
    public function test_vendor_review_accepts_long_notes(): void
    {
        $user = $this->makeUser();
        $review = str_repeat('b', 400);

        $this->actingAs($user)->post('/vendors', [
            'name' => 'Vendor Catatan Panjang',
            'category' => 'Catering',
            'price' => '100000',
            'booking_status' => 'Not Contacted',
            'review' => $review,
        ])->assertSessionHasNoErrors();

        $this->assertSame($review, Vendor::firstWhere('name', 'Vendor Catatan Panjang')->review);
    }

    /** Jalur update juga menyimpan catatan panjang tanpa menolaknya. */
    public function test_vendor_review_accepts_long_notes_on_update_too(): void
    {
        $user = $this->makeUser();
        $vendor = $user->wedding->vendors()->create([
            'name' => 'Vendor Existing',
            'category' => 'Catering',
            'booking_status' => 'Not Contacted',
        ]);
        $review = str_repeat('c', 400);

        $this->actingAs($user)->put('/vendors/' . $vendor->id, [
            'name' => 'Vendor Existing',
            'category' => 'Catering',
            'price' => '100000',
            'booking_status' => 'Not Contacted',
            'review' => $review,
        ])->assertSessionHasNoErrors();

        $this->assertSame($review, $vendor->fresh()->review);
    }

    /**
     * Cari Vendor dipindah ke dalam halaman Vendor Management sebagai banner,
     * jadi menu sidebar "Cari Vendor" harus dihilang.
     */
    public function test_vendor_management_page_offers_cari_vendor_banner(): void
    {
        $user = $this->makeUser();

        $this->actingAs($user)->get('/vendors')
            ->assertOk()
            ->assertSee(route('vendor-directory.index'), false)
            ->assertSee('Temukan vendor pernikahan');
    }

    public function test_sidebar_has_no_separate_cari_vendor_menu(): void
    {
        $user = $this->makeUser();

        $response = $this->actingAs($user)->get('/vendors')->assertOk();

        // Link menu sidebar menuju /cari-vendor tidak boleh ada; yang ada
        // hanya tombol banner di dalam konten halaman.
        $this->assertStringNotContainsString(
            'fa-magnifying-glass-location',
            $response->getContent()
        );
    }

    /**
     * Menu Vendor Management harus ikut menyala saat sedang di halaman katalog,
     * karena Cari Vendor kini bagian dari Vendor Management.
     */
    public function test_vendor_management_menu_highlights_on_catalog_pages(): void
    {
        $user = $this->makeUser();
        $this->makeVendor();

        $html = $this->actingAs($user)->get('/cari-vendor')->assertOk()->getContent();

        // Link sidebar ke /vendors harus memakai gaya menu aktif.
        $this->assertMatchesRegularExpression(
            '~<a href="[^"]*/vendors"\s+class="[^"]*bg-\[#D8A7B1\]/20~',
            $html
        );
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
