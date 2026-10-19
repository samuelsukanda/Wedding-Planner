<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tautan undangan pasangan.
     *
     * App ini tidak punya fitur reset password, jadi akun pasangan tidak boleh
     * dibuat dari sisi pemilik Wedding Kit: kalau tidak, pasangan akan
     * terkunci karena tidak tahu passwordnya. Dengan undangan, pasangan yang
     * membuka tautan ini yang membuat passwordnya sendiri.
     */
    public function up(): void
    {
        Schema::create('wedding_invites', function (Blueprint $table) {
            $table->id();
            $table->foreignId('wedding_id')->constrained()->cascadeOnDelete();

            // 48 karakter acak. Panjangnya immaterial untuk keamanan - yang
            // penting tidak bisa ditebak dan dipakai ulang.
            $table->string('token', 48)->unique();

            $table->timestamp('expires_at');
            // Sekali pakai: begitu diisi, tautan berhenti berlaku.
            $table->timestamp('accepted_at')->nullable();
            $table->foreignId('accepted_user_id')->nullable()->constrained('users')->nullOnDelete();

            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wedding_invites');
    }
};