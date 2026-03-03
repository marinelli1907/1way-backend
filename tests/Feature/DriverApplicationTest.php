<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Modules\UserManagement\Entities\DriverApplication;
use Modules\UserManagement\Entities\User;
use Tests\TestCase;

class DriverApplicationTest extends TestCase
{
    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::firstOrCreate(
            ['email' => 'admin-driverapp-test@test.local'],
            [
                'first_name' => 'Admin',
                'last_name'  => 'AppTest',
                'full_name'  => 'Admin AppTest',
                'phone'      => '15550001111',
                'password'   => Hash::make('password'),
                'user_type'  => 'super-admin',
                'is_active'  => 1,
            ]
        );
    }

    private function validPayload(): array
    {
        return [
            'first_name'          => 'Jane',
            'last_name'           => 'Doe',
            'phone'               => '(555) 867-5309',
            'email'               => 'jane.doe.' . rand(1000, 99999) . '@test.local',
            'city'                => 'Columbus',
            'state'               => 'OH',
            'vehicle_make'        => 'Toyota',
            'vehicle_model'       => 'Camry',
            'vehicle_year'        => '2022',
            'rideshare_insurance' => 'Yes',
            'availability'        => ['Weekdays', 'Weekends'],
            'preferred_service_area' => 'Downtown Columbus',
            'notes'               => 'Available starting next week.',
            'consent'             => '1',
        ];
    }

    // ─── Public API Tests ────────────────────────────────────────────────

    public function test_successful_application_creates_row_and_stores_file(): void
    {
        Storage::fake('public');

        $payload = $this->validPayload();
        $payload['license_photo'] = UploadedFile::fake()->image('license.jpg', 640, 480)->size(500);

        $response = $this->postJson('/api/driver/applications', $payload);

        $response->assertStatus(201)
                 ->assertJson([
                     'ok'     => true,
                     'status' => 'pending',
                 ])
                 ->assertJsonStructure(['ok', 'application_id', 'status']);

        $appId = $response->json('application_id');
        $this->assertNotNull($appId);

        $app = DriverApplication::find($appId);
        $this->assertNotNull($app);
        $this->assertEquals('Jane', $app->first_name);
        $this->assertEquals('Doe', $app->last_name);
        $this->assertEquals('5558675309', $app->phone);
        $this->assertEquals('Columbus', $app->city);
        $this->assertEquals('OH', $app->state);
        $this->assertEquals('pending', $app->status);
        $this->assertTrue($app->consent);
        $this->assertTrue($app->rideshare_insurance);
        $this->assertIsArray($app->availability);
        $this->assertContains('Weekdays', $app->availability);

        Storage::disk('public')->assertExists($app->license_photo_path);
    }

    public function test_validation_fails_without_consent(): void
    {
        Storage::fake('public');

        $payload = $this->validPayload();
        $payload['consent'] = '0';
        $payload['license_photo'] = UploadedFile::fake()->image('license.jpg');

        $response = $this->postJson('/api/driver/applications', $payload);

        $response->assertStatus(422)
                 ->assertJson(['ok' => false])
                 ->assertJsonValidationErrors(['consent']);
    }

    public function test_validation_fails_without_license_photo(): void
    {
        $payload = $this->validPayload();

        $response = $this->postJson('/api/driver/applications', $payload);

        $response->assertStatus(422)
                 ->assertJson(['ok' => false])
                 ->assertJsonValidationErrors(['license_photo']);
    }

    public function test_validation_fails_without_required_fields(): void
    {
        Storage::fake('public');

        $response = $this->postJson('/api/driver/applications', []);

        $response->assertStatus(422)
                 ->assertJson(['ok' => false])
                 ->assertJsonValidationErrors([
                     'first_name', 'last_name', 'phone', 'email',
                     'city', 'state', 'consent', 'license_photo',
                 ]);
    }

    // ─── Admin Tests ─────────────────────────────────────────────────────

    public function test_admin_can_view_application_list(): void
    {
        DriverApplication::create([
            'first_name' => 'Test',
            'last_name'  => 'User',
            'phone'      => '5550001234',
            'email'      => 'test@example.com',
            'city'       => 'Cleveland',
            'state'      => 'OH',
            'consent'    => true,
            'status'     => 'pending',
        ]);

        $response = $this->actingAs($this->admin, 'web')
                         ->get(route('admin.driver-applications.index'));

        $response->assertStatus(200);
        $response->assertSee('Test');
        $response->assertSee('Cleveland');
    }

    public function test_admin_can_view_application_detail(): void
    {
        $app = DriverApplication::create([
            'first_name' => 'Detail',
            'last_name'  => 'Viewer',
            'phone'      => '5550005678',
            'email'      => 'detail@example.com',
            'city'       => 'Akron',
            'state'      => 'OH',
            'consent'    => true,
            'status'     => 'pending',
        ]);

        $response = $this->actingAs($this->admin, 'web')
                         ->get(route('admin.driver-applications.show', $app->id));

        $response->assertStatus(200);
        $response->assertSee('Detail');
        $response->assertSee('Viewer');
        $response->assertSee('Akron');
    }

    public function test_admin_can_approve_application(): void
    {
        $app = DriverApplication::create([
            'first_name' => 'Approve',
            'last_name'  => 'Me',
            'phone'      => '5550009999',
            'email'      => 'approve@example.com',
            'city'       => 'Toledo',
            'state'      => 'OH',
            'consent'    => true,
            'status'     => 'pending',
        ]);

        $response = $this->actingAs($this->admin, 'web')
                         ->post(route('admin.driver-applications.approve', $app->id));

        $response->assertRedirect();

        $app->refresh();
        $this->assertEquals('approved', $app->status);
        $this->assertEquals($this->admin->id, $app->reviewed_by);
        $this->assertNotNull($app->reviewed_at);
    }

    public function test_admin_can_reject_application(): void
    {
        $app = DriverApplication::create([
            'first_name' => 'Reject',
            'last_name'  => 'Me',
            'phone'      => '5550008888',
            'email'      => 'reject@example.com',
            'city'       => 'Dayton',
            'state'      => 'OH',
            'consent'    => true,
            'status'     => 'pending',
        ]);

        $response = $this->actingAs($this->admin, 'web')
                         ->post(route('admin.driver-applications.reject', $app->id));

        $response->assertRedirect();

        $app->refresh();
        $this->assertEquals('rejected', $app->status);
        $this->assertEquals($this->admin->id, $app->reviewed_by);
        $this->assertNotNull($app->reviewed_at);
    }

    public function test_unauthenticated_user_cannot_access_admin_pages(): void
    {
        $response = $this->get(route('admin.driver-applications.index'));
        $response->assertRedirect();
    }
}
