@section('title', 'Cancellations / No-Shows')
@extends('adminmodule::layouts.master')

@section('content')
<div class="container-fluid py-4">

    {{-- PAGE HEADER --}}
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h3 class="mb-1 fw-bold"><i class="bi bi-x-circle-fill text-danger me-2"></i>Cancellations / No-Shows</h3>
            <div class="text-muted small">Track cancellations, no-shows, and associated fees &mdash; {{ $lastUpdated->format('d M Y') }}</div>
        </div>
        <button class="btn btn-sm btn-outline-success" disabled title="Coming soon">
            <i class="bi bi-download me-1"></i> Export CSV
        </button>
    </div>

    {{-- KPI CARDS --}}
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="card oneway-card p-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="oneway-kpi__icon" style="background:rgba(239,68,68,.12);color:#ef4444;">
                        <i class="bi bi-x-circle"></i>
                    </div>
                    <div>
                        <div class="fw-bold fs-4">{{ number_format($kpis['total']) }}</div>
                        <div class="oneway-kpi__label">Total</div>
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
        <div class="col-6 col-md-3">
            <div class="card oneway-card p-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="oneway-kpi__icon" style="background:rgba(59,130,246,.12);color:#3b82f6;">
                        <i class="bi bi-calendar-week"></i>
                    </div>
                    <div>
                        <div class="fw-bold fs-4">{{ number_format($kpis['week']) }}</div>
                        <div class="oneway-kpi__label">This Week</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card oneway-card p-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="oneway-kpi__icon" style="background:rgba(16,185,129,.12);color:#10b981;">
                        <i class="bi bi-currency-dollar"></i>
                    </div>
                    <div>
                        <div class="fw-bold fs-4">${{ number_format($kpis['revenue'], 0) }}</div>
                        <div class="oneway-kpi__label">Fees Collected</div>
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
                <label class="form-label small fw-semibold">Search</label>
                <input type="text" name="search" class="form-control form-control-sm" placeholder="Trip ref or reason..." value="{{ $filters['search'] ?? '' }}">
            </div>
            <div class="col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-primary btn-sm px-3">Filter</button>
                <a href="{{ route('admin.cancellations.index') }}" class="btn btn-outline-secondary btn-sm">Clear</a>
            </div>
        </div>
    </form>

    {{-- TABLE --}}
    <div class="card oneway-card">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Ref ID</th>
                        <th>Customer</th>
                        <th>Driver</th>
                        <th>Reason</th>
                        <th>Cancel Fee</th>
                        <th>Payment</th>
                        <th>Cancelled At</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($rows as $trip)
                    <tr>
                        <td><span class="badge bg-light text-dark fw-semibold">{{ $trip->ref_id }}</span></td>
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
                            @else
                                <span class="badge bg-light text-muted">No driver</span>
                            @endif
                        </td>
                        <td class="small text-muted" style="max-width:200px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">
                            {{ $trip->trip_cancellation_reason ?? '—' }}
                        </td>
                        <td class="small">
                            @if($trip->cancellation_fee > 0)
                                <span class="text-success fw-semibold">${{ number_format($trip->cancellation_fee, 2) }}</span>
                            @else
                                <span class="text-muted">$0.00</span>
                            @endif
                        </td>
                        <td class="small text-muted">{{ ucfirst($trip->payment_method ?? '—') }}</td>
                        <td class="small text-muted">{{ $trip->updated_at?->format('d M Y, H:i') ?? '—' }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-5">
                            <div class="text-muted">
                                <i class="bi bi-x-circle fs-2 d-block mb-2"></i>
                                No cancellations found for the selected period.
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
