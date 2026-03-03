<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('service_zone_drivers', function (Blueprint $table) {
            $table->dropColumn('id');
        });

        Schema::table('service_zone_drivers', function (Blueprint $table) {
            $table->id()->first();
        });
    }

    public function down(): void
    {
        Schema::table('service_zone_drivers', function (Blueprint $table) {
            $table->dropColumn('id');
        });

        Schema::table('service_zone_drivers', function (Blueprint $table) {
            $table->uuid('id')->primary()->first();
        });
    }
};
