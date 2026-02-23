@section('title', 'API Keys')
@extends('adminmodule::layouts.master')

@section('content')
<div class="container-fluid">
    @include('adminmodule::components.admin.page-header', ['title' => 'API Keys', 'subtitle' => 'Keys and scopes.'])
    @include('adminmodule::components.admin.kpi-cards', ['kpis' => $kpis ?? []])
    @include('adminmodule::components.admin.filters-row')

    <div class="card oneway-card">
        <div class="card-header bg-transparent border-0"><h5 class="mb-0 fw-semibold">Keys</h5></div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light"><tr><th>Name</th><th>Last used</th><th>Status</th></tr></thead>
                    <tbody>
                        @forelse(($items ?? collect()) as $item)
                        <tr>
                            <td>{{ $item->name ?? $item->key ?? '—' }}</td>
                            <td>{{ isset($item->last_used_at) ? $item->last_used_at->format('M j, Y') : '—' }}</td>
                            <td>{{ ($item->is_active ?? true) ? 'Active' : 'Revoked' }}</td>
                        </tr>
                        @empty
                        @include('adminmodule::components.admin.empty-state', ['message' => 'API keys will appear here.', 'colspan' => 3])
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="text-muted small mt-3">Last updated: {{ now()->format('M j, Y g:i A') }}</div>
</div>
@endsection
