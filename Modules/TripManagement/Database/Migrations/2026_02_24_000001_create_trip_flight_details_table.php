<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('trip_flight_details', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('trip_request_id')->unique();
            $table->string('provider', 40)->default('mock');
            $table->boolean('verified')->default(false);
            $table->string('input_type', 30);
            $table->string('flight_number')->nullable();
            $table->date('flight_date')->nullable();
            $table->string('airline_code', 10)->nullable();
            $table->string('airline_name')->nullable();
            $table->string('status', 30)->nullable();
            $table->string('dep_airport_iata', 10)->nullable();
            $table->string('dep_airport_name')->nullable();
            $table->string('arr_airport_iata', 10)->nullable();
            $table->string('arr_airport_name')->nullable();
            $table->dateTime('sched_dep_at')->nullable();
            $table->dateTime('sched_arr_at')->nullable();
            $table->dateTime('est_dep_at')->nullable();
            $table->dateTime('est_arr_at')->nullable();
            $table->string('terminal')->nullable();
            $table->string('gate')->nullable();
            $table->string('baggage')->nullable();
            $table->json('raw')->nullable();
            $table->dateTime('last_synced_at')->nullable();
            $table->timestamps();

            $table->foreign('trip_request_id')
                ->references('id')
                ->on('trip_requests')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('trip_flight_details');
    }
};
