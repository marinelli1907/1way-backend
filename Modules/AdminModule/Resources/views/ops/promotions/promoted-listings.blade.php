@section('title', 'Promoted Listings')
@extends('adminmodule::layouts.master')

@section('content')
<div class="container-fluid">
    @include('adminmodule::components.admin.page-header', ['title' => 'Promoted Listings', 'subtitle' => 'Banners, promoted content & events.'])
    @include('adminmodule::components.admin.kpi-cards', ['kpis' => $kpis ?? []])
    @include('adminmodule::components.admin.filters-row')

    {{-- Banner Listings --}}
    <div class="card oneway-card mb-4">
        <div class="card-header bg-transparent border-0"><h5 class="mb-0 fw-semibold">Banner Listings</h5></div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr><th>ID</th><th>Name</th><th>Position</th><th>Status</th><th>Updated</th></tr>
                    </thead>
                    <tbody>
                        @forelse(($items ?? collect()) as $item)
                        <tr>
                            <td>{{ $item->id ?? '—' }}</td>
                            <td>{{ $item->name ?? '—' }}</td>
                            <td>{{ $item->display_position ?? '—' }}</td>
                            <td>{{ ($item->is_active ?? false) ? 'Active' : 'Inactive' }}</td>
                            <td>{{ $item->updated_at ? $item->updated_at->format('M j, Y') : '—' }}</td>
                        </tr>
                        @empty
                        @include('adminmodule::components.admin.empty-state', ['colspan' => 5, 'message' => 'No banner listings yet.'])
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if(isset($items) && method_exists($items, 'links'))
            <div class="p-3">{{ $items->links() }}</div>
            @endif
        </div>
    </div>

    {{-- Promoted Events --}}
    <div class="card oneway-card">
        <div class="card-header bg-transparent border-0 d-flex justify-content-between align-items-center">
            <h5 class="mb-0 fw-semibold"><i class="bi bi-star me-2 text-warning"></i>Events — Promotion Manager</h5>
            <span class="badge bg-primary-subtle text-primary">{{ ($events ?? collect())->count() }} event(s)</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Event Title</th>
                            <th>Start</th>
                            <th>End</th>
                            <th>Visibility</th>
                            <th>Promoted</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse(($events ?? collect()) as $event)
                        <tr>
                            <td class="fw-medium">{{ $event->title }}</td>
                            <td class="small">{{ $event->start_at ? $event->start_at->format('M j, Y g:i A') : '—' }}</td>
                            <td class="small">{{ $event->end_at ? $event->end_at->format('M j, Y g:i A') : '—' }}</td>
                            <td>
                                @if($event->visibility === 'public')
                                    <span class="badge bg-success-subtle text-success">Public</span>
                                @else
                                    <span class="badge bg-secondary-subtle text-secondary">Private</span>
                                @endif
                            </td>
                            <td>
                                @if($event->is_promoted)
                                    <span class="badge bg-warning-subtle text-warning"><i class="bi bi-star-fill me-1"></i>Promoted</span>
                                @else
                                    <span class="badge bg-light text-muted">Not Promoted</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <form action="{{ route('admin.promoted-listings.toggle') }}" method="POST" class="d-inline">
                                    @csrf
                                    <input type="hidden" name="id" value="{{ $event->id }}">
                                    <button type="submit" class="btn btn-sm {{ $event->is_promoted ? 'btn-outline-warning' : 'btn-warning' }}">
                                        <i class="bi {{ $event->is_promoted ? 'bi-star-fill' : 'bi-star' }} me-1"></i>
                                        {{ $event->is_promoted ? 'Remove Promotion' : 'Promote' }}
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        @include('adminmodule::components.admin.empty-state', ['colspan' => 6, 'message' => 'No active events found. Create events from the Calendar section.'])
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if(isset($events) && method_exists($events, 'links'))
            <div class="p-3">{{ $events->links() }}</div>
            @endif
        </div>
    </div>

    <div class="text-muted small mt-3">Last updated: {{ now()->format('M j, Y g:i A') }}</div>
</div>
@endsection
