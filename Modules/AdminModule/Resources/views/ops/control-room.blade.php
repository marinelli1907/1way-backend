@section('title', 'Dispatch / Control Room')
@extends('adminmodule::layouts.master')

@section('content')
<div class="container-fluid py-4">

    {{-- PAGE HEADER --}}
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h3 class="mb-1 fw-bold"><i class="bi bi-broadcast text-primary me-2"></i>Dispatch / Control Room</h3>
            <div class="text-muted small">Monitor and manage active trips in real time &mdash; {{ $lastUpdated->format('H:i:s') }}</div>
        </div>
        <div class="d-flex gap-2">
            <button class="btn btn-sm btn-outline-secondary" onclick="location.reload()">
                <i class="bi bi-arrow-clockwise"></i> Refresh
            </button>
            @if(Route::has('admin.fleet-map'))
            <a href="{{ route('admin.fleet-map', ['type'=>'all-driver']) }}" class="btn btn-sm btn-primary">
                <i class="bi bi-map me-1"></i> Fleet Map
            </a>
            @endif
        </div>
    </div>

    {{-- KPI CARDS --}}
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="card oneway-card p-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="oneway-kpi__icon" style="background:rgba(16,185,129,.12);color:#10b981;">
                        <i class="bi bi-geo-alt-fill"></i>
                    </div>
                    <div>
                        <div class="fw-bold fs-4">{{ number_format($kpis['active']) }}</div>
                        <div class="oneway-kpi__label">Active Rides</div>
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
                    <div class="oneway-kpi__icon" style="background:rgba(107,114,128,.12);color:#6b7280;">
                        <i class="bi bi-check-circle"></i>
                    </div>
                    <div>
                        <div class="fw-bold fs-4">{{ number_format($kpis['completed']) }}</div>
                        <div class="oneway-kpi__label">Completed Today</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card oneway-card p-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="oneway-kpi__icon" style="background:rgba(239,68,68,.12);color:#ef4444;">
                        <i class="bi bi-x-circle"></i>
                    </div>
                    <div>
                        <div class="fw-bold fs-4">{{ number_format($kpis['cancelled']) }}</div>
                        <div class="oneway-kpi__label">Cancelled Today</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- FILTERS --}}
    <form method="GET" class="card oneway-card p-3 mb-4">
        <div class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label small fw-semibold">Zone</label>
                <select name="zone_id" class="form-select form-select-sm">
                    <option value="">All Zones</option>
                    @foreach($zones ?? [] as $zone)
                        <option value="{{ $zone->id }}" @selected(($filters['zone_id']??'')==$zone->id)>{{ $zone->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-semibold">Status</label>
                <select name="status" class="form-select form-select-sm">
                    <option value="all">All Active</option>
                    <option value="pending"        @selected(($filters['status']??'')=='pending')>Pending</option>
                    <option value="accepted"       @selected(($filters['status']??'')=='accepted')>Accepted</option>
                    <option value="out_for_pickup" @selected(($filters['status']??'')=='out_for_pickup')>Out for Pickup</option>
                    <option value="ongoing"        @selected(($filters['status']??'')=='ongoing')>Ongoing</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-semibold">Search Trip Ref</label>
                <input type="text" name="search" class="form-control form-control-sm" placeholder="e.g. TR-000123" value="{{ $filters['search'] ?? '' }}">
            </div>
            <div class="col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-primary btn-sm px-4">Filter</button>
                <a href="{{ route('admin.control-room.index') }}" class="btn btn-outline-secondary btn-sm">Clear</a>
            </div>
        </div>
    </form>

    {{-- LIVE TRIP TABLE --}}
    <div class="card oneway-card">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Ref ID</th>
                        <th>Status</th>
                        <th>Customer</th>
                        <th>Driver</th>
                        <th>Fare</th>
                        <th>Type</th>
                        <th>Started</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($rows as $trip)
                    <tr>
                        <td><span class="badge bg-light text-dark fw-semibold">{{ $trip->ref_id }}</span></td>
                        <td>
                            @php
                                $statusColors = [
                                    'pending'       => 'warning',
                                    'accepted'      => 'primary',
                                    'out_for_pickup'=> 'info',
                                    'ongoing'       => 'success',
                                    'completed'     => 'secondary',
                                    'cancelled'     => 'danger',
                                ];
                                $color = $statusColors[$trip->current_status] ?? 'secondary';
                            @endphp
                            <span class="badge bg-{{ $color }}">{{ str_replace('_', ' ', ucfirst($trip->current_status)) }}</span>
                        </td>
                        <td class="small">
                            @if($trip->customer)
                                {{ $trip->customer->first_name }} {{ $trip->customer->last_name }}
                                <div class="text-muted" style="font-size:11px;">{{ $trip->customer->phone }}</div>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td class="small">
                            @if($trip->driver)
                                {{ $trip->driver->first_name }} {{ $trip->driver->last_name }}
                                <div class="text-muted" style="font-size:11px;">{{ $trip->driver->phone }}</div>
                            @else
                                <span class="badge bg-light text-muted">Unassigned</span>
                            @endif
                        </td>
                        <td class="small">${{ number_format($trip->actual_fare ?? $trip->estimated_fare, 2) }}</td>
                        <td class="small text-muted">{{ ucfirst(str_replace('_', ' ', $trip->type ?? '—')) }}</td>
                        <td class="small text-muted">{{ $trip->created_at?->format('H:i') ?? '—' }}</td>
                        <td>
                            @if(Route::has('admin.trip.details'))
                                <a href="{{ route('admin.trip.details', $trip->id) }}" class="btn btn-xs btn-outline-primary py-0 px-2" style="font-size:12px;">View</a>
                            @else
                                <button class="btn btn-xs btn-outline-primary py-0 px-2" style="font-size:12px;" disabled>View</button>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center py-5">
                            <div class="text-muted">
                                <i class="bi bi-broadcast fs-2 d-block mb-2"></i>
                                No active trips found.
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
