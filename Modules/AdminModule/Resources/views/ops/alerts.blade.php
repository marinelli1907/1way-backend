@section('title', 'Safety Alerts')
@extends('adminmodule::layouts.master')

@section('content')
<div class="container-fluid py-4">

    {{-- PAGE HEADER --}}
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h3 class="mb-1 fw-bold"><i class="bi bi-bell-fill text-warning me-2"></i>Safety Alerts</h3>
            <div class="text-muted small">Passenger and driver safety alerts &mdash; last updated {{ $lastUpdated->format('H:i:s') }}</div>
        </div>
        <button class="btn btn-sm btn-outline-secondary" onclick="location.reload()">
            <i class="bi bi-arrow-clockwise"></i> Refresh
        </button>
    </div>

    {{-- KPI CARDS --}}
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="card oneway-card p-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="oneway-kpi__icon" style="background:rgba(107,114,128,.12);color:#6b7280;">
                        <i class="bi bi-shield"></i>
                    </div>
                    <div>
                        <div class="fw-bold fs-4">{{ number_format($kpis['total']) }}</div>
                        <div class="oneway-kpi__label">Total Alerts</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card oneway-card p-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="oneway-kpi__icon" style="background:rgba(239,68,68,.12);color:#ef4444;">
                        <i class="bi bi-shield-exclamation"></i>
                    </div>
                    <div>
                        <div class="fw-bold fs-4">{{ number_format($kpis['pending']) }}</div>
                        <div class="oneway-kpi__label">Open / Pending</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card oneway-card p-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="oneway-kpi__icon" style="background:rgba(16,185,129,.12);color:#10b981;">
                        <i class="bi bi-shield-check"></i>
                    </div>
                    <div>
                        <div class="fw-bold fs-4">{{ number_format($kpis['resolved']) }}</div>
                        <div class="oneway-kpi__label">Resolved</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card oneway-card p-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="oneway-kpi__icon" style="background:rgba(245,158,11,.12);color:#f59e0b;">
                        <i class="bi bi-calendar-day"></i>
                    </div>
                    <div>
                        <div class="fw-bold fs-4">{{ number_format($kpis['today']) }}</div>
                        <div class="oneway-kpi__label">Today</div>
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
            <div class="col-md-2">
                <label class="form-label small fw-semibold">Status</label>
                <select name="type" class="form-select form-select-sm">
                    <option value="">All</option>
                    <option value="pending" @selected(($filters['type']??'')=='pending')>Pending</option>
                    <option value="resolved" @selected(($filters['type']??'')=='resolved')>Resolved</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-semibold">Search</label>
                <input type="text" name="search" class="form-control form-control-sm" placeholder="Trip ref, name..." value="{{ $filters['search'] ?? '' }}">
            </div>
            <div class="col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-primary btn-sm px-4">Filter</button>
                <a href="{{ route('admin.alerts.index') }}" class="btn btn-outline-secondary btn-sm">Clear</a>
                <button type="button" class="btn btn-outline-success btn-sm ms-auto" disabled title="Coming soon">
                    <i class="bi bi-download"></i> Export
                </button>
            </div>
        </div>
    </form>

    {{-- TABLE --}}
    <div class="card oneway-card">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Trip Ref</th>
                        <th>Reported By</th>
                        <th>Status</th>
                        <th>Type</th>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($rows as $alert)
                    <tr>
                        <td class="text-muted small">{{ $loop->iteration }}</td>
                        <td>
                            @if($alert->trip)
                                <span class="badge bg-light text-dark">{{ $alert->trip->ref_id ?? '—' }}</span>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td>
                            @if($alert->customer)
                                {{ $alert->customer->first_name }} {{ $alert->customer->last_name }}
                            @else
                                <span class="text-muted">Unknown</span>
                            @endif
                        </td>
                        <td>
                            @if(strtolower($alert->status ?? '') === 'pending')
                                <span class="badge bg-danger">Pending</span>
                            @elseif(strtolower($alert->status ?? '') === 'resolved')
                                <span class="badge bg-success">Resolved</span>
                            @else
                                <span class="badge bg-secondary">{{ $alert->status ?? '—' }}</span>
                            @endif
                        </td>
                        <td class="small text-muted">{{ $alert->type ?? '—' }}</td>
                        <td class="small text-muted">{{ $alert->created_at?->format('d M Y, H:i') ?? '—' }}</td>
                        <td>
                            <button class="btn btn-xs btn-outline-primary py-0 px-2" disabled>View</button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-5">
                            <div class="text-muted">
                                <i class="bi bi-shield-check fs-2 d-block mb-2"></i>
                                No alerts found.
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
