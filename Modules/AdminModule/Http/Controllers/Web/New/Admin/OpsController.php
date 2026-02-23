<?php

namespace Modules\AdminModule\Http\Controllers\Web\New\Admin;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Modules\TripManagement\Entities\TripRequest;
use Modules\TripManagement\Entities\SafetyAlert;
use Modules\BusinessManagement\Entities\CancellationReason;
use Modules\UserManagement\Entities\User;
use Modules\UserManagement\Entities\DriverDetail;
use Modules\ChattingManagement\Entities\ChannelList;
use Modules\ZoneManagement\Entities\Zone;

class OpsController extends Controller
{
    use AuthorizesRequests;

    // ── helpers ───────────────────────────────────────────────────────────────

    private function safeCount($callable, int $default = 0): int
    {
        try { return (int) $callable(); } catch (\Throwable $e) { return $default; }
    }

    private function safeQuery($callable, $default = null)
    {
        try { return $callable(); } catch (\Throwable $e) { return $default ?? collect(); }
    }

    private function dateFilter(Request $request): array
    {
        $from = $request->input('from', Carbon::now()->subDays(30)->toDateString());
        $to   = $request->input('to',   Carbon::now()->toDateString());
        return [$from, $to];
    }

    // ── 1) Live KPIs ─────────────────────────────────────────────────────────

    public function kpis(Request $request)
    {
        [$from, $to] = $this->dateFilter($request);

        $totalTrips      = $this->safeCount(fn() => TripRequest::whereDate('created_at', '>=', $from)->whereDate('created_at', '<=', $to)->count());
        $completedTrips  = $this->safeCount(fn() => TripRequest::where('current_status', 'completed')->whereDate('created_at', '>=', $from)->whereDate('created_at', '<=', $to)->count());
        $cancelledTrips  = $this->safeCount(fn() => TripRequest::where('current_status', 'cancelled')->whereDate('created_at', '>=', $from)->whereDate('created_at', '<=', $to)->count());
        $ongoingTrips    = $this->safeCount(fn() => TripRequest::whereIn('current_status', ['accepted', 'ongoing', 'picking_up'])->count());
        $activeDrivers   = $this->safeCount(fn() => DriverDetail::where('is_online', true)->count());
        $totalDrivers    = $this->safeCount(fn() => User::where('user_type', 'driver')->where('is_active', true)->count());
        $totalCustomers  = $this->safeCount(fn() => User::where('user_type', 'customer')->where('is_active', true)->count());
        $completionRate  = $totalTrips > 0 ? round(($completedTrips / $totalTrips) * 100, 1) : 0;

        $recentTrips = $this->safeQuery(fn() => TripRequest::with(['customer', 'driver'])
            ->orderByDesc('created_at')->limit(10)->get());

        return view('adminmodule::ops.kpis', compact(
            'totalTrips', 'completedTrips', 'cancelledTrips', 'ongoingTrips',
            'activeDrivers', 'totalDrivers', 'totalCustomers', 'completionRate',
            'recentTrips', 'from', 'to'
        ));
    }

    // ── 2) Alerts ─────────────────────────────────────────────────────────────

    public function alerts(Request $request)
    {
        [$from, $to] = $this->dateFilter($request);
        $status = $request->input('status');

        $totalAlerts    = $this->safeCount(fn() => SafetyAlert::count());
        $openAlerts     = $this->safeCount(fn() => SafetyAlert::where('is_active', true)->count());
        $resolvedAlerts = $this->safeCount(fn() => SafetyAlert::where('is_active', false)->count());
        $todayAlerts    = $this->safeCount(fn() => SafetyAlert::whereDate('created_at', today())->count());

        $query = $this->safeQuery(fn() => SafetyAlert::with(['tripRequest', 'tripRequest.customer', 'tripRequest.driver'])
            ->whereDate('created_at', '>=', $from)
            ->whereDate('created_at', '<=', $to)
            ->when($status !== null && $status !== '', fn($q) => $q->where('is_active', (bool)$status))
            ->orderByDesc('created_at')
            ->paginate(20));

        return view('adminmodule::ops.alerts', compact(
            'totalAlerts', 'openAlerts', 'resolvedAlerts', 'todayAlerts',
            'query', 'from', 'to', 'status'
        ));
    }

    // ── 3) Dispatch / Control Room ─────────────────────────────────────────────

