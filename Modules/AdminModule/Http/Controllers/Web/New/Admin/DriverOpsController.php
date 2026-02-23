<?php

namespace Modules\AdminModule\Http\Controllers\Web\New\Admin;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\View\View;

/**
 * Real Driver Ops pages. Safe queries with try/catch; fallback to empty data.
 */
class DriverOpsController extends Controller
{
    private function safeCount(callable $fn): int
    {
        try {
            return (int) $fn();
        } catch (\Throwable $e) {
            \Log::warning('Driver ops query failed: ' . $e->getMessage());
            return 0;
        }
    }

    public function driverApplications(Request $request): View
    {
        $items = collect();
        $total = $pending = $approved = $rejected = 0;
        try {
            if (class_exists(\Modules\UserManagement\Entities\DriverOnboardingStatus::class)) {
                $q = \Modules\UserManagement\Entities\DriverOnboardingStatus::query();
                $total = $q->count();
                $pending = (clone $q)->where('approved', false)->count();
                $approved = (clone $q)->where('approved', true)->count();
                $rejected = 0;
                $items = \Modules\UserManagement\Entities\DriverOnboardingStatus::orderByDesc('created_at')->limit(20)->get();
            }
        } catch (\Throwable $e) {
            \Log::warning('Driver applications query failed: ' . $e->getMessage());
        }
        $kpis = [
            ['label' => 'Total Applicants', 'value' => $total, 'icon' => 'bi-people'],
            ['label' => 'Pending', 'value' => $pending, 'icon' => 'bi-clock'],
            ['label' => 'Approved', 'value' => $approved, 'icon' => 'bi-check-circle'],
            ['label' => 'Rejected', 'value' => $rejected, 'icon' => 'bi-x-circle'],
        ];
        return view('adminmodule::ops.driver.applications', compact('items', 'kpis'));
    }

    public function driverDocuments(Request $request): View
    {
        $items = collect();
        $kpis = [
            ['label' => 'Pending Review', 'value' => 0, 'icon' => 'bi-file-earmark'],
            ['label' => 'Approved', 'value' => 0, 'icon' => 'bi-check'],
            ['label' => 'Rejected', 'value' => 0, 'icon' => 'bi-x'],
            ['label' => 'Total', 'value' => 0, 'icon' => 'bi-folder'],
        ];
        return view('adminmodule::ops.driver.documents', compact('items', 'kpis'));
    }

    public function driverPayoutSplits(Request $request): View
    {
        $items = collect();
        $kpis = [
            ['label' => 'Active Splits', 'value' => 0, 'icon' => 'bi-percent'],
            ['label' => 'Driver Share %', 'value' => '—', 'icon' => 'bi-person'],
            ['label' => 'Platform %', 'value' => '—', 'icon' => 'bi-bank'],
            ['label' => 'Last Updated', 'value' => '—', 'icon' => 'bi-clock'],
        ];
        return view('adminmodule::ops.driver.payout-splits', compact('items', 'kpis'));
    }

    public function driverTiers(Request $request): View
    {
        $items = collect();
        $kpis = [
            ['label' => 'Total Tiers', 'value' => 0, 'icon' => 'bi-layers'],
            ['label' => 'Active', 'value' => 0, 'icon' => 'bi-check'],
            ['label' => 'Drivers in Top Tier', 'value' => 0, 'icon' => 'bi-trophy'],
            ['label' => '—', 'value' => 0, 'icon' => 'bi-bar-chart'],
        ];
        return view('adminmodule::ops.driver.tiers', compact('items', 'kpis'));
    }

    public function driverAvailability(Request $request): View
    {
        $items = collect();
        $online = $available = $busy = $offline = 0;
        try {
            if (class_exists(\Modules\UserManagement\Entities\DriverDetail::class)) {
                $online = $this->safeCount(fn () => \Modules\UserManagement\Entities\DriverDetail::where('is_online', true)->count());
                $available = $this->safeCount(fn () => \Modules\UserManagement\Entities\DriverDetail::where('is_online', true)->where('availability_status', 'available')->count());
                $busy = $online - $available;
                $items = \Modules\UserManagement\Entities\DriverDetail::where('is_online', true)->orderByDesc('updated_at')->limit(25)->get();
            }
        } catch (\Throwable $e) {
            \Log::warning('Driver availability query failed: ' . $e->getMessage());
        }
        $kpis = [
            ['label' => 'Online', 'value' => $online, 'icon' => 'bi-wifi'],
            ['label' => 'Available', 'value' => $available, 'icon' => 'bi-check-circle'],
            ['label' => 'Busy', 'value' => $busy, 'icon' => 'bi-hourglass'],
            ['label' => 'Offline', 'value' => $offline, 'icon' => 'bi-dash-circle'],
        ];
        return view('adminmodule::ops.driver.availability', compact('items', 'kpis'));
    }

    public function driverPerformance(Request $request): View
    {
        $items = collect();
        try {
            if (class_exists(\Modules\UserManagement\Entities\DriverDetail::class)) {
                $items = \Modules\UserManagement\Entities\DriverDetail::orderByDesc('ride_count')->limit(25)->get();
            }
        } catch (\Throwable $e) {
            \Log::warning('Driver performance query failed: ' . $e->getMessage());
        }
        $totalRides = $items->sum('ride_count');
        $kpis = [
            ['label' => 'Total Rides (sample)', 'value' => $totalRides, 'icon' => 'bi-car-front'],
            ['label' => 'Drivers Listed', 'value' => $items->count(), 'icon' => 'bi-people'],
            ['label' => 'Avg Rides/Driver', 'value' => $items->isEmpty() ? 0 : (int) round($totalRides / $items->count()), 'icon' => 'bi-graph-up'],
            ['label' => '—', 'value' => 0, 'icon' => 'bi-bar-chart'],
        ];
        return view('adminmodule::ops.driver.performance', compact('items', 'kpis'));
    }
}
