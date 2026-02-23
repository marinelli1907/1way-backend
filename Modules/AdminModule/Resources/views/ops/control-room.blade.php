@section('title', 'Control Room')
@extends('adminmodule::layouts.master')

@section('content')
<div class="container-fluid">

    {{-- HEADER --}}
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <nav aria-label="breadcrumb"><ol class="breadcrumb mb-1">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item active">Control Room</li>
            </ol></nav>
            <h3 class="mb-0 fw-bold">Dispatch / Control Room</h3>
            <div class="text-muted small">Real-time trip monitoring and dispatch management</div>
        </div>
        <div>
            <button class="btn btn-sm btn-primary" onclick="location.reload()">
                <i class="bi bi-arrow-clockwise"></i> Refresh
            </button>
        </div>
    </div>

    {{-- KPI CARDS --}}
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-3">
            <div class="card oneway-card p-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="oneway-kpi__icon"><i class="bi bi-car-front-fill text-warning"></i></div>
                    <div>
                        <div class="fw-bold fs-3">{{ $ongoingCount ?? 0 }}</div>
                        <div class="oneway-kpi__label">Active Trips</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card oneway-card p-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="oneway-kpi__icon"><i class="bi bi-hourglass-split text-info"></i></div>
                    <div>
                        <div class="fw-bold fs-3">{{ $pendingCount ?? 0 }}</div>
                        <div class="oneway-kpi__label">Pending Requests</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card oneway-card p-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="oneway-kpi__icon"><i class="bi bi-calendar2-day text-secondary"></i></div>
                    <div>
                        <div class="fw-bold fs-3">{{ $scheduledTodayCount ?? 0 }}</div>
                        <div class="oneway-kpi__label">Scheduled Today</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card oneway-card p-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="oneway-kpi__icon"><i class="bi bi-wifi text-success"></i></div>
                    <div>
                        <div class="fw-bold fs-3">{{ $onlineDriverCount ?? 0 }}</div>
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
                        <div class="fw-bold fs-3">{{ $availableDriverCount ?? 0 }}</div>
                        <div class="oneway-kpi__label">Available Now</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        {{-- ONGOING TRIPS --}}
        <div class="col-lg-6 mb-4">
            <div class="card oneway-card">
                <div class="card-header bg-transparent border-0">
                    <h5 class="mb-0 fw-semibold">Ongoing Trips</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive" style="max-height: 500px; overflow-y: auto;">
                        <table class="table table-hover mb-0">
                            <thead class="table-light sticky-top">
                                <tr>
                                    <th>Trip ID</th>
                                    <th>Customer</th>
                                    <th>Driver</th>
                                    <th>Status</th>
                                    <th>Updated</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($ongoingTrips as $trip)
                                <tr>
                                    <td>
                                        <a href="{{ route('admin.trip.show', $trip->id) }}" class="text-decoration-none">
                                            {{ Str::limit($trip->ref_id ?? $trip->id, 10, '') }}
                                        </a>
                                    </td>
                                    <td>{{ $trip->customer?->first_name ?? '—' }} {{ $trip->customer?->last_name ?? '' }}</td>
                                    <td>{!! $trip->driver ? e($trip->driver->first_name ?? '') . ' ' . e($trip->driver->last_name ?? '') : '<span class="text-muted">Unassigned</span>' !!}</td>
                                    <td><span class="badge bg-warning">{{ ucfirst(str_replace('_',' ', $trip->current_status ?? '')) }}</span></td>
                                    <td class="small">{{ $trip->updated_at?->diffForHumans() ?? '—' }}</td>
                                </tr>
                                @empty
                                <tr><td colspan="5" class="text-center text-muted py-4">No ongoing trips</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        {{-- PENDING REQUESTS --}}
        <div class="col-lg-6 mb-4">
            <div class="card oneway-card">
                <div class="card-header bg-transparent border-0">
                    <h5 class="mb-0 fw-semibold">Pending Requests</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive" style="max-height: 500px; overflow-y: auto;">
                        <table class="table table-hover mb-0">
                            <thead class="table-light sticky-top">
                                <tr>
                                    <th>Trip ID</th>
                                    <th>Customer</th>
                                    <th>Pickup</th>
                                    <th>Created</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($pendingTrips as $trip)
                                <tr>
                                    <td>
                                        <a href="{{ route('admin.trip.show', $trip->id) }}" class="text-decoration-none">
                                            {{ Str::limit($trip->ref_id ?? $trip->id, 10, '') }}
                                        </a>
                                    </td>
                                    <td>{{ $trip->customer?->first_name ?? '—' }} {{ $trip->customer?->last_name ?? '' }}</td>
                                    <td class="small">{{ Str::limit($trip->pickup_address ?? '—', 30, '...') }}</td>
                                    <td class="small">{{ $trip->created_at?->diffForHumans() ?? '—' }}</td>
                                    <td>
                                        <a href="{{ route('admin.trip.show', $trip->id) }}" class="btn btn-sm btn-primary">
                                            View
                                        </a>
                                    </td>
                                </tr>
                                @empty
                                <tr><td colspan="5" class="text-center text-muted py-4">No pending requests</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ACTIVE DRIVERS --}}
    <div class="card oneway-card">
        <div class="card-header bg-transparent border-0">
            <h5 class="mb-0 fw-semibold">Active Drivers</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Driver</th>
                            <th>Vehicle</th>
                            <th>Status</th>
                            <th>Availability</th>
                            <th>Last Seen</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($activeDrivers as $driver)
                        <tr>
                            <td>{{ $driver->user?->first_name ?? '—' }} {{ $driver->user?->last_name ?? '' }}</td>
                            <td>{{ $driver->vehicle_model ?? '—' }} {{ $driver->vehicle_number ?? '' }}</td>
                            <td>
                                <span class="badge bg-{{ $driver->is_online ? 'success' : 'secondary' }}">
                                    {{ $driver->is_online ? 'Online' : 'Offline' }}
                                </span>
                            </td>
                            <td>
                                <span class="badge bg-{{ $driver->availability_status === 'available' ? 'primary' : 'warning' }}">
                                    {{ ucfirst($driver->availability_status ?? 'unknown') }}
                                </span>
                            </td>
                            <td class="small">{{ $driver->updated_at?->diffForHumans() ?? '—' }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="5" class="text-center text-muted py-4">No active drivers</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- LAST 50 ACTIONS (recent trip updates) --}}
    <div class="card oneway-card mt-4">
        <div class="card-header bg-transparent border-0">
            <h5 class="mb-0 fw-semibold">Last 50 Actions</h5>
            <div class="text-muted small">Most recently updated trips</div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                <table class="table table-hover mb-0">
                    <thead class="table-light sticky-top">
                        <tr>
                            <th>Trip ID</th>
                            <th>Customer</th>
                            <th>Driver</th>
                            <th>Status</th>
                            <th>Updated</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentActions ?? [] as $trip)
                        <tr>
                            <td>
                                <a href="{{ route('admin.trip.show', $trip->id) }}" class="text-decoration-none">
                                    {{ Str::limit($trip->ref_id ?? $trip->id, 10, '') }}
                                </a>
                            </td>
                            <td>{{ $trip->customer?->first_name ?? '—' }} {{ $trip->customer?->last_name ?? '' }}</td>
                            <td>{{ $trip->driver?->first_name ?? '—' }} {{ $trip->driver?->last_name ?? '' }}</td>
                            <td><span class="badge bg-secondary">{{ ucfirst(str_replace('_',' ', $trip->current_status ?? '—')) }}</span></td>
                            <td class="small">{{ $trip->updated_at?->diffForHumans() ?? '—' }}</td>
                            <td><a href="{{ route('admin.trip.show', $trip->id) }}" class="btn btn-sm btn-outline-primary">View</a></td>
                        </tr>
                        @empty
                        <tr><td colspan="6" class="text-center text-muted py-4">No recent actions</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="text-muted small mt-3">Last updated: {{ now()->format('M j, Y g:i A') }}</div>
</div>
@endsection

@push('script')
<script>
// Auto refresh every 30 seconds
setTimeout(() => location.reload(), 30000);
</script>
@endpush
