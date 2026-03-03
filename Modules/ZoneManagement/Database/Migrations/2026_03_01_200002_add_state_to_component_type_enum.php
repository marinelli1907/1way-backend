<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE service_zone_components MODIFY COLUMN component_type ENUM('city','county','zip','state','custom','import') NOT NULL DEFAULT 'custom'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE service_zone_components MODIFY COLUMN component_type ENUM('city','county','zip','custom','import') NOT NULL DEFAULT 'custom'");
    }
};
