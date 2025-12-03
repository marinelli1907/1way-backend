<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('driver_monthly_earnings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('driver_id')->index();
            $table->string('year_month', 7)->index();
            $table->decimal('total_gross', 10, 2)->default(0);
            $table->decimal('driver_earnings', 10, 2)->default(0);
            $table->decimal('platform_earnings', 10, 2)->default(0);
            $table->decimal('platform_threshold', 10, 2)->default(500);
            $table->timestamp('threshold_reached_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('driver_monthly_earnings');
    }
};
