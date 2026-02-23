@section('title', 'Ride Incentives')
@extends('adminmodule::layouts.master')

@section('content')
<div class="container-fluid">
    @include('adminmodule::components.admin.page-header', ['title' => 'Ride Incentives', 'subtitle' => 'Discounts, credits, and perks.'])
    @include('adminmodule::components.admin.kpi-cards', ['kpis' => $kpis ?? []])
    @include('adminmodule::components.admin.filters-row')

    <div class="card oneway-card">
        <div class="card-header bg-transparent border-0"><h5 class="mb-0 fw-semibold">Coupons & Discounts</h5></div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light"><tr><th>ID</th><th>Name</th><th>Code / Type</th><th>Status</th><th>Updated</th></tr></thead>
                    <tbody>
                        @forelse(($items ?? collect()) as $item)
                        <tr>
                            <td>{{ $item->id ?? '—' }}</td>
                            <td>{{ $item->name ?? '—' }}</td>
                            <td>{{ $item->coupon_code ?? $item->amount_type ?? '—' }}</td>
                            <td>{{ ($item->is_active ?? false) ? 'Active' : 'Inactive' }}</td>
                            <td>{{ $item->updated_at ? $item->updated_at->format('M j, Y') : '—' }}</td>
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
