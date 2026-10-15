<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Setelan tingkat sistem: harga Pro, rekening tujuan, nomor WA admin.
        // Nilai ini sering berubah (ganti rekening, naikkan harga) sehingga
        // harus bisa diedit superadmin tanpa deploy ulang.
        Schema::create('app_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('app_settings');
    }
};