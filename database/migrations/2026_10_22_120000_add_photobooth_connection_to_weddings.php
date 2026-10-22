<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('weddings', function (Blueprint $table) {
            $table->string('photobooth_event_slug')->nullable()->after('plan');
            $table->text('photobooth_api_key')->nullable()->after('photobooth_event_slug');
            $table->boolean('photobooth_active')->default(false)->after('photobooth_api_key');
        });
    }

    public function down(): void
    {
        Schema::table('weddings', function (Blueprint $table) {
            $table->dropColumn(['photobooth_event_slug', 'photobooth_api_key', 'photobooth_active']);
        });
    }
};
