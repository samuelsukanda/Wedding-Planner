<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('upgrade_requests', function (Blueprint $table) {
            // Fitur yang dipesan: "pro" (upgrade paket) atau "photobooth"
            // (add-on photobooth). Kolom ini yang membedakan cara aktivasi.
            $table->string('feature', 30)->default('pro')->after('plan')->index();
        });
    }

    public function down(): void
    {
        Schema::table('upgrade_requests', function (Blueprint $table) {
            $table->dropColumn('feature');
        });
    }
};
