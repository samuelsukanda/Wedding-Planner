<?php

namespace App\Support;

use DOMDocument;
use DOMXPath;

/**
 * Parser halaman vendor Weddingku -> array siap simpan.
 *
 * Sifat penting situs Weddingku (sudah diverifikasi):
 *
 * 1. Halaman dirender di server. Tidak perlu browser/Playwright; Http::get()
 *    cukup. Album foto TIDAK ikut karena dimuat lewat /ajax/ yang dilarang
 *    robots.txt, jadi sengaja tidak ada di sini.
 *
 * 2. Dua jebakan yang kalau salah bikin semua vendor dapat data palsu:
 *    - div.sosmed = sosial media VENDOR (miliknya sendiri)
 *    - div.socialmedia = "Follow Us" di footer, milik WEDDINGKU
 *      Keduanya punya itemprop="sameAs", jadi WAJIB scoping ke .sosmed.
 *    - Link wa.me di bawah adalah tombol "CHAT WITH US" Weddingku yang
 *      menanyakan info vendor ke Weddingku, bukan WhatsApp vendor.
 */
class WeddingkuParser
{
    /** Nomor yang dipakai Weddingku sendiri, bukan vendor. */
    private const WEDDINGKU_PHONE = '628119528988';

    /** Halaman Weddingku sendiri, bukan sosmed vendor. */
    private const WEDDINGKU_HOSTS = [
        'weddingku.com',
        'facebook.com/weddingku',
        'instagram.com/weddingku',
        'twitter.com/weddingku',
        'youtube.com/user/weddingkutv',
        'tiktok.com/@weddingku',
        'pinterest.com/weddingku',
    ];

    /**
     * @return array|null Data vendor, atau null kalau halaman tidak punya
     *                    blok vendor (mis. 404 / layout berubah).
     */
    public function parse(string $html, string $sourceUrl): ?array
    {
        $xpath = $this->xpath($html);

        // Nama diambil dari blok ProfessionalSite/ProfessionalService, bukan
        // dari <title> (isi title disambung tag SEO) dan bukan breadcrumb.
        $name = $this->firstText($xpath, '//*[@itemscope and contains(@itemtype, "ProfessionalService")]//*[@itemprop="name"]');

        if ($name === null) {
            return null;
        }

        return [
            'source' => 'weddingku',
            'source_url' => $sourceUrl,
            'vendor_id' => $this->vendorId($html),
            'slug' => $this->slugFromUrl($sourceUrl),
            'name' => $name,
            'category' => $this->categoryFromBreadcrumb($xpath),
            'city' => $this->meta($xpath, 'addressLocality'),
            'province' => $this->meta($xpath, 'addressRegion'),
            'address' => $this->meta($xpath, 'streetAddress'),
            'phone' => $this->vendorPhone($xpath),
            'instagram' => $this->social($xpath, 'instagram'),
            'whatsapp' => $this->social($xpath, 'whatsapp'),
            ...$this->parseRating($html),
            'about' => $this->firstText($xpath, '//div[contains(@class,"about-desc")]'),
            'photo' => $this->firstPhoto($xpath),
        ];
    }

    /**
     * ID internal vendor di Weddingku, ditulis sebagai `var VendorID=84631`
     * di semua sub-page vendor. Dipakai sebagai parameter `i` untuk endpoint
     * /directory/ajax/*.asp yang memuat data venue & review.
     */
    private function vendorId(string $html): ?int
    {
        if (! preg_match('/var\s+VendorID\s*=\s*(\d+)/', $html, $m)) {
            return null;
        }

        return (int) $m[1];
    }

    /**
     * Response JSON /directory/ajax/venue_new.asp -> daftar ruangan + harga.
     *
     * Endpoint ini berada di /directory/ajax/, BUKAN /ajax/ yang dilarang
     * robots.txt, jadi aman diambil. Endpoint mengembalikan venues: [] untuk
     * vendor yang bukan venue, jadi hasilnya kosong dengan aman.
     *
     * @return array{venue_rooms: array, starting_price: ?float, price_label: ?string}
     */
    public function parseVenues(string $json): array
    {
        $empty = ['venue_rooms' => [], 'starting_price' => null, 'price_label' => null];

        $data = json_decode($json, true);

        if (! is_array($data['venues'] ?? null)) {
            return $empty;
        }

        $rooms = [];
        $lowest = null;
        $lowestLabel = null;

        foreach ($data['venues'] as $venue) {
            $seated = (int) ($venue['capacity_seated']['max'] ?? 0);
            $standing = (int) ($venue['capacity_standing']['max'] ?? 0);

            // Satu ruangan bisa punya dua harga (seated & standing). Untuk
            // "mulai dari" yang benar ambil yang paling murah, bukan
            // selalu seated -- kalau tidak harga termurah ikut hilang.
            $prices = array_filter([
                (int) ($venue['price_seated'] ?? 0),
                (int) ($venue['price_standing'] ?? 0),
            ]);
            $price = $prices === [] ? 0 : min($prices);

            if ($price <= 0 && $seated === 0 && $standing === 0) {
                continue;
            }

            $capacity = [];

            if ($seated > 0) {
                $capacity[] = 'Seated ' . ($venue['capacity_seated']['min'] ?? 0) . '-' . $seated;
            }

            if ($standing > 0) {
                $capacity[] = 'Standing ' . ($venue['capacity_standing']['min'] ?? 0) . '-' . $standing;
            }

            $rooms[] = [
                'name' => trim((string) ($venue['name'] ?? 'Ruangan')),
                'capacity' => implode(' / ', $capacity),
                'price' => $price,
            ];

            if ($price > 0 && ($lowest === null || $price < $lowest)) {
                $lowest = $price;
                $lowestLabel = 'Mulai Rp ' . number_format($price, 0, ',', '.');
            }
        }

        return [
            'venue_rooms' => $rooms,
            'starting_price' => $lowest,
            'price_label' => $lowestLabel,
        ];
    }

