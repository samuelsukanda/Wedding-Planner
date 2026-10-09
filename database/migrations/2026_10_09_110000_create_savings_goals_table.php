<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('savings_goals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('wedding_id')->constrained()->cascadeOnDelete();

            // PRD Field: Nama Target, Target Nominal, Saldo Awal, Saldo Saat Ini
            $table->string('name');
            $table->decimal('target_amount', 15, 2)->default(0);
            $table->decimal('initial_balance', 15, 2)->default(0);
            // Satu-satunya penulisan saldo selain initial_balance saat create.
            // Dijaga oleh SavingsGoal::recalculate().
            $table->decimal('current_balance', 15, 2)->default(0);

            // PRD Field: Nominal Setoran + Frekuensi
            $table->decimal('periodic_amount', 15, 2)->default(0);
            $table->string('frequency')->nullable();

            // PRD Field: Target Tanggal, Catatan, Status
            $table->date('target_date')->nullable();
            $table->text('notes')->nullable();
            $table->string('status')->default('Aktif');

            $table->timestamps();

            $table->index(['wedding_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('savings_goals');
    }
};