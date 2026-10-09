<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('proposal_guests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('wedding_id')->constrained()->cascadeOnDelete();

            // PRD Field: Nama, Hubungan/Keluarga, Jumlah, Status Kehadiran
            $table->string('name');
            $table->string('title')->nullable();
            $table->string('relation')->nullable();
            $table->string('category')->nullable();
            $table->string('phone')->nullable();
            $table->unsignedInteger('guest_count')->default(1);
            $table->string('attendance_status')->default('Pending');
            $table->text('notes')->nullable();

            $table->timestamps();

            $table->index(['wedding_id', 'attendance_status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('proposal_guests');
    }
};