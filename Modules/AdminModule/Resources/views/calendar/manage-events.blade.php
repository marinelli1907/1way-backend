@section('title', 'Manage Events')
@extends('adminmodule::layouts.master')

@section('content')
<div class="container-fluid">

    {{-- HEADER --}}
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <nav aria-label="breadcrumb"><ol class="breadcrumb mb-1">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.events.index') }}">Events</a></li>
                <li class="breadcrumb-item active">Manage Events</li>
            </ol></nav>
            <h3 class="mb-0 fw-bold">Create / Manage Events</h3>
            <div class="text-muted small">Create and manage events for scheduled rides</div>
        </div>
        <div>
            <a href="{{ route('admin.events.index') }}" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Back to Events
            </a>
        </div>
    </div>

    {{-- INFO CARD --}}
    <div class="card oneway-card mb-4">
        <div class="card-body">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h5 class="mb-2">Event Management</h5>
                    <p class="text-muted mb-0">Create events and manage scheduled rides. Events help organize trips around specific occasions, venues, or time periods.</p>
                </div>
                <div class="col-md-4 text-end">
                    <div class="fw-bold fs-3">{{ $zones->count() ?? 0 }}</div>
                    <div class="text-muted small">Active Zones Available</div>
                </div>
            </div>
        </div>
    </div>

    {{-- ZONES LIST --}}
    <div class="card oneway-card">
        <div class="card-header bg-transparent border-0">
            <h5 class="mb-0 fw-semibold">Available Zones</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Zone Name</th>
                            <th>Status</th>
                            <th>Coverage</th>
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
                            <td class="small">{{ Str::limit($zone->coordinates ?? '—', 50, '...') }}</td>
                            <td>
                                <a href="{{ route('admin.zone.index') }}" class="btn btn-sm btn-outline-primary">
                                    View Zone
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="4" class="text-center text-muted py-4">
                            <div class="py-4">
                                <i class="bi bi-geo-alt fs-1 text-muted d-block mb-2"></i>
                                <div>No zones available</div>
                                <a href="{{ route('admin.zone.index') }}" class="btn btn-sm btn-primary mt-2">Manage Zones</a>
                            </div>
                        </td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- COMING SOON NOTICE --}}
    <div class="card oneway-card mt-4 border-info">
        <div class="card-body text-center py-5">
            <i class="bi bi-tools fs-1 text-info d-block mb-3"></i>
            <h5 class="fw-bold mb-2">Event Creation Coming Soon</h5>
            <p class="text-muted mb-4">Full event creation and management features are being developed. For now, you can view scheduled trips in the Events List.</p>
            <a href="{{ route('admin.events.index') }}" class="btn btn-primary">
                <i class="bi bi-arrow-left"></i> View Events List
            </a>
        </div>
    </div>

    <div class="text-muted small mt-3">Last updated: {{ now()->format('M j, Y g:i A') }}</div>
</div>
@endsection
