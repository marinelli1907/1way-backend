{{-- Modules/AdminModule/Resources/views/dashboard.blade.php --}}
@section('title', 'Dashboard')

@extends('adminmodule::layouts.master')

@push('css_or_js')
<link rel="stylesheet" href="{{ asset('assets/admin-module/plugins/apex/apexcharts.css') }}">
@endpush

@section('content')

<div class="container-fluid">

    {{-- PAGE HEADER --}}
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h3 class="mb-1 fw-bold">Dashboard</h3>
            <div class="text-muted">
                Welcome back, {{ auth('web')->user()?->first_name ?? 'Admin' }}
            </div>
        </div>
    </div>

    @can('dashboard')

    {{-- KPI ROW --}}
    <div class="row g-3 mb-4">

        <div class="col-md-3">
            <div class="card oneway-card p-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="oneway-kpi__icon">
                        <i class="bi bi-people"></i>
                    </div>
                    <div>
                        <div class="fw-bold fs-4">{{ abbreviateNumber($customers) }}</div>
                        <div class="oneway-kpi__label">Active Customers</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card oneway-card p-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="oneway-kpi__icon">
                        <i class="bi bi-person-badge"></i>
                    </div>
                    <div>
                        <div class="fw-bold fs-4">{{ abbreviateNumber($drivers) }}</div>
                        <div class="oneway-kpi__label">Active Drivers</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card oneway-card p-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="oneway-kpi__icon">
                        <i class="bi bi-cash-coin"></i>
                    </div>
                    <div>
                        <div class="fw-bold fs-4">{{ abbreviateNumberWithSymbol($totalEarning) }}</div>
                        <div class="oneway-kpi__label">Total Revenue</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card oneway-card p-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="oneway-kpi__icon">
                        <i class="bi bi-car-front-fill"></i>
                    </div>
                    <div>
                        <div class="fw-bold fs-4">{{ abbreviateNumber($totalTrips) }}</div>
                        <div class="oneway-kpi__label">Total Trips</div>
                    </div>
                </div>
            </div>
        </div>

    </div>

    {{-- CHART + ZONE STATS --}}
    <div class="row g-4 mb-4">

        <div class="col-lg-8">
            <div class="card oneway-card">
                <div class="card-header bg-transparent border-0 d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-0 fw-semibold">Admin Earnings</h5>
                        <div class="text-muted fs-13">Trips & commission over time</div>
                    </div>
                    <div class="d-flex gap-2">
                        <select id="rideZone" class="form-select form-select-sm">
                            <option value="all">All Zones</option>
                            @foreach($zones as $zone)
                                <option value="{{ $zone->id }}">{{ $zone->name }}</option>
                            @endforeach
                        </select>

                        <select id="rideDate" class="form-select form-select-sm">
                            <option value="today">Today</option>
                            <option value="this_week">This Week</option>
                            <option value="this_month">This Month</option>
                            <option value="all_time">All Time</option>
                        </select>
                    </div>
                </div>

                <div class="card-body">
                    <div id="apex_line-chart" style="min-height:360px;"></div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card oneway-card h-100">
                <div class="card-header bg-transparent border-0">
                    <h5 class="mb-0 fw-semibold">Zone Activity</h5>
                    <div class="text-muted fs-13">Trips by service zone</div>
                </div>

                <div class="card-body">
                    <select id="zoneWiseRideDate" class="form-select form-select-sm mb-3">
                        <option value="today">Today</option>
                        <option value="this_week">This Week</option>
                        <option value="this_month">This Month</option>
                        <option value="all_time">All Time</option>
                    </select>

                    <div id="zoneWiseTripStatistics"></div>
                </div>
            </div>
        </div>

    </div>

    {{-- TRANSACTIONS + ACTIVITY --}}
    <div class="row g-4">

        <div class="col-lg-6">
            <div class="card oneway-card">
                <div class="card-header bg-transparent border-0 d-flex justify-content-between">
                    <h5 class="mb-0 fw-semibold">Recent Transactions</h5>
                    <a href="{{ route('admin.transaction.index') }}" class="small">View All</a>
                </div>

                <div class="card-body">
                    @forelse($transactions as $tx)
                        <div class="d-flex justify-content-between border-bottom py-2">
                            <div>
                                <div class="fw-semibold">
                                    {{ $tx->credit > 0 ? 'Credit' : 'Debit' }}
                                </div>
                                <div class="text-muted fs-13">
                                    {{ date(DATE_FORMAT, strtotime($tx->created_at)) }}
                                </div>
                            </div>
                            <div class="fw-bold">
                                {{ getCurrencyFormat($tx->credit ?: $tx->debit) }}
                            </div>
                        </div>
                    @empty
                        <div class="text-muted text-center py-4">
                            No transactions yet
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card oneway-card">
                <div class="card-header bg-transparent border-0 d-flex justify-content-between">
                    <h5 class="mb-0 fw-semibold">Recent Trips</h5>
                    <a href="{{ route('admin.trip.index',['all']) }}" class="small">View All</a>
                </div>

                <div class="card-body" id="recent_trips_activity">
                    {{-- Loaded via AJAX --}}
                </div>
            </div>
        </div>

    </div>

    @endcan
</div>

@endsection

@push('script')
<script src="{{ asset('assets/admin-module/plugins/apex/apexcharts.min.js') }}"></script>
<script src="{{ asset('assets/admin-module/js/admin-module/dashboard.js') }}"></script>

<script>
"use strict";

loadPartialView('{{ route('admin.recent-trip-activity') }}', '#recent_trips_activity', null);

$("#rideZone, #rideDate").on('change', function(){
    adminEarningStatistics($("#rideDate").val(), $("#rideZone").val());
});

$("#zoneWiseRideDate").on('change', function(){
    zoneWiseTripStatistics($(this).val());
});

adminEarningStatistics('today','all');
zoneWiseTripStatistics('today');
</script>

@include('adminmodule::partials.dashboard.map')
@endpush
