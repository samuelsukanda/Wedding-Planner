<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Template master: dikelola superadmin, bisa diubah tanpa deploy.
        Schema::create('document_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('description')->nullable();
            // Daftar dokumen sebagai JSON: [{name, status}, ...]. Cukup satu
            // baris per template, jadi superadmin bisa menambah atau menghapus
            // dokumen tanpa menambah tabel baru.
            $table->json('items')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['is_active', 'sort_order']);
        });

        // Salinan template untuk satu pasangan. Nullable wedding_id supaya
        // superadmin (yang tidak punya wedding) tetap bisa melihat template.
        Schema::create('document_requirements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('wedding_id')->constrained()->cascadeOnDelete();
            $table->foreignId('document_template_id')->nullable()->constrained()->nullOnDelete();

            // PRD Field: Nama Dokumen, PIC, Deadline, Status
            $table->string('name');
            $table->string('pic_name')->nullable();
            $table->string('phone')->nullable();
            $table->date('deadline')->nullable();
            $table->string('status')->default('Belum Lengkap');
            $table->string('document_file')->nullable();
            $table->text('notes')->nullable();

            // PRD: riwayat. Status tiapRequirement dicatat sebagai baris di
            // document_histories supaya perubahan bisa ditelusuri.
            $table->timestamps();

            $table->index(['wedding_id', 'status']);
        });

        // PRD Fitur: Riwayat. Setiap perubahan status留下一sid jejak.
        Schema::create('document_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('document_requirement_id')
                ->constrained('document_requirements')
                ->cascadeOnDelete();
            // Disalin juga supaya ScopedToWedding bisa membatasi riwayat
            // per pasangan tanpa harus join ke document_requirements.
            $table->foreignId('wedding_id')->constrained()->cascadeOnDelete();
            $table->string('status')->nullable();
            $table->string('pic_name')->nullable();
            $table->date('deadline')->nullable();
            $table->text('note')->nullable();
            $table->timestamps();

            $table->index(['wedding_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('document_histories');
        Schema::dropIfExists('document_requirements');
        Schema::dropIfExists('document_templates');
    }
};