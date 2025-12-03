<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTripRoutesTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('trip_routes', function (Blueprint $table) {
            $table->bigIncrements('id');

            // Foreign key: trip ID (ride ID)
            $table->unsignedBigInteger('trip_id')->index();

            /**
             * Original DriveMond code used:
             *   $table->point('start_location');
             *   $table->point('end_location');
             *
             * We cannot use "point()" on SQLite.
             * Instead, we safely store lat/lng pairs as JSON.
             */
            $table->json('start_location')->nullable();   // { "lat": ..., "lng": ... }
            $table->json('end_location')->nullable();     // { "lat": ..., "lng": ... }

            // Optional intermediate route path (polyline)
            $table->longText('polyline')->nullable();

            // Distance and duration (calculated or estimated)
            $table->decimal('distance_km', 8, 2)->nullable();
            $table->integer('duration_min')->nullable();

            // Additional route metadata
            $table->string('provider')->nullable();      // e.g. "Google", "OSRM", "Mapbox"
            $table->json('raw_response')->nullable();    // store provider's raw data if needed

            $table->timestamps();

            // Optional: Foreign key constraints (not required for SQLite)
            // $table->foreign('trip_id')->references('id')->on('trips')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trip_routes');
    }
}
