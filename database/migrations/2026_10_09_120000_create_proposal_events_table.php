<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('proposal_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('wedding_id')->constrained()->cascadeOnDelete();

            // PRD Field: Tanggal, Waktu, Lokasi, Tema
            $table->date('event_date')->nullable();
            $table->time('event_time')->nullable();
            $table->string('location')->nullable();
            $table->string('theme')->nullable();

            // PRD: "Catatan kebutuhan acara"
            $table->text('notes')->nullable();

            $table->timestamps();

            // Satu acara lamaran per wedding (keputusan desain Module 13).
            $table->unique('wedding_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('proposal_events');
    }
};