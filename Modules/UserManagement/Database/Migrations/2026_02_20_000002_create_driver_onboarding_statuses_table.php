<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Part D — Driver Onboarding Checklist
 * One row per driver tracking their onboarding progress.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('driver_onboarding_statuses', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('driver_id');
            $table->foreign('driver_id')->references('id')->on('users')->onDelete('cascade');

            // Checklist steps
            $table->boolean('profile_complete')->default(false);
            $table->boolean('docs_uploaded')->default(false);
            $table->boolean('approved')->default(false);
            $table->boolean('active')->default(false);

            // Optional notes per step
            $table->text('notes')->nullable();

            // Who approved
            $table->uuid('approved_by')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('driver_onboarding_statuses');
    }
};
