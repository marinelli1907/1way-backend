<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Part B — Zone multiplier
 * Adds a pricing multiplier to zones (e.g. 1.2 = 20% surcharge in this zone).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('zones', function (Blueprint $table) {
            if (! Schema::hasColumn('zones', 'pricing_multiplier')) {
                $table->decimal('pricing_multiplier', 5, 4)->default(1.0000)
                    ->after('extra_fare_reason')
                    ->comment('Fare multiplier for this zone (1.0 = no change, 1.2 = 20% surcharge)');
            }
            if (! Schema::hasColumn('zones', 'description')) {
                $table->text('description')->nullable()
                    ->after('pricing_multiplier')
                    ->comment('Human-readable zone description (e.g. Downtown Miami)');
            }
        });
    }

    public function down(): void
    {
        Schema::table('zones', function (Blueprint $table) {
            $table->dropColumn(['pricing_multiplier', 'description']);
        });
    }
};
