<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('trip_requests', function (Blueprint $table) {
            $table->string('flight_number', 16)->nullable()->after('map_screenshot');
            $table->date('flight_date')->nullable()->after('flight_number');
            $table->json('flight_status_cached')->nullable()->after('flight_date');
            $table->timestamp('flight_status_checked_at')->nullable()->after('flight_status_cached');
        });
    }

    public function down(): void
    {
        Schema::table('trip_requests', function (Blueprint $table) {
            $table->dropColumn([
                'flight_number',
                'flight_date',
                'flight_status_cached',
                'flight_status_checked_at',
            ]);
        });
    }
};
