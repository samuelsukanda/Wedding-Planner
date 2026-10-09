<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Business Rule 4: budget Lamaran terpisah dari budget resepsi,
        // tapi masuk ke ringkasan total biaya persiapan. Karena itu tabel ini
        // berdiri sendiri, bukan baris di tabel `budgets`.
        Schema::create('proposal_budgets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('wedding_id')->constrained()->cascadeOnDelete();
            $table->foreignId('vendor_id')->nullable()->constrained()->nullOnDelete();

            $table->string('category');
            $table->string('item_name');
            $table->decimal('planned_budget', 15, 2)->default(0);
            $table->decimal('actual_cost', 15, 2)->default(0);
            $table->text('notes')->nullable();

            $table->timestamps();

            $table->index(['wedding_id', 'category']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('proposal_budgets');
    }
};