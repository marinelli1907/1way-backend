<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('driver_applications', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');

            $table->string('first_name');
            $table->string('last_name');
            $table->string('phone');
            $table->string('email');
            $table->string('city');
            $table->string('state');

            $table->string('vehicle_make')->nullable();
            $table->string('vehicle_model')->nullable();
            $table->string('vehicle_year', 4)->nullable();
            $table->boolean('rideshare_insurance')->nullable();
            $table->json('availability')->nullable();
            $table->text('preferred_service_area')->nullable();
            $table->text('notes')->nullable();
            $table->boolean('consent')->default(false);

            $table->string('license_photo_path')->nullable();
            $table->string('license_photo_original_name')->nullable();
            $table->string('license_photo_mime', 100)->nullable();
            $table->unsignedInteger('license_photo_size')->nullable();

            $table->uuid('reviewed_by')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('driver_applications');
    }
};
