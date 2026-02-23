@section('title', 'Support Tickets')
@extends('adminmodule::layouts.master')

@section('content')
<div class="container-fluid">

    {{-- HEADER --}}
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <nav aria-label="breadcrumb"><ol class="breadcrumb mb-1">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item active">Support Inbox</li>
            </ol></nav>
            <h3 class="mb-0 fw-bold">Support Inbox / Tickets</h3>
            <div class="text-muted small">Manage customer support conversations and tickets</div>
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
                    <div class="oneway-kpi__icon"><i class="bi bi-inbox"></i></div>
                    <div>
                        <div class="fw-bold fs-3">{{ $totalChannels ?? 0 }}</div>
                        <div class="oneway-kpi__label">Total Tickets</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card oneway-card p-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="oneway-kpi__icon"><i class="bi bi-circle-fill text-danger"></i></div>
                    <div>
                        <div class="fw-bold fs-3">{{ $openChannels ?? 0 }}</div>
                        <div class="oneway-kpi__label">Open</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card oneway-card p-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="oneway-kpi__icon"><i class="bi bi-check-circle-fill text-success"></i></div>
                    <div>
                        <div class="fw-bold fs-3">{{ $closedChannels ?? 0 }}</div>
                        <div class="oneway-kpi__label">Closed</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card oneway-card p-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="oneway-kpi__icon"><i class="bi bi-calendar-day"></i></div>
                    <div>
                        <div class="fw-bold fs-3">{{ $todayChannels ?? 0 }}</div>
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
                    <input type="date" name="date_from" value="{{ $dateFrom ?? '' }}" class="form-control form-control-sm">
                </div>
                <div class="col-md-2">
                    <label class="form-label small">To Date</label>
                    <input type="date" name="date_to" value="{{ $dateTo ?? '' }}" class="form-control form-control-sm">
                </div>
                <div class="col-md-2">
                    <label class="form-label small">Status</label>
                    <select name="status" class="form-select form-select-sm">
                        <option value="">All Status</option>
                        <option value="1" {{ ($status ?? '') === '1' ? 'selected' : '' }}>Open</option>
                        <option value="0" {{ ($status ?? '') === '0' ? 'selected' : '' }}>Closed</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label small">Search</label>
                    <input type="text" name="search" value="{{ $search ?? '' }}" placeholder="Search by title..." class="form-control form-control-sm">
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-sm btn-primary w-100">Filter</button>
                </div>
            </form>
        </div>
    </div>

    {{-- TICKETS TABLE --}}
    <div class="card oneway-card">
        <div class="card-header bg-transparent border-0">
            <h5 class="mb-0 fw-semibold">Support Tickets</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Ticket ID</th>
                            <th>Title</th>
                            <th>Participants</th>
                            <th>Status</th>
                            <th>Last Updated</th>
                            <th>Created</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($channels as $channel)
                        <tr>
                            <td><span class="badge bg-secondary">{{ Str::limit($channel->id ?? '—', 8, '') }}</span></td>
                            <td>
                                <div class="fw-semibold">{{ Str::limit($channel->title ?? 'Untitled', 40, '...') }}</div>
                                @if($channel->description)
                                <div class="text-muted small">{{ Str::limit($channel->description, 50, '...') }}</div>
                                @endif
                            </td>
                            <td>
                                @if($channel->channelUsers && $channel->channelUsers->count() > 0)
                                    @foreach($channel->channelUsers->take(2) as $user)
                                        <div class="small">{{ $user->user?->first_name ?? '—' }} {{ $user->user?->last_name ?? '' }}</div>
                                    @endforeach
                                    @if($channel->channelUsers->count() > 2)
                                        <div class="small text-muted">+{{ $channel->channelUsers->count() - 2 }} more</div>
                                    @endif
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-{{ $channel->is_active ? 'danger' : 'success' }}">
                                    {{ $channel->is_active ? 'Open' : 'Closed' }}
                                </span>
                            </td>
                            <td class="small">{{ $channel->updated_at?->diffForHumans() ?? '—' }}</td>
                            <td class="small">{{ $channel->created_at?->format('M j, Y') ?? '—' }}</td>
                            <td>
                                <a href="{{ route('admin.chatting') }}?channel={{ $channel->id }}" class="btn btn-sm btn-outline-primary">
                                    View
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="7" class="text-center text-muted py-4">
                            <div class="py-4">
                                <i class="bi bi-inbox fs-1 text-muted d-block mb-2"></i>
                                <div>No support tickets found</div>
                            </div>
                        </td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if(method_exists($channels, 'links'))
            <div class="card-footer bg-transparent border-0">
                {{ $channels->links() }}
            </div>
            @endif
        </div>
    </div>

    <div class="text-muted small mt-3">Last updated: {{ now()->format('M j, Y g:i A') }}</div>
</div>
@endsection
