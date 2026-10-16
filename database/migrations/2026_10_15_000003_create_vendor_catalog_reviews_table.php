<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Review & rating pengguna untuk vendor katalog (Cari Vendor).
 *
 * Review Weddingku yang diimpor tetap tersimpan di kolom JSON reviews di
 * vendor_catalog; tabel ini hanya menyimpan penilaian pasangan sendiri.
 * Satu pasangan (wedding) cukup satu review per vendor, jadi kalau diisi
 * ulang barisnya diupdate, bukan diduplikat.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vendor_catalog_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vendor_catalog_id')->constrained('vendor_catalog')->cascadeOnDelete();
            $table->foreignId('wedding_id')->constrained('weddings')->cascadeOnDelete();
            $table->unsignedTinyInteger('rating'); // 1-5
            $table->text('review')->nullable();
            $table->timestamps();

            $table->unique(['vendor_catalog_id', 'wedding_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vendor_catalog_reviews');
    }
};
