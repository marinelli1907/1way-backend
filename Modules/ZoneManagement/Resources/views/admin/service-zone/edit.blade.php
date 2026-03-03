@extends('adminmodule::layouts.master')
@section('title', 'Edit Service Zone')

@push('css_or_js')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<style>
    #zoneMap { height: 550px; border-radius: 8px; border: 2px solid #dee2e6; }
    .locked-item { padding: 8px 12px; border: 1px solid #dee2e6; border-radius: 6px; margin-bottom: 6px; display: flex; justify-content: space-between; align-items: center; background: #f8f9fa; }
    .locked-item .label { font-weight: 600; font-size: .9rem; }
    .locked-item .meta { font-size: .75rem; color: #6c757d; }
    .locked-item.exclusion { border-color: #ffc107; background: #fff8e1; }
    .locked-item.inclusion { border-color: #28a745; background: #e8f5e9; }
    .layer-section { border: 1px solid #dee2e6; border-radius: 8px; padding: 12px; margin-bottom: 12px; }
    #searchSpinner { display: none; }
</style>
@endpush

@section('content')
<div class="main-content">
<div class="container-fluid">

    <div class="d-flex align-items-center justify-content-between mb-3">
        <div>
            <h2 class="fs-22 fw-bold mb-1">Edit: {{ $zone->name }}</h2>
            <p class="text-muted mb-0">Modify boundary components, exclusions, and overrides</p>
        </div>
        <a href="{{ route('admin.service-zone.index') }}" class="btn btn-outline-primary"><i class="bi bi-arrow-left me-1"></i>Back</a>
    </div>

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="row g-2 mb-3 align-items-end">
                        <div class="col">
                            <label class="form-label fw-semibold">Search city / county / zip</label>
                            <input type="text" id="searchQuery" class="form-control" placeholder="e.g. Highland Heights">
                        </div>
                        <div class="col-auto">
                            <select id="searchType" class="form-select">
                                <option value="city">City</option>
                                <option value="county">County</option>
                                <option value="zip">ZIP Code</option>
                                <option value="state">State</option>
                            </select>
                        </div>
                        <div class="col-auto">
                            <input type="text" id="searchState" class="form-control" placeholder="State" maxlength="2" style="width:80px"
                                   value="{{ $zone->state_code }}">
                        </div>
                        <div class="col-auto">
                            <select id="searchLayer" class="form-select">
                                <option value="boundary">Boundary</option>
                                <option value="exclusion">Exclusion</option>
                                <option value="inclusion">Override</option>
                            </select>
                        </div>
                        <div class="col-auto">
                            <button class="btn btn-primary" onclick="doSearch()">
                                <span id="searchSpinner" class="spinner-border spinner-border-sm me-1"></span>
                                <i class="bi bi-search me-1" id="searchIcon"></i>Search
                            </button>
                        </div>
                    </div>

                    <div id="zoneMap"></div>

                    <div class="mt-3 d-flex gap-2">
                        <button class="btn btn-success" id="btnLock" onclick="lockCurrent()" disabled>
                            <i class="bi bi-lock me-1"></i>Lock Area
                        </button>
                        <button class="btn btn-outline-secondary" onclick="clearPreview()">
                            <i class="bi bi-x-lg me-1"></i>Clear Preview
                        </button>
                        <div class="ms-auto">
                            <label class="btn btn-outline-info mb-0">
                                <i class="bi bi-upload me-1"></i>Import GeoJSON
                                <input type="file" id="fileImport" accept=".json,.geojson" style="display:none" onchange="handleFileImport(this)">
                            </label>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card shadow-sm mb-3">
                <div class="card-header fw-bold"><i class="bi bi-gear text-primary me-2"></i>Zone Details</div>
                <div class="card-body">
                    <div class="mb-2">
                        <label class="form-label fw-semibold">Zone Name</label>
                        <input type="text" id="zoneName" class="form-control" value="{{ $zone->name }}">
                    </div>
                    <div class="row g-2 mb-2">
                        <div class="col-6">
                            <label class="form-label fw-semibold">Country</label>
                            <input type="text" id="countryCode" class="form-control" value="{{ $zone->country_code }}" maxlength="2">
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-semibold">State</label>
                            <input type="text" id="stateCode" class="form-control" value="{{ $zone->state_code }}" maxlength="2">
                        </div>
                    </div>
                    <div class="row g-2 mb-2">
                        <div class="col-6">
                            <label class="form-label fw-semibold">Priority</label>
                            <input type="number" id="priority" class="form-control" value="{{ $zone->priority }}" min="0">
                        </div>
                        <div class="col-6 d-flex align-items-end">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="isActive" {{ $zone->is_active ? 'checked' : '' }}>
                                <label class="form-check-label fw-semibold" for="isActive">Active</label>
                            </div>
                        </div>
                    </div>
                    <div class="text-muted small">ID: <code>{{ $zone->id }}</code></div>
                </div>
            </div>

            <div class="layer-section">
                <h6><i class="bi bi-layers text-primary me-1"></i>Boundary Areas <span class="badge bg-primary" id="compCount">0</span></h6>
                <div id="componentsList"></div>
                <div class="text-muted small" id="compEmpty" style="display:none;">No boundary components.</div>
            </div>

            <div class="layer-section">
                <h6><i class="bi bi-shield-x text-warning me-1"></i>Exclusions <span class="badge bg-warning text-dark" id="exCount">0</span></h6>
                <div id="exclusionsList"></div>
                <div class="text-muted small" id="exEmpty">No exclusions.</div>
            </div>

            <div class="layer-section">
                <h6><i class="bi bi-shield-check text-success me-1"></i>Overrides <span class="badge bg-success" id="incCount">0</span></h6>
                <div id="inclusionsList"></div>
                <div class="text-muted small" id="incEmpty">No overrides.</div>
            </div>

            <button class="btn btn-primary w-100 mt-3" onclick="saveZone()" id="btnSave">
                <i class="bi bi-check-lg me-1"></i>Update Zone
            </button>
            <div id="saveError" class="alert alert-danger mt-2" style="display:none;"></div>

            {{-- Assigned Drivers --}}
            <div class="layer-section mt-3">
                <h6 class="fw-bold mb-2"><i class="bi bi-people text-info me-1"></i>Assigned Drivers</h6>
                <div class="input-group mb-2">
                    <input type="text" class="form-control form-control-sm" id="driverSearchInput" placeholder="Search driver by name, phone, email…">
                    <button class="btn btn-sm btn-outline-primary" type="button" id="btnSearchDriver">
                        <i class="bi bi-search"></i>
                    </button>
                </div>
                <div id="driverSearchResults" style="display:none; max-height: 200px; overflow-y: auto; border: 1px solid #dee2e6; border-radius: 6px; margin-bottom: 8px;"></div>
                <div id="assignedDriversList"></div>
                <div class="text-muted small" id="noDriversMsg">No drivers assigned.</div>
                <button class="btn btn-sm btn-success w-100 mt-2" type="button" onclick="saveDrivers()" id="btnSaveDrivers">
                    <i class="bi bi-check-lg me-1"></i>Save Driver Assignments
                </button>
                <div id="driverSaveAlert" class="alert mt-2 d-none"></div>
            </div>
        </div>
    </div>

</div>
</div>
@endsection

@push('script')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
var map = L.map('zoneMap').setView([39.8, -98.5], 4);
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '&copy; OSM', maxZoom: 18
}).addTo(map);

var previewLayer = null;
var currentSearchResult = null;
var components = @json($zoneComponents);
var exclusions = @json($zoneExclusions);
var inclusions = @json($zoneInclusions);

var layerGroups = { boundary: L.layerGroup().addTo(map), exclusion: L.layerGroup().addTo(map), inclusion: L.layerGroup().addTo(map) };
var COLORS = { boundary: '#2196F3', exclusion: '#FF9800', inclusion: '#4CAF50' };

function addToMapPermanent(geojson, layer) {
    var l = L.geoJSON(geojson, { style: { color: COLORS[layer], weight: 2, fillOpacity: 0.2 } });
    layerGroups[layer].addLayer(l);
}

function rebuildMapLayers() {
    layerGroups.boundary.clearLayers();
    layerGroups.exclusion.clearLayers();
    layerGroups.inclusion.clearLayers();
    components.forEach(function(c) { addToMapPermanent(c.geometry, 'boundary'); });
    exclusions.forEach(function(c) { addToMapPermanent(c.geometry, 'exclusion'); });
    inclusions.forEach(function(c) { addToMapPermanent(c.geometry, 'inclusion'); });
}

rebuildMapLayers();
renderLists();

// fit to existing boundary
if (components.length > 0) {
    var allLayers = layerGroups.boundary.getLayers();
    if (allLayers.length > 0) {
        var bounds = L.featureGroup(allLayers).getBounds();
        if (bounds.isValid()) map.fitBounds(bounds, { padding: [20, 20] });
    }
}

function doSearch() {
    var q = document.getElementById('searchQuery').value.trim();
    var type = document.getElementById('searchType').value;
    var state = document.getElementById('searchState').value.trim();
    if (!q) return;

    document.getElementById('searchSpinner').style.display = 'inline-block';
    document.getElementById('searchIcon').style.display = 'none';
    document.getElementById('btnLock').disabled = true;
    currentSearchResult = null;

    fetch("{{ route('admin.service-zone.lookup-boundary') }}?q=" + encodeURIComponent(q)
        + "&type=" + type + "&state=" + encodeURIComponent(state))
        .then(function(r) { return r.json(); })
        .then(function(data) {
            document.getElementById('searchSpinner').style.display = 'none';
            document.getElementById('searchIcon').style.display = 'inline';
            if (!data.success) { alert(data.message || 'No boundary found.'); return; }
            showPreview(data.data);
        }).catch(function(err) {
            document.getElementById('searchSpinner').style.display = 'none';
            document.getElementById('searchIcon').style.display = 'inline';
            alert('Search failed: ' + err.message);
        });
}

document.getElementById('searchQuery').addEventListener('keydown', function(e) {
    if (e.key === 'Enter') { e.preventDefault(); doSearch(); }
});

function showPreview(result) {
    clearPreview();
    currentSearchResult = result;
    previewLayer = L.geoJSON(result.geojson, {
        style: { color: '#E91E63', weight: 3, fillOpacity: 0.15, dashArray: '8 4' }
    }).addTo(map);
    map.fitBounds(previewLayer.getBounds(), { padding: [20, 20] });
    document.getElementById('btnLock').disabled = false;
}

function clearPreview() {
    if (previewLayer) { map.removeLayer(previewLayer); previewLayer = null; }
    currentSearchResult = null;
    document.getElementById('btnLock').disabled = true;
}

function lockCurrent() {
    if (!currentSearchResult) return;
    var layer = document.getElementById('searchLayer').value;
    var item = {
        label: currentSearchResult.short_name || currentSearchResult.name,
        component_type: document.getElementById('searchType').value,
        source: 'nominatim',
        geometry: currentSearchResult.geojson
    };
    if (layer === 'boundary') components.push(item);
    else if (layer === 'exclusion') exclusions.push(item);
    else inclusions.push(item);
    addToMapPermanent(item.geometry, layer);
    clearPreview();
    renderLists();
}

function removeItem(layer, index) {
    if (layer === 'boundary') components.splice(index, 1);
    else if (layer === 'exclusion') exclusions.splice(index, 1);
    else inclusions.splice(index, 1);
    rebuildMapLayers();
    renderLists();
}

function renderLists() {
    renderList('componentsList', components, 'boundary', 'compCount', 'compEmpty');
    renderList('exclusionsList', exclusions, 'exclusion', 'exCount', 'exEmpty');
    renderList('inclusionsList', inclusions, 'inclusion', 'incCount', 'incEmpty');
}

function renderList(containerId, items, layer, countId, emptyId) {
    var el = document.getElementById(containerId);
    document.getElementById(countId).textContent = items.length;
    document.getElementById(emptyId).style.display = items.length ? 'none' : 'block';
    var html = '';
    items.forEach(function(item, i) {
        var cls = layer === 'exclusion' ? 'exclusion' : (layer === 'inclusion' ? 'inclusion' : '');
        html += '<div class="locked-item ' + cls + '">'
            + '<div><div class="label">' + escHtml(item.label) + '</div>'
            + '<div class="meta">' + item.component_type + ' &middot; ' + item.source + '</div></div>'
            + '<button class="btn btn-sm btn-outline-danger" onclick="removeItem(\'' + layer + '\',' + i + ')">'
            + '<i class="bi bi-x-lg"></i></button></div>';
    });
    el.innerHTML = html;
}

function escHtml(s) { var d = document.createElement('div'); d.textContent = s; return d.innerHTML; }

function handleFileImport(input) {
    if (!input.files[0]) return;
    var reader = new FileReader();
    reader.onload = function(e) {
        try {
            var gj = JSON.parse(e.target.result);
            var mp;
            if (gj.type === 'FeatureCollection') {
                mp = { type: 'MultiPolygon', coordinates: [] };
                (gj.features || []).forEach(function(f) {
                    var g = f.geometry || f;
                    if (g.type === 'Polygon') mp.coordinates.push(g.coordinates);
                    else if (g.type === 'MultiPolygon') mp.coordinates = mp.coordinates.concat(g.coordinates);
                });
            } else if (gj.type === 'Feature') { mp = gj.geometry; } else { mp = gj; }
            if (mp.type === 'Polygon') mp = { type: 'MultiPolygon', coordinates: [mp.coordinates] };
            currentSearchResult = { name: input.files[0].name, short_name: 'Imported: ' + input.files[0].name, geojson: mp };
            showPreview(currentSearchResult);
        } catch(err) { alert('Invalid GeoJSON: ' + err.message); }
    };
    reader.readAsText(input.files[0]);
    input.value = '';
}

function showSaveError(html) {
    document.getElementById('btnSave').disabled = false;
    document.getElementById('btnSave').innerHTML = '<i class="bi bi-check-lg me-1"></i>Update Zone';
    var box = document.getElementById('saveError');
    box.innerHTML = html;
    box.style.display = 'block';
    box.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
}

function formatErrors(data) {
    var parts = [];
    if (data.message) parts.push('<strong>' + escHtml(data.message) + '</strong>');
    if (data.errors) {
        var list = '<ul class="mb-0 mt-1">';
        Object.values(data.errors).forEach(function(arr) {
            (Array.isArray(arr) ? arr : [arr]).forEach(function(msg) { list += '<li>' + escHtml(msg) + '</li>'; });
        });
        list += '</ul>';
        parts.push(list);
    }
    if (data.warnings && data.warnings.length) {
        parts.push('<div class="mt-1 text-warning"><strong>Warnings:</strong><ul class="mb-0">'
            + data.warnings.map(function(w){ return '<li>' + escHtml(w) + '</li>'; }).join('') + '</ul></div>');
    }
    return parts.join('') || 'Unknown error.';
}

function saveZone() {
    var name = document.getElementById('zoneName').value.trim();
    if (!name) { showSaveError('Zone name is required.'); return; }
    if (components.length === 0) { showSaveError('Lock at least one boundary area.'); return; }

    document.getElementById('btnSave').disabled = true;
    document.getElementById('btnSave').innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Saving...';
    document.getElementById('saveError').style.display = 'none';

    var payload = {
        name: name,
        country_code: document.getElementById('countryCode').value.trim() || 'US',
        state_code: document.getElementById('stateCode').value.trim() || null,
        priority: parseInt(document.getElementById('priority').value) || 0,
        is_active: document.getElementById('isActive').checked,
        components: components,
        exclusions: exclusions,
        inclusions: inclusions
    };

    fetch("{{ route('admin.service-zone.update', $zone->id) }}", {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json'
        },
        body: JSON.stringify(payload)
    }).then(function(r) {
        var ct = r.headers.get('content-type') || '';
        if (ct.indexOf('application/json') === -1) {
            return r.text().then(function(txt) {
                throw new Error('Server returned non-JSON (HTTP ' + r.status + '). Check server logs.');
            });
        }
        return r.json();
    }).then(function(data) {
        if (data.success) {
            window.location.href = data.redirect;
        } else {
            showSaveError(formatErrors(data));
        }
    }).catch(function(err) {
        showSaveError(escHtml(err.message || 'Network error. Please try again.'));
    });
}

// ─── Driver Assignment ──────────────────────────────────────────────────
var assignedDrivers = @json($assignedDrivers);

function renderAssignedDrivers() {
    var list = document.getElementById('assignedDriversList');
    var msg = document.getElementById('noDriversMsg');
    list.innerHTML = '';
    msg.style.display = assignedDrivers.length ? 'none' : '';
    assignedDrivers.forEach(function(d, i) {
        var row = document.createElement('div');
        row.className = 'locked-item';
        row.innerHTML = '<div><span class="label">' + escHtml(d.name) + '</span><br><span class="meta">' + escHtml(d.phone || '') + (d.email ? ' · ' + escHtml(d.email) : '') + '</span></div>'
            + '<button class="btn btn-sm btn-outline-danger" onclick="removeDriver(' + i + ')"><i class="bi bi-x"></i></button>';
        list.appendChild(row);
    });
}
renderAssignedDrivers();

function removeDriver(idx) {
    assignedDrivers.splice(idx, 1);
    renderAssignedDrivers();
}

document.getElementById('btnSearchDriver').addEventListener('click', doDriverSearch);
document.getElementById('driverSearchInput').addEventListener('keydown', function(e) {
    if (e.key === 'Enter') { e.preventDefault(); doDriverSearch(); }
});

function doDriverSearch() {
    var q = document.getElementById('driverSearchInput').value.trim();
    if (q.length < 1) return;
    var box = document.getElementById('driverSearchResults');
    box.style.display = '';
    box.innerHTML = '<div class="p-2 text-muted small">Searching…</div>';

    fetch("{{ route('admin.service-zone.drivers.search', $zone->id) }}?q=" + encodeURIComponent(q), {
        headers: { 'Accept': 'application/json' }
    }).then(function(r) { return r.json(); })
    .then(function(data) {
        if (!data.success || !data.drivers.length) {
            box.innerHTML = '<div class="p-2 text-muted small">No drivers found.</div>';
            return;
        }
        var alreadyIds = assignedDrivers.map(function(d) { return d.id; });
        var html = '';
        data.drivers.forEach(function(d) {
            var disabled = alreadyIds.indexOf(d.id) >= 0;
            html += '<div class="px-2 py-1 border-bottom d-flex justify-content-between align-items-center' + (disabled ? ' bg-light text-muted' : '') + '" style="cursor:pointer;" '
                + (disabled ? '' : 'onclick="addDriver(\'' + d.id + '\',\'' + escHtml(d.name) + '\',\'' + escHtml(d.phone || '') + '\',\'' + escHtml(d.email || '') + '\')"')
                + '><div><strong>' + escHtml(d.name) + '</strong><br><small>' + escHtml(d.phone || '') + (d.email ? ' · ' + escHtml(d.email) : '') + '</small></div>'
                + (disabled ? '<span class="badge bg-secondary">assigned</span>' : '<span class="badge bg-primary">+ Add</span>')
                + '</div>';
        });
        box.innerHTML = html;
    }).catch(function() {
        box.innerHTML = '<div class="p-2 text-danger small">Search failed.</div>';
    });
}

function addDriver(id, name, phone, email) {
    if (assignedDrivers.some(function(d) { return d.id === id; })) return;
    assignedDrivers.push({ id: id, name: name, phone: phone, email: email });
    renderAssignedDrivers();
    document.getElementById('driverSearchResults').style.display = 'none';
    document.getElementById('driverSearchInput').value = '';
}

function saveDrivers() {
    var btn = document.getElementById('btnSaveDrivers');
    var alert = document.getElementById('driverSaveAlert');
    btn.disabled = true;
    alert.className = 'alert mt-2 d-none';

    var ids = assignedDrivers.map(function(d) { return d.id; });

    fetch("{{ route('admin.service-zone.drivers.sync', $zone->id) }}", {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json'
        },
        body: JSON.stringify({ driver_ids: ids })
    }).then(function(r) { return r.json(); })
    .then(function(data) {
        btn.disabled = false;
        if (data.success) {
            alert.className = 'alert alert-success mt-2';
            alert.textContent = data.message;
        } else {
            alert.className = 'alert alert-danger mt-2';
            alert.textContent = data.message || 'Save failed.';
        }
    }).catch(function(err) {
        btn.disabled = false;
        alert.className = 'alert alert-danger mt-2';
        alert.textContent = 'Network error.';
    });
}
</script>
@endpush
