<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Album foto & review vendor, dari /directory/ajax/collections.asp dan
 * /directory/ajax/review.asp (path yang TIDAK dilarang robots.txt).
 *
 * Album disimpan sebagai URL saja, file foto tidak diunduh.
 * Hanya 6 foto pertama per vendor yang disimpan supaya tabel tidak membengkak,
 * sementara album_count menyimpan jumlah foto sebenarnya di sumber.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vendor_catalog', function (Blueprint $table) {
            $table->json('album')->nullable()->after('venue_rooms');
            $table->unsignedInteger('album_count')->nullable()->after('album');
            $table->json('reviews')->nullable()->after('album_count');
        });
    }

    public function down(): void
    {
        Schema::table('vendor_catalog', function (Blueprint $table) {
            $table->dropColumn(['album', 'album_count', 'reviews']);
        });
    }
};