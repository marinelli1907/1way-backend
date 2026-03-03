<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('trip_requests', function (Blueprint $table) {
            $table->unsignedTinyInteger('passengers_count')->nullable()->after('note');
            $table->unsignedTinyInteger('pets_count')->nullable()->after('passengers_count');
            $table->timestamp('scheduled_at')->nullable()->after('pets_count');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('trip_requests', function (Blueprint $table) {
            $table->dropColumn(['passengers_count', 'pets_count', 'scheduled_at']);
        });
    }
};
