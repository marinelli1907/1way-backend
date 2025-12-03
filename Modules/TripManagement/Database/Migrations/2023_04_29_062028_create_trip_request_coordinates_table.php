<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTripRequestCoordinatesTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('trip_request_coordinates', function (Blueprint $table) {
            $table->bigIncrements('id');

            // Link back to the trip_requests table
            $table->unsignedBigInteger('trip_request_id')->index();

            /**
             * Original DriveMond code likely used spatial points:
             *
             *   $table->point('pickup_location');
             *   $table->point('dropoff_location');
             *
             * Those only work with MySQL + spatial extensions.
             * For SQLite we store coordinates as JSON:
             *   { "lat": 41.502, "lng": -81.694 }
             */
            $table->json('pickup_location')->nullable();
            $table->json('dropoff_location')->nullable();

            // Optional full route polyline / path if you store it
            $table->longText('polyline')->nullable();

            // Distance + duration estimates for this requested route
            $table->decimal('distance_km', 8, 2)->nullable();
            $table->integer('duration_min')->nullable();

            // Provider metadata (Google, OSRM, Mapbox, etc.)
            $table->string('provider')->nullable();
            $table->json('raw_response')->nullable();

            $table->timestamps();

            // Optional FK; safe to leave commented if needed
            // $table->foreign('trip_request_id')
            //       ->references('id')->on('trip_requests')
            //       ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trip_request_coordinates');
    }
}
