@extends('adminmodule::layouts.master')

@section('title', translate('AI Tools'))

@section('content')
<div class="main-content">
    <div class="container-fluid">

        <div class="d-flex align-items-center justify-content-between mb-4">
            <h2 class="fs-22 fw-bold mb-0">
                <i class="bi bi-tools text-primary me-2"></i>{{ translate('AI Tools') }}
            </h2>
        </div>

        {{-- Tabs --}}
        <ul class="nav nav-tabs mb-4">
            <li class="nav-item">
                <a class="nav-link" href="{{ route('admin.ai.settings') }}">
                    <i class="bi bi-sliders me-1"></i>{{ translate('Settings') }}
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="{{ route('admin.ai.logs') }}">
                    <i class="bi bi-journal-text me-1"></i>{{ translate('Logs') }}
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link active" href="{{ route('admin.ai.tools') }}">
                    <i class="bi bi-tools me-1"></i>{{ translate('Tools') }}
                </a>
            </li>
        </ul>

        <div class="row g-4">

            {{-- ── Tool 1: Suggest Zone Boundaries ─── --}}
            <div class="col-lg-6">
                <div class="card shadow-sm h-100">
                    <div class="card-header fw-bold d-flex align-items-center gap-2">
                        <i class="bi bi-bounding-box text-primary"></i>
                        {{ translate('Suggest Zone Boundaries') }}
                        <span class="badge bg-secondary ms-auto">MVP</span>
                    </div>
                    <div class="card-body">
                        <p class="text-muted">
                            {{ translate('Paste a CSV of ride coordinates (lat,lng per line). The tool will analyze the spread and suggest a zone polygon.') }}
                        </p>

                        <div id="zoneSuggestResult" class="d-none mb-3"></div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">{{ translate('Ride Coordinates (lat,lng — one per line)') }}</label>
                            <textarea class="form-control font-monospace" id="zoneCoordCsv" rows="8"
                                      placeholder="25.7617,-80.1918&#10;25.7825,-80.2101&#10;25.7500,-80.1750">
</textarea>
                        </div>

                        <button class="btn btn-primary" onclick="runZoneSuggest()">
                            <i class="bi bi-geo-fill me-1"></i>{{ translate('Suggest Zone') }}
                            <span class="spinner-border spinner-border-sm ms-2 d-none" id="zoneSpinner"></span>
                        </button>

                        <div class="mt-3 d-none" id="zoneSuggestOutput">
                            <h6>{{ translate('Suggested GeoJSON') }}</h6>
                            <pre class="bg-dark text-white p-3 rounded small" id="zoneSuggestJson" style="max-height:200px;overflow:auto;"></pre>
                            <a class="btn btn-sm btn-outline-primary mt-2" id="importSuggestedZone"
                               href="{{ route('admin.zone.geojson-import') }}">
                                <i class="bi bi-plus-circle me-1"></i>{{ translate('Open Import Tool') }}
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ── Tool 2: Suggest Pricing ─── --}}
            <div class="col-lg-6">
                <div class="card shadow-sm h-100">
                    <div class="card-header fw-bold d-flex align-items-center gap-2">
                        <i class="bi bi-graph-up-arrow text-primary"></i>
                        {{ translate('Suggest Pricing Multiplier') }}
                        <span class="badge bg-secondary ms-auto">MVP</span>
                    </div>
                    <div class="card-body">
                        <p class="text-muted">
                            {{ translate('Select a zone and get a suggested fare multiplier based on historical trip volume.') }}
                        </p>

                        <div id="pricingSuggestResult" class="d-none mb-3"></div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">{{ translate('Zone') }}</label>
                            <select class="form-select" id="pricingZoneId">
                                <option value="">{{ translate('Select a zone...') }}</option>
                                @foreach(\Modules\ZoneManagement\Entities\Zone::ofStatus(1)->get() as $zone)
                                <option value="{{ $zone->id }}">{{ $zone->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <button class="btn btn-primary" onclick="runPricingSuggest()">
                            <i class="bi bi-currency-dollar me-1"></i>{{ translate('Suggest Pricing') }}
                            <span class="spinner-border spinner-border-sm ms-2 d-none" id="pricingSpinner"></span>
                        </button>

                        <div class="mt-3 d-none" id="pricingSuggestOutput">
                            <div class="card border-primary">
                                <div class="card-body" id="pricingSuggestCard"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ── Coming Soon placeholders ─── --}}
            <div class="col-12">
                <div class="card shadow-sm">
                    <div class="card-header fw-bold">
                        <i class="bi bi-hourglass-split text-muted me-2"></i>{{ translate('Coming Soon') }}
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            @foreach(['Demand Heat Map','Fraud Detection','ETA Predictor','Driver Matching AI'] as $feature)
                            <div class="col-md-3">
                                <div class="text-center p-3 border rounded bg-light">
                                    <i class="bi bi-cpu fs-2 text-muted mb-2 d-block"></i>
                                    <div class="fw-semibold text-muted">{{ $feature }}</div>
                                    <span class="badge bg-secondary mt-1">{{ translate('Planned') }}</span>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

        </div>{{-- /row --}}
    </div>
</div>
@endsection

@push('css_or_js')
<script>
const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

async function runZoneSuggest() {
    const csv = document.getElementById('zoneCoordCsv').value.trim();
    if (!csv) { alert('{{ translate("Please enter coordinates") }}'); return; }

    document.getElementById('zoneSpinner').classList.remove('d-none');

    const resp = await fetch('{{ route('admin.ai.tools.suggest-zones') }}', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
        body: JSON.stringify({ coordinates_csv: csv })
    });

    document.getElementById('zoneSpinner').classList.add('d-none');
    const data = await resp.json();

    if (data.error) {
        document.getElementById('zoneSuggestResult').className = 'alert alert-danger';
        document.getElementById('zoneSuggestResult').textContent = data.error;
        document.getElementById('zoneSuggestResult').classList.remove('d-none');
        return;
    }

    document.getElementById('zoneSuggestJson').textContent = JSON.stringify(data.geojson, null, 2);
    document.getElementById('zoneSuggestOutput').classList.remove('d-none');
    document.getElementById('zoneSuggestResult').className = 'alert alert-success';
    document.getElementById('zoneSuggestResult').textContent =
        `{{ translate('Analysed') }} ${data.points_analysed} {{ translate('points') }}`;
    document.getElementById('zoneSuggestResult').classList.remove('d-none');
}

async function runPricingSuggest() {
    const zoneId = document.getElementById('pricingZoneId').value;
    if (!zoneId) { alert('{{ translate("Please select a zone") }}'); return; }

    document.getElementById('pricingSpinner').classList.remove('d-none');

    const resp = await fetch('{{ route('admin.ai.tools.suggest-pricing') }}', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
        body: JSON.stringify({ zone_id: zoneId })
    });

    document.getElementById('pricingSpinner').classList.add('d-none');
    const data = await resp.json();

    if (data.error) {
        document.getElementById('pricingSuggestResult').className = 'alert alert-danger';
        document.getElementById('pricingSuggestResult').textContent = data.error;
        document.getElementById('pricingSuggestResult').classList.remove('d-none');
        return;
    }

    document.getElementById('pricingSuggestCard').innerHTML = `
        <div class="d-flex justify-content-between align-items-center mb-2">
            <strong>${data.zone_name}</strong>
            <span class="badge bg-primary fs-6">${data.suggested_multiplier}x</span>
        </div>
        <div class="text-muted small">${data.reason}</div>
        <hr>
        <div class="d-flex gap-3 small text-muted">
            <span><strong>{{ translate('Trips:') }}</strong> ${data.trip_count}</span>
            <span><strong>{{ translate('Confidence:') }}</strong> ${data.confidence}</span>
        </div>
        <div class="alert alert-warning mt-2 mb-0 small">${data.note}</div>
    `;
    document.getElementById('pricingSuggestOutput').classList.remove('d-none');
}
</script>
@endpush
