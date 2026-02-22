@section('title', 'Events List')
@extends('adminmodule::layouts.master')

@section('content')
<div class="container-fluid py-4">

    {{-- PAGE HEADER --}}
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h3 class="mb-1 fw-bold"><i class="bi bi-list-ul text-primary me-2"></i>Events List</h3>
            <div class="text-muted small">All scheduled events and group rides &mdash; {{ $lastUpdated->format('d M Y') }}</div>
        </div>
        <div class="d-flex gap-2">
            @if(Route::has('admin.events.manage'))
            <a href="{{ route('admin.events.manage') }}" class="btn btn-sm btn-primary">
                <i class="bi bi-plus-circle me-1"></i> Create Event
            </a>
            @endif
        </div>
    </div>

    {{-- KPI CARDS --}}
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="card oneway-card p-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="oneway-kpi__icon" style="background:rgba(107,114,128,.12);color:#6b7280;">
                        <i class="bi bi-calendar-event"></i>
                    </div>
                    <div>
                        <div class="fw-bold fs-4">{{ number_format($kpis['total']) }}</div>
                        <div class="oneway-kpi__label">Total Events</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card oneway-card p-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="oneway-kpi__icon" style="background:rgba(59,130,246,.12);color:#3b82f6;">
                        <i class="bi bi-calendar-plus"></i>
                    </div>
                    <div>
                        <div class="fw-bold fs-4">{{ number_format($kpis['upcoming']) }}</div>
                        <div class="oneway-kpi__label">Upcoming</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card oneway-card p-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="oneway-kpi__icon" style="background:rgba(16,185,129,.12);color:#10b981;">
                        <i class="bi bi-play-circle"></i>
                    </div>
                    <div>
                        <div class="fw-bold fs-4">{{ number_format($kpis['active']) }}</div>
                        <div class="oneway-kpi__label">Active Now</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card oneway-card p-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="oneway-kpi__icon" style="background:rgba(107,114,128,.12);color:#9ca3af;">
                        <i class="bi bi-archive"></i>
                    </div>
                    <div>
                        <div class="fw-bold fs-4">{{ number_format($kpis['past']) }}</div>
                        <div class="oneway-kpi__label">Past</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- FILTERS --}}
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
            <div class="col-md-2">
                <label class="form-label small fw-semibold">Status</label>
                <select name="status" class="form-select form-select-sm">
                    <option value="">All</option>
                    <option value="upcoming"  @selected(($filters['status']??'')=='upcoming')>Upcoming</option>
                    <option value="active"    @selected(($filters['status']??'')=='active')>Active</option>
                    <option value="past"      @selected(($filters['status']??'')=='past')>Past</option>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label small fw-semibold">Search</label>
                <input type="text" name="search" class="form-control form-control-sm" placeholder="Event name, venue..." value="{{ $filters['search'] ?? '' }}">
            </div>
            <div class="col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-primary btn-sm px-3">Filter</button>
                <a href="{{ route('admin.events.index') }}" class="btn btn-outline-secondary btn-sm">Clear</a>
            </div>
        </div>
    </form>

    {{-- NOTICE --}}
    <div class="alert alert-info d-flex align-items-center gap-2 mb-4" role="alert">
        <i class="bi bi-info-circle-fill fs-5"></i>
        <div>
            <strong>Events module launching soon.</strong>
            Create and manage events, then link rides to them via the Event Ride Planner.
        </div>
    </div>

    {{-- TABLE --}}
    <div class="card oneway-card">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Event Name</th>
                        <th>Venue</th>
                        <th>Date / Time</th>
                        <th>Rides Linked</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td colspan="7" class="text-center py-5">
                            <div class="text-muted">
                                <i class="bi bi-calendar-x fs-2 d-block mb-2"></i>
                                No events yet.
                                @if(Route::has('admin.events.manage'))
                                    <a href="{{ route('admin.events.manage') }}">Create your first event</a>
                                @endif
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
