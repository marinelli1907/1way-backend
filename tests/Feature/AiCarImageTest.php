<?php

namespace Tests\Feature;

use App\Models\AiCarImage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Laravel\Passport\Passport;
use Modules\UserManagement\Entities\User;
use Tests\TestCase;

class AiCarImageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('passport:keys', ['--force' => true]);
        $this->artisan('passport:client', ['--personal' => true, '--name' => 'Test Personal Access Client']);
    }

    /** Unauthenticated POST returns 401 */
    public function test_generate_car_image_requires_auth(): void
    {
        $response = $this->postJson('/api/ai/generate-car-image', [
            'make' => 'Toyota',
            'model' => 'Camry',
            'color' => 'Silver',
        ]);
        $response->assertStatus(401);
    }

    /** Authenticated POST returns ok:true and job_id */
    public function test_generate_car_image_returns_job_id_with_auth(): void
    {
        $user = User::create([
            'first_name' => 'AI',
            'last_name' => 'User',
            'phone' => '+15557777777',
            'email' => 'ai@test.example',
            'password' => Hash::make('password'),
            'user_type' => 'customer',
            'is_active' => 1,
        ]);

        Passport::actingAs($user);

        $response = $this->postJson('/api/ai/generate-car-image', [
            'make' => 'Toyota',
            'model' => 'Camry',
            'color' => 'Silver',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'ok' => true,
            'status' => 'queued',
        ]);
        $response->assertJsonStructure(['job_id']);
        $this->assertNotEmpty($response->json('job_id'));
    }

    /** Status endpoint returns queued initially */
    public function test_status_returns_queued_initially(): void
    {
        $user = User::create([
            'first_name' => 'AI',
            'last_name' => 'User',
            'phone' => '+15558888888',
            'email' => 'aistatus@test.example',
            'password' => Hash::make('password'),
            'user_type' => 'customer',
            'is_active' => 1,
        ]);

        $record = AiCarImage::create([
            'id' => \Illuminate\Support\Str::uuid()->toString(),
            'user_id' => $user->getKey(),
            'make' => 'Honda',
            'model' => 'Civic',
            'color' => 'Red',
            'status' => AiCarImage::STATUS_QUEUED,
        ]);

        Passport::actingAs($user);

        $response = $this->getJson('/api/ai/generate-car-image/status?job_id=' . $record->id);

        $response->assertStatus(200);
        $response->assertJson([
            'ok' => true,
            'status' => 'queued',
            'image_url' => null,
        ]);
    }
}
