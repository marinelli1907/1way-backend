<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Http;
use Modules\ZoneManagement\Entities\ServiceZone;
use Tests\TestCase;

class PricingQuoteTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Http::fake([
            'maps.googleapis.com/maps/api/distancematrix/*' => Http::response([
                'status' => 'OK',
                'rows' => [[
                    'elements' => [[
                        'status' => 'OK',
                        'distance' => ['value' => 16093, 'text' => '10 mi'],
                        'duration' => ['value' => 900, 'text' => '15 mins'],
                        'duration_in_traffic' => ['value' => 1020, 'text' => '17 mins'],
                    ]],
                ]],
            ]),
        ]);
    }

    public function test_quote_with_latlng_returns_pricing(): void
    {
        $response = $this->postJson('/api/pricing/quote', [
            'pickup_lat'  => 41.4993,
            'pickup_lng'  => -81.6944,
            'dropoff_lat' => 41.4500,
            'dropoff_lng' => -81.6800,
        ]);

        $response->assertOk();
        $response->assertJsonStructure([
            'success',
            'distance_meters',
            'duration_seconds',
            'distance_miles',
            'duration_minutes',
            'pricing' => [
                'base_fee_cents',
                'per_mile_cents',
                'per_minute_cents',
                'booking_fee_cents',
                'surge_multiplier',
                'final_fare_cents',
            ],
            'source',
            'debug' => ['mode', 'used', 'google_status'],
        ]);

        $data = $response->json();
        $this->assertTrue($data['success']);
        $this->assertIsInt($data['pricing']['final_fare_cents']);
        $this->assertGreaterThan(0, $data['pricing']['final_fare_cents']);
        $this->assertEquals('latlng', $data['debug']['used']);
        $this->assertEquals('deterministic', $data['source']);
    }

    public function test_quote_with_place_ids_returns_pricing(): void
    {
        $response = $this->postJson('/api/pricing/quote', [
            'pickup_place_id'  => 'ChIJLWTO_KEfMIgRB3CF9GgPYGQ',
            'dropoff_place_id' => 'ChIJA4UGSG_vMIgRNcgmp8mDzTE',
        ]);

        $response->assertOk();
        $data = $response->json();
        $this->assertTrue($data['success']);
        $this->assertEquals('place_id', $data['debug']['used']);
    }

    public function test_quote_rejects_missing_locations(): void
    {
        $response = $this->postJson('/api/pricing/quote', [
            'pickup_lat' => 41.5,
        ]);

        $response->assertStatus(422);
        $this->assertFalse($response->json('success'));
    }

    public function test_quote_pricing_uses_defaults_when_no_zone(): void
    {
        $response = $this->postJson('/api/pricing/quote', [
            'pickup_lat'  => 10.0,
            'pickup_lng'  => -10.0,
            'dropoff_lat' => 10.1,
            'dropoff_lng' => -10.1,
        ]);

        $response->assertOk();
        $data = $response->json();
        $defaults = ServiceZone::DEFAULT_PRICING_RULES;

        $this->assertEquals($defaults['base_fee_cents'], $data['pricing']['base_fee_cents']);
        $this->assertEquals($defaults['per_mile_cents'], $data['pricing']['per_mile_cents']);
        $this->assertEquals($defaults['per_minute_cents'], $data['pricing']['per_minute_cents']);
        $this->assertEquals($defaults['booking_fee_cents'], $data['pricing']['booking_fee_cents']);
    }
}
