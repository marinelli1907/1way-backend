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

        $scheduledTrips = $this->safeQuery(fn() => TripRequest::where('current_status', 'scheduled')
            ->whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->selectRaw("DATE(created_at) as trip_date, count(*) as cnt")
            ->groupBy('trip_date')
            ->get()
            ->keyBy('trip_date'));

        $daysInMonth = Carbon::create($year, $month, 1)->daysInMonth;
        $firstDayOfWeek = Carbon::create($year, $month, 1)->dayOfWeek;

        $totalScheduled   = $this->safeCount(fn() => TripRequest::where('current_status', 'scheduled')->count());
        $thisMonthTrips    = $this->safeCount(fn() => TripRequest::whereYear('created_at', $year)->whereMonth('created_at', $month)->count());
        $thisMonthComplete = $this->safeCount(fn() => TripRequest::where('current_status', 'completed')->whereYear('created_at', $year)->whereMonth('created_at', $month)->count());
        $pendingCount      = $this->safeCount(fn() => TripRequest::where('current_status', 'pending')->count());

        return view('adminmodule::calendar.calendar', compact(
            'scheduledTrips', 'daysInMonth', 'firstDayOfWeek',
            'month', 'year', 'totalScheduled', 'thisMonthTrips', 'thisMonthComplete', 'pendingCount'
        ));
    }

    // ── 2) Events List ────────────────────────────────────────────────────────

    public function eventsList(Request $request)
    {
        $from   = $request->input('date_from', Carbon::now()->subDays(30)->toDateString());
        $to     = $request->input('date_to', Carbon::now()->toDateString());
        $search = $request->input('search');
        $zoneId = $request->input('zone_id');

        $totalScheduled = $this->safeCount(fn() => TripRequest::where('current_status', 'scheduled')->count());
        $totalZones     = $this->safeCount(fn() => Zone::where('is_active', true)->count());
        $scheduledToday = $this->safeCount(fn() => TripRequest::where('current_status', 'scheduled')->whereDate('created_at', Carbon::today())->count());
        $completedCount = $this->safeCount(fn() => TripRequest::where('current_status', 'completed')->count());

        $zones = $this->safeQuery(fn() => Zone::where('is_active', true)->orderBy('name')->get());

        $scheduledTrips = $this->safeQuery(fn() => TripRequest::with(['customer', 'zone'])
            ->where('current_status', 'scheduled')
            ->whereDate('created_at', '>=', $from)
            ->whereDate('created_at', '<=', $to)
            ->when($zoneId, fn($q) => $q->where('zone_id', $zoneId))
            ->when($search, fn($q) => $q->where(function ($q2) use ($search) {
                $q2->where('ref_id', 'like', "%$search%")
                    ->orWhereHas('customer', fn($q3) => $q3->where('first_name', 'like', "%$search%")->orWhere('last_name', 'like', "%$search%"));
            }))
            ->orderByDesc('created_at')
            ->paginate(20));

        if (!is_object($scheduledTrips) || !method_exists($scheduledTrips, 'links')) {
            $scheduledTrips = new \Illuminate\Pagination\LengthAwarePaginator([], 0, 20);
        }

        return view('adminmodule::calendar.events-list', compact(
            'zones', 'scheduledTrips', 'totalScheduled', 'totalZones', 'scheduledToday', 'completedCount',
            'from', 'to', 'search', 'zoneId'
        ));
    }

    // ── 3) Create / Manage Events ─────────────────────────────────────────────

    public function manageEvents(Request $request)
    {
        $search = $request->input('search');
        $status = $request->input('status');

        $totalZones   = $this->safeCount(fn() => Zone::count());
        $activeZones  = $this->safeCount(fn() => Zone::where('is_active', true)->count());
        $scheduledTrips = $this->safeCount(fn() => TripRequest::where('current_status', 'scheduled')->count());
        $pendingTrips   = $this->safeCount(fn() => TripRequest::where('current_status', 'pending')->count());

        $zones = $this->safeQuery(fn() => Zone::when($search, fn($q) => $q->where('name', 'like', "%$search%"))
            ->when($status !== null && $status !== '', fn($q) => $q->where('is_active', (bool)(int)$status))
            ->orderBy('name')
            ->paginate(20));

        if (!is_object($zones) || !method_exists($zones, 'links')) {
            $zones = new \Illuminate\Pagination\LengthAwarePaginator([], 0, 20);
        }

        return view('adminmodule::calendar.manage-events', compact(
            'zones', 'totalZones', 'activeZones', 'scheduledTrips', 'pendingTrips', 'search', 'status'
        ));
    }

    // ── 4) Event Ride Planner ─────────────────────────────────────────────────

    public function eventRidePlanner(Request $request)
    {
        $from   = $request->input('date_from', Carbon::now()->subDays(7)->toDateString());
        $to     = $request->input('date_to', Carbon::now()->toDateString());
        $zoneId = $request->input('zone_id');
        $search = $request->input('search');

        $zones = $this->safeQuery(fn() => Zone::where('is_active', true)->orderBy('name')->get());

        $scheduledCount = $this->safeCount(fn() => TripRequest::where('current_status', 'scheduled')->count());
        $pendingCount   = $this->safeCount(fn() => TripRequest::where('current_status', 'pending')->count());
        $driversOnline  = $this->safeCount(fn() => \Modules\UserManagement\Entities\DriverDetail::where('is_online', true)->count());
        $driversAvail   = $this->safeCount(fn() => \Modules\UserManagement\Entities\DriverDetail::where('is_online', true)->where('availability_status', 'available')->count());

        $trips = $this->safeQuery(fn() => TripRequest::with(['customer', 'zone'])
            ->where('current_status', 'scheduled')
            ->whereDate('created_at', '>=', $from)
            ->whereDate('created_at', '<=', $to)
            ->when($zoneId, fn($q) => $q->where('zone_id', $zoneId))
            ->when($search, fn($q) => $q->where(function ($q2) use ($search) {
                $q2->where('ref_id', 'like', "%$search%")
                    ->orWhereHas('customer', fn($q3) => $q3->where('first_name', 'like', "%$search%")->orWhere('last_name', 'like', "%$search%"));
            }))
            ->orderByDesc('created_at')
            ->paginate(20));

        if (!is_object($trips) || !method_exists($trips, 'links')) {
            $trips = new \Illuminate\Pagination\LengthAwarePaginator([], 0, 20);
        }

        return view('adminmodule::calendar.event-ride-planner', compact(
            'zones', 'scheduledCount', 'pendingCount', 'driversOnline', 'driversAvail',
            'trips', 'from', 'to', 'zoneId', 'search'
        ));
    }

    // ── 5) Venues / Locations ─────────────────────────────────────────────────

    public function venues(Request $request)
    {
        $from   = $request->input('date_from');
        $to     = $request->input('date_to');
        $search = $request->input('search');
        $status = $request->input('status');

        $totalZones  = $this->safeCount(fn() => Zone::count());
        $activeZones = $this->safeCount(fn() => Zone::where('is_active', true)->count());
        $inactiveZones = $this->safeCount(fn() => Zone::where('is_active', false)->count());

        $zones = $this->safeQuery(fn() => Zone::when($search, fn($q) => $q->where('name', 'like', "%$search%"))
            ->when($status !== null && $status !== '', fn($q) => $q->where('is_active', (bool)(int)$status))
            ->when($from, fn($q) => $q->whereDate('created_at', '>=', $from))
            ->when($to, fn($q) => $q->whereDate('created_at', '<=', $to))
            ->orderBy('name')
            ->paginate(20));

        if (!is_object($zones) || !method_exists($zones, 'links')) {
            $zones = new \Illuminate\Pagination\LengthAwarePaginator([], 0, 20);
        }

        return view('adminmodule::calendar.venues', compact(
            'zones', 'totalZones', 'activeZones', 'inactiveZones', 'from', 'to', 'search', 'status'
        ));
    }

    // ── 6) Event Analytics ────────────────────────────────────────────────────

    public function eventAnalytics(Request $request)
    {
        $from = $request->input('date_from', $request->input('from', Carbon::now()->subDays(30)->toDateString()));
        $to   = $request->input('date_to', $request->input('to', Carbon::now()->toDateString()));
        $search = $request->input('search');

        $totalTrips     = $this->safeCount(fn() => TripRequest::whereDate('created_at', '>=', $from)->whereDate('created_at', '<=', $to)->count());
        $scheduledTrips = $this->safeCount(fn() => TripRequest::where('current_status', 'scheduled')->whereDate('created_at', '>=', $from)->whereDate('created_at', '<=', $to)->count());
        $completedTrips = $this->safeCount(fn() => TripRequest::where('current_status', 'completed')->whereDate('created_at', '>=', $from)->whereDate('created_at', '<=', $to)->count());
        $cancelledTrips = $this->safeCount(fn() => TripRequest::where('current_status', 'cancelled')->whereDate('created_at', '>=', $from)->whereDate('created_at', '<=', $to)->count());
        $zoneCount      = $this->safeCount(fn() => Zone::where('is_active', true)->count());

        $zoneBreakdown = $this->safeQuery(fn() => Zone::withCount(['tripRequest' => fn($q) =>
            $q->whereDate('created_at', '>=', $from)->whereDate('created_at', '<=', $to)
        ])->where('is_active', true)
            ->when($search, fn($q) => $q->where('name', 'like', "%$search%"))
            ->orderByDesc('trip_request_count')
            ->limit(50)
            ->get());

        $dailyTrend = $this->safeQuery(fn() => TripRequest::whereDate('created_at', '>=', $from)
            ->whereDate('created_at', '<=', $to)
            ->selectRaw("DATE(created_at) as date, count(*) as total")
            ->groupBy('date')->orderBy('date')->get());

        if (!is_iterable($zoneBreakdown)) {
            $zoneBreakdown = collect();
        }
        if (!is_iterable($dailyTrend)) {
            $dailyTrend = collect();
        }

        return view('adminmodule::calendar.event-analytics', compact(
            'totalTrips', 'scheduledTrips', 'completedTrips', 'cancelledTrips', 'zoneCount',
            'zoneBreakdown', 'dailyTrend', 'from', 'to', 'search'
        ));
    }
}
