<?php

namespace Modules\AdminModule\Http\Controllers\Web\New\Admin;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\View\View;

/**
 * Real Payments & Finance pages (read-only / safe). Do not touch working Transaction module pages.
 */
class PaymentsFinanceController extends Controller
{
    public function withdraw(Request $request): View
    {
        $items = collect();
        $kpis = [['label' => 'Pending', 'value' => 0, 'icon' => 'bi-clock'], ['label' => 'Completed', 'value' => 0, 'icon' => 'bi-check'], ['label' => '—', 'value' => 0, 'icon' => 'bi-bar-chart'], ['label' => '—', 'value' => 0, 'icon' => 'bi-bar-chart']];
        return view('adminmodule::ops.payments.withdraw', compact('items', 'kpis'));
    }

    public function cashCollect(Request $request): View
    {
        $items = collect();
        $kpis = [['label' => 'Today', 'value' => 0, 'icon' => 'bi-cash'], ['label' => '—', 'value' => 0, 'icon' => 'bi-bar-chart'], ['label' => '—', 'value' => 0, 'icon' => 'bi-bar-chart'], ['label' => '—', 'value' => 0, 'icon' => 'bi-bar-chart']];
        return view('adminmodule::ops.payments.cash-collect', compact('items', 'kpis'));
    }

    public function refunds(Request $request): View
    {
        $items = collect();
        $kpis = [['label' => 'Pending', 'value' => 0, 'icon' => 'bi-clock'], ['label' => 'Processed', 'value' => 0, 'icon' => 'bi-check'], ['label' => '—', 'value' => 0, 'icon' => 'bi-bar-chart'], ['label' => '—', 'value' => 0, 'icon' => 'bi-bar-chart']];
        return view('adminmodule::ops.payments.refunds', compact('items', 'kpis'));
    }

    public function commissions(Request $request): View
    {
        $items = collect();
        $kpis = [['label' => 'Total', 'value' => 0, 'icon' => 'bi-percent'], ['label' => '—', 'value' => 0, 'icon' => 'bi-bar-chart'], ['label' => '—', 'value' => 0, 'icon' => 'bi-bar-chart'], ['label' => '—', 'value' => 0, 'icon' => 'bi-bar-chart']];
        return view('adminmodule::ops.payments.commissions', compact('items', 'kpis'));
    }

    public function coupon(Request $request): View
    {
        $items = collect();
        $kpis = [['label' => 'Active', 'value' => 0, 'icon' => 'bi-tag'], ['label' => 'Redeemed', 'value' => 0, 'icon' => 'bi-check'], ['label' => '—', 'value' => 0, 'icon' => 'bi-bar-chart'], ['label' => '—', 'value' => 0, 'icon' => 'bi-bar-chart']];
        return view('adminmodule::ops.payments.coupon', compact('items', 'kpis'));
    }

    public function revenueReports(Request $request): View
    {
        $items = collect();
        $kpis = [['label' => 'Daily', 'value' => 0, 'icon' => 'bi-calendar-day'], ['label' => 'Weekly', 'value' => 0, 'icon' => 'bi-calendar-week'], ['label' => 'Monthly', 'value' => 0, 'icon' => 'bi-calendar-month'], ['label' => '—', 'value' => 0, 'icon' => 'bi-bar-chart']];
        return view('adminmodule::ops.payments.revenue-reports', compact('items', 'kpis'));
    }
}
