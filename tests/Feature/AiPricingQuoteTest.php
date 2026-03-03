<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class AiPricingQuoteTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->fakeGoogleDistanceMatrix();
    }

    protected function fakeGoogleDistanceMatrix(): void
    {
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

    protected function fakeXaiResponse(array $body): void
    {
        Http::fake([
            'maps.googleapis.com/maps/api/distancematrix/*' => Http::response([
                'status' => 'OK',
                'rows' => [[
                    'elements' => [[
                        'status' => 'OK',
                        'distance' => ['value' => 16093],
                        'duration' => ['value' => 900],
                        'duration_in_traffic' => ['value' => 1020],
                    ]],
                ]],
            ]),
            'api.x.ai/*' => Http::response($body),
        ]);
    }

    protected function validXaiPayload(array $overrides = []): array
    {
        $aiContent = json_encode(array_merge([
            'demand_multiplier'   => 1.1,
            'supply_multiplier'   => 1.0,
            'zone_multiplier'     => 1.0,
            'time_multiplier'     => 1.05,
            'risk_multiplier'     => 1.0,
            'discount_multiplier' => 1.0,
            'dispatch_bias'       => 'balanced',
            'confidence'          => 0.85,
            'reasons'             => ['Slight demand increase due to evening hours'],
        ], $overrides));

        return [
            'id' => 'test-id',
            'object' => 'chat.completion',
            'choices' => [[
                'message' => [
                    'role' => 'assistant',
                    'content' => $aiContent,
                ],
            ]],
        ];
    }

    protected function baseRequest(): array
    {
        return [
            'pickup_lat'  => 41.4993,
            'pickup_lng'  => -81.6944,
            'dropoff_lat' => 41.4500,
            'dropoff_lng' => -81.6800,
        ];
    }

    // ─── Test 1: Valid AI response ───────────────────────────────────────

    public function test_valid_ai_response_returns_fare_within_guardrails(): void
    {
        $this->fakeXaiResponse($this->validXaiPayload());

        $response = $this->postJson('/api/pricing/ai-quote', $this->baseRequest());

        $response->assertOk();
        $data = $response->json();

        $this->assertTrue($data['success']);
        $this->assertIsInt($data['final_fare_cents']);
        $this->assertIsInt($data['deterministic_fare_cents']);
        $this->assertIsInt($data['base_fare_cents']);
        $this->assertGreaterThanOrEqual(900, $data['final_fare_cents']);
        $this->assertLessThanOrEqual(25000, $data['final_fare_cents']);
        $this->assertFalse($data['fallback']);
        $this->assertEquals('balanced', $data['dispatch_bias']);
        $this->assertGreaterThanOrEqual(0.8, $data['confidence']);
        $this->assertIsArray($data['multipliers']);
        $this->assertIsArray($data['reasons']);
        $this->assertEquals('USD', $data['currency']);
    }

    // ─── Test 2: Invalid JSON from AI → fallback ─────────────────────────

    public function test_invalid_ai_json_triggers_fallback(): void
    {
        $this->fakeXaiResponse([
            'id' => 'test-id',
            'choices' => [[
                'message' => [
                    'role' => 'assistant',
                    'content' => 'Sorry, I cannot process this request.',
                ],
            ]],
        ]);

        $response = $this->postJson('/api/pricing/ai-quote', $this->baseRequest());

        $response->assertOk();
        $data = $response->json();

        $this->assertTrue($data['success']);
        $this->assertTrue($data['fallback']);
        $this->assertEquals($data['deterministic_fare_cents'], $data['final_fare_cents']);
        $this->assertEquals('nearest', $data['dispatch_bias']);
        $this->assertEquals(0.0, $data['confidence']);
    }

    // ─── Test 3: Extreme multipliers → clamped ───────────────────────────

    public function test_extreme_multipliers_are_clamped(): void
    {
        $this->fakeXaiResponse($this->validXaiPayload([
            'demand_multiplier'   => 10.0,   // way above 2.5 max
            'supply_multiplier'   => 0.1,    // way below 0.7 min
            'zone_multiplier'     => 5.0,    // way above 1.8 max
            'time_multiplier'     => 99.0,   // way above 2.0 max
            'risk_multiplier'     => -3.0,   // below 0.8 min
            'discount_multiplier' => 0.01,   // below 0.5 min
        ]));

        $response = $this->postJson('/api/pricing/ai-quote', $this->baseRequest());

        $response->assertOk();
        $data = $response->json();

        $this->assertTrue($data['success']);
        $m = $data['multipliers'];

        $this->assertLessThanOrEqual(2.5, $m['demand_multiplier']);
        $this->assertGreaterThanOrEqual(0.7, $m['supply_multiplier']);
        $this->assertLessThanOrEqual(1.8, $m['zone_multiplier']);
        $this->assertLessThanOrEqual(2.0, $m['time_multiplier']);
        $this->assertGreaterThanOrEqual(0.8, $m['risk_multiplier']);
        $this->assertGreaterThanOrEqual(0.5, $m['discount_multiplier']);

        // Final fare should still be within absolute bounds
        $this->assertGreaterThanOrEqual(900, $data['final_fare_cents']);
        $this->assertLessThanOrEqual(25000, $data['final_fare_cents']);
    }

    // ─── Test 4: xAI timeout → fallback ──────────────────────────────────

    public function test_xai_timeout_triggers_fallback(): void
    {
        Http::fake([
            'maps.googleapis.com/maps/api/distancematrix/*' => Http::response([
                'status' => 'OK',
                'rows' => [[
                    'elements' => [[
                        'status' => 'OK',
                        'distance' => ['value' => 16093],
                        'duration' => ['value' => 900],
                        'duration_in_traffic' => ['value' => 1020],
                    ]],
                ]],
            ]),
            'api.x.ai/*' => fn () => throw new \Illuminate\Http\Client\ConnectionException('Connection timed out'),
        ]);

        $response = $this->postJson('/api/pricing/ai-quote', $this->baseRequest());

        $response->assertOk();
        $data = $response->json();

        $this->assertTrue($data['success']);
        $this->assertTrue($data['fallback']);
        $this->assertEquals($data['deterministic_fare_cents'], $data['final_fare_cents']);
        $this->assertEquals('nearest', $data['dispatch_bias']);
    }
}
