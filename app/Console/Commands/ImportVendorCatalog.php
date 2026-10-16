<?php

namespace App\Console\Commands;

use App\Models\VendorCatalog;
use App\Support\WeddingkuParser;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

/**
 * Import katalog vendor publik dari Weddingku.
 *
 * Sumber: https://www.weddingku.com/sitemap/sitemap.xml (disebut di
 * robots.txt dan diizinkan untuk_USER_AGENT *). Hanya halaman /wedding-vendors/
 * yang diambil.
 *
 * Halaman Weddingku dirender di server, jadi Http::get() cukup tanpa
 * browser. Album foto TIDAK diimpor: dimuat lewat /ajax/ yang dilarang
 * robots.txt.
 */
class ImportVendorCatalog extends Command
{
    protected $signature = 'vendors:import
                            {--limit=0 : Batasi jumlah halaman (0 = semua)}
                            {--concurrency=6 : Permintaan sekaligus}';

    protected $description = 'Import katalog vendor publik dari Weddingku';

    private const SITEMAP = 'https://www.weddingku.com/sitemap/sitemap.xml';

    private const UA = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/131.0 Safari/537.36';

    /**
     * Endpoint pricelist venue.
     *
     * PENTING: path-nya /directory/ajax/, BUKAN /ajax/ yang dilarang
     * robots.txt Weddingku, sehingga pemanggilannya tidak melanggar robots.
     * Data di sini sama dengan yang dirender halaman /venue untuk pengunjung.
     */
    private const VENUE_ENDPOINT = 'https://www.weddingku.com/directory/ajax/venue_new.asp';

    /** Album foto vendor (URL saja, file tidak diunduh). */
    private const ALBUM_ENDPOINT = 'https://www.weddingku.com/directory/ajax/collections.asp';

    /** Review vendor. Field skor selalu 0, jadi rating tidak bisa dihitung dari sini. */
    private const REVIEW_ENDPOINT = 'https://www.weddingku.com/directory/ajax/review.asp';

    /**
     * Berapa foto album yang disimpan per vendor. Dibatasi supaya tabel tidak
     * membengkak: 484 vendor x ~70 foto = ~29.000 URL kalau disimpan semua.
     * album_count tetap menyimpan jumlah sebenarnya di sumber.
     *
     * 10 dipilih karena satu halaman endpoint = 10 foto, jadi batas ini tidak
     * menambah request sama sekali (seluruh vendor cukup 1 halaman).
     */
    private const ALBUM_PER_VENDOR = 10;

    /** Pengaman pagination album; album terbesar yang ditemukan 3.280 foto. */
    private const ALBUM_MAX_PAGES = 4;

    public function handle(WeddingkuParser $parser): int
    {
        $urls = $this->vendorUrlsFromSitemap();

        if ($urls === []) {
            $this->error('Sitemap tidak menghasilkan URL vendor.');

            return self::FAILURE;
        }

        $limit = (int) $this->option('limit');
        if ($limit > 0) {
            $urls = array_slice($urls, 0, $limit);
        }

        $this->info(count($urls) . ' URL vendor ditemukan di sitemap.');

        $imported = $this->importAll($urls, $parser);

        $this->newLine();
        $this->info("Selesai: {$imported} vendor tersimpan, total katalog " . VendorCatalog::count() . '.');

        return self::SUCCESS;
    }

    /**
     * Ambil daftar URL detail vendor dari sitemap.
     * Hanya URL dengan 3 segmen setelah /wedding-vendors/ ({kategori}/{slug}).
     *
     * @return list<string>
     */
    private function vendorUrlsFromSitemap(): array
    {
        try {
            $xml = Http::withHeaders(['User-Agent' => self::UA])
                ->timeout(30)
                ->get(self::SITEMAP)
                ->throw()
                ->body();
        } catch (\Throwable $e) {
            $this->error('Gagal mengambil sitemap: ' . $e->getMessage());

            return [];
        }

        preg_match_all('#<loc>([^<]+)</loc>#', $xml, $matches);

        $urls = array_filter($matches[1], fn (string $url) => (bool) preg_match('#/wedding-vendors/[^/]+/[^/]+$#', $url));

        return array_values(array_unique($urls));
    }

