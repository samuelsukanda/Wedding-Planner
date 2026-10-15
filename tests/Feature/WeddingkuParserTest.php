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
}
