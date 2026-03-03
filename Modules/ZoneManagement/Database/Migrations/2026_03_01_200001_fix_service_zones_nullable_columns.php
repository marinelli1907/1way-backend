<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('service_zones', function (Blueprint $table) {
            $table->json('boundary')->nullable()->change();
            $table->json('exclusions')->nullable()->change();
        });
    }

    public function down(): void
    {
        // no-op: we don't want to make them NOT NULL again
    }
};
