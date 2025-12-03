<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateZonesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('zones', function (Blueprint $table) {
            $table->bigIncrements('id');

            // Zone name (e.g. "Downtown", "Airport Area")
            $table->string('name');

            // Optional short code (e.g. "DT", "AIRPORT")
            $table->string('short_name')->nullable();

            /**
             * IMPORTANT:
             * Original DriveMond used:
             *   $table->polygon('coordinates')->nullable();
             * which only works with MySQL + spatial extensions.
             *
             * We are on SQLite, so we store the coordinates
             * as plain text/JSON instead.
             *
             * You can save GeoJSON, an array of lat/lng pairs,
             * or any serialized format here.
             */
            $table->longText('coordinates')->nullable();

            // Whether this zone is active for pricing/dispatch
            $table->boolean('is_active')->default(true);

            // Audit columns (optional but common in these templates)
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('zones');
    }
}
