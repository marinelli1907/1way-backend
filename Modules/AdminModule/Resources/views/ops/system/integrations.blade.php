@section('title', 'Integrations')
@extends('adminmodule::layouts.master')

@section('content')
<div class="container-fluid">
    @include('adminmodule::components.admin.page-header', ['title' => 'Integrations', 'subtitle' => 'Stripe, Maps, Twilio, etc.'])
    @include('adminmodule::components.admin.kpi-cards', ['kpis' => $kpis ?? []])
    @include('adminmodule::components.admin.filters-row')

    <div class="card oneway-card">
        <div class="card-header bg-transparent border-0"><h5 class="mb-0 fw-semibold">Third-party</h5></div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light"><tr><th>Service</th><th>Status</th><th>Updated</th></tr></thead>
                    <tbody>
                        @forelse(($items ?? collect()) as $item)
                        <tr>
                            <td>{{ $item->name ?? $item->service ?? '—' }}</td>
                            <td>{{ $item->status ?? ($item->is_active ?? false) ? 'Active' : 'Inactive' }}</td>
                            <td>{{ isset($item->updated_at) ? $item->updated_at->format('M j, Y') : '—' }}</td>
                        </tr>
                        @empty
                        @include('adminmodule::components.admin.empty-state', ['message' => 'Integrations will appear here.', 'colspan' => 3])
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="text-muted small mt-3">Last updated: {{ now()->format('M j, Y g:i A') }}</div>
</div>
@endsection
