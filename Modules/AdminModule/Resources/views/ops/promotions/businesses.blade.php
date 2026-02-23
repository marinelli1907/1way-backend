@section('title', 'Businesses')
@extends('adminmodule::layouts.master')

@section('content')
<div class="container-fluid">
    @include('adminmodule::components.admin.page-header', ['title' => 'Businesses', 'subtitle' => 'Restaurants, bars, and partner venues.'])
    @include('adminmodule::components.admin.kpi-cards', ['kpis' => $kpis ?? []])
    @include('adminmodule::components.admin.filters-row')

    <div class="card oneway-card">
        <div class="card-header bg-transparent border-0"><h5 class="mb-0 fw-semibold">Partners</h5></div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light"><tr><th>ID</th><th>Name</th><th>Type</th><th>Status</th><th>Updated</th></tr></thead>
                    <tbody>
                        @forelse(($items ?? collect()) as $item)
                        <tr>
                            <td>{{ $item->id ?? $item['id'] ?? '—' }}</td>
                            <td>{{ $item->name ?? $item['name'] ?? '—' }}</td>
                            <td>{{ $item->type ?? $item['type'] ?? '—' }}</td>
                            <td>{{ $item->is_active ?? $item['is_active'] ?? '—' }}</td>
                            <td>{{ isset($item->updated_at) ? $item->updated_at->format('M j, Y') : (isset($item['updated_at']) ? \Carbon\Carbon::parse($item['updated_at'])->format('M j, Y') : '—') }}</td>
                        </tr>
                        @empty
                        @include('adminmodule::components.admin.empty-state', ['colspan' => 5])
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="text-muted small mt-3">Last updated: {{ now()->format('M j, Y g:i A') }}</div>
</div>
@endsection
