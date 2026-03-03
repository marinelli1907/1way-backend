<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Hash;
use Modules\UserManagement\Entities\User;
use Modules\ZoneManagement\Entities\ServiceZone;
use Tests\TestCase;

class ZoneDriverAssignmentTest extends TestCase
{
    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::firstOrCreate(
            ['email' => 'admin-zone-driver-test@test.local'],
            [
                'first_name' => 'Admin',
                'last_name'  => 'ZoneTest',
                'full_name'  => 'Admin ZoneTest',
                'phone'      => '15559990000',
                'password'   => Hash::make('password'),
                'user_type'  => 'super-admin',
                'is_active'  => 1,
            ]
        );
    }

    private function createTestZone(): ServiceZone
    {
        return ServiceZone::create([
            'name'         => 'ZD Test Zone ' . uniqid(),
            'country_code' => 'US',
            'state_code'   => 'MT',
            'source'       => 'manual',
            'is_active'    => true,
            'priority'     => 0,
            'boundary'     => [
                'type' => 'MultiPolygon',
                'coordinates' => [[[[-112, 47], [-111, 47], [-111, 48], [-112, 48], [-112, 47]]]],
            ],
        ]);
    }

    private function createDriverUser(string $suffix = ''): User
    {
        $phone = '1555' . str_pad(mt_rand(0, 99999999), 8, '0', STR_PAD_LEFT);
        return User::create([
            'first_name' => 'Driver' . $suffix,
            'last_name'  => 'Test' . $suffix,
            'full_name'  => 'Driver' . $suffix . ' Test' . $suffix,
            'email'      => "driver{$suffix}-{$phone}@test.local",
            'phone'      => $phone,
            'password'   => Hash::make('password'),
            'user_type'  => DRIVER,
            'is_active'  => 1,
        ]);
    }

    // ── Test 1: Zone edit page returns 200 ────────────────────────────

    public function test_zone_edit_page_returns_200(): void
    {
        $zone = $this->createTestZone();

        $response = $this->actingAs($this->admin, 'web')
            ->get(route('admin.service-zone.edit', $zone->id));

        $response->assertStatus(200);
        $response->assertSee($zone->name, false);

        $zone->delete();
    }

    // ── Test 2: Assign drivers via sync endpoint ─────────────────────

    public function test_assign_drivers_to_zone(): void
    {
        $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class);

        $zone = $this->createTestZone();
        $d1 = $this->createDriverUser('A');
        $d2 = $this->createDriverUser('B');

        $response = $this->actingAs($this->admin, 'web')
            ->putJson(route('admin.service-zone.drivers.sync', $zone->id), [
                'driver_ids' => [$d1->id, $d2->id],
            ]);

        $response->assertOk();
        $response->assertJson(['success' => true]);

        $this->assertDatabaseHas('service_zone_drivers', [
            'service_zone_id' => $zone->id,
            'driver_user_id'  => $d1->id,
        ]);
        $this->assertDatabaseHas('service_zone_drivers', [
            'service_zone_id' => $zone->id,
            'driver_user_id'  => $d2->id,
        ]);

        // Remove one driver
        $response = $this->actingAs($this->admin, 'web')
            ->putJson(route('admin.service-zone.drivers.sync', $zone->id), [
                'driver_ids' => [$d1->id],
            ]);

        $response->assertOk();
        $this->assertDatabaseMissing('service_zone_drivers', [
            'service_zone_id' => $zone->id,
            'driver_user_id'  => $d2->id,
        ]);

        $zone->delete();
        $d1->forceDelete();
        $d2->forceDelete();
    }

    // ── Test 3: Search returns only drivers ──────────────────────────

    public function test_driver_search_returns_only_drivers(): void
    {
        $zone = $this->createTestZone();
        $driver = $this->createDriverUser('Search');

        // Create a customer user — should NOT appear in search
        $custPhone = '1556' . str_pad(mt_rand(0, 99999999), 8, '0', STR_PAD_LEFT);
        $customer = User::create([
            'first_name' => 'CustomerSearch',
            'last_name'  => 'Test',
            'full_name'  => 'CustomerSearch Test',
            'email'      => 'custsearch-' . uniqid() . '@test.local',
            'phone'      => $custPhone,
            'password'   => Hash::make('password'),
            'user_type'  => 'customer',
            'is_active'  => 1,
        ]);

        $response = $this->actingAs($this->admin, 'web')
            ->getJson(route('admin.service-zone.drivers.search', ['id' => $zone->id, 'q' => 'Search']));

        $response->assertOk();
        $ids = collect($response->json('drivers'))->pluck('id')->all();

        $this->assertContains((string) $driver->id, $ids);
        $this->assertNotContains((string) $customer->id, $ids);

        $zone->delete();
        $driver->forceDelete();
        $customer->forceDelete();
    }

    // ── Test 4: Reject non-driver IDs ───────────────────────────────

    public function test_rejects_non_driver_ids(): void
    {
        $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class);

        $zone = $this->createTestZone();

        // Use the admin's own UUID (a super-admin, not a driver)
        $response = $this->actingAs($this->admin, 'web')
            ->putJson(route('admin.service-zone.drivers.sync', $zone->id), [
                'driver_ids' => [$this->admin->id],
            ]);

        $response->assertStatus(422);
        $response->assertJson(['success' => false]);

        $zone->delete();
    }

    // ── Test 5: Support role cannot access zone assignment ───────────

    public function test_support_role_cannot_access_zones(): void
    {
        $support = User::create([
            'first_name' => 'Support',
            'last_name'  => 'User',
            'full_name'  => 'Support User',
            'email'      => 'support-' . uniqid() . '@test.local',
            'phone'      => '15557770' . rand(100, 999),
            'password'   => Hash::make('password'),
            'user_type'  => 'admin-employee',
            'is_active'  => 1,
        ]);

        $zone = $this->createTestZone();

        // admin-employee without role/moduleAccess for service_zone_management should be denied
        $response = $this->actingAs($support, 'web')
            ->get(route('admin.service-zone.index'));

        $this->assertContains($response->status(), [403, 302]);

        $zone->delete();
        $support->forceDelete();
    }
}
