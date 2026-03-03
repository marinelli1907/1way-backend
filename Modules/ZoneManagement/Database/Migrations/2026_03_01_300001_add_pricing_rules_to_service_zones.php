<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('service_zones', 'pricing_rules')) {
            Schema::table('service_zones', function (Blueprint $table) {
                $table->json('pricing_rules')->nullable()->after('priority')
                      ->comment('Zone-level pricing knobs — merged with defaults at runtime');
            });
        }
    }

    public function down(): void
    {
        Schema::table('service_zones', function (Blueprint $table) {
            $table->dropColumn('pricing_rules');
        });
    }
};
