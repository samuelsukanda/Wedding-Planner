<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Reset Semua Data mengosongkan profil pernikahan, jadi tiga kolom ini
     * harus boleh null. Sebelumnya NOT NULL tanpa default, sehingga tidak bisa
     * dikosongkan tanpa memaksa nilai palsu seperti tanggal 1900-01-01.
     *
     * Pembaca di app sudah aman terhadap null: form Profil memakai
     * $wedding->wedding_date ? ... : '', daftar admin pakai @if, dan
     * countdown dihitung dengan max(0, ...).
     */
    public function up(): void
    {
        Schema::table('weddings', function (Blueprint $table) {
            $table->string('bride_name')->nullable()->change();
            $table->string('groom_name')->nullable()->change();
            $table->date('wedding_date')->nullable()->change();
        });
    }

    public function down(): void
    {
        // Kolom yang kosong tidak bisa dikembalikan jadi NOT NULL tanpa data
        // pengganti, jadi down sengaja mengisi nilai cadangan bila ada.
        \Illuminate\Support\Facades\DB::table('weddings')
            ->whereNull('bride_name')->update(['bride_name' => '-']);
        \Illuminate\Support\Facades\DB::table('weddings')
            ->whereNull('groom_name')->update(['groom_name' => '-']);

        Schema::table('weddings', function (Blueprint $table) {
            $table->string('bride_name')->nullable(false)->change();
            $table->string('groom_name')->nullable(false)->change();
            $table->date('wedding_date')->nullable(false)->change();
        });
    }
};