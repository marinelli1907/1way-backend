<?php

namespace Tests\Feature;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Modules\UserManagement\Entities\User;
use Modules\UserManagement\Entities\UserLevel;
use Tests\TestCase;

class AdminDriverCreateUpdateTest extends TestCase
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
            ['email' => 'admin-driver-test@test.local'],
            [
                'first_name'  => 'Admin',
                'last_name'   => 'Test',
                'full_name'   => 'Admin Test',
                'phone'       => '15555550000',
                'password'    => Hash::make('password'),
                'user_type'   => 'super-admin',
                'is_active'   => 1,
            ]
        );
    }

    private function makeDriverPayload(array $overrides = []): array
    {
        $phone = '1555555' . rand(1000, 9999);
        return array_merge([
            'first_name'            => 'Feature',
            'last_name'             => 'Driver',
            'email'                 => 'driver-' . $phone . '@test.local',
            'phone'                 => $phone,
            'password'              => 'TestPassword123!',
            'confirm_password'      => 'TestPassword123!',
            'identification_type'   => 'passport',
            'identification_number' => 'ID' . $phone,
            'driver_split_percent'  => 80,
            'profile_image'         => UploadedFile::fake()->image('profile.jpg', 100, 100),
            'identity_images'       => [UploadedFile::fake()->image('id1.jpg', 100, 100)],
            'other_documents'       => [UploadedFile::fake()->create('doc.pdf', 100)],
        ], $overrides);
    }

    public function test_admin_driver_create_page_loads(): void
    {
        $response = $this->actingAs($this->admin, 'web')
            ->get(route('admin.driver.create'));

        $response->assertStatus(200);
        $response->assertSee('Add Driver', false);
    }

    public function test_admin_driver_create_persists_and_hashes_password(): void
    {
        $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class);

        $payload = $this->makeDriverPayload();
        $phone = $payload['phone'];
        $password = $payload['password'];

        $response = $this->actingAs($this->admin, 'web')
            ->post(route('admin.driver.store'), $payload);

        $response->assertRedirect(route('admin.driver.index'));
        $response->assertSessionMissing('error');

        $driver = User::where('phone', $phone)->where('user_type', DRIVER)->first();
        $this->assertNotNull($driver, 'Driver should exist in DB after create');
        $this->assertTrue(Hash::check($password, $driver->password), 'Password must be hashed');
        $this->assertSame('Feature', $driver->first_name);
        $this->assertSame('Driver', $driver->last_name);
        $this->assertSame('Feature Driver', $driver->full_name);
        $this->assertEquals(1, $driver->is_active);
    }

    public function test_admin_driver_create_persists_driver_split_percent(): void
    {
        $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class);

        $payload = $this->makeDriverPayload(['driver_split_percent' => 75]);
        $phone = $payload['phone'];

        $response = $this->actingAs($this->admin, 'web')
            ->post(route('admin.driver.store'), $payload);

        $response->assertRedirect(route('admin.driver.index'));

        $driver = User::where('phone', $phone)->where('user_type', DRIVER)->first();
        $this->assertNotNull($driver);
        $this->assertEquals(75, $driver->driver_split_percent);
    }

    public function test_admin_driver_create_creates_related_records(): void
    {
        $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class);

        $payload = $this->makeDriverPayload();
        $phone = $payload['phone'];

        $response = $this->actingAs($this->admin, 'web')
            ->post(route('admin.driver.store'), $payload);

        $response->assertRedirect(route('admin.driver.index'));

        $driver = User::where('phone', $phone)->where('user_type', DRIVER)->first();
        $this->assertNotNull($driver);

        $this->assertNotNull($driver->user_level_id, 'Driver must have a user_level_id');
        $this->assertNotNull($driver->driverDetails, 'DriverDetails record must be created');
        $this->assertNotNull($driver->userAccount, 'UserAccount record must be created');
    }

    public function test_admin_driver_create_does_not_500_on_failure(): void
    {
        $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class);

        $response = $this->actingAs($this->admin, 'web')
            ->post(route('admin.driver.store'), [
                'first_name' => '',
                'last_name' => '',
                'email' => 'not-an-email',
                'phone' => '1',
            ]);

        $this->assertNotEquals(500, $response->getStatusCode(), 'Must never 500 — show validation errors instead');
        $response->assertSessionHasErrors();
    }

    public function test_admin_driver_update_persists(): void
    {
        $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class);

        $phone = '1555555' . rand(2000, 9999);
        $driver = User::create([
            'user_level_id'         => $this->driverLevel->id,
            'first_name'            => 'Initial',
            'last_name'             => 'Driver',
            'full_name'             => 'Initial Driver',
            'email'                 => 'driver-update-' . $phone . '@test.local',
            'phone'                 => $phone,
            'identification_type'   => 'passport',
            'identification_number' => 'ID' . $phone,
            'password'              => Hash::make('password'),
            'user_type'             => DRIVER,
            'is_active'             => 1,
        ]);

        $response = $this->actingAs($this->admin, 'web')
            ->put(route('admin.driver.update', ['id' => $driver->id]), [
                'first_name'            => 'UpdatedFirst',
                'last_name'             => 'UpdatedLast',
                'email'                 => $driver->email,
                'phone'                 => $driver->phone,
                'identification_type'   => 'passport',
                'identification_number' => $driver->identification_number,
                'driver_split_percent'  => 90,
            ]);

        $response->assertRedirect();
        $driver->refresh();
        $this->assertSame('UpdatedFirst', $driver->first_name);
        $this->assertSame('UpdatedLast', $driver->last_name);
        $this->assertEquals(90, $driver->driver_split_percent);
    }

    public function test_admin_driver_create_with_no_password_still_works(): void
    {
        $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class);

        $payload = $this->makeDriverPayload();
        unset($payload['password'], $payload['confirm_password']);
        $phone = $payload['phone'];

        $response = $this->actingAs($this->admin, 'web')
            ->post(route('admin.driver.store'), $payload);

        $response->assertRedirect(route('admin.driver.index'));

        $driver = User::where('phone', $phone)->where('user_type', DRIVER)->first();
        $this->assertNotNull($driver);
    }
}
