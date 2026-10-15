<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Tier paket per Wedding Kit, bukan per user: satu Curator Kit dipakai
        // dua akun (suami + istri) sekaligus, jadi cukup satu yang upgrade.
        // Paket Pro dibeli sekali seumur akun, jadi tidak ada kolom masa berlaku.
        Schema::table('weddings', function (Blueprint $table) {
            $table->string('plan')->default('free')->after('wa_template');
        });
    }

    public function down(): void
    {
        Schema::table('weddings', function (Blueprint $table) {
            $table->dropColumn('plan');
        });
    }
};