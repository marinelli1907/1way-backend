@section('title', 'Promo Performance')
@extends('adminmodule::layouts.master')

@section('content')
<div class="container-fluid">
    @include('adminmodule::components.admin.page-header', ['title' => 'Promo Performance', 'subtitle' => 'Analytics for promotions.'])
    @include('adminmodule::components.admin.kpi-cards', ['kpis' => $kpis ?? []])
    @include('adminmodule::components.admin.filters-row')

    <div class="card oneway-card">
        <div class="card-header bg-transparent border-0"><h5 class="mb-0 fw-semibold">Performance by campaign</h5></div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light"><tr><th>Campaign</th><th>Impressions</th><th>Clicks</th><th>CTR %</th><th>Conversions</th></tr></thead>
                    <tbody>
                        @forelse(($items ?? collect()) as $item)
                        <tr>
                            <td>{{ $item->name ?? $item['name'] ?? '—' }}</td>
                            <td>{{ $item->impressions ?? $item['impressions'] ?? 0 }}</td>
                            <td>{{ $item->clicks ?? $item['clicks'] ?? 0 }}</td>
                            <td>{{ $item->ctr ?? $item['ctr'] ?? '0' }}</td>
                            <td>{{ $item->conversions ?? $item['conversions'] ?? 0 }}</td>
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
