<?php

namespace Modules\AdminModule\Http\Controllers\Web\New\Admin;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Modules\TripManagement\Entities\TripRequest;
use Modules\ZoneManagement\Entities\Zone;
use Modules\UserManagement\Entities\User;

/**
 * CalendarEventsController
 *
 * Handles Calendar, Events, Event Ride Planner, Venues, and Event Analytics.
 * No events/venues tables exist yet — pages show structured empty-states
 * with real data from trips/zones where possible.
 */
class CalendarEventsController extends Controller
{
    use AuthorizesRequests;

    private function safeCount($callable, int $default = 0): int
    {
        try { return (int) $callable(); } catch (\Throwable $e) { return $default; }
    }

    private function safeQuery($callable, $default = null)
    {
        try { return $callable(); } catch (\Throwable $e) { return $default ?? collect(); }
    }

    // ── 1) Calendar (admin view) ──────────────────────────────────────────────

    public function calendar(Request $request)
    {
        $month = (int) $request->input('month', now()->month);
        $year  = (int) $request->input('year',  now()->year);

        // Build calendar days — map scheduled trips onto dates
        $scheduledTrips = $this->safeQuery(fn() => TripRequest::where('current_status', 'scheduled')
            ->whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->selectRaw("DATE(created_at) as trip_date, count(*) as cnt")
            ->groupBy('trip_date')
            ->get()
            ->keyBy('trip_date'));

        $daysInMonth = Carbon::create($year, $month, 1)->daysInMonth;
        $firstDayOfWeek = Carbon::create($year, $month, 1)->dayOfWeek; // 0=Sun

        $totalScheduled = $this->safeCount(fn() => TripRequest::where('current_status', 'scheduled')->count());
        $thisMonthTrips = $this->safeCount(fn() => TripRequest::whereYear('created_at', $year)->whereMonth('created_at', $month)->count());

        return view('adminmodule::calendar.calendar', compact(
            'scheduledTrips', 'daysInMonth', 'firstDayOfWeek',
            'month', 'year', 'totalScheduled', 'thisMonthTrips'
        ));
    }

    // ── 2) Events List ────────────────────────────────────────────────────────

    public function eventsList(Request $request)
    {
        $search = $request->input('search');

        // No events table yet — show structured empty state with zone data
        $zones = $this->safeQuery(fn() => Zone::where('is_active', true)->orderBy('name')->get());
        $scheduledTrips = $this->safeQuery(fn() => TripRequest::with(['customer'])
            ->where('current_status', 'scheduled')
            ->when($search, fn($q) => $q->whereHas('customer', fn($q2) =>
                $q2->where('first_name', 'like', "%$search%")->orWhere('last_name', 'like', "%$search%")
            ))
            ->orderByDesc('created_at')
            ->paginate(20));

        $totalScheduled = $this->safeCount(fn() => TripRequest::where('current_status', 'scheduled')->count());
        $totalZones     = $this->safeCount(fn() => Zone::where('is_active', true)->count());

        return view('adminmodule::calendar.events-list', compact(
            'zones', 'scheduledTrips', 'totalScheduled', 'totalZones', 'search'
        ));
    }

    // ── 3) Create / Manage Events ─────────────────────────────────────────────

    public function manageEvents(Request $request)
    {
        $zones = $this->safeQuery(fn() => Zone::where('is_active', true)->orderBy('name')->get());

        return view('adminmodule::calendar.manage-events', compact('zones'));
    }

    // ── 4) Event Ride Planner ─────────────────────────────────────────────────

    public function eventRidePlanner(Request $request)
    {
        $zones = $this->safeQuery(fn() => Zone::where('is_active', true)->orderBy('name')->get());

        $scheduledCount = $this->safeCount(fn() => TripRequest::where('current_status', 'scheduled')->count());
        $pendingCount   = $this->safeCount(fn() => TripRequest::where('current_status', 'pending')->count());
        $driversOnline  = $this->safeCount(fn() => \Modules\UserManagement\Entities\DriverDetail::where('is_online', true)->count());
        $driversAvail   = $this->safeCount(fn() => \Modules\UserManagement\Entities\DriverDetail::where('is_online', true)->where('availability_status', 'available')->count());

        return view('adminmodule::calendar.event-ride-planner', compact(
            'zones', 'scheduledCount', 'pendingCount', 'driversOnline', 'driversAvail'
        ));
    }

    // ── 5) Venues / Locations ─────────────────────────────────────────────────

    public function venues(Request $request)
    {
        $search = $request->input('search');

        // No venues table — show active zones as coverage areas
        $zones = $this->safeQuery(fn() => Zone::when($search, fn($q) => $q->where('name', 'like', "%$search%"))
            ->orderBy('name')->paginate(20));

        $totalZones  = $this->safeCount(fn() => Zone::count());
        $activeZones = $this->safeCount(fn() => Zone::where('is_active', true)->count());

        return view('adminmodule::calendar.venues', compact(
            'zones', 'totalZones', 'activeZones', 'search'
        ));
    }

    // ── 6) Event Analytics ────────────────────────────────────────────────────

    public function eventAnalytics(Request $request)
    {
        $from = $request->input('from', Carbon::now()->subDays(30)->toDateString());
        $to   = $request->input('to',   Carbon::now()->toDateString());

        $totalTrips     = $this->safeCount(fn() => TripRequest::whereDate('created_at', '>=', $from)->whereDate('created_at', '<=', $to)->count());
        $scheduledTrips = $this->safeCount(fn() => TripRequest::where('current_status', 'scheduled')->whereDate('created_at', '>=', $from)->whereDate('created_at', '<=', $to)->count());
        $completedTrips = $this->safeCount(fn() => TripRequest::where('current_status', 'completed')->whereDate('created_at', '>=', $from)->whereDate('created_at', '<=', $to)->count());
        $cancelledTrips = $this->safeCount(fn() => TripRequest::where('current_status', 'cancelled')->whereDate('created_at', '>=', $from)->whereDate('created_at', '<=', $to)->count());

        // Zone breakdown
        $zoneBreakdown = $this->safeQuery(fn() => Zone::withCount(['tripRequest' => fn($q) =>
            $q->whereDate('created_at', '>=', $from)->whereDate('created_at', '<=', $to)
        ])->where('is_active', true)->orderByDesc('trip_request_count')->get());

        // Daily trend
        $dailyTrend = $this->safeQuery(fn() => TripRequest::whereDate('created_at', '>=', $from)
            ->whereDate('created_at', '<=', $to)
            ->selectRaw("DATE(created_at) as date, count(*) as total")
            ->groupBy('date')->orderBy('date')->get());

        return view('adminmodule::calendar.event-analytics', compact(
            'totalTrips', 'scheduledTrips', 'completedTrips', 'cancelledTrips',
            'zoneBreakdown', 'dailyTrend', 'from', 'to'
        ));
    }
}
