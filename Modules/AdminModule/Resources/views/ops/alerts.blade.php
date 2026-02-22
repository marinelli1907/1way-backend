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
            <form class="d-flex gap-2" method="GET">
                <input type="date" name="from" value="{{ $from }}" class="form-control form-control-sm">
                <input type="date" name="to"   value="{{ $to }}"   class="form-control form-control-sm">
                <select name="status" class="form-select form-select-sm">
                    <option value="">All Status</option>
                    <option value="1" {{ $status === '1' ? 'selected' : '' }}>Open</option>
                    <option value="0" {{ $status === '0' ? 'selected' : '' }}>Resolved</option>
                </select>
                <button class="btn btn-sm btn-primary">Filter</button>
            </form>
        </div>
    </div>

    {{-- KPI CARDS --}}
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-3">
            <div class="card oneway-card p-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="oneway-kpi__icon"><i class="bi bi-exclamation-triangle-fill text-warning"></i></div>
                    <div>
                        <div class="fw-bold fs-3">{{ $totalAlerts }}</div>
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
                        <div class="fw-bold fs-3">{{ $openAlerts }}</div>
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
                        <div class="fw-bold fs-3">{{ $resolvedAlerts }}</div>
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
                        <div class="fw-bold fs-3">{{ $todayAlerts }}</div>
                        <div class="oneway-kpi__label">Today</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ALERTS TABLE --}}
    <div class="card oneway-card">
        <div class="card-header bg-transparent border-0 d-flex justify-content-between align-items-center">
            <h5 class="mb-0 fw-semibold">Alert History</h5>
            <button class="btn btn-sm btn-outline-secondary" disabled title="Export CSV functionality coming soon">
                <i class="bi bi-download"></i> Export CSV
            </button>
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
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($query as $alert)
                        <tr>
                            <td><span class="badge bg-secondary">{{ Str::limit($alert->id ?? '—', 8, '') }}</span></td>
                            <td>
                                @if($alert->tripRequest)
                                    <a href="{{ route('admin.trip.details', $alert->tripRequest->id) }}" class="text-decoration-none">
                                        {{ Str::limit($alert->tripRequest->ref_id ?? $alert->tripRequest->id, 12, '') }}
                                    </a>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td>{{ $alert->tripRequest?->customer?->first_name ?? '—' }} {{ $alert->tripRequest?->customer?->last_name ?? '' }}</td>
                            <td>{{ $alert->tripRequest?->driver?->first_name ?? '<span class="text-muted">Unassigned</span>' }}</td>
                            <td><span class="badge bg-info">{{ $alert->type ?? 'Safety Alert' }}</span></td>
                            <td>
                                <span class="badge bg-{{ $alert->is_active ? 'danger' : 'success' }}">
                                    {{ $alert->is_active ? 'Open' : 'Resolved' }}
                                </span>
                            </td>
                            <td>{{ $alert->created_at?->format('M j, Y g:i A') ?? '—' }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="7" class="text-center text-muted py-4">
                            <div class="py-4">
                                <i class="bi bi-shield-check fs-1 text-muted d-block mb-2"></i>
                                <div>No alerts found for this period</div>
                            </div>
                        </td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if(method_exists($query, 'links'))
            <div class="card-footer bg-transparent border-0">
                {{ $query->links() }}
            </div>
            @endif
        </div>
    </div>

    <div class="text-muted small mt-3">Last updated: {{ now()->format('M j, Y g:i A') }}</div>
</div>
@endsection
