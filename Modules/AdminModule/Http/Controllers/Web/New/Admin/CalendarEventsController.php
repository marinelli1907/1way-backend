<?php

namespace Modules\AdminModule\Http\Controllers\Web\New\Admin;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class CalendarEventsController extends Controller
{
    // ─── Calendar (admin view) ────────────────────────────────────────────────

    public function calendar(Request $request)
    {
        $kpis        = $this->safeScheduledKpis();
        $lastUpdated = now();

        // Build calendar events from scheduled TripRequests
        $calendarEvents = $this->safeCalendarEvents();

        return view('adminmodule::calendar-events.calendar', compact('kpis', 'lastUpdated', 'calendarEvents'));
    }

    // ─── Events List ──────────────────────────────────────────────────────────

    public function events(Request $request)
    {
        $filters = [
            'from'   => $request->get('from'),
            'to'     => $request->get('to'),
            'search' => $request->get('search'),
            'status' => $request->get('status'),
        ];

        // No events table yet — safe empty
        $rows        = collect();
        $kpis        = ['total' => 0, 'upcoming' => 0, 'active' => 0, 'past' => 0];
        $lastUpdated = now();

        return view('adminmodule::calendar-events.events', compact('kpis', 'filters', 'rows', 'lastUpdated'));
    }

    // ─── Create / Manage Events ───────────────────────────────────────────────

    public function manageEvents(Request $request)
    {
        $kpis        = ['total' => 0, 'upcoming' => 0, 'active' => 0, 'past' => 0];
        $rows        = collect();
        $filters     = [];
        $lastUpdated = now();

        return view('adminmodule::calendar-events.manage-events', compact('kpis', 'filters', 'rows', 'lastUpdated'));
    }

    // ─── Event Ride Planner ───────────────────────────────────────────────────

    public function eventRidePlanner(Request $request)
    {
        $filters = [
            'from'    => $request->get('from'),
            'to'      => $request->get('to'),
            'zone_id' => $request->get('zone_id'),
        ];

        // Pull scheduled rides as a proxy for event rides
        $rows        = $this->safeScheduledTrips($filters);
        $kpis        = $this->safeScheduledKpis();
        $lastUpdated = now();

        try {
            $zones = \Modules\ZoneManagement\Entities\Zone::select('id', 'name')->orderBy('name')->get();
        } catch (\Throwable $e) {
            $zones = collect();
        }

        return view('adminmodule::calendar-events.event-ride-planner', compact('kpis', 'filters', 'rows', 'lastUpdated', 'zones'));
    }

    // ─── Venues / Locations ───────────────────────────────────────────────────

    public function venues(Request $request)
    {
        $filters = [
            'search' => $request->get('search'),
        ];

        // No venues table yet — safe empty
        $rows        = collect();
        $kpis        = ['total' => 0, 'active' => 0, 'upcoming_events' => 0, 'zones' => 0];
        $lastUpdated = now();

        try {
            $zones = \Modules\ZoneManagement\Entities\Zone::select('id', 'name')->orderBy('name')->get();
        } catch (\Throwable $e) {
            $zones = collect();
        }

        return view('adminmodule::calendar-events.venues', compact('kpis', 'filters', 'rows', 'lastUpdated', 'zones'));
    }

    // ─── Event Analytics ──────────────────────────────────────────────────────

    public function eventAnalytics(Request $request)
    {
        $filters = [
            'from' => $request->get('from', now()->startOfMonth()->toDateString()),
            'to'   => $request->get('to', now()->toDateString()),
        ];

        $kpis        = ['events' => 0, 'rides' => 0, 'revenue' => 0, 'attendees' => 0];
        $rows        = collect();
        $lastUpdated = now();

        return view('adminmodule::calendar-events.event-analytics', compact('kpis', 'filters', 'rows', 'lastUpdated'));
    }

    // ─── Private helpers ──────────────────────────────────────────────────────

    private function safeScheduledKpis(): array
    {
        try {
            $model = \Modules\TripManagement\Entities\TripRequest::query()
                ->whereNotNull('rise_request_count');

            return [
                'scheduled' => \Modules\TripManagement\Entities\TripRequest::query()->whereDate('created_at', today())->count(),
                'pending'   => \Modules\TripManagement\Entities\TripRequest::query()->where('current_status', 'pending')->count(),
                'confirmed' => \Modules\TripManagement\Entities\TripRequest::query()->whereIn('current_status', ['accepted', 'ongoing'])->count(),
                'completed' => \Modules\TripManagement\Entities\TripRequest::query()->where('current_status', 'completed')->whereDate('updated_at', today())->count(),
            ];
        } catch (\Throwable $e) {
            return ['scheduled' => 0, 'pending' => 0, 'confirmed' => 0, 'completed' => 0];
        }
    }

    private function safeScheduledTrips(array $filters)
    {
        try {
            $q = \Modules\TripManagement\Entities\TripRequest::query()
                ->with(['customer:id,first_name,last_name,phone', 'driver:id,first_name,last_name'])
                ->orderByDesc('created_at');

            if (!empty($filters['from'])) {
                $q->whereDate('created_at', '>=', $filters['from']);
            }
            if (!empty($filters['to'])) {
                $q->whereDate('created_at', '<=', $filters['to']);
            }
            if (!empty($filters['zone_id'])) {
                $q->where('zone_id', $filters['zone_id']);
            }

            return $q->paginate(25)->withQueryString();
        } catch (\Throwable $e) {
            return collect();
        }
    }

    private function safeCalendarEvents(): array
    {
        try {
            return \Modules\TripManagement\Entities\TripRequest::query()
                ->selectRaw("id, ref_id, current_status, created_at, updated_at")
                ->whereIn('current_status', ['pending', 'accepted', 'ongoing', 'completed'])
                ->whereDate('created_at', '>=', now()->startOfMonth())
                ->whereDate('created_at', '<=', now()->endOfMonth())
                ->orderBy('created_at')
                ->limit(200)
                ->get()
                ->map(fn($t) => [
                    'id'    => $t->id,
                    'title' => 'Trip ' . $t->ref_id,
                    'start' => $t->created_at?->toIso8601String(),
                    'color' => match($t->current_status) {
                        'pending'   => '#f59e0b',
                        'accepted'  => '#3b82f6',
                        'ongoing'   => '#10b981',
                        'completed' => '#6b7280',
                        default     => '#8b5cf6',
                    },
                ])
                ->toArray();
        } catch (\Throwable $e) {
            return [];
        }
    }
}
