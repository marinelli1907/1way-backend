<?php

namespace Modules\AdminModule\Http\Controllers\Web\New\Admin;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\View\View;

/**
 * Real Business Center pages. Safe queries; fallback to empty data.
 */
class BusinessCenterController extends Controller
{
    public function businessSettings(Request $request): View
    {
        $items = collect();
        try {
            if (class_exists(\Modules\BusinessManagement\Entities\BusinessSetting::class)) {
                $items = \Modules\BusinessManagement\Entities\BusinessSetting::orderBy('key_name')->limit(50)->get();
            }
        } catch (\Throwable $e) {
            \Log::warning('Business settings query failed: ' . $e->getMessage());
        }
        $kpis = [
            ['label' => 'Settings', 'value' => $items->count(), 'icon' => 'bi-gear'],
            ['label' => '—', 'value' => 0, 'icon' => 'bi-bar-chart'],
            ['label' => '—', 'value' => 0, 'icon' => 'bi-bar-chart'],
            ['label' => '—', 'value' => 0, 'icon' => 'bi-bar-chart'],
        ];
        return view('adminmodule::ops.business.settings', compact('items', 'kpis'));
    }

    public function pricingRules(Request $request): View
    {
        $items = collect();
        try {
            if (class_exists(\Modules\FareManagement\Entities\TripFare::class)) {
                $items = \Modules\FareManagement\Entities\TripFare::orderByDesc('updated_at')->limit(20)->get();
            }
        } catch (\Throwable $e) {
            \Log::warning('Pricing rules query failed: ' . $e->getMessage());
        }
        $kpis = [
            ['label' => 'Fare Rules', 'value' => $items->count(), 'icon' => 'bi-currency-dollar'],
            ['label' => '—', 'value' => 0, 'icon' => 'bi-bar-chart'],
            ['label' => '—', 'value' => 0, 'icon' => 'bi-bar-chart'],
            ['label' => '—', 'value' => 0, 'icon' => 'bi-bar-chart'],
        ];
        return view('adminmodule::ops.business.pricing-rules', compact('items', 'kpis'));
    }

    public function taxesFees(Request $request): View
    {
        $items = collect();
        $kpis = [
            ['label' => 'Tax Rules', 'value' => 0, 'icon' => 'bi-percent'],
            ['label' => 'Fee Rules', 'value' => 0, 'icon' => 'bi-cash'],
            ['label' => '—', 'value' => 0, 'icon' => 'bi-bar-chart'],
            ['label' => '—', 'value' => 0, 'icon' => 'bi-bar-chart'],
        ];
        return view('adminmodule::ops.business.taxes-fees', compact('items', 'kpis'));
    }

    public function invoices(Request $request): View
    {
        $items = collect();
        $kpis = [
            ['label' => 'Total Invoices', 'value' => 0, 'icon' => 'bi-file-earmark-text'],
            ['label' => 'Pending', 'value' => 0, 'icon' => 'bi-clock'],
            ['label' => 'Paid', 'value' => 0, 'icon' => 'bi-check'],
            ['label' => 'Overdue', 'value' => 0, 'icon' => 'bi-exclamation'],
        ];
        return view('adminmodule::ops.business.invoices', compact('items', 'kpis'));
    }

    public function subscriptions(Request $request): View
    {
        $items = collect();
        $kpis = [
            ['label' => 'Plans', 'value' => 0, 'icon' => 'bi-layers'],
            ['label' => 'Active', 'value' => 0, 'icon' => 'bi-check-circle'],
            ['label' => '—', 'value' => 0, 'icon' => 'bi-bar-chart'],
            ['label' => '—', 'value' => 0, 'icon' => 'bi-bar-chart'],
        ];
        return view('adminmodule::ops.business.subscriptions', compact('items', 'kpis'));
    }

    public function auditLogs(Request $request): View
    {
        $items = collect();
        try {
            if (class_exists(\Modules\AdminModule\Entities\ActivityLog::class)) {
                $items = \Modules\AdminModule\Entities\ActivityLog::orderByDesc('created_at')->limit(30)->get();
            }
        } catch (\Throwable $e) {
            \Log::warning('Audit logs query failed: ' . $e->getMessage());
        }
        $kpis = [
            ['label' => 'Total Logs', 'value' => $items->count(), 'icon' => 'bi-journal-text'],
            ['label' => 'Last 24h', 'value' => 0, 'icon' => 'bi-clock'],
            ['label' => '—', 'value' => 0, 'icon' => 'bi-bar-chart'],
            ['label' => '—', 'value' => 0, 'icon' => 'bi-bar-chart'],
        ];
        return view('adminmodule::ops.business.audit-logs', compact('items', 'kpis'));
    }
}
