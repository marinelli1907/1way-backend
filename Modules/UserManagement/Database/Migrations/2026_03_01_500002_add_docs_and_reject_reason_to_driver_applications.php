<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('driver_applications', function (Blueprint $table) {
            $table->json('docs')->nullable()->after('license_photo_size');
            $table->text('reject_reason')->nullable()->after('reviewed_at');
        });
    }

    public function down(): void
    {
        Schema::table('driver_applications', function (Blueprint $table) {
            $table->dropColumn(['docs', 'reject_reason']);
        });
    }
};
