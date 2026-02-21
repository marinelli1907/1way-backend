<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Part D — Quick Add Driver
 * Adds driver split % and city/region to users table.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'driver_split_percent')) {
                $table->decimal('driver_split_percent', 5, 2)->default(80.00)
                    ->after('role_id')
                    ->comment('Driver earnings split % (default 80 = driver keeps 80%)');
            }
            if (! Schema::hasColumn('users', 'city_region')) {
                $table->string('city_region', 120)->nullable()
                    ->after('driver_split_percent')
                    ->comment('City / service region for this driver');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['driver_split_percent', 'city_region']);
        });
    }
};