    /**
     * Response JSON /directory/ajax/collections.asp -> URL foto album.
     *
     * PENTING: album TIDAK pernah muncul di HTML server-side (ul.collections
     * selalu kosong) karena dimuat AJAX. Yang bisa diambil hanya URL gambarnya
     * -- file foto tidak diunduh, sesuai keputusan awal.
     *
     * Setiap elemen punya media_preview.image_media.src. Folder pertama (f=0)
     * adalah album utama vendor.
     *
     * @return array{album: array, album_count: ?int}
     */
    public function parseAlbum(string $json, int $limit = 10): array
    {
        $empty = ['album' => [], 'album_count' => null];

        $data = json_decode($json, true);

        if (! is_array($data['inspirations'] ?? null)) {
            return $empty;
        }

        $album = [];
        $total = 0;

        foreach ($data['inspirations'] as $item) {
            $url = $item['media_preview']['image_media']['src'] ?? null;

            if (! $url || ! str_contains((string) $url, 'images.weddingku.com')) {
                continue;
            }

            $total++;

            if (count($album) < $limit) {
                $album[] = str_replace('\\/', '/', $url);
            }
        }

        return [
            'album' => $album,
            // Total asli dari endpoint, bukan jumlah yang kita ambil.
            'album_count' => $total > 0 ? ($data['page_info']['count']['record'] ?? $total) : null,
        ];
    }

    /**
     * Response JSON /directory/ajax/review.asp -> review vendor.
     *
     * Rating diambil dari rata-rata skor quality/service/professionalism/price
     * milik tiap review, karena angka rating di HTML halaman vendor selalu
     * tercetak 0.0 meskipun vendor punya ratusan review.
     *
     * @return array{reviews: array, review_count: ?int, rating: ?float}
     */
    public function parseReviews(string $json, int $limit = 5): array
    {
        $empty = ['reviews' => [], 'review_count' => null, 'rating' => null];

        $data = json_decode($json, true);

        if (! is_array($data['reviews'] ?? null)) {
            return $empty;
        }

        $reviews = [];

        // Jumlah review sebenarnya ada di page_info, bukan panjang array ini:
        // array hanya berisi satu halaman (maks 20) dari total yang bisa lebih.
        $total = (int) ($data['page_info']['count']['record'] ?? count($data['reviews']));

        // Rating TIDAK ada di HTML halaman vendor (selalu tercetak 0.0), tapi
        // tiap review membawa 4 skor 1-5: quality, service, professionalism,
        // price. Rata-ratanya dipakai sebagai rating vendor.
        $scores = [];

        foreach ($data['reviews'] as $review) {
            foreach (['quality', 'service', 'professionalism', 'price'] as $key) {
                $score = (float) ($review[$key] ?? 0);

                // Hanya skala 1-5 yang dipakai; angka di luar itu data rusak.
                if ($score >= 1 && $score <= 5) {
                    $scores[] = $score;
                }
            }

            if (count($reviews) >= $limit) {
                continue;
            }

            // Teks review masih berupa entitas HTML (&amp; dkk).
            $content = $this->decode(trim((string) ($review['content'] ?? '')));

            if ($content === '') {
                continue;
            }

            $reviews[] = [
                'title' => $this->decode(trim((string) ($review['title'] ?? ''))),
                'content' => $content,
                'date' => substr((string) ($review['review_date'] ?? ''), 0, 10),
            ];
        }

        return [
            'reviews' => $reviews,
            'review_count' => $total > 0 ? $total : null,
            'rating' => $scores === []
                ? null
                : round(array_sum($scores) / count($scores), 1),
        ];
    }

    /** Teks dari endpoint AJAX masih berupa entitas HTML. */
    private function decode(string $text): string
    {
        return html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }

    /**
     * Nilai microdata Weddingku disimpan di atribut content (meta tag),
     * bukan isi teks elemen, jadi harus dibaca dari atribut.
     */
    private function meta(DOMXPath $xpath, string $itemprop): ?string
    {
        $nodes = $xpath->query('//*[@itemprop="' . $itemprop . '"]');

        foreach ($nodes as $node) {
            $value = trim($node->getAttribute('content'));
            if ($value !== '') {
                return $value;
            }

            $text = trim(preg_replace('/\s+/', ' ', $node->textContent) ?? '');
            if ($text !== '') {
                return $text;
            }
        }

        return null;
    }

