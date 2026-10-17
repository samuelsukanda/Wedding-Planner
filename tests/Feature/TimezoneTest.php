<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Aplikasi ini dipakai pasanganpengantin di Indonesia, jadi semua waktu
 * harus dalam WIB.
 *
 * Dulu config('app.timezone') = 'UTC' sementara zona sesi MySQL adalah
 * SYSTEM (WIB, +07:00). Akibatnya Eloquent menulis jam UTC, MySQL
 * memperlakukannya sebagai WIB, dan setiap timestamp tersimpan 7 jam lebih
 * awal dari waktu sebenarnya -- termasuk yang tampil di layar.
 *
 * Tes ini mengunci konfigurasi itu supaya tidak kembali diam-diam.
 */
class TimezoneTest extends TestCase
{
    use RefreshDatabase;

    public function test_app_timezone_is_wib(): void
    {
        $this->assertSame('Asia/Jakarta', config('app.timezone'));
    }

    public function test_php_default_timezone_matches_app_timezone(): void
    {
        $this->assertSame(config('app.timezone'), date_default_timezone_get());
    }

    public function test_wib_is_seven_hours_ahead_of_utc(): void
    {
        $wibOffset = now()->setTimezone('Asia/Jakarta')->getOffset();
        $utcOffset = now()->setTimezone('UTC')->getOffset();

        $this->assertSame(7 * 3600, $wibOffset - $utcOffset, 'WIB harus 7 jam di depan UTC.');
    }

    /**
     * Nilai yang disimpan harusstable walau dibaca ulang lewat beberapa
     * jalur: ini yang rusak ketika zona aplikasi dan zona database berbeda.
     */
    public function test_saved_timestamp_round_trips_without_drift(): void
    {
        $checklist = $this->makeWedding()->checklists()->create([
            'title' => 'Cek Zona Waktu',
            'category' => 'General',
            'status' => 'Todo',
        ]);

        $written = $checklist->created_at->copy();

        $reread = \App\Models\Checklist::find($checklist->id)->created_at;

        $this->assertTrue(
            $written->equalTo($reread),
            "Timestamp berubah setelah dibaca ulang: {$written} -> {$reread}"
        );
    }

    private function makeWedding(): \App\Models\Wedding
    {
        return \App\Models\Wedding::create([
            'groom_name' => 'Groom',
            'bride_name' => 'Bride',
            'wedding_date' => '2027-10-07',
            'total_budget' => 100000000,
        ]);
    }
}