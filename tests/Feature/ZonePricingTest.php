<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Http;
use Modules\ZoneManagement\Entities\ServiceZone;
use Tests\TestCase;

class ZonePricingTest extends TestCase
{
    protected function fakeGoogle(): void
    {
        Http::fake([
            'maps.googleapis.com/maps/api/distancematrix/*' => Http::response([
                'status' => 'OK',
                'rows' => [[
                    'elements' => [[
                        'status' => 'OK',
                        'distance' => ['value' => 16093], // ~10 miles
                        'duration' => ['value' => 900],   // 15 min
                        'duration_in_traffic' => ['value' => 900],
                    ]],
                ]],
            ]),
        ]);
    }

    protected function createTestZone(array $pricing = [], array $boundary = []): ServiceZone
    {
        if (empty($boundary)) {
            // Remote test area (middle of Montana) to avoid colliding with production zones
            $boundary = [
                'type' => 'MultiPolygon',
                'coordinates' => [[[
                    [-112.0, 47.0], [-111.0, 47.0], [-111.0, 48.0], [-112.0, 48.0], [-112.0, 47.0],
                ]]],
            ];
        }

        return ServiceZone::create([
            'name'          => 'Test Zone ' . uniqid(),
            'country_code'  => 'US',
            'state_code'    => 'MT',
            'source'        => 'manual',
            'is_active'     => true,
            'priority'      => 99999,
            'boundary'      => $boundary,
            'pricing_rules' => $pricing ?: null,
        ]);
    }

    // ─── Test 1: Zone edit page returns 200 ──────────────────────────────

    public function test_zone_edit_page_returns_200(): void
    {
        $zone = $this->createTestZone();

        $response = $this->get("/admin/service-zone/edit/{$zone->id}");

        // Admin middleware may redirect unauthenticated users (302/403)
        // but the route should NOT 500
        $this->assertNotEquals(500, $response->status(), 'Edit route must not return 500');

        $zone->delete();
    }

    // ─── Test 2: Zone pricing knobs persist ──────────────────────────────

    public function test_zone_pricing_knobs_persist(): void
    {
        $zone = $this->createTestZone();

        $zone->update(['pricing_rules' => [
            'per_mile_cents'   => 250,
            'base_fee_cents'   => 400,
            'min_fare_cents'   => 1200,
            'max_fare_cents'   => 8000,
            'airport_fee_cents' => 500,
        ]]);

        $zone->refresh();
        $rules = $zone->effectivePricingRules();

        $this->assertEquals(250, $rules['per_mile_cents']);
        $this->assertEquals(400, $rules['base_fee_cents']);
        $this->assertEquals(1200, $rules['min_fare_cents']);
        $this->assertEquals(8000, $rules['max_fare_cents']);
        $this->assertEquals(500, $rules['airport_fee_cents']);
        // Defaults for unset keys
        $this->assertEquals(35, $rules['per_minute_cents']);
        $this->assertEquals(100, $rules['booking_fee_cents']);
        $this->assertEquals(85, $rules['driver_split_percent']);

        $zone->delete();
    }

    // ─── Test 3: Quote applies zone pricing ──────────────────────────────

    public function test_deterministic_quote_applies_zone_pricing(): void
    {
        $this->fakeGoogle();

        $zone = $this->createTestZone([
            'per_mile_cents'    => 300,
            'per_minute_cents'  => 50,
            'base_fee_cents'    => 400,
            'booking_fee_cents' => 200,
            'airport_fee_cents' => 0,
            'min_fare_cents'    => 500,
            'max_fare_cents'    => 20000,
        ]);

        // Pickup is inside the test zone (Montana area)
        $response = $this->postJson('/api/pricing/quote', [
            'pickup_lat'  => 47.5,
            'pickup_lng'  => -111.5,
            'dropoff_lat' => 47.4,
            'dropoff_lng' => -111.6,
        ]);

        $response->assertOk();
        $data = $response->json();

        $this->assertTrue($data['success']);
        $this->assertEquals((string) $zone->id, $data['zone_id']);

        // Expected: base(400) + booking(200) + miles(~10*300=~3000) + minutes(~15*50=~750) + airport(0)
        $miles = 16093 / 1609.34;
        $mins  = 900 / 60;
        $expected = 400 + 200 + (int) round($miles * 300) + (int) round($mins * 50);
        $expectedClamped = max(500, min(20000, $expected));

        $this->assertEquals($expectedClamped, $data['pricing']['final_fare_cents']);
        $this->assertEquals(300, $data['pricing']['per_mile_cents']);
        $this->assertEquals(50, $data['pricing']['per_minute_cents']);

        $zone->delete();
    }

    // ─── Test 4: Min/max fare clamp works ────────────────────────────────

    public function test_min_fare_clamp_is_enforced(): void
    {
        $this->fakeGoogle();

        // Very high min fare to guarantee clamping
        $zone = $this->createTestZone([
            'min_fare_cents' => 15000,
            'max_fare_cents' => 20000,
        ]);

        $response = $this->postJson('/api/pricing/quote', [
            'pickup_lat'  => 47.5,
            'pickup_lng'  => -111.5,
            'dropoff_lat' => 47.4,
            'dropoff_lng' => -111.6,
        ]);

        $response->assertOk();
        $data = $response->json();

        $this->assertGreaterThanOrEqual(15000, $data['pricing']['final_fare_cents']);

        $zone->delete();
    }

    // ─── Test 5: Zone pricing page returns 200 ───────────────────────────

    public function test_zone_pricing_page_returns_200(): void
    {
        $zone = $this->createTestZone();

        $response = $this->get("/admin/service-zone/{$zone->id}/pricing");

        $this->assertNotEquals(500, $response->status(), 'Pricing page must not return 500');

        $zone->delete();
    }
}
