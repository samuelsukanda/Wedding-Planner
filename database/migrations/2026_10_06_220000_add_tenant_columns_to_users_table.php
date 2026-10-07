<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('wedding_id')->nullable()->after('id')->constrained()->nullOnDelete();
            $table->boolean('is_superadmin')->default(false)->after('wedding_id');
        });

        // Backfill: there is exactly one wedding and one user today, so the existing
        // account keeps all its data and becomes the superadmin.
        $weddingId = DB::table('weddings')->orderBy('id')->value('id');

        if ($weddingId !== null) {
            DB::table('users')
                ->whereNull('wedding_id')
                ->update(['wedding_id' => $weddingId, 'is_superadmin' => true]);
        }
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('wedding_id');
            $table->dropColumn('is_superadmin');
        });
    }
};