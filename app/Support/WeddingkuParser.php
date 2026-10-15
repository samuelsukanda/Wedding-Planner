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
