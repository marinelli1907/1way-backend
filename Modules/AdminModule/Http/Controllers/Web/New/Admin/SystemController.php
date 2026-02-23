<?php

namespace Modules\AdminModule\Http\Controllers\Web\New\Admin;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\View\View;

/**
 * Real System pages. Config, notifications, integrations, API keys, backups, maintenance.
 */
class SystemController extends Controller
{
    public function systemConfig(Request $request): View
    {
        $items = collect();
        $kpis = [
            ['label' => 'Env', 'value' => config('app.env', '—'), 'icon' => 'bi-gear'],
            ['label' => 'Debug', 'value' => config('app.debug') ? 'On' : 'Off', 'icon' => 'bi-bug'],
            ['label' => '—', 'value' => 0, 'icon' => 'bi-bar-chart'],
            ['label' => '—', 'value' => 0, 'icon' => 'bi-bar-chart'],
        ];
        return view('adminmodule::ops.system.config', compact('items', 'kpis'));
    }

    public function systemNotifications(Request $request): View
    {
        $items = collect();
        $kpis = [
            ['label' => 'Push', 'value' => '—', 'icon' => 'bi-phone'],
            ['label' => 'Email', 'value' => '—', 'icon' => 'bi-envelope'],
            ['label' => 'SMS', 'value' => '—', 'icon' => 'bi-chat-dots'],
            ['label' => '—', 'value' => 0, 'icon' => 'bi-bar-chart'],
        ];
        return view('adminmodule::ops.system.notifications', compact('items', 'kpis'));
    }

    public function systemIntegrations(Request $request): View
    {
        $items = collect();
        $kpis = [
            ['label' => 'Active', 'value' => 0, 'icon' => 'bi-plug'],
            ['label' => 'Maps', 'value' => '—', 'icon' => 'bi-geo'],
            ['label' => 'Payments', 'value' => '—', 'icon' => 'bi-credit-card'],
            ['label' => '—', 'value' => 0, 'icon' => 'bi-bar-chart'],
        ];
        return view('adminmodule::ops.system.integrations', compact('items', 'kpis'));
    }

    public function systemApiKeys(Request $request): View
    {
        $items = collect();
        $kpis = [
            ['label' => 'Keys', 'value' => 0, 'icon' => 'bi-key'],
            ['label' => 'Active', 'value' => 0, 'icon' => 'bi-check'],
            ['label' => '—', 'value' => 0, 'icon' => 'bi-bar-chart'],
            ['label' => '—', 'value' => 0, 'icon' => 'bi-bar-chart'],
        ];
        return view('adminmodule::ops.system.api-keys', compact('items', 'kpis'));
    }

    public function systemBackups(Request $request): View
    {
        $items = collect();
        $kpis = [
            ['label' => 'Last backup', 'value' => '—', 'icon' => 'bi-cloud-arrow-down'],
            ['label' => 'Size', 'value' => '—', 'icon' => 'bi-hdd'],
            ['label' => '—', 'value' => 0, 'icon' => 'bi-bar-chart'],
            ['label' => '—', 'value' => 0, 'icon' => 'bi-bar-chart'],
        ];
        return view('adminmodule::ops.system.backups', compact('items', 'kpis'));
    }

    public function systemMaintenance(Request $request): View
    {
        $kpis = [
            ['label' => 'Mode', 'value' => 'Live', 'icon' => 'bi-toggle-on'],
            ['label' => '—', 'value' => 0, 'icon' => 'bi-bar-chart'],
            ['label' => '—', 'value' => 0, 'icon' => 'bi-bar-chart'],
            ['label' => '—', 'value' => 0, 'icon' => 'bi-bar-chart'],
        ];
        return view('adminmodule::ops.system.maintenance', compact('kpis'));
    }
}
