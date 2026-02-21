<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Part E — AI Section
 * ai_logs: Records every AI tool run for auditing.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_logs', function (Blueprint $table) {
            $table->id();
            $table->string('tool', 80)->index();        // e.g. "suggest_zone_boundaries"
            $table->string('status', 20)->default('pending'); // pending | running | success | failed
            $table->json('input')->nullable();          // sanitised inputs (no PII)
            $table->json('output')->nullable();         // result / suggestion
            $table->text('error')->nullable();          // error message if failed
            $table->unsignedInteger('duration_ms')->nullable(); // how long it took
            $table->uuid('triggered_by')->nullable();   // admin user ID
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_logs');
    }
};
