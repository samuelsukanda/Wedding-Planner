<?php

namespace Tests\Feature;

use App\Support\WeddingkuParser;
use Tests\TestCase;

/**
 * Parser diuji terhadap halaman Weddingku asli yang disimpan sebagai fixture.
 *
 * Yang dijaga di sini bukan cuma "data terbaca", tapi jebakan situs:
 * sosial media di footer adalah akun Weddingku, bukan vendor, dan wa.me
 * yang ada milik tim Weddingku (tombol Chat with us).
 */
class WeddingkuParserTest extends TestCase
{
    private function fixture(): string
    {
        return file_get_contents(__DIR__ . '/../Fixtures/weddingku-vendor.html');
    }

    private function parse(): array
    {
        return (new WeddingkuParser)->parse(
            $this->fixture(),
            'https://www.weddingku.com/wedding-vendors/hotel-ballroom/the-apurva-kempinski-bali'
        );
    }

    private function venueFixture(): string
    {
        return file_get_contents(__DIR__ . '/../Fixtures/weddingku-venue.json');
    }

    private function albumFixture(): string
    {
        return file_get_contents(__DIR__ . '/../Fixtures/weddingku-album.json');
    }

    private function reviewFixture(): string
    {
        return file_get_contents(__DIR__ . '/../Fixtures/weddingku-reviews.json');
    }

    public function test_extracts_identity_and_location(): void
    {
        $data = $this->parse();

        $this->assertSame('The Apurva Kempinski Bali', $data['name']);
        $this->assertSame('Hotel Wedding Venue', $data['category']);
        $this->assertSame('Bali', $data['city']);
        $this->assertStringContainsString('Nusa Dua', (string) $data['address']);
        $this->assertSame('the-apurva-kempinski-bali', $data['slug']);
        $this->assertNotNull($data['photo']);
    }

    public function test_extracts_rating_and_review_count(): void
    {
        $data = $this->parse();

        $this->assertIsFloat($data['rating']);
        $this->assertIsInt($data['review_count']);
        $this->assertGreaterThanOrEqual(0, $data['rating']);
    }

    /**
     * Harga venue hanya ada di dalam komentar HTML dan isinya identik untuk
     * semua hotel, jadi TIDAK boleh dipakai: lebih baik kosong daripada
     * menampilkan harga yang sama salah ke setiap hotel.
     */
    public function test_commented_venue_prices_are_ignored(): void
    {
        $data = $this->parse();

        $this->assertArrayNotHasKey('venue_rooms', $data);
        $this->assertArrayNotHasKey('starting_price', $data);
        $this->assertArrayNotHasKey('price_label', $data);
        $this->assertStringNotContainsString('750.000', json_encode($data));
    }

    public function test_extracts_vendor_social_not_weddingku_accounts(): void
    {
        $data = $this->parse();

        // IG vendor diambil dari div.sosmed (kempinskibali).
        $this->assertSame('https://www.instagram.com/kempinskibali', $data['instagram']);

        // Jebakan: footer "Follow Us" Weddingku tidak boleh ikut.
        $this->assertStringNotContainsString('instagram.com/weddingku', (string) $data['instagram']);
        $this->assertNull($data['whatsapp'], 'wa.me yang ada milik Weddingku, bukan vendor.');
    }

    public function test_returns_null_when_page_has_no_vendor_block(): void
    {
        $this->assertNull((new WeddingkuParser)->parse('<html><body>404</body></html>', 'https://x.test/a/b'));
    }

    /**
     * Microdata telephone kadang diisi nomor chat-with-us Weddingku sendiri
     * (628119528988), bukan nomor vendor. Kalau ikut tersimpan, semua vendor
     * tanpa nomor akan menampilkan WA yang salah.
     */
    public function test_weddingku_own_phone_is_discarded(): void
    {
        $html = '<html><body><div itemscope itemtype="http://schema.org/ProfessionalService">'
            . '<span itemprop="name">Contoh Vendor</span>'
            . '<meta itemprop="telephone" content="628119528988">'
            . '</div></body></html>';

        $data = (new WeddingkuParser)->parse($html, 'https://www.weddingku.com/wedding-vendors/catering/contoh');

        $this->assertNotNull($data);
        $this->assertNull($data['phone']);
    }

