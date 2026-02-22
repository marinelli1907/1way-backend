@section('title', 'Live KPIs')
@extends('adminmodule::layouts.master')

@section('content')
<div class="container-fluid">

    {{-- HEADER --}}
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <nav aria-label="breadcrumb"><ol class="breadcrumb mb-1">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item active">Live KPIs</li>
            </ol></nav>
            <h3 class="mb-0 fw-bold">Live KPIs</h3>
            <div class="text-muted small">Real-time operational metrics &mdash; <span class="text-success fw-semibold">Auto-refresh every 60s</span></div>
        </div>
        <div>
            <form class="d-flex gap-2" method="GET">
                <input type="date" name="from" value="{{ $from }}" class="form-control form-control-sm">
                <input type="date" name="to"   value="{{ $to }}"   class="form-control form-control-sm">
                <button class="btn btn-sm btn-primary">Filter</button>
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
                        <div class="fw-bold fs-3">{{ $ongoingTrips }}</div>
                        <div class="oneway-kpi__label">Trips In Progress</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card oneway-card p-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="oneway-kpi__icon"><i class="bi bi-wifi"></i></div>
                    <div>
                        <div class="fw-bold fs-3">{{ $activeDrivers }}</div>
                        <div class="oneway-kpi__label">Drivers Online <span class="text-muted small">/ {{ $totalDrivers }} total</span></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card oneway-card p-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="oneway-kpi__icon"><i class="bi bi-check-circle"></i></div>
                    <div>
                        <div class="fw-bold fs-3">{{ $completionRate }}%</div>
                        <div class="oneway-kpi__label">Completion Rate</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card oneway-card p-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="oneway-kpi__icon"><i class="bi bi-people"></i></div>
                    <div>
                        <div class="fw-bold fs-3">{{ $totalCustomers }}</div>
                        <div class="oneway-kpi__label">Active Customers</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- SECOND ROW --}}
    <div class="row g-3 mb-4">
        <div class="col-sm-4">
            <div class="card oneway-card p-3 text-center">
                <div class="fs-2 fw-bold text-success">{{ $completedTrips }}</div>
                <div class="text-muted small">Completed <span class="text-muted">(period)</span></div>
            </div>
        </div>
        <div class="col-sm-4">
            <div class="card oneway-card p-3 text-center">
                <div class="fs-2 fw-bold text-danger">{{ $cancelledTrips }}</div>
                <div class="text-muted small">Cancelled <span class="text-muted">(period)</span></div>
            </div>
        </div>
        <div class="col-sm-4">
            <div class="card oneway-card p-3 text-center">
                <div class="fs-2 fw-bold">{{ $totalTrips }}</div>
                <div class="text-muted small">Total Trips <span class="text-muted">(period)</span></div>
            </div>
        </div>
    </div>

    {{-- RECENT TRIPS --}}
    <div class="card oneway-card">
        <div class="card-header bg-transparent border-0 d-flex justify-content-between align-items-center">
            <h5 class="mb-0 fw-semibold">Recent Trips</h5>
            <a href="{{ route('admin.trip.index', ['all']) }}" class="btn btn-sm btn-outline-primary">View All</a>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr><th>ID</th><th>Customer</th><th>Driver</th><th>Status</th><th>Created</th></tr>
                    </thead>
                    <tbody>
                        @forelse($recentTrips as $trip)
                        <tr>
                            <td><span class="badge bg-secondary">{{ Str::limit($trip->id ?? '—', 8, '') }}</span></td>
                            <td>{{ $trip->customer?->first_name ?? '—' }} {{ $trip->customer?->last_name ?? '' }}</td>
                            <td>{{ $trip->driver?->first_name ?? '<span class="text-muted">Unassigned</span>' }}</td>
                            <td><span class="badge bg-{{ match($trip->current_status) {
                                'completed' => 'success', 'cancelled' => 'danger', 'ongoing','picking_up','reached' => 'warning',
                                'pending' => 'secondary', default => 'info'
                            } }}">{{ ucfirst(str_replace('_',' ', $trip->current_status ?? '')) }}</span></td>
                            <td>{{ $trip->created_at?->diffForHumans() ?? '—' }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="5" class="text-center text-muted py-4">No trips found for this period</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="text-muted small mt-3">Last updated: {{ now()->format('M j, Y H:i:s') }}</div>
</div>
@endsection

@push('script')
<script>
// Auto refresh every 60 seconds
setTimeout(() => location.reload(), 60000);
</script>
@endpush
