<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;
use Laravel\Passport\Passport;
use Modules\UserManagement\Entities\User;

class UserMeSanitizationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('passport:keys', ['--force' => true]);
        $this->artisan('passport:client', ['--personal' => true, '--name' => 'Test Personal Access Client']);
    }

    public function test_api_user_does_not_expose_password()
    {
        $user = User::create([
            'first_name' => 'Test',
            'last_name' => 'User',
            'phone' => '+15559876543',
            'email' => 'me@test.example',
            'password' => Hash::make('password'),
            'user_type' => 'customer',
            'is_active' => 1,
        ]);
        $this->assertNotNull($user, 'No user exists to test against.');

        Passport::actingAs($user);

        $res = $this->getJson('/api/user');

        $res->assertOk();
        $this->assertArrayNotHasKey('password', $res->json());
    }
}
