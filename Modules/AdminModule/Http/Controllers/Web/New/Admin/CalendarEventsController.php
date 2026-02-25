<?php

namespace Modules\AdminModule\Http\Controllers\Web\New\Admin;

use App\Models\Event;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Modules\TripManagement\Entities\TripRequest;
use Modules\ZoneManagement\Entities\Zone;
use Modules\UserManagement\Entities\User;

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

        $calendarEvents = $this->safeQuery(fn() => Event::where('is_active', true)
            ->where(function ($q) use ($year, $month) {
                $startOfMonth = Carbon::create($year, $month, 1)->startOfDay();
                $endOfMonth = Carbon::create($year, $month, 1)->endOfMonth()->endOfDay();
                $q->whereBetween('start_at', [$startOfMonth, $endOfMonth])
                  ->orWhereBetween('end_at', [$startOfMonth, $endOfMonth])
                  ->orWhere(function ($q2) use ($startOfMonth, $endOfMonth) {
                      $q2->where('start_at', '<=', $startOfMonth)
                         ->where('end_at', '>=', $endOfMonth);
                  });
            })
            ->orderBy('start_at')
            ->get());

        $eventsByDate = collect();
        if ($calendarEvents && $calendarEvents->count()) {
            foreach ($calendarEvents as $event) {
                $start = $event->start_at->copy()->max(Carbon::create($year, $month, 1));
                $end = $event->end_at->copy()->min(Carbon::create($year, $month, 1)->endOfMonth());
                for ($d = $start->copy(); $d->lte($end); $d->addDay()) {
                    $key = $d->toDateString();
                    if (!$eventsByDate->has($key)) {
                        $eventsByDate[$key] = collect();
                    }
                    $eventsByDate[$key]->push($event);
                }
            }
        }

        $daysInMonth = Carbon::create($year, $month, 1)->daysInMonth;
        $firstDayOfWeek = Carbon::create($year, $month, 1)->dayOfWeek;

        $totalScheduled   = $this->safeCount(fn() => TripRequest::where('current_status', 'scheduled')->count());
        $thisMonthTrips    = $this->safeCount(fn() => TripRequest::whereYear('created_at', $year)->whereMonth('created_at', $month)->count());
        $thisMonthComplete = $this->safeCount(fn() => TripRequest::where('current_status', 'completed')->whereYear('created_at', $year)->whereMonth('created_at', $month)->count());
        $totalEvents       = $this->safeCount(fn() => Event::where('is_active', true)->count());

        return view('adminmodule::calendar.calendar', compact(
            'scheduledTrips', 'eventsByDate', 'daysInMonth', 'firstDayOfWeek',
            'month', 'year', 'totalScheduled', 'thisMonthTrips', 'thisMonthComplete', 'totalEvents'
        ));
    }

    // ── 2) Events List ────────────────────────────────────────────────────────

    public function eventsList(Request $request)
    {
        $from   = $request->input('date_from', Carbon::now()->subDays(30)->toDateString());
        $to     = $request->input('date_to', Carbon::now()->addDays(90)->toDateString());
        $search = $request->input('search');
        $visibility = $request->input('visibility');
        $status = $request->input('status');

        $totalEvents    = $this->safeCount(fn() => Event::count());
        $activeEvents   = $this->safeCount(fn() => Event::where('is_active', true)->count());
        $upcomingEvents = $this->safeCount(fn() => Event::where('is_active', true)->where('start_at', '>=', now())->count());
        $promotedEvents = $this->safeCount(fn() => Event::where('is_promoted', true)->where('is_active', true)->count());

        $events = $this->safeQuery(fn() => Event::query()
            ->when($search, fn($q) => $q->where('title', 'like', "%$search%"))
            ->when($visibility, fn($q) => $q->where('visibility', $visibility))
            ->when($status !== null && $status !== '', fn($q) => $q->where('is_active', (bool)(int)$status))
            ->when($from, fn($q) => $q->where('start_at', '>=', $from))
            ->when($to, fn($q) => $q->where('start_at', '<=', Carbon::parse($to)->endOfDay()))
            ->orderByDesc('start_at')
            ->paginate(20));

        if (!is_object($events) || !method_exists($events, 'links')) {
            $events = new \Illuminate\Pagination\LengthAwarePaginator([], 0, 20);
        }

        return view('adminmodule::calendar.events-list', compact(
            'events', 'totalEvents', 'activeEvents', 'upcomingEvents', 'promotedEvents',
            'from', 'to', 'search', 'visibility', 'status'
        ));
    }

    // ── 3) Create / Manage Events ─────────────────────────────────────────────

    public function manageEvents(Request $request)
    {
        $search = $request->input('search');
        $status = $request->input('status');

        $totalEvents    = $this->safeCount(fn() => Event::count());
        $activeEvents   = $this->safeCount(fn() => Event::where('is_active', true)->count());
        $upcomingEvents = $this->safeCount(fn() => Event::where('is_active', true)->where('start_at', '>=', now())->count());
        $promotedEvents = $this->safeCount(fn() => Event::where('is_promoted', true)->where('is_active', true)->count());

        $events = $this->safeQuery(fn() => Event::query()
            ->when($search, fn($q) => $q->where('title', 'like', "%$search%"))
            ->when($status !== null && $status !== '', fn($q) => $q->where('is_active', (bool)(int)$status))
            ->orderByDesc('created_at')
            ->paginate(20));

        if (!is_object($events) || !method_exists($events, 'links')) {
            $events = new \Illuminate\Pagination\LengthAwarePaginator([], 0, 20);
        }

        return view('adminmodule::calendar.manage-events', compact(
            'events', 'totalEvents', 'activeEvents', 'upcomingEvents', 'promotedEvents', 'search', 'status'
        ));
    }

    // ── Store Event ───────────────────────────────────────────────────────────

    public function storeEvent(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:2000',
            'start_at' => 'required|date',
            'end_at' => 'required|date|after:start_at',
            'timezone' => 'nullable|string|max:50',
            'visibility' => 'required|in:public,private',
            'private_code' => 'nullable|required_if:visibility,private|string|max:50',
        ]);

        $data['created_by'] = auth()->id();
        $data['timezone'] = $data['timezone'] ?? 'America/New_York';

        Event::create($data);

        \Brian2694\Toastr\Facades\Toastr::success('Event created successfully.');
        return redirect()->route('admin.calendar.index');
    }

    // ── Update Event ──────────────────────────────────────────────────────────

    public function updateEvent(Request $request, string $id)
    {
        $event = Event::findOrFail($id);

        $data = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:2000',
            'start_at' => 'required|date',
            'end_at' => 'required|date|after:start_at',
            'timezone' => 'nullable|string|max:50',
            'visibility' => 'required|in:public,private',
            'private_code' => 'nullable|required_if:visibility,private|string|max:50',
        ]);

        $event->update($data);

        \Brian2694\Toastr\Facades\Toastr::success('Event updated successfully.');
        return redirect()->route('admin.events.manage');
    }

    // ── Toggle Active Status ──────────────────────────────────────────────────

    public function toggleEventStatus(Request $request)
    {
        $request->validate(['event_id' => 'required|exists:events,id']);
        $event = Event::findOrFail($request->event_id);
        $event->update(['is_active' => !$event->is_active]);

        \Brian2694\Toastr\Facades\Toastr::success('Event status updated.');
        return redirect()->back();
    }

    // ── Toggle Promoted ───────────────────────────────────────────────────────

    public function togglePromoted(Request $request)
    {
        $request->validate(['event_id' => 'required|exists:events,id']);
        $event = Event::findOrFail($request->event_id);
        $event->update(['is_promoted' => !$event->is_promoted]);

        \Brian2694\Toastr\Facades\Toastr::success('Event promotion status updated.');
        return redirect()->back();
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
