<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('savings_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('wedding_id')->constrained()->cascadeOnDelete();
            $table->foreignId('savings_goal_id')->constrained('savings_goals')->cascadeOnDelete();

            // type: 'setoran' | 'penarikan'
            $table->string('type', 20);
            $table->decimal('amount', 15, 2);

            // PRD Field: Tanggal Setoran, Catatan
            $table->date('transaction_date');
            $table->text('notes')->nullable();

            $table->timestamps();

            $table->index(['savings_goal_id', 'transaction_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('savings_transactions');
    }
};