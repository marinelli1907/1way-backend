<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\UserManagement\Entities\User;
use Tests\TestCase;

class OperationsTabsTest extends TestCase
{
    private function adminUser(): User
    {
        return User::where('user_type', 'super-admin')->firstOrFail();
    }

    public function test_fleet_map_loads(): void
    {
        $response = $this->actingAs($this->adminUser())
            ->get(route('admin.fleet-map', ['type' => 'all-driver']));
        $response->assertStatus(200);
    }

    public function test_trip_requests_pending_loads(): void
    {
        $response = $this->actingAs($this->adminUser())
            ->get(route('admin.trip.index', ['type' => 'pending']));
        $response->assertStatus(200);
    }

    public function test_trip_requests_scheduled_loads(): void
    {
        $response = $this->actingAs($this->adminUser())
            ->get(route('admin.trip.index', ['type' => 'scheduled']));
        $response->assertStatus(200);
    }

    public function test_control_room_loads(): void
    {
        $response = $this->actingAs($this->adminUser())
            ->get(route('admin.control-room.index'));
        $response->assertStatus(200);
    }

    public function test_cancellations_loads(): void
    {
        $response = $this->actingAs($this->adminUser())
            ->get(route('admin.cancellations.index'));
        $response->assertStatus(200);
    }

    public function test_support_tickets_loads(): void
    {
        $response = $this->actingAs($this->adminUser())
            ->get(route('admin.support.tickets.index'));
        $response->assertStatus(200);
    }

    public function test_zones_loads(): void
    {
        $response = $this->actingAs($this->adminUser())
            ->get(route('admin.zone.index'));
        $response->assertStatus(200);
    }

    public function test_service_zones_loads(): void
    {
        $response = $this->actingAs($this->adminUser())
            ->get(route('admin.service-zone.index'));
        $response->assertStatus(200);
    }

    public function test_cancellations_filters_work(): void
    {
        $response = $this->actingAs($this->adminUser())
            ->get(route('admin.cancellations.index', [
                'date_from' => now()->subDays(7)->toDateString(),
                'date_to' => now()->toDateString(),
                'cancelled_by' => 'customer',
            ]));
        $response->assertStatus(200);
    }

    public function test_support_tickets_filters_work(): void
    {
        $response = $this->actingAs($this->adminUser())
            ->get(route('admin.support.tickets.index', [
                'status' => '1',
                'search' => 'test',
            ]));
        $response->assertStatus(200);
    }
}
