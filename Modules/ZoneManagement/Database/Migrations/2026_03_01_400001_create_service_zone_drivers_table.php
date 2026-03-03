<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_zone_drivers', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('service_zone_id')->constrained('service_zones')->cascadeOnDelete();
            $table->foreignUuid('driver_user_id')->constrained('users')->cascadeOnDelete();
            $table->boolean('is_active')->default(true);
            $table->integer('priority')->default(0);
            $table->timestamps();

            $table->unique(['service_zone_id', 'driver_user_id'], 'szd_zone_driver_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_zone_drivers');
    }
};
