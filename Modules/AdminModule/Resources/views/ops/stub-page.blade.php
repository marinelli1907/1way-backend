@section('title', $title ?? 'Section')
@extends('adminmodule::layouts.master')

@section('content')
<div class="container-fluid">

    @include('adminmodule::components.admin.page-header', [
        'title' => $title ?? 'Section',
        'subtitle' => $subtitle ?? 'View and manage data for this section.',
    ])

    @include('adminmodule::components.admin.kpi-cards', [
        'kpiValues' => $kpiValues ?? [1 => 0, 2 => 0, 3 => 0, 4 => 0],
    ])

    @include('adminmodule::components.admin.filters-row')

    <div class="card oneway-card">
        <div class="card-header bg-transparent border-0"><h5 class="mb-0 fw-semibold">Data</h5></div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light"><tr><th>ID</th><th>Name</th><th>Status</th><th>Updated</th></tr></thead>
                    <tbody>
                        @include('adminmodule::components.admin.empty-state')
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="text-muted small mt-3">Last updated: {{ now()->format('M j, Y g:i A') }}</div>
</div>
@endsection
