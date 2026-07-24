<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('moodboards', function (Blueprint $table) {
            $table->string('link_preview')->nullable()->after('link_reference');
        });
    }

    public function down(): void
    {
        Schema::table('moodboards', function (Blueprint $table) {
            $table->dropColumn('link_preview');
        });
    }
};
