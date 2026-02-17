k<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('events', function (Blueprint $table) {
            // Use nullable so we don't break existing rows
            if (!Schema::hasColumn('events', 'starts_at')) {
                $table->dateTime('starts_at')->nullable()->index();
            }
            if (!Schema::hasColumn('events', 'ends_at')) {
                $table->dateTime('ends_at')->nullable()->index();
            }
        });
    }

    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            if (Schema::hasColumn('events', 'starts_at')) $table->dropColumn('starts_at');
            if (Schema::hasColumn('events', 'ends_at')) $table->dropColumn('ends_at');
        });
    }
};
