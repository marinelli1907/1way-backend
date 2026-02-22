@section('title', 'Event Analytics')
@extends('adminmodule::layouts.master')

@section('content')
<div class="container-fluid py-4">

    {{-- PAGE HEADER --}}
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h3 class="mb-1 fw-bold"><i class="bi bi-graph-up text-primary me-2"></i>Event Analytics</h3>
            <div class="text-muted small">Performance metrics for events and linked rides &mdash; {{ $lastUpdated->format('d M Y') }}</div>
        </div>
        <button class="btn btn-sm btn-outline-success" disabled title="Export not yet available">
            <i class="bi bi-download me-1"></i> Export Report
        </button>
    </div>

    {{-- DATE FILTER --}}
    <form method="GET" class="card oneway-card p-3 mb-4">
        <div class="row g-2 align-items-end">
            <div class="col-md-2">
                <label class="form-label small fw-semibold">From</label>
                <input type="date" name="from" class="form-control form-control-sm" value="{{ $filters['from'] ?? '' }}">
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-semibold">To</label>
                <input type="date" name="to" class="form-control form-control-sm" value="{{ $filters['to'] ?? '' }}">
            </div>
            <div class="col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-primary btn-sm px-3">Apply</button>
                <a href="{{ route('admin.event-analytics.index') }}" class="btn btn-outline-secondary btn-sm">Reset</a>
            </div>
        </div>
    </form>

    {{-- KPI CARDS --}}
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="card oneway-card p-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="oneway-kpi__icon" style="background:rgba(59,130,246,.12);color:#3b82f6;">
                        <i class="bi bi-calendar-event"></i>
                    </div>
                    <div>
                        <div class="fw-bold fs-4">{{ number_format($kpis['events']) }}</div>
                        <div class="oneway-kpi__label">Events</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card oneway-card p-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="oneway-kpi__icon" style="background:rgba(16,185,129,.12);color:#10b981;">
                        <i class="bi bi-car-front"></i>
                    </div>
                    <div>
                        <div class="fw-bold fs-4">{{ number_format($kpis['rides']) }}</div>
                        <div class="oneway-kpi__label">Rides Taken</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card oneway-card p-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="oneway-kpi__icon" style="background:rgba(245,158,11,.12);color:#f59e0b;">
                        <i class="bi bi-currency-dollar"></i>
                    </div>
                    <div>
                        <div class="fw-bold fs-4">${{ number_format($kpis['revenue']) }}</div>
                        <div class="oneway-kpi__label">Revenue</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card oneway-card p-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="oneway-kpi__icon" style="background:rgba(139,92,246,.12);color:#8b5cf6;">
                        <i class="bi bi-people"></i>
                    </div>
                    <div>
                        <div class="fw-bold fs-4">{{ number_format($kpis['attendees']) }}</div>
                        <div class="oneway-kpi__label">Attendees</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- NOTICE --}}
    <div class="alert alert-info d-flex align-items-center gap-2 mb-4" role="alert">
        <i class="bi bi-info-circle-fill fs-5"></i>
        <div>
            <strong>Analytics data will populate here once events are created.</strong>
            Charts and breakdowns per event, venue, and zone will appear automatically.
        </div>
    </div>

    {{-- PLACEHOLDER CHARTS --}}
    <div class="row g-3">
        <div class="col-md-8">
            <div class="card oneway-card p-4">
                <h6 class="fw-semibold mb-3">Rides per Event (last 30 days)</h6>
                <div class="d-flex align-items-center justify-content-center text-muted" style="height:200px;">
                    <div class="text-center">
                        <i class="bi bi-bar-chart fs-1 d-block mb-2 opacity-25"></i>
                        <span class="small">No event data yet</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card oneway-card p-4">
                <h6 class="fw-semibold mb-3">Top Venues by Rides</h6>
                <div class="d-flex align-items-center justify-content-center text-muted" style="height:200px;">
                    <div class="text-center">
                        <i class="bi bi-pie-chart fs-1 d-block mb-2 opacity-25"></i>
                        <span class="small">No venue data yet</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ANALYTICS TABLE --}}
    <div class="card oneway-card mt-3">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Event</th>
                        <th>Venue</th>
                        <th>Date</th>
                        <th>Rides</th>
                        <th>Revenue</th>
                        <th>Avg Fare</th>
                        <th>Cancellations</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td colspan="7" class="text-center py-5">
                            <div class="text-muted">
                                <i class="bi bi-graph-up fs-2 d-block mb-2"></i>
                                No event analytics yet.
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
