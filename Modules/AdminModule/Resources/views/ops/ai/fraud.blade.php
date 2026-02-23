@section('title', 'Fraud / Risk Alerts')
@extends('adminmodule::layouts.master')

@section('content')
<div class="container-fluid">
    @include('adminmodule::components.admin.page-header', ['title' => 'Fraud / Risk Alerts', 'subtitle' => 'AI-detected risk events.'])
    @include('adminmodule::components.admin.kpi-cards', ['kpis' => $kpis ?? []])
    @include('adminmodule::components.admin.filters-row')

    <div class="card oneway-card">
        <div class="card-header bg-transparent border-0"><h5 class="mb-0 fw-semibold">Alerts</h5></div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light"><tr><th>ID</th><th>Type</th><th>Severity</th><th>Status</th><th>Date</th></tr></thead>
                    <tbody>
                        @forelse(($items ?? collect()) as $item)
                        <tr>
                            <td>{{ $item->id ?? '—' }}</td>
                            <td>{{ $item->type ?? '—' }}</td>
                            <td>{{ $item->severity ?? '—' }}</td>
                            <td>{{ $item->status ?? '—' }}</td>
                            <td>{{ isset($item->created_at) ? $item->created_at->format('M j, Y H:i') : '—' }}</td>
                        </tr>
                        @empty
                        @include('adminmodule::components.admin.empty-state', ['message' => 'No fraud alerts yet.', 'colspan' => 5])
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="text-muted small mt-3">Last updated: {{ now()->format('M j, Y g:i A') }}</div>
</div>
@endsection