    /**
     * Fetch per batch lalu simpan. Batch dipakai supaya tidak membanjiri
     * server dan agar command tetap responsif.
     */
    private function importAll(array $urls, WeddingkuParser $parser): int
    {
        $concurrency = max(1, (int) $this->option('concurrency'));
        $batchSize = $concurrency * 5;
        $saved = 0;
        $failed = 0;
        $priced = 0;
        $albums = 0;
        $reviewed = 0;
        $pending = [];
        $pendingMedia = [];
        $bar = $this->output->createProgressBar(count($urls));
        $bar->start();

        foreach (array_chunk($urls, $batchSize) as $batch) {
            // Satu pool per batch: request di dalam batch jalan paralel,
            // antar batch dijeda agar tidak membanjiri server.
            $responses = Http::pool(function ($pool) use ($batch) {
                foreach ($batch as $url) {
                    $pool->withHeaders(['User-Agent' => self::UA])
                        ->timeout(30)
                        ->retry(2, 250)
                        ->get($url);
                }
            });

            foreach ($batch as $index => $url) {
                $response = $responses[$index] ?? null;

                if ($response === null || ! $response->successful()) {
                    $failed++;
                    $bar->advance();

                    continue;
                }

                $data = $parser->parse($response->body(), $url);

                if ($data === null) {
                    $failed++;
                    $bar->advance();

                    continue;
                }

                $category = $data['category'];

                // Upsert di source_url supaya command aman dijalankan ulang.
                VendorCatalog::updateOrCreate(
                    ['source_url' => $url],
                    [...$data, 'scraped_at' => now()]
                );

                if ($data['vendor_id']) {
                    // Pricelist hanya ada di kategori venue; album & review
                    // ada di semua kategori.
                    if (preg_match('/venue|hotel|restaurant/i', $category)) {
                        $pending[$url] = $data['vendor_id'];
                    }

                    $pendingMedia[$url] = $data['vendor_id'];
                }

                $saved++;
                $bar->advance();
            }

            if ($pending !== []) {
                $priced += $this->importPricelists($pending, $parser);
                $pending = [];
            }

            if ($pendingMedia !== []) {
                $withMedia = $this->importMedia($pendingMedia, $parser);
                $albums += $withMedia['albums'];
                $reviewed += $withMedia['reviews'];
                $pendingMedia = [];
            }
        }

        $bar->finish();
        $this->newLine();

        if ($failed > 0) {
            $this->warn("{$failed} halaman gagal dibaca / tidak punya blok vendor.");
        }

        if ($priced > 0) {
            $this->info("{$priced} vendor venue dapat pricelist (ruangan + harga).");
        }

        if ($albums > 0) {
            $this->info("{$albums} vendor dapat album foto (URL, file tidak diunduh).");
        }

        if ($reviewed > 0) {
            $this->info("{$reviewed} vendor punya review yang bisa diambil.");
        }

        return $saved;
    }

