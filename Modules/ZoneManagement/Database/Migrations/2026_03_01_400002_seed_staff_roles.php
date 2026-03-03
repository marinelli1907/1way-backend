<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    private const ROLES = [
        'Owner' => [
            'dashboard', 'zone_management', 'trip_management', 'parcel_management',
            'promotion_management', 'vehicle_management', 'fare_management',
            'user_management', 'transaction_management', 'help_and_support',
            'business_management', 'service_zone_management',
        ],
        'Admin' => [
            'dashboard', 'zone_management', 'trip_management', 'parcel_management',
            'promotion_management', 'vehicle_management', 'fare_management',
            'user_management', 'transaction_management', 'help_and_support',
            'business_management', 'service_zone_management',
        ],
        'Ops' => [
            'dashboard', 'zone_management', 'trip_management',
            'vehicle_management', 'user_management', 'service_zone_management',
        ],
        'Support' => [
            'dashboard', 'trip_management', 'user_management', 'help_and_support',
        ],
        'Finance' => [
            'dashboard', 'transaction_management', 'fare_management',
        ],
    ];

    public function up(): void
    {
        foreach (self::ROLES as $name => $modules) {
            if (!DB::table('roles')->where('name', $name)->exists()) {
                DB::table('roles')->insert([
                    'id'         => Str::uuid()->toString(),
                    'name'       => $name,
                    'modules'    => json_encode($modules),
                    'is_active'  => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    public function down(): void
    {
        DB::table('roles')->whereIn('name', array_keys(self::ROLES))->delete();
    }
};
