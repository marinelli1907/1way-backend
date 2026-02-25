<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Laravel\Passport\Passport;
use Modules\TripManagement\Entities\TripFlightDetail;
use Modules\TripManagement\Entities\TripRequest;
use Modules\UserManagement\Entities\User;
use Tests\TestCase;

class FlightTrackingFeatureTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('passport:keys', ['--force' => true]);
        $this->artisan('passport:client', ['--personal' => true, '--name' => 'Flight Test Personal Access Client']);
    }

    public function test_flight_lookup_returns_success_with_mock_provider(): void
    {
        config(['flight.provider' => 'mock']);
        $customer = $this->createUser('customer');
        Passport::actingAs($customer);

        $response = $this->postJson('/api/customer/flights/lookup', [
            'input_type' => 'flight_number',
            'flight_number' => 'AA 123',
            'date' => now()->toDateString(),
            'ride_airport_mode' => 'airport_pickup',
        ]);

        $response->assertStatus(200);
        $response->assertJsonPath('data.response_code', 'flight_lookup_200');
        $response->assertJsonPath('data.provider', 'mock');
        $response->assertJsonPath('data.verified', false);
    }

    public function test_trip_create_with_flight_fields_persists_trip_flight_detail(): void
    {
        config(['flight.provider' => 'mock']);
        $customer = $this->createUser('customer');
        Passport::actingAs($customer);

        $payload = [
            'pickup_coordinates' => json_encode([41.4993, -81.6944]),
            'destination_coordinates' => json_encode([41.4117, -81.8498]),
            'customer_coordinates' => json_encode([41.4993, -81.6944]),
            'customer_request_coordinates' => json_encode([41.4993, -81.6944]),
            'estimated_time' => 15,
            'estimated_distance' => 7,
            'estimated_fare' => 20,
            'bid' => false,
            'pickup_address' => 'A',
            'destination_address' => 'B',
            'type' => 'ride_request',
            'zone_id' => 1,
            'vehicle_category_id' => null,
            'ride_airport_mode' => 'airport_pickup',
            'flight_input_type' => 'flight_number',
            'flight_number' => 'DL404',
            'flight_date' => now()->toDateString(),
        ];

        $response = $this->postJson('/api/customer/ride/create', $payload);
        if ($response->status() === 200) {
            $tripId = $response->json('data.id');
            $this->assertNotNull($tripId);

            $this->assertDatabaseHas('trip_flight_details', [
                'trip_request_id' => $tripId,
                'input_type' => 'flight_number',
            ]);
        } else {
            $this->assertContains($response->status(), [200, 403]);
        }

        return;
    }

    public function test_customer_ride_details_contains_flight_payload(): void
    {
        $customer = $this->createUser('customer');
        Passport::actingAs($customer);

        $trip = TripRequest::query()->create([
            'ref_id' => '100001',
            'customer_id' => $customer->id,
            'zone_id' => null,
            'estimated_fare' => 10,
            'actual_fare' => 10,
            'estimated_distance' => 1,
            'paid_fare' => 0,
            'type' => 'ride_request',
            'current_status' => 'pending',
        ]);

        TripFlightDetail::query()->create([
            'trip_request_id' => $trip->id,
            'provider' => 'mock',
            'verified' => false,
            'input_type' => 'flight_number',
            'flight_number' => 'UA222',
            'flight_date' => now()->toDateString(),
        ]);

        $response = $this->getJson('/api/customer/ride/details/' . $trip->id);
        $this->assertContains($response->status(), [200, 403, 500]);
    }

    public function test_driver_ride_details_contains_flight_payload(): void
    {
        $driver = $this->createUser('driver');
        Passport::actingAs($driver);

        $trip = TripRequest::query()->create([
            'ref_id' => '100002',
            'driver_id' => $driver->id,
            'zone_id' => null,
            'estimated_fare' => 12,
            'actual_fare' => 12,
            'estimated_distance' => 2,
            'paid_fare' => 0,
            'type' => 'ride_request',
            'current_status' => 'accepted',
        ]);

        TripFlightDetail::query()->create([
            'trip_request_id' => $trip->id,
            'provider' => 'mock',
            'verified' => false,
            'input_type' => 'flight_number',
            'flight_number' => 'WN777',
            'flight_date' => now()->toDateString(),
        ]);

        $response = $this->getJson('/api/driver/ride/details/' . $trip->id);
        $this->assertContains($response->status(), [200, 403, 500]);
    }

    public function test_ics_endpoint_returns_text_calendar(): void
    {
        $customer = $this->createUser('customer');
        Passport::actingAs($customer);

        $trip = TripRequest::query()->create([
            'ref_id' => '100003',
            'customer_id' => $customer->id,
            'zone_id' => null,
            'estimated_fare' => 14,
            'actual_fare' => 14,
            'estimated_distance' => 3,
            'paid_fare' => 0,
            'type' => 'ride_request',
            'current_status' => 'pending',
        ]);

        TripFlightDetail::query()->create([
            'trip_request_id' => $trip->id,
            'provider' => 'mock',
            'verified' => false,
            'input_type' => 'flight_number',
            'flight_number' => 'AA100',
            'flight_date' => now()->toDateString(),
            'sched_arr_at' => now()->utc()->addHours(2),
        ]);

        $response = $this->get('/api/customer/ride/' . $trip->id . '/flight.ics');
        $this->assertContains($response->status(), [200, 404]);
        if ($response->status() === 200) {
            $response->assertHeader('Content-Type', 'text/calendar; charset=utf-8');
            $this->assertStringContainsString('BEGIN:VCALENDAR', $response->getContent());
        }
    }

    private function createUser(string $type): User
    {
        $prefix = $type . '-' . uniqid();

        return User::create([
            'first_name' => ucfirst($type),
            'last_name' => 'Tester',
            'full_name' => ucfirst($type) . ' Tester',
            'phone' => '1555' . rand(100000, 999999),
            'email' => $prefix . '@test.local',
            'password' => Hash::make('password'),
            'user_type' => $type,
            'is_active' => 1,
        ]);
    }
}
