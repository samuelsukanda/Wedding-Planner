<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('proposal_checklists', function (Blueprint $table) {
            $table->id();
            // Satu acara lamaran per wedding, jadi relasi ke proposal_events
            // selalu lewat wedding_id. Kolom proposal_event_id di sini hanya
            // menambah satu sumber kebenaran yang bisa tidaksinkron.
            $table->foreignId('wedding_id')->constrained()->cascadeOnDelete();

            // PRD Field: Item, Kategori, Deadline, Status
            $table->string('title');
            $table->string('category')->nullable();
            $table->date('deadline')->nullable();
            $table->string('status')->default('Todo');
            $table->string('priority')->nullable();
            $table->text('notes')->nullable();

            $table->timestamps();

            $table->index(['wedding_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('proposal_checklists');
    }
};