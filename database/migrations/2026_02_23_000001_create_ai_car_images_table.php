<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_car_images', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('user_id', 36)->nullable()->index();
            $table->string('make', 50)->nullable();
            $table->string('model', 50)->nullable();
            $table->string('color', 50)->nullable();
            $table->string('status', 20)->default('queued'); // queued|running|done|failed
            $table->string('image_path')->nullable();
            $table->string('image_url')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_car_images');
    }
};
