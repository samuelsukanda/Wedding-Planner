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

                // Upsert di source_url supaya command aman dijalankan ulang.
                VendorCatalog::updateOrCreate(
                    ['source_url' => $url],
                    [...$data, 'scraped_at' => now()]
                );

                $saved++;
                $bar->advance();
            }
        }

        $bar->finish();
        $this->newLine();

        if ($failed > 0) {
            $this->warn("{$failed} halaman gagal dibaca / tidak punya blok vendor.");
        }

        return $saved;
    }
}