    /**
     * Rating & jumlah review. Markup yang dipakai situs:
     * <i class="fa fa-star-o"></i> x5 </span> <span>4.9</span> (<span>4 reviews </span>)
     */
    private function parseRating(string $html): array
    {
        if (preg_match('/fa-star-o.*?<\/span>\s*<span>\s*([\d.]+)\s*<\/span>\s*\(\s*<span>\s*(\d+)\s*reviews/is', $html, $m)) {
            return [
                'rating' => (float) $m[1],
                'review_count' => (int) $m[2],
            ];
        }

        return ['rating' => 0.0, 'review_count' => 0];
    }

    /**
     * Kategori dari breadcrumb: [0]=home [1]=wedding vendors [2]=kategori.
     * Microsite menuliskannya huruf kecil ("hotel wedding venue"), jadi
     * dinormalkan agar seragam dengan kategori milik user.
     */
    private function categoryFromBreadcrumb(DOMXPath $xpath): string
    {
        $nodes = $xpath->query('//*[@itemtype="http://schema.org/BreadcrumbList"]//*[@itemprop="name"]');

        $names = [];
        foreach ($nodes as $node) {
            $names[] = trim($node->textContent);
        }

        $category = $names[2] ?? $names[1] ?? 'Umum';

        return ucwords(strtolower($category));
    }

    /**
     * Nomor telepon vendor.
     *
     * Microdata telephone tidak selalu milik vendor: kalau vendor tidak
     * mencantumkan nomor, Weddingku mengisi nomor chat-with-us mereka sendiri
     * (628119528988) dan nomor itu bocor ke data vendor. Dibuang di sini.
     */
    private function vendorPhone(DOMXPath $xpath): ?string
    {
        $phone = $this->meta($xpath, 'telephone');

        if ($phone === null) {
            return null;
        }

        $digits = preg_replace('/\D/', '', $phone);

        return $digits === self::WEDDINGKU_PHONE ? null : $phone;
    }

    /**
     * Sosial media VENDOR. Hanya dari div.sosmed, dan URL asli diambil dari
     * atribut content (redirect.asp hanya pembungkus). Domain milik Weddingku
     * maupun tombol "Chat with us" dibuang.
     */
    private function social(DOMXPath $xpath, string $platform): ?string
    {
        $nodes = $xpath->query('//div[contains(@class,"sosmed")]//a[@content]');

        foreach ($nodes as $node) {
            $url = trim($node->getAttribute('content'));
            $lower = strtolower($url);

            if ($platform === 'instagram' && ! str_contains($lower, 'instagram.com')) {
                continue;
            }

            if ($platform === 'whatsapp' && ! str_contains($lower, 'wa.me')) {
                continue;
            }

            if ($this->isWeddingkuOwned($url)) {
                continue;
            }

            return $url;
        }

        return null;
    }

    private function isWeddingkuOwned(string $url): bool
    {
        $lower = strtolower($url);

        foreach (self::WEDDINGKU_HOSTS as $host) {
            if (str_contains($lower, $host)) {
                return true;
            }
        }

        // Nomor WA milik tim Weddingku (tombol "Chat with us").
        if (preg_match('#wa\.me/' . self::WEDDINGKU_PHONE . '#', $lower)) {
            return true;
        }

        // Link wrapper internal Weddingku, bukan tujuan sebenarnya.
        return str_contains($lower, 'weddingku.com/directory/redirects');
    }

    private function firstPhoto(DOMXPath $xpath): ?string
    {
        $nodes = $xpath->query('//*[@itemprop="image"]/@src | //*[@itemprop="logo"]/@src');

        foreach ($nodes as $node) {
            $url = trim($node->nodeValue ?? '');
            if (str_contains($url, 'images.weddingku.com')) {
                return $url;
            }
        }

        return null;
    }

    private function firstText(DOMXPath $xpath, string $query): ?string
    {
        $nodes = $xpath->query($query);

        foreach ($nodes as $node) {
            $value = trim(preg_replace('/\s+/', ' ', $node->textContent) ?? '');
            if ($value !== '') {
                return $value;
            }
        }

        return null;
    }

    private function xpath(string $html): DOMXPath
    {
        $doc = new DOMDocument();

        // libxml bukan parser HTML5: "&nbsp" tanpa titik koma (banyak di
        // halaman ini) membuat ia salah parse dan menelan sebagian konten.
        // Ampersand telanjang diamankan dulu supaya XPath konsisten.
        $safe = preg_replace('/&(?!#\d+;|#x[0-9a-fA-F]+;|[a-zA-Z][a-zA-Z0-9]*;)/', '&amp;', $html) ?? $html;

        libxml_use_internal_errors(true);
        $doc->loadHTML('<?xml encoding="utf-8" ?>' . $safe);
        libxml_clear_errors();

        return new DOMXPath($doc);
    }

    private function slugFromUrl(string $url): string
    {
        $path = parse_url($url, PHP_URL_PATH) ?: '';

        return basename(rtrim($path, '/'));
    }
}