    /**
     * Ambil album foto + review tiap vendor.
     *
     * Dua endpoint, keduanya di /directory/ajax/ (tidak dilarang robots.txt):
     * - collections.asp : album, dipaginasi (sampai 35 foto per vendor)
     * - review.asp      : review; field skornya selalu 0 di Weddingku jadi
     *                     tidak dipakai untuk menghitung rating.
     *
     * @param  array<string, int>  $vendorIds  source_url => vendor_id Weddingku
     * @return array{albums: int, reviews: int}
     */
    private function importMedia(array $vendorIds, WeddingkuParser $parser): array
    {
        $albums = 0;
        $reviews = 0;

        // Halaman album diambil bertahap: page 1 untuk semua vendor dulu,
        // lalu page berikutnya hanya untuk yang albumnya belum penuh.
        $pages = [1 => $vendorIds];

        for ($page = 1; $page <= self::ALBUM_MAX_PAGES; $page++) {
            $batch = $pages[$page] ?? [];
            unset($pages[$page]);

            if ($batch === []) {
                break;
            }

            $responses = Http::pool(function ($pool) use ($batch, $page) {
                foreach ($batch as $url => $vendorId) {
                    $pool->asForm()
                        ->withHeaders(['User-Agent' => self::UA, 'X-Requested-With' => 'XMLHttpRequest'])
                        ->timeout(30)
                        ->retry(2, 250)
                        ->post(self::ALBUM_ENDPOINT, ['page' => $page, 'i' => $vendorId, 'f' => 0]);
                }
            });

            $next = [];

            // Index harus POSIX: $responses dikembalikan sebagai daftar numerik,
            // sedangkan $batch di-key source_url. Mengakses $responses[$url]
            // selalu null sehingga album terlewat diam-diam.
            foreach (array_keys($batch) as $index => $url) {
                $response = $responses[$index] ?? null;

                if ($response === null || ! $response->successful()) {
                    continue;
                }

                $data = $parser->parseAlbum($response->body(), self::ALBUM_PER_VENDOR);

                if ($data['album'] === []) {
                    continue;
                }

                if ($page === 1) {
                    $albums++;
                }

                VendorCatalog::where('source_url', $url)->update([
                    // Foto dari tiap halaman digabung; album_count di-set sekali
                    // dari page 1 karena di situ page_info sudah lengkap.
                    'album' => $this->mergeAlbum($url, $data['album']),
                    ...($page === 1 ? ['album_count' => $data['album_count']] : []),
                ]);

                // Lanjut ke halaman berikut hanya kalau masih kurang dari
                // batas per vendor dan endpointsaidanya halaman berikutnya.
                if (count($data['album']) < self::ALBUM_PER_VENDOR) {
                    continue;
                }

                if ($page === 1 && ($this->albumTotal($url) ?? 0) > self::ALBUM_PER_VENDOR) {
                    $next[$url] = $batch[$url];
                }
            }

            if ($next === []) {
                break;
            }

            $pages[$page + 1] = $next;
        }

        // Review diambil terpisah: endpoint-nya sekali per vendor, dan
        // hampir semua vendor memang tidak punya review.
        $responses = Http::pool(function ($pool) use ($vendorIds) {
            foreach ($vendorIds as $url => $vendorId) {
                $pool->asForm()
                    ->withHeaders(['User-Agent' => self::UA, 'X-Requested-With' => 'XMLHttpRequest'])
                    ->timeout(30)
                    ->retry(2, 250)
                    ->post(self::REVIEW_ENDPOINT, ['page' => 1, 'row' => 20, 'i' => $vendorId, 'sort' => '', 'order' => '']);
            }
        });

        foreach (array_keys($vendorIds) as $index => $url) {
            $response = $responses[$index] ?? null;

            if ($response === null || ! $response->successful()) {
                continue;
            }

            $data = $parser->parseReviews($response->body());

            if ($data['reviews'] === []) {
                continue;
            }

            VendorCatalog::where('source_url', $url)->update([
                'reviews' => $data['reviews'],
                'review_count' => $data['review_count'],
                // Rating dari endpoint, bukan angka 0.0 yang cetakan di HTML.
                ...($data['rating'] === null ? [] : ['rating' => $data['rating']]),
            ]);

            $reviews++;
        }

        return ['albums' => $albums, 'reviews' => $reviews];
    }

    /**
     * @return list<string> Foto album sampaioir yang sudah tersimpan
     */
    private function mergeAlbum(string $sourceUrl, array $newPhotos): array
    {
        $existing = VendorCatalog::where('source_url', $sourceUrl)->value('album');
        $existing = is_string($existing) ? (json_decode($existing, true) ?: []) : ($existing ?: []);

        $merged = array_slice(array_values(array_unique(array_merge($existing, $newPhotos))), 0, self::ALBUM_PER_VENDOR);

        return $merged;
    }

    private function albumTotal(string $sourceUrl): ?int
    {
        $value = VendorCatalog::where('source_url', $sourceUrl)->value('album_count');

        return $value === null ? null : (int) $value;
    }

    /**
     * Ambil pricelist tiap vendor venue lewat /directory/ajax/venue_new.asp.
     *
     * Endpoint ini BUKAN /ajax/ yang dilarang robots.txt, melainkan
     * /directory/ajax/ yang tidak dilarang, jadi aman dipanggil langsung
     * tanpa browser. Mengembalikan venue_rooms/starting_price/price_label.
     *
     * @param  array<string, int>  $vendorIds  source_url => vendor_id Weddingku
     * @return int Banyak vendor yang pricelist-nya berhasil disimpan
     */
    private function importPricelists(array $vendorIds, WeddingkuParser $parser): int
    {
        $responses = Http::pool(function ($pool) use ($vendorIds) {
            foreach ($vendorIds as $url => $vendorId) {
                $pool->withHeaders(['User-Agent' => self::UA, 'X-Requested-With' => 'XMLHttpRequest'])
                    ->timeout(30)
                    ->retry(2, 250)
                    ->asForm()
                    ->post(self::VENUE_ENDPOINT, [
                        'page' => 1,
                        'i' => $vendorId,
                        'row' => 20,
                        'type' => '',
                        'price' => '',
                        'capacity' => '',
                    ]);
            }
        });

        $updated = 0;

        foreach (array_keys($vendorIds) as $index => $url) {
            $response = $responses[$index] ?? null;

            if ($response === null || ! $response->successful()) {
                continue;
            }

            $prices = $parser->parseVenues($response->body());

            if ($prices['venue_rooms'] === []) {
                continue;
            }

            VendorCatalog::where('source_url', $url)->update($prices);
            $updated++;
        }

        return $updated;
    }
}
