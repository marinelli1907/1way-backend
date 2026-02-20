<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

/**
 * Seeds the minimum test data needed for smoke tests to pass.
 *
 * Run on the production server:
 *   php artisan db:seed --class=TestDataSeeder
 *
 * Test accounts created:
 *   Rider  : phone=15555550123  password=password
 *   Driver : phone=15555550124  password=password
 */
class TestDataSeeder extends Seeder
{
    const RIDER_PHONE  = '15555550123';
    const DRIVER_PHONE = '15555550124';
    const PASSWORD     = 'password';

    // Matches smoke-test ride create payload
    const VEHICLE_CATEGORY_ID = '80787aa9-10b4-4af5-81a8-6c79a8acf154';

    // Cleveland, OH – near smoke-test pickup coords (41.4993, -81.6944)
    const DRIVER_LAT = '41.4993';
    const DRIVER_LNG = '-81.6944';

    // Stable test-only UUIDs so re-running stays idempotent
    const TEST_BRAND_ID   = '11111111-1111-1111-1111-111111111111';
    const TEST_MODEL_ID   = '22222222-2222-2222-2222-222222222222';
    const TEST_VEHICLE_ID = '33333333-3333-3333-3333-333333333333';

    public function run(): void
    {
        DB::transaction(function () {
            $riderId  = $this->upsertRider();
            $driverId = $this->upsertDriver();
            $this->upsertDriverDetails($driverId);
            $this->upsertTestVehicleBrand();
            $this->upsertTestVehicleModel();
            $this->upsertDriverVehicle($driverId);
            $this->upsertDriverLocation($driverId);
            $this->ensureTimeTrack($driverId);
        });

        $this->command->info('');
        $this->command->info('✅ Test data seeded successfully.');
        $this->command->info('   Rider  → phone=' . self::RIDER_PHONE  . '  password=' . self::PASSWORD);
        $this->command->info('   Driver → phone=' . self::DRIVER_PHONE . '  password=' . self::PASSWORD);
        $this->command->info('');
    }

    // -------------------------------------------------------------------------

    private function upsertRider(): string
    {
        $row = DB::table('users')->where('phone', self::RIDER_PHONE)->first();

        if ($row) {
            DB::table('users')->where('id', $row->id)->update([
                'password'       => Hash::make(self::PASSWORD),
                'is_active'      => 1,
                'phone_verified_at' => now(),
                'is_temp_blocked'   => 0,
                'blocked_at'     => null,
                'failed_attempt' => 0,
                'deleted_at'     => null,
                'updated_at'     => now(),
            ]);
            $this->command->line("  Rider updated  (id={$row->id})");
            return $row->id;
        }

        $id = (string) Str::uuid();
        DB::table('users')->insert([
            'id'                => $id,
            'first_name'        => 'Test',
            'last_name'         => 'Rider',
            'phone'             => self::RIDER_PHONE,
            'email'             => 'rider@test.1way',
            'password'          => Hash::make(self::PASSWORD),
            'user_type'         => 'customer',
            'is_active'         => 1,
            'phone_verified_at' => now(),
            'is_temp_blocked'   => 0,
            'failed_attempt'    => 0,
            'created_at'        => now(),
            'updated_at'        => now(),
        ]);
        $this->command->line("  Rider created  (id={$id})");
        return $id;
    }

    private function upsertDriver(): string
    {
        $row = DB::table('users')->where('phone', self::DRIVER_PHONE)->first();

        if ($row) {
            DB::table('users')->where('id', $row->id)->update([
                'password'          => Hash::make(self::PASSWORD),
                'is_active'         => 1,
                'user_type'         => 'driver',
                'phone_verified_at' => now(),
                'is_temp_blocked'   => 0,
                'blocked_at'        => null,
                'failed_attempt'    => 0,
                'deleted_at'        => null,
                'updated_at'        => now(),
            ]);
            $this->command->line("  Driver updated (id={$row->id})");
            return $row->id;
        }

        $id = (string) Str::uuid();
        DB::table('users')->insert([
            'id'                => $id,
            'first_name'        => 'Test',
            'last_name'         => 'Driver',
            'phone'             => self::DRIVER_PHONE,
            'email'             => 'driver@test.1way',
            'password'          => Hash::make(self::PASSWORD),
            'user_type'         => 'driver',
            'is_active'         => 1,
            'phone_verified_at' => now(),
            'is_temp_blocked'   => 0,
            'failed_attempt'    => 0,
            'created_at'        => now(),
            'updated_at'        => now(),
        ]);
        $this->command->line("  Driver created (id={$id})");
        return $id;
    }

    private function upsertDriverDetails(string $driverId): void
    {
        $exists = DB::table('driver_details')->where('user_id', $driverId)->exists();

        $data = [
            'is_online'           => 0,
            'availability_status' => 'unavailable',
            'updated_at'          => now(),
        ];

        // service column added in 2024 migration – include only when it exists
        if (Schema::hasColumn('driver_details', 'service')) {
            $data['service'] = json_encode(['ride_request']);
        }

        if ($exists) {
            DB::table('driver_details')->where('user_id', $driverId)->update($data);
        } else {
            DB::table('driver_details')->insert(array_merge($data, [
                'user_id'    => $driverId,
                'created_at' => now(),
            ]));
        }
    }

