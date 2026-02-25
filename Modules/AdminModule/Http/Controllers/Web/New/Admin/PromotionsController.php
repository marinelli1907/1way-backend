<?php

namespace Modules\AdminModule\Http\Controllers\Web\New\Admin;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Modules\PromotionManagement\Entities\CouponSetup;

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
        $events = collect();
        $total = $active = $inactive = 0;
        $totalEvents = $promotedEvents = 0;
        try {
            if (class_exists(\Modules\PromotionManagement\Entities\BannerSetup::class)) {
                $q = \Modules\PromotionManagement\Entities\BannerSetup::query();
                $total = $q->count();
                $active = (clone $q)->where('is_active', 1)->count();
                $inactive = $total - $active;
                $items = (clone $q)->orderByDesc('created_at')->paginate(15)->appends($request->query());
            }
        } catch (\Throwable $e) {
            Log::warning('Promoted listings query failed: ' . $e->getMessage());
        }
        try {
            if (class_exists(\App\Models\Event::class)) {
                $totalEvents = \App\Models\Event::where('is_active', true)->count();
                $promotedEvents = \App\Models\Event::where('is_promoted', true)->where('is_active', true)->count();
                $events = \App\Models\Event::where('is_active', true)
                    ->orderByDesc('is_promoted')
                    ->orderByDesc('start_at')
                    ->paginate(20, ['*'], 'events_page')
                    ->appends($request->query());
            }
        } catch (\Throwable $e) {
            Log::warning('Promoted events query failed: ' . $e->getMessage());
        }
        $kpis = [
            ['label' => 'Total Listings', 'value' => $total, 'icon' => 'bi-megaphone'],
            ['label' => 'Active Banners', 'value' => $active, 'icon' => 'bi-check-circle'],
            ['label' => 'Total Events', 'value' => $totalEvents, 'icon' => 'bi-calendar-event'],
            ['label' => 'Promoted Events', 'value' => $promotedEvents, 'icon' => 'bi-star'],
        ];
        return view('adminmodule::ops.promotions.promoted-listings', compact('items', 'events', 'kpis'));
    }

    public function rideIncentives(Request $request): View
    {
        $items = collect();
        $totalCoupons = $totalDiscounts = $active = $totalAll = 0;
        try {
            if (class_exists(CouponSetup::class)) {
                $totalAll = CouponSetup::count();
                $totalCoupons = CouponSetup::where('is_active', 1)->count();
            }
            if (class_exists(\Modules\PromotionManagement\Entities\DiscountSetup::class)) {
                $totalDiscounts = \Modules\PromotionManagement\Entities\DiscountSetup::where('is_active', 1)->count();
            }
            $active = $totalCoupons + $totalDiscounts;

            $query = CouponSetup::orderByDesc('created_at');
            if ($search = $request->input('search')) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('coupon_code', 'like', "%{$search}%")
                      ->orWhere('coupon', 'like', "%{$search}%");
                });
            }
            $items = $query->paginate(20)->appends($request->query());
        } catch (\Throwable $e) {
            Log::warning('Ride incentives query failed: ' . $e->getMessage());
        }
        $kpis = [
            ['label' => 'Active Coupons', 'value' => $totalCoupons, 'icon' => 'bi-tag'],
            ['label' => 'Active Discounts', 'value' => $totalDiscounts, 'icon' => 'bi-percent'],
            ['label' => 'Total Active', 'value' => $active, 'icon' => 'bi-gift'],
            ['label' => 'Total Coupons', 'value' => $totalAll, 'icon' => 'bi-graph-up'],
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

    // ── Coupon CRUD ────────────────────────────────────────────────────────────

    public function storeCoupon(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name'                => 'required|string|max:100',
            'code'                => 'required|string|max:50|unique:coupon_setups,coupon_code',
            'amount_type'         => 'required|in:percentage,amount',
            'coupon_type'         => 'nullable|string|max:50',
            'discount'            => 'required|numeric|min:0',
            'min_trip_amount'     => 'nullable|numeric|min:0',
            'max_coupon_amount'   => 'nullable|numeric|min:0',
            'limit'               => 'nullable|integer|min:1',
            'start_date'          => 'required|date',
            'end_date'            => 'required|date|after_or_equal:start_date',
            'description'         => 'nullable|string|max:500',
        ]);

        try {
            CouponSetup::create([
                'name'              => $data['name'],
                'description'       => $data['description'] ?? null,
                'coupon_code'       => strtoupper($data['code']),
                'coupon'            => strtoupper($data['code']),
                'amount_type'       => $data['amount_type'],
                'coupon_type'       => $data['coupon_type'] ?? 'default',
                'total_amount'      => $data['discount'],
                'min_trip_amount'   => $data['min_trip_amount'] ?? 0,
                'max_coupon_amount' => $data['max_coupon_amount'] ?? 0,
                'limit'             => $data['limit'] ?? 1,
                'start_date'        => $data['start_date'],
                'end_date'          => $data['end_date'],
                'is_active'         => true,
                'rules'             => null,
                'zone_coupon_type'           => 'all',
                'customer_level_coupon_type' => 'all',
                'customer_coupon_type'       => 'all',
                'category_coupon_type'       => ['all'],
            ]);
            if (class_exists(\Brian2694\Toastr\Facades\Toastr::class)) {
                \Brian2694\Toastr\Facades\Toastr::success('Coupon created successfully.');
            }
        } catch (\Throwable $e) {
            Log::error('Coupon create error: ' . $e->getMessage());
            if (class_exists(\Brian2694\Toastr\Facades\Toastr::class)) {
                \Brian2694\Toastr\Facades\Toastr::error('Failed to create coupon.');
            }
        }

        return back();
    }

    public function updateCoupon(Request $request, string $id): RedirectResponse
    {
        $data = $request->validate([
            'name'                => 'required|string|max:100',
            'code'                => 'required|string|max:50',
            'amount_type'         => 'required|in:percentage,amount',
            'coupon_type'         => 'nullable|string|max:50',
            'discount'            => 'required|numeric|min:0',
            'min_trip_amount'     => 'nullable|numeric|min:0',
            'max_coupon_amount'   => 'nullable|numeric|min:0',
            'limit'               => 'nullable|integer|min:1',
            'start_date'          => 'required|date',
            'end_date'            => 'required|date|after_or_equal:start_date',
            'description'         => 'nullable|string|max:500',
        ]);

        try {
            $coupon = CouponSetup::findOrFail($id);
            $coupon->update([
                'name'              => $data['name'],
                'description'       => $data['description'] ?? $coupon->description,
                'coupon_code'       => strtoupper($data['code']),
                'coupon'            => strtoupper($data['code']),
                'amount_type'       => $data['amount_type'],
                'coupon_type'       => $data['coupon_type'] ?? $coupon->coupon_type,
                'total_amount'      => $data['discount'],
                'min_trip_amount'   => $data['min_trip_amount'] ?? 0,
                'max_coupon_amount' => $data['max_coupon_amount'] ?? 0,
                'limit'             => $data['limit'] ?? 1,
                'start_date'        => $data['start_date'],
                'end_date'          => $data['end_date'],
            ]);
            if (class_exists(\Brian2694\Toastr\Facades\Toastr::class)) {
                \Brian2694\Toastr\Facades\Toastr::success('Coupon updated successfully.');
            }
        } catch (\Throwable $e) {
            Log::error('Coupon update error: ' . $e->getMessage());
            if (class_exists(\Brian2694\Toastr\Facades\Toastr::class)) {
                \Brian2694\Toastr\Facades\Toastr::error('Failed to update coupon.');
            }
        }

        return back();
    }

    public function toggleCoupon(Request $request): RedirectResponse
    {
        $request->validate(['id' => 'required']);

        try {
            $coupon = CouponSetup::findOrFail($request->input('id'));
            $coupon->update(['is_active' => !$coupon->is_active]);
            $status = $coupon->is_active ? 'activated' : 'deactivated';
            if (class_exists(\Brian2694\Toastr\Facades\Toastr::class)) {
                \Brian2694\Toastr\Facades\Toastr::success("Coupon {$status} successfully.");
            }
        } catch (\Throwable $e) {
            Log::error('Coupon toggle error: ' . $e->getMessage());
            if (class_exists(\Brian2694\Toastr\Facades\Toastr::class)) {
                \Brian2694\Toastr\Facades\Toastr::error('Failed to toggle coupon status.');
            }
        }

        return back();
    }

    // ── Promoted Events ────────────────────────────────────────────────────────

    public function togglePromotedEvent(Request $request): RedirectResponse
    {
        $request->validate(['id' => 'required']);

        try {
            $event = \App\Models\Event::findOrFail($request->input('id'));
            $event->update(['is_promoted' => !$event->is_promoted]);
            $status = $event->is_promoted ? 'promoted' : 'un-promoted';
            if (class_exists(\Brian2694\Toastr\Facades\Toastr::class)) {
                \Brian2694\Toastr\Facades\Toastr::success("Event {$status} successfully.");
            }
        } catch (\Throwable $e) {
            Log::error('Event promote toggle error: ' . $e->getMessage());
            if (class_exists(\Brian2694\Toastr\Facades\Toastr::class)) {
                \Brian2694\Toastr\Facades\Toastr::error('Failed to toggle event promotion.');
            }
        }

        return back();
    }
}
