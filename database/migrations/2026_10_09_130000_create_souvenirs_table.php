<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('souvenirs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('wedding_id')->constrained()->cascadeOnDelete();
            $table->foreignId('vendor_id')->nullable()->constrained()->nullOnDelete();

            // Item seserahan: nama barang, vendor, foto, dan link pembelian.
            $table->string('name');
            $table->string('link')->nullable();
            $table->string('photo')->nullable();

            // planned = harga yang dianggarkan, actual = harga yang dibayar.
            // Saat status "Diterima", actual diposting ke Budget Planner.
            $table->decimal('planned_price', 15, 2)->default(0);
            $table->decimal('actual_cost', 15, 2)->default(0);

            $table->string('status')->default('Belum Dipilih');
            $table->date('received_date')->nullable();
            $table->text('notes')->nullable();

            $table->timestamps();

            $table->index(['wedding_id', 'status']);
        });

        // Idempotensi auto-post budget: satu baris Budget Planner per item
        // seserahan. Tanpa FK ini, mengubah status jadi "Diterima" dua kali
        // akan membuat dua baris dan angka anggaran jadi dobel.
        Schema::table('budgets', function (Blueprint $table) {
            $table->foreignId('souvenir_id')
                ->nullable()
                ->after('vendor_id')
                ->constrained('souvenirs')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('budgets', function (Blueprint $table) {
            $table->dropConstrainedForeignId('souvenir_id');
        });

        Schema::dropIfExists('souvenirs');
    }
};