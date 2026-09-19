<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('store_settings', function (Blueprint $table) {
            $table->string('email')->nullable()->after('whatsapp_number');
            $table->string('tiktok_url')->nullable()->after('instagram_url');
            $table->longText('privacy_policy')->nullable()->after('schedule');
            $table->longText('terms_conditions')->nullable()->after('privacy_policy');
        });
    }

    public function down(): void
    {
        Schema::table('store_settings', function (Blueprint $table) {
            $table->dropColumn(['email', 'tiktok_url', 'privacy_policy', 'terms_conditions']);
        });
    }
};
