@section('title', 'Events List')
@extends('adminmodule::layouts.master')

@section('content')
<div class="container-fluid">

    {{-- HEADER --}}
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <nav aria-label="breadcrumb"><ol class="breadcrumb mb-1">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item active">Events List</li>
            </ol></nav>
            <h3 class="mb-0 fw-bold">Events List</h3>
            <div class="text-muted small">View and manage scheduled events and trips</div>
        </div>
        <div>
            <a href="{{ route('admin.events.manage') }}" class="btn btn-sm btn-primary">
                <i class="bi bi-plus-circle"></i> Create Event
            </a>
        </div>
    </div>

    {{-- KPI CARDS --}}
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-3">
            <div class="card oneway-card p-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="oneway-kpi__icon"><i class="bi bi-calendar-check"></i></div>
                    <div>
                        <div class="fw-bold fs-3">{{ $totalScheduled ?? 0 }}</div>
                        <div class="oneway-kpi__label">Scheduled Trips</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card oneway-card p-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="oneway-kpi__icon"><i class="bi bi-geo-alt"></i></div>
                    <div>
                        <div class="fw-bold fs-3">{{ $totalZones ?? 0 }}</div>
                        <div class="oneway-kpi__label">Active Zones</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card oneway-card p-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="oneway-kpi__icon"><i class="bi bi-calendar-day text-info"></i></div>
                    <div>
                        <div class="fw-bold fs-3">{{ $scheduledToday ?? 0 }}</div>
                        <div class="oneway-kpi__label">Scheduled Today</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card oneway-card p-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="oneway-kpi__icon"><i class="bi bi-check-circle text-success"></i></div>
                    <div>
                        <div class="fw-bold fs-3">{{ $completedCount ?? 0 }}</div>
                        <div class="oneway-kpi__label">Total Completed</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- FILTERS --}}
    <div class="card oneway-card mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-2">
                    <label class="form-label small">From Date</label>
                    <input type="date" name="date_from" value="{{ $from ?? '' }}" class="form-control form-control-sm">
                </div>
                <div class="col-md-2">
                    <label class="form-label small">To Date</label>
                    <input type="date" name="date_to" value="{{ $to ?? '' }}" class="form-control form-control-sm">
                </div>
                <div class="col-md-2">
                    <label class="form-label small">Zone</label>
                    <select name="zone_id" class="form-select form-select-sm">
                        <option value="">All zones</option>
                        @foreach($zones ?? [] as $z)
                        <option value="{{ $z->id }}" {{ ($zoneId ?? '') == $z->id ? 'selected' : '' }}>{{ $z->name ?? $z->id }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label small">Search</label>
                    <input type="text" name="search" value="{{ $search ?? '' }}" placeholder="Customer or Trip ID" class="form-control form-control-sm">
                </div>
                <div class="col-12">
                    <button type="submit" class="btn btn-sm btn-primary">Apply Filters</button>
                    <a href="{{ route('admin.events.index') }}" class="btn btn-sm btn-outline-secondary">Clear</a>
                </div>
            </form>
        </div>
    </div>

    {{-- EVENTS TABLE --}}
    <div class="card oneway-card">
        <div class="card-header bg-transparent border-0">
            <h5 class="mb-0 fw-semibold">Scheduled Trips</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Trip ID</th>
                            <th>Customer</th>
                            <th>Pickup Location</th>
                            <th>Dropoff Location</th>
                            <th>Scheduled Time</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($scheduledTrips as $trip)
                        <tr>
                            <td>
                                <a href="{{ route('admin.trip.show', $trip->id) }}" class="text-decoration-none">
                                    {{ Str::limit($trip->ref_id ?? $trip->id, 12, '') }}
                                </a>
                            </td>
                            <td>{{ $trip->customer?->first_name ?? '—' }} {{ $trip->customer?->last_name ?? '' }}</td>
                            <td class="small">{{ Str::limit($trip->pickup_address ?? '—', 30, '...') }}</td>
                            <td class="small">{{ Str::limit($trip->dropoff_address ?? '—', 30, '...') }}</td>
                            <td class="small">{{ $trip->created_at?->format('M j, Y g:i A') ?? '—' }}</td>
                            <td><span class="badge bg-info">{{ ucfirst($trip->current_status ?? 'scheduled') }}</span></td>
                            <td>
                                <a href="{{ route('admin.trip.show', $trip->id) }}" class="btn btn-sm btn-outline-primary">View</a>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="7" class="text-center text-muted py-4">
                            <div class="py-4">
                                <i class="bi bi-calendar-x fs-1 text-muted d-block mb-2"></i>
                                <div>No scheduled trips found for this period</div>
                                <a href="{{ route('admin.events.manage') }}" class="btn btn-sm btn-primary mt-2">Manage Events</a>
                            </div>
                        </td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if(method_exists($scheduledTrips, 'links'))
            <div class="card-footer bg-transparent border-0">
                {{ $scheduledTrips->links() }}
            </div>
            @endif
        </div>
    </div>

    <div class="text-muted small mt-3">Last updated: {{ now()->format('M j, Y g:i A') }}</div>
</div>
@endsection
