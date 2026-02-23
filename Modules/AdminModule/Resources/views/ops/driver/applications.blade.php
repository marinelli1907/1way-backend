@section('title', 'Driver Applications')
@extends('adminmodule::layouts.master')

@section('content')
<div class="container-fluid">
    @include('adminmodule::components.admin.page-header', ['title' => 'Driver Applications', 'subtitle' => 'Onboarding and applications.'])
    @include('adminmodule::components.admin.kpi-cards', ['kpis' => $kpis ?? []])
    @include('adminmodule::components.admin.filters-row')

    <div class="card oneway-card">
        <div class="card-header bg-transparent border-0"><h5 class="mb-0 fw-semibold">Applications</h5></div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light"><tr><th>ID</th><th>Driver ID</th><th>Profile</th><th>Docs</th><th>Approved</th><th>Updated</th></tr></thead>
                    <tbody>
                        @forelse(($items ?? collect()) as $item)
                        <tr>
                            <td>{{ $item->id ?? '—' }}</td>
                            <td>{{ $item->driver_id ?? '—' }}</td>
                            <td>{{ ($item->profile_complete ?? false) ? 'Yes' : 'No' }}</td>
                            <td>{{ ($item->docs_uploaded ?? false) ? 'Yes' : 'No' }}</td>
                            <td>{{ ($item->approved ?? false) ? 'Yes' : 'No' }}</td>
                            <td>{{ $item->updated_at ? $item->updated_at->format('M j, Y') : '—' }}</td>
                        </tr>
                        @empty
                        @include('adminmodule::components.admin.empty-state', ['colspan' => 6])
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="text-muted small mt-3">Last updated: {{ now()->format('M j, Y g:i A') }}</div>
</div>
@endsection
