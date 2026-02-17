<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('events', function (Blueprint $table) {
            if (!Schema::hasColumn('events', 'title')) {
                $table->string('title')->after('id');
            }

            if (!Schema::hasColumn('events', 'is_public')) {
                $table->boolean('is_public')->default(true)->after('title');
            }

            if (!Schema::hasColumn('events', 'timezone')) {
                $table->string('timezone')->nullable()->after('ends_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $drops = [];

            if (Schema::hasColumn('events', 'title')) $drops[] = 'title';
            if (Schema::hasColumn('events', 'is_public')) $drops[] = 'is_public';
            if (Schema::hasColumn('events', 'timezone')) $drops[] = 'timezone';

            if (!empty($drops)) {
                $table->dropColumn($drops);
            }
        });
    }
};
