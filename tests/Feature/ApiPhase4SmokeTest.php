<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Laravel\Passport\Passport;
use Modules\UserManagement\Entities\User;
use Tests\TestCase;

class ApiPhase4SmokeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('passport:keys', ['--force' => true]);
        $this->artisan('passport:client', ['--personal' => true, '--name' => 'Test Personal Access Client']);
    }


    /**
     * POST /api/customer/auth/login accepts email and formatted phone.
     */
    public function test_customer_login_accepts_email_or_phone_variants(): void
    {
        $user = User::create([
            'first_name' => 'Test',
            'last_name' => 'Customer',
            'phone' => '+1 (555) 123-4567',
            'email' => 'customer-variant@test.example',
            'password' => Hash::make('password'),
            'user_type' => 'customer',
            'is_active' => 1,
        ]);

        $byEmail = $this->postJson('/api/customer/auth/login', [
            'phone_or_email' => $user->email,
            'password' => 'password',
        ]);
        $byEmail->assertStatus(200);

        $byPhoneDigits = $this->postJson('/api/customer/auth/login', [
            'phone_or_email' => '15551234567',
            'password' => 'password',
        ]);
        $byPhoneDigits->assertStatus(200);
    }

    /**
     * POST /api/customer/auth/login with valid credentials returns 200 and token shape.
     */
    public function test_customer_login_returns_200_and_token_shape(): void
    {
        $user = User::create([
            'first_name' => 'Test',
            'last_name' => 'Customer',
            'phone' => '+15551234567',
            'email' => 'customer@test.example',
            'password' => Hash::make('password'),
            'user_type' => 'customer',
            'is_active' => 1,
        ]);

        $response = $this->postJson('/api/customer/auth/login', [
            'phone_or_email' => $user->phone,
            'password' => 'password',
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'response_code',
            'message',
            'data' => [
                'token',
                'is_active',
                'is_phone_verified',
                'is_profile_verified',
            ],
        ]);
    }

    /**
     * POST /api/driver/auth/login with valid credentials returns 200 and token shape.
     * Backend looks up driver by phone (checkClientRoute uses phone for driver).
     */
    public function test_driver_login_returns_200_and_token_shape(): void
    {
        $user = User::create([
            'first_name' => 'Test',
            'last_name' => 'Driver',
            'phone' => '+15559876543',
            'email' => 'driver@test.example',
            'password' => Hash::make('password'),
            'user_type' => 'driver',
            'is_active' => 1,
        ]);

        $response = $this->postJson('/api/driver/auth/login', [
            'phone_or_email' => $user->phone,
            'password' => 'password',
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'response_code',
            'message',
            'data' => [
                'token',
                'is_active',
                'is_phone_verified',
                'is_profile_verified',
            ],
        ]);
    }

    /**
     * GET /api/user/profile without token returns 401.
     */
    public function test_user_profile_requires_auth(): void
    {
        $response = $this->getJson('/api/user/profile');
        $response->assertStatus(401);
    }

    /**
     * GET /api/user/profile with valid token returns 200.
     */
    public function test_user_profile_works_with_token(): void
    {
        $user = User::create([
            'first_name' => 'Profile',
            'last_name' => 'User',
            'phone' => '+15551111111',
            'email' => 'profile@test.example',
            'password' => Hash::make('password'),
            'user_type' => 'customer',
            'is_active' => 1,
        ]);

        Passport::actingAs($user);

        $response = $this->getJson('/api/user/profile');

        $response->assertStatus(200);
    }

    /**
     * GET /api/driver/earnings without token returns 401.
     */
    public function test_driver_earnings_requires_auth(): void
    {
        $response = $this->getJson('/api/driver/earnings');
        $response->assertStatus(401);
    }

    /**
     * GET /api/driver/earnings with auth returns 200 and safe defaults.
     */
    public function test_driver_earnings_returns_200_with_auth(): void
    {
        $user = User::create([
            'first_name' => 'Earnings',
            'last_name' => 'Driver',
            'phone' => '+15552222222',
            'email' => 'earnings@test.example',
            'password' => Hash::make('password'),
            'user_type' => 'driver',
            'is_active' => 1,
        ]);

        Passport::actingAs($user);

        $response = $this->getJson('/api/driver/earnings');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'response_code',
            'message',
            'data' => [
                'gross_earnings',
                'app_share',
                'driver_share',
                'expenses_total',
                'miles_total',
            ],
        ]);
    }

    /**
     * GET /api/driver/expenses without token returns 401.
     */
    public function test_driver_expenses_requires_auth(): void
    {
        $response = $this->getJson('/api/driver/expenses');
        $response->assertStatus(401);
    }

    /**
     * GET /api/driver/expenses with auth returns 200 and safe defaults.
     */
    public function test_driver_expenses_returns_200_with_auth(): void
    {
        $user = User::create([
            'first_name' => 'Expenses',
            'last_name' => 'Driver',
            'phone' => '+15553333333',
            'email' => 'expenses@test.example',
            'password' => Hash::make('password'),
            'user_type' => 'driver',
            'is_active' => 1,
        ]);

        Passport::actingAs($user);

        $response = $this->getJson('/api/driver/expenses');

        $response->assertStatus(200);
        $response->assertJsonStructure(['response_code', 'message', 'data']);
    }
}
