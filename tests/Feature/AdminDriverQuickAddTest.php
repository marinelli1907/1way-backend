<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Hash;
use Modules\UserManagement\Entities\DriverInviteToken;
use Modules\UserManagement\Entities\DriverOnboardingStatus;
use Modules\UserManagement\Entities\User;
use Modules\UserManagement\Entities\UserLevel;
use Tests\TestCase;

class AdminDriverQuickAddTest extends TestCase
{
    private User $admin;
    private UserLevel $driverLevel;

    protected function setUp(): void
    {
        parent::setUp();

        $this->driverLevel = UserLevel::where('user_type', DRIVER)->orderBy('sequence')->first();
        if (!$this->driverLevel) {
            $this->driverLevel = UserLevel::create([
                'sequence'              => 1,
                'name'                  => 'Test Driver Level',
                'reward_type'           => 'no_rewards',
                'reward_amount'         => null,
                'image'                 => null,
                'targeted_ride'         => 0,
                'targeted_ride_point'   => 0,
                'targeted_amount'       => 0,
                'targeted_amount_point' => 0,
                'targeted_cancel'       => 0,
                'targeted_cancel_point' => 0,
                'targeted_review'       => 0,
                'targeted_review_point' => 0,
                'user_type'             => DRIVER,
                'is_active'             => 1,
            ]);
        }

        $this->admin = User::firstOrCreate(
            ['email' => 'admin-quickadd-test@test.local'],
            [
                'first_name' => 'Admin',
                'last_name'  => 'QATest',
                'full_name'  => 'Admin QATest',
                'phone'      => '15550009999',
                'password'   => Hash::make('password'),
                'user_type'  => 'super-admin',
                'is_active'  => 1,
            ]
        );
    }

    private function validPayload(array $overrides = []): array
    {
        $uniq = rand(10000, 99999);
        return array_merge([
            'first_name'           => 'QATest',
            'last_name'            => 'Driver',
            'email'                => "qa-driver-{$uniq}@test.local",
            'phone'                => "15559990{$uniq}",
            'city_region'          => 'Cleveland, OH',
            'driver_split_percent' => 80,
        ], $overrides);
    }

    public function test_quick_add_page_loads(): void
    {
        $response = $this->actingAs($this->admin, 'web')
            ->get(route('admin.driver.quick-add.create'));

        $response->assertStatus(200);
        $response->assertSee('Quick Add Driver', false);
    }

    public function test_quick_add_creates_driver_with_all_records(): void
    {
        $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class);

        $payload = $this->validPayload();

        $response = $this->actingAs($this->admin, 'web')
            ->post(route('admin.driver.quick-add.store'), $payload);

        $this->assertNotEquals(500, $response->getStatusCode(), 'Must never return 500');

        $driver = User::where('email', $payload['email'])->where('user_type', DRIVER)->first();
        $this->assertNotNull($driver, 'Driver user record must exist');
        $this->assertSame('QATest', $driver->first_name);
        $this->assertSame('Driver', $driver->last_name);
        $this->assertSame('QATest Driver', $driver->full_name);
        $this->assertEquals(80, $driver->driver_split_percent);
        $this->assertNotNull($driver->user_level_id, 'Must have a driver level assigned');
        $this->assertTrue(strlen($driver->password) > 10, 'Password must be hashed');

        // Related records
        $this->assertNotNull($driver->driverDetails, 'DriverDetails must be created');
        $this->assertNotNull($driver->userAccount, 'UserAccount must be created');

        $onboarding = DriverOnboardingStatus::where('driver_id', $driver->id)->first();
        $this->assertNotNull($onboarding, 'Onboarding status must be created');
        $this->assertFalse($onboarding->profile_complete);
        $this->assertFalse($onboarding->approved);

        $invite = DriverInviteToken::where('driver_id', $driver->id)->first();
        $this->assertNotNull($invite, 'Invite token must be created');
        $this->assertFalse($invite->used);
        $this->assertTrue($invite->expires_at->isFuture());
    }

    public function test_quick_add_result_page_loads_without_500(): void
    {
        $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class);

        $payload = $this->validPayload();

        $response = $this->actingAs($this->admin, 'web')
            ->post(route('admin.driver.quick-add.store'), $payload);

        // Should redirect to the show page — follow the redirect
        $driver = User::where('email', $payload['email'])->first();
        $this->assertNotNull($driver);

        $showResponse = $this->actingAs($this->admin, 'web')
            ->get(route('admin.driver.quick-add.show', $driver->id));

        $this->assertNotEquals(500, $showResponse->getStatusCode(), 'Result page must NOT 500');
        $showResponse->assertStatus(200);
        $showResponse->assertSee('Driver Account Created', false);
    }

    public function test_quick_add_rejects_duplicate_email_with_302(): void
    {
        $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class);

        $payload = $this->validPayload(['email' => $this->admin->email]);

        $response = $this->actingAs($this->admin, 'web')
            ->post(route('admin.driver.quick-add.store'), $payload);

        $this->assertNotEquals(500, $response->getStatusCode(), 'Duplicate email must NOT 500');
        $response->assertStatus(302);
        $response->assertSessionHasErrors('email');
    }

    public function test_quick_add_rejects_duplicate_phone_with_302(): void
    {
        $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class);

        $payload = $this->validPayload(['phone' => $this->admin->phone]);

        $response = $this->actingAs($this->admin, 'web')
            ->post(route('admin.driver.quick-add.store'), $payload);

        $this->assertNotEquals(500, $response->getStatusCode(), 'Duplicate phone must NOT 500');
        $response->assertStatus(302);
        $response->assertSessionHasErrors('phone');
    }

    public function test_quick_add_rejects_missing_required_fields(): void
    {
        $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class);

        $response = $this->actingAs($this->admin, 'web')
            ->post(route('admin.driver.quick-add.store'), []);

        $this->assertNotEquals(500, $response->getStatusCode(), 'Missing fields must NOT 500');
        $response->assertStatus(302);
        $response->assertSessionHasErrors(['first_name', 'last_name', 'email', 'phone']);
    }

    public function test_quick_add_with_custom_split_percent(): void
    {
        $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class);

        $payload = $this->validPayload(['driver_split_percent' => 65]);

        $response = $this->actingAs($this->admin, 'web')
            ->post(route('admin.driver.quick-add.store'), $payload);

        $this->assertNotEquals(500, $response->getStatusCode());

        $driver = User::where('email', $payload['email'])->first();
        $this->assertNotNull($driver);
        $this->assertEquals(65, $driver->driver_split_percent);
    }
}
