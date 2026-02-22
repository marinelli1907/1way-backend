@section('title', 'Calendar')
@extends('adminmodule::layouts.master')

@section('content')
<div class="container-fluid">

    {{-- HEADER --}}
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <nav aria-label="breadcrumb"><ol class="breadcrumb mb-1">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item active">Calendar</li>
            </ol></nav>
            <h3 class="mb-0 fw-bold">Calendar (Admin View)</h3>
            <div class="text-muted small">View scheduled trips and events by date</div>
        </div>
        <div>
            <form method="GET" class="d-flex gap-2">
                <select name="month" class="form-select form-select-sm" style="width: auto;">
                    @for($m = 1; $m <= 12; $m++)
                    <option value="{{ $m }}" {{ $month == $m ? 'selected' : '' }}>{{ date('F', mktime(0,0,0,$m,1)) }}</option>
                    @endfor
                </select>
                <select name="year" class="form-select form-select-sm" style="width: auto;">
                    @for($y = now()->year - 1; $y <= now()->year + 1; $y++)
                    <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                    @endfor
                </select>
                <button class="btn btn-sm btn-primary">Go</button>
            </form>
        </div>
    </div>

    {{-- KPI CARDS --}}
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-6">
            <div class="card oneway-card p-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="oneway-kpi__icon"><i class="bi bi-calendar-check"></i></div>
                    <div>
                        <div class="fw-bold fs-3">{{ $totalScheduled }}</div>
                        <div class="oneway-kpi__label">Total Scheduled Trips</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-6">
            <div class="card oneway-card p-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="oneway-kpi__icon"><i class="bi bi-calendar-month"></i></div>
                    <div>
                        <div class="fw-bold fs-3">{{ $thisMonthTrips }}</div>
                        <div class="oneway-kpi__label">This Month</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- CALENDAR --}}
    <div class="card oneway-card">
        <div class="card-body">
            <div class="calendar-wrapper">
                <table class="table table-bordered mb-0" style="table-layout: fixed;">
                    <thead>
                        <tr>
                            <th class="text-center py-2">Sun</th>
                            <th class="text-center py-2">Mon</th>
                            <th class="text-center py-2">Tue</th>
                            <th class="text-center py-2">Wed</th>
                            <th class="text-center py-2">Thu</th>
                            <th class="text-center py-2">Fri</th>
                            <th class="text-center py-2">Sat</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $day = 1;
                            $daysInMonth = $daysInMonth ?? 30;
                            $firstDayOfWeek = $firstDayOfWeek ?? 0;
                        @endphp
                        @for($week = 0; $week < 6; $week++)
                        <tr>
                            @for($dow = 0; $dow < 7; $dow++)
                            @if(($week === 0 && $dow < $firstDayOfWeek) || $day > $daysInMonth)
                            <td class="text-muted" style="height: 100px; vertical-align: top; padding: 8px;">
                                @if($week === 0 && $dow < $firstDayOfWeek)
                                    {{-- Empty before first day --}}
                                @endif
                            </td>
                            @else
                            @php
                                $currentDate = sprintf('%04d-%02d-%02d', $year, $month, $day);
                                $tripCount = $scheduledTrips->get($currentDate)?->cnt ?? 0;
                                $isToday = $currentDate === now()->toDateString();
                            @endphp
                            <td class="{{ $isToday ? 'bg-light border-primary border-2' : '' }}" style="height: 100px; vertical-align: top; padding: 8px;">
                                <div class="d-flex justify-content-between align-items-start">
                                    <span class="fw-semibold {{ $isToday ? 'text-primary' : '' }}">{{ $day }}</span>
                                    @if($tripCount > 0)
                                    <span class="badge bg-primary">{{ $tripCount }}</span>
                                    @endif
                                </div>
                                @if($tripCount > 0)
                                <div class="mt-1 small text-muted">{{ $tripCount }} trip{{ $tripCount > 1 ? 's' : '' }}</div>
                                @endif
                            </td>
                            @php $day++; @endphp
                            @endif
                            @endfor
                        </tr>
                        @if($day > $daysInMonth) @break @endif
                        @endfor
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="text-muted small mt-3">Last updated: {{ now()->format('M j, Y g:i A') }}</div>
</div>
@endsection
