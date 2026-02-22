@section('title', $title ?? 'Section')
@extends('adminmodule::layouts.master')

@section('content')
<div class="container-fluid">

    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <nav aria-label="breadcrumb"><ol class="breadcrumb mb-1">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item active">{{ $title ?? 'Section' }}</li>
            </ol></nav>
            <h3 class="mb-0 fw-bold">{{ $title ?? 'Section' }}</h3>
            <div class="text-muted small">{{ $subtitle ?? 'View and manage data for this section.' }}</div>
        </div>
        <div>
            <button class="btn btn-sm btn-outline-secondary" disabled title="Export available in a future release."><i class="bi bi-download"></i> Export CSV</button>
        </div>
    </div>

    <div class="row g-3 mb-4">
        @for($i = 1; $i <= 4; $i++)
        <div class="col-sm-6 col-xl-3">
            <div class="card oneway-card p-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="oneway-kpi__icon"><i class="bi bi-bar-chart"></i></div>
                    <div>
                        <div class="fw-bold fs-3">{{ $kpiValues[$i] ?? 0 }}</div>
                        <div class="oneway-kpi__label">Metric {{ $i }}</div>
                    </div>
                </div>
            </div>
        </div>
        @endfor
    </div>

    <div class="card oneway-card mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-2"><input type="date" name="from" value="{{ request('from', now()->subDays(30)->toDateString()) }}" class="form-control form-control-sm"></div>
                <div class="col-md-2"><input type="date" name="to" value="{{ request('to', now()->toDateString()) }}" class="form-control form-control-sm"></div>
                <div class="col-md-2"><select name="status" class="form-select form-select-sm"><option value="">All status</option><option value="active">Active</option></select></div>
                <div class="col-md-4"><input type="text" name="search" value="{{ request('search') }}" placeholder="Search..." class="form-control form-control-sm"></div>
                <div class="col-md-2"><button type="submit" class="btn btn-sm btn-primary w-100">Filter</button></div>
            </form>
        </div>
    </div>

    <div class="card oneway-card">
        <div class="card-header bg-transparent border-0"><h5 class="mb-0 fw-semibold">Data</h5></div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light"><tr><th>ID</th><th>Name</th><th>Status</th><th>Updated</th></tr></thead>
                    <tbody>
                        <tr><td colspan="4" class="text-center text-muted py-5">
                            <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                            <div>No records yet. Data will appear here when available.</div>
                        </td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="text-muted small mt-3">Last updated: {{ now()->format('M j, Y g:i A') }}</div>
</div>
@endsection
