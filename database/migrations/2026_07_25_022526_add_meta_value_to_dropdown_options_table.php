<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('dropdown_options', function (Blueprint $table) {
            $table->string('meta_value')->nullable()->after('option_value');
        });
    }

    public function down(): void
    {
        Schema::table('dropdown_options', function (Blueprint $table) {
            $table->dropColumn('meta_value');
        });
    }
};
