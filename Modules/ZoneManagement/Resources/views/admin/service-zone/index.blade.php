@extends('adminmodule::layouts.master')
@section('title', 'Service Zones')

@push('css_or_js')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<style>
    #testMap { height: 250px; border-radius: 8px; }
    .sz-badge { font-size: .75rem; padding: .25em .6em; }
</style>
@endpush

@section('content')
<div class="main-content">
    <div class="container-fluid">

        <div class="d-flex align-items-center justify-content-between mb-4">
            <div>
                <h2 class="fs-22 fw-bold mb-1">Service Zones (Map Builder)</h2>
                <p class="text-muted mb-0">
                    Geographic boundary-based service areas
                    @if($enforced)
                        <span class="badge bg-success ms-2">Enforcement ON</span>
                    @else
                        <span class="badge bg-secondary ms-2">Enforcement OFF</span>
                    @endif
                </p>
            </div>
            <a href="{{ route('admin.service-zone.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-lg me-1"></i>Create Zone (Map Builder)
            </a>
        </div>

        {{-- Test Point --}}
        <div class="card shadow-sm mb-4">
            <div class="card-header fw-bold">
                <i class="bi bi-geo-alt text-primary me-2"></i>Test Point
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-7">
                        <div class="row g-2 align-items-end">
                            <div class="col-auto">
                                <label class="form-label fw-semibold">Latitude</label>
                                <input type="text" id="testLat" class="form-control" placeholder="41.4303" style="width:150px">
                            </div>
                            <div class="col-auto">
                                <label class="form-label fw-semibold">Longitude</label>
                                <input type="text" id="testLng" class="form-control" placeholder="-81.5205" style="width:150px">
                            </div>
                            <div class="col-auto">
                                <button class="btn btn-outline-primary" onclick="runTest()">
                                    <i class="bi bi-search me-1"></i>Test
                                </button>
                            </div>
                            <div class="col-auto" id="testResultBox" style="display:none;"></div>
                        </div>
                        <div class="form-text mt-1">Click on the map or enter coordinates to test zone containment.</div>
                    </div>
                    <div class="col-md-5">
                        <div id="testMap"></div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Filters --}}
        <div class="card shadow-sm mb-4">
            <div class="card-body py-2">
                <form method="GET" class="row g-2 align-items-center">
                    <div class="col-auto">
                        <input type="text" name="search" class="form-control form-control-sm" placeholder="Search..." value="{{ request('search') }}">
                    </div>
                    <div class="col-auto">
                        <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                            <option value="">All</option>
                            <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                            <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                        </select>
                    </div>
                    <div class="col-auto"><button type="submit" class="btn btn-sm btn-primary">Filter</button></div>
                </form>
            </div>
        </div>

        {{-- Zones Table --}}
        <div class="card shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Name</th>
                                <th>Components</th>
                                <th>Exclusions</th>
                                <th>Overrides</th>
                                <th>Priority</th>
                                <th>Status</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($zones as $i => $zone)
                            <tr>
                                <td>{{ $zones->firstItem() + $i }}</td>
                                <td class="fw-semibold">{{ $zone->name }}</td>
                                <td><span class="badge bg-info text-dark sz-badge">{{ $zone->components_count }}</span></td>
                                <td><span class="badge bg-warning text-dark sz-badge">{{ $zone->zone_exclusions_count }}</span></td>
                                <td><span class="badge bg-success sz-badge">{{ $zone->zone_inclusions_count }}</span></td>
                                <td>{{ $zone->priority }}</td>
                                <td>
                                    <a href="{{ route('admin.service-zone.status', ['id' => $zone->id]) }}"
                                       class="badge {{ $zone->is_active ? 'bg-success' : 'bg-secondary' }}" style="cursor:pointer;text-decoration:none;">
                                        {{ $zone->is_active ? 'Active' : 'Inactive' }}
                                    </a>
                                </td>
                                <td class="text-center">
                                    <a href="{{ route('admin.service-zone.pricing', $zone->id) }}" class="btn btn-sm btn-outline-success" title="Zone Pricing">
                                        <i class="bi bi-currency-dollar"></i>
                                    </a>
                                    <a href="{{ route('admin.service-zone.edit', $zone->id) }}" class="btn btn-sm btn-outline-primary" title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <form action="{{ route('admin.service-zone.delete', $zone->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this zone?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete"><i class="bi bi-trash"></i></button>
                                    </form>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted py-4">No service zones yet. <a href="{{ route('admin.service-zone.create') }}">Create one</a>.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if($zones->hasPages())
            <div class="card-footer">{{ $zones->withQueryString()->links() }}</div>
            @endif
        </div>

    </div>
</div>
@endsection

@push('script')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
var testMap = L.map('testMap').setView([39.8, -98.5], 4);
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '&copy; OSM', maxZoom: 18
}).addTo(testMap);
var testMarker = null;

testMap.on('click', function(e) {
    document.getElementById('testLat').value = e.latlng.lat.toFixed(6);
    document.getElementById('testLng').value = e.latlng.lng.toFixed(6);
    runTest();
});

function runTest() {
    var lat = document.getElementById('testLat').value;
    var lng = document.getElementById('testLng').value;
    if (!lat || !lng) return;

    if (testMarker) testMap.removeLayer(testMarker);
    testMarker = L.marker([lat, lng]).addTo(testMap);
    testMap.setView([lat, lng], 10);

    fetch("{{ route('admin.service-zone.test-contains') }}?lat=" + lat + "&lng=" + lng)
        .then(r => r.json())
        .then(data => {
            var box = document.getElementById('testResultBox');
            box.style.display = 'block';
            if (data.inside) {
                box.innerHTML = '<span class="badge bg-success fs-6 px-3 py-2"><i class="bi bi-check-circle me-1"></i>Inside: ' + data.zone_name + '</span>';
            } else {
                box.innerHTML = '<span class="badge bg-danger fs-6 px-3 py-2"><i class="bi bi-x-circle me-1"></i>Outside all zones</span>';
            }
        });
}
</script>
@endpush
