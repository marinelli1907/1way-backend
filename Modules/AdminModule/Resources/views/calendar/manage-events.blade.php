@section('title', 'Manage Events')
@extends('adminmodule::layouts.master')

@section('content')
<div class="container-fluid">

    {{-- HEADER --}}
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <nav aria-label="breadcrumb"><ol class="breadcrumb mb-1">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.events.index') }}">Events</a></li>
                <li class="breadcrumb-item active">Manage Events</li>
            </ol></nav>
            <h3 class="mb-0 fw-bold">Create / Manage Events</h3>
            <div class="text-muted small">Create and manage events for scheduled rides</div>
        </div>
        <div>
            <a href="{{ route('admin.events.index') }}" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Back to Events
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
                        <div class="oneway-kpi__label">Upcoming Events</div>
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
                        <div class="oneway-kpi__label">Promoted Events</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- CREATE EVENT FORM --}}
    <div class="card oneway-card mb-4">
        <div class="card-header bg-transparent border-0">
            <h5 class="mb-0 fw-semibold"><i class="bi bi-plus-circle"></i> Create New Event</h5>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('admin.events.store') }}">
                @csrf
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">Event Title <span class="text-danger">*</span></label>
                        <input type="text" name="title" value="{{ old('title') }}" class="form-control @error('title') is-invalid @enderror" placeholder="e.g. Downtown Music Festival" required>
                        @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-semibold">Start Date/Time <span class="text-danger">*</span></label>
                        <input type="datetime-local" name="start_at" value="{{ old('start_at') }}" class="form-control @error('start_at') is-invalid @enderror" required>
                        @error('start_at')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-semibold">End Date/Time <span class="text-danger">*</span></label>
                        <input type="datetime-local" name="end_at" value="{{ old('end_at') }}" class="form-control @error('end_at') is-invalid @enderror" required>
                        @error('end_at')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-12">
                        <label class="form-label small fw-semibold">Description</label>
                        <textarea name="description" rows="3" class="form-control @error('description') is-invalid @enderror" placeholder="Describe the event...">{{ old('description') }}</textarea>
                        @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-semibold">Timezone</label>
                        <select name="timezone" class="form-select">
                            <option value="America/New_York" {{ old('timezone', 'America/New_York') == 'America/New_York' ? 'selected' : '' }}>Eastern (ET)</option>
                            <option value="America/Chicago" {{ old('timezone') == 'America/Chicago' ? 'selected' : '' }}>Central (CT)</option>
                            <option value="America/Denver" {{ old('timezone') == 'America/Denver' ? 'selected' : '' }}>Mountain (MT)</option>
                            <option value="America/Los_Angeles" {{ old('timezone') == 'America/Los_Angeles' ? 'selected' : '' }}>Pacific (PT)</option>
                            <option value="UTC" {{ old('timezone') == 'UTC' ? 'selected' : '' }}>UTC</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-semibold">Visibility <span class="text-danger">*</span></label>
                        <select name="visibility" id="visibility-select" class="form-select @error('visibility') is-invalid @enderror" required>
                            <option value="public" {{ old('visibility', 'public') == 'public' ? 'selected' : '' }}>Public</option>
                            <option value="private" {{ old('visibility') == 'private' ? 'selected' : '' }}>Private</option>
                        </select>
                        @error('visibility')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-3" id="private-code-field" style="{{ old('visibility') == 'private' ? '' : 'display: none;' }}">
                        <label class="form-label small fw-semibold">Private Code</label>
                        <input type="text" name="private_code" value="{{ old('private_code') }}" class="form-control @error('private_code') is-invalid @enderror" placeholder="Access code">
                        @error('private_code')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-12 mt-3">
                        <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg"></i> Create Event</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- FILTERS --}}
    <div class="card oneway-card mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-4">
                    <label class="form-label small">Search</label>
                    <input type="text" name="search" value="{{ $search ?? '' }}" placeholder="Event title..." class="form-control form-control-sm">
                </div>
                <div class="col-md-4">
                    <label class="form-label small">Status</label>
                    <select name="status" class="form-select form-select-sm">
                        <option value="">All</option>
                        <option value="1" {{ ($status ?? '') === '1' ? 'selected' : '' }}>Active</option>
                        <option value="0" {{ ($status ?? '') === '0' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>
                <div class="col-12">
                    <button type="submit" class="btn btn-sm btn-primary">Apply Filters</button>
                    <a href="{{ route('admin.events.manage') }}" class="btn btn-sm btn-outline-secondary">Clear</a>
                </div>
            </form>
        </div>
    </div>

    {{-- EVENTS TABLE --}}
    <div class="card oneway-card">
        <div class="card-header bg-transparent border-0">
            <h5 class="mb-0 fw-semibold">All Events</h5>
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
                            <th>Status</th>
                            <th>Promoted</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($events as $event)
                        <tr>
                            <td class="fw-semibold">{{ $event->title }}</td>
                            <td class="small">{{ $event->start_at->format('M j, Y g:i A') }}</td>
                            <td class="small">{{ $event->end_at->format('M j, Y g:i A') }}</td>
                            <td>
                                <span class="badge bg-{{ $event->visibility === 'public' ? 'info' : 'secondary' }}">
                                    {{ ucfirst($event->visibility) }}
                                </span>
                            </td>
                            <td>
                                <form method="POST" action="{{ route('admin.events.toggle-status') }}" class="d-inline">
                                    @csrf
                                    <input type="hidden" name="event_id" value="{{ $event->id }}">
                                    <button type="submit" class="btn btn-sm btn-{{ $event->is_active ? 'success' : 'outline-secondary' }}" title="Toggle active status">
                                        {{ $event->is_active ? 'Active' : 'Inactive' }}
                                    </button>
                                </form>
                            </td>
                            <td>
                                <form method="POST" action="{{ route('admin.events.toggle-promoted') }}" class="d-inline">
                                    @csrf
                                    <input type="hidden" name="event_id" value="{{ $event->id }}">
                                    <button type="submit" class="btn btn-sm btn-{{ $event->is_promoted ? 'warning' : 'outline-secondary' }}" title="Toggle promoted">
                                        <i class="bi bi-star{{ $event->is_promoted ? '-fill' : '' }}"></i>
                                        {{ $event->is_promoted ? 'Yes' : 'No' }}
                                    </button>
                                </form>
                            </td>
                            <td>
                                <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editModal-{{ $event->id }}">
                                    <i class="bi bi-pencil"></i> Edit
                                </button>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="7" class="text-center text-muted py-4">
                            <div class="py-4">
                                <i class="bi bi-calendar-x fs-1 text-muted d-block mb-2"></i>
                                <div>No events found. Create one using the form above.</div>
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

    {{-- EDIT MODALS --}}
    @foreach($events as $event)
    <div class="modal fade" id="editModal-{{ $event->id }}" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form method="POST" action="{{ route('admin.events.update', $event->id) }}">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">Edit Event: {{ $event->title }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-12">
                                <label class="form-label small fw-semibold">Title</label>
                                <input type="text" name="title" value="{{ $event->title }}" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">Start</label>
                                <input type="datetime-local" name="start_at" value="{{ $event->start_at->format('Y-m-d\TH:i') }}" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">End</label>
                                <input type="datetime-local" name="end_at" value="{{ $event->end_at->format('Y-m-d\TH:i') }}" class="form-control" required>
                            </div>
                            <div class="col-md-12">
                                <label class="form-label small fw-semibold">Description</label>
                                <textarea name="description" rows="3" class="form-control">{{ $event->description }}</textarea>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-semibold">Timezone</label>
                                <select name="timezone" class="form-select">
                                    <option value="America/New_York" {{ $event->timezone == 'America/New_York' ? 'selected' : '' }}>Eastern (ET)</option>
                                    <option value="America/Chicago" {{ $event->timezone == 'America/Chicago' ? 'selected' : '' }}>Central (CT)</option>
                                    <option value="America/Denver" {{ $event->timezone == 'America/Denver' ? 'selected' : '' }}>Mountain (MT)</option>
                                    <option value="America/Los_Angeles" {{ $event->timezone == 'America/Los_Angeles' ? 'selected' : '' }}>Pacific (PT)</option>
                                    <option value="UTC" {{ $event->timezone == 'UTC' ? 'selected' : '' }}>UTC</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-semibold">Visibility</label>
                                <select name="visibility" class="form-select">
                                    <option value="public" {{ $event->visibility == 'public' ? 'selected' : '' }}>Public</option>
                                    <option value="private" {{ $event->visibility == 'private' ? 'selected' : '' }}>Private</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-semibold">Private Code</label>
                                <input type="text" name="private_code" value="{{ $event->private_code }}" class="form-control" placeholder="Access code (if private)">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endforeach

    <div class="text-muted small mt-3">Last updated: {{ now()->format('M j, Y g:i A') }}</div>
</div>

@push('scripts')
<script>
document.getElementById('visibility-select')?.addEventListener('change', function() {
    document.getElementById('private-code-field').style.display = this.value === 'private' ? '' : 'none';
});
</script>
@endpush
@endsection
