@section('title', 'Event Analytics')
@extends('adminmodule::layouts.master')

@section('content')
<div class="container-fluid">

    {{-- HEADER --}}
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <nav aria-label="breadcrumb"><ol class="breadcrumb mb-1">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.events.index') }}">Events</a></li>
                <li class="breadcrumb-item active">Event Analytics</li>
            </ol></nav>
            <h3 class="mb-0 fw-bold">Event Analytics</h3>
            <div class="text-muted small">Analyze trip performance and trends</div>
        </div>
        <div>
            <button class="btn btn-sm btn-outline-secondary" disabled title="Export CSV functionality coming soon">
                <i class="bi bi-download"></i> Export CSV
            </button>
        </div>
    </div>

    {{-- FILTERS --}}
    <div class="card oneway-card mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-5">
                    <label class="form-label small">From Date</label>
                    <input type="date" name="from" value="{{ $from }}" class="form-control form-control-sm">
                </div>
                <div class="col-md-5">
                    <label class="form-label small">To Date</label>
                    <input type="date" name="to" value="{{ $to }}" class="form-control form-control-sm">
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-sm btn-primary w-100">Filter</button>
                </div>
            </form>
        </div>
    </div>

    {{-- KPI CARDS --}}
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-3">
            <div class="card oneway-card p-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="oneway-kpi__icon"><i class="bi bi-car-front-fill"></i></div>
                    <div>
                        <div class="fw-bold fs-3">{{ $totalTrips }}</div>
                        <div class="oneway-kpi__label">Total Trips</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card oneway-card p-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="oneway-kpi__icon"><i class="bi bi-calendar-check text-info"></i></div>
                    <div>
                        <div class="fw-bold fs-3">{{ $scheduledTrips }}</div>
                        <div class="oneway-kpi__label">Scheduled</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card oneway-card p-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="oneway-kpi__icon"><i class="bi bi-check-circle text-success"></i></div>
                    <div>
                        <div class="fw-bold fs-3">{{ $completedTrips }}</div>
                        <div class="oneway-kpi__label">Completed</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card oneway-card p-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="oneway-kpi__icon"><i class="bi bi-x-circle text-danger"></i></div>
                    <div>
                        <div class="fw-bold fs-3">{{ $cancelledTrips }}</div>
                        <div class="oneway-kpi__label">Cancelled</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ZONE BREAKDOWN --}}
    <div class="row mb-4">
        <div class="col-lg-6">
            <div class="card oneway-card">
                <div class="card-header bg-transparent border-0">
                    <h5 class="mb-0 fw-semibold">Trips by Zone</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Zone</th>
                                    <th class="text-end">Trip Count</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($zoneBreakdown as $zone)
                                <tr>
                                    <td>{{ $zone->name ?? '—' }}</td>
                                    <td class="text-end fw-semibold">{{ $zone->trip_request_count ?? 0 }}</td>
                                </tr>
                                @empty
                                <tr><td colspan="2" class="text-center text-muted py-4">No zone data available</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card oneway-card">
                <div class="card-header bg-transparent border-0">
                    <h5 class="mb-0 fw-semibold">Daily Trend</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Date</th>
                                    <th class="text-end">Trips</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($dailyTrend as $day)
                                <tr>
                                    <td>{{ \Carbon\Carbon::parse($day->date)->format('M j, Y') }}</td>
                                    <td class="text-end fw-semibold">{{ $day->total ?? 0 }}</td>
                                </tr>
                                @empty
                                <tr><td colspan="2" class="text-center text-muted py-4">No trend data available</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- SUMMARY STATS --}}
    <div class="card oneway-card">
        <div class="card-header bg-transparent border-0">
            <h5 class="mb-0 fw-semibold">Performance Summary</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-4 text-center py-3 border-end">
                    <div class="fs-2 fw-bold text-success">{{ $totalTrips > 0 ? round(($completedTrips / $totalTrips) * 100, 1) : 0 }}%</div>
                    <div class="text-muted small">Completion Rate</div>
                </div>
                <div class="col-md-4 text-center py-3 border-end">
                    <div class="fs-2 fw-bold text-info">{{ $totalTrips > 0 ? round(($scheduledTrips / $totalTrips) * 100, 1) : 0 }}%</div>
                    <div class="text-muted small">Scheduled Rate</div>
                </div>
                <div class="col-md-4 text-center py-3">
                    <div class="fs-2 fw-bold text-danger">{{ $totalTrips > 0 ? round(($cancelledTrips / $totalTrips) * 100, 1) : 0 }}%</div>
                    <div class="text-muted small">Cancellation Rate</div>
                </div>
            </div>
        </div>
    </div>

    <div class="text-muted small mt-3">Last updated: {{ now()->format('M j, Y g:i A') }}</div>
</div>
@endsection
