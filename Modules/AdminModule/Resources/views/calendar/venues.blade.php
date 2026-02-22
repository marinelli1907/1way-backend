@section('title', 'Venues')
@extends('adminmodule::layouts.master')

@section('content')
<div class="container-fluid">

    {{-- HEADER --}}
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <nav aria-label="breadcrumb"><ol class="breadcrumb mb-1">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.events.index') }}">Events</a></li>
                <li class="breadcrumb-item active">Venues</li>
            </ol></nav>
            <h3 class="mb-0 fw-bold">Venues / Locations</h3>
            <div class="text-muted small">Manage event venues and service locations</div>
        </div>
        <div>
            <button class="btn btn-sm btn-outline-secondary" disabled title="Export CSV functionality coming soon">
                <i class="bi bi-download"></i> Export CSV
            </button>
        </div>
    </div>

    {{-- KPI CARDS --}}
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-6">
            <div class="card oneway-card p-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="oneway-kpi__icon"><i class="bi bi-geo-alt"></i></div>
                    <div>
                        <div class="fw-bold fs-3">{{ $totalZones }}</div>
                        <div class="oneway-kpi__label">Total Zones</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-6">
            <div class="card oneway-card p-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="oneway-kpi__icon"><i class="bi bi-check-circle text-success"></i></div>
                    <div>
                        <div class="fw-bold fs-3">{{ $activeZones }}</div>
                        <div class="oneway-kpi__label">Active Zones</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- FILTERS --}}
    <div class="card oneway-card mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-10">
                    <label class="form-label small">Search</label>
                    <input type="text" name="search" value="{{ $search }}" placeholder="Search by zone name..." class="form-control form-control-sm">
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-sm btn-primary w-100">Search</button>
                </div>
            </form>
        </div>
    </div>

    {{-- VENUES TABLE --}}
    <div class="card oneway-card">
        <div class="card-header bg-transparent border-0 d-flex justify-content-between align-items-center">
            <h5 class="mb-0 fw-semibold">Service Zones / Venues</h5>
            <a href="{{ route('admin.zone.index') }}" class="btn btn-sm btn-primary">
                <i class="bi bi-geo-alt"></i> Manage Zones
            </a>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Zone Name</th>
                            <th>Status</th>
                            <th>Coordinates</th>
                            <th>Coverage Area</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($zones as $zone)
                        <tr>
                            <td class="fw-semibold">{{ $zone->name ?? '—' }}</td>
                            <td>
                                <span class="badge bg-{{ $zone->is_active ? 'success' : 'secondary' }}">
                                    {{ $zone->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td class="small font-monospace">{{ Str::limit($zone->coordinates ?? '—', 40, '...') }}</td>
                            <td class="small">{{ Str::limit($zone->area ?? '—', 30, '...') }}</td>
                            <td>
                                <a href="{{ route('admin.zone.index') }}" class="btn btn-sm btn-outline-primary">
                                    View Details
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="5" class="text-center text-muted py-4">
                            <div class="py-4">
                                <i class="bi bi-geo-alt fs-1 text-muted d-block mb-2"></i>
                                <div>No zones found</div>
                                <a href="{{ route('admin.zone.index') }}" class="btn btn-sm btn-primary mt-2">Create Zone</a>
                            </div>
                        </td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if(method_exists($zones, 'links'))
            <div class="card-footer bg-transparent border-0">
                {{ $zones->links() }}
            </div>
            @endif
        </div>
    </div>

    {{-- INFO CARD --}}
    <div class="card oneway-card mt-4 border-info">
        <div class="card-body">
            <h5 class="mb-2"><i class="bi bi-info-circle text-info"></i> About Venues</h5>
            <p class="text-muted mb-0">Venues are represented as service zones in the system. Each zone defines a coverage area where rides can be requested and fulfilled. Manage zones to control service availability and coverage.</p>
        </div>
    </div>

    <div class="text-muted small mt-3">Last updated: {{ now()->format('M j, Y g:i A') }}</div>
</div>
@endsection
