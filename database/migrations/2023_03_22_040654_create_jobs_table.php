<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateJobsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('jobs', function (Blueprint $table) {
            $table->bigIncrements('id');

            // Who is involved
            $table->unsignedBigInteger('driver_id')->nullable();
            $table->unsignedBigInteger('passenger_id')->nullable();

            // Pickup info
            $table->string('pickup_address');
            $table->decimal('pickup_lat', 10, 7)->nullable();
            $table->decimal('pickup_lng', 10, 7)->nullable();

            // Dropoff info
            $table->string('dropoff_address');
            $table->decimal('dropoff_lat', 10, 7)->nullable();
            $table->decimal('dropoff_lng', 10, 7)->nullable();

            // When is the ride
            $table->dateTime('pickup_time')->nullable();

            // Job status: available, accepted, started, picked_up, completed, canceled
            $table->string('status')->default('available');

            // Money
            $table->decimal('gross_fare', 10, 2)->default(0);   // total charged to rider
            $table->decimal('app_share', 10, 2)->default(0);    // what 1Way keeps
            $table->decimal('driver_share', 10, 2)->default(0); // what driver earns

            // Distance + time
            $table->decimal('distance_km', 8, 2)->nullable();
            $table->integer('duration_min')->nullable();

            // Optional rider details
            $table->string('passenger_name')->nullable();
            $table->string('passenger_phone')->nullable();

            // Notes from passenger or system
            $table->text('notes')->nullable();

            $table->timestamps();

            // (Optional) Foreign keys if your users table is standard
            // Uncomment if you want strict FK checks and your users table exists
            // $table->foreign('driver_id')->references('id')->on('users')->onDelete('set null');
            // $table->foreign('passenger_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('jobs');
    }
}
