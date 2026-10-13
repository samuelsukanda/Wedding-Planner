<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Template pesan undangan WA yang bisa diubah per wedding.
     * NULL = pakai template bawaan dari GuestController.
     */
    public function up(): void
    {
        Schema::table('weddings', function (Blueprint $table) {
            $table->text('wa_template')->nullable()->after('notes');
        });
    }

    public function down(): void
    {
        Schema::table('weddings', function (Blueprint $table) {
            $table->dropColumn('wa_template');
        });
    }
};
