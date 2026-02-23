@section('title', 'App Config')
@extends('adminmodule::layouts.master')

@section('content')
<div class="container-fluid">
    @include('adminmodule::components.admin.page-header', ['title' => 'App Config', 'subtitle' => 'Environment and app settings.'])
    @include('adminmodule::components.admin.kpi-cards', ['kpis' => $kpis ?? []])
    @include('adminmodule::components.admin.filters-row')

    <div class="card oneway-card">
        <div class="card-header bg-transparent border-0"><h5 class="mb-0 fw-semibold">Configuration</h5></div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light"><tr><th>Key</th><th>Value</th></tr></thead>
                    <tbody>
                        @forelse(($items ?? collect()) as $item)
                        <tr>
                            <td>{{ $item->key ?? $item['key'] ?? '—' }}</td>
                            <td>{{ is_string($item->value ?? $item['value'] ?? null) ? \Illuminate\Support\Str::limit($item->value ?? $item['value'], 80) : '—' }}</td>
                        </tr>
                        @empty
                        @include('adminmodule::components.admin.empty-state', ['message' => 'Config entries (read-only) will appear here.', 'colspan' => 2])
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="text-muted small mt-3">Last updated: {{ now()->format('M j, Y g:i A') }}</div>
</div>
@endsection
