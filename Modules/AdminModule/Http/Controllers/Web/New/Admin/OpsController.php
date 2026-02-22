<?php

namespace Modules\AdminModule\Http\Controllers\Web\New\Admin;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class OpsController extends Controller
{
    // ─── Live KPIs ───────────────────────────────────────────────────────────

    public function kpis()
    {
        $kpis        = $this->safeKpis();
        $lastUpdated = now();

        return view('adminmodule::ops.kpis', compact('kpis', 'lastUpdated'));
    }

    // ─── Alerts ──────────────────────────────────────────────────────────────

    public function alerts(Request $request)
    {
        $filters = [
            'from'   => $request->get('from'),
            'to'     => $request->get('to'),
            'type'   => $request->get('type'),
            'search' => $request->get('search'),
        ];

        $rows        = $this->safeAlerts($filters);
        $kpis        = $this->safeAlertKpis();
        $lastUpdated = now();

        return view('adminmodule::ops.alerts', compact('kpis', 'filters', 'rows', 'lastUpdated'));
    }

    // ─── Dispatch / Control Room ──────────────────────────────────────────────

    public function controlRoom(Request $request)
    {
        $filters = [
            'zone_id' => $request->get('zone_id'),
            'status'  => $request->get('status', 'ongoing'),
            'search'  => $request->get('search'),
        ];

        $rows        = $this->safeActiveTrips($filters);
        $kpis        = $this->safeDispatchKpis();
        $lastUpdated = now();

        try {
            $zones = \Modules\ZoneManagement\Entities\Zone::select('id', 'name')->orderBy('name')->get();
        } catch (\Throwable $e) {
            $zones = collect();
        }

        return view('adminmodule::ops.control-room', compact('kpis', 'filters', 'rows', 'lastUpdated', 'zones'));
    }

    // ─── Cancellations / No-Shows ─────────────────────────────────────────────

    public function cancellations(Request $request)
    {
        $filters = [
            'from'    => $request->get('from'),
            'to'      => $request->get('to'),
            'zone_id' => $request->get('zone_id'),
            'search'  => $request->get('search'),
        ];

        $rows        = $this->safeCancellations($filters);
        $kpis        = $this->safeCancellationKpis();
        $lastUpdated = now();

        try {
            $zones = \Modules\ZoneManagement\Entities\Zone::select('id', 'name')->orderBy('name')->get();
        } catch (\Throwable $e) {
            $zones = collect();
        }

        return view('adminmodule::ops.cancellations', compact('kpis', 'filters', 'rows', 'lastUpdated', 'zones'));
    }

    // ─── Support Inbox / Tickets ──────────────────────────────────────────────

    public function supportTickets(Request $request)
    {
        $filters = [
            'from'   => $request->get('from'),
            'to'     => $request->get('to'),
            'status' => $request->get('status'),
            'search' => $request->get('search'),
        ];

        // No tickets table yet — render safe empty page
        $rows        = collect();
        $kpis        = ['total' => 0, 'open' => 0, 'in_progress' => 0, 'resolved' => 0, 'avg_response_h' => 0];
        $lastUpdated = now();

        return view('adminmodule::ops.support-tickets', compact('kpis', 'filters', 'rows', 'lastUpdated'));
    }

    // ─── Private helpers ──────────────────────────────────────────────────────

    private function safeKpis(): array
    {
        try {
            $model = \Modules\TripManagement\Entities\TripRequest::query();

            return [
                'pending'   => (clone $model)->where('current_status', 'pending')->count(),
                'ongoing'   => (clone $model)->whereIn('current_status', ['accepted', 'ongoing', 'out_for_pickup', 'picked_up'])->count(),
                'completed' => (clone $model)->where('current_status', 'completed')->count(),
                'cancelled' => (clone $model)->where('current_status', 'cancelled')->count(),
                'today'     => (clone $model)->whereDate('created_at', today())->count(),
                'revenue'   => (clone $model)->where('current_status', 'completed')->whereDate('created_at', today())->sum('actual_fare'),
            ];
        } catch (\Throwable $e) {
            return ['pending' => 0, 'ongoing' => 0, 'completed' => 0, 'cancelled' => 0, 'today' => 0, 'revenue' => 0];
        }
    }

