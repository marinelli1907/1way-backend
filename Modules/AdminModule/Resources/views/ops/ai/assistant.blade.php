@section('title', 'AI Assistant')
@extends('adminmodule::layouts.master')

@section('content')
<div class="container-fluid">
    @include('adminmodule::components.admin.page-header', ['title' => 'AI Assistant', 'subtitle' => 'Ops Copilot.'])
    @include('adminmodule::components.admin.kpi-cards', ['kpis' => $kpis ?? []])
    @include('adminmodule::components.admin.filters-row')

    <div class="card oneway-card">
        <div class="card-header bg-transparent border-0"><h5 class="mb-0 fw-semibold">Configuration &amp; queue</h5></div>
        <div class="card-body">
            <p class="text-muted mb-0">Assistant settings and session logs will appear here. Configure model and prompts in settings.</p>
            <div class="table-responsive mt-3">
                <table class="table table-hover mb-0">
                    <thead class="table-light"><tr><th>Session</th><th>Status</th><th>Date</th></tr></thead>
                    <tbody>
                        @include('adminmodule::components.admin.empty-state', ['message' => 'No assistant sessions yet.', 'colspan' => 3])
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="text-muted small mt-3">Last updated: {{ now()->format('M j, Y g:i A') }}</div>
</div>
@endsection
