@section('title', 'Event Ride Planner')
@extends('adminmodule::layouts.master')

@section('content')
<div class="container-fluid py-4">

    {{-- PAGE HEADER --}}
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h3 class="mb-1 fw-bold"><i class="bi bi-people-fill text-primary me-2"></i>Event Ride Planner</h3>
            <div class="text-muted small">Plan and track rides linked to events &mdash; {{ $lastUpdated->format('d M Y') }}</div>
        </div>
        <button class="btn btn-sm btn-outline-success" disabled title="Export not yet available">
            <i class="bi bi-download me-1"></i> Export CSV
        </button>
    </div>

    {{-- KPI CARDS --}}
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="card oneway-card p-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="oneway-kpi__icon" style="background:rgba(245,158,11,.12);color:#f59e0b;">
                        <i class="bi bi-calendar-day"></i>
                    </div>
                    <div>
                        <div class="fw-bold fs-4">{{ number_format($kpis['scheduled']) }}</div>
                        <div class="oneway-kpi__label">Today's Rides</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card oneway-card p-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="oneway-kpi__icon" style="background:rgba(59,130,246,.12);color:#3b82f6;">
                        <i class="bi bi-hourglass-split"></i>
                    </div>
                    <div>
                        <div class="fw-bold fs-4">{{ number_format($kpis['pending']) }}</div>
                        <div class="oneway-kpi__label">Pending</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card oneway-card p-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="oneway-kpi__icon" style="background:rgba(16,185,129,.12);color:#10b981;">
                        <i class="bi bi-geo-alt-fill"></i>
                    </div>
                    <div>
                        <div class="fw-bold fs-4">{{ number_format($kpis['confirmed']) }}</div>
                        <div class="oneway-kpi__label">Confirmed</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card oneway-card p-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="oneway-kpi__icon" style="background:rgba(107,114,128,.12);color:#6b7280;">
                        <i class="bi bi-check-circle"></i>
                    </div>
                    <div>
                        <div class="fw-bold fs-4">{{ number_format($kpis['completed']) }}</div>
                        <div class="oneway-kpi__label">Completed</div>
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
            <div class="col-md-3">
                <label class="form-label small fw-semibold">Zone</label>
                <select name="zone_id" class="form-select form-select-sm">
                    <option value="">All Zones</option>
                    @foreach($zones ?? [] as $zone)
                        <option value="{{ $zone->id }}" @selected(($filters['zone_id']??'')==$zone->id)>{{ $zone->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3 d-flex gap-2 align-items-end">
                <button type="submit" class="btn btn-primary btn-sm px-3">Filter</button>
                <a href="{{ route('admin.event-ride-planner.index') }}" class="btn btn-outline-secondary btn-sm">Clear</a>
            </div>
        </div>
    </form>

    {{-- TABLE --}}
    <div class="card oneway-card">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Trip Ref</th>
                        <th>Customer</th>
                        <th>Driver</th>
                        <th>Status</th>
                        <th>Fare</th>
                        <th>Type</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($rows as $trip)
                    <tr>
                        <td><span class="badge bg-light text-dark fw-semibold">{{ $trip->ref_id }}</span></td>
                        <td class="small">
                            @if($trip->customer)
                                {{ $trip->customer->first_name }} {{ $trip->customer->last_name }}
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td class="small">
                            @if($trip->driver)
                                {{ $trip->driver->first_name }} {{ $trip->driver->last_name }}
                            @else
                                <span class="badge bg-light text-muted">Unassigned</span>
                            @endif
                        </td>
                        <td>
                            @php
                                $sc = ['pending'=>'warning','accepted'=>'primary','ongoing'=>'success','completed'=>'secondary','cancelled'=>'danger'];
                            @endphp
                            <span class="badge bg-{{ $sc[$trip->current_status] ?? 'secondary' }}">{{ str_replace('_',' ',ucfirst($trip->current_status)) }}</span>
                        </td>
                        <td class="small">${{ number_format($trip->actual_fare ?? $trip->estimated_fare, 2) }}</td>
                        <td class="small text-muted">{{ ucfirst(str_replace('_',' ',$trip->type ?? '—')) }}</td>
                        <td class="small text-muted">{{ $trip->created_at?->format('d M Y') }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-5">
                            <div class="text-muted">
                                <i class="bi bi-people fs-2 d-block mb-2"></i>
                                No rides found for the selected period.
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if(method_exists($rows, 'links'))
        <div class="p-3 border-top">
            {{ $rows->links() }}
        </div>
        @endif
    </div>

</div>
@endsection
