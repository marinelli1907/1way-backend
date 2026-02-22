@section('title', 'Live KPIs')
@extends('adminmodule::layouts.master')

@section('content')
<div class="container-fluid py-4">

    {{-- PAGE HEADER --}}
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h3 class="mb-1 fw-bold">Live KPIs</h3>
            <div class="text-muted small">Real-time operational metrics &mdash; updated {{ $lastUpdated->format('H:i:s') }}</div>
        </div>
        <div class="d-flex gap-2">
            <button class="btn btn-sm btn-outline-secondary" onclick="location.reload()">
                <i class="bi bi-arrow-clockwise"></i> Refresh
            </button>
        </div>
    </div>

    {{-- KPI CARDS --}}
    <div class="row g-3 mb-4">

        <div class="col-6 col-md-2">
            <div class="card oneway-card p-3 h-100">
                <div class="d-flex align-items-center gap-3">
                    <div class="oneway-kpi__icon" style="background:rgba(59,130,246,.12);color:#3b82f6;">
                        <i class="bi bi-hourglass-split"></i>
                    </div>
                    <div>
                        <div class="fw-bold fs-4">{{ number_format($kpis['pending']) }}</div>
                        <div class="oneway-kpi__label">Pending</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-6 col-md-2">
            <div class="card oneway-card p-3 h-100">
                <div class="d-flex align-items-center gap-3">
                    <div class="oneway-kpi__icon" style="background:rgba(16,185,129,.12);color:#10b981;">
                        <i class="bi bi-geo-alt-fill"></i>
                    </div>
                    <div>
                        <div class="fw-bold fs-4">{{ number_format($kpis['ongoing']) }}</div>
                        <div class="oneway-kpi__label">Active Rides</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-6 col-md-2">
            <div class="card oneway-card p-3 h-100">
                <div class="d-flex align-items-center gap-3">
                    <div class="oneway-kpi__icon" style="background:rgba(107,114,128,.12);color:#6b7280;">
                        <i class="bi bi-check-circle"></i>
                    </div>
                    <div>
                        <div class="fw-bold fs-4">{{ number_format($kpis['completed']) }}</div>
                        <div class="oneway-kpi__label">Completed</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-6 col-md-2">
            <div class="card oneway-card p-3 h-100">
                <div class="d-flex align-items-center gap-3">
                    <div class="oneway-kpi__icon" style="background:rgba(239,68,68,.12);color:#ef4444;">
                        <i class="bi bi-x-circle"></i>
                    </div>
                    <div>
                        <div class="fw-bold fs-4">{{ number_format($kpis['cancelled']) }}</div>
                        <div class="oneway-kpi__label">Cancelled</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-6 col-md-2">
            <div class="card oneway-card p-3 h-100">
                <div class="d-flex align-items-center gap-3">
                    <div class="oneway-kpi__icon" style="background:rgba(139,92,246,.12);color:#8b5cf6;">
                        <i class="bi bi-calendar-day"></i>
                    </div>
                    <div>
                        <div class="fw-bold fs-4">{{ number_format($kpis['today']) }}</div>
                        <div class="oneway-kpi__label">Today Total</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-6 col-md-2">
            <div class="card oneway-card p-3 h-100">
                <div class="d-flex align-items-center gap-3">
                    <div class="oneway-kpi__icon" style="background:rgba(245,158,11,.12);color:#f59e0b;">
                        <i class="bi bi-currency-dollar"></i>
                    </div>
                    <div>
                        <div class="fw-bold fs-4">${{ number_format($kpis['revenue'], 0) }}</div>
                        <div class="oneway-kpi__label">Today Revenue</div>
                    </div>
                </div>
            </div>
        </div>

    </div>

    {{-- STATUS BREAKDOWN --}}
    <div class="row g-3">
        <div class="col-md-6">
            <div class="card oneway-card p-4 h-100">
                <h6 class="fw-semibold mb-3">Trip Status Breakdown</h6>
                @php
                    $total = max(1, $kpis['pending'] + $kpis['ongoing'] + $kpis['completed'] + $kpis['cancelled']);
                @endphp
                @foreach([
                    ['label'=>'Pending',   'val'=>$kpis['pending'],   'color'=>'#3b82f6'],
                    ['label'=>'Active',    'val'=>$kpis['ongoing'],   'color'=>'#10b981'],
                    ['label'=>'Completed', 'val'=>$kpis['completed'], 'color'=>'#6b7280'],
                    ['label'=>'Cancelled', 'val'=>$kpis['cancelled'], 'color'=>'#ef4444'],
                ] as $row)
                <div class="mb-3">
                    <div class="d-flex justify-content-between mb-1">
                        <span class="small">{{ $row['label'] }}</span>
                        <span class="small fw-semibold">{{ number_format($row['val']) }}</span>
                    </div>
                    <div class="progress" style="height:6px;">
                        <div class="progress-bar" style="width:{{ round($row['val']/$total*100) }}%;background:{{ $row['color'] }};"></div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        <div class="col-md-6">
            <div class="card oneway-card p-4 h-100">
                <h6 class="fw-semibold mb-3">Quick Links</h6>
                <div class="d-flex flex-column gap-2">
                    <a href="{{ Route::has('admin.control-room.index') ? route('admin.control-room.index') : '#' }}" class="btn btn-outline-primary btn-sm text-start">
                        <i class="bi bi-broadcast me-2"></i> Open Control Room
                    </a>
                    <a href="{{ Route::has('admin.alerts.index') ? route('admin.alerts.index') : '#' }}" class="btn btn-outline-warning btn-sm text-start">
                        <i class="bi bi-bell me-2"></i> View Safety Alerts
                    </a>
                    <a href="{{ Route::has('admin.cancellations.index') ? route('admin.cancellations.index') : '#' }}" class="btn btn-outline-danger btn-sm text-start">
                        <i class="bi bi-x-circle me-2"></i> Cancellations Report
                    </a>
                    <a href="{{ Route::has('admin.fleet-map') ? route('admin.fleet-map', ['type'=>'all-driver']) : '#' }}" class="btn btn-outline-secondary btn-sm text-start">
                        <i class="bi bi-map me-2"></i> Fleet Map
                    </a>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection
