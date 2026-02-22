@section('title', 'Venues / Locations')
@extends('adminmodule::layouts.master')

@section('content')
<div class="container-fluid py-4">

    {{-- PAGE HEADER --}}
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h3 class="mb-1 fw-bold"><i class="bi bi-geo-fill text-primary me-2"></i>Venues / Locations</h3>
            <div class="text-muted small">Manage event venues and pickup/drop-off locations &mdash; {{ $lastUpdated->format('d M Y') }}</div>
        </div>
        <button class="btn btn-sm btn-primary" disabled title="Coming soon">
            <i class="bi bi-plus-circle me-1"></i> Add Venue
        </button>
    </div>

    {{-- KPI CARDS --}}
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="card oneway-card p-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="oneway-kpi__icon" style="background:rgba(107,114,128,.12);color:#6b7280;">
                        <i class="bi bi-building"></i>
                    </div>
                    <div>
                        <div class="fw-bold fs-4">{{ number_format($kpis['total']) }}</div>
                        <div class="oneway-kpi__label">Total Venues</div>
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
                        <div class="fw-bold fs-4">{{ number_format($kpis['active']) }}</div>
                        <div class="oneway-kpi__label">Active</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card oneway-card p-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="oneway-kpi__icon" style="background:rgba(59,130,246,.12);color:#3b82f6;">
                        <i class="bi bi-calendar-event"></i>
                    </div>
                    <div>
                        <div class="fw-bold fs-4">{{ number_format($kpis['upcoming_events']) }}</div>
                        <div class="oneway-kpi__label">Upcoming Events</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card oneway-card p-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="oneway-kpi__icon" style="background:rgba(139,92,246,.12);color:#8b5cf6;">
                        <i class="bi bi-geo"></i>
                    </div>
                    <div>
                        <div class="fw-bold fs-4">{{ number_format($kpis['zones']) }}</div>
                        <div class="oneway-kpi__label">Zones Covered</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ZONES REFERENCE --}}
    @if($zones->count() > 0)
    <div class="card oneway-card p-4 mb-4">
        <h6 class="fw-semibold mb-3">Active Zones (for reference)</h6>
        <div class="row g-2">
            @foreach($zones as $zone)
            <div class="col-md-3 col-6">
                <div class="border rounded p-2 small d-flex align-items-center gap-2">
                    <i class="bi bi-geo-alt text-primary"></i>
                    <span>{{ $zone->name }}</span>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- FILTER --}}
    <form method="GET" class="card oneway-card p-3 mb-4">
        <div class="row g-2 align-items-end">
            <div class="col-md-5">
                <label class="form-label small fw-semibold">Search</label>
                <input type="text" name="search" class="form-control form-control-sm" placeholder="Venue name, address..." value="{{ $filters['search'] ?? '' }}" disabled>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary btn-sm px-3" disabled>Filter</button>
            </div>
        </div>
    </form>

    {{-- NOTICE --}}
    <div class="alert alert-info d-flex align-items-center gap-2 mb-4" role="alert">
        <i class="bi bi-info-circle-fill fs-5"></i>
        <div>
            <strong>Venues module launching soon.</strong>
            Once the venues table is created, you can add, edit, and link venues to events and zones.
        </div>
    </div>

    {{-- TABLE --}}
    <div class="card oneway-card">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Venue Name</th>
                        <th>Address</th>
                        <th>Zone</th>
                        <th>Capacity</th>
                        <th>Events</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td colspan="8" class="text-center py-5">
                            <div class="text-muted">
                                <i class="bi bi-building fs-2 d-block mb-2"></i>
                                No venues added yet.
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
