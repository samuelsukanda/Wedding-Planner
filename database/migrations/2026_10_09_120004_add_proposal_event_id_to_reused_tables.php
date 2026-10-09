<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Reuse fitur Module 13 (Lamaran) ke tabel yang sudah ada.
 *
 * Nullable supaya baris lama milik resepsi tidak ikut terikat acara lamaran.
 */
return new class extends Migration
{
    public function tables(): array
    {
        return ['vendors', 'rundown_events', 'moodboards'];
    }

    public function up(): void
    {
        foreach ($this->tables() as $table) {
            Schema::table($table, function (Blueprint $blueprint) {
                $blueprint->foreignId('proposal_event_id')
                    ->nullable()
                    ->after('wedding_id')
                    ->constrained('proposal_events')
                    ->cascadeOnDelete();
            });
        }
    }

    public function down(): void
    {
        foreach ($this->tables() as $table) {
            Schema::table($table, function (Blueprint $blueprint) {
                $blueprint->dropConstrainedForeignId('proposal_event_id');
            });
        }
    }
};