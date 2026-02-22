@section('title', 'Event Ride Planner')
@extends('adminmodule::layouts.master')

@section('content')
<div class="container-fluid">

    {{-- HEADER --}}
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <nav aria-label="breadcrumb"><ol class="breadcrumb mb-1">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.events.index') }}">Events</a></li>
                <li class="breadcrumb-item active">Event Ride Planner</li>
            </ol></nav>
            <h3 class="mb-0 fw-bold">Event Ride Planner</h3>
            <div class="text-muted small">Plan and coordinate rides for events</div>
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
                    <div class="oneway-kpi__icon"><i class="bi bi-calendar-check"></i></div>
                    <div>
                        <div class="fw-bold fs-3">{{ $scheduledCount }}</div>
                        <div class="oneway-kpi__label">Scheduled</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card oneway-card p-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="oneway-kpi__icon"><i class="bi bi-hourglass-split text-info"></i></div>
                    <div>
                        <div class="fw-bold fs-3">{{ $pendingCount }}</div>
                        <div class="oneway-kpi__label">Pending</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card oneway-card p-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="oneway-kpi__icon"><i class="bi bi-wifi text-success"></i></div>
                    <div>
                        <div class="fw-bold fs-3">{{ $driversOnline }}</div>
                        <div class="oneway-kpi__label">Drivers Online</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card oneway-card p-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="oneway-kpi__icon"><i class="bi bi-check-circle text-primary"></i></div>
                    <div>
                        <div class="fw-bold fs-3">{{ $driversAvail }}</div>
                        <div class="oneway-kpi__label">Available</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ZONES SELECTOR --}}
    <div class="card oneway-card mb-4">
        <div class="card-header bg-transparent border-0">
            <h5 class="mb-0 fw-semibold">Select Zone for Planning</h5>
        </div>
        <div class="card-body">
            <div class="row g-3">
                @forelse($zones as $zone)
                <div class="col-md-4">
                    <div class="card border h-100">
                        <div class="card-body">
                            <h6 class="fw-semibold mb-2">{{ $zone->name ?? 'Unnamed Zone' }}</h6>
                            <div class="small text-muted mb-3">
                                <div><i class="bi bi-geo-alt"></i> {{ Str::limit($zone->coordinates ?? 'No coordinates', 40, '...') }}</div>
                            </div>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="badge bg-{{ $zone->is_active ? 'success' : 'secondary' }}">
                                    {{ $zone->is_active ? 'Active' : 'Inactive' }}
                                </span>
                                <a href="{{ route('admin.zone.index') }}" class="btn btn-sm btn-outline-primary">
                                    View Zone
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                @empty
                <div class="col-12">
                    <div class="text-center text-muted py-4">
                        <i class="bi bi-geo-alt fs-1 d-block mb-2"></i>
                        <div>No zones available</div>
                        <a href="{{ route('admin.zone.index') }}" class="btn btn-sm btn-primary mt-2">Manage Zones</a>
                    </div>
                </div>
                @endforelse
            </div>
        </div>
    </div>

    {{-- PLANNING INFO --}}
    <div class="card oneway-card border-info">
        <div class="card-body">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h5 class="mb-2"><i class="bi bi-info-circle text-info"></i> Ride Planning</h5>
                    <p class="text-muted mb-0">Use this tool to plan rides for events. Select a zone to view available drivers and scheduled trips. Coordinate rides efficiently by matching demand with driver availability.</p>
                </div>
                <div class="col-md-4 text-end">
                    <a href="{{ route('admin.control-room.index') }}" class="btn btn-primary">
                        <i class="bi bi-broadcast"></i> Open Control Room
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="text-muted small mt-3">Last updated: {{ now()->format('M j, Y g:i A') }}</div>
</div>
@endsection
