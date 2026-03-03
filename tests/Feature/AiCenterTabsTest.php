<?php

namespace Tests\Feature;

use Modules\UserManagement\Entities\User;
use Tests\TestCase;

class AiCenterTabsTest extends TestCase
{
    private function admin(): User
    {
        return User::where('user_type', 'super-admin')->firstOrFail();
    }

    /** @dataProvider aiRouteProvider */
    public function test_ai_center_page_loads(string $routeName, string $expectedText): void
    {
        $response = $this->actingAs($this->admin())->get(route($routeName));
        $response->assertStatus(200);
        $response->assertSee($expectedText, false);
    }

    public static function aiRouteProvider(): array
    {
        return [
            'assistant'    => ['admin.ai.assistant.index',   'AI Assistant'],
            'fraud'        => ['admin.ai.fraud.index',       'Fraud / Risk Alerts'],
            'pricing'      => ['admin.ai.pricing.index',     'Smart Pricing Suggestions'],
            'supply'       => ['admin.ai.supply.index',      'Driver Supply Predictions'],
            'promo'        => ['admin.ai.promo.index',       'Promo Optimization'],
            'autoreplies'  => ['admin.ai.autoreplies.index', 'Auto Replies'],
        ];
    }

    public function test_all_ai_pages_have_kpi_cards(): void
    {
        $routes = [
            'admin.ai.assistant.index',
            'admin.ai.fraud.index',
            'admin.ai.pricing.index',
            'admin.ai.supply.index',
            'admin.ai.promo.index',
            'admin.ai.autoreplies.index',
        ];
        foreach ($routes as $name) {
            $response = $this->actingAs($this->admin())->get(route($name));
            $response->assertSee('oneway-kpi__label', false);
        }
    }

    public function test_all_ai_pages_have_filters(): void
    {
        $routes = [
            'admin.ai.assistant.index',
            'admin.ai.fraud.index',
            'admin.ai.pricing.index',
            'admin.ai.supply.index',
            'admin.ai.promo.index',
            'admin.ai.autoreplies.index',
        ];
        foreach ($routes as $name) {
            $response = $this->actingAs($this->admin())->get(route($name));
            $response->assertSee('name="search"', false);
        }
    }

    public function test_all_ai_pages_have_table_shell(): void
    {
        $routes = [
            'admin.ai.assistant.index',
            'admin.ai.fraud.index',
            'admin.ai.pricing.index',
            'admin.ai.supply.index',
            'admin.ai.promo.index',
            'admin.ai.autoreplies.index',
        ];
        foreach ($routes as $name) {
            $response = $this->actingAs($this->admin())->get(route($name));
            $response->assertSee('<table', false);
            $response->assertSee('No ', false);
        }
    }
}
