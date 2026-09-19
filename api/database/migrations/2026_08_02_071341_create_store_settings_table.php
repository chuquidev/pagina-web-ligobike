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
        Schema::create('store_settings', function (Blueprint $table) {
            $table->id();
            $table->string('store_name');
            $table->string('whatsapp_number');
            $table->string('facebook_url')->nullable();
            $table->string('instagram_url')->nullable();
            $table->string('address')->nullable();
            $table->string('schedule')->nullable();
            $table->string('primary_color')->default('#1e3a8a');
            $table->string('secondary_color')->default('#3b82f6');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('store_settings');
    }
};
