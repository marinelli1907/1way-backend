@section('title', 'Alerts')
@extends('adminmodule::layouts.master')

@section('content')
<div class="container-fluid">

    {{-- HEADER --}}
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <nav aria-label="breadcrumb"><ol class="breadcrumb mb-1">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item active">Alerts</li>
            </ol></nav>
            <h3 class="mb-0 fw-bold">Safety Alerts</h3>
            <div class="text-muted small">Monitor and manage safety alerts from trips</div>
        </div>
        <div>
            <button class="btn btn-sm btn-outline-secondary" disabled title="Export CSV">
                <i class="bi bi-download"></i> Export CSV
            </button>
        </div>
    </div>

    {{-- KPI CARDS --}}
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-3">
            <div class="card oneway-card p-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="oneway-kpi__icon"><i class="bi bi-exclamation-triangle-fill text-warning"></i></div>
                    <div>
                        <div class="fw-bold fs-3">{{ $totalAlerts ?? 0 }}</div>
                        <div class="oneway-kpi__label">Total Alerts</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card oneway-card p-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="oneway-kpi__icon"><i class="bi bi-circle-fill text-danger"></i></div>
                    <div>
                        <div class="fw-bold fs-3">{{ $openAlerts ?? 0 }}</div>
                        <div class="oneway-kpi__label">Open Alerts</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card oneway-card p-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="oneway-kpi__icon"><i class="bi bi-check-circle-fill text-success"></i></div>
                    <div>
                        <div class="fw-bold fs-3">{{ $resolvedAlerts ?? 0 }}</div>
                        <div class="oneway-kpi__label">Resolved</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card oneway-card p-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="oneway-kpi__icon"><i class="bi bi-calendar-day"></i></div>
                    <div>
                        <div class="fw-bold fs-3">{{ $todayAlerts ?? 0 }}</div>
                        <div class="oneway-kpi__label">Today</div>
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
                    <label class="form-label small">Status</label>
                    <select name="status" class="form-select form-select-sm">
                        <option value="">All Status</option>
                        <option value="1" {{ ($status ?? '') === '1' ? 'selected' : '' }}>Open</option>
                        <option value="0" {{ ($status ?? '') === '0' ? 'selected' : '' }}>Resolved</option>
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
                    <input type="text" name="search" value="{{ $search ?? '' }}" placeholder="Trip or customer" class="form-control form-control-sm">
                </div>
                <div class="col-12">
                    <button type="submit" class="btn btn-sm btn-primary">Apply Filters</button>
                    <a href="{{ route('admin.alerts.index') }}" class="btn btn-sm btn-outline-secondary">Clear</a>
                </div>
            </form>
        </div>
    </div>

    {{-- ALERTS TABLE --}}
    <div class="card oneway-card">
        <div class="card-header bg-transparent border-0">
            <h5 class="mb-0 fw-semibold">Alert History</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Trip</th>
                            <th>Customer</th>
                            <th>Driver</th>
                            <th>Type</th>
                            <th>Status</th>
                            <th>Created</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($alerts as $alert)
                        <tr>
                            <td><span class="badge bg-secondary">{{ Str::limit($alert->id ?? '—', 8, '') }}</span></td>
                            <td>
                                @if($alert->trip)
                                    <a href="{{ route('admin.trip.show', $alert->trip->id) }}" class="text-decoration-none">
                                        {{ Str::limit($alert->trip->ref_id ?? $alert->trip->id, 12, '') }}
                                    </a>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td>{{ $alert->trip?->customer ? trim(($alert->trip->customer->first_name ?? '') . ' ' . ($alert->trip->customer->last_name ?? '')) : '—' }}</td>
                            <td class="text-muted">{{ $alert->trip?->driver ? trim(($alert->trip->driver->first_name ?? '') . ' ' . ($alert->trip->driver->last_name ?? '')) : 'Unassigned' }}</td>
                            <td><span class="badge bg-info">{{ $alert->type ?? 'Safety Alert' }}</span></td>
                            <td>
                                <span class="badge bg-{{ ($alert->status ?? '') === 'pending' ? 'danger' : 'success' }}">
                                    {{ ($alert->status ?? '') === 'pending' ? 'Open' : 'Resolved' }}
                                </span>
                            </td>
                            <td class="small">{{ $alert->created_at?->format('M j, Y g:i A') ?? '—' }}</td>
                            <td>
                                @if($alert->trip)
                                <a href="{{ route('admin.trip.show', $alert->trip->id) }}" class="btn btn-sm btn-outline-primary">View</a>
                                @else
                                <span class="text-muted">—</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="8" class="text-center text-muted py-4">
                            <div class="py-4">
                                <i class="bi bi-shield-check fs-1 text-muted d-block mb-2"></i>
                                <div>No alerts found for this period</div>
                            </div>
                        </td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if(method_exists($alerts, 'links'))
            <div class="card-footer bg-transparent border-0">
                {{ $alerts->links() }}
            </div>
            @endif
        </div>
    </div>

    <div class="text-muted small mt-3">Last updated: {{ now()->format('M j, Y g:i A') }}</div>
</div>
@endsection
