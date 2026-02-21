@extends('adminmodule::layouts.master')

@section('title', translate('Import Zones from GeoJSON'))

@section('content')
<div class="main-content">
    <div class="container-fluid">

        <div class="d-flex align-items-center justify-content-between mb-4">
            <div>
                <h2 class="fs-22 fw-bold mb-1">{{ translate('Import Zones from GeoJSON') }}</h2>
                <p class="text-muted mb-0">{{ translate('Upload real city boundaries, service areas, or any polygon as zones') }}</p>
            </div>
            <a href="{{ route('admin.zone.index') }}" class="btn btn-outline-primary">
                <i class="bi bi-arrow-left me-1"></i>{{ translate('Back to Zones') }}
            </a>
        </div>

        <div class="row g-4">

            {{-- Import Form --}}
            <div class="col-lg-7">
                <div class="card shadow-sm">
                    <div class="card-header fw-bold">
                        <i class="bi bi-upload text-primary me-2"></i>{{ translate('GeoJSON Source') }}
                    </div>
                    <div class="card-body">
                        <form action="{{ route('admin.zone.geojson-import.store') }}" method="POST" enctype="multipart/form-data">
                            @csrf

                            {{-- Tab picker: file vs paste --}}
                            <ul class="nav nav-tabs mb-3" role="tablist">
                                <li class="nav-item">
                                    <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tabFile" type="button">
                                        <i class="bi bi-file-earmark-code me-1"></i>{{ translate('Upload File') }}
                                    </button>
                                </li>
                                <li class="nav-item">
                                    <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tabPaste" type="button">
                                        <i class="bi bi-clipboard me-1"></i>{{ translate('Paste GeoJSON') }}
                                    </button>
                                </li>
                            </ul>

                            <div class="tab-content mb-3">
                                <div class="tab-pane fade show active" id="tabFile">
                                    <label class="form-label fw-semibold">{{ translate('GeoJSON File') }}</label>
                                    <input type="file" name="geojson_file" class="form-control @error('geojson_file') is-invalid @enderror"
                                           accept=".json,.geojson">
                                    @error('geojson_file')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    <div class="form-text">{{ translate('Supports .geojson and .json files up to 2MB') }}</div>
                                </div>
                                <div class="tab-pane fade" id="tabPaste">
                                    <label class="form-label fw-semibold">{{ translate('Paste GeoJSON') }}</label>
                                    <textarea name="geojson_text" class="form-control font-monospace @error('geojson_text') is-invalid @enderror"
                                              rows="12" placeholder='{ "type": "FeatureCollection", "features": [...] }'></textarea>
                                    @error('geojson_text')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-semibold">{{ translate('Name Property Key') }}</label>
                                <input type="text" name="name_property" class="form-control" value="name" placeholder="name">
                                <div class="form-text">{{ translate('Which GeoJSON feature property to use as the zone name (default: "name")') }}</div>
                            </div>

                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-cloud-upload me-1"></i>{{ translate('Import Zones') }}
                            </button>
                            <a href="{{ route('admin.zone.geojson-export-all') }}" class="btn btn-outline-secondary ms-2">
                                <i class="bi bi-download me-1"></i>{{ translate('Export All Zones') }}
                            </a>
                        </form>
                    </div>
                </div>
            </div>

            {{-- Help / Instructions --}}
            <div class="col-lg-5">
                <div class="card shadow-sm">
                    <div class="card-header fw-bold">
                        <i class="bi bi-info-circle text-primary me-2"></i>{{ translate('How It Works') }}
                    </div>
                    <div class="card-body p-0">
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item">
                                <strong>1. Get GeoJSON</strong>
                                <p class="text-muted small mb-0">Download city/county boundaries from
                                   <a href="https://www.census.gov/geographies/mapping-files.html" target="_blank">US Census</a>,
                                   <a href="https://boundaries.latimes.com/" target="_blank">LA Times Boundaries</a>, or draw on
                                   <a href="https://geojson.io" target="_blank">geojson.io</a>.
                                </p>
                            </li>
                            <li class="list-group-item">
                                <strong>2. Choose name property</strong>
                                <p class="text-muted small mb-0">US Census files often use <code>NAME</code>. City data may use <code>name</code> or <code>neighborhood</code>.</p>
                            </li>
                            <li class="list-group-item">
                                <strong>3. Import</strong>
                                <p class="text-muted small mb-0">Each Polygon/MultiPolygon feature becomes one zone. Non-polygon features are skipped.</p>
                            </li>
                            <li class="list-group-item">
                                <strong>4. Set pricing</strong>
                                <p class="text-muted small mb-0">After import, set the pricing multiplier per zone (e.g. 1.2 for airport surcharge areas).</p>
                            </li>
                        </ul>
                    </div>
                </div>

                <div class="card shadow-sm mt-3">
                    <div class="card-header fw-bold">
                        <i class="bi bi-code-slash text-primary me-2"></i>{{ translate('Sample GeoJSON') }}
                    </div>
                    <div class="card-body">
                        <pre class="bg-dark text-white p-3 rounded small" style="font-size:11px;overflow-x:auto;">{
  "type": "FeatureCollection",
  "features": [{
    "type": "Feature",
    "properties": { "name": "Downtown Miami" },
    "geometry": {
      "type": "Polygon",
      "coordinates": [[
        [-80.20, 25.77],
        [-80.18, 25.77],
        [-80.18, 25.75],
        [-80.20, 25.75],
        [-80.20, 25.77]
      ]]
    }
  }]
}</pre>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection
