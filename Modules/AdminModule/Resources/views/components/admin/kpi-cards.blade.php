{{-- Reusable KPI row: pass $kpis as array of [label, value, icon?] or $kpiValues as 1-indexed values for generic "Metric N" --}}
@php
    $kpis = $kpis ?? null;
    $kpiValues = $kpiValues ?? [1 => 0, 2 => 0, 3 => 0, 4 => 0];
    $count = $kpis ? count($kpis) : 4;
@endphp
<div class="row g-3 mb-4">
    @if($kpis)
        @foreach($kpis as $kpi)
        <div class="col-sm-6 col-xl-3">
            <div class="card oneway-card p-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="oneway-kpi__icon"><i class="bi {{ $kpi['icon'] ?? 'bi-bar-chart' }}"></i></div>
                    <div>
                        <div class="fw-bold fs-3">{{ $kpi['value'] ?? 0 }}</div>
                        <div class="oneway-kpi__label">{{ $kpi['label'] ?? '—' }}</div>
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    @else
        @for($i = 1; $i <= 4; $i++)
        <div class="col-sm-6 col-xl-3">
            <div class="card oneway-card p-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="oneway-kpi__icon"><i class="bi bi-bar-chart"></i></div>
                    <div>
                        <div class="fw-bold fs-3">{{ $kpiValues[$i] ?? 0 }}</div>
                        <div class="oneway-kpi__label">Metric {{ $i }}</div>
                    </div>
                </div>
            </div>
        </div>
        @endfor
    @endif
</div>
