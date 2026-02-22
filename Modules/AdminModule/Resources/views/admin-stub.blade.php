@section('title', $title)
@extends('adminmodule::layouts.master')

@section('content')
<div class="container-fluid py-4">

    {{-- PAGE HEADER --}}
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h3 class="mb-1 fw-bold">
                <i class="bi {{ $icon }} me-2" style="opacity:.8;"></i>{{ $title }}
            </h3>
            <div class="text-muted small">{{ $subtitle }} &mdash; last updated {{ $lastUpdated->format('d M Y, H:i') }}</div>
        </div>
        <button class="btn btn-sm btn-outline-secondary" onclick="location.reload()">
            <i class="bi bi-arrow-clockwise"></i> Refresh
        </button>
    </div>

    {{-- NOTICE BANNER (optional) --}}
    @if(!empty($notice))
    <div class="alert alert-info d-flex align-items-center gap-2 mb-4" role="alert">
        <i class="bi bi-info-circle-fill fs-5 flex-shrink-0"></i>
        <div><strong>Module launching soon.</strong> {{ $notice }}</div>
    </div>
    @endif

    {{-- KPI CARDS --}}
    <div class="row g-3 mb-4">
        @foreach($kpis as $kpi)
        <div class="col-6 col-md-3">
            <div class="card oneway-card p-3 h-100">
                <div class="d-flex align-items-center gap-3">
                    <div class="oneway-kpi__icon" style="background:{{ $kpi['color'] }}1e; color:{{ $kpi['color'] }};">
                        <i class="bi {{ $kpi['icon'] }}"></i>
                    </div>
                    <div>
                        <div class="fw-bold fs-4">{{ $kpi['value'] }}</div>
                        <div class="oneway-kpi__label">{{ $kpi['label'] }}</div>
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    {{-- FILTERS --}}
    <form method="GET" class="card oneway-card p-3 mb-4">
        <div class="row g-2 align-items-end">

            @if(isset($filters['from']) || array_key_exists('from', $filters))
            <div class="col-md-2">
                <label class="form-label small fw-semibold">From</label>
                <input type="date" name="from" class="form-control form-control-sm" value="{{ $filters['from'] ?? '' }}">
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-semibold">To</label>
                <input type="date" name="to" class="form-control form-control-sm" value="{{ $filters['to'] ?? '' }}">
            </div>
            @endif

            @if(isset($filters['status']))
            <div class="col-md-2">
                <label class="form-label small fw-semibold">Status</label>
                <select name="status" class="form-select form-select-sm">
                    <option value="">All</option>
                    <option value="active"   @selected(($filters['status']??'')=='active')>Active</option>
                    <option value="inactive" @selected(($filters['status']??'')=='inactive')>Inactive</option>
                    <option value="pending"  @selected(($filters['status']??'')=='pending')>Pending</option>
                </select>
            </div>
            @endif

            @if(isset($filters['zone_id']))
            <div class="col-md-2">
                <label class="form-label small fw-semibold">Zone</label>
                @php
                    try {
                        $zones = \Modules\ZoneManagement\Entities\Zone::select('id','name')->orderBy('name')->get();
                    } catch(\Throwable $e) {
                        $zones = collect();
                    }
                @endphp
                <select name="zone_id" class="form-select form-select-sm">
                    <option value="">All Zones</option>
                    @foreach($zones as $z)
                        <option value="{{ $z->id }}" @selected(($filters['zone_id']??'')==$z->id)>{{ $z->name }}</option>
                    @endforeach
                </select>
            </div>
            @endif

            <div class="col-md-3">
                <label class="form-label small fw-semibold">Search</label>
                <input type="text" name="search" class="form-control form-control-sm" placeholder="Search..." value="{{ $filters['search'] ?? '' }}">
            </div>

            <div class="col d-flex gap-2 align-items-end">
                <button type="submit" class="btn btn-primary btn-sm px-4">Filter</button>
                <a href="{{ url()->current() }}" class="btn btn-outline-secondary btn-sm">Clear</a>
                <button type="button" class="btn btn-outline-success btn-sm ms-auto" disabled title="Export not yet available">
                    <i class="bi bi-download"></i> Export
                </button>
            </div>

        </div>
    </form>

    {{-- DATA TABLE --}}
    <div class="card oneway-card">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        @foreach($columns as $col)
                            <th>{{ $col }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @php
                        $rowItems = is_array($rows) ? $rows : (method_exists($rows, 'items') ? $rows->items() : []);
                        $rowCount = count($rowItems);
                    @endphp

                    @if($rowCount > 0)
                        @foreach($rowItems as $idx => $row)
                        <tr>
                            <td class="text-muted small">{{ $idx + 1 }}</td>
                            @foreach(array_slice((array)$row->getAttributes(), 0, count($columns)) as $cell)
                                <td class="small">{{ is_array($cell) ? json_encode($cell) : $cell }}</td>
                            @endforeach
                        </tr>
                        @endforeach
                    @else
                    <tr>
                        <td colspan="{{ count($columns) + 1 }}" class="text-center py-5">
                            <div class="text-muted">
                                <i class="bi {{ $icon }} fs-2 d-block mb-2 opacity-50"></i>
                                <div class="fw-semibold mb-1">No data yet</div>
                                <div class="small">{{ $title }} data will appear here once available.</div>
                            </div>
                        </td>
                    </tr>
                    @endif
                </tbody>
            </table>
        </div>

        @if(is_object($rows) && method_exists($rows, 'links') && $rows->hasPages())
        <div class="p-3 border-top">
            {{ $rows->links() }}
        </div>
        @endif
    </div>

</div>
@endsection
