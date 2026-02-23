@section('title', 'Revenue Reports')
@extends('adminmodule::layouts.master')
@section('content')
<div class="container-fluid">
    @include('adminmodule::components.admin.page-header', ['title' => 'Revenue Reports', 'subtitle' => 'Daily, weekly, monthly.'])
    @include('adminmodule::components.admin.kpi-cards', ['kpis' => $kpis ?? []])
    @include('adminmodule::components.admin.filters-row')
    <div class="card oneway-card">
        <div class="card-header bg-transparent border-0"><h5 class="mb-0 fw-semibold">Reports</h5></div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light"><tr><th>Period</th><th>Revenue</th><th>Trips</th><th>Date</th></tr></thead>
                    <tbody>
                        @forelse(($items ?? collect()) as $item)
                        <tr><td>{{ $item->period ?? '—' }}</td><td>{{ $item->revenue ?? $item->amount ?? '—' }}</td><td>{{ $item->trips ?? '—' }}</td><td>{{ isset($item->created_at) ? $item->created_at->format('M j, Y') : '—' }}</td></tr>
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
