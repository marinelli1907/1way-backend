<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Http;
use Laravel\Passport\Passport;
use Modules\TripManagement\Entities\TripRequest;
use Modules\UserManagement\Entities\User;
use Tests\TestCase;

class TripRequestFlightMetaTest extends TestCase
{
    protected function createUser(string $type): User
    {
        $prefix = $type . '-' . uniqid();
        return User::create([
            'first_name' => ucfirst($type),
            'last_name' => 'Tester',
            'full_name' => ucfirst($type) . ' Tester',
            'phone' => '1555' . rand(100000, 999999),
            'email' => $prefix . '@test.local',
            'password' => \Illuminate\Support\Facades\Hash::make('password'),
            'user_type' => $type,
            'is_active' => 1,
        ]);
    }

    public function test_flight_meta_persists_on_trip_request(): void
    {
        $trip = TripRequest::query()->create([
            'ref_id' => 'TR' . rand(1000, 9999),
            'zone_id' => null,
            'estimated_fare' => 25,
            'actual_fare' => 25,
            'estimated_distance' => 10,
            'paid_fare' => 0,
            'type' => 'ride_request',
            'current_status' => 'pending',
            'flight_number' => 'AA123',
            'flight_date' => '2026-03-15',
        ]);

        $this->assertDatabaseHas('trip_requests', [
            'id' => $trip->id,
            'flight_number' => 'AA123',
            'flight_date' => '2026-03-15',
        ]);

        $trip->refresh();
        $this->assertSame('AA123', $trip->flight_number);
        $this->assertNotNull($trip->flight_date);
        $this->assertNull($trip->flight_status_cached);
        $this->assertNull($trip->flight_status_checked_at);
    }

    public function test_refresh_flight_endpoint_updates_cached_json(): void
    {
        $this->artisan('passport:keys', ['--force' => true]);
        $this->artisan('passport:client', ['--personal' => true, '--name' => 'Flight Meta Test']);

        $driver = $this->createUser('driver');
        $trip = TripRequest::query()->create([
            'ref_id' => 'TR' . rand(1000, 9999),
            'driver_id' => $driver->id,
            'zone_id' => null,
            'estimated_fare' => 30,
            'actual_fare' => 30,
            'estimated_distance' => 12,
            'paid_fare' => 0,
            'type' => 'ride_request',
            'current_status' => 'accepted',
            'flight_number' => 'BA178',
            'flight_date' => now()->toDateString(),
        ]);
        $trip->refresh();
        $this->assertSame((string) $driver->id, (string) $trip->driver_id, 'Driver should be assigned to trip');

        $fakeAmadeusResponse = [
            'data' => [
                [
                    'flightPoints' => [
                        ['iataCode' => 'JFK', 'departure' => ['timings' => [['qualifier' => 'STD', 'value' => '2026-03-03T08:05']]]],
                        ['iataCode' => 'LHR', 'arrival' => ['timings' => [['qualifier' => 'STA', 'value' => '2026-03-03T20:00Z']]]],
                    ],
                    'legs' => [['aircraftEquipment' => ['aircraftType' => '777']]],
                    'segments' => [],
                ],
            ],
        ];

        Http::fake([
            '*/v1/security/oauth2/token' => \Illuminate\Support\Facades\Http::response([
                'access_token' => 'fake-token',
                'token_type' => 'Bearer',
                'expires_in' => 1799,
            ]),
            '*/v2/schedule/flights*' => \Illuminate\Support\Facades\Http::response($fakeAmadeusResponse),
        ]);

        Passport::actingAs($driver);
        $response = $this->postJson('/api/driver/ride/' . $trip->id . '/refresh-flight');

        $response->assertStatus(200);
        $response->assertJsonPath('message', 'Flight status updated.');
        $response->assertJsonStructure(['flight_status_cached' => ['provider', 'flight', 'status', 'departure', 'arrival']]);

        $trip->refresh();
        $this->assertNotNull($trip->flight_status_cached);
        $this->assertIsArray($trip->flight_status_cached);
        $this->assertSame('amadeus', $trip->flight_status_cached['provider']);
        $this->assertSame('BA178', $trip->flight_status_cached['flight']);
        $this->assertNotNull($trip->flight_status_checked_at);
    }
}
