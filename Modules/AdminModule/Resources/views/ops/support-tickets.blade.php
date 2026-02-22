@section('title', 'Support Inbox / Tickets')
@extends('adminmodule::layouts.master')

@section('content')
<div class="container-fluid py-4">

    {{-- PAGE HEADER --}}
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h3 class="mb-1 fw-bold"><i class="bi bi-headset text-primary me-2"></i>Support Inbox / Tickets</h3>
            <div class="text-muted small">Manage customer and driver support requests &mdash; {{ $lastUpdated->format('d M Y') }}</div>
        </div>
        <div class="d-flex gap-2">
            <button class="btn btn-sm btn-outline-success" disabled title="Export not yet available">
                <i class="bi bi-download me-1"></i> Export CSV
            </button>
            <button class="btn btn-sm btn-primary" disabled title="Ticket creation coming soon">
                <i class="bi bi-plus-circle me-1"></i> New Ticket
            </button>
        </div>
    </div>

    {{-- KPI CARDS --}}
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="card oneway-card p-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="oneway-kpi__icon" style="background:rgba(107,114,128,.12);color:#6b7280;">
                        <i class="bi bi-ticket-detailed"></i>
                    </div>
                    <div>
                        <div class="fw-bold fs-4">{{ number_format($kpis['total']) }}</div>
                        <div class="oneway-kpi__label">Total Tickets</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card oneway-card p-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="oneway-kpi__icon" style="background:rgba(239,68,68,.12);color:#ef4444;">
                        <i class="bi bi-envelope-open"></i>
                    </div>
                    <div>
                        <div class="fw-bold fs-4">{{ number_format($kpis['open']) }}</div>
                        <div class="oneway-kpi__label">Open</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card oneway-card p-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="oneway-kpi__icon" style="background:rgba(59,130,246,.12);color:#3b82f6;">
                        <i class="bi bi-arrow-repeat"></i>
                    </div>
                    <div>
                        <div class="fw-bold fs-4">{{ number_format($kpis['in_progress']) }}</div>
                        <div class="oneway-kpi__label">In Progress</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card oneway-card p-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="oneway-kpi__icon" style="background:rgba(16,185,129,.12);color:#10b981;">
                        <i class="bi bi-check2-all"></i>
                    </div>
                    <div>
                        <div class="fw-bold fs-4">{{ number_format($kpis['resolved']) }}</div>
                        <div class="oneway-kpi__label">Resolved</div>
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
                <select name="status" class="form-select form-select-sm">
                    <option value="">All</option>
                    <option value="open"        @selected(($filters['status']??'')=='open')>Open</option>
                    <option value="in_progress" @selected(($filters['status']??'')=='in_progress')>In Progress</option>
                    <option value="resolved"    @selected(($filters['status']??'')=='resolved')>Resolved</option>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label small fw-semibold">Search</label>
                <input type="text" name="search" class="form-control form-control-sm" placeholder="Subject, user, ticket ID..." value="{{ $filters['search'] ?? '' }}">
            </div>
            <div class="col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-primary btn-sm px-3">Filter</button>
                <a href="{{ route('admin.support.tickets.index') }}" class="btn btn-outline-secondary btn-sm">Clear</a>
            </div>
        </div>
    </form>

    {{-- NOTICE BANNER --}}
    <div class="alert alert-info d-flex align-items-center gap-2 mb-4" role="alert">
        <i class="bi bi-info-circle-fill fs-5"></i>
        <div>
            <strong>Support system coming soon.</strong>
            The built-in ticket management module is being configured. In the meantime, direct chat with drivers is available via
            @if(Route::has('admin.chatting'))
                <a href="{{ route('admin.chatting') }}" class="alert-link">Driver Chat</a>.
            @else
                Driver Chat.
            @endif
        </div>
    </div>

    {{-- EMPTY TABLE --}}
    <div class="card oneway-card">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Ticket ID</th>
                        <th>Subject</th>
                        <th>User</th>
                        <th>Type</th>
                        <th>Priority</th>
                        <th>Status</th>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td colspan="8" class="text-center py-5">
                            <div class="text-muted">
                                <i class="bi bi-headset fs-2 d-block mb-2"></i>
                                No support tickets yet.
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
