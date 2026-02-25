@section('title', 'Events List')
@extends('adminmodule::layouts.master')

@section('content')
<div class="container-fluid">

    {{-- HEADER --}}
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <nav aria-label="breadcrumb"><ol class="breadcrumb mb-1">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item active">Events List</li>
            </ol></nav>
            <h3 class="mb-0 fw-bold">Events List</h3>
            <div class="text-muted small">View and manage all events</div>
        </div>
        <div>
            <a href="{{ route('admin.events.manage') }}" class="btn btn-sm btn-primary">
                <i class="bi bi-plus-circle"></i> Create Event
            </a>
        </div>
    </div>

    {{-- KPI CARDS --}}
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-3">
            <div class="card oneway-card p-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="oneway-kpi__icon"><i class="bi bi-calendar-event"></i></div>
                    <div>
                        <div class="fw-bold fs-3">{{ $totalEvents ?? 0 }}</div>
                        <div class="oneway-kpi__label">Total Events</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card oneway-card p-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="oneway-kpi__icon"><i class="bi bi-check-circle text-success"></i></div>
                    <div>
                        <div class="fw-bold fs-3">{{ $activeEvents ?? 0 }}</div>
                        <div class="oneway-kpi__label">Active Events</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card oneway-card p-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="oneway-kpi__icon"><i class="bi bi-calendar-plus text-info"></i></div>
                    <div>
                        <div class="fw-bold fs-3">{{ $upcomingEvents ?? 0 }}</div>
                        <div class="oneway-kpi__label">Upcoming</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card oneway-card p-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="oneway-kpi__icon"><i class="bi bi-star text-warning"></i></div>
                    <div>
                        <div class="fw-bold fs-3">{{ $promotedEvents ?? 0 }}</div>
                        <div class="oneway-kpi__label">Promoted</div>
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
                    <label class="form-label small">Visibility</label>
                    <select name="visibility" class="form-select form-select-sm">
                        <option value="">All</option>
                        <option value="public" {{ ($visibility ?? '') == 'public' ? 'selected' : '' }}>Public</option>
                        <option value="private" {{ ($visibility ?? '') == 'private' ? 'selected' : '' }}>Private</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small">Status</label>
                    <select name="status" class="form-select form-select-sm">
                        <option value="">All</option>
                        <option value="1" {{ ($status ?? '') === '1' ? 'selected' : '' }}>Active</option>
                        <option value="0" {{ ($status ?? '') === '0' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label small">Search</label>
                    <input type="text" name="search" value="{{ $search ?? '' }}" placeholder="Event title..." class="form-control form-control-sm">
                </div>
                <div class="col-12">
                    <button type="submit" class="btn btn-sm btn-primary">Apply Filters</button>
                    <a href="{{ route('admin.events.index') }}" class="btn btn-sm btn-outline-secondary">Clear</a>
                </div>
            </form>
        </div>
    </div>

    {{-- EVENTS TABLE --}}
    <div class="card oneway-card">
        <div class="card-header bg-transparent border-0">
            <h5 class="mb-0 fw-semibold">Events</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Title</th>
                            <th>Start</th>
                            <th>End</th>
                            <th>Visibility</th>
                            <th>Promoted</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($events as $event)
                        <tr>
                            <td>
                                <div class="fw-semibold">{{ $event->title }}</div>
                                @if($event->description)
                                <div class="text-muted small">{{ Str::limit($event->description, 60) }}</div>
                                @endif
                            </td>
                            <td class="small">{{ $event->start_at->format('M j, Y g:i A') }}</td>
                            <td class="small">{{ $event->end_at->format('M j, Y g:i A') }}</td>
                            <td>
                                <span class="badge bg-{{ $event->visibility === 'public' ? 'info' : 'secondary' }}">
                                    {{ ucfirst($event->visibility) }}
                                </span>
                                @if($event->visibility === 'private' && $event->private_code)
                                <span class="text-muted small d-block">Code: {{ $event->private_code }}</span>
                                @endif
                            </td>
                            <td>
                                @if($event->is_promoted)
                                <span class="badge bg-warning text-dark"><i class="bi bi-star-fill"></i> Promoted</span>
                                @else
                                <span class="text-muted small">No</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-{{ $event->is_active ? 'success' : 'secondary' }}">
                                    {{ $event->is_active ? 'Active' : 'Inactive' }}
                                </span>
                                @if($event->start_at->isFuture())
                                <span class="badge bg-primary">Upcoming</span>
                                @elseif($event->end_at->isPast())
                                <span class="badge bg-dark">Past</span>
                                @else
                                <span class="badge bg-success">Ongoing</span>
                                @endif
                            </td>
                            <td>
                                <div class="d-flex gap-1">
                                    <form method="POST" action="{{ route('admin.events.toggle-status') }}" class="d-inline">
                                        @csrf
                                        <input type="hidden" name="event_id" value="{{ $event->id }}">
                                        <button type="submit" class="btn btn-sm btn-outline-{{ $event->is_active ? 'warning' : 'success' }}" title="{{ $event->is_active ? 'Deactivate' : 'Activate' }}">
                                            <i class="bi bi-{{ $event->is_active ? 'pause' : 'play' }}"></i>
                                        </button>
                                    </form>
                                    <form method="POST" action="{{ route('admin.events.toggle-promoted') }}" class="d-inline">
                                        @csrf
                                        <input type="hidden" name="event_id" value="{{ $event->id }}">
                                        <button type="submit" class="btn btn-sm btn-outline-{{ $event->is_promoted ? 'secondary' : 'warning' }}" title="{{ $event->is_promoted ? 'Remove promotion' : 'Promote' }}">
                                            <i class="bi bi-star{{ $event->is_promoted ? '-fill' : '' }}"></i>
                                        </button>
                                    </form>
                                    <a href="{{ route('admin.events.manage') }}" class="btn btn-sm btn-outline-primary" title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="7" class="text-center text-muted py-4">
                            <div class="py-4">
                                <i class="bi bi-calendar-x fs-1 text-muted d-block mb-2"></i>
                                <div>No events found for the selected filters</div>
                                <a href="{{ route('admin.events.manage') }}" class="btn btn-sm btn-primary mt-2">Create Event</a>
                            </div>
                        </td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if(method_exists($events, 'links'))
            <div class="card-footer bg-transparent border-0">
                {{ $events->links() }}
            </div>
            @endif
        </div>
    </div>

    <div class="text-muted small mt-3">Last updated: {{ now()->format('M j, Y g:i A') }}</div>
</div>
@endsection
