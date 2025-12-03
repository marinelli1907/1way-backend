<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRecentAddressesTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('recent_addresses', function (Blueprint $table) {
            $table->bigIncrements('id');

            // Which user this address belongs to
            $table->unsignedBigInteger('user_id')->index();

            // Human readable label: "Home", "Work", "Gym", etc.
            $table->string('label')->nullable();

            // Full address text
            $table->text('address');

            /**
             * Original DriveMond code likely had something like:
             *   $table->point('location');
             *
             * We can't use spatial point() on SQLite, so we store
             * lat/lng as JSON instead, e.g. { "lat": 41.502, "lng": -81.694 }.
             */
            $table->json('location')->nullable();

            // Optional type: pickup, dropoff, favorite, etc.
            $table->string('type')->nullable();

            $table->timestamps();

            // Optional FK (not required for SQLite, but fine to leave commented)
            // $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recent_addresses');
    }
}
