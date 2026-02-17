<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('driver_monthly_earnings', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('driver_id');

            $table->integer('year');
            $table->integer('month');

            $table->integer('total_rides')->default(0);
            $table->decimal('gross_earnings', 10, 2)->default(0);
            $table->decimal('platform_fees', 10, 2)->default(0);
            $table->decimal('driver_payout', 10, 2)->default(0);

            $table->timestamps();

            $table->unique(['driver_id', 'year', 'month']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('driver_monthly_earnings');
    }
};
