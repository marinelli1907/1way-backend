@section('title', 'Calendar — Admin View')
@extends('adminmodule::layouts.master')

@push('css_or_js')
<style>
.cal-grid { display:grid; grid-template-columns:repeat(7,1fr); border-top:1px solid #e5e7eb; border-left:1px solid #e5e7eb; }
.cal-grid__head { background:#f9fafb; font-weight:600; font-size:12px; text-align:center; padding:8px 4px; border-right:1px solid #e5e7eb; border-bottom:1px solid #e5e7eb; color:#6b7280; }
.cal-grid__cell { min-height:80px; border-right:1px solid #e5e7eb; border-bottom:1px solid #e5e7eb; padding:4px; font-size:11px; position:relative; }
.cal-grid__day { font-weight:600; font-size:12px; color:#374151; margin-bottom:2px; }
.cal-grid__day.today { background:#3b82f6; color:#fff; border-radius:50%; width:22px; height:22px; display:flex; align-items:center; justify-content:center; }
.cal-grid__dot { display:block; border-radius:3px; padding:1px 4px; margin-bottom:1px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; color:#fff; font-size:10px; }
.cal-grid__cell.other-month { background:#f9fafb; }
.cal-grid__cell.other-month .cal-grid__day { color:#d1d5db; }
</style>
@endpush

@section('content')
<div class="container-fluid py-4">

    {{-- PAGE HEADER --}}
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h3 class="mb-1 fw-bold"><i class="bi bi-calendar3 text-primary me-2"></i>Calendar — Admin View</h3>
            <div class="text-muted small">{{ now()->format('F Y') }} &mdash; trips and scheduled rides</div>
        </div>
        <div class="d-flex gap-2">
            <button class="btn btn-sm btn-outline-secondary" onclick="location.reload()">
                <i class="bi bi-arrow-clockwise"></i> Refresh
            </button>
        </div>
    </div>

    {{-- KPI CARDS --}}
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="card oneway-card p-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="oneway-kpi__icon" style="background:rgba(245,158,11,.12);color:#f59e0b;">
                        <i class="bi bi-calendar-day"></i>
                    </div>
                    <div>
                        <div class="fw-bold fs-4">{{ number_format($kpis['scheduled']) }}</div>
                        <div class="oneway-kpi__label">Today's Trips</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card oneway-card p-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="oneway-kpi__icon" style="background:rgba(59,130,246,.12);color:#3b82f6;">
                        <i class="bi bi-hourglass-split"></i>
                    </div>
                    <div>
                        <div class="fw-bold fs-4">{{ number_format($kpis['pending']) }}</div>
                        <div class="oneway-kpi__label">Pending Now</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card oneway-card p-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="oneway-kpi__icon" style="background:rgba(16,185,129,.12);color:#10b981;">
                        <i class="bi bi-geo-alt-fill"></i>
                    </div>
                    <div>
                        <div class="fw-bold fs-4">{{ number_format($kpis['confirmed']) }}</div>
                        <div class="oneway-kpi__label">Active Now</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card oneway-card p-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="oneway-kpi__icon" style="background:rgba(107,114,128,.12);color:#6b7280;">
                        <i class="bi bi-check-circle"></i>
                    </div>
                    <div>
                        <div class="fw-bold fs-4">{{ number_format($kpis['completed']) }}</div>
                        <div class="oneway-kpi__label">Completed Today</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- CALENDAR --}}
    <div class="card oneway-card p-3">
        <div class="d-flex align-items-center justify-content-between mb-3">
            <h6 class="fw-semibold mb-0">{{ now()->format('F Y') }}</h6>
            <div class="d-flex gap-2 align-items-center" style="font-size:12px;">
                <span><span class="cal-grid__dot d-inline-block me-1" style="background:#f59e0b;width:10px;height:10px;border-radius:50%;"></span>Pending</span>
                <span><span class="cal-grid__dot d-inline-block me-1" style="background:#3b82f6;width:10px;height:10px;border-radius:50%;"></span>Accepted</span>
                <span><span class="cal-grid__dot d-inline-block me-1" style="background:#10b981;width:10px;height:10px;border-radius:50%;"></span>Ongoing</span>
                <span><span class="cal-grid__dot d-inline-block me-1" style="background:#6b7280;width:10px;height:10px;border-radius:50%;"></span>Completed</span>
            </div>
        </div>

        @php
            $today        = now()->day;
            $firstDayOfMonth = now()->startOfMonth()->dayOfWeek; // 0=Sun
            $daysInMonth  = now()->daysInMonth;
            $prevMonthDays = now()->startOfMonth()->subDay()->daysInMonth;

            // Group calendar events by day
            $eventsByDay = [];
            foreach ($calendarEvents as $ev) {
                $day = \Carbon\Carbon::parse($ev['start'])->day;
                $eventsByDay[$day][] = $ev;
            }
        @endphp

        <div class="cal-grid">
            @foreach(['Sun','Mon','Tue','Wed','Thu','Fri','Sat'] as $d)
                <div class="cal-grid__head">{{ $d }}</div>
            @endforeach

            {{-- Padding cells for start of month --}}
            @for($p = 0; $p < $firstDayOfMonth; $p++)
                <div class="cal-grid__cell other-month">
                    <div class="cal-grid__day">{{ $prevMonthDays - ($firstDayOfMonth - 1 - $p) }}</div>
                </div>
            @endfor

            {{-- Month days --}}
            @for($d = 1; $d <= $daysInMonth; $d++)
                <div class="cal-grid__cell {{ $d === $today ? 'today-cell' : '' }}" style="{{ $d === $today ? 'background:#eff6ff;' : '' }}">
                    <div class="cal-grid__day {{ $d === $today ? 'today' : '' }}">{{ $d }}</div>
                    @foreach(array_slice($eventsByDay[$d] ?? [], 0, 3) as $ev)
                        <span class="cal-grid__dot" style="background:{{ $ev['color'] }};">{{ $ev['title'] }}</span>
                    @endforeach
                    @if(count($eventsByDay[$d] ?? []) > 3)
                        <span class="text-muted" style="font-size:10px;">+{{ count($eventsByDay[$d]) - 3 }} more</span>
                    @endif
                </div>
            @endfor

            {{-- Fill remaining cells --}}
            @php $cellsFilled = $firstDayOfMonth + $daysInMonth; $remainder = $cellsFilled % 7 === 0 ? 0 : 7 - ($cellsFilled % 7); @endphp
            @for($r = 1; $r <= $remainder; $r++)
                <div class="cal-grid__cell other-month"><div class="cal-grid__day">{{ $r }}</div></div>
            @endfor
        </div>
    </div>

</div>
@endsection
