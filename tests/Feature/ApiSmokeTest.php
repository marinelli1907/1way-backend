<?php

namespace Tests\Feature;

use Tests\TestCase;

class ApiSmokeTest extends TestCase
{
    /**
     * GET /api/user/profile without token must return 401.
     */
    public function test_profile_requires_auth(): void
    {
        $response = $this->getJson('/api/user/profile');
        $response->assertStatus(401);
    }

    /**
     * POST /api/customer/auth/login with empty body returns validation error (4xx).
     */
    public function test_customer_login_validates_input(): void
    {
        $response = $this->postJson('/api/customer/auth/login', []);
        $response->assertStatus(422);
        $response->assertJsonStructure(['message']);
    }

    /**
     * POST /api/driver/auth/login with empty body returns validation error (4xx).
     */
    public function test_driver_login_validates_input(): void
    {
        $response = $this->postJson('/api/driver/auth/login', []);
        $response->assertStatus(422);
        $response->assertJsonStructure(['message']);
    }
}