    private function upsertTestVehicleBrand(): void
    {
        if (DB::table('vehicle_brands')->where('id', self::TEST_BRAND_ID)->exists()) {
            return;
        }
        // guard against unique constraint on name
        if (DB::table('vehicle_brands')->where('name', 'TestBrand')->exists()) {
            DB::table('vehicle_brands')
                ->where('name', 'TestBrand')
                ->update(['id' => self::TEST_BRAND_ID, 'is_active' => 1, 'updated_at' => now()]);
            return;
        }
        DB::table('vehicle_brands')->insert([
            'id'          => self::TEST_BRAND_ID,
            'name'        => 'TestBrand',
            'description' => 'Smoke-test brand',
            'image'       => 'default.png',
            'is_active'   => 1,
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);
    }

    private function upsertTestVehicleModel(): void
    {
        if (DB::table('vehicle_models')->where('id', self::TEST_MODEL_ID)->exists()) {
            return;
        }
        DB::table('vehicle_models')->insert([
            'id'               => self::TEST_MODEL_ID,
            'name'             => 'TestModel',
            'brand_id'         => self::TEST_BRAND_ID,
            'seat_capacity'    => 4,
            'maximum_weight'   => 500,
            'hatch_bag_capacity' => 2,
            'engine'           => '2.0L',
            'description'      => 'Smoke-test model',
            'image'            => 'default.png',
            'is_active'        => 1,
            'created_at'       => now(),
            'updated_at'       => now(),
        ]);
    }

    private function upsertDriverVehicle(string $driverId): void
    {
        $existing = DB::table('vehicles')
            ->whereNull('deleted_at')
            ->where('driver_id', $driverId)
            ->first();

        $hasRequestStatus = Schema::hasColumn('vehicles', 'vehicle_request_status');
        $hasDraft         = Schema::hasColumn('vehicles', 'draft');

        if ($existing) {
            $update = [
                'category_id' => self::VEHICLE_CATEGORY_ID,
                'is_active'   => 1,
                'deleted_at'  => null,
                'updated_at'  => now(),
            ];
            if ($hasRequestStatus) {
                $update['vehicle_request_status'] = 'approved';
            }
            DB::table('vehicles')->where('id', $existing->id)->update($update);
            return;
        }

        $insert = [
            'id'                   => self::TEST_VEHICLE_ID,
            'ref_id'               => 'TEST001',
            'brand_id'             => self::TEST_BRAND_ID,
            'model_id'             => self::TEST_MODEL_ID,
            'category_id'          => self::VEHICLE_CATEGORY_ID,
            'driver_id'            => $driverId,
            'licence_plate_number' => 'TEST-1WAY',
            'licence_expire_date'  => '2028-01-01',
            'vin_number'           => 'TEST1WAY0000000001',
            'transmission'         => 'automatic',
            'fuel_type'            => 'gasoline',
            'ownership'            => 'own',
            'is_active'            => 1,
            'created_at'           => now(),
            'updated_at'           => now(),
        ];

        if ($hasDraft) {
            $insert['draft'] = 0;
        }
        if ($hasRequestStatus) {
            $insert['vehicle_request_status'] = 'approved';
        }

        // Ignore if the stable vehicle UUID already exists from a previous seed
        DB::table('vehicles')->insertOrIgnore($insert);
    }

    private function upsertDriverLocation(string $driverId): void
    {
        $exists = DB::table('user_last_locations')->where('user_id', $driverId)->exists();

        if ($exists) {
            DB::table('user_last_locations')->where('user_id', $driverId)->update([
                'latitude'   => self::DRIVER_LAT,
                'longitude'  => self::DRIVER_LNG,
                'updated_at' => now(),
            ]);
        } else {
            DB::table('user_last_locations')->insert([
                'user_id'    => $driverId,
                'type'       => 'driver',
                'latitude'   => self::DRIVER_LAT,
                'longitude'  => self::DRIVER_LNG,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    /**
     * Ensure a time_track row exists for today so the onlineStatus toggle
     * works without throwing on a null $track.
     */
    private function ensureTimeTrack(string $driverId): void
    {
        $today = date('Y-m-d');

        $exists = DB::table('time_tracks')
            ->where('user_id', $driverId)
            ->where('date', $today)
            ->exists();

        if (!$exists) {
            DB::table('time_tracks')->insert([
                'user_id'      => $driverId,
                'date'         => $today,
                'total_online' => 0,
                'total_offline'=> 0,
                'total_idle'   => 0,
                'total_driving'=> 0,
                'created_at'   => now(),
                'updated_at'   => now(),
            ]);
        }
    }
}
