@section('title', 'Reviews & Ratings')
@extends('adminmodule::layouts.master')
@section('content')
<div class="container-fluid">
    @include('adminmodule::components.admin.page-header', ['title' => 'Reviews & Ratings', 'subtitle' => 'Customer and driver reviews.'])
    @include('adminmodule::components.admin.kpi-cards', ['kpis' => $kpis ?? []])
    @include('adminmodule::components.admin.filters-row')
    <div class="card oneway-card">
        <div class="card-header bg-transparent border-0"><h5 class="mb-0 fw-semibold">Reviews</h5></div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light"><tr><th>ID</th><th>Trip / Parcel</th><th>Rating</th><th>Date</th></tr></thead>
                    <tbody>
                        @forelse(($items ?? collect()) as $item)
                        <tr><td>{{ $item->id ?? '—' }}</td><td>{{ $item->trip_request_id ?? $item->parcel_id ?? '—' }}</td><td>{{ $item->rating ?? '—' }}</td><td>{{ isset($item->created_at) ? $item->created_at->format('M j, Y') : '—' }}</td></tr>
                        @empty
                        @include('adminmodule::components.admin.empty-state', ['colspan' => 4])
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="text-muted small mt-3">Last updated: {{ now()->format('M j, Y g:i A') }}</div>
</div>
@endsection
