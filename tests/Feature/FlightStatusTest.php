<?php

namespace Tests\Feature;

use App\Services\Flights\AmadeusClient;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class FlightStatusTest extends TestCase
{
    public function test_validation_rejects_missing_flight(): void
    {
        $response = $this->getJson('/api/flights/status');

        $response->assertStatus(422)
            ->assertJsonPath('errors.flight.0', 'The flight parameter is required (e.g. AA123).');
    }

    public function test_validation_rejects_invalid_format(): void
    {
        $response = $this->getJson('/api/flights/status?flight=123AA');

        $response->assertStatus(422)
            ->assertJsonPath('errors.flight.0', 'Flight must be 2 letters + 1-4 digits (e.g. AA123).');
    }

    public function test_validation_rejects_bad_date_format(): void
    {
        $response = $this->getJson('/api/flights/status?flight=AA123&date=03-01-2026');

        $response->assertStatus(422)
            ->assertJsonValidationErrors('date');
    }

    public function test_happy_path_returns_normalized_json(): void
    {
        $fakeAmadeusResponse = [
            'data' => [
                [
                    'flightPoints' => [
                        [
                            'iataCode'  => 'CLE',
                            'departure' => [
                                'timings' => [
                                    ['qualifier' => 'ST', 'value' => '2026-03-03T08:30'],
                                ],
                            ],
                        ],
                        [
                            'iataCode' => 'DFW',
                            'arrival'  => [
                                'timings' => [
                                    ['qualifier' => 'ST', 'value' => '2026-03-03T11:15'],
                                ],
                            ],
                        ],
                    ],
                    'legs' => [
                        [
                            'aircraftEquipment' => ['aircraftType' => '738'],
                            'scheduledLegDuration' => 'PT2H45M',
                        ],
                    ],
                    'segments' => [],
                ],
            ],
        ];

        Http::fake([
            '*/v1/security/oauth2/token' => Http::response([
                'access_token' => 'fake-token-123',
                'token_type'   => 'Bearer',
                'expires_in'   => 1799,
            ]),
            '*/v2/schedule/flights*' => Http::response($fakeAmadeusResponse),
        ]);

        $response = $this->getJson('/api/flights/status?flight=AA123&date=2026-03-03');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'provider', 'flight', 'carrier', 'flight_number', 'date',
                'status', 'delay_minutes',
                'departure' => ['iata', 'scheduled', 'estimated', 'actual'],
                'arrival'   => ['iata', 'scheduled', 'estimated', 'actual'],
                'raw_provider_status',
            ])
            ->assertJsonPath('provider', 'amadeus')
            ->assertJsonPath('flight', 'AA123')
            ->assertJsonPath('carrier', 'AA')
            ->assertJsonPath('flight_number', '123')
            ->assertJsonPath('departure.iata', 'CLE')
            ->assertJsonPath('arrival.iata', 'DFW')
            ->assertJsonPath('status', 'on_time');
    }

    public function test_returns_404_when_no_flight_data(): void
    {
        Http::fake([
            '*/v1/security/oauth2/token' => Http::response([
                'access_token' => 'fake-token-123',
                'token_type'   => 'Bearer',
                'expires_in'   => 1799,
            ]),
            '*/v2/schedule/flights*' => Http::response(['data' => []]),
        ]);

        $response = $this->getJson('/api/flights/status?flight=ZZ999&date=2026-03-03');

        $response->assertStatus(404)
            ->assertJsonPath('message', 'Flight not found');
    }

    public function test_returns_502_on_upstream_error(): void
    {
        Http::fake([
            '*/v1/security/oauth2/token' => Http::response([
                'access_token' => 'fake-token-123',
                'token_type'   => 'Bearer',
                'expires_in'   => 1799,
            ]),
            '*/v2/schedule/flights*' => Http::response('Server Error', 500),
        ]);

        $response = $this->getJson('/api/flights/status?flight=AA123&date=2026-03-03');

        $response->assertStatus(502)
            ->assertJsonPath('message', 'Upstream flight data provider error. Please try again later.');
    }
}
