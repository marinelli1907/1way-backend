{{--
    Reusable "Coming Soon" scaffold for admin pages where backend logic isn't ready yet.

    Required variables:
        $pageTitle    (string) — page title shown in header and breadcrumb
        $pageSubtitle (string) — subtitle below the title
        $columns      (array)  — table column headers, e.g. ['ID', 'Name', 'Status', 'Date']

    Optional variables:
        $kpis         (array)  — KPI card data: [ ['label'=>..., 'value'=>..., 'icon'=>...], ... ]
        $tableTitle   (string) — card header for the table (default: 'Data')
        $emptyMessage (string) — empty state message
        $showFilters  (bool)   — whether to show the filters row (default: true)
        $items        (Collection|array) — iterable data rows (default: empty)
--}}

@section('title', $pageTitle ?? 'Section')
@extends('adminmodule::layouts.master')

@section('content')
<div class="container-fluid">

    @include('adminmodule::components.admin.page-header', [
        'title'    => $pageTitle ?? 'Section',
        'subtitle' => $pageSubtitle ?? 'This feature is under development.',
    ])

    @include('adminmodule::components.admin.kpi-cards', [
        'kpis' => $kpis ?? [],
    ])

    @if($showFilters ?? true)
        @include('adminmodule::components.admin.filters-row')
    @endif

    <div class="card oneway-card">
        <div class="card-header bg-transparent border-0">
            <h5 class="mb-0 fw-semibold">{{ $tableTitle ?? 'Data' }}</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            @foreach($columns ?? ['ID', 'Name', 'Status', 'Date'] as $col)
                                <th>{{ $col }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @forelse(($items ?? collect()) as $item)
                            <tr>
                                @foreach($columns ?? [] as $i => $col)
                                    <td>{{ data_get($item, $fields[$i] ?? '', '—') }}</td>
                                @endforeach
                            </tr>
                        @empty
                            @include('adminmodule::components.admin.empty-state', [
                                'message' => $emptyMessage ?? 'No records yet. Data will appear here when available.',
                                'colspan' => count($columns ?? ['ID', 'Name', 'Status', 'Date']),
                            ])
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="text-muted small mt-3">Last updated: {{ now()->format('M j, Y g:i A') }}</div>
</div>
@endsection
