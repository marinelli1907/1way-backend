<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_zones', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->enum('boundary_type', ['city', 'county', 'state', 'custom'])->default('custom');
            $table->string('country_code', 2)->default('US');
            $table->string('state_code', 5)->nullable();
            $table->enum('source', ['census', 'osm', 'manual', 'import'])->default('import');
            $table->json('boundary')->comment('GeoJSON MultiPolygon');
            $table->json('exclusions')->nullable()->comment('GeoJSON MultiPolygon');
            $table->boolean('is_active')->default(true);
            $table->integer('priority')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_zones');
    }
};
