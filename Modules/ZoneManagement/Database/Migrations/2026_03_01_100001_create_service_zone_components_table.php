<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_zone_components', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('service_zone_id');
            $table->enum('component_type', ['city', 'county', 'zip', 'custom', 'import'])->default('custom');
            $table->string('label');
            $table->string('source', 50)->default('nominatim');
            $table->json('geometry')->comment('GeoJSON MultiPolygon');
            $table->timestamps();

            $table->foreign('service_zone_id')
                  ->references('id')
                  ->on('service_zones')
                  ->onDelete('cascade');
        });

        Schema::create('service_zone_exclusions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('service_zone_id');
            $table->string('label');
            $table->json('geometry')->comment('GeoJSON MultiPolygon');
            $table->timestamps();

            $table->foreign('service_zone_id')
                  ->references('id')
                  ->on('service_zones')
                  ->onDelete('cascade');
        });

        Schema::create('service_zone_inclusions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('service_zone_id');
            $table->string('label');
            $table->json('geometry')->comment('GeoJSON MultiPolygon');
            $table->timestamps();

            $table->foreign('service_zone_id')
                  ->references('id')
                  ->on('service_zones')
                  ->onDelete('cascade');
        });

        if (!Schema::hasColumn('service_zones', 'inclusions_override')) {
            Schema::table('service_zones', function (Blueprint $table) {
                $table->json('inclusions_override')->nullable()->after('exclusions')
                      ->comment('GeoJSON MultiPolygon — re-enables service inside exclusions');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('service_zone_inclusions');
        Schema::dropIfExists('service_zone_exclusions');
        Schema::dropIfExists('service_zone_components');

        if (Schema::hasColumn('service_zones', 'inclusions_override')) {
            Schema::table('service_zones', function (Blueprint $table) {
                $table->dropColumn('inclusions_override');
            });
        }
    }
};
