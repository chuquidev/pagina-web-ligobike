<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('maintenance_settings', function (Blueprint $table) {
            $table->id();
            $table->json('business_hours')->nullable();
            $table->unsignedInteger('capacity')->default(1);
            $table->unsignedInteger('slot_interval_minutes')->default(30);
            $table->unsignedInteger('advance_booking_days')->default(14);
            $table->unsignedInteger('min_notice_hours')->default(2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('maintenance_settings');
    }
};
