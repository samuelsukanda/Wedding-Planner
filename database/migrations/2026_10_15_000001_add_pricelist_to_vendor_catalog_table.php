<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Pricelist venue (daftar ruangan + harga) dari /directory/ajax/venue_new.asp.
 *
 * Kolom ini dikembalikan nullable karena hanya 62 dari 484 vendor katalog
 * yang kategorinya venue/hotel/restaurant; sisanya tidak punya data ini.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vendor_catalog', function (Blueprint $table) {
            // ID internal Weddingku; dibutuhkan untuk memanggil endpoint
            // /directory/ajax/venue_new.asp saat import ulang.
            $table->unsignedInteger('vendor_id')->nullable()->after('source_url');
            $table->decimal('starting_price', 15, 2)->nullable()->after('review_count');
            $table->string('price_label')->nullable()->after('starting_price');
            $table->json('venue_rooms')->nullable()->after('price_label');
        });
    }

    public function down(): void
    {
        Schema::table('vendor_catalog', function (Blueprint $table) {
            $table->dropColumn(['vendor_id', 'starting_price', 'price_label', 'venue_rooms']);
        });
    }
};