<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Katalog vendor publik hasil import dari Weddingku.
 *
 * Sengaja TANPA wedding_id dan tanpa trait ScopedToWedding: katalog ini
 * milik bersama seluruh pasangan, bukan data milik satu wedding. Data yang
 * "milik user" tetap di tabel vendors (Vendor Management).
 *
 * Album foto dan harga paket TIDAK disimpan: keduanya dimuat lewat
 * /ajax/ yang dilarang robots.txt Weddingku. Pada HTML server-side, blok
 * harga venue hanya muncul di dalam komentar HTML dan isinya IDENTIK untuk
 * semua hotel (contoh placeholder), sehingga membacanya akan memberi harga
 * yang salah ke setiap hotel. Lebih baik kosong daripada salah.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vendor_catalog', function (Blueprint $table) {
            $table->id();
            $table->string('source')->default('weddingku');
            $table->string('source_url')->unique();
            $table->string('slug');
            $table->string('name');
            $table->string('category');
            $table->string('city')->nullable();
            $table->string('province')->nullable();
            $table->text('address')->nullable();
            $table->string('phone')->nullable();
            $table->string('whatsapp')->nullable();
            $table->string('instagram')->nullable();
            $table->decimal('rating', 3, 1)->default(0);
            $table->unsignedInteger('review_count')->default(0);
            $table->text('about')->nullable();
            $table->string('photo')->nullable();
            $table->timestamp('scraped_at')->nullable();
            $table->timestamps();

            $table->index(['category', 'city']);
            $table->index('rating');
        });

        // Sosmed ikut dibawa ke Vendor Management saat vendor disimpan dari
        // katalog, jadi tabel vendors butuh kolomnya sendiri.
        Schema::table('vendors', function (Blueprint $table) {
            $table->string('instagram')->nullable()->after('google_maps_url');
            $table->string('whatsapp')->nullable()->after('instagram');
        });
    }

    public function down(): void
    {
        Schema::table('vendors', function (Blueprint $table) {
            $table->dropColumn(['instagram', 'whatsapp']);
        });

        Schema::dropIfExists('vendor_catalog');
    }
};
