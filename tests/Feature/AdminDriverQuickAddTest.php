<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Hash;
use Modules\UserManagement\Entities\User;
use Modules\UserManagement\Entities\UserLevel;
use Tests\TestCase;

class AdminDriverQuickAddTest extends TestCase
{
    /** @var User */
    private $admin;

    /** @var UserLevel */
    private $driverLevel;

    protected function setUp(): void
    {
        parent::setUp();
        $this->driverLevel = UserLevel::where('user_type', DRIVER)->orderBy('sequence')->first();
        if (! $this->driverLevel) {
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
                'first_name'   => 'Admin',
                'last_name'    => 'QuickAdd',
                'full_name'    => 'Admin QuickAdd',
                'phone'        => '15555559999',
                'password'     => Hash::make('password'),
                'user_type'    => 'super-admin',
                'is_active'    => 1,
            ]
        );
    }

    public function test_quick_add_driver_persists_and_redirects(): void
    {
        $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class);

        $phone = '1555555' . rand(10000, 99999);
        $email = 'quickadd-' . $phone . '@test.local';
        $payload = [
            'first_name'           => 'Quick',
            'last_name'            => 'AddDriver',
            'email'                => $email,
            'phone'                => $phone,
            'city_region'          => 'Test City',
            'driver_split_percent' => '80',
        ];

        $response = $this->actingAs($this->admin, 'web')
            ->post(route('admin.driver.quick-add.store'), $payload);

        $response->assertRedirect();
        $redirect = $response->headers->get('Location');
        $this->assertStringContainsString('quick-add', $redirect);

        $driver = User::where('phone', $phone)->where('user_type', DRIVER)->first();
        $this->assertNotNull($driver, 'Driver should exist in DB after Quick Add');
        $this->assertNotEquals($payload['email'], $driver->password, 'Password must not be stored plain');
        $this->assertGreaterThanOrEqual(50, strlen($driver->password), 'Password must be hashed (bcrypt length)');
        $this->assertSame('Quick', $driver->first_name);
        $this->assertSame('AddDriver', $driver->last_name);
        $this->assertNotNull($driver->driverDetails, 'Driver should have driver_details row');
        $this->assertNotNull($driver->userAccount, 'Driver should have user_account row');
    }

    public function test_quick_add_validation_errors_returned_for_duplicate_email(): void
    {
        $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class);

        $existing = User::where('user_type', DRIVER)->first();
        if (! $existing) {
            $this->markTestSkipped('No existing driver for duplicate test.');
        }

        $payload = [
            'first_name' => 'Duplicate',
            'last_name'  => 'Email',
            'email'     => $existing->email,
            'phone'     => '15555551111',
        ];

        $response = $this->actingAs($this->admin, 'web')
            ->post(route('admin.driver.quick-add.store'), $payload);

        $response->assertSessionHasErrors('email');
    }

    public function test_quick_add_validation_errors_returned_for_duplicate_phone(): void
    {
        $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class);

        $existing = User::where('user_type', DRIVER)->first();
        if (! $existing) {
            $this->markTestSkipped('No existing driver for duplicate test.');
        }

        $payload = [
            'first_name' => 'Duplicate',
            'last_name'  => 'Phone',
            'email'     => 'unique-' . $existing->phone . '@test.local',
            'phone'     => $existing->phone,
        ];

        $response = $this->actingAs($this->admin, 'web')
            ->post(route('admin.driver.quick-add.store'), $payload);

        $response->assertSessionHasErrors('phone');
    }

    public function test_quick_add_bootstraps_driver_level_when_missing(): void
    {
        $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class);

        // Remove any existing driver levels to force bootstrap path
        UserLevel::where('user_type', DRIVER)->delete();

        $phone = '1555555' . rand(10000, 99999);
        $email = 'quickadd-bootstrap-' . $phone . '@test.local';
        $payload = [
            'first_name'           => 'Bootstrap',
            'last_name'            => 'AddDriver',
            'email'                => $email,
            'phone'                => $phone,
            'city_region'          => 'Bootstrap City',
            'driver_split_percent' => '80',
        ];

        $response = $this->actingAs($this->admin, 'web')
            ->post(route('admin.driver.quick-add.store'), $payload);

        $response->assertRedirect();

        $this->assertGreaterThan(
            0,
            UserLevel::where('user_type', DRIVER)->count(),
            'drivers:ensure-levels should have created at least one driver level'
        );

        $driver = User::where('phone', $phone)->where('user_type', DRIVER)->first();
        $this->assertNotNull($driver, 'Driver should exist in DB after Quick Add with bootstrap');
    }
}
