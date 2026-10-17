<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Selaraskan data timestamp yang sudah terlanjur tersimpan 7 jam lebih awal.
 *
 * Latar belakang
 * --------------
 * Sebelumnya config('app.timezone') = 'UTC' sementara zona waktu sesi MySQL
 * adalah SYSTEM (WIB, +07:00). Saat Eloquent menulis jam UTC "07:42", MySQL
 * memperlakukannya sebagai WIB 07:42 lalu menyimpannya sebagai UTC 00:42.
 * Akibatnya setiap timestamp masuk 7 jam lebih awal, dan tampilannya pun 7
 * jam mundur dari waktu lokal sebenarnya.
 *
 *config('app.timezone') sudah diubah ke Asia/Jakarta oleh migrasi terpisah,
 * jadi sejak sekarang PHP dan MySQL memakai zona yang sama. Yang tersisa
 * adalah menggeser nilai lama agar sesuai dengan zona yang baru.
 *
 * Penting: kolomnya bertipe TIMESTAMP, jadi MySQL mengonversinya mengikuti
 * zona sesi. Sesi dipaksa ke +00:00 lebih dulu supaya aritmetika
 * DATE_ADD bekerja pada nilai UTC yang benar-benar tersimpan.
 */
return new class extends Migration
{
    /** Tabel milik framework Laravel, bukan data aplikasi. */
    private const SKIP_TABLES = [
        'migrations', 'failed_jobs', 'jobs', 'job_batches',
        'sessions', 'cache', 'cache_locks',
        'telescope_entries', 'telescope_entries_tags',
    ];

    public function up(): void
    {
        // Perpindahan ini hanya relevan di MySQL: hanya di sana kolomnya
        // bertipe TIMESTAMP yang dikonversi mengikuti zona sesi, dan hanya
        // di sana skew 7 jam itu terjadi. Test memakai SQLite in-memory,
        // jadi harus dilewati agar tidak menjatuhkan seluruh test.
        if (DB::connection()->getDriverName() !== 'mysql') {
            return;
        }

        // Selisih antara zona yang baru dipakai aplikasi (Asia/Jakarta, +07:00)
        // dan zona yang sebelumnya dipakai saat data ini ditulis (UTC, +00:00).
        $hours = (int) round(
            (new DateTimeZone(config('app.timezone')))->getOffset(new DateTimeImmutable('now'))
            / 3600
        );

        if ($hours === 0) {
            return;
        }

        $previous = DB::selectOne('SELECT @@session.time_zone AS tz')->tz;
        DB::statement("SET time_zone = '+00:00'");

        try {
            foreach ($this->timestampColumns() as $table => $columns) {
                if (! Schema::hasTable($table)) {
                    continue;
                }

                foreach ($columns as $column) {
                    DB::statement(
                        "UPDATE `{$table}` SET `{$column}` = DATE_ADD(`{$column}`, INTERVAL {$hours} HOUR)
                         WHERE `{$column}` IS NOT NULL"
                    );
                }
            }
        } finally {
            DB::statement("SET time_zone = '{$previous}'");
        }
    }

    public function down(): void
    {
        // Sengaja dibiarkan kosong. Mengembalikan nilainya berarti mengembalikan
        // bug 7 jam, jadi perbaikan ini tidak ought to dibatalkan.
    }

    /**
     * @return array<string, list<string>> kolom TIMESTAMP per tabel aplikasi
     */
    private function timestampColumns(): array
    {
        $skip = implode(',', array_map(fn ($t) => "'{$t}'", self::SKIP_TABLES));

        $columns = DB::select(
            "SELECT TABLE_NAME, COLUMN_NAME
             FROM information_schema.COLUMNS
             WHERE TABLE_SCHEMA = DATABASE()
               AND DATA_TYPE IN ('timestamp', 'datetime')
               AND TABLE_NAME NOT IN ({$skip})
             ORDER BY TABLE_NAME, COLUMN_NAME"
        );

        $grouped = [];

        foreach ($columns as $column) {
            $grouped[$column->TABLE_NAME][] = $column->COLUMN_NAME;
        }

        return $grouped;
    }
};