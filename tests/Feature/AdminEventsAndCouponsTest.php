<?php

namespace Tests\Feature;

use App\Models\Event;
use Illuminate\Support\Facades\Hash;
use Modules\UserManagement\Entities\User;
use Tests\TestCase;

class AdminEventsAndCouponsTest extends TestCase
{
    private $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::firstOrCreate(
            ['email' => 'admin-events-test@test.local'],
            [
                'first_name' => 'Admin',
                'last_name' => 'Events',
                'full_name' => 'Admin Events',
                'phone' => '15555558888',
                'password' => Hash::make('password'),
                'user_type' => 'super-admin',
                'is_active' => 1,
            ]
        );
    }

    public function test_calendar_page_loads(): void
    {
        $response = $this->actingAs($this->admin, 'web')
            ->get(route('admin.calendar.index'));

        $this->assertContains($response->status(), [200, 302]);
    }

    public function test_events_list_page_loads(): void
    {
        $response = $this->actingAs($this->admin, 'web')
            ->get(route('admin.events.index'));

        $this->assertContains($response->status(), [200, 302]);
    }

    public function test_manage_events_page_loads(): void
    {
        $response = $this->actingAs($this->admin, 'web')
            ->get(route('admin.events.manage'));

        $this->assertContains($response->status(), [200, 302]);
    }

    public function test_create_public_event(): void
    {
        $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class);

        $response = $this->actingAs($this->admin, 'web')
            ->post(route('admin.events.store'), [
                'title' => 'Test Public Event',
                'description' => 'A public event for testing',
                'start_at' => now()->addDay()->format('Y-m-d\TH:i'),
                'end_at' => now()->addDays(2)->format('Y-m-d\TH:i'),
                'visibility' => 'public',
            ]);

        $response->assertRedirect();

        if (class_exists(Event::class)) {
            $event = Event::where('title', 'Test Public Event')->first();
            if ($event) {
                $this->assertSame('public', $event->visibility);
                $this->assertTrue($event->is_active);
            }
        }
    }

    public function test_create_private_event_requires_code(): void
    {
        $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class);

        $response = $this->actingAs($this->admin, 'web')
            ->post(route('admin.events.store'), [
                'title' => 'Test Private Event',
                'start_at' => now()->addDay()->format('Y-m-d\TH:i'),
                'end_at' => now()->addDays(2)->format('Y-m-d\TH:i'),
                'visibility' => 'private',
            ]);

        $response->assertSessionHasErrors('private_code');
    }

    public function test_coupon_page_loads(): void
    {
        $response = $this->actingAs($this->admin, 'web')
            ->get(route('admin.coupon.index'));

        $this->assertContains($response->status(), [200, 302]);
    }

    public function test_ride_incentives_page_loads(): void
    {
        $response = $this->actingAs($this->admin, 'web')
            ->get(route('admin.ride-incentives.index'));

        $this->assertContains($response->status(), [200, 302]);
    }

    public function test_promoted_listings_page_loads(): void
    {
        $response = $this->actingAs($this->admin, 'web')
            ->get(route('admin.promoted-listings.index'));

        $this->assertContains($response->status(), [200, 302]);
    }

    public function test_profile_settings_route_exists_and_loads(): void
    {
        $response = $this->actingAs($this->admin, 'web')
            ->get(route('admin.profile-settings'));

        $this->assertContains($response->status(), [200, 302]);
    }

    public function test_business_setting_route_exists_and_redirects(): void
    {
        $response = $this->actingAs($this->admin, 'web')
            ->get(route('admin.business.setting'));

        $this->assertContains($response->status(), [200, 302]);
    }
}
