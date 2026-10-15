<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Pembayaran manual: user transfer, lalu mengunggah bukti. Baris ini
        // adalah antrean untuk superadmin sekaligus jejak audit siapa yang sudah
        // dikonfirmasi. Sengaja TIDAK memakai ScopedToWedding — daftar request
        // harus melihat semua pasangan, bukan cuma yang sedang login.
        Schema::create('upgrade_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('wedding_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('plan')->default('pro');
            $table->unsignedBigInteger('amount');
            $table->string('proof_path');
            $table->string('status')->default('pending');
            $table->text('admin_note')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('upgrade_requests');
    }
};