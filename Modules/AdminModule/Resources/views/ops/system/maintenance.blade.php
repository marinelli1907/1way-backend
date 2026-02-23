@section('title', 'Maintenance Mode')
@extends('adminmodule::layouts.master')

@section('content')
<div class="container-fluid">
    @include('adminmodule::components.admin.page-header', ['title' => 'Maintenance Mode', 'subtitle' => 'Enable or disable maintenance.'])
    @include('adminmodule::components.admin.kpi-cards', ['kpis' => $kpis ?? []])
    @include('adminmodule::components.admin.filters-row')

    <div class="card oneway-card">
        <div class="card-header bg-transparent border-0"><h5 class="mb-0 fw-semibold">Status</h5></div>
        <div class="card-body">
            <p class="text-muted mb-0">Application is currently <strong>live</strong>. Use Artisan <code>down</code> / <code>up</code> to toggle maintenance mode.</p>
            <div class="table-responsive mt-3">
                <table class="table table-hover mb-0">
                    <thead class="table-light"><tr><th>Setting</th><th>Value</th></tr></thead>
                    <tbody>
                        <tr><td>Maintenance</td><td>Off</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="text-muted small mt-3">Last updated: {{ now()->format('M j, Y g:i A') }}</div>
</div>
@endsection