    private function safeAlertKpis(): array
    {
        try {
            $model = \Modules\TripManagement\Entities\SafetyAlert::query();
            return [
                'total'    => (clone $model)->count(),
                'pending'  => (clone $model)->where('status', 'pending')->count(),
                'resolved' => (clone $model)->where('status', 'resolved')->count(),
                'today'    => (clone $model)->whereDate('created_at', today())->count(),
            ];
        } catch (\Throwable $e) {
            return ['total' => 0, 'pending' => 0, 'resolved' => 0, 'today' => 0];
        }
    }

    private function safeAlerts(array $filters)
    {
        try {
            $q = \Modules\TripManagement\Entities\SafetyAlert::query()
                ->with(['trip:id,ref_id', 'customer:id,first_name,last_name'])
                ->orderByDesc('created_at');

            if (!empty($filters['from'])) {
                $q->whereDate('created_at', '>=', $filters['from']);
            }
            if (!empty($filters['to'])) {
                $q->whereDate('created_at', '<=', $filters['to']);
            }
            if (!empty($filters['type'])) {
                $q->where('status', $filters['type']);
            }

            return $q->paginate(25)->withQueryString();
        } catch (\Throwable $e) {
            return collect();
        }
    }

    private function safeDispatchKpis(): array
    {
        try {
            $model = \Modules\TripManagement\Entities\TripRequest::query();
            return [
                'active'    => (clone $model)->whereIn('current_status', ['accepted', 'ongoing', 'out_for_pickup', 'picked_up'])->count(),
                'pending'   => (clone $model)->where('current_status', 'pending')->count(),
                'completed' => (clone $model)->where('current_status', 'completed')->whereDate('updated_at', today())->count(),
                'cancelled' => (clone $model)->where('current_status', 'cancelled')->whereDate('updated_at', today())->count(),
            ];
        } catch (\Throwable $e) {
            return ['active' => 0, 'pending' => 0, 'completed' => 0, 'cancelled' => 0];
        }
    }

    private function safeActiveTrips(array $filters)
    {
        try {
            $statuses = ['accepted', 'ongoing', 'out_for_pickup', 'picked_up', 'pending'];
            if (!empty($filters['status']) && $filters['status'] !== 'all') {
                $statuses = [$filters['status']];
            }

            $q = \Modules\TripManagement\Entities\TripRequest::query()
                ->with(['customer:id,first_name,last_name,phone', 'driver:id,first_name,last_name,phone'])
                ->whereIn('current_status', $statuses)
                ->orderByDesc('updated_at');

            if (!empty($filters['zone_id'])) {
                $q->where('zone_id', $filters['zone_id']);
            }
            if (!empty($filters['search'])) {
                $q->where('ref_id', 'like', '%' . $filters['search'] . '%');
            }

            return $q->paginate(30)->withQueryString();
        } catch (\Throwable $e) {
            return collect();
        }
    }

    private function safeCancellationKpis(): array
    {
        try {
            $model = \Modules\TripManagement\Entities\TripRequest::query()->where('current_status', 'cancelled');
            return [
                'total'   => (clone $model)->count(),
                'today'   => (clone $model)->whereDate('updated_at', today())->count(),
                'week'    => (clone $model)->whereBetween('updated_at', [now()->startOfWeek(), now()->endOfWeek()])->count(),
                'revenue' => (clone $model)->sum('cancellation_fee'),
            ];
        } catch (\Throwable $e) {
            return ['total' => 0, 'today' => 0, 'week' => 0, 'revenue' => 0];
        }
    }

    private function safeCancellations(array $filters)
    {
        try {
            $q = \Modules\TripManagement\Entities\TripRequest::query()
                ->with(['customer:id,first_name,last_name,phone', 'driver:id,first_name,last_name'])
                ->where('current_status', 'cancelled')
                ->orderByDesc('updated_at');

            if (!empty($filters['from'])) {
                $q->whereDate('updated_at', '>=', $filters['from']);
            }
            if (!empty($filters['to'])) {
                $q->whereDate('updated_at', '<=', $filters['to']);
            }
            if (!empty($filters['zone_id'])) {
                $q->where('zone_id', $filters['zone_id']);
            }
            if (!empty($filters['search'])) {
                $q->where(function ($sq) use ($filters) {
                    $sq->where('ref_id', 'like', '%' . $filters['search'] . '%')
                       ->orWhere('trip_cancellation_reason', 'like', '%' . $filters['search'] . '%');
                });
            }

            return $q->paginate(25)->withQueryString();
        } catch (\Throwable $e) {
            return collect();
        }
    }
}