    public function controlRoom(Request $request)
    {
        $ongoingTrips  = $this->safeQuery(fn() => TripRequest::with(['customer', 'driver', 'coordinate'])
            ->whereIn('current_status', ['accepted', 'ongoing', 'picking_up', 'reached'])
            ->orderByDesc('updated_at')->limit(50)->get());

        $pendingTrips  = $this->safeQuery(fn() => TripRequest::with(['customer'])
            ->where('current_status', 'pending')
            ->orderByDesc('created_at')->limit(20)->get());

        $activeDrivers = $this->safeQuery(fn() => DriverDetail::with('user')
            ->where('is_online', true)->get());

        $ongoingCount = $this->safeCount(fn() => TripRequest::whereIn('current_status', ['accepted', 'ongoing', 'picking_up', 'reached'])->count());
        $pendingCount = $this->safeCount(fn() => TripRequest::where('current_status', 'pending')->count());
        $scheduledTodayCount = $this->safeCount(fn() => TripRequest::where('current_status', 'scheduled')->whereDate('created_at', Carbon::today())->count());
        $onlineDriverCount = $this->safeCount(fn() => DriverDetail::where('is_online', true)->count());
        $availableDriverCount = $this->safeCount(fn() => DriverDetail::where('is_online', true)->where('availability_status', 'available')->count());

        $recentActions = $this->safeQuery(fn() => TripRequest::with(['customer', 'driver'])
            ->orderByDesc('updated_at')->limit(50)->get());

        return view('adminmodule::ops.control-room', compact(
            'ongoingTrips', 'pendingTrips', 'activeDrivers', 'recentActions',
            'ongoingCount', 'pendingCount', 'scheduledTodayCount', 'onlineDriverCount', 'availableDriverCount'
        ));
    }

    // ── 4) Cancellations / No-Shows ────────────────────────────────────────────

    public function cancellations(Request $request)
    {
        $from = $request->input('date_from', $request->input('from', Carbon::now()->subDays(30)->toDateString()));
        $to   = $request->input('date_to', $request->input('to', Carbon::now()->toDateString()));
        $cancelledBy = $request->input('cancelled_by');
        $zoneId      = $request->input('zone_id');
        $search      = $request->input('search');

        $totalCancelled  = $this->safeCount(fn() => TripRequest::where('current_status', 'cancelled')->count());
        $cancelledToday  = $this->safeCount(fn() => TripRequest::where('current_status', 'cancelled')->whereDate('updated_at', Carbon::today())->count());
        $topReasonRaw = $this->safeQuery(fn() => TripRequest::where('current_status', 'cancelled')
            ->whereNotNull('trip_cancellation_reason')->where('trip_cancellation_reason', '!=', '')
            ->selectRaw('trip_cancellation_reason, count(*) as cnt')
            ->groupBy('trip_cancellation_reason')->orderByDesc('cnt')->limit(1)->value('trip_cancellation_reason'));
        $topReason = is_string($topReasonRaw) ? $topReasonRaw : null;
        $total7d   = $this->safeCount(fn() => TripRequest::whereDate('created_at', '>=', Carbon::now()->subDays(7))->count());
        $cancelled7d = $this->safeCount(fn() => TripRequest::where('current_status', 'cancelled')->whereDate('updated_at', '>=', Carbon::now()->subDays(7))->count());
        $cancelRate7d = ($total7d > 0) ? round(($cancelled7d / $total7d) * 100, 1) : 0;

        $zones = $this->safeQuery(fn() => Zone::where('is_active', true)->orderBy('name')->get());

        $trips = $this->safeQuery(fn() => TripRequest::with(['customer', 'driver', 'fee', 'zone'])
            ->where('current_status', 'cancelled')
            ->whereDate('updated_at', '>=', $from)
            ->whereDate('updated_at', '<=', $to)
            ->when($cancelledBy, fn($q) => $q->whereHas('fee', fn($q2) => $q2->where('cancelled_by', $cancelledBy)))
            ->when($zoneId, fn($q) => $q->where('zone_id', $zoneId))
            ->when($search, fn($q) => $q->where(function($q2) use ($search) {
                $q2->whereHas('customer', fn($q3) => $q3->where('first_name', 'like', "%$search%")->orWhere('last_name', 'like', "%$search%"))
                   ->orWhere('ref_id', 'like', "%$search%");
            }))
            ->orderByDesc('updated_at')
            ->paginate(20));

        if (!is_object($trips) || !method_exists($trips, 'links')) {
            $trips = new \Illuminate\Pagination\LengthAwarePaginator([], 0, 20);
        }

        return view('adminmodule::ops.cancellations', compact(
            'totalCancelled', 'cancelledToday', 'topReason', 'cancelRate7d',
            'zones', 'trips', 'from', 'to', 'cancelledBy', 'zoneId', 'search'
        ));
    }

    // ── 5) Support Inbox / Tickets ──────────────────────────────────────────────

    public function supportTickets(Request $request)
    {
        $search = $request->input('search');
        $status = $request->input('status');

        // Support is handled via ChattingManagement (ChannelList model)
        $totalChannels  = $this->safeCount(fn() => ChannelList::count());
        $openChannels   = $this->safeCount(fn() => ChannelList::where('is_active', true)->count());
        $closedChannels = $this->safeCount(fn() => ChannelList::where('is_active', false)->count());
        $todayChannels  = $this->safeCount(fn() => ChannelList::whereDate('created_at', today())->count());

        $channels = $this->safeQuery(fn() => ChannelList::with(['channelUsers.user'])
            ->when($status !== null && $status !== '', fn($q) => $q->where('is_active', (bool)$status))
            ->when($search, fn($q) => $q->where('title', 'like', "%$search%"))
            ->orderByDesc('updated_at')
            ->paginate(20));

        return view('adminmodule::ops.support-tickets', compact(
            'totalChannels', 'openChannels', 'closedChannels', 'todayChannels',
            'channels', 'search', 'status'
        ));
    }
}
