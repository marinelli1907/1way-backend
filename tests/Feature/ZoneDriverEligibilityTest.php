<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Modules\UserManagement\Entities\User;
use Modules\ZoneManagement\Entities\ServiceZone;
use Modules\ZoneManagement\Service\ZoneDriverEligibilityService;
use Tests\TestCase;

class ZoneDriverEligibilityTest extends TestCase
{
    /**
     * Use an extremely isolated area (Aleutian Islands, AK) to avoid collisions
     * with production zones. Priority 999999 to always win.
     */
    private function createZone(array $overrides = []): ServiceZone
    {
        return ServiceZone::create(array_merge([
            'name'         => 'Elig Zone ' . uniqid(),
            'country_code' => 'US',
            'state_code'   => 'AK',
            'source'       => 'manual',
            'is_active'    => true,
            'priority'     => 999999,
            'boundary'     => [
                'type' => 'MultiPolygon',
                'coordinates' => [[[[-171.0, 52.0], [-170.0, 52.0], [-170.0, 53.0], [-171.0, 53.0], [-171.0, 52.0]]]],
            ],
        ], $overrides));
    }

    private function createDriver(string $suffix = ''): User
    {
        $phone = '1558' . str_pad(mt_rand(0, 99999999), 8, '0', STR_PAD_LEFT);
        return User::create([
            'first_name' => 'EligDrv' . $suffix,
            'last_name'  => 'Test',
            'full_name'  => 'EligDrv' . $suffix . ' Test',
            'email'      => "edrv{$suffix}-{$phone}@test.local",
            'phone'      => $phone,
            'password'   => Hash::make('password'),
            'user_type'  => DRIVER,
            'is_active'  => 1,
        ]);
    }

    private function pickupInZone(): array
    {
        return [52.5, -170.5]; // lat, lng inside the test zone
    }

    // ── Test 1: Zone with 0 assigned drivers returns 409 ───────────────

    public function test_zone_with_no_drivers_throws_409(): void
    {
        $zone = $this->createZone();
        [$lat, $lng] = $this->pickupInZone();

        $service = app(ZoneDriverEligibilityService::class);

        try {
            $service->resolveEligibility($lat, $lng);
            $this->fail('Expected HttpResponseException for zone with 0 drivers');
        } catch (\Illuminate\Http\Exceptions\HttpResponseException $e) {
            $response = $e->getResponse();
            $this->assertEquals(409, $response->getStatusCode());
            $data = json_decode($response->getContent(), true);
            $this->assertEquals('no_drivers_in_zone', $data['response_code']);
            $this->assertEquals((string) $zone->id, $data['zone_id']);
            $this->assertStringContainsString('No drivers available', $data['message']);
        }

        $zone->delete();
    }

    // ── Test 2: Zone with assigned drivers returns only those IDs ──────

    public function test_zone_with_drivers_returns_only_assigned(): void
    {
        $zone = $this->createZone();
        $d1 = $this->createDriver('A');
        $d2 = $this->createDriver('B');
        $dExtra = $this->createDriver('Extra');

        // Attach d1 and d2 to this zone
        DB::table('service_zone_drivers')->insert([
            ['service_zone_id' => $zone->id, 'driver_user_id' => $d1->id, 'is_active' => true, 'priority' => 0, 'created_at' => now(), 'updated_at' => now()],
            ['service_zone_id' => $zone->id, 'driver_user_id' => $d2->id, 'is_active' => true, 'priority' => 0, 'created_at' => now(), 'updated_at' => now()],
        ]);

        [$lat, $lng] = $this->pickupInZone();
        $service = app(ZoneDriverEligibilityService::class);
        $result = $service->resolveEligibility($lat, $lng);

        $this->assertNotNull($result['driver_ids']);
        $this->assertCount(2, $result['driver_ids']);
        $this->assertContains((string) $d1->id, $result['driver_ids']);
        $this->assertContains((string) $d2->id, $result['driver_ids']);
        $this->assertNotContains((string) $dExtra->id, $result['driver_ids']);

        DB::table('service_zone_drivers')->where('service_zone_id', $zone->id)->delete();
        $zone->delete();
    }

    // ── Test 3: No zone match + ZONES_ENFORCED=false => no restriction ─

    public function test_no_zone_match_unforced_returns_null_ids(): void
    {
        $service = app(ZoneDriverEligibilityService::class);
        // Point in the middle of the Pacific — no zone exists here
        $result = $service->resolveEligibility(0.0, 0.0);

        $this->assertNull($result['zone']);
        $this->assertNull($result['driver_ids']);
    }

    // ── Test 4: No fallback — zone with 0 drivers blocks despite global drivers ─

    public function test_no_fallback_to_all_drivers(): void
    {
        $zone = $this->createZone();
        // Don't assign any drivers — global driver should NOT help
        $globalDriver = $this->createDriver('Global');

        [$lat, $lng] = $this->pickupInZone();
        $service = app(ZoneDriverEligibilityService::class);

        try {
            $service->resolveEligibility($lat, $lng);
            $this->fail('Expected 409 even though global drivers exist');
        } catch (\Illuminate\Http\Exceptions\HttpResponseException $e) {
            $response = $e->getResponse();
            $this->assertEquals(409, $response->getStatusCode());
            $data = json_decode($response->getContent(), true);
            $this->assertEquals('no_drivers_in_zone', $data['response_code']);
        }

        $zone->delete();
    }

    // ── Test 5: GeoZoneService::getEligibleDriversForZone — no fallback ─

    public function test_geo_service_no_fallback(): void
    {
        $zone = $this->createZone();

        $geoService = app(\Modules\ZoneManagement\Service\GeoZoneService::class);
        $eligible = $geoService->getEligibleDriversForZone((string) $zone->id);

        $this->assertCount(0, $eligible, 'Zone with no assigned drivers must return empty, not all drivers');

        $zone->delete();
    }

    // ── Test 6: Inactive pivot driver excluded ─────────────────────────

    public function test_inactive_pivot_driver_excluded(): void
    {
        $zone = $this->createZone();
        $d1 = $this->createDriver('Active');
        $d2 = $this->createDriver('Inactive');

        DB::table('service_zone_drivers')->insert([
            ['service_zone_id' => $zone->id, 'driver_user_id' => $d1->id, 'is_active' => true, 'priority' => 0, 'created_at' => now(), 'updated_at' => now()],
            ['service_zone_id' => $zone->id, 'driver_user_id' => $d2->id, 'is_active' => false, 'priority' => 0, 'created_at' => now(), 'updated_at' => now()],
        ]);

        [$lat, $lng] = $this->pickupInZone();
        $service = app(ZoneDriverEligibilityService::class);
        $result = $service->resolveEligibility($lat, $lng);

        $this->assertCount(1, $result['driver_ids']);
        $this->assertContains((string) $d1->id, $result['driver_ids']);
        $this->assertNotContains((string) $d2->id, $result['driver_ids']);

        DB::table('service_zone_drivers')->where('service_zone_id', $zone->id)->delete();
        $zone->delete();
    }
}