    /**
     * ID internal Weddingku dibutuhkan sebagai parameter `i` untuk memanggil
     * endpoint /directory/ajax/venue_new.asp. Tanpa ini pricelist terlewat.
     */
    public function test_extracts_weddingku_vendor_id(): void
    {
        $data = $this->parse();

        $this->assertSame(84631, $data['vendor_id']);
    }

    public function test_vendor_id_is_null_when_page_has_none(): void
    {
        $html = '<html><body><div itemscope itemtype="http://schema.org/ProfessionalService">'
            . '<span itemprop="name">Contoh Vendor</span></div></body></html>';

        $data = (new WeddingkuParser)->parse($html, 'https://www.weddingku.com/wedding-vendors/catering/contoh');

        $this->assertNotNull($data);
        $this->assertNull($data['vendor_id']);
    }

    /**
     * Pricelist diambil dari endpoint venue_new.asp yang berada di
     * /directory/ajax/ (tidak dilarang robots.txt). Fixture adalah response
     * asli dari The Apurva Kempinski Bali.
     */
    public function test_parses_venue_pricelist_from_ajax_endpoint(): void
    {
        $prices = (new WeddingkuParser)->parseVenues($this->venueFixture());

        $this->assertCount(3, $prices['venue_rooms']);
        $this->assertSame('Amara or Kimaya Cliff Chapel', $prices['venue_rooms'][0]['name']);
        $this->assertSame('Seated 2-30', $prices['venue_rooms'][0]['capacity']);

        // Ruang ketiga punya seated DAN standing.
        $this->assertStringContainsString('Seated 50-300', $prices['venue_rooms'][2]['capacity']);
        $this->assertStringContainsString('Standing 200-400', $prices['venue_rooms'][2]['capacity']);

        // Harga terendah antar ruangan yang punya harga (standing 1.5jt < seated 2.299jt).
        $this->assertSame(1500000.0, (float) $prices['starting_price']);
        $this->assertSame('Mulai Rp 1.500.000', $prices['price_label']);
    }

    /** Vendor non-venue (mis. MUA) mendapat venues: [] dari endpoint. */
    public function test_empty_venue_response_yields_no_prices(): void
    {
        $empty = '{"responsestatus":{"code":"00"},"venues":[],"page_info":{"current_page":1}}';

        $prices = (new WeddingkuParser)->parseVenues($empty);

        $this->assertSame([], $prices['venue_rooms']);
        $this->assertNull($prices['starting_price']);
        $this->assertNull($prices['price_label']);
    }

    /** Endpoint bisa balas halaman error HTML, bukan JSON. */
    public function test_non_json_venue_response_does_not_crash(): void
    {
        $prices = (new WeddingkuParser)->parseVenues('<html><body>Service error</body></html>');

        $this->assertSame([], $prices['venue_rooms']);
        $this->assertNull($prices['starting_price']);
    }

    /**
     * Album TIDAK pernah ada di HTML server-side (ul.collections kosong),
     * muat lewat /directory/ajax/collections.asp. Yang disimpan URL-nya saja.
     */
    public function test_parses_album_urls_from_collections_endpoint(): void
    {
        $album = (new WeddingkuParser)->parseAlbum($this->albumFixture());

        $this->assertCount(3, $album['album']);
        $this->assertSame(
            'https://images.weddingku.com/images/upload/partners/84631/product/98403/images800/98403-va6dth2ssnuq.jpg',
            $album['album'][0]
        );

        // Total asli dari endpoint (35), bukan jumlah yang kita ambil.
        $this->assertSame(35, $album['album_count']);
    }

    /** Disimpan maksimum 10 foto per vendor supaya tabel tidak membengkak. */
    public function test_album_is_capped_per_vendor(): void
    {
        $items = [];
        for ($i = 0; $i < 30; $i++) {
            $items[] = ['media_preview' => ['image_media' => ['src' => "https://images.weddingku.com/x/{$i}.jpg"]]];
        }
        $json = json_encode(['inspirations' => $items, 'page_info' => ['count' => ['record' => 30]]]);

        $album = (new WeddingkuParser)->parseAlbum($json);

        $this->assertCount(10, $album['album']);
        $this->assertSame(30, $album['album_count']);
    }

