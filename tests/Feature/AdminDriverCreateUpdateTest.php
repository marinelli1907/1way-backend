<?php

namespace Tests\Feature;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Modules\UserManagement\Entities\User;
use Modules\UserManagement\Entities\UserLevel;
use Tests\TestCase;

class AdminDriverCreateUpdateTest extends TestCase
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
            ['email' => 'admin-driver-test@test.local'],
            [
                'first_name' => 'Admin',
                'last_name' => 'Test',
                'full_name' => 'Admin Test',
                'phone' => '15555550000',
                'password' => Hash::make('password'),
                'user_type' => 'super-admin',
                'is_active' => 1,
            ]
        );
    }

    public function test_admin_driver_create_persists_and_hashes_password(): void
    {
        $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class);

        $phone = '1555555' . rand(1000, 9999);
        $password = 'TestPassword123!';
        $payload = [
            'first_name' => 'Feature',
            'last_name' => 'Driver',
            'email' => 'driver-' . $phone . '@test.local',
            'phone' => $phone,
            'password' => $password,
            'confirm_password' => $password,
            'identification_type' => 'passport',
            'identification_number' => 'ID' . $phone,
            'profile_image' => UploadedFile::fake()->image('profile.jpg', 100, 100),
            'identity_images' => [UploadedFile::fake()->image('id1.jpg', 100, 100)],
            'other_documents' => [UploadedFile::fake()->create('doc.pdf', 100)],
        ];

        $response = $this->actingAs($this->admin, 'web')
            ->post(route('admin.driver.store'), $payload);

        $response->assertRedirect(route('admin.driver.index'));

        $driver = User::where('phone', $phone)->where('user_type', DRIVER)->first();
        $this->assertNotNull($driver, 'Driver should exist in DB after create');
        $this->assertTrue(Hash::check($password, $driver->password), 'Password must be hashed');
        $this->assertSame('Feature', $driver->first_name);
        $this->assertSame('Driver', $driver->last_name);
    }

    public function test_admin_driver_update_persists(): void
    {
        $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class);

        $driver = User::where('user_type', DRIVER)
            ->whereNotNull('email')
            ->first();

        if (! $driver) {
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
        }

        $newFirstName = 'UpdatedFirst';
        $newLastName = 'UpdatedLast';
        $payload = [
            'first_name' => $newFirstName,
            'last_name' => $newLastName,
            'email' => $driver->email,
            'phone' => $driver->phone,
            'identification_type' => $driver->identification_type ?? 'passport',
            'identification_number' => $driver->identification_number ?? '123',
        ];

        $response = $this->actingAs($this->admin, 'web')
            ->put(route('admin.driver.update', ['id' => $driver->id]), $payload);

        $response->assertRedirect();
        $driver->refresh();
        $this->assertSame($newFirstName, $driver->first_name);
        $this->assertSame($newLastName, $driver->last_name);
    }

    public function test_admin_driver_create_validation_rejects_bad_payload(): void
    {
        $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class);

        $response = $this->actingAs($this->admin, 'web')
            ->post(route('admin.driver.store'), [
                'first_name' => '',
                'last_name' => '',
                'email' => 'not-an-email',
                'phone' => '1',
            ]);

        $response->assertSessionHasErrors();
    }
}
