<?php

namespace Modules\AdminModule\Http\Controllers\Web\New\Admin;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\View\View;

/**
 * Real Promotions & Partners pages. Safe queries with try/catch; fallback to empty data.
 */
class PromotionsController extends Controller
{
    public function businesses(Request $request): View
    {
        $items = collect();
        $kpis = [
            ['label' => 'Total Partners', 'value' => 0, 'icon' => 'bi-building'],
            ['label' => 'Active', 'value' => 0, 'icon' => 'bi-check-circle'],
            ['label' => 'Pending', 'value' => 0, 'icon' => 'bi-clock'],
            ['label' => 'Inactive', 'value' => 0, 'icon' => 'bi-dash-circle'],
        ];
        return view('adminmodule::ops.promotions.businesses', compact('items', 'kpis'));
    }

    public function promotedListings(Request $request): View
    {
        $items = collect();
        $total = $active = $inactive = 0;
        try {
            if (class_exists(\Modules\PromotionManagement\Entities\BannerSetup::class)) {
                $q = \Modules\PromotionManagement\Entities\BannerSetup::query();
                $total = $q->count();
                $active = (clone $q)->where('is_active', 1)->count();
                $inactive = $total - $active;
                $items = (clone $q)->orderByDesc('created_at')->paginate(15)->appends($request->query());
            }
        } catch (\Throwable $e) {
            \Log::warning('Promoted listings query failed: ' . $e->getMessage());
        }
        $kpis = [
            ['label' => 'Total Listings', 'value' => $total, 'icon' => 'bi-megaphone'],
            ['label' => 'Active', 'value' => $active, 'icon' => 'bi-check-circle'],
            ['label' => 'Inactive', 'value' => $inactive, 'icon' => 'bi-dash-circle'],
            ['label' => 'Redirects (all)', 'value' => 0, 'icon' => 'bi-link'],
        ];
        return view('adminmodule::ops.promotions.promoted-listings', compact('items', 'kpis'));
    }

    public function rideIncentives(Request $request): View
    {
        $items = collect();
        $totalCoupons = $totalDiscounts = $active = 0;
        try {
            if (class_exists(\Modules\PromotionManagement\Entities\CouponSetup::class)) {
                $totalCoupons = \Modules\PromotionManagement\Entities\CouponSetup::where('is_active', 1)->count();
            }
            if (class_exists(\Modules\PromotionManagement\Entities\DiscountSetup::class)) {
                $totalDiscounts = \Modules\PromotionManagement\Entities\DiscountSetup::where('is_active', 1)->count();
            }
            $active = $totalCoupons + $totalDiscounts;
            $items = collect();
            if (class_exists(\Modules\PromotionManagement\Entities\CouponSetup::class)) {
                $items = \Modules\PromotionManagement\Entities\CouponSetup::orderByDesc('created_at')->limit(20)->get();
            }
        } catch (\Throwable $e) {
            \Log::warning('Ride incentives query failed: ' . $e->getMessage());
        }
        $kpis = [
            ['label' => 'Active Coupons', 'value' => $totalCoupons, 'icon' => 'bi-tag'],
            ['label' => 'Active Discounts', 'value' => $totalDiscounts, 'icon' => 'bi-percent'],
            ['label' => 'Total Active', 'value' => $active, 'icon' => 'bi-gift'],
            ['label' => 'Last 30d Uses', 'value' => 0, 'icon' => 'bi-graph-up'],
        ];
        return view('adminmodule::ops.promotions.ride-incentives', compact('items', 'kpis'));
    }

    public function promoPerformance(Request $request): View
    {
        $items = collect();
        $kpis = [
            ['label' => 'Impressions', 'value' => 0, 'icon' => 'bi-eye'],
            ['label' => 'Clicks', 'value' => 0, 'icon' => 'bi-cursor'],
            ['label' => 'Conversions', 'value' => 0, 'icon' => 'bi-arrow-right-circle'],
            ['label' => 'CTR %', 'value' => '0', 'icon' => 'bi-bar-chart'],
        ];
        return view('adminmodule::ops.promotions.promo-performance', compact('items', 'kpis'));
    }

    public function payoutRules(Request $request): View
    {
        $items = collect();
        $kpis = [
            ['label' => 'Active Rules', 'value' => 0, 'icon' => 'bi-list-check'],
            ['label' => 'Partner Payout %', 'value' => '0', 'icon' => 'bi-percent'],
            ['label' => 'Platform Share %', 'value' => '0', 'icon' => 'bi-bank'],
            ['label' => 'Last Updated', 'value' => '—', 'icon' => 'bi-clock-history'],
        ];
        return view('adminmodule::ops.promotions.payout-rules', compact('items', 'kpis'));
    }
}
