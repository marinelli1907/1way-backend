@section('title', 'Cancellations')
@extends('adminmodule::layouts.master')

@section('content')
<div class="container-fluid">

    {{-- HEADER --}}
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <nav aria-label="breadcrumb"><ol class="breadcrumb mb-1">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item active">Cancellations</li>
            </ol></nav>
            <h3 class="mb-0 fw-bold">Cancellations / No-Shows</h3>
            <div class="text-muted small">Track and analyze trip cancellations</div>
        </div>
        <div>
            <button class="btn btn-sm btn-outline-secondary" disabled title="Export CSV functionality coming soon">
                <i class="bi bi-download"></i> Export CSV
            </button>
        </div>
    </div>

    {{-- KPI CARDS --}}
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-3">
            <div class="card oneway-card p-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="oneway-kpi__icon"><i class="bi bi-x-circle-fill text-danger"></i></div>
                    <div>
                        <div class="fw-bold fs-3">{{ $totalCancelled ?? 0 }}</div>
                        <div class="oneway-kpi__label">Total Cancelled</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card oneway-card p-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="oneway-kpi__icon"><i class="bi bi-calendar-day"></i></div>
                    <div>
                        <div class="fw-bold fs-3">{{ $cancelledToday ?? 0 }}</div>
                        <div class="oneway-kpi__label">Cancelled Today</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card oneway-card p-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="oneway-kpi__icon"><i class="bi bi-chat-quote text-info"></i></div>
                    <div>
                        <div class="fw-bold fs-6 text-truncate" title="{{ $topReason ?? '—' }}">{{ Str::limit($topReason ?? '—', 20, '…') }}</div>
                        <div class="oneway-kpi__label">Top Reason</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card oneway-card p-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="oneway-kpi__icon"><i class="bi bi-percent text-warning"></i></div>
                    <div>
                        <div class="fw-bold fs-3">{{ $cancelRate7d ?? 0 }}%</div>
                        <div class="oneway-kpi__label">Cancel Rate (7d)</div>
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
                    <label class="form-label small">Cancelled By</label>
                    <select name="cancelled_by" class="form-select form-select-sm">
                        <option value="">All</option>
                        <option value="driver" {{ ($cancelledBy ?? '') === 'driver' ? 'selected' : '' }}>Driver</option>
                        <option value="customer" {{ ($cancelledBy ?? '') === 'customer' ? 'selected' : '' }}>Customer</option>
                    </select>
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
                <div class="col-md-2">
                    <label class="form-label small">Search</label>
                    <input type="text" name="search" value="{{ $search ?? '' }}" placeholder="Customer or Trip ID" class="form-control form-control-sm">
                </div>
                <div class="col-12">
                    <button type="submit" class="btn btn-sm btn-primary">Apply Filters</button>
                    <a href="{{ route('admin.cancellations.index') }}" class="btn btn-sm btn-outline-secondary">Clear</a>
                </div>
            </form>
        </div>
    </div>

    {{-- CANCELLATIONS TABLE --}}
    <div class="card oneway-card">
        <div class="card-header bg-transparent border-0">
            <h5 class="mb-0 fw-semibold">Cancellation History</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Trip ID</th>
                            <th>Customer</th>
                            <th>Driver</th>
                            <th>Cancelled By</th>
                            <th>Reason</th>
                            <th>Cancelled At</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($trips as $trip)
                        <tr>
                            <td>
                                <a href="{{ route('admin.trip.show', $trip->id) }}" class="text-decoration-none">
                                    {{ Str::limit($trip->ref_id ?? $trip->id, 12, '') }}
                                </a>
                            </td>
                            <td>{{ $trip->customer?->first_name ?? '—' }} {{ $trip->customer?->last_name ?? '' }}</td>
                            <td class="text-muted">{{ $trip->driver ? trim(($trip->driver->first_name ?? '') . ' ' . ($trip->driver->last_name ?? '')) : 'Unassigned' }}</td>
                            <td>
                                <span class="badge bg-{{ $trip->fee?->cancelled_by === 'driver' ? 'warning' : 'info' }}">
                                    {{ ucfirst($trip->fee?->cancelled_by ?? 'unknown') }}
                                </span>
                            </td>
                            <td class="small">{{ Str::limit($trip->trip_cancellation_reason ?? $trip->fee?->cancellation_reason ?? '—', 40, '...') }}</td>
                            <td class="small">{{ $trip->updated_at?->format('M j, Y g:i A') ?? '—' }}</td>
                            <td>
                                <a href="{{ route('admin.trip.show', $trip->id) }}" class="btn btn-sm btn-outline-primary">
                                    View
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="7" class="text-center text-muted py-4">
                            <div class="py-4">
                                <i class="bi bi-check-circle fs-1 text-success d-block mb-2"></i>
                                <div>No cancellations found for this period</div>
                            </div>
                        </td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if(method_exists($trips, 'links'))
            <div class="card-footer bg-transparent border-0">
                {{ $trips->links() }}
            </div>
            @endif
        </div>
    </div>

    <div class="text-muted small mt-3">Last updated: {{ now()->format('M j, Y g:i A') }}</div>
</div>
@endsection