    /** Escaped slash di JSON harus dikembalikan sebagai URL biasa. */
    public function test_album_urls_are_not_escaped(): void
    {
        $album = (new WeddingkuParser)->parseAlbum($this->albumFixture());

        foreach ($album['album'] as $url) {
            $this->assertStringNotContainsString('\\/', $url);
        }
    }

    public function test_empty_album_response_yields_nothing(): void
    {
        $album = (new WeddingkuParser)->parseAlbum('{"inspirations":[]}');

        $this->assertSame([], $album['album']);
        $this->assertNull($album['album_count']);
    }

    public function test_non_json_album_response_does_not_crash(): void
    {
        $album = (new WeddingkuParser)->parseAlbum('<html><body>Service</body></html>');

        $this->assertSame([], $album['album']);
    }

    /** Teks review harus bersih dari entitas HTML. */
    public function test_parses_reviews_and_decodes_entities(): void
    {
        $reviews = (new WeddingkuParser)->parseReviews($this->reviewFixture());

        $this->assertCount(2, $reviews['reviews']);
        $this->assertSame('Aulika & Kiki', $reviews['reviews'][0]['title']);
        $this->assertStringNotContainsString('&amp;', $reviews['reviews'][0]['title']);
        $this->assertStringNotContainsString('&amp;', $reviews['reviews'][0]['content']);
        $this->assertSame('2020-12-02', $reviews['reviews'][0]['date']);
    }

    /**
     * Jumlah review harus dari page_info, bukan panjang array: array hanya
     * satu halaman, sedangkan total bisa lebih besar (di sini 13 dari 2).
     */
    public function test_review_count_uses_total_not_page_length(): void
    {
        $reviews = (new WeddingkuParser)->parseReviews($this->reviewFixture());

        $this->assertSame(13, $reviews['review_count']);
    }

    /**
     * Rating diambil dari rata-rata 4 skor per review. Angka rating di HTML
     * halaman vendor selalu 0.0, jadi sumber satu-satunya ini.
     */
    public function test_rating_comes_from_average_review_scores(): void
    {
        $items = [
            ['quality' => 5, 'service' => 5, 'professionalism' => 5, 'price' => 5, 'content' => 'a', 'title' => 'x'],
            ['quality' => 4, 'service' => 4, 'professionalism' => 4, 'price' => 4, 'content' => 'b', 'title' => 'y'],
        ];
        $json = json_encode(['reviews' => $items, 'page_info' => ['count' => ['record' => 2]]]);

        $result = (new WeddingkuParser)->parseReviews($json);

        $this->assertSame(4.5, $result['rating']);
    }

    /** Review tanpa skor tidak boleh mengarang rating. */
    public function test_reviews_without_scores_yield_null_rating(): void
    {
        $json = json_encode([
            'reviews' => [['quality' => 6, 'service' => 7, 'professionalism' => 8, 'price' => 9, 'content' => 'a']],
            'page_info' => ['count' => ['record' => 1]],
        ]);

        $result = (new WeddingkuParser)->parseReviews($json);

        $this->assertCount(1, $result['reviews'], 'Review tetap diambil walau tanpa skor.');
        $this->assertNull($result['rating']);
    }

    /** Skor di luar 1-5 harus diabaikan, bukan ikut dihitung. */
    public function test_out_of_range_scores_are_ignored(): void
    {
        $items = [
            ['quality' => 5, 'service' => 5, 'professionalism' => 5, 'price' => 5, 'content' => 'a'],
            ['quality' => 99, 'service' => 99, 'professionalism' => 99, 'price' => 99, 'content' => 'b'],
        ];
        $json = json_encode(['reviews' => $items, 'page_info' => ['count' => ['record' => 2]]]);

        $this->assertSame(5.0, (new WeddingkuParser)->parseReviews($json)['rating']);
    }

    public function test_empty_review_response_yields_nothing(): void
    {
        $reviews = (new WeddingkuParser)->parseReviews('{"reviews":[]}');

        $this->assertSame([], $reviews['reviews']);
        $this->assertNull($reviews['review_count']);
    }
}
